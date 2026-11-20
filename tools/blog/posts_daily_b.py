"""Daily posts, 21 November to 20 December 2026 (batch B: 9 posts).

Same rules and helpers as posts_2026_q4.py (geo-writer guide). Every
statistic and every source URL was opened and checked on 20 November 2026.
"""
import importlib.util
import pathlib

_here = pathlib.Path(__file__).resolve().parent
_spec = importlib.util.spec_from_file_location("da", _here / "posts_daily_a.py")
da = importlib.util.module_from_spec(_spec)
_spec.loader.exec_module(da)

a, p, h2, h3, cap, tldr, ol, ul, table, faq, cta = (
    da.a, da.p, da.h2, da.h3, da.cap, da.tldr, da.ol, da.ul, da.table, da.faq, da.cta)
QS = da.QS
POSTS = {}

# ------------------------------------------------------------------ Claude
POSTS["claude-ai-search-b2b-citations"] = dict(
    title="How Claude cites sources: what B2B marketers should know",
    excerpt="Claude cites sources in 55% of answers but averages about 13 citations when it does. How Claude search works and how B2B brands get cited.",
    category="ai-search", kw="Claude citations",
    blocks=[
        tldr("Claude cites less often but more deeply than other assistants: in " + a("Muck Rack's May 2026 data", "muckrack_may26") + ", it cited sources in 55% of responses and averaged about 13 citations when it did. Allow Claude-SearchBot and publish primary, well-sourced pages."),
        p("Claude behaves differently from ChatGPT. Muck Rack's analysis of more than 25 million links found ChatGPT cites in 96% of responses with about five citations each, Gemini in 82% with about eight, and Claude in 55% with about 13 when it cites (" + a("Muck Rack", "muckrack_may26") + ")."),
        p("Its sources lean academic. PubMed Central was Claude's most cited domain, and U.S. News and World Report its top journalism outlet. Claude did not cite YouTube at all in the sample (" + a("Muck Rack", "muckrack_may26") + ")."),
        p("Traffic is still small. In Leadfeeder's 2026 study of B2B websites, Claude sent 5% of AI referral visits, against 81% for ChatGPT (" + a("Leadfeeder", "leadfeeder_ai") + ")."),
        h2("What is Claude search?"),
        cap("Claude search is the web search capability in Anthropic's Claude that retrieves current pages to answer a question and lists them as sources."),
        p("Anthropic runs separate bots. Claude-SearchBot indexes pages for search answers, Claude-User fetches pages when a person asks, and ClaudeBot collects training data. Blocking Claude-SearchBot may reduce your visibility in Claude's search results (" + a("Anthropic", "anthropic_bots") + ")."),
        h2("How is Claude different from ChatGPT for B2B brands?"),
        cap("Claude's difference for B2B brands is depth over frequency: fewer answers include sources, but those that do cite many, often primary and academic ones."),
        table(
            ["Assistant", "Responses with citations", "Average citations when citing", "Top domain"],
            [
                ["ChatGPT", "96%", "About 5", "Wikipedia"],
                ["Gemini", "82%", "About 8", "Reddit"],
                ["Claude", "55%", "About 13", "PubMed Central"],
            ],
            "Source: Muck Rack, What Is AI Reading, May 2026.",
        ),
        h2("How do you get cited by Claude?"),
        cap("Getting cited by Claude means being a primary source: original data, clear definitions, documented methods and pages that Claude-SearchBot is allowed to read."),
        ol([
            "<strong>Allow Claude-SearchBot</strong> in robots.txt and your firewall.",
            "<strong>Publish primary material</strong>: your own data, methods and technical documentation.",
            "<strong>Cite your sources</strong> on the page; well-referenced pages fit Claude's research-style answers.",
            "<strong>Write precise definitions</strong> under question-shaped headings.",
            "<strong>Track Claude separately</strong> in your prompt tool, because its sources differ.",
        ]),
    ] + faq([
        ("Is Claude worth optimizing for in B2B?", "It sends less traffic than ChatGPT, but when it cites, it cites many sources. The same work, primary data and clear pages, helps everywhere."),
        ("Does blocking ClaudeBot remove us from Claude search?", "ClaudeBot is for training. Claude-SearchBot handles search indexing; Anthropic says blocking it may reduce visibility in search answers."),
        ("Why does Claude cite academic sources?", "Its most cited domain in Muck Rack's data was PubMed Central, a research archive. Publishing your own methods and data puts you closer to that standard."),
        ("Does Claude cite YouTube?", "Not in Muck Rack's May 2026 sample. Put the key points of your videos in text on your site."),
        ("How do we see Claude traffic?", "Look for referrals from claude.ai in your analytics. Leadfeeder found Claude sent 5% of AI referral visits to B2B sites."),
    ]) + [
        cta("Want to know how Claude answers questions about your category? " + QS + ". See our " + a("services", "/services/") + ", or read " + a("original research as a lead magnet", "/original-research-b2b-lead-magnet-ai-citations/") + "."),
    ],
)

