<?php
/**
 * B2B GEO Index: statistics and difference insights (1.24.0).
 *
 * Pure functions on the stored results, no network:
 *   - Wilson interval (95%) for a share, used for every AI number.
 *   - Spearman rank correlation, for website score versus AI score.
 *   - Insights per list: website versus AI in four groups, differences
 *     between the assistants, Dutch versus international companies named,
 *     and the most cited sources per sector.
 *   - The gap with the sector leader for one company: points, the sources
 *     that name the leader and not the company, and the checks it misses.
 *
 * Why ranges and groups: AI answers vary from run to run. SparkToro and
 * Gumshoe found the same list in under 1 of 100 runs; the University of
 * St. Gallen advises at least 7 runs per question; a top 10 often has a
 * margin of about five places (Search Engine Journal, July 2026).
 *
 * @package BoomingVenture
 */

defined( 'ABSPATH' ) || exit;

/** Wilson score interval for k of n, as fractions [ low, high ]. */
function bv_gi_wilson( int $k, int $n, float $z = 1.96 ): array {
	if ( $n <= 0 ) {
		return [ 0.0, 1.0 ];
	}
	$p      = $k / $n;
	$z2     = $z * $z;
	$den    = 1 + $z2 / $n;
	$center = ( $p + $z2 / ( 2 * $n ) ) / $den;
	$half   = $z * sqrt( $p * ( 1 - $p ) / $n + $z2 / ( 4 * $n * $n ) ) / $den;
	return [ max( 0.0, $center - $half ), min( 1.0, $center + $half ) ];
}

/** Ranks with ties shared (average rank), 1 = lowest. */
function bv_gi_ranks( array $v ): array {
	$idx = array_keys( $v );
	usort( $idx, static fn( $a, $b ) => $v[ $a ] <=> $v[ $b ] );
	$out = [];
	$i   = 0;
	$n   = count( $idx );
	while ( $i < $n ) {
		$j = $i;
		while ( $j + 1 < $n && $v[ $idx[ $j + 1 ] ] == $v[ $idx[ $i ] ] ) { // phpcs:ignore Universal.Operators.StrictComparisons
			$j++;
		}
		$avg = ( $i + $j ) / 2 + 1;
		for ( $k = $i; $k <= $j; $k++ ) {
			$out[ $idx[ $k ] ] = $avg;
		}
		$i = $j + 1;
	}
	return $out;
}

/** Spearman rank correlation of two equal-length arrays, or null with fewer than 5 pairs or no spread. */
function bv_gi_spearman( array $x, array $y ): ?float {
	$x = array_values( $x );
	$y = array_values( $y );
	$n = min( count( $x ), count( $y ) );
	if ( $n < 5 ) {
		return null;
	}
	$rx = bv_gi_ranks( array_slice( $x, 0, $n ) );
	$ry = bv_gi_ranks( array_slice( $y, 0, $n ) );
	$mx = array_sum( $rx ) / $n;
	$my = array_sum( $ry ) / $n;
	$sxy = 0.0;
	$sxx = 0.0;
	$syy = 0.0;
	for ( $i = 0; $i < $n; $i++ ) {
		$dx   = $rx[ $i ] - $mx;
		$dy   = $ry[ $i ] - $my;
		$sxy += $dx * $dy;
		$sxx += $dx * $dx;
		$syy += $dy * $dy;
	}
	return ( $sxx > 0 && $syy > 0 ) ? $sxy / sqrt( $sxx * $syy ) : null;
}

/** Median of a list of numbers. */
function bv_gi_median( array $v ): float {
	sort( $v );
	$n = count( $v );
	if ( ! $n ) {
		return 0.0;
	}
	return $n % 2 ? (float) $v[ intdiv( $n, 2 ) ] : ( $v[ $n / 2 - 1 ] + $v[ $n / 2 ] ) / 2;
}

/** Group for places outside the top 10: strong, mid or low. */
function bv_gi_group( int $score ): string {
	return $score >= 50 ? 'strong' : ( $score >= 25 ? 'mid' : 'low' );
}

/** Do the results have the method 2 details (sources, overlap, cross mentions)? */
function bv_gi_has_v2( array $res ): bool {
	return (int) ( $res['method'] ?? 1 ) >= 2 && ! empty( $res['ans'] );
}

