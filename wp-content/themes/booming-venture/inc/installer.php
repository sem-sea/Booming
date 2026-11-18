<?php
/**
 * Content installer, v2 (theme 1.9.0+).
 *
 * Everything the site needs ships inside the theme zip:
 *   import/content.json   134 posts, 14 pages, 10 categories, SEO meta, redirects
 *   import/media/...      every upload the content uses, same paths as live
 *
 * On activation the posts, pages and categories are created (fast, a few
 * seconds). Images are copied into the Media Library in small batches so
 * shared hosts with a 30-second limit never time out:
 *   - Tools -> Booming Venture import runs the batches with a progress bar
 *   - a WP-Cron fallback keeps going if nobody opens that screen
 *   - `wp bv import` does everything in one go from the command line
 *
 * Safe on an existing install: posts that already exist keep their content
 * (only their category, missing SEO meta and old /blog/slug/ links are
 * updated). Theme-owned structural pages are refreshed, as before.
 *
 * @package BoomingVenture
 */

defined( 'ABSPATH' ) || exit;

const BV_INSTALL_FLAG    = 'bv_content_imported';
/* Bump whenever import/content.json changes. */
const BV_INSTALL_VERSION = '1.9.0';
const BV_MEDIA_BATCH     = 6;

add_action( 'after_switch_theme', 'bv_run_install', 20 );

/* A theme update uploaded over the active theme does not fire
 * after_switch_theme. Run the upgrade once, on the first admin page an
 * administrator opens after the new version is in place. */
add_action( 'admin_init', static function () {
	if ( wp_doing_ajax() || ! current_user_can( 'manage_options' ) ) return;
	if ( get_option( BV_INSTALL_FLAG ) === BV_INSTALL_VERSION ) return;
	bv_run_install();
} );

/** Load and cache the content bundle. */
function bv_content_bundle(): ?array {
	static $bundle = null;
	if ( null !== $bundle ) return $bundle;
	$path = BV_THEME_DIR . '/import/content.json';
	if ( ! is_readable( $path ) ) return null;
	$data = json_decode( (string) file_get_contents( $path ), true );
	$bundle = is_array( $data ) ? $data : null;
	return $bundle;
}

function bv_run_install(): void {
	@set_time_limit( 300 );

	/* Live URLs are /post-slug/. Keep them, never move posts under /blog/. */
	update_option( 'permalink_structure', '/%postname%/' );
	update_option( 'posts_per_page', 12 );
	bv_fix_site_identity();

	if ( get_option( BV_INSTALL_FLAG ) !== BV_INSTALL_VERSION ) {
		/* after_switch_theme fires on whichever request comes first, so a
		 * visitor or wp-cron can start a second import in parallel. add_option
		 * is an atomic INSERT on a unique key: only one request wins. */
		if ( ! bv_acquire_install_lock() ) return;
		if ( get_option( BV_INSTALL_FLAG ) === BV_INSTALL_VERSION ) {
			delete_option( 'bv_install_lock' );
			return;
		}
		$result = bv_import_content();
		if ( is_wp_error( $result ) ) {
			delete_option( 'bv_install_lock' );
			set_transient( 'bv_install_error', $result->get_error_message(), 600 );
			return;
		}
		update_option( BV_INSTALL_FLAG, BV_INSTALL_VERSION );
		delete_option( 'bv_install_lock' );
		update_option( 'bv_media_state', [ 'next' => 0, 'done' => false ], false );
		set_transient( 'bv_install_success', $result, 600 );
		if ( ! wp_next_scheduled( 'bv_media_cron' ) ) {
			wp_schedule_event( time() + 30, 'bv_every_minute', 'bv_media_cron' );
		}
	}

	bv_configure_homepage();
	bv_configure_menus();
	bv_configure_yoast();
	flush_rewrite_rules( true );
	bv_flush_all_caches();
}

function bv_acquire_lock( string $name, int $stale_after ): bool {
	wp_cache_delete( $name, 'options' );
	wp_cache_delete( 'notoptions', 'options' );
	if ( add_option( $name, time(), '', false ) ) return true;
	$since = (int) get_option( $name );
	if ( $since && time() - $since > $stale_after ) { /* stale lock from a crashed run */
		delete_option( $name );
		return add_option( $name, time(), '', false );
	}
	return false;
}

