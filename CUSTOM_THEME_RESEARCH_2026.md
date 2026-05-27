# Custom WordPress Theme , 2026 Research & Policy Brief

> **Audience:** the future Claude Code session (or human contractor)
> who will build the OndernemerMarketing.nl custom block theme.
>
> **Status:** authoritative as of **May 2026**. Every rule below was
> verified against the live WordPress.org Make-team posts, the
> Theme Handbook, and the WCAG 2.2 / ADA deadlines that apply to
> Dutch & EU sites this year. Sources at the bottom.
>
> **Read after:** `WORDPRESS_SITE_REBUILD_GUIDE.md` (the abstract
> rebuild pattern) and `ONDERNEMERMARKETING_BUILD_PLAN.md` (this
> project's spec). This document supersedes both wherever it
> conflicts with them.

---

## 0. TL;DR , the 2026 rules in one screen

1. **Custom theme = block theme.** Classic PHP-template themes are
   not the 2026 default. As of WP 6.7, **60%+ of new themes on
   wordpress.org are block themes** ([NeuronThemes, 2026][r-fse]).
2. **`theme.json` v3** is the single source of truth for design
   tokens. Works on WP 6.6+, recommended WP 6.7+ ([WP Block Editor
   Handbook][r-themejson]).
3. **WordPress 6.8 (Apr 2026)** ships **Pattern Overrides as stable**
   + Connectors API alpha + expanded post-type meta for
   `wp_template` registration ([NeuronThemes][r-fse]).
4. **Accessibility target is WCAG 2.2 AA**, not 2.1 AA. WordPress
   updated the standard in May 2026 ([Make WP Accessible, 6 May
   2026][r-a11y]). **Deadline for accessibility-ready themes to
   re-comply: 30 June 2026**.
5. **ADA + EAA legal compliance:** government sites (incl. NL public
   sector) must hit **WCAG 2.1 AA by 24 April 2026** ([WPVIP / DOJ
   ruling][r-ada]). Private commercial sites operating in the EU
   should aim for the same.
6. **Core Web Vitals 2026:** LCP ≤ 2.5s, INP ≤ 200ms, CLS ≤ 0.1.
   INP replaced FID in March 2024 ([Core Web Vitals
   guide][r-cwv]). WordPress's number-one problem is LCP driven by
   slow TTFB , page caching is mandatory.
7. **Prefix everything with ≥ 4 letters.** Functions, hooks,
   options, CSS classes, JS variables. `ondm_` for
   OndernemerMarketing ([WP Theme Handbook , Required][r-required]).
8. **No popups on activation. Admin notices must be dismissible**
   ([Required Rules][r-required]).
9. **Sanitise on input, validate for logic, escape on output.**
   Every echo through the correct `esc_*` function ([WP Security
   APIs][r-escape]).
10. **Block stylesheets via `wp_enqueue_block_style()` on `init`**,
    not via global enqueue. Allows per-block tree-shaking ([Block
    Stylesheets handbook][r-blockstyles]).

---

## 1. What "custom theme" means in 2026

A **block theme** built from scratch for one brand, not a
purchased generic theme reskinned. Specifically:

- 100% Site Editor compatible. Templates + parts + patterns,
  all editable via Appearance , Editor.
- `theme.json` v3 carries every design token. **No `style.css`
  custom-property dumps.** Only the theme header lives in
  `style.css`.
- Built on the **standard `wp_template` + `wp_template_part`**
  post types, not on `pages/` static templates.
- All content (page layouts, navigation, footer, headers) editable
  by the operator without touching code.
- Multi-language ready: text wrapped in `__()` family, hreflang
  emitted by Polylang or WPML when active.
- Performance budget hits the Core Web Vitals "good" thresholds
  out of the box on a clean install.
- WCAG 2.2 AA on the front-end and the Site Editor.

This is what OndernemerMarketing.nl needs. **Do not start from a
purchased theme.** Build the block theme `ondernemer-marketing/`
from scratch.

---

## 2. Mandatory theme files (block theme)

Per the [Required Theme Files][r-files] handbook page, a 2026
block theme MUST ship:

| File | Required? | Purpose |
|---|---|---|
| `style.css` | **Required** | Theme header (Name, Author, Version, Requires at least, Tested up to, Requires PHP, Text Domain, License URI). Only metadata , no design CSS here. |
| `theme.json` | **Required (block themes)** | Design tokens. v3 schema. Single source of truth for color, type, spacing, layout. |
| `templates/index.html` | **Required** | Default block template. Even if minimal. |
| `screenshot.png` | **Required** | 1200x900px PNG, displayed in Appearance , Themes. |
| `parts/header.html` | Optional but conventional | Site header. |
| `parts/footer.html` | Optional but conventional | Site footer. |
| `functions.php` | Optional , conventionally present | Theme version constant, theme support flags, `wp_enqueue_block_style()` calls, custom block + pattern category registration. |
| `templates/front-page.html` | Optional | If your home page differs from `index.html`. |
| `templates/single.html` | Optional | Single post layout. |
| `templates/page.html` | Optional | Single page layout. |
| `templates/archive.html` | Optional | Category + tag archive. |
| `templates/404.html` | Optional | Not found. |
| `templates/search.html` | Optional | Search results. |
| `patterns/*.php` | Optional | Each PHP file with the right header comment auto-registers as a pattern. |
| `inc/*.php` | Optional , conventional | Custom theme code (SEO emitters, nav walkers, custom block registration). |
| `assets/` | Optional | Fonts (woff2), images. CSS only if a block style requires it , otherwise put it in `theme.json`. |
| `languages/<slug>.pot` | Required if translation-ready | i18n template. |

---

## 3. The `style.css` header , exact required keys

Per the [Theme Review Required handbook][r-required], the header
**must** include these lines, in this order, with the right
format:

```css
/*
Theme Name:        OndernemerMarketing
Theme URI:         https://ondernemermarketing.nl
Author:            OndernemerMarketing
Author URI:        https://ondernemermarketing.nl
Description:       Block theme voor OndernemerMarketing.nl , Nederlandse marketingoplossingen voor ondernemers. WCAG 2.2 AA + GEO/AEO compliant. Multilingual ready. Built mobile-first.
Version:           1.0.0
Requires at least: 6.7
Tested up to:      6.8
Requires PHP:      8.1
License:           GPL-2.0-or-later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:       ondernemer-marketing
Tags:              full-site-editing, block-styles, block-patterns, accessibility-ready, translation-ready, blog, business, one-column, two-columns, three-columns, custom-colors, custom-logo, custom-menu, featured-images, footer-widgets, threaded-comments
*/
```

Rules from the handbook:

- **Version** in `X.X` or `X.X.X` format, never with a `v` prefix.
- **Requires at least** must be a WP version that supports block
  themes (6.0+). Recommend 6.7 to get v3 theme.json fully.
- **Requires PHP** 8.1+ in 2026 (PHP 8.0 reaches end-of-support
  November 2026; ship for the next year, not last year).
- **Text Domain** must match the theme slug exactly.
- **Tags** , max 12 indexed on wordpress.org. Pick the most
  valuable for search. **`accessibility-ready` requires passing the
  updated June 2026 review** (Section 7 below).
- License MUST be GPL-2.0-or-later (or another GPL-compatible).
  Closed-source themes cannot be on wordpress.org but may still
  ship to a single client.

---

## 4. `theme.json` v3 , canonical structure

Per the [Block Editor Handbook v3 reference][r-themejson], v3
introduces:

- Per-font-size **fluid typography**: each entry in
  `typography.fontSizes` can carry its own `fluid: { min, max }`,
  not just the global `typography.fluid: true`.
- Improved `settings.blocks` , disable / enable specific features
  per block (e.g. disable global colour palette for `core/heading`,
  enable a normally-blocked option for `core/group`).
- `appearanceTools: true` shortcut , one flag enables border,
  link, blockGap, spacing, textColor, backgroundColor controls
  for editors.
- `customTemplates` , declare named templates available in the
  page editor (e.g. "Landing page no-title").
- `templateParts` , declares which parts exist + their `area`
  (header / footer / sidebar / uncategorized).

Minimum-viable production v3 `theme.json`:

```json
{
    "$schema": "https://schemas.wp.org/trunk/theme.json",
    "version": 3,
    "settings": {
        "appearanceTools": true,
        "useRootPaddingAwareAlignments": true,
        "color": {
            "palette": [
                { "slug": "primary", "color": "#1d6bd1", "name": "Primary blue" },
                { "slug": "accent",  "color": "#f17a3c", "name": "Accent orange" },
                { "slug": "ink",     "color": "#0f172a", "name": "Ink" },
                { "slug": "body",    "color": "#334155", "name": "Body" },
                { "slug": "muted",   "color": "#64748b", "name": "Muted" },
                { "slug": "soft",    "color": "#f0f9ff", "name": "Soft" },
                { "slug": "white",   "color": "#ffffff", "name": "White" },
                { "slug": "border",  "color": "#e2e8f0", "name": "Border" }
            ],
            "custom": true,
            "customGradient": true,
            "link": true
        },
        "typography": {
            "fontFamilies": [
                {
                    "fontFamily": "'Inter', system-ui, -apple-system, sans-serif",
                    "slug": "inter",
                    "name": "Inter",
                    "fontFace": [
                        { "fontFamily": "Inter", "fontWeight": "400", "src": [ "file:./assets/fonts/inter-400.woff2" ] },
                        { "fontFamily": "Inter", "fontWeight": "600", "src": [ "file:./assets/fonts/inter-600.woff2" ] },
                        { "fontFamily": "Inter", "fontWeight": "700", "src": [ "file:./assets/fonts/inter-700.woff2" ] }
                    ]
                }
            ],
            "fontSizes": [
                { "slug": "small",   "size": "0.875rem", "name": "Small" },
                { "slug": "medium",  "size": "1rem",     "name": "Medium" },
                { "slug": "large",   "size": "1.25rem",  "name": "Large",
                  "fluid": { "min": "1.125rem", "max": "1.375rem" } },
                { "slug": "x-large", "size": "2rem",     "name": "XL",
                  "fluid": { "min": "1.625rem", "max": "2.25rem" } },
                { "slug": "xx-large","size": "3rem",     "name": "2XL",
                  "fluid": { "min": "2.25rem", "max": "3.5rem" } }
            ],
            "fluid": true,
            "lineHeight": true,
            "letterSpacing": true
        },
        "spacing": {
            "spacingScale": { "steps": 7 },
            "customSpacingSize": true,
            "padding": true,
            "blockGap": true
        },
        "layout": {
            "contentSize": "720px",
            "wideSize": "1200px"
        }
    },
    "styles": {
        "color": {
            "background": "var:preset|color|white",
            "text": "var:preset|color|ink"
        },
        "typography": {
            "fontFamily": "var:preset|font-family|inter",
            "fontSize": "1rem",
            "lineHeight": "1.65"
        },
        "spacing": { "blockGap": "1rem", "padding": { "left": "1rem", "right": "1rem" } },
        "elements": {
            "h1": { "typography": { "fontSize": "clamp(2rem, 5vw, 3.25rem)", "fontWeight": "700", "lineHeight": "1.15" } },
            "h2": { "typography": { "fontSize": "clamp(1.5rem, 3vw, 2.25rem)", "fontWeight": "700", "lineHeight": "1.25" } },
            "h3": { "typography": { "fontSize": "clamp(1.25rem, 2.2vw, 1.5rem)", "fontWeight": "600", "lineHeight": "1.35" } },
            "button": {
                "color": { "background": "var:preset|color|primary", "text": "#ffffff" },
                "border": { "radius": "0.5rem" },
                "spacing": { "padding": { "top": "0.75rem", "right": "1.25rem", "bottom": "0.75rem", "left": "1.25rem" } },
                ":hover": { "color": { "background": "#155ab0" } },
                ":focus-visible": { "outline": { "color": "var:preset|color|accent", "width": "2px", "offset": "2px" } }
            },
            "link": {
                "color": { "text": "var:preset|color|primary" },
                ":hover": { "color": { "text": "var:preset|color|accent" } }
            }
        }
    },
    "templateParts": [
        { "name": "header", "title": "Header", "area": "header" },
        { "name": "footer", "title": "Footer", "area": "footer" }
    ],
    "customTemplates": [
        { "name": "page-no-title", "title": "Landing page (no title)", "postTypes": [ "page" ] }
    ]
}
```

**Why `useRootPaddingAwareAlignments: true`:** without it,
`alignfull` blocks push a horizontal scrollbar when the
`<body>` already has padding. This single flag is the
canonical fix.

**Why `var:preset|color|primary` instead of `var(--wp--preset--color--primary)`:**
in `theme.json` `styles` objects, WordPress accepts the
shorthand `var:preset|<category>|<slug>` and compiles it to the
custom property at output time. Both work; the shorthand is the
2026 idiom.

---

## 5. `functions.php` , the minimum

[Block themes auto-enable a lot of theme support][r-blockstyles]:
`post-thumbnails`, `editor-styles`, `responsive-embeds`,
`automatic-feed-links`, `html5` for forms/galleries/captions/etc.
**Do not re-add these via `add_theme_support()`** , block themes
already have them. Adding them again is harmless but signals
"copy-pasted from a classic theme".

Minimum `functions.php`:

```php
<?php
/**
 * OndernemerMarketing theme bootstrap.
 *
 * @package OndernemerMarketing
 */

defined( 'ABSPATH' ) || exit;

const ONDM_THEME_VERSION = '1.0.0';

if ( ! defined( 'ONDM_THEME_DIR' ) ) {
    define( 'ONDM_THEME_DIR', get_stylesheet_directory() );
}
if ( ! defined( 'ONDM_THEME_URI' ) ) {
    define( 'ONDM_THEME_URI', get_stylesheet_directory_uri() );
}

/* ---------------------------------------------------------------------
 * after_setup_theme , editor styles + theme support that block themes
 * do NOT auto-enable.
 * --------------------------------------------------------------------- */
add_action( 'after_setup_theme', function () {
    add_editor_style( 'assets/editor-styles.css' );
    /* Translation-ready */
    load_theme_textdomain( 'ondernemer-marketing', ONDM_THEME_DIR . '/languages' );
    /* Title tag (block themes have it but explicit is fine) */
    add_theme_support( 'title-tag' );
} );

/* ---------------------------------------------------------------------
 * wp_enqueue_scripts , front-end stylesheet. The theme.json gives us
 * 95% of the CSS; this is for the small set of overrides that cannot
 * be expressed in tokens (focus-visible, skip-link, prefers-reduced-
 * motion etc.).
 * --------------------------------------------------------------------- */
add_action( 'wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'ondm-front',
        ONDM_THEME_URI . '/assets/front.css',
        [],
        ONDM_THEME_VERSION
    );
} );

/* ---------------------------------------------------------------------
 * init , per-block stylesheets via wp_enqueue_block_style().
 * This lets us ship per-block CSS only when that block is actually
 * rendered on the page (tree-shaken). Required since WP 5.9 but the
 * 2026 best-practice idiom.
 * --------------------------------------------------------------------- */
add_action( 'init', function () {
    if ( ! function_exists( 'wp_enqueue_block_style' ) ) return;
    foreach ( [ 'core/cover', 'core/button', 'core/navigation', 'core/columns' ] as $block ) {
        $slug = str_replace( '/', '-', $block );
        $path = ONDM_THEME_DIR . "/assets/blocks/{$slug}.css";
        if ( ! file_exists( $path ) ) continue;
        wp_enqueue_block_style( $block, [
            'handle' => "ondm-{$slug}",
            'src'    => ONDM_THEME_URI . "/assets/blocks/{$slug}.css",
            'path'   => $path,
            'ver'    => (string) filemtime( $path ),
        ] );
    }
} );

/* ---------------------------------------------------------------------
 * init , register block-pattern categories. Patterns themselves are
 * auto-discovered from /patterns/*.php.
 * --------------------------------------------------------------------- */
add_action( 'init', function () {
    if ( ! function_exists( 'register_block_pattern_category' ) ) return;
    foreach ( [
        'ondm-hero'         => __( 'OndernemerMarketing , Hero', 'ondernemer-marketing' ),
        'ondm-packages'     => __( 'OndernemerMarketing , Pakketten', 'ondernemer-marketing' ),
        'ondm-testimonials' => __( 'OndernemerMarketing , Testimonials', 'ondernemer-marketing' ),
        'ondm-cta'          => __( 'OndernemerMarketing , CTA', 'ondernemer-marketing' ),
        'ondm-team'         => __( 'OndernemerMarketing , Team', 'ondernemer-marketing' ),
    ] as $slug => $label ) {
        register_block_pattern_category( $slug, [ 'label' => $label ] );
    }
} );

/* SEO / GEO / AEO emitters live in inc/seo.php + inc/geo.php. */
require_once ONDM_THEME_DIR . '/inc/seo.php';
require_once ONDM_THEME_DIR . '/inc/geo.php';
```

Rules:

- `defined( 'ABSPATH' ) || exit;` at top of every PHP file.
- Prefix everything `ondm_` / `ONDM_`. 4 letters minimum per the
  Required handbook.
- `get_stylesheet_directory()` (not `get_template_directory()`)
  because a future child theme should override paths correctly.
- Asset version = `filemtime()` for trivial cache-busting.
- All translatable strings via `__()` / `esc_html__()` with the
  text domain `ondernemer-marketing`.
- `wp_enqueue_block_style()` on `init`, not on
  `wp_enqueue_scripts`. This is the 2026 idiom for per-block CSS.

---

## 6. Security , the non-negotiables for a 2026 theme

Per [WP Theme Handbook , Security][r-security] and
[Common APIs , Escaping][r-escape]:

### 6.1 Output escaping , pick the right function

```php
// Plain text inside HTML body
echo esc_html( $text );

// Inside an HTML attribute (id, class, data-*, alt)
echo esc_attr( $value );

// href, src, action , URL contexts
echo esc_url( $url );

// Inside a <script> string literal (rare in themes)
echo esc_js( $for_inline_js );

// A post body with the usual rich-text tags allowed
echo wp_kses_post( $rich_html );

// A bespoke whitelist (e.g. only <a> + <strong>)
echo wp_kses( $html, [ 'a' => [ 'href' => [], 'rel' => [] ], 'strong' => [] ] );

// Translatable string + escape in one go
echo esc_html__( 'Plan gratis intake', 'ondernemer-marketing' );
echo esc_attr__( 'Verzenden',           'ondernemer-marketing' );
esc_html_e( 'Contact opnemen',          'ondernemer-marketing' );
```

**Rule: every dynamic value reaches the browser through one of
these. No raw `echo $variable`. No `print_r` in production.**

### 6.2 Sanitisation on input

```php
$name   = sanitize_text_field( wp_unslash( $_POST['name']  ?? '' ) );
$email  = sanitize_email(      wp_unslash( $_POST['email'] ?? '' ) );
$slug   = sanitize_key(                     $_POST['slug']  ?? ''   );
$int    = absint(                           $_POST['count'] ?? 0    );
$url    = esc_url_raw(         wp_unslash( $_POST['url']   ?? '' ) );
$arr    = array_map( 'sanitize_text_field', (array) ( $_POST['tags'] ?? [] ) );
```

Always `wp_unslash()` first , WP magic-quotes adds slashes that
break sanitisation otherwise.

### 6.3 Forms , nonce + capability + PRG

```php
// Form template
?>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
    <?php wp_nonce_field( 'ondm_contact', 'ondm_nonce' ); ?>
    <input type="hidden" name="action" value="ondm_contact">
    <input type="email" name="email" required>
    <button type="submit"><?php esc_html_e( 'Verzenden', 'ondernemer-marketing' ); ?></button>
</form>
<?php

// Handler
add_action( 'admin_post_ondm_contact',        'ondm_handle_contact' );
add_action( 'admin_post_nopriv_ondm_contact', 'ondm_handle_contact' );
function ondm_handle_contact(): void {
    check_admin_referer( 'ondm_contact', 'ondm_nonce' );
    $email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
    if ( ! is_email( $email ) ) {
        wp_safe_redirect( add_query_arg( 'ondm_err', 'bad-email', wp_get_referer() ?: home_url() ) );
        exit;
    }
    // Process , send to Brevo, save, etc.
    wp_safe_redirect( add_query_arg( 'ondm_ok', '1', wp_get_referer() ?: home_url() ) );
    exit;
}
```

**`admin_post_nopriv_*` is required for forms that visitors (not
logged-in users) submit.** Without it the form 404s on submit.

### 6.4 Banned patterns

Do NOT:

- Use `$_REQUEST`. Always pick `$_POST` or `$_GET` explicitly.
- Run `eval()`. Theme review rejects.
- Load remote JS / CSS from a CDN you don't control. Bundle locally.
- Hard-code secrets (API keys, tokens, passwords). Use
  `wp_config.php` constants or env vars.
- Use `extract( $_POST )` , creates uncontrolled variable scope.
- `curl_*` , always `wp_remote_*`.
- Echo raw user input without escaping , every time, no exceptions.

---

## 7. Accessibility , WCAG 2.2 AA is the 2026 target

Per [Make WP Accessible, 6 May 2026][r-a11y], the WordPress
project officially **upgraded the accessibility standard from
WCAG 2.1 AA to WCAG 2.2 AA in May 2026**. Theme authors carrying
the `accessibility-ready` tag have until **30 June 2026** to
re-comply.

Legally, [the DOJ rule effective 24 April 2026][r-ada] requires
state/local government sites (US, 50k+ population) to hit WCAG
2.1 AA. The EAA (European Accessibility Act, June 2025 in force)
applies similar pressure to private commercial sites in NL + EU.

### 7.1 WCAG 2.2 AA , the 9 new success criteria over 2.1

WCAG 2.2 added 9 new criteria. The ones that matter for a theme:

| New SC | What it means for the theme |
|---|---|
| **2.4.11 Focus Not Obscured (Minimum)** | When a control receives keyboard focus, sticky headers / cookie banners must not fully cover it. |
| **2.4.12 Focus Not Obscured (Enhanced)** | Same but stricter , no part of the focused element is hidden. |
| **2.4.13 Focus Appearance** | Focus indicator must be ≥ 2px thick and have ≥ 3:1 contrast against the unfocused background. |
| **2.5.7 Dragging Movements** | Any drag operation has a single-pointer alternative. |
| **2.5.8 Target Size (Minimum)** | Click targets ≥ 24x24px (was 44x44 in iOS HIG; WCAG is laxer). |
| **3.2.6 Consistent Help** | If the site offers help (contact link, search), it's in the same place on every page. |
| **3.3.7 Redundant Entry** | Never ask for info already provided in the same flow. |
| **3.3.8 Accessible Authentication (Minimum)** | No "remember this 6-digit code" auth challenges. |
| **3.3.9 Accessible Authentication (Enhanced)** | No cognitive function tests at all. |

### 7.2 Hard requirements for OndernemerMarketing.nl

- **Skip link** as first focusable element on every page. Visible
  on focus.

  ```html
  <a class="skip-link screen-reader-text" href="#main">
      <?php esc_html_e( 'Spring naar inhoud', 'ondernemer-marketing' ); ?>
  </a>
  ```

  With CSS:

  ```css
  .skip-link { position: absolute; left: -9999px; }
  .skip-link:focus { left: 1rem; top: 1rem; z-index: 99999;
      background: #fff; color: #0f172a; padding: 0.75rem 1.25rem;
      border: 2px solid #1d6bd1; border-radius: 0.5rem;
  }
  ```

- **Focus indicator** visible, ≥ 2px, ≥ 3:1 contrast (WCAG 2.2
  SC 2.4.13).

  ```css
  :focus-visible {
      outline: 2px solid #1d6bd1;
      outline-offset: 2px;
      box-shadow: 0 0 0 2px rgba(29, 107, 209, 0.25);
  }
  ```

- **Color contrast** ≥ 4.5:1 for normal text, ≥ 3:1 for large text
  (18pt+ or 14pt+ bold) and UI components. The brand palette
  passes for primary `#1d6bd1` on white (5.4:1) and ink `#0f172a`
  on white (16:1). **Accent `#f17a3c` on white is only 2.9:1**,
  so use accent for non-text accents (icons, dots) only , never
  for body text.
- **Headings semantic + hierarchical** , one `<h1>` per page,
  `<h2>` for top-level sections, no skipping levels.
- **All images have meaningful alt text** OR `alt=""` for
  decorative. WP 6.7+ block-editor Featured Image control nudges
  this; theme should respect empty alt as deliberate.
- **Form labels via `<label for="…">`**. Placeholders are not
  labels (WCAG fails on contrast + disappearance).
- **Error messages** announced via `role="alert"` + `aria-live="assertive"`,
  prefixed with "Error: " in the visible text + screen-reader
  text.
- **Keyboard navigation** works on every interactive control,
  in document order, no `tabindex` ≥ 1.
- **`lang="nl"`** on `<html>` for OndernemerMarketing (Dutch).
- **Reduced motion**: any non-essential animation respects
  `prefers-reduced-motion: reduce`.

  ```css
  @media (prefers-reduced-motion: reduce) {
      *, *::before, *::after {
          animation-duration: 0.01ms !important;
          animation-iteration-count: 1 !important;
          transition-duration: 0.01ms !important;
      }
  }
  ```

- **Forced-colors mode** (Windows High Contrast) doesn't break
  layout: use `CanvasText` / `Canvas` system colors for borders
  inside `@media (forced-colors: active)`.

### 7.3 The `accessibility-ready` tag , is it worth claiming?

OndernemerMarketing.nl is a custom client theme , not for the
wordpress.org directory. The `accessibility-ready` tag is only
relevant if you submit. For an EAA-compliant commercial site,
**meet the spec without claiming the tag**. Avoids the 30-June-
2026 review treadmill while still being legally compliant.

---

## 8. Performance , Core Web Vitals 2026 targets

Per the [2026 Core Web Vitals guide][r-cwv]:

| Metric | "Good" threshold | OndernemerMarketing target |
|---|---|---|
| **LCP** (Largest Contentful Paint) | ≤ 2.5s | ≤ 2.0s (mobile, 4G simulated) |
| **INP** (Interaction to Next Paint) | ≤ 200ms | ≤ 150ms |
| **CLS** (Cumulative Layout Shift) | ≤ 0.1 | ≤ 0.05 |

### 8.1 The WordPress LCP problem

WordPress's LCP failure is **slow TTFB** , driven by PHP execution
and DB queries on every uncached request. The fix is page
caching:

- **Server-level caching:** if Cloudways / WPEngine / Kinsta /
  Hostnet , enable their built-in object + page cache.
- **Plugin-level caching:** WP Rocket (paid) or
  LiteSpeed Cache (free, if on LiteSpeed) or W3 Total Cache.
- **CDN:** Cloudflare in front for static asset edge cache.

The theme contributes by:

- Not enqueueing JS that blocks the main thread before LCP.
- Setting `fetchpriority="high"` on the hero image:

  ```html
  <img src="/hero.jpg" width="1200" height="675"
       alt="Marketing voor ondernemers"
       loading="eager" fetchpriority="high" decoding="async">
  ```

- Self-hosting fonts (woff2 in `assets/fonts/`), `font-display: swap`
  via `theme.json` `fontFace.fontDisplay: "swap"`, preload via
  `<link rel="preload">`.
- `width` + `height` on every `<img>` so the browser reserves
  space , prevents CLS.

### 8.2 INP

Block themes generally have minimal JS , the win comes from not
adding more. For OndernemerMarketing:

- Bundle the funnel calculator / ROI forecaster / quickscan as
  small vanilla-JS view scripts (no React on the front-end).
- Defer any non-critical JS.
- No tracking pixels load before user interaction (cookie
  consent gates them).

### 8.3 CLS

- Reserve space for late-loading content (hero image, embedded
  video, fonts via `size-adjust: 100%` or `font-display: swap`
  + matching fallback metrics).
- No layout-shifting cookie banner , slot it in a fixed bottom
  area with `position: fixed; bottom: 0`.

---

## 9. Patterns + Pattern Overrides (WP 6.8)

Per the [Full Site Editing 2026 guide][r-fse], **Pattern
Overrides shipped stable in WP 6.8** (April 2026). This changes
how you build reusable sections.

Three states a pattern can be in:

| State | Behaviour |
|---|---|
| **Unsynced** | Pattern = template. Each placement is a fresh copy. Edit one, others don't change. |
| **Synced** | Pattern = single source of truth. Edit one, every placement updates. All-or-nothing. |
| **Synced with Overrides (NEW in 6.8)** | Structure is locked. Specific marked blocks (text, image, button) are editable per placement. |

**For OndernemerMarketing.nl** the high-leverage uses of synced-
with-overrides:

- **Footer** , synced (every page identical) , so you never edit
  the footer twice.
- **Hero CTA banner** , synced-with-overrides , page-specific
  headline + image, locked layout.
- **Testimonial card** , synced-with-overrides , the card
  structure is locked, the quote + author per placement.
- **Pricing card** , unsynced , each plan / pakket card has its
  own content, no benefit to syncing.

Pattern PHP file format (auto-registered when placed in
`patterns/`):

```php
<?php
/**
 * Title: Hero CTA
 * Slug: ondernemer-marketing/hero-cta
 * Categories: ondm-hero, featured
 * Description: Big headline + lead + two CTA buttons + hero image right.
 * Keywords: hero, intake, plan
 * Viewport Width: 1200
 * Block Types: core/cover
 */
?>
<!-- wp:cover {"minHeight":520,"isUserOverrideable":true} -->
...
```

**`isUserOverrideable: true`** (new in 6.8) is what makes a block
editable inside a synced pattern.

---

## 10. The do-not-do list (2026 edition)

Across the handbook + WPVIP + accessibility team, these patterns
are now grounds for theme rejection (or grounds for your client
to fire you):

1. **`functions.php` over 200 lines.** Move it to a plugin if it
   does anything other than theme setup + asset enqueueing +
   pattern registration. Block theme functions.php should be
   minimal.
2. **`header.php` / `footer.php` in a block theme.** These are
   classic-theme files. Block themes use `parts/header.html` +
   `parts/footer.html`.
3. **Custom Customizer panels.** Customizer is being retired.
   Site Editor is the 2026 customisation UI. Use `theme.json` +
   patterns instead.
4. **jQuery dependency.** Modern WP and modern blocks use
   vanilla JS. Themes pulling jQuery for a mobile menu in 2026
   are flagged.
5. **`wp_head()` / `wp_footer()` missing.** Block templates
   don't have these (they're emitted by the block-template
   renderer), but custom inline JS / CSS must not break this
   contract.
