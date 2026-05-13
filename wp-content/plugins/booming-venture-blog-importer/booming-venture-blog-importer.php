<?php
/**
 * Plugin Name:       Booming Venture Blog Importer
 * Plugin URI:        https://boomingventure.com
 * Description:       Adds OR refreshes the Booming Venture long-form blog posts (83 articles, every one 1,200 to 1,969 words, refreshed for May 2026). Strictly blog posts only. Does NOT change pages, services, menus, theme settings, permalinks, the front page, or any post meta. Two buttons: Add new posts, and Refresh existing posts.
 * Version:           1.0.1
 * Requires at least: 6.6
 * Requires PHP:      8.0
 * Author:            Booming Venture
 * Author URI:        https://boomingventure.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       booming-venture-blog-importer
 * Domain Path:       /languages
 * Update URI:        false
 *
 * @package BoomingVentureBlogImporter
 */

defined( 'ABSPATH' ) || exit;

const BVBI_VERSION  = '1.0.1';
const BVBI_FLAG     = 'bvbi_last_run';
const BVBI_LAST_LOG = 'bvbi_last_log';
const BVBI_NONCE    = 'bvbi_run';

if ( ! defined( 'BVBI_PLUGIN_FILE' ) ) {
	define( 'BVBI_PLUGIN_FILE', __FILE__ );
}
if ( ! defined( 'BVBI_PLUGIN_DIR' ) ) {
	define( 'BVBI_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

/* ---------------------------------------------------------------------
 * Activation , PHP + ext-simplexml capability check + uninstall reg.
 * --------------------------------------------------------------------- */
register_activation_hook( BVBI_PLUGIN_FILE, 'bvbi_on_activate' );
function bvbi_on_activate(): void {
	if ( version_compare( PHP_VERSION, '8.0', '<' ) ) {
		deactivate_plugins( plugin_basename( BVBI_PLUGIN_FILE ) );
		wp_die(
			esc_html__( 'Booming Venture Blog Importer requires PHP 8.0 or higher.', 'booming-venture-blog-importer' ),
			esc_html__( 'Plugin activation error', 'booming-venture-blog-importer' ),
			[ 'back_link' => true ]
		);
	}
	if ( ! function_exists( 'simplexml_load_file' ) ) {
		deactivate_plugins( plugin_basename( BVBI_PLUGIN_FILE ) );
		wp_die(
			esc_html__( 'Booming Venture Blog Importer requires the PHP ext-simplexml extension. Ask your host to enable it.', 'booming-venture-blog-importer' ),
			esc_html__( 'Plugin activation error', 'booming-venture-blog-importer' ),
			[ 'back_link' => true ]
		);
	}
	register_uninstall_hook( BVBI_PLUGIN_FILE, 'bvbi_on_uninstall' );
}

function bvbi_on_uninstall(): void {
	if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) return;
	if ( ! current_user_can( 'activate_plugins' ) ) return;
	delete_option( BVBI_FLAG );
	delete_option( BVBI_LAST_LOG );
}

/* ---------------------------------------------------------------------
 * i18n
 * --------------------------------------------------------------------- */
add_action( 'init', function () {
	load_plugin_textdomain( 'booming-venture-blog-importer', false, dirname( plugin_basename( BVBI_PLUGIN_FILE ) ) . '/languages' );
} );

/* ---------------------------------------------------------------------
 * Front-end , enqueue mobile-first blog stylesheet on single post views
 * and wrap the_content in a .bvbi-post container so the CSS scopes
 * cleanly without touching the theme.
 * --------------------------------------------------------------------- */
add_action( 'wp_enqueue_scripts', function () {
	if ( ! is_singular( 'post' ) ) return;
	$css_path = BVBI_PLUGIN_DIR . 'assets/blog-post.css';
	$css_url  = plugins_url( 'assets/blog-post.css', BVBI_PLUGIN_FILE );
	$ver      = file_exists( $css_path ) ? (string) filemtime( $css_path ) : BVBI_VERSION;
	wp_enqueue_style( 'bvbi-blog-post', $css_url, [], $ver );
} );

add_filter( 'the_content', function ( $content ) {
	if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) return $content;
	return '<div class="bvbi-post">' . $content . '</div>';
}, 99 );

