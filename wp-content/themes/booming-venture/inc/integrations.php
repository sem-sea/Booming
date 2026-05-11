<?php
/**
 * Third-party integrations — Brevo, Fluent Forms hand-off.
 *
 * The recommended flow:
 *   - Install Fluent Forms (free) + Fluent Forms Brevo connector.
 *   - Configure each form (contact, newsletter, growth-guide, newsletter-inline, quickscan, head-of-growth)
 *     to push to the matching Brevo list.
 *
 * As a fallback for sites NOT running Fluent Forms, this file exposes a
 * tiny REST endpoint that the theme can post to from a custom form. It
 * is feature-gated behind the `bv_brevo_api_key` option / constant so
 * nothing fires unless the user opts in.
 *
 * @package BoomingVenture
 */

defined( 'ABSPATH' ) || exit;

/**
 * Map semantic Fluent Forms slugs to real numeric form IDs.
 *
 * The patterns ship with [fluentform id="contact"] etc. so the markup stays
 * human-readable. Once the user creates each form they store the ID/slug
 * mapping in the `bv_fluentform_map` option (or via the constant). This
 * filter rewrites the shortcode at render time so the numeric Fluent Forms
 * ID flows through correctly.
 */
function bv_fluentform_slug_map(): array {
	$default = [
		'contact'           => 0,
		'newsletter'        => 0,
		'newsletter-inline' => 0,
		'growth-guide'      => 0,
		'quickscan'         => 0,
		'head-of-growth'    => 0,
	];
	$map = (array) get_option( 'bv_fluentform_map', [] );
	if ( defined( 'BV_FLUENTFORM_MAP' ) && is_array( BV_FLUENTFORM_MAP ) ) {
		$map = BV_FLUENTFORM_MAP;
	}
	return array_merge( $default, $map );
}

add_filter( 'pre_do_shortcode_tag', function ( $output, $tag, $attr ) {
	if ( 'fluentform' !== $tag ) return $output;
	if ( ! isset( $attr['id'] ) || is_numeric( $attr['id'] ) ) return $output;
	$map = bv_fluentform_slug_map();
	$slug = sanitize_key( $attr['id'] );
	if ( ! empty( $map[ $slug ] ) ) {
		$attr['id'] = (int) $map[ $slug ];
		$attr_str = '';
		foreach ( $attr as $k => $v ) {
			$attr_str .= ' ' . $k . '="' . esc_attr( $v ) . '"';
		}
		return do_shortcode( '[' . $tag . $attr_str . ']' );
	}
	if ( current_user_can( 'edit_posts' ) ) {
		return '<div class="bv-form-missing" style="padding:1rem;border:2px dashed #fca5a5;border-radius:0.5rem;background:#fef2f2;color:#7f1d1d;">Form <code>' . esc_html( $slug ) . '</code> is not mapped. Set it in <strong>Settings → Booming Venture → Forms</strong> or via <code>BV_FLUENTFORM_MAP</code>.</div>';
	}
	return '';
}, 10, 3 );

/* Settings page: map Fluent Forms slugs → IDs. */
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
	register_setting( 'bv_settings', 'bv_fluentform_map', [
		'type'              => 'array',
		'sanitize_callback' => function ( $value ) {
			$out = [];
			foreach ( (array) $value as $k => $v ) {
				$out[ sanitize_key( $k ) ] = absint( $v );
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
} );

function bv_settings_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) return;
	$map = bv_fluentform_slug_map();
	?>
	<div class="wrap">
		<h1>Booming Venture</h1>
		<form method="post" action="options.php">
			<?php settings_fields( 'bv_settings' ); ?>
			<h2>Fluent Forms — slug → ID</h2>
			<table class="form-table">
			<?php foreach ( $map as $slug => $id ) : ?>
				<tr>
					<th><label for="bv-ff-<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $slug ); ?></label></th>
					<td><input type="number" min="0" id="bv-ff-<?php echo esc_attr( $slug ); ?>" name="bv_fluentform_map[<?php echo esc_attr( $slug ); ?>]" value="<?php echo esc_attr( (string) $id ); ?>" class="small-text"></td>
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
	if ( defined( 'FLUENTFORM' ) ) return;
	if ( get_option( 'bv_dismissed_fluentform_notice' ) ) return;
	?>
	<div class="notice notice-info is-dismissible">
		<p><strong>Booming Venture theme:</strong> install <a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=fluent+forms&tab=search&type=term' ) ); ?>">Fluent Forms</a> + its Brevo connector to wire up the contact, newsletter, and growth-guide forms baked into the patterns.</p>
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
	/* 1. CSRF — only meaningful for logged-in users, but doesn't hurt. */
	if ( ! wp_verify_nonce( $req->get_param( 'nonce' ), 'bv_brevo' ) ) {
		return new WP_Error( 'bad_nonce', 'Invalid nonce', [ 'status' => 403 ] );
	}

	/* 2. Honeypot — JS strips this on submit; bots usually fill it. */
	if ( ! empty( $req->get_param( 'website' ) ) ) {
		return new WP_REST_Response( [ 'ok' => true ], 200 ); // pretend success
	}

	/* 3. Rate limit — keyed on IP + email, 5 attempts / 10 min. */
	$ip   = isset( $_SERVER['REMOTE_ADDR'] ) ? preg_replace( '/[^0-9a-f:.]/i', '', $_SERVER['REMOTE_ADDR'] ) : '0.0.0.0';
	$key  = 'bv_brevo_rl_' . md5( $ip . '|' . strtolower( (string) $req->get_param( 'email' ) ) );
	$hits = (int) get_transient( $key );
	if ( $hits >= 5 ) {
		return new WP_Error( 'rate_limited', 'Too many requests. Try again in a few minutes.', [ 'status' => 429 ] );
	}
	set_transient( $key, $hits + 1, 10 * MINUTE_IN_SECONDS );

	/* 4. List ID allowlist — never let arbitrary list IDs through. */
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

	/* 6. Sanitize attributes — string-keyed scalars only. */
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
