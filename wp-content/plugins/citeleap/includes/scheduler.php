<?php
/**
 * CiteLeap , scheduler.
 *
 * Runs every hour via WP cron. On each tick:
 *   - If auto-publish is OFF, do nothing.
 *   - If start_date is in the future, do nothing.
 *   - If no idea is queued, generate a fresh batch (size = 1 week's
 *     worth at the configured frequency).
 *   - If a queued idea exists and the next scheduled publish slot is
 *     now or past, draft it and schedule it at the slot time (using
 *     post_status='future' so WP core publishes it).
 *
 * @package CiteLeap
 */

defined( 'ABSPATH' ) || exit;

class CiteLeap_Scheduler {

	public static function tick(): void {
		/* Transient lock: prevent concurrent ticks (manual "run tick
		 * now" race with the hourly cron, or duplicate cron firings).
		 * 5 minute expiry so a stuck tick eventually self-clears. */
		if ( get_transient( 'citeleap_tick_lock' ) ) {
			CiteLeap_Log::add( 'tick_locked', 'previous tick still in flight, skipping', 'warn' );
			return;
		}
		set_transient( 'citeleap_tick_lock', 1, 5 * MINUTE_IN_SECONDS );

		try {
			/* Refresh tick runs independently of new-content auto-publish. */
			self::tick_refresh();

			$schedule = (array) get_option( CITELEAP_OPTION_SCHEDULE, [] );
			$mode     = self::auto_mode( $schedule );
			if ( 'off' === $mode ) { delete_transient( 'citeleap_tick_lock' ); return; }
			$start_ts = isset( $schedule['start_date'] ) ? strtotime( (string) $schedule['start_date'] ) : 0;
			if ( $start_ts && $start_ts > time() ) { delete_transient( 'citeleap_tick_lock' ); return; }

			self::tick_new_content( $schedule, $mode );
		} catch ( \Throwable $e ) {
			CiteLeap_Log::add( 'tick_exception', $e->getMessage(), 'critical' );
		} finally {
			delete_transient( 'citeleap_tick_lock' );
		}
	}

	/**
	 * What WILL happen to this queued row if nothing changes?
	 * Used by the Planner to show "Next scheduled for ..." on each row.
	 *
	 * Returns a unix timestamp (0 if mode is off + no per-post override).
	 */
	public static function eta_for( array $row, ?array $schedule = null ): int {
		$schedule = $schedule ?? (array) get_option( CITELEAP_OPTION_SCHEDULE, [] );
		$mode     = self::auto_mode( $schedule );
		if ( ! empty( $row['publish_at'] ) ) return (int) strtotime( (string) $row['publish_at'] );
		if ( 'off' === $mode ) return 0;
		if ( 'draft' === $mode ) {
			$ts = wp_next_scheduled( CITELEAP_CRON_HOURLY );
			return $ts ? (int) $ts : time() + HOUR_IN_SECONDS;
		}
		return self::next_slot( $schedule );
	}

	public static function auto_mode( ?array $schedule = null ): string {
		$schedule = $schedule ?? (array) get_option( CITELEAP_OPTION_SCHEDULE, [] );
		$m = (string) ( $schedule['auto_mode'] ?? '' );
		if ( in_array( $m, [ 'off', 'draft', 'publish' ], true ) ) return $m;
		return ! empty( $schedule['auto'] ) ? 'publish' : 'off';   // legacy boolean fallback
	}

