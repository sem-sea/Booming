<?php
/**
 * CiteLeap , credits.php
 *
 * Credit metering. One credit = one billable LLM operation (a draft or
 * a refresh). Tracks usage per billing cycle in wp_options, gates the
 * draft + refresh pipelines via can_consume() / consume(), and exposes
 * remaining() for the admin banner.
 *
 * Cycle anchoring:
 *   - paid plans: monthly, anchored to billing_cycle_start (defaults
 *     to the 1st of the calendar month at site timezone) ,
 *   - free plan: lifetime (cycle never resets, credits run out and
 *     stay out until the operator upgrades).
 *
 * Storage shape in CITELEAP_OPTION_CREDITS:
 *   [
 *     'used'           => int   used this cycle (or lifetime for free)
 *     'top_up'         => int   purchased one-off credits, never expire
 *     'cycle_start'    => 'YYYY-MM-DD' anchor
 *     'last_top_up'    => 'YYYY-MM-DD HH:MM:SS'
 *     'lifetime_used'  => int   total ever consumed
 *     'last_consumed_at' => 'YYYY-MM-DD HH:MM:SS'
 *   ]
 *
 * @package CiteLeap
 */

defined( 'ABSPATH' ) || exit;

class CiteLeap_Credits {

	/* Threshold (percentage of the cycle) under which the low-credit
	 * banner appears. */
	const LOW_THRESHOLD_PCT = 20;

	public static function state(): array {
		$s = (array) get_option( CITELEAP_OPTION_CREDITS, [] );
		$defaults = [
			'used'             => 0,
			'top_up'           => 0,
			'cycle_start'      => current_time( 'Y-m-01' ),
			'last_top_up'      => '',
			'lifetime_used'    => 0,
			'last_consumed_at' => '',
		];
		$s = array_merge( $defaults, $s );
		/* Auto-reset if a new billing cycle has started AND the active
		 * plan is not lifetime-based. */
		if ( ! CiteLeap_Plan::is_lifetime_credits() ) {
			$expected = current_time( 'Y-m-01' );
			if ( $s['cycle_start'] !== $expected ) {
				$s['cycle_start'] = $expected;
				$s['used']        = 0;
				update_option( CITELEAP_OPTION_CREDITS, $s, false );
			}
		}
		return $s;
	}

	public static function included(): int {
		return (int) CiteLeap_Plan::credits_per_cycle();
	}

	public static function used(): int {
		return (int) self::state()['used'];
	}

	public static function top_up(): int {
		return (int) self::state()['top_up'];
	}

	public static function total_available(): int {
		$s = self::state();
		$inc = self::included();
		return max( 0, $inc - (int) $s['used'] ) + (int) $s['top_up'];
	}

	public static function remaining(): int {
		return self::total_available();
	}

	public static function lifetime_used(): int {
		return (int) self::state()['lifetime_used'];
	}

	public static function can_consume( int $n = 1 ): bool {
		if ( defined( 'CITELEAP_DEV_MODE' ) && CITELEAP_DEV_MODE ) return true;
		return self::total_available() >= max( 1, $n );
	}

	/**
	 * Spend $n credits. Consumes from the monthly included pool first,
	 * then dips into the top-up pool. Returns true on success.
	 */
	public static function consume( int $n = 1, string $action = 'draft' ): bool {
		if ( defined( 'CITELEAP_DEV_MODE' ) && CITELEAP_DEV_MODE ) return true;
		$n = max( 1, $n );
		if ( ! self::can_consume( $n ) ) return false;
		$s = self::state();
		$included_remaining = max( 0, self::included() - (int) $s['used'] );
		if ( $included_remaining >= $n ) {
			$s['used'] += $n;
		} else {
			$from_top_up = $n - $included_remaining;
			$s['used']   = self::included();
			$s['top_up'] = max( 0, (int) $s['top_up'] - $from_top_up );
		}
		$s['lifetime_used']   += $n;
		$s['last_consumed_at'] = current_time( 'mysql' );
		update_option( CITELEAP_OPTION_CREDITS, $s, false );
		if ( class_exists( 'CiteLeap_Log' ) ) {
			CiteLeap_Log::add( 'credit_consumed', sprintf( 'plan=%s action=%s n=%d remaining=%d', CiteLeap_Plan::current(), $action, $n, self::total_available() ), 'info' );
		}
		return true;
	}

	public static function add_top_up( int $n ): void {
		$n = max( 0, $n );
		if ( ! $n ) return;
		$s = self::state();
		$s['top_up']     += $n;
		$s['last_top_up'] = current_time( 'mysql' );
		update_option( CITELEAP_OPTION_CREDITS, $s, false );
		if ( class_exists( 'CiteLeap_Log' ) ) {
			CiteLeap_Log::add( 'credit_top_up', sprintf( 'added=%d new_balance=%d', $n, self::total_available() ), 'info' );
		}
	}

	/** Force a cycle reset (e.g. on plan change webhook). */
	public static function reset_cycle(): void {
		$s = self::state();
		$s['cycle_start'] = current_time( 'Y-m-01' );
		$s['used']        = 0;
		update_option( CITELEAP_OPTION_CREDITS, $s, false );
	}

	public static function percent_used(): int {
		$inc = self::included();
		if ( $inc <= 0 || PHP_INT_MAX === $inc ) return 0;
		return (int) min( 100, round( ( self::used() / $inc ) * 100 ) );
	}

