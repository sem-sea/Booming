# INSTALL — Booming Venture theme

> 30-minute setup from a fresh WordPress install to a fully working
> Booming Venture site with content seeded, forms wired up, and chat live.

## 0. Prerequisites

- WordPress **6.5+**, PHP **8.0+**
- Node **18+** (only required to build the custom blocks once)
- WP-CLI (optional, makes the whole flow scriptable)
- A Brevo account + API key (free tier is fine)
- A Google Tag Manager container (optional — default `GTM-K9532WK5` ships)

---

## 1. Install the theme

```bash
# from the repo root
rsync -av wp-content/themes/booming-venture/ \
        /path/to/your/wordpress/wp-content/themes/booming-venture/
```

Activate it:

- **Admin UI:** `Appearance → Themes → Activate "Booming Venture"`
- **WP-CLI:** `wp theme activate booming-venture`

---

## 2. Build the custom Gutenberg blocks

The Funnel Calculator and ROI Forecaster are React widgets compiled
through `@wordpress/scripts`. Run the build once and they appear in the
inserter under **Booming Venture**.

```bash
cd wp-content/themes/booming-venture/blocks
npm install
npm run build
```

This produces:

- `blocks/funnel-calculator/build/` → `view.js`, `editor.js`, `style.css`
- `blocks/roi-forecaster/build/`    → same

`functions.php` auto-detects them and registers each block.

> **Tip:** Before the first build, see `blocks/README.md` — the original
> Lovable components were copied in but reference `@/components/ui/*`
> (shadcn) and `react-router-dom`. Swap those imports for plain
> HTML/CSS equivalents or vendor a minimal shadcn subset.

---

## 3. Install required plugins

```bash
# Free, official
wp plugin install fluentform --activate
# Brevo connector for Fluent Forms (search "Brevo" inside Fluent Forms → Integrations)

# Optional but recommended for a "10/10" site
wp plugin install seo-by-rank-math --activate   # SEO meta + sitemap merge
wp plugin install complianz-gdpr --activate     # EU cookie consent
wp plugin install wp-super-cache --activate     # page caching
wp plugin install enable-media-replace --activate
wp plugin install wp-rocket-lazyload --activate # lazy-load <iframe>/video
```

---

## 4. Import content

Tools → Import → **WordPress** → install the importer plugin if prompted
→ choose the file at:

```
wp-content/themes/booming-venture/import/booming-venture-content.xml
```

Or via WP-CLI:

```bash
wp plugin install wordpress-importer --activate
wp import wp-content/themes/booming-venture/import/booming-venture-content.xml --authors=create
```

This seeds:

- 13 core pages (Home, About, Services, Blog, UNIFY, tools, landing pages, legal)
- 4 services (CPT)
- 40 blog posts with categories
- 23 blog categories

After import:

```bash
# Set static front page + posts page
wp option update show_on_front page
wp option update page_on_front  "$(wp post list --post_type=page --name=home --field=ID)"
wp option update page_for_posts "$(wp post list --post_type=page --name=blog --field=ID)"

# Pretty permalinks
wp rewrite structure '/%postname%/' --hard
```

---

## 5. Configure Fluent Forms → Brevo

Inside `Fluent Forms`:

1. **Create six forms** (or duplicate one and rename). Use these IDs in the
   patterns — they're referenced as `[fluentform id="..."]`:

   | Slug                 | Purpose                              | Brevo list |
   | -------------------- | ------------------------------------ | ---------- |
   | `contact`            | Main contact form (Contact section)  | 4          |
   | `newsletter`         | Big newsletter CTA on /blog          | 3          |
   | `newsletter-inline`  | Footer mini newsletter               | 3          |
   | `growth-guide`       | Hero + dedicated lead-magnet page    | 2          |
   | `quickscan`          | Boardroom Quickscan landing page     | 4 + tag    |
   | `head-of-growth`     | Head of Growth landing page          | 4 + tag    |

2. **Integrations → Brevo →** paste your API key. Map each form to its list.
3. **Settings → Booming Venture → Fluent Forms.** Map each slug
   (`contact`, `newsletter`, `newsletter-inline`, `growth-guide`,
   `quickscan`, `head-of-growth`) to the numeric Fluent Forms ID. The
   patterns ship with semantic slugs and the theme rewrites them at
   render time. Until you map them, logged-in editors see a red warning
   block; logged-out visitors see nothing.
3. (Optional) **Spam:** enable Akismet + Cloudflare Turnstile in Fluent Forms
   → settings, the patterns already include `[fluentform]` shortcodes.

> If you'd rather not use Fluent Forms, the theme also exposes a tiny
> proxy at `POST /wp-json/bv/v1/brevo` (see `inc/integrations.php`) — set
> `BV_BREVO_API_KEY` in `wp-config.php`.

---

## 6. Set tracking IDs

Either through wp-admin (recommended) or via WP-CLI:

```bash
wp option update bv_gtm_id        'GTM-XXXXXXXX'
wp option update bv_dataspeak_id  '6863892dbcf4fea86a49e9f8'
```

The defaults are already wired to the Lovable site's IDs — change them
once your new GA4/GTM property is live.

---

## 7. Add brand assets

Drop these files into `wp-content/themes/booming-venture/assets/`:

```
images/logo.png            # navbar logo
images/hero-team.jpg       # hero card photo
images/about-leader.jpg    # about section photo
images/og-default.jpg      # 1200×630 social share
fonts/Inter-Regular.woff2
fonts/Inter-Medium.woff2
fonts/Inter-SemiBold.woff2
fonts/Inter-Bold.woff2
fonts/SpaceGrotesk-Variable.woff2
```

You can pull Inter + Space Grotesk from <https://gwfh.mranftl.com/fonts>.

---

## 8. Verify

Run through this sanity check:

- [ ] Home page renders hero, services, growth-guide CTA, about,
      testimonials, contact — in that order.
- [ ] `/funnel-calculator` and `/roi-forecaster` show the calculator
      block with the data-attribute hydration mount point.
- [ ] Submit a test contact form → contact lands in Brevo list **4**.
- [ ] View source on any page: search for `<script type="application/ld+json">`
      — should see Organization + WebSite (+ BlogPosting on posts).
- [ ] Lighthouse on home: Performance ≥ 90, Best Practices ≥ 95,
      Accessibility ≥ 95, SEO = 100.
- [ ] `Appearance → Editor → Patterns` lists patterns under
      **Booming · Sections / · Home / · CTA**.

---

## 9. Going live

```bash
# Use a real Brevo API key (per environment) via wp-config
define( 'BV_BREVO_API_KEY', 'xkeysib-XXXX' );

# Optional: skip the chatbot on staging
add_filter( 'option_bv_dataspeak_id', '__return_empty_string' );
```

If you migrate from the old Lovable site, the WP URLs match the Lovable
routes exactly, so no 301 map is required. If you change a permalink,
add it to a redirects file (Rank Math has a Redirections module).

---

## 🧯 Troubleshooting

| Symptom | Fix |
| ------- | ----|
| Custom blocks missing from inserter | Did you run `npm run build` in `blocks/`? Check that `block.json` + `build/` exist. |
| `[fluentform id="contact"]` shows literal text | Install + activate Fluent Forms. |
| DataSpeak chat doesn't appear | Check `bv_dataspeak_id` option and that the page isn't blocked by an ad-blocker. |
| Patterns not appearing | Bump theme version in `style.css` and visit `Appearance → Editor` once. |
| Front page shows blog instead of Home | `Settings → Reading → Front page displays → A static page → Home`. |
