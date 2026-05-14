=== CiteLeap ===
Contributors: boomingventure
Tags: ai, content, claude, openai, gemini, scheduled posts, geo, aeo
Requires at least: 6.6
Tested up to: 6.8
Requires PHP: 8.0
Stable tag: 2.1.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

AI-powered blog content engine. Reasoning model ideates, writing model drafts, WP-Cron publishes on schedule. Bring your own keys (Claude, OpenAI, Gemini). Output is GEO/AEO compliant (May 2026 Bible).

== Description ==

CiteLeap closes the loop from idea to published post:

1. **Ideate.** Reasoning model brainstorms unique, lead-generation topics that earn citations on ChatGPT / Perplexity / Google AI Overviews / Claude / Copilot. The model picks from real audience pain points, avoids duplicates of your existing slugs, and ranks each idea by priority.
2. **Plan.** All ideas land in the Planner tab. Approve / remove / re-prioritise manually, or let auto-publish drive everything.
3. **Write.** Writing model drafts each idea using the editable master prompt (default ships GEO/AEO May 2026 Bible compliance: question H2s, 40-60 word answer capsules, 3+ statistics with named sources in the first 30% of body, lists, FAQ, 1,200-1,600 words, voice rules).
4. **Schedule + publish.** WP-Cron picks up the highest-priority idea every hour, drafts it, and schedules the publish at the next slot based on your start date + posts-per-week setting. Manual mode disables auto-publish but keeps everything else working.

= Bring your own LLM =

Supported providers as of May 2026:

* **Anthropic Claude** , Opus 4.7 (best reasoning), Sonnet 4.6 (best writer, 70% developer preference), Haiku 4.5 (cheap + fast).
* **OpenAI** , GPT-5.5-pro (reasoning), GPT-5.5 (writer), GPT-5.4 / GPT-5.4-mini (cheaper tiers).
* **Google Gemini** , Gemini 3.1 Pro (reasoning, 1M context), Gemini 2.5 Pro, Gemini 2.5 Flash, Gemini 2.5 Flash-Lite.

Pick provider AND model independently for the reasoning role and the writing role.

= Editable master prompt =

The default master prompt is research-backed (Princeton GEO study, Kevin Indig analysis, Ahrefs 560k AI Overview corpus). You can override every line, or just append site-specific notes in the Custom Instructions field.

= Output format =

Default output matches the Booming Venture blog layout: TL;DR group, question-shaped H2s, .bv-capsule answer paragraphs, ordered lists, FAQ block (auto-promoted to FAQPage schema by any standards-compliant SEO plugin), CTA at end. Override the entire output template by editing the master prompt.

== Installation ==

1. Upload the zip via Plugins -> Add New -> Upload Plugin.
2. Activate.
3. Open the CiteLeap menu in the WP sidebar.
4. Settings tab: paste your API keys, pick provider + model per role, set start date + posts-per-week + seed topics.
5. Planner tab: click "Generate ideas now" to produce a first batch. Click "Write draft" on any idea to draft immediately, or enable auto-publish in Settings and walk away.

== Frequently Asked Questions ==

= Where do API keys go? =

Stored in `wp_options` on your site only. They are sent only to the corresponding provider API during generation. CiteLeap never phones home.

= How much does generation cost? =

Roughly $0.05 to $0.30 per published post on Claude Sonnet 4.6 + Opus 4.7 (May 2026 pricing). Cheaper if you use Haiku 4.5 for ideation or GPT-5.4-mini.

= Can I edit drafts before they publish? =

Yes. Every draft lands in standard WP draft state and you can edit normally. The scheduler only acts on ideas in the queue, not on individual posts.

= Does it support languages other than English? =

The default master prompt is English. Override it with your target language and the LLM will follow.

= Is the output schema markup compliant? =

CiteLeap writes content shaped for FAQPage / HowTo / Article schema auto-detection. Pair with any standards-compliant SEO plugin (Yoast, Rank Math, our own SEO Boost) to inject the JSON-LD.

== Changelog ==

