<?php
/**
 * CiteLeap , linking.php
 *
 * Builds the "internal-link candidates" block that goes into the
 * writing prompt. The writer LLM receives a list of the site's
 * already-published posts (title, slug, short excerpt) with the
 * instruction to weave at least N natural internal links into the
 * generated body.
 *
 * Per the 2026 research (LinkBoss / Surfer "Insert Internal Links"
 * / WPLink.AI), semantic similarity in the 0.60-0.80 range is the
 * sweet spot. v2.0 ships the simpler "all posts as candidates" mode
 * (the LLM picks). v2.1 will add embedding-based pre-filtering.
 *
 * @package CiteLeap
 */

defined( 'ABSPATH' ) || exit;

class CiteLeap_Linking {

	/** Return up to N existing posts (excluding the current draft if any),
	 *  most-recently-modified first. */
	public static function candidates( int $limit = 40, int $exclude_post = 0, string $lang = '' ): array {
		$args = [
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => max( 1, min( 200, $limit ) ),
			'orderby'        => 'modified',
			'order'          => 'DESC',
			'no_found_rows'  => true,
			'fields'         => 'all',
		];
		if ( $exclude_post ) $args['post__not_in'] = [ $exclude_post ];

		/* Language filter: if Polylang or WPML is active and a lang was
		 * passed, narrow to same-language posts so we never produce a
		 * Dutch article linking to an English post. */
		if ( $lang ) {
			if ( function_exists( 'pll_get_post_translations' ) ) {
				$args['lang'] = $lang;
			} elseif ( function_exists( 'icl_object_id' ) ) {
				/* WPML: filter via taxonomy join; cheaper to fetch then post-filter. */
				$args['suppress_filters'] = false;
			}
		}

		$posts = get_posts( $args );
		$out = [];
		foreach ( $posts as $p ) {
			$excerpt = get_the_excerpt( $p );
			if ( ! $excerpt ) {
				$excerpt = wp_strip_all_tags( strip_shortcodes( (string) $p->post_content ) );
			}
			$out[] = [
				'id'      => (int) $p->ID,
				'title'   => (string) $p->post_title,
				'slug'    => (string) $p->post_name,
				'url'     => (string) get_permalink( $p ),
				'excerpt' => mb_substr( trim( preg_replace( '/\s+/', ' ', (string) $excerpt ) ), 0, 160 ),
			];
		}
		return $out;
	}

	/** Render the candidate list as a prompt block ready to inject. */
	public static function as_prompt_text( array $candidates, int $min_links = 2, int $max_show = 25 ): string {
		if ( empty( $candidates ) ) return '';
		$lines = [];
		$lines[] = sprintf(
			'You MUST include at least %d natural internal links to the operator\'s existing posts below. Use anchor text that matches the linked post\'s topic, NOT keyword stuffed. Render the links as proper <a href="…">…</a> in the block markup. Only link to posts whose topic genuinely relates to the passage you place the link in. Do not stack two links in one sentence.',
			$min_links
		);
		$lines[] = '';
		$lines[] = 'Existing posts on this site (URL → topic):';
		$shown = array_slice( $candidates, 0, $max_show );
		foreach ( $shown as $c ) {
			$lines[] = sprintf( '- %s → %s (%s)', $c['url'], $c['title'], $c['excerpt'] );
		}
		return implode( "\n", $lines );
	}

	/** Extract internal-link slugs that ended up in a generated body. */
	public static function extract_used_slugs( string $body, string $home_host = '' ): array {
		if ( '' === $home_host ) {
			$home = wp_parse_url( (string) home_url( '/' ), PHP_URL_HOST );
			$home_host = is_string( $home ) ? $home : '';
		}
		$slugs = [];
		if ( preg_match_all( '/<a\b[^>]*href=["\']([^"\']+)["\'][^>]*>/i', $body, $m ) ) {
			foreach ( $m[1] as $href ) {
				$host = wp_parse_url( $href, PHP_URL_HOST );
				if ( $host && $home_host && $host !== $home_host ) continue;
				$path = (string) wp_parse_url( $href, PHP_URL_PATH );
				$path = trim( $path, '/' );
				if ( '' === $path ) continue;
				$parts = explode( '/', $path );
				$slugs[] = end( $parts );
			}
		}
		return array_values( array_unique( $slugs ) );
	}
}