/**
 * Insights for one list, from the ranked rows and the AI results.
 * Returns [] without method 2 data.
 */
function bv_gi_insights( string $list, array $rows ): array {
	$res = (array) get_option( 'bv_gi_ai_results', [] );
	if ( ! bv_gi_has_v2( $res ) ) {
		return [];
	}
	$out = [ 'quad' => [], 'quad_n' => [ 'both' => 0, 'web' => 0, 'ai' => 0, 'none' => 0 ], 'rho' => null ];

	/* Website versus AI: split at the medians of this list. */
	$with = array_values( array_filter( $rows, static fn( $r ) => null !== $r['ai'] && empty( $r['ai_low'] ) ) );
	if ( count( $with ) >= 5 ) {
		$mw = bv_gi_median( array_column( $with, 'ready' ) );
		$ma = bv_gi_median( array_column( $with, 'ai' ) );
		foreach ( $with as $r ) {
			$w = $r['ready'] >= $mw;
			$a = $r['ai'] >= $ma && $r['ai'] > 0;
			$q = $w && $a ? 'both' : ( $w ? 'web' : ( $a ? 'ai' : 'none' ) );
			$out['quad'][ $r['domain'] ] = $q;
			$out['quad_n'][ $q ]++;
		}
		$out['rho']    = bv_gi_spearman( array_column( $with, 'ready' ), array_column( $with, 'ai' ) );
		$out['med_w']  = (int) round( $mw );
		$out['med_a']  = (int) round( $ma );
		$out['n_pair'] = count( $with );
	}

	/* Differences between the assistants: visibility per assistant and pairwise overlap. */
	$eng = [];
	foreach ( (array) ( $res['rows'][ $list ] ?? [] ) as $row ) {
		foreach ( (array) ( $row['e'] ?? [] ) as $e => $nm ) {
			$eng[ $e ] = [ (int) ( $eng[ $e ][0] ?? 0 ) + (int) $nm[0], (int) ( $eng[ $e ][1] ?? 0 ) + (int) $nm[1] ];
		}
	}
	$out['eng'] = [];
	foreach ( array_intersect( array_keys( bv_gi_ai_engines() ), (array) ( $res['engines'] ?? [] ) ) as $e ) {
		if ( ! empty( $eng[ $e ][0] ) ) {
			$out['eng'][ $e ] = (int) round( 100 * $eng[ $e ][1] / $eng[ $e ][0] );
		}
	}
	arsort( $out['eng'] );
	$out['pairs'] = [];
	foreach ( (array) ( $res['pairs'][ $list ] ?? [] ) as $k => [ $sum, $cnt ] ) {
		if ( $cnt > 0 ) {
			$out['pairs'][ $k ] = (int) round( 100 * $sum / $cnt );
		}
	}
	arsort( $out['pairs'] );
	$out['agree'] = [];
	foreach ( (array) ( $res['agree'][ $list ] ?? [] ) as $sector => [ $sum, $cnt ] ) {
		if ( $cnt > 0 ) {
			$out['agree'][ $sector ] = (int) round( 100 * $sum / $cnt );
		}
	}

	/* Dutch versus international: share of Dutch companies among the index companies named. */
	$out['cross'] = bv_gi_cross( $res );

	/* Most cited sources per sector, as a share of the sector's answers. */
	$out['src'] = [];
	$owners     = [];
	foreach ( [ 'nl', 'intl' ] as $l ) {
		foreach ( (array) ( bv_gi_companies()[ $l ] ?? [] ) as $c ) {
			$owners[ $c['domain'] ] = $c['name'];
		}
	}
	foreach ( (array) ( $res['src'][ $list ] ?? [] ) as $sector => $hosts ) {
		$ans = max( 1, (int) ( $res['ans'][ $list ][ $sector ] ?? 0 ) );
		$top = [];
		foreach ( array_slice( (array) $hosts, 0, 10, true ) as $h => $d ) {
			$owner = '';
			foreach ( $owners as $dom => $name ) {
				if ( $h === $dom || str_ends_with( $h, '.' . $dom ) ) {
					$owner = $name;
					break;
				}
			}
			$top[] = [ 'host' => (string) $h, 'pct' => (int) round( 100 * (int) $d['a'] / $ans ), 'owner' => $owner ];
		}
		$out['src'][ $sector ] = [ 'answers' => (int) ( $res['ans'][ $list ][ $sector ] ?? 0 ), 'top' => $top ];
	}
	return $out;
}

