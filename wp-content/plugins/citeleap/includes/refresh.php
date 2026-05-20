<?php
/**
 * CiteLeap , content refresh module.
 *
 * Operator selects published posts -> they get added to the same
 * planner queue as items with status='queued_refresh'. The scheduler
 * then either drafts them automatically (if refresh auto-mode is ON)
 * or waits for a manual "Refresh now" click.
 *
 * Each refresh run feeds the old title + old content to the writing
 * model along with the master prompt, requests an updated version,
 * and overwrites the existing post (preserving slug, ID, date, post
 * meta, categories, comments). The post's _citeleap_refresh_count
 * meta increments and _citeleap_last_refreshed gets a timestamp.
 *
 * @package CiteLeap
 */

defined( 'ABSPATH' ) || exit;

class CiteLeap_Refresh {

	public static function settings(): array {
		$r    = (array) get_option( CITELEAP_OPTION_REFRESH, [] );
		$mode = (string) ( $r['auto_mode'] ?? '' );
		if ( ! in_array( $mode, [ 'off', 'draft', 'live' ], true ) ) {
			$mode = ! empty( $r['auto'] ) ? 'live' : 'off';   // legacy boolean fallback
		}
		return [
			'auto'              => ( 'off' !== $mode ) ? 1 : 0,        // legacy
			'auto_mode'         => $mode,                              // 'off' | 'draft' | 'live'
			'cadence_days'      => max( 7, (int) ( $r['cadence_days']      ?? 90 ) ),
			'posts_per_week'    => max( 1, (int) ( $r['posts_per_week']    ?? 2 ) ),
			'cadence_unit'      => (string) ( $r['cadence_unit']   ?? 'week' ),
			'posts_per_unit'    => max( 1, (int) ( $r['posts_per_unit'] ?? ( $r['posts_per_week'] ?? 2 ) ) ),
		];
	}

	public static function save_settings( array $args ): void {
		$mode = sanitize_key( (string) ( $args['auto_mode'] ?? 'off' ) );
		if ( ! in_array( $mode, [ 'off', 'draft', 'live' ], true ) ) $mode = 'off';
		$unit = in_array( (string) ( $args['cadence_unit'] ?? '' ), [ 'day', 'week', 'month', 'half_year', 'year' ], true ) ? (string) $args['cadence_unit'] : 'week';
		$ppu  = max( 1, min( 365, (int) ( $args['posts_per_unit'] ?? ( $args['posts_per_week'] ?? 2 ) ) ) );
		$unit_secs = match ( $unit ) {
			'day' => DAY_IN_SECONDS, 'month' => MONTH_IN_SECONDS,
			'half_year' => MONTH_IN_SECONDS * 6, 'year' => YEAR_IN_SECONDS,
			default => WEEK_IN_SECONDS,
		};
		update_option( CITELEAP_OPTION_REFRESH, [
			'auto'           => ( 'off' !== $mode ) ? 1 : 0,
			'auto_mode'      => $mode,
			'cadence_days'   => max( 7,  min( 365, (int) ( $args['cadence_days']   ?? 90 ) ) ),
			'cadence_unit'   => $unit,
			'posts_per_unit' => $ppu,
			'posts_per_week' => max( 1, min( 14, (int) ceil( $ppu * ( WEEK_IN_SECONDS / $unit_secs ) ) ) ),
		], false );
	}

	/**
	 * Add one or more existing posts to the queue as refresh items.
	 *
	 * @param int[] $post_ids
	 */
	public static function enqueue_posts( array $post_ids ): int {
		$queue = (array) get_option( CITELEAP_OPTION_QUEUE, [] );
		$existing_post_ids = [];
		foreach ( $queue as $row ) {
			if ( in_array( ( $row['status'] ?? '' ), [ 'queued_refresh', 'refreshing' ], true ) && ! empty( $row['post_id'] ) ) {
				$existing_post_ids[ (int) $row['post_id'] ] = true;
			}
		}
		$added = 0;
		foreach ( $post_ids as $pid ) {
			$pid = (int) $pid;
			if ( ! $pid ) continue;
			if ( isset( $existing_post_ids[ $pid ] ) ) continue;
			$post = get_post( $pid );
			if ( ! $post || 'post' !== $post->post_type ) continue;
			$queue[] = [
				'id'         => wp_generate_uuid4(),
				'type'       => 'refresh',
				'post_id'    => $pid,
				'slug'       => (string) $post->post_name,
				'title'      => (string) $post->post_title,
				'priority'   => 5,
				'status'     => 'queued_refresh',
				'created_at' => current_time( 'mysql' ),
			];
			$added++;
		}
		update_option( CITELEAP_OPTION_QUEUE, $queue, false );
		if ( $added ) CiteLeap_Log::add( 'refresh_queued', $added . ' post(s) queued for refresh' );
		return $added;
	}

