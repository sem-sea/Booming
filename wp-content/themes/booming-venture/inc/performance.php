<?php
/**
 * Front-end speed, from the November 2026 PageSpeed audit.
 *
 *  1. Minified WordPress core assets even when SCRIPT_DEBUG is on.
 *     The live site loaded jquery.js (279 KiB) instead of jquery.min.js.
 *  2. Inline more small block stylesheets, fewer render-blocking files.
 *  3. Browser cache for static files via a marked block in .htaccess
 *     (mod_expires, supported on STRATO). Written once on upgrade and
 *     removed again if the site does not answer cleanly afterwards.
 *
 * @package BoomingVenture
 */

defined( 'ABSPATH' ) || exit;

/* ------------------------------------------------------------------
 * 1. Core .min swap. Only on the front end, only for files under
 *    wp-includes, only when the .min file exists on disk.
 * ------------------------------------------------------------------ */
function bv_core_min_src( $src ) {
	if ( ! defined( 'SCRIPT_DEBUG' ) || ! SCRIPT_DEBUG || is_admin() ) return $src;
	if ( ! is_string( $src ) || false === strpos( $src, '/' . WPINC . '/' ) ) return $src;
	if ( ! preg_match( '#/' . preg_quote( WPINC, '#' ) . '/(.+?)(?<!\.min)\.(js|css)(\?.*)?$#', $src, $m ) ) return $src;
	$min_rel = $m[1] . '.min.' . $m[2];
	if ( ! file_exists( ABSPATH . WPINC . '/' . $min_rel ) ) return $src;
	return str_replace( '/' . WPINC . '/' . $m[1] . '.' . $m[2], '/' . WPINC . '/' . $min_rel, $src );
}
add_filter( 'script_loader_src', 'bv_core_min_src', 20 );
add_filter( 'style_loader_src', 'bv_core_min_src', 20 );
add_filter( 'script_module_loader_src', 'bv_core_min_src', 20 );

/* ------------------------------------------------------------------
 * 2. Inline block styles up to 50 KB in total (core default 20 KB).
 * ------------------------------------------------------------------ */
add_filter( 'styles_inline_size_limit', static fn() => 50000 );

/* ------------------------------------------------------------------
 * 3. .htaccess browser cache.
 * ------------------------------------------------------------------ */
function bv_htaccess_cache_rules(): array {
	return [
		'<IfModule mod_expires.c>',
		'ExpiresActive On',
		'ExpiresByType text/css "access plus 1 years"',
		'ExpiresByType application/javascript "access plus 1 years"',
		'ExpiresByType text/javascript "access plus 1 years"',
		'ExpiresByType application/x-javascript "access plus 1 years"',
		'ExpiresByType font/woff2 "access plus 1 years"',
		'ExpiresByType application/font-woff2 "access plus 1 years"',
		'ExpiresByType image/svg+xml "access plus 6 months"',
		'ExpiresByType image/webp "access plus 6 months"',
		'ExpiresByType image/avif "access plus 6 months"',
		'ExpiresByType image/jpeg "access plus 6 months"',
		'ExpiresByType image/png "access plus 6 months"',
		'ExpiresByType image/gif "access plus 6 months"',
		'ExpiresByType image/x-icon "access plus 6 months"',
		'</IfModule>',
	];
}

/**
 * Write the cache block, then check the site still answers. Any doubt
 * (error, timeout, 5xx) removes the block again. Returns the outcome.
 */
function bv_apply_htaccess_cache(): string {
	if ( ! function_exists( 'insert_with_markers' ) ) require_once ABSPATH . 'wp-admin/includes/misc.php';
	if ( ! function_exists( 'get_home_path' ) ) require_once ABSPATH . 'wp-admin/includes/file.php';
	global $is_apache;
	if ( empty( $is_apache ) ) return 'skipped: not apache';
	$file = get_home_path() . '.htaccess';
	if ( ! file_exists( $file ) || ! wp_is_writable( $file ) ) return 'skipped: .htaccess not writable';

	if ( ! insert_with_markers( $file, 'BoomingVenture cache', bv_htaccess_cache_rules() ) ) return 'skipped: write failed';

	$probe = BV_THEME_URI . '/assets/fonts/OFL.txt?bvprobe=' . time();
	$res   = wp_remote_get( $probe, [ 'timeout' => 10, 'sslverify' => false, 'redirection' => 2 ] );
	$code  = is_wp_error( $res ) ? 0 : (int) wp_remote_retrieve_response_code( $res );
	$home  = wp_remote_get( home_url( '/?bvprobe=' . time() ), [ 'timeout' => 15, 'sslverify' => false, 'redirection' => 2 ] );
	$hcode = is_wp_error( $home ) ? 0 : (int) wp_remote_retrieve_response_code( $home );

	if ( 200 !== $code || $hcode >= 500 || 0 === $hcode ) {
		insert_with_markers( $file, 'BoomingVenture cache', [] );
		return "reverted: probe {$code}, home {$hcode}";
	}
	return 'applied';
}

/* ------------------------------------------------------------------
 * 4. The homepage has no images above the fold (the hero is text), so
 *    every image there may load lazily. Elsewhere keep the core default.
 * ------------------------------------------------------------------ */
add_filter( 'wp_omit_loading_attr_threshold', static function ( $threshold ) {
	return is_front_page() ? 0 : $threshold;
} );
