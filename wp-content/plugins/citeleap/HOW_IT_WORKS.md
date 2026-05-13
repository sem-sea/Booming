# CiteLeap — what it does and how it works

Plain language. No jargon where it can be avoided. If a feature is described here, it is the truth of what the code does.

---

## One-paragraph summary

CiteLeap brainstorms, writes, schedules, and refreshes blog posts on your WordPress site by calling Claude / OpenAI / Gemini with your own API keys. Every post follows a GEO/AEO-compliant structure designed to earn citations in ChatGPT, Perplexity, Google AI Overviews, Claude, and Microsoft Copilot. The whole loop runs from one screen with a dashboard, a single planner, a prompt editor, and a settings page. There is a hard monthly spend cap per provider so you never blow the budget.

---

## The five tabs (what each one is for)

### 1. Dashboard (default landing)
A single screen that tells you what is happening **right now**:
- **Pre-cap warnings** at the very top. If any provider is at 80–99% of its monthly cap you see an amber warning. At 100% you see a red error and generation is refused until next month.
- **KPI strip**: total spend this month, posts published this month, items pending in the queue, refresh queue size.
- **Per-provider budget table** with a progress bar that goes green → amber → red as you approach the cap.
- **Status pills**: how many items are queued, drafted, scheduled, queued for refresh, in-flight, refreshed, failed, or published this month.
- **Two side-by-side log feeds**: latest 10 successes and latest 10 errors, split by severity (info vs warn/error/critical). API keys are auto-redacted from log entries.

### 2. Planner
The **only** planner. Lists every queue item (new ideas AND refresh items) in a single sortable table:
- **REFRESH** tagged rows are visually distinct (yellow tag).
- Sort order puts in-flight items at the top so you always see what is actively being worked on.
- Each row shows the status (color-coded), priority, slug, scheduled-for or finished-at date, and action buttons.
- Top of tab: three action blocks:
  1. **Manual actions** — Generate ideas now (LLM call) and Run scheduler tick now.
  2. **Add your own topics** — paste one topic per line, each becomes a queued post in order. No LLM call until you draft. Duplicates against existing slugs and queue auto-skipped.
  3. **Refresh existing posts** (collapsible) — 100-post checkbox list ordered by oldest-modified-first.

### 3. Prompts
Three editable text areas:
- **Custom additional instructions** — applied to BOTH the idea prompt and the writing prompt. Use for site-specific tone or banned words.
- **Master writing prompt** — the full system prompt that produces the article. Default is research-backed (GEO/AEO May 2026 Bible). Clear the field and save to revert to the default.
- **Idea-generation prompt** — what the reasoning model gets when brainstorming.

### 4. Settings
- **API keys** — encrypted at rest with AES-256-CBC keyed off your `AUTH_KEY` salt. Inputs are masked (you see `••••••••sk-..xxxx`, never the raw value). Leaving a field blank keeps the existing key.
- **Test connection** button next to each populated key — sends one tiny "say OK" request to the provider and reports back. Verifies network, key validity, and quota.
- **Model selection** — per role (reasoning vs writing), per provider, per model. Mix and match freely.
- **Auto-publish schedule** — toggle, start date, posts per week, audience, seed topics, internal-link allowlist.
- **Monthly budget caps** — USD per provider plus an overall cap. 0 = unlimited.
- **Refresh existing content** — auto-refresh toggle, cadence in days, refresh posts per week.

### 5. Log
Full activity log (max 200 entries) with severity column (info / warn / error / critical), event name, and redacted detail. Plain text, sortable visually by reverse chronological order.

---

## What happens when (the four core flows)

