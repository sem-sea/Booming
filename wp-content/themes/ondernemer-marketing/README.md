# OndernemerMarketing , WordPress block theme

Custom block theme voor **ondernemermarketing.nl** , Nederlandse marketingoplossingen voor ondernemers. WordPress 6.7+, PHP 8.1+, WCAG 2.2 AA, GEO/AEO-ready.

## Installatie

1. Download de zip (`ondernemer-marketing.zip` of `ondernemer-marketing-1.0.0.zip`).
2. WP-admin , **Weergave , Thema's , Nieuw thema toevoegen , Thema uploaden** , kies de zip , **Nu installeren** , **Activeren**.
3. Maak de volgende pagina's aan (Pagina's , Nieuwe toevoegen) en wijs het bijbehorende template toe via de zijbalk **Pagina , Template**:

   | Slug | Titel | Template |
   |---|---|---|
   | `(homepage)` | OndernemerMarketing | (geen , `front-page.html` is automatisch actief op de homepagina, zie Instellingen , Lezen) |
   | `diensten` | Diensten | **Pagina , Diensten** |
   | `pakketten` | Pakketten | **Pagina , Pakketten** |
   | `google-ads-uitbesteden` | Google Ads Uitbesteden | **Pagina , Google Ads Uitbesteden** |
   | `tools` | Tools | **Pagina , Tools** |
   | `over-ons` | Over ons | **Pagina , Over ons** |
   | `contact` | Contact | **Pagina , Contact** |
   | `privacybeleid` | Privacybeleid | (standaard `page.html`) |
   | `algemene-voorwaarden` | Algemene voorwaarden | (standaard `page.html`) |
   | `disclaimer` | Disclaimer | (standaard `page.html`) |

4. **Instellingen , Lezen** , Zet **Een statische pagina** , kies "OndernemerMarketing" als Voorpagina, "Blog" (maak ook deze aan) als Berichten-pagina.

5. **Weergave , Editor , Navigatie** , controleer dat de header-menu items naar de juiste URLs verwijzen.

## Bestandsstructuur

```
ondernemer-marketing/
├── style.css                  , thema header
├── theme.json                 , design tokens (v3)
├── functions.php              , bootstrap, asset enqueue, pattern categories
├── parts/
│   ├── header.html            , sticky header met logo + nav + CTA
│   └── footer.html            , 4-koloms blauwe footer
├── templates/
│   ├── index.html             , default blog index
│   ├── front-page.html        , homepage (gebruikt 6 patterns)
│   ├── single.html            , single blogpost
│   ├── page.html              , default pagina
│   ├── archive.html           , categorie/tag archief
│   ├── search.html            , zoekresultaten
│   ├── 404.html               , niet gevonden
│   ├── page-diensten.html     , Diensten , pricing comparison + CTAs
│   ├── page-pakketten.html    , Pakketten , 4+2 pakketten + Klare Start
│   ├── page-google-ads.html   , Google Ads uitbesteden landingspagina
│   ├── page-tools.html        , Tools (resources)
│   ├── page-over-ons.html     , Over ons , verhaal + team + waarden
│   ├── page-contact.html      , Contact , form + info + FAQ
│   └── page-no-title.html     , landingspagina template (geen titel)
├── patterns/                  , herbruikbare secties (auto-geregistreerd)
│   ├── hero-cta.php           , home hero
│   ├── snelle-pakketten.php   , 3-card pakketten grid
│   ├── comparison-finder.php  , blauwe 4-knops vergelijker
│   ├── resources-row.php      , 3-card resources
│   ├── testimonials-row.php   , 2-card testimonials
│   ├── final-cta.php          , afsluitende CTA
│   ├── pricing-comparison.php , 3-tier vergelijkingstabel
│   ├── pakketten-page.php     , complete Pakketten pagina opbouw
│   ├── over-ons-page.php      , complete Over ons pagina opbouw
│   └── contact-page.php       , Contact form + FAQ
├── inc/
│   ├── seo.php                , JSON-LD, OG, Twitter, canonical, meta-desc
│   ├── geo.php                , robots.txt + llms.txt + IndexNow
│   └── contact-form.php       , admin-post handler voor het contactformulier
├── assets/
│   ├── front.css              , focus-visible, skip link, mobile menu, accessibility
│   ├── editor-styles.css      , editor canvas styles
│   └── blocks/                , per-block stylesheets (uitbreidbaar)
└── languages/                 , .pot / .po / .mo bestanden (vul aan)
```

