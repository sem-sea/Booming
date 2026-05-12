---
description: Run the 8-step GEO/AEO audit against a blog post slug, page, or local file
allowed-tools: Read, Grep, Glob, Bash, WebFetch, Write
---

# GEO/AEO audit — $ARGUMENTS

Run the 8-step audit from the Claude Code GEO/AEO Implementation Bible (May 2026) against `$ARGUMENTS`. The target can be:

- A blog post slug present in `build-blog-content-geo.py` (e.g. `what-is-ai-marketing`)
- A live URL on the deployed site
- A path to a local file (Markdown or PHP pattern)

For each step output `✅ PASS / ❌ FAIL / ⚠️ WARN`, the exact failing element, and one specific edit to make next. Audit only — do not modify files. Write the full report to `geo-audit-{slug}-{YYYY-MM-DD}.md`.

## The 8 steps

### Step 1 — Crawler posture

Fetch `/robots.txt` (via `Bash` `curl -s https://boomingventure.com/robots.txt` on prod, or inspect `wp-content/themes/booming-venture/inc/geo.php` for the dev rules). Confirm:

- Tier 1 BLOCK: GPTBot, ClaudeBot, anthropic-ai, Google-Extended, CCBot, Meta-ExternalAgent, Bytespider
- Tier 2 ALLOW: OAI-SearchBot, Claude-SearchBot, PerplexityBot, Applebot-Extended
- Tier 3 ALLOW: ChatGPT-User, Claude-User, Perplexity-User
- `Sitemap:` line present and resolves
- `/llms.txt` resolves to a valid Markdown file (H1 + blockquote + ≥1 H2 with bullet links)

### Step 2 — Rendering

`curl -A "GPTBot" $URL` and diff against the browser-rendered version. If H1, body copy, and FAQ are missing in the raw HTML, the page is client-rendered. **WordPress FSE is SSR by default; this should always pass.** Flag if any iframe / `<noscript>` content carries the answer.

### Step 3 — Answer capsule

For every H2 containing "What", "How", "Why", "When", "Should", "Can", "Is", "Are", "Does", "Do": check the next paragraph contains `[X] is …` / `[X] refers to …` / `[X] means …` of **20–25 words** wrapped in `.bv-capsule`.

Flag any question-shaped H2 without a capsule, or any capsule outside the 20–25 word range.

### Step 4 — Front-loading (ski ramp)

Compute character offset of first capsule. If > 30% of body length, flag.
Compute offsets of every statistic (`\d+%`, `\$\d`, `€\d`, `\d+x`). Confirm ≥ 3 inside first 30%.

### Step 5 — Citation density

External links to named domains. Flag if < 3 per 1,000 words.
Explicit author attributions ("according to [Org]", "[Org] found"). Flag if < 3.

### Step 6 — Schema

`curl $URL` (or `WebFetch`), extract every `<script type="application/ld+json">` block.
Validate via `https://validator.schema.org/` (use `WebFetch` to submit) or parse JSON locally.

Required: Organization (sitewide) + BlogPosting + (FAQPage OR HowTo) + Person on author byline + BreadcrumbList.

### Step 7 — Freshness

Read `dateModified` from BlogPosting schema. Flag if > 90 days old on cornerstone content.
Confirm WordPress `wp-sitemap.xml` `lastmod` matches `dateModified`.
Confirm the visible "Last updated: [Month YYYY]" banner is present (injected by `inc/geo.php`).

### Step 8 — Entity & brand signal

Proper-noun density: target ≥ 15% (Indig benchmark 20.6%). Use `grep -oE '\b[A-Z][a-z]+'` for a rough count; refine with judgement.
Confirm `Organization.sameAs` includes Wikipedia, Wikidata, LinkedIn, Crunchbase, GitHub where they exist.
Print three queries the operator should run manually in ChatGPT / Perplexity / Google AI Mode to verify citation.

## Output

Write `geo-audit-{slug}-{YYYY-MM-DD}.md` with:

```markdown
# GEO/AEO audit — {slug}
Date: {YYYY-MM-DD}
URL: {url}
Word count: {n}

| Step | Result | Detail | Next edit |
|---|---|---|---|
| 1. Crawler posture | ✅/❌/⚠️ | … | … |
| 2. Rendering | … | … | … |
| 3. Answer capsule | … | … | … |
| 4. Ski ramp | … | … | … |
| 5. Citation density | … | … | … |
| 6. Schema | … | … | … |
| 7. Freshness | … | … | … |
| 8. Entity signal | … | … | … |

## Recommended fix order

1. {highest-impact fix}
2. …

## Manual prompts to verify citation

- ChatGPT: "{query 1}"
- Perplexity: "{query 2}"
- Google AI Mode: "{query 3}"
```
