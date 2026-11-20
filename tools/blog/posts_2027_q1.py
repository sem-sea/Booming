"""Lead-focused blog posts, batch January to February 2027.

Same rules and helpers as posts_2026_q4.py (geo-writer guide). Every
statistic was checked against its source on 20 November 2026.

Run: python3 tools/blog/posts_2027_q1.py -> writes tools/blog/out/<slug>.html
and prints the checks.
"""
import importlib.util
import json
import pathlib

_here = pathlib.Path(__file__).resolve().parent
_spec = importlib.util.spec_from_file_location("q4", _here / "posts_2026_q4.py")
q4 = importlib.util.module_from_spec(_spec)
_spec.loader.exec_module(q4)

SRC = q4.SRC
SRC.update({
    "fps_pricing": "https://firstpagesage.com/reports/geo-aeo-pricing-statistics/",
    "searchlab_price": "https://searchlab.nl/kosten/wat-kost-geo",
    "peec_pricing": "https://www.peec.ai/pricing",
    "nutshell_cost": "https://www.nutshell.com/blog/geo-costs",
    "ponemon_cyber": "https://ponemonsullivanreport.com/2026/06/the-state-of-cybersecurity-marketing-influence-2026-from-awareness-to-selection-how-buyers-evaluate-vendors/",
    "ahrefs_lists": "https://ahrefs.com/blog/best-lists-research/",
    "seer_listicles": "https://www.seerinteractive.com/insights/the-listicle-window-is-closing-in-ai-search-30-decline-mom",
    "ahrefs_factors": "https://ahrefs.com/blog/ai-brand-visibility-correlations",
    "seranking_llms": "https://seranking.com/blog/llms-txt/",
    "sej_ahrefs_llms": "https://searchenginejournal.com/97-of-llms-txt-files-got-no-requests-ahrefs-data-shows/579478",
    "ms_copilot_web": "https://support.microsoft.com/microsoft-365-copilot/how-web-search-works-in-microsoft-365-copilot-chat-and-agents",
    "bing_ai_perf": "https://blogs.bing.com/webmaster/February-2026/Introducing-AI-Performance-in-Bing-Webmaster-Tools-Public-Preview",
    "bing_ai_june": "https://blogs.bing.com/search/June-2026/New-AI-Visibility-Insights-in-Bing-Webmaster-Tools-Intents-Topics-Citation-Share-Compare",
    "gartner_cmo": "https://www.gartner.com/en/newsroom/press-releases/2026-11-09-gartner-survey-finds-42-percent-of-cmos-are-monitoring-brand-misinformation-in-ai-generated-content",
    "ser_raters": "https://www.seroundtable.com/google-search-quality-rater-guidelines-generative-ai-38842.html",
    "ser_spam_june": "https://seroundtable.com/google-june-2026-spam-update-done-41580.html",
})

a, p, h2, h3, cap, tldr, ol, ul, table, faq, cta = (
    q4.a, q4.p, q4.h2, q4.h3, q4.cap, q4.tldr, q4.ol, q4.ul, q4.table, q4.faq, q4.cta)
QS = q4.QS

POSTS = {}

