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
		$r = (array) get_option( CITELEAP_OPTION_REFRESH, [] );
		return [
			'auto'              => ! empty( $r['auto'] ) ? 1 : 0,
			'cadence_days'      => max( 7, (int) ( $r['cadence_days']      ?? 90 ) ),  // refresh every X days
			'posts_per_week'    => max( 1, (int) ( $r['posts_per_week']    ?? 2 ) ),
		];
	}

	public static function save_settings( array $args ): void {
		update_option( CITELEAP_OPTION_REFRESH, [
			'auto'           => ! empty( $args['auto'] ) ? 1 : 0,
			'cadence_days'   => max( 7,  min( 365, (int) ( $args['cadence_days']   ?? 90 ) ) ),
			'posts_per_week' => max( 1,  min( 14,  (int) ( $args['posts_per_week'] ?? 2  ) ) ),
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

		$prompts = (array) get_option( CITELEAP_OPTION_PROMPTS, [] );
		$tpl     = (string) ( $prompts['master_prompt'] ?? citeleap_default_master_prompt() );
		$custom  = (string) ( $prompts['custom_prompt'] ?? '' );

		$vars = CiteLeap_Generator::context_vars();
		$vars['topic'] = 'REFRESH AN EXISTING POST. The post slug is "' . $post->post_name . '". Keep the slug. Title may stay or be improved. Update statistics to current May 2026 data. Strengthen any weak section. Replace any retired "2025" year references with "2026" or "last year" where natural. Preserve the post URL and SEO authority. Keep the FAQ count at 5. Original title: ' . $post->post_title . '. Original body (markup, for reference): ' . wp_strip_all_tags( $post->post_content );
		$vars['user_additional'] = $custom;

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

		$new_word_count = str_word_count( wp_strip_all_tags( (string) $data['body'] ) );

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

		CiteLeap_Log::add( 'post_refreshed', sprintf( '#%d "%s" -> %d words (%s/%s)', $post_id, $post->post_title, $new_word_count, $res['provider'], $res['model'] ) );
		return [ 'ok' => true, 'post_id' => $post_id, 'error' => '' ];
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
		'auto'           => $_POST['refresh_auto']           ?? 0,
		'cadence_days'   => $_POST['refresh_cadence_days']   ?? 90,
		'posts_per_week' => $_POST['refresh_posts_per_week'] ?? 2,
	] );
	wp_safe_redirect( add_query_arg( [ 'page' => 'citeleap', 'tab' => 'settings', 'citeleap_msg' => 'saved' ], admin_url( 'admin.php' ) ) );
	exit;
} );
