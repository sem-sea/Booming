<?php
/**
 * CiteLeap , LLM router + provider adapters.
 *
 * Three providers supported: Anthropic Claude, OpenAI, Google Gemini.
 * Each provider is a thin adapter around its public REST API. The
 * router chooses the provider based on stored settings, and the
 * adapter returns plain text.
 *
 * Model lineup (May 2026 research baked in as defaults):
 *
 * Claude:
 *   - Opus 4.7  , best reasoning, most expensive ($5/$25 per M)
 *   - Sonnet 4.6, best daily-driver writer ($3/$15 per M)
 *   - Haiku 4.5 , cheap + fast ($1/$5 per M)
 *
 * OpenAI:
 *   - gpt-5.5-pro , highest-intelligence reasoning
 *   - gpt-5.5     , best writer
 *   - gpt-5.4     , cheaper reasoning
 *   - gpt-5.4-mini, cheapest
 *
 * Gemini:
 *   - gemini-3.1-pro    , latest reasoning, 1M context
 *   - gemini-2.5-pro    , reasoning
 *   - gemini-2.5-flash  , cheap + fast
 *
 * @package CiteLeap
 */

defined( 'ABSPATH' ) || exit;

class CiteLeap_LLM {

	public static function defaults(): array {
		return [
			'providers' => [ 'claude', 'openai', 'gemini' ],
			'models'    => [
				'claude' => [
					'reasoning' => 'claude-opus-4-7',
					'writing'   => 'claude-sonnet-4-6',
					'cheap'     => 'claude-haiku-4-5',
				],
				'openai' => [
					'reasoning' => 'gpt-5.5-pro',
					'writing'   => 'gpt-5.5',
					'cheap'     => 'gpt-5.4-mini',
				],
				'gemini' => [
					'reasoning' => 'gemini-3.1-pro',
					'writing'   => 'gemini-2.5-pro',
					'cheap'     => 'gemini-2.5-flash',
				],
			],
			'available_models' => [
				'claude' => [ 'claude-opus-4-7', 'claude-sonnet-4-6', 'claude-haiku-4-5' ],
				'openai' => [ 'gpt-5.5-pro', 'gpt-5.5', 'gpt-5.4', 'gpt-5.4-mini' ],
				'gemini' => [ 'gemini-3.1-pro', 'gemini-2.5-pro', 'gemini-2.5-flash', 'gemini-2.5-flash-lite' ],
			],
		];
	}

	public static function get_api_key( string $provider ): string {
		$keys = (array) get_option( CITELEAP_OPTION_API_KEYS, [] );
		return (string) ( $keys[ $provider ] ?? '' );
	}

	public static function get_model( string $provider, string $role ): string {
		$models = (array) get_option( CITELEAP_OPTION_MODELS, [] );
		$d = self::defaults();
		return (string) ( $models[ $provider ][ $role ] ?? $d['models'][ $provider ][ $role ] ?? '' );
	}

	public static function get_provider( string $role ): string {
		$models = (array) get_option( CITELEAP_OPTION_MODELS, [] );
		$p = (string) ( $models[ 'provider_' . $role ] ?? 'claude' );
		return in_array( $p, [ 'claude', 'openai', 'gemini' ], true ) ? $p : 'claude';
	}

	/**
	 * High-level chat call. Picks provider + model from settings based
	 * on $role ('reasoning' for ideation, 'writing' for drafts).
	 *
	 * @return array{ok:bool, text:string, raw:array, provider:string, model:string, error:string}
	 */
	public static function chat( string $role, string $system, string $user, int $max_tokens = 4000 ): array {
		$provider = self::get_provider( $role );
		$model    = self::get_model( $provider, $role );
		$key      = self::get_api_key( $provider );

		if ( ! $key ) {
			return [
				'ok'       => false,
				'text'     => '',
				'raw'      => [],
				'provider' => $provider,
				'model'    => $model,
				'error'    => sprintf( __( 'No API key configured for provider: %s', 'citeleap' ), $provider ),
			];
		}

		return match ( $provider ) {
			'claude' => self::call_claude( $key, $model, $system, $user, $max_tokens ),
			'openai' => self::call_openai( $key, $model, $system, $user, $max_tokens ),
			'gemini' => self::call_gemini( $key, $model, $system, $user, $max_tokens ),
			default  => [ 'ok' => false, 'text' => '', 'raw' => [], 'provider' => $provider, 'model' => $model, 'error' => 'unknown provider' ],
		};
	}