# ---------------------------------------------------------------- 1 GEO pricing
POSTS["geo-pricing-what-generative-engine-optimization-costs"] = dict(
    title="What does generative engine optimization cost? GEO pricing models compared",
    excerpt="The median GEO retainer was $6,850 a month in a 2026 study of 1,146 quotes. What drives the price, which model fits, and what to check in a proposal.",
    category="ai-search", kw="GEO pricing",
    blocks=[
        tldr("The median GEO or AEO retainer was $6,850 a month in " + a("First Page Sage's October 2026 study", "fps_pricing") + " of 1,146 price quotes. The price follows scope: markets, languages, prompts tracked, and how much implementation and outreach the agency does itself."),
        p("GEO budgets are moving fast. First Page Sage collected 1,146 quotes from 214 agencies between July and October 2026. The median GEO retainer of $6,850 a month sits well above the median SEO retainer of $4,740, and up from $4,120 for GEO in early 2024 (" + a("First Page Sage", "fps_pricing") + "). First Page Sage is itself a GEO agency, so treat these figures as a market reference."),
        p("Prices in Europe can start lower. Dutch agency Searchlab lists AI visibility work from €1,500 a month, cancellable monthly (" + a("Searchlab", "searchlab_price") + "). Tools are the smallest line: " + a("Peec AI", "peec_pricing") + " charges $95 a month for 50 tracked prompts and $495 for 350."),
        p("The reason buyers pay at all: " + a("Forrester", "forrester") + " reports that 94% of business buyers use AI in their buying process. This guide shows what you pay for and how to compare proposals."),
        h2("What is GEO pricing based on?"),
        cap("GEO pricing is the fee for tracking, fixing and growing how AI assistants mention your brand, set mainly by scope, markets and implementation work."),
        p("Six things move the price. Most proposals bundle them, so ask for each line separately."),
        ol([
            "<strong>Prompts and assistants tracked.</strong> Running 50 prompts in three assistants costs far less than running 300 prompts in five.",
            "<strong>Markets and languages.</strong> Every language needs its own prompt set, its own pages and its own sources.",
            "<strong>Implementation.</strong> An agency that edits your pages and schema itself costs more than one that sends recommendations to your developers.",
            "<strong>Content production.</strong> New answer pages and comparison pages take writers and subject experts.",
            "<strong>Mentions on other sites.</strong> Digital PR, list placements and review programs are often the most expensive line, and often the most effective.",
            "<strong>Reporting and tools.</strong> Tracker licenses and monthly reporting time are usually included, but check whether you keep access to the data.",
        ]),
        h2("Which pricing models do GEO agencies use?"),
        cap("A GEO pricing model is the way an agency bills: a monthly retainer, a fixed-scope sprint, a one-off audit, hourly consulting or a performance fee."),
        table(
            ["Pricing model", "Share of agencies", "Typical range", "Median"],
            [
                ["Custom monthly retainer", "41%", "$3,000 to $15,000 a month", "$7,200 a month"],
                ["Tiered package retainer", "27%", "$2,500 to $25,000 a month", "$6,300 a month"],
                ["Project or sprint", "13%", "$5,000 to $40,000 per project", "$14,500"],
                ["One-time audit", "8%", "$1,500 to $12,000", "$4,300"],
                ["Hourly consulting", "6%", "$125 to $325 an hour", "$205 an hour"],
                ["Performance-based", "5%", "$1,500 to $4,000 base a month plus fees per result", "$2,600 base"],
            ],
            "Source: " + a("First Page Sage, GEO and AEO Pricing Statistics 2026", "fps_pricing") + ", 1,146 quotes from 214 agencies.",
        ),
        h2("How much should a B2B company budget?"),
        cap("A realistic GEO budget is the retainer for your company size plus tools, content and the internal hours needed to approve and publish changes."),
        p("By client size, First Page Sage found medians of $2,950 a month for small businesses, $7,400 for mid-market, $14,600 for upper mid-market and $31,500 for enterprise. Financial services had the highest industry median at $9,850 a month."),
        p("Add three costs that rarely appear in a proposal. Tool licenses, if you want your own access. Writer or expert time for new pages. And internal time: someone in your team has to approve changes, or nothing ships. Budget a few hours a week for that."),
        h2("What should a GEO proposal include?"),
        cap("A complete GEO proposal is one that names the prompts, assistants, monthly deliverables, owners of implementation and the reporting you keep after the contract ends."),
        ul([
            "The prompt set, or how it will be built in week one, in every language your buyers use.",
            "The assistants covered: ChatGPT, Perplexity, Gemini, Microsoft Copilot and Google AI Overviews.",
            "Named monthly deliverables, such as pages rewritten, mentions earned and reports.",
            "Who implements: the agency, your developers or both, with hours.",
            "Data ownership: prompt set, raw answers and dashboards stay with you.",
        ]),
        p("Be careful with proposals priced by the number of AI articles. Google's spam policies call using generative AI to create many pages without added value scaled content abuse (" + a("Google Search Central", "google_spam") + ")."),
        h2("Is a sprint cheaper than a retainer?"),
        cap("A GEO sprint is a fixed-scope project, usually 30 days, that buys a baseline and first fixes before you commit to a monthly retainer."),
        p("Per month, a sprint is often more expensive, because the first weeks carry the setup work. In total, it is cheaper than a retainer you stop after three months because you could not judge it. Our " + a("30-day AI visibility sprint plan", "/30-day-ai-visibility-sprint/") + " shows what a sprint should deliver week by week."),
    ] + faq([
        ("Why do GEO retainers cost more than SEO retainers?", "GEO adds work that SEO retainers rarely include: running and analyzing prompts across several assistants, and earning mentions on other sites. First Page Sage found a median of $6,850 a month for GEO against $4,740 for SEO."),
        ("Can we do GEO with tools only?", "Tools show where you appear. They do not rewrite pages, open crawler access or earn mentions. Many in-house teams start with a tracker such as Peec AI or Otterly.ai and buy help for the parts they cannot staff."),
        ("Are GEO prices lower in Europe?", "Some are. Searchlab in the Netherlands starts at €1,500 a month for AI visibility as a standalone line. Compare scope, not only price."),
        ("What does Booming Venture charge?", "We quote the 30-day AI visibility sprint at a fixed price after a short intake, then a monthly retainer if you continue. Our other service prices are on the services page."),
        ("How soon does GEO pay back?", "Assistants that search the live web can pick up improved pages within weeks. Track pipeline with a \"How did you hear about us?\" field that lists AI assistants, so you can see leads that never clicked."),
    ]) + [
        cta("Comparing GEO proposals? " + QS + " and we go through scope and price with you. Read " + a("how to choose a GEO agency", "/how-to-choose-a-geo-agency/") + " first, or see our " + a("services", "/services/") + "."),
    ],
)

