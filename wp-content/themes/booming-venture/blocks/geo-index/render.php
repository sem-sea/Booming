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

/* Crawler access: filter chips, and one "x/5" column in the table. */
$bots = [
	'bot_OAI-SearchBot'    => [ 'ChatGPT', $t( 'ChatGPT search mag de site lezen (OAI-SearchBot)', 'ChatGPT search may read the site (OAI-SearchBot)' ) ],
	'bot_Claude-SearchBot' => [ 'Claude', $t( 'Claude mag de site lezen (Claude-SearchBot)', 'Claude may read the site (Claude-SearchBot)' ) ],
	'bot_PerplexityBot'    => [ 'Perplexity', $t( 'Perplexity mag de site lezen (PerplexityBot)', 'Perplexity may read the site (PerplexityBot)' ) ],
	'bot_Google-Extended'  => [ 'Gemini', $t( 'Gemini mag de content gebruiken (Google-Extended)', 'Gemini may use the content (Google-Extended)' ) ],
	'bot_Bingbot'          => [ 'Copilot', $t( 'Bing en Copilot mogen de site lezen (Bingbot)', 'Bing and Copilot may read the site (Bingbot)' ) ],
];

/* Website checks shown as columns. */
$cols = [
	'html'                 => [ 'HTML', $t( 'Tekst leesbaar zonder JavaScript', 'Text readable without JavaScript' ) ],
	'schema_org'           => [ 'Schema', $t( 'Organization-schema op de homepage', 'Organization schema on the homepage' ) ],
	'wikipedia'            => [ 'Wikipedia', $t( 'Wikipedia-artikel over het bedrijf', 'Wikipedia article about the company' ) ],
	'fresh'                => [ $t( 'Actueel', 'Fresh' ), $t( 'Sitemap bijgewerkt in de laatste 90 dagen', 'Sitemap updated in the last 90 days' ) ],
	'lang'                 => [ $t( 'Taal', 'Lang' ), $t( 'Nederlandse versie (lijst Nederland) of meerdere talen (internationaal)', 'Dutch version (Dutch list) or several languages (international list)' ) ],
];

/* One check cell. On small screens it shows as a pill: icon plus data-label. */
$cell = static function ( $v, string $label, string $extra = '' ) {
	$cls = true === $v ? 'bv-gi__y' : ( false === $v ? 'bv-gi__n' : 'bv-gi__u' );
	$ico = true === $v ? '&#10003;' : ( false === $v ? '&#10005;' : '-' );
	return '<td class="bv-gi__pill ' . $cls . $extra . '" data-label="' . esc_attr( $label ) . '">' . $ico . '</td>';
};

