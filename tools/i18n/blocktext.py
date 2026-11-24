"""Translate the visible text of WordPress block markup without touching the markup.

The Dutch section of boomingventure.com reuses the exact blocks, classes and
inline styles of the English pages; only the words change. This module finds
the translatable units in a block template, pattern or post_content string and
swaps them for the Dutch text from a dictionary.

A unit is the inner HTML of the innermost text-bearing element (p, h1-h6, li,
a, td, span, ...) that contains no block-level child. Inline markup inside a
unit (<strong>, <br>, <a>, <span class>) stays part of the unit, so the Dutch
text can reorder words around it. PHP, HTML comments (block delimiters),
<style>, <script> and <svg> are masked and never translated. Attribute values
for alt, aria-label, title and placeholder are units too.

    python3 tools/i18n/blocktext.py extract FILE...        -> JSON of units
    python3 tools/i18n/blocktext.py apply SRC DICT DST     -> translated file
"""
import json
import re
import sys

MASK_RE = re.compile(r"<\?php.*?\?>|<!--.*?-->|<style\b.*?</style>|<script\b.*?</script>|<svg\b.*?</svg>|\[contact-form-7[^\]]*\]", re.S | re.I)
UNIT_TAGS = r"p|h[1-6]|li|a|figcaption|td|th|dt|dd|label|button|blockquote|cite|summary|div|span|strong|em|small|caption|option|legend"
ELEM_RE = re.compile(r"<(" + UNIT_TAGS + r")(\s[^<>]*)?>(.*?)</\1\s*>", re.S | re.I)
START_RE = re.compile(r"<(" + UNIT_TAGS + r")[\s>]", re.I)
BLOCK_OPEN = re.compile(r"<(?:p|h[1-6]|li|ul|ol|div|section|figure|table|thead|tbody|tr|td|th|blockquote|header|footer|nav|main|article|aside|details|summary|form|fieldset|figcaption|dl|dt|dd)[\s>/]", re.I)
ATTR_RE = re.compile(r'\b(alt|aria-label|title|placeholder)="([^"]*[A-Za-z][^"]*)"')
TOKEN_RE = re.compile(r"\x00(\d+)\x00")


def mask(s):
    tokens = []

    def rep(m):
        tokens.append(m.group(0))
        return f"\x00{len(tokens) - 1}\x00"

    return MASK_RE.sub(rep, s), tokens


def unmask(s, tokens):
    return TOKEN_RE.sub(lambda m: tokens[int(m.group(1))], s)


def has_text(inner_masked):
    t = TOKEN_RE.sub(" ", inner_masked)
    t = re.sub(r"<[^>]+>", " ", t)
    t = re.sub(r"&[a-z#0-9]+;", " ", t)
    return bool(re.search(r"[A-Za-z]{2,}", t))


def units(masked):
    """Return [(inner_start, inner_end)] of outermost translatable units."""
    found = []
    for sm in START_RE.finditer(masked):
        m = ELEM_RE.match(masked, sm.start())
        if not m:
            continue
        tag = m.group(1).lower()
        inner = m.group(3)
        if BLOCK_OPEN.search(inner):
            continue
        if re.search(r"<" + tag + r"[\s>]", inner, re.I):
            continue
        if not has_text(inner):
            continue
        found.append((m.start(3), m.end(3), m.start(), m.end()))
    found.sort(key=lambda x: (x[2], -x[3]))
    out, last_end = [], -1
    for s, e, os_, oe in found:
        if os_ < last_end:
            continue
        out.append((s, e))
        last_end = oe
    return out


def norm(s):
    return re.sub(r"\s+", " ", s).strip()


def extract(text):
    masked, tokens = mask(text)
    keys = []
    for s, e in units(masked):
        keys.append(norm(unmask(masked[s:e], tokens)))
    for m in ATTR_RE.finditer(masked):
        keys.append("@" + m.group(2))
    seen, uniq = set(), []
    for k in keys:
        if k not in seen:
            seen.add(k)
            uniq.append(k)
    return uniq


def apply(text, table, strict=True):
    masked, tokens = mask(text)
    missing = []
    spans = units(masked)
    out = masked
    for s, e in reversed(spans):
        key = norm(unmask(masked[s:e], tokens))
        if key in table:
            new = table[key]
            if new == key:  # structural unit, leave the source bytes alone
                continue
            # keep the original leading/trailing whitespace of the unit
            lead = re.match(r"\s*", masked[s:e]).group(0)
            trail = re.search(r"\s*$", masked[s:e]).group(0)
            out = out[:s] + lead + new + trail + out[e:]
        else:
            missing.append(key)

    done = set(table.values())

    def attr_rep(m):
        k = "@" + m.group(2)
        if k in table:
            return f'{m.group(1)}="{table[k][1:] if table[k].startswith("@") else table[k]}"'
        if k in done:  # already Dutch: it sat inside a unit that was translated
            return m.group(0)
        missing.append(k)
        return m.group(0)

    out = ATTR_RE.sub(attr_rep, out)
    # tokens that were inserted with translated text are literal already;
    # only the masked originals need restoring
    result = unmask(out, tokens)
    if strict and missing:
        raise SystemExit("untranslated units:\n" + "\n".join(f"  {m!r}" for m in missing))
    return result, missing


if __name__ == "__main__":
    cmd = sys.argv[1]
    if cmd == "extract":
        allk = []
        for f in sys.argv[2:]:
            for k in extract(open(f, encoding="utf-8").read()):
                if k not in allk:
                    allk.append(k)
        print(json.dumps({k: "" for k in allk}, ensure_ascii=False, indent=1))
    elif cmd == "apply":
        src, dct, dst = sys.argv[2:5]
        table = json.load(open(dct, encoding="utf-8"))
        res, _ = apply(open(src, encoding="utf-8").read(), table)
        open(dst, "w", encoding="utf-8").write(res)
