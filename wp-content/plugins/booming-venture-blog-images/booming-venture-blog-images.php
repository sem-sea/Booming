<?php
/**
 * Plugin Name:       Booming Venture , Blog Images
 * Plugin URI:        https://boomingventure.com
 * Description:       Adds a "Featured image" picker to every blog post so you can manually choose an image from the Media Library. The image renders nicely as a hero on the single post and as a card thumbnail in the blog overview. Mobile-first. Works on any active theme.
 * Version:           1.2.0
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

const BVIMG_VERSION    = '1.2.0';
const BVIMG_POOL_OPT   = 'bvimg_pool_ids';        // array<int> attachment IDs
const BVIMG_RANDOM_KEY = '_bvimg_random_assigned'; // post meta flag for random-assigned posts
const BVIMG_NONCE      = 'bvimg_pool';

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
 * Duplicate-detection
 *
 * The theme's post template usually already renders the Featured image
 * via the core/post-featured-image block. If we ALSO inject our own
 * hero figure via the_content, the user sees the image twice.
 *
 * We watch render_block for core/post-featured-image and remember,
 * per post, whether the theme already rendered it. The hero / card
 * filters short-circuit when the flag is set.
 *
 * The same flag also prevents the archive-card injection from running
 * twice when a block template includes core/post-featured-image inside
 * the post-template loop.
 * --------------------------------------------------------------------- */
function bvimg_mark_rendered( int $post_id = 0 ): void {
	static $seen = [];
	$post_id = $post_id ?: (int) get_the_ID();
	if ( ! $post_id ) return;
	$seen[ $post_id ] = true;
	$GLOBALS['bvimg_rendered'] = $seen;
}

function bvimg_already_rendered( int $post_id = 0 ): bool {
	$post_id = $post_id ?: (int) get_the_ID();
	if ( ! $post_id ) return false;
	$seen = isset( $GLOBALS['bvimg_rendered'] ) && is_array( $GLOBALS['bvimg_rendered'] ) ? $GLOBALS['bvimg_rendered'] : [];
	return ! empty( $seen[ $post_id ] );
}

add_filter( 'render_block_core/post-featured-image', function ( $block_content, $block ) {
	$post_id = isset( $block['context']['postId'] ) ? (int) $block['context']['postId'] : (int) get_the_ID();
	if ( $post_id ) bvimg_mark_rendered( $post_id );
	return $block_content;
}, 10, 2 );

/* Classic themes call the_post_thumbnail() directly. Hook into the
 * standard post_thumbnail_html filter so we can detect that too. */
add_filter( 'post_thumbnail_html', function ( $html, $post_id ) {
	if ( $html && $post_id ) bvimg_mark_rendered( (int) $post_id );
	return $html;
}, 10, 2 );

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
	$post_id = (int) get_the_ID();
	$id      = (int) get_post_thumbnail_id( $post_id );
	if ( ! $id ) return $content;

	/* Skip if the theme already rendered the Featured image (e.g. via
	 * a core/post-featured-image block in the post template, or via
	 * the_post_thumbnail() in a classic template). Avoids duplication. */
	if ( bvimg_already_rendered( $post_id ) ) return $content;

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

	bvimg_mark_rendered( $post_id );
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
	if ( bvimg_already_rendered( $post_id ) ) return $excerpt;

	$id = (int) get_post_thumbnail_id( $post_id );
	if ( ! $id ) return $excerpt;
	bvimg_mark_rendered( $post_id );

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
	if ( bvimg_already_rendered( $post_id ) ) return $block_content;

	$id = (int) get_post_thumbnail_id( $post_id );
	if ( ! $id ) return $block_content;
	bvimg_mark_rendered( $post_id );

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
 * Random-pool admin: Tools -> Blog Images Pool
 *
 * Two operations:
 *   1. PICK POOL  , open the Media Library, multi-select images.
 *                   IDs persisted in option BVIMG_POOL_OPT.
 *   2. ASSIGN     , for every published `post` that has no
 *                   _thumbnail_id, pick a random image from the pool
 *                   and set it as the Featured image. The post is
 *                   tagged with _bvimg_random_assigned=1 so we can
 *                   re-randomise only those later without clobbering
 *                   posts where the user picked manually.
 *
 * The user keeps full manual override: editing a post and changing
 * the Featured image in the sidebar removes the random flag (we keep
 * the flag only when WE wrote the thumbnail).
 * --------------------------------------------------------------------- */
