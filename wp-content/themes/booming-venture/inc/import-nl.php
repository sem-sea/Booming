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
 * POST /wp-json/bv/v1/import-nl?update=geo only refreshes the text of the
 * two existing GEO Index pages (1.20.0).
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
		'callback'            => static function ( WP_REST_Request $req ) {
			$file = get_theme_file_path( 'import/nl/content.json' );
			$data = is_readable( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null;
			if ( ! is_array( $data ) ) {
				return new WP_Error( 'bv_import', 'import/nl/content.json is missing or invalid', [ 'status' => 500 ] );
			}
			$log = [];
			kses_remove_filters(); // trusted content shipped with the theme

			/*
			 * ?update=geo (1.20.0): put the shipped text of the two GEO Index
			 * pages into the existing pages. Status, slug, parent and date stay
			 * as they are; WordPress keeps the old text as a revision.
			 */
			if ( 'geo' === $req->get_param( 'update' ) ) {
				foreach ( [ 'en' => 'b2b-geo-index', 'nl' => 'nl/b2b-geo-index' ] as $lang => $path ) {
					$page = get_page_by_path( $path );
					$b    = (array) ( $data['geo'][ $lang ] ?? [] );
					if ( ! $page || empty( $b['content'] ) ) {
						$log[] = [ 'id' => 0, 'state' => 'missing', 'slug' => $path ];
						continue;
					}
					$meta  = array_intersect_key( (array) ( $b['meta'] ?? [] ), array_flip( [ '_yoast_wpseo_title', '_yoast_wpseo_metadesc' ] ) );
					$id    = wp_update_post( wp_slash( [ 'ID' => $page->ID, 'post_content' => (string) $b['content'], 'meta_input' => $meta ] ), true );
					$log[] = [ 'id' => (int) $page->ID, 'state' => is_wp_error( $id ) ? 'error' : 'updated', 'slug' => $path ];
				}
				kses_init_filters();
				return rest_ensure_response( [ 'items' => $log ] );
			}

			/*
			 * ?update=geo-method (1.24.0): create or refresh the method pages
			 * under the two GEO Index pages: /b2b-geo-index/methodology/ and
			 * /nl/b2b-geo-index/methode/, published, linked as translations.
			 */
			if ( 'geo-method' === $req->get_param( 'update' ) ) {
				$ids = [];
				foreach ( [ 'en' => 'b2b-geo-index', 'nl' => 'nl/b2b-geo-index' ] as $lang => $parent_path ) {
					$parent = get_page_by_path( $parent_path );
					$b      = (array) ( $data['geo_method'][ $lang ] ?? [] );
					if ( ! $parent || empty( $b['content'] ) || empty( $b['slug'] ) ) {
						$log[] = [ 'id' => 0, 'state' => 'missing', 'slug' => $parent_path ];
						continue;
					}
					$meta = array_intersect_key( (array) ( $b['meta'] ?? [] ), array_flip( [ '_yoast_wpseo_title', '_yoast_wpseo_metadesc' ] ) );
					if ( 'nl' === $lang ) {
						$meta['_bv_lang'] = 'nl';
						if ( ! empty( $ids['en'] ) ) {
							$meta['_bv_translation'] = (int) $ids['en'];
						}
					}
					$page = get_page_by_path( $parent_path . '/' . sanitize_title( $b['slug'] ) );
					if ( $page ) {
						$id    = wp_update_post( wp_slash( [ 'ID' => $page->ID, 'post_title' => (string) $b['title'], 'post_content' => (string) $b['content'], 'post_status' => 'publish', 'meta_input' => $meta ] ), true );
						$state = 'updated';
					} else {
						$id    = wp_insert_post( wp_slash( [ 'post_type' => 'page', 'post_title' => (string) $b['title'], 'post_name' => sanitize_title( $b['slug'] ), 'post_parent' => (int) $parent->ID, 'post_status' => 'publish', 'post_content' => (string) $b['content'], 'comment_status' => 'closed', 'ping_status' => 'closed', 'meta_input' => $meta ] ), true );
						$state = 'created';
					}
					if ( is_wp_error( $id ) ) {
						$log[] = [ 'id' => 0, 'state' => 'error', 'slug' => $parent_path, 'error' => $id->get_error_message() ];
						continue;
					}
					update_post_meta( (int) $id, '_wp_page_template', 'page-landing' );
					$ids[ $lang ] = (int) $id;
					$log[]        = [ 'id' => (int) $id, 'state' => $state, 'slug' => get_page_uri( (int) $id ) ];
				}
				kses_init_filters();
				return rest_ensure_response( [ 'items' => $log ] );
			}

			/*
			 * ?update=pages-v2 (1.30.0): create or refresh the growth pages in
			 * English and Dutch (GEO service, Rotterdam, cases, pricing, AI
			 * check, statistics and one page per index sector), published and
			 * linked as translations. Text, title and SEO meta follow the
			 * theme; WordPress keeps the old text as a revision.
			 */
			if ( 'pages-v2' === $req->get_param( 'update' ) ) {
				$only = sanitize_key( (string) $req->get_param( 'only' ) );
				foreach ( (array) ( $data['pages_v2'] ?? [] ) as $item ) {
					if ( '' !== $only && $only !== ( $item['key'] ?? '' ) ) {
						continue;
					}
					$ids = [];
					foreach ( [ 'en', 'nl' ] as $lang ) {
						$b = (array) ( $item[ $lang ] ?? [] );
						if ( empty( $b['slug'] ) || empty( $b['content'] ) ) {
							continue;
						}
						$parent_path = trim( (string) ( $b['parent'] ?? '' ), '/' );
						$parent      = '' !== $parent_path ? get_page_by_path( $parent_path ) : null;
						if ( '' !== $parent_path && ! $parent ) {
							$log[] = [ 'id' => 0, 'state' => 'no-parent', 'slug' => $parent_path . '/' . $b['slug'] ];
							continue;
						}
						$path = ( $parent ? $parent_path . '/' : '' ) . sanitize_title( $b['slug'] );
						$meta = array_intersect_key( (array) ( $b['meta'] ?? [] ), array_flip( [ '_yoast_wpseo_title', '_yoast_wpseo_metadesc' ] ) );
						if ( ! empty( $item['sector'] ) ) {
							$meta['_bv_gi_sector'] = sanitize_key( $item['sector'] );
						}
						if ( 'nl' === $lang ) {
							$meta['_bv_lang'] = 'nl';
							if ( ! empty( $ids['en'] ) ) {
								$meta['_bv_translation'] = (int) $ids['en'];
							}
						}
						$page = get_page_by_path( $path );
						$args = [ 'post_title' => (string) $b['title'], 'post_content' => (string) $b['content'], 'post_status' => 'publish', 'meta_input' => $meta ];
						if ( $page ) {
							$id    = wp_update_post( wp_slash( [ 'ID' => $page->ID ] + $args ), true );
							$state = 'updated';
						} else {
							$id    = wp_insert_post( wp_slash( $args + [ 'post_type' => 'page', 'post_name' => sanitize_title( $b['slug'] ), 'post_parent' => $parent ? (int) $parent->ID : 0, 'comment_status' => 'closed', 'ping_status' => 'closed' ] ), true );
							$state = 'created';
						}
						if ( is_wp_error( $id ) ) {
							$log[] = [ 'id' => 0, 'state' => 'error', 'slug' => $path, 'error' => $id->get_error_message() ];
							continue;
						}
						update_post_meta( (int) $id, '_wp_page_template', sanitize_key( (string) ( $b['template'] ?? 'page-landing' ) ) );
						$ids[ $lang ] = (int) $id;
						$log[]        = [ 'id' => (int) $id, 'state' => $state, 'slug' => get_page_uri( (int) $id ) ];
					}
				}
				kses_init_filters();
				$count = array_count_values( array_column( $log, 'state' ) );
				return rest_ensure_response( [ 'created' => $count['created'] ?? 0, 'updated' => $count['updated'] ?? 0, 'errors' => ( $count['error'] ?? 0 ) + ( $count['no-parent'] ?? 0 ), 'items' => $log ] );
			}

			/*
			 * ?update=posts-nl-v2 (1.30.0): Dutch versions of English posts.
			 * Each copies the publish time, category and featured image of its
			 * English original and is linked to it, so a post whose English
			 * version is live goes live now and the rest follow their English
			 * schedule. Existing Dutch posts get the shipped text.
			 */
			if ( 'posts-nl-v2' === $req->get_param( 'update' ) ) {
				foreach ( (array) ( $data['posts_nl_v2'] ?? [] ) as $b ) {
					$en = get_posts( [ 'name' => sanitize_title( (string) ( $b['en'] ?? '' ) ), 'post_type' => 'post', 'post_status' => [ 'publish', 'future' ], 'numberposts' => 1, 'suppress_filters' => true ] );
					if ( ! $en || empty( $b['slug'] ) || empty( $b['content'] ) ) {
						$log[] = [ 'id' => 0, 'state' => 'no-original', 'slug' => (string) ( $b['slug'] ?? '' ) ];
						continue;
					}
					$en   = $en[0];
					$meta = array_intersect_key( (array) ( $b['meta'] ?? [] ), array_flip( [ '_yoast_wpseo_title', '_yoast_wpseo_metadesc' ] ) ) + [ '_bv_lang' => 'nl', '_bv_translation' => (int) $en->ID ];
					$args = [
						'post_title'   => (string) $b['title'],
						'post_content' => (string) $b['content'],
						'post_excerpt' => (string) ( $b['excerpt'] ?? '' ),
						'meta_input'   => $meta,
					];
					$have = get_posts( [ 'name' => sanitize_title( $b['slug'] ), 'post_type' => 'post', 'post_status' => [ 'publish', 'future', 'private', 'draft' ], 'numberposts' => 1, 'suppress_filters' => true ] );
					if ( $have ) {
						$id    = wp_update_post( wp_slash( [ 'ID' => $have[0]->ID ] + $args ), true );
						$state = 'updated';
					} else {
						$future = strtotime( $en->post_date_gmt . ' UTC' ) > time();
						$id     = wp_insert_post( wp_slash( $args + [
							'post_type'      => 'post',
							'post_name'      => sanitize_title( $b['slug'] ),
							'post_status'    => $future ? 'future' : 'publish',
							'post_date'      => $en->post_date,
							'post_date_gmt'  => $en->post_date_gmt,
							'post_author'    => (int) $en->post_author,
							'post_category'  => wp_get_post_categories( $en->ID ),
							'comment_status' => 'closed',
							'ping_status'    => 'closed',
						] ), true );
						$state  = $future ? 'scheduled' : 'created';
						if ( ! is_wp_error( $id ) && has_post_thumbnail( $en ) ) {
							set_post_thumbnail( (int) $id, get_post_thumbnail_id( $en ) );
						}
					}
					$log[] = is_wp_error( $id )
						? [ 'id' => 0, 'state' => 'error', 'slug' => $b['slug'], 'error' => $id->get_error_message() ]
						: [ 'id' => (int) $id, 'state' => $state, 'slug' => $b['slug'], 'date' => get_post_field( 'post_date', (int) $id ) ];
				}
				kses_init_filters();
				$count = array_count_values( array_column( $log, 'state' ) );
				return rest_ensure_response( [ 'counts' => $count, 'items' => $log ] );
			}

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
