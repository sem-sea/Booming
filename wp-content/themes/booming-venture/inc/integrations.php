<?php
/**
 * Third-party integrations ,  Brevo, Contact Form 7 hand-off.
 *
 * The recommended flow:
 *   - Install Contact Form 7 + Honeypot for CF7 + Flamingo + a Brevo
 *     bridge (CF7 to Brevo / official Brevo plugin / CF7 to Any API).
 *   - Create six forms (contact, newsletter, newsletter-inline,
 *     growth-guide, quickscan, head-of-growth). CF7 will assign each a
 *     hash ID like "a1b2c3d4".
 *   - Map each semantic slug to the CF7 hash ID under
 *     Settings → Booming Venture.
 *
 * The patterns ship with `[contact-form-7 id="contact"]` etc. so the
 * markup stays human-readable; this file rewrites the slug to the real
 * CF7 ID at render time.
 *
 * @package BoomingVenture
 */

defined( 'ABSPATH' ) || exit;

/**
 * Map semantic Contact Form 7 slugs to real CF7 form IDs (hash strings
 * since CF7 5.7, numeric post IDs still accepted).
 */
function bv_cf7_slug_map(): array {
	$default = [
		'contact'           => '231533b',
		'newsletter'        => '6c25a82',
		'newsletter-inline' => '6c25a82',
		'growth-guide'      => 'e46231e',
		'quickscan'         => '',
		'head-of-growth'    => '',
	];
	$map = (array) get_option( 'bv_cf7_map', [] );
	if ( defined( 'BV_CF7_MAP' ) && is_array( BV_CF7_MAP ) ) {
		$map = BV_CF7_MAP;
	}
	return array_merge( $default, $map );
}

/**
 * Verify a CF7 form id or hash actually exists in the database.
 * Accepts:
 *   , a numeric post ID (post_type = wpcf7_contact_form)
 *   , a CF7 hash (matches the postmeta key `_hash`)
 * Returns the numeric post ID if found, otherwise 0.
 */
function bv_cf7_form_exists( string $id_or_hash ): int {
	global $wpdb;
	if ( '' === $id_or_hash ) return 0;
	if ( ctype_digit( $id_or_hash ) ) {
		$post = get_post( (int) $id_or_hash );
		return ( $post && 'wpcf7_contact_form' === $post->post_type ) ? (int) $post->ID : 0;
	}
	$post_id = $wpdb->get_var( $wpdb->prepare(
		"SELECT pm.post_id FROM {$wpdb->postmeta} pm
		 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
		 WHERE pm.meta_key = '_hash' AND pm.meta_value = %s
		   AND p.post_type = 'wpcf7_contact_form' LIMIT 1",
		$id_or_hash
	) );
	return $post_id ? (int) $post_id : 0;
}

/**
 * Try to find a CF7 form by its post title, given a semantic slug.
 * "newsletter-inline" -> Newsletter Inline, Newsletter inline, Newsletter
 * Returns the form's id-or-hash if found, otherwise empty string.
 */
function bv_cf7_find_by_title( string $slug ): string {
	$candidates = [
		ucwords( str_replace( '-', ' ', $slug ) ),
		ucfirst( str_replace( '-', ' ', $slug ) ),
		str_replace( '-', ' ', $slug ),
		strtoupper( str_replace( '-', ' ', $slug ) ),
	];
	if ( str_contains( $slug, '-' ) ) {
		$candidates[] = ucwords( explode( '-', $slug )[0] );
	}
	global $wpdb;
	foreach ( array_unique( $candidates ) as $title ) {
		$post_id = $wpdb->get_var( $wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts} WHERE post_title = %s AND post_type = 'wpcf7_contact_form' AND post_status = 'publish' LIMIT 1",
			$title
		) );
		if ( $post_id ) {
			$hash = get_post_meta( (int) $post_id, '_hash', true );
			return $hash ? (string) $hash : (string) (int) $post_id;
		}
	}
	return '';
}

/**
 * Smart resolver: turn any incoming shortcode id into a real, verified
 * CF7 reference (numeric id or hash). Falls through these lookups:
 *   1. The configured slug map (Settings -> Booming Venture).
 *   2. Pass-through if it is already a real CF7 form.
 *   3. Search CF7 forms by post title matching the slug.
 */
