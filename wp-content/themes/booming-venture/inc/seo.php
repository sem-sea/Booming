<?php
/**
 * SEO, JSON-LD schema, OpenGraph fallbacks, meta description.
 * Plays nicely with Rank Math / Yoast (they win when present).
 *
 * @package BoomingVenture
 */

defined( 'ABSPATH' ) || exit;

function bv_seo_active_plugin_handles_meta(): bool {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' );
}

/**
 * Organization and founder nodes, shared by the theme graph and the Yoast
 * graph (1.21.0). No private e-mail address or phone number in the Person node.
 */
/** Logo for schema: custom logo, else the site icon, else the brand mark the theme uses as favicon. */
function bv_schema_logo_url(): string {
	$logo_id = (int) get_theme_mod( 'custom_logo' );
	$url     = $logo_id ? (string) wp_get_attachment_image_url( $logo_id, 'full' ) : '';
	if ( ! $url && function_exists( 'get_site_icon_url' ) ) {
		$url = (string) get_site_icon_url( 512 );
	}
	return $url ?: home_url( (string) apply_filters( 'bv_site_icon_url', '/wp-content/uploads/2026/05/icon-booming-venture.png' ) );
}

function bv_schema_identity_nodes(): array {
	$site_url = home_url( '/' );
	$logo_url = bv_schema_logo_url();

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
		'founder'       => [ '@id' => $site_url . '#person-ben-verschuur' ],
	];

	/* Person schema for the founder. LLMs and search engines use this
	 * to attribute authority, biography, and credentials to a named
	 * human, which materially lifts citation rate for shortlist queries. */
	$graph[] = [
		'@type'         => 'Person',
		'@id'           => $site_url . '#person-ben-verschuur',
		'name'          => 'Ben Verschuur',
		'givenName'     => 'Ben',
		'familyName'    => 'Verschuur',
		'jobTitle'      => 'Founder and AI Marketing Strategist',
		'description'   => 'Ben Verschuur is the founder of Booming Venture and a hands-on AI marketing strategist based in Rotterdam, the Netherlands. He helps premium brands and B2B SaaS companies turn AI into a working operating system for growth, with 15+ years of experience leading performance and growth marketing at Zeelander Yachts, DPDK Digital Agency, Knab, Aegon, and Hallmark Cards.',
		'url'           => $site_url . 'about/',
		'image'         => $site_url . 'wp-content/uploads/2026/05/Ben.png',
		'nationality'   => [ '@type' => 'Country', 'name' => 'Netherlands' ],
		'homeLocation'  => [ '@type' => 'Place', 'name' => 'Rotterdam, the Netherlands' ],
		'worksFor'      => [ '@id' => $site_url . '#organization' ],
		'founderOf'     => [
			[ '@type' => 'Organization', 'name' => 'Booming Venture', 'url' => $site_url ],
			[ '@type' => 'Organization', 'name' => 'VerantwoordAI', 'url' => 'https://verantwoordai.nl/' ],
			[ '@type' => 'Organization', 'name' => 'PromptingPro' ],
		],
		'alumniOf'      => [
			[ '@type' => 'EducationalOrganization', 'name' => 'Erasmus University Rotterdam', 'url' => 'https://www.eur.nl/' ],
		],
		'knowsAbout'    => [
			'AI marketing', 'Enterprise AI', 'AI Governance', 'AI Agents',
			'Performance marketing', 'Growth marketing', 'Conversion rate optimization',
			'Marketing automation', 'Generative Engine Optimization', 'B2B SaaS growth',
			'Premium brand strategy', 'EU AI Act compliance',
		],
		'hasOccupation' => [
			'@type'             => 'Occupation',
			'name'              => 'AI Marketing Strategist',
			'occupationLocation'=> [ '@type' => 'City', 'name' => 'Rotterdam' ],
			'skills'            => 'AI strategy, performance marketing, growth marketing, CRO, marketing automation, GEO, AI governance',
		],
		'award'         => [
			'#1 Digital Excellence Sales / Online Marketing',
			'Platinum MarCom Award , Product Launch',
			'Platinum MarCom Award , Digital Marketing',
			'AVA Digital Awards Platinum Winner 2022 , Best Performance Team Achievement',
		],
		'sameAs'        => [
			'https://www.linkedin.com/in/benverschuur',
			'https://boomingventure.com/about/',
			'https://verantwoordai.nl/',
		],
	];

	return $graph;
}

/* JSON-LD: Organization + WebSite + per-page graph.
 * Yoast and Rank Math both emit their own Organization/WebSite/Article graphs,
 * so when either is active we step back to avoid duplicate-schema warnings in
 * Google Search Console. Override with `add_filter( 'bv_emit_jsonld', '__return_true' )`. */
