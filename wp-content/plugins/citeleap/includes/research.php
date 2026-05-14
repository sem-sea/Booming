<?php
/**
 * CiteLeap , research.php
 *
 * Real web-search-augmented research for the writer prompt.
 *
 * Approach (v2.0):
 *
 *  1. Anthropic Claude's `web_search_20250305` tool (built into the
 *     Messages API, no separate API key needed). When enabled, the
 *     writer call adds the tool, the model runs N searches against
 *     the open web, and returns inline citations as document
 *     references in its response. Cleanest path: one provider, no
 *     extra contract.
 *
 *  2. Operator-supplied SERP API key (Serper.dev OR Brave Search
 *     OR Tavily). Falls back to a manual HTTP search when Claude is
 *     not the writing model OR when the operator prefers external
 *     control. Returns top-5 organic results per query for the model
 *     to cite. Configured under Settings -> Research.
 *
 *  3. Citation extraction: after the writer returns, scan the body
 *     for outbound <a href> tags whose host is NOT this site. Store
 *     them in _citeleap_sources post meta so the operator can audit
 *     "which sources did the model actually use".
 *
 * @package CiteLeap
 */

defined( 'ABSPATH' ) || exit;

class CiteLeap_Research {

	public static function settings(): array {
		$r = (array) get_option( CITELEAP_OPTION_RESEARCH, [] );
		return [
			'enabled'       => isset( $r['enabled'] )       ? (bool) $r['enabled']       : true,
			'provider'      => (string) ( $r['provider']    ?? 'claude_native' ),       // claude_native | serper | brave | tavily | off
			'api_key'       => (string) ( $r['api_key']     ?? '' ),                    // encrypted via Crypto on save
			'max_searches'  => max( 1, min( 10, (int) ( $r['max_searches']  ?? 5 ) ) ),
			'min_citations' => max( 1, min( 10, (int) ( $r['min_citations'] ?? 3 ) ) ),
			'min_internal'  => max( 0, min( 10, (int) ( $r['min_internal']  ?? 2 ) ) ),
		];
	}

	public static function save_settings( array $args ): void {
		$prev = (array) get_option( CITELEAP_OPTION_RESEARCH, [] );
		$key  = (string) ( $args['api_key'] ?? '' );
		if ( '' === $key ) {
			$key = (string) ( $prev['api_key'] ?? '' );   // preserve existing on blank submit
		} else {
			$key = CiteLeap_Crypto::encrypt( sanitize_text_field( $key ) );
		}
		update_option( CITELEAP_OPTION_RESEARCH, [
			'enabled'       => ! empty( $args['enabled'] ),
			'provider'      => in_array( (string) ( $args['provider'] ?? '' ), [ 'claude_native', 'serper', 'brave', 'tavily', 'off' ], true ) ? (string) $args['provider'] : 'claude_native',
			'api_key'       => $key,
			'max_searches'  => max( 1, min( 10, (int) ( $args['max_searches']  ?? 5 ) ) ),
			'min_citations' => max( 1, min( 10, (int) ( $args['min_citations'] ?? 3 ) ) ),
			'min_internal'  => max( 0, min( 10, (int) ( $args['min_internal']  ?? 2 ) ) ),
		], false );
	}

	public static function decrypt_key(): string {
		$r = self::settings();
		return CiteLeap_Crypto::decrypt( (string) $r['api_key'] );
	}

	/** Should the writer call attach a web-search tool? */
	public static function should_attach_web_search( string $provider, string $model ): bool {
		$r = self::settings();
		if ( ! $r['enabled'] ) return false;
		/* Anthropic web search tool is supported on claude-* models from
		 * Sonnet 4 onward. */
		return 'claude' === $provider && ( false !== strpos( $model, 'sonnet' ) || false !== strpos( $model, 'opus' ) );
	}

	/** Build the Anthropic web_search tool spec to attach to the chat call. */
	public static function claude_web_search_tool_spec(): array {
		$r = self::settings();
		return [
			'type'         => 'web_search_20250305',
			'name'         => 'web_search',
			'max_uses'     => (int) $r['max_searches'],
		];
	}

	/** Run an out-of-band search to gather sources BEFORE the writer call,
	 *  for providers that do not have native web search. Returns an array
	 *  of [title, url, snippet] entries. */
	public static function fetch_sources( string $query ): array {
		$r   = self::settings();
		if ( ! $r['enabled'] || 'off' === $r['provider'] ) return [];
		$key = self::decrypt_key();
		if ( ! $key && in_array( $r['provider'], [ 'serper', 'brave', 'tavily' ], true ) ) return [];

		return match ( $r['provider'] ) {
			'serper' => self::fetch_serper( $key, $query, (int) $r['max_searches'] ),
			'brave'  => self::fetch_brave(  $key, $query, (int) $r['max_searches'] ),
			'tavily' => self::fetch_tavily( $key, $query, (int) $r['max_searches'] ),
			default  => [],
		};
	}