	/**
	 * Refresh a single post by queue entry id. Writes a new body via
	 * the writing model and wp_update_post()s the existing post.
	 */
	public static function refresh_from_queue( string $queue_id ): array {
		$queue = (array) get_option( CITELEAP_OPTION_QUEUE, [] );
		$idx   = -1;
		foreach ( $queue as $i => $row ) {
			if ( ( $row['id'] ?? '' ) === $queue_id ) { $idx = $i; break; }
		}
		if ( -1 === $idx ) return [ 'ok' => false, 'post_id' => 0, 'error' => 'Refresh queue entry not found.' ];
		$entry   = $queue[ $idx ];
		$post_id = (int) ( $entry['post_id'] ?? 0 );
		$post    = $post_id ? get_post( $post_id ) : null;
		if ( ! $post ) return [ 'ok' => false, 'post_id' => 0, 'error' => 'Underlying post no longer exists.' ];

		if ( ! CiteLeap_Plan::has( 'refresh' ) ) {
			CiteLeap_Log::add( 'refresh_blocked_plan', sprintf( 'plan=%s post=%d', CiteLeap_Plan::current(), $post_id ), 'warn' );
			return [ 'ok' => false, 'post_id' => $post_id, 'error' => __( 'Refresh is not available on the Free plan. Upgrade to Solo or higher.', 'citeleap' ) ];
		}
		if ( ! CiteLeap_Credits::can_consume( 1 ) ) {
			CiteLeap_Log::add( 'refresh_blocked_no_credits', sprintf( 'plan=%s used=%d', CiteLeap_Plan::current(), CiteLeap_Credits::used() ), 'warn' );
			return [ 'ok' => false, 'post_id' => $post_id, 'error' => __( 'Out of credits this cycle. Upgrade your plan or buy a top-up pack.', 'citeleap' ) ];
		}

		$prompts = (array) get_option( CITELEAP_OPTION_PROMPTS, [] );
		$tpl     = (string) ( $prompts['master_prompt'] ?? citeleap_default_master_prompt() );
		$custom  = (string) ( $prompts['custom_prompt'] ?? '' );

		$vars = CiteLeap_Generator::context_vars();
		$vars['topic'] = 'REFRESH AN EXISTING POST. The post slug is "' . $post->post_name . '". Keep the slug. Title may stay or be improved. Update statistics to current May 2026 data. Strengthen any weak section. Replace any retired "2025" year references with "2026" or "last year" where natural. Preserve the post URL and SEO authority. Keep the FAQ count at 5. Original title: ' . $post->post_title . '. Original body (markup, for reference): ' . wp_strip_all_tags( $post->post_content );
		$vars['user_additional'] = $custom;

		/* v2.0 , research + linking + language + layout context. */
		$lang = (string) get_post_meta( (int) $post_id, CITELEAP_META_LANG, true );
		if ( ! $lang ) $lang = CiteLeap_I18n::settings()['default_lang'];
		$vars['language_block']       = CiteLeap_I18n::as_prompt_text( $lang );
		$vars['layout_block']         = CiteLeap_Layout::as_prompt_text();
		$vars['voice_samples_block']  = CiteLeap_Voice::as_prompt_text( 3, (int) $post_id );
		$research_cfg                 = CiteLeap_Research::settings();
		$sources                      = CiteLeap_Research::fetch_sources( $post->post_title );
		$vars['research_block']       = $sources
			? CiteLeap_Research::as_prompt_text( $sources, $research_cfg['min_citations'] )
			: 'RESEARCH: use your built-in web search tool to find at least ' . (int) $research_cfg['min_citations'] . ' real, current online sources for this topic. Cite each one inline. No bare URLs. No invented statistics.';
		$candidates                   = CiteLeap_Linking::candidates( 40, (int) $post_id, $lang );
		$vars['internal_links_block'] = CiteLeap_Linking::as_prompt_text( $candidates, $research_cfg['min_internal'] );

		/* Mark refreshing while in-flight. */
		$queue[ $idx ]['status']       = 'refreshing';
		$queue[ $idx ]['started_at']   = current_time( 'mysql' );
		update_option( CITELEAP_OPTION_QUEUE, $queue, false );

		$user_prompt = CiteLeap_Generator::render_template_public( $tpl, $vars );
		$system      = 'You are a precise content refresher. Return only the JSON object requested. No commentary, no markdown fences.';

		$res = CiteLeap_LLM::chat( 'writing', $system, $user_prompt, 8000 );
		if ( ! $res['ok'] ) {
			$queue = (array) get_option( CITELEAP_OPTION_QUEUE, [] );
			foreach ( $queue as $i => $row ) if ( ( $row['id'] ?? '' ) === $queue_id ) { $queue[ $i ]['status'] = 'failed'; $queue[ $i ]['error'] = $res['error']; }
			update_option( CITELEAP_OPTION_QUEUE, $queue, false );
			CiteLeap_Log::add( 'refresh_failed', '#' . $post_id . ' ' . $res['error'], 'error' );
			return [ 'ok' => false, 'post_id' => $post_id, 'error' => $res['error'] ];
		}

		$data = CiteLeap_Generator::parse_json_object_public( $res['text'] );
		if ( empty( $data ) || empty( $data['body'] ) ) {
			CiteLeap_Log::add( 'refresh_parse_failed', '#' . $post_id . ' ' . mb_substr( $res['text'], 0, 200 ), 'error' );
			return [ 'ok' => false, 'post_id' => $post_id, 'error' => 'Unparsable model response.' ];
		}

		/* v2.1 , defensive em-dash strip on every field. */
		$data['title']            = CiteLeap_Generator::strip_dashes( (string) ( $data['title']            ?? '' ) );
		$data['body']             = CiteLeap_Generator::strip_dashes( (string) ( $data['body']             ?? '' ) );
		$data['excerpt']          = CiteLeap_Generator::strip_dashes( (string) ( $data['excerpt']          ?? '' ) );
		$data['meta_description'] = CiteLeap_Generator::strip_dashes( (string) ( $data['meta_description'] ?? '' ) );

		$new_word_count = str_word_count( wp_strip_all_tags( (string) $data['body'] ) );
		$settings = self::settings();
		$mode     = (string) $settings['auto_mode'];

		if ( 'draft' === $mode ) {
			/* Stash the proposed refresh into post meta. The live post
			 * is NOT touched. Operator reviews + approves from the
			 * Planner row. */
			update_post_meta( $post_id, CITELEAP_META_PENDING, [
				'title'            => sanitize_text_field( (string) ( $data['title'] ?? $post->post_title ) ),
				'content'          => wp_kses_post( (string) $data['body'] ),
				'excerpt'          => sanitize_text_field( (string) ( $data['excerpt'] ?? $post->post_excerpt ) ),
				'meta_description' => sanitize_text_field( (string) ( $data['meta_description'] ?? '' ) ),
				'word_count'       => (int) $new_word_count,
				'provider'         => $res['provider'] . '/' . $res['model'],
				'created_at'       => current_time( 'mysql' ),
			] );
			$queue = (array) get_option( CITELEAP_OPTION_QUEUE, [] );
			foreach ( $queue as $i => $row ) {
				if ( ( $row['id'] ?? '' ) === $queue_id ) {
					$queue[ $i ]['status']      = 'pending_review';
					$queue[ $i ]['finished_at'] = current_time( 'mysql' );
					$queue[ $i ]['word_count']  = (int) $new_word_count;
					break;
				}
			}
			update_option( CITELEAP_OPTION_QUEUE, $queue, false );
			CiteLeap_Credits::consume( 1, 'refresh_pending' );
			CiteLeap_Log::add( 'refresh_pending_review', sprintf( '#%d "%s" awaiting approval (%d words, %s/%s)', $post_id, $post->post_title, $new_word_count, $res['provider'], $res['model'] ) );
			return [ 'ok' => true, 'post_id' => $post_id, 'error' => '' ];
		}

		/* live mode: overwrite immediately. */
		wp_update_post( [
			'ID'           => $post_id,
			'post_title'   => sanitize_text_field( (string) ( $data['title'] ?? $post->post_title ) ),
			'post_content' => wp_kses_post( (string) $data['body'] ),
			'post_excerpt' => sanitize_text_field( (string) ( $data['excerpt'] ?? $post->post_excerpt ) ),
		] );
		if ( ! empty( $data['meta_description'] ) ) {
			$md = sanitize_text_field( (string) $data['meta_description'] );
			update_post_meta( $post_id, '_yoast_wpseo_metadesc', $md );
			update_post_meta( $post_id, '_aioseo_description',   $md );
			update_post_meta( $post_id, 'rank_math_description', $md );
			update_post_meta( $post_id, '_citeleap_meta_description', $md );
		}
		update_post_meta( $post_id, CITELEAP_META_SOURCE, 'refresh' );
		update_post_meta( $post_id, CITELEAP_META_PROVIDER, $res['provider'] . '/' . $res['model'] );
		update_post_meta( $post_id, '_citeleap_word_count', (int) $new_word_count );
		update_post_meta( $post_id, CITELEAP_META_REFRESH_AT, current_time( 'mysql' ) );
		$count = (int) get_post_meta( $post_id, CITELEAP_META_REFRESH_N, true );
		update_post_meta( $post_id, CITELEAP_META_REFRESH_N, $count + 1 );

		/* Mark queue entry complete. */
		$queue = (array) get_option( CITELEAP_OPTION_QUEUE, [] );
		foreach ( $queue as $i => $row ) {
			if ( ( $row['id'] ?? '' ) === $queue_id ) {
				$queue[ $i ]['status']     = 'refreshed';
				$queue[ $i ]['finished_at'] = current_time( 'mysql' );
				$queue[ $i ]['word_count'] = (int) $new_word_count;
				break;
			}
		}
		update_option( CITELEAP_OPTION_QUEUE, $queue, false );

		CiteLeap_Credits::consume( 1, 'refresh_live' );
		CiteLeap_Log::add( 'post_refreshed', sprintf( '#%d "%s" -> %d words (%s/%s)', $post_id, $post->post_title, $new_word_count, $res['provider'], $res['model'] ) );
		return [ 'ok' => true, 'post_id' => $post_id, 'error' => '' ];
	}

