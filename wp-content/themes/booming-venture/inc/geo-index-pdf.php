<?php
/**
 * B2B GEO Index: shared check texts and the PDF report (1.25.0).
 *
 * bv_gi_texts() holds the labels and advice for every website check, in
 * Dutch and English. The block (blocks/geo-index/render.php) and the PDF use
 * the same texts, so they never drift apart.
 *
 * GET /wp-json/bv/v1/geo-pdf?domain=x.com&list=nl|intl&lang=nl|en (company), or
 * GET /wp-json/bv/v1/geo-pdf?sector=c20&list=nl|intl&lang=nl|en (whole sector)
 * returns a styled A4 report: score, every check, AI numbers when measured,
 * the gap with the sector leader and an improvement plan. For a company in
 * the index it uses the stored results; for any other domain it uses the
 * result of the public check (cached 15 minutes to a day), so it never makes
 * an outside request itself. Built with FPDF (inc/lib/fpdf, MIT-style licence).
 *
 * @package BoomingVenture
 */

defined( 'ABSPATH' ) || exit;

/** Labels and advice per check, in the page language. $list decides the language check (Dutch version or several languages). */
function bv_gi_texts( bool $nl, string $list = '' ): array {
	$t    = static fn( $nl_text, $en_text ) => $nl ? $nl_text : $en_text;
	$list = in_array( $list, [ 'nl', 'intl' ], true ) ? $list : ( $nl ? 'nl' : 'intl' );

	$bots = [
		'bot_OAI-SearchBot'    => [ 'ChatGPT', $t( 'ChatGPT search mag de site lezen (OAI-SearchBot)', 'ChatGPT search may read the site (OAI-SearchBot)' ) ],
		'bot_Claude-SearchBot' => [ 'Claude', $t( 'Claude mag de site lezen (Claude-SearchBot)', 'Claude may read the site (Claude-SearchBot)' ) ],
		'bot_PerplexityBot'    => [ 'Perplexity', $t( 'Perplexity mag de site lezen (PerplexityBot)', 'Perplexity may read the site (PerplexityBot)' ) ],
		'bot_Google-Extended'  => [ 'Gemini', $t( 'Gemini mag de content gebruiken (Google-Extended)', 'Gemini may use the content (Google-Extended)' ) ],
		'bot_Bingbot'          => [ 'Copilot', $t( 'Bing en Copilot mogen de site lezen (Bingbot)', 'Bing and Copilot may read the site (Bingbot)' ) ],
	];
	$cols = [
		'html'       => [ 'HTML', $t( 'Tekst leesbaar zonder JavaScript', 'Text readable without JavaScript' ) ],
		'schema_org' => [ 'Schema', $t( 'Organization-schema op de homepage', 'Organization schema on the homepage' ) ],
		'wikipedia'  => [ 'Wikipedia', $t( 'Wikipedia-artikel over het bedrijf', 'Wikipedia article about the company' ) ],
		'fresh'      => [ $t( 'Actueel', 'Fresh' ), $t( 'Sitemap bijgewerkt in de laatste 90 dagen', 'Sitemap updated in the last 90 days' ) ],
		'lang'       => [ $t( 'Taal', 'Lang' ), $t( 'Nederlandse versie (lijst Nederland) of meerdere talen (internationaal)', 'Dutch version (Dutch list) or several languages (international list)' ) ],
	];
	$lang_label = [
		'nl'   => $t( 'Nederlandse versie van de site', 'Dutch version of the site' ),
		'intl' => $t( 'Twee of meer taalversies (hreflang)', 'Two or more language versions (hreflang)' ),
	];
	$lang_advice = [
		'nl'   => $t( 'Publiceer een Nederlandse versie voor Nederlandse kopers, met hreflang. Bij Nederlandse vragen kiest AI vooral Nederlandstalige bronnen.', 'Publish a Dutch version for Dutch buyers, with hreflang. For Dutch questions, AI mostly picks Dutch-language sources.' ),
		'intl' => $t( 'Publiceer taalversies voor je markten, met hreflang. AI kiest vooral bronnen in de taal van de vraag.', 'Publish language versions for your markets, with hreflang. AI mostly picks sources in the language of the question.' ),
	];
	$labels = array_merge( array_map( static fn( $c ) => $c[1], $bots + $cols ), [
		'bot_GPTBot'       => $t( 'GPTBot (training van OpenAI) mag de site lezen', 'GPTBot (OpenAI training) may read the site' ),
		'bot_ChatGPT-User' => $t( 'ChatGPT mag pagina\'s openen op verzoek van gebruikers (ChatGPT-User)', 'ChatGPT may open pages for users (ChatGPT-User)' ),
		'bot_ClaudeBot'    => $t( 'ClaudeBot (training van Anthropic) mag de site lezen', 'ClaudeBot (Anthropic training) may read the site' ),
		'schema_sameas'    => $t( 'Links naar profielen elders (sameAs)', 'Links to profiles elsewhere (sameAs)' ),
		'wikidata'         => $t( 'Wikidata-item met de juiste website', 'Wikidata item with the right website' ),
		'llms'             => $t( 'llms.txt aanwezig', 'llms.txt present' ),
		'lang'             => $lang_label[ $list ],
	] );
	$advice = [
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
		'lang'                 => $lang_advice[ $list ],
		'llms'                 => $t( 'Een llms.txt is optioneel. Er is weinig bewijs dat AI-crawlers het gebruiken, maar het kost weinig.', 'An llms.txt is optional. There is little evidence AI crawlers use it, but it costs little.' ),
	];
	return [ 'bots' => $bots, 'cols' => $cols, 'labels' => $labels, 'advice' => $advice, 'lang_label' => $lang_label, 'lang_advice' => $lang_advice ];
}

/** Points per check (the 100-point model in inc/geo-index.php), used to order the improvement plan. */
function bv_gi_check_weights(): array {
	$w = [];
	foreach ( bv_gi_bots() as $bot => $points ) {
		$w[ 'bot_' . $bot ] = $points;
	}
	return $w + [ 'html' => 15, 'schema_org' => 8, 'schema_sameas' => 4, 'wikidata' => 7, 'wikipedia' => 8, 'fresh' => 10, 'lang' => 8, 'llms' => 2 ];
}

