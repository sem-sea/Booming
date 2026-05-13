<?php
/**
 * Plugin Name:       CiteLeap
 * Plugin URI:        https://boomingventure.com/citeleap
 * Description:       AI-powered blog content engine. Uses a reasoning model to ideate, a writing model to draft, and WP-Cron to publish on schedule. Bring your own API keys for Anthropic Claude, OpenAI, or Google Gemini. Default output format is GEO/AEO compliant (May 2026 Bible). Designed for lead-generation sites.
 * Version:           1.3.0
 * Requires at least: 6.6
 * Requires PHP:      8.0
 * Author:            CiteLeap (by Booming Venture)
 * Author URI:        https://boomingventure.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       citeleap
 * Update URI:        false
 *
 * @package CiteLeap
 */

defined( 'ABSPATH' ) || exit;

const CITELEAP_VERSION         = '1.3.0';
const CITELEAP_OPTION_API_KEYS = 'citeleap_api_keys';
const CITELEAP_OPTION_MODELS   = 'citeleap_models';
const CITELEAP_OPTION_PROMPTS  = 'citeleap_prompts';
const CITELEAP_OPTION_SCHEDULE = 'citeleap_schedule';
const CITELEAP_OPTION_QUEUE    = 'citeleap_queue';
const CITELEAP_OPTION_LOG      = 'citeleap_log';
const CITELEAP_OPTION_USAGE    = 'citeleap_token_usage';   // per-month token + cost ledger
const CITELEAP_OPTION_CAPS     = 'citeleap_budget_caps';   // per-provider monthly USD cap
const CITELEAP_OPTION_REFRESH  = 'citeleap_refresh';       // refresh mode + cadence
const CITELEAP_META_SOURCE     = '_citeleap_source';       // 'auto' | 'manual' | 'refresh'
const CITELEAP_META_IDEA       = '_citeleap_idea_id';
const CITELEAP_META_PROVIDER   = '_citeleap_provider';
const CITELEAP_META_REFRESH_N  = '_citeleap_refresh_count';
const CITELEAP_META_REFRESH_AT = '_citeleap_last_refreshed';
const CITELEAP_NONCE           = 'citeleap_action';
const CITELEAP_CRON_HOURLY     = 'citeleap_cron_hourly';

define( 'CITELEAP_FILE', __FILE__ );
define( 'CITELEAP_DIR', plugin_dir_path( __FILE__ ) );
define( 'CITELEAP_URL', plugin_dir_url( __FILE__ ) );

require_once CITELEAP_DIR . 'includes/crypto.php';
require_once CITELEAP_DIR . 'includes/prompts.php';
require_once CITELEAP_DIR . 'includes/pricing.php';
require_once CITELEAP_DIR . 'includes/usage.php';
require_once CITELEAP_DIR . 'includes/llm.php';
require_once CITELEAP_DIR . 'includes/generator.php';
require_once CITELEAP_DIR . 'includes/refresh.php';
require_once CITELEAP_DIR . 'includes/scheduler.php';
require_once CITELEAP_DIR . 'includes/dashboard.php';
require_once CITELEAP_DIR . 'includes/planner.php';
require_once CITELEAP_DIR . 'includes/settings.php';

/* ---------------------------------------------------------------------
 * Activation , verify prerequisites, schedule cron, register uninstall.
 * --------------------------------------------------------------------- */
register_activation_hook( CITELEAP_FILE, function () {
	if ( version_compare( PHP_VERSION, '8.0', '<' ) ) {
		deactivate_plugins( plugin_basename( CITELEAP_FILE ) );
		wp_die( esc_html__( 'CiteLeap requires PHP 8.0 or higher.', 'citeleap' ) );
	}
	if ( ! wp_next_scheduled( CITELEAP_CRON_HOURLY ) ) {
		wp_schedule_event( time() + 60, 'hourly', CITELEAP_CRON_HOURLY );
	}
	register_uninstall_hook( CITELEAP_FILE, 'citeleap_on_uninstall' );
} );

register_deactivation_hook( CITELEAP_FILE, function () {
	$ts = wp_next_scheduled( CITELEAP_CRON_HOURLY );
	if ( $ts ) wp_unschedule_event( $ts, CITELEAP_CRON_HOURLY );
} );

function citeleap_on_uninstall(): void {
	if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) return;
	delete_option( CITELEAP_OPTION_API_KEYS );
	delete_option( CITELEAP_OPTION_MODELS );
	delete_option( CITELEAP_OPTION_PROMPTS );
	delete_option( CITELEAP_OPTION_SCHEDULE );
	delete_option( CITELEAP_OPTION_QUEUE );
	delete_option( CITELEAP_OPTION_LOG );
}

/* i18n */
add_action( 'init', function () {
	load_plugin_textdomain( 'citeleap', false, dirname( plugin_basename( CITELEAP_FILE ) ) . '/languages' );
} );

/* Cron hook wires through to the scheduler tick. */
add_action( CITELEAP_CRON_HOURLY, [ 'CiteLeap_Scheduler', 'tick' ] );
