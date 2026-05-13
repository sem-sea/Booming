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

/* Rewrite `[contact-form-7 id="contact"]` → `[contact-form-7 id="<hash>"]`.
 *
 * NOTE: real CF7 hash IDs are 7 lowercase alphanumeric chars (e.g. "231533b"),
 * which means a slug like "contact" or "quickscan" matches the same shape.
 * So we ALWAYS check the slug map first and only fall through to a
 * passthrough if the value isn't a known slug. */
add_filter( 'pre_do_shortcode_tag', function ( $output, $tag, $attr ) {
	if ( 'contact-form-7' !== $tag ) return $output;
	if ( ! isset( $attr['id'] ) ) return $output;

	$id_raw = (string) $attr['id'];
	$map    = bv_cf7_slug_map();
	$slug   = sanitize_key( $id_raw );

	/* Known semantic slug ,  rewrite to mapped CF7 ID. */
	if ( isset( $map[ $slug ] ) && '' !== $map[ $slug ] ) {
		$attr['id'] = sanitize_text_field( $map[ $slug ] );
		$attr_str = '';
		foreach ( $attr as $k => $v ) {
			$attr_str .= ' ' . $k . '="' . esc_attr( $v ) . '"';
		}
		return do_shortcode( '[' . $tag . $attr_str . ']' );
	}

	/* Numeric ID or already-mapped hash ,  let CF7 handle it directly. */
	if ( is_numeric( $id_raw ) || preg_match( '/^[a-f0-9]{6,8}$/i', $id_raw ) ) {
		return $output;
	}

	if ( current_user_can( 'edit_posts' ) ) {
		return '<div class="bv-form-missing" style="padding:1rem;border:2px dashed #fca5a5;border-radius:0.5rem;background:#fef2f2;color:#7f1d1d;">Form <code>' . esc_html( $slug ) . '</code> is not mapped to a Contact Form 7 ID. Set it in <strong>Settings → Booming Venture → Forms</strong> or via <code>BV_CF7_MAP</code>.</div>';
	}
	return '';
}, 10, 3 );

/* CF7 setup: disable autop (gives us CSS Grid control), keep its JS/CSS
 * only on pages with a CF7 shortcode (perf win). */
add_filter( 'wpcf7_autop_or_not', '__return_false' );

add_action( 'wp_enqueue_scripts', function () {
	if ( ! function_exists( 'wpcf7_enqueue_scripts' ) ) return;
	global $post;
	$has_cf7 = is_a( $post, 'WP_Post' ) && (
		has_shortcode( $post->post_content, 'contact-form-7' ) ||
		false !== stripos( get_the_content(), 'contact-form-7' )
	);
	if ( ! $has_cf7 && ! is_front_page() ) {
		add_filter( 'wpcf7_load_js',  '__return_false' );
		add_filter( 'wpcf7_load_css', '__return_false' );
	}
}, 5 );

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
	return [
		// Strategic Consulting ,  consultants with tablet, cyan wall.
		'service-1'    => '4e357139-5a7e-4336-8796-94013f33dc3d',
		// Performance Marketing ,  speaker on stage with brand slide.
		'service-2'    => '64a1eea5-ff4d-4a89-83d2-1e2e9c5258d6',
		// AI-Powered Solutions ,  consultants reviewing dashboard, lockers bg.
		'service-3'    => '8ae06510-02a0-453f-a984-f0642561707e',
		// Growth Optimization ,  diverse team around laptops.
		'service-4'    => '12b0da47-c27a-4cb5-b98a-61b5ddd8dcf4',
		// Free Growth Guide ,  booklets on a desk with notes.
		'growth-guide' => '0100bc15-cf04-42bc-827d-1ecfe72c1ff6',
		// Founder portrait , Ben Verschuur.
		'ben-founder'  => 'https://boomingventure.com/wp-content/uploads/2026/05/Ben.png',
	];
}

add_filter( 'pre_option_bv_media_map', function ( $pre ) {
	if ( false !== $pre ) return $pre;
	$stored = get_option( 'bv_media_map_stored', null );
	if ( null === $stored ) return $pre;
	return array_merge( bv_media_defaults(), (array) $stored );
} );

/* Surface defaults transparently when no option row exists yet. */
add_filter( 'option_bv_media_map', function ( $value ) {
	return array_merge( bv_media_defaults(), (array) $value );
} );

function bv_settings_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) return;
	$map = bv_cf7_slug_map();
	?>
	<div class="wrap">
		<h1>Booming Venture</h1>
		<form method="post" action="options.php">
			<?php settings_fields( 'bv_settings' ); ?>
			<h2>Contact Form 7 ,  slug → CF7 form ID</h2>
			<p class="description">After creating each form in <strong>Contact → Contact Forms</strong>, paste the CF7 hash ID (e.g. <code>a1b2c3d4</code>) or numeric form ID into the matching slug below.</p>
			<table class="form-table">
			<?php foreach ( $map as $slug => $id ) : ?>
				<tr>
					<th><label for="bv-cf7-<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $slug ); ?></label></th>
					<td><input type="text" id="bv-cf7-<?php echo esc_attr( $slug ); ?>" name="bv_cf7_map[<?php echo esc_attr( $slug ); ?>]" value="<?php echo esc_attr( (string) $id ); ?>" class="regular-text" placeholder="e.g. a1b2c3d4"></td>
				</tr>
			<?php endforeach; ?>
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
