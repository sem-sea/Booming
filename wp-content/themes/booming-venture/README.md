# Booming Venture — WordPress Theme

A modern, full-site-editing (block) theme for **Booming Venture** — an
AI-powered performance-marketing agency from Rotterdam. Direct, faithful
port of the original Lovable / React site, rebuilt the WordPress-native
way so it can be edited entirely in `Appearance → Editor` without
touching code.

> **Tagline:** Performance. Personality. Powered by AI.

---

## ✨ What's inside

| Folder                    | What it is |
| ------------------------- | ---------- |
| `style.css`               | Theme header for WordPress. |
| `theme.json`              | Design tokens — brand palette (`booming-*`, `venture-*`), gradients, fluid typography, spacing scale, shadow presets. |
| `functions.php`           | Bootstraps `inc/*`. No business logic. |
| `inc/setup.php`           | Theme supports, image sizes, nav menus, head cleanup. |
| `inc/enqueue.php`         | Fonts, theme CSS/JS, GTM, DataSpeak chat (idle-loaded), preconnect hints. |
| `inc/cpt.php`             | Custom post types: `service`, `landing_page`, `case_study` + post meta. |
| `inc/blocks.php`          | Registers the custom Gutenberg blocks. |
| `inc/patterns.php`        | Pattern categories. |
| `inc/seo.php`             | JSON-LD (Organization, WebSite, BlogPosting, Service, BreadcrumbList), OG/Twitter fallback. |
| `inc/security.php`        | Hardening: hides REST users, blocks author enum, security headers. |
| `inc/integrations.php`    | Brevo REST proxy + Fluent Forms install nudge. |
| `templates/`              | Block templates: `index`, `front-page`, `page`, `single`, `archive`, `search`, `404`, + custom templates for the tool/landing pages. |
| `parts/`                  | `header.html`, `footer.html`. |
| `patterns/`               | Sectioned block patterns (hero, services-grid, about, testimonials, contact, growth-guide-cta, cta-band, newsletter-cta, awards-banner, blog-grid, UNIFY framework). |
| `blocks/funnel-calculator`| Custom block — Funnel Leak Calculator (vanilla JS, **ships working** — no build). |
| `blocks/roi-forecaster`   | Custom block — ROI Forecaster with SVG chart + table + PDF export (vanilla JS, **ships working**). |
| `assets/css/theme.css`    | Animations, glassmorphism, check-list pseudo-element, FABs, scroll-progress. |
| `assets/css/editor.css`   | Editor preview tweaks. |
| `assets/js/theme.js`      | Scroll progress, smooth scroll, FABs, external-link safety. |
| `import/booming-venture-content.xml` | One-shot WXR import: pages, services, 40 blog posts. |

---

## 🎯 What makes this a 10/10 theme & site

These are the choices that distinguish a good theme from a great one — every
one of them is wired up in this codebase. If you change anything, keep these
intact.

1. **Block-first (FSE) architecture.** Every layout lives in `templates/*.html`
   and `patterns/*.php`. You can re-skin the site in the editor — no PHP.
2. **Design tokens in `theme.json`.** Brand palette, gradients, fluid typography,
   spacing scale, shadow presets. Change one variable, the whole site updates.
3. **Patterns over page builders.** No Elementor / Divi bloat. Every section is
   a native block pattern that ships under "Booming · Sections" in the inserter.
4. **Performance.**
   - Fonts: `font-display: swap`, preloaded.
   - Critical CSS via `theme.json` (no extra request).
   - DataSpeak chat → `requestIdleCallback` after 4s, never blocks LCP.
   - `wp_resource_hints` preconnects to Brevo / GTM / Unsplash / DataSpeak.
   - JS is deferred, in footer.
   - Custom blocks are server-rendered; React mounts only where used.
5. **Accessibility (WCAG 2.2 AA).** Visible focus rings, semantic landmarks
   (`<header>`, `<main>`, `<footer>`), `prefers-reduced-motion` respected,
   `<noscript>` fallbacks on every interactive block, alt text on every image.
6. **SEO.**
   - JSON-LD graph: Organization + WebSite + per-post BlogPosting + BreadcrumbList.
   - Native WordPress sitemap left enabled.
   - OG / Twitter fallback meta (skipped if Yoast / Rank Math present).
   - Clean URL structure preserved from the Lovable site so existing links
     don't break.
7. **CPTs + meta done right.** `service`, `landing_page`, `case_study` with
   `show_in_rest: true`, post-meta registered for block bindings.
8. **Custom blocks done right.** `block.json` API v3, server-side render,
   `viewScript` for hydration only — no React shipped to pages without it.
9. **Security defaults.** No author enumeration, locked-down login errors,
   sensible headers, REST users hidden for guests.