# ------------------------------------------------------------------ Gemini
POSTS["gemini-ai-search-b2b-citations"] = dict(
    title="How Google Gemini cites sources: a B2B marketer's guide",
    excerpt="Gemini cites sources in 82% of answers, with Reddit as its top domain. How Gemini finds sources, where it gets things wrong, and what B2B brands can do.",
    category="ai-search", kw="Gemini citations",
    blocks=[
        tldr("Gemini cites sources in 82% of answers, about eight per answer, with Reddit as its most cited domain (" + a("Muck Rack, May 2026", "muckrack_may26") + "). It also had the most accuracy issues in an " + a("EBU and BBC study", "ebu_study") + ". Check what Gemini says about you and strengthen the sources it reads."),
        p("Gemini draws on Google's index, but its citations look different from Google's results. In Muck Rack's analysis of more than 25 million links, Reddit made up about 2.4% of Gemini's citations, the largest single domain, with Forbes as its top journalism outlet (" + a("Muck Rack", "muckrack_may26") + ")."),
        p("Accuracy is a concern. In the EBU and BBC study of more than 3,000 answers in 14 languages, Gemini had significant issues in 76% of its responses, more than double the other assistants tested (" + a("EBU", "ebu_study") + ")."),
        p("It is the second AI referral source for B2B sites, at 8% of AI referral visits, well behind ChatGPT at 81% (" + a("Leadfeeder, 2026", "leadfeeder_ai") + ")."),
        h2("What is Gemini's search grounding?"),
        cap("Gemini's grounding is the process of supplying content from Google's search index to the model when it answers, so responses can cite current web pages."),
        p("Google says the Google-Extended robots.txt token controls whether your content is used for training future Gemini models and for grounding in Gemini Apps, and that it does not affect inclusion in Google Search (" + a("Google", "google_crawlers") + ")."),
        h2("How does Gemini choose its sources?"),
        cap("Gemini's source choice means drawing on Google's index while often citing community threads and pages fresher than Google's organic results."),
        p("Freshness plays a role. In an " + a("Ahrefs study of 17 million citations", "ahrefs_fresh") + ", content Gemini cited was on average 298 days newer than Google's organic results."),
        table(
            ["Finding", "Value", "Source"],
            [
                ["Responses with citations", "82%", "Muck Rack, May 2026"],
                ["Average citations per answer", "About 8", "Muck Rack, May 2026"],
                ["Most cited domain", "Reddit, about 2.4%", "Muck Rack, May 2026"],
                ["Responses with significant issues", "76%", "EBU and BBC, October 2025"],
                ["Share of AI referral visits to B2B sites", "8%", "Leadfeeder, 2026"],
            ],
            "Gemini in five numbers.",
        ),
        h2("What should B2B brands do about Gemini?"),
        cap("Working on Gemini visibility means keeping Google-Extended open if you want grounding, earning community mentions, and checking Gemini's answers about you for errors."),
        ol([
            "<strong>Decide on Google-Extended deliberately.</strong> Blocking it removes your content from Gemini grounding.",
            "<strong>Take part in relevant Reddit discussions</strong> as a genuine expert.",
            "<strong>Keep key facts fresh and clear</strong> on your own pages.",
            "<strong>Fact-check Gemini monthly</strong> on pricing, features and certifications.",
            "<strong>Report wrong answers</strong> with Gemini's feedback option and fix the source pages.",
        ]),
    ] + faq([
        ("Does blocking Google-Extended hurt our Google rankings?", "Google says it does not affect inclusion in Google Search and is not used as a ranking factor."),
        ("Why does Gemini cite Reddit so often?", "Reddit was its most cited domain in Muck Rack's data. Community threads answer comparison questions in buyers' own words."),
        ("Is Gemini reliable enough to matter?", "Its error rate was high in the EBU study, which is exactly why brands should check what it says about them."),
        ("How much traffic does Gemini send?", "About 8% of AI referral visits to B2B sites in Leadfeeder's 2026 data."),
        ("Is Gemini the same as Google AI Overviews?", "Both draw on Google's models and index, but they are separate products with different interfaces."),
    ]) + [
        cta("Want to know what Gemini says about your company? " + QS + ". See our " + a("services", "/services/") + ", or read " + a("when AI gets your company wrong", "/fix-wrong-ai-answers-about-your-company/") + "."),
    ],
)

