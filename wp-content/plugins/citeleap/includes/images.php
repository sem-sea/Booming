<?php
/**
 * CiteLeap , images.php
 *
 * Featured-image module. Ports the standalone "Booming Venture , Blog
 * Images" plugin into CiteLeap so the operator has ONE plugin to
 * install, with a single pool of media-library images that:
 *   - feeds the random auto-assignment for new posts (including the
 *     drafts CiteLeap itself creates via wp_insert_post)
 *   - feeds the og:image source for SEO Boost (seo.php)
 *   - renders as a hero on the single post when the active theme
 *     doesn't already render core/post-featured-image
 *   - renders as a card on the blog archive
 *
 * Manual override always wins: if the operator picks a Featured
 * Image themselves, the random flag drops and the plugin never
 * re-rolls that post.
 *
 * @package CiteLeap
 */

defined( 'ABSPATH' ) || exit;

class CiteLeap_Images {

	public static function settings(): array {
		$r = (array) get_option( CITELEAP_OPTION_IMAGES, [] );
		return [
			'pool'         => array_values( array_filter( array_map( 'intval', (array) ( $r['pool']         ?? [] ) ) ) ),
			'auto_assign'  => isset( $r['auto_assign']  ) ? (bool) $r['auto_assign']  : true,
			'render_hero'  => isset( $r['render_hero']  ) ? (bool) $r['render_hero']  : true,
			'render_card'  => isset( $r['render_card']  ) ? (bool) $r['render_card']  : true,
		];
	}

	public static function save_settings( array $args ): void {
		$ids = array_values( array_unique( array_filter( array_map( 'intval', (array) ( $args['pool'] ?? [] ) ) ) ) );
		/* Drop anything that is not actually an attachment. */
		$ids = array_values( array_filter( $ids, fn( $id ) => 'attachment' === get_post_type( (int) $id ) ) );
		update_option( CITELEAP_OPTION_IMAGES, [
			'pool'         => $ids,
			'auto_assign'  => ! empty( $args['auto_assign'] ),
			'render_hero'  => ! empty( $args['render_hero'] ),
			'render_card'  => ! empty( $args['render_card'] ),
		], false );
	}

	public static function pool(): array { return self::settings()['pool']; }

	public static function pick_random(): int {
		$pool = self::pool();
		if ( empty( $pool ) ) return 0;
		return (int) $pool[ array_rand( $pool ) ];
	}

