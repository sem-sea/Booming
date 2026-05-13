#!/usr/bin/env python3
"""
Replace the bodies of the 43 short blog posts in the WXR import file with
expanded GEO/AEO compliant versions (1200 to 1600 words each).

Reads:  build-blog-content-2026.py  (for helper defs + S source dict)
        /tmp/batches/batch{1,2,3,4}_output.py  (the reg() calls)

Behaviour:
  - For each slug in POSTS that already exists in the WXR, REPLACES the
    <content:encoded> body, <excerpt:encoded> excerpt, and <title> if
    different.
  - For each slug in POSTS that is not yet in the WXR, APPENDS a new item
    via the existing build_item() function from build-blog-content-2026.py.
  - All other items are left untouched.

Run: python3 build-blog-update-shortposts.py
"""
import re
import sys
from pathlib import Path
from datetime import datetime

ROOT = Path(__file__).parent
WXR = ROOT / "wp-content/themes/booming-venture/import/booming-venture-content.xml"

# ---------- Load the 2026 builder helpers + S dict ----------
# We import the module by execfile-style so we get its helpers + POSTS dict.
# We block the main() autorun by guarding __name__.
builder_globals = {"__name__": "__imported__", "__file__": str(ROOT / "build-blog-content-2026.py")}
exec(compile((ROOT / "build-blog-content-2026.py").read_text(), "build-blog-content-2026.py", "exec"), builder_globals)

# Pull helpers into local namespace so batch outputs can use them.
tldr = builder_globals["tldr"]
h2   = builder_globals["h2"]
h3   = builder_globals["h3"]
p    = builder_globals["p"]
cap  = builder_globals["cap"]
ul   = builder_globals["ul"]
ol   = builder_globals["ol"]
faq  = builder_globals["faq"]
link = builder_globals["link"]
cta  = builder_globals["cta"]
S    = builder_globals["S"]
reg  = builder_globals["reg"]
POSTS = builder_globals["POSTS"]
slug_fn = builder_globals["slug"]
build_item = builder_globals["build_item"]

# Clear POSTS so we only register the 43 short ones now.
POSTS.clear()

# ---------- Load batch outputs ----------
for batch_n in (1, 2, 3, 4):
    batch_path = Path(f"/tmp/batches/batch{batch_n}_output.py")
    if not batch_path.exists():
        sys.exit(f"Missing batch output: {batch_path}")
    code = batch_path.read_text(encoding="utf-8")
    exec(compile(code, str(batch_path), "exec"), {
        "reg": reg, "tldr": tldr, "h2": h2, "h3": h3, "p": p, "cap": cap,
        "ul": ul, "ol": ol, "faq": faq, "link": link, "cta": cta, "S": S,
    })

print(f"Loaded {len(POSTS)} expanded posts from batch outputs.")

# ---------- Replace bodies inside the WXR ----------
xml = WXR.read_text(encoding="utf-8")

# Split the XML into items so we can rewrite one at a time.
ITEM_RE = re.compile(r'<item>.*?</item>', re.DOTALL)
SLUG_RE = re.compile(r'<wp:post_name><!\[CDATA\[([^\]]+)\]\]></wp:post_name>')

def replace_cdata_field(item_xml: str, tag: str, new_value: str) -> str:
    """Replace a CDATA-wrapped field. Returns item_xml unchanged if tag absent."""
    pattern = re.compile(rf'(<{re.escape(tag)}>)<!\[CDATA\[.*?\]\]>(</{re.escape(tag)}>)', re.DOTALL)
    return pattern.sub(lambda m: m.group(1) + "<![CDATA[" + new_value + "]]>" + m.group(2), item_xml)

def replace_title(item_xml: str, new_title: str) -> str:
    pattern = re.compile(r'(<title>).*?(</title>)', re.DOTALL)
    return pattern.sub(lambda m: m.group(1) + new_title + m.group(2), item_xml, count=1)

replaced = 0
appended = 0
unmatched = []

def rewrite_item(item_xml: str) -> str:
    global replaced
    m = SLUG_RE.search(item_xml)
    if not m:
        return item_xml
    slug = m.group(1)
    if slug not in POSTS:
        return item_xml
    post = POSTS[slug]
    new_item = item_xml
    new_item = replace_cdata_field(new_item, "content:encoded", post["body"])
    new_item = replace_cdata_field(new_item, "excerpt:encoded", post["excerpt"])
    # Title: only replace if substantially different (case-insensitive).
    new_item = replace_title(new_item, post["title"])
    replaced += 1
    return new_item

new_xml = ITEM_RE.sub(lambda m: rewrite_item(m.group(0)), xml)

# Find slugs in POSTS that are not yet in the WXR. Those get appended.
existing_slugs = set(SLUG_RE.findall(xml))
to_append = [s for s in POSTS.keys() if s not in existing_slugs]
if to_append:
    print(f"WARN: {len(to_append)} slugs were not found in WXR and will not be appended (this script only updates):")
    for s in to_append:
        print(f"  - {s}")
        unmatched.append(s)

WXR.write_text(new_xml, encoding="utf-8")
print(f"Replaced bodies in {replaced} posts in {WXR.name}.")
if unmatched:
    print(f"Unmatched (please check slugs): {unmatched}")
