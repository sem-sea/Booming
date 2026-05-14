<?php
/**
 * CiteLeap , seo.php
 *
 * On-render SEO injection + on-publish discovery pings.
 *
 * Passive (every page render):
 *   - Adds JSON-LD (Organization, WebSite, BlogPosting + auto FAQ /
 *     HowTo), Open Graph, Twitter Card, canonical, and meta-description
 *     ONLY when no existing SEO plugin already wrote them. Stands
 *     down for Yoast, Rank Math, AIOSEO, SEOPress, The SEO Framework.
 *
 * Active (every post publish):
 *   - Pings Google + Bing sitemap.
 *   - IndexNow POST to api.indexnow.org (Bing, Yandex, Naver).
 *   - Warms the post URL once so any page cache primes before bots
 *     arrive.
 *
 * @package CiteLeap
 */

defined( 'ABSPATH' ) || exit;

class CiteLeap_SEO {

	public static function settings(): array {
		$r = (array) get_option( CITELEAP_OPTION_SEO, [] );
		return [
			'enabled'         => isset( $r['enabled']         ) ? (bool) $r['enabled']         : true,
			'inject_schema'   => isset( $r['inject_schema']   ) ? (bool) $r['inject_schema']   : true,
			'inject_og'       => isset( $r['inject_og']       ) ? (bool) $r['inject_og']       : true,
			'inject_canonical'=> isset( $r['inject_canonical']) ? (bool) $r['inject_canonical']: true,
			'ping_sitemap'    => isset( $r['ping_sitemap']    ) ? (bool) $r['ping_sitemap']    : true,
			'indexnow'        => isset( $r['indexnow']        ) ? (bool) $r['indexnow']        : true,
		];
	}

	public static function save_settings( array $args ): void {
		update_option( CITELEAP_OPTION_SEO, [
			'enabled'          => ! empty( $args['enabled'] ),
			'inject_schema'    => ! empty( $args['inject_schema'] ),
			'inject_og'        => ! empty( $args['inject_og'] ),
			'inject_canonical' => ! empty( $args['inject_canonical'] ),
			'ping_sitemap'     => ! empty( $args['ping_sitemap'] ),
			'indexnow'         => ! empty( $args['indexnow'] ),
		], false );
	}

	public static function detected_seo_sources(): array {
		return [
			'yoast'     => defined( 'WPSEO_VERSION' )     || class_exists( 'WPSEO_Frontend' ),
			'rank_math' => defined( 'RANK_MATH_VERSION' ) || class_exists( 'RankMath' ),
			'aioseo'    => defined( 'AIOSEO_VERSION' )    || class_exists( 'AIOSEO\\Plugin\\AIOSEO' ),
			'seopress'  => defined( 'SEOPRESS_VERSION' )  || function_exists( 'seopress_get_service' ),
			'tsf'       => defined( 'THE_SEO_FRAMEWORK_PRESENT' ) || function_exists( 'the_seo_framework' ),
		];
	}

	public static function init(): void {
		add_action( 'wp_head', [ __CLASS__, 'open_head_buffer' ], 1 );
		add_action( 'wp_head', [ __CLASS__, 'close_head_buffer' ], 9999 );
		add_action( 'transition_post_status', [ __CLASS__, 'fire_publish_boosters' ], 10, 3 );
	}

	public static function open_head_buffer(): void {
		$s = self::settings();
		if ( ! $s['enabled'] || is_admin() || is_feed() || is_robots() ) return;
		ob_start();
	}

	public static function close_head_buffer(): void {
		$s = self::settings();
		if ( ! $s['enabled'] || is_admin() || is_feed() || is_robots() ) return;
		$head = (string) ob_get_clean();
		$inject = '';

		if ( $s['inject_canonical'] && ! preg_match( '/<link[^>]+rel=["\']canonical["\']/i', $head ) ) {
			$c = self::canonical();
			if ( $c ) $inject .= '<link rel="canonical" href="' . esc_url( $c ) . '">' . "\n";
		}

		if ( $s['inject_og'] ) {
			if ( ! preg_match( '/<meta[^>]+name=["\']description["\']/i', $head ) ) {
				$d = self::description();
				if ( $d ) $inject .= '<meta name="description" content="' . esc_attr( $d ) . '">' . "\n";
			}
			if ( ! preg_match( '/<meta[^>]+property=["\']og:title["\']/i', $head ) ) {
				$inject .= self::render_og();
			}
			if ( ! preg_match( '/<meta[^>]+name=["\']twitter:card["\']/i', $head ) ) {
				$inject .= self::render_twitter();
			}
		}

		if ( $s['inject_schema'] && ! preg_match( '/<script[^>]+type=["\']application\/ld\+json["\']/i', $head ) ) {
			$inject .= self::render_jsonld();
		}

		echo $head . $inject; // phpcs:ignore , both halves are escape-safe.
	}

