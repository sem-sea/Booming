"""Cases, GEO pricing, AI check and statistics pages (1.30.0), Dutch and English."""
from blocks import (button, cards, cta, faq, group, h3, hero, lst, note, p, raw_block, sec, stats, table)
from pages_main import RETAINER_EN, RETAINER_NL, S, SPRINT_EN, SPRINT_NL, a


def rep(nl):
    return S["report_nl"] if nl else S["report_en"]


# --------------------------------------------------------------------------- cases
def cases(nl):
    if nl:
        body = [
            hero("Cases", 'Cases: wat ons eigen werk <span class="bv-gradient-text">heeft opgeleverd</span>',
                 "We laten zien wat we bouwen met cijfers die je zelf kunt controleren: onze eigen website en de B2B GEO Index.",
                 facts=[("85", "websitescore van boomingventure.com op 100"), ("72", "gemiddelde van Nederlandse B2B-bedrijven"), ("400", "bedrijven in de index"), ("147", "gepubliceerde artikelen")]),
            sec("Case 1", "Van een site die AI niet kon lezen naar een score van 85",
                "De overstap van boomingventure.com van een JavaScript-app naar een WordPress-site met HTML van de server bracht de websitescore op 85 van 100.",
                [h3("Uitgangspunt"),
                 p("De oude site was gebouwd als React-app. De tekst verscheen pas in de browser, nadat JavaScript was uitgevoerd. AI-crawlers zoals GPTBot, OAI-SearchBot, ClaudeBot en PerplexityBot voeren geen JavaScript uit (" + a("Vercel", "vercel") + "). Voor hen was de site een lege pagina."),
                 h3("Wat we deden"),
                 lst([
                     "Een eigen WordPress-thema dat alle tekst als HTML vanaf de server levert.",
                     "robots.txt die de zoekcrawlers van ChatGPT, Claude, Perplexity, Gemini en Bing toelaat.",
                     "Organization- en Person-schema met KvK, adres en sameAs, zodat AI het bedrijf en de oprichter herkent.",
                     "Een Nederlandse en Engelse versie met hreflang in beide richtingen.",
                     "Een sitemap met juiste datums, IndexNow bij elke publicatie en llms.txt.",
                     "147 artikelen over AI-zoeken, GEO en B2B-groei, met bronnen, tabellen en vragen.",
                 ]),
                 h3("Resultaat"),
                 table(["Meting", "boomingventure.com", "Vergelijking"], [
                     ["Websitescore (B2B GEO Index)", "<strong>85 van 100</strong>", "Gemiddeld 72 voor Nederlandse en 75 voor internationale B2B-bedrijven"],
                     ["AI-zoekcrawlers toegelaten", "5 van 5", "98% van de bedrijven laat OAI-SearchBot toe"],
                     ["Tekst zonder JavaScript", "Ja", "92% van de Nederlandse bedrijven"],
                     ["Organization-schema met sameAs", "Ja", "45% heeft Organization-schema, 36% sameAs"],
                     ["Nederlandse versie met hreflang", "Ja", "63% van de Nederlandse bedrijven"],
                     ["Wikidata en Wikipedia", "Nog niet", "50% van de Nederlandse bedrijven heeft een Wikipedia-artikel"],
                 ], "Gemeten met de check van de B2B GEO Index op 28 november 2026. Vergelijking: " + '<a href="' + rep(True) + '">scan van 400 B2B-websites, 25 november 2026</a>' + "."),
                 note("De resterende 15 punten zitten in Wikidata en Wikipedia. Die verdien je met vermeldingen in onafhankelijke bronnen, en daar werken we nu aan.")]),
            sec("Case 2", "De B2B GEO Index: een maandelijkse meting van 400 bedrijven",
                "De B2B GEO Index is een maandelijkse meting van 200 Nederlandse en 200 internationale B2B-bedrijven op hun zichtbaarheid in AI en de staat van hun website.",
                [cards([
                    ("Wat we bouwden", [lst([
                        "Een vaste groep van 400 bedrijven, tien per sector in 20 sectoren uit SBI 2025 en NACE.",
                        "Vijf koopvragen per categorie aan ChatGPT, Claude, Gemini en Perplexity, met zoeken aan.",
                        "Een foutmarge van 95% volgens Wilson en een tweede ronde waar de uitkomst onzeker is.",
                        "Zeven websitechecks per bedrijf, elke eerste werkdag van de maand.",
                    ])]),
                    ("Wat het oplevert", [lst([
                        "Een open ranglijst met CSV-downloads onder CC BY 4.0.",
                        "Een PDF-rapport per bedrijf en per sector.",
                        "Een pagina per sector met ranglijst, checks en bronnen.",
                        "De eerste websitemeting op 25 november 2026: 395 van 400 bedrijven gemeten.",
                    ])]),
                 ]),
                 p("Dezelfde meting gebruiken we voor klanten: dezelfde vragen, dezelfde assistenten en dezelfde marge. Lees de " + '<a href="/nl/b2b-geo-index/methode/">methode</a>' + " of bekijk de " + '<a href="/nl/b2b-geo-index/">index</a>' + ".")]),
            sec("Eerder werk", "Wat heeft Ben eerder bereikt?",
                "Ben Verschuur leidde 15 jaar performance- en growthmarketing en won met zijn teams onder meer een AVA Digital Award Platinum voor Best Performance Team Achievement in 2022.",
                [lst([
                    "Head of Performance &amp; Growth Marketing bij DPDK Digital Agency (2020 tot 2022), voor B2B- en B2C-klanten.",
                    "Digitale sales en growthmarketing bij Knab en Aegon Bank (2014 tot 2020): leadgeneratie, CRO en voorspellende modellen.",
                    "Director of Marketing bij Zeelander Yachts (2023 tot 2024): lancering van de Zeelander 8.",
                    "Erkenning: #1 Digital Excellence Sales / Online Marketing en Platinum MarCom Awards voor Product Launch en Digital Marketing.",
                 ]),
                 p("Meer over Ben op de pagina " + '<a href="/nl/over-ons/">over ons</a>' + ".")]),
            faq(True, [
                ("Waarom staan hier geen klantcases met naam?", "We publiceren alleen resultaten met toestemming van de klant en met een voor- en nameting op dezelfde vragen. Tot die er zijn, laten we ons eigen werk zien met cijfers die je zelf kunt controleren."),
                ("Kan ik de score van boomingventure.com zelf controleren?", "Ja. Vul boomingventure.com in bij de gratis AI-check. Je ziet dezelfde checks en de score van vandaag."),
                ("Wat mist boomingventure.com nog?", "Een Wikidata-item en een Wikipedia-artikel. Die tellen samen voor 15 punten en hangen af van onafhankelijke bronnen over het bedrijf."),
                ("Meten jullie bij klanten op dezelfde manier?", "Ja. De nulmeting en de maandelijkse meting gebruiken de methode van de B2B GEO Index, met een foutmarge van 95%."),
            ]),
            cta("Zelf zo'n meting voor je bedrijf?", "Begin met de gratis AI-check of plan een kennismaking voor een nulmeting op jullie eigen koopvragen.",
                [button("Doe de gratis AI-check", "/nl/ai-check/"), button("Bekijk het GEO-traject", "/nl/diensten/geo/", "outline")]),
        ]
        return {"parent": "nl", "slug": "cases", "title": "Cases", "content": "\n".join(body),
                "meta": {"_yoast_wpseo_title": "Cases: van onleesbare site naar score 85 en de B2B GEO Index | Booming Venture",
                         "_yoast_wpseo_metadesc": "Hoe boomingventure.com van een JavaScript-app naar een websitescore van 85 ging (gemiddelde 72) en hoe we een maandelijkse meting van 400 B2B-bedrijven bouwden."}}
    body = [
        hero("Cases", 'Cases: what our own work <span class="bv-gradient-text">delivered</span>',
             "We show what we build with numbers you can check yourself: our own website and the B2B GEO Index.",
             facts=[("85", "website score of boomingventure.com out of 100"), ("72", "average of Dutch B2B companies"), ("400", "companies in the index"), ("147", "published articles")]),
        sec("Case 1", "From a site AI could not read to a score of 85",
            "Moving boomingventure.com from a JavaScript app to a WordPress site with server HTML took its website score to 85 out of 100.",
            [h3("Starting point"),
             p("The old site was built as a React app. The text only appeared in the browser, after JavaScript had run. AI crawlers such as GPTBot, OAI-SearchBot, ClaudeBot and PerplexityBot do not run JavaScript (" + a("Vercel", "vercel") + "). To them the site was an empty page."),
             h3("What we did"),
             lst([
                 "A custom WordPress theme that serves all text as HTML from the server.",
                 "A robots.txt that allows the search crawlers of ChatGPT, Claude, Perplexity, Gemini and Bing.",
                 "Organization and Person schema with registration number, address and sameAs, so AI recognizes the company and its founder.",
                 "A Dutch and an English version with hreflang both ways.",
                 "A sitemap with correct dates, IndexNow on every publish and llms.txt.",
                 "147 articles on AI search, GEO and B2B growth, with sources, tables and questions.",
             ]),
             h3("Result"),
             table(["Measure", "boomingventure.com", "Comparison"], [
                 ["Website score (B2B GEO Index)", "<strong>85 out of 100</strong>", "Average 72 for Dutch and 75 for international B2B companies"],
                 ["AI search crawlers allowed", "5 of 5", "98% of companies allow OAI-SearchBot"],
                 ["Text without JavaScript", "Yes", "92% of Dutch companies"],
                 ["Organization schema with sameAs", "Yes", "45% have Organization schema, 36% sameAs"],
                 ["Dutch version with hreflang", "Yes", "63% of Dutch companies"],
                 ["Wikidata and Wikipedia", "Not yet", "50% of Dutch companies have a Wikipedia article"],
             ], "Measured with the B2B GEO Index check on 28 November 2026. Comparison: " + '<a href="' + rep(False) + '">scan of 400 B2B websites, 25 November 2026</a>' + "."),
             note("The remaining 15 points sit in Wikidata and Wikipedia. You earn those with mentions in independent sources, and that is what we are working on now.")]),
        sec("Case 2", "The B2B GEO Index: a monthly measurement of 400 companies",
            "The B2B GEO Index is a monthly measurement of 200 Dutch and 200 international B2B companies on their visibility in AI and the state of their website.",
            [cards([
                ("What we built", [lst([
                    "A fixed group of 400 companies, ten per sector in 20 sectors from SBI 2025 and NACE.",
                    "Five buyer questions per category to ChatGPT, Claude, Gemini and Perplexity, with search on.",
                    "A 95% Wilson margin of error and a second round where the result is uncertain.",
                    "Seven website checks per company, on the first working day of every month.",
                ])]),
                ("What it delivers", [lst([
                    "An open ranking with CSV downloads under CC BY 4.0.",
                    "A PDF report per company and per sector.",
                    "A page per sector with ranking, checks and sources.",
                    "The first website measurement on 25 November 2026: 395 of 400 companies measured.",
                ])]),
             ]),
             p("We use the same measurement for clients: the same questions, the same assistants and the same margin. Read the " + '<a href="/b2b-geo-index/methodology/">methodology</a>' + " or see the " + '<a href="/b2b-geo-index/">index</a>' + ".")]),
        sec("Earlier work", "What has Ben achieved before?",
            "Ben Verschuur led performance and growth marketing for 15 years and won, with his teams, an AVA Digital Award Platinum for Best Performance Team Achievement in 2022, among others.",
            [lst([
                "Head of Performance &amp; Growth Marketing at DPDK Digital Agency (2020 to 2022), for B2B and B2C clients.",
                "Digital sales and growth marketing at Knab and Aegon Bank (2014 to 2020): lead generation, CRO and predictive models.",
                "Director of Marketing at Zeelander Yachts (2023 to 2024): launch of the Zeelander 8.",
                "Recognition: #1 Digital Excellence Sales / Online Marketing and Platinum MarCom Awards for Product Launch and Digital Marketing.",
             ]),
             p("More about Ben on the " + '<a href="/about/">about</a>' + " page.")]),
        faq(False, [
            ("Why are there no named client cases here?", "We only publish results with the client's permission and with a before and after measurement on the same questions. Until then we show our own work, with numbers you can check yourself."),
            ("Can I check the score of boomingventure.com myself?", "Yes. Enter boomingventure.com in the free AI check. You see the same checks and today's score."),
            ("What is boomingventure.com still missing?", "A Wikidata item and a Wikipedia article. Together they count for 15 points and depend on independent sources about the company."),
            ("Do you measure clients the same way?", "Yes. The baseline and the monthly measurement use the B2B GEO Index methodology, with a 95% margin of error."),
        ]),
        cta("Want this measurement for your company?", "Start with the free AI check or book an intro for a baseline on your own buyer questions.",
            [button("Run the free AI check", "/ai-visibility-check/"), button("See the GEO programme", "/services/geo/", "outline")]),
    ]
    return {"parent": "", "slug": "cases", "title": "Cases", "content": "\n".join(body),
            "meta": {"_yoast_wpseo_title": "Cases: from unreadable site to score 85, and the B2B GEO Index | Booming Venture",
                     "_yoast_wpseo_metadesc": "How boomingventure.com went from a JavaScript app to a website score of 85 (average 72) and how we built a monthly measurement of 400 B2B companies."}}


