<?php
/**
 * Security & hardening defaults.
 * Settings users can toggle via wp-config or filters.
 *
 * @package BoomingVenture
 */

defined( 'ABSPATH' ) || exit;

/* Disable the REST API user enumeration endpoint for anon. */
add_filter( 'rest_endpoints', function ( $endpoints ) {
	if ( ! is_user_logged_in() ) {
		unset( $endpoints['/wp/v2/users'], $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
	}
	return $endpoints;
} );

/* Hide login error specifics. */
add_filter( 'login_errors', fn () => __( 'Invalid credentials.', 'booming-venture' ) );

/* Disable XML-RPC pingbacks (still used for self-pings + Jetpack — toggle if needed). */
add_filter( 'xmlrpc_methods', function ( $methods ) {
	unset( $methods['pingback.ping'], $methods['pingback.extensions.getPingbacks'] );
	return $methods;
} );

/* Send sensible security headers on the front end. CSP intentionally omitted —
 * configure per environment because of GTM / DataSpeak / Brevo / Unsplash. */
add_action( 'send_headers', function () {
	if ( is_admin() ) return;
	header( 'X-Content-Type-Options: nosniff' );
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	header( 'Permissions-Policy: camera=(), microphone=(), geolocation=(), interest-cohort=()' );
	header( 'X-Frame-Options: SAMEORIGIN' );
} );

/* Block author=N enumeration. */
add_action( 'template_redirect', function () {
	if ( is_author() && ! is_user_logged_in() ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
} );
