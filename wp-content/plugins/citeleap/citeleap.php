<?php
/**
 * Plugin Name:       CiteLeap
 * Plugin URI:        https://boomingventure.com/citeleap
 * Description:       AI-powered blog content engine v2.0. Multi-LLM router (Claude / OpenAI / Gemini, BYOK) ideates, researches with real web citations, drafts long-form GEO/AEO posts that link to sources AND to your own existing posts, picks a Featured image from your Media Library pool, ships schema + Open Graph + IndexNow on publish, supports multilingual output with hreflang. Refresh existing posts. Pin publish dates. Pause / resume / retry per row. Works on any active theme.
 * Version:           2.8.0
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

const CITELEAP_VERSION         = '2.8.0';
const CITELEAP_DEFAULT_TZ      = 'Europe/Amsterdam';
const CITELEAP_META_PENDING    = '_citeleap_pending_refresh';
const CITELEAP_OPTION_API_KEYS = 'citeleap_api_keys';
const CITELEAP_OPTION_MODELS   = 'citeleap_models';
const CITELEAP_OPTION_PROMPTS  = 'citeleap_prompts';
const CITELEAP_OPTION_SCHEDULE = 'citeleap_schedule';
const CITELEAP_OPTION_QUEUE    = 'citeleap_queue';
const CITELEAP_OPTION_LOG      = 'citeleap_log';
const CITELEAP_OPTION_USAGE    = 'citeleap_token_usage';
const CITELEAP_OPTION_CAPS     = 'citeleap_budget_caps';
const CITELEAP_OPTION_REFRESH  = 'citeleap_refresh';
/* v2.0 additions */
const CITELEAP_OPTION_IMAGES   = 'citeleap_images';        // image pool + render settings
const CITELEAP_OPTION_SEO      = 'citeleap_seo';           // schema / OG / IndexNow toggles + key
const CITELEAP_OPTION_RESEARCH = 'citeleap_research';      // research mode + max sources
const CITELEAP_OPTION_I18N     = 'citeleap_i18n';          // language pool + plugin detection
/* v2.5 commercial layer */
const CITELEAP_OPTION_CREDITS  = 'citeleap_credits';       // per-cycle credit ledger
const CITELEAP_META_SOURCE     = '_citeleap_source';
const CITELEAP_META_IDEA       = '_citeleap_idea_id';
const CITELEAP_META_PROVIDER   = '_citeleap_provider';
const CITELEAP_META_REFRESH_N  = '_citeleap_refresh_count';
const CITELEAP_META_REFRESH_AT = '_citeleap_last_refreshed';
const CITELEAP_META_SOURCES    = '_citeleap_sources';      // per-post research citation list
const CITELEAP_META_LINKS      = '_citeleap_internal_links'; // per-post internal-link slugs used
const CITELEAP_META_LANG       = '_citeleap_lang';         // per-post language code
const CITELEAP_META_RANDOM_IMG = '_citeleap_random_img';   // post had image auto-assigned
const CITELEAP_NONCE           = 'citeleap_action';
const CITELEAP_CRON_HOURLY     = 'citeleap_cron_hourly';

define( 'CITELEAP_FILE', __FILE__ );
define( 'CITELEAP_DIR', plugin_dir_path( __FILE__ ) );
define( 'CITELEAP_URL', plugin_dir_url( __FILE__ ) );

require_once CITELEAP_DIR . 'includes/crypto.php';
require_once CITELEAP_DIR . 'includes/license.php';  // NEW v2.5
require_once CITELEAP_DIR . 'includes/plan.php';     // NEW v2.5
require_once CITELEAP_DIR . 'includes/credits.php';  // NEW v2.5
require_once CITELEAP_DIR . 'includes/topups.php';   // NEW v2.6
require_once CITELEAP_DIR . 'includes/prompts.php';
require_once CITELEAP_DIR . 'includes/pricing.php';
require_once CITELEAP_DIR . 'includes/usage.php';
require_once CITELEAP_DIR . 'includes/llm.php';
require_once CITELEAP_DIR . 'includes/layout.php';   // NEW v2.0
require_once CITELEAP_DIR . 'includes/linking.php';  // NEW v2.0
require_once CITELEAP_DIR . 'includes/research.php'; // NEW v2.0
require_once CITELEAP_DIR . 'includes/i18n.php';     // NEW v2.0
require_once CITELEAP_DIR . 'includes/images.php';   // NEW v2.0
require_once CITELEAP_DIR . 'includes/seo.php';      // NEW v2.0
require_once CITELEAP_DIR . 'includes/voice.php';    // NEW v2.1
require_once CITELEAP_DIR . 'includes/calendar.php'; // NEW v2.1
require_once CITELEAP_DIR . 'includes/generator.php';
require_once CITELEAP_DIR . 'includes/refresh.php';
require_once CITELEAP_DIR . 'includes/actions.php';
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
	/* CET default: if the site has no timezone_string AND gmt_offset
	 * is 0 (vanilla WP install on a fresh DB), promote to CET so
	 * scheduled posts publish at sensible wall-clock times. We only
	 * touch the site option if the operator has not picked one. */
	$site_tz   = (string) get_option( 'timezone_string', '' );
	$gmt_off   = (string) get_option( 'gmt_offset', '0' );
	if ( '' === $site_tz && in_array( $gmt_off, [ '0', '0.0', '0,0', '' ], true ) ) {
		update_option( 'timezone_string', CITELEAP_DEFAULT_TZ );
	}
} );

