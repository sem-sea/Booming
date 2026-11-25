<?php
/**
 * B2B GEO Index (1.12.0).
 *
 * Measures, every month, how ready the websites of 400 B2B companies are for
 * AI search, and ranks them. Two lists: 200 Dutch companies and 200
 * international B2B software companies (blocks/geo-index/companies.json).
 *
 * How it runs
 *  - On the first Monday of each month (05:00 UTC) WP-Cron queues both lists. A batch job then
 *    measures a few domains per run, every two minutes, until the queue is
 *    empty. No outside service, no browser, no computer needs to be on.
 *  - Results live in the option bv_gi_results (not autoloaded); the scores
 *    of the previous run in bv_gi_prev, for the monthly change column.
 *  - Public check for one domain: GET /wp-json/bv/v1/geo-check?domain=x.com
 *    (rate limited per IP, cached per domain for 24 hours).
 *
 * Every request goes through wp_safe_remote_get (refuses private and local
 * addresses), with a short timeout and an honest user agent.
 *
 * What is measured (100 points)
 *  - AI crawlers allowed in robots.txt ............ 30
 *  - Main text readable without JavaScript ........ 15
 *  - Structured data on the homepage .............. 20
 *  - Entity: Wikidata item and Wikipedia article .. 15
 *  - Freshness: newest sitemap lastmod ............ 10
 *  - Language: Dutch version (NL list) or
 *    several language versions (international) ..  8
 *  - llms.txt ......................................  2
 *
 * @package BoomingVenture
 */

defined( 'ABSPATH' ) || exit;

const BV_GI_UA = 'BoomingVentureGEOIndex/1.0 (+https://boomingventure.com/b2b-geo-index/; info@boomingventure.com)';

/** Crawler tokens and their weight (sum 30). */
function bv_gi_bots(): array {
	return [
		'OAI-SearchBot'    => 6,
		'ChatGPT-User'     => 3,
		'GPTBot'           => 3,
		'Claude-SearchBot' => 3,
		'ClaudeBot'        => 2,
		'PerplexityBot'    => 5,
		'Google-Extended'  => 3,
		'Bingbot'          => 5,
	];
}

function bv_gi_companies(): array {
	static $data = null;
	if ( null === $data ) {
		$file = get_theme_file_path( 'blocks/geo-index/companies.json' );
		$data = is_readable( $file ) ? (array) json_decode( (string) file_get_contents( $file ), true ) : [];
	}
	return $data;
}

/* ------------------------------------------------------------------ parsing (pure) */

/**
 * Is $path allowed for $bot under this robots.txt? true, false.
 * Follows RFC 9309: the most specific matching group wins (falls back to *),
 * the longest matching rule wins, Allow wins a tie, no rule means allowed.
 */
function bv_gi_robots_allows( string $robots, string $bot, string $path = '/' ): bool {
	$groups  = [];
	$current = null;
	$last_ua = false;
	foreach ( preg_split( '/\r\n|\r|\n/', $robots ) as $line ) {
		$line = trim( preg_replace( '/#.*$/', '', $line ) );
		if ( '' === $line || false === strpos( $line, ':' ) ) {
			continue;
		}
		[ $key, $val ] = array_map( 'trim', explode( ':', $line, 2 ) );
		$key = strtolower( $key );
		if ( 'user-agent' === $key ) {
			if ( ! $last_ua ) {
				$groups[] = [ 'agents' => [], 'rules' => [] ];
				$current  = count( $groups ) - 1;
			}
			$groups[ $current ]['agents'][] = strtolower( $val );
			$last_ua = true;
		} elseif ( null !== $current && ( 'allow' === $key || 'disallow' === $key ) ) {
			$groups[ $current ]['rules'][] = [ $key, $val ];
			$last_ua = false;
		} else {
			$last_ua = false;
		}
	}
	$token = strtolower( $bot );
	$rules = [];
	foreach ( $groups as $g ) {
		if ( in_array( $token, $g['agents'], true ) ) {
			$rules = array_merge( $rules, $g['rules'] );
		}
	}
	if ( ! $rules && ! array_filter( $groups, static fn( $g ) => in_array( $token, $g['agents'], true ) ) ) {
		foreach ( $groups as $g ) {
			if ( in_array( '*', $g['agents'], true ) ) {
				$rules = array_merge( $rules, $g['rules'] );
			}
		}
	}
	$best = -1;
	$allowed = true;
	foreach ( $rules as [ $type, $pattern ] ) {
		if ( '' === $pattern ) {
			continue; // "Disallow:" with no value allows everything
		}
		$regex = '#^' . str_replace( [ '\*', '\$' ], [ '.*', '$' ], preg_quote( $pattern, '#' ) ) . '#';
		if ( preg_match( $regex, $path ) ) {
			$len = strlen( $pattern );
			if ( $len > $best || ( $len === $best && 'allow' === $type ) ) {
				$best    = $len;
				$allowed = ( 'allow' === $type );
			}
		}
	}
	return $allowed;
}

/** Sitemap URLs listed in robots.txt. */
function bv_gi_robots_sitemaps( string $robots ): array {
	preg_match_all( '/^\s*sitemap:\s*(\S+)/im', $robots, $m );
	return array_slice( $m[1] ?? [], 0, 3 );
}

