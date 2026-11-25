<?php
/**
 * B2B GEO Index: live method details for the method page (1.24.0).
 *
 * Shortcode [bv_geo_method] prints what the last measurement actually used,
 * straight from the stored results and settings, so the method page never
 * goes out of date: the five questions per list, the assistants and models,
 * the number of categories, questions, rounds and answers, the dates, and
 * the change log of the method.
 *
 * @package BoomingVenture
 */

defined( 'ABSPATH' ) || exit;

/** Changes to the method, newest first: [ date, version, Dutch text, English text ]. */
function bv_gi_method_log(): array {
	return [
		[ '2026-12-07', '2', 'Vijf koopvragen per categorie in plaats van één, een tweede ronde waar de uitkomst onzeker is, een foutmarge van 95% bij elk getal, groepen buiten de top 10, verschillen met vorige maand alleen als ze groter zijn dan de marge, nieuwe maten (zichtbaarheid, share of voice, eerst genoemd, toon, bronaandeel), bronnen per sector en tien bedrijven per sector (400 in totaal).', 'Five buyer questions per category instead of one, a second round where the result is uncertain, a 95% margin of error on every number, groups outside the top 10, changes since last month only when larger than the margin, new measures (visibility, share of voice, first mention, tone, citation share), sources per sector and ten companies per sector (400 in total).' ],
		[ '2026-11-02', '1', 'Eén koopvraag per categorie per maand aan vier AI-assistenten, vijf bedrijven per sector (200 in totaal), zeven websitechecks.', 'One buyer question per category per month to four AI assistants, five companies per sector (200 in total), seven website checks.' ],
	];
}

