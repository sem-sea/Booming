<?php
/**
 * Custom block registration, Funnel Calculator and ROI Forecaster.
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
	$blocks = [ 'funnel-calculator', 'roi-forecaster', 'geo-index', 'geo-sector' ];
	foreach ( $blocks as $block ) {
		$path = BV_THEME_DIR . '/blocks/' . $block;
		if ( file_exists( $path . '/block.json' ) ) {
			register_block_type( $path );
		}
	}
} );

/*
 * Cache busting for block files: the style version follows the newest
 * view.css / view.js, so browsers never keep an old copy after an update.
 */
add_filter( 'block_type_metadata', function ( $metadata ) {
	if ( empty( $metadata['name'] ) || 0 !== strpos( $metadata['name'], 'booming-venture/' ) || empty( $metadata['file'] ) ) {
		return $metadata;
	}
	$dir   = dirname( $metadata['file'] );
	$mtime = max( (int) @filemtime( $dir . '/view.css' ), (int) @filemtime( $dir . '/view.js' ) );
	if ( $mtime ) {
		$metadata['version'] = ( $metadata['version'] ?? '1' ) . '.' . $mtime;
	}
	return $metadata;
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
