<?php
/**
 * Plugin Name:       Booming Venture Importer
 * Plugin URI:        https://boomingventure.com
 * Description:       One-click importer for the Booming Venture demo content (13 pages, 4 services, 83 blog posts, categories, menus). Bundled WXR is the source of truth. Re-runs are safe: existing slugs are skipped, structural pages get content refreshed. Adds Tools → Booming Venture Importer.
 * Version:           1.0.0
 * Requires at least: 6.6
 * Requires PHP:      8.0
 * Author:            Booming Venture
 * Author URI:        https://boomingventure.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       booming-venture-importer
 * Domain Path:       /languages
 * Update URI:        false
 *
 * @package BoomingVentureImporter
 */

defined( 'ABSPATH' ) || exit;

const BVI_VERSION  = '1.0.0';
const BVI_FLAG     = 'bvi_content_imported';
const BVI_LAST_LOG = 'bvi_last_import_log';
const BVI_NONCE    = 'bvi_run_import';

if ( ! defined( 'BVI_PLUGIN_FILE' ) ) {
	define( 'BVI_PLUGIN_FILE', __FILE__ );
}
if ( ! defined( 'BVI_PLUGIN_DIR' ) ) {
	define( 'BVI_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

/* ---------------------------------------------------------------------
 * Activation: register only. Defer permalink + capability work to first
 * page load so register_activation_hook stays cheap (Plugin Handbook
 * best practice: never write options inside the activation callback
 * unless they will be read on every request).
 * --------------------------------------------------------------------- */
register_activation_hook( BVI_PLUGIN_FILE, 'bvi_on_activate' );
function bvi_on_activate(): void {
	if ( version_compare( PHP_VERSION, '8.0', '<' ) ) {
		deactivate_plugins( plugin_basename( BVI_PLUGIN_FILE ) );
		wp_die(
			esc_html__( 'Booming Venture Importer requires PHP 8.0 or higher.', 'booming-venture-importer' ),
			esc_html__( 'Plugin activation error', 'booming-venture-importer' ),
			[ 'back_link' => true ]
		);
	}
	if ( ! function_exists( 'simplexml_load_file' ) ) {
		deactivate_plugins( plugin_basename( BVI_PLUGIN_FILE ) );
		wp_die(
			esc_html__( 'Booming Venture Importer requires the PHP ext-simplexml extension. Ask your host to enable it.', 'booming-venture-importer' ),
			esc_html__( 'Plugin activation error', 'booming-venture-importer' ),
			[ 'back_link' => true ]
		);
	}
	/* Register the uninstall hook here so it is written to wp_options
	 * only once (Plugin Handbook: never call register_uninstall_hook
	 * on every page load). */
	register_uninstall_hook( BVI_PLUGIN_FILE, 'bvi_on_uninstall' );
}

/* register_uninstall_hook must be available without loading the plugin
 * at uninstall time, so the callback is defined at the top level here. */
function bvi_on_uninstall(): void {
	if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) return;
	if ( ! current_user_can( 'activate_plugins' ) ) return;
	delete_option( BVI_FLAG );
	delete_option( BVI_LAST_LOG );
}

/* ---------------------------------------------------------------------
 * i18n
 * --------------------------------------------------------------------- */
add_action( 'init', function () {
	load_plugin_textdomain( 'booming-venture-importer', false, dirname( plugin_basename( BVI_PLUGIN_FILE ) ) . '/languages' );
} );

/* ---------------------------------------------------------------------
 * Tools -> Booming Venture Importer admin page
 * --------------------------------------------------------------------- */
add_action( 'admin_menu', function () {
	add_management_page(
		__( 'Booming Venture Importer', 'booming-venture-importer' ),
		__( 'Booming Venture Importer', 'booming-venture-importer' ),
		'manage_options',
		'booming-venture-importer',
		'bvi_render_admin_page'
	);
} );

function bvi_render_admin_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to access this page.', 'booming-venture-importer' ) );
	}
	$flag         = (string) get_option( BVI_FLAG, __( 'not run yet', 'booming-venture-importer' ) );
	$log          = (array) get_option( BVI_LAST_LOG, [] );
	$pages_obj    = wp_count_posts( 'page' );
	$posts_obj    = wp_count_posts( 'post' );
	$pages        = $pages_obj ? (int) $pages_obj->publish : 0;
	$posts        = $posts_obj ? (int) $posts_obj->publish : 0;
	$services_obj = post_type_exists( 'service' ) ? wp_count_posts( 'service' ) : null;
	$services     = $services_obj ? (int) $services_obj->publish : 0;
	$wxr_path     = BVI_PLUGIN_DIR . 'import/booming-venture-content.xml';
	$wxr_ok       = file_exists( $wxr_path );
	$max_upload   = size_format( wp_max_upload_size() );
	?>
	<div class="wrap">
		<h1><?php echo esc_html__( 'Booming Venture Importer', 'booming-venture-importer' ); ?></h1>

		<div class="notice" style="border-left:4px solid #0284c7;padding:1rem 1.25rem;background:#f0f9ff;">
			<h2 style="margin:0 0 0.5rem;font-size:18px;"><?php echo esc_html__( 'Content status', 'booming-venture-importer' ); ?></h2>
			<table class="form-table" style="margin:0;">
				<tr>
					<th style="width:240px;"><?php echo esc_html__( 'Bundled WXR file', 'booming-venture-importer' ); ?></th>
					<td>
						<?php if ( $wxr_ok ) : ?>
							<span style="color:#16a34a">&#10003; <?php echo esc_html__( 'found', 'booming-venture-importer' ); ?></span>
							at <code><?php echo esc_html( wp_make_link_relative( $wxr_path ) ); ?></code>
						<?php else : ?>
							<span style="color:#b91c1c">&#10007; <?php echo esc_html__( 'MISSING', 'booming-venture-importer' ); ?></span>
						<?php endif; ?>
					</td>
				</tr>
				<tr><th><?php echo esc_html__( 'Pages published', 'booming-venture-importer' ); ?></th><td><strong><?php echo (int) $pages; ?></strong> <?php echo esc_html__( '(expect 13 or more)', 'booming-venture-importer' ); ?></td></tr>
				<tr><th><?php echo esc_html__( 'Blog posts published', 'booming-venture-importer' ); ?></th><td><strong><?php echo (int) $posts; ?></strong> <?php echo esc_html__( '(expect 83 after import)', 'booming-venture-importer' ); ?></td></tr>
				<tr><th><?php echo esc_html__( 'Services published', 'booming-venture-importer' ); ?></th><td><strong><?php echo (int) $services; ?></strong> <?php echo esc_html__( '(expect 4)', 'booming-venture-importer' ); ?></td></tr>
				<tr><th><?php echo esc_html__( 'Last import version', 'booming-venture-importer' ); ?></th><td><strong><?php echo esc_html( $flag ); ?></strong></td></tr>
			</table>
		</div>

		<?php if ( ! empty( $log ) ) : ?>
			<div class="notice" style="border-left:4px solid #16a34a;padding:1rem 1.25rem;background:#f0fdf4;">
				<h2 style="margin:0 0 0.5rem;font-size:18px;"><?php echo esc_html__( 'Last import result', 'booming-venture-importer' ); ?></h2>
				<ul style="margin:0 0 0 1rem;font-size:13px;">
				<?php foreach ( $log as $line ) : ?>
					<li><?php echo esc_html( (string) $line ); ?></li>
				<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<h2 style="margin-top:1.5rem;"><?php echo esc_html__( 'Run the import', 'booming-venture-importer' ); ?></h2>
		<p>
			<?php echo wp_kses(
				__( 'The bundled WXR contains <strong>13 pages</strong>, <strong>4 services</strong>, and <strong>83 blog posts</strong>. Existing posts and pages with the same slug are <strong>skipped</strong> (your edits are safe). Structural pages get their content refreshed.', 'booming-venture-importer' ),
				[ 'strong' => [] ]
			); ?>
		</p>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:1rem;">
			<?php wp_nonce_field( BVI_NONCE ); ?>
			<input type="hidden" name="action" value="bvi_run_import">
			<button type="submit" class="button button-primary button-hero"><?php echo esc_html__( 'Import / Re-import content', 'booming-venture-importer' ); ?></button>
		</form>

		<h2 style="margin-top:2rem;"><?php echo esc_html__( 'Or upload an alternative WXR file', 'booming-venture-importer' ); ?></h2>
		<p>
			<?php
			printf(
				/* translators: %s: max upload size formatted */
				esc_html__( 'If you have a customised WXR (max upload size %s), upload it here. The bundled WXR is replaced for this import only.', 'booming-venture-importer' ),
				esc_html( $max_upload )
			);
			?>
		</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
			<?php wp_nonce_field( BVI_NONCE ); ?>
			<input type="hidden" name="action" value="bvi_upload_wxr">
			<input type="file" name="wxr_file" accept=".xml,application/xml,text/xml" required>
			<button type="submit" class="button"><?php echo esc_html__( 'Upload and import', 'booming-venture-importer' ); ?></button>
		</form>
	</div>
	<?php
}