/** Facts from the homepage HTML: words, language, hreflang, JSON-LD types, sameAs, noindex. */
function bv_gi_parse_home( string $html ): array {
	$out = [ 'words' => 0, 'lang' => '', 'hreflang' => [], 'types' => [], 'sameas' => false, 'noindex' => false ];
	if ( preg_match( '/<html[^>]*\slang=["\']?([a-zA-Z-]+)/i', $html, $m ) ) {
		$out['lang'] = strtolower( $m[1] );
	}
	if ( preg_match_all( '/<link[^>]+hreflang=["\']?([a-zA-Z-]+)/i', $html, $m ) ) {
		$out['hreflang'] = array_values( array_unique( array_map( 'strtolower', $m[1] ) ) );
	}
	if ( preg_match( '/<meta[^>]+name=["\']robots["\'][^>]+content=["\'][^"\']*noindex/i', $html ) ) {
		$out['noindex'] = true;
	}
	$types = [];
	if ( preg_match_all( '#<script[^>]+application/ld\+json[^>]*>(.*?)</script>#is', $html, $m ) ) {
		foreach ( $m[1] as $json ) {
			$data = json_decode( html_entity_decode( trim( $json ), ENT_QUOTES ), true );
			if ( is_array( $data ) ) {
				bv_gi_walk_ld( $data, $types, $out['sameas'] );
			}
		}
	}
	$out['types'] = array_values( array_unique( $types ) );
	$text = preg_replace( '#<(script|style|svg|template|iframe)\b[^>]*>.*?</\1>#is', ' ', $html );
	$text = html_entity_decode( wp_strip_all_tags_fallback( $text ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	$out['words'] = (int) preg_match_all( '/\p{L}{2,}/u', $text );
	return $out;
}

function bv_gi_walk_ld( array $node, array &$types, bool &$sameas ): void {
	if ( isset( $node['@type'] ) ) {
		foreach ( (array) $node['@type'] as $t ) {
			if ( is_string( $t ) ) {
				$types[] = $t;
			}
		}
	}
	if ( ! empty( $node['sameAs'] ) ) {
		$sameas = true;
	}
	foreach ( $node as $v ) {
		if ( is_array( $v ) ) {
			bv_gi_walk_ld( $v, $types, $sameas );
		}
	}
}

/** wp_strip_all_tags without WordPress loaded (for tests). */
function wp_strip_all_tags_fallback( string $s ): string {
	return function_exists( 'wp_strip_all_tags' ) ? wp_strip_all_tags( $s ) : strip_tags( $s );
}

/** Newest <lastmod> in a sitemap, as a Unix time, or 0. */
function bv_gi_newest_lastmod( string $xml, int $now ): int {
	$newest = 0;
	if ( preg_match_all( '#<lastmod>\s*([^<\s]+)\s*</lastmod>#i', $xml, $m ) ) {
		foreach ( $m[1] as $d ) {
			$t = strtotime( $d );
			if ( $t && $t <= $now + DAY_IN_SECONDS && $t > $newest ) {
				$newest = min( $t, $now );
			}
		}
	}
	return $newest;
}

/* ------------------------------------------------------------------ scoring (pure) */

const BV_GI_ORG_TYPES  = [ 'Organization', 'Corporation', 'LocalBusiness', 'ProfessionalService', 'OnlineBusiness', 'NGO' ];
const BV_GI_MORE_TYPES = [ 'Product', 'SoftwareApplication', 'WebApplication', 'Service', 'Article', 'BlogPosting', 'NewsArticle', 'FAQPage', 'BreadcrumbList', 'HowTo', 'Offer' ];

/**
 * Score one measurement. $m holds the raw facts, $list is 'nl' or 'intl'.
 * Returns [ 'score' => int, 'parts' => [ key => points ], 'checks' => [ key => bool|null ] ].
 */
function bv_gi_score( array $m, string $list ): array {
	$parts  = [];
	$checks = [];

	$bots = 0;
	foreach ( bv_gi_bots() as $bot => $w ) {
		$ok = $m['bots'][ $bot ] ?? null;
		$checks[ 'bot_' . $bot ] = $ok;
		if ( true === $ok ) {
			$bots += $w;
		}
	}
	$parts['bots'] = $bots;

	$words = (int) ( $m['words'] ?? 0 );
	$home_ok = ! empty( $m['home_ok'] );
	$parts['html'] = ! $home_ok ? 0 : ( $words >= 300 ? 15 : ( $words >= 150 ? 8 : 0 ) );
	$checks['html'] = $home_ok ? $words >= 150 : null;

	$types  = (array) ( $m['types'] ?? [] );
	$org    = (bool) array_intersect( $types, BV_GI_ORG_TYPES );
	$site   = in_array( 'WebSite', $types, true );
	$more   = (bool) array_intersect( $types, BV_GI_MORE_TYPES );
	$sameas = ! empty( $m['sameas'] );
	$parts['schema'] = ( $org ? 8 : 0 ) + ( $sameas ? 4 : 0 ) + ( $site ? 4 : 0 ) + ( $more ? 4 : 0 );
	$checks['schema_org'] = $home_ok ? $org : null;
	$checks['schema_sameas'] = $home_ok ? $sameas : null;

	$wd = $m['wikidata'] ?? null;
	$wp = $m['wikipedia'] ?? null;
	$parts['entity'] = ( true === $wd ? 7 : 0 ) + ( true === $wp ? 8 : 0 );
	$checks['wikidata']  = $wd;
	$checks['wikipedia'] = $wp;

	$days = $m['fresh_days'] ?? null;
	$parts['fresh'] = null === $days ? 0 : ( $days <= 30 ? 10 : ( $days <= 90 ? 6 : ( $days <= 365 ? 3 : 0 ) ) );
	$checks['fresh'] = null === $days ? null : $days <= 90;

	$hl = (array) ( $m['hreflang'] ?? [] );
	if ( 'nl' === $list ) {
		$lang_ok = 0 === strpos( (string) ( $m['lang'] ?? '' ), 'nl' ) || (bool) preg_grep( '/^nl/', $hl );
	} else {
		$lang_ok = count( array_diff( $hl, [ 'x-default' ] ) ) >= 2;
	}
	$parts['lang']   = $home_ok && $lang_ok ? 8 : 0;
	$checks['lang']  = $home_ok ? $lang_ok : null;

	$parts['llms']  = ! empty( $m['llms'] ) ? 2 : 0;
	$checks['llms'] = isset( $m['llms'] ) ? (bool) $m['llms'] : null;

	return [ 'score' => (int) array_sum( $parts ), 'parts' => $parts, 'checks' => $checks ];
}

/* ------------------------------------------------------------------ measuring (network) */

function bv_gi_get( string $url, int $limit = 1500000 ): array {
	$t0  = microtime( true );
	$res = wp_safe_remote_get( $url, [
		'timeout'             => 6,
		'redirection'         => 5,
		'user-agent'          => BV_GI_UA,
		'limit_response_size' => $limit,
		'headers'             => [ 'Accept' => 'text/html,application/xhtml+xml,text/plain,application/xml;q=0.9,*/*;q=0.8' ],
	] );
	if ( is_wp_error( $res ) ) {
		return [ 'code' => 0, 'body' => '', 'type' => '', 'url' => $url, 'ms' => 0 ];
	}
	$final = $url;
	if ( isset( $res['http_response'] ) && method_exists( $res['http_response'], 'get_response_object' ) ) {
		$obj = $res['http_response']->get_response_object();
		if ( ! empty( $obj->url ) ) {
			$final = $obj->url;
		}
	}
	return [
		'code' => (int) wp_remote_retrieve_response_code( $res ),
		'body' => (string) wp_remote_retrieve_body( $res ),
		'type' => strtolower( (string) wp_remote_retrieve_header( $res, 'content-type' ) ),
		'url'  => $final,
		'ms'   => (int) round( ( microtime( true ) - $t0 ) * 1000 ),
	];
}

/** Wikidata item whose official website matches the domain, and its Wikipedia sitelinks. */
function bv_gi_entity( string $name, string $host ): array {
	$host = preg_replace( '/^www\./', '', strtolower( $host ) );
	$api  = 'https://www.wikidata.org/w/api.php?format=json&action=wbsearchentities&type=item&limit=7&language=en&uselang=en&search=';
	$hits = [];
	foreach ( array_unique( [ $name, preg_replace( '/\..*$/', '', $host ) ] ) as $q ) {
		$r = bv_gi_get( $api . rawurlencode( $q ), 200000 );
		$j = json_decode( $r['body'], true );
		foreach ( (array) ( $j['search'] ?? [] ) as $s ) {
			$hits[] = $s['id'];
		}
		if ( $hits ) {
			break;
		}
	}
	if ( ! $hits ) {
		return [ 'wikidata' => false, 'wikipedia' => false ];
	}
	$r = bv_gi_get( 'https://www.wikidata.org/w/api.php?format=json&action=wbgetentities&props=claims|sitelinks&ids=' . implode( '|', array_slice( array_unique( $hits ), 0, 7 ) ), 1500000 );
	$j = json_decode( $r['body'], true );
	foreach ( (array) ( $j['entities'] ?? [] ) as $e ) {
		foreach ( (array) ( $e['claims']['P856'] ?? [] ) as $c ) {
			$url = $c['mainsnak']['datavalue']['value'] ?? '';
			$h   = preg_replace( '/^www\./', '', strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) ) );
			if ( $h && ( $h === $host || str_ends_with( $h, '.' . $host ) || str_ends_with( $host, '.' . $h ) ) ) {
				$links = array_keys( (array) ( $e['sitelinks'] ?? [] ) );
				return [ 'wikidata' => true, 'wikipedia' => (bool) array_intersect( $links, [ 'enwiki', 'nlwiki' ] ) ];
			}
		}
	}
	return [ 'wikidata' => false, 'wikipedia' => false ];
}

