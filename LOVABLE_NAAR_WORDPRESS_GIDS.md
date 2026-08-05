# Van Lovable naar WordPress , de definitieve gids

> **Doel:** een Lovable-website (React/TSX export) omzetten naar een
> volwaardig WordPress **block theme**, ready to zip, ready to upload.
> Volledig conform WordPress 6.7+ standards, WCAG 2.2 AA, GDPR/AVG,
> Core Web Vitals, en de WordPress security-richtlijnen.
>
> **Deze gids beschrijft alleen wat je WEL moet doen.** Elke regel is
> een concrete stap of best practice. Volg ze op volgorde.
>
> **Gebaseerd op:** de OndernemerMarketing.nl conversie in dit repo
> (branch `Ondernemermarketing`, commit `1ec202d`). Alle voorbeelden
> verwijzen naar echte bestanden in `wp-content/themes/ondernemer-marketing/`.

---

## Deel 1 , Voorbereiding (voor je begint met coderen)

### 1.1 Verzamel je bronmateriaal

**Doe:** download 3 dingen uit Lovable:
1. **De volledige zip export** (`.zip` , bevat TSX components, CSS,
   images, package.json, alles).
2. **Alle screenshots** van elke pagina in het uiteindelijke design ,
   full-page, mobiel + desktop. Deze zijn je visuele bron van
   waarheid, niet de TSX.
3. **De brand assets** apart: logo (SVG of hoge-resolutie PNG met
   transparantie), favicon, kleuren-palet.

**Doe:** commit de zip + screenshots naar een dedicated branch in
je repo (in dit project: `Ondernemermarketing`). Extract de zip
naar een `lovable-source/` directory voor referentie.

### 1.2 Doe een security-scan op de Lovable export

**Doe:** `grep -rE "xkeysib-|sk-|sk-ant-|AIza|Bearer " lovable-source/`
en controleer of er geen API keys in de code staan. Lovable
hardcodeert vaak Brevo / OpenAI / Firebase keys in service files.

**Als je een key vindt:** trek hem meteen in bij de betreffende
provider (Brevo dashboard, OpenAI dashboard, etc.) en genereer een
nieuwe. Redigeer de key uit de source vóór je commit. Als de zip
al gepushed is met de key erin: rotate meteen , de key zit in git
history en is publiek toegankelijk vanuit de zip.

**Doe:** voeg een `.gitignore` toe met minimaal:
```
vendor/
composer.lock
.phpunit.cache/
node_modules/
dist/
build/
__pycache__/
.DS_Store
.env
.env.local
```

### 1.3 Extraheer de design tokens uit de Lovable code

**Doe:** open `lovable-source/index.css` en `tailwind.config.ts` en
noteer:
- **Kleuren** (primary, accent, ink, body, muted, achtergronden,
  succes/error) , meestal in HSL. Converteer naar hex.
- **Fonts** (family, weights, welke Google Fonts worden gebruikt).
- **Border-radius**, **spacing scale**, **breakpoints**.

**Doe:** noteer de kleuren met hun contrastratio's op wit:
```
primary #1d6bd1 op wit  = 5.4:1  , goed voor tekst
accent  #f17a3c op wit  = 2.9:1  , alleen voor niet-tekst (icons)
ink     #0f172a op wit  = 16:1   , tekst
```
Contrast onder 4.5:1 mag niet als lopende tekst gebruikt worden.

### 1.4 Identificeer wat WEL overneembaar is uit de TSX

**Doe:** noteer per Lovable-component:
- **De copy** (Nederlandse teksten) , 1:1 overneembaar.
- **De layout structuur** (kolommen, kaarten, hero, CTA) , vertaalt
  naar block patterns.
- **De iconen + afbeeldingen** , download de daadwerkelijke bestanden
  uit de zip, niet de Lovable CDN URLs.
- **Kleuren + spacing** , overzetten naar `theme.json`.

**Wat WEL bruikbaar is uit Lovable maar via WordPress-equivalent:**
- React state , WordPress-server-side render (SSR via `render.php`)
- React Router , WordPress permalinks + templates
- React hooks , vanilla JS `view.js` waar echt nodig
- Tailwind classes , CSS in `theme.json` tokens + `front.css`

### 1.5 Beslis: welke Lovable-componenten worden custom blocks?

**Doe:** onderscheid twee categorieën:
- **Statische secties** (hero, pakketten, testimonials, FAQ) ,
  worden **block patterns** (PHP files in `patterns/`).
- **Interactieve widgets** (calculators, formulieren met live
  compute, sliders, quizzen) , worden **custom blocks** (Vanilla JS
  `view.js` + `render.php` in `blocks/`).

