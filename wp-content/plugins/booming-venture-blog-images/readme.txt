=== Booming Venture , Blog Images ===
Contributors: boomingventure
Tags: featured image, media library, hero, blog overview, thumbnails
Requires at least: 6.6
Tested up to: 6.8
Requires PHP: 8.0
Stable tag: 1.4.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Pick a Featured image from the Media Library for each blog post. It renders as a hero on the single post and as a card thumbnail in the blog overview. Mobile-first. Works on any active theme.

== Description ==

This is a tiny single-purpose plugin. It does three things:

1. Surfaces WordPress's standard "Featured image" panel on every blog post (forces it on if the active theme has not enabled `post-thumbnails` support). You pick the image manually from the Media Library, the same way you always have.
2. On the single post view, prepends a responsive hero figure to `the_content`, with a 16:9 aspect ratio, soft rounded corners, and an optional caption.
3. On the blog overview and any archive view, prepends a clickable card thumbnail to the excerpt. Works with both classic theme loops (via `get_the_excerpt`) and block themes (via `render_block` on `core/post-excerpt`).

= What it will NEVER do =

* Add settings, options, or admin pages other than a small editor tip when a post is saved without a Featured image.
* Modify the Media Library, attachment metadata, or any post other than to read `_thumbnail_id`.
* Change theme files, wp-config, permalinks, or post meta.
* Phone home or send any data externally.

= Image sizes registered =

* `bvimg_hero` 1600x900, hard crop , used for the single-post hero
* `bvimg_card` 640x360, hard crop , used for the archive card

== Installation ==

1. Upload the zip via Plugins -> Add New -> Upload Plugin.
2. Activate. No settings to configure.
3. Edit a blog post, open the Featured image panel in the sidebar, click "Set featured image", pick from the Media Library, Update.
4. View the post on the front-end , hero renders at the top.
5. Visit /blog/ , the card thumbnail renders above each excerpt.

== Frequently Asked Questions ==

= Does it work with the block editor and block themes? =

Yes. It uses the standard `_thumbnail_id` post meta which the block editor's Featured image panel writes to. On the front-end it hooks both classic (`the_content`, `get_the_excerpt`) and block (`render_block` on `core/post-excerpt`) rendering paths.

= Do I need to set a featured image for every post? =

No. Posts without one render exactly like before. The plugin shows an editor notice as a soft reminder when you publish without picking an image.

= Will it regenerate thumbnails for existing images? =

It registers two image sizes but does not regenerate existing uploads. To resize older images, install the official "Regenerate Thumbnails" plugin and run it once.

= Does it slow the site down? =

No. The hero image uses `loading=eager fetchpriority=high` so it counts as the LCP candidate. The card thumbnails are `loading=lazy decoding=async`.

== Changelog ==

= 1.4.0 =
* Width fix: featured image on single post views now matches the article body width (the `--wp--style--global--content-size` token, default 720px), centred. Overrides any `.alignwide` / `.alignfull` the theme applied. 16:9 aspect ratio retained.

= 1.3.0 =
* Size fix: constrain the theme's core/post-featured-image block on single post views to a tidy 16:9 box (rounded corners, soft shadow on tablet+, max-height 60vh on cover variants). Matches the compact size profile of our own injected hero from v1.0 to v1.1. Archive thumbnails are left untouched.

= 1.2.0 =
* FIX: stop the image rendering twice on themes that already render the Featured image via a `core/post-featured-image` block in the post template (or via `the_post_thumbnail()` in a classic template).
* Adds duplicate detection: tracks per-post whether the theme already rendered the Featured image (via the `render_block_core/post-featured-image` and `post_thumbnail_html` filters). If yes, the plugin's hero / card injection short-circuits. If no, the plugin renders normally.
* No configuration. Works automatically on any active theme.

= 1.1.0 =
* NEW: Tools -> Blog Images Pool admin page.
* You pick a pool of allowed images from the Media Library (wp.media multi-select).
* "Assign random images now" button: walks every published blog post; for any post WITHOUT a Featured image, picks one at random from the pool and sets it. Manual picks are never overwritten.
* "Re-randomise" button: re-rolls only the posts the plugin previously random-assigned. Manual picks are protected forever , the moment you change a Featured image yourself, the random flag is dropped automatically.
* Posts list table column: thumbnail + "(random)" / "(manual)" tag so you can scan which posts got auto-assigned vs manual.
* Manual override works the standard way: open the post editor, click the Featured image panel, pick a different image from the Media Library, Update. The plugin will never touch that post again on subsequent rerolls.

= 1.0.0 =
* Initial release.
* Single-post hero via the_content filter at priority 5.
* Archive card via get_the_excerpt + render_block on core/post-excerpt.
* Mobile-first stylesheet with skeleton shimmer.
* Editor notice when a post is published without a Featured image.

== Privacy ==

The plugin stores nothing. It reads only `_thumbnail_id` from existing WordPress post meta and renders the attachment image. No external requests, no analytics.