function bv_acquire_install_lock(): bool {
	return bv_acquire_lock( 'bv_install_lock', 600 );
}

/**
 * The live site title was "Booming Venture -", which put a dangling dash
 * in every search result title. Fix it, and only touch defaults.
 */
function bv_fix_site_identity(): void {
	$name = trim( (string) get_option( 'blogname' ) );
	if ( '' === $name || in_array( $name, [ 'Booming Venture -', 'Booming Venture –', 'My WordPress Website', 'WordPress', 'Test' ], true ) ) {
		update_option( 'blogname', 'Booming Venture' );
	}
	if ( '' === trim( (string) get_option( 'blogdescription' ) ) || 'Just another WordPress site' === get_option( 'blogdescription' ) ) {
		update_option( 'blogdescription', 'Performance. Personality. Powered by AI.' );
	}
}

/**
 * Create or update categories, pages and posts from the bundle.
 *
 * @return array|WP_Error Counts on success.
 */
function bv_import_content() {
	$bundle = bv_content_bundle();
	if ( ! $bundle ) return new WP_Error( 'bv_bundle', 'import/content.json is missing or invalid.' );

	/* Admin-authored content: keep inline styles and <style> blocks intact. */
	kses_remove_filters();
	wp_defer_term_counting( true );

	$author = bv_import_author();
	$counts = [ 'categories' => 0, 'pages_created' => 0, 'pages_updated' => 0, 'posts_created' => 0, 'posts_updated' => 0 ];

	/* 1. Categories. */
	$cat_ids = [];
	foreach ( $bundle['categories'] as $cat ) {
		$term = get_term_by( 'slug', $cat['slug'], 'category' );
		if ( $term ) {
			wp_update_term( $term->term_id, 'category', [ 'name' => $cat['name'], 'description' => $cat['description'] ] );
			$cat_ids[ $cat['slug'] ] = (int) $term->term_id;
		} else {
			$res = wp_insert_term( $cat['name'], 'category', [ 'slug' => $cat['slug'], 'description' => $cat['description'] ] );
			if ( ! is_wp_error( $res ) ) $cat_ids[ $cat['slug'] ] = (int) $res['term_id'];
		}
		$counts['categories']++;
	}
	if ( isset( $cat_ids['marketing-strategy'] ) ) {
		update_option( 'default_category', $cat_ids['marketing-strategy'] );
	}
	update_option( 'bv_category_redirects', (array) ( $bundle['category_redirects'] ?? [] ), false );

	/* 2. Pages. */
	foreach ( $bundle['pages'] as $page ) {
		$content  = ! empty( $page['structural'] ) ? bv_expand_pattern_refs( $page['content'] ) : $page['content'];
		$existing = get_page_by_path( $page['slug'], OBJECT, 'page' );
		if ( $existing ) {
			/* Theme-owned pages are refreshed. So is any page that is not
			 * published yet, such as the draft Privacy Policy WordPress
			 * creates on a fresh install. */
			if ( ! empty( $page['structural'] ) || 'publish' !== $existing->post_status ) {
				wp_update_post( wp_slash( [ 'ID' => $existing->ID, 'post_content' => $content, 'post_title' => $page['title'], 'post_status' => 'publish' ] ) );
				$counts['pages_updated']++;
			}
			$page_id = (int) $existing->ID;
		} else {
			$page_id = wp_insert_post( wp_slash( [
				'post_title'     => $page['title'],
				'post_name'      => $page['slug'],
				'post_content'   => $content,
				'post_status'    => 'publish',
				'post_type'      => 'page',
				'post_author'    => $author,
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
			] ), true );
			if ( is_wp_error( $page_id ) ) continue;
			$counts['pages_created']++;
		}
		if ( ! empty( $page['template'] ) ) {
			update_post_meta( $page_id, '_wp_page_template', $page['template'] );
		}
		bv_set_seo_meta( $page_id, $page['seo_title'] ?? '', $page['meta_description'] ?? '' );
	}

	/* 3. Posts. */
	$slugs = wp_list_pluck( $bundle['posts'], 'slug' );
	foreach ( $bundle['posts'] as $post ) {
		$existing = get_page_by_path( $post['slug'], OBJECT, 'post' );
		if ( $existing ) {
			$post_id = (int) $existing->ID;
			$fixed   = bv_fix_blog_prefix_links( $existing->post_content, $slugs );
			if ( $fixed !== $existing->post_content ) {
				/* Direct update: a link fix is not a content update, so the
				 * post keeps its real "last updated" date. */
				global $wpdb;
				$wpdb->update( $wpdb->posts, [ 'post_content' => $fixed ], [ 'ID' => $post_id ] );
				clean_post_cache( $post_id );
			}
			$counts['posts_updated']++;
		} else {
			$post_id = wp_insert_post( wp_slash( [
				'post_title'     => $post['title'],
				'post_name'      => $post['slug'],
				'post_content'   => $post['content'],
				'post_status'    => 'publish',
				'post_type'      => 'post',
				'post_author'    => $author,
				'post_date'      => $post['date'],
				'post_date_gmt'  => $post['date_gmt'],
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
			] ), true );
			if ( is_wp_error( $post_id ) ) continue;
			/* Keep the real "last updated" date; wp_insert_post sets "now". */
			global $wpdb;
			$wpdb->update( $wpdb->posts, [ 'post_modified' => $post['modified'], 'post_modified_gmt' => $post['modified_gmt'] ], [ 'ID' => $post_id ] );
			clean_post_cache( $post_id );
			$counts['posts_created']++;
		}
		if ( isset( $cat_ids[ $post['category'] ] ) ) {
			wp_set_post_categories( $post_id, [ $cat_ids[ $post['category'] ] ], false );
		}
		bv_set_seo_meta( $post_id, '', $post['meta_description'] ?? '' );
		if ( ! empty( $post['featured'] ) ) {
			update_post_meta( $post_id, '_bv_featured_file', $post['featured'] );
		}
	}

	/* Sample content from a fresh WordPress install. */
	foreach ( [ [ 'hello-world', 'post' ], [ 'sample-page', 'page' ] ] as [ $slug, $type ] ) {
		$p = get_page_by_path( $slug, OBJECT, $type );
		if ( $p ) wp_delete_post( $p->ID, 'post' === $type );
	}

	/* 4. Old categories that are now empty go away; their URLs 301 (inc/rebuild.php). */
	foreach ( array_keys( (array) ( $bundle['category_redirects'] ?? [] ) ) as $path ) {
		$old  = substr( $path, strlen( 'category/' ) );
		$term = get_term_by( 'slug', $old, 'category' );
		if ( $term && (int) $term->term_id !== (int) get_option( 'default_category' ) ) {
			$has_posts = get_posts( [ 'category' => $term->term_id, 'numberposts' => 1, 'fields' => 'ids', 'post_status' => 'any' ] );
			if ( ! $has_posts ) wp_delete_term( $term->term_id, 'category' );
		}
	}

	wp_defer_term_counting( false );
	kses_init();

	/* Featured images whose attachment already exists (existing installs). */
	bv_assign_featured_images();

	return $counts;
}