/** Measure one company. Returns the raw facts plus the score. */
function bv_gi_measure( string $domain, string $name, string $list ): array {
	$domain = strtolower( $domain );
	$now    = time();
	$m      = [ 'domain' => $domain, 'measured' => $now, 'bots' => [] ];

	$home = bv_gi_get( 'https://' . $domain . '/' );
	if ( $home['code'] >= 400 || 0 === $home['code'] ) {
		$alt = bv_gi_get( 'https://www.' . $domain . '/' );
		if ( $alt['code'] && $alt['code'] < 400 ) {
			$home = $alt;
		}
	}
	$host = (string) wp_parse_url( $home['url'], PHP_URL_HOST );
	$base = 'https://' . ( $host ?: $domain );
	$m['final_url'] = $home['url'];
	$m['home_code'] = $home['code'];
	$m['home_ok']   = $home['code'] >= 200 && $home['code'] < 300 && false !== strpos( $home['type'], 'html' );
	$m['ms']        = $home['ms'];
	if ( $m['home_ok'] ) {
		$m = array_merge( $m, bv_gi_parse_home( $home['body'] ) );
	}

	$robots = bv_gi_get( $base . '/robots.txt', 500000 );
	$m['robots_code'] = $robots['code'];
	$robots_txt = ( $robots['code'] >= 200 && $robots['code'] < 300 && false === strpos( $robots['type'], 'html' ) ) ? $robots['body'] : '';
	foreach ( array_keys( bv_gi_bots() ) as $bot ) {
		if ( $robots['code'] >= 500 || 0 === $robots['code'] ) {
			$m['bots'][ $bot ] = null; // unknown: robots.txt unreachable
		} else {
			// homepage and a typical inner page must both be open
			$m['bots'][ $bot ] = bv_gi_robots_allows( $robots_txt, $bot, '/' ) && bv_gi_robots_allows( $robots_txt, $bot, '/page/' );
		}
	}

	$llms = bv_gi_get( $base . '/llms.txt', 200000 );
	$m['llms'] = $llms['code'] >= 200 && $llms['code'] < 300 && false === strpos( $llms['type'], 'html' ) && strlen( trim( $llms['body'] ) ) > 20;

	$maps = bv_gi_robots_sitemaps( $robots_txt );
	if ( ! $maps ) {
		$maps = [ $base . '/sitemap.xml', $base . '/sitemap_index.xml' ];
	}
	$newest = 0;
	foreach ( $maps as $i => $map ) {
		$s = bv_gi_get( $map, 2000000 );
		if ( $s['code'] < 200 || $s['code'] >= 300 ) {
			continue;
		}
		$newest = bv_gi_newest_lastmod( $s['body'], $now );
		if ( ! $newest && preg_match( '#<sitemap>\s*<loc>\s*([^<\s]+)#i', $s['body'], $c ) ) {
			$child  = bv_gi_get( html_entity_decode( $c[1] ), 2000000 );
			$newest = bv_gi_newest_lastmod( $child['body'], $now );
		}
		if ( $newest || $i >= 1 ) {
			break;
		}
	}
	$m['fresh_days'] = $newest ? (int) floor( ( $now - $newest ) / DAY_IN_SECONDS ) : null;

	$m = array_merge( $m, bv_gi_entity( $name, $host ?: $domain ) );
	$m['status'] = $m['home_ok'] ? 'ok' : 'partial';

	return array_merge( $m, bv_gi_score( $m, $list ) );
}

