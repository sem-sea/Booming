=== CiteLeap ===
Contributors: boomingventure
Tags: ai, content, claude, openai, gemini, scheduled posts, geo, aeo
Requires at least: 6.6
Tested up to: 6.8
Requires PHP: 8.0
Stable tag: 2.9.0
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

= 2.9.0 =
* NEW: two-tier capability model so Editors can run CiteLeap without administrator access. includes/caps.php defines CiteLeap_Caps with the constant USE_CAP = 'citeleap_use' (granted to Editor + Administrator on plugin activation) and MANAGE_CAP = 'manage_options' (administrator-only). Editors can use the Dashboard, Planner, Calendar, and Log tabs and drive every content operation (queue, draft, schedule, refresh, pause/resume/retry, image-pool bulk operators). Administrators retain exclusive control over API keys, prompts, research, image pool config, SEO toggles, languages, settings, and license / plan / top-up purchases.
* NEW: cap-aware navigation , the nav-tab strip on the main admin page hides Settings + Prompts + Research + Images + SEO + Languages + License from Editors. Hitting a manage-only tab URL falls back to the Dashboard rather than 403'ing, so a deep-linked Editor lands somewhere useful.
* NEW: CiteLeap_Caps::guard_use() + guard_manage() helpers used in every admin-post handler in actions.php, planner.php, refresh.php, images.php (bulk operators), topups.php (simulator stays manage-tier so credits cannot be granted by an Editor), and settings.php (every save-* + test-connection handler is manage-tier).
* NEW: caps are added on activation and revoked from every role on uninstall, so removing the plugin leaves no orphan capability behind.
* NEW: accessibility pass on the admin UI (WCAG 2.1 AA targets):
  - aria-current="page" on the active nav-tab, aria-label on the nav region itself.
  - role="alert" + aria-live="assertive" on the credit-exhausted banner and error flashes; role="status" + aria-live="polite" on success flashes + warning + info banners. Screen readers announce credit + plan state changes the moment they happen.
  - Screen-reader-only "Error:" / "Success:" / "Warning:" prefix on every flash message so meaning does not depend on color.
  - aria-hidden="true" on the decorative lock emoji in the locked-feature card; the card itself gets role="region" + aria-label describing what is locked.
  - .screen-reader-text + .citeleap-sr-only CSS class in assets/admin.css (mirrors WP core's), used by the new sr-only prefixes above.
  - Visible focus ring on every interactive control inside .wrap (buttons, .nav-tab, <select>, every input type, textarea, the pool-remove (X) button). Replaces the inconsistent default focus styles, makes keyboard navigation traceable.
  - @media (forced-colors: active) fallback so the locked-feature card border stays visible in Windows High Contrast Mode.
  - @media (prefers-reduced-motion: reduce) , kills the shimmer + spinner animations for users who have opted out.
* CHANGE: bumped Version + CITELEAP_VERSION + readme Stable tag to 2.9.0.

= 2.8.0 =
* NEW: PHPUnit test suite for the commercial layer. 41 tests across 4 test files (Plan, Credits, License, TopUps) covering capability gates per plan, credit ledger arithmetic (consume, reset, top-up consumption order, low/exhausted thresholds), license resolution with no SDK, top-up catalog ordering + Freemius webhook handlers. Runs in 15ms with zero external dependencies (in-memory WP function stubs in the bootstrap, no MySQL, no full WP test install).
* NEW: composer.json + phpunit.xml.dist + tests/bootstrap.php that stubs the WordPress functions the commercial layer reads (get_option, update_option, add_action, do_action, sanitize_*, esc_*, current_time, admin_url, wp_nonce_url). One-liner to run: `composer install && composer test`.
* NEW: tests/E2E.md , manual smoke-test checklist for the things PHPUnit cannot cover (rendered UI, real LLM calls, real Freemius checkout, dev mode toggle, cycle reset, uninstall hygiene). 8 sections, ~20 minutes on a clean install. Tied to a "regression triggers" list at the bottom so it stays alive across sprints.
* NEW: .gitignore for vendor/ + .phpunit.cache so dev tooling does not leak into the shipped zip.
* CHANGE: bumped Version + CITELEAP_VERSION + readme Stable tag to 2.8.0.

= 2.7.0 =
* NEW: locked-feature upgrade cards. Gated tabs (Calendar, Languages) now render a big "Upgrade to Pro" card showing the lowest plan that includes the feature, a one-paragraph explanation of what is being missed, the per-month price, and side-by-side "Upgrade" + "Compare plans" CTAs. Turns the plan gate into an in-product sales surface rather than a dead end.
* NEW: inline upgrade nudges on settings rows. Auto-publish + Refresh section headers on the Settings tab carry a small "Solo+" pill + Upgrade link when the current plan does not include the feature, so the operator sees what they would unlock without having to leave the page.
* NEW: form-level gating , the Auto mode + Refresh mode <select>s render disabled when the plan does not include the feature, so the gate is visible BEFORE the operator tries to save.
* NEW: server-side defense in the save_settings handler. Auto-publish saves are clamped to "off" when the plan does not include auto_publish; refresh mode is clamped to "off" when refresh is not on the plan. A Free user POSTing directly with developer tools can no longer enable a paid feature.
* NEW: helper API on CiteLeap_Plan , lowest_plan_with( $feature ), render_locked_notice( $feature, $title, $why ), render_inline_nudge( $feature ). One place to maintain the upgrade-card design system; every gated screen reads from it.
* CHANGE: bumped Version + CITELEAP_VERSION + readme Stable tag to 2.7.0.

= 2.6.0 =
* NEW: top-up credit packs , Starter (10 credits / $49), Growth (50 / $199), Scale (100 / $349), Bulk (500 / $1499). Per-credit price drops from $4.90 down to $3.00 as the pack scales. Top-up credits never expire while the license stays active and are consumed AFTER monthly included credits each cycle.
* NEW: top-up grid on the License & Credits tab. Mobile-first card layout, "Best value for solo" + "Most credits" badges, per-pack description, real Freemius checkout buttons when the SDK is loaded.
* NEW: simulator mode , when the Freemius SDK is not yet present, the Buy buttons add the pack credits straight to the local ledger (after a manage_options + nonce check) so the operator can validate the full credit-grant + spend pipeline before connecting real billing. A guardrail blocks the simulator the moment the SDK goes live, so credits cannot be granted without payment in production.
* NEW: Freemius webhook listeners. citeleap_fs_loaded fires after init; CiteLeap_TopUps::register_freemius_listeners() then hooks fs_after_purchase_citeleap (grants pack credits by matching fs_plan_id), fs_after_account_plan_change_citeleap (resets the monthly cycle on plan up/down-grade), and fs_after_account_user_change_citeleap (resets cycle on account swap).
* NEW: success flash on the License tab when a top-up is granted ("Growth pack purchased , 50 credits added to your reserve"). Error flashes for unknown pack + simulator-blocked-in-production.
* CHANGE: CiteLeap_License::top_up_url() now routes through CiteLeap_TopUps so the credit banner's "Buy top-up pack" CTA matches the on-page catalog and the simulator URL when applicable.
* CHANGE: bumped Version + CITELEAP_VERSION + readme Stable tag to 2.6.0.

= 2.5.0 =
* NEW: commercial layer , three new modules ship the bones of the paid SaaS.
  - includes/license.php , Freemius SDK wrapper. When the SDK is dropped into vendor/freemius/wordpress-sdk/start.php and CITELEAP_FS_ID + CITELEAP_FS_PUBLIC_KEY are defined in wp-config.php, the plugin reads the real plan + trial state + checkout URLs straight from Freemius. Until then, the install runs on the Free plan (or on Developer mode if CITELEAP_DEV_MODE is true).
  - includes/plan.php , single source of truth for the five plans (Free, Solo, Pro, Agency, Enterprise) plus the Developer slug. Defines credits per cycle, sites allowed, overage rate, and a per-plan capability list (auto_publish, refresh, multilingual, calendar, scheduling, top_ups, white_label, priority_support, images_bulk). CiteLeap_Plan::has( $cap ) is the gate everywhere in the plugin.
  - includes/credits.php , per-cycle credit ledger in CITELEAP_OPTION_CREDITS. Tracks used + top_up + lifetime, auto-resets on the 1st of each calendar month for paid plans, never resets on Free (3 lifetime credits). consume() decrements monthly credits first, then dips into top-ups.
* NEW: hard credit gate. Drafting (CiteLeap_Generator::write_post_from_idea) and refreshing (CiteLeap_Refresh::refresh_from_queue) check can_consume() before touching the LLM; if exhausted they log credit_blocked and return a clear upgrade message. Successful drafts + refreshes consume one credit and log credit_consumed with plan + remaining.
* NEW: refresh is plan-gated. The Free plan cannot run refreshes (returns a "Upgrade to Solo or higher" message). All other paid plans + Developer mode have it.
* NEW: admin banner on every CiteLeap admin page. Three states: red error (exhausted) with Upgrade + Buy top-up CTAs; amber warning (under 20% remaining) with the same CTAs; quiet info pill (healthy) showing plan + usage + a link to the License tab. Developer mode shows a green "unlimited" pill.
* NEW: License & Credits tab. Plan label + billing status (paying / trial-days-left / free), included-per-cycle, used + percent, top-up balance, remaining, lifetime consumed, sites allowed, overage rate, full capability matrix vs current plan, and a Compare-plans table highlighting the active row. Upgrade / Buy top-up / Account & invoices buttons all wire through to Freemius when loaded, or to the License tab itself as a placeholder until then.
* NEW: Freemius bootstrap stub in citeleap.php. Conditional require on vendor/freemius/wordpress-sdk/start.php with the standard fs_dynamic_init shape (id, slug, public_key, premium, paid plans, 14-day trial no-card, plugin menu slug). Fires the citeleap_fs_loaded action when ready so listeners can hook checkout / subscription events. No-op when the SDK file is absent.
* NEW: CITELEAP_DEV_MODE constant. Define in wp-config.php to bypass every plan gate and credit check (for the author's own site + local development). Banner shows a green "Developer mode" pill so the state is visible.
* NEW: CITELEAP_OPTION_CREDITS option registered in the uninstall hook so the ledger is wiped when the plugin is removed.
* CHANGE: bumped Version + CITELEAP_VERSION + readme Stable tag to 2.5.0.

= 2.4.0 =
* NEW: full Blog-Images plugin functionality ported into CiteLeap. The Images tab now ships the bulk "Assign random images now" and "Re-randomise" operators alongside the existing pool picker. Walks every published post: posts without a Featured image get a fresh random pick from the pool, posts previously random-assigned can be re-rolled without touching manual operator picks.
* NEW: pool status panel on the Images tab , images in pool, published posts, posts without a Featured image, posts with a random-assigned image. All four counters refresh on every load.
* NEW: per-thumbnail remove (X) button on every pool preview tile so the operator can drop an image from the pool without re-opening the Media Library.
* NEW: block-theme card injection. A render_block hook on core/post-excerpt prepends a clickable card thumbnail in archive views for themes whose post template uses post-excerpt without a featured-image block.
* NEW: editor nudge , an info notice appears on the post edit screen when publishing a post without a Featured image, pointing the operator to the pool.
* NEW: thumbnail column on the Posts list with a small (random) / (manual) tag so the operator can scan at a glance which posts got auto-assigned vs operator-picked.
* NEW: theme-override CSS that constrains body.single-post .wp-block-post-featured-image to a 16:9 box with the global content-size width, so the giant edge-to-edge hero look the operator complained about is gone without us swapping blocks.
* NEW: skeleton-shimmer placeholder while the hero / card image loads.
* CHANGE: bumped Version + CITELEAP_VERSION to 2.4.0.

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