function bv_import_author(): int {
	$admins = get_users( [ 'role' => 'administrator', 'number' => 1, 'orderby' => 'ID', 'fields' => 'ID' ] );
	return $admins ? (int) $admins[0] : 1;
}

/** Write SEO title/description for both the theme and Yoast, never overwrite a value that exists. */
function bv_set_seo_meta( int $post_id, string $title, string $desc ): void {
	if ( $title ) {
		if ( ! get_post_meta( $post_id, '_bv_seo_title', true ) ) update_post_meta( $post_id, '_bv_seo_title', $title );
		if ( ! get_post_meta( $post_id, '_yoast_wpseo_title', true ) ) update_post_meta( $post_id, '_yoast_wpseo_title', $title );
	}
	if ( $desc ) {
		if ( ! get_post_meta( $post_id, '_bv_meta_description', true ) ) update_post_meta( $post_id, '_bv_meta_description', $desc );
		if ( ! get_post_meta( $post_id, '_yoast_wpseo_metadesc', true ) ) update_post_meta( $post_id, '_yoast_wpseo_metadesc', $desc );
	}
}

/** /blog/{post-slug}/ links became /{post-slug}/ when permalinks went flat. */
function bv_fix_blog_prefix_links( string $content, array $slugs ): string {
	if ( false === strpos( $content, '/blog/' ) ) return $content;
	$lookup = array_flip( $slugs );
	return preg_replace_callback(
		'#(href=")(?:https?://boomingventure\.com)?/blog/([a-z0-9\-]+)/?#',
		static function ( $m ) use ( $lookup ) {
			return isset( $lookup[ $m[2] ] ) ? $m[1] . '/' . $m[2] . '/' : $m[0];
		},
		$content
	);
}

