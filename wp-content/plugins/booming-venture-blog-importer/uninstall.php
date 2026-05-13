<?php
/**
 * Booming Venture Blog Importer , uninstall cleanup.
 *
 * Runs when the plugin is deleted from the Plugins screen. Removes only
 * the two options this plugin writes. Imported blog posts are left in
 * place , the user did not request content deletion.
 *
 * @package BoomingVentureBlogImporter
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'bvbi_last_run' );
delete_option( 'bvbi_last_log' );

if ( is_multisite() ) {
	$sites = get_sites( [ 'fields' => 'ids', 'number' => 0 ] );
	foreach ( $sites as $site_id ) {
		switch_to_blog( (int) $site_id );
		delete_option( 'bvbi_last_run' );
		delete_option( 'bvbi_last_log' );
		restore_current_blog();
	}
}