/* ------------------------------------------------------------------ monthly run (WP-Cron) */

/*
 * A daily check starts the run on the first Monday of each month, or the
 * first day after it that WP-Cron fires, once per month. The old weekly
 * event (up to 1.18.2) is removed.
 */
add_action( 'init', static function () {
	if ( wp_next_scheduled( 'bv_gi_weekly' ) ) {
		wp_clear_scheduled_hook( 'bv_gi_weekly' );
	}
	if ( ! wp_next_scheduled( 'bv_gi_daily' ) ) {
		wp_schedule_event( strtotime( 'tomorrow 05:00 UTC' ), 'daily', 'bv_gi_daily' );
	}
} );

/*
 * The automatic scan runs on the first Monday of each month (06:00 in the
 * Netherlands). Its own month marker means a manual scan earlier in the
 * month never cancels it. On a site that gets this version mid-month the
 * current month counts as done, so the first automatic scan is next month.
 */
add_action( 'init', static function () {
	if ( false === get_option( 'bv_gi_monthly_last' ) ) {
		$done = time() >= strtotime( 'first monday of this month 05:00 UTC' );
		update_option( 'bv_gi_monthly_last', $done ? gmdate( 'Y-m' ) : gmdate( 'Y-m', strtotime( 'first day of last month' ) ), false );
	}
} );
add_action( 'bv_gi_daily', static function () {
	$month = gmdate( 'Y-m' );
	if ( time() >= strtotime( 'first monday of this month 05:00 UTC' ) && get_option( 'bv_gi_monthly_last' ) !== $month ) {
		update_option( 'bv_gi_monthly_last', $month, false );
		do_action( 'bv_gi_run', 'monthly' );
	}
} );

/*
 * A new company list (the "version" in companies.json) starts one fresh
 * website measurement right after the theme update, so the new companies get
 * a website score without waiting for the next month. It asks no AI questions
 * (no cost) and writes no monthly report; both follow on the first Monday.
 * Results of companies that left the list are dropped.
 */
add_action( 'init', static function () {
	$version = (string) ( bv_gi_companies()['version'] ?? '' );
	if ( '' === $version || get_option( 'bv_gi_list_version' ) === $version ) {
		return;
	}
	update_option( 'bv_gi_list_version', $version, false );
	$keep = [];
	foreach ( [ 'nl', 'intl' ] as $list ) {
		foreach ( (array) ( bv_gi_companies()[ $list ] ?? [] ) as $c ) {
			$keep[ $list ][ $c['domain'] ] = true;
		}
	}
	$results = (array) get_option( 'bv_gi_results', [] );
	foreach ( $results as $list => $rows ) {
		$results[ $list ] = array_intersect_key( (array) $rows, $keep[ $list ] ?? [] );
	}
	update_option( 'bv_gi_results', $results, false );
	bv_gi_start_web( 'list' ); // websites only: free, AI questions wait for the monthly run
}, 20 );

