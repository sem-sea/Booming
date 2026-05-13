# Booming Venture + plugin family — Master learnings

Single-source-of-truth for everything built in this repo in one session. Read this file FIRST before starting any related work. It tells you what exists, the patterns that worked, the patterns that wasted time, and the pre-flight checks that prevent the most common mistakes.

The document is organised top-down: index, what was built, principles, anti-patterns, per-domain learnings, pre-flight checklists, decision log, reference index.

---

## 1. What was shipped today

Four branches, eleven artefacts. Each artefact owns one job. Each branch ships independently.

### Branch: `claude/lovable-to-wordpress-theme-lnXza` (the main release branch)

| Artefact | Final version | Job |
|---|---|---|
| `wp-content/themes/booming-venture` | 1.8.0 | Full FSE block theme: home, services, blog, calculators, lead-magnets, JSON-LD via `inc/seo.php`, IndexNow + GEO via `inc/geo.php` |
| `wp-content/plugins/booming-venture-importer` | 1.1.0 | Full-site import (pages + services + 83 blog posts + permalinks + structural-page refresh + force-overwrite blog content) |
| `wp-content/plugins/booming-venture-blog-importer` | 1.0.1 | Blog-only import with mobile-first CSS, no side effects on pages/services/menus |
| `wp-content/plugins/booming-venture-brevo-fix` | 1.1.0 | Front-end form fix: derive Name from Email + suppress REST notice leak + upsert duplicate contacts |
| `wp-content/themes/booming-venture/import/booming-venture-content.xml` | n/a | 83 published posts, every one ≥1,200 words, GEO/AEO compliant, May 2026 data |
| `.claude/PROJECT_PLAYBOOK.md` | n/a | The earlier playbook (15 hard-won lessons from the Lovable conversion) |

### Branch: `claude/blog-images-plugin`

| Artefact | Final version | Job |
|---|---|---|
| `wp-content/plugins/booming-venture-blog-images` | 1.4.0 | Featured-image picker + hero on single + card on overview + random-pool assignment with manual override; CSS constrains image to content-size + 16:9 |

### Branch: `claude/seo-schema-plugin`

| Artefact | Final version | Job |
|---|---|---|
| `wp-content/plugins/booming-venture-seo-boost` | 1.0.0 | Auto-detects existing SEO plugins, injects only missing tags (canonical, meta-desc, OG, Twitter, JSON-LD Organization/WebSite/BlogPosting/BreadcrumbList/auto-FAQPage/auto-HowTo). Active boosters: sitemap ping + IndexNow on publish |

### Branch: `claude/citeleap-plugin` (the commercial product)

| Artefact | Final version | Job |
|---|---|---|
| `wp-content/plugins/citeleap` | 1.6.0 | AI blog engine: multi-LLM router (Claude / OpenAI / Gemini, BYOK), idea + writing models, editable master prompt with GEO/AEO defaults, three-state auto modes for both new content and refresh, planner with per-row pin/pause/schedule/retry, encrypted-at-rest keys, monthly budget caps, dashboard with severity-tagged logs, progression overlay |
| `wp-content/plugins/citeleap/HOW_IT_WORKS.md` | n/a | Plain-language operator manual with state machine, troubleshooting matrix, GDPR notes |

### Branch: `claude/master-learnings` (this file)

You are reading it.

---

## 2. Core principles (the "always do X")

These are the patterns that worked across every plugin. Carry them forward.

### 2.1 Standards baseline for every WP plugin

- `defined( 'ABSPATH' ) || exit;` at the top of every PHP file.
- Plugin header: Name, URI, Description, **Version**, Requires WP, Requires PHP, Author, License, License URI, Text Domain, Domain Path, Update URI (set `false` for private plugins).
- `register_activation_hook` — verify PHP version, register `register_uninstall_hook` here (once, NOT on every page load).
- `register_deactivation_hook` — unschedule any cron events.
- `uninstall.php` at plugin root, gated on `WP_UNINSTALL_PLUGIN`, multisite-aware via `switch_to_blog`.
- `load_plugin_textdomain` on `init`, `/languages/` folder shipped with `.gitkeep`.
- `current_user_can( 'manage_options' )` on every admin entry point.
- `wp_nonce_field` + `check_admin_referer` on every state-changing form.
- `wp_unslash` on every `$_POST` / `$_GET` read, then sanitize with the right function (`sanitize_text_field`, `sanitize_title`, `sanitize_key`, `sanitize_textarea_field`).
- Escape on output: `esc_html`, `esc_attr`, `esc_url`, `esc_textarea`, `wp_kses_post` on `post_content`, `wp_kses` with allowlist for anything mixed.
- `wp_json_encode` (never raw `json_encode`).
- `update_option( $key, $value, false )` — `autoload=false` on every option (no perf hit on hot pages).
- `wp_remote_post` / `wp_remote_get` for outbound HTTP (never `curl` or `file_get_contents` on user URLs).
- No raw SQL. Use WP options API, `get_posts`, `wp_insert_post`, `wp_update_post`, `update_post_meta`.

