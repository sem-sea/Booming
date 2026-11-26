<?php
/**
 * Server render for the B2B GEO Index block.
 * The full ranking is in the HTML (readable for crawlers without
 * JavaScript); view.js adds tabs, filters, sorting and the domain check.
 *
 * @var array    $attributes
 * @var string   $content
 * @var WP_Block $block
 */
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'bv_gi_ranked' ) ) {
	return;
}

$nl      = function_exists( 'bv_lang' ) && 'nl' === bv_lang();
$t       = static fn( $nl_text, $en_text ) => $nl ? $nl_text : $en_text;
$default = in_array( $attributes['list'] ?? '', [ 'nl', 'intl' ], true ) ? $attributes['list'] : ( $nl ? 'nl' : 'intl' );
$data    = bv_gi_companies();
$sectors = (array) ( $data['sectors'] ?? [] );
$meta    = (array) get_option( 'bv_gi_meta', [] );
$when    = ! empty( $meta['completed'] ) ? $meta['completed'] : 0;
$date    = $when ? wp_date( 'j F Y', $when ) : '';
$id      = wp_unique_id( 'bv-gi-' );

/* AI assistants measured in the last AI run (inc/geo-index-ai.php). */
$ai_run  = (array) get_option( 'bv_gi_ai_results', [] );
$ai_cols = [];
foreach ( (array) ( $ai_run['engines'] ?? [] ) as $e ) {
	$label = function_exists( 'bv_gi_ai_engines' ) ? ( bv_gi_ai_engines()[ $e ]['label'] ?? $e ) : $e;
	$ai_cols[ 'ai_' . $e ] = [ $label, sprintf( $t( 'Genoemd door %s bij de vraag over de eigen categorie', 'Named by %s for the question about its own category' ), $label ) ];
}

$v2      = function_exists( 'bv_gi_has_v2' ) && bv_gi_has_v2( $ai_run );
$eng_lbl = static fn( $e ) => function_exists( 'bv_gi_ai_engines' ) ? ( bv_gi_ai_engines()[ $e ]['label'] ?? $e ) : $e;
$num     = static fn( $v ) => $nl ? str_replace( '.', ',', (string) $v ) : (string) $v;

/* The method page, once it is published; else the method section on this page. */
$mpage      = get_page_by_path( $nl ? 'nl/b2b-geo-index/methode' : 'b2b-geo-index/methodology' );
$method_url = ( $mpage && 'publish' === $mpage->post_status ) ? get_permalink( $mpage ) : '#' . $t( 'methode', 'methodology' );

/* The index page in the other language, for the check: each page measures against its own list. */
$opage     = get_page_by_path( $nl ? 'b2b-geo-index' : 'nl/b2b-geo-index' );
$other_url = ( $opage && 'publish' === $opage->post_status ) ? get_permalink( $opage ) . '#check' : '';

/* Crawler access chips and website check columns: shared with the PDF (inc/geo-index-pdf.php). */
$tx     = bv_gi_texts( $nl );
$dlabel = $t( 'Details en verschillen', 'Details and gaps' );
$bots = $tx['bots'];
$cols = $tx['cols'];

/* One check cell. On small screens it shows as a pill: icon plus data-label. */
$cell = static function ( $v, string $label, string $extra = '' ) {
	$cls = true === $v ? 'bv-gi__y' : ( false === $v ? 'bv-gi__n' : 'bv-gi__u' );
	$ico = true === $v ? '&#10003;' : ( false === $v ? '&#10005;' : '-' );
	return '<td class="bv-gi__pill ' . $cls . $extra . '" data-label="' . esc_attr( $label ) . '">' . $ico . '</td>';
};

/* Labels for the small-screen cards (the table header covers wide screens). */
$ml = [
	'chg'   => $t( 'Maand', 'Month' ),
	'ai'    => $t( 'AI-score', 'AI score' ),
	'ready' => $t( 'Website', 'Website' ),
	'crawl' => $t( 'Crawlers', 'Crawlers' ),
];

$stats = static function ( array $rows ) {
	$n = count( $rows );
	if ( ! $n ) {
		return null;
	}
	$pct = static fn( $key ) => (int) round( 100 * count( array_filter( $rows, static fn( $r ) => true === ( $r['checks'][ $key ] ?? null ) ) ) / $n );
	return [
		'n'         => $n,
		'avg'       => (int) round( array_sum( array_column( $rows, 'score' ) ) / $n ),
		'chatgpt'   => $pct( 'bot_OAI-SearchBot' ),
		'html'      => $pct( 'html' ),
		'wikipedia' => $pct( 'wikipedia' ),
		'ai'        => count( array_filter( $rows, static fn( $r ) => null !== $r['ai'] ) ) ? (int) round( 100 * count( array_filter( $rows, static fn( $r ) => (int) $r['ai'] > 0 ) ) / $n ) : null,
	];
};

$lists = [
	'nl'   => $t( 'Top 200 Nederland', 'Top 200 Netherlands' ),
	'intl' => $t( 'Top 200 internationaal', 'Top 200 international' ),
];
$tabs  = [
	'nl'   => $t( 'Nederland', 'Netherlands' ),
	'intl' => $t( 'Internationaal', 'International' ),
];

