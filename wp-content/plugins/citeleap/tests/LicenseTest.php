<?php
/**
 * @package CiteLeap\Tests
 */

declare( strict_types=1 );

namespace CiteLeap\Tests;

use PHPUnit\Framework\TestCase;
use CiteLeap_License;

final class LicenseTest extends TestCase {

	protected function setUp(): void {
		citeleap_test_reset_options();
	}

	public function test_default_plan_is_free_with_no_freemius_no_dev_mode(): void {
		$this->assertSame( 'free', CiteLeap_License::plan_slug() );
		$this->assertFalse( CiteLeap_License::is_paying() );
		$this->assertFalse( CiteLeap_License::is_trial() );
		$this->assertSame( 0, CiteLeap_License::trial_days_left() );
	}

	public function test_plan_label_returns_human_readable_string(): void {
		$this->assertSame( 'Free', CiteLeap_License::plan_label() );
	}

	public function test_freemius_returns_null_when_sdk_not_loaded(): void {
		$this->assertNull( CiteLeap_License::freemius(),
			'Freemius instance must be null when SDK is not loaded in tests' );
	}

	public function test_customer_email_falls_back_to_blog_admin(): void {
		$this->assertSame( 'admin@example.test', CiteLeap_License::customer_email() );
	}

	public function test_checkout_url_falls_back_to_in_admin_when_no_freemius(): void {
		$url = CiteLeap_License::checkout_url();
		$this->assertStringContainsString( 'page=citeleap', $url );
		$this->assertStringContainsString( 'tab=license',   $url );
	}

	public function test_top_up_url_routes_via_topups_when_sdk_absent(): void {
		$url = CiteLeap_License::top_up_url( 'growth' );
		/* TopUps falls back to the in-admin simulator URL. */
		$this->assertStringContainsString( 'citeleap_simulate_topup', $url );
		$this->assertStringContainsString( 'pack=growth',               $url );
		$this->assertStringContainsString( '_wpnonce=',                 $url );
	}
}