add_shortcode( 'bv_geo_method', static function ( $atts ) {
	$atts = shortcode_atts( [ 'lang' => '' ], (array) $atts );
	$nl   = 'nl' === $atts['lang'] || ( '' === $atts['lang'] && function_exists( 'bv_post_lang' ) && 'nl' === bv_post_lang() );
	$t    = static fn( $a, $b ) => $nl ? $a : $b;
	$e    = static fn( $s ) => esc_html( (string) $s );
	$res  = (array) get_option( 'bv_gi_ai_results', [] );
	$co   = bv_gi_companies();
	$cats = function_exists( 'bv_gi_ai_categories' ) ? count( bv_gi_ai_categories() ) : 0;
	$v    = (int) ( $res['variants'] ?? ( function_exists( 'bv_gi_ai_variants' ) ? bv_gi_ai_variants() : 5 ) );
	$r    = (int) ( $res['runs'] ?? ( function_exists( 'bv_gi_ai_runs' ) ? bv_gi_ai_runs() : 2 ) );
	$ans  = array_sum( array_map( 'intval', (array) ( $res['ok'] ?? [] ) ) );
	$done = (int) ( $res['completed'] ?? 0 );
	$next = function_exists( 'bv_gi_next_monthly' ) ? bv_gi_next_monthly() : 0;
	$eng  = function_exists( 'bv_gi_ai_engines' ) ? bv_gi_ai_engines() : [];
	$used = $res['engines'] ?? ( function_exists( 'bv_gi_ai_active' ) ? bv_gi_ai_active() : [] );
	$rows = [];

	$rows[] = [ $t( 'Bedrijven', 'Companies' ), sprintf( $t( '%1$d Nederlandse en %2$d internationale, in %3$d sectoren', '%1$d Dutch and %2$d international, in %3$d sectors' ), count( (array) ( $co['nl'] ?? [] ) ), count( (array) ( $co['intl'] ?? [] ) ), count( (array) ( $co['sectors'] ?? [] ) ) ) ];
	$rows[] = [ $t( 'Categorieën', 'Categories' ), sprintf( $t( '%d, elk met eigen koopvragen', '%d, each with its own buyer questions' ), $cats ) ];
	$rows[] = [ $t( 'Vragen per categorie', 'Questions per category' ), (string) $v ];
	$rows[] = [ $t( 'Rondes', 'Rounds' ), $r > 1
		? ( ! empty( $res['adaptive'] ) || ( ! $res && function_exists( 'bv_gi_ai_adaptive' ) && bv_gi_ai_adaptive() )
			? sprintf( $t( 'Ronde 1 voor alle categorieën, daarna tot %d rondes waar de uitkomst nog onzeker is', 'Round 1 for every category, then up to %d rounds where the result is still uncertain' ), $r )
			: sprintf( $t( '%d voor alle categorieën', '%d for every category' ), $r ) )
		: '1' ];
	if ( ! empty( $res['round_cats'] ) ) {
		foreach ( (array) $res['round_cats'] as $round => $n ) {
			$rows[] = [ sprintf( $t( 'Ronde %d', 'Round %d' ), (int) $round + 1 ), sprintf( $t( '%d categorieën', '%d categories' ), (int) $n ) ];
		}
	}
	$models = [];
	foreach ( (array) $used as $k ) {
		if ( isset( $eng[ $k ] ) ) {
			$via      = function_exists( 'bv_gi_ai_via' ) ? bv_gi_ai_via( $k ) : '';
			$model    = 'openrouter' === $via ? (string) $eng[ $k ]['router'] : ( function_exists( 'bv_gi_ai_model' ) ? bv_gi_ai_model( $k ) : (string) $eng[ $k ]['model'] );
			$models[] = $eng[ $k ]['label'] . ' (' . $model . ')';
		}
	}
	if ( $models ) {
		$rows[] = [ $t( 'Assistenten en modellen', 'Assistants and models' ), implode( ', ', $models ) . $t( ', met de eigen zoekfunctie van elke assistent aan', ', each with its own web search on' ) ];
	}
	$rows[] = [ $t( 'Toon', 'Tone' ), $t( 'Beoordeeld door Claude Haiku (anthropic/claude-haiku-5.5), 0 tot 100, 50 is neutraal', 'Rated by Claude Haiku (anthropic/claude-haiku-5.5), 0 to 100, 50 is neutral' ) ];
	if ( $ans ) {
		$rows[] = [ $t( 'Antwoorden in de laatste meting', 'Answers in the last measurement' ), number_format_i18n( $ans ) ];
	}
	$rows[] = [ $t( 'Minimum voor een AI-score', 'Minimum for an AI score' ), sprintf( $t( '%d antwoorden', '%d answers' ), defined( 'BV_GI_MIN_ANSWERS' ) ? BV_GI_MIN_ANSWERS : 12 ) ];
	$rows[] = [ $t( 'Laatste AI-meting', 'Last AI measurement' ), $done ? wp_date( 'j F Y', $done ) : $t( 'nog niet met deze methode', 'not yet with this method' ) ];
	if ( $next ) {
		$rows[] = [ $t( 'Volgende meting', 'Next measurement' ), wp_date( 'j F Y', $next ) ];
	}

	$h  = '<div class="bv-gim">';
	$h .= '<figure class="wp-block-table bv-gip-table"><table><tbody>';
	foreach ( $rows as [ $k, $val ] ) {
		$h .= '<tr><th scope="row">' . $e( $k ) . '</th><td>' . $e( $val ) . '</td></tr>';
	}
	$h .= '</tbody></table><figcaption class="wp-element-caption">' . $e( $t( 'Live uit de meetgegevens van de site.', 'Live from the site\'s measurement data.' ) ) . '</figcaption></figure>';

	if ( function_exists( 'bv_gi_ai_templates' ) ) {
		foreach ( [ 'nl' => $t( 'De vijf vragen voor de Nederlandse lijst (in het Nederlands)', 'The five questions for the Dutch list (in Dutch)' ), 'intl' => $t( 'De vijf vragen voor de internationale lijst (in het Engels)', 'The five questions for the international list (in English)' ) ] as $list => $head ) {
			$h .= '<h3 class="wp-block-heading">' . $e( $head ) . '</h3><ol class="wp-block-list">';
			foreach ( bv_gi_ai_templates( $list ) as $tpl ) {
				$h .= '<li>' . $e( sprintf( $tpl, $t( '[categorie]', '[category]' ) ) ) . '</li>';
			}
			$h .= '</ol>';
		}
		$ex = [];
		foreach ( [ 'nl', 'intl' ] as $list ) {
			foreach ( array_slice( (array) ( $co[ $list ] ?? [] ), 0, 40, true ) as $c ) {
				$ex[ $list ][ $c['cat'] ] = true;
			}
		}
		$h .= '<p>' . $e( sprintf( $t( 'Voorbeelden van categorieën: %1$s (Nederland); %2$s (internationaal).', 'Examples of categories: %1$s (Netherlands); %2$s (international).' ), implode( ', ', array_slice( array_keys( $ex['nl'] ?? [] ), 0, 4 ) ), implode( ', ', array_slice( array_keys( $ex['intl'] ?? [] ), 0, 4 ) ) ) ) . '</p>';
	}

	$h .= '<h3 class="wp-block-heading">' . $e( $t( 'Wijzigingen in de methode', 'Changes to the method' ) ) . '</h3>';
	$h .= '<figure class="wp-block-table bv-gip-table"><table><thead><tr><th>' . $e( $t( 'Datum', 'Date' ) ) . '</th><th>' . $e( $t( 'Methode', 'Method' ) ) . '</th><th>' . $e( $t( 'Wat veranderde', 'What changed' ) ) . '</th></tr></thead><tbody>';
	foreach ( bv_gi_method_log() as [ $date, $ver, $txt_nl, $txt_en ] ) {
		$h .= '<tr><td>' . $e( wp_date( 'j F Y', strtotime( $date . ' 12:00 UTC' ) ) ) . '</td><td>' . $e( $ver ) . '</td><td>' . $e( $nl ? $txt_nl : $txt_en ) . '</td></tr>';
	}
	$h .= '</tbody></table></figure></div>';
	return $h;
} );