### 2.2 Versioning + distribution rule

**Every release ships under a brand-new versioned filename.** Never reuse a version. Never reuse a filename.

- `Version:` header in main plugin PHP, `VERSION` constant in code, `Stable tag` in `readme.txt` — all three must match exactly before commit.
- Versioned zip: `<plugin-slug>-<version>.zip`.
- Rolling latest: `<plugin-slug>.zip` (same bytes as the versioned zip).
- Pre-commit verification: `unzip -p <zip> <plugin>/<plugin>.php | grep Version` and `unzip -p <zip> <plugin>/readme.txt | grep "Stable tag"` MUST return the new version.
- Delete the previous versioned zip from the working tree on release. Git history preserves it for rollback by SHA.

### 2.3 API key handling

If your plugin stores third-party API keys, the floor is:

1. **Encrypt at rest** with AES-256-CBC. Derive the key from `AUTH_KEY` (`hash( 'sha256', 'plugin:' . AUTH_KEY, true )`). On-disk format: `prefix:base64(iv):base64(ciphertext)`. The decryption key is in wp-config, not the database. A leaked DB backup cannot decrypt.
2. **Mask in UI**. Inputs render with empty `value=""`; placeholder shows masked existing key (e.g. `••••••••..xxxx`). Empty submit preserves the existing key, never blanks it.
3. **Redact from logs**. Regex strips `sk-*`, `sk-ant-*`, `AIza*`, `whsec_*` patterns from any log entry before persisting.
4. **Test-connection button per provider**. One-shot tiny request that verifies key + network + quota.

### 2.4 Long-running synchronous operations

WordPress admin-post handlers are synchronous, then redirect. LLM calls take 5-60 seconds. The page LOOKS frozen.

**Pattern**: a vanilla-JS progression overlay that listens for form `submit`, shows a centered card with the action title + a step-by-step narrative + a spinner. Auto-dismisses on page reload.

### 2.5 Race protection for WP-Cron

Manual "run now" buttons can race with the actual cron tick. Two ticks running in parallel = double spend on the budget cap.

**Pattern**: at top of tick, `set_transient( 'lock', 1, 5 * MINUTE_IN_SECONDS )`. Wrap the whole tick body in `try { ... } catch ( \Throwable $e ) { log critical } finally { delete_transient( 'lock' ); }`. The 5-minute expiry guarantees a crashed tick eventually self-clears.

### 2.6 State machine documentation IN THE CODE

Every plugin with non-trivial state (queue, statuses, lifecycle) gets a class-level doc block listing the allowed transitions. Example from `citeleap/includes/actions.php`:

```
queued     ── Write draft ──→ drafted
queued     ── Pin datetime ──→ queued + publish_at
queued     ── Pause / Resume ──→ queued + paused flag
```

This is faster to read than tracing the code.

### 2.7 Coherent per-row action sets

If a queue / table has rows with different statuses, every status gets a consistent action set documented in the state machine. Don't have one-off buttons that exist for some statuses and not others.

Universal where applicable: **Pause / Resume / Remove** is available on every non-terminal row.

### 2.8 Three-state auto modes (off / draft / publish)

Boolean checkboxes don't scale once you need "auto-generate but DO NOT auto-publish". Use a 3-state dropdown:

| Mode | Behaviour |
|---|---|
| `off` | Nothing automatic |
| `draft` | Auto-generate + save as draft for manual review |
| `publish` (or `live` for refresh) | Auto-generate + auto-publish at slot time |

### 2.9 Per-post override fields