### Flow A — "Generate ideas now" (manual)
1. You click the button on the Planner tab.
2. The **reasoning model** (your selected provider+model, default Claude Opus 4.7) is called with the **idea-generation prompt**.
3. Prompt is filled in with: your site name, audience, categories, seed topics, internal-link allowlist, existing slugs (to dedupe), custom additional instructions.
4. **Budget check fires first**. If you are over cap, the call is refused and logged.
5. Successful call → ideas are parsed (strict JSON array), each one given a UUID, merged into the queue option, deduped by slug.
6. **Token usage is recorded** — provider, model, input tokens, output tokens, computed cost.
7. Each idea shows up in the Planner with status `queued`.

### Flow B — "Write draft" (manual)
1. You click on a queued idea.
2. The **writing model** (default Claude Sonnet 4.6) is called with the **master writing prompt**.
3. Same budget check + token recording as Flow A.
4. The response (strict JSON object) is parsed into title / slug / meta_description / excerpt / category_name / primary_keyword / body.
5. `wp_insert_post` creates a WordPress draft with all standard meta. Yoast / Rank Math / AIOSEO meta keys are populated. Category is auto-created if missing.
6. The post gets meta `_citeleap_source=auto`, `_citeleap_provider=claude/sonnet-4-6`, `_citeleap_word_count=1342`, plus a link back to the queue idea ID.
7. Queue entry status changes from `queued` to `drafted`. The draft is editable like any normal WP post.

### Flow C — Auto-mode cron tick (hourly)
1. WP-Cron fires `citeleap_cron_hourly` every hour.
2. **Transient lock check** — if a previous tick is still running, this one bails with a warning log. Prevents race conditions.
3. **Refresh tick first** (always, independent of new-content auto-mode):
   - If auto-refresh is OFF, skip.
   - If queue has no `queued_refresh` items, top up from `due_post_ids` (oldest-modified posts past your cadence days).
   - Pick first `queued_refresh` item, run refresh, exit. **Throttled to one refresh per hourly tick** to keep budget predictable.
4. **New-content tick second** — behaviour depends on Settings → Auto mode:
   - **Off**: skip entirely.
   - **Draft only**: every tick, if the queue has a `queued` item, draft one (no slot gate). Refills the queue with the reasoning model when empty. Drafts stay as WP draft for your manual review. Throttled to one draft per hourly tick to keep budget predictable.
   - **Publish**: same as Draft, plus once the draft is created the post is moved to `post_status='future'` at the next slot time (start date + posts-per-week). WordPress core publishes at the slot time, no further plugin action needed.
5. **Exceptions are caught**. Any throw inside the tick is logged as `critical` and the transient lock is always released.

### Flow D — Refresh
1. You expand the Planner's "Refresh existing posts" section.
2. You see up to 100 published posts sorted by oldest-modified-first. Each row shows last-refresh-at and refresh-count if any.
3. Tick the ones you want, click "Queue selected for refresh".
4. The selected posts land in the same planner table with status `queued_refresh` and a yellow REFRESH tag.
5. **Manual mode**: click "Refresh now" on the row. Status goes `queued_refresh → refreshing → refreshed`.
6. **Auto mode**: the hourly tick picks them up (Flow C step 3) one per hour.
7. The writing model is called with refresh-specific instructions (preserve slug, retire 2025 references, strengthen weak sections, keep 5-Q FAQ).
8. `wp_update_post` overwrites **only** the title, content, and excerpt of the existing post. Slug, post ID, post date, post author, comments, post meta, categories are preserved.
9. Post meta `_citeleap_refresh_count` is incremented, `_citeleap_last_refreshed` updated, `_citeleap_word_count` refreshed.

---

## Status taxonomy (every queue item is in exactly one state)

| State | Meaning | Goes to |
|---|---|---|
| `queued` | Idea generated, not yet drafted | `drafted` via Flow B |
| `drafted` | WP draft post inserted | `scheduled` (Flow C) or stays drafted forever in manual mode |
| `scheduled` | `post_status=future`, will auto-publish | `published` (by WP core, not the plugin) |
| `published` | Already live (post meta lookup) | terminal |
| `queued_refresh` | Existing post awaiting refresh | `refreshing` |
| `refreshing` | Writing model call in flight | `refreshed` or `failed` |
| `refreshed` | Update completed | terminal |
| `failed` | Last operation hit an error; error reason visible on row | manual remove or rerun |

