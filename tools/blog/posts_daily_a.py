"""Daily posts, 21 November to 20 December 2026 (batch A: 9 posts).

Same rules and helpers as posts_2026_q4.py (geo-writer guide). Every
statistic and every source URL was opened and checked on 20 November 2026.
"""
import importlib.util
import pathlib

_here = pathlib.Path(__file__).resolve().parent
_spec = importlib.util.spec_from_file_location("q4", _here / "posts_2026_q4.py")
q4 = importlib.util.module_from_spec(_spec)
_spec.loader.exec_module(q4)

SRC = q4.SRC
SRC.update({
    "techcrunch_900m": "https://techcrunch.com/2026/02/27/chatgpt-reaches-900m-weekly-active-users",
    "leadfeeder_ai": "https://www.leadfeeder.com/resources/studies/ai-referrals-in-b2b/",
    "orbit_ai": "https://www.orbitmedia.com/blog/conversion-rates-ai-search/",
    "demandbase_ppc": "https://ppc.land/chatgpt-referrals-to-b2b-sites-gain-303-in-a-year-demandbase-finds/",
    "ahrefs_fresh": "https://ahrefs.com/blog/do-ai-assistants-prefer-to-cite-fresh-content/",
    "muckrack_may26": "https://media.muckrack.com/documents/What_Is_AI_Reading__May_2026.pdf",
    "muckrack_dec25": "https://www.globenewswire.com/news-release/2026/03/30/3264743/0/en/New-Research-Finds-Earned-Media-Accounts-for-25-of-All-Large-Language-Model-Citations.html",
    "cjr_tow": "https://www.cjr.org/tow_center/we-compared-eight-ai-search-engines-theyre-all-bad-at-citing-news.php",
    "ebu_study": "https://www.ebu.ch/news/2025/10/ai-s-systemic-distortion-of-news-is-consistent-across-languages-and-territories-international-study-by-public-service-broadcaste",
    "trustradius_2026": "https://hginsights.com/news/trustradius-2026-b2b-buying-disconnect-report-reveals-ai-has-changed-how-buyers-research-but-not-what-they-trust/",
    "emarketer_pricing": "https://www.emarketer.com/content/b2b-tech-buyers-crave-pricing-transparency",
    "ahrefs_mention_types": "https://ahrefs.com/blog/mention-types-ai-visibility/",
    "meltwater_linkedin": "https://www.meltwater.com/en/blog/linkedin-ai-visibility-study",
    "ahrefs_top1000": "https://ahrefs.com/blog/chatgpts-most-cited-pages",
    "gartner_validate": "https://www.gartner.com/en/newsroom/press-releases/2026-05-20-gartner-survey-finds-sixty-nine-percent-of-b-two-b-buyers-turn-to-sales-reps-to-validate-ai-generated-insights",
    "gartner_repfree": "https://www.gartner.com/en/newsroom/press-releases/2026-03-09-gartner-sales-survey-finds-67-percent-of-b2b-buyers-prefer-a-rep-free-experience",
    "google_aio_eu": "https://blog.google/feed/were-bringing-the-helpfulness-of-ai-overviews-to-more-countries-in-europe/",
    "google_aio_nl": "https://blog.google/intl/nl-nl/product/zoeken-kijken/googlen-is-nu-nog-eenvoudiger-met-ai-overzichten-in-het-nederlands/",
    "geo_paper_html": "https://arxiv.org/html/2311.09735v3",
    "google_sd_intro": "https://developers.google.com/search/docs/appearance/structured-data/intro-structured-data?hl=en",
    "sel_canel_schema": "https://searchengineland.com/microsoft-bing-copilot-use-schema-for-its-llms-453455",
    "profound_pricing": "https://www.tryprofound.com/blog/making-answer-engine-optimization-accessible-to-every-business",
    "otterly_plans": "https://help.otterly.ai/pricing-of-otterlyai",
    "peec_pricing": "https://www.peec.ai/pricing",
    "nutshell_cost": "https://www.nutshell.com/blog/geo-costs",
    "bing_ai_perf": "https://blogs.bing.com/webmaster/February-2026/Introducing-AI-Performance-in-Bing-Webmaster-Tools-Public-Preview",
    "ponemon_cyber": "https://ponemonsullivanreport.com/2026/06/the-state-of-cybersecurity-marketing-influence-2026-from-awareness-to-selection-how-buyers-evaluate-vendors/",
    "gartner_cmo": "https://www.gartner.com/en/newsroom/press-releases/2026-11-09-gartner-survey-finds-42-percent-of-cmos-are-monitoring-brand-misinformation-in-ai-generated-content",
    "ahrefs_lists": "https://ahrefs.com/blog/best-lists-research/",
})

a, p, h2, h3, cap, tldr, ol, ul, table, faq, cta = (
    q4.a, q4.p, q4.h2, q4.h3, q4.cap, q4.tldr, q4.ol, q4.ul, q4.table, q4.faq, q4.cta)
QS = q4.QS
POSTS = {}

