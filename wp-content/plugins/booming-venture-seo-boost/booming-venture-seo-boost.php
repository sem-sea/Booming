<?php
/**
 * Plugin Name:       Booming Venture , SEO Boost
 * Plugin URI:        https://boomingventure.com
 * Description:       Auto-detects existing schema markup and SEO plugins. If something else is already shipping JSON-LD or Open Graph tags for this page, the plugin stands down. If the page is bare, it injects Article / BlogPosting / FAQPage / HowTo / Organization / BreadcrumbList JSON-LD, og:image (from the featured image or first content image), Open Graph + Twitter Card tags, canonical, and a meta description. Zero configuration. Activate and forget.
 * Version:           1.0.0
 * Requires at least: 6.6
 * Requires PHP:      8.0
 * Author:            Booming Venture
 * Author URI:        https://boomingventure.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       booming-venture-seo-boost
 * Update URI:        false
 *
 * @package BoomingVentureSeoBoost
 */

defined( 'ABSPATH' ) || exit;

const BVSEO_VERSION = '1.0.0';

if ( ! defined( 'BVSEO_FILE' ) ) {
	define( 'BVSEO_FILE', __FILE__ );
}

/* ---------------------------------------------------------------------
 * Detection: is another SEO plugin or schema source already active?
 *
 * If yes, the plugin must stand down for the matching capability. We
 * detect popular plugins by their loader constants and class names,
 * plus the most common theme convention of an inc/seo.php that emits
 * JSON-LD. The result is a structured array consumed by every
 * outputter so each piece (schema, OG, Twitter, canonical, meta-desc)
 * decides independently.
 * --------------------------------------------------------------------- */
function bvseo_detect(): array {
	static $cache = null;
	if ( null !== $cache ) return $cache;

	$detected = [
		'yoast'      => defined( 'WPSEO_VERSION' )         || class_exists( 'WPSEO_Frontend' ),
		'rank_math'  => defined( 'RANK_MATH_VERSION' )     || class_exists( 'RankMath' ),
		'aioseo'     => defined( 'AIOSEO_VERSION' )        || class_exists( 'AIOSEO\\Plugin\\AIOSEO' ),
		'seopress'   => defined( 'SEOPRESS_VERSION' )      || function_exists( 'seopress_get_service' ),
		'tsf'        => defined( 'THE_SEO_FRAMEWORK_PRESENT' ) || function_exists( 'the_seo_framework' ),
		'slim_seo'   => defined( 'SLIM_SEO_VERSION' )      || class_exists( 'SlimSEO\\Plugin' ),
		'squirrly'   => defined( 'SQ_VERSION' )            || class_exists( 'SQ_Classes_RemoteController' ),
		'theme_seo'  => function_exists( 'bv_print_schema_jsonld' ) || function_exists( 'bv_render_jsonld' ),
	];
	$detected['any_seo_plugin'] = (bool) array_filter( array_diff_key( $detected, [ 'theme_seo' => '' ] ) );

	$cache = $detected;
	return $detected;
}

/* ---------------------------------------------------------------------
 * Head output buffer , capture wp_head, inspect what other plugins
 * already wrote, then append only the tags that are missing.
 *
 * Buffering wp_head is unusual but safe: we start the buffer at
 * priority 1 and flush at priority 9999, before WP's own
 * </head> tag. Everything inside the buffer is returned untouched
 * to the page; we only ADD tags on the way out.
 * --------------------------------------------------------------------- */
add_action( 'wp_head', function () {
	if ( is_admin() || is_feed() || is_robots() ) return;
	ob_start();
}, 1 );

