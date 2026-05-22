<?php
/**
 * CiteLeap , unified row-action handlers.
 *
 * Every queue row supports a consistent set of actions, gated on its
 * current status. This file is the single source of truth for those
 * transitions so the Planner UI and any future REST/CLI surface stay
 * coherent.
 *
 * STATE MACHINE (the only allowed transitions):
 *
 *   queued     ── Write draft ──→ drafted
 *   queued     ── Pin datetime ──→ queued + publish_at
 *   queued     ── Pause / Resume ──→ queued + paused flag
 *   queued     ── Remove ──→ (gone)
 *
 *   drafted    ── Schedule for X ──→ scheduled (post -> status=future)
 *   drafted    ── Publish now ──→ scheduled (post -> publish immediately)
 *   drafted    ── Pause / Resume ──→ drafted + paused flag
 *   drafted    ── Remove ──→ (gone; WP draft post stays as WP draft)
 *
 *   scheduled  ── Reschedule ──→ scheduled (post_date_gmt updated)
 *   scheduled  ── Unschedule ──→ drafted (post -> status=draft)
 *   scheduled  ── Publish now ──→ scheduled (post -> publish immediately)
 *   scheduled  ── Pause / Resume ──→ scheduled + paused (cron skips, WP still publishes at slot unless you also unschedule)
 *   scheduled  ── Remove ──→ (gone; WP post stays as future)
 *
 *   queued_refresh ── Refresh now ──→ refreshing
 *   queued_refresh ── Pause / Resume ──→ queued_refresh + paused
 *   queued_refresh ── Remove ──→ (gone)
 *
 *   refreshing ── Reset stuck ──→ failed
 *
 *   pending_review ── Approve ──→ refreshed (live post overwritten)
 *   pending_review ── Reject ──→ failed (live post unchanged)
 *
 *   failed     ── Retry ──→ queued (for content) or queued_refresh (for refresh items)
 *   failed     ── Remove ──→ (gone)
 *
 *   refreshed / published: terminal. Only Remove from the row.
 *
 * @package CiteLeap
 */

defined( 'ABSPATH' ) || exit;

class CiteLeap_Actions {

	/** Return [index, row] for the queue entry with this id, or null. */
	public static function find( string $id ): ?array {
		$queue = (array) get_option( CITELEAP_OPTION_QUEUE, [] );
		foreach ( $queue as $i => $row ) {
			if ( ( $row['id'] ?? '' ) === $id ) return [ $i, $row, $queue ];
		}
		return null;
	}

	private static function persist( array $queue ): void {
		update_option( CITELEAP_OPTION_QUEUE, array_values( $queue ), false );
	}

	public static function pause( string $id ): bool {
		$f = self::find( $id ); if ( ! $f ) return false;
		[ $i, $row, $queue ] = $f;
		$queue[ $i ]['paused']      = 1;
		$queue[ $i ]['paused_at']   = current_time( 'mysql' );
		self::persist( $queue );
		CiteLeap_Log::add( 'paused', $id . ' paused (status=' . ( $row['status'] ?? '' ) . ')', 'info' );
		return true;
	}

	public static function resume( string $id ): bool {
		$f = self::find( $id ); if ( ! $f ) return false;
		[ $i, $row, $queue ] = $f;
		unset( $queue[ $i ]['paused'], $queue[ $i ]['paused_at'] );
		self::persist( $queue );
		CiteLeap_Log::add( 'resumed', $id, 'info' );
		return true;
	}

