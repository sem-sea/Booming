<?php
/**
 * Booming Venture Brevo Form Fix , uninstall cleanup.
 *
 * Removes only the diagnostic log option. The Brevo plugin and all
 * other plugins / themes are untouched.
 *
 * @package BoomingVentureBrevoFix
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'bvbf_event_log' );

if ( is_multisite() ) {
	$sites = get_sites( [ 'fields' => 'ids', 'number' => 0 ] );
	foreach ( $sites as $site_id ) {
		switch_to_blog( (int) $site_id );
		delete_option( 'bvbf_event_log' );
		restore_current_blog();
	}
}
