=== Booming Venture Blog Importer ===
Contributors: boomingventure
Tags: importer, blog content, booming venture, wxr, geo, aeo
Requires at least: 6.6
Tested up to: 6.8
Requires PHP: 8.0
Stable tag: 1.0.1
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds or refreshes the Booming Venture long-form blog posts (83 articles, every one 1,200+ words, refreshed for May 2026). Strictly blog posts only.

== Description ==

This plugin is the surgical counterpart to the broader Booming Venture Importer. It does ONE job: get the long-form May 2026 blog posts onto your site, with three modes:

* **Add new posts** , inserts any bundled post whose slug is missing on this site. Skips matching slugs (your edits are safe).
* **Refresh existing post bodies** , overwrites the title, content, and excerpt of every existing blog post whose slug matches the bundled WXR. Use this to upgrade old short posts to the long-form May 2026 versions.
* **Sync all** , runs both: add missing + refresh existing in one go.

The bundled WXR contains 83 published posts, every one 1,200 to 1,969 words, May 2026 statistics, GEO/AEO compliant (question-shaped H2s, 20-25 word answer capsules, ski-ramp statistics with named sources, ordered lists, FAQ blocks).

The plugin ships its own **mobile-first CSS** so posts render neatly on any active theme. The stylesheet loads only on single post views.

== What this plugin will NEVER do ==

* Touch your pages, services, custom post types, or menus.
* Change the front page, posts page, or permalink structure.
* Modify theme settings, widgets, or any option unrelated to this plugin.
* Touch post meta on existing posts (custom fields are preserved).
* Touch post categories, dates, authors, or comment status on existing posts.
* Send any data to external services. No phone-home.

== Installation ==

1. Upload the plugin zip via Plugins -> Add New -> Upload Plugin.
2. Activate the plugin.
3. Go to Tools -> Booming Venture Blog Importer.
4. Pick one of the three buttons:
   * **Add new blog posts** , safest, inserts missing only.
   * **Refresh existing posts from bundled WXR** , destructive on bodies, gated by confirmation.
   * **Sync all (add + refresh)** , the fastest path to "every post matches the bundled version", gated by confirmation.

== Frequently Asked Questions ==

= Will refresh overwrite my edits? =

Yes, on bodies only (title + content + excerpt). Post meta, categories, dates, authors are preserved. WordPress keeps revisions, so you can roll back any single post via Posts -> Edit -> Revisions.

= Does the plugin need the Booming Venture theme? =

No. The plugin ships its own mobile-first CSS and works on any WP theme. Pattern references in the bundled posts are pure block markup (no theme-specific patterns), so they render anywhere.

= Why does this plugin exist next to "Booming Venture Importer"? =

The other plugin imports the full site bundle (pages, services, structural pages, permalinks, front-page setup). This one is content-only. If your site is already configured and you only want to manage blog posts, use this one.

= Can I run "Refresh" multiple times? =

Yes, the operation is idempotent. Running it again with the same WXR re-asserts the same content, so there is no drift.

= Is the May 2026 dataset GEO/AEO compliant? =

Yes. Every post has a question-shaped H2 with a 20-25 word answer capsule, 3+ statistics with named-source links in the first 30 percent of body, an ordered list with 5+ items, a 5-question FAQ block, and a CTA. No em-dashes, no AI-tell phrases anywhere in the WXR.

== Changelog ==

= 1.0.1 =
* Re-package release with fresh versioned URL (per project convention: every release gets a new unique download filename).
* No functional changes from 1.0.0.

= 1.0.0 =
* Initial release.
* Three modes: Add, Refresh, Sync.
* Bundled WXR with 83 posts (min 1,200 words, median 1,294, max 1,969).
* May 2026 dataset: 2025 references retired in titles and prose; URL slugs preserved for SEO.
* Mobile-first stylesheet enqueued on single post views.
* Activity log persisted between visits.
* WP Plugin Handbook compliance: activation/uninstall hooks, capability checks, nonces, file-type validation on uploads, full i18n, ABSPATH guard, no autoload on options.

== Privacy ==

This plugin stores two options in wp_options:

* bvbi_last_run , timestamp of the last action.
* bvbi_last_log , the result log of the last run (array of plain text lines).

No data is sent to any external service. No user data is collected.