/** Date in the report language, without depending on the site locale. */
function bv_gi_pdf_date( int $ts, bool $nl ): string {
	$months = $nl
		? [ 'januari', 'februari', 'maart', 'april', 'mei', 'juni', 'juli', 'augustus', 'september', 'oktober', 'november', 'december' ]
		: [ 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' ];
	$d = (int) wp_date( 'j', $ts );
	$m = $months[ (int) wp_date( 'n', $ts ) - 1 ];
	$y = wp_date( 'Y', $ts );
	return $nl ? "$d $m $y" : "$d $m $y";
}

/**
 * Everything the report needs, from stored data only.
 * Returns null when the domain is neither in the index nor recently checked.
 */
function bv_gi_pdf_data( string $domain, string $list, string $sector = '' ): ?array {
	$rows = bv_gi_ranked( $list );
	$self = null;
	foreach ( $rows as $r ) {
		if ( $r['domain'] === $domain ) {
			$self = $r;
			break;
		}
	}
	$meta    = (array) get_option( 'bv_gi_meta', [] );
	$sectors = (array) ( bv_gi_companies()['sectors'] ?? [] );
	$d       = [ 'domain' => $domain, 'list' => $list, 'count' => count( $rows ), 'measured' => (int) ( $meta['completed'] ?? 0 ), 'in_index' => (bool) $self ];

	if ( $self ) {
		$d += [
			'name'       => $self['name'],
			'sector'     => $self['sector'],
			'score'      => (int) $self['score'],
			'ready'      => (int) $self['ready'],
			'ai'         => $self['ai_data'] && empty( $self['ai_low'] ) ? $self['ai_data'] : null,
			'ai_low'     => (bool) $self['ai_low'],
			'margin'     => (int) $self['margin'],
			'rank'       => (int) $self['rank'],
			'group'      => (string) $self['group'],
			'change'     => $self['change'],
			'change_sig' => $self['change_sig'],
			'checks'     => (array) $self['checks'],
			'status'     => (string) $self['status'],
			'home_code'  => 0,
		];
	} else {
		$out = get_transient( 'bv_gi_c_' . md5( $list . $domain ) );
		if ( ! $out ) {
			return null;
		}
		$out = (array) $out;
		$d  += [
			'name'       => $domain,
			'sector'     => isset( $sectors[ $sector ] ) ? $sector : '',
			'score'      => (int) $out['score'],
			'ready'      => (int) $out['score'],
			'ai'         => null,
			'ai_low'     => false,
			'margin'     => 0,
			'rank'       => 0,
			'group'      => '',
			'change'     => null,
			'change_sig' => null,
			'checks'     => (array) $out['checks'],
			'status'     => (string) ( $out['status'] ?? 'ok' ),
			'home_code'  => (int) ( $out['home_code'] ?? 0 ),
			'robots_code' => (int) ( $out['robots_code'] ?? 0 ),
			'measured'   => 0,
		];
	}

	$d['sector_name'] = $d['sector'] && isset( $sectors[ $d['sector'] ] ) ? $sectors[ $d['sector'] ] : null;
	$d['gap']         = null;
	if ( $d['sector'] && function_exists( 'bv_gi_leaders' ) ) {
		$leader = bv_gi_leaders( $rows )[ $d['sector'] ] ?? null;
		if ( $leader && $leader['domain'] !== $domain ) {
			$gap = bv_gi_gap( $list, $d['sector'], $leader, $domain, $d['checks'], $self ? (int) $self['score'] : -1, $self ? (int) $self['margin'] : 0 );
			if ( $gap && ! $self ) {
				$gap['leader']['score'] = (int) $leader['ready'];
				$gap['points']          = 'ok' === $d['status'] ? (int) $leader['ready'] - $d['score'] : null;
				$gap['tied']            = false;
			}
			$d['gap'] = $gap;
		} elseif ( $leader ) {
			$d['gap'] = [ 'self' => true ];
		}
	}
	return $d;
}

/** FPDF with a few helpers: UTF-8 text, rounded boxes, a brand gradient, header and footer. */
function bv_gi_pdf_class(): void {
	if ( class_exists( 'BV_GI_PDF' ) ) {
		return;
	}
	require_once get_theme_file_path( 'inc/lib/fpdf/fpdf.php' );

	class BV_GI_PDF extends FPDF { // phpcs:ignore Generic.Files.OneObjectStructurePerFile
		public $foot = '';
		public $foot_url = '';
		public $title_small = '';

		/** UTF-8 to the Windows-1252 encoding of the core fonts. */
		public function tx( $s ): string {
			$s = (string) $s;
			if ( function_exists( 'iconv' ) ) {
				$r = @iconv( 'UTF-8', 'windows-1252//TRANSLIT//IGNORE', $s ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
				if ( false !== $r ) {
					return $r;
				}
			}
			return function_exists( 'mb_convert_encoding' ) ? mb_convert_encoding( $s, 'Windows-1252', 'UTF-8' ) : $s;
		}

		public function rgb( string $hex, string $what = 'fill' ): void {
			$hex = ltrim( $hex, '#' );
			$c   = [ hexdec( substr( $hex, 0, 2 ) ), hexdec( substr( $hex, 2, 2 ) ), hexdec( substr( $hex, 4, 2 ) ) ];
			if ( 'fill' === $what ) {
				$this->SetFillColor( ...$c );
			} elseif ( 'text' === $what ) {
				$this->SetTextColor( ...$c );
			} else {
				$this->SetDrawColor( ...$c );
			}
		}

		/** Horizontal gradient from the brand blue to the brand teal, drawn as thin strips. */
		public function gradient( float $x, float $y, float $w, float $h ): void {
			$a = [ 2, 132, 199 ];
			$b = [ 20, 184, 166 ];
			$n = 80;
			for ( $i = 0; $i < $n; $i++ ) {
				$f = $i / ( $n - 1 );
				$this->SetFillColor( (int) round( $a[0] + ( $b[0] - $a[0] ) * $f ), (int) round( $a[1] + ( $b[1] - $a[1] ) * $f ), (int) round( $a[2] + ( $b[2] - $a[2] ) * $f ) );
				$this->Rect( $x + $i * $w / $n, $y, $w / $n + 0.2, $h, 'F' );
			}
		}

		public function RoundedRect( $x, $y, $w, $h, $r, $style = '' ) { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName
			$k  = $this->k;
			$hp = $this->h;
			$op = 'F' === $style ? 'f' : ( ( 'FD' === $style || 'DF' === $style ) ? 'B' : 'S' );
			$a  = 4 / 3 * ( sqrt( 2 ) - 1 );
			$this->_out( sprintf( '%.2F %.2F m', ( $x + $r ) * $k, ( $hp - $y ) * $k ) );
			$xc = $x + $w - $r;
			$yc = $y + $r;
			$this->_out( sprintf( '%.2F %.2F l', $xc * $k, ( $hp - $y ) * $k ) );
			$this->arc( $xc + $r * $a, $yc - $r, $xc + $r, $yc - $r * $a, $xc + $r, $yc );
			$xc = $x + $w - $r;
			$yc = $y + $h - $r;
			$this->_out( sprintf( '%.2F %.2F l', ( $x + $w ) * $k, ( $hp - $yc ) * $k ) );
			$this->arc( $xc + $r, $yc + $r * $a, $xc + $r * $a, $yc + $r, $xc, $yc + $r );
			$xc = $x + $r;
			$yc = $y + $h - $r;
			$this->_out( sprintf( '%.2F %.2F l', $xc * $k, ( $hp - ( $y + $h ) ) * $k ) );
			$this->arc( $xc - $r * $a, $yc + $r, $xc - $r, $yc + $r * $a, $xc - $r, $yc );
			$xc = $x + $r;
			$yc = $y + $r;
			$this->_out( sprintf( '%.2F %.2F l', $x * $k, ( $hp - $yc ) * $k ) );
			$this->arc( $xc - $r, $yc - $r * $a, $xc - $r * $a, $yc - $r, $xc, $yc - $r );
			$this->_out( $op );
		}

		/** Status icon as vector shapes (no symbol font needed): check, cross or dash in a soft circle. */
		public function icon( float $cx, float $cy, $v ): void {
			$r = 2.3;
			$this->rgb( true === $v ? '#dcfce7' : ( false === $v ? '#fee2e2' : '#f1f5f9' ) );
			$this->RoundedRect( $cx - $r, $cy - $r, 2 * $r, 2 * $r, $r, 'F' );
			$this->rgb( true === $v ? '#047857' : ( false === $v ? '#dc2626' : '#94a3b8' ), 'draw' );
			$this->SetLineWidth( 0.45 );
			if ( true === $v ) {
				$this->Line( $cx - 1.05, $cy + 0.05, $cx - 0.3, $cy + 0.85 );
				$this->Line( $cx - 0.3, $cy + 0.85, $cx + 1.15, $cy - 0.85 );
			} elseif ( false === $v ) {
				$this->Line( $cx - 0.9, $cy - 0.9, $cx + 0.9, $cy + 0.9 );
				$this->Line( $cx - 0.9, $cy + 0.9, $cx + 0.9, $cy - 0.9 );
			} else {
				$this->Line( $cx - 0.9, $cy, $cx + 0.9, $cy );
			}
		}

		/** Number of lines MultiCell( $w ) will use for $txt in the current font. */
		public function nbLines( float $w, string $txt ): int {
			$cw   = $this->CurrentFont['cw'];
			$wmax = ( $w - 2 * $this->cMargin ) * 1000 / $this->FontSize;
			$s    = str_replace( "\r", '', $txt );
			$nb   = strlen( $s );
			if ( $nb > 0 && "\n" === $s[ $nb - 1 ] ) {
				$nb--;
			}
			$sep = -1;
			$i   = 0;
			$j   = 0;
			$l   = 0;
			$nl  = 1;
			while ( $i < $nb ) {
				$c = $s[ $i ];
				if ( "\n" === $c ) {
					$i++;
					$sep = -1;
					$j   = $i;
					$l   = 0;
					$nl++;
					continue;
				}
				if ( ' ' === $c ) {
					$sep = $i;
				}
				$l += $cw[ $c ] ?? 500;
				if ( $l > $wmax ) {
					if ( -1 === $sep ) {
						if ( $i === $j ) {
							$i++;
						}
					} else {
						$i = $sep + 1;
					}
					$sep = -1;
					$j   = $i;
					$l   = 0;
					$nl++;
				} else {
					$i++;
				}
			}
			return $nl;
		}

		protected function arc( $x1, $y1, $x2, $y2, $x3, $y3 ): void {
			$h = $this->h;
			$k = $this->k;
			$this->_out( sprintf( '%.2F %.2F %.2F %.2F %.2F %.2F c ', $x1 * $k, ( $h - $y1 ) * $k, $x2 * $k, ( $h - $y2 ) * $k, $x3 * $k, ( $h - $y3 ) * $k ) );
		}

		/** Section heading with a short accent line; starts a new page when the section would not fit. */
		public function section( string $title, float $need = 30 ): void {
			if ( $this->GetY() + $need > 272 ) {
				$this->AddPage();
			}
			$this->Ln( 2 );
			$this->SetX( 16 );
			$this->SetFont( 'Helvetica', 'B', 12.5 );
			$this->rgb( '#0c1a2e', 'text' );
			$this->Cell( $this->w - 32, 7, $this->tx( $title ), 0, 1 );
			$this->rgb( '#bae6fd', 'draw' );
			$this->SetLineWidth( 0.4 );
			$this->Line( 16, $this->GetY(), 16 + 24, $this->GetY() );
			$this->Ln( 3 );
		}

		/** Small heading inside a section. */
		public function sub( string $title, float $need = 20 ): void {
			if ( $this->GetY() + $need > 272 ) {
				$this->AddPage();
			}
			$this->Ln( 2 );
			$this->SetX( 16 );
			$this->SetFont( 'Helvetica', 'B', 10 );
			$this->rgb( '#0369a1', 'text' );
			$this->Cell( $this->w - 32, 6, $this->tx( $title ), 0, 1 );
			$this->Ln( 0.5 );
		}

		/** A paragraph in body text. */
		public function para( string $txt, float $size = 9.5, string $color = '#1f2937' ): void {
			$this->SetX( 16 );
			$this->SetFont( 'Helvetica', '', $size );
			$this->rgb( $color, 'text' );
			$this->MultiCell( $this->w - 32, $size * 0.53, $this->tx( $txt ), 0, 'L' );
		}

		/** A horizontal bar from 0 to 100%. */
		public function bar( float $x, float $y, float $w, float $pct, string $color = '#0284c7' ): void {
			$this->rgb( '#e2e8f0' );
			$this->RoundedRect( $x, $y, $w, 2.4, 1.2, 'F' );
			if ( $pct > 0 ) {
				$this->rgb( $color );
				$this->RoundedRect( $x, $y, max( 2.4, $w * min( 100, $pct ) / 100 ), 2.4, 1.2, 'F' );
			}
		}

		public function Header() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName
			if ( $this->PageNo() < 2 ) {
				return;
			}
			$this->gradient( 0, 0, $this->w, 3 );
			$this->SetXY( 16, 9 );
			$this->SetFont( 'Helvetica', 'B', 9 );
			$this->rgb( '#5b6b80', 'text' );
			$this->Cell( 0, 5, $this->tx( $this->title_small ), 0, 1 );
			$this->SetY( 20 );
		}

		public function Footer() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName
			$this->SetY( -16 );
			$this->rgb( '#e2e8f0', 'draw' );
			$this->SetLineWidth( 0.3 );
			$this->Line( 16, $this->GetY(), $this->w - 16, $this->GetY() );
			$this->SetY( -13 );
			$this->SetFont( 'Helvetica', '', 7.5 );
			$this->rgb( '#5b6b80', 'text' );
			$this->Cell( 150, 5, $this->tx( $this->foot ), 0, 0, 'L', false, $this->foot_url );
			$this->Cell( 0, 5, $this->PageNo() . ' / {nb}', 0, 0, 'R' );
		}
	}
}

