---
description: Rewrite a passage (file + heading or quoted text) to be GEO-cite-worthy
allowed-tools: Read, Edit, Grep
---

# Optimize passage — $ARGUMENTS

Rewrite the named passage so it satisfies the Bible's citation criteria. The argument can be a file path + an H2 heading text, or a quoted passage.

## Required outputs

The rewritten passage must:

1. Begin with a **definitive sentence** opening with `[X] is …`, `[X] refers to …`, or `[X] means …`. No hedging (might, could, perhaps).
2. Contain **≥1 quantified claim with named source**, formatted as `According to [Source, Year], X% …` with an inline link to the primary source. Use `link("Source Name", url)` syntax when editing `build-blog-content-geo.py`; use `<a href="..." rel="noopener" target="_blank">Source</a>` when editing the WXR directly.
3. Stay between **50 and 150 words**.
4. Include **≥3 proper nouns** (named entities: companies, frameworks, people, places).
5. Use sentences **< 25 words each**, mixed lengths (vary aggressively, fragments OK).
6. End with **a concrete example or specific datapoint**, not a generality.

## Banned

- Em-dashes (use commas, periods, parens)
- AI tells: `delve, elevate, harness, leverage, navigate, tapestry, unlock, realm, landscape, robust, seamless, foster, moreover, furthermore, additionally, it's worth noting, in conclusion, in today's fast-paced world, ever-evolving, game-changer, revolutionize`
- "It's not just X, it's Y" construction
- Stacked X / Y / Z triads in consecutive sentences
- Symmetrical sentence lengths

## Workflow

1. Read the file. Locate the passage.
2. Identify the topic noun and the buyer.
3. Draft three candidate first sentences. Pick the most definitive.
4. Add one stat + source link if missing. Use the `SRC` dict in `build-blog-content-geo.py` for canonical URLs.
5. Trim or expand to 50–150 words.
6. Apply the edit via `Edit` (in-place, exact-string match).
7. Print a one-line diff summary.