	/** Move a drafted row to scheduled at $when (Y-m-d H:i in site tz). */
	public static function schedule_drafted( string $id, string $when_local ): array {
		$f = self::find( $id ); if ( ! $f ) return [ 'ok' => false, 'error' => 'not found' ];
		[ $i, $row, $queue ] = $f;
		if ( ( $row['status'] ?? '' ) !== 'drafted' ) return [ 'ok' => false, 'error' => 'not in drafted state' ];
		$post_id = (int) ( $row['post_id'] ?? 0 );
		if ( ! $post_id ) return [ 'ok' => false, 'error' => 'no post id on row' ];
		$ts = strtotime( $when_local );
		if ( $ts <= 0 ) return [ 'ok' => false, 'error' => 'invalid datetime' ];
		$publish_at_gmt = gmdate( 'Y-m-d H:i:s', $ts );
		$publish_at     = get_date_from_gmt( $publish_at_gmt );
		wp_update_post( [
			'ID'            => $post_id,
			'post_status'   => 'future',
			'post_date'     => $publish_at,
			'post_date_gmt' => $publish_at_gmt,
		] );
		$queue[ $i ]['status']        = 'scheduled';
		$queue[ $i ]['scheduled_for'] = $publish_at;
		self::persist( $queue );
		CiteLeap_Log::add( 'scheduled_manual', '#' . $post_id . ' for ' . $publish_at, 'info' );
		return [ 'ok' => true, 'error' => '' ];
	}

	/** Update the future publish date of a scheduled row. */
	public static function reschedule( string $id, string $when_local ): array {
		$f = self::find( $id ); if ( ! $f ) return [ 'ok' => false, 'error' => 'not found' ];
		[ $i, $row, $queue ] = $f;
		if ( ( $row['status'] ?? '' ) !== 'scheduled' ) return [ 'ok' => false, 'error' => 'not in scheduled state' ];
		$post_id = (int) ( $row['post_id'] ?? 0 );
		$ts = strtotime( $when_local );
		if ( $ts <= 0 ) return [ 'ok' => false, 'error' => 'invalid datetime' ];
		$publish_at_gmt = gmdate( 'Y-m-d H:i:s', $ts );
		$publish_at     = get_date_from_gmt( $publish_at_gmt );
		wp_update_post( [
			'ID'            => $post_id,
			'post_status'   => 'future',
			'post_date'     => $publish_at,
			'post_date_gmt' => $publish_at_gmt,
		] );
		$queue[ $i ]['scheduled_for'] = $publish_at;
		self::persist( $queue );
		CiteLeap_Log::add( 'rescheduled', '#' . $post_id . ' -> ' . $publish_at, 'info' );
		return [ 'ok' => true, 'error' => '' ];
	}

	/** Move a scheduled row back to drafted (WP post -> status=draft). */
	public static function unschedule( string $id ): array {
		$f = self::find( $id ); if ( ! $f ) return [ 'ok' => false, 'error' => 'not found' ];
		[ $i, $row, $queue ] = $f;
		if ( ( $row['status'] ?? '' ) !== 'scheduled' ) return [ 'ok' => false, 'error' => 'not in scheduled state' ];
		$post_id = (int) ( $row['post_id'] ?? 0 );
		wp_update_post( [ 'ID' => $post_id, 'post_status' => 'draft' ] );
		$queue[ $i ]['status'] = 'drafted';
		unset( $queue[ $i ]['scheduled_for'] );
		self::persist( $queue );
		CiteLeap_Log::add( 'unscheduled', '#' . $post_id, 'info' );
		return [ 'ok' => true, 'error' => '' ];
	}

	/** Publish a drafted or scheduled post right now. */
	public static function publish_now( string $id ): array {
		$f = self::find( $id ); if ( ! $f ) return [ 'ok' => false, 'error' => 'not found' ];
		[ $i, $row, $queue ] = $f;
		if ( ! in_array( ( $row['status'] ?? '' ), [ 'drafted', 'scheduled' ], true ) ) return [ 'ok' => false, 'error' => 'not in drafted/scheduled state' ];
		$post_id = (int) ( $row['post_id'] ?? 0 );
		wp_update_post( [
			'ID'            => $post_id,
			'post_status'   => 'publish',
			'post_date'     => current_time( 'mysql' ),
			'post_date_gmt' => current_time( 'mysql', 1 ),
		] );
		$queue[ $i ]['status']        = 'published';
		$queue[ $i ]['scheduled_for'] = current_time( 'mysql' );
		self::persist( $queue );
		CiteLeap_Log::add( 'published_manual', '#' . $post_id, 'info' );
		return [ 'ok' => true, 'error' => '' ];
	}

