"""Build the theme's bundled content seed from a live export.

Inputs
  tools/rebuild/data/site.json       REST export of boomingventure.com (posts, pages, categories, media)
  tools/rebuild/data/pages-raw.json  raw block markup of the live pages (context=edit)
  theme import/booming-venture-content.xml  repo WXR (structural pages + known post markup)
  MEDIA_SRC                          folder with the mirrored /wp-content/uploads files

Outputs
  theme import/content.json          everything the installer needs
  theme import/media/...             uploads, same relative paths as on the live site

Run:  python3 tools/rebuild/build_content.py /path/to/uploads/mirror
"""
from __future__ import annotations

import html
import json
import os
import re
import shutil
import sys
import xml.etree.ElementTree as ET
from collections import Counter, defaultdict

HERE = os.path.dirname(os.path.abspath(__file__))
REPO = os.path.abspath(os.path.join(HERE, "..", ".."))
THEME = os.path.join(REPO, "wp-content", "themes", "booming-venture")
sys.path.insert(0, HERE)
import convert as C  # noqa: E402

MEDIA_SRC = sys.argv[1] if len(sys.argv) > 1 else "/mnt/user-data/uploads/bv-rebuild/media"
SITE = "https://boomingventure.com"

# ---------------------------------------------------------------- categories
CATEGORIES = {
    "ai-search": ("AI Search & GEO",
        "How B2B brands get found and cited in ChatGPT, Perplexity, Claude and Google AI Overviews. "
        "Playbooks for generative engine optimization (GEO), answer engine optimization (AEO) and AI visibility audits."),
    "ai-marketing": ("AI Marketing",
        "Practical AI for marketing teams: agents, content engines, team structure and the tools that pay back. "
        "Real use cases, cost breakdowns and where a human still needs to be in the loop."),
    "b2b-marketing": ("B2B Marketing",
        "Demand generation for B2B SaaS and scale-ups: pipeline, buyer journeys, ABM, sales enablement and the metrics boards ask for."),
    "paid-advertising": ("Paid Advertising",
        "LinkedIn, Meta, Google, Reddit and Perplexity ads compared on cost per lead, pipeline and creative. "
        "Benchmarks and setups for B2B and premium D2C budgets."),
    "lead-generation": ("Lead Generation",
        "Outbound stacks, AI SDRs, data tools and lead magnets compared with real pipeline numbers."),
    "marketing-analytics": ("Marketing Analytics",
        "Attribution, incrementality, marketing mix models, CAC and payback. "
        "How to measure what marketing really drives when last-click no longer works."),
    "conversion-optimization": ("Conversion & Pricing",
        "CRO audits, landing pages, funnels and pricing models that turn more visitors into pipeline and revenue."),
    "email-marketing": ("Email & Lifecycle",
        "Welcome flows, lifecycle automation, retention and the email platforms that support them."),
    "d2c-growth": ("Premium D2C Growth",
        "Channel mix, creative, pricing and scaling playbooks for premium direct-to-consumer brands."),
    "marketing-strategy": ("Marketing Strategy",
        "Positioning, brand, content and growth strategy for teams that want fewer, better bets."),
}

# Old category slug -> new slug, used when no title rule matches and for 301s.
CATEGORY_FALLBACK = {
    "ai-marketing": "ai-marketing", "b2b-marketing": "b2b-marketing", "paid-advertising": "paid-advertising",
    "lead-generation": "lead-generation", "marketing-analytics": "marketing-analytics",
    "conversion-optimization": "conversion-optimization", "email-marketing": "email-marketing",
    "marketing-strategy": "marketing-strategy",
    "funnel-strategy": "conversion-optimization", "pricing-strategy": "conversion-optimization",
    "roi-optimization": "marketing-analytics", "marketing-automation": "email-marketing",
    "customer-retention": "email-marketing", "brand-strategy": "marketing-strategy",
    "content-strategy": "marketing-strategy", "content-marketing": "marketing-strategy",
    "growth-hacking": "marketing-strategy", "creative-strategy": "marketing-strategy",
    "social-media": "marketing-strategy", "influencer-marketing": "paid-advertising",
    "video-marketing": "marketing-strategy", "mobile-marketing": "marketing-strategy",
    "local-seo": "marketing-strategy", "uncategorized": "marketing-strategy",
}