# ------------------------------------------------------------------ own pages cited
POSTS["which-company-pages-ai-assistants-cite"] = dict(
    title="Which of your own pages do AI assistants cite?",
    excerpt="Homepages made up 23.8% of ChatGPT's top 1,000 cited pages, blog articles 1.9%. Which company pages get cited by AI, and how to make them citable.",
    category="ai-search", kw="pages AI cites",
    blocks=[
        tldr("Company sites do get cited, but not the pages most teams invest in: homepages and landing pages made up 23.8% of ChatGPT's top 1,000 cited pages, educational pages 19.4% and blog articles 1.9% (" + a("Ahrefs, 2025", "ahrefs_top1000") + "). Make your core pages the clearest answer about you."),
        p("Ahrefs analyzed the 1,000 pages ChatGPT cited most in September 2025. Wikipedia led with 29.7%, followed by homepages and landing pages at 23.8% and educational pages at 19.4%. Blog articles made up 1.9% and corporate pages 0.5% (" + a("Ahrefs", "ahrefs_top1000") + ")."),
        p("Across industries, company content matters more. Muck Rack found corporate content averaged 24% of AI citations, rising to 36.6% in manufacturing (" + a("Muck Rack, May 2026", "muckrack_may26") + ")."),
        p("In B2B categories, company websites made up 18.7% of citations in a 9.5 million citation study, next to user-generated platforms at 47.5% and peer review sites at 15% (" + a("Meltwater", "meltwater_linkedin") + ")."),
        h2("Which company pages get cited most?"),
        cap("Company page citation is led by homepages and landing pages, followed by educational pages that define or explain something clearly."),
        table(
            ["Page type", "Share of ChatGPT's top 1,000 citations", "What it means for B2B"],
            [
                ["Homepages and landing pages", "23.8%", "Your homepage must say what you do in plain words"],
                ["Educational pages", "19.4%", "Definitions, explainers and documentation get cited"],
                ["Reviews", "5.8%", "Third-party reviews describe you for you"],
                ["Blog articles", "1.9%", "Generic blog posts rarely get cited"],
                ["Corporate pages", "0.5%", "About pages need facts, not slogans"],
            ],
            "Source: Ahrefs, top 1,000 ChatGPT citations, September 2025.",
        ),
        h2("Why do so few blog posts get cited?"),
        cap("The likely blog problem is repetition: many posts restate what other pages already say, so assistants prefer the original definition, product page or primary data."),
        p("Ranking is not required either: 28.3% of the top cited pages had no organic keywords at all in the Ahrefs data. Being the clearest source counts more."),
        h2("How do you make your core pages citable?"),
        cap("Making core pages citable means stating facts plainly: what you do, for whom, at what price, with which integrations and certifications, in HTML text."),
        ol([
            "<strong>Rewrite your homepage opening</strong> in one plain sentence: what you do and for whom.",
            "<strong>Give every product its own page</strong> with features, integrations and pricing approach.",
            "<strong>Turn documentation into public HTML</strong>, not PDFs behind a login.",
            "<strong>Add a glossary or explainer section</strong> for the terms your buyers search.",
            "<strong>Put company facts on the about page</strong>: founding year, locations, team, customers.",
        ]),
    ] + faq([
        ("Should we stop blogging?", "No, but write posts that add something new: data, experience or a clear opinion. Rewrites of existing content rarely get cited."),
        ("Why is Wikipedia so dominant?", "It made up 29.7% of ChatGPT's top citations. For most B2B companies, a Wikipedia article is not realistic; focus on your own pages and reviews."),
        ("Do documentation pages count?", "Educational pages, which include explainers and documentation, made up 19.4% of top citations. Public docs help."),
        ("Does our homepage need to rank to be cited?", "Not necessarily. 28.3% of the top cited pages had no organic keywords in the Ahrefs study."),
        ("What should the about page include?", "Hard facts: who you are, where you operate, what you sell, key customers and leadership."),
    ]) + [
        cta("Want your homepage and product pages to be the source AI quotes? " + QS + ". See our " + a("services", "/services/") + "."),
    ],
)

