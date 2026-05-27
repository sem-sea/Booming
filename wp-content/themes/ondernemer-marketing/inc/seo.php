<?php
/**
 * OndernemerMarketing , SEO + schema emitters.
 *
 * Detects active SEO plugins (Yoast, Rank Math, AIOSEO, SEOPress, TSF)
 * and stands down per-tag so we never double-write. Otherwise emits:
 *  - meta description
 *  - canonical URL
 *  - Open Graph + Twitter Card
 *  - JSON-LD: Organization + WebSite site-wide; Article + BreadcrumbList on singular.
 *
 * @package OndernemerMarketing
 */

defined( 'ABSPATH' ) || exit;

class ONDM_SEO {

	/** Detect which SEO plugin is active (if any). */
	public static function detected_plugin(): string {
		if ( defined( 'WPSEO_VERSION' ) )       return 'yoast';
		if ( defined( 'RANK_MATH_VERSION' ) )   return 'rank-math';
		if ( defined( 'AIOSEO_VERSION' ) )      return 'aioseo';
		if ( defined( 'SEOPRESS_VERSION' ) )    return 'seopress';
		if ( defined( 'THE_SEO_FRAMEWORK_VERSION' ) ) return 'tsf';
		return '';
	}

	public static function init(): void {
		add_action( 'wp_head', [ __CLASS__, 'emit_meta' ], 1 );
		add_action( 'wp_head', [ __CLASS__, 'emit_jsonld' ], 5 );
	}

	public static function emit_meta(): void {
		if ( '' !== self::detected_plugin() ) return; // stand down
		$desc = self::derive_description();
		$canonical = is_singular() ? get_permalink() : home_url( add_query_arg( null, null ) );
		$og_image  = self::og_image_url();
		$site_name = get_bloginfo( 'name' );
		$title     = wp_get_document_title();
		?>
<meta name="description" content="<?php echo esc_attr( $desc ); ?>">
<link rel="canonical" href="<?php echo esc_url( $canonical ); ?>">
<meta property="og:type" content="<?php echo is_singular() ? 'article' : 'website'; ?>">
<meta property="og:title" content="<?php echo esc_attr( $title ); ?>">
<meta property="og:description" content="<?php echo esc_attr( $desc ); ?>">
<meta property="og:url" content="<?php echo esc_url( $canonical ); ?>">
<meta property="og:site_name" content="<?php echo esc_attr( $site_name ); ?>">
<meta property="og:locale" content="nl_NL">
<?php if ( $og_image ) : ?>
<meta property="og:image" content="<?php echo esc_url( $og_image ); ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<?php endif; ?>
<meta name="twitter:card" content="<?php echo $og_image ? 'summary_large_image' : 'summary'; ?>">
<meta name="twitter:title" content="<?php echo esc_attr( $title ); ?>">
<meta name="twitter:description" content="<?php echo esc_attr( $desc ); ?>">
<?php if ( $og_image ) : ?>
<meta name="twitter:image" content="<?php echo esc_url( $og_image ); ?>">
<?php endif;
	}

