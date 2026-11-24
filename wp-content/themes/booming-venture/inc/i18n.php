<?php
/**
 * Bilingual site (1.11.0): English at the root, Dutch under /nl/.
 *
 * Follows Google's guidance for multilingual sites: one URL per language,
 * a subdirectory per language, hreflang annotations that point both ways
 * with a self reference and x-default, no automatic redirects by IP or
 * browser language, and fully translated pages including navigation.
 *
 *  - Language of a request comes from the URL path (/nl or /nl/...).
 *  - Dutch content carries post meta _bv_lang = nl. Dutch pages are
 *    children of the page "nl" (/nl/ is the Dutch home), Dutch posts live
 *    at /nl/blog/{slug}/.
 *  - Translation pairs: post meta _bv_translation holds the ID of the
 *    counterpart, on both sides.
 *  - Locale nl_NL on Dutch URLs, so <html lang>, dates, Yoast og:locale
 *    and schema inLanguage all follow automatically.
 *  - Header and footer parts switch to header-nl / footer-nl.
 *  - English loops never show Dutch posts and the other way round.
 *
 * @package BoomingVenture
 */

defined( 'ABSPATH' ) || exit;

/** Language of the current request: 'nl' for /nl and /nl/..., else 'en'. */
function bv_lang(): string {
	static $lang = null;
	if ( null === $lang ) {
		$path = (string) parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH );
		$lang = preg_match( '#^/nl(/|$)#', $path ) ? 'nl' : 'en';
	}
	return $lang;
}

/** Language of a post or page. */
function bv_post_lang( $post = null ): string {
	$post = get_post( $post );
	return ( $post && 'nl' === get_post_meta( $post->ID, '_bv_lang', true ) ) ? 'nl' : 'en';
}

/* Meta, editable through the REST API by editors. */
add_action( 'init', static function () {
	foreach ( [ 'post', 'page' ] as $type ) {
		register_post_meta( $type, '_bv_lang', [
			'type'          => 'string',
			'single'        => true,
			'show_in_rest'  => true,
			'auth_callback' => static fn() => current_user_can( 'edit_posts' ),
		] );
		register_post_meta( $type, '_bv_translation', [
			'type'          => 'integer',
			'single'        => true,
			'show_in_rest'  => true,
			'auth_callback' => static fn() => current_user_can( 'edit_posts' ),
		] );
	}
} );

/* Locale: nl_NL on Dutch URLs (inc/rebuild.php applies it to the front end only). */
add_filter( 'bv_frontend_locale', static fn( $locale ) => 'nl' === bv_lang() ? 'nl_NL' : $locale );

/* Dutch date order on Dutch pages (the site setting is "F j, Y"). */
add_filter( 'option_date_format', static fn( $format ) => ( 'nl' === bv_lang() && ! is_admin() ) ? 'j F Y' : $format );

/* Dutch patterns, generated from the English pattern files. */
require_once __DIR__ . '/patterns-nl.php';

/* ------------------------------------------------------------------
 * URLs
 * ------------------------------------------------------------------ */
add_filter( 'post_link', static function ( $url, $post ) {
	if ( 'post' !== $post->post_type || 'nl' !== get_post_meta( $post->ID, '_bv_lang', true ) ) {
		return $url;
	}
	if ( '' === $post->post_name || in_array( $post->post_status, [ 'draft', 'pending', 'auto-draft' ], true ) ) {
		return $url;
	}
	return home_url( '/nl/blog/' . $post->post_name . '/' );
}, 10, 2 );

add_action( 'init', static function () {
	add_rewrite_rule( '^nl/blog/([^/]+)/?$', 'index.php?name=$matches[1]', 'top' );
}, 5 );

/* Flush rewrite rules once per theme version so /nl/blog/{slug}/ resolves. */
add_action( 'init', static function () {
	if ( get_option( 'bv_i18n_rules' ) !== BV_THEME_VERSION ) {
		flush_rewrite_rules( false );
		update_option( 'bv_i18n_rules', BV_THEME_VERSION, false );
	}
}, 99 );

/* An English post asked for under /nl/blog/ goes to its own URL. Dutch
 * posts asked for at /{slug}/ are sent to /nl/blog/{slug}/ by
 * redirect_canonical, because post_link returns that URL. */
add_action( 'template_redirect', static function () {
	if ( 'nl' === bv_lang() && is_singular( 'post' ) && 'nl' !== bv_post_lang( get_queried_object_id() ) ) {
		wp_safe_redirect( get_permalink( get_queried_object_id() ), 301 );
		exit;
	}
}, 1 );

/* A Dutch legal URL without a Dutch page yet falls back to the English
 * page with a temporary redirect, so footer links never end in a 404. */