	/**
	 * Apply a pending-review refresh to the live post. Clears the
	 * pending-review meta and marks the queue entry 'refreshed'.
	 */
	public static function approve_pending( int $post_id, string $queue_id ): array {
		$pending = (array) get_post_meta( $post_id, CITELEAP_META_PENDING, true );
		if ( empty( $pending ) ) return [ 'ok' => false, 'error' => 'No pending refresh found for this post.' ];

		wp_update_post( [
			'ID'           => $post_id,
			'post_title'   => (string) ( $pending['title'] ?? '' ),
			'post_content' => (string) ( $pending['content'] ?? '' ),
			'post_excerpt' => (string) ( $pending['excerpt'] ?? '' ),
		] );
		if ( ! empty( $pending['meta_description'] ) ) {
			$md = sanitize_text_field( (string) $pending['meta_description'] );
			update_post_meta( $post_id, '_yoast_wpseo_metadesc', $md );
			update_post_meta( $post_id, '_aioseo_description',   $md );
			update_post_meta( $post_id, 'rank_math_description', $md );
			update_post_meta( $post_id, '_citeleap_meta_description', $md );
		}
		update_post_meta( $post_id, CITELEAP_META_SOURCE, 'refresh' );
		update_post_meta( $post_id, CITELEAP_META_PROVIDER, (string) ( $pending['provider'] ?? '' ) );
		update_post_meta( $post_id, '_citeleap_word_count', (int) ( $pending['word_count'] ?? 0 ) );
		update_post_meta( $post_id, CITELEAP_META_REFRESH_AT, current_time( 'mysql' ) );
		$count = (int) get_post_meta( $post_id, CITELEAP_META_REFRESH_N, true );
		update_post_meta( $post_id, CITELEAP_META_REFRESH_N, $count + 1 );
		delete_post_meta( $post_id, CITELEAP_META_PENDING );

		$queue = (array) get_option( CITELEAP_OPTION_QUEUE, [] );
		foreach ( $queue as $i => $row ) {
			if ( ( $row['id'] ?? '' ) === $queue_id ) { $queue[ $i ]['status'] = 'refreshed'; break; }
		}
		update_option( CITELEAP_OPTION_QUEUE, $queue, false );
		CiteLeap_Log::add( 'refresh_approved', '#' . $post_id . ' published refresh approved by operator' );
		return [ 'ok' => true, 'error' => '' ];
	}

