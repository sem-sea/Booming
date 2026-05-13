<?php
/**
 * CiteLeap , idea generator + writer.
 *
 * @package CiteLeap
 */

defined( 'ABSPATH' ) || exit;

class CiteLeap_Generator {

	public static function context_vars(): array {
		$schedule = (array) get_option( CITELEAP_OPTION_SCHEDULE, [] );
		$cats     = get_terms( [ 'taxonomy' => 'category', 'hide_empty' => false, 'fields' => 'names' ] );
		if ( is_wp_error( $cats ) ) $cats = [];
		return [
			'site_name'        => (string) get_bloginfo( 'name' ),
			'site_description' => (string) get_bloginfo( 'description' ),
			'audience'         => (string) ( $schedule['audience'] ?? 'B2B decision-makers' ),
			'category_list'    => implode( ', ', array_slice( (array) $cats, 0, 20 ) ),
			'internal_links'   => (string) ( $schedule['internal_links'] ?? '/services/, /#contact, /blog/' ),
			'seed_topics'      => (string) ( $schedule['topics'] ?? '' ),
		];
	}

	private static function existing_slugs(): array {
		$slugs = get_posts( [
			'post_type'      => 'post',
			'post_status'    => [ 'publish', 'future', 'draft', 'pending' ],
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		] );
		$out = [];
		foreach ( $slugs as $id ) $out[] = get_post_field( 'post_name', (int) $id );
		return array_values( array_filter( $out ) );
	}

	private static function render_template( string $template, array $vars ): string {
		foreach ( $vars as $k => $v ) {
			$template = str_replace( '{' . $k . '}', (string) $v, $template );
		}
		return $template;
	}

	/**
	 * Ask the reasoning model for $count fresh ideas. Stores them in
	 * the queue option and returns the parsed array.
	 *
	 * @return array{ok:bool, ideas:array<int,array>, error:string}
	 */
	public static function generate_ideas( int $count = 10 ): array {
		$prompts = (array) get_option( CITELEAP_OPTION_PROMPTS, [] );
		$tpl     = (string) ( $prompts['idea_prompt'] ?? citeleap_idea_prompt_template() );
		$custom  = (string) ( $prompts['custom_prompt'] ?? '' );

		$vars = self::context_vars();
		$vars['count']           = (int) $count;
		$vars['existing_slugs']  = implode( ', ', array_slice( self::existing_slugs(), 0, 200 ) );
		$vars['user_additional'] = $custom;

		$user_prompt = self::render_template( $tpl, $vars );
		$system      = 'You are a precise B2B content strategist. Return only the JSON array requested. No commentary, no markdown fences.';

		$res = CiteLeap_LLM::chat( 'reasoning', $system, $user_prompt, 4000 );
		if ( ! $res['ok'] ) {
			CiteLeap_Log::add( 'idea_generation_failed', $res['error'] );
			return [ 'ok' => false, 'ideas' => [], 'error' => $res['error'] ];
		}

		$ideas = self::parse_json_array( $res['text'] );
		if ( empty( $ideas ) ) {
			CiteLeap_Log::add( 'idea_parse_failed', mb_substr( $res['text'], 0, 200 ) );
			return [ 'ok' => false, 'ideas' => [], 'error' => 'Model returned unparsable JSON.' ];
		}

		/* Merge into queue, dedupe by slug. */
		$queue = (array) get_option( CITELEAP_OPTION_QUEUE, [] );
		$seen  = array_flip( array_column( $queue, 'slug' ) );
		foreach ( $ideas as $idea ) {
			$slug = sanitize_title( (string) ( $idea['slug'] ?? '' ) );
			if ( ! $slug || isset( $seen[ $slug ] ) ) continue;
			$queue[] = [
				'id'              => wp_generate_uuid4(),
				'slug'            => $slug,
				'title'           => sanitize_text_field( (string) ( $idea['title'] ?? '' ) ),
				'primary_keyword' => sanitize_text_field( (string) ( $idea['primary_keyword'] ?? '' ) ),
				'category_name'   => sanitize_text_field( (string) ( $idea['category_name'] ?? '' ) ),
				'angle'           => sanitize_textarea_field( (string) ( $idea['angle'] ?? '' ) ),
				'priority'        => (int) ( $idea['priority'] ?? 5 ),
				'status'          => 'queued',
				'created_at'      => current_time( 'mysql' ),
				'provider'        => $res['provider'],
				'model'           => $res['model'],
			];
			$seen[ $slug ] = true;
		}
		update_option( CITELEAP_OPTION_QUEUE, $queue, false );

		CiteLeap_Log::add( 'ideas_generated', sprintf( '%d new ideas from %s/%s', count( $ideas ), $res['provider'], $res['model'] ) );
		return [ 'ok' => true, 'ideas' => $ideas, 'error' => '' ];
	}