/* Kept for anyone who still calls the old hook. */
add_action( 'bv_gi_weekly', static fn() => do_action( 'bv_gi_run' ) );

/**
 * Start a website measurement. $source: monthly (first Monday, gets the
 * monthly report), manual (Settings > GEO Index) or list (new company list).
 */
function bv_gi_start_web( string $source = 'manual' ): void {
	$queue = [];
	foreach ( [ 'nl', 'intl' ] as $list ) {
		foreach ( (array) ( bv_gi_companies()[ $list ] ?? [] ) as $i => $c ) {
			$queue[] = [ $list, $i ];
		}
	}
	/* The reference for "change since last month" is the last run of an earlier month (1.24.0): a second run in the same month keeps it. */
	$last_done = (int) ( ( (array) get_option( 'bv_gi_meta', [] ) )['completed'] ?? 0 );
	if ( ! get_option( 'bv_gi_prev' ) || ( $last_done && gmdate( 'Y-m', $last_done ) !== gmdate( 'Y-m' ) ) ) {
		$results = (array) get_option( 'bv_gi_results', [] );
		$prev    = [];
		foreach ( $results as $list => $rows ) {
			foreach ( (array) $rows as $domain => $r ) {
				$ai = function_exists( 'bv_gi_ai_score' ) ? bv_gi_ai_score( $list, $domain ) : null;
				$prev[ $list ][ $domain ] = isset( $r['score'] ) ? bv_gi_combined( (int) $r['score'], $ai ) : null;
			}
		}
		update_option( 'bv_gi_prev', $prev, false );
	}
	update_option( 'bv_gi_queue', $queue, false );
	$source = in_array( $source, [ 'monthly', 'manual', 'list' ], true ) ? $source : 'manual';
	update_option( 'bv_gi_meta', [ 'started' => time(), 'completed' => 0, 'source' => $source, 'total' => count( $queue ), 'current' => '', 'stopped' => 0 ] + (array) get_option( 'bv_gi_meta', [] ), false );
	wp_clear_scheduled_hook( 'bv_gi_batch' );
	wp_schedule_single_event( time() + 5, 'bv_gi_batch' );
}
add_action( 'bv_gi_run', static fn( $source = '' ) => bv_gi_start_web( (string) $source ) );

/*
 * Short runs that save after every company (1.21.1). The next run is
 * planned before any company is measured, so a host that stops a long
 * request loses at most one company and the chain keeps going.
 */
add_action( 'bv_gi_batch', static function () {
	if ( get_transient( 'bv_gi_lock' ) ) {
		return;
	}
	set_transient( 'bv_gi_lock', 1, 2 * MINUTE_IN_SECONDS );
	$queue = (array) get_option( 'bv_gi_queue', [] );
	if ( $queue ) {
		wp_clear_scheduled_hook( 'bv_gi_batch' );
		wp_schedule_single_event( time() + 3 * MINUTE_IN_SECONDS, 'bv_gi_batch' ); // safety net if this request is stopped
	}
	$t0 = time();
	while ( $queue && time() - $t0 < 20 ) {
		[ $list, $i ] = array_shift( $queue );
		update_option( 'bv_gi_queue', $queue, false );
		$c = bv_gi_companies()[ $list ][ $i ] ?? null;
		if ( $c ) {
			update_option( 'bv_gi_meta', [ 'current' => $c['name'] ] + (array) get_option( 'bv_gi_meta', [] ), false );
			$results = (array) get_option( 'bv_gi_results', [] );
			$results[ $list ][ $c['domain'] ] = bv_gi_measure( $c['domain'], $c['name'], $list );
			update_option( 'bv_gi_results', $results, false );
		}
	}
	delete_transient( 'bv_gi_lock' );
	if ( $queue ) {
		wp_clear_scheduled_hook( 'bv_gi_batch' );
		wp_schedule_single_event( time() + 30, 'bv_gi_batch' );
		return;
	}
	$meta = (array) get_option( 'bv_gi_meta', [] );
	if ( ! empty( $meta['completed'] ) && $meta['completed'] >= ( $meta['started'] ?? 0 ) ) {
		return; // this run was already finished by an earlier event
	}
	$meta['completed'] = time();
	$meta['current']   = '';
	if ( empty( $meta['first'] ) ) {
		$meta['first'] = $meta['completed'];
	}
	update_option( 'bv_gi_meta', $meta, false );
	bv_gi_touch_pages();
	do_action( 'bv_gi_complete', 'web' );
} );

/*
 * Watchdog (1.21.1): every 10 minutes, a queue with work but no planned run
 * gets a new run. This restarts a measurement that stopped halfway.
 */
add_action( 'init', static function () {
	if ( get_transient( 'bv_gi_watchdog' ) ) {
		return;
	}
	set_transient( 'bv_gi_watchdog', 1, 10 * MINUTE_IN_SECONDS );
	foreach ( [ 'bv_gi_queue' => 'bv_gi_batch', 'bv_gi_ai_queue' => 'bv_gi_ai_batch' ] as $option => $hook ) {
		if ( get_option( $option ) && ! wp_next_scheduled( $hook ) ) {
			wp_schedule_single_event( time() + 10, $hook );
		}
	}
}, 30 );

