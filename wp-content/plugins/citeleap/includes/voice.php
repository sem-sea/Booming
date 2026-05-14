<?php
/**
 * CiteLeap , voice.php
 *
 * Pulls 3 to 5 of the site's most recent published posts and renders
 * their titles + first paragraphs as a "voice samples" block for the
 * writer prompt. The model reads the samples and matches the
 * sentence length, directness, point of view, and tone.
 *
 * Cheap RAG. No embeddings. Just "here is how this site speaks".
 *
 * @package CiteLeap
 */

defined( 'ABSPATH' ) || exit;

class CiteLeap_Voice {

	public static function samples( int $count = 3, int $exclude = 0 ): array {
		$args = [
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => max( 1, min( 5, $count ) ),
			'orderby'        => 'modified',
			'order'          => 'DESC',
			'no_found_rows'  => true,
		];
		if ( $exclude ) $args['post__not_in'] = [ $exclude ];
		return get_posts( $args );
	}

	public static function as_prompt_text( int $count = 3, int $exclude = 0 ): string {
		$posts = self::samples( $count, $exclude );
		if ( empty( $posts ) ) {
			return 'VOICE SAMPLES: none yet on this site. Default to a plain, direct, human voice with short sentences and concrete examples.';
		}
		$out = [];
		$out[] = 'VOICE SAMPLES (read these carefully and write the new post in this same voice , same sentence length, same level of directness, same way of speaking to the reader):';
		$out[] = '';
		foreach ( $posts as $i => $p ) {
			$body  = wp_strip_all_tags( strip_shortcodes( (string) $p->post_content ) );
			$body  = trim( preg_replace( '/\s+/', ' ', $body ) );
			$body  = mb_substr( $body, 0, 700 );
			$title = (string) $p->post_title;
			$out[] = sprintf( '--- Sample %d: "%s" ---', $i + 1, $title );
			$out[] = $body;
			$out[] = '';
		}
		return implode( "\n", $out );
	}
}
