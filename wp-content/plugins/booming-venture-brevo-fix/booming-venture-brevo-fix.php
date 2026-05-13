<?php
/**
 * Plugin Name:       Booming Venture , Brevo Form Fix
 * Plugin URI:        https://boomingventure.com
 * Description:       Stops the front-end form spinner from hanging on Brevo / Sendinblue submissions. Fixes three known issues: (1) PHP notice output corrupting REST JSON responses, (2) Brevo SMTP API rejecting payloads missing the recipient name, (3) Brevo Contacts API returning 400 on duplicate emails instead of upserting. Zero configuration. Activate and forget.
 * Version:           1.0.0
 * Requires at least: 6.6
 * Requires PHP:      8.0
 * Author:            Booming Venture
 * Author URI:        https://boomingventure.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       booming-venture-brevo-fix
 * Update URI:        false
 *
 * @package BoomingVentureBrevoFix
 */

defined( 'ABSPATH' ) || exit;

const BVBF_VERSION = '1.0.0';
const BVBF_LOG_OPT = 'bvbf_event_log';

/* ---------------------------------------------------------------------
 * FIX 1 , suppress PHP notice / warning output during REST API requests
 *
 * Why: the front-end form submits via wp-json/contact-form-7/... or
 * wp-json/wp/v2/... and expects JSON. When PHP notices echo to output
 * BEFORE the REST server sends headers, the response body becomes
 * "<b>Warning</b> ... {"success":true}" which the browser's JSON.parse
 * throws on. Neither success nor error handlers fire. The spinner
 * spins forever.
 *
 * We disable display_errors only inside REST requests, so errors are
 * still logged to debug.log (for diagnosis) but never echoed into the
 * HTTP response body.
 * --------------------------------------------------------------------- */
add_filter( 'rest_pre_dispatch', function ( $result, $server, $request ) {
	@ini_set( 'display_errors', '0' );
	@ini_set( 'html_errors', '0' );
	return $result;
}, 1, 3 );

/* Belt + braces , also clear any output already accumulated by the
 * time wp-json receives the request. */
add_action( 'rest_api_init', function () {
	if ( ob_get_level() > 0 ) {
		@ob_clean();
	}
} );

/* ---------------------------------------------------------------------
 * FIX 2 , add missing name field to Brevo SMTP / contact API payloads
 *
 * Brevo's API contracts require `name` in every `to` array entry on
 * /v3/smtp/email and a sensible `attributes.FIRSTNAME` on /v3/contacts.
 * The Brevo WP plugin sometimes sends only email when the source form
 * (CF7, Fluent, Gravity, custom) does not collect a name field.
 *
 * We intercept outbound HTTP requests to api.brevo.com and
 * api.sendinblue.com, parse the JSON body, inject sensible name
 * defaults derived from the email local part, and let the request
 * continue.
 * --------------------------------------------------------------------- */
add_filter( 'http_request_args', function ( $args, $url ) {
	if ( ! is_string( $url ) ) return $args;
	if ( strpos( $url, 'api.brevo.com' ) === false && strpos( $url, 'api.sendinblue.com' ) === false ) {
		return $args;
	}
	if ( empty( $args['body'] ) || ! is_string( $args['body'] ) ) return $args;

	$decoded = json_decode( $args['body'], true );
	if ( ! is_array( $decoded ) ) return $args;

	$changed = false;

	/* /v3/smtp/email , every `to` entry needs `email` AND `name`. */
	if ( ! empty( $decoded['to'] ) && is_array( $decoded['to'] ) ) {
		foreach ( $decoded['to'] as $idx => $entry ) {
			if ( ! is_array( $entry ) ) continue;
			if ( ! empty( $entry['email'] ) && empty( $entry['name'] ) ) {
				$decoded['to'][ $idx ]['name'] = bvbf_default_name_from_email( (string) $entry['email'] );
				$changed = true;
			}
		}
	}

	/* /v3/contacts , add FIRSTNAME attribute when only email is given. */
	if (
		strpos( $url, '/v3/contacts' ) !== false
		&& ! empty( $decoded['email'] )
		&& empty( $decoded['attributes']['FIRSTNAME'] )
		&& empty( $decoded['attributes']['NAME'] )
	) {
		if ( ! isset( $decoded['attributes'] ) || ! is_array( $decoded['attributes'] ) ) {
			$decoded['attributes'] = [];
		}
		$decoded['attributes']['FIRSTNAME'] = bvbf_default_name_from_email( (string) $decoded['email'] );
		$changed = true;
	}

	/* /v3/contacts , enable upsert so duplicates UPDATE instead of 400.
	 * Brevo accepts updateEnabled at the top level of the create payload. */
	if (
		strpos( $url, '/v3/contacts' ) !== false
		&& strpos( $url, '/v3/contacts/' ) === false       // not a PUT to /contacts/{id}
		&& ! isset( $decoded['updateEnabled'] )
	) {
		$decoded['updateEnabled'] = true;
		$changed = true;
	}

	if ( $changed ) {
		$args['body'] = wp_json_encode( $decoded );
		bvbf_log( 'rewrote outbound Brevo payload', [ 'url' => $url, 'body' => $decoded ] );
	}

	return $args;
}, 10, 2 );