$i18n = [
	'search'   => $t( 'Zoek een bedrijf', 'Search a company' ),
	'allSect'  => $t( 'Alle sectoren', 'All sectors' ),
	'sort'     => $t( 'Sorteer op', 'Sort by' ),
	'byScore'  => $t( 'Score', 'Score' ),
	'byChange' => $t( 'Verschil met vorige maand', 'Change since last month' ),
	'byName'   => $t( 'Naam', 'Name' ),
	'only'     => $t( 'Toon bedrijven die dit hebben', 'Show companies that have this' ),
	'missing'  => $t( 'Toon bedrijven die dit missen', 'Show companies that miss this' ),
	'count'    => $t( '%1$s van %2$s bedrijven', '%1$s of %2$s companies' ),
	'reset'    => $t( 'Wis filters', 'Clear filters' ),
	'more'     => $t( 'Toon %s meer', 'Show %s more' ),
	'checking' => $t( 'Bezig met meten. Dit duurt 10 tot 30 seconden.', 'Measuring. This takes 10 to 30 seconds.' ),
	'invalid'  => $t( 'Vul een geldig domein in, zoals voorbeeld.nl.', 'Enter a valid domain, such as example.com.' ),
	'limit'    => $t( 'Je hebt het maximum van 5 checks per uur bereikt. Probeer het later opnieuw.', 'You reached the limit of 5 checks per hour. Please try again later.' ),
	'error'    => $t( 'Meten lukte niet. Controleer het domein en probeer het opnieuw.', 'The check failed. Check the domain and try again.' ),
	'scoreFor' => $t( 'Websitescore van %s', 'Website score for %s' ),
	'outOf'    => $t( 'van 100', 'out of 100' ),
	'pass'     => $t( 'In orde', 'In place' ),
	'fail'     => $t( 'Ontbreekt', 'Missing' ),
	'unknown'  => $t( 'Niet te meten', 'Could not measure' ),
	'gapTitle' => $t( 'Verschil met de leider in %s', 'Gap with the leader in %s' ),
	'gapLead'  => $t( '%1$s leidt deze sector met een score van %2$s.', '%1$s leads this sector with a score of %2$s.' ),
	'gapSelf'  => $t( 'Je bent zelf de leider van deze sector.', 'You lead this sector yourself.' ),
	'gapPts'   => $t( 'Verschil: %s punten.', 'Gap: %s points.' ),
	'gapTied'  => $t( 'Dat valt binnen de foutmarge.', 'That is within the margin of error.' ),
	'gapMiss'  => $t( 'Checks die de leider wel heeft en jij niet:', 'Checks the leader has and you do not:' ),
	'gapNone'  => $t( 'Je mist geen checks die de leider wel heeft.', 'You miss no checks the leader has.' ),
	'gapSrc'   => $t( 'Hier halen AI-assistenten in deze sector hun bronnen het vaakst vandaan:', 'This is where AI assistants cite their sources most in this sector:' ),
	'gapSrcP'  => $t( '%s%% van de antwoorden', '%s%% of answers' ),
	'inIndex'  => $t( '%1$s staat in de index: score %2$s.', '%1$s is in the index: score %2$s.' ),
	'inAi'     => $t( 'Genoemd in %1$s van %2$s antwoorden (%3$s%%, marge %4$s tot %5$s%%).', 'Named in %1$s of %2$s answers (%3$s%%, margin %4$s to %5$s%%).' ),
	'partial'  => $t( 'onvolledig', 'incomplete' ),
	'refused'  => $t( 'Je homepage weigerde ons verzoek (%s). Daardoor konden we checks voor %s van de 100 punten niet doen, en is deze score te laag.', 'Your homepage refused our request (%s). So we could not run checks worth %s of the 100 points, and this score is too low.' ),
	'noAnswer' => $t( 'geen antwoord binnen 6 seconden', 'no answer within 6 seconds' ),
	'botWarn'  => $t( 'Je robots.txt laat AI-crawlers toe, maar dat is alleen de voordeur. Botbescherming of een firewall (bijvoorbeeld bij Cloudflare of Akamai) kan ChatGPT, Claude en Perplexity alsnog weigeren. Vraag je webteam of OAI-SearchBot, Claude-SearchBot en PerplexityBot je homepage kunnen openen, en check daarna opnieuw. Na 15 minuten meten we opnieuw.', 'Your robots.txt lets AI crawlers in, but that is only the front door. Bot protection or a firewall (at Cloudflare or Akamai, for example) can still turn ChatGPT, Claude and Perplexity away. Ask your web team whether OAI-SearchBot, Claude-SearchBot and PerplexityBot can open your homepage, then check again. We measure again after 15 minutes.' ),
	'retry'    => $t( 'Vraag je webteam of de homepage bereikbaar is voor crawlers, en check daarna opnieuw. Na 15 minuten meten we opnieuw.', 'Ask your web team whether the homepage is reachable for crawlers, then check again. We measure again after 15 minutes.' ),
	'listNote' => $t( 'Gemeten zoals de Nederlandse lijst: we kijken of de site een Nederlandse versie heeft. Een internationaal bedrijf? Gebruik de Engelse versie van deze check, die naar meerdere taalversies kijkt.', 'Measured like the international list: we look for two or more language versions. A Dutch company? Run the check on the Dutch page, which looks for a Dutch version instead.' ),
	'otherUrl' => $other_url,
	'otherTxt' => $t( 'Engelse versie van deze check', 'Dutch version of this check' ),
	'gapPart'  => $t( 'We kunnen je niet goed vergelijken met de leider, omdat een deel van de checks niet lukte.', 'We cannot compare you with the leader properly, because part of the checks did not run.' ),
	'labels'   => $tx['labels'],
	'langList' => $tx['lang_label'],
	'advice'   => $tx['advice'],
	'pdf'      => esc_url_raw( rest_url( 'bv/v1/geo-pdf' ) ),
	'pdfLang'  => $nl ? 'nl' : 'en',
	'pdfBtn'   => $t( 'Download als PDF', 'Download as PDF' ),
	'pdfPlan'  => $t( 'Download je verbeterplan als PDF', 'Download your improvement plan as PDF' ),
	'pdfBusy'  => $t( 'PDF wordt gemaakt...', 'Making the PDF...' ),
	'planHead' => $t( 'Je verbeterplan', 'Your improvement plan' ),
	'secPdf'   => $t( 'Download het sectorrapport %s (PDF)', 'Download the %s sector report (PDF)' ),
	'pdfGone'  => $t( 'Deze check is verlopen. Doe de check opnieuw en download dan de PDF.', 'This check has expired. Run the check again, then download the PDF.' ),
];