/* ---------------------------------------------------------------------
 * Tools -> Blog Importer admin page
 * --------------------------------------------------------------------- */
add_action( 'admin_menu', function () {
	add_management_page(
		__( 'Booming Venture Blog Importer', 'booming-venture-blog-importer' ),
		__( 'Booming Venture Blog Importer', 'booming-venture-blog-importer' ),
		'manage_options',
		'booming-venture-blog-importer',
		'bvbi_render_admin_page'
	);
} );

function bvbi_render_admin_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to access this page.', 'booming-venture-blog-importer' ) );
	}
	$last_run  = (string) get_option( BVBI_FLAG, __( 'never', 'booming-venture-blog-importer' ) );
	$log       = (array) get_option( BVBI_LAST_LOG, [] );
	$wxr_path  = BVBI_PLUGIN_DIR . 'import/blog-posts.xml';
	$wxr_ok    = file_exists( $wxr_path );
	$published = (int) wp_count_posts( 'post' )->publish;
	?>
	<div class="wrap">
		<h1><?php echo esc_html__( 'Booming Venture Blog Importer', 'booming-venture-blog-importer' ); ?></h1>

		<div class="notice" style="border-left:4px solid #0284c7;padding:1rem 1.25rem;background:#f0f9ff;">
			<h2 style="margin:0 0 0.5rem;font-size:18px;"><?php echo esc_html__( 'Status', 'booming-venture-blog-importer' ); ?></h2>
			<table class="form-table" style="margin:0;">
				<tr>
					<th style="width:240px;"><?php echo esc_html__( 'Bundled blog WXR file', 'booming-venture-blog-importer' ); ?></th>
					<td>
						<?php if ( $wxr_ok ) : ?>
							<span style="color:#16a34a">&#10003; <?php echo esc_html__( 'found', 'booming-venture-blog-importer' ); ?></span>
							at <code><?php echo esc_html( wp_make_link_relative( $wxr_path ) ); ?></code>
						<?php else : ?>
							<span style="color:#b91c1c">&#10007; <?php echo esc_html__( 'MISSING', 'booming-venture-blog-importer' ); ?></span>
						<?php endif; ?>
					</td>
				</tr>
				<tr><th><?php echo esc_html__( 'Blog posts published now', 'booming-venture-blog-importer' ); ?></th><td><strong><?php echo (int) $published; ?></strong> <?php echo esc_html__( '(83 ship in the bundled WXR)', 'booming-venture-blog-importer' ); ?></td></tr>
				<tr><th><?php echo esc_html__( 'Last action', 'booming-venture-blog-importer' ); ?></th><td><strong><?php echo esc_html( $last_run ); ?></strong></td></tr>
			</table>
		</div>

		<?php if ( ! empty( $log ) ) : ?>
			<div class="notice" style="border-left:4px solid #16a34a;padding:1rem 1.25rem;background:#f0fdf4;">
				<h2 style="margin:0 0 0.5rem;font-size:18px;"><?php echo esc_html__( 'Last result', 'booming-venture-blog-importer' ); ?></h2>
				<ul style="margin:0 0 0 1rem;font-size:13px;">
				<?php foreach ( $log as $line ) : ?>
					<li><?php echo esc_html( (string) $line ); ?></li>
				<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<h2 style="margin-top:1.5rem;"><?php echo esc_html__( 'Add new blog posts only', 'booming-venture-blog-importer' ); ?></h2>
		<p>
			<?php echo wp_kses(
				__( 'Inserts every bundled blog post whose slug does <strong>not</strong> already exist on this site. Skips matching slugs. Categories from the bundled WXR are created if missing. Pages, services, menus, permalinks, and the front page are <strong>not touched</strong>.', 'booming-venture-blog-importer' ),
				[ 'strong' => [] ]
			); ?>
		</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( BVBI_NONCE ); ?>
			<input type="hidden" name="action" value="bvbi_add">
			<button type="submit" class="button button-primary button-hero"><?php echo esc_html__( 'Add new blog posts', 'booming-venture-blog-importer' ); ?></button>
		</form>

		<h2 style="margin-top:2rem;"><?php echo esc_html__( 'Refresh existing blog post bodies', 'booming-venture-blog-importer' ); ?></h2>
		<div class="notice" style="border-left:4px solid #f59e0b;padding:0.75rem 1rem;background:#fffbeb;">
			<p style="margin:0;">
				<strong><?php echo esc_html__( 'Destructive on blog posts:', 'booming-venture-blog-importer' ); ?></strong>
				<?php echo esc_html__( 'Overwrites the title, content, and excerpt of every existing blog post whose slug matches the bundled WXR. Use this to upgrade old short posts to the new long-form May 2026 versions. Pages, services, post meta, categories, dates, authors are untouched. There is no undo.', 'booming-venture-blog-importer' ); ?>
			</p>
		</div>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:0.75rem;" onsubmit="return confirm('<?php echo esc_js( __( 'Overwrite the title, content, and excerpt of every matching blog post with the bundled long-form version? There is no undo.', 'booming-venture-blog-importer' ) ); ?>');">
			<?php wp_nonce_field( BVBI_NONCE ); ?>
			<input type="hidden" name="action" value="bvbi_refresh">
			<button type="submit" class="button button-secondary"><?php echo esc_html__( 'Refresh existing posts from bundled WXR', 'booming-venture-blog-importer' ); ?></button>
		</form>

		<h2 style="margin-top:2rem;"><?php echo esc_html__( 'Add + refresh in one shot', 'booming-venture-blog-importer' ); ?></h2>
		<p><?php echo esc_html__( 'Runs both actions in sequence: add any missing posts, then refresh every existing post body. This is the fastest path to "every post matches the bundled long-form version".', 'booming-venture-blog-importer' ); ?></p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('<?php echo esc_js( __( 'Add missing posts AND overwrite every existing post body with the bundled long-form version. There is no undo. Proceed?', 'booming-venture-blog-importer' ) ); ?>');">
			<?php wp_nonce_field( BVBI_NONCE ); ?>
			<input type="hidden" name="action" value="bvbi_sync">
			<button type="submit" class="button button-primary"><?php echo esc_html__( 'Sync all (add + refresh)', 'booming-venture-blog-importer' ); ?></button>
		</form>
	</div>
	<?php
}

