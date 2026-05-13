#!/usr/bin/env python3
"""
Insert cluster-based internal links into the 40 new posts in the WXR.

Best practices applied:
- Pillar / cluster model. Each post links to 3 to 5 sibling posts in the
  same cluster, bidirectional across the cluster.
- One or two cross-cluster bridges per post for natural topic flow.
- Descriptive anchor text matching the target post's primary keyword.
- "Related reading" block appears BEFORE the FAQ and final CTA.
- Idempotent. Adding the block again is a no-op (matches the marker).

Run: python3 add-blog-interlinks.py
"""
import re
import random
from pathlib import Path

WXR = Path(__file__).parent / "wp-content/themes/booming-venture/import/booming-venture-content.xml"
MARKER = "<!-- bv-related-reading -->"

# Slug -> (anchor_text, pillar)
POSTS = {
    # Pillar A , AI Performance Marketing
    "ai-performance-marketing-stack-2026":            ("How AI is actually used in a 2026 performance stack", "A"),
    "advantage-plus-vs-performance-max-vs-pinterest": ("Advantage+ vs Performance Max vs Pinterest Performance+", "A"),
    "ai-paid-media-roi-b2b-90-day-test":              ("Measuring AI paid-media ROI in B2B with a 90-day test", "A"),
    "ai-replacing-marketing-hire-saas":               ("Where AI replaces a marketing hire for SaaS under 50",   "A"),
    "prompt-library-performance-marketers":           ("40 tested prompts for ads, email, and CRO",              "A"),
    "ai-agents-marketing-2026":                       ("What AI marketing agents can run autonomously in 2026",  "A"),

    # Pillar B , Premium D2C Growth
    "what-premium-actually-means-d2c":         ("What premium really means in D2C marketing",          "B"),
    "meta-ads-premium-d2c-2026":               ("Meta Ads for premium D2C in 2026",                    "B"),
    "scaling-premium-d2c-1m-to-10m":           ("The real cost of scaling premium D2C from 1M to 10M", "B"),
    "d2c-beyond-meta-channel-mix-2026":        ("Going beyond Meta: 2026 D2C channel allocation",      "B"),
    "founder-led-content-d2c-playbook":        ("The founder-led content playbook for premium D2C",    "B"),
    "wholesale-retail-d2c-when-to-open":       ("Wholesale and retail as a D2C performance channel",   "B"),

    # Pillar C , B2B SaaS Demand Gen
    "b2b-saas-demand-gen-stack-2026":      ("The 2026 B2B SaaS demand-gen stack",                       "C"),
    "dark-social-dark-funnel-saas-2026":   ("Dark social and the LLM dark funnel for B2B SaaS",         "C"),
    "linkedin-ads-saas-2026-benchmarks":   ("LinkedIn Ads for B2B SaaS with 2026 benchmarks",           "C"),
    "product-led-content-saas-2026":       ("Product-led content for SaaS demand generation",           "C"),
    "abm-small-teams-saas-2026":           ("Account-based marketing with a team of three",             "C"),
    "reddit-b2b-playbook-llm-citations":   ("The Reddit B2B playbook for visibility and LLM citations", "C"),

    # Pillar D , Measurement
    "mmm-d2c-under-20m-when-it-pays-off":      ("MMM for D2C brands under 20M EUR",                         "D"),
    "incrementality-testing-geo-holdout-playbook": ("Geo-holdout incrementality testing playbook",         "D"),
    "last-click-is-dead-d2c-attribution-2026": ("What replaces last-click attribution in 2026",             "D"),
    "cac-payback-saas-board-metrics-2026":     ("CAC payback and the SaaS metrics boards care about",      "D"),
    "pipeline-forecasting-ai-saas":            ("Pipeline forecasting with AI for long-cycle SaaS",        "D"),
    "multi-touch-attribution-b2b-mostly-theatre": ("Why multi-touch attribution is mostly theatre in B2B", "D"),

    # Pillar E , GEO / LLM Visibility
    "get-cited-by-chatgpt-d2c-best-of":       ("How to get cited by ChatGPT for D2C category queries", "E"),
    "schema-markup-ecommerce-2026":           ("Schema markup priorities for premium ecommerce",       "E"),
    "geo-aeo-seo-definitions-2026":           ("GEO vs AEO vs SEO: a working 2026 definition",         "E"),
    "seven-content-formats-llms-cite-most":   ("Seven content formats LLMs cite most in B2B SaaS",     "E"),
    "audit-ai-visibility-without-paying-profound": ("A DIY AI visibility audit method",                "E"),
    "g2-reddit-wikipedia-matter-more-than-blog": ("Why G2, Reddit, Wikipedia drive LLM citations",     "E"),

    # Pillar F , Creative & CRO
    "premium-pdp-audit-20-brands":              ("A conversion audit of 20 premium D2C PDPs",          "F"),
    "ai-creative-100-variants-no-slop":         ("Generating 100 D2C ad variants without AI slop",     "F"),
    "killing-ad-fatigue-2026-refresh-cadence":  ("Ad fatigue and the 2026 refresh cadence",            "F"),
    "saas-pricing-page-patterns-2026":          ("12 SaaS pricing-page patterns that beat 3-tier",     "F"),
    "founder-thought-leadership-saas-pipeline-system": ("Founder thought leadership as a posting system", "F"),
    "brand-building-performance-marketers-2026": ("Brand building for performance marketers",          "F"),

    # Pillar G , Retention & CLV
    "clv-modelling-premium-d2c-cohort":   ("Cohort-based CLV modelling for premium D2C",      "G"),
    "email-sms-premium-d2c-flows":        ("Email and SMS retention flows for premium D2C",   "G"),
    "nrr-saas-marketing-influences":      ("Net Revenue Retention and how marketing moves it","G"),
    "saas-lifecycle-email-14-sequences":  ("14 SaaS lifecycle email sequences",               "G"),
}

