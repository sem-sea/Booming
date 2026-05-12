---
description: Generate an FAQ block for a blog post (auto-picked up into FAQPage schema)
allowed-tools: Read, Edit, Grep
---

# Generate FAQ — $ARGUMENTS

Add a `## Frequently asked questions` section to the target post in `build-blog-content-geo.py` (or a pattern file). The block is automatically converted into FAQPage JSON-LD by `inc/seo.php` (see `bv_extract_faq_from_content()`).

## Workflow

1. Read the target post's full body.
2. Extract the **5–6 most likely buyer / user questions**. Sources of question candidates:
   - H2s already in the post (rephrased as questions)
   - Common objections in the category
   - Cost / time / risk concerns
   - "Will this work for [my segment]?" variants
   - Implementation / "where do I start" questions
3. For each, write a **20–25 word answer** starting with the definitional pattern (`[X] is …`, `Yes, …`, `Most teams …`, etc).
4. Each Q+A must be **readable without the rest of the page** (FAQPage schema strips context).
5. Append the block as the last section before the closing paragraph, using `faq([...])` in `build-blog-content-geo.py`.
6. Run `python3 build-blog-content-geo.py` to rebuild the WXR.

## Template (Python builder)

```python
faq([
    ("Question one — start with What/How/Why?", "Direct answer, 20-25 words. Definitive opening."),
    ("Question two?", "Direct answer."),
    ("How much does this cost?", "Direct numeric range."),
    ("How long until results?", "Direct timeline."),
    ("Should small teams do this?", "Direct yes/no with caveat."),
])
```

## Banned in answers

Same as `optimize-passage`: no em-dashes, no AI tells, no hedging, no "It's not just X, it's Y".

## Verify

After rebuild, on a deployed page run `curl $URL | grep -A 200 'FAQPage'` to confirm the JSON-LD picked up every Q+A pair. If a pair is missing, the H3/paragraph parsing in `bv_extract_faq_from_content()` failed (usually whitespace).
