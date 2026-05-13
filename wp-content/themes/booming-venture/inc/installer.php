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
/* IMPORTANT: bump this whenever the WXR gains new content (new pages,
 * new posts, new patterns wired into existing pages). The install hook
 * re-runs the WXR import on the next activation when the stored value
 * does not match this constant. */
const BV_INSTALL_VERSION = '1.5.7';

/* Run after theme activation (priority 20 = after setup hooks). */
add_action( 'after_switch_theme', 'bv_run_install', 20 );

function bv_run_install(): void {
	$already_installed = ( get_option( BV_INSTALL_FLAG ) === BV_INSTALL_VERSION );

	@set_time_limit( 120 );

	/* Permalink structure: /blog/%postname%/ ,  re-asserted on every
	 * activation so a plugin or theme switch can't strand it. */
	update_option( 'permalink_structure', '/blog/%postname%/' );

	if ( ! $already_installed ) {
		$ok = bv_import_wxr();
		if ( ! $ok ) {
			set_transient( 'bv_install_error', 'WXR file missing or invalid.', 300 );
			return;
		}
		update_option( BV_INSTALL_FLAG, BV_INSTALL_VERSION );
		set_transient( 'bv_install_success', true, 300 );
	}

	/* These must run on EVERY activation ,  not just first install , 
	 * so the home/blog wiring and rewrite cache reflect any changes
	 * that ship in a theme update zip. */
	bv_configure_homepage();
	bv_configure_menus();
	flush_rewrite_rules( true );
	bv_flush_all_caches();
}

/**
 * One-shot cache flush ,  runs on theme activation and from the manual
 * "Flush rewrite rules" button. Covers WP core, rewrite rules,
 * transients, OPcache, and the major caching plugins (WP Rocket,
 * W3 Total Cache, WP Super Cache, LiteSpeed, SG Optimizer,
 * Autoptimize, Cache Enabler, Hummingbird, Breeze).
 */
