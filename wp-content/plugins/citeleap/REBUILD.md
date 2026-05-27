# CiteLeap , Rebuild Guide

> **Read this top to bottom before writing a single line of code.** This is
> the complete blueprint for rebuilding the **CiteLeap , AI Blog Writer &
> GEO/AEO Content Engine for WordPress (Claude, OpenAI, Gemini)** plugin
> from zero with Claude Code (or any AI coding assistant). It documents
> every functionality, every user story, every interdependency, every
> intradependency, and the order to build them in.
>
> **Audience:** a future Claude Code session, a contractor, or a human
> engineer who needs to rebuild CiteLeap end-to-end. The document is
> self-contained , no other internal docs needed.

---

## 0. Mission, in one paragraph

CiteLeap is a commercial WordPress plugin that closes the loop from "I
have a blog topic" to "the post is published with real citations, the
right schema, an Open Graph image, an IndexNow ping, and a hreflang tag
in the right language." It uses the operator's own LLM API keys (BYOK
across Claude, OpenAI, and Gemini), bills per credit (1 credit = 1
draft or 1 refresh), and ships under a hybrid pricing model (base
subscription + included credits + overage + top-up packs).
**Differentiator vs Jasper, Copy.ai, Surfer:** real web research with
named citations, GEO/AEO May 2026 Bible compliance, multilingual + hreflang
out of the box, and a credit gate that actually blocks the LLM call
when the operator is out of budget.

---

## 1. Day 0 , research before code

**Do not start coding until you have completed this section.** Skipping
ahead is the #1 reason CiteLeap-shaped projects derail.

### 1.1 Read the WordPress Plugin Handbook (~2 hours)

- <https://developer.wordpress.org/plugins/> , the whole handbook.
- Specifically: Security (nonces, sanitisation, escaping, capability
  checks), Plugin Basics (file headers, hooks, lifecycle), Internationalisation,
  the Privacy chapter (`wp_add_privacy_policy_content`,
  `wp_privacy_personal_data_exporters`,
  `wp_privacy_personal_data_erasers`), WP-Cron, Settings API, and the
  Plugin Directory submission rules.

### 1.2 Deep-dive each LLM provider's API documentation

You need three abstractions (reasoning, research, writing) wired to
three providers (Claude, OpenAI, Gemini). For each provider, read the
**latest** docs:

#### 1.2.1 Anthropic Claude

- Messages API endpoint structure, `messages.create` parameters, model
  list, pricing per million tokens.
- **Web search tool** (`web_search_20250305` for the Claude Sonnet 4+
  / Opus 4+ family) , this is the **single most important API call in
  CiteLeap** because it powers the research role natively without an
  out-of-band SERP API. Read the tool spec, max_uses limit, the way
  citations come back as document references, and how to ask the model
  to return strict JSON afterward.
- Streaming + non-streaming difference. CiteLeap uses non-streaming
  because the writer call is synchronous from an admin form-post.
- Rate limits per tier + the `retry-after` header. CiteLeap retries
  with backoff (1s, 3s, 7s) on HTTP 429 / 5xx / "overloaded" /
  "unavailable" / network timeouts.
- Model selection guidance:
  - **Reasoning role** , Opus (best ideation, best at picking
    differentiated topics, justifying priority scores).
  - **Research role** , Opus or Sonnet with `web_search_20250305`
    attached (Opus gives better source ranking, Sonnet is cheaper).
  - **Writing role** , Sonnet (best long-form writer, ~70% developer
    preference vs Opus for 1,500-word posts), Haiku for budget tiers.

#### 1.2.2 OpenAI

- Chat Completions endpoint, model list, pricing.
- Whether the operator's chosen model supports web search / Browse via
  the Tools mechanism (varies by model; recent reasoning models do).
- Structured-output mode (JSON mode + JSON schema) , CiteLeap relies
  on this for ideation (returns array of `{title, slug, angle, priority,
  lang}`) and drafting (returns `{title, slug, body, excerpt,
  meta_description, faqs}`).
- Model selection guidance:
  - **Reasoning role** , GPT-5.5-pro or current top reasoning model.
  - **Research role** , model with browsing/web-search tool enabled.
  - **Writing role** , GPT-5.5 or GPT-5.4 for budget.

#### 1.2.3 Google Gemini

- Generative Language API endpoint, model list, pricing per million
  tokens, 1M-token context window for Gemini Pro.