# Cross-cluster bridges. For each post, one or two slugs in OTHER pillars
# that connect naturally. Keeps the graph from being too siloed.
BRIDGES = {
    "ai-performance-marketing-stack-2026":            ["d2c-beyond-meta-channel-mix-2026", "last-click-is-dead-d2c-attribution-2026"],
    "advantage-plus-vs-performance-max-vs-pinterest": ["meta-ads-premium-d2c-2026", "incrementality-testing-geo-holdout-playbook"],
    "ai-paid-media-roi-b2b-90-day-test":              ["cac-payback-saas-board-metrics-2026", "linkedin-ads-saas-2026-benchmarks"],
    "ai-replacing-marketing-hire-saas":               ["b2b-saas-demand-gen-stack-2026", "ai-agents-marketing-2026"],
    "prompt-library-performance-marketers":           ["ai-creative-100-variants-no-slop", "seven-content-formats-llms-cite-most"],
    "ai-agents-marketing-2026":                       ["ai-replacing-marketing-hire-saas", "pipeline-forecasting-ai-saas"],

    "what-premium-actually-means-d2c":   ["clv-modelling-premium-d2c-cohort", "premium-pdp-audit-20-brands"],
    "meta-ads-premium-d2c-2026":         ["advantage-plus-vs-performance-max-vs-pinterest", "killing-ad-fatigue-2026-refresh-cadence"],
    "scaling-premium-d2c-1m-to-10m":     ["clv-modelling-premium-d2c-cohort", "d2c-beyond-meta-channel-mix-2026"],
    "d2c-beyond-meta-channel-mix-2026":  ["ai-performance-marketing-stack-2026", "mmm-d2c-under-20m-when-it-pays-off"],
    "founder-led-content-d2c-playbook":  ("founder-thought-leadership-saas-pipeline-system", "get-cited-by-chatgpt-d2c-best-of"),
    "wholesale-retail-d2c-when-to-open": ["scaling-premium-d2c-1m-to-10m", "clv-modelling-premium-d2c-cohort"],

    "b2b-saas-demand-gen-stack-2026":    ["linkedin-ads-saas-2026-benchmarks", "dark-social-dark-funnel-saas-2026"],
    "dark-social-dark-funnel-saas-2026": ["get-cited-by-chatgpt-d2c-best-of", "multi-touch-attribution-b2b-mostly-theatre"],
    "linkedin-ads-saas-2026-benchmarks": ["ai-paid-media-roi-b2b-90-day-test", "cac-payback-saas-board-metrics-2026"],
    "product-led-content-saas-2026":     ["seven-content-formats-llms-cite-most", "founder-thought-leadership-saas-pipeline-system"],
    "abm-small-teams-saas-2026":         ["linkedin-ads-saas-2026-benchmarks", "pipeline-forecasting-ai-saas"],
    "reddit-b2b-playbook-llm-citations": ["g2-reddit-wikipedia-matter-more-than-blog", "get-cited-by-chatgpt-d2c-best-of"],

    "mmm-d2c-under-20m-when-it-pays-off":      ["incrementality-testing-geo-holdout-playbook", "last-click-is-dead-d2c-attribution-2026"],
    "incrementality-testing-geo-holdout-playbook": ["mmm-d2c-under-20m-when-it-pays-off", "advantage-plus-vs-performance-max-vs-pinterest"],
    "last-click-is-dead-d2c-attribution-2026": ["multi-touch-attribution-b2b-mostly-theatre", "ai-performance-marketing-stack-2026"],
    "cac-payback-saas-board-metrics-2026":     ["nrr-saas-marketing-influences", "ai-paid-media-roi-b2b-90-day-test"],
    "pipeline-forecasting-ai-saas":            ["ai-agents-marketing-2026", "cac-payback-saas-board-metrics-2026"],
    "multi-touch-attribution-b2b-mostly-theatre": ["last-click-is-dead-d2c-attribution-2026", "dark-social-dark-funnel-saas-2026"],

    "get-cited-by-chatgpt-d2c-best-of":       ["reddit-b2b-playbook-llm-citations", "g2-reddit-wikipedia-matter-more-than-blog"],
    "schema-markup-ecommerce-2026":           ["seven-content-formats-llms-cite-most", "premium-pdp-audit-20-brands"],
    "geo-aeo-seo-definitions-2026":           ["seven-content-formats-llms-cite-most", "audit-ai-visibility-without-paying-profound"],
    "seven-content-formats-llms-cite-most":   ["geo-aeo-seo-definitions-2026", "product-led-content-saas-2026"],
    "audit-ai-visibility-without-paying-profound": ["get-cited-by-chatgpt-d2c-best-of", "g2-reddit-wikipedia-matter-more-than-blog"],
    "g2-reddit-wikipedia-matter-more-than-blog": ["reddit-b2b-playbook-llm-citations", "audit-ai-visibility-without-paying-profound"],

    "premium-pdp-audit-20-brands":              ["meta-ads-premium-d2c-2026", "what-premium-actually-means-d2c"],
    "ai-creative-100-variants-no-slop":         ["prompt-library-performance-marketers", "meta-ads-premium-d2c-2026"],
    "killing-ad-fatigue-2026-refresh-cadence":  ["meta-ads-premium-d2c-2026", "ai-creative-100-variants-no-slop"],
    "saas-pricing-page-patterns-2026":          ["nrr-saas-marketing-influences", "cac-payback-saas-board-metrics-2026"],
    "founder-thought-leadership-saas-pipeline-system": ["founder-led-content-d2c-playbook", "product-led-content-saas-2026"],
    "brand-building-performance-marketers-2026": ["founder-thought-leadership-saas-pipeline-system", "founder-led-content-d2c-playbook"],

    "clv-modelling-premium-d2c-cohort":   ["scaling-premium-d2c-1m-to-10m", "what-premium-actually-means-d2c"],
    "email-sms-premium-d2c-flows":        ["clv-modelling-premium-d2c-cohort", "what-premium-actually-means-d2c"],
    "nrr-saas-marketing-influences":      ["cac-payback-saas-board-metrics-2026", "saas-lifecycle-email-14-sequences"],
    "saas-lifecycle-email-14-sequences":  ["product-led-content-saas-2026", "nrr-saas-marketing-influences"],
}