# ---------------------------------------------------------------- 2 cybersecurity
POSTS["ai-visibility-cybersecurity-vendors"] = dict(
    title="AI visibility for cybersecurity vendors: how to make the shortlist when security buyers ask AI",
    excerpt="51% of security leaders use chatbots for product research. How cybersecurity vendors get named by ChatGPT, Copilot and Perplexity when buyers shortlist.",
    category="ai-search", kw="cybersecurity AI visibility",
    blocks=[
        tldr("In a June 2026 " + a("Ponemon Institute and NOLA study", "ponemon_cyber") + " of 320 enterprise security decision-makers, 51% used chatbots for product research. Security vendors get named in AI answers when peers, analysts and research reports name them first, and when their own pages answer technical questions in plain text."),
        p("Security buyers research differently from most B2B buyers. They are skeptical of marketing, check claims and lean on peers. The Ponemon study found peer recommendations the top source for vendor discovery at 55%, and research reports the strongest influence on final selection at 46% (" + a("Ponemon and NOLA", "ponemon_cyber") + ")."),
        p("AI now sits in the middle of that process: 51% of respondents use chatbots and 48% use AI overviews for product research. Microsoft Copilot deserves special attention, because " + a("Forrester", "forrester") + " found business buyers four times as likely as consumers to use it."),
        p("And when buyers ask for a shortlist, assistants lean on lists. In an " + a("Ahrefs study of 750 ChatGPT prompts", "ahrefs_lists") + ", \"best X\" lists made up 43.8% of the page types cited."),
        h2("What is AI visibility for a cybersecurity vendor?"),
        cap("AI visibility for a cybersecurity vendor is how often assistants like ChatGPT and Copilot name your product when security buyers ask for options."),
        p("It has two parts. Being named in shortlist answers, such as \"best XDR for mid-size banks\". And being described correctly when a buyer asks about you directly: certifications, deployment model, integrations and pricing approach."),
        h2("Why do security buyers trust some AI answers more than others?"),
        cap("Trust in an AI answer means the buyer can check it: security leaders still weigh peers, research reports and technical depth above any summary."),
        p("The same study found that 52% of security leaders say vendor marketing lacks technical depth, and 49% miss ROI justification. An AI answer built from vague product pages inherits that problem. An answer that cites your architecture page, your test results or an independent report gives the buyer something to verify."),
        h2("Which prompts should a security vendor track?"),
        cap("A security prompt set is a list of buyer questions by category, compliance need and company size, such as \"best XDR for mid-size banks\"."),
        table(
            ["Buying stage", "Example prompt", "Pages to build or influence"],
            [
                ["Problem", "How do we reduce alert fatigue in a small SOC?", "Practical guides with numbers from your own deployments"],
                ["Category", "What is the difference between EDR and XDR?", "A plain definition page with a comparison table"],
                ["Shortlist", "Best XDR for mid-size banks", "Independent best-of lists, review platforms, analyst notes"],
                ["Compliance", "Which tools help with NIS2 incident reporting?", "A page per regulation that maps features to obligations"],
                ["Validation", "Is [your company] ISO 27001 certified?", "A trust center with certifications in HTML text"],
            ],
            "A starting prompt set per buying stage.",
        ),
        h2("How do cybersecurity vendors get recommended?"),
        cap("Getting recommended means being named in the sources assistants read: analyst notes, peer reviews, best-of lists, research reports and your own technical pages."),
        ol([
            "<strong>Map 50 to 100 prompts</strong> across your categories, regulations and target sectors, in every language your buyers use.",
            "<strong>Publish technical answer pages.</strong> Architecture, integrations, deployment time and data residency, in HTML text and tables, not only in PDFs.",
            "<strong>Build a trust center.</strong> SOC 2, ISO 27001 and other certifications as text, with dates and scope.",
            "<strong>Publish original research.</strong> Research reports had the strongest direct effect on selection in the Ponemon study. Put the key findings on an open page so assistants can cite them; gate the full report if you must.",
            "<strong>Grow peer reviews</strong> on G2, Gartner Peer Insights and PeerSpot. Peers are the top discovery source.",
            "<strong>Get onto independent best-of lists</strong> for your category. Our guide on " + a("getting onto the lists AI assistants cite", "/get-on-best-of-lists-ai-assistants-cite/") + " covers how.",
            "<strong>Check crawler access.</strong> Security vendors often run strict firewalls. Make sure OAI-SearchBot, Claude-SearchBot, PerplexityBot and Bingbot get through. Our " + a("AI crawler checklist", "/ai-crawler-checklist-b2b-website/") + " takes 30 minutes.",
        ]),
    ] + faq([
        ("Does Microsoft Copilot matter for security vendors?", "Yes. Many enterprise security teams work inside Microsoft 365, and Forrester found business buyers four times as likely as consumers to use Copilot. Copilot searches Bing, so Bing indexing matters."),
        ("Should we gate our research reports?", "Gate the full PDF if leads depend on it, but publish the key findings, charts and method on an open HTML page. Assistants cannot cite what they cannot read."),
        ("Do review platforms help AI visibility?", "They are among the sources assistants read for shortlist questions, and peers were the top discovery source for security buyers in the Ponemon study. A steady flow of recent reviews helps both."),
        ("How do we know what AI says about us?", "Track a fixed prompt set monthly in a tool such as Otterly.ai or Peec AI, and check the AI Performance report in Bing Webmaster Tools for Copilot citations."),
        ("Can AI answers get our product wrong?", "Yes, especially on certifications and integrations. Publish those facts in plain text on your own site and correct outdated third-party pages where you can."),
    ]) + [
        cta("Selling security to European enterprises? " + QS + " and we check how ChatGPT, Copilot and Perplexity describe you today. See our " + a("services", "/services/") + "."),
    ],
)