= 1.6.0 =
* NEW: progression overlay shown while long synchronous operations run. When you click any CiteLeap form button, a centered card appears with the action title, a 4 to 5 step narrative explaining what is happening behind the scenes, a spinner, and a hint that the page will reload when finished. Pure vanilla JS, no AJAX rewrite needed.
* NEW: pause / resume on every non-terminal queue row. Paused rows are skipped by the scheduler tick. State is preserved (paused queued stays queued; paused drafted stays drafted; etc.) so resume returns the row to its prior pipeline position.
* NEW: drafted rows can be manually scheduled to a specific datetime via inline datetime-local picker + Schedule button. Moves the WP post to status=future.
* NEW: drafted and scheduled rows have a Publish now button that promotes the post to publish state immediately, ignoring any planned datetime.
* NEW: scheduled rows can be Rescheduled to a different datetime or Unscheduled back to draft state.
* NEW: failed rows have a Retry button that resets them to queued (or queued_refresh) without manual editing of the option.
* NEW: workflow explanation panel at the top of the Planner tab, expandable, showing the five-step end-to-end flow and the per-row action map.
* NEW: includes/actions.php , unified state-machine module with explicit allowed transitions documented inline.
* CHANGE: every action button gets a one-paragraph help text underneath explaining what happens, cost estimate, and expected duration.
* CHANGE: paused queue rows are excluded from the new-content auto-tick AND the refresh auto-tick so the operator can freeze any item without removing it.

= 1.5.0 =
* NEW: Per-row "Pin datetime" control on every queued topic. Set a specific publish date+time via a native datetime-local input. The auto-tick respects per-post pins ahead of the auto-computed slot, so you can schedule a specific topic for "next Tuesday 10:00 CET" without disturbing the rest of the cadence. Clear the field and save to release the pin.
* NEW: "Next / Scheduled for" column on the Planner queue table shows what WILL happen to each queued row: a purple "pinned: " timestamp if an override is set, a blue "next: " timestamp from the auto-tick if auto mode is on, or "auto mode off" if not. Operator can see at a glance whether a topic is in flight.
* NEW: CiteLeap_Scheduler::eta_for( $row, $schedule ) helper exposes the resolved ETA per row for any UI / external integration.
* CHANGE: tick_new_content scheduler logic. Per-row publish_at overrides win first. Auto-generated queue items continue to use priority desc + created_at asc (FIFO within priority) for ordering. Pinned rows are excluded from the normal queue-empty top-up so a pin never blocks brainstorming.
* DOCS: behaviour clarified in HOW_IT_WORKS.md , manually-queued AND LLM-generated topics are both processed by the auto-tick.

= 1.4.0 =
* NEW: three-state Refresh mode (Off / Draft to pending review / Live overwrite). Pending review parks the proposed update in post meta until you Approve or Reject from the Planner. Live keeps the v1.3 immediate-overwrite behaviour.
* NEW: bulk-add refresh queue by paste-list. One line per item, each a numeric post ID, a slug, or a full permalink URL. Resolves, dedupes, queues.
* NEW: "Reset stuck" button for any refreshing entry that crashed mid-flight (rare but unblockable before this).
* NEW: CET (Europe/Amsterdam) is the default plugin timezone if the site has no timezone configured. WP-Cron slot calculations and Dashboard timestamps both use it.
* NEW: citeleap_format() + citeleap_tz() helpers expose the plugin's timezone-aware datetime everywhere.
* NEW: new statuses pending_review (purple badge) + Approve / Reject row actions, plus refresh_approved / refresh_rejected log events.

= 1.3.0 =
* NEW: three-state Auto mode (Off / Draft only / Publish). Draft mode auto-generates and refills the queue continuously, but every post is left as a WP draft for your manual review. Publish mode also schedules each draft at the next slot.
* NEW: bulk "Add your own topics" form on the Planner tab. Paste one topic per line; each becomes a queued post in submission order. No LLM call until you draft. Duplicate detection against existing slugs (any status) and the queue is automatic.
* NEW: manually-queued topics carry source=manual in the queue entry for traceability.
* Back-compat: legacy `auto=1` (v1.2.0 boolean) maps to `auto_mode=publish`. No re-save required.

