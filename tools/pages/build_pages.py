"""Build the 1.30.0 growth pages into import/nl/content.json (key pages_v2).

    python3 tools/pages/build_pages.py

Then, once the theme is live: POST /wp-json/bv/v1/import-nl?update=pages-v2
"""
import json
import pathlib
import re
import sys

HERE = pathlib.Path(__file__).resolve().parent
sys.path.insert(0, str(HERE))
from pages_main import geo_service, rotterdam  # noqa: E402
from pages_more import ai_check, cases, pricing  # noqa: E402
from pages_sectors import sector_items  # noqa: E402
from pages_stats import stats_page  # noqa: E402

ROOT = HERE.parents[1]
FILE = ROOT / "wp-content/themes/booming-venture/import/nl/content.json"
BANNED = ["delve", "elevate", "harness", "leverage", "navigate", "tapestry", "unlock", "realm", "landscape", "robust", "seamless",
          "foster", "moreover", "furthermore", "it's worth noting", "in conclusion", "game-changer", "revolutionize", "ever-evolving"]


def words(html):
    text = re.sub(r"<!--.*?-->", " ", html, flags=re.S)
    text = re.sub(r"<[^>]+>", " ", text)
    return len(re.findall(r"[\w€%.,+-]+", text))


def check(label, html):
    problems = []
    if "—" in html or "–" in html:
        problems.append("dash")
    low = re.sub(r"<[^>]+>", " ", html).lower()
    for b in BANNED:
        if re.search(r"\b" + re.escape(b) + r"\b", low):
            problems.append(b)
    if html.count("<!-- wp:group") != html.count("<!-- /wp:group -->"):
        problems.append("group balance")
    return problems


def main():
    items = [
        {"key": "geo-service", "en": geo_service(False), "nl": geo_service(True)},
        {"key": "rotterdam", "en": rotterdam(False), "nl": rotterdam(True)},
        {"key": "cases", "en": cases(False), "nl": cases(True)},
        {"key": "pricing", "en": pricing(False), "nl": pricing(True)},
        {"key": "ai-check", "en": ai_check(False), "nl": ai_check(True)},
        {"key": "stats", "en": stats_page(False), "nl": stats_page(True)},
    ] + sector_items()
    bad = 0
    for it in items:
        for lang in ("en", "nl"):
            b = it[lang]
            pr = check(it["key"] + "/" + lang, b["content"] + b["title"] + json.dumps(b["meta"], ensure_ascii=False))
            if not it["key"].startswith("sector-"):
                print(f'{it["key"]:12} {lang}  {words(b["content"]):5} words  /{(b["parent"] + "/") if b["parent"] else ""}{b["slug"]}/  {pr or ""}')
            if pr:
                bad += 1
            md = b["meta"]["_yoast_wpseo_metadesc"]
            if len(md) > 160:
                print("  long meta description", it["key"], lang, len(md))
    data = json.loads(FILE.read_text(encoding="utf-8"))
    data["pages_v2"] = items
    FILE.write_text(json.dumps(data, ensure_ascii=False), encoding="utf-8")
    print(f"{len(items)} items ({sum(1 for i in items if i['key'].startswith('sector-'))} sectors) written to {FILE.relative_to(ROOT)}; problems: {bad}")


if __name__ == "__main__":
    main()