**Regel:** als de operator via de Site Editor moet kunnen bewerken
= pattern. Als er echt JavaScript-berekening in zit die live
recompute nodig heeft = custom block.

---

## Deel 2 , Theme skelet opzetten

### 2.1 Directory structuur

**Doe:** maak deze structuur aan:
```
wp-content/themes/<slug>/
├── style.css                  , theme header
├── theme.json                 , design tokens
├── functions.php              , bootstrap (minimal)
├── screenshot.png             , 1200x900 thumbnail
├── README.md                  , operator handleiding
├── parts/
│   ├── header.html
│   └── footer.html
├── templates/
│   ├── index.html
│   ├── front-page.html
│   ├── single.html
│   ├── page.html
│   ├── archive.html
│   ├── search.html
│   ├── 404.html
│   └── page-<slug>.html       , custom templates per pagina
├── patterns/
│   └── *.php                  , één per sectie
├── blocks/
│   └── <name>/
│       ├── block.json
│       ├── render.php
│       └── view.js
├── inc/
│   ├── install.php            , auto-setup hook
│   ├── seo.php
│   ├── geo.php
│   └── contact-form.php
├── assets/
│   ├── front.css
│   ├── editor-styles.css
│   ├── images/                , logo, hero photos, brand assets
│   └── fonts/                 , woff2 self-hosted
└── languages/
    └── <slug>.pot
```

### 2.2 style.css , de theme header

**Doe:** exact deze structuur volgen. Elke regel is verplicht.

```css
/*
Theme Name:        MijnMerk
Theme URI:         https://mijnmerk.nl
Author:            MijnMerk
Author URI:        https://mijnmerk.nl
Description:       Custom block theme voor mijnmerk.nl. WCAG 2.2 AA, GEO/AEO ready, self-hosted fonts.
Version:           1.0.0
Requires at least: 6.7
Tested up to:      6.8
Requires PHP:      8.1
License:           GPL-2.0-or-later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:       mijnmerk
Tags:              full-site-editing, block-patterns, block-styles, accessibility-ready, translation-ready
*/
```

**Doe:** houd `Text Domain` gelijk aan de theme-slug én aan de
directory-naam. Gebruik `PHP 8.1+` (PHP 8.0 support stopt november
2026).

### 2.3 theme.json , alle design tokens

**Doe:** gebruik `"version": 3` (WP 6.7+ verplicht voor moderne
features). Zet altijd deze top-level flags aan:
```json
{
    "$schema": "https://schemas.wp.org/trunk/theme.json",
    "version": 3,
    "settings": {
        "appearanceTools": true,
        "useRootPaddingAwareAlignments": true
    }
}
```

`useRootPaddingAwareAlignments: true` lost het "alignfull produceert
horizontale scrollbalk" probleem op.

**Doe:** definieer per token-type in `settings`:
- `color.palette` , array van `{slug, color, name}` per merkkleur.
  Verwijs later met `var:preset|color|<slug>` in `styles`.
- `typography.fontFamilies` , inclusief `fontFace` array om woff2 bestanden
  zelf te hosten (zie sectie 2.4).
- `typography.fontSizes` , met `fluid: {min, max}` per grootte voor
  automatische responsive typografie.
- `spacing.spacingScale.steps: 7` + `customSpacingSize: true`.
- `layout.contentSize` (bijv. `720px`) en `wideSize` (`1200px`).

**Doe:** definieer `styles.elements` voor h1-h6, button, link zodat de
Site Editor global styles panel ze kan overschrijven. Gebruik `clamp()`
voor fluid headline-typografie.

**Doe:** registreer `customTemplates` per landingpagina die je nodig
hebt , de operator kan die dan per pagina toewijzen via het Pagina
sidebar-panel.

### 2.4 Fonts self-hosten

