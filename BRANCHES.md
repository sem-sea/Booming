# Branch index

Every branch in this repo, what it contains, why it exists, how to use it. Read this file when you do not know which branch to check out.

This document lives on `claude/master-learnings` so it is reachable regardless of which feature branch you arrive on. Append to it whenever a new branch is created.

---

## Quick map

| Branch | Purpose | Latest commit | Status |
|---|---|---|---|
| [`claude/lovable-to-wordpress-theme-lnXza`](#branch-claudelovable-to-wordpress-theme-lnxza) | Main release branch , theme + first three plugins | `c7f763c` brevo-fix v1.1.0 | ACTIVE , the default landing branch for any non-isolated work |
| [`claude/blog-images-plugin`](#branch-claudeblog-images-plugin) | Standalone Featured-image plugin | `bfd54a7` blog-images v1.4.0 | READY , merge or distribute independently |
| [`claude/seo-schema-plugin`](#branch-claudeseo-schema-plugin) | Standalone SEO + JSON-LD plugin | `fdc486c` seo-boost v1.0.0 | READY , merge or distribute independently |
| [`claude/citeleap-plugin`](#branch-claudeciteleap-plugin) | Commercial AI content engine | `46d4d4c` citeleap v1.6.0 | COMMERCIAL-IN-PROGRESS , see scorecard in `LEARNINGS_MASTER.md` |
| [`claude/master-learnings`](#branch-claudemaster-learnings) | Meta-documentation (this file + LEARNINGS_MASTER) | `2b2a090` LEARNINGS_MASTER.md | EVERGREEN , append-only knowledge base |
| [`Old-Website`](#branch-old-website) | Pre-conversion archive | varies | ARCHIVE , do not develop on |

---

## Relationships

```
            ┌─────────────────────────────────┐
            │  Old-Website (archive, frozen)  │
            └─────────────────────────────────┘
                          │
                          ▼  initial conversion
            ┌─────────────────────────────────┐
            │ claude/lovable-to-wordpress-    │
            │   theme-lnXza  (release main)   │
            │   theme 1.8.0                   │
            │   + booming-venture-importer    │
            │   + booming-venture-blog-       │
            │       importer                  │
            │   + booming-venture-brevo-fix   │
            └─────────────────────────────────┘
                  │            │            │
                  │            │            │
                  ▼            ▼            ▼
            ┌──────────┐ ┌──────────┐ ┌──────────┐
            │ blog-    │ │ seo-     │ │ citeleap │
            │ images-  │ │ schema-  │ │ -plugin  │
            │ plugin   │ │ plugin   │ │          │
            │ v1.4.0   │ │ v1.0.0   │ │ v1.6.0   │
            └──────────┘ └──────────┘ └──────────┘
                          │
                          ▼
            ┌─────────────────────────────────┐
            │ claude/master-learnings         │
            │   LEARNINGS_MASTER.md           │
            │   BRANCHES.md  (this file)      │
            └─────────────────────────────────┘
```

The three feature plugins (`blog-images`, `seo-schema`, `citeleap`) were each forked from the release branch on the day they were created. They have NOT merged back. Each one ships as a standalone zip from its own branch.

`claude/master-learnings` is independent of every other branch and intentionally does not contain any code, only documentation.

---

## Branch: `claude/lovable-to-wordpress-theme-lnXza`

The default branch for any work that is not strictly a single-plugin feature. Houses the Booming Venture FSE theme and the first three plugins. Every release for this branch follows the version + versioned-zip rule in `LEARNINGS_MASTER.md` section 2.2.

### What is on this branch

| Artefact | Path | Latest version |
|---|---|---|
| Theme | `wp-content/themes/booming-venture/` | 1.8.0 |
| Theme zip (versioned) | `wp-content/themes/booming-venture-1.8.0.zip` | 1.8.0 |
| Theme zip (rolling) | `wp-content/themes/booming-venture.zip` | 1.8.0 |
| Importer plugin | `wp-content/plugins/booming-venture-importer/` | 1.1.0 |
| Importer zip (versioned) | `wp-content/plugins/booming-venture-importer-1.1.0.zip` | 1.1.0 |
| Blog-only importer | `wp-content/plugins/booming-venture-blog-importer/` | 1.0.1 |
| Blog-only importer zip | `wp-content/plugins/booming-venture-blog-importer-1.0.1.zip` | 1.0.1 |
| Brevo form fix | `wp-content/plugins/booming-venture-brevo-fix/` | 1.1.0 |
| Brevo zip | `wp-content/plugins/booming-venture-brevo-fix-1.1.0.zip` | 1.1.0 |
| Content seed | `wp-content/themes/booming-venture/import/booming-venture-content.xml` | 83 posts, ≥1,200 words, May 2026 data |
| Playbook | `.claude/PROJECT_PLAYBOOK.md` | the 15-lesson Lovable→WP conversion guide |
| Build script | `build-blog-content-2026.py` | regenerates blog bodies in WXR |
| In-place body updater | `build-blog-update-shortposts.py` | swaps WXR bodies for the 43 expanded posts |

### When to use this branch

- Bug fix or feature on the theme.
- Bug fix or feature on the importer / blog-importer / brevo-fix plugins.
- Content edits to the bundled WXR.
- The default if you are not sure where work belongs.

### Download URLs

- Theme: `https://github.com/sem-sea/Booming/raw/claude/lovable-to-wordpress-theme-lnXza/wp-content/themes/booming-venture-1.8.0.zip`
- Importer: `https://github.com/sem-sea/Booming/raw/claude/lovable-to-wordpress-theme-lnXza/wp-content/plugins/booming-venture-importer-1.1.0.zip`
- Blog importer: `https://github.com/sem-sea/Booming/raw/claude/lovable-to-wordpress-theme-lnXza/wp-content/plugins/booming-venture-blog-importer-1.0.1.zip`
- Brevo fix: `https://github.com/sem-sea/Booming/raw/claude/lovable-to-wordpress-theme-lnXza/wp-content/plugins/booming-venture-brevo-fix-1.1.0.zip`

---

## Branch: `claude/blog-images-plugin`

Standalone plugin: lets the operator pick a pool of Featured images from the Media Library, randomly assigns one per blog post, and renders it as a hero on single + card on overview. Mobile-first CSS. Works on any theme.

Built on its own branch so the main release branch never had to gate on image work.

### What is on this branch

| Artefact | Path | Version |
|---|---|---|
| Plugin source | `wp-content/plugins/booming-venture-blog-images/` | 1.4.0 |
| Versioned zip | `wp-content/plugins/booming-venture-blog-images-1.4.0.zip` | 1.4.0 |
| Rolling zip | `wp-content/plugins/booming-venture-blog-images.zip` | 1.4.0 |

### Version history on this branch

- 1.0.0 , initial: featured image + hero + card.
- 1.1.0 , random pool from Media Library with multi-select.
- 1.2.0 , stop double-render on block themes that already use `core/post-featured-image`.
- 1.3.0 , constrain theme featured image to 16:9 on single posts.
- 1.4.0 , match featured image width to article body content-size.

### When to use this branch

- New work on the random-pool picker, hero rendering, or thumbnail card.
- CSS tweaks to how Featured Images render.

### Download URL

`https://github.com/sem-sea/Booming/raw/claude/blog-images-plugin/wp-content/plugins/booming-venture-blog-images-1.4.0.zip`

### Merge path

If you want this plugin in the main release: cherry-pick the latest commit into `claude/lovable-to-wordpress-theme-lnXza`, OR keep it as a separate distributable.

---

## Branch: `claude/seo-schema-plugin`

Standalone plugin: auto-detects existing SEO plugins / theme schema, fills the gaps (canonical, meta-desc, OG, Twitter, JSON-LD), pings Google + Bing + IndexNow on every publish.

### What is on this branch

| Artefact | Path | Version |
|---|---|---|
| Plugin source | `wp-content/plugins/booming-venture-seo-boost/` | 1.0.0 |
| Versioned zip | `wp-content/plugins/booming-venture-seo-boost-1.0.0.zip` | 1.0.0 |
| Rolling zip | `wp-content/plugins/booming-venture-seo-boost.zip` | 1.0.0 |

### Detection list (the plugin stands down for each one)

Yoast SEO, Rank Math, All in One SEO, SEOPress, The SEO Framework, Slim SEO, Squirrly, plus Booming Venture theme's own `inc/seo.php`.

### When to use this branch

- New work on schema detection, JSON-LD generation, OG / Twitter Card output.
- Adding new schema types (Person sameAs, Product, AggregateRating, Speakable).

### Download URL

`https://github.com/sem-sea/Booming/raw/claude/seo-schema-plugin/wp-content/plugins/booming-venture-seo-boost-1.0.0.zip`

### Merge path

If you want this plugin in the main release: cherry-pick into `claude/lovable-to-wordpress-theme-lnXza`. Be careful: the Booming Venture theme already ships `inc/seo.php`, so on that theme the plugin will stand down for JSON-LD. That is correct behaviour, not a bug.

---

## Branch: `claude/citeleap-plugin`

The commercial product. AI blog content engine with multi-LLM router (Claude / OpenAI / Gemini, BYOK), idea generator, writing model, editable master prompt, three-state auto modes for both new content and refresh, single planner with per-row pin / pause / schedule / retry, encrypted-at-rest keys, monthly budget caps with pre-warning, dashboard with severity-tagged logs, progression overlay.

### What is on this branch

| Artefact | Path | Version |
|---|---|---|
| Plugin source | `wp-content/plugins/citeleap/` | 1.6.0 |
| Versioned zip | `wp-content/plugins/citeleap-1.6.0.zip` | 1.6.0 |
| Rolling zip | `wp-content/plugins/citeleap.zip` | 1.6.0 |
| Operator manual | `wp-content/plugins/citeleap/HOW_IT_WORKS.md` | n/a |

### Version history on this branch

- 1.0.0 , multi-LLM router + idea generator + writer + planner + WP-Cron + scheduling.
- 1.1.0 , token cap, dashboard, refresh module.
- 1.2.0 , production-ready security pass (encryption, masking, redaction, transient lock, severity logs, test-connection).
- 1.3.0 , three-state Auto mode (off / draft / publish) + manual topic queue.
- 1.4.0 , refresh-side symmetry (off / draft / live) + CET default + stuck recovery.
- 1.5.0 , per-row pin datetime + ETA hints.
- 1.6.0 , coherent row actions (pause / resume / schedule / reschedule / unschedule / publish-now / retry) + progression overlay.

### Internal architecture

| File | Responsibility |
|---|---|
| `citeleap.php` | Bootstrap, constants, activation, deactivation, uninstall, cron registration, CET helper |
| `includes/crypto.php` | AES-256-CBC at rest for API keys, key derived from `AUTH_KEY` |
| `includes/prompts.php` | Master + idea prompts with GEO/AEO May 2026 defaults |
| `includes/pricing.php` | May 2026 model pricing per million tokens |
| `includes/usage.php` | 12-month token + cost ledger, budget cap gate |
| `includes/llm.php` | Claude / OpenAI / Gemini adapters with shared return shape |
| `includes/generator.php` | Idea generator + writer + manual-topic queue + logger |
| `includes/refresh.php` | Refresh queue + three-state mode + bulk-add + approve / reject |
| `includes/actions.php` | State machine + per-row handlers (pause/resume/schedule/retry/etc.) |
| `includes/scheduler.php` | WP-Cron tick with transient lock, ETA calculation |
| `includes/dashboard.php` | KPIs + budget bars + status counts + log split |
| `includes/planner.php` | Single planner UI with workflow panel + per-row actions |
| `includes/settings.php` | 5-tab admin (Dashboard / Planner / Prompts / Settings / Log) + flash messages |
| `assets/admin.js` | Progression overlay (vanilla JS, no deps) |
| `assets/admin.css` | Overlay + spinner + flow-grid + help-paragraph styles |
| `HOW_IT_WORKS.md` | Plain-language operator manual |

### When to use this branch

- New work on the AI content engine: prompts, providers, models, planner UX, dashboard, refresh, scheduler.
- Adding new LLM providers (Anthropic Console Computer Use, Cohere, Mistral, etc.).
- Adding the commercial-readiness items: PHPUnit, Freemius license server, retry-with-backoff, editor capability, accessibility, GDPR consent.

### Download URL

`https://github.com/sem-sea/Booming/raw/claude/citeleap-plugin/wp-content/plugins/citeleap-1.6.0.zip`

### Merge path

Currently NOT planned to merge back into the release branch. CiteLeap is a standalone commercial product and ships from its own branch. If you want CiteLeap available alongside the Booming Venture theme on a site, install it as a separate plugin zip.

### Commercial readiness gap

See `LEARNINGS_MASTER.md` section 9. Two-week gap list before selling to strangers: PHPUnit + Freemius + retry-with-backoff + editor capability + accessibility audit + GDPR consent UX + OpenAI/Gemini pricing verification.

---

## Branch: `claude/master-learnings`

Meta-documentation branch. No code. The single source of truth for cross-branch knowledge.

### What is on this branch

| Artefact | Path | Purpose |
|---|---|---|
| Master learnings | `LEARNINGS_MASTER.md` | The 13 always-do patterns, 16 never-do anti-patterns, per-domain learnings, pre-flight checklist, decision log, commercial readiness scorecard, quick-reference commands |
| Branch index | `BRANCHES.md` (this file) | One-stop reference for every branch in the repo |

### When to use this branch

- Adding a new always-do pattern after discovering one in any feature branch.
- Adding a new never-do anti-pattern after wasting time on something.
- Updating the commercial-readiness scorecard after closing a gap.
- Creating a new branch , update BRANCHES.md in the same commit so the index never goes stale.

### Workflow

```bash
git checkout claude/master-learnings
# edit LEARNINGS_MASTER.md or BRANCHES.md
git commit -m "docs: <one-line summary>"
git push -u origin claude/master-learnings
```

Do not develop code here. Do not merge feature branches into here.

---

## Branch: `Old-Website`

Pre-conversion archive. The Booming Venture site as it existed before the Lovable → WordPress conversion captured at the start of this session.

### When to use this branch

- Historical reference only.
- Do not develop here.
- Do not delete (it is the rollback safety net for the entire conversion).

---

## Conventions across every branch

Every branch follows the rules from `LEARNINGS_MASTER.md`:

1. **Versioned zip filename rule** , every release ships under `<plugin-slug>-<version>.zip`. Never reuse a version number or filename.
2. **Triple version match** , plugin header `Version:`, `*_VERSION` constant in code, `Stable tag` in readme.txt all match before commit.
3. **Pre-commit verification** , `php -l` on every PHP file, `unzip -p ... | grep Version` on every zip.
4. **Documentation in the same commit as the feature** , readme.txt changelog + (where applicable) HOW_IT_WORKS.md.
5. **Standards baseline** , ABSPATH, nonces, caps, sanitize, escape, autoload=false, multisite uninstall, i18n.

If you create a NEW branch, the first commit on it should:
- Add the branch entry to this BRANCHES.md (back on `claude/master-learnings`).
- Establish the branch's purpose in the commit message.
- Lay out the standards-compliant scaffold (header + ABSPATH + uninstall + readme).

---

## Picking a branch (decision tree)

```
Is the work an edit to existing theme / plugin code?
  ├─ Theme or first-three plugins?
  │    → claude/lovable-to-wordpress-theme-lnXza
  ├─ Blog images plugin?
  │    → claude/blog-images-plugin
  ├─ SEO boost plugin?
  │    → claude/seo-schema-plugin
  └─ CiteLeap?
       → claude/citeleap-plugin

Is the work documentation, learnings, or branch index?
  → claude/master-learnings

Is the work a brand new plugin?
  → git checkout -b claude/<plugin-name>
  → Add a section to BRANCHES.md on claude/master-learnings
  → Scaffold per LEARNINGS_MASTER.md section 5

Is the work content (blog WXR, prompts, default settings)?
  → claude/lovable-to-wordpress-theme-lnXza (for the bundled WXR)
  → claude/citeleap-plugin (for the default master prompt)
```

---

## Pull request strategy (when you eventually open one)

Today no PRs are open , every branch has been pushed but not merged. The reasoning: each plugin is shippable as a standalone zip from its own branch, so a PR is unnecessary for distribution. Open a PR ONLY if:

- The plugin is graduating into the main release branch.
- Two branches need to merge to combine features.
- An external collaborator needs to review.

When you do open a PR, the title format used here is:
```
feat(<plugin-slug>): vX.Y.Z , one-line summary
```

The body is the commit-message-style breakdown that already exists in the last commit on the branch.

---

## Last update

`2026-05-13`. Updated on commit that creates this file. Bump when a new branch is added, removed, or repurposed.