$caps    = ( $ai_cols ? "--bv-gi-cap-ai:'" . $t( 'Genoemd door', 'Named by' ) . "';" : '' ) . "--bv-gi-cap-web:'" . $t( 'Website', 'Website' ) . "'";
$wrapper = get_block_wrapper_attributes( [ 'class' => 'bv-gi', 'id' => $id, 'style' => $caps ] );
?>
<div <?php echo $wrapper; ?> data-bv-block="geo-index" data-default-list="<?php echo esc_attr( $default ); ?>" data-api="<?php echo esc_url( rest_url( 'bv/v1/geo-check' ) ); ?>" data-lang="<?php echo $nl ? 'nl' : 'en'; ?>">
	<script type="application/json" class="bv-gi__i18n"><?php echo wp_json_encode( $i18n ); ?></script>

	<div class="bv-gi__tabs" role="tablist" aria-label="<?php echo esc_attr( $t( 'Kies een lijst', 'Choose a list' ) ); ?>">
		<?php foreach ( $tabs as $key => $label ) : ?>
			<button type="button" role="tab" class="bv-gi__tab" data-list="<?php echo esc_attr( $key ); ?>" aria-selected="<?php echo $key === $default ? 'true' : 'false'; ?>" aria-controls="<?php echo esc_attr( $id . '-' . $key ); ?>"><?php echo esc_html( $label ); ?></button>
		<?php endforeach; ?>
	</div>

	<?php
	/* How the ranking works, right at the ranking. Folded on phones, open on wide screens (view.js). */
	$eng  = array_column( $ai_cols, 0 );
	$last = array_pop( $eng );
	$engs = $eng ? implode( ', ', $eng ) . $t( ' en ', ' and ' ) . $last : (string) $last;
	$how  = [
		[ $t( '400 vaste bedrijven.', '400 fixed companies.' ), $t( '200 Nederlandse en 200 internationale B2B-bedrijven: tien per sector, in dezelfde 20 sectoren uit de SBI- en NACE-indeling. Niemand kan betalen voor een plek of een hogere score.', '200 Dutch and 200 international B2B companies: ten per sector, in the same 20 sectors from the SBI and NACE classification. Nobody can pay for a place or a higher score.' ) ],
	];
	if ( $ai_cols ) {
		$vq    = (int) ( $ai_run['variants'] ?? 1 );
		$rq    = (int) ( $ai_run['runs'] ?? 1 );
		$how[] = $v2
			? ( ! empty( $ai_run['adaptive'] ) && $rq > 1
				? [ $t( 'AI-score.', 'AI score.' ), sprintf( $t( 'Elke maand stelt de site per categorie %1$d koopvragen aan %2$s, met zoeken op internet aan. Waar de uitkomst nog onzeker is, volgt een tweede ronde. Zo krijgt elk bedrijf %3$d tot %4$d antwoorden. We tellen hoe vaak ze het bedrijf noemen (80%%) en de website als bron geven (20%%), met een foutmarge van 95%%.', 'Every month the site asks %2$s %1$d buyer questions per category, with web search on. Where the result is still uncertain, a second round follows. That gives each company %3$d to %4$d answers. We count how often they name the company (80%%) and cite its website (20%%), with a 95%% margin of error.' ), $vq, $engs, $vq * max( 1, count( $ai_cols ) ), $vq * $rq * max( 1, count( $ai_cols ) ) ) ]
				: [ $t( 'AI-score.', 'AI score.' ), sprintf( $t( 'Elke maand stelt de site per categorie %1$d koopvragen, elk %2$d keer, aan %3$s, met zoeken op internet aan. Zo krijgt elk bedrijf ongeveer %4$d antwoorden. We tellen hoe vaak ze het bedrijf noemen (80%%) en de website als bron geven (20%%), met een foutmarge van 95%%.', 'Every month the site asks %3$s %1$d buyer questions per category, each %2$d times, with web search on. That gives each company about %4$d answers. We count how often they name the company (80%%) and cite its website (20%%), with a 95%% margin of error.' ), $vq, $rq, $engs, $vq * $rq * max( 1, count( $ai_cols ) ) ) ] )
			: [ $t( 'AI-score.', 'AI score.' ), sprintf( $t( 'Elke maand stelt de site per categorie een koopvraag aan %s, met zoeken op internet aan. We tellen hoe vaak ze het bedrijf noemen (80%%) en de website als bron geven (20%%).', 'Every month the site asks %s one buyer question per category, with web search on. We count how often they name the company (80%%) and cite its website (20%%).' ), $engs ) ];
	}
	$how[] = [ $t( 'Websitescore.', 'Website score.' ), $t( 'Zeven checks op de eigen website: AI-crawlers in robots.txt, tekst zonder JavaScript, gestructureerde data, Wikidata en Wikipedia, actualiteit, taal en llms.txt.', 'Seven checks on the company website: AI crawlers in robots.txt, text without JavaScript, structured data, Wikidata and Wikipedia, freshness, language and llms.txt.' ) ];
	$how[] = $ai_cols
		? [ $t( 'Totaalscore.', 'Total score.' ), $t( 'Het gemiddelde van de AI-score en de websitescore, van 0 tot 100. Elke maand opnieuw gemeten, met het verschil met vorige maand.', 'The average of the AI score and the website score, from 0 to 100. Measured again every month, with the change since last month.' ) ]
		: [ $t( 'Score.', 'Score.' ), $t( 'De websitescore, van 0 tot 100. Elke maand opnieuw gemeten, met het verschil met vorige maand.', 'The website score, from 0 to 100. Measured again every month, with the change since last month.' ) ];
	?>
	<details class="bv-gi__how">
		<summary><?php echo esc_html( $t( 'Zo werkt de ranking', 'How the ranking works' ) ); ?></summary>
		<ol>
			<?php foreach ( $how as [ $head, $body ] ) : ?>
				<li><strong><?php echo esc_html( $head ); ?></strong> <?php echo esc_html( $body ); ?></li>
			<?php endforeach; ?>
		</ol>
		<p><a href="<?php echo esc_url( $method_url ); ?>"><?php echo esc_html( $t( 'Lees de volledige methode en bronnen', 'Read the full method and sources' ) ); ?></a></p>
	</details>

	<div class="bv-gi__filters" hidden>
		<input type="search" class="bv-gi__q" placeholder="<?php echo esc_attr( $i18n['search'] ); ?>" aria-label="<?php echo esc_attr( $i18n['search'] ); ?>">
		<label class="bv-gi__field"><span><?php echo esc_html( $t( 'Sector', 'Sector' ) ); ?></span>
			<select class="bv-gi__sector"><option value=""><?php echo esc_html( $i18n['allSect'] ); ?></option><?php
				foreach ( $sectors as $sk => $sec ) {
					$sl = (string) ( $sec[ $nl ? 0 : 1 ] ?? '' );
					echo '<option value="' . esc_attr( $sl ) . '" data-key="' . esc_attr( $sk ) . '">' . esc_html( $sl ) . '</option>'; // phpcs:ignore
				}
			?></select>
		</label>
		<label class="bv-gi__field"><span><?php echo esc_html( $i18n['sort'] ); ?></span>
			<select class="bv-gi__sort">
				<option value="score"><?php echo esc_html( $i18n['byScore'] ); ?></option>
				<?php if ( $ai_cols ) : ?><option value="ai"><?php echo esc_html( $t( 'AI-score', 'AI score' ) ); ?></option><?php endif; ?>
				<option value="ready"><?php echo esc_html( $t( 'Websitescore', 'Website score' ) ); ?></option>
				<option value="change"><?php echo esc_html( $t( 'Stijgers', 'Risers' ) ); ?></option>
				<option value="name"><?php echo esc_html( $i18n['byName'] ); ?></option>
			</select>
		</label>
		<details class="bv-gi__more">
			<summary><span><?php echo esc_html( $t( 'Filters', 'Filters' ) ); ?></span><span class="bv-gi__active" hidden></span></summary>
		<div class="bv-gi__chips">
			<?php
			$groups = [
				[ $t( 'Genoemd door', 'Named by' ), $ai_cols, ' bv-gi__chip--ai' ],
				[ $t( 'Crawler mag lezen', 'Crawler allowed' ), $bots, '' ],
				[ $t( 'Website', 'Website' ), $cols, '' ],
			];
			foreach ( $groups as $g => [ $glabel, $gchips, $gcls ] ) :
				if ( ! $gchips ) {
					continue;
				}
				?>
				<div class="bv-gi__cg" role="group" aria-labelledby="<?php echo esc_attr( $id . '-g' . $g ); ?>">
					<span class="bv-gi__cgl" id="<?php echo esc_attr( $id . '-g' . $g ); ?>"><?php echo esc_html( $glabel ); ?></span>
					<?php foreach ( $gchips as $key => [ $short, $full ] ) : ?>
						<button type="button" class="bv-gi__chip<?php echo esc_attr( $gcls ); ?>" data-check="<?php echo esc_attr( $key ); ?>" aria-pressed="false" title="<?php echo esc_attr( $full ); ?>"><?php echo esc_html( $short ); ?></button>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="bv-gi__opts">
			<label class="bv-gi__mode"><input type="checkbox" class="bv-gi__missing"> <?php echo esc_html( $i18n['missing'] ); ?></label>
			<button type="button" class="bv-gi__reset"><?php echo esc_html( $i18n['reset'] ); ?></button>
		</div>
		</details>
		<p class="bv-gi__count" aria-live="polite"></p>
		<p class="bv-gi__secpdf" hidden></p>
	</div>

	<?php
	$groups_l = [ 'strong' => $t( 'Sterk', 'Strong' ), 'mid' => $t( 'Midden', 'Middle' ), 'low' => $t( 'Laag', 'Low' ) ];
	$quad_l   = [
		'both' => $t( 'Koploper: sterke website en vaak genoemd', 'Front runner: strong website and often named' ),
		'web'  => $t( 'Klaar, maar nog onbekend: sterke website, weinig genoemd', 'Ready but not yet known: strong website, rarely named' ),
		'ai'   => $t( 'Bekend ondanks de website: vaak genoemd, zwakkere website', 'Known despite the website: often named, weaker website' ),
		'none' => $t( 'Achterblijver: zwakkere website en weinig genoemd', 'Lagging: weaker website and rarely named' ),
	];

	/*
	 * Details per company, as compact data: view.js builds the panel when the
	 * button is pressed. Keeps the page light with 400 companies; the CSV and
	 * the open data API carry the same numbers for machines.
	 * a: [ answers, named, visibility, low, high, share of voice, first, tone, cited, too few ]
	 * e: [ [ assistant, answers, named ] ], c: change, l: sector leader, q: website versus AI
	 */
	$dpack = static function ( array $r, string $list, array $ins, array $leaders, array $bysec ): array {
		$ai = $r['ai_data'];
		$o  = [];
		if ( $ai ) {
			$o['a'] = [ (int) $ai['n'], (int) $ai['m'], (int) $ai['vis'], (int) $ai['vis_lo'], (int) $ai['vis_hi'], $ai['sov'], (int) $ai['first'], $ai['sent'], (int) $ai['cite'], $ai['low'] ? 1 : 0 ];
			foreach ( (array) $ai['e'] as $eng => $nm ) {
				$o['e'][] = [ (string) $eng, (int) $nm[0], (int) $nm[1] ];
			}
		}
		$o['c'] = ! empty( $r['method_new'] ) ? 'm' : ( null === $r['change'] ? 'n' : [ (int) $r['change'], $r['change_sig'] ? 1 : 0 ] );
		$leader = $leaders[ $r['sector'] ] ?? null;
		if ( $leader && $leader['domain'] === $r['domain'] ) {
			$second = '';
			foreach ( (array) ( $bysec[ $r['sector'] ] ?? [] ) as $other ) {
				if ( $other['domain'] !== $r['domain'] ) {
					$second = ( $r['score'] - $other['score'] ) <= $r['margin'] + $other['margin'] ? (string) $other['name'] : '';
					break;
				}
			}
			$o['l'] = [ 1, $second ];
		} elseif ( $leader ) {
			$gap    = function_exists( 'bv_gi_gap' ) ? bv_gi_gap( $list, $r['sector'], $leader, $r['domain'], (array) $r['checks'], (int) $r['score'], (int) $r['margin'] ) : null;
			$o['l'] = [ 0, (string) $leader['name'], (int) $leader['score'], (int) ( $gap['points'] ?? 0 ), empty( $gap['tied'] ) ? 0 : 1, array_values( (array) ( $gap['sources'] ?? [] ) ), array_values( (array) ( $gap['missing'] ?? [] ) ) ];
		}
		if ( ! empty( $ins['quad'][ $r['domain'] ] ) ) {
			$o['q'] = $ins['quad'][ $r['domain'] ];
		}
		return $o;
	};
	$i18n['d'] = [
		'ai'       => $t( 'AI-zichtbaarheid', 'AI visibility' ),
		'noAi'     => $t( 'Nog geen AI-meting voor dit bedrijf.', 'No AI measurement for this company yet.' ),
		'low'      => $t( 'Te weinig antwoorden voor een betrouwbare AI-score: {1} van minimaal {2}. De totaalscore is daarom alleen de websitescore.', 'Too few answers for a reliable AI score: {1} of at least {2}. The total score is therefore the website score only.' ),
		'min'      => defined( 'BV_GI_MIN_ANSWERS' ) ? BV_GI_MIN_ANSWERS : 12,
		'named'    => $t( 'Genoemd in {1} van {2} antwoorden: {3}% (marge {4} tot {5}%)', 'Named in {1} of {2} answers: {3}% (margin {4} to {5}%)' ),
		'sov'      => $t( 'Share of voice in {1}: {2}% van alle keren dat AI een indexbedrijf uit deze sector noemde', 'Share of voice in {1}: {2}% of all times AI named an index company from this sector' ),
		'first'    => $t( 'Als eerste genoemd: {1}% van de antwoorden', 'Named first: {1}% of answers' ),
		'tone'     => $t( 'Toon: {1} van 100 ({2})', 'Tone: {1} out of 100 ({2})' ),
		'tones'    => [ $t( 'kritisch', 'critical' ), $t( 'neutraal', 'neutral' ), $t( 'positief', 'positive' ) ],
		'cite'     => $t( 'Eigen website als bron: {1}% van de antwoorden', 'Own website cited as a source: {1}% of answers' ),
		'perEng'   => $t( 'Per assistent', 'Per assistant' ),
		'of'       => $t( '{1} van {2}', '{1} of {2}' ),
		'chg'      => $t( 'Verschil met vorige maand', 'Change since last month' ),
		'chgM'     => $t( 'Deze maand is de eerste meting met de nieuwe methode. Het verschil met vorige maand zie je vanaf de volgende meting.', 'This month is the first measurement with the new method. The change since last month shows from the next measurement.' ),
		'chgN'     => $t( 'Eerste meting van dit bedrijf.', 'First measurement of this company.' ),
		'chgSame'  => $t( 'Gelijk aan vorige maand.', 'Same as last month.' ),
		'chgSig'   => $t( 'Totaalscore {1} punten. Dat is meer dan de foutmarge: een echte verandering.', 'Total score {1} points. That is more than the margin of error: a real change.' ),
		'chgNs'    => $t( 'Totaalscore {1} punten, binnen de foutmarge. Waarschijnlijk toeval.', 'Total score {1} points, within the margin of error. Probably chance.' ),
		'lead'     => $t( 'Verschil met de leider in {1}', 'Gap with the leader in {1}' ),
		'leadSelf' => $t( '{1} leidt deze sector.', '{1} leads this sector.' ),
		'leadTwo'  => $t( 'Nummer 2, {1}, zit binnen de foutmarge.', 'Number 2, {1}, is within the margin of error.' ),
		'leadGap'  => $t( '{1} leidt met een score van {2}. Verschil: {3} punten', '{1} leads with a score of {2}. Gap: {3} points' ),
		'tied'     => $t( ', binnen de foutmarge.', ', within the margin of error.' ),
		'srcs'     => $t( 'Bronnen die {1} noemen en {2} niet:', 'Sources that name {1} and not {2}:' ),
		'miss'     => $t( 'Checks die {1} wel heeft:', 'Checks {1} has:' ),
		'quad'     => $t( 'Website versus AI', 'Website versus AI' ),
		'quadNote' => $t( 'Sterk of zwak ten opzichte van de mediaan van deze lijst.', 'Strong or weak compared with the median of this list.' ),
		'quads'    => $quad_l,
		'eng'      => array_map( static fn( $e ) => $e['label'], function_exists( 'bv_gi_ai_engines' ) ? bv_gi_ai_engines() : [] ),
	];
	echo '<script type="application/json" class="bv-gi__i18d">' . wp_json_encode( $i18n['d'], JSON_UNESCAPED_UNICODE ) . '</script>'; // phpcs:ignore
	?>
	<?php foreach ( $lists as $key => $label ) :
		$rows    = bv_gi_ranked( $key );
		$ins     = $v2 && function_exists( 'bv_gi_insights' ) ? bv_gi_insights( $key, $rows ) : [];
		$leaders = function_exists( 'bv_gi_leaders' ) ? bv_gi_leaders( $rows ) : [];
		$bysec   = [];
		foreach ( $rows as $rr ) {
			$bysec[ $rr['sector'] ][] = $rr;
		}
		$dd = [];
		$s       = $stats( $rows );
		$have    = array_flip( array_column( $rows, 'domain' ) );
		$pending = array_values( array_filter( (array) ( $data[ $key ] ?? [] ), static fn( $c ) => ! isset( $have[ $c['domain'] ] ) ) );
		?>
		<section class="bv-gi__list" id="<?php echo esc_attr( $id . '-' . $key ); ?>" data-list="<?php echo esc_attr( $key ); ?>" role="tabpanel" <?php echo $key === $default ? '' : 'hidden'; ?>>
			<h3 class="bv-gi__title"><?php echo esc_html( 'B2B GEO Index: ' . $label ); ?></h3>
			<?php if ( ! $s && ! $pending ) : ?>
				<p class="bv-gi__empty"><?php echo esc_html( $t( 'De eerste meting loopt nu. De ranglijst verschijnt hier zodra alle bedrijven zijn gemeten.', 'The first measurement is running. The ranking appears here once every company has been measured.' ) ); ?></p>
			<?php else : ?>
				<?php if ( $s ) : ?>
				<div class="bv-gi__stats">
					<div><strong><?php echo (int) $s['n']; ?></strong><span><?php echo esc_html( $t( 'bedrijven gemeten', 'companies measured' ) ); ?></span></div>
					<div><strong><?php echo (int) $s['avg']; ?></strong><span><?php echo esc_html( $t( 'gemiddelde score van 100', 'average score out of 100' ) ); ?></span></div>
					<?php if ( null !== $s['ai'] ) : ?><div><strong><?php echo (int) $s['ai']; ?>%</strong><span><?php echo esc_html( $t( 'genoemd door AI-assistenten', 'named by AI assistants' ) ); ?></span></div><?php endif; ?>
					<div><strong><?php echo (int) $s['chatgpt']; ?>%</strong><span><?php echo esc_html( $t( 'laat ChatGPT search toe', 'allow ChatGPT search' ) ); ?></span></div>
					<div><strong><?php echo (int) $s['html']; ?>%</strong><span><?php echo esc_html( $t( 'leesbaar zonder JavaScript', 'readable without JavaScript' ) ); ?></span></div>
					<div><strong><?php echo (int) $s['wikipedia']; ?>%</strong><span><?php echo esc_html( $t( 'heeft een Wikipedia-artikel', 'have a Wikipedia article' ) ); ?></span></div>
				</div>
				<?php endif; ?>
				<div class="bv-gi__scroll">
					<table class="bv-gi__table">
						<caption class="screen-reader-text"><?php echo esc_html( 'B2B GEO Index: ' . $label ); ?></caption>
						<thead><tr>
							<th scope="col" rowspan="2">#</th>
							<th scope="col" rowspan="2"><?php echo esc_html( $t( 'Bedrijf', 'Company' ) ); ?></th>
							<th scope="col" rowspan="2"><?php echo esc_html( $t( 'Score', 'Score' ) ); ?></th>
							<th scope="col" rowspan="2" title="<?php echo esc_attr( $i18n['byChange'] ); ?>"><?php echo esc_html( $t( 'Maand', 'Month' ) ); ?></th>
							<?php if ( $ai_cols ) : ?><th scope="col" rowspan="2" title="<?php echo esc_attr( $t( 'Hoe vaak AI-assistenten het bedrijf noemen (0 tot 100)', 'How often AI assistants name the company (0 to 100)' ) ); ?>">AI</th><?php endif; ?>
							<th scope="col" rowspan="2" title="<?php echo esc_attr( $t( 'Score van de websitemeting (0 tot 100)', 'Score of the website measurement (0 to 100)' ) ); ?>"><?php echo esc_html( $t( 'Website', 'Website' ) ); ?></th>
							<?php if ( $ai_cols ) : ?><th scope="colgroup" colspan="<?php echo count( $ai_cols ); ?>" class="bv-gi__grp"><?php echo esc_html( $t( 'Genoemd door', 'Named by' ) ); ?></th><?php endif; ?>
							<th scope="colgroup" colspan="<?php echo 1 + count( $cols ); ?>" class="bv-gi__grp"><?php echo esc_html( $t( 'Website', 'Website' ) ); ?></th>
						</tr><tr>
							<?php foreach ( $ai_cols as [ $short, $full ] ) : ?>
								<th scope="col" title="<?php echo esc_attr( $full ); ?>"><?php echo esc_html( $short ); ?></th>
							<?php endforeach; ?>
							<th scope="col" title="<?php echo esc_attr( $t( 'Aantal van 5 AI-crawlers dat de site mag lezen', 'Number of 5 AI crawlers allowed to read the site' ) ); ?>"><?php echo esc_html( $t( 'Crawlers', 'Crawlers' ) ); ?></th>
							<?php foreach ( $cols as [ $short, $full ] ) : ?>
								<th scope="col" title="<?php echo esc_attr( $full ); ?>"><abbr title="<?php echo esc_attr( $full ); ?>"><?php echo esc_html( $short ); ?></abbr></th>
							<?php endforeach; ?>
						</tr></thead>
						<tbody>
						<?php foreach ( $rows as $r ) :
							$sector = $sectors[ $r['sector'] ][ $nl ? 0 : 1 ] ?? $r['sector'];
							$attrs  = '';
							$vals   = $r['checks'];
							foreach ( array_keys( $ai_cols ) as $k ) {
								$vals[ $k ] = null === $r['ai'] ? null : ! empty( $r['ai_eng'][ substr( $k, 3 ) ] );
							}
							foreach ( array_keys( $ai_cols + $bots + $cols ) as $k ) {
								$v      = $vals[ $k ] ?? null;
								$attrs .= ' data-c-' . esc_attr( strtolower( $k ) ) . '="' . ( true === $v ? '1' : ( false === $v ? '0' : '' ) ) . '"';
							}
							$open = count( array_filter( array_keys( $bots ), static fn( $k ) => true === ( $r['checks'][ $k ] ?? null ) ) );
							$chg = null === $r['change'] ? '' : ( $r['change_sig'] ? (string) $r['change'] : '0' ); // risers: only changes larger than the margin
							$did = $id . '-' . $key . '-d' . sanitize_html_class( str_replace( '.', '-', $r['domain'] ) );
							?>
							<tr data-name="<?php echo esc_attr( strtolower( $r['name'] . ' ' . $r['domain'] ) ); ?>" data-sector="<?php echo esc_attr( $sector ); ?>" data-score="<?php echo (int) $r['score']; ?>" data-change="<?php echo esc_attr( $chg ); ?>" data-ai="<?php echo null === $r['ai'] ? '' : (int) $r['ai']; ?>" data-ready="<?php echo (int) $r['ready']; ?>"<?php echo $attrs; ?>>
								<?php if ( '' === $r['group'] ) : ?>
									<td class="bv-gi__rank<?php echo $r['rank'] <= 3 ? ' bv-gi__rank--top' : ''; ?>"><?php echo (int) $r['rank']; ?></td>
								<?php else : ?>
									<td class="bv-gi__rank bv-gi__rank--grp bv-gi__g-<?php echo esc_attr( $r['group'] ); ?>" title="<?php echo esc_attr( sprintf( $t( 'Plaats %d', 'Place %d' ), (int) $r['rank'] ) ); ?>"><?php echo esc_html( $groups_l[ $r['group'] ] ); ?></td>
								<?php endif; ?>
								<td class="bv-gi__co"><div class="bv-gi__cow"><div class="bv-gi__cot"><strong><?php echo esc_html( $r['name'] ); ?></strong><span><?php echo esc_html( $r['domain'] ); ?> &middot; <?php echo esc_html( $sector ); ?></span></div><button type="button" class="bv-gi__dbtn" aria-expanded="false" aria-controls="<?php echo esc_attr( $did ); ?>" aria-label="<?php echo esc_attr( $dlabel ); ?>"><span class="bv-gi__dlf"><?php echo esc_html( $dlabel ); ?></span><span class="bv-gi__dls" aria-hidden="true"><?php echo esc_html( $t( 'Details', 'Details' ) ); ?></span><i class="bv-gi__dico" aria-hidden="true"></i></button></div></td>
								<td class="bv-gi__score"><span class="bv-gi__bar" style="--s:<?php echo (int) $r['score']; ?>"><?php echo (int) $r['score']; ?></span><?php if ( $r['margin'] > 0 ) : ?><span class="bv-gi__pm">&plusmn;<?php echo (int) $r['margin']; ?></span><?php endif; ?></td>
								<td class="bv-gi__chg" data-label="<?php echo esc_attr( $ml['chg'] ); ?>"><?php
									if ( ! empty( $r['method_new'] ) ) {
										echo '<span class="bv-gi__flat" title="' . esc_attr( $t( 'Nieuwe meetmethode: vergelijking vanaf de volgende meting', 'New method: comparison from the next measurement' ) ) . '">-</span>';
									} elseif ( null === $r['change'] || 0 === (int) $r['change'] ) {
										echo '<span class="bv-gi__flat">' . ( null === $r['change'] ? esc_html( $t( 'nieuw', 'new' ) ) : '0' ) . '</span>';
									} elseif ( ! $r['change_sig'] ) {
										echo '<span class="bv-gi__ns" title="' . esc_attr( $t( 'Binnen de foutmarge', 'Within the margin of error' ) ) . '">' . ( $r['change'] > 0 ? '+' : '' ) . (int) $r['change'] . '</span>';
									} else {
										echo '<span class="' . ( $r['change'] > 0 ? 'bv-gi__up">+' : 'bv-gi__down">' ) . (int) $r['change'] . '</span>';
									}
								?></td>
								<?php if ( $ai_cols ) : ?><td class="bv-gi__num" data-label="<?php echo esc_attr( $ml['ai'] ); ?>"><?php
									if ( null === $r['ai'] ) {
										echo '-';
									} elseif ( $r['ai_low'] ) {
										echo '<span class="bv-gi__flat" title="' . esc_attr( $t( 'Te weinig antwoorden voor een betrouwbare AI-score', 'Too few answers for a reliable AI score' ) ) . '">' . esc_html( $t( 'n.b.', 'n/a' ) ) . '</span>';
									} else {
										echo (int) $r['ai'];
									}
								?></td><?php endif; ?>
								<td class="bv-gi__num" data-label="<?php echo esc_attr( $ml['ready'] ); ?>"><?php echo (int) $r['ready']; ?></td>
								<?php foreach ( $ai_cols as $k => $c ) { echo $cell( $vals[ $k ], $c[0], ' bv-gi__ai' ); } // phpcs:ignore ?>
								<td class="bv-gi__crawl <?php echo 5 === $open ? 'bv-gi__y' : ( 0 === $open ? 'bv-gi__n' : 'bv-gi__p' ); ?>" data-label="<?php echo esc_attr( $ml['crawl'] ); ?>"><?php echo (int) $open; ?>/5</td>
								<?php foreach ( $cols as $k => $c ) { echo $cell( $r['checks'][ $k ] ?? null, $c[0] ); } // phpcs:ignore ?>
							</tr>
							<?php $dd[ $r['domain'] ] = $dpack( $r, $key, $ins, $leaders, $bysec ); ?>
						<?php endforeach; ?>
						<?php foreach ( $pending as $c ) :
							$sector = $sectors[ $c['sector'] ][ $nl ? 0 : 1 ] ?? $c['sector'];
							?>
							<tr class="bv-gi__pending" data-name="<?php echo esc_attr( strtolower( $c['name'] . ' ' . $c['domain'] ) ); ?>" data-sector="<?php echo esc_attr( $sector ); ?>" data-score="" data-change="" data-ai="" data-ready="">
								<td class="bv-gi__rank">-</td>
								<td class="bv-gi__co"><strong><?php echo esc_html( $c['name'] ); ?></strong><span><?php echo esc_html( $c['domain'] ); ?> &middot; <?php echo esc_html( $sector ); ?></span></td>
								<td class="bv-gi__score"><span class="bv-gi__wait"><?php echo esc_html( $t( 'wordt gemeten', 'being measured' ) ); ?></span></td>
								<td class="bv-gi__chg" data-label="<?php echo esc_attr( $ml['chg'] ); ?>"><span class="bv-gi__flat">-</span></td>
								<?php if ( $ai_cols ) : ?><td class="bv-gi__num" data-label="<?php echo esc_attr( $ml['ai'] ); ?>">-</td><?php endif; ?>
								<td class="bv-gi__num" data-label="<?php echo esc_attr( $ml['ready'] ); ?>">-</td>
								<?php foreach ( $ai_cols as $k => $col ) { echo $cell( null, $col[0], ' bv-gi__ai' ); } // phpcs:ignore ?>
								<td class="bv-gi__crawl bv-gi__u" data-label="<?php echo esc_attr( $ml['crawl'] ); ?>">-</td>
								<?php foreach ( $cols as $k => $col ) { echo $cell( null, $col[0] ); } // phpcs:ignore ?>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				<button type="button" class="bv-gi__next" hidden></button>
				<?php if ( $dd ) : ?><script type="application/json" class="bv-gi__dd"><?php echo wp_json_encode( $dd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ); ?></script><?php endif; ?>
				<?php if ( $ins ) :
					$pct  = static fn( $v ) => null === $v ? '-' : (int) $v . '%';
					$rho  = $ins['rho'];
					$abs  = null === $rho ? 0 : abs( $rho );
					$str  = $abs < 0.3 ? $t( 'zwak', 'weak' ) : ( $abs < 0.5 ? $t( 'matig', 'moderate' ) : $t( 'sterk', 'strong' ) );
					$ans  = array_sum( array_map( 'intval', (array) ( $ai_run['ok'] ?? [] ) ) );
					?>
					<section class="bv-gi__ins" aria-labelledby="<?php echo esc_attr( $id . '-' . $key . '-ins' ); ?>">
						<h3 id="<?php echo esc_attr( $id . '-' . $key . '-ins' ); ?>"><?php echo esc_html( $t( 'Verschillen en inzichten', 'Differences and insights' ) ); ?></h3>
						<p class="bv-gi__ins-lead"><?php echo esc_html( sprintf( $t( 'Uit de meting van %1$s: %2$s antwoorden van %3$s op dezelfde koopvragen.', 'From the measurement of %1$s: %2$s answers from %3$s to the same buyer questions.' ), $date ?: wp_date( 'j F Y', (int) ( $ai_run['completed'] ?? time() ) ), number_format_i18n( $ans ), $engs ) ); ?></p>
						<div class="bv-gi__ins-grid">
							<?php if ( null !== $rho ) : ?>
							<div class="bv-gi__icard">
								<h4><?php echo esc_html( $t( 'Website versus AI', 'Website versus AI' ) ); ?></h4>
								<div class="bv-gi__quad">
									<?php foreach ( [ 'web', 'both', 'none', 'ai' ] as $q ) : ?>
										<div class="bv-gi__q-<?php echo esc_attr( $q ); ?>"><strong><?php echo (int) $ins['quad_n'][ $q ]; ?></strong><span><?php echo esc_html( $quad_l[ $q ] ); ?></span></div>
									<?php endforeach; ?>
								</div>
								<p><?php echo esc_html( sprintf( $t( 'Samenhang tussen websitescore en AI-score (Spearman): %1$s, %2$s.', 'Correlation between website score and AI score (Spearman): %1$s, %2$s.' ), $num( number_format( $rho, 2, '.', '' ) ), $str ) ); ?>
								<?php echo esc_html( $abs < 0.3
									? $t( 'Een sterke website alleen verklaart weinig van hoe vaak AI een bedrijf noemt.', 'A strong website alone explains little of how often AI names a company.' )
									: ( $rho > 0 ? $t( 'Bedrijven met een sterkere website worden vaker genoemd.', 'Companies with a stronger website are named more often.' ) : $t( 'Bedrijven met een sterkere website worden juist minder vaak genoemd.', 'Companies with a stronger website are named less often.' ) ) ); ?></p>
								<p class="bv-gi__small"><?php echo esc_html( sprintf( $t( 'Gesplitst op de mediaan van deze lijst: websitescore %1$d, AI-score %2$d. %3$d bedrijven.', 'Split at the median of this list: website score %1$d, AI score %2$d. %3$d companies.' ), $ins['med_w'], $ins['med_a'], $ins['n_pair'] ) ); ?></p>
							</div>
							<?php endif; ?>
							<?php if ( $ins['eng'] ) : ?>
							<div class="bv-gi__icard">
								<h4><?php echo esc_html( $t( 'Verschil per assistent', 'Differences per assistant' ) ); ?></h4>
								<p class="bv-gi__small"><?php echo esc_html( $t( 'Hoe vaak elke assistent het bedrijf noemt waar de vraag over gaat.', 'How often each assistant names the company the question is about.' ) ); ?></p>
								<ul class="bv-gi__bars">
									<?php foreach ( $ins['eng'] as $e => $v ) : ?>
										<li><span class="bv-gi__bl"><?php echo esc_html( $eng_lbl( $e ) ); ?></span><span class="bv-gi__mini" style="--p:<?php echo (int) $v; ?>" aria-hidden="true"></span><span class="bv-gi__bv"><?php echo (int) $v; ?>%</span></li>
									<?php endforeach; ?>
								</ul>
								<?php if ( $ins['pairs'] ) : ?>
									<p class="bv-gi__small"><?php echo esc_html( $t( 'Overlap: welk deel van de genoemde indexbedrijven twee assistenten gemeen hebben bij dezelfde vraag.', 'Overlap: the share of index companies named that two assistants have in common for the same question.' ) ); ?></p>
									<ul class="bv-gi__pairs">
										<?php foreach ( $ins['pairs'] as $k => $v ) :
											[ $a1, $a2 ] = explode( '|', $k );
											?>
											<li><span><?php echo esc_html( $eng_lbl( $a1 ) . $t( ' en ', ' and ' ) . $eng_lbl( $a2 ) ); ?></span><strong><?php echo (int) $v; ?>%</strong></li>
										<?php endforeach; ?>
									</ul>
								<?php endif; ?>
							</div>
							<?php endif; ?>
							<?php if ( null !== $ins['cross']['nl_q'] || null !== $ins['cross']['en_q'] ) : ?>
							<div class="bv-gi__icard">
								<h4><?php echo esc_html( $t( 'Nederland versus internationaal', 'Netherlands versus international' ) ); ?></h4>
								<div class="bv-gi__duo">
									<div><strong><?php echo esc_html( $pct( $ins['cross']['nl_q'] ) ); ?></strong><span><?php echo esc_html( $t( 'Nederlands bij Nederlandse vragen', 'Dutch in answers to Dutch questions' ) ); ?></span></div>
									<div><strong><?php echo esc_html( $pct( $ins['cross']['en_q'] ) ); ?></strong><span><?php echo esc_html( $t( 'Nederlands bij Engelse vragen', 'Dutch in answers to English questions' ) ); ?></span></div>
								</div>
								<p class="bv-gi__small"><?php echo esc_html( $t( 'Aandeel Nederlandse bedrijven onder alle indexbedrijven die AI noemt. Beide lijsten gebruiken dezelfde 20 sectoren.', 'Share of Dutch companies among all index companies AI names. Both lists use the same 20 sectors.' ) ); ?></p>
								<details class="bv-gi__fold">
									<summary><?php echo esc_html( $t( 'Per sector', 'Per sector' ) ); ?></summary>
									<table class="bv-gi__mt">
										<thead><tr><th scope="col"><?php echo esc_html( $t( 'Sector', 'Sector' ) ); ?></th><th scope="col"><?php echo esc_html( $t( 'NL-vragen', 'Dutch questions' ) ); ?></th><th scope="col"><?php echo esc_html( $t( 'EN-vragen', 'English questions' ) ); ?></th></tr></thead>
										<tbody>
											<?php foreach ( $sectors as $sk => $sec ) :
												$x = $ins['cross']['sectors'][ $sk ] ?? null;
												if ( ! $x ) {
													continue;
												}
												?>
												<tr><th scope="row"><?php echo esc_html( $sec[ $nl ? 0 : 1 ] ); ?></th><td><?php echo esc_html( $pct( $x['nl_q'] ?? null ) ); ?></td><td><?php echo esc_html( $pct( $x['en_q'] ?? null ) ); ?></td></tr>
											<?php endforeach; ?>
										</tbody>
									</table>
								</details>
							</div>
							<?php endif; ?>
						</div>
						<?php if ( $ins['src'] ) : ?>
						<div class="bv-gi__icard bv-gi__src">
							<h4><?php echo esc_html( $t( 'Meest aangehaalde bronnen per sector', 'Most cited sources per sector' ) ); ?></h4>
							<p class="bv-gi__small"><?php echo esc_html( $t( 'De websites die AI-assistenten het vaakst als bron gaven bij de vragen over een sector, als deel van de antwoorden. Hier genoemd worden helpt om genoemd te worden.', 'The websites AI assistants cited most for the questions about a sector, as a share of the answers. Being mentioned here helps you get named.' ) ); ?></p>
							<div class="bv-gi__srcs">
								<?php foreach ( $sectors as $sk => $sec ) :
									$sd = $ins['src'][ $sk ] ?? null;
									if ( ! $sd || ! $sd['top'] ) {
										continue;
									}
									?>
									<details class="bv-gi__fold" data-sector="<?php echo esc_attr( $sec[ $nl ? 0 : 1 ] ); ?>">
										<summary><?php echo esc_html( $sec[ $nl ? 0 : 1 ] ); ?> <span><?php echo esc_html( sprintf( $t( '%d antwoorden', '%d answers' ), $sd['answers'] ) ); ?></span></summary>
										<ol>
											<?php foreach ( $sd['top'] as $hst ) : ?>
												<li><span class="bv-gi__host"><?php echo esc_html( $hst['host'] ); ?></span><?php if ( $hst['owner'] ) : ?> <em><?php echo esc_html( sprintf( $t( 'site van %s', 'site of %s' ), $hst['owner'] ) ); ?></em><?php endif; ?><strong><?php echo (int) $hst['pct']; ?>%</strong></li>
											<?php endforeach; ?>
										</ol>
									</details>
								<?php endforeach; ?>
							</div>
						</div>
						<?php endif; ?>
					</section>
				<?php endif; ?>
			<?php endif; ?>
		</section>
	<?php endforeach; ?>

	<p class="bv-gi__meta"><?php
		echo esc_html( $date
			? sprintf( $t( 'Laatst gemeten: %s. Elke eerste werkdag van de maand opnieuw gemeten.', 'Last measured: %s. Measured again on the first working day of every month.' ), $date )
			: $t( 'Elke eerste werkdag van de maand opnieuw gemeten.', 'Measured again on the first working day of every month.' ) );
	?></p>

	<div class="bv-gi__legend">
		<span><strong><?php echo esc_html( $t( 'Score', 'Score' ) ); ?></strong> <?php echo esc_html( $ai_cols ? $t( 'helft AI-score, helft websitescore', 'half AI score, half website score' ) : $t( 'websitescore', 'website score' ) ); ?></span>
		<?php if ( $ai_cols ) : ?><span><strong>AI</strong> <?php echo esc_html( $t( 'hoe vaak AI-assistenten het bedrijf noemen bij de vraag over zijn categorie', 'how often AI assistants name the company for the question about its category' ) ); ?></span><?php endif; ?>
		<span><strong>Crawlers</strong> <?php echo esc_html( $t( 'aantal van 5 AI-crawlers dat de site mag lezen', 'number of 5 AI crawlers allowed to read the site' ) ); ?></span>
		<?php if ( $v2 ) : ?>
			<span><strong>&plusmn;</strong> <?php echo esc_html( $t( 'foutmarge (95%): de score ligt met 95% zekerheid binnen deze marge', 'margin of error (95%): the score lies within this margin with 95% certainty' ) ); ?></span>
			<span><strong class="bv-gi__ns">+3</strong> <?php echo esc_html( $t( 'grijs verschil: binnen de foutmarge, waarschijnlijk toeval', 'grey change: within the margin of error, probably chance' ) ); ?></span>
			<span><strong><?php echo esc_html( $t( 'Sterk, Midden, Laag', 'Strong, Middle, Low' ) ); ?></strong> <?php echo esc_html( $t( 'buiten de top 10 een groep in plaats van een plaats (score 50 of meer, 25 tot 49, onder 25)', 'outside the top 10 a group instead of a place (score 50 or more, 25 to 49, under 25)' ) ); ?></span>
		<?php endif; ?>
		<span><strong>&#10003;</strong> <?php echo esc_html( $t( 'in orde', 'in place' ) ); ?>, <strong>&#10005;</strong> <?php echo esc_html( $t( 'ontbreekt', 'missing' ) ); ?>, <strong>-</strong> <?php echo esc_html( $t( 'niet te meten', 'could not measure' ) ); ?></span>
		<?php foreach ( $cols as [ $short, $full ] ) : ?>
			<span><strong><?php echo esc_html( $short ); ?></strong> <?php echo esc_html( $full ); ?></span>
		<?php endforeach; ?>
	</div>

	<span id="check" aria-hidden="true"></span>
	<div class="bv-gi__check" id="<?php echo esc_attr( $id ); ?>-check">
		<h3><?php echo esc_html( $t( 'Check je eigen bedrijf', 'Check your own company' ) ); ?></h3>
		<p><?php echo esc_html( $t( 'Vul je domein in en zie in 30 seconden de websitescore van je bedrijf, gemeten op dezelfde manier als de bedrijven in de Nederlandse lijst. Kies je sector om ook je verschil met de sectorleider te zien.', 'Enter your domain and see your website score in 30 seconds, measured the same way as the companies in the international list. Pick your sector to also see your gap with the sector leader.' ) ); ?></p>
		<form class="bv-gi__form" novalidate>
			<label class="screen-reader-text" for="<?php echo esc_attr( $id ); ?>-domain"><?php echo esc_html( $t( 'Domein', 'Domain' ) ); ?></label>
			<input type="text" id="<?php echo esc_attr( $id ); ?>-domain" name="domain" inputmode="url" autocomplete="url" placeholder="<?php echo esc_attr( $t( 'jouwbedrijf.nl', 'yourcompany.com' ) ); ?>" required>
			<?php if ( $sectors ) : ?>
				<label class="screen-reader-text" for="<?php echo esc_attr( $id ); ?>-sector"><?php echo esc_html( $t( 'Sector', 'Sector' ) ); ?></label>
				<select id="<?php echo esc_attr( $id ); ?>-sector" name="sector" class="bv-gi__csec">
					<option value=""><?php echo esc_html( $t( 'Je sector (optioneel)', 'Your sector (optional)' ) ); ?></option>
					<?php foreach ( $sectors as $sk => $sec ) : ?>
						<option value="<?php echo esc_attr( $sk ); ?>"><?php echo esc_html( $sec[ $nl ? 0 : 1 ] ); ?></option>
					<?php endforeach; ?>
				</select>
			<?php endif; ?>
			<button type="submit" class="bv-gi__btn"><?php echo esc_html( $t( 'Check mijn score', 'Check my score' ) ); ?></button>
		</form>
		<noscript><p><?php echo esc_html( $t( 'De check heeft JavaScript nodig.', 'The check needs JavaScript.' ) ); ?></p></noscript>
		<div class="bv-gi__result" aria-live="polite" hidden></div>
		<div class="bv-gi__gate" hidden>
			<h4><?php echo esc_html( $t( 'Ontvang je verbeterplan', 'Get your improvement plan' ) ); ?></h4>
			<p><?php echo esc_html( $t( 'Laat je e-mailadres achter. Je ziet direct per punt wat je kunt verbeteren en je ontvangt onze nieuwsbrief met nieuw onderzoek over AI-zoekmachines.', 'Leave your email address. You see right away what to fix for each point, and you get our newsletter with new research on AI search.' ) ); ?></p>
			<?php echo do_shortcode( '[contact-form-7 id="6c25a82" title="Newsletter"]' ); // phpcs:ignore ?>
		</div>
		<div class="bv-gi__plan" hidden></div>
	</div>
</div>
