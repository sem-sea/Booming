<?php
/**
 * CiteLeap , pricing table (May 2026 published rates).
 *
 * All prices in USD per million tokens. Operators can override the
 * full table via the 'citeleap_pricing' filter, or override a single
 * model via wp_options 'citeleap_pricing_overrides' (UI-editable on
 * the Settings tab).
 *
 * Sources confirmed during research (May 2026):
 * - Claude Opus 4.7   : $5 input / $25 output
 * - Claude Sonnet 4.6 : $3 input / $15 output
 * - Claude Haiku 4.5  : $1 input / $5 output
 * OpenAI + Gemini rates are best-effort estimates pending provider
 * publication; the operator can update the override table without
 * a code change.
 *
 * @package CiteLeap
 */

defined( 'ABSPATH' ) || exit;

class CiteLeap_Pricing {

	/** Last date the pricing table was sanity-checked against published
	 *  provider rates. Bump this when re-verifying. ISO date. */
	const LAST_VERIFIED_AT = '2026-05-26';

	public static function last_verified_at(): string {
		return self::LAST_VERIFIED_AT;
	}

	public static function table(): array {
		$default = [
			/* Anthropic , confirmed May 2026 */
			'claude-opus-4-7'      => [ 'in' => 5.00,  'out' => 25.00 ],
			'claude-sonnet-4-6'    => [ 'in' => 3.00,  'out' => 15.00 ],
			'claude-haiku-4-5'     => [ 'in' => 1.00,  'out' => 5.00  ],
			/* OpenAI , best-effort May 2026 estimates */
			'gpt-5.5-pro'          => [ 'in' => 15.00, 'out' => 60.00 ],
			'gpt-5.5'              => [ 'in' => 5.00,  'out' => 15.00 ],
			'gpt-5.4'              => [ 'in' => 2.00,  'out' => 8.00  ],
			'gpt-5.4-mini'         => [ 'in' => 0.50,  'out' => 2.00  ],
			/* Google Gemini , best-effort May 2026 estimates */
			'gemini-3.1-pro'       => [ 'in' => 5.00,  'out' => 15.00 ],
			'gemini-2.5-pro'       => [ 'in' => 1.25,  'out' => 10.00 ],
			'gemini-2.5-flash'     => [ 'in' => 0.10,  'out' => 0.40  ],
			'gemini-2.5-flash-lite'=> [ 'in' => 0.05,  'out' => 0.20  ],
		];
		$overrides = (array) get_option( 'citeleap_pricing_overrides', [] );
		foreach ( $overrides as $model => $row ) {
			if ( ! is_array( $row ) ) continue;
			$default[ $model ] = [
				'in'  => isset( $row['in'] )  ? (float) $row['in']  : ( $default[ $model ]['in']  ?? 0 ),
				'out' => isset( $row['out'] ) ? (float) $row['out'] : ( $default[ $model ]['out'] ?? 0 ),
			];
		}
		return (array) apply_filters( 'citeleap_pricing', $default );
	}

	public static function cost_for( string $model, int $input_tokens, int $output_tokens ): float {
		$t = self::table();
		$row = $t[ $model ] ?? null;
		if ( ! $row ) return 0.0;
		return ( $input_tokens  * (float) $row['in']  / 1_000_000.0 )
		     + ( $output_tokens * (float) $row['out'] / 1_000_000.0 );
	}
}