/** Build the PDF and return it as a string. */
function bv_gi_pdf_render( array $d, bool $nl ): string {
	bv_gi_pdf_class();
	$t      = static fn( $nl_text, $en_text ) => $nl ? $nl_text : $en_text;
	$tx     = bv_gi_texts( $nl, $d['list'] );
	$page   = get_page_by_path( $nl ? 'nl/b2b-geo-index' : 'b2b-geo-index' );
	$url    = ( $page && 'publish' === $page->post_status ) ? get_permalink( $page ) : home_url( '/' );
	$short  = preg_replace( '#^https?://(www\.)?#', '', untrailingslashit( $url ) );
	$today  = bv_gi_pdf_date( time(), $nl );
	$listnm = 'nl' === $d['list'] ? $t( 'Top 200 Nederland', 'Top 200 Netherlands' ) : $t( 'Top 200 internationaal', 'Top 200 international' );
	$secnm  = $d['sector_name'] ? $d['sector_name'][ $nl ? 0 : 1 ] : '';
	$partial = 'ok' !== $d['status'];

	$pdf = new BV_GI_PDF( 'P', 'mm', 'A4' );
	$pdf->SetMargins( 16, 16, 16 );
	$pdf->SetAutoPageBreak( true, 22 );
	$pdf->AliasNbPages();
	$pdf->SetTitle( $t( 'AI-vindbaarheid van ', 'AI search readiness of ' ) . $d['name'], true );
	$pdf->SetAuthor( 'Booming Venture', true );
	$pdf->SetCreator( 'B2B GEO Index', true );
	$pdf->title_small = 'B2B GEO Index  |  ' . $d['name'];
	$pdf->foot        = sprintf( $t( 'B2B GEO Index van Booming Venture  |  %s  |  gemaakt op %s', 'B2B GEO Index by Booming Venture  |  %s  |  made on %s' ), $short, $today );
	$pdf->foot_url    = $url;
	$pdf->AddPage();
	$W = $pdf->GetPageWidth() - 32; // content width

	/* Header band */
	$pdf->gradient( 0, 0, $pdf->GetPageWidth(), 34 );
	$pdf->SetXY( 16, 10 );
	$pdf->SetFont( 'Helvetica', 'B', 8.5 );
	$pdf->SetTextColor( 255, 255, 255 );
	$pdf->Cell( $W / 2, 4, 'B2B GEO INDEX', 0, 0 );
	$pdf->Cell( $W / 2, 4, 'BOOMING VENTURE', 0, 1, 'R' );
	$pdf->SetX( 16 );
	$pdf->SetFont( 'Helvetica', 'B', 17 );
	$pdf->Cell( $W * 0.7, 10, $pdf->tx( $t( 'Rapport AI-vindbaarheid', 'AI search readiness report' ) ), 0, 0 );
	$pdf->SetFont( 'Helvetica', '', 9 );
	$pdf->Cell( $W * 0.3, 10, $pdf->tx( $today ), 0, 1, 'R' );

	/* Company */
	$pdf->SetXY( 16, 44 );
	$pdf->rgb( '#0c1a2e', 'text' );
	$pdf->SetFont( 'Helvetica', 'B', 20 );
	$pdf->Cell( $W, 9, $pdf->tx( $d['name'] ), 0, 1 );
	$pdf->SetX( 16 );
	$pdf->SetFont( 'Helvetica', '', 9.5 );
	$pdf->rgb( '#5b6b80', 'text' );
	$sub = array_filter( [ $d['domain'], $secnm, $d['in_index'] ? $listnm : $t( 'niet in de index, gemeten met de check', 'not in the index, measured with the check' ) ] );
	$pdf->Cell( $W, 5, $pdf->tx( implode( '  |  ', $sub ) ), 0, 1 );

	/* Score card */
	$y = 64;
	$pdf->rgb( '#f0f9ff' );
	$pdf->rgb( '#bae6fd', 'draw' );
	$pdf->SetLineWidth( 0.3 );
	$pdf->RoundedRect( 16, $y, $W, 36, 3, 'DF' );
	$pdf->SetXY( 22, $y + 6 );
	$pdf->rgb( '#0369a1', 'text' );
	$pdf->SetFont( 'Helvetica', 'B', 40 );
	$sw = $pdf->GetStringWidth( (string) $d['score'] );
	$pdf->Cell( $sw + 1, 16, (string) $d['score'], 0, 0 );
	$pdf->SetFont( 'Helvetica', '', 11 );
	$pdf->rgb( '#5b6b80', 'text' );
	$pdf->SetXY( 22 + $sw + 1, $y + 13.5 );
	$pdf->Cell( 16, 6, '/ 100', 0, 0 );
	$pdf->SetXY( 22, $y + 24 );
	$pdf->SetFont( 'Helvetica', 'B', 8.5 );
	$kind = $d['ai'] ? $t( 'TOTAALSCORE (WEBSITE EN AI)', 'TOTAL SCORE (WEBSITE AND AI)' ) : $t( 'WEBSITESCORE', 'WEBSITE SCORE' );
	$pdf->Cell( 60, 5, $pdf->tx( $kind . ( $partial ? $t( '  |  ONVOLLEDIG', '  |  INCOMPLETE' ) : '' ) ), 0, 0 );

	/* Right side of the card: place, scores and a bar */
	$rx    = 16 + $W * 0.47;
	$lines = [];
	if ( $d['in_index'] ) {
		$lines[] = sprintf( $t( 'Plaats %1$d van %2$d in de %3$s', 'Place %1$d of %2$d in the %3$s' ), $d['rank'], $d['count'], $listnm );
		if ( $d['ai'] ) {
			$lines[] = sprintf( $t( 'Website %1$d, AI %2$d, marge plus of min %3$d', 'Website %1$d, AI %2$d, margin plus or minus %3$d' ), $d['ready'], (int) $d['ai']['score'], $d['margin'] );
		}
		if ( null !== $d['change'] ) {
			$lines[] = sprintf( $t( 'Sinds vorige maand: %1$s (%2$s)', 'Since last month: %1$s (%2$s)' ), ( $d['change'] > 0 ? '+' : '' ) . $d['change'], $d['change_sig'] ? $t( 'echt verschil', 'real change' ) : $t( 'binnen de marge', 'within the margin' ) );
		}
		if ( $d['measured'] ) {
			$lines[] = sprintf( $t( 'Gemeten op %s', 'Measured on %s' ), bv_gi_pdf_date( $d['measured'], $nl ) );
		}
	} else {
		$lines[] = $t( 'Gemeten met de gratis check, op dezelfde manier als de index.', 'Measured with the free check, the same way as the index.' );
		$lines[] = 'nl' === $d['list'] ? $t( 'Taalcheck: Nederlandse versie.', 'Language check: Dutch version.' ) : $t( 'Taalcheck: twee of meer taalversies.', 'Language check: two or more language versions.' );
	}
	$pdf->SetFont( 'Helvetica', '', 9 );
	$pdf->rgb( '#1f2937', 'text' );
	$ly = $y + 6;
	foreach ( array_slice( $lines, 0, 3 ) as $line ) {
		$pdf->SetXY( $rx, $ly );
		$pdf->Cell( 16 + $W - $rx - 6, 5, $pdf->tx( $line ), 0, 0 );
		$ly += 5.5;
	}
	$bw = 16 + $W - $rx - 6;
	$pdf->rgb( '#e0f2fe' );
	$pdf->RoundedRect( $rx, $y + 26, $bw, 3, 1.5, 'F' );
	if ( $d['score'] > 0 ) {
		$pdf->rgb( '#0284c7' );
		$pdf->RoundedRect( $rx, $y + 26, max( 3, $bw * min( 100, $d['score'] ) / 100 ), 3, 1.5, 'F' );
	}
	$pdf->SetY( $y + 42 );

	/* Incomplete measurement */
	if ( $partial ) {
		$lost = 0;
		foreach ( [ 'html' => 15, 'schema_org' => 20, 'lang' => 8, 'fresh' => 10 ] as $k => $p ) {
			if ( ! isset( $d['checks'][ $k ] ) || null === $d['checks'][ $k ] ) {
				$lost += $p;
			}
		}
		$code = $d['home_code'] ? 'HTTP ' . $d['home_code'] : $t( 'geen antwoord binnen 6 seconden', 'no answer within 6 seconds' );
		$msg  = sprintf( $t( 'Je homepage weigerde ons verzoek (%1$s). Daardoor konden we checks voor %2$d van de 100 punten niet doen, en is deze score te laag. Botbescherming of een firewall kan AI-crawlers op dezelfde manier weigeren, ook als robots.txt ze toelaat.', 'Your homepage refused our request (%1$s). So we could not run checks worth %2$d of the 100 points, and this score is too low. Bot protection or a firewall can refuse AI crawlers the same way, even when robots.txt lets them in.' ), $code, $lost );
		$y0 = $pdf->GetY();
		$pdf->SetFont( 'Helvetica', '', 9 );
		$h = $pdf->nbLines( $W - 10, $pdf->tx( $msg ) ) * 4.6 + 7;
		$pdf->rgb( '#fffbeb' );
		$pdf->RoundedRect( 16, $y0, $W, $h, 2, 'F' );
		$pdf->rgb( '#f59e0b' );
		$pdf->Rect( 16, $y0, 1.2, $h, 'F' );
		$pdf->rgb( '#92400e', 'text' );
		$pdf->SetXY( 21, $y0 + 3.5 );
		$pdf->MultiCell( $W - 10, 4.6, $pdf->tx( $msg ), 0, 'L', false );
		$pdf->SetY( $y0 + $h + 4 );
	}

	$section = static function ( string $title, float $need = 30 ) use ( $pdf ) {
		$pdf->section( $title, $need );
	};

	/* Website checks */
	$section( $t( 'De websitechecks', 'The website checks' ) );
	$weights = bv_gi_check_weights();
	$i       = 0;
	foreach ( $tx['labels'] as $k => $label ) {
		if ( ! array_key_exists( $k, $d['checks'] ) ) {
			continue;
		}
		$v = $d['checks'][ $k ];
		if ( $pdf->GetY() > 262 ) {
			$pdf->AddPage();
		}
		$ry = $pdf->GetY();
		if ( 0 === $i % 2 ) {
			$pdf->rgb( '#f8fafc' );
			$pdf->Rect( 16, $ry, $W, 7, 'F' );
		}
		$pdf->icon( 21.5, $ry + 3.5, $v );
		$pdf->SetXY( 25, $ry );
		$pdf->SetFont( 'Helvetica', '', 9.5 );
		$pdf->rgb( '#1f2937', 'text' );
		$pdf->Cell( $W - 50, 7, $pdf->tx( $label ), 0, 0 );
		$pdf->SetFont( 'Helvetica', '', 8 );
		$pdf->rgb( '#5b6b80', 'text' );
		$pdf->Cell( 14, 7, $pdf->tx( sprintf( $t( '%d pnt', '%d pts' ), $weights[ $k ] ?? 0 ) ), 0, 0, 'R' );
		$pdf->SetFont( 'Helvetica', 'B', 8.5 );
		$pdf->rgb( true === $v ? '#047857' : ( false === $v ? '#dc2626' : '#5b6b80' ), 'text' );
		$pdf->Cell( 27, 7, $pdf->tx( true === $v ? $t( 'In orde', 'In place' ) : ( false === $v ? $t( 'Ontbreekt', 'Missing' ) : $t( 'Niet te meten', 'Not measured' ) ) ), 0, 1, 'R' );
		$i++;
	}
	$pdf->Ln( 2 );
	$pdf->SetFont( 'Helvetica', '', 7.5 );
	$pdf->rgb( '#5b6b80', 'text' );
	$pdf->SetX( 16 );
	$pdf->MultiCell( $W, 3.8, $pdf->tx( $t( 'Punten: het aandeel van elke check in de websitescore van 100. Organization-schema telt samen met sameAs, WebSite en andere types tot 20 punten.', 'Points: the share of each check in the website score of 100. Organization schema counts with sameAs, WebSite and other types up to 20 points.' ) ), 0, 'L' );

	/* AI visibility */
	$section( $t( 'Wat AI-assistenten zeggen', 'What AI assistants say' ), $d['ai'] ? 20 + 5.5 * 5 + 6 * count( (array) ( $d['ai']['e'] ?? [] ) ) : 25 );
	$pdf->SetFont( 'Helvetica', '', 9.5 );
	$pdf->rgb( '#1f2937', 'text' );
	if ( $d['ai'] ) {
		$a     = $d['ai'];
		$facts = [ sprintf( $t( 'Genoemd in %1$d van %2$d antwoorden: %3$d%% (marge %4$d tot %5$d%%).', 'Named in %1$d of %2$d answers: %3$d%% (margin %4$d to %5$d%%).' ), $a['m'], $a['n'], $a['vis'], $a['vis_lo'], $a['vis_hi'] ) ];
		if ( null !== $a['sov'] ) {
			$facts[] = sprintf( $t( 'Aandeel van alle vermeldingen in de sector: %d%%.', 'Share of all mentions in the sector: %d%%.' ), $a['sov'] );
		}
		$facts[] = sprintf( $t( 'Als eerste genoemd in %d%% van de antwoorden waarin het bedrijf voorkomt.', 'Named first in %d%% of the answers that mention the company.' ), $a['first'] );
		if ( null !== $a['sent'] ) {
			$facts[] = sprintf( $t( 'Toon: %d van 100 (50 is neutraal).', 'Tone: %d of 100 (50 is neutral).' ), $a['sent'] );
		}
		$facts[] = sprintf( $t( 'De eigen site als bron in %d%% van de antwoorden.', 'Own site cited as a source in %d%% of the answers.' ), $a['cite'] );
		foreach ( $facts as $f ) {
			$pdf->SetX( 18 );
			$pdf->Cell( 4, 5.5, chr( 149 ), 0, 0 );
			$pdf->MultiCell( $W - 6, 5.5, $pdf->tx( $f ), 0, 'L' );
		}
		$eng = function_exists( 'bv_gi_ai_engines' ) ? bv_gi_ai_engines() : [];
		if ( ! empty( $a['e'] ) ) {
			$pdf->Ln( 2 );
			foreach ( (array) $a['e'] as $k => $e ) {
				$n   = (int) ( $e[0] ?? 0 );
				$m   = (int) ( $e[1] ?? 0 );
				$pct = $n ? $m / $n : 0;
				if ( $pdf->GetY() > 265 ) {
					$pdf->AddPage();
				}
				$by  = $pdf->GetY();
				$pdf->SetXY( 18, $by );
				$pdf->SetFont( 'Helvetica', 'B', 8.5 );
				$pdf->rgb( '#1f2937', 'text' );
				$pdf->Cell( 28, 5, $pdf->tx( $eng[ $k ]['label'] ?? $k ), 0, 0 );
				$pdf->rgb( '#f1f5f9' );
				$pdf->RoundedRect( 47, $by + 1.25, 100, 2.5, 1.25, 'F' );
				if ( $pct > 0 ) {
					$pdf->rgb( '#0d9488' );
					$pdf->RoundedRect( 47, $by + 1.25, max( 2.5, 100 * $pct ), 2.5, 1.25, 'F' );
				}
				$pdf->SetXY( 150, $by );
				$pdf->SetFont( 'Helvetica', '', 8.5 );
				$pdf->rgb( '#5b6b80', 'text' );
				$pdf->Cell( 28, 5, $pdf->tx( sprintf( $t( '%1$d van %2$d', '%1$d of %2$d' ), $m, $n ) ), 0, 1, 'R' );
				$pdf->Ln( 0.8 );
			}
		}
	} else {
		$next = function_exists( 'bv_gi_next_monthly' ) ? bv_gi_next_monthly() : 0;
		$msg  = $d['ai_low']
			? $t( 'Er zijn nog te weinig AI-antwoorden voor een betrouwbare AI-score. We tellen alleen met minimaal 12 antwoorden.', 'There are too few AI answers for a reliable AI score yet. We only count with at least 12 answers.' )
			: ( $d['in_index']
				? sprintf( $t( 'Nog geen AI-meting met de nieuwe methode. De volgende meting start op %s: vijf koopvragen per categorie aan ChatGPT, Claude, Gemini en Perplexity.', 'No AI measurement with the new method yet. The next one starts on %s: five buyer questions per category to ChatGPT, Claude, Gemini and Perplexity.' ), $next ? bv_gi_pdf_date( $next, $nl ) : '' )
				: $t( 'Voor bedrijven buiten de index meten we geen AI-antwoorden. Wil je weten wat ChatGPT, Claude, Gemini en Perplexity over je bedrijf zeggen, vraag dan een Boardroom Quickscan aan.', 'We do not measure AI answers for companies outside the index. To learn what ChatGPT, Claude, Gemini and Perplexity say about your company, request a Boardroom Quickscan.' ) );
		$pdf->SetX( 16 );
		$pdf->MultiCell( $W, 5, $pdf->tx( $msg ), 0, 'L' );
	}

	/* Gap with the sector leader */
	if ( $d['gap'] ) {
		$section( sprintf( $t( 'Verschil met de leider in %s', 'Gap with the leader in %s' ), $secnm ) );
		$pdf->SetFont( 'Helvetica', '', 9.5 );
		$pdf->rgb( '#1f2937', 'text' );
		$pdf->SetX( 16 );
		$g = $d['gap'];
		if ( ! empty( $g['self'] ) ) {
			$pdf->MultiCell( $W, 5, $pdf->tx( sprintf( $t( '%s leidt deze sector.', '%s leads this sector.' ), $d['name'] ) ), 0, 'L' );
		} else {
			$txt = sprintf( $t( '%1$s leidt deze sector met %2$d punten.', '%1$s leads this sector with %2$d points.' ), $g['leader']['name'], $g['leader']['score'] );
			if ( null !== ( $g['points'] ?? null ) ) {
				$txt .= ' ' . sprintf( $t( 'Verschil: %d punten.', 'Gap: %d points.' ), $g['points'] ) . ( ! empty( $g['tied'] ) ? ' ' . $t( 'Dat valt binnen de foutmarge.', 'That is within the margin of error.' ) : '' );
			} elseif ( $partial ) {
				$txt .= ' ' . $t( 'Een eerlijke vergelijking kan pas als je homepage bereikbaar is.', 'A fair comparison needs a reachable homepage.' );
			}
			$pdf->MultiCell( $W, 5, $pdf->tx( $txt ), 0, 'L' );
			if ( ! empty( $g['missing'] ) ) {
				$pdf->Ln( 1 );
				$pdf->SetX( 16 );
				$pdf->MultiCell( $W, 5, $pdf->tx( $t( 'Checks die de leider wel heeft en jij niet: ', 'Checks the leader has and you do not: ' ) . implode( ', ', array_map( static fn( $k ) => $tx['labels'][ $k ] ?? $k, $g['missing'] ) ) . '.' ), 0, 'L' );
			}
			if ( ! empty( $g['sources'] ) ) {
				$pdf->Ln( 1 );
				$pdf->SetX( 16 );
				$src_txt = $d['in_index']
					? $t( 'Bronnen die AI-assistenten bij %1$s wel citeren en bij jou niet: %2$s.', 'Sources AI assistants cite for %1$s but not for you: %2$s.' )
					: $t( 'Bronnen die AI-assistenten bij %1$s citeren: %2$s.', 'Sources AI assistants cite for %1$s: %2$s.' );
				$pdf->MultiCell( $W, 5, $pdf->tx( sprintf( $src_txt, $g['leader']['name'], implode( ', ', $g['sources'] ) ) ), 0, 'L' );
			}
		}
	}

	/* Sector analysis: where the company stands among its peers */
	$sd = $d['sector'] ? bv_gi_sector_data( $d['list'], $d['sector'] ) : null;
	if ( $sd ) {
		bv_gi_pdf_sector( $pdf, $sd, $nl, $d['domain'], $d['checks'] );
	}

	/* Improvement plan */
	$section( $t( 'Je verbeterplan', 'Your improvement plan' ) );
	$todo = [];
	foreach ( $d['checks'] as $k => $v ) {
		if ( false === $v && isset( $tx['advice'][ $k ] ) ) {
			$todo[ $k ] = $weights[ $k ] ?? 0;
		}
	}
	arsort( $todo );
	$items = [];
	if ( $partial ) {
		$items[] = [ $t( 'Maak je homepage bereikbaar voor crawlers', 'Make your homepage reachable for crawlers' ), $t( 'Vraag je webteam of de botbescherming of firewall OAI-SearchBot, Claude-SearchBot en PerplexityBot doorlaat. Robots.txt is alleen de voordeur. Doe daarna de check opnieuw.', 'Ask your web team whether bot protection or the firewall lets OAI-SearchBot, Claude-SearchBot and PerplexityBot through. Robots.txt is only the front door. Then run the check again.' ) ];
	}
	foreach ( array_keys( $todo ) as $k ) {
		$items[] = [ $tx['labels'][ $k ], $tx['advice'][ $k ] ];
	}
	if ( ! $items ) {
		$pdf->SetFont( 'Helvetica', '', 9.5 );
		$pdf->rgb( '#1f2937', 'text' );
		$pdf->SetX( 16 );
		$pdf->MultiCell( $W, 5, $pdf->tx( $t( 'Je haalt alle websitechecks. De volgende stap zit buiten je site: vermeldingen in bronnen die AI-assistenten citeren, zoals vakmedia, reviewplatforms en lijsten.', 'You pass every website check. The next step is outside your site: mentions in sources AI assistants cite, such as trade media, review platforms and lists.' ) ), 0, 'L' );
	}
	foreach ( $items as $n => [ $head, $body ] ) {
		if ( $pdf->GetY() > 255 ) {
			$pdf->AddPage();
		}
		$iy = $pdf->GetY();
		$pdf->rgb( '#0284c7' );
		$pdf->RoundedRect( 16, $iy + 0.5, 6, 6, 3, 'F' );
		$pdf->SetXY( 16, $iy + 0.5 );
		$pdf->SetFont( 'Helvetica', 'B', 8.5 );
		$pdf->SetTextColor( 255, 255, 255 );
		$pdf->Cell( 6, 6, (string) ( $n + 1 ), 0, 0, 'C' );
		$pdf->SetXY( 25, $iy );
		$pdf->SetFont( 'Helvetica', 'B', 10 );
		$pdf->rgb( '#0c1a2e', 'text' );
		$pdf->MultiCell( $W - 9, 5.5, $pdf->tx( $head ), 0, 'L' );
		$pdf->SetX( 25 );
		$pdf->SetFont( 'Helvetica', '', 9.5 );
		$pdf->rgb( '#1f2937', 'text' );
		$pdf->MultiCell( $W - 9, 5, $pdf->tx( $body ), 0, 'L' );
		$pdf->Ln( 3 );
	}

	bv_gi_pdf_closing( $pdf, $nl, $short );

	return $pdf->Output( 'S' );
}