# Ordered title rules. First match wins.
RULES = [
    ("ai-search", r"\b(cited|citations?|(?-i:GEO)\b|generative engine|AEO|answer engine|AI Overviews|AI Mode|LLMs?\b|LLM mentions|ChatGPT Shopping|Google Shopping AI|mentioned by ChatGPT|where B2B buyers search|AI buyers shortlist|Schema Markup|visibility in ChatGPT|buyer research)"),
    ("marketing-analytics", r"\b(attribution|incrementality|last-click|mix model|marketing mix|CLV|CAC|payback|dashboards?|brand lift|ROI measurement)\b"),
    ("paid-advertising", r"\b(Ads\b|Advantage\+|Performance Max|ad fatigue|ad variants|Spark Ads|whitelisting)"),
    ("email-marketing", r"\b(email|SMS|Klaviyo|lifecycle|welcome sequence|marketing automation)\b"),
    ("conversion-optimization", r"\b(CRO|conversion|landing pages?|PDP|demo pages|pricing|free trial|freemium|funnels?)\b"),
    ("d2c-growth", r"\b(D2C|premium brands?|e-?commerce|TikTok Shop|wholesale)\b"),
]


# Editorial overrides where a title keyword points the wrong way.
OVERRIDES = {
    "Dark Social and the Dark Funnel": "b2b-marketing",
    "What \u2018Premium\u2019 Actually Means in D2C": "d2c-growth",
    "Prompt Libraries for Performance Marketers": "ai-marketing",
    "How to build an AI marketing team org chart": "ai-marketing",
}


def pick_category(title: str, old_slug: str) -> str:
    for needle, slug in OVERRIDES.items():
        if needle in title:
            return slug
    for slug, rx in RULES:
        if re.search(rx, title, re.I):
            return slug
    return CATEGORY_FALLBACK.get(old_slug, "marketing-strategy")


# ---------------------------------------------------------------- page SEO
PAGE_SEO = {
    "home": ("B2B Marketing and GEO Agency in Rotterdam | Booming Venture",
             "Booming Venture helps B2B SaaS and scale-ups win pipeline from Google, LinkedIn and AI search like ChatGPT. Strategy and execution from Rotterdam."),
    "about": ("About Booming Venture and founder Ben Verschuur",
              "Booming Venture is a Rotterdam marketing consultancy founded by Ben Verschuur, with 15+ years in performance marketing and hands-on AI adoption work."),
    "services": ("Services: AI Marketing, GEO and Growth | Booming Venture",
                 "Strategic consulting, performance marketing, AI solutions and growth programs for B2B and premium brands. Clear scope, monthly pricing from EUR 2,500."),
    "unify-framework": ("UNIFY Framework: 5-Phase AI Marketing System",
                        "UNIFY is a five-phase framework (Understand, Nurture, Integrate, Forecast, Yield) to run AI in your marketing as one system, with a 30-day plan."),
    "blog": ("AI Marketing, GEO and B2B Growth Insights | Booming Venture",
             "130+ in-depth articles on AI search visibility, GEO, B2B demand generation, paid media and marketing analytics, with sources and benchmarks."),
    "funnel-calculator": ("Funnel Leak Calculator: Find Lost Revenue (Free)",
                          "Enter your traffic and conversion rates to see where your funnel leaks revenue and which stage to fix first. A free tool by Booming Venture."),
    "roi-forecaster": ("Marketing ROI Forecaster: 12-Month Model (Free)",
                       "Model the next 12 months of marketing ROI from your own budget, CAC and conversion numbers, and export the forecast as a PDF."),
    "free-growth-guide": ("Free Growth Strategy Guide (PDF) | Booming Venture",
                          "Download the free growth guide: the frameworks, templates and checklists we use for AI-powered B2B marketing, in one PDF."),
    "boardroom-quickscan": ("Boardroom Quickscan: 15-Minute Growth Diagnostic",
                            "A 15-minute boardroom-grade diagnostic for executives who need clarity on where the next quarter of growth comes from."),
    "head-of-growth": ("Fractional Head of Growth | Booming Venture",
                       "Senior growth leadership on demand: two days a week, quarterly outcomes and knowledge transfer to your own team."),
    "privacy-policy": ("Privacy Policy | Booming Venture",
                       "How Booming Venture collects, uses and protects personal data when you use boomingventure.com or work with us, in line with the GDPR."),
    "cookie-policy": ("Cookie Policy | Booming Venture",
                      "Which cookies and similar technologies boomingventure.com uses, why we use them and how you can change your consent at any time."),
    "terms-of-service": ("Terms of Service | Booming Venture",
                         "The terms that apply to the services of Booming Venture and to the use of boomingventure.com."),
    "disclaimer": ("Disclaimer | Booming Venture",
                   "Disclaimer for the articles, tools and calculators on boomingventure.com. Content is general information, not professional advice."),
}

# Pages whose content carries its own H1 (hero pattern or legal header):
# render them without the template title so each page has exactly one H1.
OWN_H1 = {"services", "unify-framework", "privacy-policy", "cookie-policy"}