add_action( 'admin_menu', function () {
	add_management_page(
		__( 'Blog Images Pool', 'booming-venture-blog-images' ),
		__( 'Blog Images Pool', 'booming-venture-blog-images' ),
		'manage_options',
		'bvimg-pool',
		'bvimg_render_pool_page'
	);
} );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( 'tools_page_bvimg-pool' !== $hook ) return;
	wp_enqueue_media();
	$js   = plugins_url( 'assets/pool-admin.js', BVIMG_FILE );
	$ver  = file_exists( BVIMG_DIR . 'assets/pool-admin.js' ) ? (string) filemtime( BVIMG_DIR . 'assets/pool-admin.js' ) : BVIMG_VERSION;
	wp_enqueue_script( 'bvimg-pool-admin', $js, [ 'jquery' ], $ver, true );
	wp_localize_script( 'bvimg-pool-admin', 'BVIMG', [
		'pickTitle'   => __( 'Pick images for the blog pool', 'booming-venture-blog-images' ),
		'pickButton'  => __( 'Use these images', 'booming-venture-blog-images' ),
		'removeLabel' => __( 'Remove from pool', 'booming-venture-blog-images' ),
	] );
} );

function bvimg_render_pool_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission.', 'booming-venture-blog-images' ) );
	}
	$pool        = (array) get_option( BVIMG_POOL_OPT, [] );
	$pool_ids    = array_map( 'intval', $pool );
	$posts_total = (int) wp_count_posts( 'post' )->publish;
	$without     = bvimg_count_posts_without_thumbnail();
	$random      = bvimg_count_random_assigned();
	?>
	<div class="wrap">
		<h1><?php echo esc_html__( 'Blog Images Pool', 'booming-venture-blog-images' ); ?></h1>

		<div class="notice" style="border-left:4px solid #0284c7;padding:1rem 1.25rem;background:#f0f9ff;">
			<h2 style="margin:0 0 0.5rem;font-size:18px;"><?php echo esc_html__( 'Status', 'booming-venture-blog-images' ); ?></h2>
			<table class="form-table" style="margin:0;">
				<tr><th style="width:280px;"><?php echo esc_html__( 'Images in pool', 'booming-venture-blog-images' ); ?></th><td><strong><?php echo count( $pool_ids ); ?></strong></td></tr>
				<tr><th><?php echo esc_html__( 'Published blog posts', 'booming-venture-blog-images' ); ?></th><td><strong><?php echo $posts_total; ?></strong></td></tr>
				<tr><th><?php echo esc_html__( 'Posts without a Featured image', 'booming-venture-blog-images' ); ?></th><td><strong><?php echo $without; ?></strong></td></tr>
				<tr><th><?php echo esc_html__( 'Posts with a random-assigned image', 'booming-venture-blog-images' ); ?></th><td><strong><?php echo $random; ?></strong></td></tr>
			</table>
		</div>

		<h2><?php echo esc_html__( 'Step 1 , pick the pool of allowed images', 'booming-venture-blog-images' ); ?></h2>
		<p><?php echo esc_html__( 'Open the Media Library and select every image that may be used for a blog post. The plugin will randomly pick one per post from this pool.', 'booming-venture-blog-images' ); ?></p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( BVIMG_NONCE ); ?>
			<input type="hidden" name="action" value="bvimg_save_pool">
			<input type="hidden" name="pool_ids" id="bvimg-pool-ids" value="<?php echo esc_attr( implode( ',', $pool_ids ) ); ?>">
			<button type="button" class="button button-primary" id="bvimg-pick"><?php echo esc_html__( 'Open Media Library', 'booming-venture-blog-images' ); ?></button>
			<button type="submit" class="button"><?php echo esc_html__( 'Save pool', 'booming-venture-blog-images' ); ?></button>
		</form>

		<h3 style="margin-top:1.25rem;"><?php echo esc_html__( 'Current pool', 'booming-venture-blog-images' ); ?></h3>
		<div id="bvimg-pool-preview" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:0.5rem;margin-top:0.5rem;">
			<?php foreach ( $pool_ids as $id ) :
				$src = wp_get_attachment_image_src( $id, 'thumbnail' );
				if ( ! $src ) continue;
			?>
				<div class="bvimg-pool-thumb" data-id="<?php echo (int) $id; ?>" style="position:relative;border:1px solid #e2e8f0;border-radius:0.375rem;overflow:hidden;background:#f8fafc;">
					<img src="<?php echo esc_url( $src[0] ); ?>" alt="" style="display:block;width:100%;height:90px;object-fit:cover;">
				</div>
			<?php endforeach; ?>
		</div>

		<h2 style="margin-top:2rem;"><?php echo esc_html__( 'Step 2 , assign random images', 'booming-venture-blog-images' ); ?></h2>
		<p>
			<?php echo wp_kses(
				__( 'Walks every published blog post. If the post has <strong>no</strong> Featured image, picks one at random from the pool and assigns it. Posts with a manually-picked Featured image are <strong>never touched</strong>.', 'booming-venture-blog-images' ),
				[ 'strong' => [] ]
			); ?>
		</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( BVIMG_NONCE ); ?>
			<input type="hidden" name="action" value="bvimg_assign">
			<button type="submit" class="button button-primary"<?php echo empty( $pool_ids ) ? ' disabled' : ''; ?>>
				<?php echo esc_html__( 'Assign random images now', 'booming-venture-blog-images' ); ?>
			</button>
			<?php if ( empty( $pool_ids ) ) : ?>
				<span style="color:#b91c1c;margin-left:0.5rem;"><?php echo esc_html__( 'Pool is empty , pick images first.', 'booming-venture-blog-images' ); ?></span>
			<?php endif; ?>
		</form>

		<h2 style="margin-top:2rem;"><?php echo esc_html__( 'Step 3 , re-randomise (optional)', 'booming-venture-blog-images' ); ?></h2>
		<p>
			<?php echo wp_kses(
				__( 'Re-picks a new random image, from the current pool, ONLY for posts that were previously random-assigned. Posts where you set a Featured image manually are <strong>not touched</strong>.', 'booming-venture-blog-images' ),
				[ 'strong' => [] ]
			); ?>
		</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('<?php echo esc_js( __( 'Re-pick random images for every post previously random-assigned?', 'booming-venture-blog-images' ) ); ?>');">
			<?php wp_nonce_field( BVIMG_NONCE ); ?>
			<input type="hidden" name="action" value="bvimg_reroll">
			<button type="submit" class="button"<?php echo ( empty( $pool_ids ) || ! $random ) ? ' disabled' : ''; ?>>
				<?php echo esc_html__( 'Re-randomise random-assigned posts', 'booming-venture-blog-images' ); ?>
			</button>
		</form>

		<?php if ( isset( $_GET['bvimg_done'] ) ) : ?>
			<div class="notice notice-success is-dismissible" style="margin-top:1.5rem;">
				<p><?php
					$mode = sanitize_text_field( wp_unslash( (string) $_GET['bvimg_done'] ) );
					$n    = isset( $_GET['n'] ) ? (int) $_GET['n'] : 0;
					$msgs = [
						'save_pool' => esc_html__( 'Pool saved.', 'booming-venture-blog-images' ),
						'assign'    => sprintf( esc_html__( 'Assigned random images to %d post(s).', 'booming-venture-blog-images' ), $n ),
						'reroll'    => sprintf( esc_html__( 'Re-randomised %d post(s).', 'booming-venture-blog-images' ), $n ),
					];
					echo esc_html( $msgs[ $mode ] ?? esc_html__( 'Done.', 'booming-venture-blog-images' ) );
				?></p>
			</div>
		<?php endif; ?>
	</div>
	<?php
}

