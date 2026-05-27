<?php
/**
 * OndernemerMarketing , contact form handler.
 *
 * Receives the contact form POST (action=ondm_contact), validates,
 * honeypot-checks, then sends via wp_mail() to the configured
 * recipient. Replace wp_mail() with the Brevo / SendGrid SDK to
 * push through a transactional provider.
 *
 * @package OndernemerMarketing
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_post_ondm_contact',        'ondm_handle_contact' );
add_action( 'admin_post_nopriv_ondm_contact', 'ondm_handle_contact' );

function ondm_handle_contact(): void {
	$referrer = wp_get_referer() ?: home_url();
	$redirect = function ( string $status ) use ( $referrer ): void {
		wp_safe_redirect( add_query_arg( 'ondm_msg', $status, $referrer ) );
		exit;
	};

	check_admin_referer( 'ondm_contact', 'ondm_nonce' );

	// Honeypot , bots fill the hidden ondm_hp field; humans don't.
	if ( ! empty( $_POST['ondm_hp'] ) ) {
		$redirect( 'ok' ); // pretend success so bots don't retry
	}

	$first   = sanitize_text_field( wp_unslash( $_POST['ondm_first']   ?? '' ) );
	$last    = sanitize_text_field( wp_unslash( $_POST['ondm_last']    ?? '' ) );
	$email   = sanitize_email(      wp_unslash( $_POST['ondm_email']   ?? '' ) );
	$message = sanitize_textarea_field( wp_unslash( $_POST['ondm_message'] ?? '' ) );

	if ( '' === $first || '' === $last || '' === $message || ! is_email( $email ) ) {
		$redirect( 'err' );
	}

	if ( mb_strlen( $message ) > 5000 ) {
		$message = mb_substr( $message, 0, 5000 );
	}

	$to      = (string) apply_filters( 'ondm_contact_to', get_option( 'admin_email' ) );
	$subject = sprintf( '[OndernemerMarketing.nl] Nieuw bericht van %s %s', $first, $last );
	$body    = sprintf(
		"Nieuw contactformulier-bericht:\n\nNaam: %s %s\nE-mail: %s\n\nBericht:\n%s\n\n,\nVerzonden vanaf %s",
		$first, $last, $email, $message, home_url()
	);
	$headers = [
		'Content-Type: text/plain; charset=UTF-8',
		'Reply-To: ' . $first . ' ' . $last . ' <' . $email . '>',
	];

	$ok = wp_mail( $to, $subject, $body, $headers );

	do_action( 'ondm_contact_submitted', [
		'first'   => $first,
		'last'    => $last,
		'email'   => $email,
		'message' => $message,
		'ok'      => (bool) $ok,
	] );

	$redirect( $ok ? 'ok' : 'err' );
}
