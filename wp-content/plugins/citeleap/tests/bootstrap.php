<?php
/**
 * CiteLeap , PHPUnit bootstrap.
 *
 * Loads the commercial-layer classes against a minimal in-memory stub
 * of the WordPress functions the plugin reads. Heavy WP integration
 * (post types, REST, cron, admin pages) is out of scope for this suite
 * , the goal is to smoke-test the pure-PHP commercial layer (credits,
 * plans, license, top-ups) without spinning up MySQL or the WP test
 * install.
 *
 * @package CiteLeap\Tests
 */

declare( strict_types=1 );

/* ABSPATH so the plugin's `defined( 'ABSPATH' ) || exit;` guards pass. */
if ( ! defined( 'ABSPATH' ) ) define( 'ABSPATH', __DIR__ . '/' );

define( 'CITELEAP_OPTION_CREDITS', 'citeleap_credits' );
define( 'CITELEAP_NONCE', 'citeleap_action' );
const DAY_IN_SECONDS = 86400;

/* In-memory storage replacing wp_options. Reset between tests via
 * citeleap_test_reset_options(). */
$GLOBALS['citeleap_test_options'] = [];
$GLOBALS['citeleap_test_actions'] = [];
$GLOBALS['citeleap_test_filters'] = [];
$GLOBALS['citeleap_test_log']     = [];

function citeleap_test_reset_options(): void {
	$GLOBALS['citeleap_test_options'] = [];
	$GLOBALS['citeleap_test_log']     = [];
}

/* ---- option store ---- */
if ( ! function_exists( 'get_option' ) ) {
	function get_option( string $name, $default = false ) {
		return $GLOBALS['citeleap_test_options'][ $name ] ?? $default;
	}
}
if ( ! function_exists( 'update_option' ) ) {
	function update_option( string $name, $value, $autoload = null ): bool {
		$GLOBALS['citeleap_test_options'][ $name ] = $value;
		return true;
	}
}
if ( ! function_exists( 'delete_option' ) ) {
	function delete_option( string $name ): bool {
		unset( $GLOBALS['citeleap_test_options'][ $name ] );
		return true;
	}
}

/* ---- hooks (record only , tests can inspect) ---- */
if ( ! function_exists( 'add_action' ) ) {
	function add_action( string $hook, $cb, int $priority = 10, int $args = 1 ): bool {
		$GLOBALS['citeleap_test_actions'][ $hook ][] = [ $cb, $priority, $args ];
		return true;
	}
}
if ( ! function_exists( 'add_filter' ) ) {
	function add_filter( string $hook, $cb, int $priority = 10, int $args = 1 ): bool {
		$GLOBALS['citeleap_test_filters'][ $hook ][] = [ $cb, $priority, $args ];
		return true;
	}
}
if ( ! function_exists( 'do_action' ) ) {
	function do_action( string $hook, ...$args ): void {
		foreach ( ( $GLOBALS['citeleap_test_actions'][ $hook ] ?? [] ) as [ $cb, $priority, $argc ] ) {
			call_user_func_array( $cb, array_slice( $args, 0, $argc ) );
		}
	}
}

/* ---- time + i18n ---- */
if ( ! function_exists( 'current_time' ) ) {
	function current_time( string $type, int $gmt = 0 ): string {
		if ( 'mysql' === $type )  return date( 'Y-m-d H:i:s' );
		if ( 'timestamp' === $type ) return time();
		return date( $type );
	}
}
if ( ! function_exists( '__' ) ) {
	function __( $text, $domain = '' ) { return $text; }
}
if ( ! function_exists( '_x' ) ) {
	function _x( $text, $ctx, $domain = '' ) { return $text; }
}
if ( ! function_exists( 'esc_html' ) ) {
	function esc_html( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' ); }
}
if ( ! function_exists( 'esc_url' ) ) {
	function esc_url( $s ) { return filter_var( (string) $s, FILTER_SANITIZE_URL ); }
}
if ( ! function_exists( 'esc_attr' ) ) {
	function esc_attr( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' ); }
}
if ( ! function_exists( 'sanitize_key' ) ) {
	function sanitize_key( $s ) { return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $s ) ); }
}
if ( ! function_exists( 'sanitize_text_field' ) ) {
	function sanitize_text_field( $s ) { return trim( strip_tags( (string) $s ) ); }
}
if ( ! function_exists( 'wp_unslash' ) ) {
	function wp_unslash( $v ) { return is_string( $v ) ? stripslashes( $v ) : $v; }
}
if ( ! function_exists( 'get_bloginfo' ) ) {
	function get_bloginfo( $key ) { return 'admin@example.test'; }
}
if ( ! function_exists( 'admin_url' ) ) {
	function admin_url( $p = '' ) { return 'https://example.test/wp-admin/' . ltrim( $p, '/' ); }
}
if ( ! function_exists( 'wp_nonce_url' ) ) {
	function wp_nonce_url( $url, $action ) { return $url . ( false === strpos( $url, '?' ) ? '?' : '&' ) . '_wpnonce=stub'; }
}
if ( ! function_exists( 'plugin_dir_path' ) ) {
	function plugin_dir_path( $f ) { return dirname( $f ) . '/'; }
}
if ( ! function_exists( 'plugin_dir_url' ) ) {
	function plugin_dir_url( $f ) { return 'https://example.test/wp-content/plugins/citeleap/'; }
}

/* ---- in-test stub for CiteLeap_Log so credits.php can call it ---- */
if ( ! class_exists( 'CiteLeap_Log' ) ) {
	class CiteLeap_Log {
		public static function add( string $event, string $detail = '', string $severity = 'info' ): void {
			$GLOBALS['citeleap_test_log'][] = compact( 'event', 'detail', 'severity' );
		}
	}
}

/* ---- load the commercial-layer classes under test ---- */
$root = dirname( __DIR__ );
require_once $root . '/includes/license.php';
require_once $root . '/includes/plan.php';
require_once $root . '/includes/credits.php';
require_once $root . '/includes/topups.php';
