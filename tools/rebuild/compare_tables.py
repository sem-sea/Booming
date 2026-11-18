"""Comparison tables for the "vs" articles (theme 1.10.0).

Every cell restates a figure or claim that is already in the article
itself, so no new facts enter the site. Output: import/compare-tables.json
(slug -> block HTML), used by the installer and by build step below.
"""
import html, json, pathlib

T = {
"ai-vs-human-landing-pages": ("AI vs human landing pages at a glance", ["Compared on", "Pure AI", "Pure human", "Hybrid (AI draft, human edit)"], [
  ["Average conversion, 20 paired tests", "2.4%", "2.6%", "2.75%"],
  ["Versus human-only", "8% relative loss", "Baseline", "6% relative lift"],
  ["Typical copy", "Generic claims, unnamed social proof", "Slower, fewer variants", "Specific claims and named proof after a 90-minute edit"],
  ["Best for", "Pages under $5K monthly spend, high-volume variant tests", "Control version in A/B tests", "High-spend pages; also earns AI citations"]]),
"advantage-plus-vs-performance-max-vs-pinterest": ("Advantage+ vs Performance Max vs Pinterest Performance+ at a glance", ["Compared on", "Meta Advantage+", "Google Performance Max", "Pinterest Performance+"], [
  ["What it is", "Meta's AI shopping campaign", "Google's cross-network goal campaign", "Pinterest's automated retail campaign"],
  ["Wins on", "Bottom-funnel retargeting and prospecting at volume", "Branded and high-intent shopping search", "Discovery for home, fashion and beauty"],
  ["Creative it rewards", "Reels video, static at 1:1 and 4:5", "Strong product imagery plus video", "Vertical lifestyle imagery, rich pins"],
  ["Typical share, premium D2C at 5M EUR+", "45 to 55%", "30 to 40%", "5 to 12%"],
  ["Main risk", "Cannibalises other campaigns without exclusions", "Eats branded traffic without exclusions", "Cannibalises other campaigns without guardrails"]]),
"chatgpt-vs-perplexity-vs-google-ai-where-b2b-buyers-search": ("ChatGPT vs Perplexity vs Google AI Overviews at a glance", ["Compared on", "ChatGPT", "Perplexity", "Google AI Overviews"], [
  ["Citation behavior", "Inconsistent; footnotes when browsing", "Inline citations on every answer", "Selective, from indexed pages"],
  ["Where answers come from", "Bing-indexed pages when browsing, otherwise training data", "Live web retrieval on every query", "Google index; authority, structured data and freshness matter"],
  ["Strongest for", "Broadest awareness", "Research-heavy buyers in finance, legal, procurement", "Largest reach"]]),
"hubspot-vs-customer-io-vs-klaviyo-lifecycle-stack": ("HubSpot vs Klaviyo vs Customer.io at a glance", ["Compared on", "HubSpot", "Klaviyo", "Customer.io"], [
  ["Built for", "B2B teams where sales and marketing share a CRM", "E-commerce and DTC brands", "Product-led SaaS and mobile apps"],
  ["Data model", "CRM-first contact timeline", "Order and product data with predictive CLV and churn", "Real-time product event streams"],
  ["Entry price", "Marketing Hub Starter from $20/month; Professional $890/month for 2,000 contacts", "Free to 500 contacts, $20/month to 1,000", "Essentials from $100/month for 5,000 profiles"],
  ["Where it breaks", "Cost per contact: Professional over $3,200/month at 100,000", "Use cases outside commerce data", "Needs developers for setup and event management"]]),
"ai-sdr-vs-human-sdr-pipeline-numbers": ("AI SDR vs human SDR at a glance", ["Compared on", "AI SDR", "Human SDR", "Hybrid"], [
  ["Reply rate", "0.5 to 2% in operator tests", "6 to 10% positive on a tight ICP", "Closes most of the gap"],
  ["Cost per qualified meeting", "$600 to $900", "$350 to $550", "$250 to $400"],
  ["Cost basis", "11x about $30,000/year per seat; Artisan $1,500 to $3,000/month", "About $95,000 fully loaded, 12 to 18 meetings/month", "AI for research and drafts, human for send and call"],
  ["Best for", "Low ACV, transactional, high volume", "Deals above $25,000 ACV, objections, multi-threading", "Almost every team"]]),
"free-trial-vs-freemium-vs-reverse-trial": ("Free trial vs freemium vs reverse trial at a glance", ["Compared on", "Free trial", "Freemium", "Reverse trial"], [
  ["How it works", "Full product for a fixed window, then pay or lose access", "Free tier with paid upgrades", "Premium for 14 to 30 days, then a free tier"],
  ["Benchmark (OpenView)", "Opt-in about 25%, card required about 60%", "2 to 5% free to paid", "30 to 80% more net new ARR in mid-market SaaS"],
  ["Best fit", "ACV $500 to $5k, single-player utility products", "ACV under $500, viral or collaborative products", "ACV $5k to $50k, multi-feature SaaS"],
  ["Fails when", "Time to value is longer than the trial", "Free tier is too generous or costly to serve", "Activation is slow or the free tier is too thin"]]),
"influencer-whitelisting-vs-spark-ads-vs-ugc": ("Whitelisting vs Spark Ads vs UGC at a glance", ["Compared on", "Influencer whitelisting", "Spark Ads", "UGC"], [
  ["What it is", "Ads from the creator's own handle on Meta or TikTok", "Boosted organic creator posts on TikTok", "Brand-owned creator assets from brand accounts"],
  ["CPM (client benchmarks)", "$18 to $35 on Meta, CTR 1.8 to 3.2%", "$6 to $12 on TikTok", "$12 to $22 on Meta, CTR 1.2 to 2.4%"],
  ["Cost note", "Thousands per partnership", "Lowest TikTok CPMs through inherited engagement", "$150 to $500 per video"],
  ["Best use", "Cold prospecting with credibility transfer", "TikTok-first brands", "Retargeting, creative testing, volume"],
  ["Suggested share of spend", "30%", "20%", "50%"]]),
"revops-vs-growth-team-who-owns-pipeline": ("RevOps vs Growth team at a glance", ["Compared on", "RevOps", "Growth team"], [
  ["Owns", "The revenue system: data, tooling, process, forecasting, compensation", "Experiments that move pipeline: paid, lifecycle, CRO, product-led loops"],
  ["Reports on", "Pipeline health", "Pipeline created"],
  ["Typical systems", "CRM configuration, enrichment rules, call analysis, AI agent platform", "Ad accounts, landing page tests, lifecycle flows, content"],
  ["AI agents", "Owns the platform layer", "Owns the playbook layer"]]),
"clay-vs-apollo-vs-smartlead-b2b-data-stack": ("Clay vs Apollo vs Smartlead at a glance", ["Compared on", "Apollo", "Clay", "Smartlead"], [
  ["Role", "Contact database with sequencer and dialer", "Enrichment and workflow layer across 100+ providers", "Cold email sending at volume"],
  ["Starting price (2025)", "$59 per user/month, $99 Professional", "$149/month Starter, credit based", "$39/month Basic, $94 Pro"],
  ["Strength", "Cheapest entry: contacts plus basic sending", "Waterfall enrichment and AI personalization", "Unlimited mailboxes, warmup, inbox rotation"],
  ["Weak spot", "Data freshness on niche and European contacts", "Credits burn on unfocused enrichment", "Deliverability drops without DMARC, SPF and warmup"]]),
"geo-vs-seo-vs-aeo-b2b-budget-2026": ("GEO vs SEO vs AEO at a glance", ["Compared on", "SEO", "GEO", "AEO"], [
  ["What it is", "Optimization for ranked blue links", "Earning citations inside AI answers", "Structuring content so machines extract a direct answer"],
  ["What the budget pays for", "The ranking floor AI engines still pull from", "Citations in ChatGPT, Perplexity and Gemini answers", "Answer capsules, schema and structured passages"],
  ["Suggested 2026 share", "40 to 50%", "25 to 35%", "20 to 25%"]]),
"clay-vs-instantly-vs-lemlist-cold-outbound-stack": ("Clay vs Instantly vs Lemlist at a glance", ["Compared on", "Clay", "Instantly", "Lemlist"], [
  ["Role", "Data enrichment and research, 100+ providers", "High-volume cold email sending", "Multichannel sequences: email, LinkedIn, calls"],
  ["Starting price", "$149/month Starter, 2,000 credits", "$37/month Growth", "$69 per user/month Email Starter"],
  ["Higher tiers", "$349 Explorer, $800 Pro", "$97 Hypergrowth, $358 Light Speed", "$99 Email Pro, $159 Multichannel Expert"],
  ["Deliverability", "Does not send; depends on your sender", "Strongest: inbox rotation and warmup network", "Solid, through Lemwarm"],
  ["Pick it if", "Your list quality is poor", "You cannot send reliably", "Replies are low and personalization is the fix"]]),
"tiktok-shop-vs-meta-shops-vs-shopify-premium-d2c": ("TikTok Shop vs Meta Shops vs Shopify at a glance", ["Compared on", "TikTok Shop", "Meta Shops", "Shopify"], [
  ["Role", "Discovery and impulse buys", "Retargeting and Advantage+ catalog ads", "Owned storefront with your customer data"],
  ["Typical order value", "$30 to $40 in beauty and apparel", "Sends shoppers to your own checkout", "About $100; premium brands $150 to $250"],
  ["Fees", "About 8% commission plus creator payouts", "No in-app checkout in the US since 2023", "2.9% plus 30 cents per transaction"],
  ["Suggested role in paid budget", "20% with TikTok ads, sampler SKUs", "60%, main acquisition engine", "Checkout for all paid traffic"]]),
"notion-vs-coda-vs-airtable-marketing-ops": ("Notion vs Coda vs Airtable at a glance", ["Compared on", "Notion", "Coda", "Airtable"], [
  ["What it is", "Docs and wiki with light databases", "Docs with tables, formulas and buttons", "Relational database with spreadsheet UX"],
  ["25-person team per year", "About $7,200 ($24 per seat, AI included)", "About $2,160 with 5 Doc Makers ($36 each)", "About $16,200 ($54 per seat)"],
  ["Best for", "Content briefs, SOPs, team wiki", "Live ops dashboards, weekly reviews", "Campaign trackers, asset libraries, influencer CRMs"],
  ["Fails on", "Relational integrity and large filtered views", "Large datasets and the formula learning curve", "Writing, and cost past 25 seats"],
  ["Choose it if your team ships", "Words", "Weekly decisions and dashboards", "Records"]]),
"klaviyo-vs-sendlane-vs-omnisend-premium-d2c": ("Klaviyo vs Sendlane vs Omnisend at a glance", ["Compared on", "Klaviyo", "Sendlane", "Omnisend"], [
  ["Strength", "Data and predictive analytics", "Deliverability and behavioral automation", "Lower tiered pricing, pre-built workflows"],
  ["About 50,000 profiles", "About $360/month", "Custom, from $100/month at small volumes", "About $230/month"],
  ["About 150,000 profiles", "About $720/month", "Custom, usually between the other two", "About $330/month"],
  ["Best fit", "AOV $80 to $250 with deep Shopify and Meta needs", "AOV above $250, subscriptions, lists over 200,000", "AOV under $80, under 75,000 profiles"],
  ["G2 rating (2026)", "4.6", "4.7", "4.5"]]),
"tiktok-shop-vs-amazon-vs-shopify-premium-d2c": ("TikTok Shop vs Amazon vs Shopify at a glance", ["Compared on", "TikTok Shop", "Amazon", "Shopify"], [
  ["Trade-off", "Discovery at the cost of price perception", "Demand capture at the cost of customer data", "Keeps data and pricing; you pay for traffic"],
  ["Take rate", "About 8% referral fee since April 2024", "8 to 15% referral fee plus FBA", "Plan fee plus 2.9% and 30 cents per payment"],
  ["Scale in 2024", "Over $100M US sales on Black Friday", "37.6% of US ecommerce sales", "$292.3B GMV"],
  ["Role in a premium catalog", "Limited drops, bundles, gifted product", "One or two trial SKUs for branded search", "Full catalog and price anchor"]]),
"ai-seo-agents-vs-human-seo-teams-cost-breakdown": ("AI SEO agents vs human SEO teams at a glance", ["Compared on", "AI SEO agents", "Human SEO team", "Hybrid"], [
  ["Monthly cost", "$99 to $2,000", "$3,209 average retainer; $5,000 to $10,000 mid-market", "40 to 60% below a pure human retainer"],
  ["Strength", "Clustering, briefs, drafts and crawls at speed", "Strategy, expertise, editing, outreach", "Three people match a five-person team's output"],
  ["Weak spot", "Original research, expert quotes, brand voice", "Highest cost per page", "Needs a senior editor"],
  ["Best for", "High-volume, low-stakes pages", "Thought leadership, YMYL, technical docs", "Most programs; best ROI"]]),
"perplexity-ads-vs-linkedin-ads-b2b-pipeline": ("Perplexity Ads vs LinkedIn Ads at a glance", ["Compared on", "Perplexity Ads", "LinkedIn Ads"], [
  ["Format", "Sponsored follow-up questions inside AI answers", "Feed, inbox and audience network placements"],
  ["CPM", "From about $50", "About $33.80 average, CPC about $5.58"],
  ["You buy", "The question being researched", "The person: title, company, seniority"],
  ["Early CPL, mid-market SaaS", "$80 to $200", "$100 to $400"],
  ["Suggested 2026 share", "20%", "70% (plus 10% testing across both)"],
  ["Fails at", "Scale: about 780M queries a month", "Cost efficiency below $5,000 ACV"]]),
"meddic-vs-command-of-the-message-ai-sales-enablement": ("MEDDIC vs Command of the Message at a glance", ["Compared on", "MEDDIC", "Command of the Message"], [
  ["Type", "Qualification framework with six criteria", "Value-messaging methodology"],
  ["Question it answers", "Does this deal deserve resources?", "Does the buyer understand why you win?"],
  ["Origin", "PTC in the 1990s, later MEDDPICC", "Force Management"],
  ["Where it lives", "CRM stage gates and forecasting", "Case studies, landing pages, battle cards, calls"],
  ["Rollout order", "Second, as the qualification lens", "First, so reps can hold a value conversation"]]),
"claude-vs-chatgpt-vs-gemini-b2b-buyer-research": ("Claude vs ChatGPT vs Gemini at a glance", ["Compared on", "Claude", "ChatGPT", "Gemini"], [
  ["Main source stack", "Brave Search plus its trained corpus", "Bing plus publisher partnerships", "Live Google Search grounding"],
  ["Buying stage", "Enterprise research, RFPs, security questionnaires", "Early discovery and shortlists", "Late-stage validation, pricing, reviews"],
  ["What it rewards", "Depth and structure", "Community mentions and PR", "Classic SEO and fresh reviews"]]),
"reddit-ads-vs-linkedin-ads-saas": ("Reddit Ads vs LinkedIn Ads at a glance", ["Compared on", "Reddit Ads", "LinkedIn Ads"], [
  ["CPM, B2B", "$2 to $8", "$30 to $80 for senior titles"],
  ["Cost per lead", "$20 to $80", "$75 to $200 in software"],
  ["Targeting", "Subreddit, interest, conversation keywords", "Job title, company, seniority, industry"],
  ["Pipeline", "Earlier funnel, practitioners, more volume", "Higher intent, decision makers, slower cycles"],
  ["Fails at", "Precision for niche enterprise ICPs", "Cost efficiency under about $15K ACV"],
  ["Split at $10K to $50K ACV", "40%", "60%"]]),
}