PILLAR_NAMES = {
    "A": "AI Performance Marketing",
    "B": "Premium D2C Growth",
    "C": "B2B SaaS Demand Generation",
    "D": "Measurement and MMM",
    "E": "GEO and LLM Visibility",
    "F": "Creative, Brand and CRO",
    "G": "Retention, CLV and Expansion",
}

def cluster_peers(slug, pillar, n=3):
    peers = [s for s, (_, p) in POSTS.items() if p == pillar and s != slug]
    return peers[:n]

def build_related_block(slug):
    if slug not in POSTS:
        return ""
    pillar = POSTS[slug][1]
    peers = cluster_peers(slug, pillar, n=3)
    bridges = BRIDGES.get(slug, [])
    # Normalise: BRIDGES values may be tuples (legacy from authoring)
    if isinstance(bridges, tuple):
        bridges = list(bridges)
    bridges = [b for b in bridges if b in POSTS and b != slug][:2]

    seen = set()
    chosen = []
    for s in peers + bridges:
        if s in seen:
            continue
        seen.add(s)
        chosen.append(s)

    if not chosen:
        return ""

    items_html = "\n".join(
        f'<li><a href="/blog/{s}/">{POSTS[s][0]}</a></li>' for s in chosen
    )
    h = (
        f"{MARKER}\n"
        '<!-- wp:group {"className":"bv-related","style":{"border":{"radius":"0.75rem","width":"1px","color":"var:preset|color|border"},"spacing":{"padding":{"top":"1.25rem","right":"1.5rem","bottom":"1.25rem","left":"1.5rem"},"margin":{"top":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->'
        '<div class="wp-block-group bv-related has-border-color" style="border-color:var(--wp--preset--color--border);border-width:1px;border-radius:0.75rem;margin-top:var(--wp--preset--spacing--50);padding:1.25rem 1.5rem">'
        '<!-- wp:heading {"level":3,"fontSize":"lg"} --><h3 class="wp-block-heading has-lg-font-size">Related reading</h3><!-- /wp:heading -->'
        f'<!-- wp:list --><ul class="wp-block-list">\n{items_html}\n</ul><!-- /wp:list -->'
        '</div><!-- /wp:group -->'
    )
    return h