add_action( 'template_redirect', static function () {
	if ( 'nl' !== bv_lang() || ! is_404() ) return;
	$path = trim( (string) parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );
	$map  = [
		'nl/privacybeleid'         => '/privacy-policy/',
		'nl/cookiebeleid'          => '/cookie-policy/',
		'nl/algemene-voorwaarden'  => '/terms-of-service/',
		'nl/disclaimer'            => '/disclaimer/',
	];
	if ( isset( $map[ $path ] ) ) {
		wp_safe_redirect( home_url( $map[ $path ] ), 302 );
		exit;
	}
}, 5 );

/* ------------------------------------------------------------------
 * Loops: each language only lists its own posts.
 * ------------------------------------------------------------------ */
function bv_lang_meta_query( string $lang ): array {
	if ( 'nl' === $lang ) {
		return [ 'key' => '_bv_lang', 'value' => 'nl' ];
	}
	return [
		'relation' => 'OR',
		[ 'key' => '_bv_lang', 'compare' => 'NOT EXISTS' ],
		[ 'key' => '_bv_lang', 'value' => 'nl', 'compare' => '!=' ],
	];
}

add_action( 'pre_get_posts', static function ( WP_Query $q ) {
	if ( is_admin() || ! $q->is_main_query() ) return;
	if ( ! ( $q->is_home() || $q->is_archive() || $q->is_search() || $q->is_feed() ) ) return;
	$mq   = (array) $q->get( 'meta_query' );
	$mq[] = bv_lang_meta_query( bv_lang() );
	$q->set( 'meta_query', $mq );
} );

add_filter( 'query_loop_block_query_vars', static function ( $vars ) {
	if ( is_admin() ) return $vars;
	$types = (array) ( $vars['post_type'] ?? 'post' );
	if ( ! in_array( 'post', $types, true ) ) return $vars;
	$mq   = isset( $vars['meta_query'] ) ? (array) $vars['meta_query'] : [];
	$mq[] = bv_lang_meta_query( bv_lang() );
	$vars['meta_query'] = $mq;
	return $vars;
} );

/* ------------------------------------------------------------------
 * Templates: Dutch header, footer, single post and 404.
 * ------------------------------------------------------------------ */
add_filter( 'render_block_data', static function ( $block ) {
	if ( 'core/template-part' !== ( $block['blockName'] ?? '' ) || 'nl' !== bv_lang() ) return $block;
	$slug = $block['attrs']['slug'] ?? '';
	if ( 'header' === $slug || 'footer' === $slug ) {
		$block['attrs']['slug'] = $slug . '-nl';
	}
	return $block;
} );

add_filter( 'single_template_hierarchy', static function ( $templates ) {
	if ( 'nl' === bv_lang() ) array_unshift( $templates, 'single-nl' );
	return $templates;
} );

add_filter( '404_template_hierarchy', static function ( $templates ) {
	if ( 'nl' === bv_lang() ) array_unshift( $templates, '404-nl' );
	return $templates;
} );

/* ------------------------------------------------------------------
 * hreflang
 * ------------------------------------------------------------------ */

/** Published counterpart of a post or page, or 0. The Dutch side stores
 * the link; the English side finds it with a reverse lookup. */
function bv_translation_id( int $id ): int {
	static $memo = [];
	if ( isset( $memo[ $id ] ) ) return $memo[ $id ];
	$t = (int) get_post_meta( $id, '_bv_translation', true );
	if ( ! $t ) {
		$found = get_posts( [
			'post_type'        => [ 'post', 'page' ],
			'post_status'      => 'publish',
			'meta_key'         => '_bv_translation',
			'meta_value'       => $id,
			'fields'           => 'ids',
			'numberposts'      => 1,
			'suppress_filters' => true,
			'no_found_rows'    => true,
		] );
		$t = $found ? (int) $found[0] : 0;
	}
	return $memo[ $id ] = ( $t && 'publish' === get_post_status( $t ) ) ? $t : 0;
}

/** ['en' => url, 'nl' => url] for the current request, or [] without a pair. */
function bv_alternates(): array {
	static $memo = null;
	if ( null !== $memo ) return $memo;
	$memo = [];
	if ( is_paged() ) return $memo;
	if ( is_front_page() && 'page' === get_option( 'show_on_front' ) ) {
		$id = (int) get_option( 'page_on_front' );
	} elseif ( is_home() ) {
		$id = (int) get_option( 'page_for_posts' );
	} elseif ( is_singular() ) {
		$id = (int) get_queried_object_id();
	} else {
		return $memo;
	}
	if ( ! $id || 'publish' !== get_post_status( $id ) ) return $memo;
	$other = bv_translation_id( $id );
	if ( ! $other ) return $memo;
	$here           = bv_post_lang( $id );
	$memo[ $here ]  = get_permalink( $id );
	$memo[ 'nl' === $here ? 'en' : 'nl' ] = get_permalink( $other );
	return $memo;
}