/* ---------------------------------------------------------------------
 * admin-post handlers , three modes: add, refresh, sync.
 * --------------------------------------------------------------------- */
add_action( 'admin_post_bvbi_add',     'bvbi_handle_add' );
add_action( 'admin_post_bvbi_refresh', 'bvbi_handle_refresh' );
add_action( 'admin_post_bvbi_sync',    'bvbi_handle_sync' );

function bvbi_handle_add(): void     { bvbi_dispatch( 'add' ); }
function bvbi_handle_refresh(): void { bvbi_dispatch( 'refresh' ); }
function bvbi_handle_sync(): void    { bvbi_dispatch( 'sync' ); }

function bvbi_dispatch( string $mode ): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Insufficient permission.', 'booming-venture-blog-importer' ), 403 );
	}
	check_admin_referer( BVBI_NONCE );

	$wxr = BVBI_PLUGIN_DIR . 'import/blog-posts.xml';
	$log = bvbi_import_blog_posts( $wxr, $mode );
	update_option( BVBI_LAST_LOG, $log, false );
	update_option( BVBI_FLAG, $mode . ' @ ' . current_time( 'mysql' ), false );

	wp_safe_redirect( add_query_arg( [ 'page' => 'booming-venture-blog-importer', 'bvbi_done' => $mode ], admin_url( 'tools.php' ) ) );
	exit;
}

