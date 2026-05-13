<?php
/**
 * Theme setup ,  supports, image sizes, nav menus, editor styles.
 *
 * @package BoomingVenture
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', function () {
	load_theme_textdomain( 'booming-venture', BV_THEME_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'custom-logo', [
		'height'      => 64,
		'width'       => 240,
		'flex-height' => true,
		'flex-width'  => true,
	] );
	add_theme_support( 'html5', [ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ] );

	add_editor_style( [ 'assets/css/editor.css' ] );

	add_image_size( 'bv-hero',     1920, 1080, true );
	add_image_size( 'bv-card',     800,  500,  true );
	add_image_size( 'bv-square',   600,  600,  true );
	add_image_size( 'bv-portrait', 600,  900,  true );

	register_nav_menus( [
		'primary' => __( 'Primary',  'booming-venture' ),
		'footer'  => __( 'Footer',   'booming-venture' ),
		'legal'   => __( 'Legal',    'booming-venture' ),
		'tools'   => __( 'Tools',    'booming-venture' ),
	] );
} );

/* Disable Gutenberg's default UI overrides we don't want. */
add_action( 'init', function () {
	remove_theme_support( 'core-block-patterns' );
} );

/* Cleaner <head>. */
add_action( 'init', function () {
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
} );

/* Viewport with viewport-fit=cover ,  needed for env(safe-area-inset-*) to fire
 * on iPhone 14+ Dynamic Island / Android 15 edge-to-edge. Kept user-scalable
 * (WCAG 1.4.4 ,  never set maximum-scale=1). */
add_action( 'wp_head', function () {
	echo '<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">' . "\n";
	echo '<meta name="theme-color" content="#0284c7">' . "\n";
	echo '<meta name="color-scheme" content="light">' . "\n";

	/* Diagnostic markers. Wrapped defensively so a single missing
	 * function or constant never breaks the front-end. */
	try {
		$ver         = defined( 'BV_THEME_VERSION' ) ? BV_THEME_VERSION : 'unknown';
		$installed_v = function_exists( 'get_option' ) ? (string) get_option( 'bv_content_imported', 'never-installed' ) : 'pre-boot';
		echo '<meta name="bv-theme-version" content="' . esc_attr( $ver ) . '">' . "\n";
		echo '<meta name="bv-install-version" content="' . esc_attr( $installed_v ) . '">' . "\n";
		echo "<!-- Booming Venture theme " . esc_html( $ver ) . " | install-flag=" . esc_html( $installed_v ) . " -->\n";
	} catch ( \Throwable $e ) {
		/* swallow ,  diagnostics must never fatal a page */
	}
}, 0 );

/* Favicon / site icon ,  falls back to the uploaded brand mark on Strato
 * when no Customizer site_icon has been set. Filterable. */
add_action( 'wp_head', function () {
	if ( has_site_icon() ) {
		return; // Core already emits link tags.
	}
	$url = apply_filters(
		'bv_site_icon_url',
		'/wp-content/uploads/2026/05/icon-booming-venture.png'
	);
	$url = esc_url( $url );
	echo '<link rel="icon" href="' . $url . '" type="image/png">' . "\n";
	echo '<link rel="apple-touch-icon" href="' . $url . '">' . "\n";
	echo '<link rel="shortcut icon" href="' . $url . '">' . "\n";
}, 1 );

/**
 * Resolve a brand image slug to a full URL.
 *
 * Accepts in bv_media_map:
 *   - A full https:// URL  (returned verbatim)
 *   - A /wp-content/... path  (prefixed with home_url)
 *   - A bare filename or UUID  (resolved via attachment query)
 *
 * The attachment query searches _wp_attached_file for any row that
 * contains the given string, so the extension doesn't have to match.
 * Result is cached per-request and in a 1-day transient.
 *
 * Falls back to the theme's assets/images/<slug>.jpg when nothing
 * resolves, so the markup never breaks.
 */
function bv_image( string $slug ): string {
	/* CRITICAL: merge defaults inline. The option_bv_media_map filter
	 * only fires when the option row exists in wp_options; on a fresh
	 * install (or before anyone saves Settings -> Booming Venture for
	 * the first time) the row does not exist, the filter does not fire,
	 * and bv_image() previously fell back to theme assets that may not
	 * exist. Merging inline guarantees the defaults always apply. */
	$defaults = function_exists( 'bv_media_defaults' ) ? bv_media_defaults() : [];
	$stored   = (array) get_option( 'bv_media_map', [] );
	$map      = array_merge( $defaults, $stored );
	$val      = isset( $map[ $slug ] ) ? trim( (string) $map[ $slug ] ) : '';

	if ( $val === '' ) {
		/* No mapped image for this slug. Fall back to a guaranteed-live
		 * brand photo on Strato so the markup never carries a broken
		 * /assets/images/<slug>.jpg path. */
		return 'https://boomingventure.com/wp-content/uploads/2026/05/4e357139-5a7e-4336-8796-94013f33dc3d.png';
	}

	if ( preg_match( '#^https?://#i', $val ) ) {
		return esc_url( $val );
	}

	if ( str_starts_with( $val, '/wp-content/' ) ) {
		return esc_url( home_url( $val ) );
	}

	$resolved = bv_resolve_attachment_url( $val );
	if ( $resolved ) {
		return esc_url( $resolved );
	}

	/* Last-resort fallback ,  assume PNG in current upload month folder. */
	return esc_url( home_url( '/wp-content/uploads/2026/05/' . $val . '.png' ) );
}

/**
 * Look up an attachment URL by filename / UUID fragment.
 */
function bv_resolve_attachment_url( string $needle ): string {
	static $memo = [];
	if ( isset( $memo[ $needle ] ) ) return $memo[ $needle ];

	$key    = 'bv_attach_' . md5( $needle );
	$cached = get_transient( $key );
	if ( is_string( $cached ) && $cached !== '' ) {
		return $memo[ $needle ] = $cached;
	}

	global $wpdb;
	$like = '%' . $wpdb->esc_like( $needle ) . '%';
	$file = $wpdb->get_var( $wpdb->prepare(
		"SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s ORDER BY post_id DESC LIMIT 1",
		$like
	) );

	if ( ! $file ) return $memo[ $needle ] = '';

	$uploads = wp_get_upload_dir();
	$url     = trailingslashit( $uploads['baseurl'] ) . ltrim( (string) $file, '/' );
	set_transient( $key, $url, DAY_IN_SECONDS );
	return $memo[ $needle ] = $url;
}

/* Body class helpers. */
add_filter( 'body_class', function ( $classes ) {
	if ( is_singular( 'service' ) )      $classes[] = 'is-service';
	if ( is_singular( 'landing_page' ) ) $classes[] = 'is-landing';
	if ( is_singular( 'case_study' ) )   $classes[] = 'is-case-study';
	return $classes;
} );