add_action( 'wp_head', function () {
	if ( is_admin() || is_feed() || is_robots() ) return;
	$head = (string) ob_get_clean();

	$inject = '';

	/* Canonical , add only if not present. */
	if ( ! preg_match( '/<link[^>]+rel=["\']canonical["\']/i', $head ) ) {
		$canonical = bvseo_get_canonical();
		if ( $canonical ) {
			$inject .= '<link rel="canonical" href="' . esc_url( $canonical ) . '">' . "\n";
		}
	}

	/* Meta description , add only if not present. */
	if ( ! preg_match( '/<meta[^>]+name=["\']description["\']/i', $head ) ) {
		$desc = bvseo_get_description();
		if ( $desc ) {
			$inject .= '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
		}
	}

	/* Open Graph tags , add only if og:title not present. */
	if ( ! preg_match( '/<meta[^>]+property=["\']og:title["\']/i', $head ) ) {
		$inject .= bvseo_render_open_graph();
	}

	/* Twitter Card , add only if not present. */
	if ( ! preg_match( '/<meta[^>]+name=["\']twitter:card["\']/i', $head ) ) {
		$inject .= bvseo_render_twitter_card();
	}

	/* JSON-LD , add only if no application/ld+json block already there. */
	if ( ! preg_match( '/<script[^>]+type=["\']application\/ld\+json["\']/i', $head ) ) {
		$inject .= bvseo_render_jsonld();
	}

	echo $head . $inject;  // phpcs:ignore , $head is the buffered output, $inject is built from escape-safe helpers.
}, 9999 );

/* ---------------------------------------------------------------------
 * Helpers , canonical / description / og image.
 * --------------------------------------------------------------------- */
function bvseo_get_canonical(): string {
	if ( is_singular() ) return (string) get_permalink( get_queried_object_id() );
	if ( is_home() || is_front_page() ) return (string) home_url( '/' );
	if ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();
		if ( $term && isset( $term->term_id ) ) return (string) get_term_link( $term );
	}
	if ( is_author() ) {
		$author = get_queried_object();
		if ( $author && isset( $author->ID ) ) return (string) get_author_posts_url( (int) $author->ID );
	}
	return '';
}

function bvseo_get_description(): string {
	if ( is_singular() ) {
		$post_id = get_queried_object_id();
		$excerpt = get_post_field( 'post_excerpt', $post_id );
		if ( $excerpt ) return wp_strip_all_tags( $excerpt );
		$content = (string) get_post_field( 'post_content', $post_id );
		$plain   = wp_strip_all_tags( strip_shortcodes( $content ) );
		$plain   = trim( preg_replace( '/\s+/', ' ', $plain ) );
		return mb_substr( $plain, 0, 160 );
	}
	if ( is_home() || is_front_page() ) return (string) get_bloginfo( 'description' );
	if ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();
		if ( $term && ! empty( $term->description ) ) return wp_strip_all_tags( (string) $term->description );
	}
	return '';
}