/* ============================================================
 * Media import, in batches.
 * ============================================================ */

/** Ordered list: originals first (they generate the sizes), then stray size files. */
function bv_media_queue(): array {
	$bundle = bv_content_bundle();
	if ( ! $bundle ) return [];
	$media = (array) $bundle['media'];
	usort( $media, static fn( $a, $b ) => (int) $a['variant'] <=> (int) $b['variant'] );
	return $media;
}

function bv_attachment_by_file( string $rel ): int {
	global $wpdb;
	return (int) $wpdb->get_var( $wpdb->prepare(
		"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value = %s LIMIT 1",
		$rel
	) );
}

/**
 * Process the next batch.
 *
 * @return array{done:int,total:int,finished:bool,errors:array}
 */
function bv_import_media_batch( int $limit = BV_MEDIA_BATCH ): array {
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	@set_time_limit( 120 );

	$queue  = bv_media_queue();
	$total  = count( $queue );
	/* One batch at a time: the progress screen and WP-Cron can overlap. */
	if ( ! bv_acquire_lock( 'bv_media_lock', 180 ) ) {
		$state = (array) get_option( 'bv_media_state', [] );
		return [ 'done' => (int) ( $state['next'] ?? 0 ), 'total' => $total, 'finished' => ! empty( $state['done'] ), 'errors' => [], 'busy' => true ];
	}
	wp_cache_delete( 'bv_media_state', 'options' );
	$state  = (array) get_option( 'bv_media_state', [ 'next' => 0, 'done' => false ] );
	$next   = (int) ( $state['next'] ?? 0 );
	$errors = [];
	$upload = wp_upload_dir( null, false );

	for ( $i = $next; $i < $total && $i < $next + $limit; $i++ ) {
		$item = $queue[ $i ];
		$rel  = $item['file'];
		$src  = BV_THEME_DIR . '/import/media/' . $rel;
		$dest = trailingslashit( $upload['basedir'] ) . $rel;

		if ( ! file_exists( $dest ) ) {
			wp_mkdir_p( dirname( $dest ) );
			if ( ! @copy( $src, $dest ) ) {
				$errors[] = $rel;
				continue;
			}
		}
		if ( ! empty( $item['variant'] ) ) continue;
		if ( bv_attachment_by_file( $rel ) ) continue;

		$type = wp_check_filetype( basename( $dest ) );
		$id   = wp_insert_attachment( [
			'post_mime_type' => $type['type'] ?: 'image/jpeg',
			'post_title'     => $item['title'] ?: preg_replace( '/\.[^.]+$/', '', basename( $rel ) ),
			'post_status'    => 'inherit',
			'post_author'    => bv_import_author(),
		], $dest, 0, true );
		if ( is_wp_error( $id ) ) {
			$errors[] = $rel;
			continue;
		}
		update_post_meta( $id, '_wp_attached_file', $rel );
		if ( ! empty( $item['alt'] ) ) update_post_meta( $id, '_wp_attachment_image_alt', $item['alt'] );
		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $dest ) );
	}

	$done     = min( $total, $next + $limit );
	$finished = $done >= $total;
	update_option( 'bv_media_state', [ 'next' => $done, 'done' => $finished, 'errors' => $errors ], false );
	delete_option( 'bv_media_lock' );

	if ( $finished ) {
		bv_assign_featured_images();
		wp_clear_scheduled_hook( 'bv_media_cron' );
	}
	return [ 'done' => $done, 'total' => $total, 'finished' => $finished, 'errors' => $errors ];
}