	/* --------------------------------------------------------------
	 * Image sizes registered with WordPress so a hero or card can be
	 * served at sane proportions.
	 * -------------------------------------------------------------- */
	public static function init(): void {
		add_action( 'after_setup_theme', [ __CLASS__, 'register_sizes' ] );
		add_action( 'add_meta_boxes_post', function () {
			if ( ! post_type_supports( 'post', 'thumbnail' ) ) {
				add_post_type_support( 'post', 'thumbnail' );
			}
		} );
		add_filter( 'image_size_names_choose', function ( $sizes ) {
			$sizes['citeleap_hero'] = __( 'CiteLeap hero (1600x900)', 'citeleap' );
			$sizes['citeleap_card'] = __( 'CiteLeap card (640x360)', 'citeleap' );
			return $sizes;
		} );

		/* Auto-assign on every save of a post-type=post (Block Editor,
		 * classic editor, Quick Draft, wp_insert_post from CiteLeap,
		 * REST API publish, WP-Cron future->publish). */
		add_action( 'save_post_post',           [ __CLASS__, 'maybe_assign_on_save' ],     20, 3 );
		add_action( 'transition_post_status',   [ __CLASS__, 'maybe_assign_on_transition' ], 20, 3 );

		/* Manual override protection , drop the random flag when an
		 * operator changes the Featured image themselves. */
		add_action( 'updated_post_meta', [ __CLASS__, 'drop_random_flag_if_manual' ], 10, 4 );
		add_action( 'added_post_meta',   [ __CLASS__, 'drop_random_flag_if_manual' ], 10, 4 );

		/* Front-end rendering , scope CSS + hero / card injection. */
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_css' ] );
		add_filter( 'the_content',      [ __CLASS__, 'inject_hero' ], 5 );
		add_filter( 'get_the_excerpt',  [ __CLASS__, 'inject_card' ], 10, 2 );
		add_filter( 'render_block',     [ __CLASS__, 'inject_card_block' ], 10, 2 );

		/* Duplicate-detection: if the theme already renders the
		 * Featured image via core/post-featured-image OR via
		 * the_post_thumbnail in a classic template, skip our injection. */
		add_filter( 'render_block_core/post-featured-image', [ __CLASS__, 'mark_rendered_block' ], 10, 2 );
		add_filter( 'post_thumbnail_html',                   [ __CLASS__, 'mark_rendered_html'  ], 10, 2 );

		/* Admin niceties , gentle nudge when publishing without a
		 * Featured image, plus a thumbnail column on the posts list. */
		add_action( 'admin_notices',                  [ __CLASS__, 'admin_notice_missing_thumb' ] );
		add_filter( 'manage_post_posts_columns',      [ __CLASS__, 'posts_list_column' ] );
		add_action( 'manage_post_posts_custom_column',[ __CLASS__, 'posts_list_column_render' ], 10, 2 );

		/* Bulk operators , Assign / Re-roll buttons on the Images tab. */
		add_action( 'admin_post_citeleap_assign_images', [ __CLASS__, 'handle_bulk_assign' ] );
		add_action( 'admin_post_citeleap_reroll_images', [ __CLASS__, 'handle_bulk_reroll' ] );
	}

	public static function register_sizes(): void {
		if ( ! current_theme_supports( 'post-thumbnails' ) ) {
			add_theme_support( 'post-thumbnails', [ 'post' ] );
		} else {
			add_post_type_support( 'post', 'thumbnail' );
		}
		add_image_size( 'citeleap_hero', 1600, 900, true );
		add_image_size( 'citeleap_card', 640,  360, true );
	}

	public static function maybe_assign_on_save( int $post_id, $post, bool $update ): void {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) return;
		if ( ! $post instanceof WP_Post )           return;
		if ( ! in_array( $post->post_status, [ 'publish', 'future', 'draft', 'pending' ], true ) ) return;
		if ( ! empty( $GLOBALS['citeleap_img_bulk'] ) ) return;
		if ( (int) get_post_thumbnail_id( $post_id ) ) return;

		$s = self::settings();
		if ( ! $s['auto_assign'] ) return;
		$pick = self::pick_random();
		if ( ! $pick ) return;