	private static function fetch_serper( string $key, string $query, int $n ): array {
		$resp = wp_remote_post( 'https://google.serper.dev/search', [
			'timeout' => 20,
			'headers' => [ 'X-API-KEY' => $key, 'Content-Type' => 'application/json' ],
			'body'    => wp_json_encode( [ 'q' => $query, 'num' => $n ] ),
		] );
		if ( is_wp_error( $resp ) ) return [];
		$body = json_decode( (string) wp_remote_retrieve_body( $resp ), true );
		if ( ! is_array( $body ) || empty( $body['organic'] ) ) return [];
		$out = [];
		foreach ( array_slice( (array) $body['organic'], 0, $n ) as $row ) {
			$out[] = [
				'title'   => (string) ( $row['title']   ?? '' ),
				'url'     => (string) ( $row['link']    ?? '' ),
				'snippet' => (string) ( $row['snippet'] ?? '' ),
			];
		}
		return $out;
	}

	private static function fetch_brave( string $key, string $query, int $n ): array {
		$url  = 'https://api.search.brave.com/res/v1/web/search?count=' . (int) $n . '&q=' . rawurlencode( $query );
		$resp = wp_remote_get( $url, [
			'timeout' => 20,
			'headers' => [ 'X-Subscription-Token' => $key, 'Accept' => 'application/json' ],
		] );
		if ( is_wp_error( $resp ) ) return [];
		$body = json_decode( (string) wp_remote_retrieve_body( $resp ), true );
		$results = $body['web']['results'] ?? [];
		$out = [];
		foreach ( array_slice( (array) $results, 0, $n ) as $row ) {
			$out[] = [
				'title'   => (string) ( $row['title']       ?? '' ),
				'url'     => (string) ( $row['url']         ?? '' ),
				'snippet' => (string) ( $row['description'] ?? '' ),
			];
		}
		return $out;
	}

	private static function fetch_tavily( string $key, string $query, int $n ): array {
		$resp = wp_remote_post( 'https://api.tavily.com/search', [
			'timeout' => 20,
			'headers' => [ 'Content-Type' => 'application/json' ],
			'body'    => wp_json_encode( [
				'api_key'        => $key,
				'query'          => $query,
				'max_results'    => $n,
				'search_depth'   => 'advanced',
				'include_answer' => false,
			] ),
		] );
		if ( is_wp_error( $resp ) ) return [];
		$body = json_decode( (string) wp_remote_retrieve_body( $resp ), true );
		$out = [];
		foreach ( array_slice( (array) ( $body['results'] ?? [] ), 0, $n ) as $row ) {
			$out[] = [
				'title'   => (string) ( $row['title']   ?? '' ),
				'url'     => (string) ( $row['url']     ?? '' ),
				'snippet' => (string) ( $row['content'] ?? '' ),
			];
		}
		return $out;
	}

	/** Render fetched sources as a prompt block. */
	public static function as_prompt_text( array $sources, int $min_citations = 3 ): string {
		if ( empty( $sources ) ) return '';
		$lines = [];
		$lines[] = sprintf(
			'You MUST cite at least %d of the named sources below inside the first 30%% of the body. Each citation is an inline <a href="…" rel="nofollow noopener">Source Name (Year)</a>. Use the publisher name as anchor text, never bare URLs. Quote concrete numbers, not vague claims. Do not invent stats not present in these sources.',
			$min_citations
		);
		$lines[] = '';
		$lines[] = 'Researched sources for this topic:';
		foreach ( $sources as $i => $src ) {
			$snip = mb_substr( (string) $src['snippet'], 0, 240 );
			$lines[] = sprintf( "%d. %s\n   %s\n   %s", $i + 1, $src['title'], $src['url'], $snip );
		}
		return implode( "\n", $lines );
	}

	/** After draft is back, harvest outbound links so the operator can
	 *  audit which sources the model actually cited. */
	public static function extract_outbound( string $body, string $home_host = '' ): array {
		if ( '' === $home_host ) {
			$home = wp_parse_url( (string) home_url( '/' ), PHP_URL_HOST );
			$home_host = is_string( $home ) ? $home : '';
		}
		$found = [];
		if ( preg_match_all( '/<a\b[^>]*href=["\']([^"\']+)["\'][^>]*>([^<]+)<\/a>/i', $body, $m, PREG_SET_ORDER ) ) {
			foreach ( $m as $hit ) {
				$href = (string) $hit[1];
				if ( 0 !== strpos( $href, 'http' ) ) continue;
				$host = (string) wp_parse_url( $href, PHP_URL_HOST );
				if ( $home_host && $host === $home_host ) continue;
				$found[] = [
					'host'   => $host,
					'url'    => $href,
					'anchor' => wp_strip_all_tags( (string) $hit[2] ),
				];
			}
		}
		/* Dedupe by URL. */
		$seen = [];
		$dedup = [];
		foreach ( $found as $row ) {
			if ( isset( $seen[ $row['url'] ] ) ) continue;
			$seen[ $row['url'] ] = true;
			$dedup[] = $row;
		}
		return $dedup;
	}
}
