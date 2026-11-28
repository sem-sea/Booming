"""Text of the 1.30.0 growth pages, Dutch and English.

Facts and prices here are the single source: change a price once, rebuild
with build_pages.py, then run POST /bv/v1/import-nl?update=pages-v2.
All third-party numbers carry a named source with a link and a date.
"""
from blocks import (button, buttons, capsule, cards, cta, faq, group, h3, hero, lst, note, p, raw_block,
                    sec, src, stats, table)

SPRINT_NL, SPRINT_EN = "€3.950", "€3,950"
RETAINER_NL, RETAINER_EN = "€1.950", "€1,950"

S = {
    "ahrefs_ctr": "https://ahrefs.com/blog/ai-overviews-reduce-clicks-update/",
    "ahrefs_top10": "https://aivelocity.dev/news-insights/google-ai-overviews-citations-fell-76-to-38-percent",
    "ahrefs_brand": "https://ahrefs.com/blog/ai-brand-visibility-correlations",
    "ahrefs_fresh": "https://ahrefs.com/blog/do-ai-assistants-prefer-to-cite-fresh-content/",
    "listicles": "https://www.ranketai.com/en/blog/deep-dive-self-promotion-listicle-ai-citation-2026-07-06",
    "clutch": "https://clutch.co/resources/what-ai-tells-b2b-buyers",
    "meltwater": "https://www.meltwater.com/en/blog/linkedin-ai-visibility-study",
    "analyze": "https://www.tryanalyze.ai/blog/state-of-ai-search-top-domains",
    "vercel": "https://vercel.com/blog/the-rise-of-the-ai-crawler",
    "geo_paper": "https://arxiv.org/html/2311.09735v3",
    "forrester": "https://www.forrester.com/press-newsroom/forrester-2026-the-state-of-business-buying/",
    "google_ai": "https://developers.google.com/search/docs/fundamentals/ai-optimization-guide",
    "google_nl": "https://blog.google/intl/nl-nl/product/zoeken-kijken/de-ai-modus-komt-naar-google-zoeken-in-nederland/",
    "gsc_ai": "https://www.quattr.com/blog/search-console-generative-ai-report",
    "bing_ai": "https://blogs.bing.com/search/June-2026/New-AI-Visibility-Insights-in-Bing-Webmaster-Tools-Intents-Topics-Citation-Share-Compare",
    "sel_chatgpt": "https://searchengineland.com/chatgpt-retrieval-stack-index-cache-pages-485036",
    "sparktoro": "https://searchengineland.com/ai-recommendation-lists-rarely-repeat-study-468076",
    "geo_agency_index": "https://geo-index.nl/",
    "searchlab_cost": "https://searchlab.nl/kosten/wat-kost-geo",
    "harundo": "https://harundo.nl/beste-geo-bureau",
    "seoptimate": "https://seoptimate.com/nl/blog/geo-bureaus-nederland-vergeleken/",
    "wildbos": "https://wildbos.com/blog/beste-geo-bureaus-nederland",
    "goldlemon": "https://goldlemon.nl/geo-bureau/",
    "report_nl": "/nl/blog/ai-vindbaarheid-400-b2b-websites-november-2026/",
    "report_en": "/ai-search-readiness-400-b2b-websites-november-2026/",
}


def a(text, key):
    return src(text, S[key])