/* ---------------------------------------------------------------------
 * Core importer , BLOG POSTS ONLY.
 *   $mode == 'add'      , insert posts whose slug is missing, skip existing
 *   $mode == 'refresh'  , overwrite title + content + excerpt for existing
 *                          slugs, do not insert anything new
 *   $mode == 'sync'     , both: insert missing AND overwrite existing
 *
 * What this function will NEVER touch:
 *   , pages, services, menus, navigation
 *   , permalink structure, front-page settings, posts-page settings
 *   , widgets, themes, options unrelated to this plugin
 *   , post meta on existing posts
 *   , post categories on existing posts
 *   , post date, author, comment status on existing posts
 *
 * Returns an array of log lines.
 * --------------------------------------------------------------------- */
function bvbi_import_blog_posts( string $wxr_path, string $mode ): array {
	$log = [];
	$log[] = 'Mode: ' . $mode;

	if ( ! file_exists( $wxr_path ) ) {
		$log[] = '✗ Bundled WXR file not found at: ' . $wxr_path;
		return $log;
	}
	if ( ! function_exists( 'simplexml_load_file' ) ) {
		$log[] = '✗ PHP ext-simplexml is not installed.';
		return $log;
	}
	if ( ! in_array( $mode, [ 'add', 'refresh', 'sync' ], true ) ) {
		$log[] = '✗ Unknown mode: ' . $mode;
		return $log;
	}

	@set_time_limit( 180 );

	libxml_use_internal_errors( true );
	$xml = simplexml_load_file( $wxr_path );
	if ( ! $xml ) {
		$errors = array_map( fn( $e ) => trim( $e->message ), libxml_get_errors() );
		$log[]  = '✗ WXR parse failed: ' . implode( ' | ', $errors );
		return $log;
	}

	$ns_wp      = 'http://wordpress.org/export/1.2/';
	$ns_content = 'http://purl.org/rss/1.0/modules/content/';
	$ns_excerpt = 'http://wordpress.org/export/1.2/excerpt/';

	$channel = $xml->channel;

	/* Categories used by bundled posts. Create only the missing ones. */
	$cat_map = [];
	foreach ( $channel->children( $ns_wp )->category as $c ) {
		$slug = sanitize_title( (string) $c->category_nicename );
		$name = sanitize_text_field( (string) $c->cat_name );
		if ( ! $slug || ! $name ) continue;
		$existing = get_term_by( 'slug', $slug, 'category' );
		if ( $existing ) {
			$cat_map[ $slug ] = (int) $existing->term_id;
			continue;
		}
		$result = wp_insert_term( $name, 'category', [ 'slug' => $slug ] );
		if ( ! is_wp_error( $result ) ) {
			$cat_map[ $slug ] = (int) $result['term_id'];
		}
	}
	$log[] = '✓ Categories available: ' . count( $cat_map );

	$inserted = 0;
	$refreshed = 0;
	$skipped = 0;
	$errors = 0;

	foreach ( $channel->item as $item ) {
		$wp_node      = $item->children( $ns_wp );
		$content_node = $item->children( $ns_content );
		$excerpt_node = $item->children( $ns_excerpt );

		$post_type   = (string) $wp_node->post_type;
		$post_status = (string) $wp_node->status;

		if ( 'post' !== $post_type || 'publish' !== $post_status ) continue;

		$slug      = sanitize_title( (string) $wp_node->post_name );
		$title     = sanitize_text_field( (string) $item->title );
		$content   = (string) $content_node->encoded;
		$excerpt   = (string) $excerpt_node->encoded;
		$post_date = (string) $wp_node->post_date;

		if ( ! $slug ) continue;

		$existing = get_page_by_path( $slug, OBJECT, 'post' );

		/* Path 1: post exists. */
		if ( $existing && $existing->ID ) {
			if ( 'add' === $mode ) {
				$skipped++;
				continue;
			}
			/* refresh or sync , update body only */
			try {
				$ok = wp_update_post( [
					'ID'           => (int) $existing->ID,
					'post_title'   => $title,
					'post_content' => wp_kses_post( $content ),
					'post_excerpt' => wp_kses_post( $excerpt ),
				], true );
				if ( is_wp_error( $ok ) ) {
					$errors++;
				} else {
					$refreshed++;
				}
			} catch ( \Throwable $e ) {
				$errors++;
			}
			continue;
		}

		/* Path 2: post is new. */
		if ( 'refresh' === $mode ) {
			$skipped++;
			continue;
		}
		/* add or sync , insert */
		$post_id = wp_insert_post( [
			'post_title'     => $title,
			'post_name'      => $slug,
			'post_content'   => wp_kses_post( $content ),
			'post_excerpt'   => wp_kses_post( $excerpt ),
			'post_status'    => 'publish',
			'post_type'      => 'post',
			'post_date'      => $post_date ?: current_time( 'mysql' ),
			'post_author'    => get_current_user_id() ?: 1,
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
		], true );
		if ( is_wp_error( $post_id ) ) {
			$errors++;
			continue;
		}

		/* Assign categories from the bundled WXR. */
		$cat_ids = [];
		foreach ( $item->category as $c ) {
			$nicename = sanitize_title( (string) $c['nicename'] );
			if ( isset( $cat_map[ $nicename ] ) ) {
				$cat_ids[] = $cat_map[ $nicename ];
			}
		}
		if ( $cat_ids ) wp_set_post_categories( $post_id, $cat_ids );
		$inserted++;
	}

	if ( function_exists( 'wp_cache_flush' ) ) wp_cache_flush();
	if ( function_exists( 'opcache_reset' ) ) @opcache_reset();

	$log[] = '─── Summary ───';
	$log[] = 'Inserted: ' . $inserted;
	$log[] = 'Refreshed: ' . $refreshed;
	$log[] = 'Skipped: ' . $skipped;
	$log[] = 'Errors: ' . $errors;

	return $log;
}

