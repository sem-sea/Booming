<?php
/**
 * CiteLeap , i18n.php
 *
 * Multilingual support module.
 *
 * Three responsibilities:
 *
 *  1. Per-queue-row language code (en, nl, fr, de, es, it, pt, etc.).
 *     The writer prompt receives the language and instructs the model
 *     to produce native-quality output in that language with locally
 *     adapted statistics and named sources.
 *
 *  2. Plugin detection for Polylang and WPML so a generated post is
 *     stored as the operator's target language and connected to
 *     existing translation groups when appropriate.
 *
 *  3. hreflang emission for posts that have explicit translations
 *     registered (Polylang `pll_get_post_translations` or WPML
 *     `wpml_get_translations_of_post`). Auto-injects hreflang tags
 *     into wp_head when neither Polylang nor WPML are emitting them
 *     themselves (some lightweight setups skip it).
 *
 * @package CiteLeap
 */

defined( 'ABSPATH' ) || exit;

class CiteLeap_I18n {

	/** Languages CiteLeap supports out of the box. Each row:
	 *  [code, native_name, english_name, locale]. Operators can extend
	 *  via the citeleap_supported_languages filter. */
	public static function supported(): array {
		/* Trimmed to the most spoken in the Western world + Dutch, in
		 * order of native-speaker count (English > Spanish > Portuguese
		 * > French > German > Italian > Dutch). Add more via the filter. */
		$base = [
			'en' => [ 'en', 'English',    'English',    'en_US' ],
			'es' => [ 'es', 'Español',    'Spanish',    'es_ES' ],
			'pt' => [ 'pt', 'Português',  'Portuguese', 'pt_PT' ],
			'fr' => [ 'fr', 'Français',   'French',     'fr_FR' ],
			'de' => [ 'de', 'Deutsch',    'German',     'de_DE' ],
			'it' => [ 'it', 'Italiano',   'Italian',    'it_IT' ],
			'nl' => [ 'nl', 'Nederlands', 'Dutch',      'nl_NL' ],
		];
		return (array) apply_filters( 'citeleap_supported_languages', $base );
	}

	public static function settings(): array {
		$r = (array) get_option( CITELEAP_OPTION_I18N, [] );
		return [
			'default_lang'   => (string) ( $r['default_lang']   ?? self::detect_site_default() ),
			'enabled_langs'  => (array)  ( $r['enabled_langs']  ?? [ self::detect_site_default() ] ),
			'auto_translate' => (bool)   ( $r['auto_translate'] ?? false ),
			'inject_hreflang'=> (bool)   ( $r['inject_hreflang'] ?? true ),
		];
	}

	public static function save_settings( array $args ): void {
		$enabled = (array) ( $args['enabled_langs'] ?? [] );
		$enabled = array_values( array_filter( array_map( 'sanitize_key', $enabled ) ) );
		if ( empty( $enabled ) ) $enabled = [ self::detect_site_default() ];
		update_option( CITELEAP_OPTION_I18N, [
			'default_lang'    => sanitize_key( (string) ( $args['default_lang'] ?? self::detect_site_default() ) ),
			'enabled_langs'   => $enabled,
			'auto_translate'  => ! empty( $args['auto_translate'] ),
			'inject_hreflang' => ! empty( $args['inject_hreflang'] ),
		], false );
	}

	public static function detect_site_default(): string {
		$loc = (string) get_locale();
		if ( strlen( $loc ) >= 2 ) return strtolower( substr( $loc, 0, 2 ) );
		return 'en';
	}

	/** Polylang present? */
	public static function has_polylang(): bool { return function_exists( 'pll_set_post_language' ); }

	/** WPML present? */
	public static function has_wpml(): bool { return function_exists( 'icl_object_id' ) || defined( 'ICL_SITEPRESS_VERSION' ); }

	/** Assign a queued post to its target language via the detected
	 *  translation plugin. Safe no-op when no plugin is installed. */
	public static function assign_post_language( int $post_id, string $lang ): void {
		if ( ! $post_id || ! $lang ) return;
		update_post_meta( $post_id, CITELEAP_META_LANG, $lang );
		if ( self::has_polylang() ) {
			\pll_set_post_language( $post_id, $lang );
		} elseif ( self::has_wpml() ) {
			do_action( 'wpml_set_element_language_details', [
				'element_id'           => $post_id,
				'element_type'         => 'post_post',
				'trid'                 => null,
				'language_code'        => $lang,
				'source_language_code' => self::detect_site_default(),
			] );
		}
	}

	/** Prompt fragment for the writer model. */
	public static function as_prompt_text( string $lang ): string {
		$map = self::supported();
		$row = $map[ $lang ] ?? null;
		if ( ! $row ) return '';
		$name = (string) $row[2];
		$native = (string) $row[1];
		return "Write the entire post in {$name} ({$native}). Use native idioms, locally relevant statistics, and locally recognised named sources (e.g. for Dutch content prefer CBS, NRC, FD, RTL; for German prefer Statistisches Bundesamt, Handelsblatt; for French prefer INSEE, Les Echos). Translate ALL meta (title, slug, meta_description, excerpt, category_name) into {$name}, not just the body. The slug must be a {$native} slug, no English placeholders.";
	}

	/* ---- hreflang auto-injection ---------------------------------- */

	public static function init(): void {
		add_action( 'wp_head', [ __CLASS__, 'maybe_emit_hreflang' ], 1 );
	}

	public static function maybe_emit_hreflang(): void {
		if ( ! is_singular( 'post' ) ) return;
		$s = self::settings();
		if ( ! $s['inject_hreflang'] ) return;
		/* If Polylang or WPML or Yoast already emit hreflang tags, do nothing. */
		if ( did_action( 'pll_pre_translation_url' ) || did_action( 'wpml_register_string' ) ) return;
		if ( defined( 'WPSEO_VERSION' ) || class_exists( 'WPSEO_Frontend' ) ) return;

		$post_id = (int) get_queried_object_id();
		$translations = self::get_translation_set( $post_id );
		if ( count( $translations ) < 2 ) return;
		$default = (string) $s['default_lang'];
		foreach ( $translations as $lang => $url ) {
			printf(
				'<link rel="alternate" hreflang="%s" href="%s">' . "\n",
				esc_attr( $lang ),
				esc_url( $url )
			);
			if ( $lang === $default ) {
				printf( '<link rel="alternate" hreflang="x-default" href="%s">' . "\n", esc_url( $url ) );
			}
		}
	}

	private static function get_translation_set( int $post_id ): array {
		$out = [];
		$lang = (string) get_post_meta( $post_id, CITELEAP_META_LANG, true );
		if ( $lang ) {
			$out[ $lang ] = (string) get_permalink( $post_id );
		}
		if ( self::has_polylang() ) {
			$set = \pll_get_post_translations( $post_id );
			foreach ( (array) $set as $code => $id ) {
				$url = (string) get_permalink( (int) $id );
				if ( $url ) $out[ $code ] = $url;
			}
		} elseif ( self::has_wpml() ) {
			$set = apply_filters( 'wpml_post_language_details', null, $post_id );
			if ( is_array( $set ) && ! empty( $set['translations'] ) ) {
				foreach ( $set['translations'] as $code => $row ) {
					$id = is_array( $row ) ? (int) ( $row['post_id'] ?? 0 ) : (int) $row;
					if ( $id ) {
						$url = (string) get_permalink( $id );
						if ( $url ) $out[ $code ] = $url;
					}
				}
			}
		}
		return $out;
	}
}

CiteLeap_I18n::init();