/* ------------------------------------------------------------------ sector analysis */

/** The checks compared across a sector, in reading order. */
function bv_gi_sector_keys(): array {
	return [ 'bot_OAI-SearchBot', 'bot_Claude-SearchBot', 'bot_PerplexityBot', 'bot_Google-Extended', 'bot_Bingbot', 'html', 'schema_org', 'schema_sameas', 'wikidata', 'wikipedia', 'fresh', 'lang', 'llms' ];
}

/**
 * Everything about one sector in one list, from stored data: the companies
 * ranked, average website scores (complete measurements only) for the sector,
 * the whole list and the same sector in the other list, the pass rate per
 * check, the checks that cost the sector most points, and the sources AI
 * assistants cite most for this sector.
 */
function bv_gi_sector_data( string $list, string $sector ): ?array {
	$sectors = (array) ( bv_gi_companies()['sectors'] ?? [] );
	if ( ! isset( $sectors[ $sector ] ) ) {
		return null;
	}
	$rows = bv_gi_ranked( $list );
	$in   = array_values( array_filter( $rows, static fn( $r ) => $r['sector'] === $sector ) );
	if ( ! $in ) {
		return null;
	}
	$ok    = static fn( array $rs ) => array_values( array_filter( $rs, static fn( $r ) => 'ok' === ( $r['status'] ?? 'ok' ) ) );
	$avg   = static fn( array $rs ) => $rs ? (int) round( array_sum( array_column( $rs, 'ready' ) ) / count( $rs ) ) : null;
	$other = array_values( array_filter( bv_gi_ranked( 'nl' === $list ? 'intl' : 'nl' ), static fn( $r ) => $r['sector'] === $sector ) );
	$rate  = static function ( array $rs, string $k ): array {
		$y = 0;
		$n = 0;
		foreach ( $rs as $r ) {
			$v = $r['checks'][ $k ] ?? null;
			if ( true === $v ) {
				$y++;
				$n++;
			} elseif ( false === $v ) {
				$n++;
			}
		}
		return [ $y, $n ];
	};
	$pass = [];
	foreach ( bv_gi_sector_keys() as $k ) {
		$pass[ $k ] = [ 'sector' => $rate( $in, $k ), 'list' => $rate( $rows, $k ) ];
	}
	/* Where the sector loses most points: share missing times the points of the check. */
	$w    = bv_gi_check_weights();
	$weak = [];
	foreach ( $pass as $k => $p ) {
		[ $y, $n ] = $p['sector'];
		if ( $n >= 3 && $y < $n ) {
			$weak[ $k ] = ( $n - $y ) / $n * ( $w[ $k ] ?? 0 );
		}
	}
	arsort( $weak );
	$res = (array) get_option( 'bv_gi_ai_results', [] );
	$ans = (int) ( $res['ans'][ $list ][ $sector ] ?? 0 );
	$src = [];
	if ( $ans ) {
		$all = (array) ( $res['src'][ $list ][ $sector ] ?? [] );
		uasort( $all, static fn( $a, $b ) => (int) ( $b['a'] ?? 0 ) <=> (int) ( $a['a'] ?? 0 ) );
		foreach ( array_slice( $all, 0, 6, true ) as $h => $d ) {
			$src[] = [ 'host' => (string) $h, 'pct' => (int) round( 100 * (int) ( $d['a'] ?? 0 ) / $ans ) ];
		}
	}
	return [
		'list'      => $list,
		'sector'    => $sector,
		'names'     => $sectors[ $sector ],
		'rows'      => $in,
		'count'     => count( $rows ),
		'n_ok'      => count( $ok( $in ) ),
		'avg'       => $avg( $ok( $in ) ),
		'avg_list'  => $avg( $ok( $rows ) ),
		'avg_other' => $avg( $ok( $other ) ),
		'pass'      => $pass,
		'weak'      => array_slice( array_keys( $weak ), 0, 3 ),
		'src'       => $src,
		'ans'       => $ans,
		'ai_n'      => count( array_filter( $in, static fn( $r ) => $r['ai_data'] && empty( $r['ai_low'] ) ) ),
		'measured'  => (int) ( ( (array) get_option( 'bv_gi_meta', [] ) )['completed'] ?? 0 ),
	];
}