# ------------------------------------------------------------------ ChatGPT search
POSTS["how-chatgpt-search-picks-sources-b2b"] = dict(
    title="How ChatGPT search picks sources: a guide for B2B marketers",
    excerpt="ChatGPT sends 81% of AI referral visits to B2B sites. How ChatGPT search finds and cites pages, and what B2B teams should fix first.",
    category="ai-search", kw="ChatGPT search",
    blocks=[
        tldr("ChatGPT reached 900 million weekly active users in February 2026 (" + a("TechCrunch", "techcrunch_900m") + ") and sends 81% of AI referral visits to B2B websites (" + a("Leadfeeder, 2026", "leadfeeder_ai") + "). To get cited, your pages must be open to OAI-SearchBot, readable without JavaScript and direct in how they answer."),
        p("For most B2B companies, ChatGPT is the only AI assistant that sends real traffic. In Leadfeeder's October 2026 study, ChatGPT accounted for 81% of AI referral visits to B2B sites, ahead of Gemini at 8% and Claude at 5% (" + a("Leadfeeder", "leadfeeder_ai") + ")."),
        p("Those visits convert. Orbit Media analyzed 97 B2B and lead generation sites and found ChatGPT visitors converted at about 2.1%, against 0.5% for Google search, although Google still sent around 100 times more visits (" + a("Orbit Media", "orbit_ai") + ")."),
        p("Volume is rising too. Demandbase measured a 303% increase in monthly ChatGPT referrals to B2B sites between June 2025 and June 2026 (" + a("PPC Land on Demandbase data", "demandbase_ppc") + ")."),
        h2("What is ChatGPT search?"),
        cap("ChatGPT search is the feature that lets ChatGPT look up current web pages and cite them as linked sources inside its answers."),
        p("It relies on OpenAI's own crawler. OpenAI says sites that opt out of OAI-SearchBot will not appear in ChatGPT search answers, though they can still show up as navigational links (" + a("OpenAI", "openai_bots") + ")."),
        h2("How does ChatGPT choose which pages to cite?"),
        cap("ChatGPT's source choice means picking pages it can crawl, that match the question closely and that look current; OpenAI does not publish a ranking formula."),
        p("Freshness is one factor you can measure. In an Ahrefs study of 17 million citations, ChatGPT showed the strongest preference for newer content of all assistants tested: its citations were on average 458 days newer than Google's organic results (" + a("Ahrefs, 2025", "ahrefs_fresh") + ")."),
        p("Citations are routine, not rare. Muck Rack found ChatGPT includes citations in 96% of responses, about five per answer, with Wikipedia as its most cited domain (" + a("Muck Rack, May 2026", "muckrack_may26") + ")."),
        h2("What should B2B teams fix first?"),
        cap("The first fix for ChatGPT visibility is access: allow OAI-SearchBot, serve full HTML, and make sure your key pages answer buyer questions directly."),
        ol([
            "<strong>Allow OAI-SearchBot</strong> in robots.txt and in your firewall or CDN settings.",
            "<strong>Decide on GPTBot separately.</strong> It collects training data. OpenAI says each crawler setting is independent of the others.",
            "<strong>Serve full HTML.</strong> In " + a("Vercel's analysis", "vercel") + ", none of the major AI crawlers rendered JavaScript.",
            "<strong>Answer buyer questions on the page</strong>: a question-shaped heading, then the answer in two sentences.",
            "<strong>Refresh your key pages</strong> with new facts and a visible update date.",
            "<strong>Earn mentions on the sites ChatGPT already cites</strong> for your category: review platforms, trade media and lists.",
        ]),
        h2("How do you measure ChatGPT visibility?"),
        cap("Measuring ChatGPT visibility means tracking two numbers: how often you are named for buyer prompts, and how many visits and leads ChatGPT sends."),
        table(
            ["Metric", "Where to find it", "Why it matters"],
            [
                ["Mention rate for buyer prompts", "A prompt tracker such as Peec AI or Otterly.ai", "Presence on shortlists"],
                ["Referral sessions from chatgpt.com", "Google Analytics 4", "Traffic you can attribute"],
                ["Leads from ChatGPT sessions", "GA4 plus your CRM", "The link to pipeline"],
                ["Pages that get cited", "Source lists in your prompt tracker", "Which pages to copy and improve"],
            ],
            "Four numbers to track every month.",
        ),
    ] + faq([
        ("Does blocking GPTBot remove us from ChatGPT search?", "No. OpenAI says the crawler settings are independent, so you can allow OAI-SearchBot for search while disallowing GPTBot for training."),
        ("Is ChatGPT traffic worth the effort for B2B?", "It is small but valuable. Orbit Media found it converts about four times better than Google search, while Leadfeeder found a slightly lower share of visitors who match the ideal customer profile than organic search."),
        ("How fast do changes show up in ChatGPT?", "Pages that OAI-SearchBot can crawl can be picked up within weeks. Knowledge from model training changes only with new model versions."),
        ("Do we need a separate ChatGPT strategy?", "Mostly not. Crawlable pages, clear answers and mentions on trusted sites help in ChatGPT, Perplexity and Google AI features alike."),
        ("Which pages does ChatGPT cite most?", "In Muck Rack's data, Wikipedia is its most cited domain. For your category, run your buyer prompts and record the sources yourself."),
    ]) + [
        cta("Want to know how ChatGPT answers your buyers' questions today? " + QS + ". See our " + a("services", "/services/") + ", or read " + a("how to get cited by ChatGPT for B2B SaaS queries", "/get-cited-by-chatgpt-b2b-saas-queries/") + "."),
    ],
)

