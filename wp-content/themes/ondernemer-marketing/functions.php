<?php
/**
 * OndernemerMarketing , block theme bootstrap.
 *
 * Minimal by design , see the included CUSTOM_THEME_RESEARCH_2026.md.
 * Block themes auto-enable: post-thumbnails, editor-styles,
 * responsive-embeds, automatic-feed-links, html5. We only register
 * what is NOT auto-enabled.
 *
 * @package OndernemerMarketing
 */

defined( 'ABSPATH' ) || exit;

const ONDM_THEME_VERSION = '1.2.0';

if ( ! defined( 'ONDM_THEME_DIR' ) ) {
	define( 'ONDM_THEME_DIR', get_stylesheet_directory() );
}
if ( ! defined( 'ONDM_THEME_URI' ) ) {
	define( 'ONDM_THEME_URI', get_stylesheet_directory_uri() );
}

/* ---------------------------------------------------------------------
 * after_setup_theme , editor styles + i18n.
 * --------------------------------------------------------------------- */
add_action( 'after_setup_theme', function () {
	add_editor_style( 'assets/editor-styles.css' );
	load_theme_textdomain( 'ondernemer-marketing', ONDM_THEME_DIR . '/languages' );
	add_theme_support( 'title-tag' );
} );

/* ---------------------------------------------------------------------
 * wp_enqueue_scripts , front-end stylesheet for the bits theme.json
 * cannot express (focus-visible, skip link, prefers-reduced-motion,
 * scoped overrides).
 * --------------------------------------------------------------------- */
add_action( 'wp_enqueue_scripts', function () {
	$path = ONDM_THEME_DIR . '/assets/front.css';
	wp_enqueue_style(
		'ondm-front',
		ONDM_THEME_URI . '/assets/front.css',
		[],
		file_exists( $path ) ? (string) filemtime( $path ) : ONDM_THEME_VERSION
	);
} );

/* ---------------------------------------------------------------------
 * init , per-block stylesheets via wp_enqueue_block_style() so each
 * block's CSS only loads when the block actually renders.
 * --------------------------------------------------------------------- */
add_action( 'init', function () {
	if ( ! function_exists( 'wp_enqueue_block_style' ) ) return;
	foreach ( [ 'core/cover', 'core/button', 'core/navigation', 'core/columns', 'core/group' ] as $block ) {
		$slug = str_replace( '/', '-', $block );
		$path = ONDM_THEME_DIR . "/assets/blocks/{$slug}.css";
		if ( ! file_exists( $path ) ) continue;
		wp_enqueue_block_style( $block, [
			'handle' => "ondm-{$slug}",
			'src'    => ONDM_THEME_URI . "/assets/blocks/{$slug}.css",
			'path'   => $path,
			'ver'    => (string) filemtime( $path ),
		] );
	}
} );

/* ---------------------------------------------------------------------
 * init , register custom blocks (apiVersion 3) from blocks/.
 * --------------------------------------------------------------------- */
add_action( 'init', function () {
	foreach ( [ 'funnel-calculator', 'roi-forecaster', 'marketing-quickscan' ] as $slug ) {
		$dir = ONDM_THEME_DIR . '/blocks/' . $slug;
		if ( file_exists( $dir . '/block.json' ) ) {
			register_block_type( $dir );
		}
	}
} );

/* ---------------------------------------------------------------------
 * init , register block-pattern categories. Patterns themselves
 * auto-discover from /patterns/*.php.
 * --------------------------------------------------------------------- */
add_action( 'init', function () {
	if ( ! function_exists( 'register_block_pattern_category' ) ) return;
	foreach ( [
		'ondm-hero'         => __( 'OndernemerMarketing , Hero', 'ondernemer-marketing' ),
		'ondm-packages'     => __( 'OndernemerMarketing , Pakketten', 'ondernemer-marketing' ),
		'ondm-pricing'      => __( 'OndernemerMarketing , Prijzen', 'ondernemer-marketing' ),
		'ondm-testimonials' => __( 'OndernemerMarketing , Testimonials', 'ondernemer-marketing' ),
		'ondm-cta'          => __( 'OndernemerMarketing , CTA', 'ondernemer-marketing' ),
		'ondm-team'         => __( 'OndernemerMarketing , Team', 'ondernemer-marketing' ),
		'ondm-contact'      => __( 'OndernemerMarketing , Contact', 'ondernemer-marketing' ),
		'ondm-content'      => __( 'OndernemerMarketing , Content', 'ondernemer-marketing' ),
	] as $slug => $label ) {
		register_block_pattern_category( $slug, [ 'label' => $label ] );
	}
} );

/* ---------------------------------------------------------------------
 * Install hook , side-loads bundled images into the Media Library,
 * sets the logo + site icon, populates the bundled-image map.
 * --------------------------------------------------------------------- */
require_once ONDM_THEME_DIR . '/inc/install.php';

/* ---------------------------------------------------------------------
 * Contact form handler , admin-post.php. Sends a notification email
 * to the site admin address via wp_mail(). Wire Brevo / SendGrid /
 * MailerLite by replacing wp_mail() with the provider's PHP SDK.
 * --------------------------------------------------------------------- */
require_once ONDM_THEME_DIR . '/inc/contact-form.php';

/* SEO + GEO + AEO emitters. */
require_once ONDM_THEME_DIR . '/inc/seo.php';
require_once ONDM_THEME_DIR . '/inc/geo.php';
