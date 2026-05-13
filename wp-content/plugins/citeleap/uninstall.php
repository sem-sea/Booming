<?php
/**
 * CiteLeap , uninstall cleanup.
 *
 * @package CiteLeap
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

foreach ( [
	'citeleap_api_keys',
	'citeleap_models',
	'citeleap_prompts',
	'citeleap_schedule',
	'citeleap_queue',
	'citeleap_log',
] as $opt ) {
	delete_option( $opt );
}

if ( is_multisite() ) {
	$sites = get_sites( [ 'fields' => 'ids', 'number' => 0 ] );
	foreach ( $sites as $site_id ) {
		switch_to_blog( (int) $site_id );
		foreach ( [
			'citeleap_api_keys',
			'citeleap_models',
			'citeleap_prompts',
			'citeleap_schedule',
			'citeleap_queue',
			'citeleap_log',
		] as $opt ) delete_option( $opt );
		restore_current_blog();
	}
}