		$GLOBALS['citeleap_img_bulk'] = true;
		set_post_thumbnail( $post_id, $pick );
		update_post_meta( $post_id, CITELEAP_META_RANDOM_IMG, '1' );
		$GLOBALS['citeleap_img_bulk'] = false;
	}

	public static function maybe_assign_on_transition( string $new, string $old, $post ): void {
		if ( ! $post instanceof WP_Post )           return;
		if ( 'post' !== $post->post_type )          return;
		if ( $new === $old )                        return;
		if ( ! in_array( $new, [ 'publish', 'future' ], true ) ) return;
		self::maybe_assign_on_save( $post->ID, $post, true );
	}

	public static function drop_random_flag_if_manual( int $meta_id, int $post_id, string $meta_key, $meta_value ): void {
		if ( '_thumbnail_id' !== $meta_key ) return;
		if ( ! empty( $GLOBALS['citeleap_img_bulk'] ) ) return;
		delete_post_meta( $post_id, CITELEAP_META_RANDOM_IMG );
	}

	/* ---- duplicate-render guards --------------------------------- */
	private static function mark_rendered( int $post_id ): void {
		if ( ! $post_id ) return;
		$seen = is_array( $GLOBALS['citeleap_img_seen'] ?? null ) ? $GLOBALS['citeleap_img_seen'] : [];
		$seen[ $post_id ] = true;
		$GLOBALS['citeleap_img_seen'] = $seen;
	}
	private static function already_rendered( int $post_id ): bool {
		$seen = is_array( $GLOBALS['citeleap_img_seen'] ?? null ) ? $GLOBALS['citeleap_img_seen'] : [];
		return ! empty( $seen[ $post_id ] );
	}

	public static function mark_rendered_block( $block_content, $block ) {
		$pid = isset( $block['context']['postId'] ) ? (int) $block['context']['postId'] : (int) get_the_ID();
		if ( $pid ) self::mark_rendered( $pid );
		return $block_content;
	}
	public static function mark_rendered_html( $html, $post_id ) {
		if ( $html && $post_id ) self::mark_rendered( (int) $post_id );
		return $html;
	}

	/* ---- front-end rendering ------------------------------------- */
	public static function enqueue_css(): void {
		$path = CITELEAP_DIR . 'assets/blog-post.css';
		if ( ! file_exists( $path ) ) return;
		wp_enqueue_style(
			'citeleap-blog-post',
			CITELEAP_URL . 'assets/blog-post.css',
			[],
			(string) filemtime( $path )
		);
	}

	public static function inject_hero( $content ) {
		$s = self::settings();
		if ( ! $s['render_hero'] ) return $content;
		if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) return $content;
		$pid = (int) get_the_ID();
		$id  = (int) get_post_thumbnail_id( $pid );
		if ( ! $id ) return $content;
		if ( self::already_rendered( $pid ) ) return $content;
		$img = wp_get_attachment_image( $id, 'citeleap_hero', false, [
			'class'         => 'citeleap-hero-img',
			'loading'       => 'eager',
			'fetchpriority' => 'high',
			'decoding'      => 'async',
		] );
		$alt  = (string) get_post_meta( $id, '_wp_attachment_image_alt', true );
		$cap  = wp_get_attachment_caption( $id );
		$cap_html = $cap ? '<figcaption class="citeleap-hero-caption">' . esc_html( $cap ) . '</figcaption>' : '';
		$fig = '<figure class="citeleap-hero" aria-label="' . esc_attr( $alt ?: get_the_title() ) . '">' . $img . $cap_html . '</figure>';
		self::mark_rendered( $pid );
		return $fig . $content;
	}

	public static function inject_card( $excerpt, $post = null ) {
		$s = self::settings();
		if ( ! $s['render_card'] ) return $excerpt;
		if ( is_singular() ) return $excerpt;
		if ( ! in_the_loop() || ! is_main_query() ) return $excerpt;
		$pid = $post ? (int) $post->ID : (int) get_the_ID();
		if ( 'post' !== get_post_type( $pid ) ) return $excerpt;
		if ( self::already_rendered( $pid ) ) return $excerpt;
		$id = (int) get_post_thumbnail_id( $pid );
		if ( ! $id ) return $excerpt;
		self::mark_rendered( $pid );
		$img  = wp_get_attachment_image( $id, 'citeleap_card', false, [
			'class' => 'citeleap-card-img', 'loading' => 'lazy', 'decoding' => 'async',
		] );
		$alt  = (string) get_post_meta( $id, '_wp_attachment_image_alt', true );
		$link = (string) get_permalink( $pid );
		return '<a class="citeleap-card-link" href="' . esc_url( $link ) . '" aria-label="' . esc_attr( $alt ?: get_the_title( $pid ) ) . '">' . $img . '</a>' . $excerpt;
	}

	public static function inject_card_block( $block_content, $block ) {
		if ( empty( $block['blockName'] ) || 'core/post-excerpt' !== $block['blockName'] ) return $block_content;
		if ( is_singular() ) return $block_content;
		$s = self::settings();
		if ( ! $s['render_card'] ) return $block_content;
		global $post;
		$pid = $post ? (int) $post->ID : (int) get_the_ID();
		if ( 'post' !== get_post_type( $pid ) ) return $block_content;
		if ( self::already_rendered( $pid ) ) return $block_content;
		$id = (int) get_post_thumbnail_id( $pid );
		if ( ! $id ) return $block_content;
		self::mark_rendered( $pid );
		$img  = wp_get_attachment_image( $id, 'citeleap_card', false, [
			'class' => 'citeleap-card-img', 'loading' => 'lazy', 'decoding' => 'async',
		] );
		$alt  = (string) get_post_meta( $id, '_wp_attachment_image_alt', true );
		$link = (string) get_permalink( $pid );
		return '<a class="citeleap-card-link" href="' . esc_url( $link ) . '" aria-label="' . esc_attr( $alt ?: get_the_title( $pid ) ) . '">' . $img . '</a>' . $block_content;
	}

	/* --------------------------------------------------------------
	 * Bulk pool operations , walk all published posts and assign a
	 * random image from the pool. Two modes:
	 *   - assign : only posts WITHOUT a Featured image are touched.
	 *   - reroll : only posts previously random-assigned by us get a
	 *              fresh random pick. Manual operator picks survive.
	 * -------------------------------------------------------------- */
	public static function assign_random_bulk( bool $reroll ): int {
		$pool = self::pool();
		if ( empty( $pool ) ) return 0;

		$args = [
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		];
		if ( $reroll ) {
			$args['meta_query'] = [ [ 'key' => CITELEAP_META_RANDOM_IMG, 'value' => '1', 'compare' => '=' ] ];
		} else {
			$args['meta_query'] = [ [ 'key' => '_thumbnail_id', 'compare' => 'NOT EXISTS' ] ];
		}
		$ids = get_posts( $args );
		if ( empty( $ids ) ) return 0;

		$GLOBALS['citeleap_img_bulk'] = true;
		$updated = 0;
		foreach ( $ids as $post_id ) {
			$pick = (int) $pool[ array_rand( $pool ) ];
			if ( ! $pick ) continue;
			set_post_thumbnail( (int) $post_id, $pick );
			update_post_meta( (int) $post_id, CITELEAP_META_RANDOM_IMG, '1' );
			$updated++;
		}
		$GLOBALS['citeleap_img_bulk'] = false;
		return $updated;
	}

	public static function count_posts_total(): int {
		$c = wp_count_posts( 'post' );
		return (int) ( $c->publish ?? 0 );
	}

	public static function count_posts_without_thumbnail(): int {
		$q = get_posts( [
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'meta_query'     => [ [ 'key' => '_thumbnail_id', 'compare' => 'NOT EXISTS' ] ],
		] );
		return count( $q );
	}

	public static function count_random_assigned(): int {
		$q = get_posts( [
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'meta_query'     => [ [ 'key' => CITELEAP_META_RANDOM_IMG, 'value' => '1', 'compare' => '=' ] ],
		] );
		return count( $q );
	}

	/* --------------------------------------------------------------
	 * Bulk admin-post handlers.
	 * -------------------------------------------------------------- */
	public static function handle_bulk_assign(): void {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );
		check_admin_referer( CITELEAP_NONCE );
		$n = self::assign_random_bulk( false );
		wp_safe_redirect( add_query_arg( [ 'page' => 'citeleap', 'tab' => 'images', 'citeleap_msg' => 'assigned', 'n' => $n ], admin_url( 'admin.php' ) ) );
		exit;
	}

	public static function handle_bulk_reroll(): void {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );
		check_admin_referer( CITELEAP_NONCE );
		$n = self::assign_random_bulk( true );
		wp_safe_redirect( add_query_arg( [ 'page' => 'citeleap', 'tab' => 'images', 'citeleap_msg' => 'rerolled', 'n' => $n ], admin_url( 'admin.php' ) ) );
		exit;
	}

	/* --------------------------------------------------------------
	 * Editor nudge: visible info notice if a post is published
	 * without a Featured image and the pool is empty (otherwise
	 * auto-assign already handles it).
	 * -------------------------------------------------------------- */
	public static function admin_notice_missing_thumb(): void {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || 'post' !== $screen->id || 'post' !== $screen->post_type ) return;
		global $post;
		if ( ! $post || 'publish' !== $post->post_status ) return;
		if ( get_post_thumbnail_id( $post->ID ) ) return;
		echo '<div class="notice notice-info"><p><strong>' . esc_html__( 'Tip:', 'citeleap' ) . '</strong> '
			. esc_html__( 'Pick a Featured image in the sidebar, or add images to the CiteLeap pool so this post gets a hero and a card thumbnail automatically.', 'citeleap' )
			. '</p></div>';
	}

	/* --------------------------------------------------------------
	 * Posts list table , thumbnail column with (random)/(manual) tag.
	 * -------------------------------------------------------------- */
	public static function posts_list_column( array $cols ): array {
		$new = [];
		foreach ( $cols as $k => $v ) {
			$new[ $k ] = $v;
			if ( 'title' === $k ) {
				$new['citeleap_thumb'] = __( 'Image', 'citeleap' );
			}
		}
		return $new;
	}

	public static function posts_list_column_render( string $column, int $post_id ): void {
		if ( 'citeleap_thumb' !== $column ) return;
		$id = (int) get_post_thumbnail_id( $post_id );
		if ( ! $id ) {
			echo '<span style="color:#9ca3af;">' . esc_html__( '— none', 'citeleap' ) . '</span>';
			return;
		}
		$src = wp_get_attachment_image_src( $id, 'thumbnail' );
		if ( ! $src ) return;
		$is_random = '1' === get_post_meta( $post_id, CITELEAP_META_RANDOM_IMG, true );
		echo '<img src="' . esc_url( $src[0] ) . '" alt="" style="display:block;width:60px;height:40px;object-fit:cover;border-radius:4px;">';
		if ( $is_random ) {
			echo '<span style="display:inline-block;margin-top:2px;font-size:11px;color:#0369a1;">' . esc_html__( '(random)', 'citeleap' ) . '</span>';
		} else {
			echo '<span style="display:inline-block;margin-top:2px;font-size:11px;color:#16a34a;">' . esc_html__( '(manual)', 'citeleap' ) . '</span>';
		}
	}

	/** Helper for seo.php , best image URL for og:image. */
	public static function og_image_for_post( int $post_id ): array {
		$id = (int) get_post_thumbnail_id( $post_id );
		if ( ! $id ) {
			/* Fallback: first image in content. */
			$content = (string) get_post_field( 'post_content', $post_id );
			if ( preg_match( '/<img[^>]+src=["\']([^"\']+)["\']/i', $content, $m ) ) {
				return [ 'url' => esc_url_raw( $m[1] ), 'width' => 1200, 'height' => 630, 'alt' => '' ];
			}
			/* Fallback to pool random if nothing is set. */
			$id = self::pick_random();
			if ( ! $id ) return [ 'url' => '', 'width' => 0, 'height' => 0, 'alt' => '' ];
		}
		$src = wp_get_attachment_image_src( $id, 'citeleap_hero' );
		if ( ! $src ) $src = wp_get_attachment_image_src( $id, 'full' );
		if ( ! $src ) return [ 'url' => '', 'width' => 0, 'height' => 0, 'alt' => '' ];
		return [
			'url'    => (string) $src[0],
			'width'  => (int) $src[1],
			'height' => (int) $src[2],
			'alt'    => (string) get_post_meta( $id, '_wp_attachment_image_alt', true ),
		];
	}
}

CiteLeap_Images::init();