# ------------------------------------------------------------------ tools
POSTS["ai-visibility-tools-compared-peec-otterly-profound"] = dict(
    title="AI visibility tools compared: Peec AI, Otterly.ai, Profound and Bing Webmaster Tools",
    excerpt="Peec AI starts at $95 a month and Profound at $99. A practical comparison of AI visibility trackers for B2B teams, plus the free Bing option.",
    category="ai-search", kw="AI visibility tools",
    blocks=[
        tldr("You can start tracking AI visibility for less than $100 a month: " + a("Peec AI", "peec_pricing") + " starts at $95 for 50 prompts and " + a("Profound", "profound_pricing") + " at $99. " + a("Bing Webmaster Tools", "bing_ai_perf") + " shows Copilot citations for free. Pick by prompts, engines and data access, not by dashboard."),
        p("Most AI visibility tools now have self-serve plans. Peec AI lists $95 a month for 50 prompts across three AI models, $245 for 150 prompts and $495 for 350 (" + a("Peec AI", "peec_pricing") + ")."),
        p("Profound launched lower-priced Starter and Growth plans in October 2025, starting at $99 a month (" + a("Profound", "profound_pricing") + "). Otterly.ai sizes its plans by prompt capacity: 16 prompts on four engines in Lite, 120 in Standard and 403 in Premium (" + a("Otterly.ai", "otterly_plans") + ")."),
        p("And one source is free. Since February 2026, Bing Webmaster Tools reports how often Copilot and Bing's AI summaries cite your pages (" + a("Bing", "bing_ai_perf") + ")."),
        h2("What is an AI visibility tool?"),
        cap("An AI visibility tool is software that runs your buyer prompts in assistants like ChatGPT and Perplexity on a schedule and records who gets named."),
        p("Most tools report three things: whether your brand appears, which competitors appear instead, and which sources the assistant cited. The source list is often the most useful part, because it tells you where to earn mentions."),
        h2("How do the main tools compare?"),
        cap("Comparing AI visibility tools means checking prompt capacity, engines covered, data export and price per prompt, since dashboards look similar across vendors."),
        table(
            ["Tool", "Entry price", "Capacity at entry level", "Notes"],
            [
                ["Peec AI", "$95 a month", "50 prompts, 3 models", "Models chosen from a list including ChatGPT, Perplexity and Gemini"],
                ["Otterly.ai", "See pricing page", "16 prompts on 4 engines (Lite)", "Fewer engines means more prompts per plan"],
                ["Profound", "From $99 a month", "Not published for Starter", "Starter and Growth plans since October 2025"],
                ["Bing Webmaster Tools", "Free", "Your own site's citations", "Copilot and Bing AI summaries only; shows citations, not ranking"],
            ],
            "Prices and capacities as published by each vendor in 2026.",
        ),
        h2("Which tool fits a B2B team?"),
        cap("The right tool for a B2B team is one that covers your buyers' assistants and languages at a prompt volume you can review monthly."),
        ol([
            "<strong>Count your prompts first.</strong> 50 to 150 buyer prompts per market is a common starting set.",
            "<strong>List the engines your buyers use.</strong> For B2B, include Microsoft Copilot next to ChatGPT, Perplexity and Gemini.",
            "<strong>Check data export.</strong> You should be able to download raw answers and sources.",
            "<strong>Check competitor tracking.</strong> Share of voice needs at least three competitors on the same prompts.",
            "<strong>Add Bing Webmaster Tools</strong> regardless; it costs nothing and covers Copilot.",
            "<strong>Run a one-month trial</strong> on your real prompts before signing an annual plan.",
        ]),
    ] + faq([
        ("Do we need a paid tool at all?", "For a first look, running 20 buyer prompts by hand works. For monthly reporting across several assistants and competitors, a tool saves days of work."),
        ("Which tool should we start with?", "One that covers your buyers' assistants and languages and lets you export raw answers. Trial it for a month on your own prompts, and add Bing Webmaster Tools and Google Search Console, which are free."),
        ("Are the results reliable?", "AI answers vary between runs. Good tools run prompts repeatedly and report trends; treat any single result as a sample."),
        ("How much should a mid-size B2B company spend on tools?", "Entry plans start around $95 to $99 a month. " + a("Nutshell's 2026 overview", "nutshell_cost") + " puts AI visibility tracking at $50 to over $1,000 a month depending on scale."),
        ("Can these tools improve visibility?", "No. They measure. Improvement comes from fixing pages, crawler access and mentions on other sites."),
    ]) + [
        cta("Not sure which prompts to track? " + QS + " and we build a first prompt set with you. See our " + a("services", "/services/") + ", or read " + a("how to audit AI visibility without paying for Profound", "/audit-ai-visibility-without-paying-profound/") + "."),
    ],
)