# --------------------------------------------------------------------------- GEO service
def geo_service(nl):
    if nl:
        body = [
            hero("GEO voor B2B", 'GEO voor B2B: genoemd worden door <span class="bv-gradient-text">ChatGPT, Claude en Gemini</span>',
                 "Booming Venture zorgt dat AI-assistenten je bedrijf noemen als B2B-kopers vragen welke leverancier ze moeten kiezen. Met een nulmeting op echte koopvragen, een vaste prijs en elke maand een meting met foutmarge.",
                 facts=[("20", "koopvragen in de nulmeting"), ("4", "AI-assistenten gemeten"), ("95%", "foutmarge bij elk AI-getal"), ("30", "dagen voor de sprint")],
                 btns=[button("Plan een kennismaking", "/nl/#contact", "gradient"), button("Doe eerst de gratis AI-check", "/nl/ai-check/", "outline")]),
            sec("Wat is GEO", "Wat is GEO voor B2B-bedrijven?",
                "GEO is het werk dat ervoor zorgt dat AI-assistenten zoals ChatGPT, Claude, Gemini en Perplexity je bedrijf noemen en je website als bron gebruiken.",
                [stats([
                    ("58%", "minder klikken op de nummer 1 in Google als er een AI-overzicht boven staat, gemeten over 300.000 zoekwoorden.", "Bron: " + a("Ahrefs, februari 2026", "ahrefs_ctr")),
                    ("52%", "van de AI-antwoorden van Claude op keuzevragen zonder bewijswoord verwijst naar websites van aanbieders zelf.", "Bron: " + a("Clutch, september 2026", "clutch")),
                    ("11 tot 13%", "van alle B2B-citaties gaat naar de tien grootste domeinen. De rest is verdeeld over duizenden sites, ook kleinere merken.", "Bron: " + a("Analyze, augustus 2026", "analyze")),
                 ]),
                 p("Kopers beginnen steeds vaker bij AI. Forrester ziet dat B2B-kopers hun zoektocht starten met generatieve AI, terwijl een gemiddelde aankoop 13 interne en 9 externe betrokkenen heeft (" + a("Forrester, januari 2026", "forrester") + "). Wie in die antwoorden ontbreekt, staat niet op de shortlist."),
                 p("GEO bouwt op SEO. Google zegt zelf dat optimaliseren voor AI-zoeken \"still SEO\" is (" + a("Google Search Central", "google_ai") + "). Het verschil zit in wat je meet en waar je aan werkt: vermeldingen in AI-antwoorden, je eigen pagina's als bron en wat andere sites over je schrijven.")]),
            sec("Aanbod", "Wat krijg je en wat kost het?",
                "Een GEO-traject bij Booming Venture is een sprint van 30 dagen voor " + SPRINT_NL + " met daarna, als je wilt, maandelijkse begeleiding vanaf " + RETAINER_NL + " per maand.",
                [table(["Onderdeel", "Wat je krijgt", "Prijs (excl. btw)", "Looptijd"], [
                    ["<strong>Gratis AI-check</strong>", "Websitescore op 100 en je verschil met de leider van je sector", "€0", "30 seconden"],
                    ["<strong>AI Visibility Sprint</strong>", "Nulmeting op 20 koopvragen in vier assistenten, technische basis, drie antwoordpagina's, tweede meting en een plan voor 90 dagen", SPRINT_NL + " eenmalig", "30 dagen"],
                    ["<strong>GEO-begeleiding</strong>", "Maandelijkse meting met foutmarge, twee nieuwe of verbeterde antwoordpagina's, werk aan vermeldingen en reviews, maandrapport", "Vanaf " + RETAINER_NL + " per maand", "Maandelijks opzegbaar"],
                 ], "Prijzen van november 2026. Advertentiebudget of betaalde vermeldingen vallen erbuiten.", "bv-gip-table bv-price-table"),
                 p("Voor bedrijven met meerdere landen of meer dan 20 koopvragen maken we een voorstel op maat. De sprint staat los: je zit nergens aan vast.")]),
            sec("Zo werkt de sprint", "Wat gebeurt er in de 30 dagen?",
                "De sprint is een vaste route van nulmeting, techniek, content en tweede meting, zodat je na 30 dagen weet waar je staat en wat werkt.",
                [lst([
                    "<strong>Week 1: nulmeting.</strong> We stellen 20 koopvragen uit jouw markt aan ChatGPT, Claude, Gemini en Perplexity, met zoeken aan en meerdere keren per vraag. Je ziet per assistent hoe vaak je genoemd wordt, wie in jouw plaats genoemd wordt en welke websites als bron dienen. Daarnaast doen we de zeven websitechecks van de B2B GEO Index en zetten we je naast de bedrijven in jouw sector.",
                    "<strong>Week 2: technische basis.</strong> AI-crawlers in robots.txt en je firewall, tekst in de HTML van de server, Organization- en Person-schema met sameAs, taalversies met hreflang en een sitemap die klopt.",
                    "<strong>Week 3: antwoordpagina's.</strong> Drie pagina's die de vragen van je kopers direct beantwoorden, met cijfers, bronnen en een vergelijking. Of de drie pagina's die je al hebt, herschreven zodat AI ze kan citeren.",
                    "<strong>Week 4: tweede meting en plan.</strong> Dezelfde vragen opnieuw, met het verschil en de foutmarge. Je krijgt een plan voor 90 dagen met de sites, lijstjes en reviewplatforms die in jouw markt het vaakst geciteerd worden.",
                 ], ordered=True),
                 note("De meting volgt de " + '<a href="/nl/b2b-geo-index/methode/">methode van de B2B GEO Index</a>' + ": vijf koopredenen per vraag (beste aanbieder, aanrader, shortlist, reputatie en overstappen), elke assistent met de eigen zoekfunctie en een foutmarge van 95% volgens Wilson.")]),
            sec("Resultaatafspraak", "Wat beloven we wel?",
                "Onze resultaatafspraak is een nulmeting en een herhaling met precies dezelfde vragen, zodat je groei ziet in cijfers met een foutmarge.",
                [p("AI-assistenten geven bij dezelfde vraag steeds andere lijsten. De kans dat je twee keer dezelfde lijst merken krijgt, is kleiner dan 1 op 100 (" + a("SparkToro en Gumshoe, via Search Engine Land", "sparktoro") + "). Daarom meten we herhaald en tonen we een marge in plaats van een vaste plek."),
                 p("Wat je wel krijgt: elke maand dezelfde vragen, dezelfde assistenten en een eerlijk verschil. Valt een stijging binnen de marge, dan zeggen we dat ook.")]),
            sec("Voor wie", "Voor welke bedrijven werkt dit?",
                "Het GEO-traject is gemaakt voor B2B-bedrijven die verkopen via een shortlist: software, IT-diensten, zakelijke dienstverlening, industrie en logistiek.",
                [cards([
                    ("B2B-tech en SaaS", [p("Kopers vragen AI om alternatieven en vergelijkingen. Je wilt genoemd worden naast de bekende namen, met de juiste categorie en prijs.")]),
                    ("Zakelijke dienstverleners", [p("Advies, juridisch, werving en marketing. AI noemt hier vaak lijstjes en reviewplatforms, dus daar begint het werk.")]),
                    ("Industrie en logistiek", [p("Lange verkoopcycli en technische kopers. AI moet je specialisme en je markten goed kunnen beschrijven, in het Nederlands en Engels.")]),
                    ("Internationaal vanuit Nederland", [p("We meten en schrijven in het Nederlands en Engels. De B2B GEO Index volgt 200 Nederlandse en 200 internationale bedrijven als benchmark.")]),
                 ])]),
            sec("Wie doet het werk", "Wie zit er achter Booming Venture?",
                "Booming Venture is een GEO-bureau uit Rotterdam, opgericht door Ben Verschuur, die ook de maandelijkse B2B GEO Index van 400 bedrijven maakt.",
                [p("Ben heeft meer dan 15 jaar ervaring in performance- en growthmarketing bij onder meer Knab, Aegon, DPDK en Zeelander Yachts. Hij doet de strategie en de metingen zelf en werkt met vaste specialisten voor techniek en content, per project bijgeschaald."),
                 p("Booming Venture, Breedveldsingel 1, 3055 PG Rotterdam. KvK 96442018. Je spreekt Ben direct via het " + '<a href="/nl/#contact">contactformulier</a>' + " of " + '<a href="https://www.linkedin.com/in/benverschuur" target="_blank" rel="noopener">LinkedIn</a>' + ". Bewijs van ons eigen werk staat bij de " + '<a href="/nl/cases/">cases</a>' + "."),
             p("Achtergrond vind je bij de " + '<a href="/nl/ai-zoeken-b2b-statistieken/">cijfers over AI-zoeken in B2B</a>' + ", de " + '<a href="/nl/b2b-geo-index/">B2B GEO Index</a>' + " met een pagina per sector en de " + '<a href="/nl/b2b-geo-index/methode/">methode</a>' + ". Zit je in de regio Rotterdam, lees dan over ons " + '<a href="/nl/geo-bureau-rotterdam/">GEO-bureau in Rotterdam</a>' + ".")]),
            faq(True, [
                ("Wat kost GEO bij Booming Venture?", "De AI Visibility Sprint kost " + SPRINT_NL + " eenmalig, excl. btw. Daarna kun je doorgaan met maandelijkse begeleiding vanaf " + RETAINER_NL + " per maand, maandelijks opzegbaar. Prijzen van andere bureaus vind je op onze pagina Wat kost GEO?"),
                ("Hoe snel zie ik resultaat?", "De technische basis werkt zodra crawlers je pagina's opnieuw lezen, vaak binnen enkele weken. Vermeldingen in AI-antwoorden groeien langzamer, omdat ze ook afhangen van wat andere sites over je schrijven. Na 30 dagen meten we het verschil op dezelfde vragen."),
                ("Wat is het verschil tussen GEO en SEO?", "SEO richt zich op posities in Google en Bing. GEO richt zich op vermeldingen en bronnen in AI-antwoorden. Ze bouwen op dezelfde basis: een goede SEO-site is het startpunt voor GEO."),
                ("Garanderen jullie een plek in ChatGPT?", "Nee, niemand kan dat eerlijk beloven, omdat antwoorden per keer wisselen. We beloven een nulmeting en een herhaling met dezelfde vragen en een foutmarge, zodat je groei aantoonbaar is."),
                ("Kan ik eerst gratis testen?", "Ja. De gratis AI-check geeft in 30 seconden je websitescore en je verschil met de leider van je sector. De sprint begint met een kennismaking van 30 minuten zonder kosten."),
                ("Werken jullie ook in het Engels en voor andere landen?", "Ja. We meten en schrijven in het Nederlands en Engels, en voor andere markten stemmen we de koopvragen af op het land en de taal van je kopers."),
            ]),
            cta("Weten waar je staat in AI?", "Begin met een kennismaking van 30 minuten. Je krijgt direct drie koopvragen uit jouw markt met wat ChatGPT en Claude nu antwoorden.",
                [button("Plan een kennismaking", "/nl/#contact"), button("Doe de gratis AI-check", "/nl/ai-check/", "outline")],
                "Of bekijk eerst " + '<a href="/nl/wat-kost-geo/">wat GEO kost bij andere bureaus</a>' + "."),
        ]
        return {"parent": "nl/diensten", "slug": "geo", "title": "GEO voor B2B", "content": "\n".join(body),
                "meta": {"_yoast_wpseo_title": "GEO-bureau voor B2B: sprint " + SPRINT_NL + ", daarna vanaf " + RETAINER_NL + " p/m | Booming Venture",
                         "_yoast_wpseo_metadesc": "Word genoemd door ChatGPT, Claude en Gemini. Nulmeting op 20 koopvragen, technische basis, antwoordpagina's en elke maand een meting met foutmarge. Vaste prijs."}}
    body = [
        hero("GEO for B2B", 'GEO for B2B: get named by <span class="bv-gradient-text">ChatGPT, Claude and Gemini</span>',
             "Booming Venture makes AI assistants name your company when B2B buyers ask which supplier to choose. With a baseline on real buyer questions, a fixed price and a monthly measurement with a margin of error.",
             facts=[("20", "buyer questions in the baseline"), ("4", "AI assistants measured"), ("95%", "margin of error on every AI number"), ("30", "days for the sprint")],
             btns=[button("Book an intro call", "/#contact", "gradient"), button("Run the free AI check first", "/ai-visibility-check/", "outline")]),
        sec("What GEO is", "What is GEO for B2B companies?",
            "GEO is the work that makes AI assistants such as ChatGPT, Claude, Gemini and Perplexity name your company and use your website as a source.",
            [stats([
                ("58%", "fewer clicks on the number 1 result in Google when an AI Overview sits above it, measured across 300,000 keywords.", "Source: " + a("Ahrefs, February 2026", "ahrefs_ctr")),
                ("52%", "of Claude's answers to buying questions without proof words point to the providers' own websites.", "Source: " + a("Clutch, September 2026", "clutch")),
                ("11 to 13%", "of all B2B citations go to the ten biggest domains. The rest is spread over thousands of sites, smaller brands included.", "Source: " + a("Analyze, August 2026", "analyze")),
             ]),
             p("Buyers start with AI more and more. Forrester sees B2B buyers begin their search with generative AI, while a typical purchase involves 13 internal and 9 external people (" + a("Forrester, January 2026", "forrester") + "). If you are missing from those answers, you are missing from the shortlist."),
             p("GEO builds on SEO. Google itself says optimizing for AI search \"is still SEO\" (" + a("Google Search Central", "google_ai") + "). The difference is what you measure and work on: mentions in AI answers, your own pages as sources and what other sites write about you.")]),
        sec("Offer", "What do you get and what does it cost?",
            "A GEO programme at Booming Venture is a 30-day sprint for " + SPRINT_EN + ", followed, if you want, by monthly support from " + RETAINER_EN + " a month.",
            [table(["Part", "What you get", "Price (excl. VAT)", "Term"], [
                ["<strong>Free AI check</strong>", "Website score out of 100 and your gap with the leader of your sector", "€0", "30 seconds"],
                ["<strong>AI Visibility Sprint</strong>", "Baseline on 20 buyer questions in four assistants, technical basis, three answer pages, a second measurement and a 90-day plan", SPRINT_EN + " one-off", "30 days"],
                ["<strong>GEO support</strong>", "Monthly measurement with a margin of error, two new or improved answer pages, work on mentions and reviews, monthly report", "From " + RETAINER_EN + " a month", "Cancel monthly"],
             ], "Prices as of November 2026. Ad budgets or paid listings are not included.", "bv-gip-table bv-price-table"),
             p("For companies in several countries or with more than 20 buyer questions we make a tailored proposal. The sprint stands alone: nothing ties you in.")]),
        sec("How the sprint works", "What happens in the 30 days?",
            "The sprint is a fixed route of baseline, technical work, content and a second measurement, so after 30 days you know where you stand and what works.",
            [lst([
                "<strong>Week 1: baseline.</strong> We ask ChatGPT, Claude, Gemini and Perplexity 20 buyer questions from your market, with search on and several times per question. You see per assistant how often you are named, who is named instead of you and which websites serve as sources. We also run the seven website checks of the B2B GEO Index and put you next to the companies in your sector.",
                "<strong>Week 2: technical basis.</strong> AI crawlers in robots.txt and your firewall, text in the server HTML, Organization and Person schema with sameAs, language versions with hreflang and a sitemap that is right.",
                "<strong>Week 3: answer pages.</strong> Three pages that answer your buyers' questions directly, with numbers, sources and a comparison. Or the three pages you already have, rewritten so AI can quote them.",
                "<strong>Week 4: second measurement and plan.</strong> The same questions again, with the change and the margin of error. You get a 90-day plan with the sites, lists and review platforms cited most in your market.",
             ], ordered=True),
             note("The measurement follows the " + '<a href="/b2b-geo-index/methodology/">B2B GEO Index methodology</a>' + ": five buying reasons per question (best provider, recommendation, shortlist, reputation and switching), each assistant with its own search and a 95% Wilson margin of error.")]),
        sec("Result agreement", "What do we promise?",
            "Our result agreement is a baseline and a repeat with exactly the same questions, so you see growth in numbers with a margin of error.",
            [p("AI assistants give different lists for the same question. The chance of getting the same list of brands twice is under 1 in 100 (" + a("SparkToro and Gumshoe, via Search Engine Land", "sparktoro") + "). That is why we measure repeatedly and show a margin instead of a fixed place."),
             p("What you do get: the same questions every month, the same assistants and an honest change. If a rise falls within the margin, we say so.")]),
        sec("Who it is for", "Which companies is this for?",
            "The GEO programme is built for B2B companies that sell through a shortlist: software, IT services, professional services, industry and logistics.",
            [cards([
                ("B2B tech and SaaS", [p("Buyers ask AI for alternatives and comparisons. You want to be named next to the known names, in the right category and price range.")]),
                ("Professional services", [p("Consulting, legal, recruitment and marketing. AI often names lists and review platforms here, so that is where the work starts.")]),
                ("Industry and logistics", [p("Long sales cycles and technical buyers. AI has to describe your specialism and markets correctly, in Dutch and English.")]),
                ("International from the Netherlands", [p("We measure and write in Dutch and English. The B2B GEO Index tracks 200 Dutch and 200 international companies as a benchmark.")]),
             ])]),
        sec("Who does the work", "Who is behind Booming Venture?",
            "Booming Venture is a GEO agency from Rotterdam, founded by Ben Verschuur, who also builds the monthly B2B GEO Index of 400 companies.",
            [p("Ben has more than 15 years of experience in performance and growth marketing at companies such as Knab, Aegon, DPDK and Zeelander Yachts. He runs the strategy and measurements himself and works with regular specialists for technical work and content, scaled per project."),
             p("Booming Venture, Breedveldsingel 1, 3055 PG Rotterdam, the Netherlands. Chamber of Commerce 96442018. You reach Ben directly through the " + '<a href="/#contact">contact form</a>' + " or " + '<a href="https://www.linkedin.com/in/benverschuur" target="_blank" rel="noopener">LinkedIn</a>' + ". Proof of our own work is on the " + '<a href="/cases/">cases</a>' + " page."),
             p("For background, see the " + '<a href="/ai-search-b2b-statistics/">AI search statistics for B2B</a>' + ", the " + '<a href="/b2b-geo-index/">B2B GEO Index</a>' + " with a page per sector and its " + '<a href="/b2b-geo-index/methodology/">methodology</a>' + ". Based near Rotterdam? Read about our " + '<a href="/geo-agency-rotterdam/">GEO agency in Rotterdam</a>' + ".")]),
        faq(False, [
            ("What does GEO cost at Booming Venture?", "The AI Visibility Sprint costs " + SPRINT_EN + " one-off, excluding VAT. After that you can continue with monthly support from " + RETAINER_EN + " a month, cancellable monthly. Prices of other agencies are on our page about GEO pricing in the Netherlands."),
            ("How fast will I see results?", "The technical basis works once crawlers read your pages again, often within a few weeks. Mentions in AI answers grow more slowly, because they also depend on what other sites write about you. After 30 days we measure the change on the same questions."),
            ("What is the difference between GEO and SEO?", "SEO is about positions in Google and Bing. GEO is about mentions and sources in AI answers. They share the same base: a good SEO site is the starting point for GEO."),
            ("Do you guarantee a place in ChatGPT?", "No, nobody can honestly promise that, because answers change every time. We promise a baseline and a repeat with the same questions and a margin of error, so growth can be shown."),
            ("Can I test for free first?", "Yes. The free AI check gives your website score and your gap with your sector leader in 30 seconds. The sprint starts with a free 30-minute intro call."),
            ("Do you work in English and for other countries?", "Yes. We measure and write in Dutch and English, and for other markets we match the buyer questions to the country and language of your buyers."),
        ]),
        cta("Want to know where you stand in AI?", "Start with a 30-minute intro call. You get three buyer questions from your market right away, with what ChatGPT and Claude answer today.",
            [button("Book an intro call", "/#contact"), button("Run the free AI check", "/ai-visibility-check/", "outline")],
            "Or first see " + '<a href="/geo-pricing-netherlands/">what GEO costs at other agencies</a>' + "."),
    ]
    return {"parent": "services", "slug": "geo", "title": "GEO for B2B", "content": "\n".join(body),
            "meta": {"_yoast_wpseo_title": "B2B GEO agency: sprint " + SPRINT_EN + ", then from " + RETAINER_EN + "/month | Booming Venture",
                     "_yoast_wpseo_metadesc": "Get named by ChatGPT, Claude and Gemini. Baseline on 20 buyer questions, technical basis, answer pages and monthly measurement. Fixed price."}}


