<?php
/**
 * SEO fixes from the October 2026 crawl (Screaming Frog, 511 URLs).
 *
 *  1. Front end in English: the content is English but the site language
 *     is Dutch, so <html lang> said nl-NL and archives read "Archieven".
 *  2. Titles and meta descriptions from post meta, with or without Yoast.
 *  3. Redirects for URLs that changed: /blog/{slug}/, old Query Loop
 *     pagination (?query-1-page=N, ?cst) and merged categories.
 *  4. Archive titles without the "Category:" prefix.
 *  5. Richer Organization schema when Yoast builds the graph.
 *
 * @package BoomingVenture
 */

defined( 'ABSPATH' ) || exit;

/* ------------------------------------------------------------------
 * 1. Front-end locale. Admin, REST, AJAX, cron and CLI keep the site
 *    language. Filter `bv_frontend_locale` to change or return '' to
 *    switch this off.
 * ------------------------------------------------------------------ */
function bv_is_frontend_request(): bool {
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) return false;
	if ( defined( 'WP_CLI' ) && WP_CLI ) return false;
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) return false;
	$uri = $_SERVER['REQUEST_URI'] ?? '';
	if ( str_contains( $uri, '/wp-json/' ) || str_contains( $uri, 'rest_route=' ) || str_contains( $uri, 'wp-login.php' ) ) return false;
	return true;
}

add_filter( 'locale', static function ( $locale ) {
	if ( ! bv_is_frontend_request() ) return $locale;
	$front = apply_filters( 'bv_frontend_locale', 'en_GB' );
	return $front ?: $locale;
}, 20 );

/* ------------------------------------------------------------------
 * 2. Titles and meta descriptions.
 * ------------------------------------------------------------------ */
function bv_current_seo_post_id(): int {
	if ( is_front_page() && 'page' === get_option( 'show_on_front' ) ) return (int) get_option( 'page_on_front' );
	if ( is_home() ) return (int) get_option( 'page_for_posts' );
	if ( is_singular() ) return (int) get_queried_object_id();
	return 0;
}

function bv_meta_description(): string {
	$id = bv_current_seo_post_id();
	if ( $id ) {
		$d = (string) get_post_meta( $id, '_bv_meta_description', true );
		if ( '' === $d ) $d = (string) get_post_meta( $id, '_yoast_wpseo_metadesc', true );
		if ( '' === $d && is_singular() ) $d = wp_trim_words( wp_strip_all_tags( get_the_excerpt( $id ) ), 28, '...' );
		return trim( $d );
	}
	if ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();
		if ( $term && ! empty( $term->description ) ) return wp_strip_all_tags( $term->description );
	}
	return (string) get_bloginfo( 'description' );
}

/* Without an SEO plugin: use the stored SEO title. */
add_filter( 'pre_get_document_title', static function ( $title ) {
	if ( bv_seo_active_plugin_handles_meta() ) return $title;
	$id = bv_current_seo_post_id();
	if ( $id && ! is_paged() ) {
		$t = (string) get_post_meta( $id, '_bv_seo_title', true );
		if ( $t ) return $t;
	}
	return $title;
}, 20 );

add_filter( 'document_title_separator', static fn() => '|' );
add_filter( 'get_the_archive_title_prefix', '__return_empty_string' );

/* With Yoast: fall back to our description where Yoast has none. */
add_filter( 'wpseo_metadesc', static fn( $d ) => $d ?: bv_meta_description() );
add_filter( 'wpseo_opengraph_desc', static fn( $d ) => $d ?: bv_meta_description() );
add_filter( 'wpseo_twitter_description', static fn( $d ) => $d ?: bv_meta_description() );

/* Yoast Organization piece: add the facts the theme graph carries. */
add_filter( 'wpseo_schema_organization', static function ( $data ) {
	if ( ! is_array( $data ) ) return $data;
	$data['name']    = $data['name'] ?? 'Booming Venture';
	$data['email']   = $data['email'] ?? 'info@boomingventure.com';
	$data['vatID']   = 'NL005211648B76';
	$data['address'] = [
		'@type'           => 'PostalAddress',
		'streetAddress'   => 'Breedveldsingel 1',
		'postalCode'      => '3055 PG',
		'addressLocality' => 'Rotterdam',
		'addressCountry'  => 'NL',
	];
	$data['founder'] = [ '@id' => home_url( '/' ) . '#person-ben-verschuur' ];
	$same            = (array) ( $data['sameAs'] ?? [] );
	$same[]          = 'https://www.linkedin.com/company/booming-venture/';
	$data['sameAs']  = array_values( array_unique( $same ) );
	return $data;
} );

/* Yoast author piece: the WordPress user is a team account, the real
 * author is the founder. Same person as the byline and Organization
 * founder, so search engines and AI see one consistent entity. */