def block(heading, cols, rows):
    e = lambda s: html.escape(s, quote=False)
    head = "".join(f"<th>{e(c)}</th>" for c in cols)
    body = "".join("<tr>" + "".join((f"<td><strong>{e(c)}</strong></td>" if i == 0 else f"<td>{e(c)}</td>") for i, c in enumerate(r)) + "</tr>" for r in rows)
    return (
        f'<!-- wp:heading -->\n<h2 class="wp-block-heading">{e(heading)}</h2>\n<!-- /wp:heading -->\n\n'
        f'<!-- wp:table {{"className":"bv-compare"}} -->\n'
        f'<figure class="wp-block-table bv-compare"><table><thead><tr>{head}</tr></thead><tbody>{body}</tbody></table>'
        f'<figcaption class="wp-element-caption">Summary of the figures in this article. Sources are linked in the sections below.</figcaption></figure>\n'
        f'<!-- /wp:table -->\n\n'
    )

if __name__ == "__main__":
    root = pathlib.Path(__file__).resolve().parents[2] / "wp-content/themes/booming-venture/import"
    out = {slug: block(*spec) for slug, spec in T.items()}
    for slug, (h, cols, rows) in T.items():
        assert all(len(r) == len(cols) for r in rows), slug
        assert "—" not in json.dumps(T[slug]) and "–" not in json.dumps(T[slug]), slug
    (root / "compare-tables.json").write_text(json.dumps(out, ensure_ascii=False, indent=1))
    print(len(out), "tables written")