function bv_cf7_resolve_id( string $id_raw ): string {
	$map  = bv_cf7_slug_map();
	$slug = sanitize_key( $id_raw );

	if ( isset( $map[ $slug ] ) && '' !== $map[ $slug ] ) {
		$mapped = (string) $map[ $slug ];
		if ( bv_cf7_form_exists( $mapped ) ) {
			return $mapped;
		}
	}
	if ( bv_cf7_form_exists( $id_raw ) ) {
		return $id_raw;
	}
	$by_title = bv_cf7_find_by_title( $slug );
	if ( '' !== $by_title ) {
		return $by_title;
	}
	return '';
}

/* Rewrite `[contact-form-7 id="contact"]` -> `[contact-form-7 id="<real-id>"]`.
 *
 * Strategy: resolve the slug to a verified CF7 form, then call do_shortcode
 * with the verified id so CF7's own handler renders. If we cannot resolve,
 * show a helpful admin-only error so the operator knows what is missing. */
add_filter( 'pre_do_shortcode_tag', function ( $output, $tag, $attr ) {
	if ( 'contact-form-7' !== $tag ) return $output;
	if ( ! isset( $attr['id'] ) ) return $output;

	$id_raw = (string) $attr['id'];

	/* Re-entrancy guard. CF7 is also registered as `contact-form-7`; once we
	 * rewrite and call do_shortcode again, the recursion comes back through
	 * here. Detect "already verified" ids and let CF7's own handler run. */
	if ( bv_cf7_form_exists( $id_raw ) ) {
		return $output;
	}

	$resolved = bv_cf7_resolve_id( $id_raw );

	if ( '' !== $resolved ) {
		$attr['id'] = sanitize_text_field( $resolved );
		$attr_str = '';
		foreach ( $attr as $k => $v ) {
			$attr_str .= ' ' . $k . '="' . esc_attr( $v ) . '"';
		}
		return do_shortcode( '[' . $tag . $attr_str . ']' );
	}

	/* Numeric or hash-shape we could not find. Probably a stale hash. */
	if ( current_user_can( 'edit_posts' ) ) {
		$slug = sanitize_key( $id_raw );
		return '<div class="bv-form-missing" style="padding:1rem;border:2px dashed #fca5a5;border-radius:0.5rem;background:#fef2f2;color:#7f1d1d;line-height:1.55">'
			. '<strong>Contact Form 7 form not found.</strong><br>'
			. 'Slug or id: <code>' . esc_html( $id_raw ) . '</code>. '
			. 'Create a CF7 form (Contact &rarr; Contact Forms), then either name it <code>' . esc_html( ucwords( str_replace( '-', ' ', $slug ) ) ) . '</code> '
			. 'or paste its hash id under <strong>Settings &rarr; Booming Venture &rarr; Forms</strong> next to <code>' . esc_html( $slug ) . '</code>.'
			. '</div>';
	}
	return '';
}, 10, 3 );

/* CF7 setup: disable autop so we keep CSS Grid control over the form layout.
 *
 * Note: we intentionally do NOT gate CF7 JS/CSS by has_shortcode() on the
 * post content. Our CF7 shortcodes live inside block patterns (PHP files
 * referenced via <!-- wp:pattern -->), which has_shortcode() cannot see
 * because it only scans the raw stored post_content. Gating on that check
 * caused CF7 scripts and styles to be skipped on every page that used
 * patterns for the form, leaving an unstyled form that did not submit.
 * Letting CF7 load on every page is a small perf cost and reliably correct. */
add_filter( 'wpcf7_autop_or_not', '__return_false' );

/* Settings page: map CF7 slugs → IDs. */
add_action( 'admin_menu', function () {
	add_options_page(
		__( 'Booming Venture', 'booming-venture' ),
		__( 'Booming Venture', 'booming-venture' ),
		'manage_options',
		'booming-venture',
		'bv_settings_page'
	);
} );