/**
 * The sector analysis block: ranking of the sector, pass rate per check (with
 * the company's own result when $self_checks is given), the checks that cost
 * the sector most points with advice, and the sources AI assistants cite.
 */
function bv_gi_pdf_sector( BV_GI_PDF $pdf, array $s, bool $nl, string $self = '', ?array $self_checks = null ): void {
	$t      = static fn( $nl_text, $en_text ) => $nl ? $nl_text : $en_text;
	$tx     = bv_gi_texts( $nl, $s['list'] );
	$W      = $pdf->GetPageWidth() - 32;
	$name   = $s['names'][ $nl ? 0 : 1 ];
	$listnm = 'nl' === $s['list'] ? $t( 'Top 200 Nederland', 'Top 200 Netherlands' ) : $t( 'Top 200 internationaal', 'Top 200 international' );
	$n      = count( $s['rows'] );
	$pos    = 0;
	foreach ( $s['rows'] as $i => $r ) {
		if ( $r['domain'] === $self ) {
			$pos = $i + 1;
		}
	}

	$pdf->section( sprintf( $t( 'Sectoranalyse: %s', 'Sector analysis: %s' ), $name ), 70 );
	$intro = sprintf( $t( 'In de %1$s staan %2$d bedrijven in %3$s.', 'The %1$s has %2$d companies in %3$s.' ), $listnm, $n, $name );
	if ( null !== $s['avg'] ) {
		$intro .= ' ' . sprintf( $t( 'Hun gemiddelde websitescore is %1$d, tegen %2$d voor de hele lijst', 'Their average website score is %1$d, against %2$d for the whole list' ), $s['avg'], (int) $s['avg_list'] );
		$intro .= null !== $s['avg_other'] ? sprintf( $t( ' en %d voor dezelfde sector in de %s.', ' and %d for the same sector in the %s.' ), $s['avg_other'], 'nl' === $s['list'] ? $t( 'internationale lijst', 'international list' ) : $t( 'Nederlandse lijst', 'Dutch list' ) ) : '.';
	}
	if ( $s['n_ok'] < $n ) {
		$miss   = $n - $s['n_ok'];
		$intro .= ' ' . ( 1 === $miss
			? $t( 'Bij 1 bedrijf weigerde de homepage ons verzoek; dat telt niet mee in het gemiddelde.', 'For 1 of them the homepage refused our request; it is left out of the average.' )
			: sprintf( $t( 'Bij %d bedrijven weigerde de homepage ons verzoek; die tellen niet mee in het gemiddelde.', 'For %d of them the homepage refused our request; they are left out of the average.' ), $miss ) );
	}
	if ( $pos ) {
		$intro .= ' ' . sprintf( $t( '%1$s staat op plaats %2$d van %3$d in deze sector.', '%1$s is number %2$d of %3$d in this sector.' ), $s['rows'][ $pos - 1 ]['name'], $pos, $n );
	}
	$pdf->para( $intro );

	/* Ranking of the sector */
	$pdf->sub( $t( 'De ranglijst van de sector', 'The sector ranking' ), 12 + 6.6 * $n );
	$cols = [ 8, $W - 8 - 52 - 16 - 14 - 16, 52, 16, 14, 16 ];
	$head = [ '#', $t( 'Bedrijf', 'Company' ), $t( 'Score', 'Score' ), $t( 'Website', 'Website' ), 'AI', $t( 'Index', 'Index' ) ];
	$pdf->SetX( 16 );
	$pdf->SetFont( 'Helvetica', 'B', 7.5 );
	$pdf->rgb( '#f0f9ff' );
	$pdf->rgb( '#5b6b80', 'text' );
	foreach ( $head as $i => $h ) {
		$pdf->Cell( $cols[ $i ], 6, $pdf->tx( strtoupper( $h ) ), 0, 0, $i >= 3 ? 'R' : 'L', true );
	}
	$pdf->Ln( 6 );
	$star = false;
	foreach ( $s['rows'] as $i => $r ) {
		if ( $pdf->GetY() > 266 ) {
			$pdf->AddPage();
		}
		$ry   = $pdf->GetY();
		$mine = $r['domain'] === $self;
		if ( $mine ) {
			$pdf->rgb( '#e0f2fe' );
			$pdf->Rect( 16, $ry, $W, 6.6, 'F' );
			$pdf->rgb( '#0284c7' );
			$pdf->Rect( 16, $ry, 1, 6.6, 'F' );
		} elseif ( 1 === $i % 2 ) {
			$pdf->rgb( '#f8fafc' );
			$pdf->Rect( 16, $ry, $W, 6.6, 'F' );
		}
		$part = 'ok' !== ( $r['status'] ?? 'ok' );
		$star = $star || $part;
		$pdf->SetXY( 16, $ry );
		$pdf->SetFont( 'Helvetica', 'B', 8.5 );
		$pdf->rgb( '#5b6b80', 'text' );
		$pdf->Cell( $cols[0], 6.6, (string) ( $i + 1 ), 0, 0, 'C' );
		$pdf->SetFont( 'Helvetica', $mine ? 'B' : '', 8.5 );
		$pdf->rgb( '#0c1a2e', 'text' );
		$label = $r['name'] . '  ';
		$pdf->Cell( $pdf->GetStringWidth( $pdf->tx( $label ) ), 6.6, $pdf->tx( $label ), 0, 0 );
		$pdf->SetFont( 'Helvetica', '', 7 );
		$pdf->rgb( '#5b6b80', 'text' );
		$pdf->Cell( max( 1, $cols[1] - $pdf->GetStringWidth( $pdf->tx( $label ) ) - 0.5 ), 6.6, $pdf->tx( $r['domain'] ), 0, 0 );
		$bx = 16 + $cols[0] + $cols[1];
		$pdf->bar( $bx, $ry + 2.1, 38, (float) $r['score'], $mine ? '#0284c7' : '#38bdf8' );
		$pdf->SetXY( $bx + 40, $ry );
		$pdf->SetFont( 'Helvetica', 'B', 8.5 );
		$pdf->rgb( '#0c1a2e', 'text' );
		$pdf->Cell( 12, 6.6, (string) $r['score'], 0, 0, 'R' );
		$pdf->SetFont( 'Helvetica', '', 8.5 );
		$pdf->rgb( '#1f2937', 'text' );
		$pdf->Cell( $cols[3], 6.6, $r['ready'] . ( $part ? '*' : '' ), 0, 0, 'R' );
		$pdf->Cell( $cols[4], 6.6, null === $r['ai'] ? '-' : ( $r['ai_low'] ? $t( 'n.b.', 'n/a' ) : (string) $r['ai'] ), 0, 0, 'R' );
		$pdf->rgb( '#5b6b80', 'text' );
		$pdf->Cell( $cols[5], 6.6, (string) $r['rank'], 0, 1, 'R' );
	}
	if ( $star ) {
		$pdf->Ln( 1 );
		$pdf->para( $t( '* Onvolledig gemeten: de homepage weigerde ons verzoek, dus de websitescore is te laag.', '* Incomplete: the homepage refused our request, so the website score is too low.' ), 7.5, '#5b6b80' );
	}

	/* Pass rate per check */
	$pdf->sub( $t( 'Hoeveel bedrijven in de sector elke check halen', 'How many companies in the sector pass each check' ), 14 + 6.2 * count( $s['pass'] ) );
	$pdf->SetX( 16 );
	$pdf->SetFont( 'Helvetica', 'B', 7.5 );
	$pdf->rgb( '#f0f9ff' );
	$pdf->rgb( '#5b6b80', 'text' );
	$cw = [ $W - 46 - 18 - 18 - ( $self_checks ? 14 : 0 ), 46, 18, 18 ];
	$pdf->Cell( $cw[0], 6, $pdf->tx( strtoupper( $t( 'Check', 'Check' ) ) ), 0, 0, 'L', true );
	$pdf->Cell( $cw[1], 6, '', 0, 0, 'L', true );
	$pdf->Cell( $cw[2], 6, $pdf->tx( strtoupper( $t( 'Sector', 'Sector' ) ) ), 0, 0, 'R', true );
	$pdf->Cell( $cw[3], 6, $pdf->tx( strtoupper( $t( 'Lijst', 'List' ) ) ), 0, $self_checks ? 0 : 1, 'R', true );
	if ( $self_checks ) {
		$pdf->Cell( 14, 6, $pdf->tx( strtoupper( $t( 'Jij', 'You' ) ) ), 0, 1, 'C', true );
	}
	$i = 0;
	foreach ( $s['pass'] as $k => $p ) {
		if ( $pdf->GetY() > 268 ) {
			$pdf->AddPage();
		}
		[ $y, $nn ]   = $p['sector'];
		[ $ly, $lnn ] = $p['list'];
		$pct  = $nn ? 100 * $y / $nn : 0;
		$lpct = $lnn ? 100 * $ly / $lnn : 0;
		$ry   = $pdf->GetY();
		if ( 1 === $i % 2 ) {
			$pdf->rgb( '#f8fafc' );
			$pdf->Rect( 16, $ry, $W, 6.2, 'F' );
		}
		$pdf->SetXY( 16, $ry );
		$pdf->SetFont( 'Helvetica', '', 8.5 );
		$pdf->rgb( '#1f2937', 'text' );
		$lbl = $tx['labels'][ $k ] ?? $k;
		while ( $pdf->GetStringWidth( $pdf->tx( $lbl ) ) > $cw[0] - 3 && strlen( $lbl ) > 10 ) {
			$lbl = rtrim( mb_substr( $lbl, 0, mb_strlen( $lbl ) - 2 ) ) . '…';
		}
		$pdf->Cell( $cw[0], 6.2, $pdf->tx( $lbl ), 0, 0 );
		$bx = 16 + $cw[0];
		if ( $nn ) {
			$pdf->bar( $bx, $ry + 1.9, $cw[1] - 4, $pct, $pct >= 75 ? '#0d9488' : ( $pct >= 40 ? '#0284c7' : '#f59e0b' ) );
		}
		$pdf->SetXY( $bx + $cw[1], $ry );
		$pdf->SetFont( 'Helvetica', 'B', 8.5 );
		$pdf->rgb( '#0c1a2e', 'text' );
		$pdf->Cell( $cw[2], 6.2, $nn ? sprintf( '%d/%d', $y, $nn ) : '-', 0, 0, 'R' );
		$pdf->SetFont( 'Helvetica', '', 8.5 );
		$pdf->rgb( '#5b6b80', 'text' );
		$pdf->Cell( $cw[3], 6.2, $lnn ? round( $lpct ) . '%' : '-', 0, $self_checks ? 0 : 1, 'R' );
		if ( $self_checks ) {
			$pdf->icon( 16 + $W - 7, $ry + 3.1, $self_checks[ $k ] ?? null );
			$pdf->SetXY( 16, $ry + 6.2 );
		}
		$i++;
	}
	$pdf->Ln( 1 );
	$pdf->para( $t( 'Sector: bedrijven die de check halen, van de bedrijven waar we hem konden meten. Lijst: hetzelfde voor de hele lijst van 200.', 'Sector: companies that pass the check, out of those where we could measure it. List: the same for the whole list of 200.' ), 7.5, '#5b6b80' );

	/* Where the sector loses points */
	if ( $s['weak'] ) {
		$pdf->sub( $t( 'Waar de sector de meeste punten laat liggen', 'Where the sector loses most points' ), 40 );
		foreach ( $s['weak'] as $j => $k ) {
			if ( $pdf->GetY() > 258 ) {
				$pdf->AddPage();
			}
			[ $y, $nn ] = $s['pass'][ $k ]['sector'];
			$iy = $pdf->GetY();
			$pdf->rgb( '#f59e0b' );
			$pdf->RoundedRect( 16, $iy + 0.5, 6, 6, 3, 'F' );
			$pdf->SetXY( 16, $iy + 0.5 );
			$pdf->SetFont( 'Helvetica', 'B', 8.5 );
			$pdf->SetTextColor( 255, 255, 255 );
			$pdf->Cell( 6, 6, (string) ( $j + 1 ), 0, 0, 'C' );
			$pdf->SetXY( 25, $iy );
			$pdf->SetFont( 'Helvetica', 'B', 9.5 );
			$pdf->rgb( '#0c1a2e', 'text' );
			$pdf->MultiCell( $W - 9, 5.3, $pdf->tx( sprintf( $t( '%1$d van de %2$d bedrijven missen: %3$s', '%1$d of %2$d companies miss: %3$s' ), $nn - $y, $nn, $tx['labels'][ $k ] ?? $k ) ), 0, 'L' );
			$pdf->SetX( 25 );
			$pdf->SetFont( 'Helvetica', '', 9 );
			$pdf->rgb( '#1f2937', 'text' );
			$pdf->MultiCell( $W - 9, 4.8, $pdf->tx( $tx['advice'][ $k ] ?? '' ), 0, 'L' );
			$pdf->Ln( 2 );
		}
	}

	/* Sources AI assistants cite in this sector */
	if ( $s['src'] ) {
		$pdf->sub( sprintf( $t( 'Bronnen die AI-assistenten in %1$s het vaakst citeren (%2$d antwoorden)', 'Sources AI assistants cite most in %1$s (%2$d answers)' ), $name, $s['ans'] ), 12 + 6 * count( $s['src'] ) );
		foreach ( $s['src'] as $src ) {
			$ry = $pdf->GetY();
			$pdf->SetXY( 18, $ry );
			$pdf->SetFont( 'Helvetica', '', 8.5 );
			$pdf->rgb( '#1f2937', 'text' );
			$pdf->Cell( 58, 5.6, $pdf->tx( $src['host'] ), 0, 0 );
			$pdf->bar( 78, $ry + 1.6, 72, (float) $src['pct'], '#0d9488' );
			$pdf->SetXY( 152, $ry );
			$pdf->rgb( '#5b6b80', 'text' );
			$pdf->Cell( 42, 5.6, $pdf->tx( sprintf( $t( '%d%% van de antwoorden', '%d%% of answers' ), $src['pct'] ) ), 0, 1, 'R' );
		}
		$pdf->Ln( 1 );
		$pdf->para( $t( 'Een vermelding op deze sites is vaak de snelste weg naar meer AI-zichtbaarheid in deze sector.', 'A mention on these sites is often the fastest route to more AI visibility in this sector.' ), 8.5, '#5b6b80' );
	}
}

