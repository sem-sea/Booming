=== Booming Venture , Brevo Form Fix ===
Contributors: boomingventure
Tags: brevo, sendinblue, contact-form-7, fluent-forms, smtp, rest-api
Requires at least: 6.6
Tested up to: 6.8
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Stops the front-end form spinner from hanging on Brevo / Sendinblue submissions. Patches three real-world issues with zero configuration.

== Description ==

If a visitor submits a form on your site and the loading spinner spins forever (without ever showing success or error), this plugin fixes it.

Three independent bugs combine to produce that symptom:

1. PHP notices echo into the HTTP response body **before** the REST API sends its JSON, corrupting the JSON. The browser's `JSON.parse` throws. Neither success nor error callback fires. Spinner hangs.
2. Brevo's `/v3/smtp/email` API rejects payloads where any `to` entry is missing the `name` field. Many WP forms only collect email, producing this 400.
3. Brevo's `/v3/contacts` API returns `400 duplicate_parameter` when the submitted email already exists. The Brevo WP plugin treats this as a hard error; from the user's perspective, "I am already on the list" is success.

This plugin fixes all three without modifying the Brevo plugin source, wp-config.php, or any theme file.

= What it does =

* On every REST API request: disables `display_errors` and `html_errors` so PHP notices land in `debug.log` only, never in the response body.
* On every outbound HTTP request to `api.brevo.com` or `api.sendinblue.com`:
  * Injects a sensible `name` into each `to` array entry on `/v3/smtp/email` (derived from the email local part).
  * Adds `FIRSTNAME` to `/v3/contacts` payloads when only email is supplied.
  * Sets `updateEnabled: true` on `POST /v3/contacts` so duplicates UPSERT instead of returning 400.
* On Brevo `400 duplicate_parameter` or `400 document_already_exists` responses: converts them to `200 OK` so the calling code (the Brevo WP plugin) treats them as success.
* Logs every patched request to a small ring buffer (max 50 entries) viewable at Tools -> Brevo Form Fix.

= What it does not do =

* Modify the Brevo plugin source.
* Modify wp-config.php.
* Modify any theme file.
* Hide errors from `debug.log`. Everything still gets logged for diagnosis.

== Installation ==

1. Upload the plugin zip via Plugins -> Add New -> Upload Plugin.
2. Activate. Done.

Submit your form on the front-end. The spinner should now resolve to success or error within the same time the Brevo API responds. Tools -> Brevo Form Fix shows the diagnostic log.

== Frequently Asked Questions ==

= I have a different SMTP plugin (WP Mail SMTP, FluentSMTP). Does this still help? =

The PHP-notice-suppression fix (issue 1) helps any form regardless of provider. The Brevo-specific fixes (issues 2 + 3) only fire on requests to api.brevo.com / api.sendinblue.com.

= Will this hide real errors? =

No. Errors are still written to `debug.log` exactly as before. Only their display in the HTTP response body is suppressed, which is the correct production setting.

= Does this need an API key? =

No. The plugin patches outbound requests in transit; it never talks to Brevo directly.

== Changelog ==

= 1.0.0 =
* Initial release.
* REST API output sanitisation.
* Brevo SMTP `to` name injection.
* Brevo contacts FIRSTNAME injection + updateEnabled upsert.
* Duplicate-as-success response coercion.
* Tools admin page with diagnostic ring-buffer log.

== Privacy ==

The plugin stores one option (`bvbf_event_log`) holding the last 50 patched outbound requests, for diagnosis. No data is sent to any external service. The ring buffer can be cleared from the admin page.