function bv_assign_featured_images(): int {
	$set   = 0;
	$posts = get_posts( [ 'post_type' => 'post', 'numberposts' => -1, 'fields' => 'ids', 'meta_key' => '_bv_featured_file', 'post_status' => 'any' ] );
	foreach ( $posts as $post_id ) {
		$att = bv_attachment_by_file( (string) get_post_meta( $post_id, '_bv_featured_file', true ) );
		if ( $att && (int) get_post_thumbnail_id( $post_id ) !== $att ) {
			set_post_thumbnail( $post_id, $att );
			$set++;
		}
	}
	return $set;
}

/* WP-Cron fallback. */
add_filter( 'cron_schedules', static function ( $s ) {
	$s['bv_every_minute'] = [ 'interval' => 60, 'display' => 'Every minute (Booming Venture import)' ];
	return $s;
} );
add_action( 'bv_media_cron', static function () {
	$state = (array) get_option( 'bv_media_state', [] );
	if ( ! empty( $state['done'] ) ) {
		wp_clear_scheduled_hook( 'bv_media_cron' );
		return;
	}
	bv_import_media_batch();
} );

/* AJAX batch endpoint for the progress screen. */
add_action( 'wp_ajax_bv_import_media', static function () {
	if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Forbidden', 403 );
	check_ajax_referer( 'bv_import_media' );
	wp_send_json_success( bv_import_media_batch() );
} );

/* Tools -> Booming Venture import. */
add_action( 'admin_menu', static function () {
	add_management_page( 'Booming Venture import', 'Booming Venture import', 'manage_options', 'bv-import', 'bv_render_import_page' );
} );

function bv_render_import_page(): void {
	$state  = (array) get_option( 'bv_media_state', [ 'next' => 0, 'done' => false ] );
	$total  = count( bv_media_queue() );
	$posts  = (int) wp_count_posts( 'post' )->publish;
	$pages  = (int) wp_count_posts( 'page' )->publish;
	$bundle = bv_content_bundle();
	$nonce  = wp_create_nonce( 'bv_import_media' );
	$auto   = isset( $_GET['autostart'] ) && empty( $state['done'] );
	?>
	<div class="wrap">
		<h1>Booming Venture import</h1>
		<table class="widefat striped" style="max-width:720px;margin:1rem 0">
			<tbody>
				<tr><td>Content bundle</td><td><?php echo $bundle ? esc_html( count( $bundle['posts'] ) . ' posts, ' . count( $bundle['pages'] ) . ' pages, ' . count( $bundle['categories'] ) . ' categories (export ' . substr( (string) $bundle['fetched_at'], 0, 10 ) . ')' ) : 'missing'; ?></td></tr>
				<tr><td>Published posts</td><td><strong><?php echo (int) $posts; ?></strong></td></tr>
				<tr><td>Published pages</td><td><strong><?php echo (int) $pages; ?></strong></td></tr>
				<tr><td>Content import</td><td><?php echo esc_html( get_option( BV_INSTALL_FLAG ) ?: 'not run' ); ?></td></tr>
				<tr><td>Media</td><td><span id="bv-media-status"><?php echo (int) ( $state['next'] ?? 0 ); ?> / <?php echo (int) $total; ?></span><?php echo ! empty( $state['done'] ) ? ' &check; complete' : ''; ?></td></tr>
			</tbody>
		</table>
		<div style="max-width:720px;background:#e2e8f0;border-radius:6px;height:14px;overflow:hidden"><div id="bv-bar" style="height:14px;background:#0284c7;width:<?php echo $total ? (int) round( 100 * (int) ( $state['next'] ?? 0 ) / $total ) : 0; ?>%"></div></div>
		<p>
			<button class="button button-primary" id="bv-run" <?php disabled( ! empty( $state['done'] ) ); ?>>Import media now</button>
			<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=bv_reimport' ), 'bv_reimport' ) ); ?>">Re-run content import</a>
		</p>
		<p class="description">Images are copied into the Media Library in batches of <?php echo (int) BV_MEDIA_BATCH; ?>. You can leave this page; a background task finishes the job.</p>
	</div>
	<script>
	(function(){
		var btn=document.getElementById('bv-run'),st=document.getElementById('bv-media-status'),bar=document.getElementById('bv-bar');
		function step(){
			var f=new FormData();f.append('action','bv_import_media');f.append('_ajax_nonce','<?php echo esc_js( $nonce ); ?>');
			fetch(ajaxurl,{method:'POST',body:f,credentials:'same-origin'}).then(function(r){return r.json();}).then(function(j){
				if(!j||!j.success){st.textContent='Error, retrying...';setTimeout(step,3000);return;}
				var d=j.data;st.textContent=d.done+' / '+d.total;bar.style.width=(d.total?Math.round(100*d.done/d.total):100)+'%';
				if(d.finished){st.textContent+=' ✓ complete';btn.disabled=true;}else{setTimeout(step,d.busy?2000:50);}
			}).catch(function(){setTimeout(step,3000);});
		}
		btn&&btn.addEventListener('click',function(e){e.preventDefault();btn.disabled=true;step();});
		<?php if ( $auto ) : ?>btn.disabled=true;step();<?php endif; ?>
	})();
	</script>
	<?php
}