/* ---------------------------------------------------------------------
 * admin-post handlers , save pool, assign, reroll.
 * --------------------------------------------------------------------- */
add_action( 'admin_post_bvimg_save_pool', function () {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );
	check_admin_referer( BVIMG_NONCE );
	$raw  = isset( $_POST['pool_ids'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['pool_ids'] ) ) : '';
	$ids  = array_filter( array_map( 'intval', explode( ',', $raw ) ) );
	$ids  = array_values( array_unique( $ids ) );
	/* Drop any ID that does not point to an attachment we can read. */
	$ids = array_values( array_filter( $ids, function ( $id ) {
		return 'attachment' === get_post_type( (int) $id );
	} ) );
	update_option( BVIMG_POOL_OPT, $ids, false );
	wp_safe_redirect( add_query_arg( [ 'page' => 'bvimg-pool', 'bvimg_done' => 'save_pool', 'n' => count( $ids ) ], admin_url( 'tools.php' ) ) );
	exit;
} );

add_action( 'admin_post_bvimg_assign', function () {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );
	check_admin_referer( BVIMG_NONCE );
	$n = bvimg_assign_random( false );
	wp_safe_redirect( add_query_arg( [ 'page' => 'bvimg-pool', 'bvimg_done' => 'assign', 'n' => $n ], admin_url( 'tools.php' ) ) );
	exit;
} );

