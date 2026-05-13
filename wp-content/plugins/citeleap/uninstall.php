<?php
/**
 * CiteLeap , uninstall cleanup.
 *
 * @package CiteLeap
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

$opts = [
	'citeleap_api_keys',
	'citeleap_models',
	'citeleap_prompts',
	'citeleap_schedule',
	'citeleap_queue',
	'citeleap_log',
	'citeleap_token_usage',
	'citeleap_budget_caps',
	'citeleap_refresh',
];
foreach ( $opts as $opt ) delete_option( $opt );

if ( is_multisite() ) {
	$sites = get_sites( [ 'fields' => 'ids', 'number' => 0 ] );
	foreach ( $sites as $site_id ) {
		switch_to_blog( (int) $site_id );
		foreach ( $opts as $opt ) delete_option( $opt );
		restore_current_blog();
	}
}