/* ---------------------------------------------------------------------
 * Admin notices on return from a run.
 * --------------------------------------------------------------------- */
add_action( 'admin_notices', function () {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'tools_page_booming-venture-blog-importer' !== $screen->id ) return;

	if ( isset( $_GET['bvbi_done'] ) ) {
		$mode = sanitize_text_field( wp_unslash( (string) $_GET['bvbi_done'] ) );
		$titles = [
			'add'     => __( 'New posts added.', 'booming-venture-blog-importer' ),
			'refresh' => __( 'Existing post bodies refreshed.', 'booming-venture-blog-importer' ),
			'sync'    => __( 'Sync complete , added missing + refreshed existing.', 'booming-venture-blog-importer' ),
		];
		$title = $titles[ $mode ] ?? __( 'Done.', 'booming-venture-blog-importer' );
		printf(
			'<div class="notice notice-success is-dismissible"><p><strong>%s</strong> %s</p></div>',
			esc_html( $title ),
			wp_kses(
				/* translators: %s: blog page URL */
				sprintf( __( 'See the result log on this page. Visit <a href="%s">/blog/</a> to verify.', 'booming-venture-blog-importer' ), esc_url( home_url( '/blog/' ) ) ),
				[ 'a' => [ 'href' => [] ] ]
			)
		);
	}
} );

/* ---------------------------------------------------------------------
 * Plugin row action: quick link to the import page.
 * --------------------------------------------------------------------- */
add_filter( 'plugin_action_links_' . plugin_basename( BVBI_PLUGIN_FILE ), function ( $links ) {
	$import_url = admin_url( 'tools.php?page=booming-venture-blog-importer' );
	array_unshift( $links, '<a href="' . esc_url( $import_url ) . '">' . esc_html__( 'Open', 'booming-venture-blog-importer' ) . '</a>' );
	return $links;
} );