/**
 * Plugin-local timezone helper. Returns the site timezone first; if
 * the site is on UTC and the operator never picked, falls back to
 * Europe/Amsterdam (CET / CEST per DST). Used for display + slot
 * calculation everywhere in the plugin.
 */
function citeleap_tz(): DateTimeZone {
	$tz = (string) get_option( 'timezone_string', '' );
	if ( ! $tz ) $tz = CITELEAP_DEFAULT_TZ;
	try {
		return new DateTimeZone( $tz );
	} catch ( \Throwable $e ) {
		return new DateTimeZone( CITELEAP_DEFAULT_TZ );
	}
}

function citeleap_format( int $ts, string $fmt = 'Y-m-d H:i' ): string {
	if ( $ts <= 0 ) return '';
	$d = ( new DateTimeImmutable( '@' . $ts ) )->setTimezone( citeleap_tz() );
	return $d->format( $fmt ) . ' ' . $d->format( 'T' );
}

register_deactivation_hook( CITELEAP_FILE, function () {
	$ts = wp_next_scheduled( CITELEAP_CRON_HOURLY );
	if ( $ts ) wp_unschedule_event( $ts, CITELEAP_CRON_HOURLY );
} );

function citeleap_on_uninstall(): void {
	if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) return;
	foreach ( [
		CITELEAP_OPTION_API_KEYS, CITELEAP_OPTION_MODELS, CITELEAP_OPTION_PROMPTS,
		CITELEAP_OPTION_SCHEDULE, CITELEAP_OPTION_QUEUE,    CITELEAP_OPTION_LOG,
		CITELEAP_OPTION_USAGE,    CITELEAP_OPTION_CAPS,     CITELEAP_OPTION_REFRESH,
		CITELEAP_OPTION_IMAGES,   CITELEAP_OPTION_SEO,      CITELEAP_OPTION_RESEARCH,
		CITELEAP_OPTION_I18N,     CITELEAP_OPTION_CREDITS,
	] as $opt ) delete_option( $opt );
}

/* i18n */
add_action( 'init', function () {
	load_plugin_textdomain( 'citeleap', false, dirname( plugin_basename( CITELEAP_FILE ) ) . '/languages' );
} );

/* Cron hook wires through to the scheduler tick. */
add_action( CITELEAP_CRON_HOURLY, [ 'CiteLeap_Scheduler', 'tick' ] );

/* ---------------------------------------------------------------------
 * Freemius bootstrap (commercial layer).
 *
 * Drop the SDK at vendor/freemius/wordpress-sdk/start.php and add the
 * real plugin_id + public_key from your Freemius dashboard. Until then
 * the SDK is not loaded and the license layer falls back to "free"
 * (or "dev" when CITELEAP_DEV_MODE is defined in wp-config.php).
 * --------------------------------------------------------------------- */
if ( ! function_exists( 'citeleap_fs' ) ) {
	$citeleap_fs_sdk = CITELEAP_DIR . 'vendor/freemius/wordpress-sdk/start.php';
	if ( file_exists( $citeleap_fs_sdk ) ) {
		require_once $citeleap_fs_sdk;
		function citeleap_fs() {
			global $citeleap_fs;
			if ( ! isset( $citeleap_fs ) ) {
				$citeleap_fs = fs_dynamic_init( [
					'id'             => defined( 'CITELEAP_FS_ID' )         ? CITELEAP_FS_ID         : '0',
					'slug'           => 'citeleap',
					'type'           => 'plugin',
					'public_key'     => defined( 'CITELEAP_FS_PUBLIC_KEY' ) ? CITELEAP_FS_PUBLIC_KEY : '',
					'is_premium'     => true,
					'has_addons'     => true,
					'has_paid_plans' => true,
					'trial'          => [ 'days' => 14, 'is_require_payment' => false ],
					'menu'           => [ 'slug' => 'citeleap', 'support' => false ],
					'is_live'        => true,
				] );
			}
			return $citeleap_fs;
		}
		citeleap_fs();
		do_action( 'citeleap_fs_loaded' );
	}
}

/* Credit banner on every CiteLeap admin page. */
add_action( 'admin_notices', [ 'CiteLeap_Credits', 'admin_banner' ] );
