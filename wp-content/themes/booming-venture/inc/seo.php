<?php
/**
 * SEO — JSON-LD schema, OpenGraph fallbacks, meta description.
 * Plays nicely with Rank Math / Yoast (they win when present).
 *
 * @package BoomingVenture
 */

defined( 'ABSPATH' ) || exit;

function bv_seo_active_plugin_handles_meta(): bool {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' );
}

/* JSON-LD: Organization + WebSite + per-page graph.
 * Yoast and Rank Math both emit their own Organization/WebSite/Article graphs,
 * so when either is active we step back to avoid duplicate-schema warnings in
 * Google Search Console. Override with `add_filter( 'bv_emit_jsonld', '__return_true' )`. */
add_action( 'wp_head', function () {
	if ( is_admin() ) return;
	if ( apply_filters( 'bv_emit_jsonld', ! bv_seo_active_plugin_handles_meta() ) === false ) return;

	$site_url = home_url( '/' );
	$logo_id  = (int) get_theme_mod( 'custom_logo' );
	$logo_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : BV_THEME_URI . '/assets/images/logo.png';

	$graph = [];

	$graph[] = [
		'@type'         => 'Organization',
		'@id'           => $site_url . '#organization',
		'name'          => 'Booming Venture',
		'legalName'     => 'Booming Venture',
		'url'           => $site_url,
		'logo'          => $logo_url,
		'description'   => 'AI-powered marketing strategies and business consulting from Rotterdam. We help premium brands grow with performance marketing that delivers results.',
		'email'         => 'info@boomingventure.com',
		'taxID'         => 'NL005211648B76',
		'vatID'         => 'NL005211648B76',
		'identifier'    => [
			[ '@type' => 'PropertyValue', 'propertyID' => 'KvK', 'value' => '96442018' ],
			[ '@type' => 'PropertyValue', 'propertyID' => 'VAT', 'value' => 'NL005211648B76' ],
		],
		'address'       => [
			'@type'           => 'PostalAddress',
			'streetAddress'   => 'Breedveldsingel 1',
			'postalCode'      => '3055 PG',
			'addressLocality' => 'Rotterdam',
			'addressCountry'  => 'NL',
		],
		'contactPoint'  => [
			'@type'       => 'ContactPoint',
			'contactType' => 'customer support',
			'email'       => 'info@boomingventure.com',
			'areaServed'  => [ 'NL', 'BE', 'DE', 'EU' ],
			'availableLanguage' => [ 'English', 'Dutch' ],
		],
		'sameAs'        => apply_filters( 'bv_organization_sameas', [
			'https://www.linkedin.com/company/booming-venture/',
			/* Add Wikipedia, Wikidata, Crunchbase, GitHub, X/Twitter when they exist. */
		] ),
		'knowsAbout'    => [ 'AI marketing', 'Performance marketing', 'Conversion rate optimization', 'Growth strategy', 'Marketing automation', 'B2B SaaS growth' ],
		'areaServed'    => [
			[ '@type' => 'Country', 'name' => 'Netherlands' ],
			[ '@type' => 'AdministrativeArea', 'name' => 'European Union' ],
		],
		'foundingDate'  => '2024',
		'foundingLocation' => [ '@type' => 'Place', 'name' => 'Rotterdam, Netherlands' ],
	];

	$graph[] = [
		'@type'           => 'WebSite',
		'@id'             => $site_url . '#website',
		'url'             => $site_url,
		'name'            => 'Booming Venture',
		'description'     => 'Performance. Personality. Powered by AI.',
		'publisher'       => [ '@id' => $site_url . '#organization' ],
		'inLanguage'      => get_bloginfo( 'language' ),
		'potentialAction' => [
			'@type'       => 'SearchAction',
			'target'      => [
				'@type'       => 'EntryPoint',
				'urlTemplate' => $site_url . '?s={search_term_string}',
			],
			'query-input' => 'required name=search_term_string',
		],
	];

	if ( is_singular( 'post' ) ) {
		global $post;

		$author_id  = (int) $post->post_author;
		$author_url = get_author_posts_url( $author_id );
		$author_name = get_the_author_meta( 'display_name', $author_id );

		$author_node = [
			'@type'    => 'Person',
			'@id'      => $author_url . '#person',
			'name'     => $author_name,
			'url'      => $author_url,
			'jobTitle' => get_the_author_meta( 'description', $author_id ) ? null : 'Marketing Consultant',
			'worksFor' => [ '@id' => $site_url . '#organization' ],
		];

		/* Person sameAs: from user_meta keys (linkedin, twitter, github, scholar). */
		$person_sameas = array_filter( [
			get_the_author_meta( 'linkedin', $author_id ) ?: get_the_author_meta( 'user_url', $author_id ),
			get_the_author_meta( 'twitter', $author_id ),
			get_the_author_meta( 'github', $author_id ),
			get_the_author_meta( 'scholar', $author_id ),
		] );
		if ( $person_sameas ) $author_node['sameAs'] = array_values( $person_sameas );
		$author_node = array_filter( $author_node );

		$graph[] = $author_node;

		$body_words = str_word_count( wp_strip_all_tags( $post->post_content ) );

		$graph[] = [
			'@type'            => 'BlogPosting',
			'@id'              => get_permalink() . '#article',
			'mainEntityOfPage' => get_permalink(),
			'headline'         => get_the_title(),
			'description'      => get_the_excerpt(),
			'datePublished'    => get_the_date( 'c' ),
			'dateModified'     => get_the_modified_date( 'c' ),
			'image'            => get_the_post_thumbnail_url( $post, 'full' ) ?: $logo_url,
			'author'           => [ '@id' => $author_url . '#person' ],
			'publisher'        => [ '@id' => $site_url . '#organization' ],
			'inLanguage'       => get_bloginfo( 'language' ),
			'articleSection'   => wp_get_post_categories( $post->ID, [ 'fields' => 'names' ] ),
			'keywords'         => wp_get_post_tags( $post->ID, [ 'fields' => 'names' ] ),
			'wordCount'        => $body_words,
		];

		/* Breadcrumb */
		$home   = [ 'name' => 'Home', 'url' => $site_url ];
		$blog   = [ 'name' => 'Blog', 'url' => $site_url . 'blog/' ];
		$crumbs = [ $home, $blog, [ 'name' => get_the_title(), 'url' => get_permalink() ] ];
		$items  = [];
		foreach ( $crumbs as $i => $c ) {
			$items[] = [
				'@type'    => 'ListItem',
				'position' => $i + 1,
				'name'     => $c['name'],
				'item'     => $c['url'],
			];
		}
		$graph[] = [ '@type' => 'BreadcrumbList', 'itemListElement' => $items ];

		/* FAQPage schema — parse the post body's "Frequently asked questions"
		 * section, extracting H3 + paragraph pairs. */
		$faq_items = bv_extract_faq_from_content( $post->post_content );
		if ( count( $faq_items ) >= 2 ) {
			$graph[] = [
				'@type'      => 'FAQPage',
				'@id'        => get_permalink() . '#faq',
				'mainEntity' => array_map( function ( $qa ) {
					return [
						'@type'          => 'Question',
						'name'           => $qa[0],
						'acceptedAnswer' => [ '@type' => 'Answer', 'text' => $qa[1] ],
					];
				}, $faq_items ),
			];
		}

		/* HowTo schema — if the post is "How to X" or contains an Ordered list
		 * starting with "Step 1" style numbered headings. */
		$howto = bv_extract_howto_from_content( $post->post_content, get_the_title() );
		if ( $howto && count( $howto['step'] ) >= 3 ) {
			$graph[] = $howto;
		}
	}

	if ( is_singular( 'service' ) ) {
		$graph[] = [
			'@type'       => 'Service',
			'@id'         => get_permalink() . '#service',
			'name'        => get_the_title(),
			'description' => get_the_excerpt(),
			'provider'    => [ '@id' => $site_url . '#organization' ],
			'areaServed'  => 'Netherlands',
		];
	}

	/* SoftwareApplication schema for the on-site calculator pages. */
	if ( is_page( [ 'funnel-calculator', 'roi-forecaster' ] ) ) {
		$is_funnel = is_page( 'funnel-calculator' );
		$graph[] = [
			'@type'            => 'SoftwareApplication',
			'@id'              => get_permalink() . '#app',
			'name'             => $is_funnel ? 'Funnel Leak Calculator' : 'ROI Forecaster',
			'description'      => $is_funnel
				? 'Interactive calculator that identifies where revenue is leaking in your sales funnel by comparing each stage against B2B benchmarks.'
				: 'Interactive 12-month ROI forecaster that models cumulative revenue from a marketing programme based on traffic, conversion rate, AOV, and LTV inputs.',
			'applicationCategory' => 'BusinessApplication',
			'operatingSystem'  => 'Web',
			'url'              => get_permalink(),
			'offers'           => [ '@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'EUR' ],
			'publisher'        => [ '@id' => $site_url . '#organization' ],
			'inLanguage'       => get_bloginfo( 'language' ),
			'isAccessibleForFree' => true,
		];
	}

	$payload = [ '@context' => 'https://schema.org', '@graph' => $graph ];
	echo "\n<script type=\"application/ld+json\">" . wp_json_encode( $payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "</script>\n";
}, 5 );

/* OpenGraph / Twitter card fallbacks — skip if SEO plugin present. */
add_action( 'wp_head', function () {
	if ( bv_seo_active_plugin_handles_meta() ) return;
	if ( is_admin() ) return;

	$title = wp_get_document_title();
	$desc  = is_singular() ? get_the_excerpt() : get_bloginfo( 'description' );
	$url   = is_singular() ? get_permalink() : home_url( add_query_arg( null, null ) );
	$img   = is_singular() && has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'bv-hero' ) : BV_THEME_URI . '/assets/images/og-default.jpg';

	printf( "<meta name=\"description\" content=\"%s\">\n", esc_attr( wp_strip_all_tags( $desc ) ) );
	printf( "<meta property=\"og:type\" content=\"%s\">\n", is_singular( 'post' ) ? 'article' : 'website' );
	printf( "<meta property=\"og:title\" content=\"%s\">\n", esc_attr( $title ) );
	printf( "<meta property=\"og:description\" content=\"%s\">\n", esc_attr( wp_strip_all_tags( $desc ) ) );
	printf( "<meta property=\"og:url\" content=\"%s\">\n", esc_url( $url ) );
	printf( "<meta property=\"og:image\" content=\"%s\">\n", esc_url( $img ) );
	printf( "<meta property=\"og:site_name\" content=\"%s\">\n", esc_attr( get_bloginfo( 'name' ) ) );
	printf( "<meta name=\"twitter:card\" content=\"summary_large_image\">\n" );
	printf( "<meta name=\"twitter:title\" content=\"%s\">\n", esc_attr( $title ) );
	printf( "<meta name=\"twitter:description\" content=\"%s\">\n", esc_attr( wp_strip_all_tags( $desc ) ) );
	printf( "<meta name=\"twitter:image\" content=\"%s\">\n", esc_url( $img ) );
}, 6 );

/* Canonical for paged archives / search. */
add_action( 'wp_head', 'rel_canonical' );

/* Robots: keep WordPress' default sitemap (wp-sitemap.xml). */
add_filter( 'wp_sitemaps_max_urls', fn () => 1000 );


/* ============================================================
 * Helpers: extract FAQPage and HowTo entities from post content
 * so we can emit JSON-LD without authors hand-editing schema.
 * ============================================================ */

/**
 * Find the "Frequently asked questions" H2 and parse the following
 * H3 + paragraph pairs until the next H2 or end of content.
 *
 * @return array<int,array{0:string,1:string}>
 */
function bv_extract_faq_from_content( string $html ): array {
	if ( ! preg_match( '/<h2[^>]*>\s*Frequently asked questions\s*<\/h2>(.*?)(?=<h2|\Z)/is', $html, $m ) ) {
		return [];
	}
	$section = $m[1];
	if ( ! preg_match_all( '/<h3[^>]*>(.*?)<\/h3>\s*<!--\s*\/wp:heading\s*-->\s*<!--\s*wp:paragraph\s*-->\s*<p[^>]*>(.*?)<\/p>/is', $section, $matches, PREG_SET_ORDER ) ) {
		/* Fallback: simpler H3 + P match without block-comment scaffolding. */
		preg_match_all( '/<h3[^>]*>(.*?)<\/h3>\s*<p[^>]*>(.*?)<\/p>/is', $section, $matches, PREG_SET_ORDER );
	}
	$out = [];
	foreach ( $matches as $m2 ) {
		$q = trim( wp_strip_all_tags( $m2[1] ) );
		$a = trim( wp_strip_all_tags( $m2[2] ) );
		if ( $q && $a ) $out[] = [ $q, $a ];
	}
	return $out;
}

/**
 * Treat ordered lists with explicit step language as a HowTo when the
 * post title starts with "How to" (or similar imperative pattern).
 */
function bv_extract_howto_from_content( string $html, string $title ): ?array {
	if ( ! preg_match( '/^(how to|how-to|step-by-step|guide to|tutorial)/i', trim( $title ) ) ) {
		return null;
	}

	if ( ! preg_match( '/<ol[^>]*class="[^"]*wp-block-list[^"]*"[^>]*>(.*?)<\/ol>/is', $html, $m ) ) {
		return null;
	}

	preg_match_all( '/<li[^>]*>(.*?)<\/li>/is', $m[1], $li_matches );
	if ( empty( $li_matches[1] ) || count( $li_matches[1] ) < 3 ) return null;

	$steps = [];
	foreach ( $li_matches[1] as $i => $li ) {
		$text = trim( wp_strip_all_tags( $li ) );
		if ( ! $text ) continue;
		/* If item starts with bold "Title.", use that as the step name. */
		$name = '';
		if ( preg_match( '/^([^.]+?)\.\s+(.*)$/', $text, $sm ) ) {
			$name = trim( $sm[1] );
			$text = trim( $sm[2] );
		}
		$steps[] = [
			'@type'    => 'HowToStep',
			'position' => $i + 1,
			'name'     => $name ?: ( 'Step ' . ( $i + 1 ) ),
			'text'     => $text,
		];
	}

	return [
		'@type'       => 'HowTo',
		'@id'         => get_permalink() . '#howto',
		'name'        => $title,
		'description' => get_the_excerpt(),
		'step'        => $steps,
	];
}
