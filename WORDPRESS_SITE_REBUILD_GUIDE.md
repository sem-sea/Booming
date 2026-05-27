# WordPress Site Rebuild Guide

> **2026 UPDATE , READ FIRST:** the authoritative current-policy
> brief is `CUSTOM_THEME_RESEARCH_2026.md` in this same branch. It
> supersedes everything in this guide where they conflict, in
> particular: WCAG 2.2 AA (not 2.1) is the WordPress accessibility
> target as of May 2026; WP 6.7+ is the minimum for full v3
> `theme.json`; WP 6.8 ships Pattern Overrides stable; the ADA +
> EAA legal deadlines apply.
>
> **Audience:** a future Claude Code session, a contractor, or a human
> engineer who needs to rebuild a marketing-agency-style WordPress
> site (block theme + content seed + custom blocks + SEO/GEO/AEO layer
> + optional CiteLeap-style AI content plugin) from zero.
>
> **Sister doc:** read `ONDERNEMERMARKETING_BUILD_PLAN.md` next for
> the project-specific blueprint that uses this guide.
>
> This guide is **theme-side**. For the CiteLeap plugin rebuild, read
> `wp-content/plugins/citeleap/REBUILD.md` on the main branch.

---

## 0. Mission in one paragraph

Build a WordPress 6.6+ block theme + content seed for a marketing
agency landing site. Editor must be able to swap copy / images / CTAs
without touching code. Pages must rank on Google AND earn citations on
ChatGPT / Perplexity / Google AI Overviews / Claude / Copilot via
GEO/AEO May 2026 Bible compliance. Mobile-first. WCAG 2.2 AA. Multi-
lingual ready. The same theme is portable to any marketing-agency
brand by swapping `theme.json` + content seed.

---

## 1. Day 0 , research before code

### 1.1 Read the WordPress theme handbook

- <https://developer.wordpress.org/themes/> (the whole handbook).
- Specifically: **Block themes**, the **Site Editor**, `theme.json`
  full schema, `templates/`, `parts/`, `patterns/`, `assets/`,
  `inc/`. WP 6.6+ supports `theme.json` schema v3, full pattern
  registration, fontFamilies + spacingScale + custom CSS via
  `theme.json` (so no `style.css` overrides needed for most things).
- Block patterns vs reusable blocks vs synced patterns , the
  distinction matters for editor usability.

### 1.2 Read the WP-guru section in CiteLeap/REBUILD.md

`wp-content/plugins/citeleap/REBUILD.md` section 1.6 is the complete
2026 best-practice cheatsheet. Every rule there applies to themes too:
ABSPATH guards, hook timing, escape on output, asset enqueueing,
deprecated APIs, accessibility, performance budget, the 10
commandments. **Re-read it before writing line 1.**

### 1.3 Read the GEO/AEO May 2026 Bible

Same sources as CiteLeap (Princeton GEO study, Indig Ski Ramp, Ahrefs
560k AI Overview corpus). The theme must render content shaped for
citation. Pattern: H1 with primary entity, 1+ question H2 followed by
20-60 word answer capsule, 3+ statistics with named source in first
30% of body, JSON-LD Article + FAQPage minimum.

### 1.4 Inspect the existing reference theme

`wp-content/themes/booming-venture/` on `main` branch is the
reference implementation. Read it in this order before rebuilding:

1. `style.css` , theme header.
2. `theme.json` , the design token source of truth.
3. `functions.php` , `BV_THEME_VERSION` constant, theme support flags,
   asset enqueueing, custom-block registration.
4. `templates/` , block templates (index, single, page, archive,
   front-page, 404).
5. `parts/` , header, footer, sidebar (block template parts).
6. `patterns/*.php` , every section pattern.
7. `inc/seo.php` , GEO/AEO infra: robots.txt, llms.txt, IndexNow,
   schema, "Last updated" banner.
8. `inc/geo.php` , the AI-crawler allowlist + extended schema.
9. `blocks/*/block.json` , custom blocks (funnel-calculator,
   roi-forecaster).
10. `import/*.xml` , the WXR content seed.

---

## 2. Block-theme architecture

