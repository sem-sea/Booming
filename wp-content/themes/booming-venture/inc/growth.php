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

/** Paths of the 1.30.0 pages: menu links to them only show once the page is live. */
function bv_growth_paths(): array {
	return [
		'services/geo', 'geo-agency-rotterdam', 'cases', 'ai-visibility-check', 'ai-search-b2b-statistics', 'geo-pricing-netherlands',
		'nl/diensten/geo', 'nl/geo-bureau-rotterdam', 'nl/cases', 'nl/ai-check', 'nl/ai-zoeken-b2b-statistieken', 'nl/wat-kost-geo',
		'b2b-geo-index', 'nl/b2b-geo-index', 'b2b-geo-index/methodology', 'nl/b2b-geo-index/methode',
	];
}

/*
 * Menu guard: a single menu item (no submenu inside) that points to one of
 * the paths above disappears while its page is not published, so the menu
 * never links to a page visitors cannot open. Replaces the GEO Index-only
 * guard of 1.16.0.
 */
add_filter( 'render_block_core/navigation', static function ( $html ) {
	$guard = array_flip( bv_growth_paths() );
	return preg_replace_callback(
		'#<li\b[^>]*>\s*<a\b[^>]*href="([^"\#]*)(?:\#[^"]*)?"[^>]*>(?:(?!<li\b).)*?</a>\s*</li>#s',
		static function ( $m ) use ( $guard ) {
			$path = trim( (string) wp_parse_url( $m[1], PHP_URL_PATH ), '/' );
			if ( ! isset( $guard[ $path ] ) ) {
				return $m[0];
			}
			$page = get_page_by_path( $path );
			return ( $page && 'publish' === $page->post_status ) ? $m[0] : '';
		},
		$html
	);
}, 20 );

/* A submenu whose own page is not live yet keeps its children but loses the parent link target. */
add_filter( 'render_block_core/navigation-submenu', static function ( $html, $block ) {
	$url  = (string) ( $block['attrs']['url'] ?? '' );
	$path = trim( (string) wp_parse_url( $url, PHP_URL_PATH ), '/' );
	if ( '' === $path || ! in_array( $path, bv_growth_paths(), true ) ) {
		return $html;
	}
	$page = get_page_by_path( $path );
	if ( $page && 'publish' === $page->post_status ) {
		return $html;
	}
	return preg_replace( '#(<a\b[^>]*?)\shref="' . preg_quote( $url, '#' ) . '"#', '$1', $html, 1 );
}, 10, 2 );

/* ------------------------------------------------------------------ links from articles to the service */

/** URL of a live page by path, or ''. */
function bv_growth_url( string $path ): string {
	$p = get_page_by_path( $path );
	return ( $p && 'publish' === $p->post_status ) ? (string) get_permalink( $p ) : '';
}

/*
 * Articles about AI search end with one short line to the GEO service and
 * the free check. The wording rotates per article, so the anchor text into
 * the service page varies across the site.
 */
add_filter( 'the_content', static function ( $content ) {
	if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	$post = get_post();
	if ( ! $post || ! has_category( 'ai-search', $post ) ) {
		return $content;
	}
	$nl  = function_exists( 'bv_post_lang' ) && 'nl' === bv_post_lang( $post->ID );
	$geo = bv_growth_url( $nl ? 'nl/diensten/geo' : 'services/geo' );
	if ( '' === $geo ) {
		return $content;
	}
	$chk  = bv_growth_url( $nl ? 'nl/ai-check' : 'ai-visibility-check' );
	$a    = static fn( string $url, string $text ) => '<a href="' . esc_url( $url ) . '">' . esc_html( $text ) . '</a>';
	$line = $nl
		? [
			'Wil je dit laten doen? Bekijk ons ' . $a( $geo, 'GEO-traject voor B2B' ) . ', met een nulmeting op echte koopvragen en een maandelijkse meting.',
			'Liever hulp bij deze stappen? Lees hoe ons ' . $a( $geo, 'GEO-bureau voor B2B-bedrijven' ) . ' werkt, van nulmeting tot maandrapport.',
			'Wil je weten wat dit voor jouw bedrijf betekent? Lees over onze ' . $a( $geo, 'hulp bij AI-vindbaarheid' ) . '.',
			'Zelf geen tijd? Wij helpen B2B-teams om ' . $a( $geo, 'genoemd te worden in ChatGPT, Claude en Gemini' ) . ', met een vaste prijs.',
			'Meer weten over ' . $a( $geo, 'onze GEO-aanpak' ) . '? Je ziet daar de stappen, de prijs en wat je na 30 dagen hebt.',
		]
		: [
			'Want this done for you? See our ' . $a( $geo, 'GEO programme for B2B' ) . ', with a baseline on real buyer questions and monthly measurement.',
			'Prefer help with these steps? Read how our ' . $a( $geo, 'B2B GEO agency' ) . ' works, from baseline to monthly report.',
			'Want to know what this means for your company? Read about our ' . $a( $geo, 'help with AI visibility' ) . '.',
			'No time to do it yourself? We help B2B teams ' . $a( $geo, 'get named in ChatGPT, Claude and Gemini' ) . ', at a fixed price.',
			'Curious about ' . $a( $geo, 'our GEO approach' ) . '? It shows the steps, the price and what you have after 30 days.',
		];
	$text = $line[ $post->ID % count( $line ) ];
	if ( $chk ) {
		$text .= $nl
			? ' Of begin met de ' . $a( $chk, 'gratis AI-check' ) . '.'
			: ' Or start with the ' . $a( $chk, 'free AI visibility check' ) . '.';
	}
	return $content . '<aside class="bv-geo-cta"><p>' . $text . '</p></aside>';
}, 20 );

/* ------------------------------------------------------------------ schema */

/*
 * One author entity (1.30.0). Yoast names the WordPress user ("Team BV") as
 * a separate Person; the articles are Ben's, so every reference to that
 * Person points to the founder node the theme adds, and the extra node goes.
 */
add_filter( 'wpseo_schema_graph', static function ( $graph ) {
	if ( ! is_array( $graph ) ) {
		return $graph;
	}
	$ben = home_url( '/' ) . '#person-ben-verschuur';
	$old = [];
	foreach ( $graph as $i => $n ) {
		if ( is_array( $n ) && in_array( 'Person', (array) ( $n['@type'] ?? [] ), true ) && str_contains( (string) ( $n['@id'] ?? '' ), '#/schema/person/' ) ) {
			$old[] = (string) $n['@id'];
			unset( $graph[ $i ] );
		}
	}
	if ( ! $old ) {
		return $graph;
	}
	$fix = static function ( $v ) use ( &$fix, $old, $ben ) {
		if ( ! is_array( $v ) ) {
			return $v;
		}
		if ( isset( $v['@id'] ) && in_array( $v['@id'], $old, true ) && count( array_diff( array_keys( $v ), [ '@id', 'name' ] ) ) === 0 ) {
			return [ '@id' => $ben ];
		}
		foreach ( $v as $k => $x ) {
			$v[ $k ] = $fix( $x );
		}
		return $v;
	};
	return array_values( array_map( $fix, $graph ) );
}, 99 );

/* The author meta tag names the founder, like the byline and the schema. */
add_filter( 'wpseo_meta_author', static fn() => 'Ben Verschuur' );

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
