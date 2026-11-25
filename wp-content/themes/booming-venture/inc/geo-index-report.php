<?php
/**
 * Monthly B2B GEO Index report (1.20.0, monthly scans only since 1.22.0).
 *
 * When all numbers of the first-Monday measurement are in (the website checks and,
 * when AI measuring is on, the AI answers), the site writes one blog post in
 * English and one in Dutch from the measured data. No AI writes the text, so
 * nothing is made up and it costs nothing. The posts follow the blog layout
 * (key takeaways, capsule, tables, list, FAQ, CTA), are linked as
 * translations, sit in "AI Search & GEO", and get their schema, "Last
 * updated" line and IndexNow ping from the theme like every other post.
 *
 * Settings > GEO Index: publish automatically (default), save as draft, or
 * off, plus a button that makes or refreshes the report of the latest run.
 *
 * @package BoomingVenture
 */

defined( 'ABSPATH' ) || exit;

function bv_gi_report_opts(): array {
	$o = (array) get_option( 'bv_gi_report_opts', [] );
	return [ 'mode' => in_array( $o['mode'] ?? '', [ 'publish', 'draft', 'off' ], true ) ? $o['mode'] : 'publish' ];
}

/** Where the latest run stands: month, and whether website and AI numbers are complete. */
function bv_gi_report_state(): array {
	$meta    = (array) get_option( 'bv_gi_meta', [] );
	$started = (int) ( $meta['started'] ?? 0 );
	$web     = $started && (int) ( $meta['completed'] ?? 0 ) >= $started && ! get_option( 'bv_gi_queue' ) && empty( $meta['stopped'] );
	$ai_on   = function_exists( 'bv_gi_ai_active' ) && (bool) bv_gi_ai_active();
	$air     = (array) get_option( 'bv_gi_ai_results', [] );
	$ai      = $ai_on && (int) ( $air['completed'] ?? 0 ) >= $started && ! get_option( 'bv_gi_ai_queue' );
	/* AI answers that never finish (no credit, all caps hit) do not block the report for more than 5 days. */
	$late    = $started && time() - $started > 5 * DAY_IN_SECONDS;
	return [
		'month'   => $started ? gmdate( 'Y-m', $started ) : '',
		'source'  => (string) ( $meta['source'] ?? '' ),
		'started' => $started,
		'web'     => $web,
		'ai_on'   => $ai_on,
		'ai'      => $ai,
		'ready'   => $web && ( ! $ai_on || $ai || $late ),
	];
}