**Doe:** download de woff2 bestanden van de Lovable Google Font
lokaal (bijvoorbeeld via <https://gwfh.mranftl.com/fonts>). Plaats
ze in `assets/fonts/`.

**Doe:** registreer ze in `theme.json` via de `fontFace` array met
`"src": [ "file:./assets/fonts/inter-400.woff2" ]` per gewicht.

Waarom lokaal: het Duitse LG München-arrest van januari 2022 (nog
steeds geldig in 2026) verklaart Google Fonts CDN gebruik zonder
consent in strijd met AVG. Voor een NL commerciële site is
self-hosten verplicht om EAA-compliant te blijven.

### 2.5 functions.php , minimalistisch

**Doe:** houd `functions.php` onder de 150 regels. De enige zaken die
erin horen: theme version constant, editor styles + i18n,
`wp_enqueue_scripts` voor front.css, `wp_enqueue_block_style()` per
block, `register_block_type()` voor custom blocks,
`register_block_pattern_category()` voor pattern categorien, en
`require_once` per include module.

Prefix álles met een unieke 4+ letter code (`mjm_`, `MJM_`) om
conflicten met andere plugins/themes te voorkomen. Dit is een
verplichte WordPress-review regel.

---

## Deel 3 , Templates en parts opbouwen

### 3.1 parts/header.html

**Doe:** gebruik `<!-- wp:site-logo -->` en niet hardcoded `<img>`.
De install-hook (deel 5) zet automatisch de logo. Voeg een
skip-to-content link toe als eerste focusable element via een
`<!-- wp:html -->` block.

Verberg de site-title tekst als het logo aanwezig is via CSS:
```css
.wp-block-site-logo + .mjm-site-title-fallback { display: none; }
```

### 3.2 templates/front-page.html , via patterns

**Doe:** compose de homepage door patterns te referencen, niet door
inline block-markup te dumpen. Zo kan de operator de pattern later
één keer bewerken en zie je de wijziging overal.

```html
<!-- wp:template-part {"slug":"header","tagName":"header"} /-->
<!-- wp:group {"tagName":"main"} -->
<main id="main-content" class="wp-block-group">
    <!-- wp:pattern {"slug":"mijnmerk/hero-cta"} /-->
    <!-- wp:pattern {"slug":"mijnmerk/features-row"} /-->
    <!-- wp:pattern {"slug":"mijnmerk/testimonials-row"} /-->
    <!-- wp:pattern {"slug":"mijnmerk/final-cta"} /-->
</main>
<!-- /wp:group -->
<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->
```

### 3.3 Custom templates per pagina

**Doe:** één custom template per belangrijke landingpagina. Zo krijgt
de operator via Pagina , Template de juiste layout automatisch.
Registreer in `theme.json`:
```json
"customTemplates": [
    { "name": "page-diensten", "title": "Pagina , Diensten", "postTypes": [ "page" ] }
]
```

---

## Deel 4 , Block patterns , één per sectie

### 4.1 Pattern file structuur

**Doe:** één PHP-bestand per sectie in `patterns/`. Elk bestand
begint met een header-comment die WordPress leest om het pattern
automatisch te registreren:

```php
<?php
/**
 * Title: Hero CTA
 * Slug: mijnmerk/hero-cta
 * Categories: mjm-hero, featured
 * Description: Homepage hero met headline + CTA + afbeelding.
 * Keywords: hero, banner, intake
 * Viewport Width: 1200
 * Block Types: core/cover
 */
?>
<!-- wp:group {"align":"full"} -->
<div class="wp-block-group alignfull">
    <!-- wp:heading {"level":1} -->
    <h1 class="wp-block-heading">Jouw krachtige headline hier.</h1>
    <!-- /wp:heading -->
</div>
<!-- /wp:group -->
```

### 4.2 Verwijs images via helper

**Doe:** verwijs images via een helper functie die zowel de Media
Library (na install) als de theme directory (fallback) kan
opleveren:

```php
<img src="<?php echo esc_url( MJM_Install::img( 'hero-home' ) ); ?>"
     alt="Ondernemer aan het werk"
     loading="eager"
     fetchpriority="high"
     decoding="async">
```

Zo hoeven patterns nooit aangepast als je image bestanden verandert
, alleen de map in `install.php` update je (zie deel 5.3).

### 4.3 Pattern Overrides (WP 6.8+) waar zinvol

**Doe:** voor de footer + hero + testimonial card , gebruik
`isUserOverrideable: true` op de bewerkbare velden zodat de operator
per plaatsing tekst/afbeelding kan veranderen, maar de structuur
blijft gelocked.

### 4.4 Card grid , gelijke hoogte

**Doe:** voor rijen met kaarten , gebruik in `front.css`:
```css
.wp-block-columns:has(.mjm-card) > .wp-block-column {
    display: flex;
    flex-direction: column;
}
.mjm-card { height: 100%; display: flex; flex-direction: column; }
.mjm-card .wp-block-buttons { margin-top: auto; }
```

Zo krijgen alle kaarten in een rij dezelfde hoogte en zit de CTA
altijd onderaan.

---

## Deel 5 , Auto-install , dé sleutel tot "upload en klaar"

### 5.1 Waarom een install hook

**Doe:** implementeer een `after_switch_theme` hook die op activatie:
1. Alle brand-images uit `assets/images/` sideload naar de Media
   Library.
2. Het logo instelt via `set_theme_mod( 'custom_logo', $id )`.
3. De favicon instelt via `update_option( 'site_icon', $id )`.
4. Automatisch alle vereiste pagina's aanmaakt (Home, Diensten,
   Contact, etc.) met de juiste custom template.
