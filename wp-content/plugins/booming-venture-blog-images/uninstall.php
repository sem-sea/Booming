<?php
/**
 * Booming Venture Blog Images , uninstall cleanup.
 *
 * The plugin stores no options. Featured-image attachments and
 * _thumbnail_id post meta entries are standard WordPress and are
 * left in place so any future plugin / theme still has access.
 *
 * @package BoomingVentureBlogImages
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/* Remove the only option this plugin writes (the random-image pool).
 * Featured-image attachments and _thumbnail_id post meta entries are
 * standard WordPress and are left in place so any future plugin /
 * theme still has access. The _bvimg_random_assigned post meta flag
 * is left in place too, harmless if the plugin is reinstalled later. */
delete_option( 'bvimg_pool_ids' );

if ( is_multisite() ) {
	$sites = get_sites( [ 'fields' => 'ids', 'number' => 0 ] );
	foreach ( $sites as $site_id ) {
		switch_to_blog( (int) $site_id );
		delete_option( 'bvimg_pool_ids' );
		restore_current_blog();
	}
}