## Brand tokens (theme.json)

| Token | Kleur | Toelichting |
|---|---|---|
| `primary` | `#1d6bd1` | Knoppen, links, secties |
| `accent` | `#f17a3c` | Logo "ondernemer", accenten (niet voor lopende tekst , slaagt geen 4.5:1) |
| `ink` | `#0f172a` | Koppen + body |
| `body` | `#334155` | Lopende tekst |
| `muted` | `#64748b` | Bijschriften |
| `soft` | `#f0f9ff` | Lichte achtergrondsecties |
| `white` | `#ffffff` | Witte achtergrond |

Typografie: **Inter** (system-stack fallback , geen Google Fonts CDN, GDPR/EAA-compliant).

## Contactformulier

Het formulier op `/contact/` POST naar `admin-post.php?action=ondm_contact`. De handler in `inc/contact-form.php`:

- Verifieert WordPress nonce + honeypot
- Valideert verplichte velden (Voornaam, Achternaam, E-mail, Bericht)
- Verstuurt via `wp_mail()` naar de site admin e-mail (configureerbaar via filter `ondm_contact_to`)
- Redirect met `?ondm_msg=ok` of `?ondm_msg=err`
- Hook `do_action( 'ondm_contact_submitted', $data )` voor integraties (Brevo, HubSpot, etc.)

Voor productie: vervang `wp_mail()` met de Brevo / SendGrid SDK , of installeer een SMTP-plugin (WP Mail SMTP).

## SEO / GEO / AEO

Automatisch geactiveerd:

- **JSON-LD schema** , Organization + WebSite site-breed; Article + FAQPage op singular.
- **Open Graph + Twitter Card** , `og:image` uit Featured image van de pagina, fallback naar site-logo.
- **Canonical URL**.
- **Meta description** , uit excerpt of eerste 160 tekens van de content.
- **`robots.txt`** , 3-laags AI crawler beleid (toestaan: OAI-SearchBot, Claude-SearchBot, PerplexityBot, Google-Extended, Applebot-Extended ; verbieden: GPTBot, CCBot, ClaudeBot, anthropic-ai).
- **`llms.txt`** op `/llms.txt` , Markdown samenvatting voor AI crawlers (pakketten, prijzen, contact).
- **IndexNow ping** , automatisch op publish van post/page naar `api.indexnow.org`.
- **Stand-down** , als Yoast / Rank Math / AIOSEO / SEOPress / The SEO Framework actief is, schrijft het thema GEEN dubbele tags.

## Toegankelijkheid (WCAG 2.2 AA)

- Skip-to-content link als eerste focusable element
- Zichtbare focus-ring (2px solid, 4px halo) op elk interactief element
- `lang="nl"` op `<html>` via WordPress core
- `aria-current="page"` op actieve nav items (via core navigation block)
- Forms met `<label>` koppeling + honeypot anti-spam
- Reduced-motion + forced-colors media queries
- Kleur-contrast: Primary blue (5.4:1) en Ink (16:1) op wit slagen ; Accent oranje alleen voor niet-tekst accenten (2.9:1, slaagt 3:1 voor UI)

## Versies & compatibiliteit

- WordPress **6.7+** (vereist voor `theme.json` v3 + Pattern Overrides)
- PHP **8.1+** (PHP 8.0 stopt support November 2026)
- Block themes only , werkt op klassieke thema's NIET (dit thema vervangt de klassieke layer volledig)

## Volgende stappen na installatie

1. **Voeg een screenshot.png toe** (1200x900px) aan de theme root , wordt getoond in Weergave , Thema's.
2. **Pas teksten + foto's aan** via de Site Editor (Weergave , Editor).
3. **Upload een logo** via Weergave , Editor , Stijlen , (logo opties).
4. **Stel een SMTP-plugin in** zodat het contactformulier daadwerkelijk e-mails verstuurt.
5. **Installeer een caching-plugin** (WP Rocket / LiteSpeed Cache) voor Core Web Vitals LCP < 2.5s.
6. **Optioneel: installeer Polylang of WPML** voor meertalige versies.
7. **Optioneel: installeer de CiteLeap-plugin** (zie `wp-content/plugins/citeleap/`) voor AI-gegenereerde blogcontent.

## Licentie

GPL-2.0-or-later , <https://www.gnu.org/licenses/gpl-2.0.html>