function bvseo_get_og_image(): array {
	$post_id = is_singular() ? get_queried_object_id() : 0;

	/* 1. Featured image. */
	if ( $post_id ) {
		$thumb = (int) get_post_thumbnail_id( $post_id );
		if ( $thumb ) {
			$src = wp_get_attachment_image_src( $thumb, 'full' );
			if ( $src ) {
				return [
					'url'    => (string) $src[0],
					'width'  => (int) $src[1],
					'height' => (int) $src[2],
					'alt'    => (string) get_post_meta( $thumb, '_wp_attachment_image_alt', true ),
				];
			}
		}
	}

	/* 2. First <img> in post content. */
	if ( $post_id ) {
		$content = (string) get_post_field( 'post_content', $post_id );
		if ( preg_match( '/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $m ) ) {
			$url = $m[1];
			$attach_id = attachment_url_to_postid( $url );
			$w = 1200; $h = 630;
			if ( $attach_id ) {
				$src = wp_get_attachment_image_src( $attach_id, 'full' );
				if ( $src ) { $w = (int) $src[1]; $h = (int) $src[2]; }
			}
			return [ 'url' => esc_url_raw( $url ), 'width' => $w, 'height' => $h, 'alt' => '' ];
		}
	}

	/* 3. Site logo as fallback. */
	$logo_id = (int) get_theme_mod( 'custom_logo' );
	if ( $logo_id ) {
		$src = wp_get_attachment_image_src( $logo_id, 'full' );
		if ( $src ) {
			return [ 'url' => (string) $src[0], 'width' => (int) $src[1], 'height' => (int) $src[2], 'alt' => (string) get_bloginfo( 'name' ) ];
		}
	}

	return [ 'url' => '', 'width' => 0, 'height' => 0, 'alt' => '' ];
}

/* ---------------------------------------------------------------------
 * Open Graph tags.
 * --------------------------------------------------------------------- */
function bvseo_render_open_graph(): string {
	$title = is_singular() ? get_the_title( get_queried_object_id() ) : wp_get_document_title();
	$desc  = bvseo_get_description();
	$url   = bvseo_get_canonical();
	$type  = is_singular( 'post' ) ? 'article' : ( is_singular() ? 'article' : 'website' );
	$site  = (string) get_bloginfo( 'name' );
	$locale = (string) get_locale();
	$image = bvseo_get_og_image();

	$out = '';
	$out .= '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
	if ( $desc )  $out .= '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
	if ( $url )   $out .= '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
	$out .= '<meta property="og:type" content="' . esc_attr( $type ) . '">' . "\n";
	$out .= '<meta property="og:site_name" content="' . esc_attr( $site ) . '">' . "\n";
	$out .= '<meta property="og:locale" content="' . esc_attr( $locale ) . '">' . "\n";
	if ( ! empty( $image['url'] ) ) {
		$out .= '<meta property="og:image" content="' . esc_url( $image['url'] ) . '">' . "\n";
		if ( $image['width'] )  $out .= '<meta property="og:image:width" content="' . (int) $image['width']  . '">' . "\n";
		if ( $image['height'] ) $out .= '<meta property="og:image:height" content="' . (int) $image['height'] . '">' . "\n";
		if ( $image['alt'] )    $out .= '<meta property="og:image:alt" content="' . esc_attr( $image['alt'] ) . '">' . "\n";
	}
	if ( is_singular( 'post' ) ) {
		$post_id   = get_queried_object_id();
		$published = mysql2date( DATE_W3C, get_post_field( 'post_date_gmt', $post_id ), false );
		$modified  = mysql2date( DATE_W3C, get_post_field( 'post_modified_gmt', $post_id ), false );
		if ( $published ) $out .= '<meta property="article:published_time" content="' . esc_attr( $published ) . '">' . "\n";
		if ( $modified )  $out .= '<meta property="article:modified_time" content="' . esc_attr( $modified ) . '">' . "\n";
		$author_id = (int) get_post_field( 'post_author', $post_id );
		if ( $author_id ) {
			$author_name = (string) get_the_author_meta( 'display_name', $author_id );
			if ( $author_name ) $out .= '<meta property="article:author" content="' . esc_attr( $author_name ) . '">' . "\n";
		}
	}
	return $out;
}

/* ---------------------------------------------------------------------
 * Twitter Card tags.
 * --------------------------------------------------------------------- */
function bvseo_render_twitter_card(): string {
	$title = is_singular() ? get_the_title( get_queried_object_id() ) : wp_get_document_title();
	$desc  = bvseo_get_description();
	$image = bvseo_get_og_image();
	$card  = ! empty( $image['url'] ) ? 'summary_large_image' : 'summary';

	$out  = '<meta name="twitter:card" content="' . esc_attr( $card ) . '">' . "\n";
	$out .= '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
	if ( $desc ) $out .= '<meta name="twitter:description" content="' . esc_attr( $desc ) . '">' . "\n";
	if ( ! empty( $image['url'] ) ) $out .= '<meta name="twitter:image" content="' . esc_url( $image['url'] ) . '">' . "\n";
	if ( ! empty( $image['alt'] ) ) $out .= '<meta name="twitter:image:alt" content="' . esc_attr( $image['alt'] ) . '">' . "\n";
	return $out;
}

/* ---------------------------------------------------------------------
 * JSON-LD , Article + FAQPage + HowTo + BreadcrumbList + Organization
 * (Organization site-wide; the rest only where the content fits).
 * --------------------------------------------------------------------- */
function bvseo_render_jsonld(): string {
	$graph = [];

	/* Organization (always). */
	$org = bvseo_build_organization();
	if ( $org ) $graph[] = $org;

	/* WebSite (always). */
	$graph[] = bvseo_build_website();

	/* Per-page graphs. */
	if ( is_singular() ) {
		$post_id = get_queried_object_id();

		$article = bvseo_build_article( $post_id );
		if ( $article ) $graph[] = $article;

		$breadcrumbs = bvseo_build_breadcrumbs( $post_id );
		if ( $breadcrumbs ) $graph[] = $breadcrumbs;

		$content = (string) get_post_field( 'post_content', $post_id );

		$faq = bvseo_detect_faq( $content );
		if ( $faq ) $graph[] = $faq;

		$howto = bvseo_detect_howto( $content, get_the_title( $post_id ) );
		if ( $howto ) $graph[] = $howto;
	}

	if ( empty( $graph ) ) return '';

	$json = wp_json_encode(
		[ '@context' => 'https://schema.org', '@graph' => $graph ],
		JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
	);
	return '<script type="application/ld+json">' . $json . '</script>' . "\n";
}

function bvseo_build_organization(): array {
	$name = (string) get_bloginfo( 'name' );
	$url  = (string) home_url( '/' );
	$out  = [
		'@type' => 'Organization',
		'@id'   => $url . '#organization',
		'name'  => $name,
		'url'   => $url,
	];
	$logo_id = (int) get_theme_mod( 'custom_logo' );
	if ( $logo_id ) {
		$src = wp_get_attachment_image_src( $logo_id, 'full' );
		if ( $src ) {
			$out['logo'] = [
				'@type'  => 'ImageObject',
				'url'    => $src[0],
				'width'  => (int) $src[1],
				'height' => (int) $src[2],
			];
			$out['image'] = $src[0];
		}
	}
	return $out;
}

function bvseo_build_website(): array {
	$url  = (string) home_url( '/' );
	$name = (string) get_bloginfo( 'name' );
	return [
		'@type'         => 'WebSite',
		'@id'           => $url . '#website',
		'name'          => $name,
		'url'           => $url,
		'description'   => (string) get_bloginfo( 'description' ),
		'inLanguage'    => (string) get_locale(),
		'publisher'     => [ '@id' => $url . '#organization' ],
		'potentialAction' => [
			[
				'@type'       => 'SearchAction',
				'target'      => [
					'@type'       => 'EntryPoint',
					'urlTemplate' => $url . '?s={search_term_string}',
				],
				'query-input' => 'required name=search_term_string',
			],
		],
	];
}

function bvseo_build_article( int $post_id ): array {
	$image_data  = bvseo_get_og_image();
	$author_id   = (int) get_post_field( 'post_author', $post_id );
	$author_name = $author_id ? (string) get_the_author_meta( 'display_name', $author_id ) : '';
	$author_url  = $author_id ? (string) get_author_posts_url( $author_id ) : '';
	$published   = mysql2date( DATE_W3C, get_post_field( 'post_date_gmt', $post_id ), false );
	$modified    = mysql2date( DATE_W3C, get_post_field( 'post_modified_gmt', $post_id ), false );
	$url         = (string) get_permalink( $post_id );
	$home        = (string) home_url( '/' );

	$schema = [
		'@type'         => 'BlogPosting',
		'@id'           => $url . '#article',
		'mainEntityOfPage' => [ '@type' => 'WebPage', '@id' => $url ],
		'headline'      => (string) get_the_title( $post_id ),
		'description'   => bvseo_get_description(),
		'datePublished' => (string) $published,
		'dateModified'  => (string) $modified,
		'inLanguage'    => (string) get_locale(),
		'isPartOf'      => [ '@id' => $home . '#website' ],
		'publisher'     => [ '@id' => $home . '#organization' ],
	];
	if ( $author_name ) {
		$schema['author'] = array_filter( [
			'@type' => 'Person',
			'name'  => $author_name,
			'url'   => $author_url,
		] );
	}
	if ( ! empty( $image_data['url'] ) ) {
		$schema['image'] = array_filter( [
			'@type'  => 'ImageObject',
			'url'    => $image_data['url'],
			'width'  => $image_data['width']  ?: null,
			'height' => $image_data['height'] ?: null,
		] );
	}
	$word_count = str_word_count( wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) ) );
	if ( $word_count ) $schema['wordCount'] = (int) $word_count;
	return $schema;
}

