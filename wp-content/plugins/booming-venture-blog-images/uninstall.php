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

/* Nothing to delete , the plugin writes no options. */