# ------------------------------------------------------------------ GA4 AI traffic
POSTS["measure-ai-referral-traffic-ga4-b2b"] = dict(
    title="How to measure AI referral traffic in GA4: a B2B setup guide",
    excerpt="AI sent 0.5% of B2B site visits in one study, yet those visitors converted 3x more often. How to track AI referrals in GA4 and tie them to leads.",
    category="marketing-analytics", kw="AI referral traffic GA4",
    blocks=[
        tldr("AI referral traffic is small but converts: in " + a("Orbit Media's study of 97 B2B sites", "orbit_ai") + ", AI sources sent about 0.5% of visits, yet those visitors were about three times as likely to become leads. Set up a dedicated AI channel in GA4 so you can see it."),
        p("The share differs by study, but it is small everywhere. Orbit Media found about 0.5% of 29 million sessions came from AI sources between July 2025 and June 2026 (" + a("Orbit Media", "orbit_ai") + "). Leadfeeder measured AI at 1.8% of combined AI and organic search visits in 2026 (" + a("Leadfeeder", "leadfeeder_ai") + ")."),
        p("It is growing. Demandbase recorded a 303% increase in ChatGPT referrals to B2B sites between June 2025 and June 2026 (" + a("PPC Land on Demandbase data", "demandbase_ppc") + ")."),
        p("And it is concentrated: ChatGPT accounted for about 82% of AI-referred sessions in the Orbit data and 81% of AI referral visits in the Leadfeeder data."),
        h2("What is AI referral traffic?"),
        cap("AI referral traffic is visits that arrive by clicking a link in an AI assistant's answer, such as ChatGPT, Gemini, Perplexity, Claude or Copilot."),
        p("Not all AI influence shows up this way. Orbit Media notes that Google AI Mode and AI Overviews are recorded as organic search, and some AI app visits arrive as direct traffic."),
        h2("How do you set up AI tracking in GA4?"),
        cap("Setting up AI tracking in GA4 means creating a custom channel group that captures AI assistant referrers, then reporting leads by that channel."),
        ol([
            "<strong>Create a custom channel group</strong> in GA4 admin, under data display.",
            "<strong>Add an \"AI assistants\" channel</strong> above Referral, matching sources such as chatgpt.com, gemini.google.com, perplexity.ai, claude.ai and copilot.microsoft.com.",
            "<strong>Save and apply it</strong> to your acquisition reports.",
            "<strong>Mark your lead events as key events</strong>, such as form submissions and demo requests.",
            "<strong>Compare conversion rate by channel</strong> monthly.",
            "<strong>Add a self-reported source field</strong> to forms, with AI assistants as options, to catch visits GA4 cannot attribute.",
        ]),
        h2("How should you read the numbers?"),
        cap("Reading AI traffic data means weighing conversion against fit: AI visitors convert well, but fewer match your ideal customer profile than organic visitors."),
        table(
            ["Measure", "AI referrals", "Comparison", "Source"],
            [
                ["Share of traffic", "About 0.5%", "All sessions", "Orbit Media"],
                ["Lead conversion", "About 3x more likely", "Other organic sources", "Orbit Media"],
                ["Visiting companies that fit the ICP", "15.8%", "Organic search: 20.4%", "Leadfeeder"],
                ["Visits per company", "2.68", "Organic search: 3.78", "Leadfeeder"],
            ],
            "AI traffic: high intent, smaller and less targeted.",
        ),
    ] + faq([
        ("Why does GA4 show so little AI traffic?", "Many AI answers send no click, AI Mode counts as organic search, and some app visits arrive as direct. Treat GA4 numbers as a floor."),
        ("Which AI assistant sends the most B2B traffic?", "ChatGPT, with about 81% to 82% of AI referral visits in both the Leadfeeder and Orbit Media studies."),
        ("Is AI traffic better quality than organic?", "It converts more often, according to Orbit Media, but Leadfeeder found a lower share of visitors who match the ideal customer profile."),
        ("Should we report AI traffic to the board?", "Report it next to AI visibility metrics and self-reported sources. Traffic alone undercounts AI's influence."),
        ("How often should we review it?", "Monthly. Volumes are small, so look at three-month trends before drawing conclusions."),
    ]) + [
        cta("Want AI traffic and leads in one report? " + QS + ". See our " + a("services", "/services/") + ", or try the " + a("funnel calculator", "/funnel-calculator/") + "."),
    ],
)

