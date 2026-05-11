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

/* Body class helpers. */
add_filter( 'body_class', function ( $classes ) {
	if ( is_singular( 'service' ) )      $classes[] = 'is-service';
	if ( is_singular( 'landing_page' ) ) $classes[] = 'is-landing';
	if ( is_singular( 'case_study' ) )   $classes[] = 'is-case-study';
	return $classes;
} );
