<?php
/**
 * Server render for one sector of the B2B GEO Index (1.30.0).
 * Everything comes from the stored monthly measurement, so each sector page
 * shows its own numbers, companies, buyer questions and sources.
 *
 * @var array    $attributes
 * @var string   $content
 * @var WP_Block $block
 */
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'bv_gi_sector_data' ) || ! function_exists( 'bv_gi_texts' ) ) {
	return;
}

$key = sanitize_key( (string) ( $attributes['sector'] ?? '' ) );
$nl  = function_exists( 'bv_lang' ) && 'nl' === bv_lang();
$t   = static fn( $nl_text, $en_text ) => $nl ? $nl_text : $en_text;
$e   = static fn( $s ) => esc_html( (string) $s );

$main  = $nl ? 'nl' : 'intl';
$other = $nl ? 'intl' : 'nl';
$s     = bv_gi_sector_data( $main, $key );
$o     = bv_gi_sector_data( $other, $key );

$wrapper = get_block_wrapper_attributes( [ 'class' => 'bv-gs' ] );

if ( ! $s ) {
	printf( '<div %s><p class="bv-gip-note">%s</p></div>', $wrapper, $e( $t( 'De cijfers voor deze sector verschijnen na de eerstvolgende meting.', 'The numbers for this sector appear after the next measurement.' ) ) ); // phpcs:ignore
	return;
}

$names  = (array) $s['names'];
$name   = $names[ $nl ? 0 : 1 ] ?? $key;
$phrase = function_exists( 'bv_gi_sector_phrase' ) ? bv_gi_sector_phrase( $key, $nl ) : $name;
$date   = $s['measured'] ? bv_gi_pdf_date( (int) $s['measured'], $nl ) : '';
$tx     = bv_gi_texts( $nl, $main );
$labels = (array) $tx['labels'];
$advice = (array) $tx['advice'];
$listn  = static fn( string $l ) => 'nl' === $l ? $t( 'Nederland', 'Netherlands' ) : $t( 'internationaal', 'international' );
$src    = $date ? sprintf( $t( 'Bron: B2B GEO Index, meting van %s', 'Source: B2B GEO Index, measured %s' ), $date ) : $t( 'Bron: B2B GEO Index', 'Source: B2B GEO Index' );
$pct    = static fn( array $yn ) => $yn[1] ? (int) round( 100 * $yn[0] / $yn[1] ) : null;
$leader = $s['rows'][0] ?? null;
$has_ai = (bool) array_filter( array_merge( $s['rows'], $o ? $o['rows'] : [] ), static fn( $r ) => null !== $r['ai'] && empty( $r['ai_low'] ) );
$index  = get_page_by_path( $nl ? 'nl/b2b-geo-index' : 'b2b-geo-index' );
$iurl   = $index ? get_permalink( $index ) : home_url( $nl ? '/nl/b2b-geo-index/' : '/b2b-geo-index/' );
$check  = get_page_by_path( $nl ? 'nl/ai-check' : 'ai-visibility-check' );
$curl   = ( $check && 'publish' === $check->post_status ) ? get_permalink( $check ) : $iurl . '#check';
$pdf    = static fn( string $l ) => add_query_arg( [ 'list' => $l, 'sector' => $key, 'lang' => $nl ? 'nl' : 'en' ], rest_url( 'bv/v1/geo-pdf' ) );

/* The check this sector misses most, as a share of complete measurements. */
$worst = null;
foreach ( (array) $s['weak'] as $k ) {
	$p = $pct( $s['pass'][ $k ]['sector'] ?? [ 0, 0 ] );
	if ( null !== $p ) {
		$worst = [ $k, 100 - $p ];
		break;
	}
}

