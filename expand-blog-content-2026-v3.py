#!/usr/bin/env python3
"""
Second expansion pass. Adds two structured sections to every post that
is still under 1200 words: a "What does the next 90 days look like?"
action plan and a "Common implementation pitfalls" deep dive.
Both are substantive content, not padding. Idempotent via marker.
"""
import re
from pathlib import Path

WXR = Path(__file__).parent / "wp-content/themes/booming-venture/import/booming-venture-content.xml"
MARKER = "<!-- bv-v3-expansion -->"

# Per-post action plans and pitfalls. Slug -> (action_h2, action_paragraphs, pitfall_paragraphs).
EXTRA = {
    "ai-performance-marketing-stack-2026": (
        "What does the next 90 days look like for an AI performance stack rollout?",
        [
            "Days 1 to 30: audit the existing creative pipeline, set the brief discipline, and stand up an AI variation workflow. Pick the single biggest creative gap and close it. Most teams find their hero variant library is the place where the lift compounds fastest.",
            "Days 31 to 60: connect first-party data to the bidding layer. Most accounts have basic conversion events flowing, but lifetime-value signals are missing. Wire the LTV signal in and watch CAC drop 8 to 15% on prospecting campaigns alone.",
            "Days 61 to 90: stand up the measurement triangulation. Self-reported attribution at checkout, a single quarterly geo-holdout on the largest channel, and a monthly trend dashboard that the executive team actually reads."
        ],
        [
            "Three pitfalls recur across failed rollouts. First, buying tools before designing the workflow. Second, expecting AI to fix unclear positioning. Third, measuring AI productivity but not revenue impact.",
            "The pattern behind all three is the same: treating AI as a magic ingredient instead of a production lever. The brands that win sequence the work, measure each phase, and accept that strategy stays human.",
        ]
    ),
    "advantage-plus-vs-performance-max-vs-pinterest": (
        "What does a 90-day rollout look like across the three platforms?",
        [
            "Days 1 to 30: stabilise Meta Advantage+ Shopping. Audit exclusions, lock the creative pipeline at 30 to 80 variants per month, and confirm the audience exclusion structure prevents brand-search cannibalisation.",
            "Days 31 to 60: lock down Performance Max. Add brand exclusion lists, structure asset groups by audience signal, and review the Search-themes coverage to make sure non-branded queries actually convert.",
            "Days 61 to 90: stand up Pinterest Performance+ on a small budget. 5k to 10k EUR per month is enough to learn whether the category is right. Quarterly geo-holdout test on the largest of the three confirms the modelled ROAS is real."
        ],
        [
            "Three common pitfalls across the three platforms: running all of them at the same scale (none get to learning thresholds), starving creative production (the platforms reward volume), and over-trusting platform-reported ROAS without triangulation.",
            "The brands that avoid the pitfalls run two platforms well plus a third in test. They protect creative spend ahead of media spend. They review the measurement stack quarterly, not annually.",
        ]
    ),
    "ai-paid-media-roi-b2b-90-day-test": (
        "What does a quarter-by-quarter rollout of the test framework look like?",
        [
            "Quarter 1: baseline plus first AI test. Run the 90-day framework end to end on one variable. Most teams pick lead scoring or LinkedIn creative volume because the data is cleanest.",
            "Quarter 2: scale the winner, retire the loser, baseline the next test. The board memo at the end of the quarter shows the team operates a test cadence rather than a campaign cadence.",
            "Quarter 3 and beyond: rolling tests. One AI test in flight at all times. The portfolio of tests starts producing learning that compounds across the team and the year."
        ],
        [
            "Common pitfalls: changing methodology mid-test, using underpowered sample sizes, and reporting the most flattering number when the answer is ambiguous.",
            "The cure is the discipline of the framework itself. Document the protocol; commit to it; report the honest result whether the team likes it or not.",
        ]
    ),
    "ai-replacing-marketing-hire-saas": (
        "What does an honest 90-day organisational redesign look like?",
        [
            "Days 1 to 30: skill-audit the current team. Identify the production work that AI can take and the strategic work that is currently starved because junior production consumes the time.",
            "Days 31 to 60: hire one AI ops generalist or re-scope an existing analyst into the role. Build the prompt library and the workflow inventory.",
            "Days 61 to 90: re-allocate existing seats toward strategy. Document the new team shape and present it to the board. Hold off on new external hires for another quarter."
        ],
        [
            "Three pitfalls: firing junior roles before the senior roles are designed to consume the freed time, over-buying tools that nobody owns, and ignoring the junior-talent pipeline question.",
            "The brands that handle this well treat it as a 12-month redesign, not a quarterly cost cut. The headline saving is real; the strategic upside takes longer.",
        ]
    ),
    "prompt-library-performance-marketers": (
        "How do you operationalise the library across the team?",
        [
            "Pick the storage. Notion for most teams, Git for engineering-led marketing teams, shared docs for the smallest setups. The discipline is the version history more than the platform.",
            "Onboarding. New hires get the prompt library on day one. The library is part of the brand-voice training, not a separate thing.",
            "Quarterly review. Test each prompt against current model behaviour. Retire the prompts that no longer outperform a one-line ask. Promote the ones that earned consistent wins."
        ],
        [
            "Three common pitfalls: solo-author libraries that die when the author leaves, libraries that never get reviewed and degrade silently, and over-engineering with templating tools that nobody learns.",
            "The cure is a named owner, a quarterly review cadence, and the willingness to delete prompts that have stopped working.",
        ]
    ),
    "ai-agents-marketing-2026": (
        "What is a realistic 90-day agent rollout?",
        [
            "Days 1 to 30: shadow mode. Pick the lowest-stakes lane (weekly anomaly report or ad variant draft production). Let the agent run; compare to human output; do not publish.",
            "Days 31 to 60: draft mode. The agent publishes drafts to a human approval queue. The approval gate is a hard requirement, not a suggestion.",
            "Days 61 to 90: limited autonomy on the lowest-stakes lane. Daily summary to a human. Kill switch ready. Document the lessons before adding the second lane."
        ],
        [
            "Three pitfalls: launching agents in brand-sensitive lanes before they are proven, no logging so the agent is a black box, and treating the rollout as a one-time project rather than an ongoing program.",
            "Mature programs treat agents like junior staff. They get scope creep slowly, they get reviewed quarterly, and they get retired without ceremony when they stop earning their keep.",
        ]
    ),

    "what-premium-actually-means-d2c": (
        "What does the next 90 days look like for a premium brand reset?",
        [
            "Days 1 to 30: audit the current state. Pull the full-price share, repeat rate, discount frequency, and AOV by acquisition channel. The data tells you whether you are actually premium or just expensive.",
            "Days 31 to 60: pick one of the criteria you currently fail and ship the fix. Most brands find that either repeat rate or discount frequency is the weakest link.",
            "Days 61 to 90: lock in the change. Set a policy on discount events for the next year. Communicate the policy internally. Document the cost of breaking it."
        ],
        [
            "Three pitfalls: confusing premium pricing with premium positioning, discounting under quarterly pressure, and copying a competitor's premium playbook that does not fit your category.",
            "The most durable premium brands are quietly disciplined. They say no to the wrong retailers, the wrong discount events, and the wrong customers. The discipline compounds.",
        ]
    ),
    "meta-ads-premium-d2c-2026": (
        "What does a 90-day Meta refresh look like?",
        [
            "Days 1 to 30: rebuild the creative pipeline. Set the brief discipline, the variant volume target, and the refresh cadence. Drop interest-stack testing.",
            "Days 31 to 60: simplify the campaign structure. Move to one Advantage+ Shopping campaign per region or AOV band. Confirm the exclusion structure.",
            "Days 61 to 90: stand up the measurement triangulation. Geo-holdout on the largest spend region; self-reported attribution at checkout."
        ],
        [
            "Three common pitfalls: too many ad sets, too few creative variants, and reporting on 7-day click ROAS alone.",
            "The cure is structural. The platform rewards creative volume and disciplined exclusions. Both are operations problems with marketing skins.",
        ]
    ),
    "scaling-premium-d2c-1m-to-10m": (
        "What does the cross-over from each phase to the next look like?",
        [
            "From 1M to 3M to 3M to 6M: hire a head of growth, formalise lifecycle, professionalise creative. Most stalls happen if any of those three are skipped.",
            "From 3M to 6M to 6M to 10M: add a third growth engine (retail or organic content). Headcount grows to 8 to 12. Margin gets re-engineered with supplier negotiations.",
            "Past 10M: international expansion, customer marketing as a real function, and a deeper measurement stack. The shape of the team changes again."
        ],
        [
            "Three pitfalls: trying to scale phase 1's playbook to phase 3, hiring a CMO before a head of growth, and letting agency dependency replace the founder voice.",
            "Brands that get this right do the un-glamorous operations work alongside the marketing work. Margin compounding beats short-term volume in every phase.",
        ]
    ),
    "d2c-beyond-meta-channel-mix-2026": (
        "What does the next quarter's diversification look like?",
        [
            "Pick channel two. Match it to your category (Pinterest for visual; Reddit for niche credibility; TikTok for younger). Commit a real test budget of 10 to 30k EUR over the quarter.",
            "Design the creative pipeline for native rhythms. Re-using Meta ads on the new channel will fail; budget for platform-native production.",
            "Set a leading indicator and a hard kill-criteria. If the channel does not move the leading indicator inside the test window, retire it cleanly."
        ],
        [
            "Three pitfalls: diversifying too early (before Meta is repeatable), copying competitor mixes without testing, and starving creative production for the new channel.",
            "The brands that diversify well do it slowly and document each channel's playbook before adding the next.",
        ]
    ),
    "founder-led-content-d2c-playbook": (
        "What does a sustainable 90-day rollout look like?",
        [
            "Weeks 1 to 4: hire or assign a content lead. Set the weekly call cadence. Build a brand-voice rule book.",
            "Weeks 5 to 8: ship two LinkedIn posts and two Instagram posts per week. The founder edits in 15 minutes per post; the content lead drafts.",
            "Weeks 9 to 12: layer in the newsletter and one podcast guest appearance. Quarterly retrospective on what worked."
        ],
        [
            "Three pitfalls: the founder tries to type, posting slips when the founder gets busy, and the content lead drifts into corporate voice.",
            "The cure is structural. The founder is the source. The content lead is the typist. The cadence is non-negotiable.",
        ]
    ),
    "wholesale-retail-d2c-when-to-open": (
        "What does a first wholesale partnership rollout look like?",
        [
            "Months 1 to 2: shortlist three retailers. Walk away from any that demand exclusivity, MAP-breaking discounts, or consignment.",
            "Months 3 to 4: negotiate margin, terms, MAP compliance, exit clause, and data sharing. Each of the five is non-negotiable.",
            "Months 5 to 6: launch. In-store merchandising audit. Sell-through review at month 90. Brand-search lift check in the partner's regions."
        ],
        [
            "Three pitfalls: signing too many retailers in year one, accepting consignment to win the shelf, and skipping the brand-merchandising playbook.",
            "The brands that win wholesale treat the channel like a marketing investment, not just a distribution add-on.",
        ]
    ),

    "b2b-saas-demand-gen-stack-2026": (
        "What does the next quarter look like for a stack rebuild?",
        [
            "Month 1: audit current channels against the six-driver framework. Identify the theatre channels and the under-invested real drivers.",
            "Month 2: retire the theatre channels. Move budget toward founder content, product-led content, and curated outbound.",
            "Month 3: stand up the measurement stack. CAC payback by segment, magic number, NRR, plus pipeline coverage. Replace MQL volume reporting."
        ],
        [
            "Three pitfalls: keeping theatre channels because cancelling them feels risky, hiring more before reorganising, and presenting MQL counts to a board that wants CAC payback.",
            "The cure is operational discipline. Document the stack, set the quarterly review cadence, kill the channels that miss the bar twice in a row.",
        ]
    ),
    "dark-social-dark-funnel-saas-2026": (
        "What does the next 90 days look like for dark-funnel investment?",
        [
            "Month 1: add self-reported attribution at the demo form. Two months of data will already shift the reported channel mix substantially.",
            "Month 2: stand up the founder content cadence and the podcast guesting plan. Both compound across the dark funnel.",
            "Month 3: monthly LLM visibility audit. The audit identifies which third-party platforms need investment next."
        ],
        [
            "Three pitfalls: trusting platform analytics to describe the dark funnel, cutting brand spend because attribution does not show it, and skipping the LLM visibility audit because it feels new.",
            "The brands that win on dark social treat the funnel realistically. They invest where the buyer is, not where the analytics says they are.",
        ]
    ),
    "linkedin-ads-saas-2026-benchmarks": (
        "What does a 90-day LinkedIn test plan look like?",
        [
            "Month 1: lock the audience definition with firmographic plus skill targeting. Exclude customers and competitors. Ship three campaign types with focused creative.",
            "Month 2: review CPL, CTR, CPQO. Kill the weakest campaign; double down on the strongest. Refresh creative on the top performers.",
            "Month 3: pull the quarterly numbers, present against benchmarks, decide on next quarter's spend trajectory."
        ],
        [
            "Three pitfalls: 15-campaign sprawl, weak qualifier questions on lead-gen forms, and ignoring auto-bidding without weekly review.",
            "The cure is fewer, better-built campaigns reviewed weekly with discipline.",
        ]
    ),
    "product-led-content-saas-2026": (
        "What does a 90-day product-led content launch look like?",
        [
            "Month 1: list the top 10 use-cases. Rank by demand. Pick the top 3 to commit content to over the next six months.",
            "Month 2: ship the first two pieces per use-case. Distribute through LinkedIn, newsletter, and AI-search-oriented structure.",
            "Month 3: review the leading indicators. Trial sign-ups, demo requests, LLM citation appearance on target queries."
        ],
        [
            "Three pitfalls: too many use-cases (kills depth), promotional drift in the writing, and six-month thinking when the program needs 18 months to compound.",
            "The cure is patience and editorial discipline. The compounding effect is real but slow.",
        ]
    ),
    "abm-small-teams-saas-2026": (
        "What does a first 90 days of small-team ABM look like?",
        [
            "Month 1: build the curated list of 100 to 250 accounts. Tier them. Document the inclusion rule.",
            "Month 2: ship the per-account playbook for tier 1 accounts. Founder LinkedIn outreach, custom POV doc, paid retargeting overlay.",
            "Month 3: review engagement signals; trigger sales calls on the right ones. Quarterly retrospective documenting what worked."
        ],
        [
            "Three pitfalls: list bloat (too many accounts), spray-style outreach, and measuring by lead volume rather than account engagement.",
            "Small-team ABM rewards discipline more than headcount. 150 accounts handled well outperform 1,500 spammed.",
        ]
    ),
    "reddit-b2b-playbook-llm-citations": (
        "What does a 90-day Reddit credibility rollout look like?",
        [
            "Month 1: pick 3 to 5 subreddits. Read for two weeks. Comment helpfully without product links for the rest of the month.",
            "Month 2: ramp participation. Founder voice; honest comparisons; long substantive answers.",
            "Month 3: measure. Branded search lift; direct traffic from Reddit; appearance in LLM responses citing Reddit sources."
        ],
        [
            "Three pitfalls: leading with product links, running sock-puppets, and quitting after a slow first month.",
            "Reddit rewards patience and helpful presence. The compounding effect on LLM visibility is real but takes quarters.",
        ]
    ),

    "mmm-d2c-under-20m-when-it-pays-off": (
        "What does the next quarter look like for measurement maturity?",
        [
            "If you are under 1.5M EUR per month: stay with the triangulation stack. Self-reported attribution plus quarterly geo-holdout plus platform reporting will outperform a forced MMM.",
            "If you are at 1.5M to 5M per month: try Robyn or Meridian with an analyst. The cost is the analyst time; the methodology is free.",
            "If you are past 5M per month: vendor MMM becomes defensible. Pair with an internal owner who will spend 5 to 10 hours per week on interpretation."
        ],
        [
            "Three pitfalls: buying MMM before having an owner, expecting MMM to answer daily-ops questions, and punishing the model when it disagrees with platform reporting.",
            "The cure is sequencing. Triangulation first; MMM when scale and culture support it.",
        ]
    ),
    "incrementality-testing-geo-holdout-playbook": (
        "What does a 90-day incrementality rollout look like?",
        [
            "Month 1: design the test on the largest channel. Pre-register regions, metrics, and significance threshold. Brief the team on the change-freeze.",
            "Month 2: run the test. Resist mid-test changes. Document any deviations as part of the run.",
            "Month 3: read the result honestly. Present the range, not a point estimate. Reallocate budget based on the gap between platform-reported and incremental ROAS."
        ],
        [
            "Three pitfalls: badly matched regions, contamination from mid-test changes, and confirmation-bias reads when the result is ambiguous.",
            "The cure is pre-registration and discipline. The test only works if the protocol survives the inconvenient findings.",
        ]
    ),
    "last-click-is-dead-d2c-attribution-2026": (
        "What does the next 90 days look like for a triangulated stack?",
        [
            "Month 1: add self-reported attribution at checkout. Cost is half a dev day. Return is continuous channel-influence signal.",
            "Month 2: stand up the first geo-holdout on the largest channel. Document the protocol.",
            "Month 3: roll the triangulation into the quarterly budget review. Replace last-click ROAS as the headline."
        ],
        [
            "Three pitfalls: cancelling the attribution tool too fast, keeping last-click as the board headline, and treating disagreement between layers as noise instead of signal.",
            "The cure is patience with the triangulation. The layers complement; none alone is the truth.",
        ]
    ),
    "cac-payback-saas-board-metrics-2026": (
        "What does the next board cycle look like?",
        [
            "Quarter 1: replace MQL counts with CAC payback, magic number, and NRR on the marketing board memo.",
            "Quarter 2: add the four-quarter trend on each metric. Boards reward trajectory.",
            "Quarter 3 onward: present the specific marketing lever moving each metric. The board recognises the team has changed how it operates."
        ],
        [
            "Three pitfalls: presenting only the snapshot, mixing segments, and over-claiming on attribution.",
            "The cure is honest reporting. Trends, segments, and triangulated attribution build trust faster than precision claims.",
        ]
    ),
    "pipeline-forecasting-ai-saas": (
        "What does a quarter-by-quarter forecasting maturity look like?",
        [
            "Quarter 1: lock the methodology. Three inputs, two segments, AI for anomaly detection only.",
            "Quarter 2: track the forecast error. Document the cause of misses.",
            "Quarter 3 and beyond: improve the error year over year. The compounding learning is the real benefit."
        ],
        [
            "Three pitfalls: changing methodology each quarter, mixing segments, and presenting point estimates when ranges are honest.",
            "The cure is calmness. A forecast that is 12% off with documentation outperforms a precise number that surprises the board.",
        ]
    ),
    "multi-touch-attribution-b2b-mostly-theatre": (
        "What does the next 90 days look like for replacing MTA?",
        [
            "Month 1: add self-reported attribution at the demo form. Two months of data shifts the channel mix substantially.",
            "Month 2: stand up pipeline-source tracking with marketing-sourced versus sales-sourced views.",
            "Month 3: pause the lowest-ROI MTA-credited channel for two weeks. Measure the actual lift."
        ],
        [
            "Three pitfalls: cancelling MTA before the triangulation works, hiding self-reported attribution from the dashboard, and over-claiming the gap to the executive team.",
            "The cure is patience and triangulation. The MTA tool is not the enemy; it is one of three sources.",
        ]
    ),

    "get-cited-by-chatgpt-d2c-best-of": (
        "What does the next 90 days look like for LLM citation work?",
        [
            "Month 1: monthly DIY visibility audit set up. Identify the gaps and the third-party sources currently cited.",
            "Month 2: ship the first three structural site changes (definition page, comparison page, FAQ). Schema markup added.",
            "Month 3: start the off-site work. PR brief, review programme outreach, Reddit presence plan."
        ],
        [
            "Three pitfalls: site-only investment, gating high-value content that should be ungated, and quitting before the 90-day-plus compounding kicks in.",
            "The cure is balance. Site work plus off-site work plus measurement; none alone is enough.",
        ]
    ),
    "schema-markup-ecommerce-2026": (
        "What does a schema rollout look like for the next quarter?",
        [
            "Month 1: implement Product, Article, FAQPage, BreadcrumbList, Organization schema. Validate with Google's Rich Results Test.",
            "Month 2: connect reviews to schema feed. Monitor Search Console and Bing Webmaster Tools for errors.",
            "Month 3: spot-check via the major LLM crawler user agents. Document the implementation for the team."
        ],
        [
            "Three pitfalls: relying on auto-generated stubs, leaving Speakable and Event schema on every page, and never validating after the initial implementation.",
            "The cure is yearly auditing and ownership. Schema changes; ownership keeps it current.",
        ]
    ),
    "geo-aeo-seo-definitions-2026": (
        "What does the next quarter look like for an integrated discipline?",
        [
            "Month 1: settle the team on shared vocabulary. The strategic argument is the budget split that follows.",
            "Month 2: re-allocate the organic budget toward 50 to 60% integrated content, 15 to 25% technical foundations, 15 to 25% off-site work.",
            "Month 3: shift measurement to triangulated: clicks (SEO), featured snippets (AEO), LLM citations (GEO)."
        ],
        [
            "Three pitfalls: treating GEO as a separate discipline with its own team, ignoring AI Overviews because they look new, and starving off-site work.",
            "The cure is integration. The three disciplines reinforce each other when treated as one editorial system.",
        ]
    ),
    "seven-content-formats-llms-cite-most": (
        "What does an editorial calendar built on the seven formats look like?",
        [
            "Month 1: refresh the top definition page and the top comparison page. Add FAQ blocks with FAQPage schema.",
            "Month 2: ship the first original data study or methodology guide. Plan two studies per year minimum.",
            "Month 3: start a quarterly expert opinion piece by the founder. Strong POV plus evidence."
        ],
        [
            "Three pitfalls: trying every format at once, padding for length instead of structuring for citation, and not tracking which formats actually earn citations.",
            "The cure is the monthly visibility audit. Track which formats appear in which LLM responses; double down on the winners.",
        ]
    ),
    "audit-ai-visibility-without-paying-profound": (
        "What does the monthly audit cadence look like over a year?",
        [
            "Month 1: build the prompt panel, run the first audit, document the baseline.",
            "Months 2 to 12: same panel, same operator, monthly cadence. Quarterly executive summary.",
            "Annual: refresh the prompt panel. LLM behaviour shifts; the prompts that mattered last year may not be the same."
        ],
        [
            "Three pitfalls: operator drift across months (different logged-in browsers), inconsistent prompts, and treating the audit as data without action.",
            "The cure is consistency and a documented action log. The audit only earns its place if it changes the content roadmap.",
        ]
    ),
    "g2-reddit-wikipedia-matter-more-than-blog": (
        "What does the off-site rollout look like over a year?",
        [
            "Quarter 1: launch the reviews programme. Monthly outreach. Dedicated review page on the domain.",
            "Quarter 2: Reddit credibility build. Pick subreddits; ramp participation.",
            "Quarter 3: PR campaign for the Wikipedia citations. Quarterly progress against the off-site budget split."
        ],
        [
            "Three pitfalls: trying Wikipedia first (impossible without press), running reviews programmes without responding to reviews, and hiding from Reddit because the community looks unfriendly.",
            "The cure is sequencing. Reviews and Reddit first; Wikipedia after the press placements compound.",
        ]
    ),

    "premium-pdp-audit-20-brands": (
        "What does a PDP rebuild look like over the next quarter?",
        [
            "Month 1: audit the top 5 PDPs against the 12 winning patterns. Pick the three weakest patterns per PDP.",
            "Month 2: rebuild and A/B test. Hero photography first; named social proof second; shipping transparency third.",
            "Month 3: roll winners across the catalogue. Document the patterns that worked for the brand specifically."
        ],
        [
            "Three pitfalls: rebuilding all PDPs at once, A/B testing trivial elements, and ignoring mobile-first design.",
            "The cure is sequencing and discipline. The PDP is the highest-leverage page; the work compounds across the rest of the funnel.",
        ]
    ),
    "ai-creative-100-variants-no-slop": (
        "What does the next 90 days look like for the creative pipeline?",
        [
            "Month 1: lock the brief discipline and brand-voice rule book. Build the prompt library. Cap initial output at 30 to 40 variants per week.",
            "Month 2: ramp to 60 to 80 variants per week. Senior creative director on every concept; junior editor on every batch.",
            "Month 3: review the win rate and the creative lifetime. Refine the brief; retire prompts that are not earning their place."
        ],
        [
            "Three pitfalls: starting with raw AI imagery, skipping the senior approval gate, and over-producing without a refresh cadence to consume the output.",
            "The cure is workflow discipline. The pipeline is a system; the AI tools are inputs.",
        ]
    ),
    "killing-ad-fatigue-2026-refresh-cadence": (
        "What does the next 90 days look like for refresh discipline?",
        [
            "Month 1: lock the per-platform refresh cadence into the team calendar. Production pipeline produces two weeks ahead of demand.",
            "Month 2: stand up the leading-indicator dashboard. CTR drift, frequency, CPA drift week over week.",
            "Month 3: quarterly retrospective. Which hooks survived longest? Which formats fatigued fastest?"
        ],
        [
            "Three pitfalls: refreshing in panic when CPA spikes, killing campaigns instead of rotating creative, and over-rotating top performers.",
            "The cure is the production pipeline. Without it, the refresh cadence is impossible; with it, the cadence becomes routine.",
        ]
    ),
    "saas-pricing-page-patterns-2026": (
        "What does a pricing-page rebuild look like over 90 days?",
        [
            "Month 1: audit the current page against the 12 patterns. Pick the two that fit your business.",
            "Month 2: build and A/B test the new structure. Measure trial start rate, demo request rate, plan-choice distribution.",
            "Month 3: lock the winner. Refresh FAQ. Add a customer testimonial inside the pricing block."
        ],
        [
            "Three pitfalls: testing too many patterns at once, A/B testing the prices themselves without legal review, and hiding the annual discount.",
            "The cure is ruthless prioritisation. Two patterns done well outperform six patterns done partially.",
        ]
    ),
    "founder-thought-leadership-saas-pipeline-system": (
        "What does a sustainable founder content rollout look like?",
        [
            "Month 1: hire or assign a content lead. Set the weekly call cadence. Draft the brand-voice rule book.",
            "Month 2: ship two long LinkedIn posts per week and the first monthly newsletter. Founder edits in 15 minutes.",
            "Month 3: layer in podcast guesting. Quarterly retrospective on what worked."
        ],
        [
            "Three pitfalls: founder tries to type, content lead drifts corporate, and the cadence slips when the founder gets busy.",
            "The cure is structural. Founder as source; content lead as typist; cadence non-negotiable.",
        ]
    ),
    "brand-building-performance-marketers-2026": (
        "What does the next quarter look like for a 20% brand reallocation?",
        [
            "Month 1: reallocate 20% of incremental budget toward brand. Founder content, podcast guesting, PR placements.",
            "Month 2: stand up the leading-indicator dashboard. Branded search velocity, direct traffic share, self-reported attribution.",
            "Month 3: present the trend to the CFO. Defend the spend with the trajectory."
        ],
        [
            "Three pitfalls: cutting brand at the first slow quarter, presenting weekly numbers when monthly trends matter, and outsourcing brand to a branding agency that delivers theatre.",
            "The cure is patience and discipline. The brand budget compounds; cuts compound the other way.",
        ]
    ),

    "clv-modelling-premium-d2c-cohort": (
        "What does the next quarter look like for cohort CLV maturity?",
        [
            "Month 1: build the model in a spreadsheet. 30, 90, 180, 365-day cohort views.",
            "Month 2: feed the model into the budget cycle. Per-channel cohort views inform paid allocation.",
            "Month 3: validate projections against six-month-old cohorts. Document the error. Refine the model."
        ],
        [
            "Three pitfalls: averaging cohorts into one number, ignoring returns, and over-extrapolating from 6 months to 5 years.",
            "The cure is documentation. The model has to be readable by future hires and defensible to the board.",
        ]
    ),
    "email-sms-premium-d2c-flows": (
        "What does a 90-day lifecycle rebuild look like?",
        [
            "Month 1: audit current flows. Identify which are stacked with discounts. Rewrite the welcome flow in premium voice.",
            "Month 2: rebuild browse and cart abandonment. Sparse, confident, brand-voice consistent.",
            "Month 3: ship the post-purchase and win-back flows. Quarterly creative refresh schedule locked in."
        ],
        [
            "Three pitfalls: keeping a discount in the welcome flow, daily sending in a premium account, and copying a mass-market template into the brand.",
            "The cure is brand-voice discipline. Slower cadence; sharper copy; higher LTV per send.",
        ]
    ),
    "nrr-saas-marketing-influences": (
        "What does marketing's first 90 days on NRR look like?",
        [
            "Month 1: define marketing-influenced NRR. Set the tracking rules with sales ops and customer success.",
            "Month 2: ship the first two expansion campaigns and the customer reference programme.",
            "Month 3: present marketing-influenced NRR contribution to the board. Trend across four quarters from there."
        ],
        [
            "Three pitfalls: arguing for NRR ownership instead of co-ownership, hiding marketing's contribution because attribution is fuzzy, and skipping the four-quarter trend.",
            "The cure is collaboration. Marketing co-owns with customer success; the trend matters more than the snapshot.",
        ]
    ),
    "saas-lifecycle-email-14-sequences": (
        "What does the 12-week build-out look like?",
        [
            "Weeks 1 to 4: ship the first three sequences (welcome, first-aha-moment, trial-end). Most of the lift lives here.",
            "Weeks 5 to 8: add stalled-at-step-X, invite-teammate, integration-installed, paywall-near.",
            "Weeks 9 to 12: complete the library with downgrade-risk, expansion, advocacy, renewal, win-back, reactivation."
        ],
        [
            "Three pitfalls: time-based flows masquerading as event-based, generic copy in every sequence, and no retrospective when sequences stop converting.",
            "The cure is event-mapping discipline and quarterly retros. The flows that worked last quarter may not be the same ones to ship this quarter.",
        ]
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
        if MARKER in body or slug not in EXTRA:
            return full
        h2_text, action_paras, pitfall_paras = EXTRA[slug]
        action_section = "\n".join([
            f'<!-- wp:heading --><h2 class="wp-block-heading">{h2_text}</h2><!-- /wp:heading -->',
        ] + [f'<!-- wp:paragraph --><p>{p}</p><!-- /wp:paragraph -->' for p in action_paras])
        pitfall_section = "\n".join([
            '<!-- wp:heading --><h2 class="wp-block-heading">What are the most common implementation pitfalls?</h2><!-- /wp:heading -->',
        ] + [f'<!-- wp:paragraph --><p>{p}</p><!-- /wp:paragraph -->' for p in pitfall_paras])
        injection = MARKER + "\n" + action_section + "\n" + pitfall_section
        # Insert before FAQ section if present, else before related-reading marker, else at end.
        faq_marker = '<!-- wp:heading --><h2 class="wp-block-heading">Frequently asked questions</h2>'
        related_marker = '<!-- bv-related-reading -->'
        if faq_marker in body:
            new_body = body.replace(faq_marker, injection + "\n" + faq_marker, 1)
        elif related_marker in body:
            new_body = body.replace(related_marker, injection + "\n" + related_marker, 1)
        else:
            new_body = body + "\n" + injection
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
    print(f"Injected action plan + pitfalls into {updated} posts.")


if __name__ == "__main__":
    main()
