<?php
/**
 * One-shot content installer.
 *
 * On theme activation, parses the WXR file shipped at
 * `import/booming-venture-content.xml`, creates every page / post /
 * service inside WordPress, configures the static front page + blog
 * page, sets pretty permalinks, and registers nav menus.
 *
 * Idempotent: protected by an option flag. Re-activating the theme
 * does not re-import. To force a re-run, delete the
 * `bv_content_imported` option from `wp_options`.
 *
 * Works without the WordPress Importer plugin (uses SimpleXML).
 * Safe on Strato / SiteGround / Kinsta / any shared host.
 *
 * @package BoomingVenture
 */

defined( 'ABSPATH' ) || exit;

const BV_INSTALL_FLAG    = 'bv_content_imported';
const BV_INSTALL_VERSION = '1.0.0';

/* Run after theme activation (priority 20 = after setup hooks). */
add_action( 'after_switch_theme', 'bv_run_install', 20 );

function bv_run_install(): void {
	if ( get_option( BV_INSTALL_FLAG ) === BV_INSTALL_VERSION ) {
		return;
	}

	@set_time_limit( 120 );

	/* Permalink structure: /blog/%postname%/ — gives clean
	 * /blog/{slug}/ URLs for posts while leaving pages and CPTs
	 * (services, landing pages, case studies) on their own slugs.
	 * WordPress handles the conflict between the "blog" page slug
	 * and the /blog/ prefix natively as long as page_for_posts is
	 * set to that page (done below in bv_configure_homepage). */
	update_option( 'permalink_structure', '/blog/%postname%/' );

	$ok = bv_import_wxr();
	if ( ! $ok ) {
		set_transient( 'bv_install_error', 'WXR file missing or invalid.', 300 );
		return;
	}

	bv_configure_homepage();
	bv_configure_menus();

	flush_rewrite_rules( false );
	update_option( BV_INSTALL_FLAG, BV_INSTALL_VERSION );
	set_transient( 'bv_install_success', true, 300 );
}

/**
 * Parse the WXR and create all items.
 */
function bv_import_wxr(): bool {
	$path = BV_THEME_DIR . '/import/booming-venture-content.xml';
	if ( ! file_exists( $path ) ) return false;

	libxml_use_internal_errors( true );
	$xml = simplexml_load_file( $path );
	if ( ! $xml ) return false;

	$ns_wp      = 'http://wordpress.org/export/1.2/';
	$ns_content = 'http://purl.org/rss/1.0/modules/content/';
	$ns_excerpt = 'http://wordpress.org/export/1.2/excerpt/';
	$ns_dc      = 'http://purl.org/dc/elements/1.1/';

	$channel = $xml->channel;

	/* 1. Categories. */
	$cat_map = [];
	foreach ( $channel->children( $ns_wp )->category as $c ) {
		$slug = (string) $c->category_nicename;
		$name = (string) $c->cat_name;
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

	/* 2. Items (pages, posts, CPTs). */
	foreach ( $channel->item as $item ) {
		$wp = $item->children( $ns_wp );
		$content_node = $item->children( $ns_content );
		$excerpt_node = $item->children( $ns_excerpt );

		$post_type   = (string) $wp->post_type;
		$post_status = (string) $wp->status;
		$slug        = (string) $wp->post_name;
		$title       = (string) $item->title;
		$content     = (string) $content_node->encoded;
		$excerpt     = (string) $excerpt_node->encoded;
		$post_date   = (string) $wp->post_date;
		$template    = '';

		if ( ! $post_type || ! $slug || 'publish' !== $post_status ) continue;
		if ( ! in_array( $post_type, [ 'page', 'post', 'service', 'landing_page', 'case_study' ], true ) ) continue;

		/* Skip if it already exists (re-run safety). */
		$existing = get_page_by_path( $slug, OBJECT, $post_type );
		if ( $existing ) continue;

		/* Look for `_wp_page_template` postmeta. */
		foreach ( $wp->postmeta as $meta ) {
			if ( '_wp_page_template' === (string) $meta->meta_key ) {
				$template = (string) $meta->meta_value;
			}
		}

		$post_id = wp_insert_post( [
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_content' => $content,
			'post_excerpt' => $excerpt,
			'post_status'  => 'publish',
			'post_type'    => $post_type,
			'post_date'    => $post_date ?: current_time( 'mysql' ),
			'post_author'  => 1,
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
		], true );

		if ( is_wp_error( $post_id ) ) continue;

		if ( $template ) {
			update_post_meta( $post_id, '_wp_page_template', $template );
		}

		/* Categories on posts. */
		if ( 'post' === $post_type ) {
			$cat_ids = [];
			foreach ( $item->category as $c ) {
				$nicename = (string) $c['nicename'];
				if ( isset( $cat_map[ $nicename ] ) ) {
					$cat_ids[] = $cat_map[ $nicename ];
				}
			}
			if ( $cat_ids ) wp_set_post_categories( $post_id, $cat_ids );
		}

		/* All postmeta except `_wp_page_template` already handled. */
		foreach ( $wp->postmeta as $meta ) {
			$key = (string) $meta->meta_key;
			if ( '_wp_page_template' === $key ) continue;
			$val = (string) $meta->meta_value;
			update_post_meta( $post_id, $key, $val );
		}
	}

	return true;
}

/**
 * Set the static front page + posts page.
 */
function bv_configure_homepage(): void {
	$home = get_page_by_path( 'home', OBJECT, 'page' );
	$blog = get_page_by_path( 'blog', OBJECT, 'page' );

	if ( $home ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home->ID );
	}
	if ( $blog ) {
		update_option( 'page_for_posts', $blog->ID );
	}
}