5. De homepage instelt via `show_on_front` + `page_on_front`.
6. De installatie idempotent maakt , een tweede activatie doet
   niets dubbels.

Zonder deze hook moet de operator handmatig 10+ pagina's aanmaken,
templates toewijzen, images uploaden, etc. Met de hook is de site
kant-en-klaar na één klik op "Activeren".

### 5.2 De install hook , praktisch voorbeeld

**Doe:** maak `inc/install.php` met een `MJM_Install` klasse die:
- `after_switch_theme` haakt aan `run_install()`.
- `admin_init` haakt aan `maybe_run_install()` als safety net.
- Een `MAP_OPT` optie bijhoudt met alle attachment-IDs per slug.
- Een `FLAG_OPT` optie stampt met de theme-versie zodat installatie
  eenmalig is per release.

Zie het volledige voorbeeld in
`wp-content/themes/ondernemer-marketing/inc/install.php` op deze
branch , 350 regels, alle helpers erin.

### 5.3 Helper voor image URLs

**Doe:** exposeer `MJM_Install::img( $slug, $size )` die eerst kijkt
of de attachment al in de Media Library staat, en anders fallback
naar de theme directory. Zo werken je patterns direct na activatie
én voor de sideload klaar is.

### 5.4 Auto-create pages , idempotent

**Doe:** kijk of elke pagina al bestaat via `get_page_by_path()`
vóór je hem aanmaakt. Zo kan de hook onbeperkt vaak runnen zonder
duplicates. Gebruik `wp_insert_post()` + `update_post_meta()` voor
`_wp_page_template` om de custom template te binden.

---

## Deel 6 , Custom blocks (alleen voor interactieve widgets)

### 6.1 Wanneer een custom block

**Doe:** bouw een custom block alleen als het element:
- Live gebruikersinput verwerkt (calculator, formulier met live
  preview).
- Berekeningen uitvoert op de client.
- Dynamische state heeft die niet met patterns opgelost kan worden.

Voor alle statische content (hero, cards, testimonials, hero met
CTA) , gebruik een pattern.

### 6.2 block.json , apiVersion 3

**Doe:** volg exact deze structuur:

```json
{
    "$schema": "https://schemas.wp.org/trunk/block.json",
    "apiVersion": 3,
    "name": "mijnmerk/funnel-calculator",
    "title": "Funnel Calculator",
    "category": "widgets",
    "icon": "chart-pie",
    "description": "Bereken conversies + omzet uit je marketingfunnel.",
    "textdomain": "mijnmerk",
    "viewScript": "file:./view.js",
    "render": "file:./render.php",
    "supports": {
        "html": false,
        "align": [ "wide", "full" ],
        "anchor": true
    }
}
```

### 6.3 Server-side render , voor SEO/AEO crawlers

**Doe:** implementeer `render.php` zodat de HTML meteen in de
page-source staat , AI crawlers (OAI-SearchBot, Claude-SearchBot,
PerplexityBot) voeren geen JavaScript uit, dus SSR is verplicht om
inhoud gevonden te krijgen.

### 6.4 view.js , vanilla JavaScript, geen build step

**Doe:** schrijf `view.js` als plain JavaScript , geen React, geen
TypeScript, geen webpack. Attach event listeners op
`DOMContentLoaded`. Gebruik `Intl.NumberFormat( 'nl-NL', ... )` voor
NL locale formatting.

---

## Deel 7 , Security , non-negotiable

### 7.1 Elke PHP file begint met de ABSPATH guard

**Doe:** eerste regel van elk PHP bestand:
```php
<?php
defined( 'ABSPATH' ) || exit;
```

### 7.2 Elke form-handler heeft nonce + capability + sanitize + escape

**Doe:** het volledige patroon in elke admin-post handler:
1. `check_admin_referer( 'mjm_contact', 'mjm_nonce' );`
2. Honeypot check , als hidden field ingevuld is, redirect naar
   success zonder verwerking.
3. Sanitize per veldtype: `sanitize_email()`, `sanitize_text_field()`,
   `sanitize_textarea_field()`. Altijd `wp_unslash()` eerst.
4. Valideer op logica: `is_email( $email )`.
5. Verwerk.
6. PRG (Post-Redirect-Get) via `wp_safe_redirect()` + `exit;`.

