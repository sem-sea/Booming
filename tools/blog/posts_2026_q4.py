"""Lead-focused blog posts, batch November 2026.

Written to the geo-writer guide (.claude/skills/geo-writer/SKILL.md):
question H2s with 20-25 word answer capsules, key takeaways box, three
named-source statistics in the first 30%, a table or ordered list, an FAQ
section and internal links. Every statistic was checked against its
source on 20 November 2026 (URLs in SRC).

Run: python3 tools/blog/posts_2026_q4.py  -> writes tools/blog/out/<slug>.html
and prints the checks.
"""
import html
import json
import pathlib
import re

OUT = pathlib.Path(__file__).resolve().parent / "out"

SRC = {
    "forrester": "https://www.forrester.com/blogs/b2b_buyers_make_zero_click_buying_number_one/",
    "pew": "https://www.pewresearch.org/short-reads/2025/07/22/google-users-are-less-likely-to-click-on-links-when-an-ai-summary-appears-in-the-results/",
    "ahrefs_ctr": "https://ahrefs.com/blog/ai-overviews-reduce-clicks-update/",
    "ahrefs_mentions": "https://ahrefs.com/blog/ai-overview-brand-correlation/",
    "geo_paper": "https://arxiv.org/abs/2311.09735",
    "vercel": "https://vercel.com/blog/the-rise-of-the-ai-crawler",
    "openai_bots": "https://developers.openai.com/api/docs/bots",
    "anthropic_bots": "https://support.claude.com/en/articles/8896518",
    "perplexity_bots": "https://docs.perplexity.ai/guides/bots",
    "google_crawlers": "https://developers.google.com/crawling/docs/crawlers-fetchers/google-common-crawlers?hl=en",
    "whitecase_omnibus": "https://www.whitecase.com/insight-alert/eu-ai-omnibus-enters-force-amending-ai-act",
    "cloudflare": "https://www.cloudflare.com/press-releases/2025/cloudflare-just-changed-how-ai-crawlers-scrape-the-internet-at-large/",
    "google_spam": "https://developers.google.com/search/docs/essentials/spam-policies",
    "google_genai": "https://developers.google.com/search/docs/fundamentals/using-gen-ai-content",
    "ec_faq_art50": "https://digital-strategy.ec.europa.eu/en/faqs/transparency-obligations-under-article-50-ai-act",
    "ai_act_art50": "https://ai-act-service-desk.ec.europa.eu/en/ai-act/article-50",
    "orrick_omnibus": "https://www.orrick.com/en/Insights/2026/07/EU-AI-Act-Update-Digital-Omnibus-Finalizes-8-Compliance-Changes",
    "goodwin_art50": "https://www.goodwinlaw.com/en/insights/publications/2026/08/alerts-technology-dpc-eu-ai-act-transparency-obligations-now-in-force",
    "lewis_silkin": "https://www.lewissilkin.com/insights/2026/07/27/the-digital-omnibus-on-ai-enters-into-force-today-102nedo",
}


def a(text, key_or_url):
    url = SRC.get(key_or_url, key_or_url)
    if url.startswith("http"):
        return f'<a href="{url}" target="_blank" rel="noopener">{text}</a>'
    return f'<a href="{url}">{text}</a>'


def p(text):
    return f"<!-- wp:paragraph -->\n<p>{text}</p>\n<!-- /wp:paragraph -->"


def h2(text):
    return f'<!-- wp:heading -->\n<h2 class="wp-block-heading">{text}</h2>\n<!-- /wp:heading -->'


def h3(text):
    return f'<!-- wp:heading {{"level":3}} -->\n<h3 class="wp-block-heading">{text}</h3>\n<!-- /wp:heading -->'


def cap(text):
    return (
        '<!-- wp:paragraph {"className":"bv-capsule","style":{"typography":{"fontWeight":"500"}},"fontSize":"lg"} -->\n'
        f'<p class="bv-capsule has-lg-font-size" style="font-weight:500">{text}</p>\n'
        "<!-- /wp:paragraph -->"
    )


def tldr(text):
    return (
        '<!-- wp:group {"backgroundColor":"booming-50","style":{"border":{"radius":"0.75rem"},"spacing":{"padding":{"top":"1.25rem","right":"1.25rem","bottom":"1.25rem","left":"1.25rem"}}},"layout":{"type":"constrained"}} -->\n'
        '<div class="wp-block-group has-booming-50-background-color has-background" style="border-radius:0.75rem;padding:1.25rem">'
        '<!-- wp:paragraph {"fontSize":"sm","style":{"typography":{"fontWeight":"600","textTransform":"uppercase","letterSpacing":"0.08em"}}} -->\n'
        '<p class="has-sm-font-size" style="font-weight:600;text-transform:uppercase;letter-spacing:0.08em">Key takeaways</p>\n'
        "<!-- /wp:paragraph -->\n"
        f"<!-- wp:paragraph -->\n<p>{text}</p>\n<!-- /wp:paragraph --></div>\n"
        "<!-- /wp:group -->"
    )


