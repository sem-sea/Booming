# Van Vercel-URL naar WordPress , de learnings

> **Doel:** een deployed React/Next.js applicatie op Vercel omzetten
> naar een volwaardig WordPress block theme dat je klant zelf kan
> beheren via wp-admin. Uploadable zip, ready to ship.
>
> **Deze gids beschrijft alleen wat je WEL moet doen.** Elke regel
> is een concrete stap of best practice, positief geframed.
>
> **Verified against 2026 web research (September 2026):** de
> dominante 2026-trend is WordPress , Vercel (headless). Deze gids
> gaat over de omgekeerde route , Vercel , WordPress , die
> zeldzamer is maar heel legitieme use-cases heeft (zie deel 1).
>
> **Gebaseerd op:** de OndernemerMarketing.nl conversie (Lovable
> export , WP theme, 7 sessies, v1.2.0 shipped) plus de
> Vercel/Next.js-specifieke patronen die daarna zijn geleerd.
>
> **Sister docs op deze branch:**
> - `LOVABLE_NAAR_WORDPRESS_GIDS.md` , specifieke Lovable-workflow.
> - `WORDPRESS_SITE_REBUILD_GUIDE.md` , abstract WP-theme pattern.
> - `CUSTOM_THEME_RESEARCH_2026.md` , 2026 WP-theme policy research.
> - `CLAUDE_CODE_ALGEMENE_GIDS.md` , algemene Claude Code werkwijze.

---

## Deel 1 , Waarom je Vercel , WordPress zou willen

### 1.1 Legitieme use-cases

**Doe:** overweeg Vercel , WordPress als een van deze redenen speelt:

- **Non-tech content editors** , je klant is content-team, niet
  developer. WordPress admin (Gutenberg, categorieen, media library)
  is de meest bekende CMS ter wereld. React-app updates vereisen een
  developer.
- **Plugin ecosystem** , je klant wil later een form-builder,
  webshop (WooCommerce), membership (MemberPress), booking, of
  meertaligheid (Polylang/WPML) toevoegen zonder de site opnieuw
  te bouwen.
- **Kosten** , Vercel Pro is $20/mo per seat + edge function usage.
  Cheap shared hosting met WP kost $5/mo. Voor een marketing site
  is $180/jaar besparing reeel.
- **Content editorial workflow** , WP heeft revisies, draft/publish,
  scheduled posts, editorial roles native. Next.js apps hebben dit
  niet zonder headless CMS bovenop.
- **SEO plugins** , Yoast, Rank Math, AIOSEO doen een specifieke
  soort SEO-werk dat je in Next.js zelf moet bouwen.
- **Legacy compatibility** , als de klant al andere WP sites heeft
  en het staff het al kent, uniformiteit is een reeel voordeel.
- **Self-hostable eindstaat** , op eigen server, geen platform
  lock-in bij Vercel.

### 1.2 Wanneer je BETER kunt heroverwegen

**Doe:** eerst controleren of de Vercel-app werkelijk moet
verhuizen. Alternatieven om te overwegen:

- **Headless WordPress + Vercel** , de dominante 2026-architectuur.
  WordPress als CMS, Next.js als frontend op Vercel. Beste van beide.
  Zie <https://vercel.com/templates/next.js/nextjs-wordpress-headless-cms>.
- **Vercel + Sanity/Contentful** , als het content-editorial issue
  is en de team open staat voor een moderne headless CMS.
- **Vercel houden + editor-panel bouwen** , een custom admin
  in de Next.js-app zelf met bijvoorbeeld TinaCMS.
- **WordPress.com Business** , voor een klant die geen technische
  overhead wil, no-code hosted WP oplossing.

**Doe:** presenteer deze alternatieven aan de klant met kosten +
learning curve trade-offs. Vaak is de "converteer naar WP"
beslissing minder rationeel dan het lijkt.

### 1.3 Wanneer JA doen

**Doe:** ga door met Vercel , WordPress als:

1. De klant duidelijk WP admin wil (heeft ervaring met WP, of
   content-team is klaar).
2. De site is redelijk statisch (marketing pagina's, blog,
   pakketten, contact) , geen zwaar dynamische SPA-functionaliteit.
3. Kostenverschil is reeel en aanhoudend.
4. Er is geen bestaande WordPress-installatie waar dit een
   sub-optimaal deel van wordt (dus niet: converteer Vercel-app
   naar WP en dan blijkt: klant heeft al drie WP sites).

---

## Deel 2 , Wat je krijgt uit een Vercel deployment

### 2.1 Bronnen van waarheid

**Doe:** verzamel deze 5 bronnen voordat je begint met bouwen:

1. **De live Vercel URL** , `https://klant.vercel.app` of custom
   domein. Dit is de VISUELE bron van waarheid.
2. **Screenshots** , maak per route/pagina een full-page screenshot,
   desktop + mobile. Playwright of Puppeteer:
   ```bash
   npx playwright install
   # Simpel Node script om alle sitemap URLs te screenshoten
   ```
3. **De GitHub repo** , als je die toegang hebt. Bevat de complete
   source code van de Next.js/React app. Design tokens (Tailwind
   config), copy, images, en API-routes staan hierin.
4. **Vercel dashboard access** , environment variables, build logs,
   analytics, domain-mapping. Log in als teamlid en check de
   Project Settings.
5. **Sitemap + robots.txt** , op de root van de deployment. Geven
   je een lijst van alle publiek toegankelijke URLs.

### 2.2 Design tokens uit de deployed app halen

**Doe:** open de live Vercel-URL in Chrome + DevTools:

- **Elements , Computed** op een `<h1>` , lees de exacte font-family,
  font-size, font-weight, line-height, kleur.
- **Elements , Computed** op de primary CTA button , border-radius,
  background-color, hover state.
- **CSS Overview panel** (in DevTools , Rendering , CSS Overview) ,
  automatische samenvatting van gebruikte kleuren + fonts + text-
  styles op de hele site.
- **Network , Fonts filter** , welke woff2 files worden geladen.
  Download ze en host lokaal (GDPR safe, no Google Fonts CDN).

### 2.3 Content extractie via public rendering

**Doe:** als je geen source code toegang hebt , scrape via de
publieke URL. Playwright script voorbeeld:

```javascript
const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage();
  const urls = ['/', '/diensten', '/pakketten', '/contact']; // uit sitemap.xml
  for (const url of urls) {
    await page.goto('https://klant.vercel.app' + url, { waitUntil: 'networkidle' });
    const html = await page.content();
    const title = await page.title();
    const meta = await page.$eval('meta[name="description"]', el => el.content).catch(() => '');
    require('fs').writeFileSync(`extract${url.replace(/\//g, '_')}.html`, html);
    await page.screenshot({ path: `screenshot${url.replace(/\//g, '_')}.png`, fullPage: true });
  }
  await browser.close();
})();
```

Playwright rendert de app zoals de browser dat doet, inclusief
client-side rendered content (React hydration). Puppeteer werkt
identiek.

### 2.4 Images downloaden

**Doe:** Vercel serveert images via de Vercel Image Optimization
CDN (`/_next/image?url=...`). Zoek in de gerenderde HTML naar
`<img src="/_next/image?url=..."` en download de originele images:

```bash
grep -oE 'src="/_next/image\?url=[^"]+"' extract.html | \
  sed 's|src="/_next/image?url=||; s|&.*||; s|%2F|/|g' | \
  while read url; do wget "https://klant.vercel.app$url"; done