STRUCTURAL = ["home", "about", "services", "blog", "unify-framework", "funnel-calculator", "roi-forecaster",
              "free-growth-guide", "boardroom-quickscan", "head-of-growth"]
LEGAL = ["privacy-policy", "cookie-policy", "terms-of-service", "disclaimer"]


def clean_text(s: str) -> str:
    s = re.sub(r"<[^>]+>", " ", s)
    s = html.unescape(s)
    return re.sub(r"\s+", " ", s).strip()


def trim(s: str, limit: int = 155) -> str:
    s = s.strip()
    if len(s) <= limit:
        return s
    cut = s[:limit]
    dot = max(cut.rfind(". "), cut.rfind("? "), cut.rfind("! "))
    if dot >= 80:
        return cut[: dot + 1]
    sp = cut[: limit - 3].rfind(" ")
    return cut[:sp].rstrip(",;:") + "..."


def describe(post: dict) -> str:
    y = (post.get("yoast_head_json") or {}).get("description")
    if y:
        return trim(clean_text(y))
    c = post["content"]["rendered"]
    m = re.search(r"Key takeaways</p>\s*<p[^>]*>(.*?)</p>", c, re.S)
    if m:
        return trim(clean_text(m.group(1)))
    m = re.search(r'<p class="bv-capsule[^"]*"[^>]*>(.*?)</p>', c, re.S)
    if m:
        return trim(clean_text(m.group(1)))
    return trim(clean_text(post["excerpt"]["rendered"]))


def rewrite_links(markup: str, post_slugs: set, stats: Counter) -> str:
    def blog_link(m):
        slug = m.group(2)
        if slug in post_slugs:
            stats["blog_prefix_links"] += 1
            return f'{m.group(1)}/{slug}/'
        return m.group(0)
    markup = re.sub(r'(href=")(?:https?://boomingventure\.com)?/blog/([a-z0-9\-]+)/?', blog_link, markup)
    n = len(re.findall(r'(href|src)="https?://boomingventure\.com/', markup))
    stats["absolute_to_relative"] += n
    markup = re.sub(r'((?:href|src)=")https?://boomingventure\.com/', r"\1/", markup)
    return markup


