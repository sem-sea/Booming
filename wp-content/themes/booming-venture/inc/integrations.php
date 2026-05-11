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
	if ( ! wp_verify_nonce( $req->get_param( 'nonce' ), 'bv_brevo' ) ) {
		return new WP_Error( 'bad_nonce', 'Invalid nonce', [ 'status' => 403 ] );
	}

	$api_key = defined( 'BV_BREVO_API_KEY' ) ? BV_BREVO_API_KEY : get_option( 'bv_brevo_api_key' );
	if ( ! $api_key ) {
		return new WP_Error( 'not_configured', 'Brevo API key is not configured', [ 'status' => 503 ] );
	}

	$payload = [
		'email'         => sanitize_email( $req->get_param( 'email' ) ),
		'listIds'       => [ (int) $req->get_param( 'list_id' ) ],
		'attributes'    => (array) $req->get_param( 'attrs' ),
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