# --------------------------------------------------------------------------- pricing
def pricing(nl):
    if nl:
        rows = [
            ["Harundo", "GEO, SEO en linkbuilding in één abonnement", "Vanaf €299 per maand", a("Harundo, 6 november 2026", "harundo")],
            ["Citeerbaar", "Scan, Fix, Sprint (eenmalig) en Groei", "€295, €695, €1.495; Groei €695 per maand (minimaal 3 maanden)", a("Searchlab, 1 oktober 2026", "searchlab_cost")],
            ["OpsCode", "Basis, Pro en Premium", "€99 tot €149, €997 of €2.295 per maand (Premium plus €3.995 opstart)", a("Searchlab, 1 oktober 2026", "searchlab_cost")],
            ["Estrelle", "Audit, sprint en monitoring", "€450 audit, sprint vanaf €1.450, €250 per maand", a("SEOptimate, oktober 2026", "seoptimate")],
            ["Internet Impact", "Audit en GEO per website", "€399 audit, GEO vanaf €750 per maand", a("Harundo, 6 november 2026", "harundo")],
            ["Searchlab", "GEO als kanaal, maandelijks opzegbaar", "Vanaf €1.500 per maand per kanaal", a("Searchlab", "searchlab_cost")],
            ["Gold Lemon", "Opstart en maandelijkse uitvoering voor B2B-mkb", "€2.499 eenmalig plus €699 per maand", a("Gold Lemon", "goldlemon")],
            ["<strong>Booming Venture</strong>", "AI Visibility Sprint en GEO-begeleiding voor B2B", "<strong>" + SPRINT_NL + " eenmalig, daarna vanaf " + RETAINER_NL + " per maand</strong>", '<a href="/nl/diensten/geo/">GEO voor B2B</a>'],
        ]
        body = [
            hero("Prijzen", 'Wat kost GEO? <span class="bv-gradient-text">Prijzen van Nederlandse bureaus</span>',
                 "Een overzicht van de prijzen die Nederlandse GEO-bureaus zelf publiceren, wat je voor dat geld krijgt en waar de verschillen vandaan komen.",
                 facts=[("€99", "laagste openbare maandprijs (alleen meten)"), ("€2.000 tot €3.000", "per maand bij ongeveer driekwart van de bureaus"), ("8", "bureaus met openbare prijzen vergeleken")]),
            sec("Kort antwoord", "Wat kost GEO in Nederland?",
                "GEO kost in Nederland tussen €99 per maand voor alleen meten en ongeveer €3.000 per maand voor volledige uitvoering, plus soms een eenmalige opstart.",
                [stats([
                    ("€2.000 tot €3.000", "per maand rekent ongeveer driekwart van de GEO-bureaus volgens een eigen onderzoek van De GEO Experts onder 15 bureaus.", "Bron: " + a("Wildbos, bijgewerkt 25 november 2026", "wildbos")),
                    ("1.572", "aanbieders noemen GEO of AEO op hun website. AI noemt er 459 ooit bij naam als je vraagt naar een bureau.", "Bron: " + a("GEO Agency Index, november 2026", "geo_agency_index")),
                    ("€650 tot €1.000", "per maand is volgens OpsCode een instapniveau, €1.000 tot €2.500 een gangbaar traject voor het mkb.", "Bron: " + a("Searchlab, 1 oktober 2026", "searchlab_cost")),
                 ])]),
            sec("Vergelijking", "Welke prijzen publiceren Nederlandse GEO-bureaus?",
                "Acht bureaus publiceren hun prijs, van een abonnement van €299 per maand tot een opstart van €2.499 met €699 per maand.",
                [table(["Bureau", "Wat", "Prijs (excl. btw)", "Bron en datum"], rows,
                       "Gecontroleerd tussen 1 oktober en 28 november 2026. Prijzen veranderen; controleer altijd de site van het bureau.", "bv-gip-table bv-price-table"),
                 note("Veel bureaus in de top van de GEO Agency Index publiceren geen prijs, onder wie ROXTAR en SmartRanking. Zij staan daarom niet in de tabel.")]),
            sec("Prijsverschillen", "Waar hangt de prijs van GEO van af?",
                "De prijs van GEO hangt vooral af van hoeveel er gemeten wordt, of het bureau ook uitvoert en of er werk buiten je eigen site in zit.",
                [lst([
                    "<strong>Meten of uitvoeren.</strong> Een dashboard dat vragen naar AI stuurt, is goedkoop. Pagina's herschrijven, techniek aanpassen en vermeldingen regelen kost uren.",
                    "<strong>Aantal vragen en assistenten.</strong> 5 vragen in één assistent zegt weinig. 20 koopvragen in vier assistenten met herhaling geeft een betrouwbaar beeld.",
                    "<strong>Herhaling en foutmarge.</strong> AI geeft bij elke vraag een ander antwoord. Een meting zonder herhaling en marge kan een stijging tonen die toeval is.",
                    "<strong>Eenmalig of doorlopend.</strong> Een sprint legt de basis. Vermeldingen en nieuwe antwoordpagina's vragen maandelijks werk.",
                    "<strong>Werk buiten je site.</strong> Lijstjes, reviewplatforms en digitale PR wegen zwaar voor AI. Vermeldingen op het web hangen sterk samen met AI-zichtbaarheid (" + a("Ahrefs, 75.000 merken", "ahrefs_brand") + ").",
                    "<strong>Contract en opstart.</strong> Let op minimale looptijden en opstartkosten. Ze verschillen van geen tot €3.995.",
                 ], ordered=True)]),
            sec("Zelf of uitbesteden", "Wanneer kun je GEO zelf doen?",
                "Je kunt GEO zelf doen als je een SEO-specialist en contentmakers in huis hebt; uitbesteden loont als je snel een meting met foutmarge en een plan wilt.",
                [p("Zelf beginnen kan met de " + '<a href="/nl/ai-check/">gratis AI-check</a>' + ", de rapporten van Search Console en Bing Webmaster Tools en een vaste set koopvragen die je elke maand in ChatGPT, Claude, Gemini en Perplexity stelt."),
                 p("Vraag een bureau in elke offerte naar vijf dingen: hoeveel vragen en assistenten, hoe vaak herhaald, welke foutmarge, wat er buiten je site gebeurt en hoe lang je vastzit.")]),
            faq(True, [
                ("Wat is een redelijke prijs voor GEO?", "Voor een B2B-bedrijf met een eigen marketingteam is €1.000 tot €3.000 per maand gangbaar voor uitvoering. Alleen meten kan vanaf ongeveer €100 per maand."),
                ("Is een eenmalige sprint genoeg?", "Een sprint legt de techniek en de eerste antwoordpagina's neer. Vermeldingen op andere sites en nieuwe content groeien alleen met doorlopend werk."),
                ("Waarom publiceren niet alle bureaus hun prijs?", "Grotere bureaus werken vaak op maat. Voor AI en kopers is een openbare prijs juist een sterk signaal: de GEO Agency Index weegt transparantie van het aanbod mee."),
                ("Wat kost GEO bij Booming Venture?", "De AI Visibility Sprint kost " + SPRINT_NL + " eenmalig, daarna kan maandelijkse begeleiding vanaf " + RETAINER_NL + " per maand. Alles excl. btw, maandelijks opzegbaar."),
            ]),
            cta("Een prijs op maat van je koopvragen?", "Plan een kennismaking. Je krijgt een voorstel op basis van het aantal koopvragen, markten en talen.",
                [button("Plan een kennismaking", "/nl/#contact"), button("Bekijk het GEO-traject", "/nl/diensten/geo/", "outline")]),
        ]
        return {"parent": "nl", "slug": "wat-kost-geo", "title": "Wat kost GEO?", "content": "\n".join(body),
                "meta": {"_yoast_wpseo_title": "Wat kost GEO? Prijzen van 8 Nederlandse bureaus vergeleken (2026)",
                         "_yoast_wpseo_metadesc": "GEO kost van €99 per maand voor meten tot ongeveer €3.000 voor uitvoering. Openbare prijzen van 8 Nederlandse bureaus, met bron en datum."}}
    rows = [
        ["Harundo", "GEO, SEO and link building in one subscription", "From €299 a month", a("Harundo, 6 Nov 2026", "harundo")],
        ["Citeerbaar", "Scan, Fix, Sprint (one-off) and Grow", "€295, €695, €1,495; Grow €695 a month (3 months minimum)", a("Searchlab, 1 Oct 2026", "searchlab_cost")],
        ["OpsCode", "Basic, Pro and Premium", "€99 to €149, €997 or €2,295 a month (Premium plus €3,995 setup)", a("Searchlab, 1 Oct 2026", "searchlab_cost")],
        ["Estrelle", "Audit, sprint and monitoring", "€450 audit, sprint from €1,450, €250 a month", a("SEOptimate, Oct 2026", "seoptimate")],
        ["Internet Impact", "Audit and GEO per website", "€399 audit, GEO from €750 a month", a("Harundo, 6 Nov 2026", "harundo")],
        ["Searchlab", "GEO as a channel, cancel monthly", "From €1,500 a month per channel", a("Searchlab", "searchlab_cost")],
        ["Gold Lemon", "Setup and monthly work for B2B SMEs", "€2,499 one-off plus €699 a month", a("Gold Lemon", "goldlemon")],
        ["<strong>Booming Venture</strong>", "AI Visibility Sprint and GEO support for B2B", "<strong>" + SPRINT_EN + " one-off, then from " + RETAINER_EN + " a month</strong>", '<a href="/services/geo/">GEO for B2B</a>'],
    ]
    body = [
        hero("Pricing", 'What does GEO cost? <span class="bv-gradient-text">Prices of Dutch agencies</span>',
             "An overview of the prices Dutch GEO agencies publish themselves, what you get for the money and where the differences come from.",
             facts=[("€99", "lowest published monthly price (measurement only)"), ("€2,000 to €3,000", "a month at about three in four agencies"), ("8", "agencies with published prices compared")]),
        sec("Short answer", "What does GEO cost in the Netherlands?",
            "GEO in the Netherlands costs between €99 a month for measurement only and about €3,000 a month for full execution, sometimes plus a one-off setup.",
            [stats([
                ("€2,000 to €3,000", "a month is what about three in four GEO agencies charge, according to research by De GEO Experts among 15 agencies.", "Source: " + a("Wildbos, updated 25 November 2026", "wildbos")),
                ("1,572", "providers mention GEO or AEO on their website. AI names 459 of them when asked for an agency.", "Source: " + a("GEO Agency Index, November 2026", "geo_agency_index")),
                ("€650 to €1,000", "a month is an entry level according to OpsCode, €1,000 to €2,500 a typical programme for SMEs.", "Source: " + a("Searchlab, 1 October 2026", "searchlab_cost")),
             ])]),
        sec("Comparison", "Which prices do Dutch GEO agencies publish?",
            "Eight agencies publish their price, from a €299 monthly subscription to a €2,499 setup with €699 a month.",
            [table(["Agency", "What", "Price (excl. VAT)", "Source and date"], rows,
                   "Checked between 1 October and 28 November 2026. Prices change; always check the agency's own site.", "bv-gip-table bv-price-table"),
             note("Many agencies at the top of the GEO Agency Index publish no price, including ROXTAR and SmartRanking, so they are not in the table.")]),
        sec("Price drivers", "What does the price of GEO depend on?",
            "The price of GEO mainly depends on how much is measured, whether the agency also executes and whether work outside your own site is included.",
            [lst([
                "<strong>Measuring or executing.</strong> A dashboard that sends questions to AI is cheap. Rewriting pages, fixing technical issues and earning mentions takes hours.",
                "<strong>Number of questions and assistants.</strong> 5 questions in one assistant says little. 20 buyer questions in four assistants, repeated, gives a reliable picture.",
                "<strong>Repetition and margin of error.</strong> AI gives a different answer to every question. A measurement without repetition and margin can show a rise that is chance.",
                "<strong>One-off or ongoing.</strong> A sprint lays the foundation. Mentions and new answer pages need monthly work.",
                "<strong>Work outside your site.</strong> Lists, review platforms and digital PR weigh heavily for AI. Web mentions correlate strongly with AI visibility (" + a("Ahrefs, 75,000 brands", "ahrefs_brand") + ").",
                "<strong>Contract and setup.</strong> Watch minimum terms and setup fees. They range from none to €3,995.",
             ], ordered=True)]),
        sec("DIY or outsource", "When can you do GEO yourself?",
            "You can do GEO yourself if you have an SEO specialist and content makers in house; outsourcing pays off if you want a measurement with a margin of error and a plan fast.",
            [p("You can start with the " + '<a href="/ai-visibility-check/">free AI check</a>' + ", the reports in Search Console and Bing Webmaster Tools, and a fixed set of buyer questions you ask ChatGPT, Claude, Gemini and Perplexity every month."),
             p("Ask every agency five things in its quote: how many questions and assistants, how often repeated, which margin of error, what happens outside your site and how long you are tied in.")]),
        faq(False, [
            ("What is a fair price for GEO?", "For a B2B company with its own marketing team, €1,000 to €3,000 a month is common for execution. Measurement only starts at about €100 a month."),
            ("Is a one-off sprint enough?", "A sprint lays down the technical basis and the first answer pages. Mentions on other sites and new content only grow with ongoing work."),
            ("Why don't all agencies publish their price?", "Larger agencies often work to measure. For AI and buyers a published price is a strong signal: the GEO Agency Index counts offer transparency in its score."),
            ("What does GEO cost at Booming Venture?", "The AI Visibility Sprint costs " + SPRINT_EN + " one-off, followed by optional monthly support from " + RETAINER_EN + " a month. All excluding VAT, cancellable monthly."),
        ]),
        cta("A price based on your buyer questions?", "Book an intro call. You get a proposal based on the number of buyer questions, markets and languages.",
            [button("Book an intro call", "/#contact"), button("See the GEO programme", "/services/geo/", "outline")]),
    ]
    return {"parent": "", "slug": "geo-pricing-netherlands", "title": "GEO pricing in the Netherlands", "content": "\n".join(body),
            "meta": {"_yoast_wpseo_title": "What does GEO cost? Prices of 8 Dutch agencies compared (2026)",
                     "_yoast_wpseo_metadesc": "GEO costs from €99 a month for measurement to about €3,000 for execution. Published prices of 8 Dutch agencies, with source and date."}}