function bv_flush_all_caches(): void {
	if ( function_exists( 'wp_cache_flush' ) ) {
		wp_cache_flush();
	}

	/* Site transients in the options table. */
	if ( function_exists( 'delete_expired_transients' ) ) {
		delete_expired_transients( true );
	}

	/* WP global stylesheet (block-styles + theme.json) caches ,  these
	 * hold the compiled CSS for the colour palette, layout sizes etc.
	 * Without clearing them, a change to theme.json (e.g. contentSize)
	 * won't show up until they expire. */
	delete_transient( 'global_styles' );
	delete_transient( 'gutenberg_global_styles' );
	delete_option( '_transient_global_styles' );
	if ( class_exists( 'WP_Theme_JSON_Resolver' ) && method_exists( 'WP_Theme_JSON_Resolver', 'clean_cached_data' ) ) {
		WP_Theme_JSON_Resolver::clean_cached_data();
	}

	/* PHP OPcache ,  clears bytecode so updated PHP files load. */
	if ( function_exists( 'opcache_reset' ) ) {
		@opcache_reset();
	}

	/* Third-party caches ,  call only when present, so we don't fatal. */
	if ( function_exists( 'rocket_clean_domain' ) )          { @rocket_clean_domain(); }
	if ( function_exists( 'rocket_clean_minify' ) )          { @rocket_clean_minify(); }
	if ( function_exists( 'w3tc_flush_all' ) )               { @w3tc_flush_all(); }
	if ( function_exists( 'wp_cache_clear_cache' ) )         { @wp_cache_clear_cache(); }
	if ( has_action( 'litespeed_purge_all' ) )               { do_action( 'litespeed_purge_all' ); }
	if ( function_exists( 'sg_cachepress_purge_cache' ) )    { @sg_cachepress_purge_cache(); }
	if ( class_exists( 'autoptimizeCache' )
		&& method_exists( 'autoptimizeCache', 'clearall' ) )  { @autoptimizeCache::clearall(); }
	if ( has_action( 'cache_enabler_clear_complete_cache' ) ){ do_action( 'cache_enabler_clear_complete_cache' ); }
	if ( has_action( 'wphb_clear_cache_url' ) )              { do_action( 'wphb_clear_cache_url' ); }
	if ( function_exists( 'breeze_clear_all_cache' ) )       { @breeze_clear_all_cache(); }

	/* Cloudflare via the official plugin. */
	if ( function_exists( 'cloudflare_purge_everything' ) )  { @cloudflare_purge_everything(); }

	/* Let other code hook in. */
	do_action( 'bv_after_cache_flush' );
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

		/* Structural pages whose content is owned by the theme and gets
		 * refreshed on every install-version bump. User-edited content
		 * (blog posts, CPT detail pages, legal pages) is preserved. */
		$structural_slugs = [
			'home', 'about', 'services', 'blog', 'unify-framework',
			'funnel-calculator', 'roi-forecaster',
			'free-growth-guide', 'boardroom-quickscan', 'head-of-growth',
		];
		$is_structural = ( 'page' === $post_type ) && in_array( $slug, $structural_slugs, true );

		$existing = get_page_by_path( $slug, OBJECT, $post_type );
		if ( $existing ) {
			if ( $is_structural && $existing->ID ) {
				/* Refresh theme-owned structural page content so pattern
				 * roster updates (e.g. new about-founder pattern) take
				 * effect on every install-version bump. Wrapped in a
				 * try/catch so a single bad row never breaks activation. */
				try {
					wp_update_post( [
						'ID'           => $existing->ID,
						'post_content' => $content,
						'post_excerpt' => $excerpt,
					] );
					foreach ( $wp->postmeta as $meta ) {
						if ( '_wp_page_template' === (string) $meta->meta_key ) {
							update_post_meta( $existing->ID, '_wp_page_template', (string) $meta->meta_value );
						}
					}
				} catch ( \Throwable $e ) {
					/* swallow ,  install must not break runtime */
				}
			}
			continue;
		}

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
		. '<li>Posts page set: <strong>' . ( $blog_set ? esc_html( get_the_title( $blog_set ) ) : 'NOT SET ,  your blog will not show!' ) . '</strong></li>'
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
 * [bv_read_time] shortcode ,  outputs the per-post _bv_read_time
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


/* ============================================================
 * 301 redirect catcher for old-style URLs.
 *
 * Posts now live at /blog/{slug}/. If someone hits the legacy URL
 * /{slug}/ (which would happen with bookmarks, external links, or
 * permalink_structure briefly being /%postname%/ during a previous
 * theme), look up the post by slug and 301 to the canonical URL.
 *
 * This runs only on 404s so it doesn't intercept any real page or
 * CPT URL.
 * ============================================================ */
add_action( 'template_redirect', function () {
	if ( ! is_404() ) return;

	$req = trim( wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ) ?: '', '/' );
	if ( ! $req || str_contains( $req, '/' ) ) return; // multi-segment paths handled elsewhere

	$slug = sanitize_title( $req );
	if ( ! $slug ) return;

	/* Direct slug match against any post type that should be public. */
	foreach ( [ 'post', 'page', 'service', 'landing_page', 'case_study' ] as $pt ) {
		$post = get_page_by_path( $slug, OBJECT, $pt );
		if ( $post && 'publish' === $post->post_status ) {
			$dest = get_permalink( $post );
			if ( $dest && trailingslashit( $dest ) !== trailingslashit( home_url( $_SERVER['REQUEST_URI'] ?? '' ) ) ) {
				wp_safe_redirect( $dest, 301 );
				exit;
			}
		}
	}
}, 1 );


/* ============================================================
 * Manual "Flush rewrite rules" endpoint ,  for when Settings →
 * Permalinks → Save Changes did not pick up the new structure on
 * Strato (common when .htaccess is not writable).
 * ============================================================ */
add_action( 'admin_post_bv_flush_rewrites', function () {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );
	check_admin_referer( 'bv_flush_rewrites' );

	/* Force the structure to /blog/%postname%/ again, in case a
	 * plugin or theme switch reset it. */
	update_option( 'permalink_structure', '/blog/%postname%/' );

	/* WP regenerates rules and tries to write .htaccess. */
	flush_rewrite_rules( true );

	/* Plus all object / plugin / OPcache layers. */
	bv_flush_all_caches();

	wp_safe_redirect( admin_url( 'options-general.php?page=booming-venture&bv_flushed=1' ) );
	exit;
} );

/* ============================================================
 * Manual "Force re-import content" endpoint. Clears the install
 * flag and re-runs bv_import_wxr() + bv_configure_homepage() +
 * bv_configure_menus(). Use when the WXR re-import did not run
 * on theme activation (e.g. install flag already matched).
 * Safe: existing pages are kept, structural pages get refreshed,
 * missing blog posts are inserted.
 * ============================================================ */
add_action( 'admin_post_bv_force_reimport', function () {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );
	check_admin_referer( 'bv_force_reimport' );

	delete_option( BV_INSTALL_FLAG );
	bv_run_install();

	wp_safe_redirect( admin_url( 'options-general.php?page=booming-venture&bv_reimported=1' ) );
	exit;
} );

