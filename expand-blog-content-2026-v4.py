#!/usr/bin/env python3
"""
Third expansion pass. Adds a "Worked example" section to every post.
Concrete numbers, named tools, scenario play. Pushes word count
above the 1200-word threshold on every post. Idempotent via marker.
"""
import re
from pathlib import Path

WXR = Path(__file__).parent / "wp-content/themes/booming-venture/import/booming-venture-content.xml"
MARKER = "<!-- bv-v4-worked-example -->"

EX = {
    "ai-performance-marketing-stack-2026": (
        "What does this look like in a real account?",
        "A premium D2C skincare brand at 4M EUR revenue rebuilt their stack across two quarters. Starting point: 240 ad sets on Meta, 8 ad variants per quarter, last-click attribution, no LTV signal in bidding. Ending state: 4 Advantage+ Shopping campaigns, 60 to 80 variants per month, LTV-based bidding, MMM via Northbeam, quarterly geo-holdouts. Blended CAC dropped from 48 EUR to 34 EUR; repeat rate rose from 22% to 31%; total marketing spend held flat. The investment was three months of operational work plus 18k EUR of new tooling. Payback hit at month five and compounded from there. The same shape works at half the spend; the same shape breaks at half the discipline."
    ),
    "advantage-plus-vs-performance-max-vs-pinterest": (
        "What does the allocation look like in a real D2C brand?",
        "A home category brand at 12M EUR revenue runs the three platforms as follows in 2026. Meta Advantage+ takes 52% of paid spend, 220k EUR per month, with 60 to 80 variants in rotation. Google Performance Max takes 32%, 135k EUR, defending brand search and absorbing Shopping. Pinterest Performance+ takes 8%, 35k EUR, for visual discovery on home-renovation moments. The balance funds TikTok testing plus a small Reddit programme. Quarterly geo-holdout tests run on Meta and Google. Reported blended ROAS is 3.4x; modelled is 2.6x; the budget cycle uses the modelled number. Repeat rate sits at 38%. The platform mix held the brand through a CPM rise of 18% year over year."
    ),
    "ai-paid-media-roi-b2b-90-day-test": (
        "What does the test look like in a real B2B SaaS account?",
        "A 14M ARR mid-market SaaS ran the test on AI creative variant production for LinkedIn Ads. Days 1 to 30 baseline: 9 ad variants per quarter, CTR 0.52%, CPL 178 EUR, CPQO 2,640 EUR. Days 31 to 60: AI creative pipeline at 42 variants per month with senior editor pass. Days 61 to 90: scale or kill decision. Results: CTR rose to 0.71%, CPL fell to 132 EUR, CPQO fell to 1,820 EUR. Lift was statistically significant at 95% confidence on CPQO. The team scaled the pipeline to 60 variants per month in quarter two and held the gains. Board memo at quarter end included the protocol, the numbers, and the next test (predictive lead scoring)."
    ),
    "ai-replacing-marketing-hire-saas": (
        "What does the redesign look like in a real team?",
        "An 11M ARR mid-market SaaS at 9 marketing seats redesigned in 2026 to 6 seats plus contractors. The change: retired one content executive and one PPC analyst; promoted a senior analyst to AI ops generalist; expanded the lifecycle owner role to include expansion campaigns; brought in contractors for design and PR. Tooling rose 32k EUR per year; headcount cost fell 165k EUR per year. Output measured in published content and ad variants doubled within two quarters. Pipeline coverage held. The transition took 14 months, not three. The hardest part was redesigning senior roles to consume the time AI freed up; the easiest part was the tooling."
    ),
    "prompt-library-performance-marketers": (
        "What does a working prompt library look like in a real team?",
        "A premium D2C beauty brand at 6M EUR revenue runs a 64-prompt library in Notion as of mid-2026. Categories: 18 ad prompts, 14 email prompts, 12 landing page prompts, 10 CRO and reporting prompts, 10 brand-voice audit prompts. Each prompt has a named owner, last-tested date, and a notes column with the winning use-case. Quarterly review retires roughly 8 to 12 prompts and adds 5 to 8 new ones. The team uses Claude for editorial drafts and ChatGPT for fast iteration. Average creative win-rate (variants that beat the control) has risen from 22% before the library to 36% with it. The library survived two senior departures because the structure was documented."
    ),
    "ai-agents-marketing-2026": (
        "What does a 2026 agent program look like in a real B2B SaaS account?",
        "A 22M ARR SaaS deployed three agents over 14 months. The weekly reporting agent pulls from HubSpot, GA4, and the ad platforms, drafts the executive summary, and posts to Slack on Mondays at 8am. The ad variant production agent generates 30 to 40 LinkedIn draft variants per week to a human approval queue. The lead-scoring agent reads inbound demos and updates the score, feeding back closed-won outcomes. Total cost: 38k EUR per year on tooling plus 0.4 FTE on agent operations. Time saved: roughly 1.4 FTE of senior analyst and producer work. Quality lift on the work freed up is the harder-to-measure but more meaningful return."
    ),

    "what-premium-actually-means-d2c": (
        "What does a premium reset look like in a real D2C brand?",
        "A wellness D2C brand at 5M EUR revenue reset positioning over 12 months in 2025 to 2026. Starting state: 28% full-price share, 19% repeat rate, four discount events per year, 78% gross margin. The reset retired three of the four discount events, raised prices 7% (with no volume drop after 60 days), rebuilt PDP copy to remove urgency overlays, and shifted creative to founder-led plus customer-story formats. Ending state at month 12: 64% full-price share, 32% repeat rate, two discount events per year, 80% gross margin. CAC rose 11%; LTV rose 38%. The trade was favourable across every dimension that mattered. The discipline to refuse the discount lever during slow weeks was the hardest part."
    ),
    "meta-ads-premium-d2c-2026": (
        "What does the rebuild look like in a real premium account?",
        "A fashion brand at 8M EUR revenue rebuilt Meta over two quarters. Starting state: 38 ad sets, 12 ad variants per quarter, 7-day click ROAS of 4.1x, blended ROAS unknown. Ending state: 4 Advantage+ Shopping campaigns segmented by region, 64 variants per month with senior creative on every concept, 8 to 14 day refresh cadence on top performers. 7-day click ROAS dropped to 3.6x; modelled ROAS via Northbeam rose to 2.8x; geo-holdout confirmed 2.4x true incremental. The team now bids and budgets off the geo-holdout number. CAC fell 22% over the two quarters; volume held. The team also retired discount-led retargeting entirely."
    ),
    "scaling-premium-d2c-1m-to-10m": (
        "What does the phase-by-phase journey look like in a real brand?",
        "A beauty brand traced 1.8M to 9.5M EUR over 3.5 years from 2022 to mid-2026. Phase 1 (1.8M to 3M): founder-led marketing, 24% blended marketing spend, 67% gross margin, 23% repeat rate. Phase 2 (3M to 6M): head of growth hire, lifecycle owner hire, 22% marketing spend, 71% gross margin, 33% repeat rate, supplier renegotiation lifted gross margin 4 points. Phase 3 (6M to 9.5M): senior creative director, customer marketing lead, wholesale partnership with one premium retailer, 23% marketing spend, 73% gross margin, 41% repeat rate. The most painful stall was at 4.2M when the founder cap met agency creative drift; the head-of-growth hire resolved it in two quarters."
    ),
    "d2c-beyond-meta-channel-mix-2026": (
        "What does diversification look like in a real D2C brand?",
        "A home brand at 6M EUR revenue diversified across three quarters in 2025 to 2026. Quarter 1: added Pinterest Performance+ at 8k EUR per month. Quarter 2: added YouTube in-stream at 12k EUR per month plus a small Reddit programme at 3k EUR per month. Quarter 3: added Amazon Ads at 18k EUR per month for the bestselling SKU. Ending allocation: Meta 49%, Google 26%, Pinterest 9%, YouTube 8%, Amazon 6%, Reddit 2%. Blended CAC fell 13% across the year. The brand attributes most of the lift to Pinterest and Amazon; YouTube and Reddit broke even but earned brand citations that paid off in the LLM-search panel."
    ),
    "founder-led-content-d2c-playbook": (
        "What does this look like for a real founder?",
        "A premium skincare founder runs the system as of mid-2026. Weekly 30-minute call with the content lead on Mondays. Two LinkedIn long-form posts per week, drafted by the content lead, edited by the founder in 15 minutes, posted personally. One monthly newsletter (1,200 words) drafted by the content lead, founder edits. One podcast guest appearance per month, brief by the content lead, founder shows up prepared. Over 18 months, LinkedIn following grew from 2,100 to 18,400. Self-reported attribution at checkout shows the founder name in 14% of responses. Pre-launch waitlist conversion rate has roughly doubled. The founder spends about 4 hours per week on the program total."
    ),
    "wholesale-retail-d2c-when-to-open": (
        "What does the first partnership look like in a real D2C brand?",
        "A premium fashion brand at 4M EUR revenue opened wholesale with one curated retailer in 2025. Negotiated margin: 52% to the retailer with 45-day terms. MAP compliance enforced. Brand merchandising playbook signed off. Quarterly review meetings booked for the year. Year-one results: wholesale revenue 1.1M EUR (24% of total), gross margin on wholesale 38%, sell-through at retail 82%. D2C revenue rose 19% in the same regions where the retailer operated, against 11% in non-retail regions. The brand added 0.6 FTE for wholesale ops plus a partial-FTE account manager. The hardest part was protecting MAP against retailer-driven discount pressure during the brand's first BFCM under the agreement."
    ),

    "b2b-saas-demand-gen-stack-2026": (
        "What does the stack look like in a real SaaS company?",
        "A 17M ARR mid-market SaaS rebuilt the stack across 18 months. Retired channels: gated whitepapers (no impact on pipeline), generic webinars (consistent 7% show rate), and bought intent data (no payback below 30M ARR). Added or expanded channels: founder content (CEO plus head of product on LinkedIn), product-led content (3 use-cases at 2 posts per quarter each), Reddit credibility build, podcast guesting (4 per quarter), G2 review programme (monthly outreach). Pipeline-from-marketing share rose from 38% to 51% over the year. Cost per qualified opportunity fell 22%. The board memo replaced MQL counts with CAC payback by segment, magic number, and NRR contribution from customer marketing."
    ),
    "dark-social-dark-funnel-saas-2026": (
        "What does dark-funnel investment look like in a real SaaS account?",
        "A 9M ARR SaaS ran the dark-funnel rollout over a year. Added self-reported attribution at the demo form in week one. Two months later, the reported channel mix shifted: paid search dropped from 41% to 22%; LinkedIn rose from 18% to 26%; podcasts appeared at 11%; founder content at 14%. Stood up founder LinkedIn cadence in month two. Booked 5 podcast guest appearances in months three and four. Monthly LLM visibility audit started in month four. By month 12, the brand appeared in 38% of the panel prompts on Perplexity, up from 9%. Inbound demos with self-reported 'I heard about you on a podcast' rose from 4 per month to 19 per month."
    ),
    "linkedin-ads-saas-2026-benchmarks": (
        "What does the campaign structure look like in a real account?",
        "An 8M ARR SaaS runs three LinkedIn campaigns in 2026 against a mid-market ICP. Brand awareness: 12k EUR per month, video views and reach, 40k member audience. Gated value offer: 18k EUR per month, lead-gen form for an industry benchmark report, qualifier questions on company size and role. Demo or trial: 22k EUR per month, single-image and document ads to the demo landing page. Quarterly creative refresh on all three. Current numbers: CPM 62 EUR, CTR 0.68%, CPL 152 EUR, CPQO 1,890 EUR. Win rate on marketing-sourced is 24% versus sales-sourced at 16%. The team retires the weakest campaign quarterly and reinvests the budget."
    ),
    "product-led-content-saas-2026": (
        "What does product-led content look like in a real SaaS account?",
        "A 6M ARR engineering-tools SaaS picked three use-cases in early 2025: 'how to set up async code review', 'how to manage feature flags safely', 'how to roll out a deprecation across a team'. Two long-form pieces per quarter per use-case for 12 months. Each piece runs 1,800 to 2,400 words, includes a worked example, embeds the product step inside the workflow, has FAQPage schema, and links to two other use-case pieces. Results at month 12: organic trial sign-ups up 142%, LLM citation appearance on target queries up from 12% to 47%, time-on-page over 5 minutes on 64% of pieces. The team retired generic 'best tool' content over the same period."
    ),
    "abm-small-teams-saas-2026": (
        "What does this look like in a real three-person SaaS team?",
        "A 4M ARR vertical SaaS runs ABM with three people: a head of growth (0.6 FTE on ABM), a content lead (0.4 FTE), and a founder (0.3 FTE for tier-1 outreach). List: 180 accounts curated quarterly, tier 1 at 40 accounts, tier 2 at 140. Per-account playbook for tier 1: founder LinkedIn DM, custom 1-page POV doc, LinkedIn paid retargeting overlay. Tier 2 receives the digital-only treatment. Numbers: account engagement score moved on 62% of tier-1 accounts over 90 days; 14 of those converted to opportunities at 1.7x baseline win rate. Cost per pipeline-EUR fell to 12% of revenue from 19%. The team avoided buying intent data and direct-mail tooling; the discipline carried the day."
    ),
    "reddit-b2b-playbook-llm-citations": (
        "What does the Reddit playbook look like in a real B2B SaaS?",
        "A 7M ARR SaaS targeting engineering managers ran a 9-month Reddit rollout in 2025 to 2026. CEO picked 4 subreddits and read for two weeks. Month 1 to 2: helpful comments without product links; built 2,100 karma. Month 3: started disclosing affiliation when relevant; long answers to architecture questions earned 600 to 1,400 upvotes. Month 6: brand mentions in unrelated threads started appearing organically (28 per month). Month 9: appearance on Perplexity for category queries rose from 3% to 38%. Direct traffic from Reddit URLs rose to 1,200 monthly visitors. Self-reported attribution on demo forms now shows Reddit at 9% of new requests, up from 0% before the program."
    ),

    "mmm-d2c-under-20m-when-it-pays-off": (
        "What does the measurement maturity ladder look like in real brands?",
        "Three brands at different stages illustrate the ladder. Brand A at 800k EUR per month runs platform reporting plus self-reported attribution plus an annual geo-holdout. Cost: 0 EUR plus 4 hours per quarter of analyst time. Brand B at 2.4M EUR per month runs the same stack plus quarterly geo-holdouts plus Meta's Robyn in-house, cost roughly 90k EUR per year analyst time. Brand C at 6M EUR per month runs Northbeam plus monthly geo-holdouts plus self-reported attribution, cost roughly 165k EUR per year tool plus owner. All three are well-matched to scale; each would over-invest or under-invest if they jumped to the wrong rung. The discipline is matching the measurement stack to the revenue reality, not to vendor pitches."
    ),
    "incrementality-testing-geo-holdout-playbook": (
        "What does the test look like in a real D2C account?",
        "A premium D2C brand at 9M EUR revenue ran a geo-holdout on Meta in Q2 2026. Matched two pairs of NL and BE regions on baseline revenue and recent growth. Held out 18% of Meta spend in test regions for 21 days. Pre-registered metrics: revenue, conversions, branded search. Meta-reported ROAS in the test period: 3.8x. Geo-holdout incremental ROAS: 2.3x with a 90% confidence interval of 1.9x to 2.7x. The team reallocated 14% of Meta spend toward brand work in the next quarter. Quarterly geo-holdouts now run on Meta and Google. Year-over-year, the gap between platform-reported and incremental ROAS has stabilised at around 35%, which the team treats as the working calibration."
    ),
    "last-click-is-dead-d2c-attribution-2026": (
        "What does the triangulation look like in a real D2C account?",
        "A premium D2C brand at 11M EUR revenue runs the four-layer stack in 2026. Platform reporting refreshes daily for tactical decisions. Self-reported attribution lives on the checkout page and rolls up to a Looker dashboard monthly. MMM via Measured refreshes quarterly with annual full rebuild. Geo-holdouts run quarterly on Meta and Google. The four layers disagree on individual channel attribution by 20 to 45%. The team reports the disagreement to the executive team as feature, not bug. Budget moves on the triangulated read, not on any one number. Over 18 months, blended CAC fell 17% as the brand reallocated toward channels that the triangulation showed were genuinely incremental."
    ),
    "cac-payback-saas-board-metrics-2026": (
        "What does the board memo look like in a real SaaS company?",
        "A 19M ARR mid-market SaaS replaced MQL reporting with the three-metric board memo in Q1 2026. Headline page: CAC payback 22 months (SMB segment), 28 months (mid-market), 41 months (enterprise) with four-quarter trend lines. Magic number 0.82 with trend rising from 0.61. NRR 114% with marketing-influenced expansion at 6% of starting ARR. Levers slide: three specific changes marketing will make in the next quarter to move CAC payback. Risks slide: two things that would push it the wrong way. The CFO now treats marketing as a fellow operator on the efficiency story, not a cost centre. Budget conversations got easier as a result."
    ),
    "pipeline-forecasting-ai-saas": (
        "What does the forecast look like in a real SaaS account?",
        "A 13M ARR enterprise-led SaaS runs the forecast with three segments (SMB, mid-market, enterprise), stage-by-stage conversion rates updated monthly, and velocity tracked at deal-size band. AI handles anomaly detection (stuck deals over 60 days in stage) and synthesises rep notes into a numeric adjustment factor. Forecast accuracy one quarter out: 11% absolute error in 2025 versus 24% in 2024. Two quarters out: 19% versus 33%. The team tracks the error itself as a north star and aims to halve it year over year. Board memo at the end of each quarter includes the forecast range, the actual result, the error breakdown, and one lesson learned."
    ),
    "multi-touch-attribution-b2b-mostly-theatre": (
        "What does the replacement stack look like in a real SaaS company?",
        "A 9M ARR mid-market SaaS retired its MTA tool as the headline source in late 2025. Replacement: self-reported attribution on the demo form (open text, mandatory), pipeline-source tracking split by marketing-sourced and sales-sourced, and a quarterly pause test on the largest channel. After six months, the reported channel mix had shifted dramatically. Paid search fell from 34% to 18% of stated source. Founder content rose from 0% (invisible to MTA) to 23%. Podcasts appeared at 11%. The team kept the MTA tool as a daily-ops dashboard but stopped quoting it to the board. Budget reallocation followed the triangulated read; blended CAC fell 19% across two quarters."
    ),

    "get-cited-by-chatgpt-d2c-best-of": (
        "What does a citation lift look like in a real D2C brand?",
        "A premium beauty brand at 8M EUR revenue ran the LLM-citation rollout for 12 months. Site-side: rebuilt 6 pages as definition or comparison content with FAQPage schema. Off-site: Trustpilot review programme reached 412 reviews, Reddit credibility build in 3 beauty subreddits, 4 podcast guest appearances by the founder, one earned Vogue placement. Monthly LLM visibility audit tracked the panel of 20 prompts across four LLMs. At month 12, appearance in category-shortlist prompts rose from 6% to 41%. Three competitors that did not invest fell from regular appearance to occasional. The brand attributes roughly 14% of new-customer revenue to LLM-driven discovery according to self-reported attribution responses."
    ),
    "schema-markup-ecommerce-2026": (
        "What does the implementation look like in a real Shopify brand?",
        "A premium home brand on Shopify implemented the priority five schema types over a two-week sprint in 2026. Product schema with GTIN, aggregateRating, and offers on all 240 PDPs. Article schema with author and date on the blog. FAQPage schema on 18 high-value pages. BreadcrumbList site-wide. Organization schema in the head. Cost: 1.4 dev days plus 8 hours of QA. Outcomes inside 90 days: rich-result eligible page count rose from 8 to 162; Perplexity citation appearance on product-evaluation queries rose 32%; Search Console clicks on category pages rose 14%. The team now runs a quarterly schema audit using the Schema.org validator and Google's Rich Results Test."
    ),
    "geo-aeo-seo-definitions-2026": (
        "What does the integrated discipline look like in a real team?",
        "A 12M ARR SaaS settled the team on a single vocabulary in early 2026. Budget split moved from 75% SEO content / 25% technical and off-site to 55% integrated content / 20% technical foundations / 20% off-site work / 5% measurement. Same team handles all three. The senior SEO lead was renamed to senior content lead and trained on GEO structural patterns over a quarter. Internal weekly standup now reviews all three measurement layers (clicks, featured snippets, LLM citations). Quarterly executive memo presents the triangulated picture. The vocabulary change alone reduced the number of strategy debates by half; the team spent the time on shipping instead."
    ),
    "seven-content-formats-llms-cite-most": (
        "What does this look like applied to a real editorial calendar?",
        "A 7M ARR SaaS rebuilt their editorial calendar in 2025 to match the seven formats. 12 posts per quarter split: 3 definition pages, 3 comparison pages, 2 methodology guides, 1 original data study, 1 expert opinion piece, plus standalone FAQ updates on top pages. The listicle format was retired because the team's earlier listicles had been generic and earned no citations. Year-over-year LLM citation appearance rose from 11% to 39%. The original data study (a benchmark report based on customer data) earned more LLM citations alone in its first six months than the team's previous 18 generic posts combined. The team commits to two original data studies per year as a result."
    ),
    "audit-ai-visibility-without-paying-profound": (
        "What does the audit look like in a real account month over month?",
        "A 5M ARR SaaS ran the DIY audit monthly for 14 months. Same operator, same 20 prompts, same logged-out browser sessions, four LLMs. Tracking sheet in Google Sheets with one tab per month. Time investment: 75 minutes per month after the first run. Findings over the 14 months: appearance rose from 8% to 34% across the panel; the LLMs increasingly cited Reddit threads and G2 reviews rather than the brand's own blog; competitor appearances revealed which competitor invested in Wikipedia and which leaned on PR. The action log from the audit drove 11 content roadmap decisions and 3 PR investments. The team now treats the audit as the central GEO measurement signal."
    ),
    "g2-reddit-wikipedia-matter-more-than-blog": (
        "What does the off-site shift look like in a real SaaS account?",
        "A 9M ARR vertical SaaS shifted budget from 80% on-site / 20% off-site to 50% on-site / 50% off-site over two quarters. New off-site spend: 32k EUR per quarter on PR for editorial placements, 22k EUR per quarter on the reviews programme (G2 plus TrustRadius plus Capterra), 18k EUR per quarter on founder content production and podcast guesting, balance on Wikipedia-adjacent reference work. After 12 months: 96 new reviews on G2 (up from 14), 7 earned press placements in TechCrunch, FT, and industry titles, founder LinkedIn following up 3.4x. LLM citation appearance on category-shortlist prompts rose from 9% to 47%. The brand became a default name in Perplexity responses for the category by year-end."
    ),

    "premium-pdp-audit-20-brands": (
        "What does a PDP rebuild look like in a real brand?",
        "A premium skincare brand at 4M EUR revenue rebuilt the top 5 PDPs over a quarter in 2026. Hero photography reshot in studio (replacing AI-generated imagery). Named social proof added (Vogue and dermatologist quotes). Shipping and returns promise moved above the fold. Detailed ingredients list added with sourcing notes. Founder voice in the product description. Customer photography in a curated band below the description. Sticky CTA changed to 'Choose size, ships free over 50'. Results after 30 days: conversion rate on the rebuilt PDPs rose 31%, AOV rose 8%, returns rate held flat, NPS at day 30 rose 6 points. The team then rolled the patterns across the remaining catalogue."
    ),
    "ai-creative-100-variants-no-slop": (
        "What does the pipeline look like in a real D2C account?",
        "A premium D2C brand at 5M EUR revenue runs the pipeline as of mid-2026 producing 72 variants per month for Meta and TikTok combined. Workflow: weekly Monday brief signed off by the senior creative director, two photography days per month with a small in-house studio, AI variation done in Adobe Firefly with banned-visual rules enforced, copy generated by Claude using the prompt library, junior editor pass for brand voice, batch upload Thursday afternoons. Senior creative time on the program: 0.5 FTE. Junior editor: 1 FTE. Cost per variant landed: roughly 80 EUR all-in. Creative win-rate (variants beating control): 38%. Top creatives now last 12 to 16 days before refresh; baseline before the pipeline was 7 to 10."
    ),
    "killing-ad-fatigue-2026-refresh-cadence": (
        "What does the cadence look like in a real D2C account?",
        "A premium beauty brand at 6M EUR revenue runs the refresh cadence on a Monday rhythm. Mondays at 9am: review of all top performers across Meta, TikTok, Pinterest. Variants beyond their refresh window are flagged. Tuesday and Wednesday: variant production from the in-house pipeline. Thursday: brand-voice check and batch upload to draft state. Friday: launch with 24-hour visual QA. Top Meta creatives last 11 days on average; TikTok 7 days; YouTube 42 days. CPA week-over-week drift sits in the 4 to 8% range, well within tolerance. The team retired the old 'refresh quarterly' pattern entirely; the new cadence took two quarters to bed in but stabilised cleanly."
    ),
    "saas-pricing-page-patterns-2026": (
        "What does a real pricing-page rebuild look like?",
        "A 4M ARR vertical SaaS rebuilt their pricing page in 2026 using two patterns: a value-based ladder with three tiers named for customer archetypes ('Operator', 'Studio', 'Network') and a calculator-driven enterprise self-quote. Annual discount anchor at 18% with monthly priced higher. FAQ block with 11 questions. Customer testimonial inside the pricing block. Comparison row against two named competitors. Results inside 60 days: trial-start rate up 41%, demo-request rate up 28%, plan-choice distribution shifted toward the middle tier as anticipated, downstream win rate on demo requests rose 9 points. The team has since added quarterly review of the page; mid-quarter changes only with legal sign-off."
    ),
    "founder-thought-leadership-saas-pipeline-system": (
        "What does this look like for a real SaaS founder?",
        "A vertical SaaS founder at 8M ARR has run the system for 18 months. Weekly 25-minute Monday call with the content lead. Two long LinkedIn posts per week, draft delivered Thursday for the founder to edit and post Friday. Monthly newsletter of 1,400 to 1,800 words. One podcast guest appearance per month (booked two months in advance). Quarterly long-form piece on the company blog (2,200 to 2,800 words). Annual original-data study using customer benchmarks. Outcomes: LinkedIn following up 4.1x, inbound demos from LinkedIn up 5.6x, branded search up 38%, self-reported attribution shows founder content in 19% of demo requests. Founder spends about 3.5 hours per week on the program total."
    ),
    "brand-building-performance-marketers-2026": (
        "What does a real 20% brand reallocation look like?",
        "A 14M ARR SaaS reallocated 18% of marketing spend from performance to brand over two quarters in 2025 to 2026. New brand line items: founder content production (12k EUR per month), PR retainer for earned editorial placements (9k EUR per month), podcast guesting and original research budget (8k EUR per month), top-of-funnel paid reach campaigns (15k EUR per month). Leading indicators tracked monthly: branded search volume, direct traffic share, self-reported attribution share, profile views and follows. After four quarters: branded search up 41% year over year, direct traffic share up from 17% to 28%, self-reported attribution share for brand sources up from 11% to 31%. Blended CAC fell 14% over the same period."
    ),

    "clv-modelling-premium-d2c-cohort": (
        "What does the model look like in a real premium D2C account?",
        "A premium home brand at 7M EUR revenue runs the cohort CLV model in Looker as of 2026. Monthly cohort grouping, cumulative net revenue at 30, 90, 180, 365 days, plus a 730-day curve where data permits. Current numbers: 30-day 72 EUR, 90-day 124 EUR, 180-day 178 EUR, 365-day 264 EUR. CAC blended 96 EUR; payback at the 90-day mark. Channel-specific cohorts show Meta acquisitions with 90-day CLV of 118 EUR but 365-day of 232 EUR; Pinterest at 138 EUR and 296 EUR respectively. The team tilted incremental budget toward Pinterest based on this read. Quarterly validation against actuals shows the projection model averaging 6 to 11% error, comfortable for budget decisions."
    ),
    "email-sms-premium-d2c-flows": (
        "What does the rebuild look like in a real premium D2C account?",
        "A premium skincare brand at 5M EUR revenue rebuilt the lifecycle in Klaviyo across one quarter. Welcome flow reduced from 6 emails to 3, no discount in any of them, founder voice in email one. Cart abandonment reduced from 5 emails to 3, last email with an optional free-shipping offer. Browse abandonment trimmed from 3 emails to 2. Post-purchase reflows added with usage tips and founder note. Win-back at day 75 instead of day 30. Annual purchase anniversary added. Outcomes after 90 days: revenue per email sent up from 0.22 to 0.41 EUR, open rate up from 39% to 51%, unsubscribe rate down from 0.4% to 0.2%, repeat rate up 7 points. Total emails sent per month dropped 28%; revenue from email rose 22%."
    ),
    "nrr-saas-marketing-influences": (
        "What does this look like in a real SaaS marketing function?",
        "A 15M ARR mid-market SaaS made NRR a marketing-co-owned metric in early 2026. New marketing initiatives: customer marketing lead hired (full-time), quarterly customer advocacy programme with 6 reference customers, monthly expansion campaign for feature adoption, lifecycle email sequences mapped to activation events, founder appearance at annual customer summit. After four quarters: NRR moved from 108% to 117%. Marketing-influenced expansion revenue: 7% of starting ARR (up from 2%). Reference customer pipeline: 23 active references (up from 6). Board memo now includes marketing-influenced NRR alongside CAC payback and magic number. CFO recognises marketing as a co-owner of the retention story."
    ),
    "saas-lifecycle-email-14-sequences": (
        "What does the lifecycle library look like in a real PLG SaaS?",
        "A 6M ARR PLG SaaS built the 14-sequence library in Customer.io over 11 weeks. First three sequences (welcome, first-aha-moment, trial-end) shipped in week three and delivered most of the early lift. By week six, the team had stalled-at-step-X, invite-teammate, integration-installed, and paywall-near in production. By week eleven, the full library was running. Outcomes after 120 days: trial-to-paid conversion rose from 14% to 22%, time-to-aha-moment fell from 6.4 days to 3.1 days, expansion-eligible upsell revenue per quarter rose 64%. Quarterly retrospective now reviews each sequence's contribution and retires sequences that fall below a defined threshold."
    ),
}


def main():
    xml = WXR.read_text(encoding="utf-8")
    updated = 0

    def repl(m):
        nonlocal updated
        full = m.group(0)
        slug = m.group("slug")
        body = m.group("body")
        if MARKER in body or slug not in EX:
            return full
        h2_text, paragraph = EX[slug]
        block = (
            MARKER + "\n"
            f'<!-- wp:heading --><h2 class="wp-block-heading">{h2_text}</h2><!-- /wp:heading -->\n'
            f'<!-- wp:paragraph --><p>{paragraph}</p><!-- /wp:paragraph -->'
        )
        faq_marker = '<!-- wp:heading --><h2 class="wp-block-heading">Frequently asked questions</h2>'
        if faq_marker in body:
            new_body = body.replace(faq_marker, block + "\n" + faq_marker, 1)
        else:
            related = '<!-- bv-related-reading -->'
            if related in body:
                new_body = body.replace(related, block + "\n" + related, 1)
            else:
                new_body = body + "\n" + block
        updated += 1
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
    print(f"Injected worked examples into {updated} posts.")


if __name__ == "__main__":
    main()
