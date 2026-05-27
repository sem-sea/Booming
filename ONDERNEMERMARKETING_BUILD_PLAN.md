# OndernemerMarketing.nl , Build Plan

> **Read `WORDPRESS_SITE_REBUILD_GUIDE.md` first.** This document
> applies that abstract guide to the specific OndernemerMarketing.nl
> brief , brand tokens, sitemap, page-by-page block specs, exact
> pricing, sprint plan, and acceptance checklist.
>
> **Source of truth for design:** the six screenshots
> `Ondernemermarketing-nl-...screen-*.png` at the repo root on the
> `Ondernemermarketing` branch.

---

## 0. Project brief in one paragraph

**OndernemerMarketing.nl** is a Dutch marketing agency for solo
founders + small business owners ("ondernemers") who want customers,
not a marketing crash course. Tagline: **"Je wilt klanten. Niet leren
marketen."** The site sells four productised packages and three
subscription tiers, plus a "Klare Start Pack" funnel + a Google Ads
sub-line. Built as a WordPress 6.6+ block theme (slug:
`ondernemer-marketing`), portable to any small-agency brand.

---

## 1. Brand tokens (from screenshots)

| Token | Value | Notes |
|---|---|---|
| `primary` | `#1d6bd1` | Mid-saturation blue, all H-buttons + links + section bg |
| `primary-dark` | `#155ab0` | Hover state |
| `accent` | `#f17a3c` | Orange for the logo "ondernemer" word + small icons |
| `accent-dark` | `#d8662a` | Hover |
| `ink` | `#0f172a` | Headings + body text |
| `body` | `#334155` | Paragraph text on white |
| `muted` | `#64748b` | Captions, helper text |
| `soft` | `#f0f9ff` | Light section background |
| `soft-2` | `#eff6ff` | Alternate light section background |
| `white` | `#ffffff` | Page background, card background |
| `border` | `#e2e8f0` | Card borders, dividers |
| `success` | `#16a34a` | Green check icons (incl. price-table ticks) |
| `error` | `#b91c1c` | Red X icons (price-table negatives) |

### Typography

- **Display + Body:** `Inter` (Google Fonts, weights 400 / 500 / 600
  / 700). Fallback: `system-ui, sans-serif`.
- **H1:** `clamp(2rem, 5vw, 3.25rem)`, weight 700, line-height 1.15.
- **H2:** `clamp(1.5rem, 3vw, 2.25rem)`, weight 700, line-height 1.25.
- **H3:** `clamp(1.25rem, 2.2vw, 1.5rem)`, weight 600.
- **Body:** `1rem`, line-height 1.65.
- **Caption:** `0.875rem`, line-height 1.5, color `muted`.

### Logo