---

## What CiteLeap will NEVER do

- **Never** call an API without first checking the budget cap.
- **Never** store an API key in plaintext. AES-256-CBC at rest, derived from `AUTH_KEY`.
- **Never** echo an API key into the page HTML (input fields render with empty `value=""`; placeholders show only the masked existing key).
- **Never** publish without going through the standard WP draft → future → publish pipeline. Your editorial team still has a checkpoint.
- **Never** overwrite a published post unless it is explicitly queued for refresh.
- **Never** run two ticks in parallel (transient lock).
- **Never** send any data to anyone other than the configured LLM provider endpoints. No telemetry, no phone-home.

---

## Dependencies you should know about

### External (HTTP requests this plugin makes)

| Endpoint | When | Sensitive data sent | Pattern |
|---|---|---|---|
| `https://api.anthropic.com/v1/messages` | Idea generation, writing, refresh, test-connection — when provider=claude | Your prompt + content. Your Claude key. | wp_remote_post, 120s timeout |
| `https://api.openai.com/v1/chat/completions` | Same — when provider=openai | Your prompt + content. Your OpenAI key. | wp_remote_post, 120s timeout |
| `https://generativelanguage.googleapis.com/v1beta/...` | Same — when provider=gemini | Your prompt + content. Your Gemini key. | wp_remote_post, 120s timeout |

**There are no other outbound calls.** No analytics, no update check against a remote server, no licence verification.

### Internal (the moving pieces)

```
                  +----------+        +---------+
                  | Settings |        | Prompts |
                  +----+-----+        +----+----+
                       |                   |
                       v                   v
                +------+-------------------+------+
                |      wp_options (encrypted)     |
                |  citeleap_api_keys              |
                |  citeleap_models                |
                |  citeleap_prompts               |
                |  citeleap_schedule              |
                |  citeleap_refresh               |
                |  citeleap_budget_caps           |
                |  citeleap_token_usage (ledger)  |
                |  citeleap_queue (state machine) |
                |  citeleap_log (200-entry buffer)|
                +-----------+---------------------+
                            |
       +--------------------+------+----------------+
       v                           v                v
  +--------+                  +--------+       +----------+
  |Planner |  user clicks --> |Generator|----> |   LLM    |
  +--------+                  +---+----+       |  router  |
       ^                          |            +----+-----+
       |                          |                 |
       |                          v                 v
       |                    +-----------+      +----------+
       +------ <-- ---------+ wp_posts  |<-----+ Provider |
              draft / future|           |      | API call |
              status        +-----------+      +----------+
                                  ^
                                  |  hourly
                            +-----+-----+
                            | Scheduler |
                            +-----------+
                            tick locked by transient
```

### Function-level intra-dependencies

- `CiteLeap_LLM::chat()` is the only path to an external provider. Calls flow:
  - `chat()` → `CiteLeap_Usage::can_spend()` (budget gate) → provider-specific `call_*()` → `extract_token_counts()` → `CiteLeap_Usage::record()`
- `CiteLeap_Generator::generate_ideas()` and `write_post_from_idea()` are the only callers of `chat('reasoning', …)` and `chat('writing', …)`.
- `CiteLeap_Refresh::refresh_from_queue()` is the only caller of `chat('writing', …)` for refresh runs.
- `CiteLeap_Scheduler::tick()` is the only auto path; it calls `tick_refresh()` and `tick_new_content()`, both of which fan into the Generator/Refresh classes.
- `CiteLeap_Log::add()` is the only writer of `citeleap_log`. It auto-redacts API-key-shaped strings.
- `CiteLeap_Crypto::encrypt()` / `decrypt()` is the only path API keys take through the option store.

### Where to look when something goes wrong