	public static function is_low(): bool {
		if ( defined( 'CITELEAP_DEV_MODE' ) && CITELEAP_DEV_MODE ) return false;
		if ( PHP_INT_MAX === self::included() ) return false;
		return self::percent_used() >= ( 100 - self::LOW_THRESHOLD_PCT );
	}

	public static function is_exhausted(): bool {
		if ( defined( 'CITELEAP_DEV_MODE' ) && CITELEAP_DEV_MODE ) return false;
		return self::total_available() <= 0;
	}

	/**
	 * Admin banner. Three states:
	 *  - dev mode    , green pill "unlimited"
	 *  - exhausted   , red error notice with upgrade + top-up CTAs
	 *  - low credits , amber warning with upgrade CTA
	 *  - healthy     , quiet info pill on plugin pages
	 *
	 * Only shows on screens whose slug starts with "citeleap" so we do
	 * not pollute the rest of wp-admin.
	 */
	public static function admin_banner(): void {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || 0 !== strpos( (string) $screen->id, 'toplevel_page_citeleap' )
			&& 0 !== strpos( (string) $screen->id, 'citeleap_page_citeleap' )
			&& 0 !== strpos( (string) ( $screen->base ?? '' ), 'toplevel_page_citeleap' ) ) {
			/* fall through , some screens report base differently */
			$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['page'] ) ) : '';
			if ( 'citeleap' !== $page ) return;
		}

		$plan_label = CiteLeap_License::plan_label();
		$plan_slug  = CiteLeap_Plan::current();

		if ( defined( 'CITELEAP_DEV_MODE' ) && CITELEAP_DEV_MODE ) {
			echo '<div class="notice notice-info" role="status" aria-live="polite" style="border-left:4px solid #16a34a;"><p><strong>' . esc_html__( 'CiteLeap , Developer mode.', 'citeleap' )
				. '</strong> ' . esc_html__( 'Unlimited credits, all features enabled. Plan gates are bypassed.', 'citeleap' ) . '</p></div>';
			return;
		}

		$inc       = self::included();
		$used      = self::used();
		$top_up    = self::top_up();
		$remaining = self::remaining();
		$pct       = self::percent_used();

		if ( self::is_exhausted() ) {
			$upgrade = esc_url( CiteLeap_License::checkout_url() );
			$top     = esc_url( CiteLeap_License::top_up_url( 'growth' ) );
			echo '<div class="notice notice-error" role="alert" aria-live="assertive"><p><span class="screen-reader-text">' . esc_html__( 'Error: ', 'citeleap' ) . '</span><strong>'
				. esc_html__( 'CiteLeap , out of credits.', 'citeleap' ) . '</strong> '
				. esc_html( sprintf( __( 'Your %s plan cycle is exhausted. Drafting and refreshing are blocked until you upgrade or top up.', 'citeleap' ), $plan_label ) )
				. ' <a class="button button-primary" href="' . $upgrade . '" style="margin-left:0.5rem;">' . esc_html__( 'Upgrade plan', 'citeleap' ) . '</a>'
				. ' <a class="button" href="' . $top . '" style="margin-left:0.25rem;">' . esc_html__( 'Buy top-up pack', 'citeleap' ) . '</a>'
				. '</p></div>';
			return;
		}

		if ( self::is_low() ) {
			$upgrade = esc_url( CiteLeap_License::checkout_url() );
			$top     = esc_url( CiteLeap_License::top_up_url( 'growth' ) );
			echo '<div class="notice notice-warning" role="status" aria-live="polite"><p><span class="screen-reader-text">' . esc_html__( 'Warning: ', 'citeleap' ) . '</span><strong>'
				. esc_html__( 'CiteLeap , credits running low.', 'citeleap' ) . '</strong> '
				. esc_html( sprintf( __( '%1$d of %2$d credits remaining this cycle on the %3$s plan.', 'citeleap' ), $remaining, $inc, $plan_label ) )
				. ' <a class="button" href="' . $upgrade . '" style="margin-left:0.5rem;">' . esc_html__( 'Upgrade plan', 'citeleap' ) . '</a>'
				. ' <a class="button" href="' . $top . '" style="margin-left:0.25rem;">' . esc_html__( 'Buy top-up pack', 'citeleap' ) . '</a>'
				. '</p></div>';
			return;
		}

		/* Healthy , quiet info pill. */
		$inc_label = ( PHP_INT_MAX === $inc ) ? __( 'unlimited', 'citeleap' ) : (string) $inc;
		echo '<div class="notice notice-info" role="status" aria-live="polite" style="border-left:4px solid #0284c7;"><p>'
			. esc_html__( 'CiteLeap:', 'citeleap' ) . ' <strong>' . esc_html( $plan_label ) . '</strong> , '
			. esc_html( sprintf( __( '%1$d of %2$s credits used this cycle.', 'citeleap' ), $used, $inc_label ) )
			. ( $top_up > 0 ? ' ' . esc_html( sprintf( __( '%d top-up credits in reserve.', 'citeleap' ), $top_up ) ) : '' )
			. ' <a href="' . esc_url( admin_url( 'admin.php?page=citeleap&tab=license' ) ) . '">' . esc_html__( 'Plan & credits', 'citeleap' ) . '</a>'
			. '</p></div>';
	}
}