add_action( 'admin_notices', function () {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'settings_page_booming-venture' !== $screen->id ) return;
	if ( isset( $_GET['bv_flushed'] ) ) {
		?>
		<div class="notice notice-success is-dismissible">
			<p><strong>Rewrite rules flushed.</strong> Visit any blog post URL to verify. Permalink structure: <code>/blog/%postname%/</code></p>
		</div>
		<?php
	}
	if ( isset( $_GET['bv_reimported'] ) ) {
		global $wpdb;
		$post_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'post' AND post_status = 'publish'" );
		?>
		<div class="notice notice-success is-dismissible">
			<p><strong>Content re-imported.</strong> Total published posts now: <strong><?php echo $post_count; ?></strong>. About page refreshed with founder section. Visit <a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>">/blog/</a> and <a href="<?php echo esc_url( home_url( '/about/' ) ); ?>">/about/</a> to verify.</p>
		</div>
		<?php
	}
} );


/* ============================================================
 * Diagnostics: extra fields for the Settings page panel , 
 * current permalink structure, .htaccess writability, and the
 * rules WordPress wants to write (so user can paste them
 * manually on Strato if needed).
 * ============================================================ */
function bv_htaccess_status(): array {
	$path = ABSPATH . '.htaccess';
	$exists   = file_exists( $path );
	$writable = $exists ? is_writable( $path ) : is_writable( ABSPATH );
	$has_wp_rules = $exists && false !== strpos( @file_get_contents( $path ) ?: '', '# BEGIN WordPress' );
	return [ 'path' => $path, 'exists' => $exists, 'writable' => $writable, 'has_wp_rules' => $has_wp_rules ];
}

add_action( 'admin_print_footer_scripts-settings_page_booming-venture', function () {
	$permalink = get_option( 'permalink_structure' );
	$ht        = bv_htaccess_status();
	$nonce     = wp_nonce_field( 'bv_flush_rewrites', '_wpnonce', true, false );
	$action    = esc_url( admin_url( 'admin-post.php' ) );

	$expected_rules = '<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>';

	$html = '<div class="notice" style="margin:1rem 20px;border-left:4px solid #f97316;padding:1rem 1.25rem;background:#fff7ed;">'
		. '<h3 style="margin:0 0 0.5rem;">Permalink / rewrite diagnostics</h3>'
		. '<ul style="margin:0 0 1rem;font-size:13px;line-height:1.7;">'
		. '<li>Current permalink structure: <code>' . esc_html( $permalink ?: '(default ?p=N)' ) . '</code>'
		. ( '/blog/%postname%/' === $permalink ? ' ✅' : ' ⚠️ Expected <code>/blog/%postname%/</code>' ) . '</li>'
		. '<li><code>.htaccess</code> file: ' . ( $ht['exists'] ? 'exists' : 'MISSING' ) . ' at <code>' . esc_html( $ht['path'] ) . '</code></li>'
		. '<li>Writable by WordPress: ' . ( $ht['writable'] ? '✅ yes' : '❌ no ,  WordPress cannot save permalink rules' ) . '</li>'
		. '<li>Contains WP rewrite rules: ' . ( $ht['has_wp_rules'] ? '✅ yes' : '❌ no ,  pretty URLs will 404' ) . '</li>'
		. '</ul>'
		. '<form method="post" action="' . $action . '" style="display:inline;">'
		. $nonce
		. '<input type="hidden" name="action" value="bv_flush_rewrites">'
		. '<button type="submit" class="button button-primary">Flush rewrite rules now</button>'
		. '</form>'
		. ' <a href="' . esc_url( admin_url( 'options-permalink.php' ) ) . '" class="button">Open Permalinks page</a>'
		. ' <form method="post" action="' . $action . '" style="display:inline;margin-left:8px;">'
		. wp_nonce_field( 'bv_force_reimport', '_wpnonce', true, false )
		. '<input type="hidden" name="action" value="bv_force_reimport">'
		. '<button type="submit" class="button button-secondary">Force re-import content (WXR)</button>'
		. '</form>';

	if ( ! $ht['writable'] || ! $ht['has_wp_rules'] ) {
		$html .= '<div style="margin-top:1rem;padding:0.75rem 1rem;background:#fff;border:1px solid #f97316;border-radius:0.5rem;">'
			. '<p style="margin:0 0 0.5rem;font-weight:600;">Manual fix for Strato (when .htaccess is not writable)</p>'
			. '<p style="margin:0 0 0.5rem;font-size:13px;">Paste these rules at the top of <code>.htaccess</code> in your WordPress root via Strato File Manager:</p>'
			. '<pre style="margin:0;padding:0.75rem;background:#0f172a;color:#e2e8f0;border-radius:0.375rem;font-size:12px;overflow:auto;">' . esc_html( $expected_rules ) . '</pre>'
			. '</div>';
	}

	$html .= '</div>';

	echo "<script>(function(){var t=document.querySelector('.wrap h1');if(t)t.insertAdjacentHTML('beforeend'," . wp_json_encode( $html ) . ");})();</script>";
}, 11 );
