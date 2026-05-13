# LOVABLE → WORDPRESS PLAYBOOK
*Hard-won lessons from the Booming Venture conversion. Read in full before touching any future Lovable-to-WordPress project.*

---

## A. KEY LEARNINGS (every painful lesson, ranked by hours lost)

### A1. WordPress core type checks tighten silently between versions
- WP 6.7 added `array_map()` strict type checks inside `build_query_vars_from_query_block()`.
- A theme that worked on 6.5 fatals on 6.7 if any `wp:query` block has a string in `exclude` / `include` / `parents` / `sticky` where an array is expected.
- **Forbidden in `wp:query` block JSON**: `"exclude":"current"`, `"exclude":""`, `"include":""`, `"sticky":""`, `"queryId":0`, `"pages":0`, `"offset":0`. All of these are either string-where-array or zero-as-falsy that WP cannot reconcile.
- **Safe `wp:query`** uses only: `perPage`, `postType`, `order`, `orderBy`, `inherit`. Everything else WordPress fills in.
- **Lesson**: when a user reports a critical-error page, the FIRST thing to ask for is `wp-content/debug.log` — never guess.

### A2. Pages with pattern references are not editable in the block editor
- `<!-- wp:pattern {"slug":"booming-venture/about-story"} /-->` in `post_content` renders fine on the front-end but the editor shows a single **non-editable Pattern placeholder**.
- WP 6.6+ best practice: page `post_content` must contain **real, individual blocks**, not pattern references.
- Pattern references belong in `templates/*.html` and `parts/*.html` (Site Editor scope), not in pages.
- **Fix shipped**: `bv_expand_pattern_refs()` in `inc/installer.php` reads the pattern PHP file via `ob_start / include / ob_get_clean` and substitutes the rendered block markup at import time. Pages become fully editable; templates keep their references.

### A3. `BV_INSTALL_VERSION` gating made every theme update silently skip the WXR re-import
- The installer compared `bv_content_imported` option to `BV_INSTALL_VERSION` constant. When they matched, the import was skipped — even when the WXR had new content.
- Every release that changed the WXR (new posts, structural-page pattern roster updates) must bump `BV_INSTALL_VERSION` too.
- **Rule**: `BV_INSTALL_VERSION` follows the same release cadence as `BV_THEME_VERSION`. Both bumped together.

### A4. `get_option($name, [])` does NOT fire `option_<name>` filter when the row is missing
- WordPress fires `option_<name>` filter only when an option row exists in `wp_options`.
- Missing row → fires `default_option_<name>` instead.
- A theme that registers defaults via `option_<name>` alone gets zero defaults on a fresh install until someone saves Settings → … for the first time.
- **Rule**: when adding defaults to an option, hook BOTH `option_<name>` AND `default_option_<name>`. OR merge defaults inline in every consumer function.

### A5. CF7 enqueue gating on `has_shortcode($post->post_content, ...)` blanks every form
- `has_shortcode()` only scans the raw stored `post_content`. It cannot see shortcodes injected by block patterns at render time.
- Gating CF7's JS/CSS enqueue on `has_shortcode()` means every page that uses a pattern for its form loses CF7's scripts and styles — form HTML renders but does not submit.
- **Rule**: never gate CF7 enqueue. The perf cost is small; the correctness gain is non-negotiable.

### A6. `display: flex` on `.wp-block-column` collapses every column site-wide
- Adding `display: flex` to a column block without setting `flex-direction: column` makes the column's children lay out as a flex row, squeezed to min-content.
- Result: every word in every section renders one letter per line. The entire site looks broken.
- **Forbidden CSS**: any selector targeting `.wp-block-column` for layout changes. Scope card styling to the specific card classes (`.bv-value-card`, `.bv-step-card`, `.bv-why-card`).

### A7. CF7 hash IDs collide with slug shape
- CF7 hash IDs are 7 lowercase hex chars (`231533b`, `6c25a82`, `e46231e`).
- The string `contact` is also 7 lowercase chars. So is `quickscan` (9), `newsletter` (10).
- A regex `/^[a-z0-9]{6,}$/i` that passes through "already-mapped hashes" will swallow real slugs and break the resolver.
- **Rule**: check the slug map FIRST. Fall through to a hash regex only after the slug lookup fails. The hash regex itself must be `/^[a-f0-9]{6,8}$/i` (hex only, bounded length).

