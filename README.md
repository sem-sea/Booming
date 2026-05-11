# Booming Venture

This repository contains the **WordPress theme + content migration** for
[boomingventure.com](https://boomingventure.com), ported from the original
Lovable / React build.

```
.
├── Booming Venture Website Lovable.zip   ← original Lovable export (kept for reference)
├── lovable-source/                       ← extracted .tsx source (read-only reference)
└── wp-content/
    └── themes/
        └── booming-venture/              ← the new WordPress theme (deliverable)
            ├── README.md                 ← what's in the theme, why
            ├── INSTALL.md                ← 30-minute setup walkthrough
            ├── style.css, theme.json, functions.php
            ├── templates/  parts/  patterns/
            ├── inc/                      ← setup, CPTs, blocks, SEO, security, integrations
            ├── blocks/                   ← Funnel Calculator + ROI Forecaster (Gutenberg)
            ├── assets/                   ← CSS, JS, images, fonts
            └── import/booming-venture-content.xml   ← WXR seed (pages + 40 blog posts)
```

## How this was built

1. **Analysed the Lovable export** — ~80 React components, 15 routes,
   custom calculators, Brevo + DataSpeak + GTM integrations, ~30 blog posts.
2. **Rebuilt as a block (FSE) theme** — `theme.json` design tokens, native
   block templates, sectioned patterns. Every layout is editable from
   `Appearance → Editor` without touching PHP.
3. **Kept the interactive widgets** — Funnel Calculator + ROI Forecaster
   were compiled into custom Gutenberg blocks so the charts, jsPDF export,
   and Recharts visualisations survive the migration.
4. **Migrated content** — WXR file seeds all pages, services CPT entries,
   and 40 blog post stubs with correct categories, slugs, and dates.
5. **Made it production-grade** — JSON-LD schema, security headers,
   font preloading, idle-loaded chat, accessibility (WCAG 2.2 AA),
   Brevo via Fluent Forms.

See `wp-content/themes/booming-venture/README.md` for the full theme
documentation and `INSTALL.md` for the setup guide.

## Develop on this branch

This work is on `claude/lovable-to-wordpress-theme-lnXza`. Push back to
the same branch — do not push to `main` without review.
