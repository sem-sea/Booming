"""REST payloads for the Dutch posts (/nl/blog/<slug>/).

    python3 tools/blog/schedule_nl.py OUT_DIR [--status private|future]

Each Dutch post copies the publish time, featured image and category of its
English original, and carries _bv_lang=nl plus _bv_translation=<EN id> so
the theme builds hreflang pairs and the Dutch URL.

Step 1 (now): --status private, so the posts can be checked while logged in.
Step 2 (launch of the Dutch section): the same batch with --status future.
WordPress publishes a "future" post with a past date right away, so the
posts whose English version is live go live at launch and the rest follow
their English schedule (07:30 UTC, 08:30 Amsterdam).

Writes OUT_DIR/create-<n>.json (POST /batch/v1, at most 25 requests each).
"""
import importlib.util
import json
import pathlib
import sys

HERE = pathlib.Path(__file__).resolve().parent

# English originals as stored on the live site (GET /wp/v2/posts, 24 Nov 2026)
EN = {
    "how-chatgpt-search-picks-sources-b2b": (761, "2026-11-21T07:30:00", 282, 35),
    "brand-mentions-vs-backlinks-ai-visibility": (744, "2026-11-22T07:30:00", 254, 35),
    "ai-visibility-tools-compared-peec-otterly-profound": (762, "2026-11-23T07:30:00", 285, 35),
    "how-to-choose-a-geo-agency": (737, "2026-11-24T07:30:00", 260, 35),
    "get-on-best-of-lists-ai-assistants-cite": (742, "2026-11-25T07:30:00", 251, 35),
    "comparison-pages-ai-assistants-cite": (763, "2026-11-26T07:30:00", 288, 17),
    "fix-wrong-ai-answers-about-your-company": (764, "2026-11-27T07:30:00", 290, 35),
    "content-freshness-ai-search-b2b": (765, "2026-11-28T07:30:00", 291, 35),
    "do-press-releases-get-cited-by-ai": (766, "2026-11-29T07:30:00", 295, 6),
    "earned-media-ai-visibility-b2b": (767, "2026-11-30T07:30:00", 296, 6),
    "eu-ai-act-b2b-marketing-teams": (738, "2026-12-01T07:30:00", 272, 2),
    "ai-written-content-google-spam-policies": (748, "2026-12-02T07:30:00", 280, 2),
    "linkedin-ai-citations-b2b": (768, "2026-12-03T07:30:00", 241, 6),
    "original-research-b2b-lead-magnet-ai-citations": (769, "2026-12-04T07:30:00", 242, 18),
    "claude-ai-search-b2b-citations": (770, "2026-12-05T07:30:00", 243, 35),
    "gemini-ai-search-b2b-citations": (771, "2026-12-06T07:30:00", 244, 35),
    "which-company-pages-ai-assistants-cite": (772, "2026-12-07T07:30:00", 245, 35),
    "ai-crawler-checklist-b2b-website": (739, "2026-12-08T07:30:00", 286, 35),
    "does-llms-txt-help-ai-visibility": (745, "2026-12-09T07:30:00", 261, 35),
    "ai-visibility-cybersecurity-vendors": (743, "2026-12-10T07:30:00", 248, 35),
    "measure-ai-referral-traffic-ga4-b2b": (773, "2026-12-11T07:30:00", 247, 22),
    "b2b-buyers-ai-before-sales": (774, "2026-12-12T07:30:00", 249, 6),
    "schema-markup-b2b-ai-search": (775, "2026-12-13T07:30:00", 250, 35),
    "publish-pricing-ai-search-b2b": (776, "2026-12-14T07:30:00", 252, 17),
    "30-day-ai-visibility-sprint": (740, "2026-12-15T07:30:00", 294, 35),
    "geo-pricing-what-generative-engine-optimization-costs": (741, "2026-12-16T07:30:00", 246, 35),
    "ai-overviews-europe-b2b": (777, "2026-12-17T07:30:00", 253, 35),
    "microsoft-copilot-bing-b2b-ai-visibility": (746, "2026-12-18T07:30:00", 262, 35),
    "write-passages-ai-assistants-quote": (778, "2026-12-19T07:30:00", 255, 35),
    "ai-visibility-metrics-for-the-board": (747, "2026-12-20T07:30:00", 264, 22),
}


def load_posts():
    spec = importlib.util.spec_from_file_location("posts_nl", HERE / "posts_nl.py")
    m = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(m)
    return m


def main(out, status):
    m = load_posts()
    bad = {s: r["issues"] for s, r in ((s, m.check(s, p)) for s, p in m.POSTS.items()) if r["issues"]}
    if bad:
        raise SystemExit(f"posts with issues: {bad}")
    nl_slugs = {p["en"]: s for s, p in m.POSTS.items()}
    reqs = []
    for slug, post in m.POSTS.items():
        en_id, date_gmt, media, cat = EN[post["en"]]
        content = "\n".join(post["blocks"])
        # links to Dutch posts must point at posts that exist in this set
        for target in __import__("re").findall(r'href="/nl/blog/([^/"]+)/"', content):
            assert target in m.POSTS, (slug, target)
        reqs.append({"method": "POST", "path": "/wp/v2/posts", "body": {
            "title": post["title"], "slug": slug, "status": status, "date_gmt": date_gmt,
            "content": content, "excerpt": post["excerpt"], "categories": [cat],
            "featured_media": media, "author": 1, "comment_status": "closed", "ping_status": "closed",
            "meta": {"_bv_lang": "nl", "_bv_translation": en_id,
                     "_yoast_wpseo_metadesc": post["excerpt"], "_yoast_wpseo_focuskw": post["kw"]},
        }})
    out = pathlib.Path(out)
    out.mkdir(parents=True, exist_ok=True)
    for i in range(0, len(reqs), 25):
        (out / f"create-{i // 25 + 1}.json").write_text(
            json.dumps({"validation": "normal", "requests": reqs[i:i + 25]}, ensure_ascii=False), encoding="utf-8")
    missing = [s for s in EN if s not in nl_slugs]
    print(f"{len(reqs)} Dutch posts ready ({status}); English posts without a Dutch version yet: {len(missing)}")


if __name__ == "__main__":
    args = sys.argv[1:]
    status = "private"
    if "--status" in args:
        status = args[args.index("--status") + 1]
        args = [a for a in args if a not in ("--status", status)]
    main(args[0] if args else "build/nl-posts", status)