	/** Retry a failed row: reset to its proper queued state. */
	public static function retry( string $id ): array {
		$f = self::find( $id ); if ( ! $f ) return [ 'ok' => false, 'error' => 'not found' ];
		[ $i, $row, $queue ] = $f;
		if ( ( $row['status'] ?? '' ) !== 'failed' ) return [ 'ok' => false, 'error' => 'not in failed state' ];
		/* If the row had a post_id and the row came from a refresh, it is a refresh retry. */
		$is_refresh = ( ( $row['type'] ?? '' ) === 'refresh' ) || ! empty( $row['post_id'] );
		$queue[ $i ]['status'] = $is_refresh ? 'queued_refresh' : 'queued';
		unset( $queue[ $i ]['error'], $queue[ $i ]['finished_at'] );
		self::persist( $queue );
		CiteLeap_Log::add( 'retry', $id . ' -> ' . $queue[ $i ]['status'], 'info' );
		return [ 'ok' => true, 'error' => '' ];
	}

	public static function remove( string $id ): bool {
		$queue = (array) get_option( CITELEAP_OPTION_QUEUE, [] );
		$out = [];
		$removed = false;
		foreach ( $queue as $row ) {
			if ( ( $row['id'] ?? '' ) === $id ) { $removed = true; continue; }
			$out[] = $row;
		}
		if ( $removed ) {
			update_option( CITELEAP_OPTION_QUEUE, $out, false );
			CiteLeap_Log::add( 'removed', $id, 'info' );
		}
		return $removed;
	}
}

/* admin-post handlers , one per action, all gated by manage_options + nonce. */
$__bvi_actions = [
	'citeleap_pause'       => [ 'pause',       [ 'id' ] ],
	'citeleap_resume'      => [ 'resume',      [ 'id' ] ],
	'citeleap_publish_now' => [ 'publish_now', [ 'id' ] ],
	'citeleap_unschedule'  => [ 'unschedule',  [ 'id' ] ],
	'citeleap_retry'       => [ 'retry',       [ 'id' ] ],
];
foreach ( $__bvi_actions as $__act => $__cfg ) {
	add_action( 'admin_post_' . $__act, function () use ( $__cfg ) {
		CiteLeap_Caps::guard_use();
		check_admin_referer( CITELEAP_NONCE );
		$id  = sanitize_text_field( wp_unslash( (string) ( $_POST['id'] ?? '' ) ) );
		$fn  = $__cfg[0];
		CiteLeap_Actions::$fn( $id );
		wp_safe_redirect( add_query_arg( [ 'page' => 'citeleap', 'tab' => 'planner', 'citeleap_msg' => 'action-' . $fn ], admin_url( 'admin.php' ) ) );
		exit;
	} );
}

/* Schedule a drafted row at operator-picked datetime. */
add_action( 'admin_post_citeleap_schedule_drafted', function () {
	CiteLeap_Caps::guard_use();
	check_admin_referer( CITELEAP_NONCE );
	$id   = sanitize_text_field( wp_unslash( (string) ( $_POST['id']   ?? '' ) ) );
	$when = sanitize_text_field( wp_unslash( (string) ( $_POST['when'] ?? '' ) ) );
	$res  = CiteLeap_Actions::schedule_drafted( $id, $when );
	wp_safe_redirect( add_query_arg( [ 'page' => 'citeleap', 'tab' => 'planner', 'citeleap_msg' => $res['ok'] ? 'action-scheduled' : 'action-err' ], admin_url( 'admin.php' ) ) );
	exit;
} );

/* Reschedule a scheduled row to a different datetime. */
add_action( 'admin_post_citeleap_reschedule', function () {
	CiteLeap_Caps::guard_use();
	check_admin_referer( CITELEAP_NONCE );
	$id   = sanitize_text_field( wp_unslash( (string) ( $_POST['id']   ?? '' ) ) );
	$when = sanitize_text_field( wp_unslash( (string) ( $_POST['when'] ?? '' ) ) );
	$res  = CiteLeap_Actions::reschedule( $id, $when );
	wp_safe_redirect( add_query_arg( [ 'page' => 'citeleap', 'tab' => 'planner', 'citeleap_msg' => $res['ok'] ? 'action-rescheduled' : 'action-err' ], admin_url( 'admin.php' ) ) );
	exit;
} );