# ---------------------------------------------------------------- 3 best-of lists
POSTS["get-on-best-of-lists-ai-assistants-cite"] = dict(
    title="How to get your company onto the \"best of\" lists AI assistants cite",
    excerpt="Best X lists made up 43.8% of the page types ChatGPT cited in an Ahrefs study. How B2B companies earn a place on the lists AI assistants read.",
    category="ai-search", kw="best of lists AI",
    blocks=[
        tldr("In an " + a("Ahrefs study of 750 ChatGPT prompts", "ahrefs_lists") + ", \"best X\" lists made up 43.8% of cited page types. Getting onto independent lists that are updated often is one of the most direct ways to show up when buyers ask AI for a shortlist."),
        p("When a buyer asks ChatGPT for the best tools or agencies in a category, the answer often borrows from existing rankings. Ahrefs found that 79.1% of 1,100 cited blog lists had been updated in 2025, and 26% in the two months before the study (" + a("Ahrefs", "ahrefs_lists") + "). Fresh lists win."),
        p("Authority matters less than you might expect: 35% of cited lists sat on low-authority domains. But lists are not a sure bet. " + a("Seer Interactive", "seer_listicles") + " measured a drop in ChatGPT citations of listicle URLs from 160,000 to 111,000 between December 2025 and January 2026, about 30%."),
        p("So lists are one channel among several. Buyers use them anyway: " + a("Forrester", "forrester") + " reports that 94% of business buyers use AI in their buying process."),
        h2("What is a best-of list in AI search?"),
        cap("A best-of list in AI search is a ranked comparison page, like \"best CRM tools\", that assistants read and summarize when buyers ask for options."),
        p("Lists come in several forms, and you control them to different degrees."),
        table(
            ["Type of list", "Who controls it", "Effort to get on it", "Watch out for"],
            [
                ["Trade media roundup", "Editor", "Medium: pitch with facts and a use case", "Updated once a year at most"],
                ["Review platform category page (G2, Capterra)", "Platform, ranked by reviews", "Ongoing: a steady flow of reviews", "Paid placements next to organic rankings"],
                ["Analyst or consultant list", "Analyst", "High: briefings and references", "Often behind a paywall"],
                ["Partner or integration directory", "Partner", "Low: complete your listing", "Narrow audience"],
                ["Your own comparison page", "You", "Low to write, high to keep fair", "Readers and assistants may discount it"],
            ],
            "Lists by how much you can influence them.",
        ),
        h2("Why do AI assistants rely on these lists?"),
        cap("List reliance means assistants borrow a shortlist someone already made: a ranked page answers \"which ones?\" faster than ten separate vendor sites."),
        p("A shortlist question has no single right answer, so assistants look for pages that already compare options. Find the lists they cite for your prompts, and you know where you need to be."),
        h2("How do you get onto the lists that matter?"),
        cap("Getting listed means finding the pages assistants already cite for your category, then giving their authors a reason and the facts to include you."),
        ol([
            "<strong>Run your shortlist prompts</strong> in ChatGPT, Perplexity, Gemini and Copilot, and write down every list they cite.",
            "<strong>Sort the lists</strong> by how often they appear, how fresh they are and how well they fit your segment.",
            "<strong>Check what each list needs</strong>: pricing, a use case, screenshots, customer proof. Most authors list what they compare.",
            "<strong>Prepare a one-page fact sheet</strong> with exactly those facts, so including you takes the author ten minutes.",
            "<strong>Pitch with a reason</strong>: a segment the list misses, a new feature, or a customer result the author can quote.",
            "<strong>Grow independent reviews</strong>, so review-ranked category pages move without a pitch.",
            "<strong>Follow up when lists refresh</strong>. Many authors update yearly; be in their inbox before that.",
        ]),
        h2("Should you publish your own best-of list?"),
        cap("A self-published list is a comparison on your own site that ranks you; it can get cited when it is fair, sourced and current."),
        p("Many companies do it. Of 250 \"best X software\" results pages Ahrefs checked in Google, 169 (67.6%) featured a list where the publisher ranked itself first. A first-party list also appeared in 34% of ChatGPT software answers that recommended the publisher."),
        p("If you publish one, keep it honest: real criteria, competitors described fairly, a date, and sources. A list that reads as an advert helps nobody, and buyers notice."),
    ] + faq([
        ("Can we pay to get onto lists?", "Some lists sell placements or earn affiliate fees. Disclosed sponsorship is legal, but buying links that pass ranking value breaks Google's spam policies. Earned placements on independent lists are the safer route."),
        ("How many lists should we target?", "Start with the five to ten lists that appear most often in your prompt results. Add more when new ones show up in your monthly tracking."),
        ("Are listicles losing value in AI search?", "Seer measured a 30% drop in ChatGPT listicle citations in one month. Treat lists as one channel next to reviews, media mentions and your own answer pages."),
        ("Do Dutch-language lists matter?", "They can. Assistants usually answer in the language of the question, and the sources they cite can differ by language. Track your Dutch prompts separately."),
        ("How do we know if a list placement worked?", "Rerun the same prompts monthly and track whether you are named and which sources are cited. Tools such as Otterly.ai or Peec AI automate this."),
    ]) + [
        cta("Want to know which lists AI assistants cite in your category? " + QS + " and we map them with you. See our " + a("services", "/services/") + ", or read " + a("how to choose a GEO agency", "/how-to-choose-a-geo-agency/") + "."),
    ],
)

# ---------------------------------------------------------------- 4 mentions vs backlinks
POSTS["brand-mentions-vs-backlinks-ai-visibility"] = dict(
    title="Brand mentions vs backlinks: what moves AI visibility for B2B brands",
    excerpt="Branded mentions correlated with AI Overview visibility at 0.664, backlinks at 0.218. What that means for B2B budgets, PR and link building.",
    category="ai-search", kw="brand mentions AI visibility",
    blocks=[
        tldr("Across 75,000 brands, branded web mentions correlated with AI Overview visibility at 0.664 and backlinks at 0.218 (" + a("Ahrefs, 2025", "ahrefs_mentions") + "). Links still help pages rank. AI visibility follows how often and where the web talks about you."),
        p("For twenty years, link building was the core of off-site SEO. AI search shifts the weight. In Ahrefs' May 2025 analysis, the three strongest factors for AI Overview visibility were all brand signals: branded web mentions (0.664), branded anchors (0.527) and branded search volume (0.392) (" + a("Ahrefs", "ahrefs_mentions") + ")."),
        p("A December 2025 follow-up across ChatGPT, Google AI Mode and AI Overviews found YouTube mentions the strongest single factor, at about 0.737, with Domain Rating far weaker (" + a("Ahrefs", "ahrefs_factors") + "). These are correlations, not proof of cause, but the pattern is consistent."),
        p("Brand perception in AI is now a board topic. " + a("Gartner", "gartner_cmo") + " found in November 2026 that 42% of CMOs actively monitor and correct misinformation about their brand in AI-generated output."),
        h2("What is a brand mention in AI search?"),
        cap("A brand mention is any place on the web that names your company, with or without a link: articles, reviews, forums, podcasts and video."),
        p("The link is optional. A trade article that names you as an example, a Reddit thread that recommends you, a podcast transcript or a review on G2 all count, linked or not."),
        h2("Why do mentions matter more than links for AI?"),
        cap("The mention effect means AI models link brands to topics through how often and where they are named, which backlink counts miss."),
        table(
            ["Compared on", "Brand mentions", "Backlinks"],
            [
                ["Correlation with AI Overview visibility (Ahrefs, 2025)", "0.664", "0.218"],
                ["What it signals", "Which topics your brand belongs to", "Which pages deserve authority"],
                ["How you earn it", "Research, PR, reviews, podcasts, communities", "Outreach, guest posts, digital PR"],
                ["Effect on Google rankings", "Indirect", "Direct"],
                ["Main risk", "Low when earned", "Bought links break Google's spam policies"],
            ],
            "Mentions and links do different jobs.",
        ),
        h2("How should B2B teams split budget between PR and links?"),
        cap("A sensible split is one that funds earned mentions first, through research, PR and reviews, and keeps link building for pages that must rank."),
        p("Most B2B teams can shift part of a link budget to mention-building; press coverage and quoted research often bring links as well. Six channels to use:"),
        ol([
            "<strong>Original research</strong> that journalists and analysts quote: a benchmark, a survey, an index.",
            "<strong>Expert commentary</strong> in trade media, such as columns and quotes in roundups.",
            "<strong>Podcasts and YouTube</strong>, given the strong YouTube correlation in the Ahrefs data.",
            "<strong>Reviews</strong> on the platforms your buyers use.",
            "<strong>Communities</strong> such as LinkedIn and relevant subreddits, with real answers, not promotion.",
            "<strong>Partner pages</strong>: integration directories, case studies on partner sites, joint webinars.",
        ]),
        h2("How do you measure mentions?"),
        cap("Measuring mentions means tracking named references across the web and in AI answers each month, then comparing your share against three main competitors."),
        p("Use a media monitoring tool for web mentions and an AI visibility tracker for answers. Report both as a share against the same competitors, so the trend means something. Our post on " + a("measuring brand lift from LLM mentions", "/measure-brand-lift-llm-mentions/") + " goes deeper."),
    ] + faq([
        ("Do unlinked mentions help SEO?", "Google has not confirmed a direct ranking effect. For AI visibility, the Ahrefs data shows branded mentions, linked or not, correlate far more strongly than backlinks."),
        ("Should we stop link building?", "No. Pages still need links to rank in Google, and Google results feed AI Overviews. Shift the balance, not the whole budget."),
        ("Do negative mentions hurt?", "They can shape how assistants describe you. Gartner found 42% of CMOs now monitor and correct misinformation in AI output. Track sentiment, not only volume."),
        ("Does YouTube matter for B2B?", "In the December 2025 Ahrefs study, YouTube mentions had the strongest correlation with AI visibility, at about 0.737. Talks, demos and podcast clips on YouTube count."),
        ("How long before mentions show up in AI answers?", "Assistants that search the web can pick up new mentions within weeks. Model knowledge from training updates only with new model versions."),
    ]) + [
        cta("Want to know where your competitors get mentioned and you do not? " + QS + " and we compare your share of voice. See our " + a("services", "/services/") + "."),
    ],
)