/* ---------------------------------------------------------------------
 * FIX 3 , treat Brevo duplicate_parameter 400 as success
 *
 * When the user submits the same email twice (e.g. they already
 * subscribed), Brevo returns 400 duplicate_parameter. The Brevo WP
 * plugin treats this as a hard failure and the form returns an error.
 * From the user's perspective, "I am already subscribed" is success.
 *
 * We catch the 400 response, inspect the body, and convert
 * duplicate_parameter into 200 + success body so the form continues.
 * --------------------------------------------------------------------- */
add_filter( 'http_response', function ( $response, $args, $url ) {
	if ( ! is_string( $url ) ) return $response;
	if ( strpos( $url, 'api.brevo.com' ) === false && strpos( $url, 'api.sendinblue.com' ) === false ) {
		return $response;
	}
	$code = (int) wp_remote_retrieve_response_code( $response );
	if ( 400 !== $code ) return $response;

	$body    = (string) wp_remote_retrieve_body( $response );
	$decoded = json_decode( $body, true );
	if ( ! is_array( $decoded ) || empty( $decoded['code'] ) ) return $response;

	if ( in_array( $decoded['code'], [ 'duplicate_parameter', 'document_already_exists' ], true ) ) {
		bvbf_log( 'swallowed duplicate as success', [ 'url' => $url, 'body' => $decoded ] );
		/* Build a fake 200 response so calling code sees success. */
		if ( is_array( $response ) ) {
			$response['response']['code']    = 200;
			$response['response']['message'] = 'OK';
			$response['body']                = wp_json_encode( [
				'id'      => 0,
				'message' => 'already-subscribed',
			] );
		}
	}

	return $response;
}, 99, 3 );

/* ---------------------------------------------------------------------
 * Helper , derive a plausible name from an email local-part
 * ("john.doe@x.com" -> "John Doe"). Falls back to "Subscriber".
 * --------------------------------------------------------------------- */
function bvbf_default_name_from_email( string $email ): string {
	$at = strpos( $email, '@' );
	if ( false === $at || 0 === $at ) return 'Subscriber';
	$local = substr( $email, 0, $at );
	$local = preg_replace( '/[+\.\-_0-9]+/', ' ', $local );
	$local = trim( (string) $local );
	if ( '' === $local ) return 'Subscriber';
	return ucwords( strtolower( $local ) );
}

/* ---------------------------------------------------------------------
 * Helper , append to a small ring-buffer log for diagnostics.
 * Stored as a non-autoloaded option, capped at 50 entries.
 * --------------------------------------------------------------------- */
function bvbf_log( string $event, array $context = [] ): void {
	$log   = (array) get_option( BVBF_LOG_OPT, [] );
	$log[] = [
		'time'    => current_time( 'mysql' ),
		'event'   => $event,
		'context' => $context,
	];
	if ( count( $log ) > 50 ) {
		$log = array_slice( $log, -50 );
	}
	update_option( BVBF_LOG_OPT, $log, false );
}

/* ---------------------------------------------------------------------
 * Tools -> Brevo Form Fix , diagnostic page
 * --------------------------------------------------------------------- */
add_action( 'admin_menu', function () {
	add_management_page(
		__( 'Brevo Form Fix', 'booming-venture-brevo-fix' ),
		__( 'Brevo Form Fix', 'booming-venture-brevo-fix' ),
		'manage_options',
		'booming-venture-brevo-fix',
		'bvbf_render_admin_page'
	);
} );

