<?php
/**
 * One-time import of the Dutch section and the B2B GEO Index pages (1.17.0).
 *
 * POST /wp-json/bv/v1/import-nl (administrators only) reads
 * import/nl/content.json and creates, all PRIVATE:
 *   - the Dutch home /nl/ and its 13 child pages,
 *   - the B2B GEO Index page in English and in Dutch,
 *   - the Dutch blog posts.
 * Every item that already exists (same path or slug) is skipped, so the
 * import can run again without making duplicates. Nothing is published:
 * the site owner checks each page and publishes it when ready.
 *
 * @package BoomingVenture
 */

defined( 'ABSPATH' ) || exit;

/** Create one page or post from a REST-style body, or return the existing one. */
function bv_import_item( array $b, string $type, int $parent = 0, array $extra_meta = [] ): array {
	$slug = sanitize_title( (string) ( $b['slug'] ?? '' ) );
	if ( '' === $slug ) {
		return [ 'id' => 0, 'state' => 'error', 'slug' => '' ];
	}
	if ( 'page' === $type ) {
		$path     = $parent ? get_page_uri( $parent ) . '/' . $slug : $slug;
		$existing = get_page_by_path( $path );
		if ( $existing && 'trash' !== $existing->post_status ) {
			return [ 'id' => (int) $existing->ID, 'state' => 'exists', 'slug' => $path ];
		}
	} else {
		$found = get_posts( [
			'name'             => $slug,
			'post_type'        => 'post',
			'post_status'      => [ 'publish', 'private', 'draft', 'future', 'pending' ],
			'numberposts'      => 1,
			'fields'           => 'ids',
			'suppress_filters' => true,
		] );
		if ( $found ) {
			return [ 'id' => (int) $found[0], 'state' => 'exists', 'slug' => $slug ];
		}
	}

	$args = [
		'post_type'      => $type,
		'post_title'     => (string) ( $b['title'] ?? $slug ),
		'post_name'      => $slug,
		'post_status'    => 'private',
		'post_content'   => (string) ( $b['content'] ?? '' ),
		'post_excerpt'   => (string) ( $b['excerpt'] ?? '' ),
		'post_parent'    => $parent,
		'comment_status' => (string) ( $b['comment_status'] ?? 'closed' ),
		'ping_status'    => (string) ( $b['ping_status'] ?? 'closed' ),
		'meta_input'     => array_merge( (array) ( $b['meta'] ?? [] ), $extra_meta ),
	];
	if ( ! empty( $b['author'] ) && get_userdata( (int) $b['author'] ) ) {
		$args['post_author'] = (int) $b['author'];
	}
	if ( ! empty( $b['date_gmt'] ) ) {
		$gmt                   = gmdate( 'Y-m-d H:i:s', strtotime( $b['date_gmt'] . ' UTC' ) );
		$args['post_date_gmt'] = $gmt;
		$args['post_date']     = get_date_from_gmt( $gmt );
	}
	if ( ! empty( $b['categories'] ) ) {
		$args['post_category'] = array_map( 'intval', (array) $b['categories'] );
	}

	$id = wp_insert_post( wp_slash( $args ), true );
	if ( is_wp_error( $id ) ) {
		return [ 'id' => 0, 'state' => 'error', 'slug' => $slug, 'error' => $id->get_error_message() ];
	}
	if ( ! empty( $b['template'] ) ) {
		update_post_meta( $id, '_wp_page_template', sanitize_key( $b['template'] ) );
	}
	if ( ! empty( $b['featured_media'] ) && wp_attachment_is_image( (int) $b['featured_media'] ) ) {
		set_post_thumbnail( $id, (int) $b['featured_media'] );
	}
	return [ 'id' => (int) $id, 'state' => 'created', 'slug' => 'page' === $type ? get_page_uri( $id ) : $slug ];
}

add_action( 'rest_api_init', static function () {
	register_rest_route( 'bv/v1', '/import-nl', [
		'methods'             => 'POST',
		'permission_callback' => static fn() => current_user_can( 'manage_options' ),
		'callback'            => static function () {
			$file = get_theme_file_path( 'import/nl/content.json' );
			$data = is_readable( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null;
			if ( ! is_array( $data ) ) {
				return new WP_Error( 'bv_import', 'import/nl/content.json is missing or invalid', [ 'status' => 500 ] );
			}
			$log = [];
			kses_remove_filters(); // trusted content shipped with the theme

			/* Dutch home, then its child pages. */
			$home  = bv_import_item( (array) $data['pages']['parent'], 'page' );
			$log[] = $home;
			if ( $home['id'] ) {
				foreach ( (array) $data['pages']['children'] as $child ) {
					$log[] = bv_import_item( (array) $child, 'page', $home['id'] );
				}
			}

			/* GEO Index: English first, then the Dutch page linked to it. */
			$en    = bv_import_item( (array) $data['geo']['en'], 'page' );
			$log[] = $en;
			if ( $home['id'] && $en['id'] ) {
				$log[] = bv_import_item( (array) $data['geo']['nl'], 'page', $home['id'], [ '_bv_lang' => 'nl', '_bv_translation' => $en['id'] ] );
			}

			/* Dutch posts. */
			foreach ( (array) $data['posts'] as $post ) {
				$log[] = bv_import_item( (array) $post, 'post' );
			}

			kses_init_filters();
			$count = array_count_values( array_column( $log, 'state' ) );
			return rest_ensure_response( [ 'created' => $count['created'] ?? 0, 'exists' => $count['exists'] ?? 0, 'errors' => $count['error'] ?? 0, 'items' => $log ] );
		},
	] );
} );
