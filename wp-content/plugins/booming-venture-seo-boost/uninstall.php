<?php
/**
 * Booming Venture SEO Boost , uninstall cleanup.
 * Plugin writes no options. Nothing to delete.
 *
 * @package BoomingVentureSeoBoost
 */
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

delete_option( 'bvseo_indexnow_key' );

if ( is_multisite() ) {
	$sites = get_sites( [ 'fields' => 'ids', 'number' => 0 ] );
	foreach ( $sites as $site_id ) {
		switch_to_blog( (int) $site_id );
		delete_option( 'bvseo_indexnow_key' );
		restore_current_blog();
	}
}