$table = static function ( array $d ) use ( $t, $e, $has_ai ): string {
	$h  = '<figure class="wp-block-table bv-gip-table bv-gs__rank"><table><thead><tr>';
	$h .= '<th>' . $e( $t( 'Plaats in sector', 'Place in sector' ) ) . '</th><th>' . $e( $t( 'Bedrijf', 'Company' ) ) . '</th><th>' . $e( $t( 'Score', 'Score' ) ) . '</th>';
	if ( $has_ai ) {
		$h .= '<th>' . $e( $t( 'AI-score', 'AI score' ) ) . '</th>';
	}
	$h .= '<th>' . $e( $t( 'Websitescore', 'Website score' ) ) . '</th></tr></thead><tbody>';
	$place = 0;
	$last  = null;
	foreach ( $d['rows'] as $i => $r ) {
		if ( $r['score'] !== $last ) {
			$place = $i + 1;
			$last  = $r['score'];
		}
		$part = 'ok' !== ( $r['status'] ?? 'ok' ) ? ' <span class="bv-gs__part">' . $e( $t( '(onvolledig gemeten)', '(partly measured)' ) ) . '</span>' : '';
		$h   .= '<tr><td>' . (int) $place . '</td><td><strong>' . $e( $r['name'] ) . '</strong><br><span class="bv-gs__dom">' . $e( $r['domain'] ) . '</span>' . $part . '</td><td>' . (int) $r['score'] . ( $r['margin'] ? ' &plusmn;' . (int) $r['margin'] : '' ) . '</td>';
		if ( $has_ai ) {
			$h .= '<td>' . ( null === $r['ai'] || ! empty( $r['ai_low'] ) ? $e( $t( 'n.b.', 'n/a' ) ) : (int) $r['ai'] ) . '</td>';
		}
		$h .= '<td>' . (int) $r['ready'] . '</td></tr>';
	}
	return $h . '</tbody></table></figure>';
};