# ---------------------------------------------------------------- 5 llms.txt
POSTS["does-llms-txt-help-ai-visibility"] = dict(
    title="Does llms.txt help AI visibility? What the data says",
    excerpt="A 300,000-domain study found no link between llms.txt and AI citations, and 97% of files got no requests. What the data shows and what to do instead.",
    category="ai-search", kw="llms.txt",
    blocks=[
        tldr("No measurable effect so far: " + a("SE Ranking", "seranking_llms") + " found no correlation between llms.txt and AI citations across 300,000 domains (October 2026), and " + a("Ahrefs data", "sej_ahrefs_llms") + " showed 97% of llms.txt files got zero requests in May 2026. Keep one if it costs nothing. Spend the budget elsewhere."),
        p("Few GEO tactics spread as fast as llms.txt, and few have this little evidence behind them. SE Ranking found that 10.13% of the domains it studied had adopted the file. When the researchers removed llms.txt from their prediction model for AI citations, the model got more accurate, not less (" + a("SE Ranking", "seranking_llms") + ")."),
        p("Ahrefs looked at the demand side. Of about 38,000 domains with a valid file, only about 1,100 received any request in May 2026. AI retrieval bots linked to ChatGPT and Perplexity made 1.1% of the requests (" + a("Search Engine Journal on Ahrefs data", "sej_ahrefs_llms") + ")."),
        p("Meanwhile AI search keeps growing as a buying channel: " + a("Forrester", "forrester") + " reports that 94% of business buyers use AI in their buying process. The question is where your effort pays off."),
        h2("What is llms.txt?"),
        cap("llms.txt is a proposed text file at the root of a website that lists its key pages in Markdown so language models can find them."),
        p("It sits next to robots.txt and sitemap.xml. Unlike those, there is no public evidence that AI search crawlers use it to choose sources."),
        h2("Does llms.txt improve AI citations?"),
        cap("The llms.txt effect is, on current evidence, zero: a 300,000-domain study found no link between having the file and being cited by AI assistants."),
        table(
            ["Measure", "Finding", "Source"],
            [
                ["Domains studied", "About 300,000", "SE Ranking, October 2026"],
                ["Domains with llms.txt", "10.13%", "SE Ranking"],
                ["Correlation with AI citations", "None found", "SE Ranking"],
                ["Domains in log sample", "137,000", "Ahrefs, May 2026"],
                ["Valid files with zero requests", "97%", "Ahrefs"],
                ["Requests from ChatGPT and Perplexity retrieval bots", "1.1%", "Ahrefs"],
                ["Requests from SEO audit tools", "21%", "Ahrefs"],
            ],
            "What two large datasets show about llms.txt.",
        ),
        h2("Why do so many sites still publish one?"),
        cap("Publishing llms.txt is a low-cost bet: it takes an hour, does no measurable harm, and may matter if AI companies adopt the standard later."),
        p("We publish one ourselves, generated automatically by our WordPress theme, because it costs nothing to keep. There is one real audience: coding agents made 10% of requests in the Ahrefs data. If you have developer documentation, a clean llms.txt helps those tools."),
        h2("What should you do instead?"),
        cap("The better investment is what assistants do read: crawlable HTML, answer-first pages, structured data, fresh facts and mentions on other sites."),
        ol([
            "<strong>Allow the search crawlers</strong>: OAI-SearchBot, Claude-SearchBot, PerplexityBot and Bingbot.",
            "<strong>Serve full HTML</strong>, so crawlers that do not run JavaScript see your text.",
            "<strong>Lead each section with the answer</strong>, in two sentences under a question-shaped heading.",
            "<strong>Put key facts in text and tables</strong>, not in images or PDFs.",
            "<strong>Earn mentions on other sites</strong>; branded mentions correlated with AI Overview visibility at 0.664 in " + a("Ahrefs' 2025 study", "ahrefs_mentions") + ".",
            "<strong>Submit your sitemap to Bing</strong>, the index behind Microsoft Copilot.",
        ]),
        p("Our " + a("AI crawler checklist", "/ai-crawler-checklist-b2b-website/") + " turns the first four into a 30-minute test."),
    ] + faq([
        ("Does Google use llms.txt?", "Googlebot made 13% of llms.txt requests in the Ahrefs data, but a request is not use. No study has shown a ranking or citation effect from the file."),
        ("Should we remove our llms.txt?", "No need. It does no measurable harm. Just do not count it as GEO work."),
        ("What about llms-full.txt?", "The same applies: no published evidence links it to AI citations."),
        ("Could llms.txt matter in the future?", "It could, if AI companies adopt it. Keeping a simple, accurate file is cheap insurance."),
        ("Who reads llms.txt today?", "Mostly SEO audit tools, unidentified bots, Googlebot and coding agents, according to the Ahrefs data. AI search assistants made a small share of requests."),
    ]) + [
        cta("Want to know what actually moves your AI visibility? " + QS + " and we look at the signals that matter. See our " + a("services", "/services/") + "."),
    ],
)