add_action( 'wp_head', function () {
	if ( is_admin() ) return;
	if ( apply_filters( 'bv_emit_jsonld', ! bv_seo_active_plugin_handles_meta() ) === false ) return;

	$site_url = home_url( '/' );
	$logo_url = bv_schema_logo_url();
	$graph    = bv_schema_identity_nodes();

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

		/* FAQPage schema, parse the post body's "Frequently asked questions"
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

		/* HowTo schema, if the post is "How to X" or contains an Ordered list
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

	$graph = array_merge( $graph, bv_gi_schema_nodes() );

	$payload = [ '@context' => 'https://schema.org', '@graph' => $graph ];
	echo "\n<script type=\"application/ld+json\">" . wp_json_encode( $payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "</script>\n";
}, 5 );

/* OpenGraph / Twitter card fallbacks, skip if SEO plugin present. */
add_action( 'wp_head', function () {
	if ( bv_seo_active_plugin_handles_meta() ) return;
	if ( is_admin() ) return;

	$title = wp_get_document_title();
	$desc  = bv_meta_description();
	$url   = is_singular() ? get_permalink() : home_url( add_query_arg( null, null ) );
	$img   = is_singular() && has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'bv-hero' ) : ( function_exists( 'bv_image' ) ? bv_image( 'service-1' ) : 'https://boomingventure.com/wp-content/uploads/2026/05/4e357139-5a7e-4336-8796-94013f33dc3d.png' );

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

/* FAQPage next to Yoast (1.11.0). Yoast builds the graph and the theme's
 * own graph stands down, so the FAQ piece is added to Yoast's graph from
 * the same "Frequently asked questions" / "Veelgestelde vragen" section. */
add_filter( 'wpseo_schema_graph', function ( $graph ) {
	if ( ! is_singular( [ 'post', 'page' ] ) || ! is_array( $graph ) ) return $graph;
	$post  = get_post();
	$items = $post ? bv_extract_faq_from_content( (string) $post->post_content ) : [];
	if ( count( $items ) < 2 ) return $graph;
	$url     = get_permalink( $post );
	$graph[] = [
		'@type'      => 'FAQPage',
		'@id'        => $url . '#faq',
		'isPartOf'   => [ '@id' => $url ],
		'inLanguage' => get_bloginfo( 'language' ),
		'mainEntity' => array_map( static function ( $qa ) {
			return [
				'@type'          => 'Question',
				'name'           => $qa[0],
				'acceptedAnswer' => [ '@type' => 'Answer', 'text' => $qa[1] ],
			];
		}, $items ),
	];
	return $graph;
} );

/*
 * Organization and founder in Yoast's graph (1.21.0). Without a site
 * representation set in Yoast, its graph had no Organization and no sameAs,
 * so search engines, AI systems and the GEO Index check saw no company.
 * An Organization that Yoast does output keeps its fields and gets ours
 * added where it has none (sameAs, KvK, address).
 */
add_filter( 'wpseo_schema_graph', static function ( $graph ) {
	if ( ! is_array( $graph ) ) return $graph;
	foreach ( bv_schema_identity_nodes() as $node ) {
		$found = false;
		foreach ( $graph as $i => $n ) {
			if ( is_array( $n ) && ( $n['@id'] ?? '' ) === $node['@id'] ) {
				$graph[ $i ] = $n + $node;
				$found       = true;
			}
		}
		if ( ! $found ) {
			$graph[] = $node;
		}
	}
	foreach ( $graph as $i => $n ) {
		if ( is_array( $n ) && 'WebSite' === ( $n['@type'] ?? '' ) && empty( $n['publisher'] ) ) {
			$graph[ $i ]['publisher'] = [ '@id' => home_url( '/' ) . '#organization' ];
		}
	}
	return $graph;
}, 5 );

/*
 * B2B GEO Index pages (1.16.0): Dataset (Google Dataset Search, with the CSV
 * downloads as distribution) and the top 10 of each list as ItemList, built
 * from the live measurements. Added to Yoast's graph, or to the theme graph
 * when no SEO plugin runs.
 */
function bv_gi_schema_nodes(): array {
	if ( ! is_singular( 'page' ) || ! function_exists( 'bv_gi_ranked' ) ) return [];
	$post = get_post();
	if ( ! $post || ! has_block( 'booming-venture/geo-index', $post ) ) return [];
	$nl   = function_exists( 'bv_post_lang' ) && 'nl' === bv_post_lang( $post->ID );
	$t    = static fn( $a, $b ) => $nl ? $a : $b;
	$url  = get_permalink( $post );
	$org  = [ '@type' => 'Organization', '@id' => home_url( '/' ) . '#organization', 'name' => 'Booming Venture', 'url' => home_url( '/' ) ];
	$meta = (array) get_option( 'bv_gi_meta', [] );
	$ai   = (array) get_option( 'bv_gi_ai_results', [] );
	$done = max( (int) ( $meta['completed'] ?? 0 ), (int) ( $ai['completed'] ?? 0 ) );
	$data = [];
	foreach ( [ 'nl', 'intl' ] as $list ) {
		$data[] = [
			'@type'          => 'DataDownload',
			'name'           => 'nl' === $list ? $t( 'Top 200 Nederland (CSV)', 'Top 200 Netherlands (CSV)' ) : $t( 'Top 200 internationaal (CSV)', 'Top 200 international (CSV)' ),
			'encodingFormat' => 'text/csv',
			'contentUrl'     => add_query_arg( [ 'list' => $list, 'format' => 'csv' ], rest_url( 'bv/v1/geo-index' ) ),
		];
	}
	$dataset = [
		'@type'                => 'Dataset',
		'@id'                  => $url . '#dataset',
		'name'                 => $t( 'B2B GEO Index: AI-zichtbaarheid van 400 B2B-bedrijven', 'B2B GEO Index: AI visibility of 400 B2B companies' ),
		'description'          => $t(
			'Maandelijkse meting van 200 Nederlandse en 200 internationale B2B-bedrijven in dezelfde 20 sectoren (SBI 2025 en NACE Rev. 2.1): hoe vaak AI-assistenten ze noemen, met foutmarge, share of voice, toon en bronnen per sector, en hoe klaar hun websites zijn voor AI-zoekmachines, met zeven websitechecks en een score van 0 tot 100.',
			'Monthly measurement of 200 Dutch and 200 international B2B companies in the same 20 sectors (SBI 2025 and NACE Rev. 2.1): how often AI assistants name them, with margins of error, share of voice, tone and sources per sector, and how ready their websites are for AI search, with seven website checks and a score from 0 to 100.'
		),
		'url'                  => $url,
		'creator'              => $org,
		'publisher'            => $org,
		'isAccessibleForFree'  => true,
		'inLanguage'           => $nl ? 'nl-NL' : 'en',
		'keywords'             => [ 'GEO', 'generative engine optimization', 'AI search', 'B2B', 'ChatGPT', 'Claude', 'Gemini', 'Perplexity' ],
		'measurementTechnique' => $t(
			'Elke eerste werkdag van de maand: per categorie vijf koopvragen aan AI-assistenten via hun API, met de eigen zoekfunctie van elke assistent, een tweede ronde waar de uitkomst onzeker is en een foutmarge van 95% (Wilson), plus robots.txt, HTML zonder JavaScript, structured data, Wikidata, Wikipedia, sitemap-actualiteit, taalversies en llms.txt van elke website.',
			'On the first working day of every month: five buyer questions per category to AI assistants through their APIs, each using its own web search, a second round where the result is uncertain and a 95% margin of error (Wilson), plus robots.txt, HTML without JavaScript, structured data, Wikidata, Wikipedia, sitemap freshness, language versions and llms.txt of each website.'
		),
		'variableMeasured'     => [ 'GEO score', 'Margin of error', 'AI score', 'Visibility', 'Share of voice', 'First mention', 'Sentiment', 'Cited sources', 'Website score', 'AI crawler access', 'Text without JavaScript', 'Organization schema', 'Wikipedia article', 'Sitemap freshness', 'Language versions' ],
		'distribution'         => $data,
	];
	if ( $done ) {
		$dataset['dateModified'] = gmdate( 'c', $done );
	}
	if ( ! empty( $meta['first'] ) ) {
		$dataset['temporalCoverage'] = gmdate( 'Y-m-d', (int) $meta['first'] ) . '/..';
	}
	$nodes = [ $dataset ];
	foreach ( [ 'nl', 'intl' ] as $list ) {
		$rows = array_slice( bv_gi_ranked( $list ), 0, 10 );
		if ( ! $rows ) continue;
		$items = [];
		foreach ( $rows as $i => $r ) {
			$items[] = [ '@type' => 'ListItem', 'position' => $i + 1, 'item' => [ '@type' => 'Organization', 'name' => $r['name'], 'url' => 'https://' . $r['domain'] . '/' ] ];
		}
		$nodes[] = [
			'@type'           => 'ItemList',
			'@id'             => $url . '#top10-' . $list,
			'name'            => 'nl' === $list ? $t( 'B2B GEO Index: top 10 Nederland', 'B2B GEO Index: top 10 Netherlands' ) : $t( 'B2B GEO Index: top 10 internationaal', 'B2B GEO Index: top 10 international' ),
			'itemListOrder'   => 'https://schema.org/ItemListOrderDescending',
			'numberOfItems'   => count( $items ),
			'itemListElement' => $items,
		];
	}
	return $nodes;
}

add_filter( 'wpseo_schema_graph', static function ( $graph ) {
	return is_array( $graph ) ? array_merge( $graph, bv_gi_schema_nodes() ) : $graph;
} );

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
	if ( ! preg_match( '/<h2[^>]*>\s*(?:Frequently asked questions|Veelgestelde vragen)\s*<\/h2>(.*?)(?=<h2|\Z)/is', $html, $m ) ) {
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