Per-row datetime pinning, paused flag, retry-from-failed. The auto-cadence is the default; per-row overrides win first. Operator stays in control.

### 2.10 Multi-tier dedupe

When accepting user input that becomes a slug (manual topics, refresh items):

1. The idea prompt RECEIVES the existing-slug list so the LLM avoids them.
2. The bulk-add handler dedupes against `get_posts` (every status).
3. The bulk-add handler also dedupes against the existing queue.
4. The state machine refuses to add the same post twice to the refresh queue while one is in-flight.

### 2.11 Tagged severity in logs

Every log entry carries `severity` ∈ `info | warn | error | critical`. The dashboard splits success vs error feeds on severity (not event-name allowlist) so future event names classify correctly without code changes.

### 2.12 Budget caps with pre-warning

Hard cap per provider per month + an overall cap. 0 = unlimited. **Pre-warning at 80% (amber banner)**, hard refuse at 100% (red banner). Operator sees the wall coming.

### 2.13 Plain-language operator manual

Ship `HOW_IT_WORKS.md` inside the plugin folder. Contents: one-paragraph summary, per-tab guide, every flow step-by-step, status taxonomy, troubleshooting matrix (symptom → where to look → likely cause), where data lives, cost math, GDPR notes, audit table.

Non-developer operators need this to be self-sufficient. Code-comments are for the next developer.

---

## 3. Anti-patterns (the "never do Y")

These wasted real time today. Avoid them in future work.

### 3.1 NEVER reuse a version number or zip filename

Even a re-package counts as a new release. Bump the patch number, build a new versioned zip, delete the old. The user's mental model is "the URL with the version in it = the build I'm downloading".

### 3.2 NEVER echo an API key value into HTML

The naive WP pattern of `<input value="<?php echo esc_attr( $key ); ?>">` leaks the secret on every settings render. Use empty `value=""` + placeholder showing the masked version.

### 3.3 NEVER touch posts where the operator set state manually

If a user manually changes the Featured Image, or manually publishes a draft, or manually overrides any auto-flag: the plugin's auto-loop must drop its claim on that row. We achieve this by listening to `updated_post_meta` and clearing our private flags.

### 3.4 NEVER use boolean checkboxes for tri-state behaviour

"Auto-publish: ON/OFF" is fine until someone wants "generate but don't publish". Refactor to a dropdown EARLY. Migration: keep the boolean field, populate it as a fallback from the new field, but read the new field first.

### 3.5 NEVER call an LLM API without a budget cap check

`can_spend( $provider )` MUST gate every chat() call. If the cap is hit, return ok=false with the reason, log it, and let the caller report it to the user. No silent overspend.

### 3.6 NEVER let logs include API key strings

`error_log` calls leak. `var_dump` of `$_POST` leaks. The HTTP response body of a 401 leaks. Add a regex-redaction step inside the log writer so the next developer can't accidentally re-introduce the leak.

### 3.7 NEVER assume WP-Cron actually runs on schedule

WP-Cron is page-load triggered. A low-traffic site can go hours without firing. For commercial reliability: recommend setting up a real OS cron + `DISABLE_WP_CRON=true` in wp-config, OR build a watchdog that fires the cron via curl from an external service.

### 3.8 NEVER duplicate render the Featured Image

Block themes render `core/post-featured-image` in the post template. If your plugin ALSO prepends a hero via `the_content`, you get two images. Listen to `render_block_core/post-featured-image` and `post_thumbnail_html` to detect what the theme already rendered, then skip your own injection.

### 3.9 NEVER assume the strict-type version of WP blocks

`wp:query` block attributes like `exclude` and `categories` changed from string-acceptable to MUST-be-array in WP 6.7. Old WXR exports break new sites. Fix: post-process the WXR with `python3` to convert `"categories":"123,456"` → `"categories":[123,456]`.

### 3.10 NEVER skip the verification command output before claiming done

`php -l` on every PHP file, `unzip -p <zip> | grep Version` on every zip. Five seconds. Catches 90% of "I forgot to bump the constant" bugs.

### 3.11 NEVER write a "this is how it works" file and leave it stale

Update the docs file in the SAME commit as the feature. If you ship a feature without updating HOW_IT_WORKS.md, you've shipped half the work.

### 3.12 NEVER ship an "auto" feature without a kill switch