add_action( 'admin_init', function () {
	register_setting( 'bv_settings', 'bv_cf7_map', [
		'type'              => 'array',
		'sanitize_callback' => function ( $value ) {
			$out = [];
			foreach ( (array) $value as $k => $v ) {
				$out[ sanitize_key( $k ) ] = sanitize_text_field( $v );
			}
			return $out;
		},
		'default'           => [],
	] );
	register_setting( 'bv_settings', 'bv_brevo_api_key', [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ] );
	register_setting( 'bv_settings', 'bv_brevo_allowed_lists', [
		'type'              => 'array',
		'sanitize_callback' => function ( $value ) {
			if ( is_string( $value ) ) {
				$value = preg_split( '/[\s,]+/', $value, -1, PREG_SPLIT_NO_EMPTY );
			}
			return array_values( array_unique( array_filter( array_map( 'absint', (array) $value ) ) ) );
		},
		'default'           => [ 2, 3, 4 ],
	] );
	register_setting( 'bv_settings', 'bv_gtm_id', [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ] );
	register_setting( 'bv_settings', 'bv_dataspeak_id', [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ] );
	register_setting( 'bv_settings', 'bv_media_map', [
		'type'              => 'array',
		'sanitize_callback' => function ( $value ) {
			$out = [];
			foreach ( (array) $value as $k => $v ) {
				$out[ sanitize_key( $k ) ] = sanitize_text_field( $v );
			}
			return $out;
		},
		'default'           => [],
	] );
} );

/* Default brand image map ,  UUIDs / URLs the user uploaded to Strato.
 * Override per-slug in Settings -> Booming Venture -> Brand images. */
function bv_media_defaults(): array {
	$base = 'https://boomingventure.com/wp-content/uploads/2026/05/';
	return [
		// Strategic Consulting , consultants with tablet, cyan wall.
		'service-1'    => $base . '4e357139-5a7e-4336-8796-94013f33dc3d.png',
		// Performance Marketing , speaker on stage with brand slide.
		'service-2'    => $base . '64a1eea5-ff4d-4a89-83d2-1e2e9c5258d6.png',
		// AI-Powered Solutions , consultants reviewing dashboard, lockers bg.
		'service-3'    => $base . '8ae06510-02a0-453f-a984-f0642561707e.png',
		// Growth Optimization , diverse team around laptops.
		'service-4'    => $base . '12b0da47-c27a-4cb5-b98a-61b5ddd8dcf4.png',
		// Free Growth Guide , booklets on a desk with notes.
		'growth-guide' => $base . '0100bc15-cf04-42bc-827d-1ecfe72c1ff6.png',
		// Founder portrait , Ben Verschuur.
		'ben-founder'  => $base . 'Ben.png',
		// Extra brand image , available for future patterns.
		'hero-extra'   => $base . 'ab7934c4-0ed7-4a27-b7fd-aa2bcee19255.png',
	];
}

add_filter( 'pre_option_bv_media_map', function ( $pre ) {
	if ( false !== $pre ) return $pre;
	$stored = get_option( 'bv_media_map_stored', null );
	if ( null === $stored ) return $pre;
	return array_merge( bv_media_defaults(), (array) $stored );
} );

/* Surface defaults transparently when the option row exists. */
add_filter( 'option_bv_media_map', function ( $value ) {
	return array_merge( bv_media_defaults(), (array) $value );
} );

/* Surface defaults transparently when the option row does NOT yet exist.
 * WordPress fires this filter, not option_<name>, when get_option finds
 * no row in wp_options. Without it, a fresh install sees zero defaults
 * and every brand image breaks. */
add_filter( 'default_option_bv_media_map', function ( $default_value ) {
	return array_merge( bv_media_defaults(), (array) $default_value );
} );

