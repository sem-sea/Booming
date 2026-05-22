<?php
/**
 * @package CiteLeap\Tests
 */

declare( strict_types=1 );

namespace CiteLeap\Tests;

use PHPUnit\Framework\TestCase;
use CiteLeap_TopUps;
use CiteLeap_Credits;

final class TopUpsTest extends TestCase {

	protected function setUp(): void {
		citeleap_test_reset_options();
	}

	public function test_catalog_has_four_packs_in_published_pricing_order(): void {
		$catalog = CiteLeap_TopUps::catalog();
		$this->assertSame( [ 'starter', 'growth', 'scale', 'bulk' ], array_keys( $catalog ) );
	}

	public function test_per_credit_price_descends_as_pack_size_grows(): void {
		$catalog = CiteLeap_TopUps::catalog();
		$prices  = array_map( fn( $p ) => $p['per_credit'], $catalog );
		$sorted  = $prices;
		rsort( $sorted );
		$this->assertSame( array_values( $sorted ), array_values( $prices ),
			'per-credit price must drop as pack size grows (incentive to buy bulk)' );
	}

	public function test_each_pack_has_required_keys(): void {
		foreach ( CiteLeap_TopUps::catalog() as $slug => $pack ) {
			$this->assertArrayHasKey( 'slug',        $pack, "$slug missing slug" );
			$this->assertArrayHasKey( 'credits',     $pack, "$slug missing credits" );
			$this->assertArrayHasKey( 'price_usd',   $pack, "$slug missing price_usd" );
			$this->assertArrayHasKey( 'fs_plan_id',  $pack, "$slug missing fs_plan_id" );
			$this->assertSame( $slug, $pack['slug'], "slug key mismatch on $slug" );
		}
	}

	public function test_pack_lookup_returns_empty_for_unknown_slug(): void {
		$this->assertSame( [], CiteLeap_TopUps::pack( 'nonexistent-pack' ) );
	}

	public function test_pack_lookup_returns_full_pack_for_known_slug(): void {
		$pack = CiteLeap_TopUps::pack( 'growth' );
		$this->assertSame( 50, $pack['credits'] );
		$this->assertSame( 199, $pack['price_usd'] );
	}

	public function test_buy_url_routes_to_simulator_when_freemius_absent(): void {
		$url = CiteLeap_TopUps::buy_url( 'starter' );
		$this->assertStringContainsString( 'admin-post.php', $url );
		$this->assertStringContainsString( 'citeleap_simulate_topup', $url );
		$this->assertStringContainsString( 'pack=starter', $url );
	}

	public function test_buy_url_returns_safe_fallback_for_unknown_pack(): void {
		$url = CiteLeap_TopUps::buy_url( 'no-such-pack' );
		$this->assertStringContainsString( 'page=citeleap', $url );
		$this->assertStringContainsString( 'tab=license',   $url );
	}

	public function test_freemius_purchase_handler_grants_correct_pack_credits(): void {
		/* Simulate the Freemius webhook payload object shape. */
		$purchase = (object) [
			'plan_id'   => 'pack_growth',
			'plan_name' => 'Growth pack',
		];
		CiteLeap_TopUps::on_freemius_purchase( $purchase );
		$this->assertSame( 50, CiteLeap_Credits::top_up(),
			'Growth pack webhook should grant 50 credits' );
	}

	public function test_freemius_purchase_handler_ignores_unknown_plan_id(): void {
		$purchase = (object) [ 'plan_id' => 'not-a-pack', 'plan_name' => 'Mystery' ];
		CiteLeap_TopUps::on_freemius_purchase( $purchase );
		$this->assertSame( 0, CiteLeap_Credits::top_up(),
			'unknown plan_id must NOT grant credits' );
	}

	public function test_freemius_plan_change_resets_cycle(): void {
		CiteLeap_Credits::consume( 1, 'draft' );
		CiteLeap_Credits::consume( 1, 'draft' );
		$this->assertSame( 2, CiteLeap_Credits::used() );
		CiteLeap_TopUps::on_freemius_plan_change( (object) [ 'name' => 'free' ], (object) [ 'name' => 'solo' ] );
		$this->assertSame( 0, CiteLeap_Credits::used(),
			'plan change must reset the monthly cycle' );
	}
}
