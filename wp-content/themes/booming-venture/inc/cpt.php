<?php
/**
 * Custom post types & taxonomies.
 *
 * @package BoomingVenture
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {

	register_post_type( 'service', [
		'labels' => [
			'name'          => __( 'Services', 'booming-venture' ),
			'singular_name' => __( 'Service',  'booming-venture' ),
			'add_new_item'  => __( 'Add New Service', 'booming-venture' ),
		],
		'public'              => true,
		'show_in_rest'        => true,
		'has_archive'         => false,
		'rewrite'             => [ 'slug' => 'service', 'with_front' => false ],
		'menu_icon'           => 'dashicons-chart-area',
		'supports'            => [ 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes', 'custom-fields', 'revisions' ],
		'template'            => [
			[ 'core/heading', [ 'level' => 1, 'placeholder' => 'Service name' ] ],
			[ 'core/paragraph', [ 'placeholder' => 'One-line value proposition.' ] ],
		],
	] );

	register_post_type( 'landing_page', [
		'labels' => [
			'name'          => __( 'Landing Pages', 'booming-venture' ),
			'singular_name' => __( 'Landing Page',  'booming-venture' ),
		],
		'public'              => true,
		'show_in_rest'        => true,
		'has_archive'         => false,
		'exclude_from_search' => false,
		'rewrite'             => [ 'slug' => 'lp', 'with_front' => false ],
		'menu_icon'           => 'dashicons-megaphone',
		'supports'            => [ 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'revisions' ],
	] );

	register_post_type( 'case_study', [
		'labels' => [
			'name'          => __( 'Case Studies', 'booming-venture' ),
			'singular_name' => __( 'Case Study',   'booming-venture' ),
		],
		'public'              => true,
		'show_in_rest'        => true,
		'has_archive'         => 'case-studies',
		'rewrite'             => [ 'slug' => 'case-studies', 'with_front' => false ],
		'menu_icon'           => 'dashicons-portfolio',
		'supports'            => [ 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'revisions' ],
	] );

	register_taxonomy( 'service_category', [ 'service' ], [
		'labels'       => [ 'name' => __( 'Service Categories', 'booming-venture' ), 'singular_name' => __( 'Service Category', 'booming-venture' ) ],
		'public'       => true,
		'show_in_rest' => true,
		'hierarchical' => true,
		'rewrite'      => [ 'slug' => 'service-category' ],
	] );

	register_taxonomy( 'industry', [ 'case_study' ], [
		'labels'       => [ 'name' => __( 'Industries', 'booming-venture' ), 'singular_name' => __( 'Industry', 'booming-venture' ) ],
		'public'       => true,
		'show_in_rest' => true,
		'hierarchical' => true,
		'rewrite'      => [ 'slug' => 'industry' ],
	] );

	/* Post meta exposed to the block editor (for binding to the new
	 * core/post-meta source — works without ACF). */
	$meta = [
		'service'      => [ '_bv_icon', '_bv_subtitle', '_bv_benefits', '_bv_cta_url', '_bv_cta_label' ],
		'landing_page' => [ '_bv_hero_eyebrow', '_bv_hero_subtitle', '_bv_cta_url', '_bv_cta_label' ],
		'case_study'   => [ '_bv_client', '_bv_industry', '_bv_metric_a', '_bv_metric_b', '_bv_metric_c' ],
		'post'         => [ '_bv_read_time', '_bv_summary' ],
	];
	foreach ( $meta as $post_type => $keys ) {
		foreach ( $keys as $key ) {
			register_post_meta( $post_type, $key, [
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => function () { return current_user_can( 'edit_posts' ); },
			] );
		}
	}
} );