add_action( 'admin_post_bv_reimport', static function () {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );
	check_admin_referer( 'bv_reimport' );
	delete_option( BV_INSTALL_FLAG );
	bv_run_install();
	wp_safe_redirect( admin_url( 'tools.php?page=bv-import&autostart=1' ) );
	exit;
} );

/* Activation notice. */
add_action( 'admin_notices', static function () {
	if ( $r = get_transient( 'bv_install_success' ) ) {
		delete_transient( 'bv_install_success' );
		$state = (array) get_option( 'bv_media_state', [] );
		printf(
			'<div class="notice notice-success"><p><strong>Booming Venture installed.</strong> %d posts created, %d updated, %d pages created, %d refreshed, %d categories. %s</p></div>',
			(int) $r['posts_created'], (int) $r['posts_updated'], (int) $r['pages_created'], (int) $r['pages_updated'], (int) $r['categories'],
			empty( $state['done'] ) ? '<a class="button button-primary" href="' . esc_url( admin_url( 'tools.php?page=bv-import&autostart=1' ) ) . '">Finish image import</a>' : ''
		);
	}
	if ( $err = get_transient( 'bv_install_error' ) ) {
		delete_transient( 'bv_install_error' );
		echo '<div class="notice notice-error"><p><strong>Booming Venture import failed:</strong> ' . esc_html( $err ) . '</p></div>';
	}
} );

/* WP-CLI: `wp bv import` runs content + all media batches. */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'bv import', static function ( $args, $assoc ) {
		if ( ! empty( $assoc['force'] ) ) delete_option( BV_INSTALL_FLAG );
		bv_run_install();
		$r = get_transient( 'bv_install_success' );
		if ( $r ) WP_CLI::log( wp_json_encode( $r ) );
		if ( empty( $assoc['skip-media'] ) ) {
			do {
				$b = bv_import_media_batch( 10 );
				if ( ! empty( $b['busy'] ) ) { sleep( 2 ); continue; }
				WP_CLI::log( sprintf( 'Media %d/%d', $b['done'], $b['total'] ) );
			} while ( ! $b['finished'] );
		}
		WP_CLI::success( 'Booming Venture import complete.' );
	} );
}

/* ============================================================
 * Helpers kept from v1.
 * ============================================================ */