= 1.2.0 =
* SECURITY: API keys encrypted at rest with AES-256-CBC, key derived from your AUTH_KEY salt. Decryption key lives in wp-config, not the database. Legacy plaintext keys auto-migrate on next save.
* SECURITY: input fields no longer echo the key value into the page HTML. Placeholders show only last 4 chars of the existing key.
* SECURITY: log entries auto-redact strings that look like sk-*, sk-ant-*, AIza* API keys.
* RELIABILITY: scheduler tick now uses a transient lock (5-min expiry) so concurrent cron ticks cannot double-spend. Unhandled exceptions are caught, logged as critical, and the lock is always released.
* OBSERVABILITY: log entries carry a severity field (info / warn / error / critical). Dashboard splits successes vs errors by severity rather than event-name allowlist.
* OBSERVABILITY: pre-cap warning banners on Dashboard at 80% spend (amber) and 100% (red).
* UX: per-provider "Test connection" button on Settings tab. One-shot tiny request that verifies key + quota + network.
* DOCS: shipped HOW_IT_WORKS.md inside the plugin folder. Plain-language operator guide covering every flow, status, dependency, and gotcha.

= 1.1.0 =
* NEW: Dashboard tab with this-month token usage + cost per provider, cap progress bars, status counts, recent successes / errors split.
* NEW: monthly budget caps per provider (Claude / OpenAI / Gemini) + an overall cap. 0 = unlimited. Generation refuses with a logged reason once a cap is reached. Resets on the 1st.
* NEW: pricing table baked in (May 2026 published Claude rates; best-effort OpenAI + Gemini estimates) with `wp_options 'citeleap_pricing_overrides'` for in-place adjustment and a `citeleap_pricing` filter.
* NEW: token + cost ledger in `wp_options 'citeleap_token_usage'`, keeps last 12 months.
* NEW: Refresh module. Pick existing published posts from the Planner tab, queue them for refresh, work them off manually with "Refresh now" or auto-queue posts older than the cadence-days threshold. Refresh runs preserve the slug, post ID, date, comments, post meta, and categories. Each refreshed post gets _citeleap_refresh_count + _citeleap_last_refreshed meta.
* NEW: auto-refresh mode with separate cadence (days since modified) and posts-per-week control. Independent of new-content auto-publish.
* NEW: post statuses: queued, drafted, scheduled, published, queued_refresh, refreshing, refreshed, failed. Color-coded badges in the Planner queue.
* NEW: "REFRESH" tag on refresh rows so they are distinguishable at a glance.
* CHANGE: default landing tab is now Dashboard.
* CHANGE: planner queue now sorts refresh items above new-content items so in-flight work is always visible at the top.

= 1.0.0 =
* Initial release.
* Multi-provider LLM router (Claude / OpenAI / Gemini).
* Idea generator (reasoning model) + writer (writing model).
* Editable master prompt with GEO/AEO May 2026 Bible defaults.
* Custom additional instructions field (applied to both prompts).
* Planner tab with queue + manual drafting + manual scheduling.
* WP-Cron hourly tick: auto-generates ideas + drafts + future-publishes.
* Auto-publish toggle. Manual mode keeps all manual actions working.
* Activity log of every operation.
* Word-count guardrail (warns under 1,000 words).
* Auto category creation + assignment.
* Meta description written into Yoast / AIOSEO / Rank Math compatible meta keys.

== Privacy ==

CiteLeap stores six options on your site (API keys, model selections, prompts, schedule, idea queue, activity log). The plugin makes outbound HTTP requests only to the LLM provider endpoints you have configured. No telemetry, no analytics, no phone-home.

== Roadmap ==

* Image generation for featured images (DALL-E / Imagen / Claude vision).
* Auto-internal-linking based on existing content corpus.
* A/B test mode: generate two variants per idea, compare engagement.
* Translation pipeline: same idea, multiple languages.
* Token-budget reporting per provider.
* Webhook on publish (Zapier / Slack / IndexNow).