- Native Google Search grounding tool (the Gemini-equivalent of
  Anthropic's `web_search_20250305`).
- Structured-output mode (response schema).
- Model selection guidance:
  - **Reasoning role** , Gemini Pro (latest), 1M context useful when
    feeding lots of "existing slugs to avoid duplicating".
  - **Research role** , Gemini Pro with grounding enabled.
  - **Writing role** , Gemini Pro for quality, Gemini Flash for budget,
    Flash-Lite for ultra-budget.

### 1.3 Read the GEO/AEO May 2026 Bible

CiteLeap is opinionated about what "good content" looks like. Required
reading before designing the master prompt:

- **Princeton GEO study** , 9 ranking factors that earn citations on
  generative engines (statistics, quotes, citations, authoritative
  sources, fluency).
- **Kevin Indig "Ski Ramp" analysis** , the first 30% of body content
  is where ChatGPT / Perplexity / AI Overviews look for facts.
- **Ahrefs 560k AI Overview corpus** , question H2s + 40-60 word answer
  capsules win citations.
- Schema.org FAQPage, HowTo, Article, Person, SoftwareApplication
  templates. Validate against schema.org/validator and Google's Rich
  Results test before shipping.

### 1.4 Read the Freemius docs

- The "How to convert a free WP plugin to paid in 24 hours" guide.
- SDK installation pattern (`vendor/freemius/wordpress-sdk/start.php`).
- The `fs_dynamic_init` shape (id, slug, public_key, premium flag,
  paid plans config, menu binding, trial config).
- Webhook events: `fs_after_purchase_<slug>`,
  `fs_after_account_plan_change_<slug>`,
  `fs_after_account_user_change_<slug>`.
- One-off "lifetime" purchases for top-up credit packs.
- Merchant-of-Record VAT handling.

### 1.5 Read the WordPress accessibility coding standards

- WCAG 2.1 AA targets , `aria-current`, `role="alert"` vs `role="status"`,
  `aria-live` politeness, focus-visible, forced-colors fallback,
  prefers-reduced-motion.
- WP's `.screen-reader-text` utility convention.

**Output of Day 0:** a one-page "what I will build, in what order, on
top of which APIs" doc. Show it to the operator before writing line 1.

---

## 2. Architecture overview

```
                                          ,  external providers (BYOK)
                                          ,
operator                                   ,
   ,                                       ,
   ,  wp-admin , CiteLeap menu             ,
   ,                                       ,
   v                                       ,
+----------------+    +------------------+ ,    +-----------+
|  Admin UI      | -> |  CiteLeap_LLM    |---+->| Anthropic |
|  (Settings,    |    |  router          |   +->| OpenAI    |
|   Planner,     |    |  (retry+backoff) |   +->| Gemini    |
|   Calendar,    |    +------------------+ ,    +-----------+
|   Dashboard,   |             |           ,
|   Log,         |             v           ,
|   License)     |    +------------------+ ,
+-------+--------+    |  CiteLeap_       | ,
        |             |  Research        | ,    +-----------+
        |             |  (Claude native  | --+->| Serper    |
        v             |   web_search +   | --+->| Brave     |
+----------------+    |   3 SERP API     | --+->| Tavily    |
| CiteLeap_      |    |   fallbacks)     | ,    +-----------+
| Generator      |    +------------------+ ,
| (write_post    |             |           ,
|  _from_idea)   |             v           ,
+-------+--------+    +------------------+ ,    +-----------+
        |             |  CiteLeap_LLM    | --+->| (provider |
        |             |  chat('writing') | ,    |   chosen  |
        |             +------------------+ ,    |  by op)   |
        v                                  ,    +-----------+
+----------------+
| wp_insert_post |
| + meta + cats  |
| + featured img |  <-- from CiteLeap_Images pool
+-------+--------+
        |
        v
+----------------+    +------------------+
| CiteLeap_SEO   | -> | IndexNow ping    |  on publish
| (schema, OG,   |    | Google+Bing      |
|  Twitter,      |    | sitemap submit   |
|  canonical)    |    +------------------+
+----------------+
```

**Commercial layer wraps every billable LLM call:**

```
draft / refresh request
        |
        v
+----------------+
| CiteLeap_Caps  |  user authorisation (Editor vs Admin)
+----------------+
        |
        v
+----------------+
| CiteLeap_Plan  |  feature gate (refresh? multilingual? calendar?)
+----------------+
        |
        v
+----------------+
| CiteLeap_      |  credit gate (>=1 available?)
| Credits        |
+----------------+
        |  [allow]                              [deny]
        v                                        v
   LLM call                          return error + log credit_blocked
        |
   on success
        v
+----------------+
| CiteLeap_      |  consume( 1, action )
| Credits        |  + CiteLeap_Usage::record_tokens()
+----------------+
```

---

## 3. User stories

Use these as acceptance criteria. Every story has a primary persona
(Owner = paying customer / Administrator), and some stories mention
Editor (delegated content user).

### 3.1 Onboarding (Owner)

1. As an Owner, I install CiteLeap from the zip via Plugins , Add New ,
   Upload Plugin and activate it. The plugin registers a top-level
   CiteLeap menu in wp-admin within 2 seconds of activation.
2. As an Owner, on first activation I see a data-flow disclosure
   notice telling me exactly what gets sent to which provider (Claude,
   OpenAI, Gemini, Freemius). I dismiss it; the dismissal is recorded
   in my user meta so I don't see it again.
3. As an Owner I open Settings, paste my Claude API key, pick Opus 4.7
   for reasoning + research + Sonnet 4.6 for writing, set my publishing
   cadence to "3 posts per week starting next Monday at 10:00 CET",
   click Save. Settings are encrypted at rest with AES-256-CBC.
4. As an Owner I click "Test connection" per provider; the plugin
   sends a tiny request and confirms my key + quota + network work.

### 3.2 Ideation & planner (Owner)

5. As an Owner I click "Generate ideas now" on the Planner tab. The
   reasoning model brainstorms 10 unique topics, ranked by priority,
   dedupe'd against existing post slugs, returned as a JSON array.
6. As an Owner I can also paste a list of my own topics (one per line),
   each becomes a queued item with `source=manual` and `priority=5`.
7. As an Owner I see every queued item in the Planner queue with its
   status badge (queued / drafted / scheduled / published / refreshed /
   failed / pending_review / paused), title, slug, priority, and a per-
   row action map.

### 3.3 Plan + schedule (Owner)

8. As an Owner I can "Plan" a specific datetime per queue row using a
   native `<input type="datetime-local">`. The scheduler auto-tick
   honours pinned datetimes ahead of the auto-computed slot.
9. As an Owner I see a Calendar tab showing the month-grid with green
   checks for manually planned items, blue checks for auto-planned,
   and R badges for refresh items. Drag to reschedule (or click to
   pin).
10. As an Owner I can pause / resume / retry / unschedule / publish-now
    / approve / reject any queue row from the Planner row controls.

### 3.4 Drafting (Owner, or Editor on Solo+)

11. As an Owner I click "Write draft" on a queued topic. CiteLeap:
    a. Checks `CiteLeap_Caps::can_use()` and `CiteLeap_Plan::has(...)`.
    b. Checks `CiteLeap_Credits::can_consume( 1 )`. If exhausted,
       returns "Out of credits this cycle. Upgrade or top up." and
       logs `credit_blocked`.
    c. Fetches real sources for the topic via `CiteLeap_Research::
       fetch_sources()` , Claude native web_search if writer is
       Claude, Serper / Brave / Tavily otherwise, or the research-role
       Claude model returning strict JSON.
    d. Fetches the layout block (theme.json content-size + accent +
       fonts + registered patterns).
    e. Fetches the voice-samples block (3 most recent posts).
    f. Fetches the internal-links candidates (40 nearby slugs).
    g. Fetches the language block (lang + hreflang + Polylang/WPML
       integration).
    h. Renders the master prompt template with placeholders
       `{topic}` `{language_block}` `{layout_block}`
       `{voice_samples_block}` `{research_block}` `{internal_links_block}`
       `{user_additional}`.
    i. Calls `CiteLeap_LLM::chat( 'writing', system, user, 8000 )`
       with retry-with-backoff.
    j. Parses the JSON response. Validates `title`, `body`, `slug`,
       `excerpt`, `meta_description`, `faqs`.
    k. Strips em-dashes defensively from every field.
    l. `wp_insert_post()` as draft, assigns category (mandatory
       fallback chain), assigns featured image from the pool if no
       manual pick.
    m. Writes meta_description into Yoast / AIOSEO / Rank Math + own
       meta key.
    n. Stores `_citeleap_sources` (outbound links audit) and
       `_citeleap_internal_links` (internal slug list used).
    o. Consumes 1 credit, logs `post_drafted` + `credit_consumed`.
12. As an Editor (not Admin), I can do everything in 3.4.11 but I
    cannot change API keys, prompts, models, or buy top-ups.

### 3.5 Schedule + publish (Owner)

13. As an Owner with `auto_mode=publish`, the WP-Cron tick (hourly)
    drafts the highest-priority pending idea, schedules it at the next
    cadence slot, and `wp_publish_post`s it when due. Pinned rows go
    first.
14. As an Owner with `auto_mode=draft`, the same tick drafts but
    leaves the post in WP draft status for manual review.
15. As an Owner with `auto_mode=off`, nothing happens automatically.
16. As an Owner with `refresh_auto_mode=draft`, the refresh tick
    proposes updates to old posts and parks them in `pending_review`
    state until I Approve or Reject.
17. As an Owner with `refresh_auto_mode=live`, refresh updates
    overwrite the live post directly.

### 3.6 SEO + publish ping (Owner)

18. As an Owner on a site with no SEO plugin detected, CiteLeap
    injects JSON-LD (Article + FAQPage + auto-HowTo when steps are
    present + BreadcrumbList + Organization + WebSite), Open Graph,
    Twitter Card, and a canonical URL into `<head>`.
19. As an Owner on a site with Yoast / Rank Math / AIOSEO / SEOPress /
    TSF active, CiteLeap stands down per-tag so we never double-write.
20. On every post publish, CiteLeap fires IndexNow to Bing / Yandex /
    Naver and submits the sitemap to Google + Bing.

### 3.7 Multilingual (Owner, Pro+)

21. As an Owner with Polylang or WPML active, CiteLeap detects them
    and writes posts in the language the topic is assigned to, with
    hreflang tags pointing to the translated versions.
22. As an Owner without Polylang/WPML, the language block in the
    prompt still routes the writer to my chosen target language; the
    post is written in that language with a `_citeleap_lang` meta tag.

### 3.8 Images (Owner)

23. As an Owner I open the Images tab, click "Open Media Library",
    multi-select images, click Save. The pool is persisted.
24. As an Owner I see a status panel: images in pool, total published
    posts, posts without a Featured image, posts random-assigned.
25. As an Owner I click "Assign random images now"; every published
    post without a Featured image gets one from the pool. Posts I
    manually picked are never touched.
26. As an Owner I click "Re-randomise"; only posts previously
    random-assigned by us get re-rolled.
27. The image becomes the og:image automatically and renders as a
    hero on single posts + as a card thumbnail in the archive (with
    theme-override CSS that constrains
    `body.single-post .wp-block-post-featured-image` to 16:9 at
    content-size width).

### 3.9 Commercial layer (Owner)

28. As an Owner on the Free plan I get 3 lifetime credits. After they
    run out, drafting and refreshing return "Out of credits".
29. As an Owner I see a credit banner on every CiteLeap page (red
    error when exhausted, amber when low, quiet info pill when healthy,
    green when Developer mode).
30. As an Owner I click "Upgrade plan" on the License & Credits tab;
    the Freemius checkout opens. After purchase, my plan flips to Solo
    / Pro / Agency / Enterprise. Plan changes reset the monthly cycle.
31. As an Owner I click "Buy top-up pack"; I can purchase Starter /
    Growth / Scale / Bulk. Top-up credits never expire and are consumed
    AFTER monthly included credits.
32. As an Owner I see the plan capability matrix on the License tab so
    I know exactly what I do or do not have on my plan.
33. As an Owner on the Free plan, gated tabs (Calendar, Languages)
    show me an "Upgrade to Pro" card with the per-month price and a
    one-click checkout CTA, instead of hiding them.

### 3.10 GDPR / privacy (Owner)

34. As an Owner I open Tools , Privacy; CiteLeap's privacy policy text
    is already registered for me to paste into my published policy.
35. As an Owner I open Tools , Export Personal Data against my admin
    email; I get a JSON export of my plan, credit ledger, usage
    history, budget caps.
36. As an Owner I open Tools , Erase Personal Data against my admin
    email; CiteLeap wipes my credit ledger, encrypted API keys,
    activity log, and token usage history. (Plan stays since it lives
    with Freemius.)

### 3.11 Observability (Owner)

37. As an Owner I see a Dashboard tab with this-month token usage +
    cost per provider, monthly cap progress bars, status counts,
    recent successes/errors split by severity.
38. As an Owner I set monthly budget caps per provider + an overall
    cap. Generation refuses with a logged reason once a cap is reached.
    Resets on the 1st of every month.
39. As an Owner I see an Activity Log of every operation with severity
    (info / warn / error / critical). API keys auto-redact in log
    output.

### 3.12 Uninstall

40. As an Owner I deactivate + delete the plugin; every `citeleap_*`
    option in `wp_options` is wiped (including the credit ledger), the
    USE capability is revoked from every role, and no orphan data is
    left.

---

## 4. File structure

Every file the rebuild needs. Read this whole section before creating
the first file.

```
wp-content/plugins/citeleap/
├── citeleap.php                 , Plugin header, constants, requires, activation,
│                                  uninstall, cron registration, Freemius bootstrap.
├── readme.txt                   , WP.org listing. Title is the SEO/GEO/AEO name.
│                                  Tags list = 24 keyword bundle. Stable tag tracked.
├── composer.json                , Dev-only phpunit/phpunit ^11. zero runtime deps.
├── phpunit.xml.dist             , strict mode, failOnRisky + failOnWarning.
├── .gitignore                   , vendor/, .phpunit.cache/, composer.lock
├── REBUILD.md                   , this document.
├── HOW_IT_WORKS.md              , operator-facing manual (ships in the zip).
├── assets/
│   ├── admin.css                , wp-admin styles + a11y rules (focus-visible,
│   │                              forced-colors fallback, prefers-reduced-motion,
│   │                              .screen-reader-text).
│   ├── admin.js                 , progression overlay JS, vanilla, no AJAX rewrite.
│   └── blog-post.css            , frontend: hero + card + theme-override for
│                                  body.single-post .wp-block-post-featured-image.
├── includes/
│   ├── crypto.php               , AES-256-CBC encrypt/decrypt using AUTH_KEY salt.
│   ├── caps.php                 , two-tier capability model:
│   │                              USE_CAP=citeleap_use (Editor + Admin)
│   │                              MANAGE_CAP=manage_options (Admin only)
│   ├── license.php              , Freemius wrapper + mock fallback.
│   ├── plan.php                 , 5 plans + dev, capability gates, locked-card
│   │                              + inline-nudge renderers.
│   ├── credits.php              , per-cycle ledger, consume / top-up / cycle reset,
│   │                              admin banner method.
│   ├── topups.php               , one-off pack catalog, Freemius webhook listeners,
│   │                              in-admin simulator for pre-launch testing.
│   ├── prompts.php              , master prompt template + GEO/AEO defaults.
│   ├── pricing.php              , per-million-tokens pricing table + LAST_VERIFIED_AT.
│   ├── usage.php                , token + cost ledger (last 12 months).
│   ├── llm.php                  , multi-provider router (Claude/OpenAI/Gemini),
│   │                              retry-with-backoff, attaches web-search tool for
│   │                              Claude when research is on.
│   ├── layout.php               , reads wp_get_global_settings (contentSize, accent,
│   │                              fonts) + registered block patterns , prompt block.
│   ├── linking.php              , builds internal-link candidates (lang-aware).
│   ├── research.php             , real web research:
│   │                              - claude_native (web_search_20250305 attached to
│   │                                writer call OR fetch_via_research_role for
│   │                                non-Claude writers)
│   │                              - serper / brave / tavily fallbacks
│   │                              - extract_outbound() audits outbound links.
│   ├── i18n.php                 , 7 languages (en/es/pt/fr/de/it/nl), Polylang +
│   │                              WPML detection, hreflang emission.
│   ├── images.php               , pool, auto-assign on save_post_post +
│   │                              transition_post_status, hero injection on
│   │                              the_content, card on get_the_excerpt +
│   │                              render_block(core/post-excerpt), bulk Assign /
│   │                              Re-roll, posts list column, og:image helper.
│   ├── seo.php                  , detects existing SEO plugins, injects JSON-LD +
│   │                              OG + Twitter + canonical only when no other
│   │                              plugin handles them; IndexNow on publish.
│   ├── voice.php                , pulls 3 recent posts as voice-match samples.
│   ├── calendar.php             , month-grid renderer (plan-gated to Pro+).
│   ├── generator.php            , write_post_from_idea() , the central drafting
│   │                              entrypoint. Em-dash strip, category fallback,
│   │                              word-count guardrail.
│   ├── refresh.php              , three-state refresh (off / draft / live),
│   │                              pending_review state, bulk add by paste-list,
│   │                              reset_stuck.
│   ├── actions.php              , unified state machine for queue row actions
│   │                              (pause / resume / publish_now / unschedule /
│   │                              retry / schedule / rescheduled / approve / reject).
│   ├── scheduler.php            , WP-Cron tick: top-up queue with new ideas, draft
│   │                              the next, schedule the next slot, distribute
│   │                              queued topics evenly across the calendar.
│   ├── dashboard.php            , KPI tiles, log split by severity.
│   ├── planner.php              , single planner UI with the queue, manual draft
│   │                              + manual schedule + Plan button.
│   ├── privacy.php              , wp_add_privacy_policy_content,
│   │                              wp_privacy_personal_data_exporters/erasers,
│   │                              first-run disclosure notice.
│   └── settings.php             , 10 tabs (Dashboard, Planner, Calendar, Prompts,
│                                  Research, Images, SEO, Languages, Settings,
│                                  License & Credits, Log), nav rendering with
│                                  cap-aware tab list + aria-current.
├── languages/                   , .pot / .po / .mo translation files.
├── tests/
│   ├── bootstrap.php            , in-memory WP function stubs (get_option,
│   │                              update_option, add_action, do_action,
│   │                              sanitize_*, esc_*, current_time, admin_url,
│   │                              wp_nonce_url).
│   ├── PlanTest.php             , 14 tests , plan definitions + capability gates.
│   ├── CreditsTest.php          , 11 tests , ledger arithmetic + cycle reset.
│   ├── LicenseTest.php          , 6 tests  , plan resolution without SDK.
│   ├── TopUpsTest.php           , 10 tests , catalog + webhooks + simulator.
│   └── E2E.md                   , 8-section manual smoke-test checklist.
└── vendor/                      , dev-only PHPUnit via composer install.
                                   (.gitignore'd so it doesn't ship.)
```

---

## 5. Interdependencies (module , module)

Module-to-module dependencies. When you change a downstream module,
re-test the upstream ones. The arrow reads "depends on":

```
settings.php , every other include (it's the admin shell + flash + tab renderer)

generator.php , prompts.php
              , llm.php
              , research.php
              , layout.php
              , voice.php
              , linking.php
              , i18n.php
              , credits.php          [credit gate]
              , plan.php             [feature gate via has()]

refresh.php   , generator.php (uses public helpers)
              , llm.php
              , research.php
              , linking.php
              , voice.php
              , i18n.php
              , credits.php
              , plan.php

scheduler.php , generator.php
              , refresh.php
              , planner.php

planner.php   , generator.php
              , refresh.php
              , actions.php
              , scheduler.php (for eta_for + distribute_queue)
              , calendar.php (for cross-link)
              , plan.php

calendar.php  , scheduler.php (eta_for)
              , plan.php (has('calendar'))

llm.php       , crypto.php (decrypts API keys before call)
              , pricing.php (logs cost-per-call)
              , usage.php (records tokens + cost)
              , research.php (attaches web_search_20250305 to Claude calls)

research.php  , llm.php (fetch_via_research_role uses chat('research'))
              , crypto.php (decrypts SERP API keys)

seo.php       , images.php (og_image_for_post)
              , i18n.php (hreflang URLs)

images.php    , (no internal deps)

credits.php   , plan.php
              , license.php
              , (for usage banner) , license.php

plan.php      , license.php (current() reads from license)
              , (for locked card) , license.php (checkout_url)

license.php   , topups.php (top_up_url routes via TopUps)
              , freemius SDK (loaded conditionally)

topups.php    , credits.php (add_top_up on purchase)
              , license.php (freemius instance lookup)

privacy.php   , caps.php
              , license.php (plan label in export)
              , credits.php (ledger in export)

caps.php      , (no internal deps)

usage.php     , pricing.php (cost_for)

i18n.php      , (no internal deps)

voice.php     , (no internal deps)

linking.php   , i18n.php (lang filter)

layout.php    , (no internal deps , reads core wp_get_global_settings)

dashboard.php , usage.php (token + cost)
              , credits.php (credit numbers)
              , license.php (plan label)
              , (log read from CITELEAP_OPTION_LOG)
```

---

## 6. Intradependencies (within a module)

Each module's internal coupling. Use this to refactor safely.

### 6.1 `credits.php` (CiteLeap_Credits)

- `state()` is the source of truth, every other public method calls it.
- `consume()` updates `used` until `included` is exhausted, then dips
  into `top_up`. Always increments `lifetime_used`. Cycle reset is
  triggered by `state()` itself when `cycle_start != current month`
  AND the plan is not lifetime-credits.
- `is_low()` and `is_exhausted()` are pure functions of `percent_used()`
  and `total_available()`; never set state.
- `admin_banner()` is the rendering branch, no state mutation.

### 6.2 `plan.php` (CiteLeap_Plan)

- `definitions()` is the static catalog; every other method reads from
  it.
- `current()` proxies to `CiteLeap_License::plan_slug()`. Never
  inverted (plan does not write to license).
- `has( $cap, $slug = '' )` resolves to the active plan when `$slug`
  is empty.
- `render_locked_notice()` and `render_inline_nudge()` are pure UI
  helpers.

### 6.3 `llm.php` (CiteLeap_LLM)

- `chat( $role, $system, $user, $max_tokens )` is the single public
  entrypoint.
- Per-role model lookup goes through `models_for_provider()` -> per-
  role default.
- Retry loop: `is_transient_error()` decides between break (permanent)
  vs `sleep( backoff[ $attempts ] )` then retry.
- Token + cost recording happens in `record_usage_post_call()` after
  every successful call, BEFORE returning to the caller. If the caller
  doesn't consume credits, usage was still recorded.

### 6.4 `research.php` (CiteLeap_Research)

- `should_attach_web_search()` is the toggle: returns true only when
  the writer call is Claude AND the model name contains
  `sonnet`/`opus`.
- `fetch_sources()` is the public entrypoint; dispatches to:
  - `claude_native` , `fetch_via_research_role()` IF writer is not
    Claude (else `llm.php` attaches the tool inline to the writer
    call).
  - `serper` / `brave` / `tavily` , out-of-band SERP fetch.
- `extract_outbound()` runs AFTER the draft returns to audit which
  sources the model actually cited (stored in `_citeleap_sources`).

### 6.5 `generator.php` (CiteLeap_Generator)

- `write_post_from_idea( $idea_id )` is the central method. Order:
  1. Find idea in queue.
  2. **Credit gate** `CiteLeap_Credits::can_consume()`.
  3. Build prompt context (vars, language, layout, voice, research,
     internal links).
  4. Render template with `render_template()`.
  5. Call `CiteLeap_LLM::chat( 'writing', ... )`.
  6. Parse JSON; on failure, log + return.
  7. Strip em-dashes from every field.
  8. `wp_insert_post()`.
  9. Write meta description, category, sources, internal-links meta.
  10. Update queue row to `drafted`.
  11. `CiteLeap_Credits::consume( 1, 'draft' )`.
  12. Log `post_drafted`.

### 6.6 `refresh.php` (CiteLeap_Refresh)

- `refresh_from_queue( $queue_id )` order:
  1. Find queue row.
  2. **Plan gate** `CiteLeap_Plan::has( 'refresh' )`.
  3. **Credit gate** `CiteLeap_Credits::can_consume()`.
  4. Build prompt context (same as generator).
  5. LLM call.
  6. Parse + em-dash strip.
  7. Branch: `draft` mode , write pending_review meta; `live` mode ,
     wp_update_post.
  8. Mark queue row + meta.
  9. `CiteLeap_Credits::consume( 1, 'refresh_pending' or
     'refresh_live' )`.
  10. Log.

### 6.7 `images.php` (CiteLeap_Images)

- `pick_random()` is the central RNG; called from `maybe_assign_on_save`
  and `og_image_for_post()` fallback.
- `assign_random_bulk( $reroll )` walks every published post; uses
  `$GLOBALS['citeleap_img_bulk']` as a re-entry guard so the
  `drop_random_flag_if_manual` hook doesn't fire during bulk.
- Render injection (`inject_hero`, `inject_card`, `inject_card_block`)
  all check `already_rendered()` to avoid duplication when the theme
  already rendered the Featured image.

### 6.8 `scheduler.php` (CiteLeap_Scheduler)

- `tick()` is the cron entry. Order:
  1. Acquire transient lock (5-min expiry); refuse if already held.
  2. Try/catch around the body so unhandled exceptions still release
     the lock.
  3. `tick_new_content()` , top up queue if empty, draft the next
     highest-priority + non-paused row, schedule at its eta_for slot.
  4. `tick_refresh()` , if refresh auto_mode is on, refresh the next
     due post.
  5. Release lock.
- `eta_for( $row, $schedule )` resolves: pinned datetime > auto
  cadence slot > "auto mode off" sentinel.

---

## 7. Build order (sprint-by-sprint rebuild)

If you start over, build in this order. Do not skip; later modules
depend on earlier ones.

### Sprint 1 , foundations (week 1)

1. `citeleap.php` plugin header, constants, requires (empty), activation
   hook (PHP version check, cron schedule, register uninstall),
   deactivation hook, uninstall function, i18n loader.
2. `crypto.php` , AES-256-CBC encrypt/decrypt.
3. `caps.php` , two-tier capability model, grant on activation, revoke
   on uninstall.
4. `pricing.php` , per-million-tokens table + LAST_VERIFIED_AT.
5. `usage.php` , 12-month token + cost ledger.
6. `llm.php` , three-provider router with retry-with-backoff. NO
   research-tool attachment yet. NO research role yet.
7. `prompts.php` , master prompt template with placeholders, GEO/AEO
   May 2026 Bible defaults.
8. Smoke test: install the plugin, paste API keys, "Test connection"
   per provider works.

### Sprint 2 , single-provider drafting (week 2)

9. `generator.php` , `write_post_from_idea()` with NO research, NO
   linking, NO voice, NO images. Just topic , LLM , wp_insert_post.
10. `planner.php` , single-tab planner UI with queue + manual draft +
    paste-topics + generate-ideas.
11. `dashboard.php` , KPI tiles.
12. `actions.php` , state machine + per-row admin-post handlers.
13. `scheduler.php` , hourly tick that drafts + schedules.
14. `settings.php` , admin shell, tab nav, save handlers for API keys
    + models + cadence + caps.
15. `assets/admin.css`, `assets/admin.js` , progression overlay.
16. Smoke test: end-to-end draft a real post from a topic with one
    provider, scheduler picks up the next idea hourly.

### Sprint 3 , research + linking + voice (week 3)

17. `research.php` , Claude native web_search + 3 SERP fallbacks +
    fetch_via_research_role for non-Claude writers + extract_outbound.
18. `linking.php` , internal-link candidate builder (lang-aware).
19. `voice.php` , 3 most recent posts as voice samples.
20. `layout.php` , reads wp_get_global_settings + patterns.
21. Wire all four into `generator.php`'s prompt context.
22. Smoke test: a drafted post now contains 3+ outbound citations,
    2+ internal links, matches the recent voice, and the layout block
    reflects the theme's content-size.

### Sprint 4 , multilingual + images + SEO (week 4)

23. `i18n.php` , 7 languages, Polylang + WPML detection, hreflang.
24. `images.php` , pool + auto-assign + hero/card injection + bulk
    operators + posts-list column.
25. `seo.php` , JSON-LD + OG + canonical + IndexNow, with stand-down
    when an existing SEO plugin is detected.
26. `assets/blog-post.css` , theme override + hero + card + shimmer.
27. `calendar.php` , month-grid view.
28. Smoke test: a published post has FAQPage schema, Open Graph image,
    correct hreflang, and IndexNow ping fires on save.

### Sprint 5 , refresh (week 5)

29. `refresh.php` , three-state refresh module, pending_review state,
    bulk-add-by-paste-list, reset_stuck.
30. Wire refresh into `scheduler.php`'s tick.
31. Wire refresh actions into `actions.php`.
32. Smoke test: refresh an old post in draft mode (lands in
    pending_review), approve it, the live post updates with new stats.

### Sprint 6 , commercial layer (week 6) , the sprint this guide is for

33. `license.php` , Freemius wrapper with mock fallback.
34. `plan.php` , 5 plans + dev tier + capability map + locked-card
    renderer + inline-nudge.
35. `credits.php` , per-cycle ledger + consume + add_top_up +
    reset_cycle + admin banner.
36. Wire credit check into `generator.php::write_post_from_idea()` and
    `refresh.php::refresh_from_queue()` (BOTH the plan gate AND credit
    gate). Consume 1 on success.
37. `topups.php` , 4 packs (Starter/Growth/Scale/Bulk), Freemius
    webhook listeners + in-admin simulator.
38. Add License & Credits tab to `settings.php`.
39. Add server-side defense in save_settings (auto_mode + refresh_mode
    clamped to off when plan does not include them).
40. Render upgrade cards in `calendar.php` + `settings.php` languages
    tab when plan does not include those features.
41. PHPUnit tests for plan + credits + license + topups.

### Sprint 7 , polish & launch prep (week 7)

42. `privacy.php` , `wp_add_privacy_policy_content`, exporter, eraser,
    first-run disclosure.
43. Accessibility pass: aria-current, role="alert/status", aria-live,
    aria-hidden on decorative icons, .screen-reader-text class,
    focus-visible CSS, forced-colors fallback, prefers-reduced-motion.
44. Editor-role capability fully wired (every handler uses
    `CiteLeap_Caps::guard_use()` or `::guard_manage()`).
45. `tests/E2E.md` , manual smoke checklist.
46. `readme.txt` , title, tags, full changelog.
47. Bump version, build zip, ship.

---

## 8. Quality gates (every release)

A release is not shippable until ALL of these pass.

### 8.1 PHP

- `php -l` on every PHP file (zero errors / zero warnings).
- `composer test` , PHPUnit suite green (target: 41+ tests passing).

### 8.2 GEO/AEO compliance on output

Validate every freshly drafted post against:

- H1 contains the primary entity / keyword.
- 1 question-shaped H2 followed by a 20-60 word answer capsule.
- 3+ statistics with named source link in the first 30% of body.
- 1+ list or table.
- JSON-LD Article + FAQPage detected by Google Rich Results test.
- No em-dashes.
- No AI tells (delve, leverage, navigate, tapestry, harness, etc.,
  per the master prompt's banned-phrases list).
- Word count 1,000-1,600.

### 8.3 Accessibility

- All form inputs have `<label for="">` bindings.
- All data tables have `<th scope="col">` / `<th scope="row">`.
- Active nav-tab has `aria-current="page"`.
- All flash messages have `role="alert"` or `role="status"` + `aria-live`.
- All icon-only buttons have `aria-label`.
- Decorative icons have `aria-hidden="true"`.
- Visible focus ring on every interactive control.
- Run axe-core or Pa11y on every admin tab.

### 8.4 Security

- Every admin-post handler: capability check (`guard_use` /
  `guard_manage`) + `check_admin_referer()` + sanitised inputs.
- Every output: `esc_html`, `esc_attr`, `esc_url`, `wp_kses_post` per
  context.
- API keys: never echoed back into the page HTML; placeholders show
  only last 4 chars.
- Log entries: redact `sk-*`, `sk-ant-*`, `AIza*` patterns.

### 8.5 GDPR

- `wp_add_privacy_policy_content()` returns CiteLeap-specific text.
- Personal-data exporter returns data for admin email.
- Personal-data eraser wipes the ledger + API keys + log on the
  matching admin's request.
- Uninstall removes every `citeleap_*` option AND the capability from
  every role.

### 8.6 Plan + credit gates

- Free plan with 3 lifetime credits: 4th draft attempt blocks with
  upgrade message.
- Plan downgrade webhook re-clamps `auto_mode` to `off` for the
  removed feature.
- Top-up purchase webhook adds credits to `top_up`, NOT to monthly
  `included`.

---

## 9. Commercial layer , exact pricing

This is the locked-in pricing. Do not invent new numbers.

| Tier | Monthly | Annual | Credits/mo | Sites | Overage | Features |
|---|---|---|---|---|---|---|
| Free | $0 | , | 3 lifetime | 1 | , | bulk image pool only |
| Solo | $29 | $290 | 10 | 1 | $5 | + auto_publish, refresh, scheduling, top_ups |
| Pro | $99 | $990 | 30 | 3 | $4 | + multilingual, calendar |
| Agency | $299 | $2,990 | 100 | 20 | $3 | + white_label |
| Enterprise | $999 | $9,990 | 500 | unlimited | $2 | + priority_support |

Top-ups (one-off, never expire while license active, consumed after
monthly):

| Pack | Credits | Price | Per-credit |
|---|---|---|---|
| Starter | 10 | $49 | $4.90 |
| Growth | 50 | $199 | $3.98 |
| Scale | 100 | $349 | $3.49 |
| Bulk | 500 | $1,499 | $3.00 |

Annual = 2 months free (17% off) + 20% bonus credits in month 1.

Reverse trial: 14 days full Pro, card required at credit 5 used. Day
14 drops to Free (3 lifetime).

---

## 10. Common pitfalls & how to avoid them

These were learned the expensive way. Don't relearn them.

1. **Don't double-render the Featured image.** Themes already render
   `core/post-featured-image`. CiteLeap must hook `render_block_core/
   post-featured-image` + `post_thumbnail_html` to set the "already
   rendered" flag, then `inject_hero` / `inject_card` short-circuit.
2. **Don't crash the scheduler when an idea returns invalid JSON.**
   The cron lock must release even on uncaught exception. Wrap the
   tick body in try/finally.
3. **Don't trust the LLM to skip em-dashes.** Strip them in code.
4. **Don't assume the operator picked a category.** Fall back: explicit
   meta, then first match by name, then a default "Uncategorised".
5. **Don't store API keys in plaintext.** AES-256-CBC. Show placeholder
   "...last4" in the UI.
6. **Don't write JSON-LD if Yoast/Rank Math/AIOSEO is already writing
   it.** Detect them by their option keys + active-plugin signatures
   and stand down PER TAG (e.g. you can still emit hreflang if the
   detected plugin doesn't).
7. **Don't reset the cycle on a plan upgrade if the operator already
   used credits this month.** Reset on plan_change webhook (so a new
   higher tier starts with full included credits) BUT keep the top-up
   reserve and lifetime counter untouched.
8. **Don't show the developer-mode pill in production.** Gate it on
   `CITELEAP_DEV_MODE` constant in wp-config.php, not on a database
   option.
9. **Don't 403 an Editor who deep-links a manage-only tab.** Fall back
   to Dashboard so the navigation feels deliberate.
10. **Don't ship a release without bumping all three version markers**
    (citeleap.php header `Version:`, `CITELEAP_VERSION` constant,
    `readme.txt` Stable tag). Mismatch breaks WP's update notifications.

---

## 11. Day-1 acceptance test (the smoke test)

After every full rebuild, run this end-to-end on a clean WordPress
6.6+ install:

1. Activate the plugin. Disclosure notice appears.
2. Open Settings , paste a Claude API key , pick Sonnet 4.6 for writing
   + Opus 4.7 for reasoning + research , Save.
3. Click "Test connection" , green check.
4. Open Planner , "Generate ideas now" , 10 items appear.
5. Click "Write draft" on the top idea.
6. Within ~60 seconds, a WP draft post is created with:
   - 1,200-1,600 words
   - 3+ outbound citation links to real sources
   - 2+ internal links to existing posts (if any exist)
   - FAQPage schema validates on schema.org/validator
   - No em-dashes
   - Featured image from the pool (if pool is populated)
7. Activity Log shows `post_drafted` + `credit_consumed`.
8. License & Credits tab shows 1/3 credits used.
9. After 3 drafts, the 4th blocks with the upgrade banner.

If any of these fail, the rebuild is not done.

---

## 12. Pointers to the live codebase

This document describes the shape. The actual code lives in this
repo on branch `claude/citeleap-2`. Read it in this order:

1. `citeleap.php` , constants, requires, lifecycle.
2. `includes/caps.php` , the two cap helpers.
3. `includes/license.php` , the Freemius wrapper.
4. `includes/plan.php` , the capability map.
5. `includes/credits.php` , the ledger.
6. `includes/llm.php` , the provider router.
7. `includes/research.php` , the web-search layer.
8. `includes/generator.php` , the draft pipeline.
9. `includes/refresh.php` , the refresh pipeline.
10. `includes/settings.php` , the admin shell.
11. `tests/PlanTest.php` through `tests/TopUpsTest.php` , the
    commercial-layer guarantees expressed as assertions.

Once you've internalised those, you can rebuild the rest from the
sprint plan in section 7 above.

---

**End of guide.** If you found a contradiction between this document
and the live code, the live code wins until the doc is updated. PRs
to keep the two in sync are encouraged.