/* ------------------------------------------------------------------ public check */

add_action( 'rest_api_init', static function () {
	register_rest_route( 'bv/v1', '/geo-check', [
		'methods'             => 'GET',
		'permission_callback' => '__return_true',
		'args'                => [
			'domain' => [ 'required' => true, 'type' => 'string' ],
			'lang'   => [ 'type' => 'string', 'default' => 'en' ],
			'sector' => [ 'type' => 'string', 'default' => '' ],
		],
		'callback'            => static function ( WP_REST_Request $req ) {
			$raw    = strtolower( trim( (string) $req['domain'] ) );
			$raw    = preg_replace( '#^[a-z]+://#', '', $raw );
			$domain = preg_replace( '#^www\.#', '', preg_replace( '#[/?\#:].*$#', '', $raw ) );
			if ( ! preg_match( '/^(?=.{4,253}$)([a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,24}$/', $domain ) ) {
				return new WP_Error( 'bv_domain', 'invalid domain', [ 'status' => 400 ] );
			}
			$list   = 'nl' === $req['lang'] ? 'nl' : 'intl';
			$sector = sanitize_key( (string) $req['sector'] );
			$cached = get_transient( 'bv_gi_c_' . md5( $list . $domain ) );
			if ( $cached ) {
				return bv_gi_check_extra( (array) $cached, $list, $sector );
			}
			$ip  = preg_replace( '/[^0-9a-f:.]/i', '', (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ) );
			$key = 'bv_gi_rl_' . md5( $ip );
			$n   = (int) get_transient( $key );
			if ( $n >= 5 ) {
				return new WP_Error( 'bv_rate', 'too many checks', [ 'status' => 429 ] );
			}
			set_transient( $key, $n + 1, HOUR_IN_SECONDS );
			$m   = bv_gi_measure( $domain, preg_replace( '/\..*$/', '', $domain ), $list );
			$out = [ 'domain' => $domain, 'score' => $m['score'], 'parts' => $m['parts'], 'checks' => $m['checks'], 'status' => $m['status'] ];
			set_transient( 'bv_gi_c_' . md5( $list . $domain ), $out, DAY_IN_SECONDS );
			return bv_gi_check_extra( $out, $list, $sector );
		},
	] );
} );

/**
 * Extra for the public check (1.24.0): the gap with the sector leader (the
 * checks the leader has, the most cited sources in the sector) and, for a
 * domain in the index, its AI numbers. Computed on every request from the
 * stored results, so it is not part of the cached measurement.
 */
function bv_gi_check_extra( array $out, string $list, string $sector ): array {
	$rows = bv_gi_ranked( $list );
	$self = null;
	foreach ( $rows as $r ) {
		if ( $r['domain'] === $out['domain'] ) {
			$self = $r;
			break;
		}
	}
	if ( $self ) {
		$sector = $self['sector'];
		$ai     = $self['ai_data'];
		$out['index'] = [
			'name'  => $self['name'],
			'rank'  => (int) $self['rank'],
			'group' => (string) $self['group'],
			'score' => (int) $self['score'],
			'ai'    => $ai ? [ 'score' => $ai['score'], 'n' => $ai['n'], 'm' => $ai['m'], 'vis' => $ai['vis'], 'lo' => $ai['vis_lo'], 'hi' => $ai['vis_hi'], 'low' => $ai['low'] ] : null,
		];
	}
	$sectors = (array) ( bv_gi_companies()['sectors'] ?? [] );
	if ( '' === $sector || ! isset( $sectors[ $sector ] ) ) {
		return $out;
	}
	$leaders = function_exists( 'bv_gi_leaders' ) ? bv_gi_leaders( $rows ) : [];
	$leader  = $leaders[ $sector ] ?? null;
	if ( ! $leader ) {
		return $out;
	}
	$gap = bv_gi_gap( $list, $sector, $leader, (string) $out['domain'], (array) $out['checks'], $self ? (int) $self['score'] : -1, $self ? (int) $self['margin'] : 0 );
	$res = (array) get_option( 'bv_gi_ai_results', [] );
	$top = [];
	$ans = max( 1, (int) ( $res['ans'][ $list ][ $sector ] ?? 0 ) );
	foreach ( array_slice( (array) ( $res['src'][ $list ][ $sector ] ?? [] ), 0, 5, true ) as $h => $d ) {
		$top[] = [ 'host' => (string) $h, 'pct' => (int) round( 100 * (int) $d['a'] / $ans ) ];
	}
	$out['gap'] = [
		'sector'  => (string) $sectors[ $sector ][ 'nl' === $list ? 0 : 1 ],
		'leader'  => $gap ? $gap['leader'] : [ 'name' => $leader['name'], 'domain' => $leader['domain'], 'score' => (int) $leader['score'] ],
		'self'    => ! $gap,
		'points'  => $gap['points'] ?? null,
		'tied'    => (bool) ( $gap['tied'] ?? false ),
		'missing' => $gap['missing'] ?? [],
		'sources' => $top,
	];
	return $out;
}

/** Total score: half website and half AI score when there are enough AI answers, else the website score alone. */
function bv_gi_combined( int $ready, ?array $ai ): int {
	return ( $ai && empty( $ai['low'] ) ) ? (int) round( ( $ready + (int) $ai['score'] ) / 2 ) : $ready;
}