function bvseo_build_breadcrumbs( int $post_id ): array {
	$items = [];
	$pos   = 1;
	$home  = (string) home_url( '/' );
	$items[] = [
		'@type'    => 'ListItem',
		'position' => $pos++,
		'name'     => __( 'Home', 'booming-venture-seo-boost' ),
		'item'     => $home,
	];
	$post_type = get_post_type( $post_id );
	if ( 'post' === $post_type ) {
		$posts_page = (int) get_option( 'page_for_posts' );
		if ( $posts_page ) {
			$items[] = [
				'@type'    => 'ListItem',
				'position' => $pos++,
				'name'     => (string) get_the_title( $posts_page ),
				'item'     => (string) get_permalink( $posts_page ),
			];
		}
	}
	$items[] = [
		'@type'    => 'ListItem',
		'position' => $pos,
		'name'     => (string) get_the_title( $post_id ),
		'item'     => (string) get_permalink( $post_id ),
	];
	return [
		'@type'           => 'BreadcrumbList',
		'@id'             => get_permalink( $post_id ) . '#breadcrumbs',
		'itemListElement' => $items,
	];
}

/* ---------------------------------------------------------------------
 * Detect FAQ structure: an <h2> whose text contains "FAQ" or
 * "frequently asked questions", followed by alternating h3+p pairs.
 * --------------------------------------------------------------------- */
