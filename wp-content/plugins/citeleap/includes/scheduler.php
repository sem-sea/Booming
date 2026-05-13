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
		$schedule = (array) get_option( CITELEAP_OPTION_SCHEDULE, [] );
		if ( empty( $schedule['auto'] ) ) return;

		$start_ts = isset( $schedule['start_date'] ) ? strtotime( (string) $schedule['start_date'] ) : 0;
		if ( $start_ts && $start_ts > time() ) return;

		/* Find the next slot we should publish on. */
		$next_slot = self::next_slot( $schedule );
		if ( $next_slot > time() ) return;

		/* Make sure we have ideas. */
		$queue = (array) get_option( CITELEAP_OPTION_QUEUE, [] );
		$queued = array_values( array_filter( $queue, fn( $r ) => ( $r['status'] ?? '' ) === 'queued' ) );
		if ( empty( $queued ) ) {
			$gen = CiteLeap_Generator::generate_ideas( max( 5, (int) ( $schedule['posts_per_week'] ?? 3 ) ) );
			if ( ! $gen['ok'] ) return;
			$queue = (array) get_option( CITELEAP_OPTION_QUEUE, [] );
			$queued = array_values( array_filter( $queue, fn( $r ) => ( $r['status'] ?? '' ) === 'queued' ) );
			if ( empty( $queued ) ) return;
		}

		/* Pick the highest-priority queued idea. */
		usort( $queued, fn( $a, $b ) => ( (int) ( $b['priority'] ?? 5 ) ) <=> ( (int) ( $a['priority'] ?? 5 ) ) );
		$idea = $queued[0];

		$res = CiteLeap_Generator::write_post_from_idea( (string) $idea['id'] );
		if ( ! $res['ok'] || ! $res['post_id'] ) return;

		/* Schedule the publish at the slot time. */
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