/**
 * Dutch versus international, from the cross counts of both lists.
 * nl_q: share of Dutch companies among index companies named in answers to
 * Dutch questions; en_q: the same for English questions. Per sector too.
 */
function bv_gi_cross( array $res ): array {
	$out = [ 'nl_q' => null, 'en_q' => null, 'sectors' => [] ];
	$tot = [ 'nl' => [ 0, 0 ], 'intl' => [ 0, 0 ] ]; // [ Dutch named, all named ]
	foreach ( [ 'nl', 'intl' ] as $l ) {
		foreach ( (array) ( $res['cross'][ $l ] ?? [] ) as $sector => $x ) {
			$own   = (int) ( $x['own'] ?? 0 );
			$other = (int) ( $x['other'] ?? 0 );
			$dutch = 'nl' === $l ? $own : $other;
			$all   = $own + $other;
			$tot[ $l ][0] += $dutch;
			$tot[ $l ][1] += $all;
			$out['sectors'][ $sector ][ 'nl' === $l ? 'nl_q' : 'en_q' ] = $all ? (int) round( 100 * $dutch / $all ) : null;
		}
	}
	$out['nl_q'] = $tot['nl'][1] ? (int) round( 100 * $tot['nl'][0] / $tot['nl'][1] ) : null;
	$out['en_q'] = $tot['intl'][1] ? (int) round( 100 * $tot['intl'][0] / $tot['intl'][1] ) : null;
	$out['n_nl'] = $tot['nl'][1];
	$out['n_en'] = $tot['intl'][1];
	return $out;
}

/** The website checks a leader can have and a company can miss, in the order of the score. */
function bv_gi_gap_checks(): array {
	return [ 'bot_OAI-SearchBot', 'bot_Claude-SearchBot', 'bot_PerplexityBot', 'bot_Google-Extended', 'bot_Bingbot', 'html', 'schema_org', 'schema_sameas', 'wikidata', 'wikipedia', 'fresh', 'lang', 'llms' ];
}

/** Leader per sector: the first ranked row of each sector. */
function bv_gi_leaders( array $rows ): array {
	$out = [];
	foreach ( $rows as $r ) {
		if ( ! isset( $out[ $r['sector'] ] ) ) {
			$out[ $r['sector'] ] = $r;
		}
	}
	return $out;
}

/**
 * The gap between a company (or a checked domain) and its sector leader.
 * $checks: the company's website checks; $domain: its domain (may be outside the index).
 * Returns null when there is no leader or the company is the leader.
 */
function bv_gi_gap( string $list, string $sector, ?array $leader, string $domain, array $checks, int $score = -1, int $margin = 0 ): ?array {
	if ( ! $leader || $leader['domain'] === $domain ) {
		return null;
	}
	$missing = [];
	foreach ( bv_gi_gap_checks() as $k ) {
		if ( true === ( $leader['checks'][ $k ] ?? null ) && false === ( $checks[ $k ] ?? null ) ) {
			$missing[] = $k;
		}
	}
	$sources = [];
	$res     = (array) get_option( 'bv_gi_ai_results', [] );
	foreach ( (array) ( $res['src'][ $list ][ $sector ] ?? [] ) as $h => $d ) {
		$by = (array) ( $d['by'] ?? [] );
		if ( ! empty( $by[ $leader['domain'] ] ) && empty( $by[ $domain ] ) && $h !== $domain && ! str_ends_with( (string) $h, '.' . $domain ) && $h !== $leader['domain'] && ! str_ends_with( (string) $h, '.' . $leader['domain'] ) ) {
			$sources[ (string) $h ] = (int) $by[ $leader['domain'] ];
		}
	}
	arsort( $sources );
	$gap = $score >= 0 ? (int) $leader['score'] - $score : null;
	return [
		'leader'  => [ 'name' => $leader['name'], 'domain' => $leader['domain'], 'score' => (int) $leader['score'] ],
		'points'  => $gap,
		'tied'    => null !== $gap && $gap <= (int) ( $leader['margin'] ?? 0 ) + $margin,
		'missing' => $missing,
		'sources' => array_slice( array_keys( $sources ), 0, 5 ),
	];
}
