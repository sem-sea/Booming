=== CiteLeap ===
Contributors: boomingventure
Tags: ai, content, claude, openai, gemini, scheduled posts, geo, aeo
Requires at least: 6.6
Tested up to: 6.8
Requires PHP: 8.0
Stable tag: 1.0.0
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