/**
 * Rows for one list, ranked, with the change since last month (1.24.0):
 * - margin: half the AI margin of error (the website half is exact);
 * - change_sig: the change is larger than the noise of both months' AI
 *   scores (95%), or the AI half is not part of the score;
 * - method_new: last month used another method or had no AI score, so the
 *   two months cannot be compared;
 * - group: strong, mid or low for places outside the top 10.
 */
function bv_gi_ranked( string $list ): array {
	$results = (array) ( get_option( 'bv_gi_results', [] )[ $list ] ?? [] );
	$prev    = (array) ( get_option( 'bv_gi_prev', [] )[ $list ] ?? [] );
	$aires   = (array) get_option( 'bv_gi_ai_results', [] );
	$aiprev  = (array) get_option( 'bv_gi_ai_prev', [] );
	$same    = (int) ( $aiprev['method'] ?? 1 ) === (int) ( $aires['method'] ?? 1 );
	$rows    = [];
	foreach ( (array) ( bv_gi_companies()[ $list ] ?? [] ) as $c ) {
		$r = $results[ $c['domain'] ] ?? null;
		if ( ! $r ) {
			continue;
		}
		$p      = $prev[ $c['domain'] ] ?? null;
		$ai     = function_exists( 'bv_gi_ai_score_in' ) ? bv_gi_ai_score_in( $aires, $list, $c['domain'] ) : null;
		$pai    = ( function_exists( 'bv_gi_ai_score_in' ) && $aiprev ) ? bv_gi_ai_score_in( $aiprev, $list, $c['domain'] ) : null;
		$use    = $ai && empty( $ai['low'] );
		$p_use  = $pai && empty( $pai['low'] );
		$total  = bv_gi_combined( (int) $r['score'], $ai );
		$change = null;
		$sig    = null;
		$new    = false;
		if ( null !== $p ) {
			if ( $use !== $p_use || ( $use && ! $same ) ) {
				$new = true;
			} else {
				$change = $total - (int) $p;
				$sig    = $use ? abs( $change ) > 0.5 * 196 * sqrt( (float) $ai['var'] + (float) $pai['var'] ) : 0 !== $change;
			}
		}
		$rows[] = $c + [
			'score'      => $total,
			'ready'      => (int) $r['score'],
			'ai'         => $ai ? $ai['score'] : null,
			'ai_low'     => $ai ? (bool) $ai['low'] : false,
			'ai_data'    => $ai,
			'ai_eng'     => $ai ? $ai['eng'] : [],
			'margin'     => $use ? (int) round( $ai['margin'] / 2 ) : 0,
			'change'     => $change,
			'change_sig' => $sig,
			'method_new' => $new,
			'checks'     => (array) $r['checks'],
			'status'     => $r['status'] ?? 'ok',
		];
	}
	usort( $rows, static fn( $a, $b ) => [ $b['score'], $a['name'] ] <=> [ $a['score'], $b['name'] ] );
	$rank = 0;
	$last = null;
	foreach ( $rows as $i => &$row ) {
		if ( $row['score'] !== $last ) {
			$rank = $i + 1;
			$last = $row['score'];
		}
		$row['rank']  = $rank;
		$row['group'] = $rank <= 10 ? '' : bv_gi_group( (int) $row['score'] );
	}
	unset( $row );
	return $rows;
}

/*
 * The GEO Index menu link only shows once its page is published, so the
 * menu never points to a page that visitors cannot open.
 */
add_filter( 'render_block_core/navigation', function ( $html ) {
	if ( false === strpos( $html, 'b2b-geo-index' ) ) {
		return $html;
	}
	/* Removing the <li> from the finished menu keeps one list; an empty link block would split it in two. */
	return preg_replace_callback(
		'#<li\b[^>]*>\s*<a\b[^>]*href="([^"]*b2b-geo-index/?)"[^>]*>.*?</a>\s*</li>#s',
		static function ( $m ) {
			$page = get_page_by_path( trim( (string) wp_parse_url( $m[1], PHP_URL_PATH ), '/' ) );
			return ( $page && 'publish' === $page->post_status ) ? $m[0] : '';
		},
		$html
	);
} );

/* ------------------------------------------------------------------ open data and freshness */

/** IDs of published pages that show the index (EN and NL). */
function bv_gi_pages(): array {
	global $wpdb;
	return array_map( 'intval', (array) $wpdb->get_col(
		"SELECT ID FROM {$wpdb->posts} WHERE post_type = 'page' AND post_status = 'publish' AND post_content LIKE '%wp:booming-venture/geo-index%' LIMIT 5"
	) );
}

/*
 * After each monthly run the index pages get a new modified date: the
 * sitemap lastmod, dateModified and "Last updated" follow the data, and the
 * publish hook in inc/geo.php pings IndexNow. Content stays as it is.
 */
function bv_gi_touch_pages(): void {
	$ids = bv_gi_pages();
	if ( ! $ids ) {
		return;
	}
	kses_remove_filters();
	foreach ( $ids as $id ) {
		wp_update_post( [ 'ID' => $id ] );
	}
	kses_init_filters();
}