### A8. Theme version cache + filemtime ≠ versioned URL
- `BV_THEME_VERSION` baked into CSS/JS URLs (`?ver=1.0.0`) means every CSS edit lands with the same URL until the constant bumps.
- Strato and the browser cache that URL aggressively.
- **Fix**: `bv_asset_ver()` helper uses `filemtime()` on the file path. Every edit gets a fresh `?ver=<unix-timestamp>` automatically. The constant becomes a fallback only.
- Three version concerns are separate: `BV_THEME_VERSION` (WP admin display), per-asset filemtime (browser cache), versioned ZIP filename (download UX).

### A9. Mixed `<?php ?>` / HTML mode inside `add_filter` closures is fragile
- PHP allows it. OPcache versions and PHP 8.x configurations sometimes don't.
- **Rule**: inside any `add_filter` / `add_action` closure, use plain string concatenation. Never `ob_start()` mixed with `<?php ?>` mode-switching.

### A10. Visible diagnostics save hours
- `<meta name="bv-theme-version" content="X">` in `wp_head` lets the user prove which build is live just by viewing page source.
- A missing meta tag → upload did not land or theme is not active.
- An older value in the meta tag → server-side cache (OPcache, page cache) serving old PHP.
- **Rule**: every release ships a diagnostic meta tag with the current `BV_THEME_VERSION` AND the current `bv_content_imported` flag.

### A11. WP_DEBUG output is the single source of truth
- WordPress shows a generic "Er heeft zich een kritieke fout voorgedaan" page on fatals. Useless.
- `WP_DEBUG=true; WP_DEBUG_LOG=true; WP_DEBUG_DISPLAY=false; @ini_set('display_errors', 0);` in `wp-config.php` writes the real error to `wp-content/debug.log`.
- **First step on every "blank page" or "critical error" report**: enable WP_DEBUG, trigger the page, read the log.
- Recommended plugin for non-technical operators: **WP Debugging** by Andy Fragen (one toggle, no file edits).

### A12. Image URLs are not the same as WP attachments
- A bare UUID stored in `bv_media_map` (`4e357139-5a7e-4336-8796-94013f33dc3d`) needs resolution.
- `bv_resolve_attachment_url()` searches `_wp_attached_file` for the UUID, which works only if the file was uploaded via Media Library.
- FTP-uploaded files don't have a `_wp_attached_file` row.
- **Rule**: brand image defaults must use **full absolute URLs** (`https://boomingventure.com/wp-content/uploads/2026/05/<uuid>.png`). The lookup helper is the fallback, not the path.

### A13. Fallback content is mandatory for every external dependency
- CF7 not installed → form area looks empty.
- Brand image URL 404 → broken image icon.
- The theme must ship a graceful visible fallback for every external dependency. CSS `:has()` selectors can hide the fallback when the real content renders.

### A14. Versioned zip filename = primary download URL
- A rolling `booming-venture.zip` link is convenient but the WordPress admin cannot tell the difference between two uploads that have the same internal version.
- A versioned `booming-venture-1.7.1.zip` link in the same directory gives the user a name that proves the version on disk.
- **Rule**: every release commits BOTH the rolling latest AND the versioned copy. Delete the prior versioned copy before committing the new one to keep the repo small.

### A15. Em-dash mass replacement creates artefacts
- Stripping em-dashes site-wide with `sed -i 's/—/, /g'` produces `, ,` clusters and double-space artefacts that look like a typo on the front-end.
- **Rule**: when running mass voice-rule strips, also normalize the residue. `sed -i 's| ,  |, |g'` plus a manual pass to convert `, ` to `. ` where the original was a sentence break.

---

## B. TOOLING, SKILLS, CLAUDE FUNCTIONALITY TO HIT 10/10

### B1. Pre-flight tooling (run BEFORE writing any code)

| Tool | Purpose | Install |
|---|---|---|
| **Local by Flywheel** or **wp-env** | Local WP environment matching target version | `npx @wordpress/env start` |
| **WP-CLI** | Automated theme/plugin checks, WXR import dry-runs | https://wp-cli.org |
| **Theme Check plugin** | WP.org-compliant theme audit | install via Plugins |
| **Plugin Check** | Static analysis for hooks, escaping, i18n | install via wp-env |
| **PHP CodeSniffer + WordPress-Coding-Standards** | Linting | `composer global require wp-coding-standards/wpcs` |
| **WP Debugging plugin** by Andy Fragen | One-toggle WP_DEBUG on production | install once on every site |
| **WPGraphiQL / Query Monitor** | Inspect actual DB queries during render | install on dev |