/** Everything the report needs, computed from the ranking. */
function bv_gi_report_data( array $st ): array {
	$air     = (array) get_option( 'bv_gi_ai_results', [] );
	$use_ai  = $st['ai'] || ( ! $st['ai_on'] && ! empty( $air['rows'] ) && (int) ( $air['completed'] ?? 0 ) >= $st['started'] );
	$engines = $use_ai ? array_values( array_intersect( array_keys( bv_gi_ai_engines() ), (array) ( $air['engines'] ?? [] ) ) ) : [];
	$sectors = (array) ( bv_gi_companies()['sectors'] ?? [] );
	$d       = [ 'engines' => $engines, 'lists' => [], 'risers' => [] ];
	foreach ( [ 'nl', 'intl' ] as $list ) {
		$rows = bv_gi_ranked( $list );
		$ok   = array_values( array_filter( $rows, static fn( $r ) => 'ok' === ( $r['status'] ?? 'ok' ) ) );
		$n    = max( 1, count( $ok ) );
		$pct  = static fn( callable $f ) => (int) round( 100 * count( array_filter( $ok, $f ) ) / $n );
		$yes  = static fn( string $k ) => static fn( $r ) => true === ( $r['checks'][ $k ] ?? null );
		$with_ai = $engines ? array_values( array_filter( $rows, static fn( $r ) => null !== $r['ai'] ) ) : [];
		$na      = max( 1, count( $with_ai ) );
		$eng     = [];
		foreach ( $engines as $e ) {
			$eng[ $e ] = (int) round( 100 * count( array_filter( $with_ai, static fn( $r ) => ! empty( $r['ai_eng'][ $e ] ) ) ) / $na );
		}
		$avg = static fn( array $rs, string $k ) => $rs ? (int) round( array_sum( array_column( $rs, $k ) ) / count( $rs ) ) : 0;
		foreach ( $rows as &$r ) { // sector names in both languages: [ Dutch, English ]
			$r['sector_nl'] = $sectors[ $r['sector'] ][0] ?? $r['sector'];
			$r['sector_en'] = $sectors[ $r['sector'] ][1] ?? $r['sector'];
		}
		unset( $r );
		$d['lists'][ $list ] = [
			'measured' => count( $ok ),
			'ai_rows'  => count( $with_ai ),
			'top'      => array_slice( $rows, 0, 10 ),
			'bysector' => array_reduce( $rows, static function ( $acc, $r ) { // rows are ranked, so the first per sector leads it
				if ( ! isset( $acc[ $r['sector'] ] ) ) {
					$acc[ $r['sector'] ] = $r;
				}
				return $acc;
			}, [] ),
			'leaders'  => array_values( array_filter( $rows, static fn( $r ) => 1 === (int) $r['rank'] ) ),
			'avg'      => $avg( $rows, 'score' ),
			'named'    => $with_ai ? (int) round( 100 * count( array_filter( $with_ai, static fn( $r ) => (bool) array_filter( (array) $r['ai_eng'] ) ) ) / $na ) : null,
			'eng'      => $eng,
			'checks'   => [
				'oai'   => $pct( $yes( 'bot_OAI-SearchBot' ) ),
				'claude' => $pct( $yes( 'bot_Claude-SearchBot' ) ),
				'pplx'  => $pct( $yes( 'bot_PerplexityBot' ) ),
				'html'  => $pct( $yes( 'html' ) ),
				'org'   => $pct( $yes( 'schema_org' ) ),
				'wiki'  => $pct( $yes( 'wikipedia' ) ),
				'fresh' => $pct( $yes( 'fresh' ) ),
				'llms'  => $pct( $yes( 'llms' ) ),
			],
		];
		foreach ( $rows as $r ) {
			if ( null !== $r['change'] && $r['change'] > 0 ) {
				$d['risers'][] = $r + [ 'list' => $list ];
			}
		}
	}
	usort( $d['risers'], static fn( $a, $b ) => [ $b['change'], $b['score'], $a['name'] ] <=> [ $a['change'], $a['score'], $b['name'] ] );
	$d['risers'] = array_slice( $d['risers'], 0, 5 );
	$all = count( $d['lists']['nl']['top'] ) ? $d['lists']['nl']['measured'] + $d['lists']['intl']['measured'] : 0;
	$d['all'] = [
		'measured' => $all,
		'oai'      => $all ? (int) round( ( $d['lists']['nl']['checks']['oai'] * $d['lists']['nl']['measured'] + $d['lists']['intl']['checks']['oai'] * $d['lists']['intl']['measured'] ) / $all ) : 0,
		'html'     => $all ? (int) round( ( $d['lists']['nl']['checks']['html'] * $d['lists']['nl']['measured'] + $d['lists']['intl']['checks']['html'] * $d['lists']['intl']['measured'] ) / $all ) : 0,
	];
	$ai_rows   = $d['lists']['nl']['ai_rows'] + $d['lists']['intl']['ai_rows'];
	$d['all']['named'] = $ai_rows ? (int) round( ( (int) $d['lists']['nl']['named'] * $d['lists']['nl']['ai_rows'] + (int) $d['lists']['intl']['named'] * $d['lists']['intl']['ai_rows'] ) / $ai_rows ) : null;
	/* Publish only numbers that look complete: 80 of 100 companies per list, and AI data when AI is on. */
	$d['complete'] = $d['lists']['nl']['measured'] >= 80 && $d['lists']['intl']['measured'] >= 80
		&& ( ! $st['ai_on'] || ( $engines && $d['lists']['nl']['ai_rows'] >= 80 && $d['lists']['intl']['ai_rows'] >= 80 ) );
	return $d;
}