ob_start();
?>
<div <?php echo $wrapper; // phpcs:ignore ?>>

	<div class="wp-block-group bv-gip-sec bv-gs__facts">
		<div class="wp-block-group bv-gip-head">
			<p class="bv-gip-eyebrow"><?php echo $e( $t( 'De cijfers', 'The numbers' ) ); ?></p>
			<h2 class="wp-block-heading"><?php echo $e( sprintf( $t( 'Hoe klaar zijn bedrijven %s voor AI-zoekmachines?', 'How ready are companies %s for AI search?' ), $phrase ) ); ?></h2>
			<?php if ( null !== $s['avg'] ) : ?>
				<p class="bv-capsule has-lg-font-size" style="font-weight:500"><?php echo $e( sprintf( $t( 'De gemiddelde websitescore %1$s is %2$d van 100, tegen %3$d voor alle %4$d bedrijven in de lijst %5$s, gemeten op %6$s.', 'The average website score %1$s is %2$d out of 100, against %3$d for all %4$d companies in the %5$s list, measured on %6$s.' ), $phrase, (int) $s['avg'], (int) $s['avg_list'], (int) $s['count'], $listn( $main ), $date ) ); ?></p>
			<?php endif; ?>
		</div>
		<div class="wp-block-group bv-gip-grid bv-gip-grid--3">
			<?php if ( null !== $s['avg'] ) : ?>
				<div class="wp-block-group bv-gip-card bv-gip-card--stat">
					<p class="bv-gip-num"><?php echo (int) $s['avg']; ?> <?php echo $e( $t( 'tegen', 'vs' ) ); ?> <?php echo (int) $s['avg_list']; ?></p>
					<p><?php echo $e( sprintf( $t( 'gemiddelde websitescore %1$s tegen de hele lijst %2$s, op basis van %3$d volledige metingen.', 'average website score %1$s against the whole %2$s list, based on %3$d complete measurements.' ), $phrase, $listn( $main ), (int) $s['n_ok'] ) ); ?></p>
					<p class="bv-gip-src"><?php echo $e( $src ); ?></p>
				</div>
			<?php endif; ?>
			<?php if ( $leader ) : ?>
				<div class="wp-block-group bv-gip-card bv-gip-card--stat">
					<p class="bv-gip-num"><?php echo $e( $leader['name'] ); ?></p>
					<p><?php echo $e( sprintf( $t( 'leidt de sector in de lijst %1$s met een score van %2$d.', 'leads the sector in the %1$s list with a score of %2$d.' ), $listn( $main ), (int) $leader['score'] ) ); ?></p>
					<p class="bv-gip-src"><?php echo $e( $src ); ?></p>
				</div>
			<?php endif; ?>
			<?php if ( $worst ) : ?>
				<div class="wp-block-group bv-gip-card bv-gip-card--stat">
					<p class="bv-gip-num"><?php echo (int) $worst[1]; ?>%</p>
					<p><?php echo $e( sprintf( $t( 'van de bedrijven mist dit punt: %s. Geen check kost deze sector meer punten.', 'of the companies miss this point: %s. No check costs this sector more points.' ), mb_strtolower( (string) ( $labels[ $worst[0] ] ?? $worst[0] ) ) ) ); ?></p>
					<p class="bv-gip-src"><?php echo $e( $src ); ?></p>
				</div>
			<?php endif; ?>
		</div>
		<?php if ( null !== $s['avg_other'] && $o ) : ?>
			<p class="bv-gip-note"><?php echo $e( sprintf( $t( 'Dezelfde sector in de lijst %1$s scoort gemiddeld %2$d.', 'The same sector in the %1$s list averages %2$d.' ), $listn( $other ), (int) $s['avg_other'] ) ); ?></p>
		<?php endif; ?>
	</div>

	<div class="wp-block-group bv-gip-sec bv-gs__ranking">
		<div class="wp-block-group bv-gip-head">
			<p class="bv-gip-eyebrow"><?php echo $e( $t( 'Ranglijst', 'Ranking' ) ); ?></p>
			<h2 class="wp-block-heading"><?php echo $e( sprintf( $t( 'Welke bedrijven %1$s scoren het best? Lijst %2$s', 'Which companies %1$s score best? %2$s list' ), $phrase, $listn( $main ) ) ); ?></h2>
		</div>
		<?php echo $table( $s ); // phpcs:ignore ?>
		<?php if ( $o ) : ?>
			<h3 class="wp-block-heading"><?php echo $e( sprintf( $t( 'Lijst %s', '%s list' ), $listn( $other ) ) ); ?></h3>
			<?php echo $table( $o ); // phpcs:ignore ?>
		<?php endif; ?>
		<p class="bv-gip-note"><?php echo $e( $has_ai
			? $t( 'Score is het gemiddelde van de AI-score en de websitescore. De marge (±) is de foutmarge van 95% van het AI-deel.', 'Score is the average of the AI score and the website score. The margin (±) is the 95% margin of error of the AI part.' )
			: $t( 'Score is hier de websitescore. Zodra de AI-meting genoeg antwoorden heeft, telt de AI-score voor de helft mee.', 'Score is the website score here. Once the AI measurement has enough answers, the AI score counts for half.' ) ); ?></p>
	</div>

	<div class="wp-block-group bv-gip-sec bv-gs__checks">
		<div class="wp-block-group bv-gip-head">
			<p class="bv-gip-eyebrow"><?php echo $e( $t( 'Websitechecks', 'Website checks' ) ); ?></p>
			<h2 class="wp-block-heading"><?php echo $e( sprintf( $t( 'Welke checks halen bedrijven %s?', 'Which checks do companies %s pass?' ), $phrase ) ); ?></h2>
		</div>
		<figure class="wp-block-table bv-gip-table"><table><thead><tr>
			<th><?php echo $e( $t( 'Check', 'Check' ) ); ?></th>
			<th><?php echo $e( $t( 'Deze sector', 'This sector' ) ); ?></th>
			<th><?php echo $e( sprintf( $t( 'Hele lijst %s', 'Whole %s list' ), $listn( $main ) ) ); ?></th>
		</tr></thead><tbody>
			<?php foreach ( (array) $s['pass'] as $k => $p ) :
				$ps = $pct( $p['sector'] );
				$pl = $pct( $p['list'] );
				if ( null === $ps ) {
					continue;
				}
				?>
				<tr><td><?php echo $e( $labels[ $k ] ?? $k ); ?></td><td><?php echo (int) $ps; ?>% <span class="bv-gs__dom">(<?php echo (int) $p['sector'][0]; ?>/<?php echo (int) $p['sector'][1]; ?>)</span></td><td><?php echo null === $pl ? '' : (int) $pl . '%'; ?></td></tr>
			<?php endforeach; ?>
		</tbody></table></figure>
	</div>

	<?php if ( $s['weak'] ) : ?>
		<div class="wp-block-group bv-gip-sec bv-gs__weak">
			<div class="wp-block-group bv-gip-head">
				<p class="bv-gip-eyebrow"><?php echo $e( $t( 'Verbeterpunten', 'Improvements' ) ); ?></p>
				<h2 class="wp-block-heading"><?php echo $e( sprintf( $t( 'Waar verliezen bedrijven %s de meeste punten?', 'Where do companies %s lose most points?' ), $phrase ) ); ?></h2>
			</div>
			<ol class="wp-block-list">
				<?php foreach ( (array) $s['weak'] as $k ) :
					$miss = $pct( $s['pass'][ $k ]['sector'] ?? [ 0, 0 ] );
					?>
					<li><strong><?php echo $e( $labels[ $k ] ?? $k ); ?></strong><?php echo null === $miss ? '' : $e( sprintf( $t( ' (%d%% heeft dit niet).', ' (%d%% miss this).' ), 100 - $miss ) ); ?> <?php echo $e( $advice[ $k ] ?? '' ); ?></li>
				<?php endforeach; ?>
			</ol>
		</div>
	<?php endif; ?>

	<?php
	$cats = [];
	foreach ( (array) ( bv_gi_companies()[ $main ] ?? [] ) as $c ) {
		if ( $c['sector'] === $key && ! empty( $c['cat'] ) ) {
			$cats[ $c['cat'] ] = true;
		}
	}
	if ( $cats && function_exists( 'bv_gi_ai_prompt' ) ) :
		?>
		<div class="wp-block-group bv-gip-sec bv-gs__questions">
			<div class="wp-block-group bv-gip-head">
				<p class="bv-gip-eyebrow"><?php echo $e( $t( 'Koopvragen', 'Buyer questions' ) ); ?></p>
				<h2 class="wp-block-heading"><?php echo $e( sprintf( $t( 'Welke vragen stelt de index aan AI %s?', 'Which questions does the index ask AI %s?' ), $phrase ) ); ?></h2>
				<p><?php echo $e( $t( 'Elke koopcategorie krijgt vijf vragen met een andere koopreden. Dit is per categorie de vraag naar een aanrader en de vraag naar een shortlist:', 'Each buying category gets five questions with a different buying reason. Per category, these are the question for a recommendation and the question for a shortlist:' ) ); ?></p>
			</div>
			<ul class="wp-block-list">
				<?php foreach ( array_keys( $cats ) as $cat ) : ?>
					<li><strong><?php echo $e( ucfirst( $cat ) ); ?>:</strong> &ldquo;<?php echo $e( bv_gi_ai_prompt( $main, $cat, 1 ) ); ?>&rdquo; <?php echo $e( $t( 'en', 'and' ) ); ?> &ldquo;<?php echo $e( bv_gi_ai_prompt( $main, $cat, 2 ) ); ?>&rdquo;</li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>

	<?php if ( $s['src'] ) : ?>
		<div class="wp-block-group bv-gip-sec bv-gs__sources">
			<div class="wp-block-group bv-gip-head">
				<p class="bv-gip-eyebrow"><?php echo $e( $t( 'Bronnen', 'Sources' ) ); ?></p>
				<h2 class="wp-block-heading"><?php echo $e( sprintf( $t( 'Welke websites citeert AI %s?', 'Which websites does AI cite %s?' ), $phrase ) ); ?></h2>
				<p><?php echo $e( sprintf( $t( 'Aandeel van de %d antwoorden waarin de website als bron stond. Hier genoemd worden helpt om zelf genoemd te worden.', 'Share of the %d answers that cited the website as a source. Being mentioned here helps you get named.' ), (int) $s['ans'] ) ); ?></p>
			</div>
			<ol class="wp-block-list">
				<?php foreach ( $s['src'] as $x ) : ?>
					<li><?php echo $e( $x['host'] ); ?>: <?php echo (int) $x['pct']; ?>%</li>
				<?php endforeach; ?>
			</ol>
		</div>
	<?php endif; ?>

	<div class="wp-block-group bv-gip-sec bv-gs__next">
		<div class="wp-block-group bv-gip-grid bv-gip-grid--2">
			<div class="wp-block-group bv-gip-card">
				<h3 class="wp-block-heading"><?php echo $e( $t( 'Sectorrapport als PDF', 'Sector report as PDF' ) ); ?></h3>
				<p><?php echo $e( $t( 'De volledige analyse van deze sector, met ranglijst, checks, verbeterpunten en bronnen.', 'The full analysis of this sector, with ranking, checks, improvements and sources.' ) ); ?></p>
				<p><a href="<?php echo esc_url( $pdf( $main ) ); ?>" rel="nofollow"><?php echo $e( sprintf( $t( 'Download lijst %s (PDF)', 'Download %s list (PDF)', ), $listn( $main ) ) ); ?></a><?php if ( $o ) : ?><br><a href="<?php echo esc_url( $pdf( $other ) ); ?>" rel="nofollow"><?php echo $e( sprintf( $t( 'Download lijst %s (PDF)', 'Download %s list (PDF)' ), $listn( $other ) ) ); ?></a><?php endif; ?></p>
			</div>
			<div class="wp-block-group bv-gip-card">
				<h3 class="wp-block-heading"><?php echo $e( $t( 'Hoe scoort jouw bedrijf?', 'How does your company score?' ) ); ?></h3>
				<p><?php echo $e( $t( 'Doe de gratis AI-check en zie in 30 seconden je websitescore en je verschil met de leider van deze sector.', 'Run the free AI check and see your website score and your gap with the leader of this sector in 30 seconds.' ) ); ?></p>
				<p><a href="<?php echo esc_url( $curl ); ?>"><?php echo $e( $t( 'Naar de gratis AI-check', 'Go to the free AI check' ) ); ?></a><br><a href="<?php echo esc_url( $iurl ); ?>"><?php echo $e( $t( 'Bekijk de hele B2B GEO Index', 'See the whole B2B GEO Index' ) ); ?></a></p>
			</div>
		</div>
		<?php
		$all = function_exists( 'bv_gi_sector_pages' ) ? bv_gi_sector_pages( $nl ? 'nl' : 'en' ) : [];
		unset( $all[ $key ] );
		if ( $all ) :
			?>
			<nav class="bv-gs__others" aria-label="<?php echo esc_attr( $t( 'Andere sectoren', 'Other sectors' ) ); ?>">
				<h3 class="wp-block-heading"><?php echo $e( $t( 'Andere sectoren in de index', 'Other sectors in the index' ) ); ?></h3>
				<ul class="bv-gs__chips">
					<?php foreach ( $all as $page ) : ?>
						<li><a href="<?php echo esc_url( $page['url'] ); ?>"><?php echo $e( $page['name'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</nav>
		<?php endif; ?>
	</div>
</div>
<?php
echo ob_get_clean(); // phpcs:ignore