	public static function reject_pending( int $post_id, string $queue_id ): array {
		delete_post_meta( $post_id, CITELEAP_META_PENDING );
		$queue = (array) get_option( CITELEAP_OPTION_QUEUE, [] );
		foreach ( $queue as $i => $row ) {
			if ( ( $row['id'] ?? '' ) === $queue_id ) { $queue[ $i ]['status'] = 'failed'; $queue[ $i ]['error'] = 'rejected by operator'; break; }
		}
		update_option( CITELEAP_OPTION_QUEUE, $queue, false );
		CiteLeap_Log::add( 'refresh_rejected', '#' . $post_id . ' refresh rejected by operator', 'info' );
		return [ 'ok' => true, 'error' => '' ];
	}

	/**
	 * Bulk-add posts to the refresh queue by paste-list. Each line
	 * may be a numeric post ID, a slug, or a full permalink URL.
	 * Resolves each to a post ID, dedupes against the existing
	 * refresh queue, and enqueues.
	 *
	 * @return array{added:int, not_found:int, skipped_dupes:int, total_in:int}
	 */
	public static function enqueue_by_paste( string $raw ): array {
		$lines = preg_split( '/\r?\n/', $raw ) ?: [];
		$post_ids = [];
		$not_found = 0;
		foreach ( $lines as $line ) {
			$line = trim( (string) $line );
			$line = trim( preg_replace( '/^[\-\*\d\.\)\s]+/', '', $line ) );
			if ( '' === $line ) continue;
			$pid = self::resolve_to_post_id( $line );
			if ( $pid ) {
				$post_ids[] = $pid;
			} else {
				$not_found++;
			}
		}
		$pre_count = count( (array) get_option( CITELEAP_OPTION_QUEUE, [] ) );
		$added     = self::enqueue_posts( $post_ids );
		$skipped   = max( 0, count( $post_ids ) - $added );
		return [
			'added'         => $added,
			'not_found'     => $not_found,
			'skipped_dupes' => $skipped,
			'total_in'      => count( $lines ),
		];
	}

