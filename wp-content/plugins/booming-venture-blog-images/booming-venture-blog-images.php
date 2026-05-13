<?php
/**
 * Plugin Name:       Booming Venture , Blog Images
 * Plugin URI:        https://boomingventure.com
 * Description:       Adds a "Featured image" picker to every blog post so you can manually choose an image from the Media Library. The image renders nicely as a hero on the single post and as a card thumbnail in the blog overview. Mobile-first. Works on any active theme.
 * Version:           1.0.0
 * Requires at least: 6.6
 * Requires PHP:      8.0
 * Author:            Booming Venture
 * Author URI:        https://boomingventure.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       booming-venture-blog-images
 * Update URI:        false
 *
 * @package BoomingVentureBlogImages
 */

defined( 'ABSPATH' ) || exit;

const BVIMG_VERSION = '1.0.0';

if ( ! defined( 'BVIMG_FILE' ) ) {
	define( 'BVIMG_FILE', __FILE__ );
}
if ( ! defined( 'BVIMG_DIR' ) ) {
	define( 'BVIMG_DIR', plugin_dir_path( __FILE__ ) );
}

/* ---------------------------------------------------------------------
 * Image sizes + theme support
 *
 * Enabling post-thumbnails surfaces the standard "Featured image" panel
 * in the post editor sidebar (the one with a Media Library picker).
 * That is the manual selection UI the operator already knows. We do
 * not invent our own meta box.
 * --------------------------------------------------------------------- */
add_action( 'after_setup_theme', function () {
	if ( ! current_theme_supports( 'post-thumbnails' ) ) {
		add_theme_support( 'post-thumbnails', [ 'post' ] );
	} else {
		add_post_type_support( 'post', 'thumbnail' );
	}
	add_image_size( 'bvimg_hero', 1600, 900, true );
	add_image_size( 'bvimg_card', 640, 360, true );
} );

add_filter( 'image_size_names_choose', function ( $sizes ) {
	$sizes['bvimg_hero'] = __( 'Booming hero (1600x900)', 'booming-venture-blog-images' );
	$sizes['bvimg_card'] = __( 'Booming card (640x360)', 'booming-venture-blog-images' );
	return $sizes;
} );

/* Force the "Featured image" meta box to appear even on themes that do
 * not register it explicitly. Side bar position by default. */
add_action( 'add_meta_boxes_post', function () {
	if ( ! post_type_supports( 'post', 'thumbnail' ) ) {
		add_post_type_support( 'post', 'thumbnail' );
	}
} );

/* ---------------------------------------------------------------------
 * Front-end CSS , mobile-first, scoped to .bvimg-* wrappers.
 * --------------------------------------------------------------------- */
add_action( 'wp_enqueue_scripts', function () {
	$path = BVIMG_DIR . 'assets/blog-images.css';
	$url  = plugins_url( 'assets/blog-images.css', BVIMG_FILE );
	$ver  = file_exists( $path ) ? (string) filemtime( $path ) : BVIMG_VERSION;
	wp_enqueue_style( 'bvimg-blog', $url, [], $ver );
} );

/* ---------------------------------------------------------------------
 * Single post , prepend a hero figure to the_content.
 *
 * Priority 5 so it lands above any wpautop / shortcode processing.
 * Skips if no featured image is set, so posts without a picked image
 * render exactly like before.
 * --------------------------------------------------------------------- */
add_filter( 'the_content', function ( $content ) {
	if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	$id = (int) get_post_thumbnail_id( get_the_ID() );
	if ( ! $id ) return $content;

	$img = wp_get_attachment_image(
		$id,
		'bvimg_hero',
		false,
		[
			'class'         => 'bvimg-hero-img',
			'loading'       => 'eager',
			'fetchpriority' => 'high',
			'decoding'      => 'async',
		]
	);
	$alt  = get_post_meta( $id, '_wp_attachment_image_alt', true );
	$cap  = wp_get_attachment_caption( $id );
	$cap_html = $cap ? '<figcaption class="bvimg-hero-caption">' . esc_html( $cap ) . '</figcaption>' : '';

	$figure = '<figure class="bvimg-hero" aria-label="' . esc_attr( $alt ?: get_the_title() ) . '">'
		. $img
		. $cap_html
		. '</figure>';

	return $figure . $content;
}, 5 );

/* ---------------------------------------------------------------------
 * Blog overview / archive , prepend a card image to the excerpt.
 *
 * Wraps the image in a permalink so the whole card thumbnail is
 * clickable. Skips on single views. Lazy-loaded.
 * --------------------------------------------------------------------- */
