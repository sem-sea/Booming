<?php
/**
 * @package CiteLeap\Tests
 */

declare( strict_types=1 );

namespace CiteLeap\Tests;

use PHPUnit\Framework\TestCase;
use CiteLeap_Plan;

final class PlanTest extends TestCase {

	protected function setUp(): void {
		citeleap_test_reset_options();
	}

	public function test_definitions_include_all_known_plans(): void {
		$defs = CiteLeap_Plan::definitions();
		foreach ( [ 'dev', 'free', 'solo', 'pro', 'agency', 'enterprise' ] as $slug ) {
			$this->assertArrayHasKey( $slug, $defs, "plan slug missing: $slug" );
			$this->assertArrayHasKey( 'credits_per_cycle', $defs[ $slug ] );
			$this->assertArrayHasKey( 'sites',             $defs[ $slug ] );
			$this->assertArrayHasKey( 'features',          $defs[ $slug ] );
		}
	}

	public function test_default_plan_is_free_when_no_license(): void {
		$this->assertSame( 'free', CiteLeap_Plan::current() );
	}

	public function test_free_plan_locks_refresh_and_multilingual(): void {
		$this->assertFalse( CiteLeap_Plan::has( 'refresh',      'free' ) );
		$this->assertFalse( CiteLeap_Plan::has( 'multilingual', 'free' ) );
		$this->assertFalse( CiteLeap_Plan::has( 'auto_publish', 'free' ) );
		$this->assertFalse( CiteLeap_Plan::has( 'calendar',     'free' ) );
	}

	public function test_solo_unlocks_refresh_and_auto_publish_but_not_multilingual(): void {
		$this->assertTrue(  CiteLeap_Plan::has( 'refresh',      'solo' ) );
		$this->assertTrue(  CiteLeap_Plan::has( 'auto_publish', 'solo' ) );
		$this->assertFalse( CiteLeap_Plan::has( 'multilingual', 'solo' ) );
		$this->assertFalse( CiteLeap_Plan::has( 'calendar',     'solo' ) );
	}

	public function test_pro_unlocks_calendar_and_multilingual(): void {
		$this->assertTrue( CiteLeap_Plan::has( 'multilingual', 'pro' ) );
		$this->assertTrue( CiteLeap_Plan::has( 'calendar',     'pro' ) );
	}

	public function test_agency_unlocks_white_label(): void {
		$this->assertTrue(  CiteLeap_Plan::has( 'white_label', 'agency' ) );
		$this->assertFalse( CiteLeap_Plan::has( 'white_label', 'pro' ) );
	}

	public function test_enterprise_unlocks_priority_support(): void {
		$this->assertTrue(  CiteLeap_Plan::has( 'priority_support', 'enterprise' ) );
		$this->assertFalse( CiteLeap_Plan::has( 'priority_support', 'agency' ) );
	}

	public function test_dev_plan_has_every_capability(): void {
		foreach ( [ 'auto_publish', 'refresh', 'multilingual', 'calendar', 'scheduling', 'top_ups', 'white_label', 'priority_support', 'images_bulk' ] as $cap ) {
			$this->assertTrue( CiteLeap_Plan::has( $cap, 'dev' ), "dev plan missing: $cap" );
		}
	}

	public function test_credits_per_cycle_matches_published_pricing(): void {
		$this->assertSame(   3, CiteLeap_Plan::credits_per_cycle( 'free' ) );
		$this->assertSame(  10, CiteLeap_Plan::credits_per_cycle( 'solo' ) );
		$this->assertSame(  30, CiteLeap_Plan::credits_per_cycle( 'pro' ) );
		$this->assertSame( 100, CiteLeap_Plan::credits_per_cycle( 'agency' ) );
		$this->assertSame( 500, CiteLeap_Plan::credits_per_cycle( 'enterprise' ) );
	}

	public function test_overage_rates_descend_as_plans_scale(): void {
		$rates = [
			CiteLeap_Plan::overage_rate( 'solo' ),
			CiteLeap_Plan::overage_rate( 'pro' ),
			CiteLeap_Plan::overage_rate( 'agency' ),
			CiteLeap_Plan::overage_rate( 'enterprise' ),
		];
		$sorted = $rates;
		rsort( $sorted );
		$this->assertSame( $sorted, $rates, 'overage rates must decrease as plan tier rises' );
	}

	public function test_free_plan_is_lifetime_credits(): void {
		$this->assertTrue(  CiteLeap_Plan::is_lifetime_credits( 'free' ) );
		$this->assertFalse( CiteLeap_Plan::is_lifetime_credits( 'solo' ) );
	}

	public function test_lowest_plan_with_returns_correct_slug(): void {
		$this->assertSame( 'solo',       CiteLeap_Plan::lowest_plan_with( 'refresh' ) );
		$this->assertSame( 'pro',        CiteLeap_Plan::lowest_plan_with( 'multilingual' ) );
		$this->assertSame( 'pro',        CiteLeap_Plan::lowest_plan_with( 'calendar' ) );
		$this->assertSame( 'agency',     CiteLeap_Plan::lowest_plan_with( 'white_label' ) );
		$this->assertSame( 'enterprise', CiteLeap_Plan::lowest_plan_with( 'priority_support' ) );
	}

	public function test_is_paid_is_true_for_solo_and_higher(): void {
		$this->assertFalse( CiteLeap_Plan::is_paid( 'free' ) );
		$this->assertTrue(  CiteLeap_Plan::is_paid( 'solo' ) );
		$this->assertTrue(  CiteLeap_Plan::is_paid( 'pro' ) );
		$this->assertTrue(  CiteLeap_Plan::is_paid( 'agency' ) );
		$this->assertTrue(  CiteLeap_Plan::is_paid( 'enterprise' ) );
		$this->assertTrue(  CiteLeap_Plan::is_paid( 'dev' ) );
	}

	public function test_upgrades_from_returns_higher_tiers_only(): void {
		$this->assertSame( [ 'solo', 'pro', 'agency', 'enterprise' ], CiteLeap_Plan::upgrades_from( 'free' ) );
		$this->assertSame( [ 'pro', 'agency', 'enterprise' ],         CiteLeap_Plan::upgrades_from( 'solo' ) );
		$this->assertSame( [],                                        CiteLeap_Plan::upgrades_from( 'enterprise' ) );
	}
}