6. **Google Fonts CDN in 2026.** GDPR judgment in DE (Jan 2022,
   still in force) ruled that Google Fonts hot-loaded from
   `fonts.googleapis.com` violates GDPR without consent.
   **Self-host woff2 in `assets/fonts/`.** Mandatory for an NL
   client.
7. **Carousel as primary navigation.** Carousels fail multiple
   WCAG criteria. Use a grid or list.
8. **Modal dialog without `<dialog>`.** WP 6.7+ ships the
   `core/modal` block in alpha; if you build your own, use the
   native `<dialog>` element with `aria-modal` + focus-trap.
9. **`tabindex` ≥ 1.** Use `tabindex="0"` to make a non-
   interactive element focusable; never positive numbers.
10. **Hard-coded `style="font-family: …"` in templates.** The
    token lives in `theme.json`. If you find yourself typing
    inline `style=` in a template, the design system has a gap;
    fix the gap in `theme.json`.

---

## 11. Recommended directory layout for OndernemerMarketing.nl

Apply Sections 1-10 to the actual file tree:

```
wp-content/themes/ondernemer-marketing/
├── style.css                       , theme header only , no CSS
├── theme.json                      , v3 schema, full token set
├── functions.php                   , ONDM_THEME_VERSION + minimal setup
├── screenshot.png                  , 1200x900 px
├── templates/
│   ├── index.html
│   ├── front-page.html
│   ├── single.html
│   ├── page.html
│   ├── page-no-title.html          , customTemplate "Landing page"
│   ├── archive.html
│   ├── 404.html
│   └── search.html
├── parts/
│   ├── header.html
│   ├── footer.html
│   └── post-meta.html
├── patterns/
│   ├── hero-cta.php
│   ├── snelle-pakketten.php
│   ├── comparison-finder.php
│   ├── pricing-comparison.php
│   ├── snelle-pakketten-grid.php
│   ├── google-ads-pakketten-grid.php
│   ├── klare-start-pack-card.php
│   ├── testimonials-row.php
│   ├── resources-row.php
│   ├── final-cta.php
│   ├── over-ons-hero.php
│   ├── ons-verhaal.php
│   ├── onze-missie.php
│   ├── ons-team.php
│   ├── onze-waarden.php
│   ├── contact-intro.php
│   ├── contact-form-row.php
│   └── contact-faq.php
├── inc/
│   ├── seo.php                     , JSON-LD + OG + canonical + meta
│   ├── geo.php                     , robots.txt + llms.txt + IndexNow
│   ├── contact-form.php            , admin_post handler + Brevo bridge
│   └── booking.php                 , Cal.com embed wrapper
├── assets/
│   ├── front.css                   , <30KB. focus-visible, skip link,
│   │                                 prefers-reduced-motion, scoped tweaks
│   ├── editor-styles.css           , Site Editor canvas
│   ├── fonts/
│   │   ├── inter-400.woff2
│   │   ├── inter-600.woff2
│   │   └── inter-700.woff2
│   ├── images/
│   │   ├── hero-laptop-desk.avif
│   │   ├── hero-laptop-desk.webp
│   │   ├── team-ben-verschuur.avif
│   │   └── ...
│   └── blocks/
│       ├── core-cover.css
│       ├── core-button.css
│       ├── core-navigation.css
│       └── core-columns.css
├── languages/
│   ├── ondernemer-marketing.pot
│   └── nl_NL.po + .mo
└── README.md                       , one-pager about the theme
```

