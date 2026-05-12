---
name: citation-finder
description: Use this skill when a passage in any blog post or pattern needs a statistic, expert quote, or named source. Will search the web, prioritise primary sources, and either insert a verifiable citation or recommend dropping the claim. Triggers when editing `build-blog-content-geo.py` or any pattern file, and the geo-writer skill flags missing source attribution.
allowed-tools: WebSearch, WebFetch, Read, Edit
---

# citation-finder

Given a claim that needs a source:

## Source priority order (do not skip)

1. **Peer-reviewed papers** — arXiv, ACM, IEEE, ACL, Nature.
2. **Official primary docs** — OpenAI, Anthropic, Google, Microsoft, W3C, IETF, EU regulator pages, Dutch government, KvK, Autoriteit Persoonsgegevens.
3. **Named industry research with disclosed methodology** — Vercel/MERJ, Cloudflare research, Ahrefs studies, Search Engine Land original research, McKinsey, Gartner, Bain, BCG, HubSpot State of Marketing, Salesforce State of Marketing, Klaviyo benchmarks, Baymard Institute, SparkToro original studies.
4. **Established news** — Reuters, AP, Bloomberg, TechCrunch, Search Engine Journal, Search Engine Land.

## Avoid

- Anonymous SEO blogs
- AI-generated listicles
- Marketing-vendor blog posts citing themselves
- Out-of-date stats (> 24 months for fast-moving categories: AI, ad platforms, privacy regs)
- "Studies show…" without naming the study

## Workflow

1. Read the passage and identify the unsourced claim.
2. Run 2 to 3 targeted web searches via `WebSearch`. Quote the specific claim, not the topic.
3. For each candidate source, run `WebFetch` to confirm:
   - The exact stat is on the page
   - The publication date is recent enough
   - The methodology is disclosed (sample size, time window, geography)
4. Return:
   - **Verbatim quote ≤ 30 words**
   - **Source name** (organization + author if applicable)
   - **URL** (canonical, not a tracker-laden one)
   - **Publication date**
   - **Methodology note** (sample / methodology / category)
5. If no primary source exists, propose to either **drop the claim** or **rephrase to a softer version** the available sources support.

## Inserting the citation

In `build-blog-content-geo.py`, prefer the `SRC` dict for repeated sources:

```python
# If the source is already in SRC:
p("According to " + link("HubSpot State of Marketing", SRC["hubspot_som"]) + ", 71% of marketing teams use AI.")

# If new, add to SRC first:
SRC["baymard"] = "https://baymard.com/research"
p("Baymard Institute checkout research puts global cart abandonment at 70.19%.")
```

In WXR or patterns, hand-write the `<a>`:

```html
<a href="https://baymard.com/research" rel="noopener" target="_blank">Baymard Institute research</a>
```

Always include `rel="noopener" target="_blank"` on external links.

## Hard rules

- **Never invent a statistic.** If a search returns nothing reliable, the claim must change.
- **Never cite a vendor blog without checking the underlying study.** Vendor blogs often misquote the original (the Princeton GEO paper's table values are the textbook example of widespread misattribution).
- **Always note the year.** AI marketing stats from 2022 are not credible in 2026.
- **Prefer original studies over secondary write-ups.** If a Forbes article references a McKinsey study, link McKinsey.
- **One source per claim minimum.** Two if the claim is contested.