/* ---------------------------------------------------------------------
 * admin-post handlers, both gated by capability + nonce.
 * --------------------------------------------------------------------- */
add_action( 'admin_post_bvi_run_import', 'bvi_handle_run_import' );
function bvi_handle_run_import(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Insufficient permission.', 'booming-venture-importer' ), 403 );
	}
	check_admin_referer( BVI_NONCE );

	$wxr = BVI_PLUGIN_DIR . 'import/booming-venture-content.xml';
	$log = bvi_import_wxr( $wxr );
	update_option( BVI_LAST_LOG, $log, false );

	wp_safe_redirect( add_query_arg( [ 'page' => 'booming-venture-importer', 'bvi_imported' => 1 ], admin_url( 'tools.php' ) ) );
	exit;
}

add_action( 'admin_post_bvi_upload_wxr', 'bvi_handle_upload_wxr' );
function bvi_handle_upload_wxr(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Insufficient permission.', 'booming-venture-importer' ), 403 );
	}
	check_admin_referer( BVI_NONCE );

	if ( empty( $_FILES['wxr_file']['tmp_name'] ) || ! is_uploaded_file( $_FILES['wxr_file']['tmp_name'] ) ) {
		wp_safe_redirect( add_query_arg( [ 'page' => 'booming-venture-importer', 'bvi_error' => 'nofile' ], admin_url( 'tools.php' ) ) );
		exit;
	}

	/* Validate file type strictly: accept only XML. */
	$file = $_FILES['wxr_file'];
	$check = wp_check_filetype_and_ext(
		(string) $file['tmp_name'],
		(string) $file['name'],
		[ 'xml' => 'application/xml', 'xml-text' => 'text/xml' ]
	);
	if ( empty( $check['ext'] ) || 'xml' !== $check['ext'] ) {
		wp_safe_redirect( add_query_arg( [ 'page' => 'booming-venture-importer', 'bvi_error' => 'badtype' ], admin_url( 'tools.php' ) ) );
		exit;
	}

	$tmp = (string) $file['tmp_name'];
	$log = bvi_import_wxr( $tmp );
	update_option( BVI_LAST_LOG, $log, false );

	wp_safe_redirect( add_query_arg( [ 'page' => 'booming-venture-importer', 'bvi_imported' => 1 ], admin_url( 'tools.php' ) ) );
	exit;
}