---

## 12. Day-1 acceptance test (project-specific, updated for 2026)

After the rebuild, on a clean WordPress 6.7+ install:

1. Theme activates. No PHP notices. No JS console errors.
2. Visit `/` , LCP ≤ 2.0s mobile (Lighthouse).
3. Lighthouse mobile: Performance ≥ 90, Accessibility = 100,
   SEO ≥ 95, Best Practices ≥ 95.
4. axe-core: 0 serious / 0 critical / 0 moderate violations.
5. Manual keyboard test: Tab through every page, every control
   reaches focus, focus indicator visible everywhere, focus is
   never obscured by sticky header / cookie banner (WCAG 2.2
   SC 2.4.11).
6. NVDA / VoiceOver test: skip-link works, headings make sense,
   form labels announce correctly, error messages announce via
   `role="alert"`.
7. Color contrast check (axe DevTools or Stark): every text
   colour passes 4.5:1.
8. Reduced-motion test (DevTools , Rendering , Emulate CSS media
   feature): animations stop.
9. Forced-colors test (Edge , Settings , Accessibility): layout
   still readable.
10. Mobile menu: opens, closes, body scroll locks when open,
    focus traps inside menu when open, Escape closes.
11. Schema.org Rich Results: home + Diensten + Pakketten pass.
12. Open Graph: Facebook Sharing Debugger renders the og:image.
13. WXR import on clean install: every page from
    `import/ondernemer-marketing-content.xml` lands with the
    right slug + content matches the screenshots.