add_filter( 'get_the_excerpt', function ( $excerpt, $post = null ) {
	if ( is_singular() ) return $excerpt;
	if ( ! in_the_loop() || ! is_main_query() ) return $excerpt;
	$post_id = $post ? (int) $post->ID : (int) get_the_ID();
	if ( 'post' !== get_post_type( $post_id ) ) return $excerpt;

	$id = (int) get_post_thumbnail_id( $post_id );
	if ( ! $id ) return $excerpt;

	$img = wp_get_attachment_image(
		$id,
		'bvimg_card',
		false,
		[
			'class'    => 'bvimg-card-img',
			'loading'  => 'lazy',
			'decoding' => 'async',
		]
	);
	$alt  = get_post_meta( $id, '_wp_attachment_image_alt', true );
	$link = get_permalink( $post_id );

	$card = '<a class="bvimg-card-link" href="' . esc_url( $link ) . '" aria-label="' . esc_attr( $alt ?: get_the_title( $post_id ) ) . '">'
		. $img
		. '</a>';

	return $card . $excerpt;
}, 10, 2 );

/* ---------------------------------------------------------------------
 * Block-theme support , inject thumbnail into core/post-template loops
 * even when the theme's block markup does not include a post-featured-
 * image block. We hook render_block on core/post-excerpt and prepend
 * the same card markup so block-based archive pages light up too.
 * --------------------------------------------------------------------- */
add_filter( 'render_block', function ( $block_content, $block ) {
	if ( empty( $block['blockName'] ) ) return $block_content;
	if ( 'core/post-excerpt' !== $block['blockName'] ) return $block_content;
	if ( is_singular() ) return $block_content;

	global $post;
	$post_id = $post ? (int) $post->ID : (int) get_the_ID();
	if ( 'post' !== get_post_type( $post_id ) ) return $block_content;

	$id = (int) get_post_thumbnail_id( $post_id );
	if ( ! $id ) return $block_content;

	/* Only inject once per loop iteration: avoid double-render if the
	 * theme already shows the featured image via a sibling block. */
	static $seen = [];
	if ( isset( $seen[ $post_id ] ) ) return $block_content;
	$seen[ $post_id ] = true;

	$img = wp_get_attachment_image(
		$id,
		'bvimg_card',
		false,
		[
			'class'    => 'bvimg-card-img',
			'loading'  => 'lazy',
			'decoding' => 'async',
		]
	);
	$alt  = get_post_meta( $id, '_wp_attachment_image_alt', true );
	$link = get_permalink( $post_id );

	$card = '<a class="bvimg-card-link" href="' . esc_url( $link ) . '" aria-label="' . esc_attr( $alt ?: get_the_title( $post_id ) ) . '">'
		. $img
		. '</a>';

	return $card . $block_content;
}, 10, 2 );

/* ---------------------------------------------------------------------
 * Editor: helpful sidebar nudge if a post is saved without a featured
 * image. Renders as a small notice at the top of the editor screen.
 * --------------------------------------------------------------------- */
add_action( 'admin_notices', function () {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'post' !== $screen->id || 'post' !== $screen->post_type ) return;
	global $post;
	if ( ! $post || 'publish' !== $post->post_status ) return;
	if ( get_post_thumbnail_id( $post->ID ) ) return;
	?>
	<div class="notice notice-info">
		<p><strong><?php echo esc_html__( 'Tip:', 'booming-venture-blog-images' ); ?></strong>
		<?php echo esc_html__( 'Pick a Featured image in the sidebar so this post gets a hero banner and a card thumbnail in the blog overview.', 'booming-venture-blog-images' ); ?></p>
	</div>
	<?php
} );

/* ---------------------------------------------------------------------
 * Activation , regenerate image sizes for existing images would be
 * expensive, so we just register the sizes. Existing uploads will be
 * served at the closest WP-generated size; new uploads use ours.
 * --------------------------------------------------------------------- */
register_activation_hook( BVIMG_FILE, function () {
	if ( version_compare( PHP_VERSION, '8.0', '<' ) ) {
		deactivate_plugins( plugin_basename( BVIMG_FILE ) );
		wp_die(
			esc_html__( 'Booming Venture Blog Images requires PHP 8.0+.', 'booming-venture-blog-images' ),
			esc_html__( 'Activation error', 'booming-venture-blog-images' ),
			[ 'back_link' => true ]
		);
	}
} );
