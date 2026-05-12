<?php
/**
 * Booming Venture — theme bootstrap.
 *
 * @package BoomingVenture
 */

defined( 'ABSPATH' ) || exit;

define( 'BV_THEME_VERSION', '1.0.0' );
define( 'BV_THEME_DIR', get_template_directory() );
define( 'BV_THEME_URI', get_template_directory_uri() );

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
