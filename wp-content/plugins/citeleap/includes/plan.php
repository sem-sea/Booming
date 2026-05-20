<?php
/**
 * CiteLeap , plan.php
 *
 * Plan definitions + capability gate. Single source of truth for what
 * each plan slug is allowed to do, how many credits it ships with,
 * how many sites a license can activate on, and what overage costs.
 *
 * @package CiteLeap
 */

defined( 'ABSPATH' ) || exit;

class CiteLeap_Plan {

	/** Capability map per plan. Order matters for inheritance / UI sort. */
	public static function definitions(): array {
		return [
			'dev' => [
				'label'             => __( 'Developer (unlimited)', 'citeleap' ),
				'price_monthly'     => 0,
				'price_annual'      => 0,
				'credits_per_cycle' => PHP_INT_MAX,
				'lifetime_credits'  => false,
				'sites'             => PHP_INT_MAX,
				'seats'             => PHP_INT_MAX,
				'overage_per_credit'=> 0,
				'features'          => [ 'auto_publish', 'refresh', 'multilingual', 'calendar', 'scheduling', 'top_ups', 'white_label', 'priority_support', 'images_bulk' ],
			],
			'free' => [
				'label'             => __( 'Free', 'citeleap' ),
				'price_monthly'     => 0,
				'price_annual'      => 0,
				'credits_per_cycle' => 3,
				'lifetime_credits'  => true,
				'sites'             => 1,
				'seats'             => 1,
				'overage_per_credit'=> 0,
				'features'          => [ 'images_bulk' ],
			],
			'solo' => [
				'label'             => __( 'Solo', 'citeleap' ),
				'price_monthly'     => 29,
				'price_annual'      => 290,
				'credits_per_cycle' => 10,
				'lifetime_credits'  => false,
				'sites'             => 1,
				'seats'             => 1,
				'overage_per_credit'=> 5,
				'features'          => [ 'auto_publish', 'refresh', 'scheduling', 'top_ups', 'images_bulk' ],
			],
			'pro' => [
				'label'             => __( 'Pro', 'citeleap' ),
				'price_monthly'     => 99,
				'price_annual'      => 990,
				'credits_per_cycle' => 30,
				'lifetime_credits'  => false,
				'sites'             => 3,
				'seats'             => 3,
				'overage_per_credit'=> 4,
				'features'          => [ 'auto_publish', 'refresh', 'multilingual', 'calendar', 'scheduling', 'top_ups', 'images_bulk' ],
			],
			'agency' => [
				'label'             => __( 'Agency', 'citeleap' ),
				'price_monthly'     => 299,
				'price_annual'      => 2990,
				'credits_per_cycle' => 100,
				'lifetime_credits'  => false,
				'sites'             => 20,
				'seats'             => 10,
				'overage_per_credit'=> 3,
				'features'          => [ 'auto_publish', 'refresh', 'multilingual', 'calendar', 'scheduling', 'top_ups', 'white_label', 'images_bulk' ],
			],
			'enterprise' => [
				'label'             => __( 'Enterprise', 'citeleap' ),
				'price_monthly'     => 999,
				'price_annual'      => 9990,
				'credits_per_cycle' => 500,
				'lifetime_credits'  => false,
				'sites'             => PHP_INT_MAX,
				'seats'             => PHP_INT_MAX,
				'overage_per_credit'=> 2,
				'features'          => [ 'auto_publish', 'refresh', 'multilingual', 'calendar', 'scheduling', 'top_ups', 'white_label', 'priority_support', 'images_bulk' ],
			],
		];
	}

	public static function current(): string {
		return CiteLeap_License::plan_slug();
	}

	public static function definition( string $slug = '' ): array {
		$defs = self::definitions();
		$slug = $slug ?: self::current();
		return $defs[ $slug ] ?? $defs['free'];
	}

	/** Does the current (or named) plan have a capability? */
	public static function has( string $feature, string $slug = '' ): bool {
		$def = self::definition( $slug );
		return in_array( $feature, (array) ( $def['features'] ?? [] ), true );
	}

	public static function credits_per_cycle( string $slug = '' ): int {
		return (int) self::definition( $slug )['credits_per_cycle'];
	}

	public static function is_lifetime_credits( string $slug = '' ): bool {
		return (bool) self::definition( $slug )['lifetime_credits'];
	}

	public static function sites_allowed( string $slug = '' ): int {
		return (int) self::definition( $slug )['sites'];
	}

	public static function overage_rate( string $slug = '' ): float {
		return (float) self::definition( $slug )['overage_per_credit'];
	}

	public static function is_paid( string $slug = '' ): bool {
		$slug = $slug ?: self::current();
		return in_array( $slug, [ 'solo', 'pro', 'agency', 'enterprise', 'dev' ], true );
	}

	/** UI helper: list plans that are higher than $slug. */
	public static function upgrades_from( string $slug = '' ): array {
		$order = [ 'free', 'solo', 'pro', 'agency', 'enterprise' ];
		$slug  = $slug ?: self::current();
		$idx   = array_search( $slug, $order, true );
		if ( false === $idx ) return [];
		return array_slice( $order, (int) $idx + 1 );
	}
}