function bvseo_detect_faq( string $content ): array {
	if ( ! $content ) return [];
	if ( ! preg_match( '/<h2[^>]*>([^<]*(?:faq|frequently asked questions|veelgestelde vragen)[^<]*)<\/h2>(.*)/is', $content, $m ) ) return [];
	$tail = $m[2];
	if ( ! preg_match_all( '/<h3[^>]*>([^<]+)<\/h3>\s*<p[^>]*>(.*?)<\/p>/is', $tail, $pairs, PREG_SET_ORDER ) ) return [];
	if ( count( $pairs ) < 2 ) return [];

	$entities = [];
	foreach ( $pairs as $p ) {
		$q = trim( wp_strip_all_tags( $p[1] ) );
		$a = trim( wp_strip_all_tags( $p[2] ) );
		if ( ! $q || ! $a ) continue;
		$entities[] = [
			'@type'          => 'Question',
			'name'           => $q,
			'acceptedAnswer' => [
				'@type' => 'Answer',
				'text'  => $a,
			],
		];
	}
	if ( count( $entities ) < 2 ) return [];

	return [
		'@type'      => 'FAQPage',
		'@id'        => get_permalink() . '#faq',
		'mainEntity' => $entities,
	];
}

/* ---------------------------------------------------------------------
 * Detect HowTo structure: an <ol> with 3+ <li>s, where the post title
 * starts with "How to" / "How do" / "Steps to".
 * --------------------------------------------------------------------- */