def ol(items):
    lis = "".join(f"<!-- wp:list-item -->\n<li>{i}</li>\n<!-- /wp:list-item -->\n" for i in items)
    return f'<!-- wp:list {{"ordered":true}} -->\n<ol class="wp-block-list">{lis}</ol>\n<!-- /wp:list -->'


def ul(items):
    lis = "".join(f"<!-- wp:list-item -->\n<li>{i}</li>\n<!-- /wp:list-item -->\n" for i in items)
    return f'<!-- wp:list -->\n<ul class="wp-block-list">{lis}</ul>\n<!-- /wp:list -->'


def table(cols, rows, caption):
    head = "".join(f"<th>{c}</th>" for c in cols)
    body = "".join("<tr>" + "".join((f"<td><strong>{c}</strong></td>" if i == 0 else f"<td>{c}</td>") for i, c in enumerate(r)) + "</tr>" for r in rows)
    return (
        '<!-- wp:table {"className":"bv-compare"} -->\n'
        f'<figure class="wp-block-table bv-compare"><table><thead><tr>{head}</tr></thead><tbody>{body}</tbody></table>'
        f'<figcaption class="wp-element-caption">{caption}</figcaption></figure>\n'
        "<!-- /wp:table -->"
    )


def faq(pairs):
    out = [h2("Frequently asked questions")]
    for q, ans in pairs:
        out += [h3(q), p(ans)]
    return out


def cta(text):
    return (
        '<!-- wp:group {"backgroundColor":"booming-50","style":{"border":{"radius":"0.75rem"},"spacing":{"padding":{"top":"1.25rem","right":"1.25rem","bottom":"1.25rem","left":"1.25rem"}}},"layout":{"type":"constrained"}} -->\n'
        '<div class="wp-block-group has-booming-50-background-color has-background" style="border-radius:0.75rem;padding:1.25rem">'
        f"<!-- wp:paragraph -->\n<p>{text}</p>\n<!-- /wp:paragraph --></div>\n"
        "<!-- /wp:group -->"
    )


QS = a("Request a free 15-minute Boardroom Quickscan", "/boardroom-quickscan/")

POSTS = {}