10. **Editor parity.** `editor.css` mirrors front-end key styles so authors
    see what they'll ship.

---

## 🚀 Install in 5 steps

See **INSTALL.md** for the full walk-through. TL;DR:

```bash
# 1. Drop the theme into wp-content/themes
# 2. Activate
wp theme activate booming-venture

# 3. Install Fluent Forms + the Brevo connector
wp plugin install fluentform brevo-fluentform-integration --activate
# (or use the admin notice link)

# 4. Import content
wp import wp-content/themes/booming-venture/import/booming-venture-content.xml --authors=create
```

Then set **Settings → Reading → Front page** to the imported `Home` page.

---

## 🧩 Mapping — Lovable → WordPress

| Lovable route                | WordPress equivalent                                       |
| ---------------------------- | ---------------------------------------------------------- |
| `/`                          | Page **Home** (front-page) with home patterns              |
| `/about`                     | Page **About Us**                                          |
| `/services`                  | Page **Services** + CPT `service` archive                  |
| `/unify-framework`           | Page **UNIFY Framework**                                   |
| `/blog`                      | Page **Blog** + posts archive                              |
| `/blog/:slug`                | Single `post`                                              |
| `/funnel-calculator`         | Page using `page-funnel-calculator` template               |
| `/roi-forecaster`            | Page using `page-roi-forecaster` template                  |
| `/free-growth-guide`         | Page using `page-landing` template                         |
| `/boardroom-quickscan`       | Page using `page-boardroom-quickscan` template             |
| `/head-of-growth`            | Page using `page-head-of-growth` template                  |
| `/privacy-policy`, `/terms-of-service`, `/disclaimer` | Standard pages         |

| Lovable React widget         | WordPress equivalent                                       |
| ---------------------------- | ---------------------------------------------------------- |
| Funnel Calculator (jsPDF)    | Custom block `booming-venture/funnel-calculator`           |
| ROI Forecaster (recharts)    | Custom block `booming-venture/roi-forecaster`              |
| Brevo `addContactToBrevo`    | Fluent Forms → Brevo connector (recommended) or `wp-json/bv/v1/brevo` |
| DataSpeak chat               | Idle-loaded in `inc/enqueue.php`, interface ID `6863892dbcf4fea86a49e9f8` |
| Google Tag Manager           | Auto-injected in `inc/enqueue.php`, ID `GTM-K9532WK5`      |
| react-router-dom             | Native WordPress routing                                   |
| Lottie preloader             | Removed — improved perf budget                             |
| Cookie consent (`CookieConsent.tsx`) | Add Complianz or CookieYes plugin (recommended)    |

---

## 🛠 Customising

- **Brand colours**: `theme.json → settings.color.palette`. Everything cascades.
- **Logo**: `Appearance → Editor → Header → Site Logo`, or `wp option update`.
- **Menus**: `Appearance → Menus` — five locations registered (primary, footer, legal, tools).
- **GTM / DataSpeak IDs**: set as options `bv_gtm_id`, `bv_dataspeak_id` or
  drop into wp-config:
  ```php
  define( 'BV_BREVO_API_KEY', 'xkeysib-...' );
  ```
- **Add a new section pattern**: drop a `.php` file in `patterns/` with the
  pattern header — it auto-registers.

---

## 📈 What to do next (production-readiness checklist)

- [ ] Add real client/award logos to `pattern/awards-banner.php`
- [ ] Replace placeholder hero / about / service imagery in `assets/images/`
- [ ] Drop Inter + Space Grotesk woff2 files into `assets/fonts/`
- [ ] Publish each Fluent Form (contact, newsletter, growth-guide,
      newsletter-inline, quickscan, head-of-growth) and connect to Brevo
      lists (Newsletter=3, Growth Guide=2, Contact=4)
- [ ] Install Rank Math (or Yoast) and import the JSON-LD already on-page
- [ ] Install a cookie-consent plugin (Complianz / CookieYes) — required EU
- [ ] Run Lighthouse → target ≥95 across the board
- [ ] Set up staging on a `develop` branch + CI deploy
- [ ] Add `image-team.jpg`, `about-leader.jpg`, `og-default.jpg` to
      `assets/images/` (referenced by hero, about, OG meta)

---

## 🏗 Files at a glance

```
booming-venture/
├── style.css                    Theme header
├── theme.json                   Design tokens (the source of truth)
├── functions.php                Bootstrap
├── inc/                         Setup, enqueue, CPTs, blocks, SEO, security, integrations
├── templates/                   Block templates incl. custom page templates
├── parts/                       header.html, footer.html
├── patterns/                    Section patterns (auto-registered)
├── blocks/                      Custom Gutenberg blocks (separate npm workspace)
├── assets/                      CSS, JS, images, fonts
└── import/booming-venture-content.xml   One-shot content seed
```
