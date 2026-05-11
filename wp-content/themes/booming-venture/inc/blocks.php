<?php
/**
 * Custom block registration — Funnel Calculator and ROI Forecaster.
 *
 * Each block has a block.json + a build/index.js produced by the Vite
 * pipeline in /blocks (run `npm install && npm run build` inside the
 * theme's `blocks/` directory). The Vite build emits the compiled React
 * bundle into `build/`, which WordPress picks up via block.json.
 *
 * @package BoomingVenture
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	$blocks = [ 'funnel-calculator', 'roi-forecaster' ];
	foreach ( $blocks as $block ) {
		$path = BV_THEME_DIR . '/blocks/' . $block;
		if ( file_exists( $path . '/block.json' ) ) {
			register_block_type( $path );
		}
	}
} );

/* Block category so authors find them quickly. */
add_filter( 'block_categories_all', function ( $categories ) {
	array_unshift( $categories, [
		'slug'  => 'booming-venture',
		'title' => __( 'Booming Venture', 'booming-venture' ),
		'icon'  => 'chart-line',
	] );
	return $categories;
}, 10, 1 );
