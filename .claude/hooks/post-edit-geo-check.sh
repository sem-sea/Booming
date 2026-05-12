#!/usr/bin/env bash
# post-edit-geo-check.sh — deterministic GEO/AEO validation hook.
#
# Wired by .claude/settings.json. Fires after Edit / Write on any file
# matching the content paths. Emits warnings, never blocks.
#
# Bible §9 reference + project-specific extensions.

set -e
FILE="$1"

# Only audit content files. Skip everything else fast.
case "$FILE" in
	*build-blog-content-geo.py|*build-blog-content.py|*/patterns/*.php|*/templates/*.html|*/booming-venture-content.xml)
		;;
	*)
		exit 0
		;;
esac

# Skip if the file was deleted.
[ -f "$FILE" ] || exit 0

ERRORS=0
warn() { printf 'GEO-HOOK ⚠️  %s — %s\n' "$1" "$2" >&2; ERRORS=$((ERRORS + 1)); }
ok()   { printf 'GEO-HOOK ✅  %s — %s\n' "$1" "$2" >&2; }

# Skip empty files.
SIZE=$(wc -c < "$FILE")
[ "$SIZE" -lt 200 ] && exit 0

# ----- AI-tell / filler check (always-on) ---------------------------
BANNED='(delve|elevate|harness|leverage|tapestry|in today.{0,3}s fast-paced|ever-evolving|game-changer|revolutionize|it.{0,2}s worth noting|in conclusion|moreover|furthermore|landscape of|navigate the complexit|seamless|robust ecosystem)'
if grep -qiE "$BANNED" "$FILE"; then
	HIT=$(grep -oiE "$BANNED" "$FILE" | sort -u | head -3 | tr '\n' ',')
	warn "$FILE" "Contains banned AI-tell phrases: ${HIT%,}"
fi

# ----- Em-dash check ------------------------------------------------
if grep -qE '—|–' "$FILE"; then
	warn "$FILE" "Contains em-dash / en-dash. Use commas, periods, or parens instead."
fi

# ----- "It's not just X, it's Y" check ------------------------------
if grep -qiE "it.{0,2}s not just [^,]+, it.{0,2}s" "$FILE"; then
	warn "$FILE" "Contains banned construction: \"It's not just X, it's Y\""
fi

# ----- Content-file-specific GEO checks -----------------------------
case "$FILE" in
	*build-blog-content-geo.py)
		# Each POSTS entry should have ≥3 question-shaped H2s and a faq() call.
		# Cheap heuristic: file-wide counts.
		POSTS_COUNT=$(grep -cE '^POSTS\["[a-z0-9-]+"\] = ' "$FILE" || true)
		FAQ_COUNT=$(grep -cE '^\s*faq\(\[' "$FILE" || true)
		Q_H2=$(grep -ciE 'h2\("(What|How|Why|When|Should|Can|Is|Are|Does|Do|Where) ' "$FILE" || true)
		CAP_COUNT=$(grep -cE '^\s*cap\(' "$FILE" || true)

		if [ "$POSTS_COUNT" -gt 0 ]; then
			# Expect ≥1 faq per post and ≥3 question H2s per post on average.
			EXPECTED_Q=$((POSTS_COUNT * 3))
			[ "$FAQ_COUNT" -lt "$POSTS_COUNT" ] && warn "$FILE" "Only $FAQ_COUNT faq() blocks for $POSTS_COUNT posts (expected ≥$POSTS_COUNT)"
			[ "$Q_H2"      -lt "$EXPECTED_Q" ] && warn "$FILE" "Only $Q_H2 question-shaped H2s for $POSTS_COUNT posts (expected ≥$EXPECTED_Q)"
			[ "$CAP_COUNT" -lt "$EXPECTED_Q" ] && warn "$FILE" "Only $CAP_COUNT cap() answer capsules for $POSTS_COUNT posts (expected ≥$EXPECTED_Q)"
		fi
		;;

	*/patterns/*.php|*/templates/*.html)
		# Patterns and templates: cheaper checks. Just look for em-dashes and filler.
		# (Schema and capsules are not required on every pattern.)
		:
		;;

	*booming-venture-content.xml)
		# WXR-wide: every blog post should have a capsule and an FAQ block.
		# Heuristic: count bv-capsule occurrences vs post count.
		POST_COUNT=$(grep -cE '<wp:post_type><!\[CDATA\[post\]\]>' "$FILE" || true)
		CAP_OCC=$(grep -oc 'bv-capsule' "$FILE" || true)
		FAQ_OCC=$(grep -ciE '>Frequently asked questions<' "$FILE" || true)
		if [ "$POST_COUNT" -gt 0 ]; then
			# Expect ≥3 capsules per post.
			EXP_CAPS=$((POST_COUNT * 3))
			[ "$CAP_OCC" -lt "$EXP_CAPS" ] && warn "$FILE" "Only $CAP_OCC bv-capsule occurrences across $POST_COUNT posts (expected ≥$EXP_CAPS)"
			[ "$FAQ_OCC" -lt "$POST_COUNT" ] && warn "$FILE" "Only $FAQ_OCC FAQ blocks across $POST_COUNT posts"
		fi
		;;
esac

# ----- Summary ------------------------------------------------------
if [ "$ERRORS" -gt 0 ]; then
	echo "GEO-HOOK: $ERRORS warning(s) in $FILE. Fix or accept consciously." >&2
	# Exit 0 so the hook never blocks. Treat as advisory.
	exit 0
fi

ok "$FILE" "passes GEO/AEO check"
exit 0