```
wp-content/themes/<slug>/
├── style.css                    , theme header. Version: line MUST
│                                  match BV_THEME_VERSION constant.
├── theme.json                   , design tokens (colors, fonts,
│                                  spacing, contentSize). Read at
│                                  runtime by every block.
├── functions.php                , theme version constant, theme
│                                  support flags, custom block + part
│                                  registration, asset enqueueing,
│                                  inc/* requires.
├── front-page.html              , block template for the home page.
├── index.html                   , default block template.
├── singular.html                , single post + page fallback.
├── single.html                  , single post.
├── page.html                    , single page.
├── archive.html                 , category + tag listing.
├── 404.html                     , not found.
├── search.html                  , search results.
├── templates/                   , additional named templates
│   ├── page-no-title.html       ,   (e.g. landing-page template).
│   └── page-pricing.html        ,
├── parts/                       , block template parts.
│   ├── header.html              ,   site header (logo + nav + CTA).
│   ├── footer.html              ,   site footer (4-col + legal).
│   └── post-meta.html           ,
├── patterns/                    , registered patterns (PHP files).
│   ├── hero-call-to-action.php  ,   each pattern is a top-level
│   ├── pricing-table.php        ,   block + a header comment with
│   ├── testimonial-row.php      ,   slug + title + categories.
│   └── ...
├── blocks/                      , custom blocks (block.json + view.js).
│   ├── funnel-calculator/
│   │   ├── block.json           ,
│   │   ├── render.php           ,   server-side render.
│   │   └── view.js              ,   vanilla JS, no transpile.
│   └── roi-forecaster/
├── inc/                         , theme PHP modules.
│   ├── seo.php                  , JSON-LD schema generator,
│   │                              meta-description, OG, canonical.
│   ├── geo.php                  , AI-crawler robots.txt (3-tier
│   │                              split: allow OAI/Claude/Perplexity,
│   │                              disallow scrapers), llms.txt,
│   │                              IndexNow ping on publish,
│   │                              "Last updated:" banner injection.
│   ├── nav.php                  , menu walker / fallback nav.
│   ├── shortcodes.php           , legacy shortcodes if needed.
│   └── customizer.php           , customizer fallback (rarely needed
│                                  on a block theme).
├── assets/                      , images, fonts, CSS, JS.
│   ├── images/
│   ├── fonts/
│   └── style-overrides.css      , scoped admin/frontend tweaks
│                                  theme.json can't express.
├── languages/                   , .pot + .po + .mo files.
└── import/                      , WXR content seed XML for first
    └── <slug>-content.xml         install. Imported via Tools , Import.
```

---

## 3. The `theme.json` design-token rulebook

`theme.json` is the design system. **Everything possible MUST live
here**, not in `style.css`. Reasons: global styles UI in Site Editor
respects it; child themes can override per-token; AI editors (and
your future self) read it to know the design.

Mandatory top-level keys:

```json
{
  "$schema": "https://schemas.wp.org/trunk/theme.json",
  "version": 3,
  "settings": {
    "appearanceTools": true,
    "color": {
      "palette": [
        { "slug": "primary",   "color": "#1d6bd1", "name": "Primary" },
        { "slug": "accent",    "color": "#f17a3c", "name": "Accent"  },
        { "slug": "ink",       "color": "#0f172a", "name": "Ink"     },
        { "slug": "soft",      "color": "#f0f9ff", "name": "Soft"    },
        { "slug": "white",     "color": "#ffffff", "name": "White"   }
      ],
      "custom": true,
      "customGradient": true,
      "link": true
    },
    "typography": {
      "fontFamilies": [
        {
          "fontFamily": "'Inter', system-ui, sans-serif",
          "slug": "inter",
          "name": "Inter"
        }
      ],
      "fontSizes": [
        { "slug": "small",   "size": "0.875rem", "name": "Small"  },
        { "slug": "medium",  "size": "1rem",     "name": "Medium" },
        { "slug": "large",   "size": "1.25rem",  "name": "Large"  },
        { "slug": "x-large", "size": "2rem",     "name": "XL"     },
        { "slug": "xx-large","size": "3rem",     "name": "2XL"    }
      ],
      "fluid": true,
      "lineHeight": true,
      "letterSpacing": true,
      "textDecoration": true,
      "textTransform": true
    },
    "spacing": {
      "spacingScale": { "steps": 7 },
      "customSpacingSize": true,
      "padding": true,
      "margin": true,
      "blockGap": true
    },
    "layout": {
      "contentSize": "720px",
      "wideSize": "1200px"
    },
    "useRootPaddingAwareAlignments": true
  },
  "styles": {
    "color": { "background": "#ffffff", "text": "var(--wp--preset--color--ink)" },
    "typography": { "fontFamily": "var(--wp--preset--font-family--inter)", "fontSize": "1rem", "lineHeight": "1.65" },
    "spacing": { "padding": "0", "blockGap": "1rem" },
    "elements": {
      "h1": { "typography": { "fontSize": "clamp(2rem, 5vw, 3.25rem)", "fontWeight": "700", "lineHeight": "1.15" } },
      "h2": { "typography": { "fontSize": "clamp(1.5rem, 3vw, 2.25rem)", "fontWeight": "700", "lineHeight": "1.25" } },
      "button": {
        "color": { "background": "var(--wp--preset--color--primary)", "text": "#ffffff" },
        "border": { "radius": "0.5rem" },
        "spacing": { "padding": { "top": "0.75rem", "right": "1.25rem", "bottom": "0.75rem", "left": "1.25rem" } }
      },
      "link": { "color": { "text": "var(--wp--preset--color--primary)" } }
    }
  },
  "patterns": [],
  "templateParts": [
    { "name": "header", "title": "Header", "area": "header" },
    { "name": "footer", "title": "Footer", "area": "footer" }
  ]
}
```

Rules:
- Use **CSS custom properties** referencing presets, never raw hex in
  styles. WP auto-generates `--wp--preset--color--primary` etc.
- Use `clamp()` for fluid type so headlines scale on mobile without
  media queries.
- `useRootPaddingAwareAlignments: true` is the modern alignfull
  fix , it makes `alignfull` actually edge-to-edge without sideways
  scroll.

---

## 4. Block patterns , the secret weapon

Patterns are how the editor composes pages. Every reusable section
on the site becomes a pattern. The editor picks "Hero CTA", "Pricing
Table", "Testimonial Row" from the Site Editor pattern picker.

Pattern file format (PHP-based, simpler than block.json patterns):

```php
<?php
/**
 * Title: Hero CTA
 * Slug: ondernemermarketing/hero-cta
 * Categories: featured, header
 * Description: Big headline, lead paragraph, two CTA buttons, hero image.
 * Viewport Width: 1200
 */
?>
<!-- wp:cover { "url": "<?php echo esc_url( get_theme_file_uri( 'assets/images/hero.jpg' ) ); ?>", "minHeight": 520 } -->
<div class="wp-block-cover" style="min-height:520px;">
    <span aria-hidden="true" class="wp-block-cover__background has-background-dim-50 has-background-dim"></span>
    <div class="wp-block-cover__inner-container">
        <!-- wp:heading { "level": 1 } -->
        <h1>Je wilt klanten. Niet leren marketen.</h1>
        <!-- /wp:heading -->
        <!-- wp:paragraph -->
        <p>Wij regelen je marketing. Jij ondernemt.</p>
        <!-- /wp:paragraph -->
        <!-- wp:buttons -->
        <div class="wp-block-buttons">
            <!-- wp:button { "backgroundColor": "primary" } -->
            <div class="wp-block-button"><a class="wp-block-button__link has-primary-background-color has-background wp-element-button" href="#contact">Plan gratis intake</a></div>
            <!-- /wp:button -->
        </div>
        <!-- /wp:buttons -->
    </div>
</div>
<!-- /wp:cover -->
```

Patterns are auto-registered when placed in `patterns/`. The header
comment slug, title, description, and categories are parsed by WP.
Categories must exist or be registered via `register_block_pattern_category()`
in `functions.php`.

**Naming convention:** one pattern per visible "section" on the
designed page. The build plan's "Sitemap , per-page block list" maps
1:1 to patterns.

---