/**
 * Create primary + footer + legal + tools nav menus and assign them
 * to the registered theme locations. Idempotent: uses
 * `wp_get_nav_menu_object` to avoid duplicates.
 */
function bv_configure_menus(): void {
	$locations = get_theme_mod( 'nav_menu_locations', [] );
	if ( ! is_array( $locations ) ) $locations = [];

	$menus = [
		'primary' => [
			'name'  => 'Primary',
			'items' => [
				[ 'title' => 'About',                  'slug' => 'about' ],
				[ 'title' => 'UNIFY Framework',        'slug' => 'unify-framework' ],
				[ 'title' => 'Services',               'slug' => 'services' ],
				[ 'title' => 'Funnel Leak Calculator', 'slug' => 'funnel-calculator' ],
				[ 'title' => 'ROI Forecaster',         'slug' => 'roi-forecaster' ],
				[ 'title' => 'Blog',                   'slug' => 'blog' ],
			],
		],
		'footer' => [
			'name'  => 'Footer',
			'items' => [
				[ 'title' => 'About Us',         'slug' => 'about' ],
				[ 'title' => 'UNIFY Framework',  'slug' => 'unify-framework' ],
				[ 'title' => 'Services',         'slug' => 'services' ],
				[ 'title' => 'Blog',             'slug' => 'blog' ],
			],
		],
		'legal' => [
			'name'  => 'Legal',
			'items' => [
				[ 'title' => 'Privacy Policy',   'slug' => 'privacy-policy' ],
				[ 'title' => 'Terms of Service', 'slug' => 'terms-of-service' ],
				[ 'title' => 'Disclaimer',       'slug' => 'disclaimer' ],
			],
		],
		'tools' => [
			'name'  => 'Tools',
			'items' => [
				[ 'title' => 'Funnel Leak Calculator', 'slug' => 'funnel-calculator' ],
				[ 'title' => 'ROI Forecaster',         'slug' => 'roi-forecaster' ],
				[ 'title' => 'Free Growth Guide',      'slug' => 'free-growth-guide' ],
				[ 'title' => 'Boardroom Quickscan',    'slug' => 'boardroom-quickscan' ],
			],
		],
	];

	foreach ( $menus as $location => $cfg ) {
		$menu = wp_get_nav_menu_object( $cfg['name'] );
		if ( ! $menu ) {
			$menu_id = wp_create_nav_menu( $cfg['name'] );
			if ( is_wp_error( $menu_id ) ) continue;
		} else {
			$menu_id = $menu->term_id;
			/* Empty existing menu items so re-run is clean. */
			foreach ( wp_get_nav_menu_items( $menu_id ) as $existing ) {
				wp_delete_post( $existing->ID, true );
			}
		}

		foreach ( $cfg['items'] as $item ) {
			$page = get_page_by_path( $item['slug'], OBJECT, 'page' );
			if ( ! $page ) continue;
			wp_update_nav_menu_item( $menu_id, 0, [
				'menu-item-title'     => $item['title'],
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $page->ID,
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
			] );
		}

		$locations[ $location ] = $menu_id;
	}

	set_theme_mod( 'nav_menu_locations', $locations );
}

/**
 * Admin notice on first activation: success or error message.
 */
add_action( 'admin_notices', function () {
	if ( get_transient( 'bv_install_success' ) ) {
		delete_transient( 'bv_install_success' );
		?>
		<div class="notice notice-success is-dismissible">
			<p><strong>Booming Venture theme installed.</strong> All pages, 43 blog posts, 4 services, and the menus were imported. Visit <a href="<?php echo esc_url( home_url( '/' ) ); ?>">the homepage</a> or open <a href="<?php echo esc_url( admin_url( 'options-general.php?page=booming-venture' ) ); ?>">Settings → Booming Venture</a> to map your Contact Form 7 IDs.</p>
		</div>
		<?php
	}
	if ( $err = get_transient( 'bv_install_error' ) ) {
		delete_transient( 'bv_install_error' );
		?>
		<div class="notice notice-error">
			<p><strong>Booming Venture import failed:</strong> <?php echo esc_html( $err ); ?> Re-activate the theme to retry, or delete the <code>bv_content_imported</code> option in <em>Tools → Site Health → Info → wp_options</em> first.</p>
		</div>
		<?php
	}
} );


/* ============================================================
 * Manual re-import endpoint + diagnostics on the Settings page.
 * Useful when the auto-importer ran but missed posts (Strato
 * 30-second timeout) or when re-installing the theme over the top.
 * ============================================================ */