### 7.3 Elke output door de juiste escape-functie

**Doe:** kies de escape-functie op basis van context:

| Context | Functie |
|---|---|
| Tekst binnen HTML body | `esc_html( $text )` |
| Attribuut waarde | `esc_attr( $value )` |
| href, src, action | `esc_url( $url )` |
| Rich HTML (post body) | `wp_kses_post( $html )` |
| Binnen `<script>` | `esc_js( $string )` |
| Vertaalde string + escape in één | `esc_html__( 'Save', 'mijnmerk' )` |

### 7.4 HTTP calls via wp_remote_*

**Doe:** gebruik nooit `curl_*`. Altijd `wp_remote_get()` /
`wp_remote_post()` met `is_wp_error()` check en `response_code >= 400`
guard.

### 7.5 API keys nooit in code

**Doe:** lees keys uit `wp-config.php` constants of via de
WordPress Settings API (encrypted at rest via AES-256-CBC met
`AUTH_KEY` als salt).

---

## Deel 8 , Toegankelijkheid , WCAG 2.2 AA

### 8.1 Skip link als eerste focusable element

**Doe:** in `parts/header.html`:
```html
<a class="skip-link screen-reader-text" href="#main-content">Spring naar inhoud</a>
```

Style de skip-link in `front.css` zodat hij verschijnt op focus.

### 8.2 Focus-visible ring op elk interactief element

**Doe:** globale focus-ring (WCAG 2.4.13 vereist ≥2px, ≥3:1 contrast):
```css
a:focus-visible, button:focus-visible, input:focus-visible,
textarea:focus-visible, select:focus-visible, summary:focus-visible {
    outline: 2px solid #1d6bd1 !important;
    outline-offset: 2px !important;
    box-shadow: 0 0 0 4px rgba(29,107,209,0.15) !important;
}
```

### 8.3 Status + alert regions

**Doe:** vertaal Lovable's toasts / notices naar `role` + `aria-live`:
`role="alert"` voor errors (met aria-live="assertive"), `role="status"`
voor successes/info (met aria-live="polite"). Voeg een
screen-reader-only "Error: " / "Success: " prefix toe zodat betekenis
niet alleen van kleur afhangt.

### 8.4 Prefers-reduced-motion

**Doe:** in `front.css` , kill animaties voor gebruikers die
`prefers-reduced-motion: reduce` hebben ingesteld.

### 8.5 Forced-colors mode (Windows High Contrast)

**Doe:** bewaar zichtbaarheid van essentiele elementen via
`@media (forced-colors: active)` overrides met `CanvasText` /
`Canvas` system colors.

### 8.6 lang attribuut + heading hierarchie

**Doe:** zet in WP-admin , Instellingen , Algemeen , Sitetaal op
Nederlands (WordPress voegt `lang="nl-NL"` toe aan `<html>` automatisch).
Gebruik één `<h1>` per pagina en sla geen heading-niveaus over.

---

## Deel 9 , Performance , Core Web Vitals

### 9.1 Images optimaliseren VÓÓR ze in de theme belanden

**Doe:** converteer PNG's naar WebP q80-85. Resize tot maximaal
1200px breed voor heroes, 760px voor cards, 200px voor avatars.
Voor OndernemerMarketing.nl bespaarde dit 90% (4.4 MB , 477 KB).

Python one-liner met PIL:
```python
from PIL import Image
im = Image.open('hero.png').convert('RGB')
if im.width > 1200:
    im = im.resize((1200, int(1200 * im.height / im.width)), Image.LANCZOS)
im.save('hero.webp', 'WEBP', quality=82, method=6)
```

WebP wordt door WordPress 5.8+ ondersteund én door 96%+ van de
browsers. Voor logos met transparantie: houd PNG (optimize).

### 9.2 Fetchpriority op LCP image

**Doe:** de hero-image (Largest Contentful Paint) krijgt:
```html
<img src="..." alt="..." loading="eager" fetchpriority="high"
     decoding="async" width="1200" height="675">
```

Alle images onder de fold: `loading="lazy"`. Altijd width+height zetten
(voorkomt Cumulative Layout Shift).

### 9.3 Per-block CSS

**Doe:** in plaats van één grote `front.css` met alle block styles ,
splits per block via `wp_enqueue_block_style()` (zie 2.5). Alleen als
`core/cover` op de pagina staat wordt `core-cover.css` geladen.

### 9.4 Caching plugin

**Doe:** voor productie , installeer WP Rocket of LiteSpeed Cache.
Page-level HTML caching brengt TTFB van 400ms naar 40ms wat direct
LCP verbetert.