function bv_settings_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) return;
	$map = bv_cf7_slug_map();
	?>
	<div class="wrap">
		<h1>Booming Venture</h1>
		<form method="post" action="options.php">
			<?php settings_fields( 'bv_settings' ); ?>
			<h2>Contact Form 7 ,  slug → CF7 form</h2>
			<p class="description">After creating each form in <strong>Contact → Contact Forms</strong>, either name the form to match the slug (e.g. "Contact") OR paste its CF7 hash id (e.g. <code>a1b2c3d4</code>) or numeric id into the box. The status column shows whether the form was found.</p>
			<table class="form-table">
				<thead><tr><th>Slug</th><th>Configured id or hash</th><th>Status</th></tr></thead>
				<tbody>
			<?php foreach ( $map as $slug => $id ) :
				$resolved = bv_cf7_resolve_id( $slug );
				if ( '' !== $resolved ) {
					$status = '<span style="color:#16a34a">&#10003; Found</span> &mdash; using <code>' . esc_html( $resolved ) . '</code>';
				} else {
					$status = '<span style="color:#b91c1c">&#10007; Not found</span> &mdash; create the form or paste an id.';
				}
				?>
				<tr>
					<th><label for="bv-cf7-<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $slug ); ?></label></th>
					<td><input type="text" id="bv-cf7-<?php echo esc_attr( $slug ); ?>" name="bv_cf7_map[<?php echo esc_attr( $slug ); ?>]" value="<?php echo esc_attr( (string) $id ); ?>" class="regular-text" placeholder="e.g. a1b2c3d4"></td>
					<td><?php echo $status; ?></td>
				</tr>
			<?php endforeach; ?>
				</tbody>
			</table>

			<h2>Brand images ,  slug → upload UUID or URL</h2>
			<p class="description">Paste either the bare UUID (e.g. <code>4e357139-5a7e-4336-8796-94013f33dc3d</code> ,  resolved against <code>/wp-content/uploads/2026/05/&lt;uuid&gt;.png</code>), a path starting with <code>/wp-content/</code>, or a full <code>https://</code> URL.</p>
			<table class="form-table">
			<?php $media = (array) get_option( 'bv_media_map', [] ); foreach ( bv_media_defaults() as $slug => $default ) : $val = $media[ $slug ] ?? $default; ?>
				<tr>
					<th><label for="bv-media-<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $slug ); ?></label></th>
					<td><input type="text" id="bv-media-<?php echo esc_attr( $slug ); ?>" name="bv_media_map[<?php echo esc_attr( $slug ); ?>]" value="<?php echo esc_attr( (string) $val ); ?>" class="regular-text code" placeholder="UUID, /wp-content/uploads/... or https://..."></td>
				</tr>
			<?php endforeach; ?>
			</table>

			<h2>Tracking IDs</h2>
			<table class="form-table">
				<tr><th><label for="bv-gtm">Google Tag Manager ID</label></th><td><input type="text" id="bv-gtm" name="bv_gtm_id" value="<?php echo esc_attr( (string) get_option( 'bv_gtm_id', 'GTM-K9532WK5' ) ); ?>" class="regular-text"></td></tr>
				<tr><th><label for="bv-ds">DataSpeak interface ID</label></th><td><input type="text" id="bv-ds" name="bv_dataspeak_id" value="<?php echo esc_attr( (string) get_option( 'bv_dataspeak_id', '6863892dbcf4fea86a49e9f8' ) ); ?>" class="regular-text"></td></tr>
			</table>

			<h2>Brevo</h2>
			<p class="description">Prefer setting <code>BV_BREVO_API_KEY</code> in <code>wp-config.php</code> for production.</p>
			<table class="form-table">
				<tr><th><label for="bv-brevo">Brevo API key</label></th><td><input type="password" id="bv-brevo" name="bv_brevo_api_key" value="<?php echo esc_attr( (string) get_option( 'bv_brevo_api_key', '' ) ); ?>" class="regular-text" autocomplete="off"></td></tr>
				<tr><th><label for="bv-lists">Allowed list IDs</label></th><td><input type="text" id="bv-lists" name="bv_brevo_allowed_lists" value="<?php echo esc_attr( implode( ',', (array) get_option( 'bv_brevo_allowed_lists', [ 2, 3, 4 ] ) ) ); ?>" class="regular-text" placeholder="2,3,4"><p class="description">Comma-separated. Submissions to any list not in this list are rejected.</p></td></tr>
			</table>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/* Admin notice nudging the install. */
add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'install_plugins' ) ) return;
	if ( defined( 'WPCF7_VERSION' ) ) return;
	?>
	<div class="notice notice-info is-dismissible">
		<p><strong>Booming Venture theme:</strong> install <a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=contact+form+7&tab=search&type=term' ) ); ?>">Contact Form 7</a>, <a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=honeypot+for+contact+form+7&tab=search&type=term' ) ); ?>">Honeypot for CF7</a>, and <a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=flamingo&tab=search&type=term' ) ); ?>">Flamingo</a> to wire up the forms baked into the patterns.</p>
	</div>
	<?php
} );