	private static function tick_new_content( array $schedule, string $mode = 'publish' ): void {

		/* PER-POST OVERRIDE FIRST. Any queued row with a publish_at
		 * timestamp at or before "now" wins, regardless of mode + slot.
		 * Sorted by publish_at ascending so the oldest due slot fires
		 * first. This is how the operator pins a specific topic to a
		 * specific datetime. */
		$queue   = (array) get_option( CITELEAP_OPTION_QUEUE, [] );
		$pinned  = [];
		foreach ( $queue as $row ) {
			if ( ( $row['status'] ?? '' ) !== 'queued' ) continue;
			$pa = isset( $row['publish_at'] ) ? strtotime( (string) $row['publish_at'] ) : 0;
			if ( $pa > 0 && $pa <= time() ) $pinned[] = $row + [ '_pa' => $pa ];
		}
		if ( $pinned ) {
			usort( $pinned, fn( $a, $b ) => $a['_pa'] <=> $b['_pa'] );
			$idea = $pinned[0];
			$next_slot = (int) $idea['_pa'];
		} else {
			/* In 'publish' mode the next slot gates how often we
			 * draft+schedule. In 'draft' mode the tick runs every hour
			 * regardless of slot. */
			if ( 'publish' === $mode ) {
				$next_slot = self::next_slot( $schedule );
				if ( $next_slot > time() ) return;
			} else {
				$next_slot = time();
			}
			/* Make sure we have ideas. */
			$queued = array_values( array_filter( $queue, fn( $r ) => ( $r['status'] ?? '' ) === 'queued' && empty( $r['publish_at'] ) ) );
			if ( empty( $queued ) ) {
				$gen = CiteLeap_Generator::generate_ideas( max( 5, (int) ( $schedule['posts_per_week'] ?? 3 ) ) );
				if ( ! $gen['ok'] ) return;
				$queue  = (array) get_option( CITELEAP_OPTION_QUEUE, [] );
				$queued = array_values( array_filter( $queue, fn( $r ) => ( $r['status'] ?? '' ) === 'queued' && empty( $r['publish_at'] ) ) );
				if ( empty( $queued ) ) return;
			}
			/* Sort: priority desc, then created_at asc (FIFO within same priority). */
			usort( $queued, function ( $a, $b ) {
				$pa = (int) ( $a['priority'] ?? 5 );
				$pb = (int) ( $b['priority'] ?? 5 );
				if ( $pa !== $pb ) return $pb <=> $pa;
				return strcmp( (string) ( $a['created_at'] ?? '' ), (string) ( $b['created_at'] ?? '' ) );
			} );
			$idea = $queued[0];
		}

		$res = CiteLeap_Generator::write_post_from_idea( (string) $idea['id'] );
		if ( ! $res['ok'] || ! $res['post_id'] ) return;

		if ( 'draft' === $mode ) {
			/* Draft-only mode: post is already a WP draft after
			 * write_post_from_idea(). Nothing more to schedule. */
			CiteLeap_Log::add( 'post_drafted_auto', sprintf( '#%d auto-drafted (mode=draft)', $res['post_id'] ) );
			return;
		}

		/* publish mode: schedule the publish at the slot time. */
		$publish_at_gmt = gmdate( 'Y-m-d H:i:s', $next_slot );
		$publish_at     = get_date_from_gmt( $publish_at_gmt );
		wp_update_post( [
			'ID'            => (int) $res['post_id'],
			'post_status'   => 'future',
			'post_date'     => $publish_at,
			'post_date_gmt' => $publish_at_gmt,
		] );

		/* Mark idea as scheduled. */
		$queue = (array) get_option( CITELEAP_OPTION_QUEUE, [] );
		foreach ( $queue as $i => $row ) {
			if ( ( $row['id'] ?? '' ) === $idea['id'] ) {
				$queue[ $i ]['status']        = 'scheduled';
				$queue[ $i ]['scheduled_for'] = $publish_at;
				$queue[ $i ]['post_id']       = (int) $res['post_id'];
				break;
			}
		}
		update_option( CITELEAP_OPTION_QUEUE, $queue, false );

		CiteLeap_Log::add( 'post_scheduled', sprintf( '#%d for %s', $res['post_id'], $publish_at ) );
	}

	public static function next_slot( array $schedule ): int {
		$ppw    = max( 1, (int) ( $schedule['posts_per_week'] ?? 3 ) );
		$start  = isset( $schedule['start_date'] ) ? strtotime( (string) $schedule['start_date'] ) : time();
		$now    = time();
		if ( $start > $now ) return $start;

		$interval = (int) round( WEEK_IN_SECONDS / $ppw );
		$elapsed  = $now - $start;
		$slot_idx = (int) floor( $elapsed / $interval );
		$last     = self::last_scheduled_time();

		/* The candidate slot is the first slot index after the last
		 * scheduled post. We round up to "now" if no posts yet. */
		$candidate_idx = $last
			? (int) ceil( ( $last - $start ) / $interval ) + 1
			: $slot_idx;
		return $start + ( $candidate_idx * $interval );
	}

	/**
	 * Refresh tick , independent of new-content schedule.
	 * If auto-refresh is ON: enqueue due posts (capped at posts_per_week
	 * for this tick) and process the first queued_refresh entry.
	 */
	public static function tick_refresh(): void {
		$r = CiteLeap_Refresh::settings();
		if ( empty( $r['auto'] ) ) return;

		/* Top up the refresh queue with due posts if it is empty. */
		$queue        = (array) get_option( CITELEAP_OPTION_QUEUE, [] );
		$queued_count = 0;
		foreach ( $queue as $row ) if ( ( $row['status'] ?? '' ) === 'queued_refresh' ) $queued_count++;
		if ( 0 === $queued_count ) {
			$due = CiteLeap_Refresh::due_post_ids( max( 1, (int) $r['posts_per_week'] ) );
			if ( ! empty( $due ) ) CiteLeap_Refresh::enqueue_posts( $due );
		}

		/* Throttle: at most one refresh per hourly tick. */
		$queue = (array) get_option( CITELEAP_OPTION_QUEUE, [] );
		foreach ( $queue as $row ) {
			if ( ( $row['status'] ?? '' ) === 'queued_refresh' ) {
				CiteLeap_Refresh::refresh_from_queue( (string) $row['id'] );
				return;
			}
		}
	}

	private static function last_scheduled_time(): int {
		$ids = get_posts( [
			'post_type'      => 'post',
			'post_status'    => [ 'future', 'publish' ],
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => CITELEAP_META_SOURCE,
			'meta_value'     => 'auto',
			'orderby'        => 'date',
			'order'          => 'DESC',
			'no_found_rows'  => true,
		] );
		if ( empty( $ids ) ) return 0;
		return (int) strtotime( get_post_field( 'post_date_gmt', (int) $ids[0] ) . ' UTC' );
	}
}