---

## Deel 10 , SEO / GEO / AEO infrastructure

### 10.1 JSON-LD schema

**Doe:** in `inc/seo.php` , emit `Organization` + `WebSite`
site-breed, `Article` op single posts, `FAQPage` als er `<details>`
blocks in de content staan. Detect andere SEO plugins (Yoast, Rank
Math, AIOSEO, SEOPress, TSF) en stand down als er al één actief is:
```php
if ( defined( 'WPSEO_VERSION' ) ) return; // Yoast is aan
```

### 10.2 Open Graph + Twitter Card + canonical

**Doe:** minimaal deze meta-tags per pagina: `description`,
`canonical`, `og:type`, `og:title`, `og:description`, `og:image`,
`og:locale=nl_NL`, `twitter:card=summary_large_image`.

### 10.3 robots.txt , 3-tier AI crawler beleid

**Doe:** via `robots_txt` filter uitbreiden:
- **Toestaan** (brengt AI-citaties): `OAI-SearchBot`,
  `Claude-SearchBot`, `PerplexityBot`, `Google-Extended`,
  `Applebot-Extended`.
- **Weigeren** (bulk training scrapers): `GPTBot`, `CCBot`,
  `anthropic-ai`, `ClaudeBot`.

### 10.4 llms.txt op site root

**Doe:** creëer een rewrite rule + template_redirect handler die een
Markdown samenvatting van je diensten + prijzen serveert op
`/llms.txt`. Deze wordt door ChatGPT / Claude / Perplexity gebruikt
voor snelle site-context.

### 10.5 IndexNow auto-ping

**Doe:** op elke `transition_post_status` naar publish , POST de URL
naar `api.indexnow.org` zodat Bing/Yandex/Naver de nieuwe pagina
binnen minuten indexeren.

---

## Deel 11 , Contactformulier

### 11.1 Native WordPress admin-post, geen externe form-plugin

**Doe:** implementeer het formulier zelf via `admin_post_*` +
`admin_post_nopriv_*`. Externe plugins (Contact Form 7, WPForms,
Gravity) voegen bloat + eigen JS toe wat je Core Web Vitals score
schaadt. Voor een marketing landing page is een self-built form
zowel lichter als beter te controleren.

Zie de complete `inc/contact-form.php` op deze branch als voorbeeld
(70 regels, werkt volledig).

### 11.2 SMTP voor productie

**Doe:** installeer op de live host de plugin **WP Mail SMTP** en
koppel aan Brevo / SendGrid / Postmark. `wp_mail()` alleen werkt
zelden betrouwbaar op shared hosting.

### 11.3 Honeypot voor spam

**Doe:** voeg een verborgen input toe die alleen bots invullen:
```html
<input type="text" name="mjm_hp" tabindex="-1" autocomplete="off"
       style="position:absolute;left:-9999px" aria-hidden="true">
```
In de handler , als `$_POST['mjm_hp']` niet leeg is, doe alsof het
gelukt is (redirect naar success) zonder de e-mail te versturen. Zo
weten bots niet dat ze zijn afgevangen.

---

## Deel 12 , AVG + juridisch voor NL sites

### 12.1 Drie juridische pagina's minimaal

**Doe:** ship deze drie pagina's met de theme (auto-created via
install hook):
1. **Privacybeleid** , AVG-conform , welke gegevens, doel,
   bewaartermijn, delen met derden (Brevo verwerkersovereenkomst),
   cookies, rechten (inzage, correctie, verwijdering).
2. **Algemene voorwaarden** , 7 artikelen minimaal , definities,
   toepasselijkheid, looptijd + opzegging, tarieven, aansprakelijkheid,
   vertrouwelijkheid, toepasselijk recht.
3. **Disclaimer** , gebruik op eigen risico, externe links,
   intellectueel eigendom.

Voeg boven aan elke pagina een `<em>Deze tekst is een Nederlandse
boilerplate. Laat juridisch reviewen voordat je live gaat.</em>`
notice toe zodat de operator dit niet vergeet.

### 12.2 KvK nummer

**Doe:** verwerk het KvK-nummer in de footer én in de privacypolicy.
Verplicht voor NL ondernemingen die commerciële diensten aanbieden.

### 12.3 Cookie consent

**Doe:** installeer Klaro (GPL, gratis, self-hostable) of Cookiebot
op de live site voordat je analytics of tracking cookies inschakelt.
AVG-verplicht voor tracking cookies; niet nodig voor puur functionele
cookies.

---

## Deel 13 , Testen voor je zipt