### B2. Hardened Claude Code agent infrastructure

#### B2a. New subagents to define in `.claude/agents/`

- **`wp-block-validator`** — parses `wp:*` block markup and validates attribute types against the current WP block schema. Catches `"exclude":"current"`-style bugs before they fatal in core.
- **`wp-import-tester`** — dry-runs the WXR import against an in-memory SQLite DB and reports missing patterns, broken pattern refs, and structural-page slugs.
- **`css-impact-analyzer`** — flags global selectors that could break columns/headings (`.wp-block-column`, `.wp-block-columns > *`, `display: flex` on layout classes).
- **`release-builder`** — bumps `BV_THEME_VERSION` + `BV_INSTALL_VERSION`, edits `style.css` + `functions.php`, builds both zips, verifies version strings inside via `unzip -p`, commits with the standard message template, returns both download URLs.
- **`debug-log-reader`** — given a `debug.log` path or pasted block, pinpoints the file + line + WP version + likely root cause.

Each subagent gets its own `.claude/agents/<name>.md` with: trigger description, scoped tool list, system prompt.

#### B2b. Hooks (in `.claude/settings.json`)

```json
{
  "hooks": {
    "post-edit": [
      "php -l {{file}}",
      "test {{file}} = wp-content/themes/booming-venture/style.css && grep -q '^Version: ' {{file}}"
    ],
    "pre-commit": [
      "scripts/verify-versions.sh"
    ]
  }
}
```

- `post-edit` runs `php -l` on every PHP edit and immediately flags syntax errors before the agent moves on.
- A script `scripts/verify-versions.sh` confirms that `style.css` Version and `functions.php` `BV_THEME_VERSION` match before any commit.

#### B2c. Slash commands (in `.claude/commands/`)

- `/release [patch|minor|major]` — runs the release-builder subagent.
- `/verify-zip` — unzip the latest `booming-venture.zip` and dump the three critical version strings.
- `/wp-debug enable|disable` — toggle `WP_DEBUG` in `wp-config.php` (with SSH credentials).
- `/wxr-validate` — parse the WXR, count posts, check block balance per post, flag invalid `wp:query` attributes.
- `/test-import` — dry-run the WXR import via wp-import-tester subagent.

#### B2d. Skills (in `.claude/skills/`)

- `wp-block-builder` — produces valid `wp:*` block markup with current-version-safe attributes.
- `wp-pattern-author` — writes patterns that survive the block editor's parser AND render correctly when called from `wp:pattern` references in templates.
- `wp-installer` — composes the importer logic (after_switch_theme, structural-page refresh, install_version flag, force-reimport admin button) as one unit.
- `cf7-form-mapper` — given a list of `id="HASH" title="NAME"` shortcodes from a user, replaces every slug reference in patterns and templates with the literal shortcode.
- `geo-writer`, `schema-builder`, `citation-finder` — already in this repo; keep as-is.

### B3. Default `.claude/CLAUDE.md` improvements

The current AGENTS.md has the right shape. Add these sections (already partly there):

1. **Mandatory pre-flight checklist** (block this above any release):
   - [ ] `php -l` passes on every changed file
   - [ ] `WP_DEBUG=true` is enabled on the target install
   - [ ] `BV_THEME_VERSION` bumped
   - [ ] `BV_INSTALL_VERSION` bumped if WXR or pattern roster changed
   - [ ] Versioned zip name unique
   - [ ] `unzip -p booming-venture.zip booming-venture/style.css | grep Version` returns the new version
   - [ ] Two download URLs included in the release reply

2. **Forbidden patterns** (link to A1 to A15 above).

3. **Required defensive idioms**:
   - Every `wp_update_post` wrapped in `try/catch`.
   - Every `add_filter` callback either pure string concat or explicit `<?php` to top level.
   - Every option getter merges defaults inline OR registers both `option_*` and `default_option_*` filters.

### B4. Visual regression catch-net

- **Percy** or **Chromatic** snapshot home + about + services + blog + a representative post.
- Run before merging any CSS PR. The `.wp-block-column` collapse would have been caught here on the first commit.
- Cheap alternative: a shell script using `playwright` that screenshots 6 URLs and diffs against a baseline.