/** Title, slug, excerpt and block content of the report in one language. */
function bv_gi_report_build( string $lang, array $d, int $started ): array {
	$nl     = 'nl' === $lang;
	$months = $nl
		? [ 'januari', 'februari', 'maart', 'april', 'mei', 'juni', 'juli', 'augustus', 'september', 'oktober', 'november', 'december' ]
		: [ 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' ];
	$days   = $nl ? [ 'zondag', 'maandag', 'dinsdag', 'woensdag', 'donderdag', 'vrijdag', 'zaterdag' ] : [ 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' ];
	$m      = $months[ (int) gmdate( 'n', $started ) - 1 ];
	$y      = gmdate( 'Y', $started );
	$when   = $m . ' ' . $y;
	$next   = strtotime( 'first monday of next month', strtotime( gmdate( 'Y-m-01', $started ) . ' 12:00 UTC' ) );
	$next_s = $days[ (int) gmdate( 'w', $next ) ] . ' ' . gmdate( 'j', $next ) . ' ' . $months[ (int) gmdate( 'n', $next ) - 1 ] . ' ' . gmdate( 'Y', $next );
	$and    = $nl ? ' en ' : ' and ';
	$join   = static fn( array $a ) => count( $a ) > 1 ? implode( ', ', array_slice( $a, 0, -1 ) ) . $and . end( $a ) : implode( '', $a );
	$page   = home_url( $nl ? '/nl/b2b-geo-index/' : '/b2b-geo-index/' );
	$link   = static fn( string $text, string $url ) => '<a href="' . esc_url( $url ) . '"' . ( 0 === strpos( $url, 'http' ) && 0 !== strpos( $url, home_url() ) ? ' target="_blank" rel="noopener"' : '' ) . '>' . $text . '</a>';
	$src    = $link( 'B2B GEO Index, ' . $when, $page );
	$e      = static fn( $s ) => esc_html( (string) $s );
	$p      = static fn( string $t ) => "<!-- wp:paragraph -->\n<p>{$t}</p>\n<!-- /wp:paragraph -->";
	$h2     = static fn( string $t ) => "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">{$t}</h2>\n<!-- /wp:heading -->";
	$h3     = static fn( string $t ) => "<!-- wp:heading {\"level\":3} -->\n<h3 class=\"wp-block-heading\">{$t}</h3>\n<!-- /wp:heading -->";
	$cap    = static fn( string $t ) => "<!-- wp:paragraph {\"className\":\"bv-capsule\",\"style\":{\"typography\":{\"fontWeight\":\"500\"}},\"fontSize\":\"lg\"} -->\n<p class=\"bv-capsule has-lg-font-size\" style=\"font-weight:500\">{$t}</p>\n<!-- /wp:paragraph -->";
	$items  = static fn( array $li ) => implode( '', array_map( static fn( $i ) => "<!-- wp:list-item -->\n<li>{$i}</li>\n<!-- /wp:list-item -->\n", $li ) );
	$ol     = static fn( array $li ) => "<!-- wp:list {\"ordered\":true} -->\n<ol class=\"wp-block-list\">" . $items( $li ) . "</ol>\n<!-- /wp:list -->";
	$ul     = static fn( array $li ) => "<!-- wp:list -->\n<ul class=\"wp-block-list\">" . $items( $li ) . "</ul>\n<!-- /wp:list -->";
	$table  = static function ( array $cols, array $rows, string $caption ): string {
		$head = implode( '', array_map( static fn( $c ) => "<th>{$c}</th>", $cols ) );
		$body = implode( '', array_map( static fn( $r ) => '<tr>' . implode( '', array_map( static fn( $c ) => "<td>{$c}</td>", $r ) ) . '</tr>', $rows ) );
		return "<!-- wp:table {\"className\":\"bv-compare\"} -->\n<figure class=\"wp-block-table bv-compare\"><table><thead><tr>{$head}</tr></thead><tbody>{$body}</tbody></table><figcaption class=\"wp-element-caption\">{$caption}</figcaption></figure>\n<!-- /wp:table -->";
	};
	$box = static fn( string $title, string $inner ) => '<!-- wp:group {"backgroundColor":"booming-50","style":{"border":{"radius":"0.75rem"},"spacing":{"padding":{"top":"1.25rem","right":"1.25rem","bottom":"1.25rem","left":"1.25rem"}}},"layout":{"type":"constrained"}} -->' . "\n"
		. '<div class="wp-block-group has-booming-50-background-color has-background" style="border-radius:0.75rem;padding:1.25rem">'
		. '<!-- wp:paragraph {"fontSize":"sm","style":{"typography":{"fontWeight":"600","textTransform":"uppercase","letterSpacing":"0.08em"}}} -->' . "\n"
		. '<p class="has-sm-font-size" style="font-weight:600;text-transform:uppercase;letter-spacing:0.08em">' . $title . "</p>\n<!-- /wp:paragraph -->\n"
		. $inner . "</div>\n<!-- /wp:group -->";

	$L      = $d['lists'];
	$eng    = array_map( static fn( $k ) => bv_gi_ai_engines()[ $k ]['label'], $d['engines'] );
	$has_ai = (bool) $eng;
	$leader = static fn( array $l ) => $join( array_map( static fn( $r ) => '<strong>' . esc_html( $r['name'] ) . '</strong>', $l['leaders'] ) );
	$lead_s = static fn( array $l ) => (int) ( $l['leaders'][0]['score'] ?? 0 );
	$chg    = static fn( $c ) => null === $c ? ( $nl ? 'nieuw' : 'new' ) : ( $c > 0 ? '+' . (int) $c : (string) (int) $c );

	/* Ranking table for one list. */
	$rank_table = static function ( string $list ) use ( $L, $has_ai, $nl, $e, $chg, $table, $when ): string {
		$cols = $nl ? [ 'Bedrijf', 'Sector', 'GEO-score' ] : [ 'Company', 'Sector', 'GEO score' ];
		if ( $has_ai ) {
			$cols[] = $nl ? 'AI-score' : 'AI score';
		}
		$cols[] = $nl ? 'Website' : 'Website';
		$cols[] = $nl ? 'Verschil' : 'Change';
		$rows = [];
		foreach ( $L[ $list ]['top'] as $r ) {
			$row = [ '<strong>' . (int) $r['rank'] . '. ' . $e( $r['name'] ) . '</strong>', $e( $nl ? $r['sector_nl'] : $r['sector_en'] ), (int) $r['score'] ];
			if ( $has_ai ) {
				$row[] = null === $r['ai'] ? '-' : (int) $r['ai'];
			}
			$row[] = (int) $r['ready'];
			$row[] = $chg( $r['change'] );
			$rows[] = $row;
		}
		$cap = 'nl' === $list
			? ( $nl ? 'Top 10 van 100 Nederlandse B2B-bedrijven. Verschil met vorige maand. Bron: B2B GEO Index, ' . $when . '.' : 'Top 10 of 100 Dutch B2B companies. Change against last month. Source: B2B GEO Index, ' . $when . '.' )
			: ( $nl ? 'Top 10 van 100 internationale B2B-bedrijven. Verschil met vorige maand. Bron: B2B GEO Index, ' . $when . '.' : 'Top 10 of 100 international B2B companies. Change against last month. Source: B2B GEO Index, ' . $when . '.' );
		return $table( $cols, $rows, $cap );
	};

	$c = [];
	/* Key takeaways. */
	$take = [
		$nl
			? sprintf( '%s staat bovenaan de Nederlandse lijst met een GEO-score van %d. %s leidt de internationale lijst met %d.', $leader( $L['nl'] ), $lead_s( $L['nl'] ), $leader( $L['intl'] ), $lead_s( $L['intl'] ) )
			: sprintf( '%s leads the Dutch list with a GEO score of %d. %s leads the international list with %d.', $leader( $L['nl'] ), $lead_s( $L['nl'] ), $leader( $L['intl'] ), $lead_s( $L['intl'] ) ),
		$nl
			? sprintf( '%d%% van de gemeten bedrijven laat OAI-SearchBot toe, de crawler die bepaalt of een site in ChatGPT search kan verschijnen.', $d['all']['oai'] )
			: sprintf( '%d%% of the measured companies allow OAI-SearchBot, the crawler that decides whether a site can appear in ChatGPT search.', $d['all']['oai'] ),
		$has_ai && null !== $d['all']['named']
			? ( $nl ? sprintf( '%d%% werd door minstens één AI-assistent genoemd (%s).', $d['all']['named'], $join( $eng ) ) : sprintf( '%d%% were named by at least one AI assistant (%s).', $d['all']['named'], $join( $eng ) ) )
			: ( $nl ? sprintf( '%d%% heeft genoeg tekst op de homepage die AI-crawlers zonder JavaScript kunnen lezen.', $d['all']['html'] ) : sprintf( '%d%% have enough homepage text that AI crawlers can read without JavaScript.', $d['all']['html'] ) ),
	];
	$c[] = $box( $nl ? 'Belangrijkste punten' : 'Key takeaways', $ul( $take ) );

	/* Intro with the numbers and their sources (the first 30%). */
	$c[] = $p( $nl
		? sprintf( 'Elke maand meet de %s 200 B2B-bedrijven: 100 Nederlandse en 100 internationale, vijf in elk van dezelfde 20 sectoren. In %s was de gemiddelde GEO-score %d voor de Nederlandse lijst en %d voor de internationale lijst.', $link( 'B2B GEO Index', $page ), $when, $L['nl']['avg'], $L['intl']['avg'] )
		: sprintf( 'Every month the %s measures 200 B2B companies: 100 Dutch and 100 international, five in each of the same 20 sectors. In %s the average GEO score was %d for the Dutch list and %d for the international list.', $link( 'B2B GEO Index', $page ), $when, $L['nl']['avg'], $L['intl']['avg'] ) );
	$c[] = $p( $nl
		? sprintf( 'Aan de websitekant laat %d%% van de Nederlandse en %d%% van de internationale bedrijven OAI-SearchBot toe (%s). %d%% toont minstens 150 woorden tekst op de homepage zonder JavaScript (%s). Dat telt: in de %s renderde geen van de grote AI-crawlers JavaScript. En volgens %s gebruikt 94%% van de zakelijke kopers AI tijdens het aankoopproces.', $L['nl']['checks']['oai'], $L['intl']['checks']['oai'], $src, $d['all']['html'], $src, $link( 'analyse van Vercel', 'https://vercel.com/blog/the-rise-of-the-ai-crawler' ), $link( 'Forrester', 'https://www.forrester.com/blogs/b2b_buyers_make_zero_click_buying_number_one/' ) )
		: sprintf( 'On the website side, %d%% of the Dutch and %d%% of the international companies allow OAI-SearchBot (%s). %d%% show at least 150 words of homepage text without JavaScript (%s). That matters: none of the major AI crawlers rendered JavaScript in %s. And %s reports that 94%% of business buyers use AI in their buying process.', $L['nl']['checks']['oai'], $L['intl']['checks']['oai'], $src, $d['all']['html'], $src, $link( "Vercel's analysis", 'https://vercel.com/blog/the-rise-of-the-ai-crawler' ), $link( 'Forrester', 'https://www.forrester.com/blogs/b2b_buyers_make_zero_click_buying_number_one/' ) ) );

	/* Definition. */
	$c[] = $h2( $nl ? 'Wat is de B2B GEO Index?' : 'What is the B2B GEO Index?' );
	$c[] = $cap( $nl
		? 'De B2B GEO Index is een maandelijkse ranglijst van 200 B2B-bedrijven op hoe vaak AI-assistenten ze noemen en hoe klaar hun websites zijn.'
		: 'The B2B GEO Index is a monthly ranking of 200 B2B companies by how often AI assistants name them and how ready their websites are.' );
	$c[] = $p( $has_ai
		? ( $nl ? sprintf( 'De helft van de GEO-score komt uit hoe vaak %s een bedrijf noemen in antwoorden op koopvragen, de helft uit zeven websitechecks. De %s legt elke check en de punten uit.', $join( $eng ), $link( 'methode', $page . '#methode' ) ) : sprintf( 'Half of the GEO score comes from how often %s name a company in answers to buyer questions, half from seven website checks. The %s explains every check and its points.', $join( $eng ), $link( 'methodology', $page . '#methodology' ) ) )
		: ( $nl ? sprintf( 'Deze maand is de GEO-score gelijk aan de websitescore: zeven checks op de eigen website van elk bedrijf, van 0 tot 100. De %s legt elke check uit.', $link( 'methode', $page . '#methode' ) ) : sprintf( 'This month the GEO score is the website score: seven checks on each company\'s own website, from 0 to 100. The %s explains every check.', $link( 'methodology', $page . '#methodology' ) ) ) );

	/* The two top 10s. */
	$c[] = $h2( ( $nl ? 'Top 10 Nederland: ' : 'Top 10 Netherlands: ' ) . $when );
	$c[] = $rank_table( 'nl' );
	$c[] = $h2( ( $nl ? 'Top 10 internationaal: ' : 'Top 10 international: ' ) . $when );
	$c[] = $rank_table( 'intl' );

	/* Sector leaders: both lists use the same 20 sectors, so they sit side by side. */
	$sectors = (array) ( bv_gi_companies()['sectors'] ?? [] );
	$cell    = static fn( $r ) => $r ? '<strong>' . esc_html( $r['name'] ) . '</strong> (' . (int) $r['score'] . ')' : '-';
	$rows    = [];
	foreach ( $sectors as $key => $sec ) {
		if ( empty( $L['nl']['bysector'][ $key ] ) && empty( $L['intl']['bysector'][ $key ] ) ) {
			continue;
		}
		$rows[] = [ esc_html( $nl ? $sec[0] : $sec[1] ), $cell( $L['nl']['bysector'][ $key ] ?? null ), $cell( $L['intl']['bysector'][ $key ] ?? null ) ];
	}
	if ( $rows ) {
		$c[] = $h2( $nl ? 'Koplopers per sector' : 'Sector leaders' );
		$c[] = $p( $nl ? 'Beide lijsten gebruiken dezelfde 20 sectoren, gebaseerd op de SBI 2025 van het CBS en de Europese NACE-indeling. Per sector het bedrijf met de hoogste GEO-score.' : 'Both lists use the same 20 sectors, based on the Dutch SBI 2025 and the European NACE classification. For each sector, the company with the highest GEO score.' );
		$c[] = $table( $nl ? [ 'Sector', 'Nederland', 'Internationaal' ] : [ 'Sector', 'Netherlands', 'International' ], $rows,
			$nl ? 'GEO-score tussen haakjes. Bron: B2B GEO Index, ' . $when . '.' : 'GEO score in brackets. Source: B2B GEO Index, ' . $when . '.' );
	}

	/* Risers. */
	if ( $d['risers'] ) {
		$c[] = $h2( $nl ? 'Grootste stijgers deze maand' : 'Biggest risers this month' );
		$c[] = $p( $nl ? 'De bedrijven waarvan de GEO-score sinds vorige maand het meest steeg:' : 'The companies whose GEO score rose most since last month:' );
		$c[] = $ol( array_map( static fn( $r ) => '<strong>' . esc_html( $r['name'] ) . '</strong> (' . ( 'nl' === $r['list'] ? ( $nl ? 'Nederland' : 'Netherlands' ) : ( $nl ? 'internationaal' : 'international' ) ) . '): +' . (int) $r['change'] . ( $nl ? ' naar ' : ' to ' ) . (int) $r['score'], $d['risers'] ) );
	}

	/* Assistants compared. */
	if ( $has_ai ) {
		$c[] = $h2( $nl ? 'AI-assistenten vergeleken' : 'AI assistants compared' );
		$c[] = $p( $nl ? 'Het aandeel bedrijven dat elke assistent deze maand minstens één keer noemde, per lijst.' : 'The share of companies each assistant named at least once this month, per list.' );
		$rows = [];
		foreach ( $d['engines'] as $k ) {
			$rows[] = [ '<strong>' . esc_html( bv_gi_engines_label( $k ) ) . '</strong>', (int) ( $L['nl']['eng'][ $k ] ?? 0 ) . '%', (int) ( $L['intl']['eng'][ $k ] ?? 0 ) . '%' ];
		}
		$c[] = $table( $nl ? [ 'Assistent', 'Nederland', 'Internationaal' ] : [ 'Assistant', 'Netherlands', 'International' ], $rows,
			$nl ? 'Eén koopvraag per categorie, met de eigen zoekfunctie van elke assistent aan. Bron: B2B GEO Index, ' . $when . '.' : 'One buyer question per category, with each assistant\'s own web search on. Source: B2B GEO Index, ' . $when . '.' );
	}

	/* Website checks. */
	$c[] = $h2( $nl ? 'Websites in cijfers' : 'Websites in numbers' );
	$labels = $nl
		? [ 'oai' => 'Laat OAI-SearchBot toe', 'claude' => 'Laat Claude-SearchBot toe', 'pplx' => 'Laat PerplexityBot toe', 'html' => 'Minstens 150 woorden zonder JavaScript', 'org' => 'Organization-schema', 'wiki' => 'Wikipedia-artikel', 'fresh' => 'Sitemap bijgewerkt in de laatste 90 dagen', 'llms' => 'llms.txt' ]
		: [ 'oai' => 'Allows OAI-SearchBot', 'claude' => 'Allows Claude-SearchBot', 'pplx' => 'Allows PerplexityBot', 'html' => 'At least 150 words without JavaScript', 'org' => 'Organization schema', 'wiki' => 'Wikipedia article', 'fresh' => 'Sitemap updated in the last 90 days', 'llms' => 'llms.txt' ];
	$rows = [];
	foreach ( $labels as $k => $label ) {
		$rows[] = [ '<strong>' . $label . '</strong>', $L['nl']['checks'][ $k ] . '%', $L['intl']['checks'][ $k ] . '%' ];
	}
	$c[] = $table( $nl ? [ 'Check', 'Nederland', 'Internationaal' ] : [ 'Check', 'Netherlands', 'International' ], $rows,
		$nl ? sprintf( 'Aandeel van de gemeten bedrijven (%d Nederlandse, %d internationale). Bron: B2B GEO Index, %s.', $L['nl']['measured'], $L['intl']['measured'], $when ) : sprintf( 'Share of the measured companies (%d Dutch, %d international). Source: B2B GEO Index, %s.', $L['nl']['measured'], $L['intl']['measured'], $when ) );

	/* What to do. */
	$c[] = $h2( $nl ? 'Zo verhoog je je score' : 'How to raise your score' );
	$c[] = $ol( $nl
		? [ 'Laat OAI-SearchBot, Claude-SearchBot en PerplexityBot toe in robots.txt, voor de homepage en onderliggende pagina\'s.', 'Zet je belangrijkste tekst in de HTML, zodat crawlers die geen JavaScript uitvoeren hem kunnen lezen.', 'Voeg Organization-schema toe met sameAs-links naar je officiële profielen.', 'Houd de lastmod-datums in je sitemap actueel als je pagina\'s bijwerkt.', 'Check je eigen domein op de ' . $link( 'B2B GEO Index', $page ) . ' en los de checks op die falen.' ]
		: [ 'Allow OAI-SearchBot, Claude-SearchBot and PerplexityBot in robots.txt, for the homepage and inner pages.', 'Put your main text in the HTML, so crawlers that do not run JavaScript can read it.', 'Add Organization schema with sameAs links to your official profiles.', 'Keep the lastmod dates in your sitemap current when you update pages.', 'Check your own domain on the ' . $link( 'B2B GEO Index', $page ) . ' page and fix the checks that fail.' ] );

	/* FAQ: H2 + H3/paragraph pairs, picked up for the FAQPage schema. */
	$faq = $nl
		? [
			[ 'Wanneer verschijnt de volgende B2B GEO Index?', sprintf( 'Op de eerste maandag van de volgende maand: %s. De ranglijst op de indexpagina wordt bijgewerkt zodra die meting klaar is.', $next_s ) ],
			[ 'Hoe wordt de GEO-score berekend?', $has_ai ? sprintf( 'De helft komt uit hoe vaak %s het bedrijf noemen in antwoorden op koopvragen, de helft uit zeven websitechecks. Beide delen lopen van 0 tot 100.', $join( $eng ) ) : 'Deze maand is het de websitescore: zeven checks op de eigen website van het bedrijf, van 0 tot 100. Als de AI-meting draait, komt de helft van de score uit hoe vaak AI-assistenten het bedrijf noemen.' ],
			[ 'Kan ik mijn eigen bedrijf checken?', 'Ja. Vul je domein in bij de check op de pagina van de B2B GEO Index. Je krijgt dezelfde websitescore als de bedrijven in de ranglijst.' ],
			[ 'Mag ik deze cijfers gebruiken?', 'Ja. Download de volledige ranglijst als CSV op de pagina van de B2B GEO Index en citeer de cijfers met een link naar de index als bron.' ],
		]
		: [
			[ 'When is the next B2B GEO Index?', sprintf( 'On the first Monday of next month: %s. The ranking on the index page updates as soon as that measurement is done.', $next_s ) ],
			[ 'How is the GEO score calculated?', $has_ai ? sprintf( 'Half comes from how often %s name the company in answers to buyer questions, half from seven website checks. Both parts run from 0 to 100.', $join( $eng ) ) : 'This month it is the website score: seven checks on the company\'s own website, from 0 to 100. When the AI measurement runs, half of the score comes from how often AI assistants name the company.' ],
			[ 'Can I check my own company?', 'Yes. Enter your domain in the check on the B2B GEO Index page. You get the same website score as the companies in the ranking.' ],
			[ 'Can I use these numbers?', 'Yes. Download the full ranking as CSV on the B2B GEO Index page and quote the numbers with a link to the index as the source.' ],
		];
	$c[] = $h2( $nl ? 'Veelgestelde vragen' : 'Frequently asked questions' );
	foreach ( $faq as [ $q, $a ] ) {
		$c[] = $h3( $q );
		$c[] = $p( $a );
	}

	/* CTA, same box as the other posts. */
	$c[] = '<!-- wp:group {"backgroundColor":"booming-50","style":{"border":{"radius":"0.75rem"},"spacing":{"padding":{"top":"1.25rem","right":"1.25rem","bottom":"1.25rem","left":"1.25rem"}}},"layout":{"type":"constrained"}} -->' . "\n"
		. '<div class="wp-block-group has-booming-50-background-color has-background" style="border-radius:0.75rem;padding:1.25rem">'
		. $p( $nl
			? 'Wil je vaker in AI-antwoorden verschijnen? ' . $link( 'Vraag een gratis Boardroom Quickscan van 15 minuten aan', home_url( '/nl/boardroom-quickscan/' ) ) . '. Of bekijk de volledige ' . $link( 'B2B GEO Index', $page ) . '.'
			: 'Want to show up in AI answers more often? ' . $link( 'Request a free 15-minute Boardroom Quickscan', home_url( '/boardroom-quickscan/' ) ) . '. Or see the full ' . $link( 'B2B GEO Index', $page ) . '.' )
		. "</div>\n<!-- /wp:group -->";

	$title = $nl ? sprintf( 'B2B GEO Index %s: top 10 Nederland en internationaal', $when ) : sprintf( 'B2B GEO Index %s: top 10 Netherlands and international', $when );
	$desc  = $nl
		? sprintf( 'B2B GEO Index %s: de top 10 van 200 B2B-bedrijven in AI-zoekmachines, de grootste stijgers en hoe klaar hun websites zijn.', $when )
		: sprintf( 'B2B GEO Index %s: the top 10 of 200 B2B companies in AI search, the biggest risers and how ready their websites are.', $when );
	return [
		'title'   => $title,
		'slug'    => sanitize_title( 'b2b-geo-index-' . $m . '-' . $y . ( $nl ? '-resultaten' : '-results' ) ),
		'excerpt' => $desc,
		'content' => implode( "\n", $c ),
	];
}

function bv_gi_engines_label( string $k ): string {
	return (string) ( bv_gi_ai_engines()[ $k ]['label'] ?? $k );
}

/**
 * Make (or, when forced, refresh) the report of the latest run.
 * Returns [ 'state' => created|updated|exists|waiting|off|early, 'en' => id, 'nl' => id, 'status' => publish|draft ].
 */
function bv_gi_report_maybe( bool $force = false ): array {
	$st = bv_gi_report_state();
	if ( ! $st['month'] || ! $st['web'] || ( ! $force && ! $st['ready'] ) ) {
		return [ 'state' => 'waiting' ];
	}
	$opts = bv_gi_report_opts();
	if ( ! $force && 'off' === $opts['mode'] ) {
		return [ 'state' => 'off' ];
	}
	if ( ! $force && 'monthly' !== $st['source'] ) {
		return [ 'state' => 'manual' ]; // automatic posts only follow the first-Monday scan
	}
	$done = (array) get_option( 'bv_gi_report', [] );
	$have = (array) ( $done[ $st['month'] ] ?? [] );
	$en   = isset( $have['en'] ) ? get_post( (int) $have['en'] ) : null;
	$nlp  = isset( $have['nl'] ) ? get_post( (int) $have['nl'] ) : null;
	$en   = $en && 'trash' !== $en->post_status ? $en : null;
	$nlp  = $nlp && 'trash' !== $nlp->post_status ? $nlp : null;
	if ( ! $force && ( $en || $nlp ) ) {
		return [ 'state' => 'exists' ] + $have;
	}

	$d      = bv_gi_report_data( $st );
	$status = 'publish' === $opts['mode'] && $d['complete'] ? 'publish' : 'draft';
	$cat    = get_category_by_slug( 'ai-search' );
	$geo    = get_page_by_path( 'b2b-geo-index' );
	$author = $geo ? (int) $geo->post_author : 0;
	if ( ! $author ) {
		$admins = get_users( [ 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ] );
		$author = (int) ( $admins[0] ?? 1 );
	}
	$thumb = $geo ? (int) get_post_thumbnail_id( $geo ) : 0;
	$ids   = [];
	kses_remove_filters(); // content is built here from measured numbers, all names escaped
	foreach ( [ 'en' => $en, 'nl' => $nlp ] as $lang => $existing ) {
		$b    = bv_gi_report_build( $lang, $d, $st['started'] );
		$args = [
			'post_type'    => 'post',
			'post_title'   => $b['title'],
			'post_content' => $b['content'],
			'post_excerpt' => $b['excerpt'],
			'meta_input'   => [ '_yoast_wpseo_title' => $b['title'], '_yoast_wpseo_metadesc' => $b['excerpt'], '_bv_gi_report' => $st['month'] ],
		];
		if ( $existing ) {
			$args['ID'] = $existing->ID; // refresh numbers, keep slug, date and status
			$id = wp_update_post( wp_slash( $args ), true );
		} else {
			$args += [ 'post_name' => $b['slug'], 'post_status' => $status, 'post_author' => $author, 'comment_status' => 'closed', 'ping_status' => 'closed', 'post_category' => $cat ? [ (int) $cat->term_id ] : [] ];
			if ( 'nl' === $lang ) {
				$args['meta_input'] += [ '_bv_lang' => 'nl', '_bv_translation' => (int) ( $ids['en'] ?? 0 ) ];
			}
			$id = wp_insert_post( wp_slash( $args ), true );
		}
		if ( is_wp_error( $id ) || ! $id ) {
			continue;
		}
		$ids[ $lang ] = (int) $id;
		if ( $thumb && ! has_post_thumbnail( $id ) ) {
			set_post_thumbnail( $id, $thumb );
		}
	}
	kses_init_filters();
	$done[ $st['month'] ] = $ids;
	update_option( 'bv_gi_report', array_slice( $done, -24, null, true ), false );
	return [ 'state' => ( $en || $nlp ) ? 'updated' : 'created', 'status' => ( $en || $nlp ) ? get_post_status( $ids['en'] ?? 0 ) : $status ] + $ids;
}

/* When a measurement finishes, check two minutes later; the daily check is the safety net. */
add_action( 'bv_gi_complete', static function () {
	if ( ! wp_next_scheduled( 'bv_gi_report' ) ) {
		wp_schedule_single_event( time() + 2 * MINUTE_IN_SECONDS, 'bv_gi_report' );
	}
} );
add_action( 'bv_gi_report', static fn() => bv_gi_report_maybe() );
add_action( 'bv_gi_daily', static fn() => bv_gi_report_maybe(), 20 );

/* ------------------------------------------------------------------ settings */

add_action( 'bv_gi_settings_rows', static function () {
	$o = bv_gi_report_opts();
	$choices = [ 'publish' => 'Automatisch publiceren', 'draft' => 'Als concept opslaan', 'off' => 'Uit' ];
	?>
	<tr>
		<th scope="row">Maandbericht</th>
		<td>
			<?php foreach ( $choices as $k => $label ) : ?>
				<label style="margin-right:16px"><input type="radio" name="report_mode" value="<?php echo esc_attr( $k ); ?>" <?php checked( $o['mode'], $k ); ?>> <?php echo esc_html( $label ); ?></label>
			<?php endforeach; ?>
			<p class="description">Na de automatische scan op de eerste maandag van de maand schrijft de site een blogbericht in het Engels en het Nederlands met de top 10, de koplopers per sector, de stijgers en de websitescores. Na een handmatige scan maak je het bericht met de knop hieronder. De tekst komt uit de meetdata. Is een lijst niet goed gemeten (minder dan 80 van de 100 bedrijven), dan wordt het een concept.</p>
		</td>
	</tr>
	<?php
} );

add_action( 'bv_gi_settings_save', static function () {
	$mode = sanitize_key( wp_unslash( $_POST['report_mode'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification -- checked by the caller
	if ( in_array( $mode, [ 'publish', 'draft', 'off' ], true ) ) {
		update_option( 'bv_gi_report_opts', [ 'mode' => $mode ], false );
	}
} );

add_action( 'bv_gi_settings_status', static function () {
	$done = (array) get_option( 'bv_gi_report', [] );
	$st   = bv_gi_report_state();
	end( $done );
	$month = key( $done );
	$ids   = $month ? (array) $done[ $month ] : [];
	$out   = [];
	foreach ( [ 'en' => 'EN', 'nl' => 'NL' ] as $lang => $label ) {
		if ( ! empty( $ids[ $lang ] ) && get_post( $ids[ $lang ] ) ) {
			$out[] = '<a href="' . esc_url( (string) get_edit_post_link( $ids[ $lang ] ) ) . '">' . esc_html( $label . ': ' . get_the_title( $ids[ $lang ] ) ) . '</a> (' . esc_html( get_post_status( $ids[ $lang ] ) ) . ')';
		}
	}
	$wait = $st['web'] ? ( $st['ai_on'] && ! $st['ai'] ? 'wacht op de AI-meting' : 'cijfers compleet' ) : 'wacht op de websitemeting';
	?>
	<tr><td>Maandbericht</td><td><?php echo $out ? wp_kses_post( implode( '<br>', $out ) ) : 'nog geen'; ?><br><span class="description">Huidige meting: <?php echo esc_html( $wait ); ?></span></td></tr>
	<?php
} );

add_action( 'bv_gi_settings_after', static function () {
	?>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:16px">
		<input type="hidden" name="action" value="bv_gi_report_now">
		<?php wp_nonce_field( 'bv_gi_report_now' ); ?>
		<?php submit_button( 'Maak het maandbericht nu', 'secondary', 'submit', false ); ?>
		<p class="description">Maakt het bericht van de laatste meting, of werkt de cijfers bij als het al bestaat. Status en publicatiedatum blijven dan hetzelfde.</p>
	</form>
	<?php
} );

add_action( 'admin_post_bv_gi_report_now', static function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'bv_gi_report_now' ) ) {
		wp_die( 'Not allowed.' );
	}
	$r = bv_gi_report_maybe( true );
	wp_safe_redirect( admin_url( 'options-general.php?page=bv-geo-index&report=' . rawurlencode( $r['state'] ) ) );
	exit;
} );

add_action( 'admin_notices', static function () {
	if ( ! isset( $_GET['report'], $_GET['page'] ) || 'bv-geo-index' !== $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification
		return;
	}
	$msg = [
		'created' => 'Het maandbericht is gemaakt in het Engels en het Nederlands.',
		'updated' => 'De cijfers in het maandbericht zijn bijgewerkt.',
		'waiting' => 'Nog geen bericht: de websitemeting is nog niet klaar.',
	][ sanitize_key( wp_unslash( $_GET['report'] ) ) ] ?? ''; // phpcs:ignore WordPress.Security.NonceVerification
	if ( $msg ) {
		echo '<div class="notice notice-info"><p>' . esc_html( $msg ) . '</p></div>';
	}
} );
