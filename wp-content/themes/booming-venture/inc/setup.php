<?php
/**
 * Theme setup — supports, image sizes, nav menus, editor styles.
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

/* Viewport with viewport-fit=cover — needed for env(safe-area-inset-*) to fire
 * on iPhone 14+ Dynamic Island / Android 15 edge-to-edge. Kept user-scalable
 * (WCAG 1.4.4 — never set maximum-scale=1). */
add_action( 'wp_head', function () {
	echo '<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">' . "\n";
	echo '<meta name="theme-color" content="#0284c7">' . "\n";
	echo '<meta name="color-scheme" content="light">' . "\n";
}, 0 );

/* Favicon / site icon — falls back to the uploaded brand mark on Strato
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
 * Each slug (service-1 … service-4, growth-guide, etc.) can be mapped
 * in Settings → Booming Venture → Brand images to a Strato-hosted
 * /wp-content/uploads/<yyyy>/<mm>/<filename> URL, or just a filename
 * (we prepend /wp-content/uploads/2026/05/). Falls back to the
 * theme's assets/images/<slug>.jpg.
 */
function bv_image( string $slug, string $ext = 'png' ): string {
	$map = (array) get_option( 'bv_media_map', [] );
	$val = isset( $map[ $slug ] ) ? trim( (string) $map[ $slug ] ) : '';

	if ( $val !== '' ) {
		if ( preg_match( '#^https?://#', $val ) ) {
			return esc_url( $val );
		}
		if ( str_starts_with( $val, '/wp-content/' ) ) {
			return esc_url( home_url( $val ) );
		}
		// Bare filename or UUID — assume current upload month folder.
		$file = ltrim( $val, '/' );
		if ( ! preg_match( '/\.[a-z0-9]+$/i', $file ) ) {
			$file .= '.' . $ext;
		}
		return esc_url( home_url( '/wp-content/uploads/2026/05/' . $file ) );
	}

	return esc_url( BV_THEME_URI . '/assets/images/' . $slug . '.jpg' );
}

/* Body class helpers. */
add_filter( 'body_class', function ( $classes ) {
	if ( is_singular( 'service' ) )      $classes[] = 'is-service';
	if ( is_singular( 'landing_page' ) ) $classes[] = 'is-landing';
	if ( is_singular( 'case_study' ) )   $classes[] = 'is-case-study';
	return $classes;
} );