### 13.1 PHP lint elke file

**Doe:**
```bash
find wp-content/themes/mijnmerk -name "*.php" -exec php -l {} \;
```
Verwacht: geen enkele syntax error.

### 13.2 JSON validate

**Doe:**
```bash
python3 -c "import json,glob; [json.load(open(f)) for f in ['wp-content/themes/mijnmerk/theme.json'] + glob.glob('wp-content/themes/mijnmerk/blocks/*/block.json')]; print('OK')"
```

### 13.3 Verse WordPress install

**Doe:** installeer WordPress lokaal (LocalWP, Docker, of Studio by
WordPress.com) , WordPress 6.7+, PHP 8.1+. Upload je zip via
Weergave , Thema's , Thema uploaden , Activeren. Verifieer:
- Homepage rendert met alle patterns + real images.
- Alle 10+ pagina's zijn automatisch aangemaakt.
- Logo staat in de header.
- Contact-formulier POST'ed en verstuurt.
- Tools-page interactieve widgets werken.

### 13.4 axe-core + Lighthouse

**Doe:** run in Chrome DevTools:
- **Lighthouse mobile** , streef naar Performance ≥ 90, Accessibility
  = 100, SEO ≥ 95, Best Practices ≥ 95.
- **axe-core** (via de Chrome extensie) , 0 serious + 0 critical
  + 0 moderate violations.

### 13.5 Schema.org validator

**Doe:** plak elke pagina-URL in <https://validator.schema.org> en
in Google's Rich Results Test. Zorg dat Article + FAQPage +
Organization schema slagen.

### 13.6 Real-device check

**Doe:** open de site op een echte iPhone (Safari) en Android
(Chrome). Test:
- Mobiel menu opent + sluit.
- Body scroll wordt gelocked als menu open is.
- Alle CTA's zijn tapable (minimum 44x44 px hit target, WCAG 2.5.8).
- Tekst is leesbaar zonder pinchen.

---

## Deel 14 , Ship , de zip

### 14.1 Zip structuur

**Doe:** zip zo dat er binnen de zip één directory met de theme-slug
zit. Commando:
```bash
cd wp-content/themes && \
zip -rq mijnmerk.zip mijnmerk \
    -x "*.DS_Store" "mijnmerk/.git/*" "mijnmerk/node_modules/*"
```

### 14.2 Versioning , 3 markers gelijk houden

**Doe:** bij elke release update je in 3 plekken tegelijk (semver):

| Bestand | Waar |
|---|---|
| `style.css` | `Version: 1.2.0` regel |
| `functions.php` | `const MJM_THEME_VERSION = '1.2.0';` |
| Zip filename | `mijnmerk-1.2.0.zip` |

Als deze uit sync raken breekt de update-detectie in WP-admin.

### 14.3 Versioned + rolling zip

**Doe:** ship altijd twee bestanden , de rolling `mijnmerk.zip`
(zelfde naam elke release) én de versioned `mijnmerk-1.2.0.zip`.

### 14.4 Verifieer versie IN de zip

**Doe:** vóór je de zip deelt:
```bash
unzip -p mijnmerk.zip mijnmerk/style.css | grep Version
unzip -p mijnmerk.zip mijnmerk/functions.php | grep MJM_THEME_VERSION
```
Beide moeten dezelfde versie tonen.

---

## Deel 15 , Post-launch , wat je live nog moet doen

### 15.1 In WP-admin na theme activatie

**Doe:**
1. **Instellingen , Algemeen** , controleer Sitetaal (Nederlands),
   Tijdzone (Europe/Amsterdam), Website-URL, Site-titel.
2. **Instellingen , Permalinks** , kies "Berichtnaam" (SEO-friendly).
3. **Instellingen , Lezen** , controleer dat de homepage op de juiste
   pagina staat (install-hook heeft dit al gedaan maar verifieer).
4. **Weergave , Editor , Navigatie** , controleer dat alle menu-items
   naar de juiste URLs verwijzen.
5. **Gebruikers , Profiel** , upload je admin-avatar (Gravatar).

### 15.2 Plugins die je live nodig hebt

**Doe:** installeer minimaal:
- **WP Mail SMTP** , voor betrouwbare mail delivery
- **LiteSpeed Cache** of **WP Rocket** , voor Core Web Vitals
- **Klaro** of **Cookiebot** , voor cookie consent
- **Wordfence** of **Sucuri** , voor security (2FA + firewall)
- Optioneel: **Polylang** of **WPML** voor meertalige versies
- Optioneel: **Yoast SEO** of **Rank Math** , maar alleen als je
  meer nodig hebt dan wat het theme al levert (theme staat automatisch
  down als deze actief zijn)