```

Of via Chrome DevTools , Sources , Filesystem , download hero
images + team foto's per klik.

Optimize daarna direct naar WebP q80-85 en resize tot max 1200px
breed. Zie deel 9 van `LOVABLE_NAAR_WORDPRESS_GIDS.md` voor de
Python one-liner.

### 2.5 Route-inventarisatie

**Doe:** maak een tabel per URL uit sitemap.xml met:
- Path (`/`, `/diensten`, `/pakketten/[slug]`)
- Titel + meta description
- OG image URL
- Type (statisch, dynamisch, API, redirect)
- WordPress destination (page slug, custom post type, custom template)

Voorbeeld:

| Vercel URL | WP-equivalent | Type |
|---|---|---|
| `/` | Voorpagina (`front-page.html`) | statisch |
| `/diensten` | Pagina `diensten` + `page-diensten` template | statisch |
| `/pakketten/[slug]` | Custom post type `pakket` | dynamisch |
| `/api/contact` | admin-post handler `mjm_contact` | server |
| `/tools/roi-calculator` | Custom block `roi-forecaster` in page | interactief |

Deze mapping is je bouwplan.

---

## Deel 3 , Design tokens en visueel systeem

### 3.1 Kleuren extraheren

**Doe:** gebruik de Chrome DevTools "CSS Overview" panel. Onder
Colors zie je elke gebruikte kleur + hoe vaak. Neem de top 8-12
mee als je `theme.json` palette. Categoriseer:

- **Primary** , de dominante brand-kleur (buttons, links)
- **Accent** , secundaire brand-kleur (highlights, icons)
- **Ink** , tekst
- **Body** , licht-tekst
- **Muted** , helper-tekst
- **Soft** , licht-achtergrond
- **Border** , dividers, cards

**Doe:** controleer contrast op wit met <https://webaim.org/resources/contrastchecker/>:

- Tekst: WCAG AA vereist 4.5:1.
- UI-componenten (borders, focus rings): 3:1.
- Als de accent kleur onder 4.5:1 zakt , clamp tot iconen +
  decoratieve accenten, gebruik ink voor lopende tekst.

### 3.2 Typografie

**Doe:** identificeer het font stack via computed styles:

```css
font-family: "Inter", ui-sans-serif, system-ui, ...;
```

Download de woff2 files (bijvoorbeeld via
<https://gwfh.mranftl.com/fonts>) en registreer via `fontFace` in
`theme.json`:

```json
"typography": {
    "fontFamilies": [{
        "fontFamily": "Inter, system-ui, sans-serif",
        "slug": "inter",
        "name": "Inter",
        "fontFace": [
            { "fontFamily": "Inter", "fontWeight": "400", "src": [ "file:./assets/fonts/inter-400.woff2" ] },
            { "fontFamily": "Inter", "fontWeight": "600", "src": [ "file:./assets/fonts/inter-600.woff2" ] },
            { "fontFamily": "Inter", "fontWeight": "700", "src": [ "file:./assets/fonts/inter-700.woff2" ] }
        ]
    }]
}
```

Waarom lokaal hosten: het Duitse LG Munchen-arrest (jan 2022, nog
steeds geldig 2026) verbiedt Google Fonts CDN zonder consent onder
AVG. Voor een NL/EU commerciele site is dit verplicht.

### 3.3 Spacing en layout

**Doe:** in DevTools , inspecteer een sectie-container en lees de
`padding` + `max-width`. Meestal zie je:
- `max-width: 1200px` , dit wordt `wideSize` in theme.json.
- `max-width: 720px` op content-blokken , dit wordt `contentSize`.
- `padding: 4rem 1rem` op secties , spacingScale step voor `4rem`.

---

## Deel 4 , Bouw het WordPress block theme

### 4.1 Volg de bestaande blueprints

**Doe:** de theme-structuur, patterns aanpak, auto-install hook,
security, WCAG en performance patronen zijn identiek aan de
Lovable , WordPress workflow. Lees:

- `LOVABLE_NAAR_WORDPRESS_GIDS.md` , delen 2-14 (skelet, patterns,
  auto-install, security, WCAG, performance, SEO, contact, AVG,
  testen, ship).
- `WORDPRESS_SITE_REBUILD_GUIDE.md` , abstract pattern.
- `CUSTOM_THEME_RESEARCH_2026.md` , 2026 WCAG 2.2 AA + WP 6.7 policy.

Deze gids beschrijft alleen wat **anders** is voor de Vercel-route.

### 4.2 Referentie-implementatie

**Doe:** kopieer de structuur van
`wp-content/themes/ondernemer-marketing/` als startpunt. Rename
prefixen (`ONDM_` , je eigen 4+ letter prefix), pas `theme.json`
tokens aan naar de nieuwe brand.

---

## Deel 5 , Wat WEL bruikbaar is uit de Next.js source

### 5.1 Copy (teksten)

**Doe:** grep de repo voor tekstblokken:
```bash
grep -rE "<h1|<h2|<p" src/ | head -100
```

Kopieer de teksten 1:1 in je WordPress patterns. Als de teksten
Nederlands zijn en de site meertalig moet worden , wrap in `__()`.

### 5.2 Layout structuur

**Doe:** noteer per Next.js component welke WP-blocks het vervangen:
- `<Hero>` , core/cover of core/group met heading + button.
- `<Features grid>` , core/columns met kaarten.
- `<Pricing>` , custom pattern met vergelijkingstabel.
- `<Testimonials>` , core/columns met quote cards.
- `<CTA>` , core/group met heading + button.
- `<Footer>` , template part `parts/footer.html`.

### 5.3 Design tokens (Tailwind config)

**Doe:** open `tailwind.config.ts` en zet de `theme.colors`,
`fontFamily`, `spacing` extensies over naar `theme.json`. Direct
1:1 mapbaar.

### 5.4 Images en icons

**Doe:** kopieer alle bestanden uit `public/` naar
`wp-content/themes/<slug>/assets/images/`. Vergeet niet:
- `favicon.ico` , naar theme root.
- `og-image.png` , als default og-image in `inc/seo.php`.
- Static images uit `public/images/` , converteer naar WebP.

---

## Deel 6 , Wat NIET rechtstreeks overkomt

### 6.1 Vercel-specifieke features

**Doe:** vertaal deze Vercel/Next.js concepten naar
WordPress-equivalents:

| Vercel/Next.js | WordPress equivalent |
|---|---|
| `getStaticProps` / SSG | Statische pagina met vaste content in block markup |
| `getServerSideProps` / SSR | PHP template die dynamisch rendert (dit is default WP) |
| ISR (Incremental Static Regeneration) | Caching plugin (WP Rocket / LiteSpeed) + revalidate on publish |
| API routes (`/pages/api/*`) | `admin-post.php` handlers (`admin_post_{action}`) |
| Middleware (auth check) | WordPress capability checks + `current_user_can()` |
| Edge Functions | Niet direct; gebruik WP cron of admin-post |
| Environment variables | `wp-config.php` constants of encrypted `wp_options` |
| Vercel KV | WordPress transients (`set_transient()`) |
| Vercel Blob | WordPress Media Library |
| Vercel Analytics | Plausible, Fathom, of een WP analytics plugin |
| Vercel Cron | WP-Cron of `wp_schedule_event()` |
| `next/image` | Native `<img loading="lazy">` + Media Library thumbnails |

### 6.2 Client-side state en React hooks

**Doe:** voor puur presentatie: patterns + native HTML. Voor
interactiviteit: custom blocks met vanilla JS (`view.js`).

Voor complexe state (multi-step form, wizard, dashboard) , overweeg
of dit werkelijk in WordPress moet. Alternatief: houd de app
gedeeltelijk op Vercel als een iframe of subdomain (`app.klant.nl`
op Vercel, `www.klant.nl` op WordPress).

### 6.3 Vercel Edge Middleware auth

**Doe:** als de app een gated area heeft (`/dashboard`, `/portal`):

- Voor eenvoudige login: WordPress users + capabilities.
- Voor lidmaatschap: MemberPress of Paid Memberships Pro plugin.
- Voor SaaS-achtige gated features: overweeg WordPress als
  marketing site + de app blijven laten draaien op Vercel als
  `/app/` subdomein of iframe.

### 6.4 Environment variables + secrets

**Doe:** log in op Vercel dashboard , Project , Settings ,
Environment Variables. Screenshot / kopieer de VARIABLE NAMES (niet
de values , scan de values voor keys en rotate ze zo nodig).

Migreer naar `wp-config.php`:
```php
define( 'MJM_BREVO_KEY', 'nieuwe-key-hier' );
define( 'MJM_STRIPE_KEY', 'nieuwe-stripe-key' );
```

En lees in je theme:
```php
$key = defined( 'MJM_BREVO_KEY' ) ? MJM_BREVO_KEY : '';
```

**Doe:** rotate elke API key die je in de Vercel repo of dashboard
vindt. Migratie is een goed moment om security hygiene te resetten.

---

## Deel 7 , Route mapping in de praktijk

### 7.1 Statische pagina's

**Doe:** voor elke Next.js `/pages/{route}.tsx` (of App Router
`/app/{route}/page.tsx`), maak een WordPress pagina + custom
template:

- `/diensten` , WP pagina `diensten` + `templates/page-diensten.html`.
- `/pakketten` , WP pagina `pakketten` + `templates/page-pakketten.html`.
- `/over-ons` , WP pagina `over-ons` + `templates/page-over-ons.html`.

Registreer per landingpagina in `theme.json.customTemplates` zodat
de operator hem in de Page sidebar kan toewijzen.

### 7.2 Dynamische routes met slug-content

**Doe:** voor `/blog/[slug]` , WordPress heeft dit al ingebouwd:
- Ontlast de `/blog` route naar de standaard blog archive.
- Elke blog-post is een WP `post` met de juiste slug.
- Content porteren via WXR (WordPress eXtended RSS) of via de REST
  API met een `wp_insert_post()` script.

### 7.3 Dynamische routes met custom entiteiten

**Doe:** voor `/pakketten/[slug]` (elk pakket is een aparte entiteit):
- Registreer een custom post type `pakket` via
  `register_post_type()` in `functions.php`.
- Rewrite rules: `/pakketten/[slug]` , custom post type single.
- Fields via native custom fields of ACF (Advanced Custom Fields).

### 7.4 API routes

**Doe:** voor elke `/pages/api/{name}.ts`:
- Als het een form-handler is , WordPress `admin-post.php` +
  `admin_post_mjm_{name}` handler.
- Als het een read-only endpoint is voor de frontend , WordPress
  REST API: `register_rest_route()` in `inc/rest.php`.
- Als het een cron-job is , `wp_schedule_event()` met een custom
  action.

---

## Deel 8 , Content migratie

### 8.1 Als er een headless CMS gebruikt werd

**Doe:** als de Vercel-app data uit Sanity / Contentful / Strapi /
Airtable haalde , exporteer daar direct + importeer naar WP:

- Sanity: `sanity dataset export production` , JSON. Transform
  naar WXR met een Python script + `wp_insert_post()` via REST.
- Contentful: `contentful space export` , JSON.
- Airtable: API export , CSV , WXR.

### 8.2 Als de content in de code stond

**Doe:** als de content hardcoded in TSX-bestanden stond (Lovable
of v0 style) , kopieer 1:1 naar block patterns of naar post-content
via WXR.

### 8.3 Als de content in een database zat

**Doe:** dump de bestaande database, transform naar WXR:
```bash
pg_dump -t posts klant_db | ...
python3 -c "import wxr_generator; ..."
```

### 8.4 Blog-content overzetten

**Doe:** voor elke blog-post op de Vercel-site:
1. Fetch de rendered HTML via Playwright.
2. Extract title + date + author + body via BeautifulSoup.
3. Transform body-HTML naar Gutenberg-block markup (of houd het als
   klassiek HTML in `<!-- wp:html -->` blocks).
4. Insert via `wp_insert_post()` of via WXR import.

### 8.5 Featured images

**Doe:** download alle Vercel-optimized images, converteer naar
WebP, importeer via `media_sideload_image()` per post. Koppel als
featured image via `set_post_thumbnail()`.

---

## Deel 9 , SEO overdracht , kritiek voor rankings

### 9.1 Redirect map maken

**Doe:** dit is de belangrijkste stap. Elke URL die op Vercel
publiek is moet een `301 Permanent Redirect` krijgen naar de nieuwe
WP-URL. Anders verlies je rankings.

- Extract elke URL uit `sitemap.xml`.
- Bepaal de nieuwe URL (zelfde structuur als mogelijk , bijvoorbeeld
  `/diensten` blijft `/diensten`).
- Bij URL-changes (`/services` , `/diensten`) , 301 redirect.

Voer de redirects uit via:
- **.htaccess** (Apache) met `RewriteRule`.
- **nginx.conf** (nginx).
- **Redirection plugin** in WordPress voor management via wp-admin.

### 9.2 Meta tags en Open Graph

**Doe:** per pagina in WordPress:
- Meta title en description overnemen uit de Vercel-versie.
- Open Graph image (`og-image.png`) importeren naar Media Library.
- Als je `inc/seo.php` gebruikt (uit de Lovable-gids), zet deze in
  post_meta of laat het theme automatisch genereren.

### 9.3 Structured data

**Doe:** vergelijk welke schema.org types de Vercel-site emit
(inspecteer `<script type="application/ld+json">` in view-source).
Zorg dat je WP `inc/seo.php` dezelfde types genereert:
- `Organization` + `WebSite` , site-breed.
- `Article` op posts.
- `FAQPage` waar `<details>` blocks staan.
- `Service` op pakketten.
- `BreadcrumbList` op subpaginas.

### 9.4 Sitemap.xml

**Doe:** WordPress emit standaard een `/wp-sitemap.xml`. Als de
Vercel-site had `/sitemap.xml` (custom) , zorg dat je Yoast of
Rank Math installeert die dat pad ook serveert, OF configureer een
redirect naar `/wp-sitemap.xml`.

### 9.5 Google Search Console

**Doe:** na de switch:
1. Verifieer de WP-versie in Search Console.
2. Submit de nieuwe sitemap.
3. Check "Coverage" , kijk of alle URLs terugkomen.
4. Monitor "Manual Actions" en klachten van Googlebot.

Tips: doe de switch buiten piektijden. Gebruik een canary window
van 48u waarin je Vercel en WP allebei laat draaien op verschillende
subdomeinen om te vergelijken.

---

## Deel 10 , Wanneer hybride beter is

### 10.1 Hybrid stack , mijn 2026 favoriet

**Doe:** overweeg deze split:
- **Marketing site** op WordPress (`www.klant.nl`) , homepage,
  blog, diensten, pakketten, contact. Alles waar content-editing
  belangrijk is.
- **App / dashboard** op Vercel (`app.klant.nl`) , interactieve
  functies, gated area, calculators, kalkulators.

Zo krijg je het beste van beide werelden en hoef je niet elke
Vercel-feature 1:1 na te bouwen in WordPress.

### 10.2 Headless als je Vercel wilt houden

**Doe:** als na analyse blijkt dat Vercel infra behouden zou moeten
blijven, ga voor **headless WordPress**:
- WP als CMS op cheap shared hosting of Kinsta.
- Next.js op Vercel als frontend, fetcht content via WPGraphQL.
- ISR / revalidate-on-publish voor snelheid + fresh content.
- Beste voor 2026 volgens de web-search resultaten.

Reference: <https://vercel.com/templates/next.js/nextjs-wordpress-headless-cms>.

---

## Deel 11 , Praktische conversie-workflow

### 11.1 De stappen op volgorde

**Doe:** volg deze 15 stappen voor elke Vercel , WordPress
migratie:

1. **Analyse** , inventaris routes, tokens, images, environment
   vars, secrets.
2. **Backup Vercel-site** , screenshots per URL, source code fork.
3. **Beslissing** , volledige migratie, hybride split, of headless
   WP. Presenteer aan de klant met trade-offs.
4. **Rotate secrets** , alle API-keys die je in Vercel dashboard
   of repo vindt, meteen intrekken en nieuwe genereren.
5. **Bouw WP block theme skelet** , volg de Lovable-gids sections
   2-3 (style.css, theme.json, functions.php, minimal).
6. **Design tokens** , kleuren + typografie + spacing in
   `theme.json`.
7. **Patterns** , een per sectie uit de screenshots.
8. **Templates** , front-page + custom templates per landingpagina.
9. **Custom blocks** , alleen voor interactieve widgets die geen
   pattern kunnen zijn.
10. **Auto-install hook** , images sideloaden, logo instellen,
    pagina's aanmaken.
11. **Content migratie** , WXR import of `wp_insert_post()` script
    voor blog + pagina's.
12. **SEO overdracht** , meta tags, OG, schema, redirects.
13. **Testing** , php lint, JSON validate, Lighthouse mobile,
    axe-core, Schema.org validator.
14. **Cutover** , DNS omzwaaien, sitemap submitten, redirects
    activeren.
15. **Monitoring** , 30 dagen Google Search Console monitoren,
    Core Web Vitals meten.

### 11.2 De handovermatrix voor de klant

**Doe:** lever de klant een one-pager met:
- Waar wp-admin te vinden is + hoe in te loggen.
- Waar de pagina's te bewerken zijn (via Editor of Pagina's).
- Waar het contactformulier heen mailt.
- Welke plugins actief zijn en waarom.
- Wie belt bij welk probleem (jij, hosting, SMTP provider).

---

## Deel 12 , Learnings specifiek uit deze case

### 12.1 Secret scanning is verplicht

**Doe:** voor elke commit die repo-content bevat van een deployed
app , scan op:
```bash
grep -rE "xkeysib-|sk-[a-zA-Z0-9]{20,}|sk-ant-[a-zA-Z0-9-]{40,}|AIza[a-zA-Z0-9_-]{30,}|Bearer [a-zA-Z0-9]{20,}|xoxb-|xoxp-|ghp_[A-Za-z0-9]{20,}" .
```

Herkent Brevo, OpenAI, Anthropic, Google, Bearer tokens, Slack en
GitHub PATs. Rotate elke gevonden key.

### 12.2 GitHub push-protection is een safety net

**Doe:** als je een push van commited content krijgt geweigerd met
"Sendinblue API Key" of soortgelijk , trek de key in bij de
provider en rewrite de commit. Niet forceren.

### 12.3 WebP conversie in de asset-preparatie

**Doe:** downloads van Vercel Image Optimization CDN zijn vaak al
WebP. Als ze PNG zijn (client images), gebruik Python + PIL:

```python
from PIL import Image
im = Image.open('hero.png').convert('RGB')
if im.width > 1200:
    im = im.resize((1200, int(1200 * im.height / im.width)), Image.LANCZOS)
im.save('hero.webp', 'WEBP', quality=82, method=6)
```

Bespaart 80-90% op elke image. Voor OndernemerMarketing.nl: van
4.4 MB naar 477 KB (90% reductie).

### 12.4 Auto-install hook is de sleutel tot "upload en klaar"

**Doe:** ship de theme met een `after_switch_theme` hook die op
activatie:
1. Alle brand-images sideloaded in Media Library.
2. Logo instelt via `set_theme_mod( 'custom_logo', $id )`.
3. Favicon via `update_option( 'site_icon', $id )`.
4. 11 pagina's aanmaakt met correcte templates.
5. Homepage instelt via `show_on_front` + `page_on_front`.

Idempotent maken zodat re-activatie geen duplicates maakt.

### 12.5 GitHub MCP als git-push fallback

**Doe:** als de git-proxy in je sessie geen push-auth heeft , push
via `mcp__github__create_or_update_file` API-tool. Werkt
onafhankelijk van git-config.

### 12.6 Version bumpen in 3 markers tegelijk

**Doe:** bij elke release:
1. `style.css` , `Version: 1.2.0`
2. `functions.php` , `const MJM_THEME_VERSION = '1.2.0'`
3. Zip filename , `theme-slug-1.2.0.zip`

Als ze uit sync raken , WP-admin update-detectie breekt.

### 12.7 Ship 2 zip artefacten per release

**Doe:** altijd twee zips per release:
- **Rolling:** `theme-slug.zip` , zelfde naam elke release.
- **Versioned:** `theme-slug-1.2.0.zip` , versie in filename.

De versioned zip is het bewijs welke versie de klant uploadt en
voorkomt cache-confusion.

---

## Deel 13 , Risicos en mitigaties

### 13.1 SEO impact

**Risico:** verlies van rankings door URL-changes of missing
redirects.

**Mitigatie:**
- Bewaar exact dezelfde URL-structuur waar mogelijk.
- 301 redirects voor elke gewijzigde URL.
- Monitor Search Console 30-60 dagen post-launch.
- Behoud dezelfde meta tags + Open Graph images.

### 13.2 Feature-gap

**Risico:** klant mist een interactieve feature die op Vercel wel
werkte.

**Mitigatie:**
- Doe features-inventaris vooraf.
- Bepaal per feature: pattern, custom block, plugin, of accepteer
  dat het weg is.
- Als een key-feature echt niet naar WP kan , gebruik hybrid stack.

### 13.3 Performance regressie

**Risico:** Vercel edge-served static ~150ms TTFB. WordPress op
shared hosting kan 400-800ms TTFB hebben.

**Mitigatie:**
- Installeer een caching-plugin (WP Rocket, LiteSpeed) direct na
  cutover.
- Cloudflare Free voor edge CDN.
- Kies snelle hosting (Kinsta, Cloudways) als de klant het budget
  heeft.
- Optimaliseer images naar WebP zodat de kleinere payload een deel
  compenseert.

### 13.4 Content-team transitie

**Risico:** het content-team weet niet hoe WordPress werkt (waren
gewend aan Sanity of GitHub-edits).

**Mitigatie:**
- Lever een 1-uur training-sessie na cutover.
- Screenrecordings van "hoe voeg ik een blog toe" etc.
- Documenteer patterns in de theme README.

### 13.5 Vergrendeling in de repo

**Risico:** GitHub repo van de Vercel-app wordt ontoegankelijk (bv
private en de klant heeft geen toegang meer).

**Mitigatie:**
- Fork de repo vroeg in het proces naar je eigen namespace.
- Download een tarball backup als offline copy.

---

## Deel 14 , Verifieren dat het werkt

### 14.1 Lighthouse targets

**Doe:** na cutover, run Lighthouse mobile:
- Performance , 90 of hoger.
- Accessibility , 100.
- SEO , 95 of hoger.
- Best Practices , 95 of hoger.

Als scores onder targets zakken , dat is de volgende sprint.

### 14.2 axe-core accessibility scan

**Doe:** 0 serious + 0 critical violations. Fix moderate binnen
30 dagen.

### 14.3 Schema.org validator

**Doe:** plak elke belangrijke pagina in
<https://validator.schema.org>. Zorg dat Article + Organization +
Service schema slaagt.

### 14.4 Google Rich Results Test

**Doe:** <https://search.google.com/test/rich-results> per pagina.
Als een schema uitvalt , debug in `inc/seo.php`.

### 14.5 Verkeers-analyse post-launch

**Doe:** 30 dagen post-launch , vergelijk:
- Organische bezoeken (Search Console).
- Bounce rate + gemiddelde sessieduur (analytics).
- Core Web Vitals (PageSpeed Insights of Search Console).
- Conversies (form-submissions, contact clicks).

Als een metric daalt , investigate. 5-10% daling in de eerste 2
weken is normaal (redirect settling); langer of groter , actie.

---

## Deel 15 , De essentiele beslisboom

**Doe:** volg deze beslisboom voor elke Vercel-site die naar WP
moet:

```
Vercel-site , waar naartoe?
    |
    v
Content-team wil admin? --NEE--> Blijf op Vercel, evt. TinaCMS
    |
    JA
    v
Klant heeft dev-resources? --JA--> Overweeg headless WP + Vercel
    |
    NEE
    v
Site heeft dyn. features? --VEEL--> Hybride: WP marketing + Vercel app
    |
    WEINIG
    v
Kosten belangrijk? --NEE--> Vercel + Sanity als CMS
    |
    JA
    v
    Doe: volledige migratie naar WordPress block theme
```

---

## Samenvatting , de gouden regels voor Vercel , WordPress

**Doe:** volg deze 10 regels bij elke Vercel , WordPress conversie:

1. **Beslis eerst of het moet** , headless of hybrid stack is
   vaak beter dan volledig converteren.
2. **Rotate elke gevonden secret** , Vercel dashboard + repo
   scannen voor commit.
3. **Screenshot elke publieke URL** voor visuele referentie.
4. **Design tokens overzetten in theme.json v3** , kleuren, fonts,
   spacing, layout.
5. **Route-inventaris eerst** , mapping Vercel-route , WP-pagina
   voordat je code schrijft.
6. **Auto-install hook is de sleutel** , images sideloaden, logo
   instellen, pagina's auto-createn.
7. **SEO redirect map is niet-onderhandelbaar** , 301 op elke
   gewijzigde URL, meta tags + og images behouden.
8. **Optimaliseer images naar WebP** direct , 80-90% besparing +
   Core Web Vitals win.
9. **Ship in versioned zip** met 3 markers gelijk (style.css +
   constant + filename).
10. **Monitor 30-60 dagen post-launch** , Search Console + Core
    Web Vitals + conversies.

Volg deze regels en je Vercel , WordPress conversie is een
production-grade migratie, geen SEO-catastrofe.

---

## Referenties (September 2026)

**Vercel , WordPress specifiek (deze route is niche in 2026):**
- <https://instawp.com/how-to-convert-wordpress-to-a-static-site/> ,
  static export tools (bruikbaar voor URL-scraping learnings).
- <https://nocodexport.com/tools/wordpress-to-html> , URL-scrape
  export patterns die 1-op-1 werken voor Vercel , static , WP.

**WordPress , Vercel (dominante 2026 trend, ter vergelijking):**
- <https://vercel.com/templates/next.js/nextjs-wordpress-headless-cms>
  , Next.js starter template voor headless WP.
- <https://www.ultraredesign.com/blog/wordpress-to-nextjs-guide>
  , volledige 2026 migratie-gids omgekeerde route.
- <https://community.vercel.com/t/panda-patches-wordpress-to-next-js-migration-on-vercel-zero-seo-drops-38k-mo-on-25-mo-stack/38222>
  , real-world case study Panda Patches.

**Architectuur-vergelijkingen (helpt bij deel 1 en 10):**
- <https://eseospace.com/blog/headless-wordpress-vs-traditional-2026/>
  , headless vs traditional WordPress 2026.
- <https://www.zebedeecreations.com/blog/wordpress-in-2026-traditional-headless-static-or-hybrid/>
  , keuze-matrix per situatie.
- <https://gautamkhorana.com/blog/vercel-for-wordpress-headless/>
  , real limits van Vercel + WordPress headless.

**Next.js 15 + WordPress in 2026:**
- <https://www.corgenx.com/blog/nextjs-wordpress-headless-cms-guide>
  , Next.js 15 App Router + WordPress 6.7 + WPGraphQL v1.28.
- <https://www.assertivlogix.com/blogs/headless-wordpress-with-next-js-complete-implementation-guide-2026/>
  , complete implementation guide.

**Kosten en hosting:**
- <https://clonemysite.ai/blog/vercel-vs-wordpress-2026> , Vercel
  vs WordPress 2026 cost comparison (85-95% cheaper claim).
- <https://www.mayankdigitallabs.in/blog/deploy-wordpress-vercel-guide-2026>
  , deploy WordPress on Vercel guide.

**Sister docs op deze branch (deze `Ondernemermarketing`):**
- `LOVABLE_NAAR_WORDPRESS_GIDS.md` , volledige Lovable workflow
  (referentie voor deel 4).
- `WORDPRESS_SITE_REBUILD_GUIDE.md` , abstract WP-site rebuild
  pattern.
- `CUSTOM_THEME_RESEARCH_2026.md` , 2026 WCAG 2.2 AA + WP 6.7
  policy research.
- `CLAUDE_CODE_ALGEMENE_GIDS.md` , algemene Claude Code workflow.
- `ONDERNEMERMARKETING_BUILD_PLAN.md` , specifieke project spec.
- `wp-content/themes/ondernemer-marketing/` , referentie-
  implementatie (v1.2.0).

---

*Einde gids. Deze werkwijze is deels gevalideerd op de Lovable ,
WordPress conversie (OndernemerMarketing.nl). De Vercel-specifieke
patronen komen uit de web-research (September 2026) en zijn nog
niet in productie getest. Bij de volgende echte Vercel , WordPress
migratie: update deze gids met wat blijkt te werken en wat niet.*