# ------------------------------------------------------------------ buyers and AI before sales
POSTS["b2b-buyers-ai-before-sales"] = dict(
    title="How B2B buyers use AI before they talk to sales",
    excerpt="45% of B2B buyers used generative AI in a recent purchase, and 69% want sales reps to validate what AI told them. What that means for B2B marketing.",
    category="b2b-marketing", kw="B2B buyers AI",
    blocks=[
        tldr("45% of B2B buyers used generative AI in a recent purchase, and 69% prefer to validate AI-generated insights with a sales rep (" + a("Gartner, 2026", "gartner_validate") + "). Buyers research with AI, check with peers and reviews, and come to sales with a shortlist."),
        p("Gartner surveyed 645 B2B buyers in August and September 2025. They used an average of seven information sources in a recent purchase; 45% used generative AI, mainly to research vendors and products (" + a("Gartner", "gartner_validate") + ")."),
        p("They prefer to do it alone. In a related Gartner survey, 67% of B2B buyers preferred a buying experience without a sales rep (" + a("Gartner", "gartner_repfree") + ")."),
        p("But they check what AI tells them. TrustRadius found that 63% of technology buyers used AI during their purchase journey, and 94% of those fact-check its answers at least some of the time (" + a("TrustRadius, 2026", "trustradius_2026") + ")."),
        h2("What does the AI-assisted B2B buying journey look like?"),
        cap("The AI-assisted buying journey is research by assistant, validation through peers and reviews, and a sales conversation that starts with a shortlist already made."),
        table(
            ["Stage", "What buyers do", "What marketing should provide"],
            [
                ["Problem research", "Ask AI to explain the problem and options", "Clear explainers and definitions"],
                ["Shortlisting", "Ask AI for vendors that fit their situation", "Visibility on lists, reviews and comparisons"],
                ["Validation", "Check reviews, peers and vendor sites", "Reviews, case studies, transparent pricing"],
                ["Sales conversation", "Ask reps to confirm what AI said", "Sales teams briefed on how AI describes you"],
            ],
            "Four stages, each with its own content job.",
        ),
        h2("Why do buyers still want sales reps?"),
        cap("The main reason buyers want sales reps is validation: about half expect misleading information from generative AI, so reps confirm facts before commitment."),
        p("Gartner found 51% of buyers expect more misleading information from generative AI, and 49% expect it from sales reps. Reps matter most when buyers research a business problem, choose a preferred supplier, secure internal support and finalize the purchase."),
        h2("What should B2B marketing do differently?"),
        cap("Marketing for AI-assisted buyers means making your facts easy for assistants to find, easy for buyers to verify, and easy for reps to confirm."),
        ol([
            "<strong>Check how AI describes you</strong> for the questions buyers ask at each stage.",
            "<strong>Publish verifiable facts</strong>: pricing approach, integrations, certifications, customers.",
            "<strong>Invest in reviews</strong>; TrustRadius found 74% of buyers use them in purchase decisions.",
            "<strong>Brief sales</strong> on what AI says about you and your competitors.",
            "<strong>Ask every new lead</strong> which AI tools they used, and log it in the CRM.",
        ]),
    ] + faq([
        ("How many B2B buyers use AI?", "45% in Gartner's survey of 645 buyers and 63% in TrustRadius' survey of 1,862 technology buyers."),
        ("Do buyers trust AI answers?", "Partly. 94% of AI users in the TrustRadius study fact-check answers at least some of the time."),
        ("Do buyers still want to talk to sales?", "67% prefer a rep-free experience overall, but 69% want reps to validate AI-generated insights."),
        ("Which sources do buyers trust most?", "TrustRadius found demos, free trials, prior experience and user reviews among the most influential resources."),
        ("What should sales teams change?", "Expect buyers who arrive with a shortlist and AI-sourced claims. Prepare to confirm or correct them with evidence."),
    ]) + [
        cta("Want to see your brand through your buyers' AI research? " + QS + ". See our " + a("services", "/services/") + ", or read " + a("how ChatGPT search picks sources", "/how-chatgpt-search-picks-sources-b2b/") + "."),
    ],
)