If any of these fail, the rebuild is not done.

---

## Sources (verified May 2026)

- [r-required]: <https://make.wordpress.org/themes/handbook/review/required/>
  , Required Rules, Make WordPress Themes handbook
- [r-files]: <https://developer.wordpress.org/themes/releasing-your-theme/required-theme-files/>
  , Required Theme Files, Theme Handbook
- [r-themejson]: <https://developer.wordpress.org/block-editor/reference-guides/theme-json-reference/theme-json-living/>
  , theme.json v3 living reference, Block Editor Handbook
- [r-blockstyles]: <https://developer.wordpress.org/themes/features/block-stylesheets/>
  , Block Stylesheets, Theme Handbook
- [r-security]: <https://developer.wordpress.org/themes/advanced-topics/security/>
  , Security, Theme Handbook
- [r-escape]: <https://developer.wordpress.org/apis/security/escaping/>
  , Escaping Data, Common APIs Handbook
- [r-fse]: <https://neuronthemes.com/wordpress-block-themes-full-site-editing-guide>
  , WordPress Block Themes & Full Site Editing , Complete 2026 Guide
- [r-a11y]: <https://make.wordpress.org/accessibility/2026/05/06/accessibility-ready-requirements-updated/>
  , Accessibility-Ready Requirements Updated (6 May 2026), Make WP Accessible
- [r-ada]: <https://wpvip.com/blog/ada-website-accessibility-deadline-2026/>
  , ADA Website Accessibility , WCAG 2.1 by 2026, WordPress VIP
- [r-cwv]: <https://www.corewebvitals.io/core-web-vitals/wordpress-guide>
  , Core Web Vitals for WordPress (2026 Optimisation Guide)

---

**End of research brief.** Update this document whenever the
WordPress core team publishes a new accessibility-ready ruleset,
ships a new `theme.json` schema version, or new Core Web Vitals
thresholds change. Bump the heading "**Verified as of**" date at
the top.