| Symptom | Where to look | Likely cause |
|---|---|---|
| "No API key configured" in Log | Settings → API keys | Key never saved, or saved blank, or wp-config `AUTH_KEY` rotated (decrypt fails) |
| `budget_cap_hit` error log | Dashboard → Per-provider budget table | At cap. Raise the cap or wait for the 1st. |
| `tick_locked` warning, no posts publishing | Logs | A previous tick stuck (>5 min). Self-clears on the next tick. Investigate if persistent. |
| `idea_parse_failed` | Logs detail (first 200 chars of response) | Model returned non-JSON. Often a momentary rate-limit or content-policy issue. Try again. |
| `write_too_short` warning | Logs | Generated post under 1000 words. Inspect the draft, may want to adjust master prompt to enforce length more strictly. |
| Post drafted but never published | Planner status = `drafted` | Auto-publish is OFF, or start date is in the future, or scheduler tick has not yet hit the slot. |
| HTTP 401 errors in test-connection | Settings → test the key | Key invalid or revoked at provider. |
| HTTP 429 errors | Logs | Provider rate-limited you. Slow down posts_per_week, or upgrade your provider tier. |
| HTTP 5xx | Logs | Provider outage. Will retry on next cron tick. |
| `tick_exception` critical | Logs detail | Unhandled exception in tick. Capture the stack frame from the log line; report it. |

---

## Where data lives in the database

All stored in standard `wp_options`. None are autoloaded (no perf hit on page loads).

| Option | Type | Purpose | Sensitive? |
|---|---|---|---|
| `citeleap_api_keys` | array | Three encrypted strings, one per provider | Yes (encrypted) |
| `citeleap_models` | array | Provider+model per role | No |
| `citeleap_prompts` | array | Three custom prompts | Low (user content) |
| `citeleap_schedule` | array | Auto-publish settings | No |
| `citeleap_refresh` | array | Refresh settings | No |
| `citeleap_budget_caps` | array | USD caps per provider | No |
| `citeleap_token_usage` | array | 12-month token/cost ledger | No |
| `citeleap_queue` | array | All queue items, every status | No |
| `citeleap_log` | array | 200-entry activity log with redaction | No (auto-redacted) |
| `citeleap_pricing_overrides` | array | Optional model-price overrides | No |

On uninstall, all 10 options are deleted (multisite-aware via `switch_to_blog`). Imported / generated posts are left in place (the operator did not ask for them to be deleted).

---

## Cost math

Default stack (Claude Sonnet 4.6 writer + Claude Opus 4.7 ideation, May 2026 rates):
- ~3,500 input tokens + ~3,000 output tokens per draft = $0.15
- ~1,500 input + ~1,500 output for an idea batch (10 ideas) = $0.04 — split across 10 = $0.004 per idea
- **Roughly $0.16 per published post end to end**

At posts-per-week = 3 over 4 weeks = 12 posts = **~$2 / month**.

Bump to GPT-5.5-pro for ideation + GPT-5.5 for writing → roughly $0.30 per post → $3.60 / month for the same volume.

Gemini 2.5 Pro + 3.1 Pro → cheapest at ~$0.08 per post → $1 / month.

---

## Production-readiness audit (v1.2.0)