# ------------------------------------------------------------------ schema
POSTS["schema-markup-b2b-ai-search"] = dict(
    title="Schema markup for B2B websites: what helps AI search",
    excerpt="Microsoft says schema markup helps its LLMs understand content, and Google recommends JSON-LD. Which schema types B2B sites need, and how to add them.",
    category="ai-search", kw="schema markup B2B",
    blocks=[
        tldr("Structured data helps machines understand your pages: Google says it uses structured data to understand page content (" + a("Google Search Central", "google_sd_intro") + "), and Microsoft has said schema markup helps its LLMs, including Copilot (" + a("Search Engine Land, 2025", "sel_canel_schema") + "). B2B sites need a small, accurate set, in the initial HTML."),
        p("Google recommends JSON-LD for structured data where your site allows it. Its documentation also lists results from sites that added markup: Rotten Tomatoes measured a 25% higher click-through rate on 100,000 pages with structured data, and Nestlé an 82% higher click-through rate for pages with rich results (" + a("Google Search Central", "google_sd_intro") + ")."),
        p("Microsoft's Fabrice Canel confirmed at SMX Munich in 2025 that schema markup helps Microsoft's LLMs understand content (" + a("Search Engine Land", "sel_canel_schema") + ")."),
        p("Where you add it matters. In " + a("Vercel's analysis", "vercel") + ", none of the major AI crawlers rendered JavaScript, so markup injected by scripts may never be seen."),
        h2("What is schema markup?"),
        cap("Schema markup is structured data, usually in JSON-LD, that labels what a page is about: an organization, article, person, product or service."),
        p("It does not change what visitors see. It tells crawlers, in a standard vocabulary from Schema.org, which facts on the page mean what."),
        h2("Which schema types does a B2B website need?"),
        cap("The core schema set for a B2B website is Organization, Article or BlogPosting, Person, Product or Service, and BreadcrumbList for navigation."),
        table(
            ["Schema type", "Where to use it", "What it tells machines"],
            [
                ["Organization", "Homepage, about page", "Company name, logo, URL, contact, social profiles"],
                ["Article or BlogPosting", "Blog posts and guides", "Headline, author, publish and update dates"],
                ["Person", "Author and team pages", "Who wrote it and their role"],
                ["Product or SoftwareApplication", "Product pages", "Product name, category, offers"],
                ["Service", "Service pages", "What you offer and where"],
                ["BreadcrumbList", "All pages", "Where the page sits in the site"],
            ],
            "A practical starter set for B2B sites.",
        ),
        h2("How do you add schema without breaking things?"),
        cap("Adding schema safely means generating it from real page content, placing it in server-rendered HTML, and validating it before and after release."),
        ol([
            "<strong>Start with Organization</strong> on the homepage, with consistent name, logo and profiles.",
            "<strong>Add Article and Person</strong> to posts, with real authors and accurate dates.",
            "<strong>Generate markup server-side</strong>, from your CMS or theme, not with tag managers.",
            "<strong>Mark up only what is visible</strong> on the page.",
            "<strong>Validate</strong> with the Schema Markup Validator and Google's Rich Results Test.",
            "<strong>Re-check after every redesign</strong> or plugin change.",
        ]),
    ] + faq([
        ("Does schema guarantee AI citations?", "No. It helps machines understand pages; being cited still depends on content, access and authority."),
        ("Which format should we use?", "JSON-LD, which Google recommends where your setup allows it."),
        ("Can a plugin handle schema?", "Yes, SEO plugins such as Yoast generate core types. Check the output and avoid two plugins writing conflicting markup."),
        ("Should we add FAQ markup?", "Mark up real questions and answers that are visible on the page. Do not add markup for content that is not there."),
        ("Does Google Tag Manager work for schema?", "Markup injected by JavaScript may be missed by AI crawlers that do not render scripts. Server-side is safer."),
    ]) + [
        cta("Want to know whether your schema is helping or hurting? " + QS + ". See our " + a("services", "/services/") + ", or read the " + a("AI crawler checklist", "/ai-crawler-checklist-b2b-website/") + "."),
    ],
)

# ------------------------------------------------------------------ pricing transparency
POSTS["publish-pricing-ai-search-b2b"] = dict(
    title="Should B2B companies publish pricing? What AI search changes",
    excerpt="Transparent pricing has been B2B buyers' top wish for four years running. Why hidden prices hurt you in AI search, and how to publish pricing safely.",
    category="conversion-optimization", kw="publish B2B pricing",
    blocks=[
        tldr("Buyers want prices and AI assistants answer price questions anyway: transparent pricing has been buyers' top wish-list item since 2023 (" + a("TrustRadius, 2026", "trustradius_2026") + "). If you do not publish a price or a pricing model, assistants will describe you from someone else's numbers."),
        p("Pricing transparency is the most requested change in B2B buying. In TrustRadius' January 2025 survey of 2,058 technology buyers, 45% named it the number one change they want from vendors (" + a("eMarketer on TrustRadius data", "emarketer_pricing") + ")."),
        p("It has stayed at the top of the list since 2023, according to TrustRadius' 2026 report, which surveyed 1,862 buyers (" + a("TrustRadius", "trustradius_2026") + ")."),
        p("Buyers want to do the work themselves: " + a("Gartner", "gartner_validate") + " found 70% prefer a fully digital, self-service buying experience."),
        h2("What does AI search change about pricing?"),
        cap("The pricing shift is that buyers now ask assistants \"how much does X cost?\" and get an answer, whether or not you published one."),
        p("Without a source on your site, the assistant relies on reviews, old articles or competitor pages. That is how outdated or wrong prices end up in shortlists."),
        h2("Which pricing information should you publish?"),
        cap("Publishable pricing means at least your pricing model and a starting point, even if final quotes depend on scope, users or volume."),
        table(
            ["Option", "What you publish", "Fits when"],
            [
                ["Full price list", "Plans, prices, limits", "Self-serve software, standard services"],
                ["Starting prices", "\"From\" prices per plan or service", "Most B2B services and mid-market software"],
                ["Pricing model only", "How you charge: per user, per project, retainer", "Complex enterprise deals"],
                ["Ranges with examples", "Typical project sizes with ranges", "Consulting and agency work"],
            ],
            "Four ways to be transparent without giving away negotiation room.",
        ),
        h2("How do you publish pricing without losing deals?"),
        cap("Publishing pricing safely means explaining what drives the price, showing a starting point, and making it easy to ask for a quote."),
        ol([
            "<strong>State your pricing model</strong> in one sentence at the top of the page.",
            "<strong>Show a starting price</strong> or typical range per plan or service.",
            "<strong>List what drives the price</strong>: users, volume, markets, implementation.",
            "<strong>Explain what is included</strong> and what costs extra.",
            "<strong>Date the page</strong> and update it with every change.",
            "<strong>Add a clear next step</strong>: a calculator, a quote request or a call.",
        ]),
    ] + faq([
        ("Will competitors use our prices against us?", "They can find them anyway through buyers. Being clear about what is included lets you compete on value, not on a hidden number."),
        ("What if every deal is custom?", "Publish the pricing model and typical ranges. Buyers mainly want to know if you fit their budget."),
        ("Does a pricing page help AI visibility?", "It gives assistants a first-party source for price questions, instead of third-party guesses."),
        ("How often should pricing pages be updated?", "With every change, plus a quarterly review. Outdated prices cause more harm than none."),
        ("Do buyers really shortlist on price?", "TrustRadius found 83% of buyers shortlist three or fewer products. With a list that short, unclear pricing can cost you a place on it."),
    ]) + [
        cta("Not sure how to show pricing for complex deals? " + QS + ". See our " + a("services", "/services/") + ", or try the " + a("ROI forecaster", "/roi-forecaster/") + "."),
    ],
)

