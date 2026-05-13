<?php
/**
 * CiteLeap , token + cost ledger and budget caps.
 *
 * Stored shape in wp_options 'citeleap_token_usage':
 *   [
 *     '2026-05' => [
 *       'claude' => [
 *         'input_tokens'  => 12345,
 *         'output_tokens' => 6789,
 *         'cost_usd'      => 0.18,
 *         'calls'         => 3,
 *       ],
 *       'openai' => [ ... ],
 *       'gemini' => [ ... ],
 *     ],
 *     '2026-04' => [ ... ],
 *   ]
 *
 * 'citeleap_budget_caps' shape:
 *   [
 *     'claude' => 25.00,
 *     'openai' => 0,            // 0 = unlimited
 *     'gemini' => 0,
 *     'overall' => 50.00,       // total across all providers, 0 = unlimited
 *   ]
 *
 * @package CiteLeap
 */

defined( 'ABSPATH' ) || exit;

class CiteLeap_Usage {

	public static function current_month(): string {
		return (string) wp_date( 'Y-m' );
	}

	public static function record( string $provider, string $model, int $input_tokens, int $output_tokens ): void {
		$cost  = CiteLeap_Pricing::cost_for( $model, $input_tokens, $output_tokens );
		$month = self::current_month();
		$ledger = (array) get_option( CITELEAP_OPTION_USAGE, [] );
		if ( ! isset( $ledger[ $month ] ) ) $ledger[ $month ] = [];
		if ( ! isset( $ledger[ $month ][ $provider ] ) ) {
			$ledger[ $month ][ $provider ] = [ 'input_tokens' => 0, 'output_tokens' => 0, 'cost_usd' => 0.0, 'calls' => 0 ];
		}
		$ledger[ $month ][ $provider ]['input_tokens']  += $input_tokens;
		$ledger[ $month ][ $provider ]['output_tokens'] += $output_tokens;
		$ledger[ $month ][ $provider ]['cost_usd']      += $cost;
		$ledger[ $month ][ $provider ]['calls']         += 1;
		/* Keep only the most recent 12 months in the ledger. */
		if ( count( $ledger ) > 12 ) {
			ksort( $ledger );
			$ledger = array_slice( $ledger, -12, null, true );
		}
		update_option( CITELEAP_OPTION_USAGE, $ledger, false );
	}

	public static function month_usage( ?string $month = null ): array {
		$month  = $month ?: self::current_month();
		$ledger = (array) get_option( CITELEAP_OPTION_USAGE, [] );
		return (array) ( $ledger[ $month ] ?? [] );
	}

	public static function month_cost( ?string $month = null ): float {
		$total = 0.0;
		foreach ( self::month_usage( $month ) as $row ) {
			$total += (float) ( $row['cost_usd'] ?? 0 );
		}
		return $total;
	}

	public static function month_cost_for( string $provider, ?string $month = null ): float {
		$u = self::month_usage( $month );
		return (float) ( $u[ $provider ]['cost_usd'] ?? 0 );
	}

	public static function caps(): array {
		$caps = (array) get_option( CITELEAP_OPTION_CAPS, [] );
		return [
			'claude'  => (float) ( $caps['claude']  ?? 0 ),
			'openai'  => (float) ( $caps['openai']  ?? 0 ),
			'gemini'  => (float) ( $caps['gemini']  ?? 0 ),
			'overall' => (float) ( $caps['overall'] ?? 0 ),
		];
	}

	/**
	 * Returns [ ok=>bool, reason=>string ] indicating whether a new
	 * call to $provider can proceed without breaching the monthly
	 * budget cap. A cap of 0 means "unlimited".
	 */
	public static function can_spend( string $provider ): array {
		$caps = self::caps();
		$total_now    = self::month_cost();
		$provider_now = self::month_cost_for( $provider );

		if ( $caps['overall'] > 0 && $total_now >= $caps['overall'] ) {
			return [ 'ok' => false, 'reason' => sprintf( 'Overall monthly cap %s reached ($%.2f / $%.2f).', $caps['overall'], $total_now, $caps['overall'] ) ];
		}
		if ( $caps[ $provider ] > 0 && $provider_now >= $caps[ $provider ] ) {
			return [ 'ok' => false, 'reason' => sprintf( '%s monthly cap reached ($%.2f / $%.2f).', ucfirst( $provider ), $provider_now, $caps[ $provider ] ) ];
		}
		return [ 'ok' => true, 'reason' => '' ];
	}

	public static function reset_month( string $month ): void {
		$ledger = (array) get_option( CITELEAP_OPTION_USAGE, [] );
		unset( $ledger[ $month ] );
		update_option( CITELEAP_OPTION_USAGE, $ledger, false );
	}
}