/* Labels for the small-screen cards (the table header covers wide screens). */
$ml = [
	'chg'   => $t( 'Week', 'Week' ),
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
	'nl'   => $t( 'Top 100 Nederland', 'Top 100 Netherlands' ),
	'intl' => $t( 'Top 100 internationaal', 'Top 100 international' ),
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
	'byChange' => $t( 'Verschil met vorige week', 'Change since last week' ),
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
	'labels'   => array_map( static fn( $c ) => $c[1], $bots + $cols ) + [
		'bot_GPTBot'       => $t( 'GPTBot (training van OpenAI) mag de site lezen', 'GPTBot (OpenAI training) may read the site' ),
		'bot_ChatGPT-User' => $t( 'ChatGPT mag pagina\'s openen op verzoek van gebruikers (ChatGPT-User)', 'ChatGPT may open pages for users (ChatGPT-User)' ),
		'bot_ClaudeBot'    => $t( 'ClaudeBot (training van Anthropic) mag de site lezen', 'ClaudeBot (Anthropic training) may read the site' ),
		'schema_sameas'    => $t( 'Links naar profielen elders (sameAs)', 'Links to profiles elsewhere (sameAs)' ),
		'wikidata'         => $t( 'Wikidata-item met de juiste website', 'Wikidata item with the right website' ),
		'llms'             => $t( 'llms.txt aanwezig', 'llms.txt present' ),
	],
	'advice'   => [
		'bot_OAI-SearchBot'    => $t( 'Sta OAI-SearchBot toe in robots.txt en in je firewall. Zonder deze crawler verschijn je niet als bron in ChatGPT search.', 'Allow OAI-SearchBot in robots.txt and your firewall. Without it you do not appear as a source in ChatGPT search.' ),
		'bot_ChatGPT-User'     => $t( 'Sta ChatGPT-User toe, zodat ChatGPT je pagina\'s kan openen als een gebruiker erom vraagt.', 'Allow ChatGPT-User so ChatGPT can open your pages when a user asks for them.' ),
		'bot_GPTBot'           => $t( 'GPTBot verzamelt trainingsdata. Toestaan is een keuze; het staat los van ChatGPT search.', 'GPTBot collects training data. Allowing it is a choice; it is separate from ChatGPT search.' ),
		'bot_Claude-SearchBot' => $t( 'Sta Claude-SearchBot toe, zodat Claude je pagina\'s kan vinden en citeren.', 'Allow Claude-SearchBot so Claude can find and cite your pages.' ),
		'bot_ClaudeBot'        => $t( 'ClaudeBot verzamelt trainingsdata voor Anthropic. Toestaan is een keuze.', 'ClaudeBot collects training data for Anthropic. Allowing it is a choice.' ),
		'bot_PerplexityBot'    => $t( 'Sta PerplexityBot toe, anders kan Perplexity je niet als bron tonen.', 'Allow PerplexityBot, or Perplexity cannot show you as a source.' ),
		'bot_Google-Extended'  => $t( 'Google-Extended bepaalt of Gemini je content mag gebruiken. Het heeft geen invloed op Google Zoeken.', 'Google-Extended controls whether Gemini may use your content. It does not affect Google Search.' ),
		'bot_Bingbot'          => $t( 'Sta Bingbot toe. Copilot gebruikt de index van Bing.', 'Allow Bingbot. Copilot uses the Bing index.' ),
		'html'                 => $t( 'Zorg dat de tekst in de HTML van de server staat. AI-crawlers voeren meestal geen JavaScript uit.', 'Make sure your text is in the server HTML. AI crawlers usually do not run JavaScript.' ),
		'schema_org'           => $t( 'Voeg Organization-schema toe aan je homepage met naam, logo, adres en links naar je profielen.', 'Add Organization schema to your homepage with name, logo, address and links to your profiles.' ),
		'schema_sameas'        => $t( 'Voeg sameAs-links toe naar LinkedIn, Wikidata en andere profielen, zodat AI je bedrijf goed herkent.', 'Add sameAs links to LinkedIn, Wikidata and other profiles so AI recognizes your company.' ),
		'wikidata'             => $t( 'Maak of verbeter een Wikidata-item met je officiële website. AI-systemen gebruiken het om entiteiten te herkennen.', 'Create or improve a Wikidata item with your official website. AI systems use it to recognize entities.' ),
		'wikipedia'            => $t( 'Een Wikipedia-artikel kun je niet zelf schrijven. Verdien eerst vermeldingen in onafhankelijke media.', 'You cannot write a Wikipedia article yourself. Earn coverage in independent media first.' ),
		'fresh'                => $t( 'Werk je belangrijkste pagina\'s bij en laat je sitemap de juiste lastmod-datum tonen.', 'Update your key pages and make your sitemap show the right lastmod date.' ),
		'lang'                 => $t( 'Publiceer een Nederlandse versie voor Nederlandse kopers, met hreflang. Bij Nederlandse vragen kiest AI vooral Nederlandstalige bronnen.', 'Publish language versions for your markets, with hreflang. AI mostly picks sources in the language of the question.' ),
		'llms'                 => $t( 'Een llms.txt is optioneel. Er is weinig bewijs dat AI-crawlers het gebruiken, maar het kost weinig.', 'An llms.txt is optional. There is little evidence AI crawlers use it, but it costs little.' ),
	],
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
		[ $t( '200 vaste bedrijven.', '200 fixed companies.' ), $t( '100 Nederlandse B2B-bedrijven en 100 internationale B2B-softwarebedrijven. Niemand kan betalen voor een plek of een hogere score.', '100 Dutch B2B companies and 100 international B2B software companies. Nobody can pay for a place or a higher score.' ) ],
	];
	if ( $ai_cols ) {
		$how[] = [ $t( 'AI-score.', 'AI score.' ), sprintf( $t( 'Elke maandag stelt de site per categorie een koopvraag aan %s, met zoeken op internet aan. We tellen hoe vaak ze het bedrijf noemen (80%%) en de website als bron geven (20%%).', 'Every Monday the site asks %s one buyer question per category, with web search on. We count how often they name the company (80%%) and cite its website (20%%).' ), $engs ) ];
	}
	$how[] = [ $t( 'Websitescore.', 'Website score.' ), $t( 'Zeven checks op de eigen website: AI-crawlers in robots.txt, tekst zonder JavaScript, gestructureerde data, Wikidata en Wikipedia, actualiteit, taal en llms.txt.', 'Seven checks on the company website: AI crawlers in robots.txt, text without JavaScript, structured data, Wikidata and Wikipedia, freshness, language and llms.txt.' ) ];
	$how[] = $ai_cols
		? [ $t( 'Totaalscore.', 'Total score.' ), $t( 'Het gemiddelde van de AI-score en de websitescore, van 0 tot 100. Elke maandag opnieuw gemeten, met het verschil met vorige week.', 'The average of the AI score and the website score, from 0 to 100. Measured again every Monday, with the change since last week.' ) ]
		: [ $t( 'Score.', 'Score.' ), $t( 'De websitescore, van 0 tot 100. Elke maandag opnieuw gemeten, met het verschil met vorige week.', 'The website score, from 0 to 100. Measured again every Monday, with the change since last week.' ) ];
	?>
	<details class="bv-gi__how">
		<summary><?php echo esc_html( $t( 'Zo werkt de ranking', 'How the ranking works' ) ); ?></summary>
		<ol>
			<?php foreach ( $how as [ $head, $body ] ) : ?>
				<li><strong><?php echo esc_html( $head ); ?></strong> <?php echo esc_html( $body ); ?></li>
			<?php endforeach; ?>
		</ol>
		<p><a href="#<?php echo esc_attr( $t( 'methode', 'methodology' ) ); ?>"><?php echo esc_html( $t( 'Lees de volledige methode en bronnen', 'Read the full method and sources' ) ); ?></a></p>
	</details>

	<div class="bv-gi__filters" hidden>
		<input type="search" class="bv-gi__q" placeholder="<?php echo esc_attr( $i18n['search'] ); ?>" aria-label="<?php echo esc_attr( $i18n['search'] ); ?>">
		<label class="bv-gi__field"><span><?php echo esc_html( $t( 'Sector', 'Sector' ) ); ?></span>
			<select class="bv-gi__sector"><option value=""><?php echo esc_html( $i18n['allSect'] ); ?></option></select>
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
	</div>

	<?php foreach ( $lists as $key => $label ) :
		$rows = bv_gi_ranked( $key );
		$s    = $stats( $rows );
		?>
		<section class="bv-gi__list" id="<?php echo esc_attr( $id . '-' . $key ); ?>" data-list="<?php echo esc_attr( $key ); ?>" role="tabpanel" <?php echo $key === $default ? '' : 'hidden'; ?>>
			<h3 class="bv-gi__title"><?php echo esc_html( 'B2B GEO Index: ' . $label ); ?></h3>
			<?php if ( ! $s ) : ?>
				<p class="bv-gi__empty"><?php echo esc_html( $t( 'De eerste meting loopt nu. De ranglijst verschijnt hier zodra alle bedrijven zijn gemeten.', 'The first measurement is running. The ranking appears here once every company has been measured.' ) ); ?></p>
			<?php else : ?>
				<div class="bv-gi__stats">
					<div><strong><?php echo (int) $s['n']; ?></strong><span><?php echo esc_html( $t( 'bedrijven gemeten', 'companies measured' ) ); ?></span></div>
					<div><strong><?php echo (int) $s['avg']; ?></strong><span><?php echo esc_html( $t( 'gemiddelde score van 100', 'average score out of 100' ) ); ?></span></div>
					<?php if ( null !== $s['ai'] ) : ?><div><strong><?php echo (int) $s['ai']; ?>%</strong><span><?php echo esc_html( $t( 'genoemd door AI-assistenten', 'named by AI assistants' ) ); ?></span></div><?php endif; ?>
					<div><strong><?php echo (int) $s['chatgpt']; ?>%</strong><span><?php echo esc_html( $t( 'laat ChatGPT search toe', 'allow ChatGPT search' ) ); ?></span></div>
					<div><strong><?php echo (int) $s['html']; ?>%</strong><span><?php echo esc_html( $t( 'leesbaar zonder JavaScript', 'readable without JavaScript' ) ); ?></span></div>
					<div><strong><?php echo (int) $s['wikipedia']; ?>%</strong><span><?php echo esc_html( $t( 'heeft een Wikipedia-artikel', 'have a Wikipedia article' ) ); ?></span></div>
				</div>
				<div class="bv-gi__scroll">
					<table class="bv-gi__table">
						<caption class="screen-reader-text"><?php echo esc_html( 'B2B GEO Index: ' . $label ); ?></caption>
						<thead><tr>
							<th scope="col" rowspan="2">#</th>
							<th scope="col" rowspan="2"><?php echo esc_html( $t( 'Bedrijf', 'Company' ) ); ?></th>
							<th scope="col" rowspan="2"><?php echo esc_html( $t( 'Score', 'Score' ) ); ?></th>
							<th scope="col" rowspan="2" title="<?php echo esc_attr( $i18n['byChange'] ); ?>"><?php echo esc_html( $t( 'Week', 'Week' ) ); ?></th>
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
							$chg = null === $r['change'] ? '' : (string) $r['change'];
							?>
							<tr data-name="<?php echo esc_attr( strtolower( $r['name'] . ' ' . $r['domain'] ) ); ?>" data-sector="<?php echo esc_attr( $sector ); ?>" data-score="<?php echo (int) $r['score']; ?>" data-change="<?php echo esc_attr( $chg ); ?>" data-ai="<?php echo null === $r['ai'] ? '' : (int) $r['ai']; ?>" data-ready="<?php echo (int) $r['ready']; ?>"<?php echo $attrs; ?>>
								<td class="bv-gi__rank<?php echo $r['rank'] <= 3 ? ' bv-gi__rank--top' : ''; ?>"><?php echo (int) $r['rank']; ?></td>
								<td class="bv-gi__co"><strong><?php echo esc_html( $r['name'] ); ?></strong><span><?php echo esc_html( $r['domain'] ); ?> &middot; <?php echo esc_html( $sector ); ?></span></td>
								<td class="bv-gi__score"><span class="bv-gi__bar" style="--s:<?php echo (int) $r['score']; ?>"><?php echo (int) $r['score']; ?></span></td>
								<td class="bv-gi__chg" data-label="<?php echo esc_attr( $ml['chg'] ); ?>"><?php
									if ( '' === $chg || '0' === $chg ) {
										echo '<span class="bv-gi__flat">' . ( '' === $chg ? esc_html( $t( 'nieuw', 'new' ) ) : '0' ) . '</span>';
									} else {
										echo '<span class="' . ( $r['change'] > 0 ? 'bv-gi__up">+' : 'bv-gi__down">' ) . (int) $r['change'] . '</span>';
									}
								?></td>
								<?php if ( $ai_cols ) : ?><td class="bv-gi__num" data-label="<?php echo esc_attr( $ml['ai'] ); ?>"><?php echo null === $r['ai'] ? '-' : (int) $r['ai']; ?></td><?php endif; ?>
								<td class="bv-gi__num" data-label="<?php echo esc_attr( $ml['ready'] ); ?>"><?php echo (int) $r['ready']; ?></td>
								<?php foreach ( $ai_cols as $k => $c ) { echo $cell( $vals[ $k ], $c[0], ' bv-gi__ai' ); } // phpcs:ignore ?>
								<td class="bv-gi__crawl <?php echo 5 === $open ? 'bv-gi__y' : ( 0 === $open ? 'bv-gi__n' : 'bv-gi__p' ); ?>" data-label="<?php echo esc_attr( $ml['crawl'] ); ?>"><?php echo (int) $open; ?>/5</td>
								<?php foreach ( $cols as $k => $c ) { echo $cell( $r['checks'][ $k ] ?? null, $c[0] ); } // phpcs:ignore ?>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				<button type="button" class="bv-gi__next" hidden></button>
			<?php endif; ?>
		</section>
	<?php endforeach; ?>

	<p class="bv-gi__meta"><?php
		echo esc_html( $date
			? sprintf( $t( 'Laatst gemeten: %s. Elke maandag opnieuw gemeten.', 'Last measured: %s. Measured again every Monday.' ), $date )
			: $t( 'Elke maandag opnieuw gemeten.', 'Measured again every Monday.' ) );
	?></p>

	<div class="bv-gi__legend">
		<span><strong><?php echo esc_html( $t( 'Score', 'Score' ) ); ?></strong> <?php echo esc_html( $ai_cols ? $t( 'helft AI-score, helft websitescore', 'half AI score, half website score' ) : $t( 'websitescore', 'website score' ) ); ?></span>
		<?php if ( $ai_cols ) : ?><span><strong>AI</strong> <?php echo esc_html( $t( 'hoe vaak AI-assistenten het bedrijf noemen bij de vraag over zijn categorie', 'how often AI assistants name the company for the question about its category' ) ); ?></span><?php endif; ?>
		<span><strong>Crawlers</strong> <?php echo esc_html( $t( 'aantal van 5 AI-crawlers dat de site mag lezen', 'number of 5 AI crawlers allowed to read the site' ) ); ?></span>
		<span><strong>&#10003;</strong> <?php echo esc_html( $t( 'in orde', 'in place' ) ); ?>, <strong>&#10005;</strong> <?php echo esc_html( $t( 'ontbreekt', 'missing' ) ); ?>, <strong>-</strong> <?php echo esc_html( $t( 'niet te meten', 'could not measure' ) ); ?></span>
		<?php foreach ( $cols as [ $short, $full ] ) : ?>
			<span><strong><?php echo esc_html( $short ); ?></strong> <?php echo esc_html( $full ); ?></span>
		<?php endforeach; ?>
	</div>

	<div class="bv-gi__check" id="<?php echo esc_attr( $id ); ?>-check">
		<h3><?php echo esc_html( $t( 'Check je eigen bedrijf', 'Check your own company' ) ); ?></h3>
		<p><?php echo esc_html( $t( 'Vul je domein in en zie in 30 seconden de websitescore van je bedrijf, gemeten op dezelfde manier als de bedrijven in de index.', 'Enter your domain and see your website score in 30 seconds, measured the same way as the companies in the index.' ) ); ?></p>
		<form class="bv-gi__form" novalidate>
			<label class="screen-reader-text" for="<?php echo esc_attr( $id ); ?>-domain"><?php echo esc_html( $t( 'Domein', 'Domain' ) ); ?></label>
			<input type="text" id="<?php echo esc_attr( $id ); ?>-domain" name="domain" inputmode="url" autocomplete="url" placeholder="<?php echo esc_attr( $t( 'jouwbedrijf.nl', 'yourcompany.com' ) ); ?>" required>
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