/** Brand header band with a title and the date (first page). */
function bv_gi_pdf_band( BV_GI_PDF $pdf, string $title, string $date ): void {
	$W = $pdf->GetPageWidth() - 32;
	$pdf->gradient( 0, 0, $pdf->GetPageWidth(), 34 );
	$pdf->SetXY( 16, 10 );
	$pdf->SetFont( 'Helvetica', 'B', 8.5 );
	$pdf->SetTextColor( 255, 255, 255 );
	$pdf->Cell( $W / 2, 4, 'B2B GEO INDEX', 0, 0 );
	$pdf->Cell( $W / 2, 4, 'BOOMING VENTURE', 0, 1, 'R' );
	$pdf->SetX( 16 );
	$pdf->SetFont( 'Helvetica', 'B', 17 );
	$pdf->Cell( $W * 0.7, 10, $pdf->tx( $title ), 0, 0 );
	$pdf->SetFont( 'Helvetica', '', 9 );
	$pdf->Cell( $W * 0.3, 10, $pdf->tx( $date ), 0, 1, 'R' );
}

/** Closing box with the Boardroom Quickscan and the index URL. */
function bv_gi_pdf_closing( BV_GI_PDF $pdf, bool $nl, string $short ): void {
	$t = static fn( $nl_text, $en_text ) => $nl ? $nl_text : $en_text;
	$W = $pdf->GetPageWidth() - 32;
	if ( $pdf->GetY() > 240 ) {
		$pdf->AddPage();
	}
	$pdf->Ln( 3 );
	$cy = $pdf->GetY();
	$pdf->rgb( '#f0fdfa' );
	$pdf->rgb( '#99f6e4', 'draw' );
	$pdf->SetLineWidth( 0.3 );
	$pdf->RoundedRect( 16, $cy, $W, 24, 3, 'DF' );
	$pdf->SetXY( 21, $cy + 4 );
	$pdf->SetFont( 'Helvetica', 'B', 10.5 );
	$pdf->rgb( '#0f766e', 'text' );
	$pdf->Cell( $W - 10, 5.5, $pdf->tx( $t( 'Hulp nodig bij de uitvoering?', 'Need help putting this into practice?' ) ), 0, 1 );
	$pdf->SetX( 21 );
	$pdf->SetFont( 'Helvetica', '', 9 );
	$pdf->rgb( '#1f2937', 'text' );
	$quick = home_url( $nl ? '/nl/boardroom-quickscan/' : '/boardroom-quickscan/' );
	$pdf->MultiCell( $W - 10, 4.8, $pdf->tx( sprintf( $t( 'Vraag een gratis Boardroom Quickscan van 15 minuten aan via %1$s. De volledige index, de methode en de open data staan op %2$s.', 'Request a free 15-minute Boardroom Quickscan at %1$s. The full index, the method and the open data are at %2$s.' ), preg_replace( '#^https?://(www\.)?#', '', untrailingslashit( $quick ) ), $short ) ), 0, 'L' );
	$pdf->Link( 16, $cy, $W, 24, $quick );
}