# ---------------------------------------------------------------- 1
POSTS["how-to-choose-a-geo-agency"] = dict(
    title="How to choose a GEO agency: 10 questions to ask before you sign",
    excerpt="Choosing a GEO agency? Ask these 10 questions about prompts, measurement, technical fixes, content quality, authority and pricing before you sign.",
    category="ai-search",
    blocks=[
        tldr("94% of business buyers now use AI in their buying process, according to " + a("Forrester (2026)", "forrester") + ". A good GEO agency shows where your brand appears in AI answers today, fixes what blocks citation, and reports share of voice every month. Ask the 10 questions below before you sign."),
        p("Your next buyer will probably meet your brand in an AI answer before they meet your sales team. In Forrester's Buyers' Journey Survey 2025, generative AI and conversational search ranked as the most important information source for business buyers, ahead of vendor websites, product experts and sales reps (" + a("Forrester", "forrester") + ")."),
        p("Clicks are thinning out at the same time. " + a("Pew Research Center", "pew") + " tracked the browsing of 900 US adults in March 2025. When Google showed an AI summary, 8% of visits included a click on a search result, against 15% when it did not."),
        p("So every agency now sells GEO. Some do real work on prompts, crawler access and authority. Others relabel an SEO retainer or sell AI-written articles by the hundred. These questions separate the two."),
        h2("What is a GEO agency?"),
        cap("A GEO agency is a marketing partner that improves how often and how accurately AI assistants like ChatGPT, Perplexity and Gemini cite your brand."),
        p("GEO stands for generative engine optimization. The term comes from a 2024 study by researchers at Princeton and partners, presented at KDD, which found that content changes can lift a source's visibility in generative engine answers by up to 40% (" + a("Aggarwal et al., 2024", "geo_paper") + ")."),
        p("GEO overlaps with SEO, because Google AI Overviews and ChatGPT search still read indexed pages. But it adds two layers that SEO retainers often skip: measuring answers instead of rankings, and building mentions of your brand on other sites. That second layer matters. In an " + a("Ahrefs analysis of 75,000 brands (2025)", "ahrefs_mentions") + ", branded web mentions correlated with AI Overview visibility at 0.664, against 0.218 for backlinks."),
        h2("Why does the wrong GEO agency cost you pipeline?"),
        cap("The wrong GEO agency is one that bills for reports and volume while your brand stays missing from AI answers buyers use for shortlists."),
        p("Volume is the risk most buyers underestimate. Google's spam policies list using generative AI tools to generate many pages without adding value as scaled content abuse, and warn that sites breaking the policies may rank lower or disappear from results (" + a("Google Search Central", "google_spam") + "). An agency that pushes dozens of AI articles a month can damage the domain your AI visibility depends on."),
        p("The other failure is a report without fixes. A prompt audit tells you where you are missing. Nothing changes until someone edits pages, opens crawler access and earns mentions."),
        h2("Which 10 questions should you ask a GEO agency?"),
        cap("Vetting a GEO agency means asking ten questions about prompts, measurement, technical access, content quality, authority, attribution and pricing before you sign."),
        ol([
            "<strong>Which prompts will you track, and how did you pick them?</strong> A good answer maps 50 to 200 prompts to your buying journey, from problem questions to \"best [category] for [industry]\" shortlists, in every language your buyers use. Generic prompts produce flattering, useless scores.",
            "<strong>How do you measure AI visibility, and how often?</strong> Ask which assistants they cover (ChatGPT, Perplexity, Gemini, Microsoft Copilot, Google AI Overviews), which tool they use, and whether you get the raw answers. Forrester found business buyers are four times as likely as consumers to use Microsoft Copilot, so a B2B tracker without Copilot misses part of your market.",
            "<strong>Can AI crawlers read our site today?</strong> In " + a("Vercel's 2024 analysis", "vercel") + ", none of the major AI crawlers rendered JavaScript. The agency should check server-rendered HTML, robots.txt rules for OAI-SearchBot, Claude-SearchBot and PerplexityBot, and firewall settings in week one.",
            "<strong>What will you change on our site in the first 30 days?</strong> Expect named pages and named fixes: answer-first service pages, comparison tables, author pages, schema. A first month that only delivers a slide deck is a research project.",
            "<strong>How will you get our brand mentioned on other sites?</strong> Ask about digital PR, industry lists, review platforms, podcasts and communities, with examples of mentions they earned for other clients.",
            "<strong>Who writes the content, and who checks the facts?</strong> Google's guidance says it is critical to manually fact-check and review all AI-generated content (" + a("Google Search Central", "google_genai") + "). You want named writers, a reviewer from your side, and a source for every number.",
            "<strong>How many pages will you publish per month, and why that number?</strong> The right number follows from your prompt gaps, not from a package. Be careful with any plan built on volume.",
            "<strong>How do you connect AI visibility to pipeline?</strong> AI answers often send no click. Good agencies combine share of voice with branded search trends, a \"How did you hear about us?\" field that lists AI assistants, and notes from sales calls.",
            "<strong>What exactly does the fee include?</strong> Separate the diagnostic, often a fixed-price sprint, from the ongoing retainer, and check who implements: the agency, your developers, or both.",
            "<strong>What do we keep if we stop?</strong> You should own the prompt set, the raw answer data, the dashboards and every page. If tracking lives only in the agency's tool account, your baseline leaves with them.",
        ]),
        h2("How is a GEO agency different from an SEO agency or an in-house team?"),
        cap("A GEO agency is different from an SEO agency in what it measures: citations inside AI answers, not rankings and clicks in search results."),
        table(
            ["Compared on", "SEO agency", "GEO agency", "In-house team"],
            [
                ["Main goal", "Rankings and organic clicks", "Mentions and citations in AI answers", "Both, within team capacity"],
                ["What gets measured", "Positions, traffic, conversions", "Share of voice per prompt and assistant, cited sources", "Depends on tools and skills"],
                ["Typical work", "Technical SEO, content, links", "Prompt tracking, answer-ready pages, crawler access, brand mentions", "Content and site changes, little outreach"],
                ["Strength", "Mature methods and tools", "Built for buying journeys without clicks", "Product knowledge and speed"],
                ["Watch out for", "Ignores answers that send no click", "Volume packages, reports without fixes", "No tracking tools or PR reach"],
            ],
            "How the three options differ in practice.",
        ),
        h2("What red flags should end the conversation?"),
        cap("A GEO red flag is any promise you cannot verify, such as guaranteed ChatGPT rankings, secret prompt hacks, or content packages priced per hundred articles."),
        ul([
            "Guaranteed positions in ChatGPT or Gemini. Answers vary by user, location and model version.",
            "A visibility score without access to the raw answers behind it.",
            "Content packages priced by volume.",
            "No technical check of rendering and bot access.",
            "A contract that keeps your tracking data with the agency.",
        ]),
    ] + faq([
        ("How long does GEO take to show results?", "Assistants that search the live web, such as Perplexity and ChatGPT search, can pick up fixed pages within weeks. What models know from training changes more slowly, because it depends on new model versions."),
        ("Can my SEO agency also do GEO?", "Often partly. Technical SEO, structured content and indexation carry over. Check whether they track AI answers per prompt and build brand mentions, the two parts SEO retainers usually leave out."),
        ("Which AI assistants matter most for B2B?", "Start where your buyers work. Forrester found business buyers are twice as likely as consumers to use ChatGPT and four times as likely to use Microsoft Copilot, and 61% use private AI tools provided by their organization."),
        ("What does a GEO agency cost?", "Prices vary with scope, markets and how much implementation is included. Ask for a fixed-scope diagnostic first, then a retainer with named monthly deliverables. Our own pricing is on the services page."),
        ("Is GEO the same as AEO?", "They overlap. AEO, answer engine optimization, focuses on being the direct answer to a question. GEO covers the wider task of being mentioned and cited by generative AI, including your reputation on other sites. We compare all three in " + a("GEO vs SEO vs AEO", "/geo-vs-seo-vs-aeo-b2b-budget-2026/") + "."),
        ("Do we need GEO if our sales cycle is long and relationship-driven?", "Yes. Shortlists form before the first sales call, and Forrester reports that 94% of business buyers use AI in their buying process."),
    ]) + [
        cta("Want a second opinion before you sign with an agency? " + QS + " and we look at your AI visibility together. See our " + a("services and pricing", "/services/") + ", or read " + a("what a generative engine optimization audit includes", "/generative-engine-optimization-audit/") + "."),
    ],
)