Text-mark in two colours:
- `ondernemer` , `accent` (#f17a3c)
- `marketing.nl` , `ink` (#0f172a)
- Stacked on two lines, weight 600, slight letter-spacing
  (`-0.01em`).

### Buttons

- **Primary:** `primary` bg, white text, radius `0.5rem`, padding
  `0.75rem 1.25rem`, weight 600. Hover: `primary-dark` + 2% lift
  shadow.
- **Secondary:** transparent bg, `primary` border + text, same
  radius + padding.
- **Outline-on-blue (footer):** white border + text, transparent bg.

### Voice rules

- **Language:** Dutch (`lang="nl"`, locale `nl_NL`).
- **Tone:** direct, ondernemer-vriendelijk, geen jargon. Korte zinnen.
  "Je" not "U".
- **Forbidden phrases:** *synergie, paradigma, holistisch, in een
  notendop, het is geen X maar Y* and the English AI-tell list
  translated (delve , verdiepen, leverage , inzetten, navigate ,
  navigeren). No em-dashes. Use commas or periods.
- **Numbers always specific:** "48% groei", not "veel groei". Named
  source when citing.

---

## 2. Sitemap

```
/                  Home
/diensten          Diensten (Services overview + pricing comparison)
/pakketten         Pakketten (Productised packages catalog)
/google-ads-uitbesteden    Google Ads sub-page
/tools             Tools (calculators , Funnel + ROI + Quickscan)
/over-ons          Over ons (Story + mission + team + values)
/contact           Contact (form + details + FAQ)
/blog              Blog (post archive)
/blog/<slug>       Single blog post
/privacybeleid     Privacy policy
/algemene-voorwaarden  Terms of service
/disclaimer        Disclaimer
```

Header nav (in order, from screenshots):
`Diensten` , `Pakketten` , `Tools` , `Over ons` , `Contact` ,
**[Plan gratis intake]** (primary button)

Footer columns:
- **OndernemerMarketing** (about + contact details)
- **Snelle links:** Diensten / Pakketten / Google Ads Uitbesteden /
  Tools / Blog / Over ons / Contact
- **Juridisch:** Privacybeleid / Algemene voorwaarden / Disclaimer
- **Volg ons:** LinkedIn

---

## 3. Page-by-page block specs

Each page is a sequence of registered patterns. One pattern per
section. Pattern slugs are namespaced `ondernemer-marketing/<slug>`.

### 3.1 Home (`/`) , screen 1 + 4

| Order | Pattern slug | Content |
|---|---|---|
| 1 | `hero-cta` | H1: **"Je wilt klanten. Niet leren marketen."** , Subhead: "Wij regelen je marketing. Jij ondernemt." , CTA primary "Plan gratis intake" , CTA secondary "Bekijk pakketten" , Hero image right (laptop on desk) |
| 2 | `snelle-pakketten` | Section title "Snelle Pakketten" + intro , 3 cards: Social Media Funnel Pack €600 / Lead Magnet Landingspagina €950 / Website Light €99 (icon, title, blurb, price, "Bekijk pakket" CTA) |
| 3 | `comparison-finder` | Blue background. H2 "Welke marketingoplossing past bij jou?" , subhead "Een vergelijking in 4 stappen, ontdek wat het beste bij jou past." , 4-tab toggle: "Eenmalig & laagdrempelig" / "Doorlopend & geautomatiseerd" / "Volledig uitbesteed" / "Hulp van een expert" , 1 CTA per tab |
| 4 | `resources-row` | H2 "Standaard resources" , 3 download cards: Marketing Pluskaart / Strategie Calculator / Funnel Audit (each: icon, title, blurb, "Download" CTA) |
| 5 | `testimonials-row` | H2 "Wat onze klanten zeggen" , 2 cards: Sarah van der Berg ("Dankzij OndernemerMarketing hebben we eindelijk een marketingstrategie die werkt") + Lisa Hartmann ("Het was alsof we een marketingteam in dienst hadden") , 5-star rating , "Lees meer cases" link |
| 6 | `final-cta` | Bold blue/soft section. H2 "Klaar om marketing uit te besteden?" , 3-bullet list (Wat krijg je?) , CTA primary "Plan gratis intake" , Hero image right |
| 7 | `site-footer` (template part) | 4-col footer + legal line |

### 3.2 Diensten (`/diensten`) , screen 2

| Order | Pattern | Content |
|---|---|---|
| 1 | `site-header` (template part) | nav + CTA |
| 2 | `pricing-comparison` | H1 "Vergelijk onze diensten" , subhead "Kies de perfecte oplossing voor jouw bedrijf" , intro paragraph , **3-column comparison table** with the 3 subscription tiers (see Section 4 below for exact prices + features). Each column has a "Meer over X" button at the bottom. |
| 3 | `site-footer` | |

### 3.3 Pakketten (`/pakketten`) , screen 3

| Order | Pattern | Content |
|---|---|---|
| 1 | `site-header` | |
| 2 | `pakketten-intro` | H1 "Pakketten" , subhead "Kies de perfecte marketingoplossing voor jouw bedrijf" , 1-paragraph intro |
| 3 | `snelle-pakketten-grid` | Section title "Snelle Pakketten" , 4 cards in 2x2 grid: Social Media Funnel Pack / Lead Magnet Landingspagina / One Page Funnel / Website Light (each: hero image, title, price, "Inclusief:" bullet list, "Bestellen" CTA) |
| 4 | `google-ads-pakketten-grid` | Section title "Google Ads Pakketten" , 2 cards: Google Ads Kickstart €450/mo + €750 setup / Google Ads Groei Pack €750/mo |
| 5 | `klare-start-pack-card` | Featured wide blue card. Title "Klare Start Pack" , "Snel van 0 naar je eerste professionele online aanwezigheid" , price "vanaf €2.400" , 4-bullet "Inclusief" list , CTA "Plan gratis intake gesprek" |
| 6 | `welk-pakket-cta` | "Niet zeker welk pakket bij je past?" + CTA "Plan gratis intake gesprek" |
| 7 | `site-footer` | |

### 3.4 Google Ads Uitbesteden (`/google-ads-uitbesteden`) , bespoke

| Order | Pattern | Content |
|---|---|---|
| 1 | `site-header` | |
| 2 | `google-ads-hero` | H1 "Google Ads uitbesteden" + subhead + CTA |
| 3 | `google-ads-pakketten-grid` (reused) | |
| 4 | `google-ads-benefits` | 3-col features: Stop budget verspillen / Doorlopende optimalisatie / Transparante rapportages |
| 5 | `google-ads-process` | 4-step proces met icons |
| 6 | `testimonials-row` (reused) | |
| 7 | `final-cta` (reused) | |
| 8 | `site-footer` | |

### 3.5 Tools (`/tools`)

| Order | Pattern | Content |
|---|---|---|
| 1 | `site-header` | |
| 2 | `tools-intro` | H1 "Gratis tools voor ondernemers" + subhead |
| 3 | `tool-card-grid` | 3 cards linking to: Funnel Calculator / ROI Forecaster / Marketing Quickscan |
| 4 | `site-footer` | |

Each tool gets its own page with the custom block embedded:
- `/tools/funnel-calculator` , `<!-- wp:ondernemer-marketing/funnel-calculator /-->`
- `/tools/roi-forecaster` , `<!-- wp:ondernemer-marketing/roi-forecaster /-->`
- `/tools/marketing-quickscan` , `<!-- wp:ondernemer-marketing/marketing-quickscan /-->`

### 3.6 Over Ons (`/over-ons`) , screen 5

| Order | Pattern | Content |
|---|---|---|
| 1 | `site-header` | |
| 2 | `over-ons-hero` | H1 "Over Ons" , subhead "Wij zijn OndernemerMarketing" , wide office photo |
| 3 | `ons-verhaal` | H2 "Ons verhaal" , 3-paragraph story (frustration met marketingbureaus die jargon spreken, opgericht om dat anders te doen) |
| 4 | `onze-missie` | H2 "Onze missie" , 1-paragraph mission statement |
| 5 | `ons-team` | H2 "Ons team" , subhead "De mensen achter OndernemerMarketing" , Ben Verschuur card (Oprichter & Strategie, 15 jaar in digital marketing), portrait, 2-sentence bio |
| 6 | `onze-waarden` | H2 "Onze waarden" , 4 value cards: Resultaatgericht / Eerlijkheid / Ownership / Eenvoud |
| 7 | `final-cta` | |
| 8 | `site-footer` | |

### 3.7 Contact (`/contact`) , screen 6

| Order | Pattern | Content |
|---|---|---|
| 1 | `site-header` | |
| 2 | `contact-intro` | H1 "Contact" , subhead "Neem contact met ons op" + 1-paragraph blurb |
| 3 | `contact-form-row` | 2-column: left = Form (Voornaam* / Achternaam* / E-mailadres* / Bericht* + Verzenden) ; right = Contactgegevens (E-mail, Telefoon, Adres) + photo |
| 4 | `contact-faq` | H2 "Veelgestelde vragen" , 3 FAQ items: "Hoe snel kunnen jullie starten?" / "Werken jullie met contracten?" / "Wat als ik niet tevreden ben?" |
| 5 | `site-footer` | |

### 3.8 Blog (`/blog`) , standard archive

Block template `archive.html` with:
- Posts loop , card grid (image + title + excerpt + date)
- Pagination
- Sidebar with "Onderwerpen" categories + "Populair" recent posts

### 3.9 Legal pages

`/privacybeleid`, `/algemene-voorwaarden`, `/disclaimer`. Single-
column text pages. Boilerplate Dutch privacy + ToS adapted for:
- Bedrijfsnaam: OndernemerMarketing (KvK: TBD)
- E-mail: `info@ondernemermarketing.nl`
- Adres: Breedveldsingel 1, 3055PG Rotterdam
- Telefoon: +31 6 1301 3266

---

## 4. Pricing catalog (locked-in numbers from screenshots)

### 4.1 Subscription tiers (Diensten page comparison)

| Feature | **Kickstart Funnel** | **Groei Machine** | **Volledig Uitbesteed** |
|---|---|---|---|
| **Price** | **€3.500** (one-shot) | **€4.500 / mo** | **€7.500 / mo** |
| Ideal for | Ondernemers die willen starten met een effectieve funnel zonder gedoe | Ondernemers die serieus groei willen realiseren met content + performance marketing | Ondernemers die hun marketing volledig willen uitbesteden voor maximale focus op hun bedrijf |
| Strategische lead magnets | ✓ | ✓ | ✓ |
| E-mail nurturing reeks | ✓ | ✓ | ✓ |
| Verkoopgesprek strategie | ✓ | ✓ | ✓ |
| Technische implementatie | ✓ | ✓ | ✓ |
| A/B testing | ✗ | beperkt | ✓ |
| Contentcreatie | ✗ | ✓ | ✓ |
| SEO-optimalisatie | ✗ | ✓ | ✓ |
| Performance marketing | ✗ | ✓ | ✓ |
| Persoonlijke marketeer | ✗ | ✗ | ✓ |
| Maandelijkse optimalisatie | ✓ | ✓ | ✓ |

### 4.2 Snelle Pakketten (one-shot productised packages)

| Pack | Price | Inclusief |
|---|---|---|
| **Social Media Funnel Pack** | **€600** | Nieuwe profielafsteek + lead magnet of leadmodel ria a profile + 5 SMM-posts met automation om te starten |
| **Lead Magnet Landingspagina** | **€950** | Landingspagina copy + structuur + lead magnet of download + e-mail nurturing reeks aan de start |
| **One Page Funnel** | **€1.200** | Verkooppagina + opt-in + lead-magnet + e-mail follow-up + analytics setup |
| **Website Light** | **€99** | Eén pagina; basis website met je content en CTA's, of professionele website |

### 4.3 Google Ads Pakketten

| Pack | Setup | Monthly | Inclusief |
|---|---|---|---|
| **Google Ads Kickstart** | **€750** eenmalig | **€450 / mo** | Account opzetten + zoekwoorden + advertentiecampagnes opzetten + maandelijkse optimalisatie (tot €1.500 budget) |
| **Google Ads Groei Pack** | included | **€750 / mo** | Voor bestaande accounts willen verder groeien |

### 4.4 Klare Start Pack (featured wide card)

- **Price:** **vanaf €2.400** (one-shot)
- **Inclusief:**
  - Social Media Funnel Pack
  - Lead Magnet Landingspagina
  - Onboarding & 1-uur Plan Call met onze
  - Live na 2-3 weken klaar met aangeleverde content

---

## 5. Theme structure on disk

```
wp-content/themes/ondernemer-marketing/
├── style.css                     , Theme: "OndernemerMarketing"
│                                   Version: 1.0.0 (bump every release)
├── theme.json                    , tokens from Section 1 above
├── functions.php                 , OM_THEME_VERSION const, asset
│                                   enqueueing, block registration,
│                                   pattern category registration
├── front-page.html               , Home blocks chain
├── singular.html                 , single fallback
├── single.html                   , blog single
├── page.html                     , default page
├── archive.html                  , blog archive
├── 404.html                      ,
├── search.html                   ,
├── parts/
│   ├── header.html               , site header (logo + nav + CTA)
│   └── footer.html               , 4-col footer
├── patterns/
│   ├── hero-cta.php
│   ├── snelle-pakketten.php
│   ├── comparison-finder.php
│   ├── resources-row.php
│   ├── testimonials-row.php
│   ├── final-cta.php
│   ├── pricing-comparison.php
│   ├── pakketten-intro.php
│   ├── snelle-pakketten-grid.php
│   ├── google-ads-pakketten-grid.php
│   ├── klare-start-pack-card.php
│   ├── welk-pakket-cta.php
│   ├── google-ads-hero.php
│   ├── google-ads-benefits.php
│   ├── google-ads-process.php
│   ├── tools-intro.php
│   ├── tool-card-grid.php
│   ├── over-ons-hero.php
│   ├── ons-verhaal.php
│   ├── onze-missie.php
│   ├── ons-team.php
│   ├── onze-waarden.php
│   ├── contact-intro.php
│   ├── contact-form-row.php
│   └── contact-faq.php
├── blocks/
│   ├── funnel-calculator/        , block.json + view.js + render.php
│   ├── roi-forecaster/
│   └── marketing-quickscan/
├── inc/
│   ├── seo.php                   , JSON-LD + OG + canonical + meta
│   ├── geo.php                   , robots.txt + llms.txt + IndexNow
│   ├── nav.php                   ,
│   └── booking.php               , "Plan gratis intake" handler
│                                   (Cal.com / Calendly embed wrapper)
├── assets/
│   ├── images/
│   │   ├── hero-laptop-desk.jpg
│   │   ├── team-ben-verschuur.jpg
│   │   ├── client-sarah.jpg
│   │   ├── client-lisa.jpg
│   │   └── office-wide.jpg
│   ├── fonts/                    , Inter (4 weights, woff2 only)
│   └── style-overrides.css       , scoped tweaks only
├── languages/
│   └── ondernemer-marketing.pot
└── import/
    └── ondernemer-marketing-content.xml   , WXR seed
```

---

## 6. Sprint plan (10 working days end-to-end)

### Sprint 1 , skeleton + tokens (1.5 days)

- [ ] `style.css` header (Version: 1.0.0).
- [ ] `theme.json` with the full token set from Section 1.
- [ ] `functions.php` with `OM_THEME_VERSION` const + asset enqueue
      + register_block_pattern_category() for our categories
      ("OndernemerMarketing , Hero", "OndernemerMarketing , Pakketten",
      etc.).
- [ ] `front-page.html`, `singular.html`, `single.html`, `page.html`,
      `archive.html`, `404.html`, `search.html` block templates with
      placeholders.
- [ ] `parts/header.html`, `parts/footer.html` , real, from screenshots.
- [ ] `inc/seo.php` + `inc/geo.php` (port from booming-venture theme).
- [ ] Inter font loaded via `theme.json` `fontFamilies` (woff2 in
      `assets/fonts/` so no Google Fonts CDN call on the front-end ,
      EU GDPR best practice).

**Smoke test:** activate theme on a fresh WP install , homepage loads
white-background-no-errors , Lighthouse Best Practices ≥ 90.

### Sprint 2 , patterns (3 days)

One pattern per row in the per-page block specs (Section 3). Order
of build:

Day 1: home patterns (`hero-cta`, `snelle-pakketten`,
`comparison-finder`, `resources-row`, `testimonials-row`, `final-cta`).

Day 2: pricing + packages patterns (`pricing-comparison`,
`pakketten-intro`, `snelle-pakketten-grid`,
`google-ads-pakketten-grid`, `klare-start-pack-card`,
`welk-pakket-cta`).

Day 3: about + contact + google-ads-sub + tools patterns. Total
≈25 patterns.

**Smoke test:** Site Editor pattern picker shows all 25 patterns,
each renders without "block validation failed" warnings.

### Sprint 3 , content seed (1.5 days)

- [ ] `build-content.py` , Python script that emits the WXR with one
      WP page per sitemap entry. Each page's `content:encoded` is the
      block markup composing the patterns above.
- [ ] Pages: Home (front-page slug), Diensten, Pakketten, Google Ads
      Uitbesteden, Tools, Over Ons, Contact, Privacybeleid, Algemene
      Voorwaarden, Disclaimer.
- [ ] Tool pages: 3 separate page entries embedding the custom blocks.
- [ ] Validate XML with the standard `xml.etree.ElementTree.parse()`
      check.
- [ ] Import on a clean install , every screenshot should be matched
      pixel-close.

### Sprint 4 , custom blocks (2 days)

Three blocks, vanilla JS, no transpile, server-side rendered for AI
crawlers:

- [ ] `blocks/funnel-calculator/` , 4 inputs (visitors / opt-in / lead
      / customer) + computed output (revenue projection + leak %).
- [ ] `blocks/roi-forecaster/` , spend + CAC + LTV + months , chart
      output (use Canvas API, no Chart.js dependency).
- [ ] `blocks/marketing-quickscan/` , 10-question survey + scored
      output + auto-email follow-up (POSTs to `admin-post.php` ,
      Brevo).

Each block:
- `block.json` apiVersion 3
- `view.js` vanilla, < 5KB
- `render.php` SSR fallback
- Registered via `register_block_type( __DIR__ . '/blocks/<slug>' )`.

### Sprint 5 , contact form + booking + integrations (1 day)

- [ ] Contact form: native WP form via Brevo Form embed OR
      lightweight custom handler in `inc/booking.php` POSTing to
      `admin-post.php`. Reuse the Brevo Form Fix plugin pattern from
      Booming Venture if Brevo is in scope.
- [ ] "Plan gratis intake" CTA: link to Cal.com / Calendly embed (or
      a dedicated `/intake` page with the embed).
- [ ] Newsletter subscribe (footer): Brevo list subscribe.
- [ ] Email destination: `info@ondernemermarketing.nl`.

### Sprint 6 , SEO + GEO + AEO infra (1 day)

- [ ] `inc/seo.php` writes JSON-LD `Article` + `FAQPage` + `Service`
      + `Organization` + `WebSite` + `BreadcrumbList`.
- [ ] `inc/geo.php` ships robots.txt with the 3-tier AI-crawler
      split + llms.txt + IndexNow ping on publish + "Laatste update:"
      banner on stale posts.
- [ ] Every page H1 contains the primary entity.
- [ ] Home + Diensten + Pakketten + Over Ons + Contact each have ≥1
      question-shaped H2 followed by a 20-60 word answer capsule.
- [ ] Pakketten + Diensten pages have ≥3 statistics with named source
      in the first 30% of body (cite Indig 2026, Princeton GEO study,
      Ahrefs 560k corpus where relevant).

### Sprint 7 , polish + launch (1 day)

- [ ] Lighthouse pass: Performance ≥ 90, Accessibility ≥ 95, SEO ≥ 95,
      Best Practices ≥ 95 on mobile.
- [ ] axe-core pass: 0 serious / critical violations.
- [ ] Real-device check (iOS Safari + Android Chrome).
- [ ] Cookie consent banner (Klaro or Cookiebot, GDPR-compliant for NL).
- [ ] Privacy / ToS / Disclaimer text reviewed by legal (placeholder
      template ships with the seed).
- [ ] Versioned zip built: `wp-content/themes/ondernemer-marketing.zip`
      AND `wp-content/themes/ondernemer-marketing-1.0.0.zip`.
- [ ] Tag the release: `git tag ondernemer-marketing-1.0.0`.

---

## 7. Content (Dutch copy , draft)

The full Dutch copy per pattern. The build-content.py script reads
this section + the screenshots to generate `content:encoded` blocks.

### Hero (home)

- **H1:** `Je wilt klanten. Niet leren marketen.`
- **Lead:** `Wij regelen je marketing. Jij ondernemt.`
- **Sub-lead:** `Resultaatgerichte marketing voor ondernemers met
  weinig tijd. Geen jargon, geen gedoe, gewoon klanten erbij.`
- **CTA primary:** `Plan gratis intake`
- **CTA secondary:** `Bekijk pakketten`

### Snelle Pakketten section (home)

- **Section title:** `Snelle Pakketten`
- **Subhead:** `Professionele marketingoplossingen die werken.`
- **Card 1 , Social Media Funnel Pack (€600):** `Nieuwe profielafsteek
  + lead magnet of leadmodel, vijf SMM-posts met automation, klaar
  om te starten.`
- **Card 2 , Lead Magnet Landingspagina (€950):** `Een
  professioneel ingerichte landingspagina, een gratis lead magnet,
  een e-mail nurturing reeks. Direct meer leads.`
- **Card 3 , Website Light (€99):** `Eén pagina, basis website met
  je content en CTA's. Klaar in 7 dagen.`

### Comparison finder (home)

- **H2:** `Welke marketingoplossing past bij jou?`
- **Subhead:** `Een vergelijking in 4 stappen, ontdek wat het beste
  bij jou past.`
- **Tabs:**
  - `Eenmalig & laagdrempelig` , linkt naar `/pakketten#snelle`
  - `Doorlopend & geautomatiseerd` , linkt naar `/diensten#groei`
  - `Volledig uitbesteed` , linkt naar `/diensten#uitbesteed`
  - `Hulp van een expert` , linkt naar `/contact`

### Standaard resources (home)

- **H2:** `Standaard resources`
- **Subhead:** `Gratis tools om je marketing te verbeteren.`
- **Card 1 , Marketing Pluskaart:** `Een visuele kaart van je
  marketingfunnel met de juiste tools per stap.`
- **Card 2 , Strategie Calculator:** `Bereken hoeveel klanten je
  per maand kan binnenhalen.`
- **Card 3 , Funnel Audit:** `Een 5-minuten audit van je huidige
  funnel, met direct bruikbare verbeterpunten.`

### Testimonials (home)

- **H2:** `Wat onze klanten zeggen`
- **Quote 1 (Sarah van der Berg):** `Dankzij OndernemerMarketing
  hebben we eindelijk een marketingstrategie die werkt. Geen praat,
  gewoon resultaat.`
- **Quote 2 (Lisa Hartmann):** `Het was alsof we een marketingteam
  in dienst hadden, zonder de overhead. Aanrader.`

### Final CTA (home + reused)

- **H2:** `Klaar om marketing uit te besteden?`
- **Lead:** `We nemen het stuur over zodat jij kan ondernemen.`
- **3-bullet "Wat krijg je?":**
  - `Persoonlijke marketeer als aanspreekpunt`
  - `Maandelijkse strategie + uitvoering`
  - `Transparante rapportage in jouw dashboard`
- **CTA:** `Plan gratis intake`

### Over Ons sections (screen 5)

- **Hero H1:** `Over Ons`
- **Subhead:** `Wij zijn OndernemerMarketing.`
- **Ons verhaal:**
  > OndernemerMarketing is opgericht uit frustratie. Frustratie over
  > marketingbureaus die ingewikkelde strategieën verkopen aan
  > ondernemers zijdezeen met marketingvakken die ze niet willen
  > delen.
  >
  > Als ondernemer wil je klanten, geen marketingcursus. Je wilt
  > resultaten, geen eindeloze conferencerooms die je pet moet
  > zetten. En je wilt zekerheid, geen experimenteel met "naar veen
  > kijken of het werkt".
  >
  > Daarom doen wij het anders. We nemen de volledige marketing uit je
  > handen, hanteren transparante rapportages, en zorgen voor
  > voorspelbare klantengroei. Geen ingewikkeld jargon, geen
  > losse flodders, maar een compleet system dat werkt.

- **Onze missie:**
  > Ondernemers bevrijden van marketinggedoe en hen helpen om meer
  > impact te maken met hun bedrijf door hen toegang te geven tot
  > een voorspelbare stroom aan ideale klanten.

- **Team , Ben Verschuur, Oprichter & Strategie:**
  > Met 15 jaar ervaring in digitale marketing heeft Ben vele
  > ondernemers geholpen met het bouwen van effectieve
  > marketingsystemen.

- **Onze waarden (4 cards):**
  - `Resultaatgericht` , We meten alles en sturen constant bij op
    basis van data, niet op basis van onderbuikgevoel.
  - `Eerlijkheid` , We zeggen wat we kunnen waarmaken en zijn 100%
    transparant over verwachtingen en resultaten.
  - `Ownership` , We nemen verantwoordelijkheid voor het succes van
    onze klanten en doen er alles aan om in resultaten te behalen.
  - `Eenvoud` , We maken marketing simpel en begrijpelijk, zonder
    jargon of onnodige complexiteit.

### Contact page (screen 6)

- **H1:** `Contact`
- **Subhead:** `Neem contact met ons op.`
- **Lead:** `Wil je weten wat wij voor jou kunnen betekenen? Vul het
  formulier in of neem direct contact met ons op.`
- **Form labels:** `Voornaam*`, `Achternaam*`, `E-mailadres*`,
  `Bericht*`, button `Verzenden`.
- **Contactgegevens:**
  - E-mail: `info@ondernemermarketing.nl`
  - Telefoon: `+31 6 1301 3266`
  - Adres: `Breedveldsingel 1, 3055PG Rotterdam, Nederland`
- **FAQ Veelgestelde vragen:**
  - Q: `Hoe snel kunnen jullie starten?`
    A: `Meestal kunnen we binnen 2 dagen na het intakegesprek
       starten met de implementatie van je marketingstrategie.`
  - Q: `Werken jullie met contracten?`
    A: `Voor de Social Media Funnel Pack werken we met een eenmalig
       project. Voor andere diensten werken we met een minimale
       periode van 3 maanden, daarna maandelijks opzegbaar.`
  - Q: `Wat als ik niet tevreden ben?`
    A: `We werken met duidelijke resultaatafspraken. Als we deze
       niet halen, kijken we samen naar een passende oplossing of
       compensatie.`

---

## 8. Day-1 acceptance checklist (project-specific)

After the full rebuild, run this on a clean WordPress 6.6+ install:

1. Activate `ondernemer-marketing` theme.
2. Tools , Import , WordPress , upload
   `wp-content/themes/ondernemer-marketing/import/ondernemer-marketing-content.xml`.
3. Visit `/` , every block from screen 1 + screen 4 renders.
4. Visit `/diensten` , 3-column comparison table matches screen 2
   (Kickstart Funnel €3.500 / Groei Machine €4.500 / Volledig
   Uitbesteed €7.500).
5. Visit `/pakketten` , 4 Snelle pakketten + 2 Google Ads pakketten +
   Klare Start Pack featured card all render (screen 3).
6. Visit `/over-ons` , team card with Ben Verschuur photo + value
   cards (screen 5).
7. Visit `/contact` , form posts + FAQ renders (screen 6).
8. Mobile view: menu opens + closes, body scroll locks.
9. Lighthouse mobile: Performance ≥ 90, Accessibility ≥ 95,
   SEO ≥ 95, Best Practices ≥ 95.
10. axe-core: zero serious / critical violations.
11. Schema.org Rich Results test passes on home + Pakketten + Diensten.
12. Open Graph image renders in Facebook Sharing Debugger.
13. JSON validates: `python3 -c "import xml.etree.ElementTree as ET;
    ET.parse('wp-content/themes/ondernemer-marketing/import/ondernemer-marketing-content.xml'); print('OK')"`.
14. PHP lint: zero errors on every PHP file.

If any of these fail, the rebuild is not done.

---

## 9. Open decisions before sprint start

Lock these BEFORE Sprint 1, or the build stalls:

1. **Domain:** confirm `ondernemermarketing.nl` is owned + DNS-ready.
2. **KvK number** for the legal footer + privacy policy.
3. **Real photos** for hero + team + clients (or stock placeholders
   tagged for replacement).
4. **Booking system:** Cal.com vs Calendly vs custom WP plugin (e.g.
   Amelia)?
5. **Newsletter platform:** Brevo (already in Booming Venture stack)
   vs MailerLite vs ActiveCampaign?
6. **Analytics:** Plausible (privacy-friendly, NL-cookieless) vs GA4
   (need consent banner)?
7. **CiteLeap plugin:** ship preinstalled for AI blog posts, or
   leave blog empty for manual editorial?
8. **Hosting:** Kinsta / Cloudways / WP Engine / Dutch host (Hostnet,
   Vimexx)?

---

**End of build plan.** Read this together with
`WORDPRESS_SITE_REBUILD_GUIDE.md` and the six screenshots at the
repo root. That is everything Claude Code (or a human contractor)
needs to ship OndernemerMarketing.nl.