	/**
	 * Draft a single post from a queue entry. Inserts as draft and
	 * returns the new post ID.
	 */
	public static function write_post_from_idea( string $idea_id ): array {
		$queue = (array) get_option( CITELEAP_OPTION_QUEUE, [] );
		$idx   = self::find_idea_index( $queue, $idea_id );
		if ( -1 === $idx ) {
			return [ 'ok' => false, 'post_id' => 0, 'error' => 'Idea not found in queue.' ];
		}
		$idea = $queue[ $idx ];

		$prompts = (array) get_option( CITELEAP_OPTION_PROMPTS, [] );
		$tpl     = (string) ( $prompts['master_prompt'] ?? citeleap_default_master_prompt() );
		$custom  = (string) ( $prompts['custom_prompt'] ?? '' );

		$vars = self::context_vars();
		$vars['topic']           = $idea['title'] . ' , ' . $idea['angle'];
		$vars['user_additional'] = $custom;

		$user_prompt = self::render_template( $tpl, $vars );
		$system      = 'You are a precise long-form content writer. Return only the JSON object requested. No commentary, no markdown fences.';

		$res = CiteLeap_LLM::chat( 'writing', $system, $user_prompt, 8000 );
		if ( ! $res['ok'] ) {
			CiteLeap_Log::add( 'write_failed', $res['error'] );
			return [ 'ok' => false, 'post_id' => 0, 'error' => $res['error'] ];
		}

		$post_data = self::parse_json_object( $res['text'] );
		if ( empty( $post_data ) || empty( $post_data['title'] ) || empty( $post_data['body'] ) ) {
			CiteLeap_Log::add( 'write_parse_failed', mb_substr( $res['text'], 0, 200 ) );
			return [ 'ok' => false, 'post_id' => 0, 'error' => 'Model returned unparsable JSON.' ];
		}

		$word_count = str_word_count( wp_strip_all_tags( (string) $post_data['body'] ) );
		if ( $word_count < 1000 ) {
			CiteLeap_Log::add( 'write_too_short', sprintf( '%d words, below 1000 threshold', $word_count ) );
		}

		$post_id = wp_insert_post( [
			'post_title'     => sanitize_text_field( (string) $post_data['title'] ),
			'post_name'      => sanitize_title( (string) ( $post_data['slug'] ?? $idea['slug'] ) ),
			'post_content'   => wp_kses_post( (string) $post_data['body'] ),
			'post_excerpt'   => sanitize_text_field( (string) ( $post_data['excerpt'] ?? '' ) ),
			'post_status'    => 'draft',
			'post_type'      => 'post',
			'post_author'    => get_current_user_id() ?: 1,
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
		], true );

		if ( is_wp_error( $post_id ) ) {
			return [ 'ok' => false, 'post_id' => 0, 'error' => $post_id->get_error_message() ];
		}

		/* Meta description via WP excerpt + dedicated meta key (works
		 * with Yoast / Rank Math / our SEO Boost plugin). */
		if ( ! empty( $post_data['meta_description'] ) ) {
			update_post_meta( (int) $post_id, '_yoast_wpseo_metadesc', sanitize_text_field( (string) $post_data['meta_description'] ) );
			update_post_meta( (int) $post_id, '_aioseo_description', sanitize_text_field( (string) $post_data['meta_description'] ) );
			update_post_meta( (int) $post_id, 'rank_math_description', sanitize_text_field( (string) $post_data['meta_description'] ) );
			update_post_meta( (int) $post_id, '_citeleap_meta_description', sanitize_text_field( (string) $post_data['meta_description'] ) );
		}
		if ( ! empty( $post_data['primary_keyword'] ) ) {
			update_post_meta( (int) $post_id, '_citeleap_primary_keyword', sanitize_text_field( (string) $post_data['primary_keyword'] ) );
		}

		update_post_meta( (int) $post_id, CITELEAP_META_SOURCE, 'auto' );
		update_post_meta( (int) $post_id, CITELEAP_META_IDEA, $idea_id );
		update_post_meta( (int) $post_id, CITELEAP_META_PROVIDER, $res['provider'] . '/' . $res['model'] );
		update_post_meta( (int) $post_id, '_citeleap_word_count', (int) $word_count );

		/* Category assignment. */
		$cat_name = (string) ( $post_data['category_name'] ?? $idea['category_name'] ?? '' );
		if ( $cat_name ) {
			$term = get_term_by( 'name', $cat_name, 'category' );
			if ( ! $term ) {
				$inserted = wp_insert_term( $cat_name, 'category' );
				if ( ! is_wp_error( $inserted ) ) $term = get_term( (int) $inserted['term_id'], 'category' );
			}
			if ( $term && ! is_wp_error( $term ) ) wp_set_post_categories( (int) $post_id, [ (int) $term->term_id ] );
		}

		/* Mark idea as drafted in queue. */
		$queue[ $idx ]['status']      = 'drafted';
		$queue[ $idx ]['post_id']     = (int) $post_id;
		$queue[ $idx ]['drafted_at']  = current_time( 'mysql' );
		update_option( CITELEAP_OPTION_QUEUE, $queue, false );

		CiteLeap_Log::add( 'post_drafted', sprintf( '#%d "%s" (%d words, %s/%s)', $post_id, $post_data['title'], $word_count, $res['provider'], $res['model'] ) );
		return [ 'ok' => true, 'post_id' => (int) $post_id, 'error' => '' ];
	}