# ---------------------------------------------------------------- 6 Copilot and Bing
POSTS["microsoft-copilot-bing-b2b-ai-visibility"] = dict(
    title="Microsoft Copilot and Bing: the AI visibility channel B2B marketers overlook",
    excerpt="Business buyers are four times as likely as consumers to use Copilot, and Copilot searches Bing. How to track and improve your Copilot citations.",
    category="ai-search", kw="Copilot visibility",
    blocks=[
        tldr(a("Forrester", "forrester") + " found business buyers four times as likely as consumers to use Microsoft Copilot (2026). Copilot sends its web queries to Bing, and since February 2026 Bing Webmaster Tools shows how often Copilot cites your pages."),
        p("Most GEO advice starts with ChatGPT. For B2B, that leaves out the assistant built into the software your buyers use all day. Forrester also found that 61% of business buyers use private AI tools provided by their organization (" + a("Forrester", "forrester") + ")."),
        p("Copilot's web answers run on Bing. Microsoft says Copilot generates a short query from your prompt and sends it to Bing, and a Sources button shows the query and the sources used (" + a("Microsoft Support", "ms_copilot_web") + ")."),
        p("And Microsoft now shares the data. Since 10 February 2026, the AI Performance report in Bing Webmaster Tools shows citations in Copilot, AI summaries in Bing and select partners (" + a("Bing Webmaster Blog", "bing_ai_perf") + ")."),
        h2("What is Copilot visibility?"),
        cap("Copilot visibility is how often Microsoft Copilot cites or names your company in answers, which depends largely on how Bing indexes and ranks your pages."),
        p("Bing has its own index. A page that ranks in Google is not automatically indexed or ranked in Bing, so Copilot can miss you even when Google does not."),
        h2("Why does Copilot matter more in B2B than in consumer markets?"),
        cap("Copilot's B2B weight means it sits inside Microsoft 365, where many business buyers already work, so research happens next to email, documents and Teams."),
        p("A buyer preparing a vendor comparison in Word or a summary for a buying committee can ask Copilot without opening a browser. In organizations that allow web search in Copilot Chat, those answers draw on Bing."),
        h2("How do you see your Copilot citations?"),
        cap("Seeing Copilot citations means using the AI Performance report in Bing Webmaster Tools, which counts citations, cited pages and grounding queries for your site."),
        table(
            ["Metric", "What it shows", "Added"],
            [
                ["Total Citations", "How often your site is shown as a source in AI answers", "February 2026"],
                ["Average Cited Pages", "Daily average of your unique pages cited", "February 2026"],
                ["Grounding queries", "A sample of the phrases the AI used to retrieve your content", "February 2026"],
                ["Intents", "Grounding queries grouped by intent, such as Commercial or Research", "June 2026"],
                ["Topics", "Grounding queries grouped into themes", "June 2026"],
                ["Citation Share", "Your share of all citations for a grounding query", "June 2026"],
                ["Compare", "The current period set against an earlier one", "June 2026"],
            ],
            "Sources: " + a("Bing, February 2026", "bing_ai_perf") + " and " + a("Bing, June 2026", "bing_ai_june") + ". Microsoft notes the report shows citations, not ranking.",
        ),
        h2("How do you improve visibility in Copilot?"),
        cap("Improving Copilot visibility means getting indexed and ranked well in Bing: verified site, fresh sitemap, IndexNow, crawlable HTML and pages that answer business questions."),
        ol([
            "<strong>Verify your site in Bing Webmaster Tools.</strong> You can import it from Google Search Console in a few clicks.",
            "<strong>Submit your XML sitemap</strong> and check that the important pages are indexed.",
            "<strong>Turn on IndexNow</strong>, so Bing hears about new and updated pages right away.",
            "<strong>Check that Bingbot is not blocked</strong> by robots.txt, a firewall or rate limiting.",
            "<strong>Read your grounding queries</strong> and map each one to the page that should answer it.",
            "<strong>Strengthen the pages behind commercial intents</strong>: comparisons, pricing, integrations, case studies.",
            "<strong>Track monthly with Compare</strong>, and add Copilot to your prompt tracking tool.",
        ]),
        p("Our " + a("30-day AI visibility sprint", "/30-day-ai-visibility-sprint/") + " includes Copilot from week one."),
    ] + faq([
        ("Is Copilot the same as Bing Chat?", "Microsoft renamed Bing Chat to Copilot in late 2023. Today Copilot appears in Windows, Edge, Bing and Microsoft 365."),
        ("Does Microsoft 365 Copilot Chat use the web?", "Yes, when web search is turned on. Administrators can turn it off for their organization, and users can switch it off in their settings."),
        ("Does the AI Performance report show rankings?", "No. Microsoft says it shows how often content is cited, not ranking, authority or placement within an answer."),
        ("Does a good Google ranking carry over to Bing?", "Not automatically. Bing crawls and ranks with its own systems, so check indexing in Bing Webmaster Tools separately."),
        ("Can we track private Copilot use by buyers?", "Not directly. Buyers who read a Copilot answer and never click leave no trace on your site. A \"How did you hear about us?\" field that lists AI assistants is the practical proxy."),
    ]) + [
        cta("Not sure how Copilot describes your company? " + QS + " and we check your Bing and Copilot visibility with you. See our " + a("services", "/services/") + "."),
    ],
)

