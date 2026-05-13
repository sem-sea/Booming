=== Booming Venture , SEO Boost ===
Contributors: boomingventure
Tags: schema, json-ld, open graph, twitter card, indexnow, sitemap
Requires at least: 6.6
Tested up to: 6.8
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Auto-detects existing schema and SEO plugins. Injects only what is missing: JSON-LD, Open Graph + Twitter Card, canonical, meta description. Pings Google, Bing, and IndexNow on every new post.

== Description ==

= Two halves =

**Passive** (every page render): the plugin buffers `wp_head`, inspects what other plugins / the theme already wrote, and adds only the tags that are MISSING. If Yoast / Rank Math / All in One SEO / SEOPress / The SEO Framework / Slim SEO / Squirrly / a theme-shipped JSON-LD function is doing the job, the plugin stands down for that capability.

**Active** (every post publish): the plugin pings Google + Bing sitemaps, fires an IndexNow notification to Bing / Yandex / Naver, and warms the post URL once so caches prime before search bots arrive.

= What the passive half injects (only if missing) =

* `<link rel="canonical">`
* `<meta name="description">` (from excerpt; falls back to first 160 chars of content)
* `<meta property="og:title|og:description|og:url|og:type|og:site_name|og:locale|og:image|og:image:width|og:image:height|og:image:alt">`
* `<meta property="article:published_time|article:modified_time|article:author">` on blog posts
* `<meta name="twitter:card|twitter:title|twitter:description|twitter:image|twitter:image:alt">`
* `<script type="application/ld+json">` containing:
  * **Organization** , site-wide identity, logo, name.
  * **WebSite** , inLanguage, SearchAction.
  * **BlogPosting** , per-post: headline, description, datePublished, dateModified, author, image, wordCount.
  * **BreadcrumbList** , Home -> Blog (if posts page set) -> Current post.
  * **FAQPage** , auto-detected from an `<h2>` containing "FAQ" / "Frequently asked questions" followed by `<h3>` / `<p>` Q/A pairs.
  * **HowTo** , auto-detected when post title starts with "How to" / "How do" / "Steps to" and the body contains an ordered list of 3+ items.

= og:image source priority =

1. Featured image of the post.
2. First `<img>` in the post content.
3. Site logo (Appearance -> Customize -> Site Identity).

= What the active half does on publish =

1. Pings Google: `https://www.google.com/ping?sitemap=<site>/wp-sitemap.xml`
2. Pings Bing: `https://www.bing.com/ping?sitemap=<site>/wp-sitemap.xml`
3. IndexNow POST to `https://api.indexnow.org/IndexNow` with the new post URL. Bing, Yandex, and Naver receive the ping. A unique 32-char key is auto-generated on first run and saved both in `wp_options` and at the site root (`/{KEY}.txt`) per the IndexNow protocol.
4. Fires a `wp_remote_get` against the post URL with `blocking=false, timeout=1` so any page / object cache primes before search bots arrive.

All pings use `blocking=false` so the publish save is never slowed down.

= What it will NEVER do =

* Touch posts where you (or another SEO plugin) already control the schema or social tags.
* Modify wp-config or theme files.
* Run on admin, feeds, or robots.txt.
* Send any data to third parties beyond the four sitemap / IndexNow endpoints listed above.

== Installation ==

1. Upload the zip via Plugins -> Add New -> Upload Plugin.
2. Activate. Zero configuration.
3. View any front-end page, "View source", look for the new tags. Tools -> SEO Boost shows the detection table.

== Frequently Asked Questions ==

= I already have Yoast (or Rank Math, AIOSEO, etc.) installed. Will this conflict? =

No. The detection table on the admin page shows which SEO sources are active. When a known SEO plugin or theme-shipped JSON-LD is detected, the corresponding tags are NOT injected. You can run this plugin alongside any of them safely.

= Will it work on existing posts? =

Yes. Every front-end page render emits its missing SEO tags. No "regenerate" step is needed.

= Does it work for new posts automatically? =

Yes. Two paths:
1. **On publish**: the active boosters (sitemap ping + IndexNow + cache warm) fire immediately.
2. **On every render**: the passive half emits all missing tags on every page load, including the very first view of a brand-new post.

= Where is the IndexNow key file? =

After first publish, the plugin writes `/<32-char-key>.txt` to your site root containing the key (this is the IndexNow protocol's verification mechanism). You can see the key on the Tools -> SEO Boost admin page if curious.

== Changelog ==

= 1.0.0 =
* Initial release.
* Passive: head buffering + per-tag detection. Canonical, meta description, Open Graph (incl. article:*), Twitter Card, JSON-LD (Organization, WebSite, BlogPosting, BreadcrumbList, auto FAQPage, auto HowTo).
* Active: sitemap ping (Google + Bing), IndexNow POST (Bing + Yandex + Naver), cache warm.
* Tools -> SEO Boost admin page with detection table.

== Privacy ==

The plugin makes outbound HTTP requests only on post publish, to the four well-known search-discovery endpoints (Google, Bing sitemap ping; IndexNow; your own post URL for cache warm). No other data leaves your site. One option is stored (`bvseo_indexnow_key`, a 32-char random key per IndexNow protocol).