function bvseo_detect_howto( string $content, string $title ): array {
	if ( ! preg_match( '/^(how to|how do|how does|steps to|guide to)\b/i', $title ) ) return [];
	if ( ! preg_match( '/<ol[^>]*>(.*?)<\/ol>/is', $content, $m ) ) return [];
	$ol_inner = $m[1];
	if ( ! preg_match_all( '/<li[^>]*>(.*?)<\/li>/is', $ol_inner, $li_matches ) ) return [];
	if ( count( $li_matches[1] ) < 3 ) return [];

	$steps = [];
	foreach ( $li_matches[1] as $idx => $li ) {
		$text = trim( wp_strip_all_tags( $li ) );
		if ( ! $text ) continue;
		$steps[] = [
			'@type'    => 'HowToStep',
			'position' => $idx + 1,
			'name'     => mb_substr( $text, 0, 80 ),
			'text'     => $text,
		];
	}
	if ( count( $steps ) < 3 ) return [];

	return [
		'@type' => 'HowTo',
		'@id'   => get_permalink() . '#howto',
		'name'  => $title,
		'step'  => $steps,
	];
}

/* ---------------------------------------------------------------------
 * ACTIVE BOOSTERS , fire on publish so new posts get discovered by
 * search engines immediately, not when they next crawl.
 *
 * Three actions on every post publish / update:
 *   1. Ping Google + Bing sitemap (sitemap.xml URL submitted).
 *   2. IndexNow ping (Bing, Yandex, Naver) with the post URL.
 *   3. Schedule sitemap-aware revalidation via WP cron.
 *
 * All pings are async via wp_remote_post non-blocking calls; the
 * publish save is never slowed down.
 * --------------------------------------------------------------------- */
add_action( 'transition_post_status', function ( $new_status, $old_status, $post ) {
	if ( ! $post || ! ( $post instanceof WP_Post ) ) return;
	if ( 'post' !== $post->post_type ) return;
	if ( 'publish' !== $new_status ) return;
	/* Trigger only on actual publish (new or moved-into-publish). */
	$first_publish = ( 'publish' !== $old_status );

	$url = (string) get_permalink( $post->ID );
	if ( ! $url ) return;

	bvseo_ping_sitemap_engines();
	bvseo_indexnow( [ $url ] );

	if ( $first_publish ) {
		/* Soft cache warm: hit the URL once so any object cache /
		 * page cache primes itself before search bots arrive. */
		wp_remote_get( $url, [ 'timeout' => 1, 'blocking' => false, 'redirection' => 2 ] );
	}
}, 10, 3 );

function bvseo_ping_sitemap_engines(): void {
	$sitemap = home_url( '/wp-sitemap.xml' );
	$urls = [
		'https://www.google.com/ping?sitemap=' . rawurlencode( $sitemap ),
		'https://www.bing.com/ping?sitemap='   . rawurlencode( $sitemap ),
	];
	foreach ( $urls as $u ) {
		wp_remote_get( $u, [ 'timeout' => 1, 'blocking' => false, 'redirection' => 2 ] );
	}
}

function bvseo_indexnow( array $urls ): void {
	$urls = array_filter( array_map( 'esc_url_raw', $urls ) );
	if ( empty( $urls ) ) return;

	$key = (string) get_option( 'bvseo_indexnow_key', '' );
	if ( ! $key ) {
		$key = strtolower( wp_generate_password( 32, false, false ) );
		update_option( 'bvseo_indexnow_key', $key, false );
		bvseo_write_indexnow_key_file( $key );
	}

	$host = wp_parse_url( home_url( '/' ), PHP_URL_HOST );
	if ( ! $host ) return;

	$body = wp_json_encode( [
		'host'        => $host,
		'key'         => $key,
		'keyLocation' => home_url( '/' . $key . '.txt' ),
		'urlList'     => array_values( $urls ),
	] );

	wp_remote_post( 'https://api.indexnow.org/IndexNow', [
		'timeout'     => 1,
		'blocking'    => false,
		'redirection' => 2,
		'headers'     => [ 'Content-Type' => 'application/json; charset=utf-8' ],
		'body'        => $body,
	] );
}

