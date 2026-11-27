# Booming Venture ,  WordPress Theme

A modern, full-site-editing (block) theme for **Booming Venture** ,  an
AI-powered performance-marketing agency from Rotterdam. Direct, faithful
port of the original Lovable / React site, rebuilt the WordPress-native
way so it can be edited entirely in `Appearance → Editor` without
touching code.

> **Tagline:** Performance. Personality. Powered by AI.

---

## 1.29.2 (November 2026)

- The site chat talks like a person: small talk is answered briefly and kindly, follow-up questions build on the conversation, general questions about marketing, sales, SEO, GEO, AI search and B2B growth get a real answer from general knowledge (facts about Ben and Booming Venture still only from the site), length follows the question (up to about 150 words), and it answers in the visitor's language.
- The no-model filter now only stops attempts to change the rules and clear coding or homework jobs; summaries, translations of its own answers and other normal questions go to the model. Memory: the last 12 messages instead of 8.

## 1.29.1 (November 2026)

- Fix: chat answers stopped halfway. Claude Haiku 5.5 thinks by default and thinking tokens count toward max_tokens, so 450 tokens could run out before the answer was done. The chat now turns thinking off (reasoning.enabled false), drops the temperature setting (Haiku 5.5 only accepts the default), allows 900 tokens and up to about 120 words, and if an answer still hits the limit it ends at the last full sentence (noted in bv_chat_last_error).
- Same cause fixed in the GEO Index: the tone rating turns thinking off (400 tokens), Claude through OpenRouter thinks at low effort, and the direct Anthropic call allows 4000 tokens so thinking and the answer both fit.

## 1.29.0 (November 2026)

- The site chat knows the B2B GEO Index numbers: measurement dates, the website checks and their points, and per list the average, the top 10, the pass rate per check and all 20 sectors with average, leaders and weakest check (bv_chat_index, from stored results, cached per measurement).
- When a visitor names a company from the index (name, alias or domain), the chat gets that company's list, sector, rank, score and missing checks (bv_chat_index_match, at most three companies). Names that are everyday words, such as Boom or Maandag, only count when written as the company name.
- New rules: numbers only from the index data with the measurement date, no judgments about companies, unknown companies go to the free check. New page tag geo-method for the method page.

## 1.28.0 (November 2026)

- The monthly GEO Index scan now starts on the first working day of the month (Monday to Friday, not a Dutch public holiday) at 05:00 UTC, instead of the first Monday. New helpers bv_gi_first_workday() and bv_gi_nl_holidays() (Easter worked out in PHP, no calendar extension). Next scans: Tuesday 1 December 2026, Monday 4 January 2027, Monday 1 February 2027.
- All texts follow: index block, monthly report FAQ, settings, chat context, Dataset schema, index page FAQ (content.json) and the method log (method 2 now dated 1 December 2026).

## 1.27.0 (November 2026)

- Sector deep link on the GEO Index: /b2b-geo-index/?sector=c64 (or /nl/b2b-geo-index/?sector=c64) opens the index filtered on that sector, with the sector PDF button shown and the filters scrolled into view. Unknown keys are ignored. Made for the LinkedIn sector videos.

## 1.26.0 (November 2026)

- Sector analysis in every company PDF: the sector ranking with the company highlighted, average website score against the whole list and the same sector in the other list, the pass rate per check in the sector and the list next to the company's own result, the three checks that cost the sector most points (with advice), and the sources AI assistants cite most in the sector.
- Sector report as its own PDF: GET /wp-json/bv/v1/geo-pdf?sector=c20&list=nl&lang=nl. A download button appears under the filters when a sector is picked.

## 1.25.0 (November 2026)

- PDF report per company: GET /wp-json/bv/v1/geo-pdf?domain=&list=&lang= (inc/geo-index-pdf.php, FPDF 1.9 in inc/lib/fpdf). Brand header, score card, every check with its points, AI numbers when measured, gap with the sector leader and a numbered improvement plan, in Dutch or English. Download button in every details panel, and "Download your improvement plan as PDF" after the newsletter step of the check. Built from stored data only (no outside requests), 30 per hour per visitor.
- Labels and advice per check live in bv_gi_texts(), shared by the block and the PDF.
- Details button restyled (white pill with a round chevron, gradient when open) and pinned to the right edge of the company column on wide screens, under the company name on phones. Short label "Details" in the wide table, full label for screen readers.
- Motion: details grow open and closed, cards and bars animate in, lists fade when you switch tabs or filter, the check shows a spinner, fades in and counts the score up, folding sections open smoothly where supported. All motion is off with "reduce motion".

