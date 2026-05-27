<?php
/**
 * OndernemerMarketing , GEO/AEO infrastructure.
 *  - 3-tier robots.txt extension (allow citation crawlers, disallow
 *    bulk training scrapers, allow everyone else by default).
 *  - llms.txt at /llms.txt , Markdown summary of the site for AI.
 *  - IndexNow ping on publish (Bing / Yandex / Naver).
 *
 * @package OndernemerMarketing
 */

defined( 'ABSPATH' ) || exit;

class ONDM_GEO {

	const INDEXNOW_KEY_OPT = 'ondm_indexnow_key';

	public static function init(): void {
		add_filter( 'robots_txt',          [ __CLASS__, 'extend_robots' ], 20, 2 );
		add_action( 'init',                [ __CLASS__, 'register_llms_txt_rewrite' ] );
		add_filter( 'query_vars',          [ __CLASS__, 'add_llms_query_var' ] );
		add_action( 'template_redirect',   [ __CLASS__, 'maybe_render_llms_txt' ] );
		add_action( 'transition_post_status', [ __CLASS__, 'maybe_indexnow_ping' ], 10, 3 );
	}

	public static function extend_robots( string $output, bool $public ): string {
		if ( ! $public ) return $output;

		$allow = [ 'OAI-SearchBot', 'Claude-SearchBot', 'PerplexityBot', 'Google-Extended', 'Applebot-Extended' ];
		$deny  = [ 'GPTBot', 'CCBot', 'anthropic-ai', 'ClaudeBot' ];

		$out = $output;
		$out .= "\n# OndernemerMarketing , AI crawler policy\n";
		foreach ( $allow as $ua ) $out .= "User-agent: {$ua}\nAllow: /\n\n";
		foreach ( $deny  as $ua ) $out .= "User-agent: {$ua}\nDisallow: /\n\n";
		$out .= "# llms.txt for AI summarisation\n";
		$out .= 'Sitemap: ' . esc_url_raw( home_url( '/llms.txt' ) ) . "\n";
		return $out;
	}

	public static function register_llms_txt_rewrite(): void {
		add_rewrite_rule( '^llms\.txt$', 'index.php?ondm_llms=1', 'top' );
	}

	public static function add_llms_query_var( array $vars ): array {
		$vars[] = 'ondm_llms';
		return $vars;
	}

	public static function maybe_render_llms_txt(): void {
		if ( ! get_query_var( 'ondm_llms' ) ) return;
		nocache_headers();
		header( 'Content-Type: text/markdown; charset=utf-8' );

		$site = (string) get_bloginfo( 'name' );
		$desc = (string) get_bloginfo( 'description' );
		echo "# {$site}\n\n{$desc}\n\n";
		echo "## Diensten\n";
		echo "- Kickstart Funnel , vanaf EUR 3.500 (eenmalig)\n";
		echo "- Groei Machine , vanaf EUR 4.500/maand\n";
		echo "- Volledig Uitbesteed , vanaf EUR 7.500/maand\n\n";
		echo "## Snelle pakketten\n";
		echo "- Social Media Funnel Pack , EUR 600\n";
		echo "- Lead Magnet Landingspagina , EUR 950\n";
		echo "- One Page Funnel , EUR 1.200\n";
		echo "- Website Light , EUR 99\n\n";
		echo "## Google Ads pakketten\n";
		echo "- Google Ads Kickstart , EUR 450/mnd + EUR 750 setup\n";
		echo "- Google Ads Groei Pack , EUR 750/mnd\n\n";
		echo "## Contact\n";
		echo "- E-mail: info@ondernemermarketing.nl\n";
		echo "- Telefoon: +31 6 1301 3266\n";
		echo "- Adres: Breedveldsingel 1, 3055PG Rotterdam, Nederland\n\n";
		echo "## Belangrijke pagina's\n";
		$pages = get_pages( [ 'sort_column' => 'menu_order' ] );
		foreach ( $pages as $p ) {
			$title = get_the_title( $p );
			$url   = get_permalink( $p );
			echo "- [{$title}]({$url})\n";
		}
		exit;
	}

	/** Auto-generate + store IndexNow key on first need. */
	private static function indexnow_key(): string {
		$key = (string) get_option( self::INDEXNOW_KEY_OPT, '' );
		if ( '' === $key ) {
			$key = bin2hex( random_bytes( 16 ) );
			update_option( self::INDEXNOW_KEY_OPT, $key, false );
		}
		return $key;
	}

	public static function maybe_indexnow_ping( string $new, string $old, $post ): void {
		if ( ! $post instanceof WP_Post ) return;
		if ( 'publish' !== $new || 'publish' === $old ) return;
		if ( ! in_array( $post->post_type, [ 'post', 'page' ], true ) ) return;
		$url  = get_permalink( $post );
		if ( ! $url ) return;
		$host = (string) wp_parse_url( home_url(), PHP_URL_HOST );
		$key  = self::indexnow_key();
		$endpoint = 'https://api.indexnow.org/indexnow';
		wp_remote_post( $endpoint, [
			'timeout' => 5,
			'blocking' => false,
			'headers' => [ 'Content-Type' => 'application/json; charset=utf-8' ],
			'body'    => wp_json_encode( [
				'host'        => $host,
				'key'         => $key,
				'keyLocation' => home_url( '/' . $key . '.txt' ),
				'urlList'     => [ $url ],
			] ),
		] );
	}
}

ONDM_GEO::init();

/* Serve the IndexNow key file at /<key>.txt */
add_action( 'init', function () {
	$key = (string) get_option( ONDM_GEO::INDEXNOW_KEY_OPT, '' );
	if ( '' === $key ) return;
	add_rewrite_rule( '^' . preg_quote( $key, '/' ) . '\.txt$', 'index.php?ondm_indexnow_key=1', 'top' );
} );
add_filter( 'query_vars', function ( $vars ) {
	$vars[] = 'ondm_indexnow_key';
	return $vars;
} );
add_action( 'template_redirect', function () {
	if ( ! get_query_var( 'ondm_indexnow_key' ) ) return;
	$key = (string) get_option( ONDM_GEO::INDEXNOW_KEY_OPT, '' );
	if ( '' === $key ) return;
	nocache_headers();
	header( 'Content-Type: text/plain; charset=utf-8' );
	echo $key;
	exit;
} );