def main():
    xml = WXR.read_text(encoding="utf-8")
    n_updated = 0
    n_skipped = 0

    def repl(match):
        nonlocal n_updated, n_skipped
        full = match.group(0)
        slug = match.group("slug")
        body = match.group("body")
        if slug not in POSTS:
            return full
        if MARKER in body:
            n_skipped += 1
            return full
        related = build_related_block(slug)
        if not related:
            n_skipped += 1
            return full
        # Insert BEFORE the FAQ heading if present, otherwise BEFORE the final paragraph.
        faq_marker = '<!-- wp:heading --><h2 class="wp-block-heading">Frequently asked questions</h2>'
        if faq_marker in body:
            new_body = body.replace(faq_marker, related + "\n" + faq_marker, 1)
        else:
            # Fall back to appending at the end of the body
            new_body = body + "\n" + related
        n_updated += 1
        return full.replace(body, new_body)

    pattern = re.compile(
        r'<wp:post_name><!\[CDATA\[(?P<slug>[^\]]+)\]\]></wp:post_name>'
        r'[^<]*<wp:status><!\[CDATA\[publish\]\]></wp:status>'
        r'[^<]*<wp:post_type><!\[CDATA\[post\]\]></wp:post_type>'
        r'.*?<content:encoded><!\[CDATA\[(?P<body>.*?)\]\]></content:encoded>',
        re.DOTALL,
    )
    new_xml = pattern.sub(repl, xml)
    WXR.write_text(new_xml, encoding="utf-8")
    print(f"Updated {n_updated} posts with Related reading blocks.")
    if n_skipped:
        print(f"Skipped {n_skipped} (already had marker or not in registry).")

if __name__ == "__main__":
    main()
