---
name: geo-writer
description: Use this skill when writing or rewriting any blog post body, landing page copy, FAQ block, or other long-form content for the Booming Venture site. Enforces the Claude Code GEO/AEO Implementation Bible (May 2026) plus the project voice rules. Triggers when editing `build-blog-content-geo.py`, any `wp-content/themes/booming-venture/patterns/*.php`, or any `content:encoded` block in the WXR import file.
allowed-tools: Read, Edit, Write, Grep, WebFetch
---

# geo-writer

Every piece of long-form content must satisfy ALL of the following before return. Treat these as hard gates, not suggestions.

## Structure (hard)

- **H1** contains the primary entity / keyword (already set by the post title).
- **Question-shaped H2s** for at least 3 of the section headings: `What is X?`, `How does Y work?`, `Why does Z fail?`, `When should I …?`. Descriptive H2s ("Why this happened now") are NOT compliant.
- **Answer capsule** immediately after every question-shaped H2: a single paragraph wrapped in `.bv-capsule`, 20–25 words, opening with `[X] is …` / `[X] refers to …` / `[X] means …`.
- One **comparison table** OR **ordered list ≥ 5 items** when the topic admits one.
- `## Frequently asked questions` section with 5 to 6 Q+A pairs (auto-converted to FAQPage JSON-LD by `inc/seo.php`).
- Closing paragraph with at least one **internal link** to `/services/`, `/funnel-calculator/`, `/roi-forecaster/`, or `/unify-framework/`.

## Density (per 1,000 words)

- ≥ 3 statistics with **named source link**. Format: `According to [Source, Year], X%` with `<a href="…" rel="noopener" target="_blank">Source</a>` or `link("Source", SRC["key"])` in the Python builder.
- ≥ 3 external authoritative links (.gov / .edu / peer-reviewed / named industry research / primary docs). Use `SRC` dict in `build-blog-content-geo.py` for canonical URLs.
- ≥ 1 named expert reference (e.g. "Ehrenberg-Bass Institute research", "April Dunford positioning work").
- Proper-noun (entity) density ≥ 15%. Target 20% (Indig benchmark 20.6% for AI-cited content vs 5–8% baseline).
- Average sentence length ≤ 22 words. Vary aggressively.

## Front-loading (the ski ramp)

44.2% of ChatGPT citations come from the first 30% of the page (Indig Feb 2026, Growth Memo, 1.2M-response analysis). Therefore:

- The **first answer capsule** must appear inside the first 30% of body content.
- **≥ 3 named-source statistics** must appear inside the first 30%.
- TL;DR / "Key takeaways" box at the very top is mandatory.

## Freshness

- Visible "Last updated: [Month YYYY]" line is injected automatically by `inc/geo.php` — do NOT duplicate manually.
- If quoting a statistic > 12 months old, note its year explicitly in the sentence.

## Voice (hard)

- **No em-dashes** anywhere. Use commas, periods, parentheses.
- **International English**, US spelling default (organize, optimize, color).
- **Banned phrases** (auto-flagged by the post-edit hook): `delve`, `elevate`, `harness`, `leverage`, `navigate`, `tapestry`, `unlock`, `realm`, `landscape`, `robust`, `seamless`, `foster`, `moreover`, `furthermore`, `additionally`, `it's worth noting`, `in conclusion`, `in today's fast-paced world`, `ever-evolving`, `game-changer`, `revolutionize`.
- Banned construction: `It's not just X, it's Y`.
- Banned pattern: stacked `X, Y, and Z` triads in consecutive sentences.
- Fragments OK. "And"/"But" sentence starts OK. Conversational, not casual.
- Specific numbers, named tools, real frameworks. No vague hyperbole.

## Workflow

1. Read existing post body if rewriting.
2. List the 3+ question-shaped H2s.
3. Draft each capsule (20–25 words, opening pattern enforced).
4. Add 3 stat-with-source links in the first 30%.
5. Verify entity density via `grep -oE '\b[A-Z][a-z]+'` rough count.
6. Run the post-edit hook (see `.claude/hooks/post-edit-geo-check.sh`).
7. If hook fails, fix and re-run before declaring done.

## Output format (Python builder)

```python
POSTS["slug"] = "\n".join([
    tldr("40-60 word key-takeaway with one source link."),
    p("Intro paragraph with FIRST stat + named source link."),
    h2("What is X?"),
    cap("X is [category] that [function] for [user]. Definitive opening, 20-25 words."),
    p("Supporting paragraph with second linked stat."),
    h2("How does X work in practice?"),
    cap("X works by … definitive 20-25-word answer."),
    ol([...]),
    h2("Why do most teams fail at X?"),
    cap("Most teams fail because … definitive."),
    p("Supporting paragraph with third stat link."),
    faq([
        ("Q1?", "A1."),
        ("Q2?", "A2."),
        ...
    ]),
    p("Closing with internal link to /services/ or similar."),
])
```

## Never

- Hand-write schema. `inc/seo.php` does it.
- Skip the FAQ block. FAQPage JSON-LD is the highest-leverage AI citation surface we have.
- Inline marketing-cliché filler intros.
- Write more than 4 sentences per paragraph.
- Ship without running the post-edit hook.