	private static function find_idea_index( array $queue, string $id ): int {
		foreach ( $queue as $i => $row ) {
			if ( ( $row['id'] ?? '' ) === $id ) return $i;
		}
		return -1;
	}

	private static function parse_json_array( string $text ): array {
		$text = self::strip_code_fences( $text );
		$decoded = json_decode( $text, true );
		if ( is_array( $decoded ) ) return $decoded;
		/* Attempt to extract the first JSON array. */
		if ( preg_match( '/\[\s*\{.*\}\s*\]/s', $text, $m ) ) {
			$try = json_decode( $m[0], true );
			if ( is_array( $try ) ) return $try;
		}
		return [];
	}

	private static function parse_json_object( string $text ): array {
		$text = self::strip_code_fences( $text );
		$decoded = json_decode( $text, true );
		if ( is_array( $decoded ) ) return $decoded;
		if ( preg_match( '/\{.*\}/s', $text, $m ) ) {
			$try = json_decode( $m[0], true );
			if ( is_array( $try ) ) return $try;
		}
		return [];
	}

	private static function strip_code_fences( string $text ): string {
		$text = preg_replace( '/^```(?:json)?\s*\n/m', '', $text );
		$text = preg_replace( '/\n```\s*$/m', '', $text );
		return trim( $text );
	}
}

class CiteLeap_Log {
	public static function add( string $event, string $detail = '' ): void {
		$log   = (array) get_option( CITELEAP_OPTION_LOG, [] );
		$log[] = [
			'time'   => current_time( 'mysql' ),
			'event'  => $event,
			'detail' => $detail,
		];
		if ( count( $log ) > 200 ) $log = array_slice( $log, -200 );
		update_option( CITELEAP_OPTION_LOG, $log, false );
	}
}