/* ---------------------------------------------------------------------
 * Core importer , self-contained, no external dependencies.
 * Parses the WXR with SimpleXML. Skips existing slugs (idempotent).
 * Refreshes structural-page content on every run.
 * Returns an array of log lines for the admin UI.
 * --------------------------------------------------------------------- */
function bvi_import_wxr( string $wxr_path ): array {
	$log = [];

	if ( ! file_exists( $wxr_path ) ) {
		$log[] = '✗ WXR file not found at: ' . $wxr_path;
		return $log;
	}
	if ( ! function_exists( 'simplexml_load_file' ) ) {
		$log[] = '✗ PHP ext-simplexml is not installed on this host. Ask your host to enable it.';
		return $log;
	}

	@set_time_limit( 180 );

	update_option( 'permalink_structure', '/blog/%postname%/' );
	$log[] = '✓ Permalink structure set to /blog/%postname%/';

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

	/* Categories. */
	$cat_map      = [];
	$cat_inserted = 0;
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
			$cat_inserted++;
		}
	}
	$log[] = '✓ Categories: ' . count( $cat_map ) . ' total (inserted ' . $cat_inserted . ')';

	$structural_slugs = [
		'home', 'about', 'services', 'blog', 'unify-framework',
		'funnel-calculator', 'roi-forecaster',
		'free-growth-guide', 'boardroom-quickscan', 'head-of-growth',
	];

	$counts = [
		'page_inserted'    => 0,
		'page_refreshed'   => 0,
		'page_skipped'     => 0,
		'post_inserted'    => 0,
		'post_skipped'     => 0,
		'service_inserted' => 0,
		'service_skipped'  => 0,
		'errors'           => 0,
	];

	foreach ( $channel->item as $item ) {
		$wp_node      = $item->children( $ns_wp );
		$content_node = $item->children( $ns_content );
		$excerpt_node = $item->children( $ns_excerpt );

		$post_type   = (string) $wp_node->post_type;
		$post_status = (string) $wp_node->status;
		$slug        = sanitize_title( (string) $wp_node->post_name );
		$title       = sanitize_text_field( (string) $item->title );
		$content     = (string) $content_node->encoded;
		$excerpt     = (string) $excerpt_node->encoded;
		$post_date   = (string) $wp_node->post_date;
		$template    = '';

		if ( ! $post_type || ! $slug || 'publish' !== $post_status ) continue;
		if ( ! in_array( $post_type, [ 'page', 'post', 'service', 'landing_page', 'case_study' ], true ) ) continue;

		if ( 'page' === $post_type ) {
			$content = bvi_expand_pattern_refs( $content );
		}

		foreach ( $wp_node->postmeta as $meta ) {
			if ( '_wp_page_template' === (string) $meta->meta_key ) {
				$template = (string) $meta->meta_value;
			}
		}

		$is_structural = ( 'page' === $post_type ) && in_array( $slug, $structural_slugs, true );
		$existing      = get_page_by_path( $slug, OBJECT, $post_type );

		if ( $existing ) {
			if ( $is_structural && $existing->ID ) {
				try {
					wp_update_post( [
						'ID'           => $existing->ID,
						'post_content' => wp_kses_post( $content ),
						'post_excerpt' => wp_kses_post( $excerpt ),
					] );
					if ( $template ) update_post_meta( $existing->ID, '_wp_page_template', sanitize_text_field( $template ) );
					$counts['page_refreshed']++;
				} catch ( \Throwable $e ) {
					$counts['errors']++;
				}
			} else {
				$counts[ $post_type . '_skipped' ]++;
			}
			continue;
		}

		$post_id = wp_insert_post( [
			'post_title'     => $title,
			'post_name'      => $slug,
			'post_content'   => wp_kses_post( $content ),
			'post_excerpt'   => wp_kses_post( $excerpt ),
			'post_status'    => 'publish',
			'post_type'      => $post_type,
			'post_date'      => $post_date ?: current_time( 'mysql' ),
			'post_author'    => get_current_user_id() ?: 1,
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
		], true );

		if ( is_wp_error( $post_id ) ) {
			$counts['errors']++;
			continue;
		}

		if ( $template ) {
			update_post_meta( $post_id, '_wp_page_template', sanitize_text_field( $template ) );
		}

		if ( 'post' === $post_type ) {
			$cat_ids = [];
			foreach ( $item->category as $c ) {
				$nicename = sanitize_title( (string) $c['nicename'] );
				if ( isset( $cat_map[ $nicename ] ) ) {
					$cat_ids[] = $cat_map[ $nicename ];
				}
			}
			if ( $cat_ids ) wp_set_post_categories( $post_id, $cat_ids );
		}

		foreach ( $wp_node->postmeta as $meta ) {
			$key = sanitize_key( (string) $meta->meta_key );
			$val = (string) $meta->meta_value;
			if ( '_wp_page_template' === $key ) continue;
			update_post_meta( $post_id, $key, maybe_unserialize( wp_unslash( $val ) ) );
		}

		$counts[ $post_type . '_inserted' ]++;
	}

	/* Static front + posts page. */
	$home = get_page_by_path( 'home', OBJECT, 'page' );
	$blog = get_page_by_path( 'blog', OBJECT, 'page' );
	if ( $home ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', (int) $home->ID );
		$log[] = '✓ Front page set to: Home';
	}
	if ( $blog ) {
		update_option( 'page_for_posts', (int) $blog->ID );
		$log[] = '✓ Posts page set to: Blog';
	}

	flush_rewrite_rules( true );
	if ( function_exists( 'wp_cache_flush' ) ) wp_cache_flush();
	if ( function_exists( 'opcache_reset' ) ) @opcache_reset();

	$log[] = '─── Import summary ───';
	$log[] = 'Pages: inserted ' . $counts['page_inserted'] . ', refreshed ' . $counts['page_refreshed'] . ', skipped ' . $counts['page_skipped'];
	$log[] = 'Blog posts: inserted ' . $counts['post_inserted'] . ', skipped ' . $counts['post_skipped'];
	$log[] = 'Services: inserted ' . $counts['service_inserted'] . ', skipped ' . $counts['service_skipped'];
	$log[] = 'Errors: ' . $counts['errors'];

	update_option( BVI_FLAG, BVI_VERSION, false );
	$log[] = '✓ Install flag set to ' . BVI_VERSION;

	return $log;
}