add_action( 'admin_post_bvimg_reroll', function () {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );
	check_admin_referer( BVIMG_NONCE );
	$n = bvimg_assign_random( true );
	wp_safe_redirect( add_query_arg( [ 'page' => 'bvimg-pool', 'bvimg_done' => 'reroll', 'n' => $n ], admin_url( 'tools.php' ) ) );
	exit;
} );

/* ---------------------------------------------------------------------
 * Core , walk posts, pick a random image from the pool, assign it.
 *
 * @param bool $reroll  If true, target only posts already flagged
 *                      _bvimg_random_assigned=1 and replace their
 *                      Featured image with a fresh random pick.
 *                      If false, target only posts WITHOUT a Featured
 *                      image and assign one.
 * @return int  Number of posts updated.
 * --------------------------------------------------------------------- */
function bvimg_assign_random( bool $reroll ): int {
	$pool = (array) get_option( BVIMG_POOL_OPT, [] );
	$pool = array_values( array_filter( array_map( 'intval', $pool ) ) );
	if ( empty( $pool ) ) return 0;

	$args = [
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
	];

	if ( $reroll ) {
		$args['meta_query'] = [
			[
				'key'     => BVIMG_RANDOM_KEY,
				'value'   => '1',
				'compare' => '=',
			],
		];
	} else {
		$args['meta_query'] = [
			[
				'key'     => '_thumbnail_id',
				'compare' => 'NOT EXISTS',
			],
		];
	}

	$ids = get_posts( $args );
	if ( empty( $ids ) ) return 0;

	$GLOBALS['bvimg_bulk_running'] = true;
	$updated = 0;
	foreach ( $ids as $post_id ) {
		$pick = (int) $pool[ array_rand( $pool ) ];
		if ( ! $pick ) continue;
		set_post_thumbnail( (int) $post_id, $pick );
		update_post_meta( (int) $post_id, BVIMG_RANDOM_KEY, '1' );
		$updated++;
	}
	$GLOBALS['bvimg_bulk_running'] = false;
	return $updated;
}