| Area | Status | Notes |
|---|---|---|
| ABSPATH guard, nonces, capability checks | ✅ | Every file, every form, every entry point. |
| Escaping on output | ✅ | esc_html / esc_attr / esc_url / wp_kses_post on post_content. |
| Sanitization on input | ✅ | sanitize_text_field, sanitize_title, sanitize_key + wp_unslash on every $_POST. |
| API keys encrypted at rest | ✅ | AES-256-CBC, key derived from AUTH_KEY. Decryption key never in DB. |
| API keys masked in UI | ✅ | Input value attribute always empty; placeholder shows last 4 chars only. |
| API keys redacted in logs | ✅ | Regex strips sk-… / sk-ant-… / AIza… patterns. |
| Budget cap with hard refuse | ✅ | can_spend() gate before every chat() call. |
| Pre-cap warning on Dashboard | ✅ | 80% amber, 100% red. |
| Token usage ledger | ✅ | 12-month rolling, per-provider, in/out/cost/calls. |
| WP-Cron race protection | ✅ | Transient lock with 5-min expiry. |
| Catch + log unhandled exceptions in tick | ✅ | try/finally around the tick body. |
| Test-connection per provider | ✅ | Settings tab, one button per populated key. |
| Severity-tagged logs | ✅ | info / warn / error / critical. Dashboard splits by severity. |
| Multisite-aware uninstall | ✅ | Loops sites via switch_to_blog. |
| i18n | ✅ | load_plugin_textdomain on init, /languages folder shipped. |
| No autoload on hot options | ✅ | update_option(…, …, false) everywhere. |
| Idempotent operations | ✅ | enqueue_posts dedupes; idea slugs dedupe; uninstall is repeatable. |
| Schema migration | ⚠️ | No DB tables; option-array schema is forward-compatible. |
| Privacy / GDPR notice | ⚠️ | See "GDPR notes" below. |
| Email / Slack alert hook | ❌ | Roadmap. WP filter `citeleap_alert` will fire from v1.3 so you can wire to MainWP / Pushover / Slack. |
| Dry-run preview mode | ❌ | Roadmap. |
| Automatic retry on 5xx | ❌ | Today: one attempt, log the failure, retry on next cron tick. |
| Server-cron vs WP-Cron recommendation | ⚠️ | WP-Cron is page-load triggered. Site with low traffic should use real cron. Banner in v1.3. |

---

## GDPR notes (read this if you operate in the EU)

The plugin sends your **prompts** (master + idea + custom) and the **resulting drafts** to whichever LLM provider you configured. **No visitor PII is sent** — only operator-supplied content and site context.

For B2B SaaS / consultancy sites where the operator is also the data controller, your obligations are:

1. **Lawful basis under Article 6**: legitimate interest for operating editorial content production. Document it.
2. **Data Processing Agreement** with the LLM provider:
   - Anthropic: https://www.anthropic.com/legal/commercial-terms (Enterprise / API)
   - OpenAI: https://openai.com/policies/data-processing-addendum
   - Google Cloud Vertex AI: https://cloud.google.com/terms/data-processing-terms
3. **Provider data retention**: Anthropic does NOT train on Console / API data by default. OpenAI API does NOT train on data submitted through the API by default. Google Vertex AI same. Verify the current policy for your account.
4. **What we recommend**: do not paste customer PII into the seed topics, audience, or custom prompt fields. The plugin will send those verbatim to the provider.

CiteLeap itself stores nothing on third-party servers. All state is in your WP database.

---

## Roadmap (next versions, in priority order)

- v1.3: WP filter `citeleap_alert($severity, $event, $detail)` for email / Slack / Pushover wiring.
- v1.3: WP-Cron health banner + "use real cron" recommendation on Dashboard.
- v1.3: Dry-run preview mode — render the master prompt with all substitutions before paying for a generation.
- v1.4: Automatic retry-with-backoff on HTTP 5xx and 429.
- v1.4: Per-post audit trail — store the exact prompt used for each post in `_citeleap_audit_log` post meta.
- v1.5: Featured-image generation (DALL-E / Imagen / Gemini Image).
- v1.5: Multi-language pipeline (one idea → N language variants).
- v2.0: Editorial workflow with reviewer roles and Slack approval.

---

## Versioning policy

CiteLeap follows semver:
- **MAJOR** bumps when the option schema changes incompatibly (you would need to re-save settings).
- **MINOR** bumps for new tabs, new providers, new auto-publish features.
- **PATCH** bumps for bug fixes only.

Every release ships under a versioned zip filename (`citeleap-{version}.zip`). The rolling `citeleap.zip` is a convenience copy of the latest versioned zip.