/* ---------------------------------------------------------------------
 * Expand <!-- wp:pattern {"slug":"booming-venture/X"} /--> refs into the
 * real block markup so pages are editable in the block editor.
 * Reads patterns/X.php from the ACTIVE THEME (parent or child).
 * --------------------------------------------------------------------- */
function bvi_expand_pattern_refs( string $content ): string {
	$theme_dirs = array_unique( array_filter( [
		get_stylesheet_directory(),
		get_template_directory(),
	] ) );

	return preg_replace_callback(
		'/<!--\s*wp:pattern\s+\{[^}]*"slug":"booming-venture\/([a-z0-9\-]+)"[^}]*\}\s*\/-->/i',
		function ( $m ) use ( $theme_dirs ) {
			$slug = $m[1];
			foreach ( $theme_dirs as $dir ) {
				$file = $dir . '/patterns/' . $slug . '.php';
				if ( ! file_exists( $file ) ) continue;
				ob_start();
				try {
					include $file;
				} catch ( \Throwable $e ) {
					ob_end_clean();
					return $m[0];
				}
				$rendered = trim( (string) ob_get_clean() );
				if ( '' !== $rendered ) return $rendered;
			}
			return $m[0];
		},
		$content
	);
}

/* ---------------------------------------------------------------------
 * Admin notices for post-import success / failure.
 * --------------------------------------------------------------------- */