# ---------------------------------------------------------------- 7 CMO metrics
POSTS["ai-visibility-metrics-for-the-board"] = dict(
    title="AI visibility metrics for the board: five numbers a CMO can defend",
    excerpt="AI answers send fewer clicks, so traffic alone undersells marketing. Five AI visibility metrics a B2B CMO can report to the board, with sources and limits.",
    category="marketing-analytics", kw="AI visibility metrics",
    blocks=[
        tldr("61% of CMOs have changed their content strategy for AI visibility (" + a("Gartner, 2026", "gartner_cmo") + "), yet clicks keep falling: " + a("Pew", "pew") + " measured 8% click-through when Google showed an AI summary, against 15% without. Report five numbers: share of voice, citation share, answer accuracy, branded demand and AI-sourced pipeline."),
        p("Traffic used to be the easy proof that content worked. AI answers break that link. In " + a("Ahrefs' December 2025 data", "ahrefs_ctr") + ", an AI Overview lowered the click-through rate of the top Google result by 58%."),
        p("Boards still want a number. Gartner surveyed 408 senior marketing leaders in July and August 2026: 61% had adapted content for machine readability and AI visibility, and 42% monitor and correct misinformation about their brand in AI output (" + a("Gartner", "gartner_cmo") + ")."),
        p("Buyers have already moved: " + a("Forrester", "forrester") + " reports that 94% of business buyers use AI in their buying process. Here is how to report on it without vanity metrics."),
        h2("What is an AI visibility metric?"),
        cap("An AI visibility metric is a repeatable number that shows how AI assistants present your brand, measured on a fixed prompt set each month."),
        p("Repeatable is the key word. AI answers vary between runs and users, so a single screenshot proves nothing. A fixed set of prompts, run on a schedule, gives you a trend."),
        h2("Which five metrics belong in a board report?"),
        cap("A board-ready AI metric is one that links to revenue or risk: share of voice, citation share, answer accuracy, branded demand and AI-sourced pipeline."),
        table(
            ["Metric", "Definition", "Data source", "Why the board cares"],
            [
                ["AI share of voice", "Share of tracked prompts where you are named, against three competitors", "Otterly.ai, Peec AI or similar", "Presence on buyer shortlists"],
                ["Citation share", "Your share of all citations for a query", "Bing Webmaster Tools, AI Performance", "Your pages as the source of truth"],
                ["Answer accuracy", "Share of answers that describe product, pricing and positioning correctly", "Monthly review of tracked answers", "Brand and sales risk"],
                ["Branded demand", "Trend in branded search impressions and direct visits", "Google Search Console, Bing, analytics", "Awareness that AI answers create"],
                ["AI-sourced pipeline", "Pipeline from leads who name an AI assistant as source", "CRM with a self-reported source field", "The link to revenue"],
            ],
            "Five metrics, each with a data source you can audit.",
        ),
        h2("How do you connect AI visibility to pipeline?"),
        cap("Connecting AI visibility to pipeline means combining a self-reported source field, CRM opportunity data and branded demand, since most AI answers send no click."),
        p("Add \"How did you hear about us?\" to every form and sales intake, with ChatGPT, Perplexity, Copilot and Gemini as options. Tag those opportunities in the CRM. Then report pipeline and win rate for that group next to share of voice. Our " + a("ROI forecaster", "/roi-forecaster/") + " helps translate the numbers into a business case."),
        h2("What should a CMO not report?"),
        cap("A vanity AI metric is a number that moves without affecting buyers, such as mentions on irrelevant prompts or a vendor score without raw answers."),
        ul([
            "Mention counts on prompts no buyer asks.",
            "A single visibility score from a tool, without the answers behind it.",
            "Screenshots of one good answer.",
            "Traffic from AI referrals alone; most AI influence never clicks.",
        ]),
    ] + faq([
        ("How often should we report AI visibility?", "Monthly to the marketing team, quarterly to the board. Monthly data shows trends; quarterly reporting smooths out the noise of varying answers."),
        ("How many prompts do we need?", "Usually 50 to 150 per market, covering problem questions, solution comparisons and shortlist questions in every language your buyers use."),
        ("How do we handle answers that change between runs?", "Run each prompt several times per period and report the share of runs where you appear. Trends over months matter more than any single run."),
        ("Can we benchmark against competitors?", "Yes. Track the same prompts for three main competitors and report share of voice as a percentage of all mentions."),
        ("What is a good share of voice?", "There is no universal benchmark. Compare against your direct competitors and track the direction over time."),
    ]) + [
        cta("Need AI visibility numbers your board will accept? " + QS + " and we set up the five metrics with you. See our " + a("services", "/services/") + ", or read about the " + a("30-day AI visibility sprint", "/30-day-ai-visibility-sprint/") + "."),
    ],
)