add_action( 'wp_head', static function () {
	$alt = bv_alternates();
	if ( empty( $alt['en'] ) || empty( $alt['nl'] ) ) return;
	printf( '<link rel="alternate" hreflang="en" href="%s">' . "\n", esc_url( $alt['en'] ) );
	printf( '<link rel="alternate" hreflang="nl" href="%s">' . "\n", esc_url( $alt['nl'] ) );
	printf( '<link rel="alternate" hreflang="x-default" href="%s">' . "\n", esc_url( $alt['en'] ) );
}, 2 );

/* Language link in the footer: the counterpart of this page, else the
 * other language's home. Empty until the Dutch home is published. */
add_shortcode( 'bv_lang_switch', static function () {
	$to      = 'nl' === bv_lang() ? 'en' : 'nl';
	$nl_home = get_page_by_path( 'nl' );
	if ( ! $nl_home || 'publish' !== $nl_home->post_status ) return '';
	$alt = bv_alternates();
	$url = $alt[ $to ] ?? ( 'nl' === $to ? get_permalink( $nl_home ) : home_url( '/' ) );
	return sprintf(
		'<a class="bv-lang-switch" href="%s" hreflang="%s" lang="%s">%s</a>',
		esc_url( $url ),
		$to,
		$to,
		'nl' === $to ? 'Nederlands' : 'English'
	);
} );

/* A list item that ended up empty (switch not shown yet) renders nothing. */
add_filter( 'render_block_core/list-item', static fn( $html ) => '<li></li>' === trim( $html ) ? '' : $html );

/* ------------------------------------------------------------------
 * Yoast: breadcrumbs start at the Dutch home on Dutch pages.
 * og:locale and schema inLanguage follow the locale filter above.
 * ------------------------------------------------------------------ */
add_filter( 'wpseo_breadcrumb_links', static function ( $links ) {
	if ( 'nl' !== bv_lang() || empty( $links ) ) return $links;
	$nl_home = home_url( '/nl/' );
	$en_blog = (int) get_option( 'page_for_posts' );
	$nl_blog = get_page_by_path( 'nl/blog' );
	$out     = [ [ 'url' => $nl_home, 'text' => 'Home' ] ];
	foreach ( array_slice( $links, 1 ) as $l ) {
		$url = $l['url'] ?? ( isset( $l['id'] ) ? get_permalink( $l['id'] ) : '' );
		if ( $url && untrailingslashit( $url ) === untrailingslashit( $nl_home ) ) continue;
		if ( $en_blog && ( ( $l['id'] ?? 0 ) === $en_blog || ( $url && untrailingslashit( $url ) === untrailingslashit( get_permalink( $en_blog ) ) ) ) ) {
			if ( $nl_blog ) $out[] = [ 'url' => get_permalink( $nl_blog ), 'text' => 'Blog' ];
			continue;
		}
		$out[] = $l;
	}
	return $out;
} );

/* ------------------------------------------------------------------
 * Contact Form 7: one form per purpose, Dutch labels and messages on
 * Dutch pages, so leads keep flowing through the same Brevo setup.
 * ------------------------------------------------------------------ */
add_filter( 'wpcf7_form_elements', static function ( $html ) {
	if ( 'nl' !== bv_lang() ) return $html;
	return preg_replace(
		[ '/(<label>\s*)Name(\s)/', '/(<label>\s*)Email(\s)/', '/(<label>\s*)Subject(\s)/', '/(<label>\s*)Message(\s)/', '/value="Send"/', '/placeholder="Email Address"/' ],
		[ '$1Naam$2', '$1E-mail$2', '$1Onderwerp$2', '$1Bericht$2', 'value="Versturen"', 'placeholder="E-mailadres"' ],
		$html
	);
}, 20 );

function bv_cf7_is_nl(): bool {
	if ( class_exists( 'WPCF7_Submission' ) ) {
		$s = WPCF7_Submission::get_instance();
		if ( $s ) {
			$cid = (int) $s->get_meta( 'container_post_id' );
			if ( $cid ) return 'nl' === bv_post_lang( $cid );
			$url = (string) $s->get_meta( 'url' );
			if ( $url ) return (bool) preg_match( '#^https?://[^/]+/nl(/|$)#', $url );
		}
	}
	return 'nl' === bv_lang();
}

add_filter( 'wpcf7_display_message', static function ( $message ) {
	if ( ! bv_cf7_is_nl() ) return $message;
	$nl = [
		'Thank you for your message.' => 'Bedankt voor je bericht.',
		'Thank you.'                  => 'Bedankt!',
		'Please check the fields.'    => 'Controleer de velden.',
		'Please try again.'           => 'Er ging iets mis. Probeer het opnieuw.',
		'Please fill out this field.' => 'Vul dit veld in.',
	];
	return $nl[ $message ] ?? $message;
} );