### 15.3 DNS + SSL

**Doe:**
1. Domein DNS A/AAAA-record naar server IP.
2. `www` subdomain als CNAME naar apex.
3. Let's Encrypt SSL via hoster of Cloudflare Flexible SSL.
4. In WP-admin , Instellingen , Algemeen , force HTTPS in beide
   URL-velden.

### 15.4 Verificatie na go-live

**Doe:**
- **Google Search Console** , voeg property toe, submit sitemap.
- **Bing Webmaster Tools** , IndexNow key registreren als het theme
  hem heeft aangemaakt.
- **Facebook Sharing Debugger** , scrape de homepage-URL, verifieer
  og:image + og:title.
- **Google Rich Results Test** per belangrijke pagina.
- **PageSpeed Insights mobile** , streef naar groen (LCP ≤ 2.5s,
  INP ≤ 200ms, CLS ≤ 0.1).

---

## Deel 16 , Onderhoud , continue verbetering

### 16.1 Elke release een changelog

**Doe:** in de theme root of README.md , bewaar changelog:
```
= 1.2.0 =
* PERF: images geconverteerd naar WebP (90% kleinere zip)

= 1.1.0 =
* NEW: bundled brand images + auto-install hook
* NEW: 3 custom blocks voor Tools pagina

= 1.0.0 =
* Initial release.
```

### 16.2 Semver bumping regel

**Doe:** volg semver strak:
- `feat:` commits , minor bump (1.2.0 , 1.3.0)
- `fix:` of `perf:` of `chore:` , patch bump (1.2.0 , 1.2.1)
- Breaking change , major bump (1.2.0 , 2.0.0)

### 16.3 Elke 3 maanden Lighthouse check

**Doe:** WordPress core update kan performance beïnvloeden.
Elke kwartaal:
1. Update WordPress + plugins.
2. Run Lighthouse mobile op homepage + top 3 landing pages.
3. Als scores gezakt zijn , investigate en fix.

### 16.4 KvK / adres / contact wijzigingen

**Doe:** houd deze 3 plekken in sync:
- `parts/footer.html` (adres + KvK + telefoon)
- `inc/seo.php` PostalAddress schema
- Privacybeleid + Algemene voorwaarden pagina content

---

## Samenvatting , de gouden regel

**Doe:** volg dit proces in strikte volgorde:

1. **Design tokens** eerst , kleuren + typografie + spacing in
   `theme.json`.
2. **Patterns** , één per sectie uit de screenshots.
3. **Templates** , die de patterns compose'n.
4. **Custom blocks** alleen als interactiviteit dat vereist.
5. **Install hook** , auto-images, auto-logo, auto-pages.
6. **SEO/GEO/AEO** infra , JSON-LD, robots, llms.txt, IndexNow.
7. **Contact form** , native admin-post handler.
8. **Accessibility** , skip link, focus-visible, aria-live, sr-only.
9. **Performance** , WebP + fetchpriority + per-block CSS.
10. **Security** , ABSPATH + nonce + cap + sanitize + escape + prefix.
11. **Test** , PHP lint + JSON validate + Lighthouse + axe.
12. **Ship** , versioned zip + rolling zip.

Volg deze 12 stappen en je Lovable-conversie is een
production-grade WordPress custom theme. Referentie-implementatie
op deze branch , `wp-content/themes/ondernemer-marketing/`.

---

## Referenties

- **WordPress Theme Handbook**: <https://developer.wordpress.org/themes/>
- **Block Editor Handbook** (theme.json v3): <https://developer.wordpress.org/block-editor/reference-guides/theme-json-reference/>
- **WCAG 2.2 AA** (Make WP Accessible, mei 2026): <https://make.wordpress.org/accessibility/2026/05/06/accessibility-ready-requirements-updated/>
- **Core Web Vitals 2026 guide**: <https://www.corewebvitals.io/core-web-vitals/wordpress-guide>
- **Deze branch's referentie theme**: `wp-content/themes/ondernemer-marketing/`
- **Verwante docs op deze branch**:
  - `WORDPRESS_SITE_REBUILD_GUIDE.md` , abstract patroon
  - `CUSTOM_THEME_RESEARCH_2026.md` , 2026 policy research
  - `ONDERNEMERMARKETING_BUILD_PLAN.md` , dit project's spec

---

*Einde gids. Laat weten wanneer je de eerstvolgende Lovable-naar-
WordPress conversie start , dan kunnen we het proces meten en de
gids updaten op basis van wat we leren.*