/** One ranking row as a flat record for CSV and JSON downloads. */
function bv_gi_export_row( array $r, string $list ): array {
	$sectors = (array) ( bv_gi_companies()['sectors'] ?? [] );
	$ai      = $r['ai_data'] ?? null;
	$yn      = static fn( $v ) => true === $v ? 'yes' : ( false === $v ? 'no' : '' );
	$bots    = [ 'bot_OAI-SearchBot', 'bot_Claude-SearchBot', 'bot_PerplexityBot', 'bot_Google-Extended', 'bot_Bingbot' ];
	return [
		'rank'            => (int) $r['rank'],
		'company'         => $r['name'],
		'domain'          => $r['domain'],
		'sector'          => $sectors[ $r['sector'] ][ 'nl' === $list ? 0 : 1 ] ?? $r['sector'],
		'group'           => (string) ( $r['group'] ?? '' ),
		'geo_score'       => (int) $r['score'],
		'geo_margin'      => (int) ( $r['margin'] ?? 0 ),
		'ai_score'        => null === $r['ai'] ? '' : (int) $r['ai'],
		'ai_answers'      => (int) ( $ai['n'] ?? 0 ) ?: '',
		'ai_low_data'     => $ai ? ( empty( $ai['low'] ) ? 'no' : 'yes' ) : '',
		'visibility_pct'  => $ai ? (int) $ai['vis'] : '',
		'visibility_low'  => $ai ? (int) $ai['vis_lo'] : '',
		'visibility_high' => $ai ? (int) $ai['vis_hi'] : '',
		'share_of_voice'  => $ai && null !== $ai['sov'] ? (int) $ai['sov'] : '',
		'first_mention'   => $ai ? (int) $ai['first'] : '',
		'sentiment'       => $ai && null !== $ai['sent'] ? (int) $ai['sent'] : '',
		'cited_pct'       => $ai ? (int) $ai['cite'] : '',
		'website_score'   => (int) $r['ready'],
		'change_month'    => null === $r['change'] ? '' : (int) $r['change'],
		'change_real'     => null === ( $r['change_sig'] ?? null ) ? '' : ( $r['change_sig'] ? 'yes' : 'within margin' ),
		'ai_crawlers'     => count( array_filter( $bots, static fn( $k ) => true === ( $r['checks'][ $k ] ?? null ) ) ) . '/5',
		'html_without_js' => $yn( $r['checks']['html'] ?? null ),
		'schema_org'      => $yn( $r['checks']['schema_org'] ?? null ),
		'wikipedia'       => $yn( $r['checks']['wikipedia'] ?? null ),
		'fresh_sitemap'   => $yn( $r['checks']['fresh'] ?? null ),
		'language'        => $yn( $r['checks']['lang'] ?? null ),
	];
}

/*
 * Open data: GET /wp-json/bv/v1/geo-index?list=nl|intl&format=json|csv.
 * Journalists, analysts and AI tools can cite the numbers with a source link.
 */
add_action( 'rest_api_init', static function () {
	register_rest_route( 'bv/v1', '/geo-index', [
		'methods'             => 'GET',
		'permission_callback' => '__return_true',
		'args'                => [
			'list'   => [ 'type' => 'string', 'enum' => [ 'nl', 'intl' ], 'default' => 'nl' ],
			'format' => [ 'type' => 'string', 'enum' => [ 'json', 'csv' ], 'default' => 'json' ],
		],
		'callback'            => static function ( WP_REST_Request $req ) {
			$list = (string) $req['list'];
			$meta = (array) get_option( 'bv_gi_meta', [] );
			$page = get_page_by_path( 'nl' === $list ? 'nl/b2b-geo-index' : 'b2b-geo-index' );
			$res  = rest_ensure_response( [
				'name'     => 'B2B GEO Index',
				'list'     => $list,
				'measured' => empty( $meta['completed'] ) ? null : gmdate( 'c', (int) $meta['completed'] ),
				'source'   => ( $page && 'publish' === $page->post_status ) ? get_permalink( $page ) : home_url( '/' ),
				'rows'     => array_map( static fn( $r ) => bv_gi_export_row( $r, $list ), bv_gi_ranked( $list ) ),
			] );
			$res->header( 'Cache-Control', 'public, max-age=3600' );
			return $res;
		},
	] );
} );

add_filter( 'rest_pre_serve_request', static function ( $served, $result, $request ) {
	if ( $served || '/bv/v1/geo-index' !== $request->get_route() || 'csv' !== $request['format'] || $result->is_error() ) {
		return $served;
	}
	$data = $result->get_data();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: inline; filename="b2b-geo-index-' . sanitize_key( $data['list'] ) . '.csv"' );
	header( 'Cache-Control: public, max-age=3600' );
	$out = fopen( 'php://output', 'w' );
	fwrite( $out, "\xEF\xBB\xBF" ); // UTF-8 mark, so Excel shows accents correctly
	$head = $data['rows'] ? array_keys( $data['rows'][0] ) : array_keys( bv_gi_export_row( [ 'rank' => 0, 'name' => '', 'domain' => '', 'sector' => '', 'score' => 0, 'ai' => null, 'ready' => 0, 'change' => null, 'checks' => [] ], 'intl' ) );
	fputcsv( $out, $head, ',', '"', '' );
	foreach ( $data['rows'] as $row ) {
		fputcsv( $out, $row, ',', '"', '' );
	}
	fclose( $out );
	return true;
}, 10, 3 );