# ---------------------------------------------------------------- 2
POSTS["ai-crawler-checklist-b2b-website"] = dict(
    title="Can ChatGPT read your website? A 12-point AI crawler checklist for B2B sites",
    excerpt="In Vercel's analysis no major AI crawler rendered JavaScript. This 12-point checklist shows if ChatGPT, Claude and Perplexity can read your B2B site.",
    category="ai-search",
    blocks=[
        tldr("None of the major AI crawlers rendered JavaScript in " + a("Vercel's 2024 analysis", "vercel") + ". If your site needs JavaScript to show its text, AI search may see an empty page. These 12 checks show in about 30 minutes whether ChatGPT, Claude and Perplexity can read you."),
        p("We learned this on our own site. Booming Venture's website started as a React single-page app built in Lovable. It looked fine in a browser and showed almost nothing to bots that do not run JavaScript. In 2026 we rebuilt it as a server-rendered WordPress site."),
        p("The traffic is real. Vercel counted 569 million GPTBot fetches and 370 million Claude fetches across its network in one month, about 20% of Googlebot's 4.5 billion (" + a("Vercel", "vercel") + "). And buyers use the answers: " + a("Forrester", "forrester") + " reports that 94% of business buyers use AI in their buying process."),
        h2("What is an AI crawler?"),
        cap("An AI crawler is a bot from an AI company, such as GPTBot or PerplexityBot, that fetches pages for model training or AI search answers."),
        p("There are three kinds, and they matter differently. Training crawlers collect pages for future models. Search crawlers build the index that AI search answers from. User-triggered fetchers open a page when someone asks about it. Blocking the wrong one can remove you from AI answers without anyone noticing."),
        table(
            ["Crawler", "Company", "What it does", "If you block it"],
            [
                ["GPTBot", "OpenAI", "Collects content for training models", "Your pages stay out of training; ChatGPT search is not affected"],
                ["OAI-SearchBot", "OpenAI", "Builds the index for ChatGPT search", "You do not appear in ChatGPT search answers"],
                ["ChatGPT-User", "OpenAI", "Opens pages a user asks about", "Not an automatic crawler, so it does not decide search inclusion"],
                ["ClaudeBot", "Anthropic", "Collects content for training", "Your future pages stay out of training data"],
                ["Claude-SearchBot", "Anthropic", "Indexes pages for Claude search answers", "Anthropic says this may reduce your visibility in Claude search"],
                ["PerplexityBot", "Perplexity", "Surfaces and links sites in Perplexity search", "You drop out of Perplexity results; it is not used for model training"],
                ["Google-Extended", "Google", "Controls use in Gemini Apps and Vertex AI", "No grounding with Google Search on Vertex AI; Google Search ranking is unaffected"],
            ],
            "Sources: " + a("OpenAI", "openai_bots") + ", " + a("Anthropic", "anthropic_bots") + ", " + a("Perplexity", "perplexity_bots") + ", " + a("Google", "google_crawlers") + ".",
        ),
        h2("Why do some websites look empty to AI crawlers?"),
        cap("An empty page for AI crawlers means text that appears only after JavaScript runs, or a request blocked by robots.txt or a firewall."),
        p("Single-page apps built with React, Vue or tools like Lovable often send a nearly empty HTML shell and fill it with JavaScript. Google renders JavaScript, so these sites can still rank. AI crawlers that skip rendering only see the shell."),
        p("Firewalls are the second cause. " + a("Cloudflare", "cloudflare") + ", which says it handles traffic for 20% of the web, announced in July 2025 that it blocks AI crawlers by default and leaves the choice to site owners. Many teams never made that choice on purpose."),
        p("The third cause is quieter. Aggressive rate limiting at your host can answer bots with 503 errors. We saw exactly that during our own audits, when fast crawling from one IP address triggered 503 responses at the hosting level."),
        h2("How do you check if ChatGPT can read your site?"),
        cap("Checking AI readability means testing four layers in order: rendered HTML, crawler access, page structure and trust signals, as in the 12 steps below."),
        ol([
            "<strong>Read the raw HTML.</strong> Open view-source in your browser, or fetch the page with curl, and search for a sentence from your main content. If it is not in the source, many AI crawlers will not see it.",
            "<strong>Reload with JavaScript turned off.</strong> Your headline, body text and navigation should still appear.",
            "<strong>Allow the search crawlers.</strong> OAI-SearchBot, Claude-SearchBot and PerplexityBot should be allowed in robots.txt if you want to appear in ChatGPT, Claude and Perplexity search.",
            "<strong>Decide on training crawlers on purpose.</strong> Blocking GPTBot or ClaudeBot keeps your pages out of future training data. Google says pages that disallow Google-Extended are not used for grounding with Google Search on Vertex AI.",
            "<strong>Check your firewall and CDN.</strong> Look for AI crawler and bot settings in Cloudflare, Wordfence or your host's panel, and confirm the crawlers you want get through.",
            "<strong>Test status codes.</strong> Request a few pages with an AI crawler user agent and confirm a 200 response, not 403 or 503.",
            "<strong>Lead every page with the answer.</strong> Use one H1, question-shaped H2s and a two-sentence answer directly under each. Assistants quote passages, not whole pages. Our guide on " + a("getting cited in Google AI Mode", "/how-to-get-cited-in-google-ai-mode/") + " shows the structure in detail.",
            "<strong>Keep key facts in text.</strong> Prices, specifications and proof points inside images or PDFs are easy to miss. Put them in HTML text or tables.",
            "<strong>Add structured data.</strong> Organization, Article and Person markup in JSON-LD tells crawlers who you are and who wrote the page.",
            "<strong>Keep sitemaps current.</strong> Submit an XML sitemap with accurate lastmod dates in Bing Webmaster Tools. Bing is the index behind Microsoft Copilot.",
            "<strong>Watch server speed under load.</strong> Slow responses and timeouts look like missing pages to a crawler with a limited budget.",
            "<strong>Show who is behind the content.</strong> A named author with credentials and an About page with company facts give assistants and readers a reason to trust the page.",
        ]),
        h2("What did we learn from rebuilding our own site?"),
        cap("Our main lesson is that rendering comes first: content, schema and prompt work only pay off after AI crawlers can read the HTML itself."),
        p("After the rebuild, our own crawl of the new site showed the full text in the HTML source, with headings, meta descriptions and JSON-LD structured data in place. Before, bots that skip JavaScript saw little more than an empty shell."),
        p("Two things surprised us. Host-level rate limiting answered our audit crawler with 503 errors, which bots would get as well. And our robots.txt still blocked AI training crawlers that we later decided to allow for brand visibility. Check both, even if your site looks fine in a browser."),
    ] + faq([
        ("Do AI crawlers run JavaScript?", "In Vercel's December 2024 analysis, none of the major AI crawlers rendered JavaScript. Googlebot does, which is why a JavaScript site can rank in Google and still be hard to read for ChatGPT or Claude."),
        ("Does blocking GPTBot remove us from ChatGPT search?", "No. OpenAI says a site can allow OAI-SearchBot to appear in search results while disallowing GPTBot. Inclusion in ChatGPT search depends on OAI-SearchBot."),
        ("Should a B2B company allow training crawlers?", "It is a business choice. Allowing them puts your public pages into future model knowledge, which can help brand recall in answers. Blocking them protects content you consider proprietary. Search crawlers should stay allowed either way."),
        ("Does Perplexity respect robots.txt?", "Perplexity says PerplexityBot is not used to crawl content for AI foundation models, and that Perplexity-User generally ignores robots.txt because a person requested the page."),
        ("How often should we run this check?", "After every redesign, CMS migration or firewall change, and at least once a quarter."),
    ]) + [
        cta("Not sure what AI crawlers see on your site? " + QS + " and we look at rendering, robots.txt and firewall settings with you. See our " + a("services", "/services/") + ", or read " + a("how to choose a GEO agency", "/how-to-choose-a-geo-agency/") + "."),
    ],
)