	private static function resolve_to_post_id( string $input ): int {
		if ( ctype_digit( $input ) ) {
			$pid = (int) $input;
			return ( get_post_type( $pid ) === 'post' ) ? $pid : 0;
		}
		/* Try slug. */
		$post = get_page_by_path( sanitize_title( $input ), OBJECT, 'post' );
		if ( $post ) return (int) $post->ID;
		/* Try URL. */
		if ( false !== filter_var( $input, FILTER_VALIDATE_URL ) ) {
			$pid = url_to_postid( $input );
			if ( $pid && get_post_type( $pid ) === 'post' ) return (int) $pid;
		}
		return 0;
	}

	/**
	 * Unstick a stale 'refreshing' entry (e.g. if a tick crashed
	 * mid-flight). Reverts to 'failed' so the operator can retry.
	 */
	public static function reset_stuck( string $queue_id ): bool {
		$queue = (array) get_option( CITELEAP_OPTION_QUEUE, [] );
		$changed = false;
		foreach ( $queue as $i => $row ) {
			if ( ( $row['id'] ?? '' ) === $queue_id && ( $row['status'] ?? '' ) === 'refreshing' ) {
				$queue[ $i ]['status'] = 'failed';
				$queue[ $i ]['error']  = 'reset by operator';
				$changed = true;
				break;
			}
		}
		if ( $changed ) {
			update_option( CITELEAP_OPTION_QUEUE, $queue, false );
			CiteLeap_Log::add( 'refresh_stuck_reset', $queue_id . ' reset to failed', 'warn' );
		}
		return $changed;
	}

	/**
	 * Posts older than cadence_days that have never been refreshed
	 * or whose last refresh is older than cadence_days. Returns
	 * candidate post IDs for auto-refresh batching.
	 */
	public static function due_post_ids( int $limit = 50 ): array {
		$r = self::settings();
		$cutoff_gmt = gmdate( 'Y-m-d H:i:s', time() - ( (int) $r['cadence_days'] * DAY_IN_SECONDS ) );
		return get_posts( [
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'orderby'        => 'modified',
			'order'          => 'ASC',
			'date_query'     => [ [ 'column' => 'post_modified_gmt', 'before' => $cutoff_gmt ] ],
		] );
	}
}