# ------------------------------------------------------------------ comparison pages
POSTS["comparison-pages-ai-assistants-cite"] = dict(
    title="How to write comparison pages AI assistants cite",
    excerpt="Side-by-side comparisons are among the top-cited formats in AI answers. How to write X vs Y pages that buyers and AI assistants trust and cite.",
    category="conversion-optimization", kw="comparison pages",
    blocks=[
        tldr("In a " + a("Meltwater and LinkedIn study", "meltwater_linkedin") + " of 9.5 million AI citations, side-by-side comparisons (50%) and best-of lists (54%) were among the top-cited LinkedIn formats. Fair, sourced comparison pages help buyers who shortlist three or fewer products."),
        p("Buyers narrow down fast. In TrustRadius' 2026 survey of 1,862 technology buyers, 83% shortlisted three or fewer products (" + a("TrustRadius", "trustradius_2026") + "). The comparison happens before the first sales call."),
        p("AI assistants do a lot of that comparing now. In an " + a("Ahrefs study of 750 ChatGPT prompts", "ahrefs_lists") + ", \"best X\" lists made up 43.8% of cited page types. And the Meltwater study found side-by-side comparisons among the top-cited formats on LinkedIn, at 50% (" + a("Meltwater", "meltwater_linkedin") + ")."),
        p("If you do not publish the comparison, someone else will, with their facts about you."),
        h2("What is a comparison page?"),
        cap("A comparison page is a page that sets your product against alternatives on the same criteria, usually titled \"X vs Y\" or \"X alternatives\"."),
        p("Done well, it is a buying tool: the criteria buyers care about, honest answers per option, and a clear statement of who each option suits."),
        h2("Why do AI assistants cite comparison pages?"),
        cap("Comparison citation means the assistant reuses structured answers: a page with criteria, a table and verdicts answers \"which is better for me?\" in one place."),
        p("A table with the same rows for every option is easy to quote and summarize. That is the format assistants lean on when a buyer asks for a recommendation."),
        h2("How do you write a comparison page that gets cited?"),
        cap("Writing a citable comparison means choosing real buyer criteria, filling a table honestly for every option, and saying clearly who should pick which."),
        ol([
            "<strong>Pick the comparisons buyers actually make.</strong> Check sales calls, lost deals and your prompt tracker.",
            "<strong>Use the buyer's criteria</strong>: price model, integrations, deployment time, support, compliance.",
            "<strong>Build one table</strong> with the same rows for every option, in HTML text.",
            "<strong>Source competitor facts</strong> from their own pricing and documentation pages, with a date.",
            "<strong>Write a verdict per segment</strong>: \"Pick X if you need...\", including cases where the competitor fits better.",
            "<strong>Review every quarter</strong>, because prices and features change.",
        ]),
        table(
            ["Element", "Weak comparison page", "Strong comparison page"],
            [
                ["Criteria", "Chosen to make you win", "Chosen by buyers"],
                ["Competitor facts", "Unsourced, outdated", "Linked to their pages, dated"],
                ["Verdict", "\"We are better\"", "Who each option suits"],
                ["Format", "Images and sliders", "HTML table and text"],
            ],
            "What separates a page buyers trust from an advert.",
        ),
    ] + faq([
        ("Is it risky to name competitors?", "Comparative claims must be accurate and verifiable. Link to their public pages, date your facts and avoid claims you cannot prove."),
        ("How many comparison pages should we publish?", "Start with the three to five comparisons your buyers make most often. Quality matters more than volume."),
        ("Should we admit where a competitor is better?", "Yes. Buyers notice one-sided pages, and an honest verdict makes the rest of the page credible."),
        ("Where should comparison pages live?", "On your own site, linked from product and pricing pages, so buyers and crawlers find them."),
        ("Do comparison pages work for services, not just software?", "Yes. Compare engagement models, such as agency vs in-house vs freelancer, on cost, speed and control."),
    ]) + [
        cta("Want a comparison page buyers trust? " + QS + " and we pick the comparisons that matter. See our " + a("services", "/services/") + ", or read " + a("how to get onto the lists AI assistants cite", "/get-on-best-of-lists-ai-assistants-cite/") + "."),
    ],
)

# ------------------------------------------------------------------ wrong AI answers
POSTS["fix-wrong-ai-answers-about-your-company"] = dict(
    title="When AI gets your company wrong: a correction playbook for B2B brands",
    excerpt="AI search tools answered more than 60% of news queries incorrectly in one test. How B2B brands find and fix wrong AI answers about their company.",
    category="ai-search", kw="wrong AI answers",
    blocks=[
        tldr("AI assistants get facts wrong often: a " + a("Tow Center test", "cjr_tow") + " found eight AI search tools answered more than 60% of queries incorrectly, and an " + a("EBU and BBC study", "ebu_study") + " found significant issues in 45% of answers. Check what assistants say about your company monthly and fix the sources they rely on."),
        p("Wrong answers are not rare edge cases. The Tow Center for Digital Journalism ran 1,600 queries across eight AI search tools in early 2025; collectively, they answered more than 60% incorrectly (" + a("Columbia Journalism Review", "cjr_tow") + ")."),
        p("A larger study by 22 public service broadcasters reviewed more than 3,000 answers from ChatGPT, Copilot, Gemini and Perplexity in 14 languages. 45% had at least one significant issue, and 31% had serious sourcing problems (" + a("EBU", "ebu_study") + ")."),
        p("Marketing leaders are responding. " + a("Gartner", "gartner_cmo") + " found in November 2026 that 42% of CMOs monitor and correct misinformation about their brand in AI output."),
        h2("What counts as a wrong AI answer about your company?"),
        cap("A wrong AI answer is any statement about your company that misstates facts buyers use to decide: pricing, features, certifications, customers, locations or ownership."),
        p("The common ones in B2B: outdated pricing, a discontinued product presented as current, missing certifications, a competitor's feature attributed to you, or the wrong headquarters country."),
        h2("Why do AI assistants get companies wrong?"),
        cap("An AI error about a company usually means the assistant relied on outdated, thin or conflicting sources, or filled a gap where no source existed."),
        p("If your pricing page hides prices, a review site from 2023 becomes the source. If your certifications sit in a PDF, the assistant may not find them. Every gap is an invitation to guess."),
        h2("How do you correct wrong AI answers?"),
        cap("Correcting AI answers means fixing the sources, not the assistant: publish the right facts clearly, update outdated third-party pages and re-check monthly."),
        ol([
            "<strong>Run a fact check monthly.</strong> Ask ChatGPT, Copilot, Gemini and Perplexity ten questions buyers ask about you.",
            "<strong>Log each error</strong> with the assistant, the wrong statement and the cited source.",
            "<strong>Publish the correct fact</strong> on your own site in plain HTML text: a pricing page, a trust page, an about page.",
            "<strong>Fix third-party sources</strong>: update review profiles, directories, partner pages and outdated articles where you can.",
            "<strong>Use the feedback buttons</strong> in each assistant to report the wrong answer.",
            "<strong>Re-check after four weeks</strong> and track the error rate over time.",
        ]),
    ] + faq([
        ("Can we ask OpenAI or Google to correct an answer?", "You can report answers with the feedback options in each product. The lasting fix is correcting the sources assistants rely on."),
        ("Which facts matter most?", "The ones that end deals: pricing model, certifications, integrations, customer segments and whether a product still exists."),
        ("How often should we check?", "Monthly for core facts, and after every launch, price change or rebrand."),
        ("Do buyers notice wrong AI answers?", "Many do. TrustRadius found that 94% of buyers who use AI fact-check its answers at least some of the time."),
        ("Who should own this?", "Marketing usually owns it, with input from product and sales, who hear the wrong claims first."),
    ]) + [
        cta("Want to know what AI assistants say about your company? " + QS + " and we check the facts with you. See our " + a("services", "/services/") + ", or read " + a("brand mentions vs backlinks", "/brand-mentions-vs-backlinks-ai-visibility/") + "."),
    ],
)