# ---------------------------------------------------------------- 3
POSTS["eu-ai-act-b2b-marketing-teams"] = dict(
    title="The EU AI Act for B2B marketing teams: what applies since 2 August 2026",
    excerpt="Since 2 August 2026, EU AI Act transparency rules apply. What B2B marketing teams must do about chatbots, AI images, deepfakes and AI-written content.",
    category="ai-marketing",
    blocks=[
        tldr("Article 50 of the EU AI Act has applied since 2 August 2026. For marketing teams that means disclosed chatbots, labelled deepfakes, vendors that mark AI output (2 December 2026 for tools already on the market) and human editorial control. Fines reach €15 million or 3% of turnover."),
        p("Marketing is one of the heaviest AI users in most companies, and the first part of the EU AI Act that touches daily marketing work is now live. Article 50, the transparency rule, has applied since 2 August 2026 (" + a("European Commission", "ec_faq_art50") + ")."),
        p("The July 2026 Digital Omnibus, Regulation (EU) 2026/1744 (" + a("White &amp; Case", "whitecase_omnibus") + "), moved the high-risk rules for Annex III systems to 2 December 2027 (" + a("Orrick", "orrick_omnibus") + "). It did not move Article 50. Breaches of these obligations can cost up to €15 million or 3% of worldwide annual turnover, whichever is higher (" + a("Goodwin", "goodwin_art50") + ")."),
        h2("What is Article 50 of the EU AI Act?"),
        cap("Article 50 is the EU AI Act's transparency rule: people must be told when they deal with an AI system or see certain AI-generated content."),
        p("It sets four duties, split between providers (who build or sell the AI system) and deployers (the companies that use it). The full text is on the " + a("AI Act Service Desk", "ai_act_art50") + "."),
        ol([
            "<strong>Interaction.</strong> Providers must design systems that talk with people, such as chatbots, so users know they deal with AI, unless that is obvious.",
            "<strong>Marking.</strong> Providers of systems that generate audio, images, video or text must mark the output in a machine-readable, detectable way. Assistive editing tools are exempt.",
            "<strong>Emotion recognition.</strong> Deployers of emotion recognition or biometric categorisation must inform the people exposed to it.",
            "<strong>Deepfakes and public-interest text.</strong> Deployers must disclose deepfakes, and AI-generated text published to inform the public on matters of public interest, unless a human reviewed it under editorial responsibility.",
            "<strong>Timing.</strong> All four apply from 2 August 2026, with one grace period for marking by existing tools.",
        ]),
        h2("What did the Digital Omnibus change?"),
        cap("The Digital Omnibus is the July 2026 amendment that delayed most high-risk AI rules but kept Article 50 transparency duties on schedule."),
        ul([
            "It entered into force on 27 July 2026.",
            "High-risk systems in Annex III, such as hiring and education tools, now apply from 2 December 2027. Annex I systems in regulated products follow on 2 August 2028.",
            "Generative tools already on the market before 2 August 2026 have until 2 December 2026 to mark their output.",
            "The AI literacy duty in Article 4 was softened: organizations must take measures that support AI literacy, and certifying each person is not required (" + a("Lewis Silkin", "lewis_silkin") + ").",
            "It adds new bans on nudification apps and on systems that generate child sexual abuse material.",
        ]),
        h2("Which marketing activities trigger obligations?"),
        cap("A marketing obligation under Article 50 is triggered by four uses: website chatbots, deepfakes of real people, unreviewed public-interest AI text and generative tools."),
        table(
            ["Marketing use", "What the rule requires", "Who is responsible", "Applies from"],
            [
                ["Website or messaging chatbot", "People must know they are talking to AI, unless it is obvious", "Provider, often your vendor; check your setup", "2 August 2026"],
                ["AI-generated images, video, audio or text", "Output marked in a machine-readable, detectable way", "Provider of the generative tool", "2 August 2026; 2 December 2026 for tools already on the market"],
                ["Deepfake of a real person, place or event", "Disclose that it is AI-generated or manipulated", "Deployer: your company", "2 August 2026"],
                ["AI-written text on matters of public interest", "Disclose AI generation, unless human review and editorial responsibility", "Deployer: your company", "2 August 2026"],
                ["Emotion recognition in ad or UX testing", "Inform the people exposed", "Deployer: your company", "2 August 2026"],
            ],
            "Summary of Article 50 for marketing use. General information, not legal advice.",
        ),
        h2("Does the AI Act require a label on AI-written blog posts?"),
        cap("An AI disclosure on a blog post is required only when it informs the public on matters of public interest and nobody reviewed it editorially."),
        p("The Commission's FAQ describes matters of public interest as consumer safety and economic, financial, political, scientific or cultural developments that may be a subject of public debate (" + a("European Commission", "ec_faq_art50") + "). A typical B2B how-to or product page is usually not that kind of text. Market commentary and research findings can be."),
        p("The exemption has two conditions: the text went through human review or editorial control, and a person or company holds editorial responsibility for the publication. In practice, name an editor and keep a short review log. Google expects the same discipline for search, since its guidance says all AI-generated content should be fact-checked and reviewed by a person (" + a("Google Search Central", "google_genai") + ")."),
        h2("How should a marketing team prepare?"),
        cap("Preparing a marketing team means mapping every AI tool and use, checking vendor marking, adding disclosures where needed, and assigning a human editor."),
        ol([
            "List every AI tool the team uses, including AI features inside software you already pay for.",
            "Mark which uses reach customers or the public: chatbots, images, video, voice and published text.",
            "Ask each generative-tool vendor how it marks output and whether it meets the 2 December 2026 deadline.",
            "Add a clear line such as \"You are chatting with an AI assistant\" to chatbots if the vendor does not show one.",
            "Write a deepfake rule: no synthetic real people, places or events without disclosure.",
            "Send AI-written public content through a named editor and keep a simple review log.",
            "Document the AI training you give the team as evidence of AI literacy measures under Article 4.",
        ]),
    ] + faq([
        ("Does Article 50 apply to companies outside the EU?", "Yes, when their AI output is used in the EU. The AI Act covers providers and deployers in third countries where the output of the AI system is used in the Union."),
        ("Do we have to label every AI-generated image?", "Not with a visible label in most cases. The provider of the generative tool must mark output in a machine-readable way. Your company must disclose deepfakes: content that resembles real people, places or events and could falsely appear authentic."),
        ("What are the fines for breaking Article 50?", "Up to €15 million or 3% of worldwide annual turnover, whichever is higher. For SMEs and start-ups the lower of the two applies."),
        ("When do the high-risk rules apply?", "From 2 December 2027 for Annex III systems such as hiring and education tools, and from 2 August 2028 for Annex I systems in regulated products. Typical marketing tools are not on these lists."),
        ("Is AI literacy training mandatory?", "Article 4 requires providers and deployers to take measures that support AI literacy. Since the Omnibus, certifying each employee is not required. Documenting training and awareness sessions is the practical approach."),
        ("Does Article 50 cover AI-written ad copy?", "Ad copy is rarely text published to inform the public on matters of public interest, so that disclosure duty usually does not apply. Deepfakes in ads always need a disclosure."),
    ]) + [
        p("This article is general information, not legal advice. Check your obligations with counsel for your situation."),
        cta("Booming Venture helps B2B marketing teams use AI in content, search and campaigns with workflows that hold up under these rules. For compliance programs and AI literacy training, our sister practice " + a("VerantwoordAI", "https://verantwoordai.nl/") + " advises enterprise and public sector teams. " + QS + ", or see our " + a("services", "/services/") + "."),
    ],
)