/** A report for a whole sector: averages, ranking, checks, weak spots and sources. */
function bv_gi_pdf_render_sector( array $s, bool $nl ): string {
	bv_gi_pdf_class();
	$t      = static fn( $nl_text, $en_text ) => $nl ? $nl_text : $en_text;
	$page   = get_page_by_path( $nl ? 'nl/b2b-geo-index' : 'b2b-geo-index' );
	$url    = ( $page && 'publish' === $page->post_status ) ? get_permalink( $page ) : home_url( '/' );
	$short  = preg_replace( '#^https?://(www\.)?#', '', untrailingslashit( $url ) );
	$today  = bv_gi_pdf_date( time(), $nl );
	$name   = $s['names'][ $nl ? 0 : 1 ];
	$listnm = 'nl' === $s['list'] ? $t( 'Top 200 Nederland', 'Top 200 Netherlands' ) : $t( 'Top 200 internationaal', 'Top 200 international' );

	$pdf = new BV_GI_PDF( 'P', 'mm', 'A4' );
	$pdf->SetMargins( 16, 16, 16 );
	$pdf->SetAutoPageBreak( true, 22 );
	$pdf->AliasNbPages();
	$pdf->SetTitle( $t( 'Sectorrapport ', 'Sector report ' ) . $name, true );
	$pdf->SetAuthor( 'Booming Venture', true );
	$pdf->SetCreator( 'B2B GEO Index', true );
	$pdf->title_small = 'B2B GEO Index  |  ' . $name;
	$pdf->foot        = sprintf( $t( 'B2B GEO Index van Booming Venture  |  %s  |  gemaakt op %s', 'B2B GEO Index by Booming Venture  |  %s  |  made on %s' ), $short, $today );
	$pdf->foot_url    = $url;
	$pdf->AddPage();
	$W = $pdf->GetPageWidth() - 32;
	bv_gi_pdf_band( $pdf, $t( 'Sectorrapport AI-vindbaarheid', 'Sector report AI search readiness' ), $today );

	$pdf->SetXY( 16, 44 );
	$pdf->rgb( '#0c1a2e', 'text' );
	$pdf->SetFont( 'Helvetica', 'B', 20 );
	$pdf->Cell( $W, 9, $pdf->tx( $name ), 0, 1 );
	$pdf->SetX( 16 );
	$pdf->SetFont( 'Helvetica', '', 9.5 );
	$pdf->rgb( '#5b6b80', 'text' );
	$sub = [ $listnm, sprintf( $t( '%d bedrijven', '%d companies' ), count( $s['rows'] ) ) ];
	if ( $s['measured'] ) {
		$sub[] = sprintf( $t( 'gemeten op %s', 'measured on %s' ), bv_gi_pdf_date( $s['measured'], $nl ) );
	}
	$pdf->Cell( $W, 5, $pdf->tx( implode( '  |  ', $sub ) ), 0, 1 );

	/* Card: sector average against the list and the other list */
	$y = 64;
	$pdf->rgb( '#f0f9ff' );
	$pdf->rgb( '#bae6fd', 'draw' );
	$pdf->SetLineWidth( 0.3 );
	$pdf->RoundedRect( 16, $y, $W, 36, 3, 'DF' );
	$pdf->SetXY( 22, $y + 6 );
	$pdf->rgb( '#0369a1', 'text' );
	$pdf->SetFont( 'Helvetica', 'B', 40 );
	$big = null === $s['avg'] ? '-' : (string) $s['avg'];
	$sw  = $pdf->GetStringWidth( $big );
	$pdf->Cell( $sw + 1, 16, $big, 0, 0 );
	$pdf->SetFont( 'Helvetica', '', 11 );
	$pdf->rgb( '#5b6b80', 'text' );
	$pdf->SetXY( 22 + $sw + 1, $y + 13.5 );
	$pdf->Cell( 16, 6, '/ 100', 0, 0 );
	$pdf->SetXY( 22, $y + 24 );
	$pdf->SetFont( 'Helvetica', 'B', 8.5 );
	$pdf->Cell( 70, 5, $pdf->tx( $t( 'GEMIDDELDE WEBSITESCORE', 'AVERAGE WEBSITE SCORE' ) ), 0, 0 );
	$rx     = 16 + $W * 0.47;
	$leader = $s['rows'][0];
	$lines  = [
		sprintf( $t( 'Hele lijst: %d', 'Whole list: %d' ), (int) $s['avg_list'] ),
		null !== $s['avg_other'] ? sprintf( $t( 'Zelfde sector in de %1$s: %2$d', 'Same sector in the %1$s: %2$d' ), 'nl' === $s['list'] ? $t( 'internationale lijst', 'international list' ) : $t( 'Nederlandse lijst', 'Dutch list' ), $s['avg_other'] ) : '',
		sprintf( $t( 'Leider: %1$s (%2$d)', 'Leader: %1$s (%2$d)' ), $leader['name'], $leader['score'] ),
	];
	$pdf->SetFont( 'Helvetica', '', 9 );
	$pdf->rgb( '#1f2937', 'text' );
	$ly = $y + 6;
	foreach ( array_filter( $lines ) as $line ) {
		$pdf->SetXY( $rx, $ly );
		$pdf->Cell( 16 + $W - $rx - 6, 5, $pdf->tx( $line ), 0, 0 );
		$ly += 5.5;
	}
	$pdf->bar( $rx, $y + 26, 16 + $W - $rx - 6, (float) ( $s['avg'] ?? 0 ) );
	$pdf->SetY( $y + 40 );

	bv_gi_pdf_sector( $pdf, $s, $nl );
	bv_gi_pdf_closing( $pdf, $nl, $short );
	return $pdf->Output( 'S' );
}