function bv_flush_all_caches(): void {
	if ( function_exists( 'wp_cache_flush' ) ) wp_cache_flush();
	if ( function_exists( 'delete_expired_transients' ) ) delete_expired_transients( true );
	delete_transient( 'global_styles' );
	delete_transient( 'gutenberg_global_styles' );
	if ( class_exists( 'WP_Theme_JSON_Resolver' ) && method_exists( 'WP_Theme_JSON_Resolver', 'clean_cached_data' ) ) {
		WP_Theme_JSON_Resolver::clean_cached_data();
	}
	if ( function_exists( 'opcache_reset' ) ) @opcache_reset();
	if ( function_exists( 'rocket_clean_domain' ) )       { @rocket_clean_domain(); }
	if ( function_exists( 'w3tc_flush_all' ) )            { @w3tc_flush_all(); }
	if ( function_exists( 'wp_cache_clear_cache' ) )      { @wp_cache_clear_cache(); }
	if ( has_action( 'litespeed_purge_all' ) )            { do_action( 'litespeed_purge_all' ); }
	if ( function_exists( 'sg_cachepress_purge_cache' ) ) { @sg_cachepress_purge_cache(); }
	if ( class_exists( 'autoptimizeCache' ) && method_exists( 'autoptimizeCache', 'clearall' ) ) { @autoptimizeCache::clearall(); }
	if ( has_action( 'cache_enabler_clear_complete_cache' ) ) { do_action( 'cache_enabler_clear_complete_cache' ); }
	if ( function_exists( 'breeze_clear_all_cache' ) )    { @breeze_clear_all_cache(); }
	do_action( 'bv_after_cache_flush' );
}

/**
 * Expand `<!-- wp:pattern {"slug":"booming-venture/X"} /-->` references in page
 * content into real blocks so the pages stay editable in the block editor.
 */
function bv_expand_pattern_refs( string $content ): string {
	return preg_replace_callback(
		'/<!--\s*wp:pattern\s+\{[^}]*"slug":"booming-venture\/([a-z0-9\-]+)"[^}]*\}\s*\/-->/i',
		static function ( $m ) {
			$file = BV_THEME_DIR . '/patterns/' . $m[1] . '.php';
			if ( ! file_exists( $file ) ) return $m[0];
			ob_start();
			try {
				include $file;
			} catch ( \Throwable $e ) {
				ob_end_clean();
				return $m[0];
			}
			$rendered = trim( (string) ob_get_clean() );
			return '' !== $rendered ? $rendered : $m[0];
		},
		$content
	);
}

function bv_configure_homepage(): void {
	$home = get_page_by_path( 'home', OBJECT, 'page' );
	$blog = get_page_by_path( 'blog', OBJECT, 'page' );
	if ( $home ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home->ID );
	}
	if ( $blog ) update_option( 'page_for_posts', $blog->ID );
}