# ---------------------------------------------------------------- 8 AI content and Google
POSTS["ai-written-content-google-spam-policies"] = dict(
    title="AI-written content and Google: what the spam policies actually allow",
    excerpt="Google does not ban AI content. It penalizes many pages made without added value. What the spam policies, rater guidelines and June 2026 update mean for B2B.",
    category="ai-marketing", kw="AI content Google",
    blocks=[
        tldr(a("Google's spam policies", "google_spam") + " name using generative AI to create many pages without adding value as scaled content abuse. AI-assisted content is allowed; volume without original value is the risk. Google's June 2026 spam update rolled out worldwide in two days (" + a("Search Engine Roundtable", "ser_spam_june") + ")."),
        p("Many B2B marketing teams now use AI to draft content, and many publish more than ever. Google's line is clear in its own documentation: what counts is whether pages help people, not how they were made. Its guidance on generative AI content asks site owners to check accuracy and add value (" + a("Google Search Central", "google_genai") + ")."),
        p("Google's human quality raters follow the same logic. Since the January 2025 update to their guidelines, pages made with \"little to no effort, little to no originality, and little to no added value\" get the Lowest rating, and using automated tools as a low-effort way to produce many pages is named as an example (" + a("Search Engine Roundtable", "ser_raters") + ")."),
        p("Enforcement comes in waves. The June 2026 spam update started on 24 June and finished on 26 June, applied to all languages, and Google said recovery can take many months (" + a("Search Engine Roundtable", "ser_spam_june") + ")."),
        h2("Does Google penalize AI-generated content?"),
        cap("Google's position is that AI use is not the problem: content made at scale to rank, without added value, breaks its spam policies."),
        p("An AI-drafted article that an expert has checked, extended with real examples and kept current is judged on the same quality signals as any other article. A hundred AI-generated pages that rephrase what already ranks are what the policies target."),
        h2("What is scaled content abuse?"),
        cap("Scaled content abuse is Google's term for generating many pages mainly to manipulate rankings, whether by AI, scraping or people, without helping users."),
        table(
            ["Signal", "Lower risk", "Higher risk"],
            [
                ["Publishing pace", "What your experts can review", "Dozens of posts a week from a small team"],
                ["Topic fit", "Topics your buyers ask and you know", "Any topic with search volume"],
                ["Facts", "Every number has a source", "Unsourced or invented statistics"],
                ["Experience", "Your own examples, data and opinions", "Rephrased summaries of top results"],
                ["Authorship", "Named author with relevant background", "No author, or a generic brand byline"],
                ["Maintenance", "Pages updated or removed when outdated", "Published once, never touched again"],
            ],
            "Patterns that raise or lower the risk of being judged as scaled content.",
        ),
        h2("How do raters judge AI-assisted pages?"),
        cap("Rater judgment means human reviewers score pages for effort, originality and added value; copied or auto-generated pages with little of each get the Lowest rating."),
        p("Rater scores do not change rankings directly. Google uses them to test whether its ranking systems work. But they show what Google wants those systems to reward. The guidelines also say generative AI can support high-quality work; the tool itself does not set the rating."),
        h2("How should a B2B team use AI for content safely?"),
        cap("Using AI safely means a human owns each page: AI drafts, while an expert adds experience, checks every fact and decides what gets published."),
        ol([
            "<strong>Start from a buyer question</strong>, not a keyword list.",
            "<strong>Write the brief with a first-hand angle</strong>: a project, a number, an opinion only your team has.",
            "<strong>Let AI draft and research</strong> structure, definitions and sources.",
            "<strong>Have an expert edit</strong> and add examples, data and judgment.",
            "<strong>Check every number</strong> against its original source and link it.",
            "<strong>Publish under a named author</strong> with relevant experience.",
            "<strong>Set a pace you can review</strong>, and update or remove posts that no longer help anyone.",
        ]),
        p("If you publish in the EU, the same editorial control also covers you under the AI Act. Our post on " + a("the EU AI Act for marketing teams", "/eu-ai-act-b2b-marketing-teams/") + " explains when AI-written text needs a label."),
    ] + faq([
        ("Do we need to label AI-written blog posts?", "Not for Google's spam policies. Under the EU AI Act, a label is needed only for AI text on matters of public interest that no human reviewed under editorial responsibility."),
        ("How many posts a week is safe?", "Google gives no number. Publish what your experts can review properly. Quality and topic fit count, not volume."),
        ("Can a site recover after a spam update?", "Yes, but Google says it can take many months. Improve or remove low-value pages and keep publishing helpful content."),
        ("Should we delete old AI-written posts?", "Review them. Improve posts that serve your buyers, merge thin ones, and remove or noindex those that add nothing."),
        ("Does Google's guidance apply to AI search answers too?", "AI Overviews and AI Mode draw on Google's index, so pages that fall in rankings also lose visibility there."),
    ]) + [
        cta("Using AI for content and want it to hold up in Google and AI search? " + QS + " and we review your workflow. See our " + a("services", "/services/") + "."),
    ],
)

SCHEDULE = [
    ("geo-pricing-what-generative-engine-optimization-costs", "2027-01-05T07:30:00", 246),
    ("get-on-best-of-lists-ai-assistants-cite", "2027-01-12T07:30:00", 251),
    ("ai-visibility-cybersecurity-vendors", "2027-01-19T07:30:00", 248),
    ("brand-mentions-vs-backlinks-ai-visibility", "2027-01-26T07:30:00", 254),
    ("does-llms-txt-help-ai-visibility", "2027-02-02T07:30:00", 261),
    ("microsoft-copilot-bing-b2b-ai-visibility", "2027-02-09T07:30:00", 262),
    ("ai-visibility-metrics-for-the-board", "2027-02-16T07:30:00", 264),
    ("ai-written-content-google-spam-policies", "2027-02-23T07:30:00", 280),
]
CATS = {"ai-search": 35, "ai-marketing": 2, "marketing-analytics": 22}

if __name__ == "__main__":
    out = q4.OUT
    out.mkdir(exist_ok=True)
    pay = pathlib.Path("/tmp/claude-0/-home-claude/b1c26679-a08f-5463-946e-707c8c5a96ab/scratchpad/payloads_q1")
    pay.mkdir(exist_ok=True)
    for slug, date_gmt, media in SCHEDULE:
        post = POSTS[slug]
        content = "\n".join(post["blocks"])
        (out / f"{slug}.html").write_text(content)
        body = {"title": post["title"], "slug": slug, "status": "future", "date_gmt": date_gmt,
                "content": content, "excerpt": post["excerpt"], "categories": [CATS[post["category"]]],
                "featured_media": media, "author": 1, "comment_status": "closed",
                "meta": {"_yoast_wpseo_metadesc": post["excerpt"], "_yoast_wpseo_focuskw": post["kw"]}}
        (pay / f"{slug}.json").write_text(json.dumps(body, ensure_ascii=False))
        print(slug, len(post["excerpt"]), json.dumps(q4.check(slug, post)))