## 5. SEO + GEO + AEO infrastructure (`inc/seo.php` + `inc/geo.php`)

CiteLeap-the-plugin handles this in detail; for a theme that doesn't
ship the plugin, replicate the essentials inline. Reference:
`wp-content/themes/booming-venture/inc/seo.php` on `main`.

Must emit:

1. **JSON-LD schema** in `<head>`:
   - `Article` for posts (auto from post body).
   - `FAQPage` when an FAQ block is detected.
   - `HowTo` when a list of steps is detected.
   - `BreadcrumbList`.
   - `Organization` + `WebSite` site-wide.
   - `Person` for team / author pages.
   - `SoftwareApplication` for tools (calculators, forecasters).
2. **Meta description** , derive from post excerpt; honour
   Yoast/Rank Math/AIOSEO if active (don't double-write).
3. **Open Graph + Twitter Card** with og:image from the Featured
   image (or first image in content as fallback).
4. **Canonical URL** when not already emitted.
5. **`robots.txt`** with the **three-tier AI crawler split**
   (see `inc/geo.php`):
   - **Allow:** `OAI-SearchBot`, `Claude-SearchBot`, `PerplexityBot`,
     `Google-Extended`, `Applebot-Extended` (these earn you AI
     citations).
   - **Disallow:** `GPTBot`, `CCBot`, `anthropic-ai`,
     `ClaudeBot` if you don't want bulk training crawls.
   - **Allow** default everyone else.
6. **`llms.txt`** at site root , a Markdown summary of the site for
   AI crawlers. Auto-generate from page titles + descriptions.
7. **IndexNow ping** on publish , submit the URL to Bing / Yandex /
   Naver. Single HTTP POST.
8. **"Last updated:" banner** auto-injected at the top of every post
   when the modified date > published date by more than 7 days.

---

## 6. Custom blocks (when needed)

Build a custom block ONLY when a pattern can't express the
interactivity. Examples that justify a block:

- **Funnel calculator** , user inputs visitors + conversion rate,
  block computes ROI live.
- **Pricing toggle** , monthly / annual toggle that swaps values.
- **ROI forecaster** , multi-input form with chart output.

Convention: vanilla JS in `view.js`, no transpile step. Block.json
apiVersion 3. Server-side render via `render.php` so SSR-only AI
crawlers still see the result.

```json
{
    "$schema": "https://schemas.wp.org/trunk/block.json",
    "apiVersion": 3,
    "name": "ondernemermarketing/funnel-calculator",
    "title": "Funnel Calculator",
    "category": "widgets",
    "icon": "calculator",
    "editorScript": "file:./editor.js",
    "viewScript": "file:./view.js",
    "render": "file:./render.php",
    "supports": { "html": false, "align": [ "wide", "full" ] },
    "attributes": {
        "defaultVisitors": { "type": "number", "default": 1000 },
        "defaultConversion": { "type": "number", "default": 2 }
    }
}
```

---

## 7. Content seed XML (`import/<slug>-content.xml`)

Every page in the screenshots gets a `<item>` in a WXR file the
theme ships. On first install the operator imports it via Tools ,
Import , WordPress. This means:

- Editor never starts from blank.
- The screenshots match a real install out of the box.
- Brand voice is locked in from minute one.

Build the WXR with a Python script (`build-content.py`) that
generates `<content:encoded>` from text + block markup so you can
regenerate / re-tune the copy in batches. Validate XML syntax:

```bash
python3 -c "import xml.etree.ElementTree as ET; ET.parse('import/<slug>-content.xml'); print('WXR OK')"
```

---

## 8. Build commands (every project)

```bash
# Sanity-check PHP
php -l wp-content/themes/<slug>/inc/*.php wp-content/themes/<slug>/functions.php

# Sanity-check theme.json + block.json
python3 -c "import json; [json.load(open(f)) for f in ['wp-content/themes/<slug>/theme.json'] + [b for b in __import__('glob').glob('wp-content/themes/<slug>/blocks/*/block.json')]]; print('JSON OK')"

# Sanity-check the WXR
python3 -c "import xml.etree.ElementTree as ET; ET.parse('wp-content/themes/<slug>/import/<slug>-content.xml'); print('WXR OK')"

# Rebuild the installable zip (rolling latest + versioned copy)
VERSION=$(grep '^Version:' wp-content/themes/<slug>/style.css | awk '{print $2}')
rm -f wp-content/themes/<slug>.zip wp-content/themes/<slug>-${VERSION}.zip
(cd wp-content/themes && zip -rq <slug>.zip <slug> -x "*.DS_Store" "<slug>/.git/*" "<slug>/blocks/node_modules/*")
cp wp-content/themes/<slug>.zip wp-content/themes/<slug>-${VERSION}.zip

# Verify version inside the zip
unzip -p wp-content/themes/<slug>.zip <slug>/style.css | head -8 | grep Version
unzip -p wp-content/themes/<slug>.zip <slug>/functions.php | grep _THEME_VERSION
```

---

## 9. Performance budget

Targets per page render on the front-end:

- LCP ≤ 2.0s (mobile, 4G simulated)
- CLS ≤ 0.05
- INP ≤ 200ms
- Largest hero image ≤ 200KB (AVIF/WebP, srcset, eager-loaded with
  `fetchpriority="high"`)
- Theme CSS ≤ 30KB minified
- Theme JS ≤ 10KB minified (only the custom blocks' `view.js`)
- Web fonts: max 2 families, max 2 weights each, `font-display: swap`,
  preloaded

Run Lighthouse against the dev server before claiming any of these
are met.

---

## 10. Accessibility (WCAG 2.2 AA)

Same rules as the CiteLeap plugin admin (REBUILD.md section 1.5 + 1.6):

- All form controls have `<label for="">`.
- Color contrast ≥ 4.5:1 text, 3:1 UI components. Test with axe.
- Visible focus ring on every interactive element.
- `aria-current="page"` on active nav links.
- `aria-label` on icon-only buttons.
- `aria-hidden="true"` on decorative icons.
- `prefers-reduced-motion: reduce` kills any non-essential animations.
- `lang="nl"` (or whatever) on `<html>`.
- Skip-to-content link as the first focusable element.
- Forms have inline error messages with `aria-describedby` + role="alert".

---

## 11. Multilingual + hreflang

If the site needs translations:

- **Polylang** (lighter, free core) or **WPML** (paid, fuller-
  featured) , pick one early.
- Theme reads the active language from `pll_current_language()` /
  `apply_filters( 'wpml_current_language', null )`.
- hreflang tags emitted by the multilingual plugin; theme `inc/seo.php`
  must NOT double-write them. Detect plugin presence and stand down.
- All theme strings wrapped in `__()` / `esc_html__()` with the
  theme's text domain.

---

## 12. The 10 commandments for WP theme code (2026 edition)

1. **`theme.json` first.** If it can be expressed there, never put it
   in `style.css`.
2. **Block themes over classic.** No `header.php` / `footer.php` PHP
   in a 2026 theme , use block template parts.
3. **Patterns over hard-coded HTML.** Editor must be able to swap copy
   without touching code.
4. **Custom blocks only when patterns can't express interactivity.**
5. **ABSPATH guard, escape on output, `wp_remote_*` for HTTP.**
   (Same rules as plugins , see CiteLeap REBUILD.md section 1.6.)
6. **Bump `Version:` in `style.css` AND `<SLUG>_THEME_VERSION` in
   `functions.php`. Together. Every release.**
7. **Ship a versioned zip alongside the rolling-latest. Never reuse
   a version number.**
8. **No `eval`, no obfuscated PHP, no remote-loaded scripts.** Themes
   are static. Crawlers and security plugins flag the alternatives.
9. **Editor-role accessibility:** Editor must be able to edit every
   page in Site Editor without `manage_options`. If a block requires
   admin rights, redesign.
10. **GEO/AEO compliance on every page.** Question H2, answer capsule,
    3+ named stats in first 30%, JSON-LD Article + FAQPage, "Last
    updated" banner.

---

## 13. Sprint plan (any project from zero)

### Sprint 1 , skeleton (2 days)

- `style.css` + `functions.php` + `theme.json` (full token set).
- `front-page.html`, `index.html`, `singular.html`, `404.html` block
  templates.
- `parts/header.html`, `parts/footer.html`.
- `inc/seo.php` + `inc/geo.php`.
- One placeholder pattern per planned section so the editor doesn't
  see "no patterns".

### Sprint 2 , patterns (3-4 days)

- One pattern PHP file per section identified in the design.
- All copy in patterns, all images in `assets/images/`.
- All CTAs link to anchors / pages that will exist.
- Lint + JSON validate.

### Sprint 3 , content seed (2 days)

- `build-content.py` generates the WXR.
- Every page in the sitemap has a WP page with the right slug, title,
  and block markup composing the patterns above.
- Import on a clean WP install , every screenshot matches.

### Sprint 4 , custom blocks (1-3 days, if needed)

- Build `blocks/<name>/block.json` + `view.js` + `render.php` per
  custom block.
- Register in `functions.php`.
- Add usage examples to the relevant pattern.

### Sprint 5 , polish & launch prep (2 days)

- Lighthouse pass: LCP ≤ 2.0s on mobile.
- axe-core pass: zero serious/critical issues.
- Mobile responsiveness review on real devices (iOS Safari + Android
  Chrome).
- Cross-theme check , does the theme survive deactivating + reactivating?
- `import/<slug>-content.xml` validates as XML.
- Versioned zip built. Tag the release.

---

## 14. Common pitfalls

1. **Edge-to-edge hero pushing a horizontal scrollbar.** Fix:
   `useRootPaddingAwareAlignments: true` in theme.json + no
   `overflow-x: hidden` on `body` (let WP handle it).
2. **Featured image rendering twice.** Theme template uses
   `core/post-featured-image` AND the plugin (CiteLeap, Blog Images)
   injects a hero. Pick one. CiteLeap detects via
   `render_block_core/post-featured-image` hook to skip.
3. **Mobile menu hides behind sticky elements.** Fix:
   `.wp-block-navigation__responsive-container { z-index: 99999;
   position: fixed; inset: 0; }` + lock body scroll via
   `body:has(.is-menu-open) { overflow: hidden; }`.
4. **Service-card columns uneven height.** Fix:
   `.wp-block-columns:has(.card) > .wp-block-column { display: flex;
   flex-direction: column; }` + `.card { height: 100%; }` +
   `.card :last-child { margin-top: auto; }`.
5. **`<form>` inside a pattern submits to the wrong URL.** Block-
   theme forms must POST to a real endpoint (Brevo, MailChimp,
   admin-post.php). Inline shortcode plugins (Brevo, Forminator)
   are fine here; if rolling your own, register via `admin_post_*`
   hooks.
6. **Custom block `view.js` doesn't run on the front-end.** Fix:
   ensure `viewScript` in `block.json` and the block is actually
   placed on a page (not just registered). Use the Site Editor to
   place it.
7. **`theme.json` change doesn't show.** WP caches the compiled CSS.
   Fix: bump the theme version (the cache key includes it) OR run
   `wp transient delete --all` via WP-CLI.
8. **WP.org theme review rejects.** Don't ship third-party scripts
   without consent. Don't bundle non-GPL fonts. Don't include
   tracking pixels in the theme.

---

## 15. The "Day 1 acceptance test" (every project)

After every full theme rebuild, run this on a clean WP 6.6+ install:

1. Theme activates without errors.
2. Tools , Import , WordPress , upload the seed XML.
3. Front page loads at `/`. LCP ≤ 2.0s in Lighthouse mobile.
4. Every pattern from the design renders.
5. Mobile menu opens, closes, body scroll locks.
6. Every CTA button links to a real URL (no `#`).
7. JSON-LD validates on schema.org/validator.
8. Open Graph image loads in the FB sharing debugger.
9. Lighthouse: Performance 90+, Accessibility 95+, SEO 95+, Best
   Practices 95+.
10. axe-core: zero serious / critical violations.

If any of these fail, the rebuild is not done.

---

**End of guide.** This is the abstract pattern. The next document,
`ONDERNEMERMARKETING_BUILD_PLAN.md`, applies this pattern to the
specific OndernemerMarketing.nl brief.