# ------------------------------------------------------------------ AI Overviews Europe
POSTS["ai-overviews-europe-b2b"] = dict(
    title="AI Overviews in Europe: what B2B companies in the Benelux need to know",
    excerpt="AI Overviews reached Dutch searches in May 2025, after nine more European markets in March. What that means for B2B traffic in the Benelux, and how to respond.",
    category="ai-search", kw="AI Overviews Europe",
    blocks=[
        tldr("Google AI Overviews launched in Dutch in May 2025 (" + a("Google Netherlands", "google_aio_nl") + ") after a March 2025 rollout to nine more European countries (" + a("Google", "google_aio_eu") + "). Where they appear, the top result loses clicks: " + a("Ahrefs", "ahrefs_ctr") + " measured a 58% lower click-through rate."),
        p("Europe got AI Overviews later than the US. In March 2025, Google added Austria, Belgium, Germany, Ireland, Italy, Poland, Portugal, Spain and Switzerland. Belgium launched with English only (" + a("Google", "google_aio_eu") + ")."),
        p("Dutch followed on 20 May 2025, announced at Google I/O (" + a("Google Netherlands", "google_aio_nl") + "). For B2B companies in the Benelux, that means many of the search markets they sell in now show AI summaries."),
        p("The effect on clicks is measurable. Pew Research Center found that when Google showed an AI summary, 8% of visits included a click on a result, against 15% without one (" + a("Pew Research Center", "pew") + ")."),
        h2("What are AI Overviews?"),
        cap("An AI Overview is an AI-generated summary at the top of Google search results that answers the query and links to a few source pages."),
        p("They draw on Google's index. Pages that rank and answer clearly have a better chance of being linked, but many users read the summary and stop there."),
        h2("Where do AI Overviews appear in Europe?"),
        cap("AI Overview coverage in Europe is broad: most large EU markets, including the Netherlands in Dutch, Germany in German and Belgium in English."),
        table(
            ["Market", "Languages at launch", "Launch"],
            [
                ["Netherlands", "Dutch", "May 2025"],
                ["Belgium", "English", "March 2025"],
                ["Germany", "German, English", "March 2025"],
                ["Austria", "German, English", "March 2025"],
                ["Switzerland", "French, German, Italian, English", "March 2025"],
                ["Ireland", "English", "March 2025"],
            ],
            "Sources: Google, March 2025 and May 2025 announcements.",
        ),
        h2("How should B2B companies respond?"),
        cap("Responding to AI Overviews means measuring which of your queries trigger them, structuring pages to be the cited source, and building demand beyond clicks."),
        ol([
            "<strong>Check your top queries</strong> by market for AI Overviews, starting with commercial ones.",
            "<strong>Lead pages with the answer</strong> in two sentences under a question-shaped heading.",
            "<strong>Strengthen pages that already rank</strong>, since Overviews draw on Google's index.",
            "<strong>Track branded search</strong> in Search Console per country as a demand signal.",
            "<strong>Earn mentions in local trade media</strong> in each market you sell in.",
        ]),
    ] + faq([
        ("Do AI Overviews appear for B2B queries?", "Yes, especially for informational and comparison queries. Check your own queries per market."),
        ("Can we opt out of AI Overviews?", "Google offers snippet controls such as nosnippet, but they also limit how your page appears in regular results. Use them with care."),
        ("Do AI Overviews show in every language in Belgium?", "Belgium launched with English in March 2025. Check current availability in Dutch and French before planning."),
        ("How do we see AI Overview traffic?", "Search Console reports it as part of web search; it is not separated. Track click-through trends per query."),
        ("Does this affect paid search?", "Ads still appear around AI Overviews. Watch click-through on informational keywords and shift budget to commercial ones if needed."),
    ]) + [
        cta("Selling across the Benelux and DACH? " + QS + " and we check how AI Overviews affect your queries per market. See our " + a("services", "/services/") + "."),
    ],
)