function bvbf_render_admin_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission.', 'booming-venture-brevo-fix' ) );
	}
	$log = (array) get_option( BVBF_LOG_OPT, [] );
	$log = array_reverse( $log );
	?>
	<div class="wrap">
		<h1><?php echo esc_html__( 'Booming Venture , Brevo Form Fix', 'booming-venture-brevo-fix' ); ?></h1>

		<div class="notice" style="border-left:4px solid #16a34a;padding:1rem 1.25rem;background:#f0fdf4;">
			<p style="margin:0;"><strong><?php echo esc_html__( 'Active.', 'booming-venture-brevo-fix' ); ?></strong>
			<?php echo esc_html__( 'PHP notice suppression on REST API, Brevo payload patching, and duplicate-as-success are all enabled. No configuration needed.', 'booming-venture-brevo-fix' ); ?>
			</p>
		</div>

		<h2><?php echo esc_html__( 'What this plugin does', 'booming-venture-brevo-fix' ); ?></h2>
		<ol>
			<li><?php echo esc_html__( 'Suppresses display of PHP errors on every REST API request so JSON responses are not corrupted by leaked notices.', 'booming-venture-brevo-fix' ); ?></li>
			<li><?php echo esc_html__( 'Intercepts outbound HTTP requests to api.brevo.com and api.sendinblue.com, injects a name field into every `to` entry of the SMTP payload, adds FIRSTNAME to /v3/contacts payloads, and sets updateEnabled=true so duplicates upsert.', 'booming-venture-brevo-fix' ); ?></li>
			<li><?php echo esc_html__( 'Converts Brevo `400 duplicate_parameter` responses into 200 OK so already-subscribed users see success instead of a form error.', 'booming-venture-brevo-fix' ); ?></li>
		</ol>

		<h2><?php echo esc_html__( 'What this plugin will NEVER do', 'booming-venture-brevo-fix' ); ?></h2>
		<ul>
			<li><?php echo esc_html__( 'Touch the Brevo plugin source.', 'booming-venture-brevo-fix' ); ?></li>
			<li><?php echo esc_html__( 'Touch wp-config.php.', 'booming-venture-brevo-fix' ); ?></li>
			<li><?php echo esc_html__( 'Modify theme files.', 'booming-venture-brevo-fix' ); ?></li>
			<li><?php echo esc_html__( 'Hide errors from debug.log. Everything still gets logged for diagnosis.', 'booming-venture-brevo-fix' ); ?></li>
		</ul>

		<h2><?php echo esc_html__( 'Recent activity', 'booming-venture-brevo-fix' ); ?></h2>
		<?php if ( empty( $log ) ) : ?>
			<p><?php echo esc_html__( 'No activity yet. Submit a form on the front-end and refresh this page.', 'booming-venture-brevo-fix' ); ?></p>
		<?php else : ?>
			<table class="widefat striped" style="margin-top:0.5rem;">
				<thead><tr><th style="width:160px;"><?php echo esc_html__( 'Time', 'booming-venture-brevo-fix' ); ?></th><th><?php echo esc_html__( 'Event', 'booming-venture-brevo-fix' ); ?></th><th><?php echo esc_html__( 'Context', 'booming-venture-brevo-fix' ); ?></th></tr></thead>
				<tbody>
				<?php foreach ( $log as $row ) : ?>
					<tr>
						<td><code><?php echo esc_html( (string) ( $row['time'] ?? '' ) ); ?></code></td>
						<td><?php echo esc_html( (string) ( $row['event'] ?? '' ) ); ?></td>
						<td><pre style="white-space:pre-wrap;margin:0;font-size:11px;"><?php echo esc_html( wp_json_encode( $row['context'] ?? [], JSON_PRETTY_PRINT ) ); ?></pre></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:1rem;">
				<?php wp_nonce_field( 'bvbf_clear_log' ); ?>
				<input type="hidden" name="action" value="bvbf_clear_log">
				<button type="submit" class="button"><?php echo esc_html__( 'Clear activity log', 'booming-venture-brevo-fix' ); ?></button>
			</form>
		<?php endif; ?>
	</div>
	<?php
}

add_action( 'admin_post_bvbf_clear_log', function () {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );
	check_admin_referer( 'bvbf_clear_log' );
	delete_option( BVBF_LOG_OPT );
	wp_safe_redirect( admin_url( 'tools.php?page=booming-venture-brevo-fix' ) );
	exit;
} );

/* ---------------------------------------------------------------------
 * Uninstall: remove only our option. We touch nothing else.
 * --------------------------------------------------------------------- */
register_activation_hook( __FILE__, function () {
	if ( version_compare( PHP_VERSION, '8.0', '<' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die(
			esc_html__( 'Booming Venture Brevo Form Fix requires PHP 8.0+.', 'booming-venture-brevo-fix' ),
			esc_html__( 'Activation error', 'booming-venture-brevo-fix' ),
			[ 'back_link' => true ]
		);
	}
	register_uninstall_hook( __FILE__, 'bvbf_on_uninstall' );
} );

function bvbf_on_uninstall(): void {
	if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) return;
	if ( ! current_user_can( 'activate_plugins' ) ) return;
	delete_option( BVBF_LOG_OPT );
}