Pause flag, off mode, transient lock, manual reset of stuck state. Every automation needs a brake.

### 3.13 NEVER hardcode timezones

Default to a sensible value (CET / Europe/Amsterdam if your user base is European, UTC otherwise). Read `get_option('timezone_string')` first. If empty + `gmt_offset=0`, set the default on activation. Never overwrite a deliberate operator pick.

### 3.14 NEVER ship without a troubleshooting matrix

Six rows max: symptom → first place to look → likely cause. Goes in HOW_IT_WORKS.md. Saves you from getting paged for the same five issues every week.

### 3.15 NEVER store post bodies in version-control if they will be regenerated

The blog WXR is 1 MB of generated content. Keep it in git (it's the install seed) but tag the regenerator script (`build-blog-content-2026.py`) as the source of truth. Don't hand-edit the WXR.

### 3.16 NEVER let pre-commit hooks block the work

The repo has a stop-hook checking for untracked files. Respect it. Stage and commit before letting the session end.

---

## 4. Per-domain learnings

### 4.1 WordPress block themes (FSE)

- `wp:query` exclude/categories must be arrays in WP 6.7+. Strict.
- Pattern references inside page `post_content` block the editor. Expand them at import time.
- `.wp-block-column { display: flex }` collapses every column site-wide. Don't.
- The post template renders `core/post-featured-image` already; CSS-constrain it via `body.single-post .wp-block-post-featured-image` rather than injecting your own.
- `--wp--style--global--content-size` is the content-column width CSS variable. Use it to match prose width.

### 4.2 WordPress plugin lifecycle

- `register_activation_hook` runs on activation, not on upload. To re-trigger an importer, the operator must deactivate + reactivate, OR you must offer a manual "re-run" button on a settings page.
- `BV_INSTALL_VERSION` style version gating is risky if the option write never lands (e.g. if `bv_import_wxr()` returned false silently). Add explicit "Force re-run" button.
- `cf7_form` shortcode hash IDs collide with our slug shape. Use literal IDs in patterns.
- Asset versioning: `filemtime()` of the CSS file beats baked-in plugin version (cache-bust on every CSS edit).
- `ob_start` + `<?php ?>` mixed mode inside `add_filter` is fragile. Build the string in PHP and `echo` once.

### 4.3 LLM integration

- BYOK (bring your own key) is the standard commercial pattern. Encrypt at rest.
- Adapter pattern per provider (Claude / OpenAI / Gemini) with a shared return shape `[ok, text, raw, provider, model, error]`. Caller never branches on provider.
- Token usage: Anthropic returns `usage.input_tokens` / `usage.output_tokens`. OpenAI returns `usage.prompt_tokens` / `usage.completion_tokens`. Gemini returns `usageMetadata.promptTokenCount` / `usageMetadata.candidatesTokenCount`. Different shapes — normalise once in `extract_token_counts()`.
- Strict JSON output: tell the model "return ONLY the JSON object/array. No commentary, no markdown fences." Even then, expect markdown fences in 5% of responses. Strip `^```(?:json)?\n` and `\n```$` before `json_decode`.
- Master prompt: 80+ lines. Variables `{site_name}`, `{topic}`, `{user_additional}`, `{category_list}`, `{internal_links}`, etc., substituted at generation time. Operator can override; clear-field-and-save reverts to default.
- Custom additional instructions field: appended to BOTH the idea prompt and the writing prompt. One field, two effects. Reduces operator surface area.
- Cost math, Claude May 2026: Sonnet 4.6 = $3 in / $15 out per M tokens. A 1,400 word post = ~5,000 input tokens (prompt + topic + context) + ~3,500 output tokens = $0.07.

### 4.4 GEO / AEO content rules (May 2026 Bible)

Baked into the default CiteLeap master prompt:

- **Statistics addition: +41% AI visibility** (Princeton GEO study, ACM KDD 2024). Include 3+ stats with named-source links in the first 30% of body (the "ski ramp").
- **First 30% = ski ramp**, holds 44.2% of citations (Kevin Indig, 1.2M ChatGPT answers).
- **Listicle format: 74.2% of all AI citations**.
- **Comparison tables: +32.5% citations**.
- **FAQ schema: +60% likely to appear in AI Overviews**.
- **40 to 60 word answer capsule under each question-shaped H2**.
- **75 to 150 word self-contained passages** = 2.3x more citations.
- **Flesch-Kincaid grade 14-16**, a statistic or named reference every 150-200 words.
- **Keyword stuffing: -8.7%** (worse than baseline). Never do.
- **Brand mentions correlate 3x stronger** with AI citation than backlinks (0.664 vs 0.218).

### 4.5 Schema markup (the high-value ones AI engines actually parse)

Always JSON-LD (`<script type="application/ld+json">`). Triple-stacking pattern: **Article + ItemList + FAQPage** on every post.

- Organization (site-wide) , `sameAs` linking Wikipedia / LinkedIn / Crunchbase / Wikidata is particularly important for cross-referencing
- WebSite with SearchAction
- BlogPosting with `headline`, `description`, `datePublished`, `dateModified`, `author` (Person sub-entity with own `sameAs`), `image` (with width/height), `wordCount`
- BreadcrumbList
- FAQPage (auto-detected from H2 "Frequently asked questions" + H3/P pairs)
- HowTo (auto-detected when title starts with "How to" + ordered list ≥3 items)

Strict alignment between schema and visible content. If schema says "In Stock" but page says "Sold Out", AI extraction confidence drops to zero.

### 4.6 IndexNow protocol

Critical for Bing / Yandex / Copilot visibility. AI referrals through Copilot grew 357% YoY to 1.13B visits in June 2025.

- POST to `https://api.indexnow.org/IndexNow` with `{ host, key, keyLocation, urlList }`.
- Generate a 32-char key once, store in `wp_options`, also write `/<key>.txt` to site root (the verification mechanism).
- Use `blocking=false, timeout=1` so the publish save is never blocked.

### 4.7 Multi-LLM model lineup (verified May 2026)

| Provider | Reasoning | Writing | Cheap |
|---|---|---|---|
| Anthropic Claude | Opus 4.7 ($5/$25 per M) | Sonnet 4.6 ($3/$15) | Haiku 4.5 ($1/$5) |
| OpenAI | GPT-5.5-pro | GPT-5.5 | GPT-5.4-mini |
| Google Gemini | Gemini 3.1 Pro (1M ctx) | Gemini 2.5 Pro | Gemini 2.5 Flash |

Claude Sonnet 4.6 is preferred over Sonnet 4.5 by 70% of developers and over Opus 4.5 by 59%. Daily-driver writer for most paid use cases.

### 4.8 Brevo / SMTP form integration gotchas

Three failure modes combined to hang form spinners:

1. **PHP notice display in REST**: `_doing_it_wrong` echoes HTML before `wp_send_json`. JSON parse fails. Spinner spins. Fix: `@ini_set('display_errors', '0')` at `rest_pre_dispatch` priority 1.
2. **Brevo SMTP `to[].name` required**: payload missing `name` returns 400. Fix: inject derived name (from email local-part) via `http_request_args` filter on `api.brevo.com`.
3. **Brevo contacts upsert**: duplicate email returns 400. Fix: add `updateEnabled: true` to the POST `/v3/contacts` body.

Plus front-end JS auto-fills the Name field from the Email as the visitor types. Belt and braces.

### 4.9 Multi-tenant timezones

- Site `timezone_string` is the canonical source.
- If empty AND `gmt_offset=0` (vanilla install), set a sensible default on activation.
- For European customers: `Europe/Amsterdam` covers CET in winter + CEST in summer via DST.
- WP scheduled posts use site tz, so once `timezone_string` is set, future-posts publish at wall-clock local time without extra plumbing.

---

## 5. Pre-flight checklist for any new WP plugin

Run through this before writing the first line of code. 20 minutes well-spent.

### 5.1 Repository
- [ ] On the correct branch? Run `git status`. Stash if dirty.
- [ ] Latest from main pulled?
- [ ] Plugin slug + directory name agreed?

### 5.2 Plugin header
- [ ] Name, URI, Description (with the "what + why"), Version (start 1.0.0), Requires at least, Requires PHP, Author, Author URI, License (GPL-2.0-or-later), License URI, Text Domain (matches slug), Domain Path (`/languages`), Update URI (`false` for private)

### 5.3 Files scaffolded
- [ ] `<slug>.php` main file with header + ABSPATH guard
- [ ] `uninstall.php` at root with `WP_UNINSTALL_PLUGIN` guard + multisite loop
- [ ] `readme.txt` with Stable tag, Description, Installation, FAQ, Changelog, Privacy sections
- [ ] `/languages/.gitkeep` placeholder
- [ ] `/assets/` if there will be CSS or JS

### 5.4 Lifecycle hooks
- [ ] `register_activation_hook`: verify PHP version, register the uninstall hook here once
- [ ] `register_deactivation_hook`: unschedule any cron events
- [ ] `register_uninstall_hook`: callback name documented

### 5.5 Security & secrets
- [ ] If storing API keys: encryption helper (AES-256-CBC, key from AUTH_KEY), masked UI, redacted logs
- [ ] All admin entry points: `current_user_can( 'manage_options' )`
- [ ] All forms: `wp_nonce_field` + `check_admin_referer`
- [ ] All inputs: `wp_unslash` + sanitize_*
- [ ] All outputs: esc_html / esc_attr / esc_url / esc_textarea / wp_kses_post

### 5.6 Observability (if the plugin does long work)
- [ ] Activity log option (200-entry ring buffer, redacted)
- [ ] Severity field (info / warn / error / critical)
- [ ] Dashboard / log tab in admin
- [ ] Progression overlay JS for sync admin-post handlers

### 5.7 Documentation
- [ ] HOW_IT_WORKS.md inside the plugin folder
- [ ] Troubleshooting matrix
- [ ] State machine doc-block in code if there is state
- [ ] Inline comment with the WHY for any non-obvious decision

### 5.8 Release verification
- [ ] `php -l` on every PHP file
- [ ] WXR parse via Python if shipping content
- [ ] `unzip -p` verifies Version + Stable tag inside the zip
- [ ] Versioned filename matches the version
- [ ] Old versioned zip deleted from working tree

---

## 6. Step-by-step blueprint: new commercial WP plugin from zero

Followed by CiteLeap. Adapt as needed.

1. **Day 1 — Spec.** Write a single-page brief: what does the plugin DO in one sentence, who is the user, what is the simplest happy path, what is explicitly out of scope. Pin to a docs/spec.md.
2. **Day 1 — Branch.** `git checkout -b claude/<plugin-name>` so the work is isolated.
3. **Day 1 — Scaffold.** Plugin header, ABSPATH guard, uninstall.php, readme.txt skeleton. `php -l` clean.
4. **Day 1 — Settings.** Bare admin page accessible from the WP sidebar. Save one option as a sanity check.
5. **Day 2 — Core loop.** The one thing the plugin must do. End to end. Ugly is fine.
6. **Day 2 — Hardening.** Nonces, caps, sanitization, escaping. `php -l` clean. WordPress Plugin Check installed and run.
7. **Day 3 — Observability.** Activity log, severity field, dashboard with KPI strip, log table, success / error split.
8. **Day 3 — Documentation.** HOW_IT_WORKS.md with the four core flows, troubleshooting matrix, where-data-lives table.
9. **Day 4 — Edge cases.** Failure modes, retry, pause, race protection, transient locks.
10. **Day 4 — Version + ship.** Bump to 1.0.0. Build versioned zip. `unzip -p` check. Commit. Push.
11. **Day 5 — User feedback.** Ship to one user. Listen. Iterate.

The CiteLeap session went 1.0.0 → 1.6.0 in one day because each version was a focused increment of feedback, not a redesign.

---

## 7. Decision log (things that surprised us)

| Discovery | Implication |
|---|---|
| `wp:query` attributes are strict-typed in WP 6.7+ | Old WXR exports break. Post-process before import. |
| `register_activation_hook` does NOT fire on plugin upload, only on activation | The "Replace current with uploaded" flow re-activates; FTP overwrite does not. Offer a manual "re-run" button. |
| Pattern references in page `post_content` block the editor | Expand them at import time, store the rendered markup. |
| `cf7_form` shortcode hash IDs (`[a-f0-9]{6,8}`) collide with our slug shape | Use literal IDs in patterns. |
| `.wp-block-column { display: flex }` cascades to every column site-wide | Scope CSS by post-template class. |
| Filemtime versioning beats baked-in version constants for cache-bust | Use `(string) filemtime($path)` as the `wp_enqueue_*` version arg. |
| `core/post-featured-image` block renders by default in single-post templates | Inject your own hero only if not already rendered. |
| Brevo SMTP requires `name` in every `to[]` | The Brevo WP plugin sometimes omits it. Inject server-side via `http_request_args` filter. |
| Brevo `/v3/contacts` returns 400 on duplicate email by default | Set `updateEnabled: true` to upsert. |
| PHP notice output during REST corrupts JSON | `@ini_set('display_errors','0')` at `rest_pre_dispatch` priority 1. |
| WP-Cron is page-load triggered | Low-traffic sites need real OS cron. |
| Claude Sonnet 4.6 is preferred over Opus 4.5 by 59% of developers | Default writer for cost-aware use cases. |
| AI-search referrals convert at 3.49% (22% higher than organic 2.6%) | Worth optimising for even at low traffic volumes. |
| 82% of AI citations come from earned media | On-site optimisation is half the game. |
| Only 11% domain overlap between ChatGPT and Perplexity citations | Each platform needs distinct strategy. |
| WP scheduled posts use site timezone | Setting `timezone_string` on activation makes future-posts publish at wall-clock local time. |
| The "REFRESH" tag on planner rows changes the row order semantics | Pinned > in-flight > drafted > scheduled > queued_refresh > queued > failed. Sort order matters for operator scanning. |
| Empty input field can mean "leave unchanged" or "blank the field" | For masked secrets, empty submit must PRESERVE the existing value, not blank it. |

---

## 8. Reference index (where to find things in this repo)

### Theme
- `wp-content/themes/booming-venture/style.css` — version + theme metadata
- `wp-content/themes/booming-venture/functions.php` — `BV_THEME_VERSION` constant
- `wp-content/themes/booming-venture/inc/seo.php` — JSON-LD generation
- `wp-content/themes/booming-venture/inc/geo.php` — IndexNow + "Last updated" banner
- `wp-content/themes/booming-venture/import/booming-venture-content.xml` — the WXR seed
- `CLAUDE.md` (repo root) — voice + GEO rules + release discipline

### Booming Venture Importer (full-site)
- `wp-content/plugins/booming-venture-importer/booming-venture-importer.php` — main file
- `wp-content/plugins/booming-venture-importer/import/booming-venture-content.xml` — bundled WXR copy

### Booming Venture Blog Importer (content-only)
- `wp-content/plugins/booming-venture-blog-importer/booming-venture-blog-importer.php`
- `wp-content/plugins/booming-venture-blog-importer/assets/blog-post.css` — mobile-first single-post styling

### Brevo Form Fix
- `wp-content/plugins/booming-venture-brevo-fix/booming-venture-brevo-fix.php`
- `wp-content/plugins/booming-venture-brevo-fix/assets/auto-name.js` — front-end auto-fill

### Blog Images (own branch)
- `wp-content/plugins/booming-venture-blog-images/booming-venture-blog-images.php`
- `wp-content/plugins/booming-venture-blog-images/assets/blog-images.css` — 16:9 constrained
- `wp-content/plugins/booming-venture-blog-images/assets/pool-admin.js` — wp.media picker

### SEO Boost (own branch)
- `wp-content/plugins/booming-venture-seo-boost/booming-venture-seo-boost.php`

### CiteLeap (own branch)
- `wp-content/plugins/citeleap/citeleap.php` — bootstrap
- `wp-content/plugins/citeleap/includes/crypto.php` — AES-256-CBC at rest
- `wp-content/plugins/citeleap/includes/prompts.php` — master + idea prompts (GEO/AEO defaults)
- `wp-content/plugins/citeleap/includes/pricing.php` — May 2026 model pricing
- `wp-content/plugins/citeleap/includes/usage.php` — 12-month token + cost ledger
- `wp-content/plugins/citeleap/includes/llm.php` — Claude / OpenAI / Gemini adapters
- `wp-content/plugins/citeleap/includes/generator.php` — idea generator + writer
- `wp-content/plugins/citeleap/includes/refresh.php` — refresh queue + 3-state mode
- `wp-content/plugins/citeleap/includes/actions.php` — state machine + per-row handlers
- `wp-content/plugins/citeleap/includes/scheduler.php` — WP-Cron tick with lock
- `wp-content/plugins/citeleap/includes/dashboard.php` — KPIs + budget bars + log split
- `wp-content/plugins/citeleap/includes/planner.php` — single planner UI
- `wp-content/plugins/citeleap/includes/settings.php` — settings + prompts + flash
- `wp-content/plugins/citeleap/assets/admin.js` — progression overlay
- `wp-content/plugins/citeleap/assets/admin.css` — overlay + flow grid styles
- `wp-content/plugins/citeleap/HOW_IT_WORKS.md` — plain-language operator manual

---

## 9. Commercial readiness scorecard (CiteLeap v1.6.0)

The honest gap list before selling to strangers:

| Category | Status | Action before commercial launch |
|---|---|---|
| Security (encryption, masking, redaction, nonces, caps, escaping) | ✅ shipped | — |
| Race protection (transient lock + try/finally) | ✅ shipped | — |
| Observability (severity logs, dashboard, troubleshooting matrix) | ✅ shipped | — |
| Multi-provider abstraction (Claude / OpenAI / Gemini, BYOK) | ✅ shipped | — |
| Three-state auto modes both for new content AND refresh | ✅ shipped | — |
| Coherent per-row action set with pause / resume / retry | ✅ shipped | — |
| Progression overlay for sync admin-post calls | ✅ shipped | — |
| CET / sensible-default timezone | ✅ shipped | — |
| PHPUnit test suite | ❌ missing | 2-3 days. Focus crypto + usage + scheduler + state-machine transitions |
| License + activation server | ❌ missing | Use Freemius or EDD Software Licensing |
| Update server (Update URI = false today) | ❌ missing | Same as above |
| Automatic retry on 5xx / 429 with exponential backoff | ❌ missing | Half day |
| Editor-role capability (currently `manage_options` only) | ❌ missing | Half day |
| Accessibility audit on admin pages (WCAG 2.1 AA for EU AI Act + EAA 2025) | ❌ missing | 1 day |
| GDPR consent UX (today only docs in HOW_IT_WORKS) | ⚠️ partial | Half day |
| OpenAI / Gemini pricing verification (current values are best-effort estimates) | ⚠️ partial | Quick |
| Master-prompt versioning + "migrate to latest default" button | ❌ missing | Half day |

Two-week commercial-launch sprint: tests + Freemius + retry + editor cap + accessibility + GDPR consent + pricing verification.

---

## 10. Quick-reference commands

```bash
# PHP lint every file in a plugin
php -l wp-content/plugins/<slug>/<slug>.php wp-content/plugins/<slug>/uninstall.php
php -l wp-content/plugins/<slug>/includes/*.php

# Validate the WXR
python3 -c "import xml.etree.ElementTree as ET; ET.parse('wp-content/themes/booming-venture/import/booming-venture-content.xml'); print('WXR OK')"

# Build a versioned zip
rm -f wp-content/plugins/<slug>-<old>.zip wp-content/plugins/<slug>.zip
(cd wp-content/plugins && zip -rq <slug>-<new>.zip <slug> -x "*.DS_Store")
cp wp-content/plugins/<slug>-<new>.zip wp-content/plugins/<slug>.zip

# Verify the version string inside the zip
unzip -p wp-content/plugins/<slug>-<new>.zip <slug>/<slug>.php | grep -E "Version:|VERSION  ?=" | head -2
unzip -p wp-content/plugins/<slug>-<new>.zip <slug>/readme.txt | grep "Stable tag"

# Word-count a WXR post body
python3 -c "import re; print(len(re.sub('<[^>]+>',' ',open('post.xml').read()).split()))"

# Force re-import on a live WP site (via the plugin)
# WP admin -> Tools -> Booming Venture Importer -> Force re-import content (WXR)

# Inspect WP-Cron events
wp cron event list   # via WP-CLI
```

---

## 11. The one-page "before you start coding" reminder

1. Read this file.
2. Identify which branch you should be on.
3. Read the HOW_IT_WORKS.md of the plugin you are about to touch.
4. Use the pre-flight checklist (section 5).
5. Bump the version BEFORE writing the change.
6. Write the change.
7. Update HOW_IT_WORKS.md + readme.txt changelog in the same commit.
8. `php -l` every touched file.
9. Build the versioned zip.
10. `unzip -p` check.
11. Commit with a doc-block-style message explaining WHY.
12. Push.

This loop avoided the most common time sinks today. Trust the process.