/* ------------------------------------------------------------------ download route */

add_action( 'rest_api_init', static function () {
	register_rest_route( 'bv/v1', '/geo-pdf', [
		'methods'             => 'GET',
		'permission_callback' => '__return_true',
		'args'                => [
			'domain' => [ 'type' => 'string', 'default' => '' ],
			'list'   => [ 'type' => 'string', 'default' => '' ],
			'lang'   => [ 'type' => 'string', 'default' => 'en' ],
			'sector' => [ 'type' => 'string', 'default' => '' ],
		],
		'callback'            => static function ( WP_REST_Request $req ) {
			$raw    = strtolower( trim( (string) $req['domain'] ) );
			$raw    = preg_replace( '#^[a-z]+://#', '', $raw );
			$domain = preg_replace( '#^www\.#', '', preg_replace( '#[/?\#:].*$#', '', $raw ) );
			$sector = sanitize_key( (string) $req['sector'] );
			if ( '' !== $domain && ! preg_match( '/^(?=.{4,253}$)([a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,24}$/', $domain ) ) {
				return new WP_Error( 'bv_domain', 'invalid domain', [ 'status' => 400 ] );
			}
			$nl   = 'nl' === $req['lang'];
			$list = in_array( $req['list'], [ 'nl', 'intl' ], true ) ? (string) $req['list'] : ( $nl ? 'nl' : 'intl' );
			$ip   = preg_replace( '/[^0-9a-f:.]/i', '', (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ) );
			$key  = 'bv_gi_pdf_' . md5( $ip );
			$n    = (int) get_transient( $key );
			if ( $n >= 30 ) {
				return new WP_Error( 'bv_rate', 'too many reports', [ 'status' => 429 ] );
			}
			if ( '' === $domain ) {
				/* No domain: a report for the whole sector. */
				$s = bv_gi_sector_data( $list, $sector );
				if ( ! $s ) {
					return new WP_Error( 'bv_nodata', 'unknown sector', [ 'status' => 404 ] );
				}
				set_transient( $key, $n + 1, HOUR_IN_SECONDS );
				$pdf  = bv_gi_pdf_render_sector( $s, $nl );
				$file = sanitize_file_name( 'geo-index-sector-' . sanitize_title( $s['names'][1] ) . '-' . $list . '-' . wp_date( 'Y-m-d' ) . '.pdf' );
			} else {
				$d = bv_gi_pdf_data( $domain, $list, $sector );
				if ( ! $d ) {
					return new WP_Error( 'bv_nodata', $nl ? 'Doe eerst de check voor dit domein.' : 'Run the check for this domain first.', [ 'status' => 404 ] );
				}
				set_transient( $key, $n + 1, HOUR_IN_SECONDS );
				$pdf  = bv_gi_pdf_render( $d, $nl );
				$file = sanitize_file_name( 'geo-index-' . $domain . '-' . wp_date( 'Y-m-d' ) . '.pdf' );
			}
			nocache_headers();
			header( 'Content-Type: application/pdf' );
			header( 'Content-Disposition: attachment; filename="' . $file . '"' );
			header( 'Content-Length: ' . strlen( $pdf ) );
			header( 'X-Robots-Tag: noindex, nofollow' );
			echo $pdf; // phpcs:ignore WordPress.Security.EscapeOutput -- binary PDF
			exit;
		},
	] );
} );