	private static function call_claude( string $key, string $model, string $system, string $user, int $max_tokens ): array {
		$resp = wp_remote_post( 'https://api.anthropic.com/v1/messages', [
			'timeout' => 120,
			'headers' => [
				'x-api-key'         => $key,
				'anthropic-version' => '2023-06-01',
				'content-type'      => 'application/json',
			],
			'body'    => wp_json_encode( [
				'model'      => $model,
				'max_tokens' => $max_tokens,
				'system'     => $system,
				'messages'   => [
					[ 'role' => 'user', 'content' => $user ],
				],
			] ),
		] );
		if ( is_wp_error( $resp ) ) {
			return [ 'ok' => false, 'text' => '', 'raw' => [], 'provider' => 'claude', 'model' => $model, 'error' => $resp->get_error_message() ];
		}
		$code = (int) wp_remote_retrieve_response_code( $resp );
		$body = json_decode( (string) wp_remote_retrieve_body( $resp ), true );
		if ( 200 !== $code || ! is_array( $body ) ) {
			return [ 'ok' => false, 'text' => '', 'raw' => (array) $body, 'provider' => 'claude', 'model' => $model, 'error' => 'HTTP ' . $code . ' ' . wp_json_encode( $body ) ];
		}
		$text = '';
		foreach ( ( $body['content'] ?? [] ) as $block ) {
			if ( ( $block['type'] ?? '' ) === 'text' ) $text .= (string) ( $block['text'] ?? '' );
		}
		return [ 'ok' => true, 'text' => $text, 'raw' => $body, 'provider' => 'claude', 'model' => $model, 'error' => '' ];
	}

	private static function call_openai( string $key, string $model, string $system, string $user, int $max_tokens ): array {
		$resp = wp_remote_post( 'https://api.openai.com/v1/chat/completions', [
			'timeout' => 120,
			'headers' => [
				'authorization' => 'Bearer ' . $key,
				'content-type'  => 'application/json',
			],
			'body' => wp_json_encode( [
				'model'                 => $model,
				'max_completion_tokens' => $max_tokens,
				'messages'              => [
					[ 'role' => 'system', 'content' => $system ],
					[ 'role' => 'user',   'content' => $user ],
				],
			] ),
		] );
		if ( is_wp_error( $resp ) ) {
			return [ 'ok' => false, 'text' => '', 'raw' => [], 'provider' => 'openai', 'model' => $model, 'error' => $resp->get_error_message() ];
		}
		$code = (int) wp_remote_retrieve_response_code( $resp );
		$body = json_decode( (string) wp_remote_retrieve_body( $resp ), true );
		if ( 200 !== $code || ! is_array( $body ) ) {
			return [ 'ok' => false, 'text' => '', 'raw' => (array) $body, 'provider' => 'openai', 'model' => $model, 'error' => 'HTTP ' . $code . ' ' . wp_json_encode( $body ) ];
		}
		$text = (string) ( $body['choices'][0]['message']['content'] ?? '' );
		return [ 'ok' => true, 'text' => $text, 'raw' => $body, 'provider' => 'openai', 'model' => $model, 'error' => '' ];
	}

	private static function call_gemini( string $key, string $model, string $system, string $user, int $max_tokens ): array {
		$url = sprintf( 'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s', rawurlencode( $model ), rawurlencode( $key ) );
		$resp = wp_remote_post( $url, [
			'timeout' => 120,
			'headers' => [ 'content-type' => 'application/json' ],
			'body'    => wp_json_encode( [
				'systemInstruction' => [ 'role' => 'system', 'parts' => [ [ 'text' => $system ] ] ],
				'contents'          => [ [ 'role' => 'user', 'parts' => [ [ 'text' => $user ] ] ] ],
				'generationConfig'  => [ 'maxOutputTokens' => $max_tokens, 'temperature' => 0.7 ],
			] ),
		] );
		if ( is_wp_error( $resp ) ) {
			return [ 'ok' => false, 'text' => '', 'raw' => [], 'provider' => 'gemini', 'model' => $model, 'error' => $resp->get_error_message() ];
		}
		$code = (int) wp_remote_retrieve_response_code( $resp );
		$body = json_decode( (string) wp_remote_retrieve_body( $resp ), true );
		if ( 200 !== $code || ! is_array( $body ) ) {
			return [ 'ok' => false, 'text' => '', 'raw' => (array) $body, 'provider' => 'gemini', 'model' => $model, 'error' => 'HTTP ' . $code . ' ' . wp_json_encode( $body ) ];
		}
		$text = '';
		foreach ( ( $body['candidates'][0]['content']['parts'] ?? [] ) as $part ) {
			$text .= (string) ( $part['text'] ?? '' );
		}
		return [ 'ok' => true, 'text' => $text, 'raw' => $body, 'provider' => 'gemini', 'model' => $model, 'error' => '' ];
	}
}