add_action( 'admin_notices', function () {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'tools_page_booming-venture-importer' !== $screen->id ) return;

	if ( isset( $_GET['bvi_imported'] ) ) {
		printf(
			'<div class="notice notice-success is-dismissible"><p><strong>%s</strong> %s</p></div>',
			esc_html__( 'Import complete.', 'booming-venture-importer' ),
			wp_kses(
				/* translators: %s: blog page URL */
				sprintf( __( 'See the result log on this page. Visit <a href="%s">/blog/</a> to verify.', 'booming-venture-importer' ), esc_url( home_url( '/blog/' ) ) ),
				[ 'a' => [ 'href' => [] ] ]
			)
		);
	}
	if ( isset( $_GET['bvi_error'] ) ) {
		$err = sanitize_text_field( wp_unslash( (string) $_GET['bvi_error'] ) );
		$messages = [
			'nofile'  => __( 'No file was uploaded. Please choose a WXR file and try again.', 'booming-venture-importer' ),
			'badtype' => __( 'The uploaded file is not a valid XML WXR file.', 'booming-venture-importer' ),
		];
		$message = $messages[ $err ] ?? sprintf( __( 'Import error: %s', 'booming-venture-importer' ), $err );
		printf( '<div class="notice notice-error is-dismissible"><p>%s</p></div>', esc_html( $message ) );
	}
} );

/* ---------------------------------------------------------------------
 * Plugin row meta on Plugins screen: quick links to the import page.
 * --------------------------------------------------------------------- */
add_filter( 'plugin_action_links_' . plugin_basename( BVI_PLUGIN_FILE ), function ( $links ) {
	$import_url = admin_url( 'tools.php?page=booming-venture-importer' );
	array_unshift( $links, '<a href="' . esc_url( $import_url ) . '">' . esc_html__( 'Run import', 'booming-venture-importer' ) . '</a>' );
	return $links;
} );