# ------------------------------------------------------------------ freshness
POSTS["content-freshness-ai-search-b2b"] = dict(
    title="Content freshness and AI search: how often B2B teams should update pages",
    excerpt="AI assistants cite content that is 25.7% fresher than Google's organic results. Which B2B pages to update, how often, and what counts as a real update.",
    category="ai-search", kw="content freshness AI",
    blocks=[
        tldr("AI assistants cite newer content than Google ranks: in an " + a("Ahrefs study of 17 million citations", "ahrefs_fresh") + ", cited pages were 25.7% fresher than organic results. Update your key commercial pages every quarter with real changes, not just a new date."),
        p("Freshness shows up across large citation studies. Ahrefs compared 17 million citations from ChatGPT, Perplexity, Gemini, Copilot and Google AI Overviews with Google's organic results: on average, AI-cited content was 25.7% fresher (" + a("Ahrefs, 2025", "ahrefs_fresh") + ")."),
        p("Lists show the same pattern. Of 1,100 blog lists ChatGPT cited, 79.1% had been updated in 2025, and 26% in the two months before the study (" + a("Ahrefs", "ahrefs_lists") + ")."),
        p("Journalism too: 57% of journalism citations with a known date were published in the last 12 months, and citations peak in the first month after publication (" + a("Muck Rack, May 2026", "muckrack_may26") + ")."),
        h2("What is content freshness in AI search?"),
        cap("Content freshness is how recently a page was published or meaningfully updated, which AI assistants appear to weigh when choosing sources to cite."),
        p("Not every assistant behaves the same. Ahrefs found ChatGPT had the strongest preference for newer content, while Google AI Overviews was the only system that cited slightly older content than Google's organic results."),
        h2("Which B2B pages should you update first?"),
        cap("Your first update priority is the pages buyers use to decide: pricing, comparisons, product pages, integration pages and your most visited guides."),
        table(
            ["Page type", "Update cadence", "What a real update looks like"],
            [
                ["Pricing", "Every change, review quarterly", "Current prices, plan limits, date"],
                ["Comparison pages", "Quarterly", "Competitor facts re-checked, new criteria"],
                ["Product and integration pages", "Every release", "New features, screenshots, supported tools"],
                ["Top guides and statistics posts", "Every 6 months", "New data, removed outdated claims"],
                ["Case studies", "Yearly", "Updated results, client still active"],
            ],
            "A practical cadence for B2B sites.",
        ),
        h2("What counts as a real update?"),
        cap("A real update means the content changed in substance: new data, corrected facts, new sections or removed outdated material, not only a changed date."),
        ol([
            "<strong>Replace outdated statistics</strong> with the latest data and link the source.",
            "<strong>Add what changed</strong>: new features, prices, regulations or market data.",
            "<strong>Remove what is no longer true</strong>, including discontinued products.",
            "<strong>Show the update date</strong> visibly on the page.",
            "<strong>Notify search engines</strong> through your sitemap and IndexNow, which Bing supports.",
        ]),
    ] + faq([
        ("Does changing the date alone help?", "It is not a real update and it misleads readers. Change the substance, then update the date."),
        ("Do all AI assistants prefer fresh content?", "Most do in the Ahrefs data. ChatGPT showed the strongest preference; Google AI Overviews did not prefer newer content."),
        ("Should we update old blog posts or write new ones?", "Update posts that still serve buyers. Merge or remove thin ones. New posts make sense for new questions."),
        ("How many pages can a small team keep fresh?", "Focus on 10 to 20 commercial pages. A quarterly review of those beats a yearly sweep of everything."),
        ("Does freshness matter for evergreen topics?", "Less, but cited content in the Ahrefs study was still fresher on average. Re-check facts at least yearly."),
    ]) + [
        cta("Not sure which pages to refresh first? " + QS + " and we prioritize them with you. See our " + a("services", "/services/") + ", or read " + a("how ChatGPT search picks sources", "/how-chatgpt-search-picks-sources-b2b/") + "."),
    ],
)