## 1.24.1 (November 2026)

- Public check: when a homepage refuses our request (bot protection, firewall), the score shows as incomplete with the HTTP status, the points that could not be measured and what to ask the web team. Such results are kept 15 minutes instead of 24 hours, so a fix can be checked again the same day.
- Public check: the sector picker always shows; a domain outside the index is compared with the leader's website score. The language check names what this page measures (Dutch version on the Dutch page, two or more language versions on the English page) and links to the check on the other page.
- Dutch feed at /nl/feed/ (Dutch posts, nl-NL, Dutch Yoast footer); Dutch pages link to it. Before, /nl/feed/ was the empty comment feed of the /nl/ page.
- Contact Form 7 buttons in Dutch on Dutch pages (Aanmelden, Stuur me de gids, Bericht versturen).
- GEO Index settings: the draft rule says 160 of 200 companies per list.

## 1.24.0 (November 2026)

- GEO Index method 2: five buyer questions per category with a different intent, round 1 for every category and a second round only where the result is still uncertain (adaptive, cheaper), 95% margins of error (Wilson) on every AI number, ranks for the top 10 and groups after that, changes since last month only when larger than the margin, at least 12 answers for an AI score.
- New measures per company: visibility, share of voice in the sector, named first, tone (rated by Claude Haiku), citation share, per assistant.
- Difference insights: change since last month, per assistant (and overlap between assistants), gap with the sector leader (points, sources, checks), website versus AI (four groups and Spearman), Netherlands versus international, most cited sources per sector. Details load on demand from compact data.
- Ten companies per sector in both lists: 400 companies, 137 categories. The public check shows the gap with the sector leader.
- Method pages /b2b-geo-index/methodology/ and /nl/b2b-geo-index/methode/ (tools/geo-index/method.py, POST /bv/v1/import-nl?update=geo-method) with live details from [bv_geo_method].

## 1.23.1 (November 2026)

- Chat language follows the page: English pages show the English chat and answers, Dutch pages (/nl/) the Dutch ones, also when a visitor types in the other language. Each language keeps its own conversation.

## 1.23.0 (November 2026)

- "Ask Ben" AI chat (`inc/chat.php`, `assets/js/chat.js`): launcher with Ben's photo on every page, panel loaded only after a click, always labelled as an AI assistant (EU AI Act art. 50). Answers from the site's own pages and blog posts with Claude Haiku via OpenRouter (`data_collection: deny`, no transcripts stored).
- Safety: only browsers on this site (Origin, Sec-Fetch-Site, bot list), limits per visitor per minute and per day, a site cap per day and a monthly budget, signed answers so a forged history is dropped, fixed answers without a model call for greetings, misuse and requests for promises, links only from a whitelist of own pages and posts.
- Settings > AI-chat: off, admins only (default, for testing) or everyone; own OpenRouter key or the GEO Index key; usage and cost this month.

## 1.10.1 (November 2026)

- SCRIPT_DEBUG is switched off once in wp-config.php (single define only, the site is checked right after and the original file is put back on any doubt). Status in the `bv_script_debug_status` option.

## 1.10.0 (November 2026)

From the SEO and GEO audit of 18 November 2026.

- Forms: one field style on every form. Fields no longer run outside their card on phones, 52 px fields and buttons, a visible button inside white cards, no floating asterisk, accessible border contrast, autofill hints and clear button labels ("Send me the guide", "Subscribe", "Send message").
- Contact form on the homepage rendered as plain text "[contact-form-7 ...]": shortcode blocks inside patterns now run.
- Phones: no floating tool buttons; one chat button loads DataSpeak on tap, so no pop-up covers content. Desktop: chat loads after the first interaction. Header button hidden below 440 px (it ran off screen), Contact added to the mobile menu.
- Fonts: Inter and Space Grotesk ship with the theme (they were missing and gave 404s).
- Speed: WordPress core assets load minified even with SCRIPT_DEBUG on, minified theme CSS and JS (tools/build-min.sh), more inline block CSS, lazy images on the homepage, browser cache rules in .htaccess (mod_expires) that remove themselves if the site does not answer cleanly.
- robots.txt: AI model crawlers (GPTBot, ClaudeBot, Google-Extended and others) are now allowed for brand visibility in AI answers; only heavy crawlers without value stay blocked. The redirecting wp-sitemap.xml line is gone.
- llms.txt: the static Yoast file is switched off; the theme version lists all articles by category, with clean text. llms-full.txt is served without a slash redirect.
- Schema: the Yoast author is Ben Verschuur (founder), matching the byline and Organization.
- Content: five duplicate articles merged into the stronger one (draft plus 301), comparison tables in the 20 "vs" articles (figures from the articles themselves), About copy without clichés.
- Accessibility: darker brand gradient and link color for 4.5:1 contrast, muted text darkened, heading order fixed in hero, footer and post lists.

