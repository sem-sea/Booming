=== Booming Venture Importer ===
Contributors: boomingventure
Tags: importer, demo content, booming venture, wxr
Requires at least: 6.6
Tested up to: 6.8
Requires PHP: 8.0
Stable tag: 1.0.1
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

One-click importer for the Booming Venture demo content. Bundles the canonical WXR (13 pages, 4 services, 83 blog posts, categories). Re-runs safely.

== Description ==

This plugin imports the Booming Venture demo content into your WordPress site in a single click. It is designed to work alongside (or independently of) the Booming Venture theme.

Features:

* Bundles the full WXR file (pages, services, blog posts, categories).
* One-click import / re-import from Tools → Booming Venture Importer.
* Existing posts and pages with the same slug are skipped (your edits are safe).
* Structural pages (home, about, services, blog, unify-framework, calculator pages, lead-magnet pages) get their content refreshed on every run.
* Sets the static front page (Home) and posts page (Blog) automatically.
* Re-asserts the permalink structure `/blog/%postname%/` so blog URLs work out of the box.
* Flushes caches and rewrite rules after import.
* Pattern references in page content are expanded into real block markup so pages remain editable in the block editor.
* Optional upload of a custom WXR replaces the bundled one for a single import run.
* Activity log persisted in `wp_options` and displayed on every visit to the admin page.

== Installation ==

1. Upload the `booming-venture-importer` folder to your `/wp-content/plugins/` directory (or upload the zip via Plugins → Add New → Upload Plugin).
2. Activate the plugin via the Plugins screen.
3. Go to Tools → Booming Venture Importer and click **Import / Re-import content**.
4. Visit `/blog/` to verify the import landed.

== Frequently Asked Questions ==

= Is it safe to re-run the import? =

Yes. Existing posts and pages with the same slug are skipped automatically, so user edits are preserved. Only the theme-owned structural pages (Home, About, Services, Blog, UNIFY Framework, calculators, lead magnets) get their content refreshed on every run.

= Does it delete content when I deactivate the plugin? =

No. Deactivation leaves all imported content in place. Deleting the plugin (via Delete on the Plugins screen) removes only the plugin's two options (`bvi_content_imported`, `bvi_last_import_log`). Imported posts remain.

= What if my WXR is different? =

Use the "Or upload an alternative WXR file" form on the admin page. Upload your XML; the plugin runs the same importer on that file instead.

= Why does it need PHP 8.0? =

The importer uses PHP 8.0 syntax (typed properties, arrow functions, named arguments). The plugin refuses to activate on older PHP and points the operator at the host.

= What WordPress versions are supported? =

WordPress 6.6 and above. Tested through 6.8.

== Changelog ==

= 1.0.1 =
* Bundled WXR now ships every blog post at 1,200 words or more (median 1,294, max 1,964).
* All 43 previously-thin posts expanded with question-shaped H2s, 20-25 word answer capsules, 3+ ski-ramp statistics with named sources, comparison lists, FAQ blocks.
* Voice cleanup: zero em-dashes, zero banned AI tells anywhere in the import.
* No functional plugin changes; install path and admin UI unchanged.

= 1.0.0 =
* Initial public release.
* Bundled WXR (13 pages, 4 services, 83 blog posts, categories).
* One-click import + custom WXR upload.
* Pattern expansion so imported pages are editable in the block editor.
* Structural-page refresh on every run.
* Activation, deactivation, uninstall lifecycle hooks per WordPress Plugin Handbook.
* Full i18n via `load_plugin_textdomain`.
* Nonce-protected admin POST handlers, capability checks throughout.
* Strict file-type validation on custom WXR uploads.

== Privacy ==

This plugin stores two options in the `wp_options` table on the site:

* `bvi_content_imported` , the version of the importer that last ran (string).
* `bvi_last_import_log` , the result log of the last import run (array of plain text lines).

No data is sent to any external service. No user data is collected.