# ---------------------------------------------------------------- 4
POSTS["30-day-ai-visibility-sprint"] = dict(
    title="What happens in a 30-day AI visibility sprint? A week-by-week plan for B2B teams",
    excerpt="A 30-day AI visibility sprint measures where ChatGPT, Perplexity and Gemini mention your brand, fixes the blockers and sets up tracking. The week-by-week plan.",
    category="ai-search",
    blocks=[
        tldr("AI Overviews cut the click-through rate of the top Google result by 58% (" + a("Ahrefs", "ahrefs_ctr") + ", December 2025 data). A 30-day AI visibility sprint gives B2B teams a baseline, the first fixes and a tracking setup before they commit to a retainer."),
        p("B2B buyers increasingly get their answer without a click. " + a("Pew Research Center", "pew") + " found that 18% of the Google searches it tracked in March 2025 showed an AI summary, and visits with a summary ended in a click on a result 8% of the time, against 15% without one."),
        p("The effect on rankings is measurable. " + a("Ahrefs", "ahrefs_ctr") + " compared 300,000 keywords and found that an AI Overview lowered the click-through rate of the position-one result by 58% in December 2025. Meanwhile " + a("Forrester", "forrester") + " reports that 94% of business buyers use AI in their buying process."),
        p("Most marketing teams know this. Few know where their brand stands in the answers. A sprint fixes that in a month."),
        h2("What is an AI visibility sprint?"),
        cap("An AI visibility sprint is a fixed 30-day program that measures where AI assistants mention your brand, fixes the blockers and sets up monthly tracking."),
        p("It has a fixed scope and a fixed end date. You get a baseline you can rerun, a list of fixes with owners, the first fixes shipped, and a tracking setup you keep."),
        h2("Why start with a sprint instead of a retainer?"),
        cap("Starting with a sprint means you pay for a measured baseline and first fixes before committing budget to months of work you cannot yet judge."),
        p("GEO retainers are easy to sell and hard to evaluate. A sprint gives you hard numbers from your own prompts, so you can decide on a retainer, an in-house plan or both with data instead of promises."),
        h2("What happens in each week of the sprint?"),
        cap("A sprint week is a block with one job: measure, fix access and structure, rewrite key pages, then build authority and reporting."),
        table(
            ["Week", "Focus", "What you get"],
            [
                ["Week 1", "Baseline", "50 to 150 buyer prompts run in ChatGPT, Perplexity, Gemini, Microsoft Copilot and Google AI Overviews; share of voice against three competitors; the sources each assistant cites"],
                ["Week 2", "Access and structure", "Rendering, robots.txt and firewall check; schema on key pages; fixes shipped or briefed to your developer"],
                ["Week 3", "Answer-ready pages", "Three to five money pages rewritten with answer-first sections, comparison tables and FAQs"],
                ["Week 4", "Authority and reporting", "A target list of sites and lists that assistants cite in your category, an outreach plan, and a monthly tracking dashboard"],
            ],
            "The four sprint weeks and their deliverables.",
        ),
        h2("Which prompts should a B2B company track?"),
        cap("A B2B prompt set is a list of real buyer questions across three stages: problem questions, solution comparisons and vendor shortlists."),
        ul([
            "<strong>Problem:</strong> \"Why is our pipeline from organic search shrinking?\"",
            "<strong>Solution:</strong> \"What is the difference between GEO and SEO for B2B companies?\"",
            "<strong>Shortlist:</strong> \"Best security awareness training providers for Dutch financial services\"",
        ]),
        p("Write the prompts in every language your buyers use, and include Microsoft Copilot. Forrester found business buyers are four times as likely as consumers to use Copilot. Then look at which sites the assistants cite. In an " + a("Ahrefs study of 75,000 brands", "ahrefs_mentions") + ", branded web mentions correlated with AI Overview visibility at 0.664, against 0.218 for backlinks. That is why week four goes into mentions, not only links. If you want to try the baseline yourself first, see " + a("how to audit AI visibility without paying for Profound", "/audit-ai-visibility-without-paying-profound/") + "."),
        h2("How do you measure the result after 30 days?"),
        cap("Measuring a sprint means comparing the week-one baseline with a week-four rerun of the same prompts, plus crawler access, branded search and sales feedback."),
        p("Be realistic about the timeline. Assistants that search the live web can pick up fixed pages within weeks. What models remember from training moves more slowly. So judge the sprint on fixes shipped and early movement in retrieval-based answers, then track monthly."),
        p("Add one question to your forms and sales calls: \"How did you hear about us?\", with ChatGPT, Perplexity and Copilot as options. It is the simplest attribution you will get for answers that send no click. For more methods, read " + a("how to measure brand lift from LLM mentions", "/measure-brand-lift-llm-mentions/") + "."),
    ] + faq([
        ("How much does a 30-day sprint cost?", "It depends on markets, languages and how much implementation you want us to do. We quote a fixed price after a short intake. Our retainer pricing is on the services page."),
        ("Do we need developers for the sprint?", "Usually a few hours for technical fixes. We brief your developer, or implement directly when your site runs on WordPress."),
        ("Which tools are used for tracking?", "A prompt tracker that runs the prompts on a schedule across the main AI assistants. Google Search Console and Bing Webmaster Tools cover the search side."),
        ("Can we run a sprint ourselves?", "Yes, with the plan above. The hard parts are choosing prompts that match real buyer questions and earning mentions on other sites."),
        ("What happens after the sprint?", "You keep the prompt set, the data and the dashboard. Many teams continue with monthly tracking and a retainer for content and authority work; others take it in-house."),
    ]) + [
        cta("Want this plan run for your brand? " + QS + " and we scope the sprint together. Before you choose a partner, read " + a("how to choose a GEO agency", "/how-to-choose-a-geo-agency/") + " and the " + a("AI crawler checklist", "/ai-crawler-checklist-b2b-website/") + ". See our " + a("services", "/services/") + " for pricing."),
    ],
)