/* admin-post handlers for the refresh UI inside the Planner tab */
add_action( 'admin_post_citeleap_enqueue_refresh', function () {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );
	check_admin_referer( CITELEAP_NONCE );
	$ids = isset( $_POST['post_ids'] ) ? array_map( 'intval', (array) wp_unslash( $_POST['post_ids'] ) ) : [];
	$n   = CiteLeap_Refresh::enqueue_posts( $ids );
	wp_safe_redirect( add_query_arg( [ 'page' => 'citeleap', 'tab' => 'planner', 'citeleap_msg' => 'refresh-queued:' . $n ], admin_url( 'admin.php' ) ) );
	exit;
} );

add_action( 'admin_post_citeleap_run_refresh', function () {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );
	check_admin_referer( CITELEAP_NONCE );
	$queue_id = sanitize_text_field( wp_unslash( (string) ( $_POST['queue_id'] ?? '' ) ) );
	$res      = CiteLeap_Refresh::refresh_from_queue( $queue_id );
	wp_safe_redirect( add_query_arg( [ 'page' => 'citeleap', 'tab' => 'planner', 'citeleap_msg' => $res['ok'] ? 'refresh-ok' : 'refresh-err' ], admin_url( 'admin.php' ) ) );
	exit;
} );

add_action( 'admin_post_citeleap_save_refresh', function () {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );
	check_admin_referer( CITELEAP_NONCE );
	CiteLeap_Refresh::save_settings( [
		'auto_mode'      => $_POST['refresh_auto_mode']      ?? 'off',
		'cadence_days'   => $_POST['refresh_cadence_days']   ?? 90,
		'posts_per_week' => $_POST['refresh_posts_per_week'] ?? 2,
	] );
	wp_safe_redirect( add_query_arg( [ 'page' => 'citeleap', 'tab' => 'settings', 'citeleap_msg' => 'saved' ], admin_url( 'admin.php' ) ) );
	exit;
} );

add_action( 'admin_post_citeleap_bulk_refresh', function () {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );
	check_admin_referer( CITELEAP_NONCE );
	$raw = (string) wp_unslash( (string) ( $_POST['paste'] ?? '' ) );
	$res = CiteLeap_Refresh::enqueue_by_paste( $raw );
	$msg = sprintf( 'bulk-refresh:%d:%d:%d:%d', $res['added'], $res['skipped_dupes'], $res['not_found'], $res['total_in'] );
	wp_safe_redirect( add_query_arg( [ 'page' => 'citeleap', 'tab' => 'planner', 'citeleap_msg' => $msg ], admin_url( 'admin.php' ) ) );
	exit;
} );

add_action( 'admin_post_citeleap_approve_refresh', function () {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );
	check_admin_referer( CITELEAP_NONCE );
	$qid = sanitize_text_field( wp_unslash( (string) ( $_POST['queue_id'] ?? '' ) ) );
	$pid = (int) ( $_POST['post_id'] ?? 0 );
	$res = CiteLeap_Refresh::approve_pending( $pid, $qid );
	$msg = $res['ok'] ? 'refresh-approved:' . $pid : 'refresh-err';
	wp_safe_redirect( add_query_arg( [ 'page' => 'citeleap', 'tab' => 'planner', 'citeleap_msg' => $msg ], admin_url( 'admin.php' ) ) );
	exit;
} );

add_action( 'admin_post_citeleap_reject_refresh', function () {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );
	check_admin_referer( CITELEAP_NONCE );
	$qid = sanitize_text_field( wp_unslash( (string) ( $_POST['queue_id'] ?? '' ) ) );
	$pid = (int) ( $_POST['post_id'] ?? 0 );
	CiteLeap_Refresh::reject_pending( $pid, $qid );
	wp_safe_redirect( add_query_arg( [ 'page' => 'citeleap', 'tab' => 'planner', 'citeleap_msg' => 'refresh-rejected' ], admin_url( 'admin.php' ) ) );
	exit;
} );

add_action( 'admin_post_citeleap_reset_stuck', function () {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );
	check_admin_referer( CITELEAP_NONCE );
	$qid = sanitize_text_field( wp_unslash( (string) ( $_POST['queue_id'] ?? '' ) ) );
	CiteLeap_Refresh::reset_stuck( $qid );
	wp_safe_redirect( add_query_arg( [ 'page' => 'citeleap', 'tab' => 'planner', 'citeleap_msg' => 'reset' ], admin_url( 'admin.php' ) ) );
	exit;
} );
