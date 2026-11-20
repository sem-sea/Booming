"""Daily calendar 21 November to 20 December 2026 at 07:30 UTC (08:30 Amsterdam).

Checks every post with the geo-writer checks, verifies that internal links
only point to pages that are live on the publish date, and writes REST
payloads for the new posts.
"""
import datetime as dt
import importlib.util
import json
import pathlib
import re

HERE = pathlib.Path(__file__).resolve().parent


def load(name, fname):
    spec = importlib.util.spec_from_file_location(name, HERE / fname)
    m = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(m)
    return m


q4, q1, da, db = (load("q4", "posts_2026_q4.py"), load("q1", "posts_2027_q1.py"),
                  load("da", "posts_daily_a.py"), load("db", "posts_daily_b.py"))

CAL = {
    "2026-11-21": ("new", "how-chatgpt-search-picks-sources-b2b"),
    "2026-11-22": ("move", "brand-mentions-vs-backlinks-ai-visibility"),
    "2026-11-23": ("new", "ai-visibility-tools-compared-peec-otterly-profound"),
    "2026-11-24": ("keep", "how-to-choose-a-geo-agency"),
    "2026-11-25": ("move", "get-on-best-of-lists-ai-assistants-cite"),
    "2026-11-26": ("new", "comparison-pages-ai-assistants-cite"),
    "2026-11-27": ("new", "fix-wrong-ai-answers-about-your-company"),
    "2026-11-28": ("new", "content-freshness-ai-search-b2b"),
    "2026-11-29": ("new", "do-press-releases-get-cited-by-ai"),
    "2026-11-30": ("new", "earned-media-ai-visibility-b2b"),
    "2026-12-01": ("keep", "eu-ai-act-b2b-marketing-teams"),
    "2026-12-02": ("move", "ai-written-content-google-spam-policies"),
    "2026-12-03": ("new", "linkedin-ai-citations-b2b"),
    "2026-12-04": ("new", "original-research-b2b-lead-magnet-ai-citations"),
    "2026-12-05": ("new", "claude-ai-search-b2b-citations"),
    "2026-12-06": ("new", "gemini-ai-search-b2b-citations"),
    "2026-12-07": ("new", "which-company-pages-ai-assistants-cite"),
    "2026-12-08": ("keep", "ai-crawler-checklist-b2b-website"),
    "2026-12-09": ("move", "does-llms-txt-help-ai-visibility"),
    "2026-12-10": ("move", "ai-visibility-cybersecurity-vendors"),
    "2026-12-11": ("new", "measure-ai-referral-traffic-ga4-b2b"),
    "2026-12-12": ("new", "b2b-buyers-ai-before-sales"),
    "2026-12-13": ("new", "schema-markup-b2b-ai-search"),
    "2026-12-14": ("new", "publish-pricing-ai-search-b2b"),
    "2026-12-15": ("keep", "30-day-ai-visibility-sprint"),
    "2026-12-16": ("move", "geo-pricing-what-generative-engine-optimization-costs"),
    "2026-12-17": ("new", "ai-overviews-europe-b2b"),
    "2026-12-18": ("move", "microsoft-copilot-bing-b2b-ai-visibility"),
    "2026-12-19": ("new", "write-passages-ai-assistants-quote"),
    "2026-12-20": ("move", "ai-visibility-metrics-for-the-board"),
}
MOVE_IDS = {"geo-pricing-what-generative-engine-optimization-costs": 741, "get-on-best-of-lists-ai-assistants-cite": 742,
            "ai-visibility-cybersecurity-vendors": 743, "brand-mentions-vs-backlinks-ai-visibility": 744,
            "does-llms-txt-help-ai-visibility": 745, "microsoft-copilot-bing-b2b-ai-visibility": 746,
            "ai-visibility-metrics-for-the-board": 747, "ai-written-content-google-spam-policies": 748}
CATS = {"ai-search": 35, "ai-marketing": 2, "marketing-analytics": 22, "b2b-marketing": 6,
        "conversion-optimization": 17, "lead-generation": 18}
MEDIA = [282, 285, 288, 290, 291, 295, 296, 241, 242, 243, 244, 245, 247, 249, 250, 252, 253, 255]

ALL = {}
for m in (q4, q1, da, db):
    ALL.update(m.POSTS)

# live pages and posts already published (internal links may always point to these)
LIVE_PAGES = {"services", "boardroom-quickscan", "funnel-calculator", "roi-forecaster", "unify-framework",
              "free-growth-guide", "about", "blog"}
LIVE_POSTS = {"generative-engine-optimization-audit", "geo-vs-seo-vs-aeo-b2b-budget-2026",
              "how-to-get-cited-in-google-ai-mode", "audit-ai-visibility-without-paying-profound",
              "measure-brand-lift-llm-mentions", "get-cited-by-chatgpt-b2b-saas-queries"}

if __name__ == "__main__":
    date_of = {slug: d for d, (_, slug) in CAL.items()}
    assert len(CAL) == 30 and len(set(date_of)) == 30
    problems = 0
    out = pathlib.Path("/tmp/claude-0/-home-claude/b1c26679-a08f-5463-946e-707c8c5a96ab/scratchpad/payloads_daily")
    out.mkdir(exist_ok=True)
    mi = 0
    for d, (kind, slug) in sorted(CAL.items()):
        post = ALL[slug]
        content = "\n".join(post["blocks"])
        for l in sorted(set(re.findall(r'href="/([^"/]+)/"', content))):
            if l in LIVE_PAGES or l in LIVE_POSTS:
                continue
            if l not in date_of or date_of[l] >= d:
                print("LINK PROBLEM", d, slug, "->", l)
                problems += 1
        c = q4.check(slug, post)
        if c["issues"] or len(post["excerpt"]) > 160:
            print("CHECK", slug, c["issues"], len(post["excerpt"]))
            problems += 1
        if kind == "new":
            body = {"title": post["title"], "slug": slug, "status": "future", "date_gmt": d + "T07:30:00",
                    "content": content, "excerpt": post["excerpt"], "categories": [CATS[post["category"]]],
                    "featured_media": MEDIA[mi], "author": 1, "comment_status": "closed",
                    "meta": {"_yoast_wpseo_metadesc": post["excerpt"], "_yoast_wpseo_focuskw": post["kw"]}}
            mi += 1
            (out / f"{d}_{slug}.json").write_text(json.dumps(body, ensure_ascii=False))
            (q4.OUT / f"{slug}.html").write_text(content)
        print(d, kind.ljust(4), slug, c["words"], "words")
    print("problems:", problems)
