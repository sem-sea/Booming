<?php
/**
 * Booming Venture Importer , uninstall cleanup.
 *
 * Runs when the plugin is DELETED from the Plugins screen (not on
 * deactivation). Removes the two options the plugin writes. Imported
 * pages, posts and services are left in place , the user did not ask
 * for content deletion.
 *
 * @package BoomingVentureImporter
 */

// Exit if not called by WordPress uninstall mechanism.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'bvi_content_imported' );
delete_option( 'bvi_last_import_log' );

/* Multisite cleanup (in case the plugin was activated network-wide). */
if ( is_multisite() ) {
	$sites = get_sites( [ 'fields' => 'ids', 'number' => 0 ] );
	foreach ( $sites as $site_id ) {
		switch_to_blog( (int) $site_id );
		delete_option( 'bvi_content_imported' );
		delete_option( 'bvi_last_import_log' );
		restore_current_blog();
	}
}
