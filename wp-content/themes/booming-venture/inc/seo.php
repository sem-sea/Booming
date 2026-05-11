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

/* JSON-LD: Organization + WebSite + per-page graph. */
add_action( 'wp_head', function () {
	if ( is_admin() ) return;

	$site_url = home_url( '/' );
	$logo_id  = (int) get_theme_mod( 'custom_logo' );
	$logo_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : BV_THEME_URI . '/assets/images/logo.png';

	$graph = [];

	$graph[] = [
		'@type'         => 'Organization',
		'@id'           => $site_url . '#organization',
		'name'          => 'Booming Venture',
		'url'           => $site_url,
		'logo'          => $logo_url,
		'description'   => 'AI-powered marketing strategies and business consulting from Rotterdam. We help premium brands grow with performance marketing that delivers results.',
		'email'         => 'info@boomingventure.com',
		'address'       => [
			'@type'           => 'PostalAddress',
			'streetAddress'   => 'Breedveldsingel 1',
			'postalCode'      => '3055 PG',
			'addressLocality' => 'Rotterdam',
			'addressCountry'  => 'NL',
		],
		'sameAs'        => [
			'https://www.linkedin.com/company/booming-venture/',
		],
		'areaServed'    => 'Netherlands',
		'foundingDate'  => '2024',
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
		$graph[] = [
			'@type'            => 'BlogPosting',
			'@id'              => get_permalink() . '#article',
			'mainEntityOfPage' => get_permalink(),
			'headline'         => get_the_title(),
			'description'      => get_the_excerpt(),
			'datePublished'    => get_the_date( 'c' ),
			'dateModified'     => get_the_modified_date( 'c' ),
			'image'            => get_the_post_thumbnail_url( $post, 'full' ) ?: $logo_url,
			'author'           => [ '@type' => 'Person', 'name' => get_the_author_meta( 'display_name', $post->post_author ) ],
			'publisher'        => [ '@id' => $site_url . '#organization' ],
			'articleSection'   => wp_get_post_categories( $post->ID, [ 'fields' => 'names' ] ),
		];

		if ( is_singular( 'post' ) ) {
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