### B5. The debug.log triage workflow

When a user reports "blank page" or "critical error":

1. **Stop everything**. Don't ship another release.
2. Ask for `wp-content/debug.log`. Give the WP Debugging plugin instructions if they don't have it.
3. Wait for the actual error message.
4. The error message contains: PHP version, WP version, file path, line number. Match against the codebase.
5. Ship a targeted one-line fix.
6. Bump version + commit + verify + provide versioned URL.

Three iterations max from report → fix → verified deploy. If it takes more than three, the agent is guessing and should switch to debug-log-reader subagent.

---

## C. NEVER FUCK UP THIS PROCESS (HARD RULES)

### C1. RELEASE DISCIPLINE

1. **NEVER reuse a version number.** Even tiny fixes get a patch bump.
2. **NEVER reuse a versioned ZIP filename.** Each release ships `booming-venture-<NEW>.zip` and deletes `booming-venture-<OLD>.zip`.
3. **NEVER ship a release reply without the new versioned URL.** Primary link in the reply.
4. **NEVER skip the `unzip -p` verification.** Confirm version strings inside the ZIP before sharing the link.
5. **NEVER bump only one of**: `style.css` Version, `functions.php` `BV_THEME_VERSION`, `BV_INSTALL_VERSION`. All three are part of every release.

### C2. CODE DISCIPLINE

6. **NEVER use string values for array-typed `wp:query` attributes.** `"exclude":"current"`, `"include":""`, `"sticky":""`, `"parents":""` — all fatal on WP 6.7+.
7. **NEVER include optional `wp:query` attributes with empty/zero values.** Drop them; let core fill in.
8. **NEVER add layout-affecting CSS to `.wp-block-column` or `.wp-block-columns`.** Scope to specific card classes.
9. **NEVER use `display: flex` on a class that already contains `.wp-block-column` children unless `flex-direction: column` is explicit.**
10. **NEVER mix `<?php ?>` HTML-mode switching inside `add_filter` callbacks.** Plain string concat.
11. **NEVER gate CF7 enqueue on `has_shortcode($post->post_content, …)`.** Patterns are invisible to it.
12. **NEVER reference patterns from page `post_content`.** References live in `templates/*.html` and `parts/*.html` only. Pages need real blocks.
13. **NEVER trust `get_option('foo', $default)` to return the default.** Either merge defaults inline OR register both `option_*` and `default_option_*` filters.
14. **NEVER use a hex regex `/^[a-z0-9]{6,}$/` to detect CF7 hashes** — collides with `contact`, `quickscan`, etc. Use `/^[a-f0-9]{6,8}$/i` and check the slug map FIRST.
15. **NEVER use slug-style CF7 shortcodes when the literal hash + title is available.** Bulk-replace via sed when a user provides hash IDs.

### C3. CONTENT / WXR DISCIPLINE

16. **NEVER add new blog posts or change pattern rosters without bumping `BV_INSTALL_VERSION`.**
17. **NEVER ship the WXR without a balanced block count.** Run the open/close balance check before committing.
18. **NEVER hard-code `/assets/images/<slug>.jpg` fallbacks.** The files won't exist on the deployed theme. Use a real Strato URL.
19. **NEVER em-dash-strip without normalising residue.** Run `sed -i 's| ,  |, |g'` after the dash strip.
20. **NEVER let a pattern PHP file have side effects at register time** beyond computing local `$variables`. Patterns are included at init AND at render.

### C4. DEPLOYMENT DISCIPLINE

21. **NEVER ship a release without WP_DEBUG ready to flip on.** Recommend the WP Debugging plugin on the first deploy.
22. **NEVER assume a re-upload triggers `after_switch_theme`.** Re-activation does; check the install flag in the DB to confirm.
23. **NEVER assume Strato browser cache is fresh.** Use filemtime asset versioning so `theme.css?ver=<timestamp>` busts automatically.
24. **NEVER assume the user clicked "Replace current with uploaded".** Tell them explicitly, every release.

### C5. COMMUNICATION DISCIPLINE

25. **NEVER ship more than 3 consecutive "this should fix it" releases without evidence.** After 3, ask for `debug.log` + page source diagnostic meta tag.
26. **NEVER guess when a debug.log is available.** Read it; one error line is worth 5 speculative fixes.
27. **NEVER tell the user a version is "live" without their confirmation (page source meta tag).**
28. **NEVER respond to a frustrated user with a non-versioned link.** Always: bump → build → verify → versioned URL.