	/* ---- helpers ------------------------------------------------- */
	public static function canonical(): string {
		if ( is_singular() ) return (string) get_permalink( get_queried_object_id() );
		if ( is_home() || is_front_page() ) return (string) home_url( '/' );
		if ( is_category() || is_tag() || is_tax() ) {
			$t = get_queried_object();
			if ( $t && isset( $t->term_id ) ) return (string) get_term_link( $t );
		}
		return '';
	}

	public static function description(): string {
		if ( is_singular() ) {
			$pid = get_queried_object_id();
			$e = get_post_field( 'post_excerpt', $pid );
			if ( $e ) return wp_strip_all_tags( $e );
			$c = (string) get_post_field( 'post_content', $pid );
			$p = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( strip_shortcodes( $c ) ) ) );
			return mb_substr( $p, 0, 160 );
		}
		if ( is_home() || is_front_page() ) return (string) get_bloginfo( 'description' );
		return '';
	}

	public static function render_og(): string {
		$title  = is_singular() ? get_the_title( get_queried_object_id() ) : wp_get_document_title();
		$desc   = self::description();
		$url    = self::canonical();
		$type   = is_singular( 'post' ) ? 'article' : ( is_singular() ? 'article' : 'website' );
		$site   = (string) get_bloginfo( 'name' );
		$locale = (string) get_locale();
		$image  = is_singular() && class_exists( 'CiteLeap_Images' )
			? CiteLeap_Images::og_image_for_post( (int) get_queried_object_id() )
			: [ 'url' => '', 'width' => 0, 'height' => 0, 'alt' => '' ];

		$o  = '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
		if ( $desc )   $o .= '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
		if ( $url )    $o .= '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
		$o .= '<meta property="og:type" content="' . esc_attr( $type ) . '">' . "\n";
		$o .= '<meta property="og:site_name" content="' . esc_attr( $site ) . '">' . "\n";
		$o .= '<meta property="og:locale" content="' . esc_attr( $locale ) . '">' . "\n";
		if ( $image['url'] ) {
			$o .= '<meta property="og:image" content="' . esc_url( $image['url'] ) . '">' . "\n";
			if ( $image['width']  ) $o .= '<meta property="og:image:width" content="'  . (int) $image['width']  . '">' . "\n";
			if ( $image['height'] ) $o .= '<meta property="og:image:height" content="' . (int) $image['height'] . '">' . "\n";
			if ( $image['alt'] )    $o .= '<meta property="og:image:alt" content="'    . esc_attr( $image['alt'] ) . '">' . "\n";
		}
		if ( is_singular( 'post' ) ) {
			$pid = get_queried_object_id();
			$pub = mysql2date( DATE_W3C, get_post_field( 'post_date_gmt', $pid ), false );
			$mod = mysql2date( DATE_W3C, get_post_field( 'post_modified_gmt', $pid ), false );
			if ( $pub ) $o .= '<meta property="article:published_time" content="' . esc_attr( $pub ) . '">' . "\n";
			if ( $mod ) $o .= '<meta property="article:modified_time" content="'  . esc_attr( $mod ) . '">' . "\n";
		}
		return $o;
	}

	public static function render_twitter(): string {
		$title = is_singular() ? get_the_title( get_queried_object_id() ) : wp_get_document_title();
		$desc  = self::description();
		$image = is_singular() && class_exists( 'CiteLeap_Images' )
			? CiteLeap_Images::og_image_for_post( (int) get_queried_object_id() )
			: [ 'url' => '', 'alt' => '' ];
		$card  = $image['url'] ? 'summary_large_image' : 'summary';
		$o  = '<meta name="twitter:card" content="' . esc_attr( $card ) . '">' . "\n";
		$o .= '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
		if ( $desc )         $o .= '<meta name="twitter:description" content="' . esc_attr( $desc ) . '">' . "\n";
		if ( $image['url'] ) $o .= '<meta name="twitter:image" content="' . esc_url( $image['url'] ) . '">' . "\n";
		return $o;
	}

	public static function render_jsonld(): string {
		$graph = [];
		$graph[] = self::build_org();
		$graph[] = self::build_website();
		if ( is_singular( 'post' ) ) {
			$pid = (int) get_queried_object_id();
			$graph[] = self::build_article( $pid );
			$graph[] = self::build_breadcrumbs( $pid );
			$content = (string) get_post_field( 'post_content', $pid );
			$faq = self::detect_faq( $content );
			if ( $faq ) $graph[] = $faq;
			$how = self::detect_howto( $content, get_the_title( $pid ) );
			if ( $how ) $graph[] = $how;
		}
		if ( empty( $graph ) ) return '';
		return '<script type="application/ld+json">'
			. wp_json_encode( [ '@context' => 'https://schema.org', '@graph' => $graph ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
			. '</script>' . "\n";
	}

	private static function build_org(): array {
		$url = (string) home_url( '/' );
		return [
			'@type' => 'Organization',
			'@id'   => $url . '#organization',
			'name'  => (string) get_bloginfo( 'name' ),
			'url'   => $url,
		];
	}
	private static function build_website(): array {
		$url = (string) home_url( '/' );
		return [
			'@type'       => 'WebSite',
			'@id'         => $url . '#website',
			'name'        => (string) get_bloginfo( 'name' ),
			'url'         => $url,
			'description' => (string) get_bloginfo( 'description' ),
			'inLanguage'  => (string) get_locale(),
			'publisher'   => [ '@id' => $url . '#organization' ],
		];
	}
	private static function build_article( int $pid ): array {
		$url   = (string) get_permalink( $pid );
		$home  = (string) home_url( '/' );
		$image = class_exists( 'CiteLeap_Images' ) ? CiteLeap_Images::og_image_for_post( $pid ) : [ 'url' => '', 'width' => 0, 'height' => 0 ];
		$schema = [
			'@type'         => 'BlogPosting',
			'@id'           => $url . '#article',
			'mainEntityOfPage' => [ '@type' => 'WebPage', '@id' => $url ],
			'headline'      => (string) get_the_title( $pid ),
			'description'   => self::description(),
			'datePublished' => mysql2date( DATE_W3C, get_post_field( 'post_date_gmt',     $pid ), false ),
			'dateModified'  => mysql2date( DATE_W3C, get_post_field( 'post_modified_gmt', $pid ), false ),
			'inLanguage'    => (string) get_locale(),
			'isPartOf'      => [ '@id' => $home . '#website' ],
			'publisher'     => [ '@id' => $home . '#organization' ],
		];
		$author_id = (int) get_post_field( 'post_author', $pid );
		if ( $author_id ) {
			$schema['author'] = [
				'@type' => 'Person',
				'name'  => (string) get_the_author_meta( 'display_name', $author_id ),
				'url'   => (string) get_author_posts_url( $author_id ),
			];
		}
		if ( $image['url'] ) {
			$schema['image'] = array_filter( [
				'@type'  => 'ImageObject',
				'url'    => $image['url'],
				'width'  => $image['width']  ?: null,
				'height' => $image['height'] ?: null,
			] );
		}
		return $schema;
	}
	private static function build_breadcrumbs( int $pid ): array {
		$home = (string) home_url( '/' );
		$items = [
			[ '@type' => 'ListItem', 'position' => 1, 'name' => __( 'Home', 'citeleap' ), 'item' => $home ],
		];
		$blog = (int) get_option( 'page_for_posts' );
		$pos = 2;
		if ( $blog ) {
			$items[] = [ '@type' => 'ListItem', 'position' => $pos++, 'name' => (string) get_the_title( $blog ), 'item' => (string) get_permalink( $blog ) ];
		}
		$items[] = [ '@type' => 'ListItem', 'position' => $pos, 'name' => (string) get_the_title( $pid ), 'item' => (string) get_permalink( $pid ) ];
		return [
			'@type'           => 'BreadcrumbList',
			'@id'             => get_permalink( $pid ) . '#breadcrumbs',
			'itemListElement' => $items,
		];
	}
	private static function detect_faq( string $content ): array {
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
			$entities[] = [ '@type' => 'Question', 'name' => $q, 'acceptedAnswer' => [ '@type' => 'Answer', 'text' => $a ] ];
		}
		if ( count( $entities ) < 2 ) return [];
		return [ '@type' => 'FAQPage', '@id' => get_permalink() . '#faq', 'mainEntity' => $entities ];
	}
	private static function detect_howto( string $content, string $title ): array {
		if ( ! preg_match( '/^(how to|how do|how does|steps to|guide to)\b/i', $title ) ) return [];
		if ( ! preg_match( '/<ol[^>]*>(.*?)<\/ol>/is', $content, $m ) ) return [];
		if ( ! preg_match_all( '/<li[^>]*>(.*?)<\/li>/is', $m[1], $li ) ) return [];
		if ( count( $li[1] ) < 3 ) return [];
		$steps = [];
		foreach ( $li[1] as $idx => $item ) {
			$t = trim( wp_strip_all_tags( $item ) );
			if ( ! $t ) continue;
			$steps[] = [ '@type' => 'HowToStep', 'position' => $idx + 1, 'name' => mb_substr( $t, 0, 80 ), 'text' => $t ];
		}
		if ( count( $steps ) < 3 ) return [];
		return [ '@type' => 'HowTo', '@id' => get_permalink() . '#howto', 'name' => $title, 'step' => $steps ];
	}

	/* ---- active boosters on publish ------------------------------ */
	public static function fire_publish_boosters( string $new, string $old, $post ): void {
		if ( ! $post instanceof WP_Post )            return;
		if ( 'post' !== $post->post_type )           return;
		if ( 'publish' !== $new )                    return;
		$s = self::settings();
		$url = (string) get_permalink( $post->ID );
		if ( ! $url ) return;

		if ( $s['ping_sitemap'] ) {
			$sm = home_url( '/wp-sitemap.xml' );
			foreach ( [
				'https://www.google.com/ping?sitemap=' . rawurlencode( $sm ),
				'https://www.bing.com/ping?sitemap='   . rawurlencode( $sm ),
			] as $u ) {
				wp_remote_get( $u, [ 'timeout' => 1, 'blocking' => false, 'redirection' => 2 ] );
			}
		}

		if ( $s['indexnow'] ) self::indexnow( [ $url ] );

		/* Soft cache warm. */
		wp_remote_get( $url, [ 'timeout' => 1, 'blocking' => false, 'redirection' => 2 ] );
	}

	public static function indexnow( array $urls ): void {
		$urls = array_filter( array_map( 'esc_url_raw', $urls ) );
		if ( empty( $urls ) ) return;
		$key = (string) get_option( 'citeleap_indexnow_key', '' );
		if ( ! $key ) {
			$key = strtolower( wp_generate_password( 32, false, false ) );
			update_option( 'citeleap_indexnow_key', $key, false );
			self::write_indexnow_key_file( $key );
		}
		$host = (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST );
		if ( ! $host ) return;
		wp_remote_post( 'https://api.indexnow.org/IndexNow', [
			'timeout'     => 1,
			'blocking'    => false,
			'redirection' => 2,
			'headers'     => [ 'Content-Type' => 'application/json; charset=utf-8' ],
			'body'        => wp_json_encode( [
				'host'        => $host,
				'key'         => $key,
				'keyLocation' => home_url( '/' . $key . '.txt' ),
				'urlList'     => array_values( $urls ),
			] ),
		] );
	}

	private static function write_indexnow_key_file( string $key ): void {
		if ( ! function_exists( 'WP_Filesystem' ) ) require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
		global $wp_filesystem;
		if ( ! $wp_filesystem ) return;
		$path = ABSPATH . $key . '.txt';
		if ( ! $wp_filesystem->exists( $path ) ) $wp_filesystem->put_contents( $path, $key, FS_CHMOD_FILE );
	}
}

CiteLeap_SEO::init();