def main():
    site = json.load(open(os.path.join(HERE, "data", "site.json")))
    pages_raw = json.load(open(os.path.join(HERE, "data", "pages-raw.json")))

    ns = {"wp": "http://wordpress.org/export/1.2/", "content": "http://purl.org/rss/1.0/modules/content/"}
    wxr = ET.parse(os.path.join(THEME, "import", "booming-venture-content.xml")).getroot()
    wx_posts, wx_pages = {}, {}
    for it in wxr.findall(".//item"):
        t = it.find("wp:post_type", ns).text
        slug = it.find("wp:post_name", ns).text
        body = it.find("content:encoded", ns).text or ""
        meta = {m.find("wp:meta_key", ns).text: m.find("wp:meta_value", ns).text for m in it.findall("wp:postmeta", ns)}
        if t == "post":
            wx_posts[slug] = body
        elif t == "page":
            wx_pages[slug] = {"title": it.find("title").text, "content": body, "template": meta.get("_wp_page_template", "")}

    table = C.learn_signatures(list(wx_posts.values()))
    cats_by_id = {c["id"]: c["slug"] for c in site["categories"]}
    media_by_id = {m["id"]: m for m in site["media"]}
    post_slugs = {p["slug"] for p in site["posts"]}

    stats = Counter()
    unknown_all = []
    posts_out = []
    moves = Counter()
    for p in sorted(site["posts"], key=lambda x: x["date"]):
        unknown = []
        blocks = C.to_blocks(p["content"]["rendered"], table, unknown)
        unknown_all += unknown
        blocks = rewrite_links(blocks, post_slugs, stats)
        title = html.unescape(re.sub(r"<[^>]+>", "", p["title"]["rendered"]))
        old = cats_by_id.get(p["categories"][0], "uncategorized") if p["categories"] else "uncategorized"
        new = pick_category(title, old)
        moves[(old, new)] += 1
        fm = media_by_id.get(p["featured_media"]) if p.get("featured_media") else None
        posts_out.append({
            "slug": p["slug"],
            "title": title,
            "content": blocks,
            "date": p["date"].replace("T", " "),
            "date_gmt": p["date_gmt"].replace("T", " "),
            "modified": p["modified"].replace("T", " "),
            "modified_gmt": p["modified_gmt"].replace("T", " "),
            "category": new,
            "meta_description": describe(p),
            "featured": (fm["source_url"].split("/wp-content/uploads/")[1] if fm else ""),
        })

    pages_out = []
    for slug in STRUCTURAL:
        src = wx_pages[slug]
        content = src["content"]
        if slug == "free-growth-guide":
            # The landing template has no title block, so give the page its own H1.
            content = ('<!-- wp:heading {"textAlign":"center","level":1,"fontSize":"5xl","style":{"spacing":{"margin":{"top":"var:preset|spacing|60"}}}} -->'
                       '<h1 class="wp-block-heading has-text-align-center has-5-xl-font-size" style="margin-top:var(--wp--preset--spacing--60)">Free Growth Strategy Guide</h1>'
                       '<!-- /wp:heading -->\n' + content)
        pages_out.append({"slug": slug, "title": src["title"], "content": content, "template": src["template"] or ("page-landing" if slug in OWN_H1 else ""),
                          "structural": True, "seo_title": PAGE_SEO[slug][0], "meta_description": PAGE_SEO[slug][1]})
    titles = {p["slug"]: html.unescape(p["title"]["rendered"]) for p in site["pages"]}
    for slug in LEGAL:
        raw = pages_raw[slug]["raw"]
        raw = rewrite_links(raw, post_slugs, stats)
        pages_out.append({"slug": slug, "title": titles[slug], "content": raw, "template": pages_raw[slug]["template"] or ("page-landing" if slug in OWN_H1 else ""),
                          "structural": False, "seo_title": PAGE_SEO[slug][0], "meta_description": PAGE_SEO[slug][1]})

    # Media: copy every mirrored upload into the theme, keep relative paths.
    dest_root = os.path.join(THEME, "import", "media")
    if os.path.isdir(dest_root):
        shutil.rmtree(dest_root)
    files = []
    for root, _, names in os.walk(MEDIA_SRC):
        for n in names:
            rel = os.path.relpath(os.path.join(root, n), MEDIA_SRC).replace(os.sep, "/")
            files.append(rel)
    files.sort()
    alts = {}
    for m in site["media"]:
        rel = m["source_url"].split("/wp-content/uploads/")[1]
        alts[rel] = {"alt": m.get("alt_text") or "", "title": html.unescape((m.get("title") or {}).get("rendered") or "")}
    names = set(files)
    media_out = []
    for rel in files:
        os.makedirs(os.path.dirname(os.path.join(dest_root, rel)), exist_ok=True)
        shutil.copy2(os.path.join(MEDIA_SRC, rel), os.path.join(dest_root, rel))
        base = re.sub(r"-\d+x\d+(\.[a-z]+)$", r"\1", rel)
        is_variant = base != rel and base in names
        media_out.append({"file": rel, "variant": is_variant, **alts.get(rel, {"alt": "", "title": ""})})
    # Alt text fallback for featured images without one: the post title that uses it.
    for p in posts_out:
        for m in media_out:
            if m["file"] == p["featured"] and not m["alt"]:
                m["alt"] = p["title"]

    redirects = {}
    for old in {c["slug"] for c in site["categories"]}:
        new = CATEGORY_FALLBACK.get(old, "marketing-strategy")
        if old not in CATEGORIES:
            redirects[f"category/{old}"] = f"category/{new}"

    out = {
        "version": "1.9.0",
        "source": SITE,
        "fetched_at": site["fetched_at"],
        "categories": [{"slug": s, "name": n, "description": d} for s, (n, d) in CATEGORIES.items()],
        "category_redirects": redirects,
        "posts": posts_out,
        "pages": pages_out,
        "media": media_out,
    }
    path = os.path.join(THEME, "import", "content.json")
    with open(path, "w", encoding="utf-8") as fh:
        json.dump(out, fh, ensure_ascii=False, separators=(",", ":"))

    cat_count = Counter(p["category"] for p in posts_out)
    print(json.dumps({
        "posts": len(posts_out), "pages": len(pages_out), "media": len(media_out),
        "media_originals": sum(1 for m in media_out if not m["variant"]),
        "unknown_block_signatures": len(unknown_all),
        "link_rewrites": stats, "categories": cat_count,
        "with_featured": sum(1 for p in posts_out if p["featured"]),
        "desc_len_max": max(len(p["meta_description"]) for p in posts_out),
        "desc_len_min": min(len(p["meta_description"]) for p in posts_out),
        "content_json_kb": os.path.getsize(path) // 1024,
        "redirects": len(redirects),
    }, indent=1, default=dict))
    with open(os.path.join(HERE, "data", "category-moves.txt"), "w") as fh:
        for p in posts_out:
            fh.write(f"{p['category']:24} {p['title']}\n")


if __name__ == "__main__":
    main()