## 1.9.2 (November 2026)

- Site Editor copies of theme templates that still carry the old hero, stock testimonials, unverified stats or the old blog query are moved to the trash (restorable) so the updated theme files show. The list is kept in the `bv_set_aside_templates` option and shown once in wp-admin.
- About page checklist: unverified claims (200+ projects, 3x results) replaced with facts.

## 1.9.1 (November 2026)

- Content bundle and media fall back to a pinned copy on GitHub when the theme is deployed without `import/` (file-by-file deploys).
- The upgrade also runs on the first authenticated admin REST request, not only on a wp-admin page view.
- 404 slug redirect can no longer redirect to itself; it flushes stale rewrite rules instead.
- Bundle entries without a body never create empty posts.

## 1.9.0: update over the live site (November 2026)

Built from a Screaming Frog crawl (511 URLs) and a REST export of boomingventure.com.
Same theme, same design, same URLs. Upload it over the active theme
(Appearance > Themes > Add New > Upload, then "Replace current with uploaded").
The upgrade runs once on the next wp-admin page you open.

- `import/content.json`: all 134 posts (live content, block markup), 14 pages, 10 categories, SEO meta. Regenerate with `tools/rebuild/build_content.py`.
- `import/media/`: every upload the content uses, same `/wp-content/uploads/2026/05/` paths as live. Copied into the Media Library in batches (Tools > Booming Venture import, WP-Cron fallback, or `wp bv import`).
- Existing posts are never overwritten. Only their category, missing SEO meta and old `/blog/slug/` links change, and their "last updated" date stays.
- Site title "Booming Venture -" becomes "Booming Venture" (no dangling dash in search titles).
- Front end in English (`lang="en-GB"`), admin stays in Dutch. Filter `bv_frontend_locale` to change.
- Permalinks stay `/%postname%/`. 301s for `/blog/{slug}/`, `?query-1-page=N`, `?cst` and the 16 merged categories.
- Blog index uses the main query: `/blog/page/2/` instead of `?query-1-page=2`.
- Meta descriptions for every post and page (`_bv_meta_description` + `_yoast_wpseo_metadesc`, existing values kept).
- Yoast: English title templates, company name, LinkedIn `sameAs`, richer Organization schema.
- Stock testimonials and unverified stats removed; homepage links to the latest articles.
- Atomic install lock, so parallel requests after activation cannot create duplicate posts.

## ✨ What's inside

| Folder                    | What it is |
| ------------------------- | ---------- |
| `style.css`               | Theme header for WordPress. |
| `theme.json`              | Design tokens ,  brand palette (`booming-*`, `venture-*`), gradients, fluid typography, spacing scale, shadow presets. |
| `functions.php`           | Bootstraps `inc/*`. No business logic. |
| `inc/setup.php`           | Theme supports, image sizes, nav menus, head cleanup. |
| `inc/enqueue.php`         | Fonts, theme CSS/JS, GTM, preconnect hints. |
| `inc/cpt.php`             | Custom post types: `service`, `landing_page`, `case_study` + post meta. |
| `inc/blocks.php`          | Registers the custom Gutenberg blocks. |
| `inc/patterns.php`        | Pattern categories. |
| `inc/seo.php`             | JSON-LD (Organization, WebSite, BlogPosting, Service, BreadcrumbList), OG/Twitter fallback. |
| `inc/security.php`        | Hardening: hides REST users, blocks author enum, security headers. |
| `inc/integrations.php`    | Brevo REST proxy + Contact Form 7 slug→ID mapper + admin settings page. |
| `templates/`              | Block templates: `index`, `front-page`, `page`, `single`, `archive`, `search`, `404`, + custom templates for the tool/landing pages. |
| `parts/`                  | `header.html`, `footer.html`. |
| `patterns/`               | Sectioned block patterns (hero, services-grid, about, testimonials, contact, growth-guide-cta, cta-band, newsletter-cta, awards-banner, blog-grid, UNIFY framework). |
| `blocks/funnel-calculator`| Custom block ,  Funnel Leak Calculator (vanilla JS, **ships working** ,  no build). |
| `blocks/roi-forecaster`   | Custom block ,  ROI Forecaster with SVG chart + table + PDF export (vanilla JS, **ships working**). |
| `assets/css/theme.css`    | Animations, glassmorphism, check-list pseudo-element, FABs, scroll-progress. |
| `assets/css/editor.css`   | Editor preview tweaks. |
| `assets/js/theme.js`      | Scroll progress, smooth scroll, FABs, external-link safety. |
| `import/booming-venture-content.xml` | One-shot WXR import: pages, services, 40 blog posts. |

