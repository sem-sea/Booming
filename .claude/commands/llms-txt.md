---
description: Audit and rebuild /llms.txt and /llms-full.txt
allowed-tools: Read, Bash, WebFetch
---

# llms.txt audit / rebuild

`/llms.txt` and `/llms-full.txt` are dynamic endpoints served by `inc/geo.php` (see `template_redirect` for `bv_llms`). They are rebuilt at request time from live WordPress content; there is no static file to edit.

## Workflow

1. On a deployed environment, fetch:
   ```bash
   curl -s https://boomingventure.com/llms.txt
   curl -s https://boomingventure.com/llms-full.txt | head -100
   ```
2. Confirm against llmstxt.org spec:
   - H1 site name first line
   - `>` blockquote summary directly under H1
   - `## Core`, `## Lead magnets and landing pages`, `## Blog`, `## Optional`, `## Company` sections
   - Bullet links in `[Link text](url): description` format
3. If a page is missing from `## Core`, add it to the `$core` array in `inc/geo.php` (template_redirect handler).
4. If full-text variant is too short or too long, adjust `numberposts` in the `get_posts()` call.
5. Confirm rewrite rules are flushed:
   ```bash
   wp rewrite flush --hard
   ```
6. Re-verify with curl.

## Common failures

| Symptom | Fix |
|---|---|
| `/llms.txt` returns the WP 404 page | Rewrite rules not flushed. Run `wp rewrite flush --hard` or visit Settings → Permalinks → Save. |
| Body is empty | `template_redirect` exited too early. Check `get_query_var( 'bv_llms' )` returns the mode. |
| Content-Type wrong | Header sent after WP started output. Move `header()` calls before any other echo. |
| Wrong posts listed | Edit the `get_posts()` array in `inc/geo.php`. |
| File too large | Reduce the `numberposts` for the full variant; spec allows truncation. |