# --------------------------------------------------------------------------- Rotterdam
def rotterdam(nl):
    if nl:
        body = [
            hero("Rotterdam", 'GEO-bureau in <span class="bv-gradient-text">Rotterdam</span> voor B2B-bedrijven',
                 "Booming Venture zit aan de Breedveldsingel in Rotterdam en helpt B2B-bedrijven uit de regio om genoemd te worden in ChatGPT, Claude, Gemini en Perplexity. We komen langs, meten en werken in het Nederlands en Engels.",
                 facts=[("Rotterdam", "Breedveldsingel 1, 3055 PG"), ("400", "B2B-bedrijven in onze maandelijkse index"), ("NL en EN", "meten en schrijven"), ("30", "dagen voor een sprint")],
                 btns=[button("Plan een kennismaking", "/nl/#contact", "gradient"), button("Bekijk het GEO-traject", "/nl/diensten/geo/", "outline")]),
            sec("Waarom lokaal", "Wat doet een GEO-bureau in Rotterdam voor je?",
                "Een GEO-bureau in Rotterdam is een partner in de regio die meet hoe vaak AI-assistenten je bedrijf noemen en daarna je site en vermeldingen verbetert.",
                [stats([
                    ("72", "is de gemiddelde websitescore op 100 van de 200 Nederlandse B2B-bedrijven in de B2B GEO Index, tegen 75 voor de internationale groep.", "Bron: " + '<a href="' + S["report_nl"] + '">B2B GEO Index, 25 november 2026</a>'),
                    ("45%", "van de Nederlandse B2B-homepages heeft Organization-schema. De rest vertelt AI niet in een vaste vorm wie het bedrijf is.", "Bron: " + '<a href="' + S["report_nl"] + '">B2B GEO Index, 25 november 2026</a>'),
                    ("8 oktober 2025", "kwam de AI-modus van Google naar Nederland, met vragen die twee tot drie keer zo lang zijn als gewone zoekopdrachten.", "Bron: " + a("Google", "google_nl")),
                 ]),
                 p("Voor Rotterdamse B2B-bedrijven is dat een kans. In logistiek, chemie, energie en de groothandel zijn de kopers vaak internationaal en stellen ze hun vragen in het Engels. Wie in beide talen goed vindbaar is, staat vaker op de shortlist.")]),
            sec("Sectoren", "Welke Rotterdamse sectoren meten we al?",
                "De B2B GEO Index meet elke maand tien Nederlandse en tien internationale bedrijven in 20 sectoren, waaronder de sectoren die in de Rotterdamse regio groot zijn.",
                [lst([
                    '<a href="/nl/b2b-geo-index/transport-logistiek/">Transport en logistiek</a>: welke vervoerders en logistieke dienstverleners AI noemt en welke bronnen het gebruikt.',
                    '<a href="/nl/b2b-geo-index/chemie/">Chemie</a>: hoe klaar de websites van chemiebedrijven zijn voor AI-crawlers.',
                    '<a href="/nl/b2b-geo-index/energie/">Energie</a>: de netbeheerders en energiebedrijven in de index en hun scores.',
                    '<a href="/nl/b2b-geo-index/groothandel/">Groothandel</a>: waar groothandels punten verliezen op schema, taal en actualiteit.',
                    '<a href="/nl/b2b-geo-index/infrastructuur-waterbouw/">Infrastructuur en waterbouw</a>: bouwers en waterbouwers en hoe AI ze beschrijft.',
                 ]),
                 p("Zit je bedrijf in een van deze sectoren? Dan kun je je score direct naast de koplopers leggen met de " + '<a href="/nl/ai-check/">gratis AI-check</a>' + ".")]),
            sec("Werkwijze", "Hoe werken we met bedrijven in de regio?",
                "We werken in een vaste route: kennismaking op locatie of online, een nulmeting op jullie eigen koopvragen en daarna een sprint van 30 dagen.",
                [lst([
                    "<strong>Kennismaking.</strong> Een uur bij jullie op kantoor in de regio Rotterdam of online. We kijken samen naar drie koopvragen en wat AI daar nu over jullie zegt.",
                    "<strong>Nulmeting.</strong> 20 koopvragen in vier AI-assistenten, met een foutmarge. Zo weten we waar jullie staan tegenover concurrenten.",
                    "<strong>Sprint.</strong> Techniek, antwoordpagina's en een plan voor vermeldingen. Details en prijzen staan op de pagina " + '<a href="/nl/diensten/geo/">GEO voor B2B</a>' + ".",
                    "<strong>Maandelijks.</strong> Wie wil, gaat door met een maandelijkse meting en werk aan vermeldingen, maandelijks opzegbaar.",
                 ], ordered=True)]),
            sec("Contact", "Waar vind je ons?",
                "Booming Venture is gevestigd aan de Breedveldsingel 1, 3055 PG Rotterdam, en staat ingeschreven bij de KvK onder nummer 96442018.",
                [p("Afspraken maken we via het " + '<a href="/nl/#contact">contactformulier</a>' + " of " + '<a href="https://www.linkedin.com/in/benverschuur" target="_blank" rel="noopener">LinkedIn</a>' + ". Ben Verschuur voert de kennismaking zelf. Bewijs van ons eigen werk staat bij de " + '<a href="/nl/cases/">cases</a>' + ".")]),
            faq(True, [
                ("Werken jullie alleen voor bedrijven in Rotterdam?", "Nee. We werken voor B2B-bedrijven in heel Nederland en daarbuiten. Voor bedrijven in de regio Rotterdam komen we graag langs voor de kennismaking en de presentatie van de metingen."),
                ("Wat kost een GEO-traject?", "De AI Visibility Sprint kost " + SPRINT_NL + " eenmalig, daarna kan maandelijkse begeleiding vanaf " + RETAINER_NL + " per maand. Alle bedragen zijn excl. btw."),
                ("Meten jullie ook in het Engels?", "Ja. Voor bedrijven met internationale kopers stellen we de koopvragen in het Nederlands en het Engels, zodat je ziet waar je in beide talen staat."),
                ("Hoe is dit anders dan een SEO-bureau?", "Een SEO-bureau stuurt op posities in Google. Wij sturen op vermeldingen en bronnen in AI-antwoorden, met SEO als basis. We meten met dezelfde methode als de B2B GEO Index."),
            ]),
            cta("Kennismaken in Rotterdam?", "Plan een uur, op locatie of online. Je ziet direct wat ChatGPT en Claude nu over je bedrijf zeggen.",
                [button("Plan een kennismaking", "/nl/#contact"), button("Doe de gratis AI-check", "/nl/ai-check/", "outline")]),
        ]
        return {"parent": "nl", "slug": "geo-bureau-rotterdam", "title": "GEO-bureau Rotterdam", "content": "\n".join(body),
                "meta": {"_yoast_wpseo_title": "GEO-bureau Rotterdam voor B2B | Booming Venture",
                         "_yoast_wpseo_metadesc": "GEO-bureau aan de Breedveldsingel in Rotterdam. Genoemd worden in ChatGPT, Claude en Gemini, met een nulmeting op jullie koopvragen, in NL en EN."}}
    body = [
        hero("Rotterdam", 'GEO agency in <span class="bv-gradient-text">Rotterdam</span> for B2B companies',
             "Booming Venture is based on Breedveldsingel in Rotterdam and helps B2B companies from the region get named in ChatGPT, Claude, Gemini and Perplexity. We visit, measure and work in Dutch and English.",
             facts=[("Rotterdam", "Breedveldsingel 1, 3055 PG"), ("400", "B2B companies in our monthly index"), ("NL and EN", "measured and written"), ("30", "days for a sprint")],
             btns=[button("Book an intro call", "/#contact", "gradient"), button("See the GEO programme", "/services/geo/", "outline")]),
        sec("Why local", "What does a GEO agency in Rotterdam do for you?",
            "A GEO agency in Rotterdam is a partner in the region that measures how often AI assistants name your company and then improves your site and mentions.",
            [stats([
                ("72", "is the average website score out of 100 of the 200 Dutch B2B companies in the B2B GEO Index, against 75 for the international group.", "Source: " + '<a href="' + S["report_en"] + '">B2B GEO Index, 25 November 2026</a>'),
                ("45%", "of Dutch B2B homepages carry Organization schema. The rest do not tell AI in a fixed format who the company is.", "Source: " + '<a href="' + S["report_en"] + '">B2B GEO Index, 25 November 2026</a>'),
                ("8 October 2025", "is when Google's AI Mode came to the Netherlands, with questions two to three times as long as normal searches.", "Source: " + a("Google", "google_nl")),
             ]),
             p("For B2B companies in Rotterdam that is an opening. In logistics, chemicals, energy and wholesale the buyers are often international and ask their questions in English. Companies that are easy to find in both languages make the shortlist more often.")]),
        sec("Sectors", "Which Rotterdam sectors do we already measure?",
            "The B2B GEO Index measures ten Dutch and ten international companies in 20 sectors every month, including the sectors that are large in the Rotterdam region.",
            [lst([
                '<a href="/b2b-geo-index/transport-logistics/">Transport and logistics</a>: which carriers and logistics providers AI names and which sources it uses.',
                '<a href="/b2b-geo-index/chemicals/">Chemicals</a>: how ready the websites of chemical companies are for AI crawlers.',
                '<a href="/b2b-geo-index/energy/">Energy</a>: the grid operators and energy companies in the index and their scores.',
                '<a href="/b2b-geo-index/wholesale/">Wholesale</a>: where wholesalers lose points on schema, language and freshness.',
                '<a href="/b2b-geo-index/civil-engineering/">Civil engineering</a>: builders and hydraulic engineers and how AI describes them.',
             ]),
             p("Is your company in one of these sectors? Put your score next to the leaders with the " + '<a href="/ai-visibility-check/">free AI check</a>' + ".")]),
        sec("How we work", "How do we work with companies in the region?",
            "We work along a fixed route: an intro on site or online, a baseline on your own buyer questions and then a 30-day sprint.",
            [lst([
                "<strong>Intro.</strong> An hour at your office in the Rotterdam region or online. Together we look at three buyer questions and what AI says about you today.",
                "<strong>Baseline.</strong> 20 buyer questions in four AI assistants, with a margin of error. Now we know where you stand against competitors.",
                "<strong>Sprint.</strong> Technical work, answer pages and a plan for mentions. Details and prices are on the " + '<a href="/services/geo/">GEO for B2B</a>' + " page.",
                "<strong>Monthly.</strong> If you want, you continue with a monthly measurement and work on mentions, cancellable monthly.",
             ], ordered=True)]),
        sec("Contact", "Where can you find us?",
            "Booming Venture is based at Breedveldsingel 1, 3055 PG Rotterdam, the Netherlands, and is registered with the Chamber of Commerce under number 96442018.",
            [p("Book a meeting through the " + '<a href="/#contact">contact form</a>' + " or " + '<a href="https://www.linkedin.com/in/benverschuur" target="_blank" rel="noopener">LinkedIn</a>' + ". Ben Verschuur runs the intro himself. Proof of our own work is on the " + '<a href="/cases/">cases</a>' + " page.")]),
        faq(False, [
            ("Do you only work for companies in Rotterdam?", "No. We work for B2B companies across the Netherlands and abroad. For companies in the Rotterdam region we are happy to visit for the intro and the presentation of the measurements."),
            ("What does a GEO programme cost?", "The AI Visibility Sprint costs " + SPRINT_EN + " one-off, followed by optional monthly support from " + RETAINER_EN + " a month. All amounts exclude VAT."),
            ("Do you also measure in English?", "Yes. For companies with international buyers we ask the buyer questions in Dutch and English, so you see where you stand in both languages."),
            ("How is this different from an SEO agency?", "An SEO agency steers on positions in Google. We steer on mentions and sources in AI answers, with SEO as the base. We measure with the same method as the B2B GEO Index."),
        ]),
        cta("Meet in Rotterdam?", "Book an hour, on site or online. You see right away what ChatGPT and Claude say about your company today.",
            [button("Book an intro call", "/#contact"), button("Run the free AI check", "/ai-visibility-check/", "outline")]),
    ]
    return {"parent": "", "slug": "geo-agency-rotterdam", "title": "GEO agency Rotterdam", "content": "\n".join(body),
            "meta": {"_yoast_wpseo_title": "GEO agency Rotterdam for B2B companies | Booming Venture",
                     "_yoast_wpseo_metadesc": "GEO agency on Breedveldsingel in Rotterdam. Get named in ChatGPT, Claude and Gemini, with a baseline on your buyer questions, in Dutch and English."}}
