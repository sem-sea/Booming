<?php
/**
 * One-time self-update to Booming Venture 1.10.1.
 *
 * Downloads the release zip from GitHub (pinned commit), checks its
 * SHA-256, and installs it over this theme with WordPress' own theme
 * upgrader (the same path as "Upload theme > Replace current"). The
 * 1.10.1 package does not contain this file, so it removes itself.
 *
 * @package BoomingVenture
 */

defined( 'ABSPATH' ) || exit;

const BV_SU_URL    = 'https://raw.githubusercontent.com/sem-sea/Booming/12bc23f5c79821ccf585156e9e820938fbcb9c9e/wp-content/themes/booming-venture-1.10.1.zip';
const BV_SU_SHA256 = '8484bfa658cc91b341ce0b557f7d98821af144baa4f73f6f9f6aee3229488977';

function bv_self_update_run(): void {
	if ( get_option( 'bv_self_update_done' ) === BV_SU_SHA256 ) return;
	if ( ! current_user_can( 'install_themes' ) && ! current_user_can( 'update_themes' ) ) return;

	if ( ! add_option( 'bv_self_update_lock', time(), '', false ) ) {
		if ( time() - (int) get_option( 'bv_self_update_lock' ) < 600 ) return;
		update_option( 'bv_self_update_lock', time(), false );
	}
	@set_time_limit( 300 );

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/misc.php';
	require_once ABSPATH . 'wp-admin/includes/theme.php';
	require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

	$fail = static function ( string $msg ): void {
		update_option( 'bv_self_update_status', $msg, false );
		delete_option( 'bv_self_update_lock' );
	};

	$tmp = download_url( BV_SU_URL, 180 );
	if ( is_wp_error( $tmp ) ) {
		$fail( 'download failed: ' . $tmp->get_error_message() );
		return;
	}
	if ( ! hash_equals( BV_SU_SHA256, (string) hash_file( 'sha256', $tmp ) ) ) {
		@unlink( $tmp );
		$fail( 'checksum mismatch' );
		return;
	}
	if ( ! WP_Filesystem() ) {
		@unlink( $tmp );
		$fail( 'filesystem not available' );
		return;
	}

	$upgrader = new Theme_Upgrader( new Automatic_Upgrader_Skin() );
	$result   = $upgrader->install( $tmp, [ 'overwrite_package' => true, 'clear_update_cache' => true ] );
	@unlink( $tmp );

	if ( is_wp_error( $result ) || ! $result ) {
		$msg = is_wp_error( $result ) ? $result->get_error_message() : implode( ' | ', (array) $upgrader->skin->get_upgrade_messages() );
		$fail( 'install failed: ' . $msg );
		return;
	}
	update_option( 'bv_self_update_done', BV_SU_SHA256, false );
	update_option( 'bv_self_update_status', 'installed 1.10.1 at ' . gmdate( 'c' ), false );
	delete_option( 'bv_self_update_lock' );
}

add_filter( 'rest_pre_dispatch', static function ( $result ) {
	bv_self_update_run();
	return $result;
}, 1 );
add_action( 'admin_init', 'bv_self_update_run', 1 );