# --------------------------------------------------------------------------- AI check
def ai_check(nl):
    t = (lambda x, y: x) if nl else (lambda x, y: y)
    body = [
        hero(t("Gratis AI-check", "Free AI check"),
             t('Gratis AI-check: hoe goed kan <span class="bv-gradient-text">AI je website lezen?</span>', 'Free AI check: how well can <span class="bv-gradient-text">AI read your website?</span>'),
             t("Vul je domein in en zie in 30 seconden je websitescore op 100, gemeten op dezelfde manier als de 400 bedrijven in de B2B GEO Index. Kies je sector en zie je verschil met de leider.",
               "Enter your domain and see your website score out of 100 in 30 seconds, measured the same way as the 400 companies in the B2B GEO Index. Pick your sector and see your gap with the leader."),
             facts=[("30", t("seconden", "seconds")), ("7", t("checks op je website", "checks on your website")), ("400", t("bedrijven als vergelijking", "companies to compare with")), ("€0", t("geen account nodig", "no account needed"))]),
        group("bv-gip-sec", raw_block("booming-venture/geo-index", {"mode": "check", "align": "wide"}), wide=True),
        sec(t("Wat we meten", "What we check"), t("Wat meet de gratis AI-check?", "What does the free AI check measure?"),
            t("De gratis AI-check is een meting van zeven onderdelen van je website die bepalen of AI-zoekmachines je pagina's kunnen lezen, begrijpen en citeren.",
              "The free AI check is a measurement of seven parts of your website that decide whether AI search engines can read, understand and cite your pages."),
            [lst([
                t("<strong>AI-crawlers in robots.txt.</strong> Mogen de zoekcrawlers van ChatGPT, Claude, Perplexity, Gemini en Bing je site lezen?", "<strong>AI crawlers in robots.txt.</strong> May the search crawlers of ChatGPT, Claude, Perplexity, Gemini and Bing read your site?"),
                t("<strong>Tekst zonder JavaScript.</strong> Staat je tekst in de HTML van de server? AI-crawlers voeren geen JavaScript uit (", "<strong>Text without JavaScript.</strong> Is your text in the server HTML? AI crawlers do not run JavaScript (") + a("Vercel", "vercel") + ").",
                t("<strong>Gestructureerde data.</strong> Organization-schema met sameAs-links naar je profielen.", "<strong>Structured data.</strong> Organization schema with sameAs links to your profiles."),
                t("<strong>Wikidata en Wikipedia.</strong> Herkent AI je bedrijf als entiteit?", "<strong>Wikidata and Wikipedia.</strong> Does AI recognize your company as an entity?"),
                t("<strong>Actualiteit.</strong> Is je sitemap in de laatste 90 dagen bijgewerkt?", "<strong>Freshness.</strong> Was your sitemap updated in the last 90 days?"),
                t("<strong>Taal.</strong> Een Nederlandse versie, of meerdere talen met hreflang.", "<strong>Language.</strong> A Dutch version, or several languages with hreflang."),
                t("<strong>llms.txt.</strong> Optioneel en licht gewogen.", "<strong>llms.txt.</strong> Optional and lightly weighted."),
             ], ordered=True),
             note(t("De punten per check staan in de ", "The points per check are in the ") + '<a href="' + t("/nl/b2b-geo-index/methode/", "/b2b-geo-index/methodology/") + '">' + t("methode", "methodology") + "</a>.")]),
        sec(t("Vergelijken", "Compare"), t("Hoe scoren andere B2B-bedrijven?", "How do other B2B companies score?"),
            t("Nederlandse B2B-bedrijven halen gemiddeld 72 op de websitescore en internationale bedrijven 75, gemeten op 25 november 2026.",
              "Dutch B2B companies average 72 on the website score and international companies 75, measured on 25 November 2026."),
            [stats([
                ("98%", t("laat de zoekcrawler van ChatGPT toe (OAI-SearchBot).", "allow ChatGPT's search crawler (OAI-SearchBot)."), t("Bron: ", "Source: ") + '<a href="' + rep(nl) + '">B2B GEO Index</a>'),
                ("45%", t("van de Nederlandse homepages heeft Organization-schema.", "of Dutch homepages carry Organization schema."), t("Bron: ", "Source: ") + '<a href="' + rep(nl) + '">B2B GEO Index</a>'),
                ("19%", t("van de Nederlandse bedrijven heeft een llms.txt.", "of Dutch companies have an llms.txt."), t("Bron: ", "Source: ") + '<a href="' + rep(nl) + '">B2B GEO Index</a>'),
             ]),
             p(t("Bekijk de scores per sector op de ", "See the scores per sector on the ") + '<a href="' + t("/nl/b2b-geo-index/#sectoren", "/b2b-geo-index/#sectors") + '">' + t("sectorpagina's van de index", "sector pages of the index") + "</a>.")]),
        sec(t("Na de check", "After the check"), t("Wat doe je met je score?", "What do you do with your score?"),
            t("Met je score zie je per check wat ontbreekt; de snelste winst zit meestal in schema, sameAs en een sitemap met juiste datums.",
              "Your score shows per check what is missing; the fastest wins are usually schema, sameAs and a sitemap with correct dates."),
            [p(t("Laat je e-mailadres achter voor je verbeterplan per punt. Wil je weten hoe vaak AI-assistenten je echt noemen? Dat meten we in de nulmeting van het ",
                 "Leave your email address for your improvement plan per point. Want to know how often AI assistants really name you? We measure that in the baseline of the ")
               + '<a href="' + t("/nl/diensten/geo/", "/services/geo/") + '">' + t("GEO-traject", "GEO programme") + "</a>.")]),
        faq(nl, [
            (t("Is de AI-check echt gratis?", "Is the AI check really free?"), t("Ja. Je hebt geen account nodig. Voor het verbeterplan per punt vragen we je e-mailadres.", "Yes. You need no account. For the improvement plan per point we ask for your email address.")),
            (t("Waarom zie ik geen AI-score?", "Why do I not see an AI score?"), t("De AI-score meet hoe vaak assistenten je noemen. Daarvoor stellen we tientallen koopvragen, en dat doen we maandelijks voor de indexbedrijven en in de nulmeting voor klanten.", "The AI score measures how often assistants name you. That takes dozens of buyer questions, which we run monthly for the index companies and in the baseline for clients.")),
            (t("Wat gebeurt er met mijn domein?", "What happens to my domain?"), t("We lezen alleen openbare bestanden: je homepage, robots.txt, sitemap en llms.txt. Je domein komt niet in de openbare ranglijst.", "We only read public files: your homepage, robots.txt, sitemap and llms.txt. Your domain does not go into the public ranking.")),
            (t("Mijn score is onvolledig. Hoe kan dat?", "My score is incomplete. Why?"), t("Sommige sites weigeren verzoeken van onbekende bezoekers. Dan kunnen we een deel van de checks niet doen. Zet de crawlers van AI-zoekmachines in je firewall op de toegestane lijst.", "Some sites refuse requests from unknown visitors. Then we cannot run part of the checks. Put the crawlers of AI search engines on the allow list of your firewall.")),
        ]),
    ]
    if nl:
        return {"parent": "nl", "slug": "ai-check", "title": "Gratis AI-check", "content": "\n".join(body),
                "meta": {"_yoast_wpseo_title": "Gratis AI-check: hoe goed kan AI je website lezen? | B2B GEO Index",
                         "_yoast_wpseo_metadesc": "Vul je domein in en zie in 30 seconden je websitescore op 100 en je verschil met de leider van je sector. Zeven checks, gemeten zoals de B2B GEO Index."}}
    return {"parent": "", "slug": "ai-visibility-check", "title": "Free AI visibility check", "content": "\n".join(body),
            "meta": {"_yoast_wpseo_title": "Free AI visibility check: how well can AI read your website? | B2B GEO Index",
                     "_yoast_wpseo_metadesc": "Enter your domain and see your website score out of 100 and your gap with your sector leader in 30 seconds. Seven checks, measured like the B2B GEO Index."}}
