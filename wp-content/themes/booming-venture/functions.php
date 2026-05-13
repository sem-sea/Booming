<?php
/**
 * Booming Venture ,  theme bootstrap.
 *
 * @package BoomingVenture
 */

defined( 'ABSPATH' ) || exit;

define( 'BV_THEME_VERSION', '1.5.3' );
define( 'BV_THEME_DIR', get_template_directory() );
define( 'BV_THEME_URI', get_template_directory_uri() );

/**
 * Per-asset version string ,  uses filemtime() in dev so any CSS / JS
 * edit auto-busts the browser and proxy cache. Falls back to the
 * BV_THEME_VERSION constant if the file is unreadable.
 */
function bv_asset_ver( string $relative ): string {
	$path = BV_THEME_DIR . '/' . ltrim( $relative, '/' );
	$mtime = @filemtime( $path );
	return $mtime ? (string) $mtime : BV_THEME_VERSION;
}

require_once BV_THEME_DIR . '/inc/icons.php';
require_once BV_THEME_DIR . '/inc/setup.php';
require_once BV_THEME_DIR . '/inc/enqueue.php';
require_once BV_THEME_DIR . '/inc/cpt.php';
require_once BV_THEME_DIR . '/inc/blocks.php';
require_once BV_THEME_DIR . '/inc/patterns.php';
require_once BV_THEME_DIR . '/inc/seo.php';
require_once BV_THEME_DIR . '/inc/geo.php';
require_once BV_THEME_DIR . '/inc/installer.php';
require_once BV_THEME_DIR . '/inc/security.php';
require_once BV_THEME_DIR . '/inc/integrations.php';
