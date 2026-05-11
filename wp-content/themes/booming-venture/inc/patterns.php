<?php
/**
 * Pattern category registration.
 * Pattern files live in /patterns/ and are auto-discovered by core.
 *
 * @package BoomingVenture
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	register_block_pattern_category( 'booming-venture/home',       [ 'label' => __( 'Booming · Home',     'booming-venture' ) ] );
	register_block_pattern_category( 'booming-venture/sections',   [ 'label' => __( 'Booming · Sections', 'booming-venture' ) ] );
	register_block_pattern_category( 'booming-venture/landing',    [ 'label' => __( 'Booming · Landing',  'booming-venture' ) ] );
	register_block_pattern_category( 'booming-venture/cta',        [ 'label' => __( 'Booming · CTA',      'booming-venture' ) ] );
} );
