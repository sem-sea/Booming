"""AI search statistics for B2B (1.30.0), Dutch and English. Every number has a source, a link and a date."""
from blocks import button, cta, faq, hero, note, p, sec, table
from pages_main import S, a


def stats_page(nl):
    t = (lambda x, y: x) if nl else (lambda x, y: y)
    rp = S["report_nl"] if nl else S["report_en"]
    idx = lambda d: '<a href="' + rp + '">B2B GEO Index, ' + d + "</a>"
    H = [t("Cijfer", "Number"), t("Wat het betekent", "What it means"), t("Bron en datum", "Source and date")]

    search = [
        ["58%", t("minder klikken op de nummer 1 in Google als er een AI-overzicht boven staat", "fewer clicks on the number 1 Google result when an AI Overview sits above it"), a(t("Ahrefs, februari 2026", "Ahrefs, February 2026"), "ahrefs_ctr")],
        ["38%", t("van de citaties in AI-overzichten komt van pagina's uit de top 10; in juli 2025 was dat 76%", "of citations in AI Overviews come from top-10 pages; in July 2025 it was 76%"), a(t("Ahrefs, maart 2026", "Ahrefs, March 2026"), "ahrefs_top10")],
        ["8 okt 2025" if nl else "8 Oct 2025", t("start van de AI-modus van Google in Nederland; vragen zijn er twee tot drie keer zo lang", "launch of Google's AI Mode in the Netherlands; questions there are two to three times as long"), a("Google", "google_nl")],
        ["31 aug 2026" if nl else "31 Aug 2026", t("Search Console toont voor alle sites vertoningen in AI-overzichten en AI-modus", "Search Console shows impressions in AI Overviews and AI Mode for every site"), a("Quattr", "gsc_ai")],
        ["16 jun 2026" if nl else "16 Jun 2026", t("Bing Webmaster Tools toont citaties, intenties en aandeel in citaties per vraag", "Bing Webmaster Tools shows citations, intents and citation share per question"), a("Bing", "bing_ai")],
    ]
    cited = [
        ["43,8%" if nl else "43.8%", t("van de bronnen die ChatGPT citeert bij koopvragen zijn \"beste X\"-lijsten", "of the sources ChatGPT cites for buying questions are \"best X\" lists"), a(t("Ahrefs, via RanketAI, juli 2026", "Ahrefs, via RanketAI, July 2026"), "listicles")],
        ["11 tot 13%" if nl else "11 to 13%", t("van de B2B-citaties gaat naar de tien grootste domeinen", "of B2B citations go to the ten biggest domains"), a(t("Analyze, augustus 2026", "Analyze, August 2026"), "analyze")],
        ["65%", t("van de AI-antwoorden op vragen naar bewijs noemt een reviewplatform", "of AI answers to proof-seeking questions name a review platform"), a(t("Clutch, september 2026", "Clutch, September 2026"), "clutch")],
        ["11", t("links per antwoord geeft Claude gemiddeld bij B2B-koopvragen", "links per answer is Claude's average for B2B buying questions"), a(t("Clutch, september 2026", "Clutch, September 2026"), "clutch")],
        ["25,7%" if nl else "25.7%", t("recenter is content die AI-assistenten citeren dan wat Google organisch toont", "fresher is the content AI assistants cite than what Google shows organically"), a(t("Ahrefs, 17 miljoen citaties", "Ahrefs, 17 million citations"), "ahrefs_fresh")],
        ["+41% / +31%", t("meer zichtbaarheid in een AI-antwoord met citaten en met statistieken in de tekst", "more visibility in an AI answer with quotes and with statistics in the text"), a(t("Aggarwal e.a., KDD 2024", "Aggarwal et al., KDD 2024"), "geo_paper")],
    ]
    brand = [
        ["0,737" if nl else "0.737", t("correlatie tussen vermeldingen op YouTube en AI-zichtbaarheid, de sterkste factor", "correlation between YouTube mentions and AI visibility, the strongest factor"), a(t("Ahrefs, 75.000 merken, december 2025", "Ahrefs, 75,000 brands, December 2025"), "ahrefs_brand")],
        ["0,66 tot 0,71" if nl else "0.66 to 0.71", t("correlatie tussen vermeldingen op het web en AI-zichtbaarheid", "correlation between web mentions and AI visibility"), a("Ahrefs", "ahrefs_brand")],
        ["#2", t("is LinkedIn in B2B-citaties, na YouTube; 75% van die citaties komt van persoonlijke profielen", "is LinkedIn's rank in B2B citations, after YouTube; 75% of those citations come from personal profiles"), a(t("Meltwater en LinkedIn, mei 2026", "Meltwater and LinkedIn, May 2026"), "meltwater")],
        ["48%", t("van de geciteerde LinkedIn-content is jonger dan drie maanden", "of cited LinkedIn content is less than three months old"), a(t("Meltwater en LinkedIn, mei 2026", "Meltwater and LinkedIn, May 2026"), "meltwater")],
    ]
    tech = [
        ["0", t("van de grote AI-crawlers (GPTBot, ClaudeBot, PerplexityBot) voert JavaScript uit", "of the major AI crawlers (GPTBot, ClaudeBot, PerplexityBot) run JavaScript"), a("Vercel", "vercel")],
        ["35%", t("van de bezoeken van ChatGPT- en Claude-crawlers komt op een 404-pagina, tegen 8% bij Googlebot", "of visits by ChatGPT and Claude crawlers hit a 404 page, against 8% for Googlebot"), a("Vercel", "vercel")],
        ["4 MB", t("is de grens: grotere pagina's leest ChatGPT niet", "is the limit: ChatGPT does not read larger pages"), a("Search Engine Land", "sel_chatgpt")],
        ["98%", t("van de B2B-bedrijven laat de zoekcrawler van ChatGPT toe", "of B2B companies allow ChatGPT's search crawler"), idx(t("25 november 2026", "25 November 2026"))],
        ["45% / 50%", t("van de Nederlandse en internationale homepages heeft Organization-schema", "of Dutch and international homepages carry Organization schema"), idx(t("25 november 2026", "25 November 2026"))],
        ["50% / 87%", t("van de Nederlandse en internationale B2B-bedrijven heeft een Wikipedia-artikel", "of Dutch and international B2B companies have a Wikipedia article"), idx(t("25 november 2026", "25 November 2026"))],
        ["72 / 75", t("gemiddelde websitescore op 100 van Nederlandse en internationale B2B-bedrijven", "average website score out of 100 of Dutch and international B2B companies"), idx(t("25 november 2026", "25 November 2026"))],
    ]
    buyers = [
        ["13 + 9", t("interne en externe betrokkenen bij een gemiddelde B2B-aankoop", "internal and external people involved in a typical B2B purchase"), a(t("Forrester, januari 2026", "Forrester, January 2026"), "forrester")],
        ["60%+", t("van de zakelijke kopers test een oplossing voor de aankoop", "of business buyers trial a solution before buying"), a(t("Forrester, januari 2026", "Forrester, January 2026"), "forrester")],
        ["1.572 / 459" if nl else "1,572 / 459", t("Nederlandse aanbieders van GEO in het register, en hoeveel AI er ooit bij naam noemt", "Dutch GEO providers in the register, and how many AI ever names"), a(t("GEO Agency Index, november 2026", "GEO Agency Index, November 2026"), "geo_agency_index")],
    ]
    cap = t("Gecontroleerd op 28 november 2026.", "Checked on 28 November 2026.")
    body = [
        hero(t("Statistieken", "Statistics"),
             t('AI-zoeken in B2B: <span class="bv-gradient-text">cijfers voor 2026</span>', 'AI search in B2B: <span class="bv-gradient-text">numbers for 2026</span>'),
             t("De belangrijkste cijfers over AI-zoekmachines voor B2B-bedrijven, elk met bron en datum, aangevuld met onze eigen metingen uit de B2B GEO Index. Elk kwartaal bijgewerkt.",
               "The key numbers on AI search for B2B companies, each with source and date, plus our own measurements from the B2B GEO Index. Updated every quarter."),
             facts=[("25", t("cijfers met bron", "sourced numbers")), ("400", t("bedrijven in onze eigen meting", "companies in our own measurement")), (t("Elk kwartaal", "Quarterly"), t("bijgewerkt", "updated"))]),
        sec(t("Zoeken", "Search"), t("Hoeveel klikken kosten AI-antwoorden?", "How many clicks do AI answers cost?"),
            t("AI-antwoorden kosten veel klikken: een AI-overzicht boven de resultaten verlaagt de klikratio van de nummer 1 met 58%.",
              "AI answers cost many clicks: an AI Overview above the results lowers the click-through rate of the number 1 result by 58%."),
            [table(H, search, cap)]),
        sec(t("Citaties", "Citations"), t("Welke bronnen citeert AI bij koopvragen?", "Which sources does AI cite for buying questions?"),
            t("Bij koopvragen citeert AI vooral ranglijsten, reviewplatforms en de sites van aanbieders zelf, verspreid over duizenden domeinen.",
              "For buying questions AI mostly cites rankings, review platforms and the providers' own sites, spread over thousands of domains."),
            [table(H, cited, cap)]),
        sec(t("Merk", "Brand"), t("Wat bepaalt of AI je merk noemt?", "What decides whether AI names your brand?"),
            t("Vermeldingen buiten je eigen site bepalen het meest of AI je merk noemt, met YouTube, het web en LinkedIn als sterkste signalen.",
              "Mentions outside your own site decide most whether AI names your brand, with YouTube, the web and LinkedIn as the strongest signals."),
            [table(H, brand, cap), note(t("Correlaties zijn geen oorzaken. Sterke merken scoren op alle signalen tegelijk.", "Correlations are not causes. Strong brands score on all signals at once."))]),
        sec(t("Techniek", "Technical"), t("Hoe klaar zijn B2B-websites voor AI-crawlers?", "How ready are B2B websites for AI crawlers?"),
            t("De meeste B2B-websites laten AI-crawlers toe, maar de helft mist de entiteitsdata waarmee AI een bedrijf herkent.",
              "Most B2B websites allow AI crawlers, but half lack the entity data AI uses to recognize a company."),
            [table(H, tech, cap)]),
        sec(t("Kopers", "Buyers"), t("Hoe kopen B2B-kopers met AI?", "How do B2B buyers buy with AI?"),
            t("B2B-kopers beginnen bij generatieve AI, maar beslissen in grote groepen en willen bewijs zien voor ze kopen.",
              "B2B buyers start with generative AI, but decide in large groups and want proof before they buy."),
            [table(H, buyers, cap)]),
        sec(t("Gebruik", "Use"), t("Mag ik deze cijfers gebruiken?", "May I use these numbers?"),
            t("Ja, je mag de cijfers van de B2B GEO Index gebruiken onder CC BY 4.0, met een vermelding van de bron en een link naar deze pagina.",
              "Yes, you may use the B2B GEO Index numbers under CC BY 4.0, crediting the source with a link to this page."),
            [p(t("Voor cijfers van andere bronnen geldt de licentie van die bron; link dan naar het origineel. Journalisten kunnen nieuwe indexcijfers een dag eerder onder embargo krijgen via het ",
                 "For numbers from other sources, that source's license applies; link to the original. Journalists can get new index numbers one day early under embargo through the ")
               + '<a href="' + t("/nl/#contact", "/#contact") + '">' + t("contactformulier", "contact form") + "</a>.")]),
        faq(nl, [
            (t("Hoe vaak wordt deze pagina bijgewerkt?", "How often is this page updated?"), t("Elk kwartaal, en de cijfers van de B2B GEO Index na elke maandelijkse meting op de eerste werkdag van de maand.", "Every quarter, and the B2B GEO Index numbers after each monthly measurement on the first working day of the month.")),
            (t("Waar komen de cijfers van de B2B GEO Index vandaan?", "Where do the B2B GEO Index numbers come from?"), t("Uit onze eigen meting van 200 Nederlandse en 200 internationale B2B-bedrijven. De methode staat op de methodepagina.", "From our own measurement of 200 Dutch and 200 international B2B companies. The method is on the methodology page.")),
            (t("Gelden deze cijfers ook voor Nederland?", "Do these numbers apply to the Netherlands?"), t("De meeste internationale studies meten Engelstalige vragen. Voor Nederland gebruiken we waar mogelijk onze eigen meting en de GEO Agency Index.", "Most international studies measure English-language questions. For the Netherlands we use our own measurement and the GEO Agency Index where possible.")),
            (t("Mis ik een cijfer?", "Am I missing a number?"), t("Stuur ons de bron via het contactformulier. We nemen alleen cijfers op met een openbare bron en datum.", "Send us the source through the contact form. We only include numbers with a public source and date.")),
        ]),
        cta(t("Wat betekenen deze cijfers voor jouw bedrijf?", "What do these numbers mean for your company?"),
            t("Doe de gratis AI-check of bekijk hoe jouw sector scoort in de B2B GEO Index.", "Run the free AI check or see how your sector scores in the B2B GEO Index."),
            [button(t("Doe de gratis AI-check", "Run the free AI check"), t("/nl/ai-check/", "/ai-visibility-check/")),
             button(t("Bekijk de index", "See the index"), t("/nl/b2b-geo-index/", "/b2b-geo-index/"), "outline")]),
    ]
    if nl:
        return {"parent": "nl", "slug": "ai-zoeken-b2b-statistieken", "title": "AI-zoeken in B2B: cijfers 2026", "content": "\n".join(body),
                "meta": {"_yoast_wpseo_title": "AI-zoeken in B2B: 25 cijfers voor 2026, met bron | Booming Venture",
                         "_yoast_wpseo_metadesc": "Klikken, citaties, merk, techniek en kopers: de belangrijkste cijfers over AI-zoeken voor B2B, elk met bron en datum, plus eigen metingen van 400 bedrijven."}}
    return {"parent": "", "slug": "ai-search-b2b-statistics", "title": "AI search in B2B: numbers for 2026", "content": "\n".join(body),
            "meta": {"_yoast_wpseo_title": "AI search in B2B: 25 statistics for 2026, sourced | Booming Venture",
                     "_yoast_wpseo_metadesc": "Clicks, citations, brand, technical and buyers: the key numbers on AI search for B2B, each with source and date, plus our own measurements of 400 companies."}}
