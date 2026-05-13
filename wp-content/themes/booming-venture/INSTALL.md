# INSTALL ,  Booming Venture theme

> 30-minute setup from a fresh WordPress install to a fully working
> Booming Venture site with content seeded, forms wired up, and chat live.

## 0. Prerequisites

- WordPress **6.5+**, PHP **8.0+**
- Node **18+** (only required to build the custom blocks once)
- WP-CLI (optional, makes the whole flow scriptable)
- A Brevo account + API key (free tier is fine)
- A Google Tag Manager container (optional ,  default `GTM-K9532WK5` ships)

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

## 2. The custom Gutenberg blocks

The **Funnel Leak Calculator** and **ROI Forecaster** ship as working
vanilla-JS apps inside the theme ,  no `npm install`, no build step.
They appear in the editor inserter under **Booming Venture** as soon as
the theme is activated.

Each block is fully interactive on the front end:

- Funnel Calculator: 6 input fields, live recalculation, SVG benchmark
  chart, biggest-leak recommendation, "save as PDF" via `window.print()`.
- ROI Forecaster: 7 inputs, 3-month linear ramp model, cumulative
  revenue SVG chart, month-by-month table, break-even highlight, PDF
  export.

To customise the math or visuals, edit:
- `blocks/funnel-calculator/view.js` + `view.css`
- `blocks/roi-forecaster/view.js` + `view.css`

---

## 3. Install required plugins

```bash
# Required for the forms shipped in the patterns
wp plugin install contact-form-7 --activate
wp plugin install contact-form-7-honeypot --activate   # anti-spam, mandatory
wp plugin install flamingo --activate                  # stores submissions (CF7 stores nothing by default)

# Optional but recommended for a "10/10" site
wp plugin install seo-by-rank-math --activate   # SEO meta + sitemap merge
wp plugin install complianz-gdpr --activate     # EU cookie consent
wp plugin install wp-super-cache --activate     # page caching
wp plugin install simple-cloudflare-turnstile --activate  # better than reCAPTCHA for privacy

# Brevo: install one of these to push CF7 submissions to Brevo lists
# Option A: official Brevo plugin (has CF7 mapping in its settings)
wp plugin install mailin --activate
# Option B: lightweight webhook-style
# wp plugin install cf7-to-any-api --activate
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

## 5. Create the six Contact Form 7 forms

The theme's patterns reference forms by semantic slug
(`[contact-form-7 id="contact"]` etc.). The integrations layer rewrites
each slug to the matching CF7 hash ID at render time. Six forms to
create, one per slug:

| Slug                 | Purpose                              | Brevo list |
| -------------------- | ------------------------------------ | ---------- |
| `contact`            | Main contact form (Contact section)  | 4          |
| `newsletter`         | Big newsletter CTA on /blog          | 3          |
| `newsletter-inline`  | Footer mini newsletter               | 3          |
| `growth-guide`       | Hero + dedicated lead-magnet page    | 2          |
| `quickscan`          | Boardroom Quickscan landing page     | 4 + tag    |
| `head-of-growth`     | Head of Growth landing page          | 4 + tag    |

### 5a. Recipes for each form

In **Contact → Contact Forms → Add New**, paste the form-tags below into
the **Form** tab. Each form already gets the brand styling via
`theme.css` because the CF7 default class hooks are styled.

**`contact`** ,  full contact form:

```
<label>Your name *
  [text* your-name placeholder "Jane Doe" autocomplete:name]</label>
<label>Email *
  [email* your-email placeholder "jane@company.com" autocomplete:email]</label>
<label>Company
  [text your-company autocomplete:organization]</label>
<label>How can we help? *
  [textarea* your-message]</label>
[honeypot website-2]
[acceptance gdpr optional]I agree to the [link url="/privacy-policy/"]Privacy Policy[/link].[/acceptance]
[submit "Send message"]
```

**`newsletter`** ,  big newsletter CTA:

```
<label>Email *
  [email* your-email placeholder "you@work.com" autocomplete:email]</label>
[honeypot website-2]
[submit "Join 700+ growth pros"]
```

**`newsletter-inline`** ,  footer mini newsletter (wrap in `.bv-form-inline`):

```
[email* your-email placeholder "Enter email" autocomplete:email]
[honeypot website-2]
[submit "Join"]
```

**`growth-guide`** ,  lead magnet:

```
<label>Business email *
  [email* your-email placeholder "you@company.com" autocomplete:email]</label>
[honeypot website-2]
[submit "Download the guide"]
```

**`quickscan`** ,  Boardroom Quickscan landing form:

```
<label>Your name *
  [text* your-name autocomplete:name]</label>
<label>Email *
  [email* your-email autocomplete:email]</label>
<label>Company *
  [text* your-company autocomplete:organization]</label>
<label>Annual revenue
  [select revenue "< €1M" "€1M ,  €5M" "€5M ,  €25M" "> €25M"]</label>