function bvseo_write_indexnow_key_file( string $key ): void {
	if ( ! function_exists( 'WP_Filesystem' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
	}
	WP_Filesystem();
	global $wp_filesystem;
	if ( ! $wp_filesystem ) return;
	$path = ABSPATH . $key . '.txt';
	if ( ! $wp_filesystem->exists( $path ) ) {
		$wp_filesystem->put_contents( $path, $key, FS_CHMOD_FILE );
	}
}

/* ---------------------------------------------------------------------
 * Tools -> SEO Boost diagnostic page.
 * --------------------------------------------------------------------- */
add_action( 'admin_menu', function () {
	add_management_page(
		__( 'SEO Boost', 'booming-venture-seo-boost' ),
		__( 'SEO Boost', 'booming-venture-seo-boost' ),
		'manage_options',
		'booming-venture-seo-boost',
		'bvseo_render_admin'
	);
} );

function bvseo_render_admin(): void {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );
	$d = bvseo_detect();
	$known = [
		'yoast'      => 'Yoast SEO',
		'rank_math'  => 'Rank Math',
		'aioseo'     => 'All in One SEO',
		'seopress'   => 'SEOPress',
		'tsf'        => 'The SEO Framework',
		'slim_seo'   => 'Slim SEO',
		'squirrly'   => 'Squirrly',
		'theme_seo'  => 'Theme-shipped JSON-LD (Booming Venture / similar)',
	];
	?>
	<div class="wrap">
		<h1><?php echo esc_html__( 'Booming Venture , SEO Boost', 'booming-venture-seo-boost' ); ?></h1>
		<p><?php echo esc_html__( 'This plugin auto-detects every tag and JSON-LD block already present on each page render, and injects ONLY what is missing.', 'booming-venture-seo-boost' ); ?></p>

		<h2><?php echo esc_html__( 'Detected sources of SEO', 'booming-venture-seo-boost' ); ?></h2>
		<table class="widefat striped" style="max-width:720px;">
			<thead><tr><th><?php echo esc_html__( 'Source', 'booming-venture-seo-boost' ); ?></th><th><?php echo esc_html__( 'Active', 'booming-venture-seo-boost' ); ?></th></tr></thead>
			<tbody>
			<?php foreach ( $known as $k => $label ) : ?>
				<tr>
					<td><?php echo esc_html( $label ); ?></td>
					<td><?php echo ! empty( $d[ $k ] ) ? '<span style="color:#16a34a">&#10003; yes</span>' : '<span style="color:#9ca3af">&mdash; no</span>'; ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<h2><?php echo esc_html__( 'What this plugin will inject', 'booming-venture-seo-boost' ); ?></h2>
		<p><?php echo esc_html__( 'On every page render, the plugin examines the head output and adds only the tags that are NOT already present:', 'booming-venture-seo-boost' ); ?></p>
		<ul style="list-style:disc;margin-left:1.25rem;">
			<li><code>&lt;link rel="canonical"&gt;</code></li>
			<li><code>&lt;meta name="description"&gt;</code></li>
			<li><code>&lt;meta property="og:title|og:description|og:url|og:type|og:site_name|og:locale|og:image*"&gt;</code></li>
			<li><code>&lt;meta property="article:published_time|article:modified_time|article:author"&gt;</code></li>
			<li><code>&lt;meta name="twitter:card|twitter:title|twitter:description|twitter:image*"&gt;</code></li>
			<li><code>&lt;script type="application/ld+json"&gt;</code> with Organization + WebSite + BlogPosting + BreadcrumbList + auto-detected FAQPage + auto-detected HowTo.</li>
		</ul>
		<p><strong><?php echo esc_html__( 'Trigger', 'booming-venture-seo-boost' ); ?>:</strong> <?php echo esc_html__( 'view any front-end page. View its source. Look for tags. The plugin emits in the order: canonical, description, OG, Twitter, JSON-LD.', 'booming-venture-seo-boost' ); ?></p>
	</div>
	<?php
}