function bv_author_person( $data ) {
	if ( ! is_array( $data ) ) return $data;
	$types = (array) ( $data['@type'] ?? [] );
	if ( ! in_array( 'Person', $types, true ) ) return $data;
	if ( isset( $data['@id'] ) && str_contains( (string) $data['@id'], '#/schema/person/' ) ) {
		$data['name']     = 'Ben Verschuur';
		$data['url']      = home_url( '/about/' );
		$data['jobTitle'] = 'Founder';
		$data['worksFor'] = [ '@id' => home_url( '/#organization' ) ];
		$data['sameAs']   = [ 'https://www.linkedin.com/in/benverschuur' ];
		unset( $data['description'] );
		if ( isset( $data['image']['caption'] ) ) $data['image']['caption'] = 'Ben Verschuur';
	}
	return $data;
}
add_filter( 'wpseo_schema_person', 'bv_author_person', 20 );
add_filter( 'wpseo_schema_author', 'bv_author_person', 20 );

/* "Written by" in Slack/LinkedIn previews. */
add_filter( 'wpseo_enhanced_slack_data', static function ( $data ) {
	if ( is_array( $data ) ) {
		foreach ( $data as $label => $value ) {
			if ( false !== stripos( (string) $label, 'written' ) || false !== stripos( (string) $label, 'geschreven' ) ) $data[ $label ] = 'Ben Verschuur';
		}
	}
	return $data;
} );

/* ------------------------------------------------------------------
 * 3. Redirects. Run before WordPress decides on a 404.
 * ------------------------------------------------------------------ */
add_action( 'template_redirect', static function () {
	$uri   = $_SERVER['REQUEST_URI'] ?? '';
	$path  = trim( (string) wp_parse_url( $uri, PHP_URL_PATH ), '/' );
	$query = (string) wp_parse_url( $uri, PHP_URL_QUERY );
	parse_str( $query, $q );

	/* Articles merged into a stronger one on the same topic (1.10.0). */
	if ( $path && ! str_contains( $path, '/' ) ) {
		$merged = (array) get_option( 'bv_post_redirects', [] );
		if ( isset( $merged[ $path ] ) ) {
			wp_safe_redirect( home_url( '/' . $merged[ $path ] . '/' ), 301 );
			exit;
		}
	}

	/* Old Query Loop pagination on the blog: ?query-1-page=N and ?cst. */
	if ( isset( $q['query-1-page'] ) || array_key_exists( 'cst', $q ) ) {
		$n    = isset( $q['query-1-page'] ) ? max( 1, (int) $q['query-1-page'] ) : 1;
		$base = trailingslashit( home_url( $path ? '/' . $path . '/' : '/' ) );
		$base = preg_replace( '#/page/\d+/$#', '/', $base );
		unset( $q['query-1-page'], $q['cst'] );
		$dest = $n > 1 ? $base . 'page/' . $n . '/' : $base;
		if ( $q ) $dest = add_query_arg( $q, $dest );
		wp_safe_redirect( $dest, 301 );
		exit;
	}

	/* /blog/{post-slug}/ -> /{post-slug}/ */
	if ( preg_match( '#^blog/([a-z0-9\-]+)$#', $path, $m ) && 'page' !== $m[1] ) {
		$post = get_page_by_path( $m[1], OBJECT, 'post' );
		if ( $post && 'publish' === $post->post_status ) {
			wp_safe_redirect( get_permalink( $post ), 301 );
			exit;
		}
	}

	/* Merged categories. */
	if ( str_starts_with( $path, 'category/' ) ) {
		$map = (array) get_option( 'bv_category_redirects', [] );
		if ( preg_match( '#^(category/[a-z0-9\-]+)(?:/page/\d+)?$#', $path, $m ) && isset( $map[ $m[1] ] ) ) {
			wp_safe_redirect( home_url( '/' . $map[ $m[1] ] . '/' ), 301 );
			exit;
		}
	}

	/* Single-segment 404 that matches a published slug of another type.
	 * Never redirect to the URL that was requested (stale rewrite rules
	 * would otherwise loop); flush the rules instead so the next request
	 * resolves. */
	if ( is_404() && $path && ! str_contains( $path, '/' ) ) {
		foreach ( [ 'post', 'page' ] as $pt ) {
			$p = get_page_by_path( sanitize_title( $path ), OBJECT, $pt );
			if ( $p && 'publish' === $p->post_status ) {
				$dest = get_permalink( $p );
				if ( untrailingslashit( (string) wp_parse_url( $dest, PHP_URL_PATH ) ) === '/' . $path ) {
					if ( ! get_transient( 'bv_rules_flushed' ) ) {
						set_transient( 'bv_rules_flushed', 1, 300 );
						flush_rewrite_rules( false );
					}
					return;
				}
				wp_safe_redirect( $dest, 301 );
				exit;
			}
		}
	}
}, 0 );
