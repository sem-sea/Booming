<?php
/**
 * CiteLeap , license.php
 *
 * Freemius license wrapper. When the Freemius SDK is present (dropped
 * into vendor/freemius/wordpress-sdk/start.php and bootstrapped from
 * citeleap.php), this class returns the real plan slug + trial state +
 * checkout URLs straight from Freemius.
 *
 * When the SDK is NOT present (the default in this repo until the
 * operator drops it in and configures the IDs in wp-config.php), the
 * class falls back to:
 *   - "dev" plan if the CITELEAP_DEV_MODE constant is true
 *     (gives unlimited credits + every feature, for the author's own
 *     site and for local development),
 *   - "free" plan otherwise (3 lifetime credits, no refresh, no
 *     multilingual, no auto-publish).
 *
 * @package CiteLeap
 */

defined( 'ABSPATH' ) || exit;

class CiteLeap_License {

	/** Return the active plan slug: dev / free / solo / pro / agency / enterprise. */
	public static function plan_slug(): string {
		if ( defined( 'CITELEAP_DEV_MODE' ) && CITELEAP_DEV_MODE ) return 'dev';
		$fs = self::freemius();
		if ( $fs && $fs->is_paying() ) {
			$plan = strtolower( (string) $fs->get_plan_name() );
			if ( in_array( $plan, [ 'solo', 'pro', 'agency', 'enterprise' ], true ) ) return $plan;
		}
		if ( $fs && $fs->is_trial() ) {
			/* Trial defaults to Pro features. */
			return 'pro';
		}
		return 'free';
	}

	public static function is_paying(): bool {
		$fs = self::freemius();
		return $fs ? (bool) $fs->is_paying() : false;
	}

	public static function is_trial(): bool {
		$fs = self::freemius();
		return $fs ? (bool) $fs->is_trial() : false;
	}

	public static function trial_days_left(): int {
		$fs = self::freemius();
		if ( ! $fs || ! $fs->is_trial() ) return 0;
		$ends = method_exists( $fs, 'get_trial_plan' ) ? (int) strtotime( (string) $fs->_get_site()->trial_ends ) : 0;
		if ( ! $ends ) return 0;
		return max( 0, (int) ceil( ( $ends - time() ) / DAY_IN_SECONDS ) );
	}

	public static function customer_email(): string {
		$fs = self::freemius();
		if ( ! $fs ) return (string) get_bloginfo( 'admin_email' );
		$user = method_exists( $fs, 'get_user' ) ? $fs->get_user() : null;
		return $user && isset( $user->email ) ? (string) $user->email : (string) get_bloginfo( 'admin_email' );
	}

	/** Upgrade / change-plan checkout URL. */
	public static function checkout_url( string $plan_slug = 'pro' ): string {
		$fs = self::freemius();
		if ( $fs && method_exists( $fs, 'get_upgrade_url' ) ) {
			return (string) $fs->get_upgrade_url();
		}
		return admin_url( 'admin.php?page=citeleap&tab=license' );
	}

	/** One-time top-up purchase URL. */
	public static function top_up_url( string $pack = 'growth' ): string {
		$fs = self::freemius();
		if ( $fs && method_exists( $fs, 'addon_url' ) ) {
			return (string) $fs->checkout_url( WP_FS__PERIOD_LIFETIME ?? 'lifetime', false, [ 'pack' => $pack ] );
		}
		return admin_url( 'admin.php?page=citeleap&tab=license' );
	}

	public static function account_url(): string {
		$fs = self::freemius();
		if ( $fs && method_exists( $fs, 'get_account_url' ) ) {
			return (string) $fs->get_account_url();
		}
		return admin_url( 'admin.php?page=citeleap&tab=license' );
	}

	/** Return the Freemius instance if the SDK is loaded, otherwise null. */
	public static function freemius() {
		if ( ! function_exists( 'fs_dynamic_init' ) ) return null;
		if ( function_exists( 'citeleap_fs' ) ) return citeleap_fs();
		return null;
	}

	/** Convenience: human label for the plan. */
	public static function plan_label(): string {
		$labels = [
			'dev'        => __( 'Developer (unlimited)', 'citeleap' ),
			'free'       => __( 'Free', 'citeleap' ),
			'solo'       => __( 'Solo', 'citeleap' ),
			'pro'        => __( 'Pro', 'citeleap' ),
			'agency'     => __( 'Agency', 'citeleap' ),
			'enterprise' => __( 'Enterprise', 'citeleap' ),
		];
		return $labels[ self::plan_slug() ] ?? ucfirst( self::plan_slug() );
	}
}