<label>What's the single biggest growth question on your desk? *
  [textarea* your-message]</label>
[honeypot website-2]
[acceptance gdpr optional]I agree to the [link url="/privacy-policy/"]Privacy Policy[/link].[/acceptance]
[submit "Request my Quickscan"]
```

**`head-of-growth`** ,  fractional service intake:

```
<label>Your name *
  [text* your-name autocomplete:name]</label>
<label>Work email *
  [email* your-email autocomplete:email]</label>
<label>Company *
  [text* your-company autocomplete:organization]</label>
<label>Stage
  [select stage "Pre-seed" "Seed" "Series A" "Series B+" "Bootstrapped"]</label>
<label>What you'd want a fractional Head of Growth to fix first *
  [textarea* your-message]</label>
[honeypot website-2]
[acceptance gdpr optional]I agree to the [link url="/privacy-policy/"]Privacy Policy[/link].[/acceptance]
[submit "Apply for an intro call"]
```

### 5b. Configure the Mail tab per form

CF7 sends one email per submission. In each form's **Mail** tab, set:

- **To:** `info@boomingventure.com`
- **From:** `[your-name] <wordpress@boomingventure.com>`
- **Subject:** `[BV] {slug} ,  [your-email]` (replace `{slug}` with the form name)
- **Body:** include every field token: `Name: [your-name]`, `Email: [your-email]`, `Company: [your-company]`, `Message: [your-message]`. Plus a footer line: `Submitted via {page-url}`.

### 5c. Push to Brevo

Pick one of the two routes:

- **Brevo plugin** (`mailin`): in *Brevo → Settings → Contact Form 7*,
  map each form to its target list using the same field names as above.
- **CF7 to Any API**: in each form's **CF7 to API** tab, point at
  `https://api.brevo.com/v3/contacts` with the right JSON body and your
  Brevo API key header.

### 5d. Wire the slugs into the theme

Open **Settings → Booming Venture** in WP admin. For each slug paste
the CF7 hash ID from the form's edit screen (e.g. `a1b2c3d4`). Until
you do this, the front-end shows a red warning to logged-in editors and
nothing to visitors.

For production, set the map in `wp-config.php` instead:

```php
define( 'BV_CF7_MAP', [
    'contact'           => 'a1b2c3d4',
    'newsletter'        => 'b2c3d4e5',
    'newsletter-inline' => 'c3d4e5f6',
    'growth-guide'      => 'd4e5f6a7',
    'quickscan'         => 'e5f6a7b8',
    'head-of-growth'    => 'f6a7b8c9',
] );
```

### 5e. Anti-spam stack

- **Honeypot for CF7**: already in your form-tags as `[honeypot website-2]`.
- **Turnstile**: install *Simple Cloudflare Turnstile*, get a site key
  + secret from Cloudflare, paste into the plugin settings. It auto-
  attaches to every CF7 form.
- **Akismet**: if you already pay for it, CF7 detects it automatically.

### 5f. Submission storage

CF7 stores nothing by default. **Flamingo** (free, by the CF7 author)
captures every submission. Set a retention window in your
calendar / use a plugin like WP-Optimize to purge older entries to keep
GDPR retention obligations clean (24 months for prospect data per the
shipped Privacy Policy).

> Want a no-plugin alternative? The theme also exposes a tiny proxy at
> `POST /wp-json/bv/v1/brevo` (see `inc/integrations.php`). Set
> `BV_BREVO_API_KEY` in `wp-config.php`.

---

## 6. Set tracking IDs

Either through wp-admin (recommended) or via WP-CLI:

```bash
wp option update bv_gtm_id        'GTM-XXXXXXXX'
wp option update bv_dataspeak_id  '6863892dbcf4fea86a49e9f8'
```

The defaults are already wired to the Lovable site's IDs ,  change them
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
      testimonials, contact ,  in that order.
- [ ] `/funnel-calculator` and `/roi-forecaster` show the calculator
      block with the data-attribute hydration mount point.
- [ ] Submit a test contact form → contact lands in Brevo list **4**.
- [ ] View source on any page: search for `<script type="application/ld+json">`
      ,  should see Organization + WebSite (+ BlogPosting on posts).
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
| Custom blocks missing from inserter | Bump theme version in `style.css` and visit `Appearance → Editor` once. Check that `blocks/*/block.json` exist. |
| `[contact-form-7 id="contact"]` shows literal text | Install + activate Contact Form 7. |
| Form shows the red "not mapped" warning | Go to *Settings → Booming Venture* and paste the CF7 hash ID for each slug. |
| Form spinner shows the default brown gif | Hard refresh; the theme replaces it with a brand spinner via CSS. |
| DataSpeak chat doesn't appear | Check `bv_dataspeak_id` option and that the page isn't blocked by an ad-blocker. |
| Patterns not appearing | Bump theme version in `style.css` and visit `Appearance → Editor` once. |
| Front page shows blog instead of Home | `Settings → Reading → Front page displays → A static page → Home`. |