### C6. THE GOLDEN PRE-RELEASE CHECK (run this before every commit)

```bash
# 1. Lint every changed PHP file
find wp-content/themes/booming-venture -name "*.php" -newer .last-release | xargs -I{} php -l {}

# 2. Validate WXR balance
python3 -c "
import re
content = open('wp-content/themes/booming-venture/import/booming-venture-content.xml').read()
pattern = re.compile(r'<wp:post_name><!\[CDATA\[([^\]]+)\]\]>.*?<content:encoded><!\[CDATA\[(.*?)\]\]></content:encoded>', re.DOTALL)
bad = [m.group(1) for m in pattern.finditer(content) if (m.group(2).count('<!-- wp:') - m.group(2).count(' /-->')) != m.group(2).count('<!-- /wp:')]
assert not bad, f'Block imbalance in: {bad}'
print('WXR balance OK')
"

# 3. Confirm version strings sync
v_css=$(grep '^Version:' wp-content/themes/booming-venture/style.css | awk '{print $2}')
v_fn=$(grep "BV_THEME_VERSION" wp-content/themes/booming-venture/functions.php | head -1 | grep -oE "'[0-9]+\.[0-9]+\.[0-9]+'" | tr -d "'")
v_in=$(grep "BV_INSTALL_VERSION = " wp-content/themes/booming-venture/inc/installer.php | grep -oE "'[0-9]+\.[0-9]+\.[0-9]+'" | tr -d "'")
test "$v_css" = "$v_fn" || { echo "Version mismatch: style.css=$v_css vs functions.php=$v_fn"; exit 1; }
test "$v_css" = "$v_in" || { echo "Install version mismatch: $v_css vs $v_in"; exit 1; }

# 4. Build zip
rm -f wp-content/themes/booming-venture.zip wp-content/themes/booming-venture-*.zip
(cd wp-content/themes && zip -rq booming-venture.zip booming-venture -x "*.DS_Store" "booming-venture/.git/*")
cp wp-content/themes/booming-venture.zip wp-content/themes/booming-venture-${v_css}.zip

# 5. Verify version inside the zip
unzip -p wp-content/themes/booming-venture.zip booming-venture/style.css | grep "^Version: $v_css" || { echo "Version not inside zip!"; exit 1; }

echo "Release $v_css ready."
```

If any of the five steps fail, the release does not ship.

---

## D. PROCESS BLUEPRINT FOR THE NEXT LOVABLE → WORDPRESS CONVERSION

1. **Audit the Lovable site**: enumerate every page, every section, every image, every form. Save inventory to `inventory.md`.
2. **Stand up a local WP env** with `wp-env` or Local. Match the target WP version (ask the host).
3. **Scaffold the FSE theme**: `theme.json` v3, `templates/`, `parts/`, `patterns/`, `inc/` modules (setup, enqueue, cpt, blocks, patterns, seo, geo, installer, security, integrations).
4. **Build patterns FIRST**, page content SECOND. Patterns are starter blocks; pages reference them only during dev — then `bv_expand_pattern_refs()` inlines them at import.
5. **Wire CF7 mapping early.** Ask the user for the three hash IDs (`231533b` style) on day one. Hard-code them in `bv_cf7_slug_map()`.
6. **Wire brand images early.** Get the actual Strato uploads/<year>/<month>/ URLs from the user, put them as defaults in `bv_media_defaults()`.
7. **Enable WP_DEBUG on day one.** Don't deploy without it.
8. **Add diagnostic meta tags before first deploy.** Save hours later.
9. **Build the release script.** Versioned zip naming is non-negotiable from release 1.0.0.
10. **Test the WXR import on a fresh DB** before shipping. Run `force_reimport` manually too.
11. **Ship 1.0.0**, hold the user's hand through the upload, confirm the meta tag matches.
12. **From 1.0.0 onward**, every change goes through the golden pre-release check in C6.

Total expected timeline for a Lovable site of similar scope (1 brand mark + 5 to 7 images + 6 service rows + 40-blog content seed + 3 CF7 forms): 3 to 5 working days, not 15.

---

*Read this file before starting. Cross-reference every commit against C1 to C5. The expensive lessons are written down; don't re-pay them.*
