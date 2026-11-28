<?php
/**
 * Growth pages (1.30.0): sector pages of the B2B GEO Index, the GEO service,
 * Rotterdam, cases, the AI check and statistics pages, plus the links and
 * schema that tie them together.
 *
 * @package BoomingVenture
 */

defined( 'ABSPATH' ) || exit;

/** Slug and phrase per sector: [ nl slug, en slug, nl phrase, en phrase ]. */
function bv_gi_sector_slugs(): array {
	return [
		'c20'  => [ 'chemie', 'chemicals', 'in de chemie', 'in chemicals' ],
		'c26'  => [ 'elektronica', 'electronics', 'in elektronica en elektrotechniek', 'in electronics and electrical equipment' ],
		'c28'  => [ 'machinebouw', 'machinery', 'in de machinebouw', 'in machinery' ],
		'c29'  => [ 'voertuigen-schepen-vliegtuigen', 'vehicles-ships-aircraft', 'in voertuigen, schepen en vliegtuigen', 'in vehicles, ships and aircraft' ],
		'c35'  => [ 'energie', 'energy', 'in de energiesector', 'in energy' ],
		'c42'  => [ 'infrastructuur-waterbouw', 'civil-engineering', 'in infrastructuur en waterbouw', 'in civil engineering' ],
		'c46'  => [ 'groothandel', 'wholesale', 'in de groothandel', 'in wholesale' ],
		'c49'  => [ 'transport-logistiek', 'transport-logistics', 'in transport en logistiek', 'in transport and logistics' ],
		'c581' => [ 'vakinformatie-uitgeverijen', 'professional-publishing', 'in vakinformatie en uitgeverijen', 'in professional publishing' ],
		'c582' => [ 'software', 'software', 'in software', 'in software' ],
		'c61'  => [ 'telecom', 'telecommunications', 'in telecom', 'in telecommunications' ],
		'c62'  => [ 'it-diensten', 'it-services', 'in IT-diensten', 'in IT services' ],
		'c63'  => [ 'hosting-datacenters', 'hosting-data-centers', 'in hosting en datacenters', 'in hosting and data centers' ],
		'c64'  => [ 'banken-betalingen', 'banking-payments', 'in banken en betalingen', 'in banking and payments' ],
		'c65'  => [ 'verzekeringen', 'insurance', 'in verzekeringen', 'in insurance' ],
		'c69'  => [ 'juridische-dienstverlening', 'legal-services', 'in juridische dienstverlening', 'in legal services' ],
		'c70'  => [ 'managementadvies', 'management-consulting', 'in managementadvies', 'in management consulting' ],
		'c71'  => [ 'ingenieurs-testen-keuren', 'engineering-testing-inspection', 'in ingenieurs, testen en keuren', 'in engineering, testing and inspection' ],
		'c73'  => [ 'reclame-marktonderzoek', 'advertising-market-research', 'in reclame en marktonderzoek', 'in advertising and market research' ],
		'c78'  => [ 'uitzenden-werving', 'staffing-recruitment', 'in uitzenden en werving', 'in staffing and recruitment' ],
	];
}

/** "in de chemie" / "in chemicals" for running text. */
function bv_gi_sector_phrase( string $key, bool $nl ): string {
	$s = bv_gi_sector_slugs()[ $key ] ?? null;
	return $s ? $s[ $nl ? 2 : 3 ] : $key;
}

/**
 * Published sector pages in one language, in the order of the standard:
 * [ key => [ 'url' => ..., 'name' => ... ] ].
 */
function bv_gi_sector_pages( string $lang ): array {
	static $cache = [];
	if ( isset( $cache[ $lang ] ) ) {
		return $cache[ $lang ];
	}
	$ids = get_posts( [
		'post_type'        => 'page',
		'post_status'      => 'publish',
		'numberposts'      => 60,
		'fields'           => 'ids',
		'meta_key'         => '_bv_gi_sector', // phpcs:ignore WordPress.DB.SlowDBQuery
		'suppress_filters' => true,
	] );
	$by = [];
	foreach ( $ids as $id ) {
		$is_nl = 'nl' === get_post_meta( $id, '_bv_lang', true );
		if ( ( 'nl' === $lang ) !== $is_nl ) {
			continue;
		}
		$by[ (string) get_post_meta( $id, '_bv_gi_sector', true ) ] = (int) $id;
	}
	$sectors = function_exists( 'bv_gi_companies' ) ? (array) ( bv_gi_companies()['sectors'] ?? [] ) : [];
	$out     = [];
	foreach ( $sectors as $key => $names ) {
		if ( isset( $by[ $key ] ) ) {
			$out[ $key ] = [ 'url' => get_permalink( $by[ $key ] ), 'name' => (string) $names[ 'nl' === $lang ? 0 : 1 ] ];
		}
	}
	return $cache[ $lang ] = $out;
}

/* ------------------------------------------------------------------ schema */

/* Sector pages: the sector ranking as an ItemList, linked to the index Dataset. */
add_filter( 'wpseo_schema_graph', static function ( $graph ) {
	if ( ! is_array( $graph ) || ! is_singular( 'page' ) || ! function_exists( 'bv_gi_sector_data' ) ) {
		return $graph;
	}
	$post = get_post();
	$key  = $post ? (string) get_post_meta( $post->ID, '_bv_gi_sector', true ) : '';
	if ( '' === $key ) {
		return $graph;
	}
	$nl = 'nl' === get_post_meta( $post->ID, '_bv_lang', true );
	$s  = bv_gi_sector_data( $nl ? 'nl' : 'intl', $key );
	if ( ! $s ) {
		return $graph;
	}
	$items = [];
	foreach ( array_slice( $s['rows'], 0, 10 ) as $i => $r ) {
		$items[] = [ '@type' => 'ListItem', 'position' => $i + 1, 'item' => [ '@type' => 'Organization', 'name' => $r['name'], 'url' => 'https://' . $r['domain'] . '/' ] ];
	}
	$url     = get_permalink( $post );
	$name    = $s['names'][ $nl ? 0 : 1 ];
	$graph[] = array_filter( [
		'@type'           => 'ItemList',
		'@id'             => $url . '#ranking',
		'name'            => $nl ? 'B2B GEO Index: ' . $name . ', lijst Nederland' : 'B2B GEO Index: ' . $name . ', international list',
		'itemListOrder'   => 'https://schema.org/ItemListOrderDescending',
		'numberOfItems'   => count( $items ),
		'itemListElement' => $items,
		'url'             => $url,
	] );
	return $graph;
} );