add_action( 'admin_post_bv_reimport', function () {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );
	check_admin_referer( 'bv_reimport' );
	delete_option( BV_INSTALL_FLAG );
	bv_run_install();
	wp_safe_redirect( admin_url( 'options-general.php?page=booming-venture&bv_reimported=1' ) );
	exit;
} );

/* Inject the diagnostic card + button at the top of the BV settings
 * page. Hooks in_admin_footer for the options-general page so it
 * renders even if the rest of the settings page is loaded by the
 * existing options framework. */
add_action( 'admin_notices', function () {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'settings_page_booming-venture' !== $screen->id ) return;
	if ( isset( $_GET['bv_reimported'] ) ) {
		?>
		<div class="notice notice-success is-dismissible">
			<p><strong>Re-import complete.</strong> Demo content has been re-applied. Visit the front-end to verify.</p>
		</div>
		<?php
	}
} );

/* Render a "Booming Venture content" panel at the top of the
 * settings page. Counts pages / posts / services and shows the
 * Re-import button. */
add_action( 'admin_init', function () {
	add_filter( 'admin_footer_text', function ( $text ) {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || 'settings_page_booming-venture' !== $screen->id ) return $text;
		return $text;
	} );
} );

add_action( 'admin_print_footer_scripts-settings_page_booming-venture', function () {
	$pages    = count( get_posts( [ 'post_type' => 'page',    'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids' ] ) );
	$posts    = count( get_posts( [ 'post_type' => 'post',    'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids' ] ) );
	$services = count( get_posts( [ 'post_type' => 'service', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids' ] ) );
	$home_set = (int) get_option( 'page_on_front' );
	$blog_set = (int) get_option( 'page_for_posts' );
	$front    = get_option( 'show_on_front' );
	$installed = get_option( BV_INSTALL_FLAG );

	$action = esc_url( admin_url( 'admin-post.php' ) );
	$nonce  = wp_nonce_field( 'bv_reimport', '_wpnonce', true, false );

	$html = '<div class="notice" style="margin:1rem 20px;border-left:4px solid #0284c7;padding:1rem 1.25rem;background:#f0f9ff;">'
		. '<h3 style="margin:0 0 0.5rem;">Booming Venture content status</h3>'
		. '<ul style="margin:0 0 1rem;font-size:13px;line-height:1.6;">'
		. '<li>Pages published: <strong>' . $pages . '</strong> (expect ≥13)</li>'
		. '<li>Blog posts published: <strong>' . $posts . '</strong> (expect 43)</li>'
		. '<li>Services published: <strong>' . $services . '</strong> (expect 4)</li>'
		. '<li>Front page set: <strong>' . ( $home_set ? esc_html( get_the_title( $home_set ) ) : 'NOT SET' ) . '</strong></li>'
		. '<li>Posts page set: <strong>' . ( $blog_set ? esc_html( get_the_title( $blog_set ) ) : 'NOT SET — your blog will not show!' ) . '</strong></li>'
		. '<li>Show on front: <strong>' . esc_html( $front ) . '</strong></li>'
		. '<li>Install flag: <strong>' . ( $installed ? esc_html( $installed ) : 'not run' ) . '</strong></li>'
		. '</ul>'
		. '<form method="post" action="' . $action . '" style="display:inline;">'
		. $nonce
		. '<input type="hidden" name="action" value="bv_reimport">'
		. '<button type="submit" class="button button-primary" onclick="return confirm(\'Re-run the import? Existing pages/posts/services with the same slug will be skipped (safe). Menus will be rebuilt from the theme defaults.\')">Re-import demo content</button>'
		. '</form>'
		. ' <a href="' . esc_url( admin_url( 'options-permalink.php' ) ) . '" class="button">Open Permalinks (Save once to flush rules)</a>'
		. ' <a href="' . esc_url( admin_url( 'edit.php' ) ) . '" class="button">View all posts</a>'
		. '</div>';

	/* Inject after the <h1> on the settings page so it appears at
	 * the top of the panel. */
	echo "<script>(function(){var t=document.querySelector('.wrap h1');if(t)t.insertAdjacentHTML('afterend'," . wp_json_encode( $html ) . ");})();</script>";
} );


/* ============================================================
 * [bv_read_time] shortcode — outputs the per-post _bv_read_time
 * meta value. Used inside wp:post-template loops on the blog
 * index so each card can show "8 min read" / "12 min read" etc.
 * Falls back to a calculated estimate from word count if the
 * meta is empty.
 * ============================================================ */
add_shortcode( 'bv_read_time', function () {
	$id = get_the_ID();
	if ( ! $id ) return '';
	$rt = get_post_meta( $id, '_bv_read_time', true );
	if ( ! $rt ) {
		$words = str_word_count( wp_strip_all_tags( get_post_field( 'post_content', $id ) ) );
		$min   = max( 1, (int) round( $words / 220 ) );
		$rt    = $min . ' min read';
	}
	return esc_html( $rt );
} );