# ------------------------------------------------------------------ press releases
POSTS["do-press-releases-get-cited-by-ai"] = dict(
    title="Do press releases get cited by AI? What the citation data shows",
    excerpt="Press releases made up 1.1% of AI citations in Muck Rack's May 2026 data, while earned media made up 84%. What that means for B2B PR teams.",
    category="b2b-marketing", kw="press releases AI",
    blocks=[
        tldr("Rarely: press releases made up 1.1% of AI citations in " + a("Muck Rack's May 2026 analysis", "muckrack_may26") + " of more than 25 million links, against 84% for earned media. A release helps when it leads to coverage, not as a source on its own."),
        p("Muck Rack analyzed more than 25 million links cited by ChatGPT, Claude and Gemini across 17 industries. Earned media, including journalism, academic, government and third-party sources, made up 84% of citations; journalism alone about 27% (" + a("Muck Rack, May 2026", "muckrack_may26") + ")."),
        p("Press releases moved around. Their share rose from about 1.2% in July 2025 to 6% in December 2025, then fell back to 1.1% by May 2026 (" + a("Muck Rack", "muckrack_may26") + "). Wire-service releases alone went from 0.2% to 1% of citations in the second half of 2025 (" + a("GlobeNewswire release on Muck Rack data", "muckrack_dec25") + ")."),
        p("So a release is a weak source and a strong trigger. What matters is the coverage it creates."),
        h2("What is the role of a press release in AI search?"),
        cap("A press release in AI search is mainly a trigger: it rarely gets cited itself, but the coverage it generates in trade media often does."),
        p("Muck Rack found releases appear about 3.5 times more often in answers about industry trends than in best-of answers. They help most when your news is the trend."),
        h2("Which sources do AI assistants cite instead?"),
        cap("The preferred source type is earned media: journalism, academic and government pages, encyclopedias, social platforms and other companies' content, together 84% of citations."),
        table(
            ["Source type", "Share or finding", "Source"],
            [
                ["Earned media overall", "84% of citations", "Muck Rack, May 2026"],
                ["Journalism", "About 27% of citations", "Muck Rack, May 2026"],
                ["Press releases", "1.1% of citations", "Muck Rack, May 2026"],
                ["Paid and advertorial", "0.3% of citations", "Muck Rack, May 2026"],
                ["Technology journalism", "24.2% of technology citations; trade titles outweigh general news", "Muck Rack, May 2026"],
            ],
            "Where AI citations come from.",
        ),
        h2("How should B2B PR teams adapt?"),
        cap("Adapting PR for AI means measuring coverage in the outlets assistants cite, pitching trade media with data, and treating releases as one tool among several."),
        ol([
            "<strong>Find the outlets assistants cite</strong> for your category by running buyer prompts.",
            "<strong>Pitch those outlets with data</strong>: a benchmark, a survey or a customer result.",
            "<strong>Keep releases factual and dated</strong>, so they hold up if they are cited.",
            "<strong>Put the full story on your own site</strong>, in HTML, with the numbers.",
            "<strong>Track AI mentions</strong> after each announcement, not only press clippings.",
        ]),
    ] + faq([
        ("Should we stop sending press releases?", "No. Use them for real news and as a basis for pitches. Do not expect AI citations from the release itself."),
        ("Do wire services help AI visibility?", "Wire releases were about 1% of citations at the end of 2025. Coverage that follows is worth far more."),
        ("Which outlets matter for B2B technology?", "Muck Rack found trade publications outweigh general news in technology answers. Check which titles assistants cite for your prompts."),
        ("How fast do AI assistants pick up news?", "Muck Rack saw journalism citations peak in the first month after publication."),
        ("Is paid placement worth it?", "Paid and advertorial content made up 0.3% of citations. Earned coverage is the better investment."),
    ]) + [
        cta("Want PR that also moves AI visibility? " + QS + " and we map the outlets assistants cite in your category. See our " + a("services", "/services/") + "."),
    ],
)