---

## 🎯 What makes this a 10/10 theme & site

These are the choices that distinguish a good theme from a great one ,  every
one of them is wired up in this codebase. If you change anything, keep these
intact.

1. **Block-first (FSE) architecture.** Every layout lives in `templates/*.html`
   and `patterns/*.php`. You can re-skin the site in the editor ,  no PHP.
2. **Design tokens in `theme.json`.** Brand palette, gradients, fluid typography,
   spacing scale, shadow presets. Change one variable, the whole site updates.
3. **Patterns over page builders.** No Elementor / Divi bloat. Every section is
   a native block pattern that ships under "Booming · Sections" in the inserter.
4. **Performance.**
   - Fonts: `font-display: swap`, preloaded.
   - Critical CSS via `theme.json` (no extra request).
   - `wp_resource_hints` preconnects to GTM.
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
   `viewScript` for hydration only ,  no React shipped to pages without it.
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

# 3. Install Contact Form 7 + anti-spam + storage + Brevo bridge
wp plugin install contact-form-7 contact-form-7-honeypot flamingo mailin --activate
# (or use the admin notice link)

# 4. Import content
wp import wp-content/themes/booming-venture/import/booming-venture-content.xml --authors=create
```

Then set **Settings → Reading → Front page** to the imported `Home` page.

---

## 🧩 Mapping ,  Lovable → WordPress

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
| Brevo `addContactToBrevo`    | Contact Form 7 + Brevo plugin (or CF7 to Any API) |
| Google Tag Manager           | Auto-injected in `inc/enqueue.php`, ID `GTM-K9532WK5`      |
| react-router-dom             | Native WordPress routing                                   |
| Lottie preloader             | Removed ,  improved perf budget                             |
| Cookie consent (`CookieConsent.tsx`) | Add Complianz or CookieYes plugin (recommended)    |

---

## 🛠 Customising

- **Brand colours**: `theme.json → settings.color.palette`. Everything cascades.
- **Logo**: `Appearance → Editor → Header → Site Logo`, or `wp option update`.
- **Menus**: `Appearance → Menus` ,  five locations registered (primary, footer, legal, tools).
- **GTM ID**: set as option `bv_gtm_id` or
  drop into wp-config:
  ```php
  define( 'BV_BREVO_API_KEY', 'xkeysib-...' );
  ```
- **Add a new section pattern**: drop a `.php` file in `patterns/` with the
  pattern header ,  it auto-registers.

---

## 📈 What to do next (production-readiness checklist)

- [ ] Add real client/award logos to `pattern/awards-banner.php`
- [ ] Replace placeholder hero / about / service imagery in `assets/images/`
- [ ] Drop Inter + Space Grotesk woff2 files into `assets/fonts/`
- [ ] Create the six Contact Form 7 forms following the recipes in
      `INSTALL.md §5a` (contact, newsletter, newsletter-inline,
      growth-guide, quickscan, head-of-growth), wire each to its Brevo
      list (Newsletter=3, Growth Guide=2, Contact=4), and paste each
      form's CF7 hash ID into *Settings → Booming Venture*
- [ ] Install Rank Math (or Yoast) and import the JSON-LD already on-page
- [ ] Install a cookie-consent plugin (Complianz / CookieYes) ,  required EU
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