/* REST endpoint: /wp-json/bv/v1/brevo */
add_action( 'rest_api_init', function () {
	register_rest_route( 'bv/v1', '/brevo', [
		'methods'             => 'POST',
		'permission_callback' => '__return_true',
		'callback'            => 'bv_brevo_proxy_handler',
		'args'                => [
			'email'   => [ 'required' => true, 'validate_callback' => fn ( $v ) => is_email( $v ) ],
			'list_id' => [ 'required' => true, 'sanitize_callback' => 'absint' ],
			'attrs'   => [ 'required' => false ],
			'nonce'   => [ 'required' => true ],
		],
	] );
} );

function bv_brevo_proxy_handler( WP_REST_Request $req ) {
	/* 1. CSRF ,  only meaningful for logged-in users, but doesn't hurt. */
	if ( ! wp_verify_nonce( $req->get_param( 'nonce' ), 'bv_brevo' ) ) {
		return new WP_Error( 'bad_nonce', 'Invalid nonce', [ 'status' => 403 ] );
	}

	/* 2. Honeypot ,  JS strips this on submit; bots usually fill it. */
	if ( ! empty( $req->get_param( 'website' ) ) ) {
		return new WP_REST_Response( [ 'ok' => true ], 200 ); // pretend success
	}

	/* 3. Rate limit ,  keyed on IP + email, 5 attempts / 10 min. */
	$ip   = isset( $_SERVER['REMOTE_ADDR'] ) ? preg_replace( '/[^0-9a-f:.]/i', '', $_SERVER['REMOTE_ADDR'] ) : '0.0.0.0';
	$key  = 'bv_brevo_rl_' . md5( $ip . '|' . strtolower( (string) $req->get_param( 'email' ) ) );
	$hits = (int) get_transient( $key );
	if ( $hits >= 5 ) {
		return new WP_Error( 'rate_limited', 'Too many requests. Try again in a few minutes.', [ 'status' => 429 ] );
	}
	set_transient( $key, $hits + 1, 10 * MINUTE_IN_SECONDS );

	/* 4. List ID allowlist ,  never let arbitrary list IDs through. */
	$allowed = (array) get_option( 'bv_brevo_allowed_lists', [ 2, 3, 4 ] );
	$list_id = (int) $req->get_param( 'list_id' );
	if ( ! in_array( $list_id, array_map( 'intval', $allowed ), true ) ) {
		return new WP_Error( 'list_not_allowed', 'That list is not allowed for public submissions.', [ 'status' => 403 ] );
	}

	/* 5. Auth + API key. */
	$api_key = defined( 'BV_BREVO_API_KEY' ) ? BV_BREVO_API_KEY : get_option( 'bv_brevo_api_key' );
	if ( ! $api_key ) {
		return new WP_Error( 'not_configured', 'Brevo API key is not configured', [ 'status' => 503 ] );
	}

	/* 6. Sanitize attributes ,  string-keyed scalars only. */
	$attrs = [];
	foreach ( (array) $req->get_param( 'attrs' ) as $k => $v ) {
		if ( ! is_scalar( $v ) ) continue;
		$attrs[ sanitize_key( $k ) ] = sanitize_text_field( (string) $v );
	}

	$payload = [
		'email'         => sanitize_email( $req->get_param( 'email' ) ),
		'listIds'       => [ $list_id ],
		'attributes'    => $attrs,
		'updateEnabled' => true,
	];

	$response = wp_remote_post( 'https://api.brevo.com/v3/contacts', [
		'headers' => [
			'accept'       => 'application/json',
			'content-type' => 'application/json',
			'api-key'      => $api_key,
		],
		'body'    => wp_json_encode( $payload ),
		'timeout' => 15,
	] );

	if ( is_wp_error( $response ) ) {
		return new WP_Error( 'brevo_error', $response->get_error_message(), [ 'status' => 502 ] );
	}

	$code = wp_remote_retrieve_response_code( $response );
	return new WP_REST_Response(
		[ 'ok' => $code >= 200 && $code < 300 ],
		$code
	);
}

/* Expose nonce + endpoint to front-end JS. */
add_action( 'wp_enqueue_scripts', function () {
	wp_localize_script( 'booming-venture', 'BV_BREVO', [
		'endpoint' => esc_url_raw( rest_url( 'bv/v1/brevo' ) ),
		'nonce'    => wp_create_nonce( 'bv_brevo' ),
		'lists'    => [
			'newsletter'    => 3,
			'growth_guide'  => 2,
			'contact'       => 4,
		],
	] );
}, 30 );