# ------------------------------------------------------------------ earned media
POSTS["earned-media-ai-visibility-b2b"] = dict(
    title="Earned media for AI visibility: where B2B brands should get mentioned",
    excerpt="Earned media made up 84% of AI citations in a 25 million link study. Where B2B brands should earn mentions, and how to pitch for them.",
    category="b2b-marketing", kw="earned media AI visibility",
    blocks=[
        tldr("84% of AI citations come from earned media (" + a("Muck Rack, May 2026", "muckrack_may26") + "), and news mentions correlate with AI visibility at 0.67 (" + a("Ahrefs, 2026", "ahrefs_mention_types") + "). Earn mentions in the trade media, communities and lists that assistants already cite for your category."),
        p("Owned content is a small part of what AI assistants cite. Muck Rack's analysis of more than 25 million links found earned media made up 84% of citations, with owned and paid content at about 16% (" + a("Muck Rack", "muckrack_may26") + ")."),
        p("Mentions track visibility. In an Ahrefs study of 10,000 brands and 260 million AI prompts, mentions in Reddit ranking threads correlated with AI mentions at 0.75, webpage mentions at 0.70 and news article mentions at 0.67 (" + a("Ahrefs", "ahrefs_mention_types") + ")."),
        p("And the journalists who matter may not be on your list. Muck Rack found an average overlap of just 2% between journalists brands pitched and journalists AI cited (" + a("GlobeNewswire release on Muck Rack data", "muckrack_dec25") + ")."),
        h2("What is earned media in AI search?"),
        cap("Earned media in AI search means brand mentions you did not pay for or publish: articles, reviews, forum threads, lists and other companies' content."),
        p("The definition is broad on purpose. Muck Rack counts journalism, academic, government, encyclopedic, social and third-party corporate sources."),
        h2("Where should B2B brands earn mentions?"),
        cap("The best place for a mention is a source assistants already cite for your buyer prompts, found by running those prompts and logging citations."),
        table(
            ["Channel", "Correlation with AI mentions (Ahrefs, 2026)", "How B2B brands earn it"],
            [
                ["Reddit ranking threads", "0.75", "Real answers in relevant subreddits, never promotion"],
                ["Webpage mentions", "0.70", "Partner pages, lists, guest contributions"],
                ["News articles", "0.67", "Data-led pitches to trade media"],
                ["YouTube", "0.64", "Talks, demos, interviews"],
            ],
            "Correlations, not proof of cause.",
        ),
        h2("How do you pitch for mentions that AI cites?"),
        cap("Pitching for AI-cited mentions means targeting the outlets and authors assistants already cite, and offering them data or expertise they cannot get elsewhere."),
        ol([
            "<strong>Build your target list from AI answers</strong>, not only from a media database.",
            "<strong>Lead with data</strong>: a benchmark, a survey, anonymized results.",
            "<strong>Offer an expert</strong> with a clear point of view and availability.",
            "<strong>Make facts easy to check</strong> with a source page on your site.",
            "<strong>Follow up on lists and roundups</strong> before they refresh.",
            "<strong>Track AI mentions monthly</strong> to see which placements moved the needle.",
        ]),
    ] + faq([
        ("Does small coverage count?", "Ahrefs found traffic-weighted mentions correlated about as strongly as raw mentions, so a mention on a smaller relevant site still counts."),
        ("Is earned media more important than our own site?", "Both matter. Your site supplies the facts; earned media tells assistants others trust you."),
        ("How many mentions do we need?", "Ahrefs found returns diminish: ten times more webpage mentions went with about 3.7 times more AI mentions."),
        ("Should B2B brands be on Reddit?", "Where buyers discuss your category, yes, as genuine participants. Promotion gets removed and hurts trust."),
        ("Can an agency do this for us?", "Yes, as long as they pitch real stories and data. Mass outreach rarely earns coverage."),
    ]) + [
        cta("Want to know where your competitors get mentioned and you do not? " + QS + ". See our " + a("services", "/services/") + ", or read " + a("do press releases get cited by AI?", "/do-press-releases-get-cited-by-ai/") + "."),
    ],
)

# ------------------------------------------------------------------ LinkedIn
POSTS["linkedin-ai-citations-b2b"] = dict(
    title="LinkedIn and AI search: why B2B posts get cited",
    excerpt="LinkedIn is the second most cited domain in AI answers in a 9.5 million citation study. What gets cited, who gets cited, and what B2B teams should post.",
    category="b2b-marketing", kw="LinkedIn AI citations",
    blocks=[
        tldr("LinkedIn ranked second among cited domains in a " + a("Meltwater and LinkedIn study", "meltwater_linkedin") + " of 9.5 million AI citations, behind YouTube. About 75% of cited LinkedIn content came from individual members, mostly text posts, and 48% was less than three months old."),
        p("For B2B topics, LinkedIn is now a major AI source. The Meltwater study, published in May 2026, tracked citations in ChatGPT, Google AI Mode and AI Overviews, Gemini, Copilot and Claude. LinkedIn ranked first in AI and data science and in marketing and advertising categories (" + a("Meltwater", "meltwater_linkedin") + ")."),
        p("People beat pages: about 75% of LinkedIn citations came from individual profiles and 25% from company pages. Accounts with 1,000 to 10,000 followers contributed 40% of citations (" + a("Meltwater", "meltwater_linkedin") + ")."),
        p("Buyers use what assistants find. " + a("Gartner", "gartner_validate") + " reported in May 2026 that 45% of B2B buyers used generative AI in a recent purchase, mainly to research vendors and products."),
        h2("What LinkedIn content gets cited by AI?"),
        cap("Cited LinkedIn content is mostly recent, original text posts from individual experts, often formatted as lists, comparisons or how-to-choose guides."),
        table(
            ["Finding", "Value", "Source"],
            [
                ["Text posts as share of cited content", "72%", "Meltwater, 2026"],
                ["Articles", "12%", "Meltwater, 2026"],
                ["Original (not reshared) content", "72%", "Meltwater, 2026"],
                ["Published in the last three months", "48%", "Meltwater, 2026"],
                ["Cited formats: best X lists, comparisons, how-to-choose guides", "54%, 50%, 33%", "Meltwater, 2026"],
            ],
            "What the 9.5 million citation study found about LinkedIn.",
        ),
        h2("Why do AI assistants cite LinkedIn posts?"),
        cap("LinkedIn citation means assistants treat professionals' first-hand posts as evidence of how practitioners see a topic, especially in B2B categories."),
        p("Its share grew 26% over the four-week research period. Recency matters: content older than 12 months made up only 12% of citations."),
        h2("What should B2B teams post on LinkedIn?"),
        cap("A LinkedIn plan for AI visibility means experts posting original, specific text regularly, using the formats assistants cite: comparisons, lists and decision guides."),
        ol([
            "<strong>Post from people, not only the company page.</strong> Founders, practice leads and engineers.",
            "<strong>Write original text</strong> with a clear point and specific numbers.",
            "<strong>Use citable formats</strong>: \"how to choose\", \"X vs Y\", \"five mistakes we see\".",
            "<strong>Post consistently</strong>, since recent content dominates citations.",
            "<strong>Link to the deeper page</strong> on your site for the full answer.",
        ]),
    ] + faq([
        ("Do we need a big following?", "No. In the Meltwater data, 51% of cited creators had fewer than 10,000 followers."),
        ("Do company pages get cited?", "Yes, but less: about 25% of LinkedIn citations came from company pages."),
        ("Are LinkedIn articles better than posts?", "Text posts made up 72% of cited content, articles 12%. Short, specific posts work."),
        ("How often should experts post?", "Regularly enough to stay recent; 48% of cited content was under three months old."),
        ("Does LinkedIn replace our blog?", "No. Posts get cited for perspective; your site holds the facts, pricing and depth."),
    ]) + [
        cta("Want a LinkedIn plan that also builds AI visibility? " + QS + ". See our " + a("services", "/services/") + ", or read " + a("earned media for AI visibility", "/earned-media-ai-visibility-b2b/") + "."),
    ],
)

