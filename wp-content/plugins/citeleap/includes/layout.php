<?php
/**
 * CiteLeap , layout.php
 *
 * Reads the active WordPress theme's design tokens (theme.json), its
 * registered block patterns, and any single-post template so the
 * writing prompt produces output that matches what the operator's
 * theme actually renders.
 *
 * Why this exists: a generic block-markup template produces correct
 * but visually unmatched output. By passing the theme's content-size,
 * accent colour, heading font, and a tiny sample of the theme's own
 * "section" pattern markup into the prompt, the writing model emits
 * a draft that uses the same wp:group classes the theme styles.
 *
 * @package CiteLeap
 */

defined( 'ABSPATH' ) || exit;

class CiteLeap_Layout {

	/** Pull a small, prompt-safe summary of the active theme's design. */
	public static function active_summary(): array {
		$theme = wp_get_theme();
		$summary = [
			'theme_name'   => (string) $theme->get( 'Name' ),
			'theme_slug'   => (string) $theme->get_stylesheet(),
			'is_block'     => function_exists( 'wp_is_block_theme' ) ? (bool) wp_is_block_theme() : false,
			'content_size' => '',
			'wide_size'    => '',
			'accent_hex'   => '',
			'heading_font' => '',
			'body_font'    => '',
			'sample_pattern' => '',
		];

		if ( function_exists( 'wp_get_global_settings' ) ) {
			$g = (array) wp_get_global_settings();
			$layout = (array) ( $g['layout'] ?? [] );
			$summary['content_size'] = (string) ( $layout['contentSize'] ?? '' );
			$summary['wide_size']    = (string) ( $layout['wideSize']    ?? '' );

			$palette = $g['color']['palette']['theme'] ?? $g['color']['palette']['default'] ?? [];
			if ( is_array( $palette ) ) {
				foreach ( $palette as $c ) {
					$slug = strtolower( (string) ( $c['slug'] ?? '' ) );
					if ( false !== strpos( $slug, 'accent' ) || false !== strpos( $slug, 'primary' ) ) {
						$summary['accent_hex'] = (string) ( $c['color'] ?? '' );
						break;
					}
				}
				if ( '' === $summary['accent_hex'] && ! empty( $palette[0]['color'] ) ) {
					$summary['accent_hex'] = (string) $palette[0]['color'];
				}
			}

			$fonts = $g['typography']['fontFamilies']['theme'] ?? $g['typography']['fontFamilies']['default'] ?? [];
			if ( is_array( $fonts ) ) {
				foreach ( $fonts as $f ) {
					$slug = strtolower( (string) ( $f['slug'] ?? '' ) );
					if ( false !== strpos( $slug, 'display' ) || false !== strpos( $slug, 'heading' ) ) {
						$summary['heading_font'] = (string) ( $f['fontFamily'] ?? '' );
					} elseif ( false !== strpos( $slug, 'body' ) || false !== strpos( $slug, 'sans' ) || '' === $summary['body_font'] ) {
						$summary['body_font'] = (string) ( $f['fontFamily'] ?? '' );
					}
				}
			}
		}

		/* Snip a sample of any "section" or "content" pattern this theme
		 * registered, to teach the writer model the wrapper class names. */
		if ( function_exists( 'WP_Block_Patterns_Registry::get_instance' ) || class_exists( 'WP_Block_Patterns_Registry' ) ) {
			$registry = WP_Block_Patterns_Registry::get_instance();
			$all = $registry->get_all_registered();
			foreach ( $all as $p ) {
				$cat = (array) ( $p['categories'] ?? [] );
				$name = (string) ( $p['name'] ?? '' );
				if ( false === strpos( $name, $summary['theme_slug'] ) ) continue;
				$content = (string) ( $p['content'] ?? '' );
				if ( strlen( $content ) > 30 && strlen( $content ) < 2000 ) {
					$summary['sample_pattern'] = $content;
					break;
				}
			}
		}

		return $summary;
	}

	/** Render the summary as a compact text block for prompt injection. */
	public static function as_prompt_text(): string {
		$s = self::active_summary();
		$out = "Active WordPress theme: {$s['theme_name']}" . ( $s['is_block'] ? ' (block theme)' : ' (classic theme)' ) . ".";
		if ( $s['content_size'] )  $out .= " Content column width: {$s['content_size']}.";
		if ( $s['accent_hex'] )    $out .= " Accent colour: {$s['accent_hex']}.";
		if ( $s['heading_font'] )  $out .= " Heading font: {$s['heading_font']}.";
		if ( $s['body_font'] )     $out .= " Body font: {$s['body_font']}.";
		$out .= " Match the active theme's typography hierarchy and content width.";
		if ( $s['sample_pattern'] ) {
			$snip = mb_substr( $s['sample_pattern'], 0, 500 );
			$out .= "\n\nExample of a section this theme uses (mirror this wrapper structure when emitting block markup):\n" . $snip;
		}
		return $out;
	}
}
