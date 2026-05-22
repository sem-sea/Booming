<?php
/**
 * @package CiteLeap\Tests
 */

declare( strict_types=1 );

namespace CiteLeap\Tests;

use PHPUnit\Framework\TestCase;
use CiteLeap_Credits;
use CiteLeap_Plan;

final class CreditsTest extends TestCase {

	protected function setUp(): void {
		citeleap_test_reset_options();
	}

	public function test_fresh_ledger_starts_with_full_balance(): void {
		$this->assertSame( 0,  CiteLeap_Credits::used() );
		$this->assertSame( 0,  CiteLeap_Credits::top_up() );
		$this->assertSame( 0,  CiteLeap_Credits::lifetime_used() );
		$this->assertSame( 3,  CiteLeap_Credits::remaining(),
			'free plan should report 3 credits at first read' );
	}

	public function test_consume_decrements_remaining(): void {
		$this->assertTrue( CiteLeap_Credits::consume( 1, 'draft' ) );
		$this->assertSame( 1, CiteLeap_Credits::used() );
		$this->assertSame( 2, CiteLeap_Credits::remaining() );
	}

	public function test_exhausted_when_all_credits_consumed(): void {
		CiteLeap_Credits::consume( 1, 'draft' );
		CiteLeap_Credits::consume( 1, 'draft' );
		CiteLeap_Credits::consume( 1, 'draft' );
		$this->assertSame( 0, CiteLeap_Credits::remaining() );
		$this->assertTrue( CiteLeap_Credits::is_exhausted() );
		$this->assertFalse( CiteLeap_Credits::can_consume( 1 ) );
	}

	public function test_consume_returns_false_when_no_credits(): void {
		CiteLeap_Credits::consume( 1, 'draft' );
		CiteLeap_Credits::consume( 1, 'draft' );
		CiteLeap_Credits::consume( 1, 'draft' );
		$this->assertFalse( CiteLeap_Credits::consume( 1, 'draft' ),
			'consume must refuse when no credits remain' );
	}

	public function test_lifetime_used_increments_even_when_blocked(): void {
		CiteLeap_Credits::consume( 1, 'draft' );
		CiteLeap_Credits::consume( 1, 'draft' );
		$this->assertSame( 2, CiteLeap_Credits::lifetime_used() );
	}

	public function test_top_up_extends_balance(): void {
		CiteLeap_Credits::consume( 1, 'draft' );
		CiteLeap_Credits::consume( 1, 'draft' );
		CiteLeap_Credits::consume( 1, 'draft' );
		$this->assertSame( 0, CiteLeap_Credits::remaining() );

		CiteLeap_Credits::add_top_up( 50 );

		$this->assertSame( 50, CiteLeap_Credits::top_up() );
		$this->assertSame( 50, CiteLeap_Credits::remaining() );
		$this->assertTrue( CiteLeap_Credits::can_consume( 1 ) );
	}

	public function test_top_up_consumed_after_included_credits(): void {
		CiteLeap_Credits::add_top_up( 10 );

		/* Consume the 3 included credits first , top-up untouched. */
		CiteLeap_Credits::consume( 1, 'draft' );
		CiteLeap_Credits::consume( 1, 'draft' );
		CiteLeap_Credits::consume( 1, 'draft' );
		$this->assertSame( 10, CiteLeap_Credits::top_up(), 'top-up must NOT decrement while monthly credits remain' );
		$this->assertSame( 3,  CiteLeap_Credits::used() );

		/* Next consume dips into top-up. */
		CiteLeap_Credits::consume( 1, 'draft' );
		$this->assertSame( 9, CiteLeap_Credits::top_up(), 'top-up must decrement once monthly is exhausted' );
	}

	public function test_reset_cycle_zeroes_used_but_preserves_top_up_and_lifetime(): void {
		CiteLeap_Credits::consume( 1, 'draft' );
		CiteLeap_Credits::add_top_up( 5 );

		CiteLeap_Credits::reset_cycle();

		$this->assertSame( 0, CiteLeap_Credits::used(),     'used must reset to 0' );
		$this->assertSame( 5, CiteLeap_Credits::top_up(),   'top-up must survive cycle reset' );
		$this->assertSame( 1, CiteLeap_Credits::lifetime_used(), 'lifetime counter must NOT reset' );
	}

	public function test_low_threshold_does_not_trigger_below_80_pct(): void {
		/* Free plan included = 3. Low fires at >=80% used. */
		$this->assertFalse( CiteLeap_Credits::is_low(), 'fresh ledger must not be low' );
		CiteLeap_Credits::consume( 1, 'draft' );
		$this->assertFalse( CiteLeap_Credits::is_low(), '1/3 (33%) must not be low' );
		CiteLeap_Credits::consume( 1, 'draft' );
		$this->assertFalse( CiteLeap_Credits::is_low(), '2/3 (67%) must not be low' );
	}

	public function test_low_threshold_triggers_at_100_pct_used(): void {
		CiteLeap_Credits::consume( 1, 'draft' );
		CiteLeap_Credits::consume( 1, 'draft' );
		CiteLeap_Credits::consume( 1, 'draft' );
		$this->assertTrue( CiteLeap_Credits::is_low(), '3/3 (100%) must trigger low' );
	}

	public function test_percent_used_reports_correctly(): void {
		$this->assertSame( 0, CiteLeap_Credits::percent_used() );
		CiteLeap_Credits::consume( 1, 'draft' );
		$this->assertSame( 33, CiteLeap_Credits::percent_used() );
		CiteLeap_Credits::consume( 1, 'draft' );
		$this->assertSame( 67, CiteLeap_Credits::percent_used() );
		CiteLeap_Credits::consume( 1, 'draft' );
		$this->assertSame( 100, CiteLeap_Credits::percent_used() );
	}
}