# ------------------------------------------------------------------ original research
POSTS["original-research-b2b-lead-magnet-ai-citations"] = dict(
    title="Original research as a B2B lead magnet: how data earns AI citations",
    excerpt="Adding statistics lifted visibility in AI answers by about 31% in the GEO study. How to plan B2B research that earns leads and AI citations.",
    category="lead-generation", kw="original research lead magnet",
    blocks=[
        tldr("Data gets cited: adding statistics improved visibility in generative engine answers by about 31% in the " + a("GEO study (KDD 2024)", "geo_paper_html") + ", and research reports had the strongest direct impact on security vendor selection, at 46% (" + a("Ponemon and NOLA, 2026", "ponemon_cyber") + "). Original research works as a lead magnet and a citation magnet."),
        p("The researchers behind the GEO study tested content changes on 10,000 queries. Adding quotations lifted visibility by about 41%, adding statistics by about 31% and citing sources by about 28%; keyword stuffing made visibility worse (" + a("Aggarwal et al.", "geo_paper_html") + ")."),
        p("Buyers value research too. In a June 2026 study of 320 enterprise security decision-makers, research and survey reports had the strongest direct impact on selection at 46% (" + a("Ponemon and NOLA", "ponemon_cyber") + ")."),
        p("And research earns coverage. Journalism made up about 27% of AI citations in Muck Rack's May 2026 data (" + a("Muck Rack", "muckrack_may26") + "), and journalists need data."),
        h2("What is original research in B2B marketing?"),
        cap("Original research is data your company collects and publishes, such as a survey, a benchmark from anonymized client data, or an index tracked over time."),
        p("It does not need to be large. A survey of 200 buyers in one sector, done well, is more useful than a generic report everyone has seen."),
        h2("Why does original research earn AI citations?"),
        cap("Research earns citations because it is a primary source: assistants and journalists quoting a specific number need someone to attribute it to."),
        p("Every statistic in an AI answer comes from somewhere. If your benchmark is the only source for a number in your niche, you become the citation."),
        h2("How do you plan research that generates leads?"),
        cap("Planning lead-generating research means choosing a question buyers care about, collecting credible data, and publishing findings openly while gating the full report."),
        ol([
            "<strong>Pick one question</strong> your buyers argue about, such as costs, benchmarks or adoption rates.",
            "<strong>Choose a credible method</strong>: a sample size, a period and a clear definition.",
            "<strong>Publish key findings openly</strong> on an HTML page with charts and method.",
            "<strong>Gate the full report</strong> for leads, if you want, but keep the headline numbers open.",
            "<strong>Pitch the findings</strong> to trade media and analysts.",
            "<strong>Repeat yearly</strong>, so the trend becomes the reason to cite you.",
        ]),
    ] + faq([
        ("How big does a survey need to be?", "Big enough to be credible for your niche. Report the sample size and method so readers can judge."),
        ("Should we gate the report?", "Gate the full PDF if leads matter, but keep key numbers on an open page. Assistants cannot cite what they cannot read."),
        ("Can we use our own client data?", "Yes, anonymized and aggregated, with client consent where contracts require it."),
        ("How long does a research project take?", "A focused survey takes four to eight weeks from questions to published findings."),
        ("What if the results are boring?", "Then the angle is wrong. Pick a question where the answer is uncertain or disputed."),
    ]) + [
        cta("Want research that buyers and journalists quote? " + QS + " and we shape the question with you. See our " + a("services", "/services/") + "."),
    ],
)

ORDER = [
    "how-chatgpt-search-picks-sources-b2b",
    "ai-visibility-tools-compared-peec-otterly-profound",
    "comparison-pages-ai-assistants-cite",
    "fix-wrong-ai-answers-about-your-company",
    "content-freshness-ai-search-b2b",
    "do-press-releases-get-cited-by-ai",
    "earned-media-ai-visibility-b2b",
    "linkedin-ai-citations-b2b",
    "original-research-b2b-lead-magnet-ai-citations",
]