# ------------------------------------------------------------------ passages AI quotes
POSTS["write-passages-ai-assistants-quote"] = dict(
    title="How to write passages AI assistants quote: lessons from the GEO study",
    excerpt="Adding quotations lifted visibility in AI answers by about 41% in the GEO study, while keyword stuffing made it worse. How to write B2B passages AI quotes.",
    category="ai-search", kw="write for AI search",
    blocks=[
        tldr("In the " + a("GEO study", "geo_paper_html") + " from Princeton and IIT Delhi, adding quotations lifted a source's visibility in generative answers by about 41%, adding statistics by about 31% and citing sources by about 28%. Keyword stuffing lowered it. Write specific, sourced passages that answer one question each."),
        p("The researchers built GEO-bench, a set of 10,000 queries, and tested how content changes affected a source's visibility in generative engine answers. The study was accepted at KDD 2024 (" + a("arXiv", "geo_paper") + ")."),
        p("Measured by position-adjusted word count, quotation addition scored about 41% above the unoptimized baseline, statistics addition about 31%, citing sources about 28% and fluency optimization about 28%. Keyword stuffing scored about 8% below baseline (" + a("Aggarwal et al.", "geo_paper_html") + ")."),
        p("Assistants quote a lot of sources: ChatGPT cites sources in 96% of its responses, about five per answer (" + a("Muck Rack, May 2026", "muckrack_may26") + "). Each one is a passage someone wrote."),
        h2("What makes a passage quotable for AI?"),
        cap("A quotable passage is a short, self-contained block that answers one question directly, with a specific fact, number or attributed quote."),
        p("Assistants work with passages, not whole pages. A paragraph that only makes sense after reading three others is hard to reuse."),
        h2("Which writing techniques had the biggest effect?"),
        cap("The most effective GEO technique is adding quotations, followed by statistics and cited sources, while keyword stuffing had a negative effect in the study."),
        table(
            ["Technique", "Change in visibility vs baseline", "What it looks like in B2B writing"],
            [
                ["Quotation addition", "About +41%", "A named expert or customer quote with context"],
                ["Statistics addition", "About +31%", "A specific number with its source and year"],
                ["Cite sources", "About +28%", "A link to the original study or document"],
                ["Fluency optimization", "About +28%", "Clear, readable sentences"],
                ["Keyword stuffing", "About -8%", "Repeating the target phrase; avoid it"],
            ],
            "Source: GEO: Generative Engine Optimization, Table 1, position-adjusted word count.",
        ),
        h2("How do you write passages AI assistants quote?"),
        cap("Writing quotable passages means one question per section, the answer first, then a sourced number or quote, in plain sentences under 25 words."),
        ol([
            "<strong>Use a question as the heading</strong>, phrased the way buyers ask.",
            "<strong>Answer in the first two sentences</strong>, so the passage stands alone.",
            "<strong>Add one specific number</strong> with its source and year.",
            "<strong>Add a quote</strong> from a named expert, customer or document where it helps.",
            "<strong>Link the original source</strong>, not a summary of it.",
            "<strong>Cut filler and repetition</strong>; keyword stuffing lowered visibility in the study.",
        ]),
    ] + faq([
        ("Is GEO writing different from good writing?", "Mostly not. The techniques that worked, quotes, numbers and sources, are what good analysts already do."),
        ("How long should a passage be?", "Short enough to stand alone: a heading, a two-sentence answer and one supporting fact."),
        ("Do the results apply to ChatGPT and Gemini?", "The study tested generative engines in a research setting. The direction fits what citation studies show, but exact gains will differ."),
        ("Should we add quotes to every section?", "Only where a real quote adds evidence. Invented or generic quotes damage trust."),
        ("Does keyword stuffing still hurt?", "It scored below baseline in the GEO study, and Google's spam policies prohibit it."),
    ]) + [
        cta("Want your key pages rewritten so AI quotes them? " + QS + ". See our " + a("services", "/services/") + ", or read " + a("which of your own pages AI assistants cite", "/which-company-pages-ai-assistants-cite/") + "."),
    ],
)

ORDER = [
    "claude-ai-search-b2b-citations",
    "gemini-ai-search-b2b-citations",
    "which-company-pages-ai-assistants-cite",
    "measure-ai-referral-traffic-ga4-b2b",
    "b2b-buyers-ai-before-sales",
    "schema-markup-b2b-ai-search",
    "publish-pricing-ai-search-b2b",
    "ai-overviews-europe-b2b",
    "write-passages-ai-assistants-quote",
]