/* Counts for the status panel */
function bvimg_count_posts_without_thumbnail(): int {
	$q = get_posts( [
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'meta_query'     => [
			[ 'key' => '_thumbnail_id', 'compare' => 'NOT EXISTS' ],
		],
	] );
	return count( $q );
}

function bvimg_count_random_assigned(): int {
	$q = get_posts( [
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'meta_query'     => [
			[ 'key' => BVIMG_RANDOM_KEY, 'value' => '1', 'compare' => '=' ],
		],
	] );
	return count( $q );
}

/* MANUAL OVERRIDE PROTECTION
 *
 * If the user changes the Featured image themselves (from the post
 * editor's Featured image panel, Quick Edit, or Bulk Edit), drop the
 * random-assigned flag so future "Re-randomise" runs never overwrite
 * their pick. The flag stays cleared even on subsequent re-saves.
 *
 * Bulk runs (Assign / Re-roll) set $GLOBALS['bvimg_bulk_running']=true
 * around their loop so this hook short-circuits and leaves the flag
 * we just wrote intact. */
add_action( 'updated_post_meta', function ( $meta_id, $post_id, $meta_key, $meta_value ) {
	if ( '_thumbnail_id' !== $meta_key ) return;
	if ( ! empty( $GLOBALS['bvimg_bulk_running'] ) ) return;
	delete_post_meta( (int) $post_id, BVIMG_RANDOM_KEY );
}, 10, 4 );

/* Same protection when WP fires added_post_meta the first time a
 * thumbnail is set on a post that previously had none. */
add_action( 'added_post_meta', function ( $meta_id, $post_id, $meta_key, $meta_value ) {
	if ( '_thumbnail_id' !== $meta_key ) return;
	if ( ! empty( $GLOBALS['bvimg_bulk_running'] ) ) return;
	delete_post_meta( (int) $post_id, BVIMG_RANDOM_KEY );
}, 10, 4 );

/* ---------------------------------------------------------------------
 * Posts list table column , show the current Featured image thumbnail
 * with a tiny "(random)" tag when applicable, so the operator can
 * scan which posts got auto-assigned vs manual.
 * --------------------------------------------------------------------- */
add_filter( 'manage_post_posts_columns', function ( $cols ) {
	$new = [];
	foreach ( $cols as $k => $v ) {
		$new[ $k ] = $v;
		if ( 'title' === $k ) {
			$new['bvimg_thumb'] = __( 'Image', 'booming-venture-blog-images' );
		}
	}
	return $new;
} );

add_action( 'manage_post_posts_custom_column', function ( $column, $post_id ) {
	if ( 'bvimg_thumb' !== $column ) return;
	$id = (int) get_post_thumbnail_id( (int) $post_id );
	if ( ! $id ) {
		echo '<span style="color:#9ca3af;">' . esc_html__( '— none', 'booming-venture-blog-images' ) . '</span>';
		return;
	}
	$src = wp_get_attachment_image_src( $id, 'thumbnail' );
	if ( ! $src ) return;
	$is_random = get_post_meta( (int) $post_id, BVIMG_RANDOM_KEY, true ) === '1';
	echo '<img src="' . esc_url( $src[0] ) . '" alt="" style="display:block;width:60px;height:40px;object-fit:cover;border-radius:4px;">';
	if ( $is_random ) {
		echo '<span style="display:inline-block;margin-top:2px;font-size:11px;color:#0369a1;">' . esc_html__( '(random)', 'booming-venture-blog-images' ) . '</span>';
	} else {
		echo '<span style="display:inline-block;margin-top:2px;font-size:11px;color:#16a34a;">' . esc_html__( '(manual)', 'booming-venture-blog-images' ) . '</span>';
	}
}, 10, 2 );

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