BANNED = ["delve", "elevate", "harness", "leverage", "navigate", "tapestry", "unlock", "realm", "landscape", "robust", "seamless", "foster", "moreover", "furthermore", "additionally", "it's worth noting", "in conclusion", "in today's fast-paced world", "ever-evolving", "game-changer", "revolutionize"]


def text_of(s):
    return html.unescape(re.sub(r"<[^>]+>", " ", re.sub(r"<!--.*?-->", " ", s, flags=re.S)))


def check(slug, post):
    content = "\n".join(post["blocks"])
    txt = text_of(content)
    words = txt.split()
    issues = []
    if "—" in content or "–" in content:
        issues.append("dash found")
    low = txt.lower()
    for b in BANNED:
        if re.search(r"\b" + re.escape(b) + r"\b", low):
            issues.append("banned: " + b)
    caps = re.findall(r'class="bv-capsule[^"]*"[^>]*>(.*?)</p>', content, re.S)
    for c in caps:
        n = len(text_of(c).split())
        if not 20 <= n <= 25:
            issues.append(f"capsule {n} words: {text_of(c)[:60]}")
        if not re.match(r"^\s*\S.*?\b(is|means|refers to)\b", text_of(c)):
            issues.append("capsule opening: " + text_of(c)[:50])
    qh2 = [h for h in re.findall(r"<h2[^>]*>(.*?)</h2>", content) if h.strip().endswith("?")]
    if len(qh2) < 3:
        issues.append("question H2s: %d" % len(qh2))
    cut = content[: int(len(content) * 0.30)]
    stats = len(re.findall(r'href="https?://(?!boomingventure)', cut))
    if stats < 3:
        issues.append(f"source links in first 30%: {stats}")
    if "Frequently asked questions" not in content:
        issues.append("no FAQ")
    if not re.search(r'href="/(services|funnel-calculator|roi-forecaster|unify-framework)/"', content):
        issues.append("no required internal link")
    sents = [s for s in re.split(r"(?<=[.!?])\s+", txt) if len(s.split()) > 2]
    avg = sum(len(s.split()) for s in sents) / max(1, len(sents))
    if len(post["excerpt"]) > 160:
        issues.append("excerpt %d chars" % len(post["excerpt"]))
    return dict(words=len(words), avg_sentence=round(avg, 1), capsules=len(caps), question_h2=len(qh2), first30_links=stats, issues=issues)


if __name__ == "__main__":
    OUT.mkdir(exist_ok=True)
    manifest = {}
    for slug, post in POSTS.items():
        content = "\n".join(post["blocks"])
        (OUT / f"{slug}.html").write_text(content)
        manifest[slug] = {k: post[k] for k in ("title", "excerpt", "category")}
        print(slug, json.dumps(check(slug, post)))
    (OUT / "manifest.json").write_text(json.dumps(manifest, indent=1))