	public static function emit_jsonld(): void {
		$detected = self::detected_plugin();
		$home     = home_url( '/' );
		$site_name = get_bloginfo( 'name' );

		$graph = [];

		// Organization + WebSite (site-wide, even when SEO plugin is active,
		// because OndernemerMarketing-specific values are useful).
		if ( '' === $detected ) {
			$graph[] = [
				'@type'  => 'Organization',
				'@id'    => $home . '#org',
				'name'   => $site_name,
				'url'    => $home,
				'logo'   => self::og_image_url() ?: '',
				'email'  => 'info@ondernemermarketing.nl',
				'telephone' => '+31613013266',
				'address' => [
					'@type'           => 'PostalAddress',
					'streetAddress'   => 'Breedveldsingel 1',
					'postalCode'      => '3055PG',
					'addressLocality' => 'Rotterdam',
					'addressCountry'  => 'NL',
				],
				'sameAs' => [],
			];
			$graph[] = [
				'@type' => 'WebSite',
				'@id'   => $home . '#website',
				'url'   => $home,
				'name'  => $site_name,
				'inLanguage' => 'nl-NL',
				'publisher'  => [ '@id' => $home . '#org' ],
			];
		}

		// Article on singular posts.
		if ( '' === $detected && is_singular( 'post' ) ) {
			$post = get_post();
			if ( $post ) {
				$graph[] = [
					'@type'         => 'Article',
					'@id'           => get_permalink( $post ) . '#article',
					'headline'      => get_the_title( $post ),
					'description'   => self::derive_description(),
					'datePublished' => get_the_date( DATE_W3C, $post ),
					'dateModified'  => get_the_modified_date( DATE_W3C, $post ),
					'author'        => [ '@type' => 'Person', 'name' => get_the_author_meta( 'display_name', (int) $post->post_author ) ],
					'publisher'     => [ '@id' => $home . '#org' ],
					'image'         => self::og_image_url() ?: '',
					'mainEntityOfPage' => get_permalink( $post ),
					'inLanguage'    => 'nl-NL',
				];
			}
		}

		// FAQPage auto-detection on pages with <details> blocks.
		if ( '' === $detected && is_singular() ) {
			$faqs = self::detect_faqs();
			if ( ! empty( $faqs ) ) {
				$graph[] = [
					'@type'      => 'FAQPage',
					'mainEntity' => array_map( function ( $faq ) {
						return [
							'@type'          => 'Question',
							'name'           => $faq['q'],
							'acceptedAnswer' => [
								'@type' => 'Answer',
								'text'  => $faq['a'],
							],
						];
					}, $faqs ),
				];
			}
		}

		if ( empty( $graph ) ) return;
		$jsonld = [ '@context' => 'https://schema.org', '@graph' => $graph ];
		echo '<script type="application/ld+json">' . wp_json_encode( $jsonld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
	}

	private static function derive_description(): string {
		if ( is_singular() ) {
			$post = get_post();
			$excerpt = $post ? trim( wp_strip_all_tags( get_the_excerpt( $post ) ) ) : '';
			if ( $excerpt ) return mb_substr( $excerpt, 0, 160 );
			$raw = $post ? trim( wp_strip_all_tags( $post->post_content ) ) : '';
			if ( $raw ) return mb_substr( $raw, 0, 160 );
		}
		return trim( (string) get_bloginfo( 'description' ) ) ?: 'Marketingoplossingen voor ondernemers , wij regelen je marketing, jij ondernemt.';
	}

	private static function og_image_url(): string {
		if ( is_singular() ) {
			$post_id = get_the_ID();
			if ( $post_id ) {
				$thumb = get_the_post_thumbnail_url( $post_id, 'full' );
				if ( $thumb ) return (string) $thumb;
			}
		}
		$logo_id = (int) get_theme_mod( 'custom_logo' );
		if ( $logo_id ) {
			$src = wp_get_attachment_image_src( $logo_id, 'full' );
			if ( $src && ! empty( $src[0] ) ) return (string) $src[0];
		}
		return '';
	}

	/** Scan post content for <details>/<summary> blocks and turn into FAQs. */
	private static function detect_faqs(): array {
		$post = get_post();
		if ( ! $post ) return [];
		$content = (string) $post->post_content;
		$out = [];
		if ( preg_match_all( '/<details[^>]*>\s*<summary[^>]*>(.*?)<\/summary>(.*?)<\/details>/is', $content, $m, PREG_SET_ORDER ) ) {
			foreach ( $m as $hit ) {
				$q = trim( wp_strip_all_tags( $hit[1] ) );
				$a = trim( wp_strip_all_tags( $hit[2] ) );
				if ( $q && $a ) $out[] = [ 'q' => $q, 'a' => mb_substr( $a, 0, 1000 ) ];
			}
		}
		return $out;
	}
}

ONDM_SEO::init();