function bv_configure_menus(): void {
	$locations = get_theme_mod( 'nav_menu_locations', [] );
	if ( ! is_array( $locations ) ) $locations = [];

	$menus = [
		'primary' => [ 'name' => 'Primary', 'items' => [
			[ 'About', 'about' ], [ 'UNIFY Framework', 'unify-framework' ], [ 'Services', 'services' ],
			[ 'Funnel Leak Calculator', 'funnel-calculator' ], [ 'ROI Forecaster', 'roi-forecaster' ], [ 'Blog', 'blog' ],
		] ],
		'footer' => [ 'name' => 'Footer', 'items' => [
			[ 'About Us', 'about' ], [ 'UNIFY Framework', 'unify-framework' ], [ 'Services', 'services' ], [ 'Blog', 'blog' ],
		] ],
		'legal' => [ 'name' => 'Legal', 'items' => [
			[ 'Privacy Policy', 'privacy-policy' ], [ 'Cookie Policy', 'cookie-policy' ], [ 'Terms of Service', 'terms-of-service' ], [ 'Disclaimer', 'disclaimer' ],
		] ],
		'tools' => [ 'name' => 'Tools', 'items' => [
			[ 'Funnel Leak Calculator', 'funnel-calculator' ], [ 'ROI Forecaster', 'roi-forecaster' ],
			[ 'Free Growth Guide', 'free-growth-guide' ], [ 'Boardroom Quickscan', 'boardroom-quickscan' ],
		] ],
	];

	foreach ( $menus as $location => $cfg ) {
		$menu = wp_get_nav_menu_object( $cfg['name'] );
		if ( ! $menu ) {
			$menu_id = wp_create_nav_menu( $cfg['name'] );
			if ( is_wp_error( $menu_id ) ) continue;
		} else {
			$menu_id = $menu->term_id;
			foreach ( (array) wp_get_nav_menu_items( $menu_id ) as $existing ) {
				wp_delete_post( $existing->ID, true );
			}
		}
		foreach ( $cfg['items'] as [ $title, $slug ] ) {
			$page = get_page_by_path( $slug, OBJECT, 'page' );
			if ( ! $page ) continue;
			wp_update_nav_menu_item( $menu_id, 0, [
				'menu-item-title'     => $title,
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
 * Yoast stores its title templates in the site language (Dutch on live:
 * "Archieven", "Je hebt gezocht naar") while the content is English, and
 * the organisation name and logo were empty. Fix only defaults.
 */
function bv_configure_yoast(): void {
	if ( ! defined( 'WPSEO_VERSION' ) ) return;
	$titles = (array) get_option( 'wpseo_titles', [] );
	$dutch  = static fn( $v ) => '' === (string) $v || (bool) preg_match( '/Archieven|Archief|gezocht|niet gevonden|auteur op|verscheen|Pagina niet/i', (string) $v );
	$want   = [
		'title-tax-category'          => '%%term_title%% %%page%% %%sep%% %%sitename%%',
		'social-title-tax-category'   => '%%term_title%%',
		'title-tax-post_tag'          => '%%term_title%% %%page%% %%sep%% %%sitename%%',
		'social-title-tax-post_tag'   => '%%term_title%%',
		'title-search-wpseo'          => 'Search results for %%searchphrase%% %%page%% %%sep%% %%sitename%%',
		'title-404-wpseo'             => 'Page not found %%sep%% %%sitename%%',
		'title-author-wpseo'          => '%%name%%, author at %%sitename%% %%page%%',
		'title-ptarchive-case_study'  => '%%pt_plural%% %%page%% %%sep%% %%sitename%%',
		'social-title-ptarchive-case_study' => '%%pt_plural%%',
		'title-tax-service_category'  => '%%term_title%% %%page%% %%sep%% %%sitename%%',
		'title-tax-industry'          => '%%term_title%% %%page%% %%sep%% %%sitename%%',
		'rssafter'                    => 'The post %%POSTLINK%% appeared first on %%BLOGLINK%%.',
		'breadcrumbs-archiveprefix'   => 'Archives for',
		'breadcrumbs-searchprefix'    => 'You searched for',
		'breadcrumbs-404crumb'        => 'Error 404: Page not found',
	];
	foreach ( $want as $k => $v ) {
		if ( ! isset( $titles[ $k ] ) || $dutch( $titles[ $k ] ) ) $titles[ $k ] = $v;
	}
	$identity = [
		'company_or_person' => 'company',
		'company_name'      => 'Booming Venture',
		'website_name'      => 'Booming Venture',
		'org-email'         => 'info@boomingventure.com',
		'org-vat-id'        => 'NL005211648B76',
		'org-founding-date' => '2024',
		'org-description'   => 'AI-powered B2B marketing and generative engine optimization (GEO) consultancy from Rotterdam, the Netherlands.',
	];
	foreach ( $identity as $k => $v ) {
		if ( empty( $titles[ $k ] ) ) $titles[ $k ] = $v;
	}
	update_option( 'wpseo_titles', $titles );

	$social = (array) get_option( 'wpseo_social', [] );
	$urls   = (array) ( $social['other_social_urls'] ?? [] );
	if ( ! in_array( 'https://www.linkedin.com/company/booming-venture/', $urls, true ) ) {
		$urls[] = 'https://www.linkedin.com/company/booming-venture/';
	}
	$social['other_social_urls'] = array_values( $urls );
	update_option( 'wpseo_social', $social );
}

/* [bv_read_time] shortcode for the blog cards. */
add_shortcode( 'bv_read_time', static function () {
	$id = get_the_ID();
	if ( ! $id ) return '';
	$rt = get_post_meta( $id, '_bv_read_time', true );
	if ( ! $rt ) {
		$words = str_word_count( wp_strip_all_tags( get_post_field( 'post_content', $id ) ) );
		$rt    = max( 1, (int) round( $words / 220 ) ) . ' min read';
	}
	return esc_html( $rt );
} );
