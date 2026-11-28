"""Dutch versions of the English GEO posts scheduled 3 to 20 December 2026 (1.30.0).

Same rules and helpers as posts_nl.py. Each post copies its English
original's facts and sources; Dutch numbers and a Dutch angle where our own
B2B GEO Index or a Dutch source supports one. Every English post of the
series gets a Dutch twin, linked as translation (hreflang both ways), so the
blog has no English-only or Dutch-only post in the GEO series.
Import is create only: an existing post is never changed.

Run: python3 tools/blog/posts_nl_2.py
-> checks every post and writes them to import/nl/content.json (posts_nl_v2),
   for POST /wp-json/bv/v1/import-nl?update=posts-nl-v2
"""
import importlib.util
import json
import pathlib
import re

HERE = pathlib.Path(__file__).resolve().parent
_spec = importlib.util.spec_from_file_location("posts_nl", HERE / "posts_nl.py")
nl = importlib.util.module_from_spec(_spec)
_spec.loader.exec_module(nl)

a, p, h2, h3, cap, ol, ul, table, cta, tldr, faq = nl.a, nl.p, nl.h2, nl.h3, nl.cap, nl.ol, nl.ul, nl.table, nl.cta, nl.tldr, nl.faq
QS, DIENSTEN = nl.QS, nl.DIENSTEN
GEO = a("GEO-traject voor B2B", "/nl/diensten/geo/")
PRICES = a("Wat kost GEO?", "/nl/wat-kost-geo/")
CHECK = a("gratis AI-check", "/nl/ai-check/")
INDEX = a("B2B GEO Index", "/nl/b2b-geo-index/")
REPORT = "/nl/blog/ai-vindbaarheid-400-b2b-websites-november-2026/"


def blog(slug):
    return "/nl/blog/" + slug + "/"


U = {
    "meltwater": "https://www.meltwater.com/en/blog/linkedin-ai-visibility-study",
    "gartner_buyers": "https://www.gartner.com/en/newsroom/press-releases/2026-05-20-gartner-survey-finds-sixty-nine-percent-of-b-two-b-buyers-turn-to-sales-reps-to-validate-ai-generated-insights",
    "geo_paper": "https://arxiv.org/html/2311.09735v3",
    "ponemon": "https://ponemonsullivanreport.com/2026/06/the-state-of-cybersecurity-marketing-influence-2026-from-awareness-to-selection-how-buyers-evaluate-vendors/",
    "muckrack": "https://media.muckrack.com/documents/What_Is_AI_Reading__May_2026.pdf",
    "leadfeeder": "https://www.leadfeeder.com/resources/studies/ai-referrals-in-b2b/",
    "anthropic_bots": "https://support.claude.com/en/articles/8896518",
    "temso": "https://www.temso.ai/data/-Lost-in-Translation-How-AI-Models-Handle-Local-Language-Sources",
    "clutch": "https://clutch.co/resources/what-ai-tells-b2b-buyers",
    "ebu": "https://www.ebu.ch/news/2025/10/ai-s-systemic-distortion-of-news-is-consistent-across-languages-and-territories-international-study-by-public-service-broadcaste",
    "google_crawlers": "https://developers.google.com/crawling/docs/crawlers-fetchers/google-common-crawlers?hl=en",
    "ahrefs_fresh": "https://ahrefs.com/blog/do-ai-assistants-prefer-to-cite-fresh-content/",
    "ahrefs_top": "https://ahrefs.com/blog/chatgpts-most-cited-pages",
    "vercel": "https://vercel.com/blog/the-rise-of-the-ai-crawler",
    "forrester": "https://www.forrester.com/blogs/b2b_buyers_make_zero_click_buying_number_one/",
    "openai_bots": "https://developers.openai.com/api/docs/bots",
    "perplexity_bots": "https://docs.perplexity.ai/guides/bots",
    "cloudflare": "https://www.cloudflare.com/press-releases/2025/cloudflare-just-changed-how-ai-crawlers-scrape-the-internet-at-large/",
    "seranking": "https://seranking.com/blog/llms-txt/",
    "sej_llms": "https://searchenginejournal.com/97-of-llms-txt-files-got-no-requests-ahrefs-data-shows/579478",
    "ahrefs_brand": "https://ahrefs.com/blog/ai-overview-brand-correlation/",
    "ahrefs_lists": "https://ahrefs.com/blog/best-lists-research/",
    "orbit": "https://www.orbitmedia.com/blog/conversion-rates-ai-search/",
    "demandbase": "https://ppc.land/chatgpt-referrals-to-b2b-sites-gain-303-in-a-year-demandbase-finds/",
    "gartner_repfree": "https://www.gartner.com/en/newsroom/press-releases/2026-03-09-gartner-sales-survey-finds-67-percent-of-b2b-buyers-prefer-a-rep-free-experience",
    "trustradius": "https://hginsights.com/news/trustradius-2026-b2b-buying-disconnect-report-reveals-ai-has-changed-how-buyers-research-but-not-what-they-trust/",
    "google_sd": "https://developers.google.com/search/docs/appearance/structured-data/intro-structured-data?hl=en",
    "sel_schema": "https://searchengineland.com/microsoft-bing-copilot-use-schema-for-its-llms-453455",
    "emarketer": "https://www.emarketer.com/content/b2b-tech-buyers-crave-pricing-transparency",
    "ahrefs_ctr": "https://ahrefs.com/blog/ai-overviews-reduce-clicks-update/",
    "pew": "https://www.pewresearch.org/short-reads/2025/07/22/google-users-are-less-likely-to-click-on-links-when-an-ai-summary-appears-in-the-results/",
    "fps": "https://firstpagesage.com/reports/geo-aeo-pricing-statistics/",
    "searchlab": "https://searchlab.nl/kosten/wat-kost-geo",
    "peec": "https://www.peec.ai/pricing",
    "google_spam": "https://developers.google.com/search/docs/essentials/spam-policies",
    "google_nl_aio": "https://blog.google/intl/nl-nl/product/zoeken-kijken/googlen-is-nu-nog-eenvoudiger-met-ai-overzichten-in-het-nederlands/",
    "google_eu_aio": "https://blog.google/feed/were-bringing-the-helpfulness-of-ai-overviews-to-more-countries-in-europe/",
    "ms_copilot": "https://support.microsoft.com/microsoft-365-copilot/how-web-search-works-in-microsoft-365-copilot-chat-and-agents",
    "bing_feb": "https://blogs.bing.com/webmaster/February-2026/Introducing-AI-Performance-in-Bing-Webmaster-Tools-Public-Preview",
    "bing_jun": "https://blogs.bing.com/search/June-2026/New-AI-Visibility-Insights-in-Bing-Webmaster-Tools-Intents-Topics-Citation-Share-Compare",
    "geo_abs": "https://arxiv.org/abs/2311.09735",
    "gartner_cmo": "https://www.gartner.com/en/newsroom/press-releases/2026-11-09-gartner-survey-finds-42-percent-of-cmos-are-monitoring-brand-misinformation-in-ai-generated-content",
}

POSTS = {}

# ------------------------------------------------------------------ LinkedIn
POSTS["linkedin-ai-citaties-b2b"] = dict(
    en="linkedin-ai-citations-b2b",
    title="LinkedIn en AI-citaties: wat B2B-experts moeten posten om geciteerd te worden",
    excerpt="LinkedIn is na YouTube het meest geciteerde domein in AI-antwoorden. 75% van die citaties komt van persoonlijke profielen. Zo post je als B2B-expert.",
    kw="LinkedIn AI-citaties",
    blocks=[
        tldr("LinkedIn stond op plek twee van de meest geciteerde domeinen in een " + a("onderzoek van Meltwater en LinkedIn", U["meltwater"]) + " naar 9,5 miljoen AI-citaties, na YouTube. Ongeveer 75% van de geciteerde LinkedIn-content kwam van individuele leden (vooral tekstposts) en 48% was jonger dan drie maanden."),
        p("Voor B2B-onderwerpen is LinkedIn een grote bron voor AI geworden. Het onderzoek van Meltwater uit mei 2026 volgde citaties in ChatGPT, Google AI Mode en AI Overviews, Gemini, Copilot en Claude. LinkedIn stond op één in de categorieën AI en data science en marketing en reclame (" + a("Meltwater", U["meltwater"]) + ")."),
        p("Mensen winnen van bedrijfspagina's. Ongeveer 75% van de LinkedIn-citaties kwam van persoonlijke profielen en 25% van bedrijfspagina's. Accounts met 1.000 tot 10.000 volgers leverden samen 40% van de citaties (" + a("Meltwater", U["meltwater"]) + ")."),
        p("En kopers gebruiken wat assistenten vinden. " + a("Gartner", U["gartner_buyers"]) + " meldde in mei 2026 dat 45% van de B2B-kopers generatieve AI gebruikte bij een recente aankoop, vooral om leveranciers en producten te onderzoeken."),
        h2("Welke LinkedIn-content citeert AI?"),
        cap("Geciteerde LinkedIn-content is vooral recente, originele tekst van individuele experts, vaak in de vorm van een lijst, een vergelijking of een keuzegids."),
        table(["Bevinding", "Waarde", "Bron"], [
            ["Tekstposts als deel van de geciteerde content", "72%", "Meltwater, 2026"],
            ["Artikelen", "12%", "Meltwater, 2026"],
            ["Originele content (niet gedeeld)", "72%", "Meltwater, 2026"],
            ["Gepubliceerd in de laatste drie maanden", "48%", "Meltwater, 2026"],
            ["Geciteerde vormen: beste-lijsten, vergelijkingen, keuzegidsen", "54%, 50%, 33%", "Meltwater, 2026"],
        ], "Wat het onderzoek naar 9,5 miljoen citaties over LinkedIn vond."),
        h2("Waarom citeren AI-assistenten LinkedIn-posts?"),
        cap("Een LinkedIn-citatie betekent dat een assistent de ervaring van een professional gebruikt als bewijs voor hoe mensen uit het vak over een onderwerp denken."),
        p("Het aandeel van LinkedIn groeide 26% in de vier weken van het onderzoek. Actualiteit telt: content ouder dan twaalf maanden was maar 12% van de citaties. Wie een jaar niet post, verdwijnt dus langzaam uit de antwoorden."),
        p("Voor Nederlandse kopers telt ook de taal. Bij Nederlandstalige vragen kwam 62,2% van de citaties in ChatGPT uit Nederlandstalige bronnen (" + a("Temso, 2026", U["temso"]) + "). Post je voor de Nederlandse markt, doe dat dan ook in het Nederlands."),
        h2("Wat moeten B2B-teams op LinkedIn posten?"),
        cap("Een LinkedIn-plan voor AI-zichtbaarheid is een plan waarin experts regelmatig originele, specifieke tekst posten in de vormen die assistenten citeren: vergelijkingen, lijsten en keuzegidsen."),
        ol([
            "<strong>Post vanuit mensen</strong>, niet alleen vanaf de bedrijfspagina: oprichters, teamleiders en specialisten.",
            "<strong>Schrijf originele tekst</strong> met een duidelijk punt en concrete cijfers.",
            "<strong>Kies vormen die geciteerd worden:</strong> \"zo kies je\", \"X versus Y\", \"vijf fouten die we zien\".",
            "<strong>Post op een vast ritme</strong>, want recente content domineert de citaties.",
            "<strong>Link naar de verdieping op je site</strong> voor het volledige antwoord met bronnen.",
            "<strong>Deel eigen cijfers.</strong> Een getal uit je eigen meting is iets wat alleen jij kunt geven.",
        ]),
    ] + faq([
        ("Heb je veel volgers nodig?", "Nee. In de data van Meltwater had 51% van de geciteerde makers minder dan 10.000 volgers."),
        ("Worden bedrijfspagina's ook geciteerd?", "Ja, maar minder: ongeveer 25% van de LinkedIn-citaties kwam van bedrijfspagina's."),
        ("Zijn LinkedIn-artikelen beter dan posts?", "Tekstposts waren 72% van de geciteerde content, artikelen 12%. Korte, specifieke posts werken."),
        ("Hoe vaak moeten experts posten?", "Vaak genoeg om recent te blijven: 48% van de geciteerde content was jonger dan drie maanden."),
        ("Vervangt LinkedIn onze blog?", "Nee. Posts worden geciteerd om het perspectief; je site bevat de feiten, prijzen en de diepgang."),
    ]) + [
        cta("Wil je een LinkedIn-plan dat ook je AI-zichtbaarheid opbouwt? " + QS + ". Bekijk ook ons " + GEO + " of lees over " + a("verdiende media en AI-zichtbaarheid", blog("verdiende-media-ai-zichtbaarheid-b2b")) + "."),
    ],
)

# ------------------------------------------------------------------ original research
POSTS["eigen-onderzoek-leadmagneet-ai-citaties"] = dict(
    en="original-research-b2b-lead-magnet-ai-citations",
    title="Eigen onderzoek als leadmagneet: zo verdien je leads en AI-citaties tegelijk",
    excerpt="Statistieken toevoegen gaf 31% meer zichtbaarheid in AI-antwoorden. Zo plan je B2B-onderzoek dat kopers, journalisten en AI-assistenten citeren.",
    kw="eigen onderzoek B2B",
    blocks=[
        tldr("Data wordt geciteerd: statistieken toevoegen verhoogde de zichtbaarheid in antwoorden van generatieve zoekmachines met ongeveer 31% in de " + a("GEO-studie (KDD 2024)", U["geo_paper"]) + ", en onderzoeksrapporten hadden de sterkste directe invloed op de keuze van securityleveranciers, met 46% (" + a("Ponemon en NOLA, 2026", U["ponemon"]) + "). Eigen onderzoek werkt als leadmagneet en als bron voor AI."),
        p("De onderzoekers achter de GEO-studie testten aanpassingen aan content op 10.000 zoekvragen. Citaten toevoegen gaf ongeveer 41% meer zichtbaarheid, statistieken ongeveer 31% en bronvermelding ongeveer 28%. Zoekwoorden herhalen maakte de zichtbaarheid juist slechter (" + a("Aggarwal e.a.", U["geo_paper"]) + ")."),
        p("Kopers waarderen onderzoek ook. In een onderzoek uit juni 2026 onder 320 beslissers over security in grote bedrijven hadden onderzoeks- en surveyrapporten met 46% de sterkste directe invloed op de keuze (" + a("Ponemon en NOLA", U["ponemon"]) + ")."),
        p("En onderzoek levert publiciteit op. Journalistiek was ongeveer 27% van de AI-citaties in de data van Muck Rack uit mei 2026 (" + a("Muck Rack", U["muckrack"]) + "), en journalisten zoeken cijfers."),
        h2("Wat is eigen onderzoek in B2B-marketing?"),
        cap("Eigen onderzoek is data die je bedrijf zelf verzamelt en publiceert, zoals een enquête, een benchmark op geanonimiseerde klantdata of een vaste jaarlijkse index."),
        p("Het hoeft niet groot te zijn. Een goed opgezette enquête onder 200 kopers in één sector is nuttiger dan een algemeen rapport dat iedereen al kent. Onze eigen " + INDEX + " is een voorbeeld: elke maand dezelfde 400 bedrijven, met een vaste methode."),
        h2("Waarom levert eigen onderzoek AI-citaties op?"),
        cap("Eigen onderzoek levert citaties op omdat het een primaire bron is: assistenten en journalisten die een specifiek getal noemen, moeten het aan iemand toeschrijven."),
        p("Elk getal in een AI-antwoord komt ergens vandaan. Ben jij de enige bron voor een cijfer in je niche, dan word jij de bron die geciteerd wordt."),
        h2("Hoe plan je onderzoek dat leads oplevert?"),
        cap("Onderzoek voor leads betekent een vraag kiezen waar kopers om geven, goede data verzamelen en de kern open publiceren, met het rapport achter een formulier."),
        ol([
            "<strong>Kies één vraag</strong> waar je kopers over discussiëren, zoals kosten, benchmarks of adoptie.",
            "<strong>Kies een geloofwaardige methode:</strong> een steekproefgrootte, een periode en een duidelijke definitie.",
            "<strong>Publiceer de kernuitkomsten open</strong> op een HTML-pagina met grafieken en de methode.",
            "<strong>Zet het volledige rapport achter een formulier</strong> als je leads wilt, maar houd de hoofdcijfers open.",
            "<strong>Bied de uitkomsten aan bij vakmedia</strong> en analisten.",
            "<strong>Herhaal het elk jaar</strong>, zodat de trend de reden wordt om jou te citeren.",
        ]),
    ] + faq([
        ("Hoe groot moet een enquête zijn?", "Groot genoeg om geloofwaardig te zijn in je niche. Noem de steekproefgrootte en de methode, zodat lezers zelf kunnen oordelen."),
        ("Moeten we het rapport achter een formulier zetten?", "Zet de volledige pdf achter een formulier als leads belangrijk zijn, maar houd de kerncijfers op een open pagina. Assistenten kunnen niet citeren wat ze niet kunnen lezen."),
        ("Mogen we onze eigen klantdata gebruiken?", "Ja, geanonimiseerd en samengevoegd, met toestemming van klanten waar contracten dat vragen."),
        ("Hoe lang duurt een onderzoek?", "Een gerichte enquête kost vier tot acht weken, van vragen tot gepubliceerde uitkomsten."),
        ("Wat als de uitkomsten saai zijn?", "Dan is de invalshoek verkeerd. Kies een vraag waarop het antwoord onzeker of omstreden is."),
    ]) + [
        cta("Wil je onderzoek dat kopers en journalisten citeren? " + QS + " en we bepalen samen de vraag. Bekijk ook ons " + GEO + "."),
    ],
)

# ------------------------------------------------------------------ Claude
POSTS["claude-ai-zoeken-b2b-citaties"] = dict(
    en="claude-ai-search-b2b-citations",
    title="Claude en AI-zoeken: hoe B2B-merken geciteerd worden door Claude",
    excerpt="Claude citeert minder vaak maar dieper dan andere assistenten: 55% van de antwoorden, gemiddeld 13 bronnen. Zo word je als B2B-merk geciteerd.",
    kw="Claude AI-zoeken",
    blocks=[
        tldr("Claude citeert minder vaak maar dieper dan andere assistenten: in de " + a("data van Muck Rack uit mei 2026", U["muckrack"]) + " noemde Claude in 55% van de antwoorden bronnen, en dan gemiddeld zo'n 13. Laat Claude-SearchBot toe en publiceer primaire pagina's met goede bronnen."),
        p("Claude werkt anders dan ChatGPT. Muck Rack analyseerde meer dan 25 miljoen links. ChatGPT noemt in 96% van de antwoorden bronnen, met zo'n vijf per antwoord. Gemini doet dat in 82% met zo'n acht, en Claude in 55% met gemiddeld 13 als het citeert (" + a("Muck Rack", U["muckrack"]) + ")."),
        p("De bronnen zijn vaak wetenschappelijk. PubMed Central was het meest geciteerde domein van Claude, en U.S. News and World Report de belangrijkste journalistieke bron. YouTube citeerde Claude in de steekproef helemaal niet (" + a("Muck Rack", U["muckrack"]) + ")."),
        p("Bij B2B-koopvragen ziet het er weer anders uit. Clutch zag dat Claude gemiddeld zo'n 11 links per antwoord geeft, vooral naar sites van aanbieders en lijstjes van bureaus (" + a("Clutch, september 2026", U["clutch"]) + "). Het verkeer is nog klein: Claude leverde 5% van de AI-bezoeken aan B2B-sites, tegen 81% voor ChatGPT (" + a("Leadfeeder", U["leadfeeder"]) + ")."),
        h2("Wat is Claude search?"),
        cap("Claude search is de zoekfunctie in Claude van Anthropic die actuele pagina's ophaalt om een vraag te beantwoorden en die pagina's als bron noemt."),
        p("Anthropic gebruikt aparte bots. Claude-SearchBot indexeert pagina's voor zoekantwoorden, Claude-User haalt pagina's op als iemand erom vraagt en ClaudeBot verzamelt trainingsdata. Wie Claude-SearchBot blokkeert, kan minder zichtbaar worden in de zoekresultaten van Claude (" + a("Anthropic", U["anthropic_bots"]) + ")."),
        h2("Hoe verschilt Claude van ChatGPT voor B2B-merken?"),
        cap("Het verschil van Claude voor B2B-merken is diepgang boven frequentie: minder antwoorden bevatten bronnen, maar die noemen er veel, vaak primair en wetenschappelijk."),
        table(["Assistent", "Antwoorden met bronnen", "Gemiddeld aantal bronnen", "Meest geciteerde domein"], [
            ["ChatGPT", "96%", "Ongeveer 5", "Wikipedia"],
            ["Gemini", "82%", "Ongeveer 8", "Reddit"],
            ["Claude", "55%", "Ongeveer 13", "PubMed Central"],
        ], "Bron: Muck Rack, What Is AI Reading, mei 2026."),
        h2("Hoe word je geciteerd door Claude?"),
        cap("Geciteerd worden door Claude betekent een primaire bron zijn: eigen data, heldere definities, beschreven methodes en pagina's die Claude-SearchBot mag lezen."),
        ol([
            "<strong>Laat Claude-SearchBot toe</strong> in robots.txt en in je firewall.",
            "<strong>Publiceer primair materiaal:</strong> je eigen data, methodes en technische documentatie.",
            "<strong>Noem je bronnen op de pagina.</strong> Goed onderbouwde pagina's passen bij de onderzoeksachtige antwoorden van Claude.",
            "<strong>Schrijf precieze definities</strong> onder koppen in vraagvorm.",
            "<strong>Volg Claude apart</strong> in je meting, want de bronnen verschillen per assistent.",
        ]),
        p("In de " + INDEX + " meten we Claude elke maand naast ChatGPT, Gemini en Perplexity, met dezelfde koopvragen. Zo zie je per sector of Claude andere bedrijven noemt."),
    ] + faq([
        ("Is Claude de moeite waard voor B2B?", "Claude stuurt minder verkeer dan ChatGPT, maar als het citeert, noemt het veel bronnen. Hetzelfde werk, eigen data en heldere pagina's, helpt bij alle assistenten."),
        ("Halen we ons uit Claude search als we ClaudeBot blokkeren?", "ClaudeBot is voor training. Claude-SearchBot doet de indexering voor search; volgens Anthropic kan het blokkeren daarvan je zichtbaarheid in zoekantwoorden verlagen."),
        ("Waarom citeert Claude wetenschappelijke bronnen?", "Het meest geciteerde domein in de data van Muck Rack was PubMed Central, een archief van onderzoek. Wie eigen methodes en data publiceert, komt dichter bij die standaard."),
        ("Citeert Claude YouTube?", "Niet in de steekproef van Muck Rack uit mei 2026. Zet de kern van je video's als tekst op je site."),
        ("Hoe zie je verkeer uit Claude?", "Kijk naar doorverwijzingen vanaf claude.ai in je analytics. Leadfeeder zag dat Claude 5% van de AI-bezoeken aan B2B-sites leverde."),
    ]) + [
        cta("Benieuwd hoe Claude vragen over jouw categorie beantwoordt? " + QS + ". Bekijk ook ons " + GEO + " of lees over " + a("eigen onderzoek als leadmagneet", blog("eigen-onderzoek-leadmagneet-ai-citaties")) + "."),
    ],
)

# ------------------------------------------------------------------ Gemini
POSTS["gemini-ai-zoeken-b2b-citaties"] = dict(
    en="gemini-ai-search-b2b-citations",
    title="Gemini en AI-zoeken: welke bronnen Gemini citeert en wat B2B-merken moeten doen",
    excerpt="Gemini noemt in 82% van de antwoorden bronnen, vaak Reddit, en maakte in een EBU-onderzoek de meeste fouten. Zo controleer en versterk je je bronnen.",
    kw="Gemini AI-zoeken",
    blocks=[
        tldr("Gemini noemt in 82% van de antwoorden bronnen, gemiddeld zo'n acht, en Reddit is het meest geciteerde domein (" + a("Muck Rack, mei 2026", U["muckrack"]) + "). Gemini had ook de meeste fouten in een " + a("onderzoek van de EBU en de BBC", U["ebu"]) + ". Kijk dus wat Gemini over je zegt en versterk de bronnen die het leest."),
        p("Gemini gebruikt de index van Google, maar citeert anders dan de gewone zoekresultaten. In de analyse van Muck Rack van meer dan 25 miljoen links was Reddit goed voor zo'n 2,4% van de citaties van Gemini, het grootste losse domein. Forbes was de belangrijkste journalistieke bron (" + a("Muck Rack", U["muckrack"]) + ")."),
        p("Betrouwbaarheid is een punt van zorg. De EBU en de BBC lieten ruim 3.000 antwoorden in 14 talen beoordelen. Bij Gemini had 76% van de antwoorden een serieus probleem, meer dan twee keer zoveel als bij de andere geteste assistenten (" + a("EBU", U["ebu"]) + ")."),
        p("Voor B2B-sites is Gemini de tweede bron van AI-verkeer. Het leverde 8% van de AI-bezoeken, ver achter ChatGPT met 81% (" + a("Leadfeeder, 2026", U["leadfeeder"]) + ")."),
        h2("Wat is grounding bij Gemini?"),
        cap("Grounding bij Gemini is het proces waarbij Google content uit zijn zoekindex aan het model geeft, zodat een antwoord actuele webpagina's kan noemen."),
        p("Google zegt dat de robots.txt-regel voor Google-Extended bepaalt of je content gebruikt wordt voor het trainen van toekomstige Gemini-modellen en voor grounding in de Gemini-apps. Het heeft volgens Google geen invloed op je plek in Google Search (" + a("Google", U["google_crawlers"]) + ")."),
        h2("Hoe kiest Gemini zijn bronnen?"),
        cap("Bronkeuze bij Gemini betekent putten uit de index van Google, maar vaker discussies uit communities citeren en pagina's die nieuwer zijn dan de gewone zoekresultaten."),
        p("Actualiteit telt mee. In een " + a("onderzoek van Ahrefs naar 17 miljoen citaties", U["ahrefs_fresh"]) + " was content die Gemini citeerde gemiddeld 298 dagen nieuwer dan de organische resultaten van Google."),
        table(["Bevinding", "Waarde", "Bron"], [
            ["Antwoorden met bronnen", "82%", "Muck Rack, mei 2026"],
            ["Gemiddeld aantal bronnen per antwoord", "Ongeveer 8", "Muck Rack, mei 2026"],
            ["Meest geciteerde domein", "Reddit, ongeveer 2,4%", "Muck Rack, mei 2026"],
            ["Antwoorden met serieuze problemen", "76%", "EBU en BBC, oktober 2025"],
            ["Aandeel in AI-bezoeken aan B2B-sites", "8%", "Leadfeeder, 2026"],
        ], "Gemini in vijf cijfers."),
        h2("Wat moeten B2B-merken met Gemini doen?"),
        cap("Werken aan zichtbaarheid in Gemini betekent Google-Extended open laten als je grounding wilt, vermeldingen in communities verdienen en de antwoorden over je bedrijf controleren."),
        ol([
            "<strong>Kies bewust over Google-Extended.</strong> Blokkeer je het, dan valt je content buiten de grounding van Gemini.",
            "<strong>Doe mee in relevante discussies op Reddit</strong> als echte specialist, niet als verkoper.",
            "<strong>Houd de kernfeiten op je eigen pagina's actueel</strong> en helder geformuleerd.",
            "<strong>Controleer Gemini elke maand</strong> op prijzen, functies en certificeringen.",
            "<strong>Meld foute antwoorden</strong> via de feedbackknop van Gemini en verbeter de bronpagina's.",
        ]),
        p("Gemini is een van de vier assistenten die we elke maand dezelfde koopvragen stellen in de " + INDEX + ". Ook de wekelijkse " + CHECK + " laat zien welke bedrijven Gemini in jouw categorie noemt."),
    ] + faq([
        ("Kost het blokkeren van Google-Extended posities in Google?", "Volgens Google niet. Het heeft geen invloed op opname in Google Search en is geen rankingfactor."),
        ("Waarom citeert Gemini zo vaak Reddit?", "Reddit was het meest geciteerde domein in de data van Muck Rack. Discussies daar beantwoorden vergelijkingsvragen in de woorden van kopers zelf."),
        ("Is Gemini betrouwbaar genoeg om er tijd in te steken?", "Het foutpercentage was hoog in het onderzoek van de EBU. Juist daarom moet je controleren wat Gemini over je bedrijf zegt."),
        ("Hoeveel verkeer stuurt Gemini?", "Zo'n 8% van de AI-bezoeken aan B2B-sites, in de data van Leadfeeder uit 2026."),
        ("Is Gemini hetzelfde als AI Overviews van Google?", "Beide gebruiken modellen en de index van Google, maar het zijn aparte producten met een eigen opbouw."),
    ]) + [
        cta("Wil je weten wat Gemini over je bedrijf zegt? " + QS + ". Bekijk ook ons " + GEO + " of lees " + a("wat je doet als AI je bedrijf verkeerd beschrijft", blog("foute-ai-antwoorden-over-je-bedrijf")) + "."),
    ],
)

# ------------------------------------------------------------------ company pages
POSTS["welke-bedrijfspaginas-ai-assistenten-citeren"] = dict(
    en="which-company-pages-ai-assistants-cite",
    title="Welke bedrijfspagina's citeren AI-assistenten? Homepage en uitleg winnen",
    excerpt="Homepages en landingspagina's zijn 23,8% van de pagina's die ChatGPT het vaakst citeert, blogs 1,9%. Zo maak je je belangrijkste pagina's citeerbaar.",
    kw="bedrijfspagina's AI-citaties",
    blocks=[
        tldr("Bedrijfssites worden wel geciteerd, alleen niet de pagina's waar teams het meest in steken. Homepages en landingspagina's waren 23,8% van de 1.000 pagina's die ChatGPT het vaakst citeerde, uitlegpagina's 19,4% en blogartikelen 1,9% (" + a("Ahrefs, 2025", U["ahrefs_top"]) + "). Maak je kernpagina's het duidelijkste antwoord over jezelf."),
        p("Ahrefs analyseerde de 1.000 pagina's die ChatGPT in september 2025 het vaakst citeerde. Wikipedia stond bovenaan met 29,7%, daarna homepages en landingspagina's met 23,8% en uitlegpagina's met 19,4%. Blogartikelen waren 1,9% en corporate pagina's 0,5% (" + a("Ahrefs", U["ahrefs_top"]) + ")."),
        p("Over alle sectoren heen weegt eigen content zwaarder. Muck Rack zag dat corporate content gemiddeld 24% van de AI-citaties was, en 36,6% in de maakindustrie (" + a("Muck Rack, mei 2026", U["muckrack"]) + ")."),
        p("In B2B-categorieën waren bedrijfswebsites 18,7% van de citaties in een onderzoek naar 9,5 miljoen citaties. Platforms met content van gebruikers waren 47,5% en reviewsites 15% (" + a("Meltwater", U["meltwater"]) + ")."),
        h2("Welke bedrijfspagina's worden het vaakst geciteerd?"),
        cap("Citaties van bedrijfspagina's zijn vooral homepages en landingspagina's, met op de tweede plaats uitlegpagina's die een begrip helder definiëren of een onderwerp uitleggen."),
        table(["Soort pagina", "Aandeel in de top 1.000 van ChatGPT", "Wat het betekent voor B2B"], [
            ["Homepages en landingspagina's", "23,8%", "Je homepage zegt in gewone woorden wat je doet"],
            ["Uitlegpagina's", "19,4%", "Definities, uitleg en documentatie worden geciteerd"],
            ["Reviews", "5,8%", "Reviews van anderen beschrijven jou"],
            ["Blogartikelen", "1,9%", "Algemene blogs worden zelden geciteerd"],
            ["Corporate pagina's", "0,5%", "Een over-ons-pagina heeft feiten nodig, geen slogans"],
        ], "Bron: Ahrefs, top 1.000 citaties van ChatGPT, september 2025."),
        h2("Waarom worden zo weinig blogs geciteerd?"),
        cap("Het waarschijnlijke probleem van blogs is herhaling: veel artikelen zeggen wat andere pagina's al zeggen, dus kiezen assistenten de oorspronkelijke definitie, productpagina of primaire data."),
        p("Een hoge positie in Google is ook geen voorwaarde. In de data van Ahrefs had 28,3% van de meest geciteerde pagina's helemaal geen organische zoekwoorden. De duidelijkste bron zijn telt zwaarder."),
        p("Voor Nederlandse bedrijven ligt er een simpele winst op de homepage. In onze " + a("scan van 400 B2B-websites", REPORT) + " van 25 november 2026 vertelde maar 45% van de Nederlandse homepages in Organization-schema wie het bedrijf is, en 36% had sameAs-links naar profielen als LinkedIn en Wikidata."),
        h2("Hoe maak je je kernpagina's citeerbaar?"),
        cap("Kernpagina's citeerbaar maken betekent feiten gewoon opschrijven: wat je doet, voor wie, tegen welke prijs, met welke koppelingen en certificeringen, als tekst in HTML."),
        ol([
            "<strong>Herschrijf de opening van je homepage</strong> in één gewone zin: wat je doet en voor wie.",
            "<strong>Geef elk product een eigen pagina</strong> met functies, koppelingen en hoe de prijs werkt.",
            "<strong>Zet documentatie online als HTML</strong>, niet als pdf achter een login.",
            "<strong>Maak een begrippenlijst of uitlegsectie</strong> voor de termen waar je kopers op zoeken.",
            "<strong>Zet bedrijfsfeiten op de over-ons-pagina:</strong> oprichtingsjaar, vestigingen, team en klanten.",
            "<strong>Voeg Organization-schema toe</strong> met sameAs-links naar LinkedIn, Wikidata en de KvK.",
        ]),
    ] + faq([
        ("Moeten we stoppen met bloggen?", "Nee, maar schrijf artikelen die iets nieuws toevoegen: data, ervaring of een duidelijke mening. Herschreven bestaande content wordt zelden geciteerd."),
        ("Waarom is Wikipedia zo dominant?", "Het was 29,7% van de meest geciteerde pagina's van ChatGPT. Voor de meeste B2B-bedrijven is een Wikipedia-artikel niet haalbaar; een Wikidata-item met bronnen wel."),
        ("Tellen documentatiepagina's mee?", "Uitlegpagina's, waaronder documentatie, waren 19,4% van de top. Openbare documentatie helpt dus."),
        ("Moet onze homepage hoog staan in Google om geciteerd te worden?", "Niet per se. In het onderzoek van Ahrefs had 28,3% van de meest geciteerde pagina's geen organische zoekwoorden."),
        ("Wat hoort er op de over-ons-pagina?", "Harde feiten: wie je bent, waar je werkt, wat je verkoopt, belangrijke klanten en de directie."),
    ]) + [
        cta("Wil je dat AI je homepage en productpagina's citeert? " + QS + ". Bekijk ook ons " + GEO + " of doe de " + CHECK + "."),
    ],
)

# ------------------------------------------------------------------ crawler checklist
POSTS["ai-crawler-checklist-b2b-website"] = dict(
    en="ai-crawler-checklist-b2b-website",
    title="Kan ChatGPT je website lezen? Een checklist van 12 punten voor AI-crawlers",
    excerpt="Geen van de grote AI-crawlers voert JavaScript uit. Met deze 12 checks zie je of ChatGPT, Claude en Perplexity je B2B-website kunnen lezen.",
    kw="AI-crawlers checklist",
    blocks=[
        tldr("Geen van de grote AI-crawlers voerde JavaScript uit in de " + a("analyse van Vercel uit 2024", U["vercel"]) + ". Heeft je site JavaScript nodig om tekst te tonen, dan ziet AI-zoeken misschien een lege pagina. Met deze 12 checks zie je in een half uur of ChatGPT, Claude en Perplexity je kunnen lezen."),
        p("We leerden dit op onze eigen site. De website van Booming Venture begon als React-app, gebouwd in Lovable. In een browser zag hij er goed uit, maar bots die geen JavaScript uitvoeren zagen bijna niets. In 2026 bouwden we hem opnieuw als WordPress-site die de HTML op de server maakt."),
        p("Het verkeer is echt. Vercel telde in één maand 569 miljoen bezoeken van GPTBot en 370 miljoen van Claude op zijn netwerk, zo'n 20% van de 4,5 miljard van Googlebot (" + a("Vercel", U["vercel"]) + "). En kopers gebruiken de antwoorden: volgens " + a("Forrester", U["forrester"]) + " gebruikt 94% van de zakelijke kopers AI in hun aankoopproces."),
        p("In Nederland is de voordeur meestal open. In onze " + a("scan van 400 B2B-websites", REPORT) + " liet 98% van de leesbare robots.txt-bestanden OAI-SearchBot toe. Toch weigerden 77 homepages ons verzoek, vaak door een firewall, en bij drie van de tien Nederlandse managementadviesbureaus stond zonder JavaScript bijna geen tekst op de homepage."),
        h2("Wat is een AI-crawler?"),
        cap("Een AI-crawler is een bot van een AI-bedrijf, zoals GPTBot of PerplexityBot, die pagina's ophaalt voor het trainen van modellen of voor antwoorden in AI-zoeken."),
        p("Er zijn drie soorten, en ze tellen verschillend mee. Trainingscrawlers verzamelen pagina's voor toekomstige modellen. Zoekcrawlers bouwen de index waaruit AI-zoeken antwoordt. Ophalers op verzoek openen een pagina als iemand ernaar vraagt. Blokkeer je de verkeerde, dan verdwijn je uit AI-antwoorden zonder dat iemand het merkt."),
        table(["Crawler", "Bedrijf", "Wat hij doet", "Als je hem blokkeert"], [
            ["GPTBot", "OpenAI", "Verzamelt content voor training", "Je pagina's blijven buiten de training; ChatGPT search merkt er niets van"],
            ["OAI-SearchBot", "OpenAI", "Bouwt de index voor ChatGPT search", "Je komt niet in de antwoorden van ChatGPT search"],
            ["ChatGPT-User", "OpenAI", "Opent pagina's waar een gebruiker om vraagt", "Geen automatische crawler, dus geen invloed op opname in search"],
            ["ClaudeBot", "Anthropic", "Verzamelt content voor training", "Je toekomstige pagina's blijven buiten de trainingsdata"],
            ["Claude-SearchBot", "Anthropic", "Indexeert pagina's voor zoekantwoorden van Claude", "Volgens Anthropic kan je zichtbaarheid in Claude search dalen"],
            ["PerplexityBot", "Perplexity", "Toont en linkt sites in Perplexity", "Je valt uit de resultaten van Perplexity; niet gebruikt voor training"],
            ["Google-Extended", "Google", "Bepaalt gebruik in de Gemini-apps en Vertex AI", "Geen grounding met Google Search in Vertex AI; je posities in Google blijven gelijk"],
        ], "Bronnen: " + a("OpenAI", U["openai_bots"]) + ", " + a("Anthropic", U["anthropic_bots"]) + ", " + a("Perplexity", U["perplexity_bots"]) + ", " + a("Google", U["google_crawlers"]) + "."),
        h2("Waarom zien sommige websites er leeg uit voor AI-crawlers?"),
        cap("Een lege pagina voor AI-crawlers betekent tekst die pas verschijnt nadat JavaScript draait, of een verzoek dat robots.txt of een firewall tegenhoudt."),
        p("Apps gebouwd met React, Vue of tools als Lovable sturen vaak een bijna lege HTML-pagina en vullen die met JavaScript. Google voert JavaScript uit, dus zulke sites kunnen nog scoren in Google. AI-crawlers die dat overslaan, zien alleen het lege omhulsel."),
        p("Firewalls zijn de tweede oorzaak. " + a("Cloudflare", U["cloudflare"]) + ", dat naar eigen zeggen verkeer voor 20% van het web afhandelt, blokkeert sinds juli 2025 standaard AI-crawlers en laat de keuze aan de eigenaar van de site. Veel teams hebben die keuze nooit bewust gemaakt."),
        p("De derde oorzaak valt minder op. Strenge limieten bij je hostingpartij kunnen bots beantwoorden met een 503-fout. Dat zagen we zelf tijdens onze audits: snel crawlen vanaf één IP-adres leverde 503-antwoorden op bij de host."),
        h2("Hoe check je of ChatGPT je site kan lezen?"),
        cap("AI-leesbaarheid checken betekent vier lagen op volgorde testen: de HTML, de toegang voor crawlers, de opbouw van de pagina en de signalen van vertrouwen."),
        ol([
            "<strong>Lees de kale HTML.</strong> Open de paginabron in je browser, of haal de pagina op met curl, en zoek een zin uit je hoofdtekst. Staat hij er niet in, dan zien veel AI-crawlers hem ook niet.",
            "<strong>Laad de pagina opnieuw met JavaScript uit.</strong> Je kop, tekst en navigatie moeten blijven staan.",
            "<strong>Laat de zoekcrawlers toe.</strong> OAI-SearchBot, Claude-SearchBot en PerplexityBot horen in robots.txt toegestaan te zijn als je in ChatGPT, Claude en Perplexity wilt verschijnen.",
            "<strong>Kies bewust over trainingscrawlers.</strong> GPTBot of ClaudeBot blokkeren houdt je pagina's buiten toekomstige trainingsdata. Pagina's die Google-Extended weren, gebruikt Google niet voor grounding in Vertex AI.",
            "<strong>Kijk naar je firewall en CDN.</strong> Zoek de instellingen voor AI-crawlers en bots in Cloudflare, Wordfence of het paneel van je host, en controleer of de crawlers die je wilt erdoor komen.",
            "<strong>Test de statuscodes.</strong> Vraag een paar pagina's op met de user agent van een AI-crawler en controleer of je een 200 krijgt, geen 403 of 503.",
            "<strong>Begin elke pagina met het antwoord.</strong> Eén H1, H2's in vraagvorm en direct daaronder een antwoord van twee zinnen. Assistenten citeren passages, geen hele pagina's.",
            "<strong>Houd kernfeiten in tekst.</strong> Prijzen, specificaties en bewijs in afbeeldingen of pdf's worden makkelijk gemist. Zet ze in HTML-tekst of tabellen.",
            "<strong>Voeg gestructureerde data toe.</strong> Organization-, Article- en Person-markup in JSON-LD vertelt crawlers wie je bent en wie de pagina schreef.",
            "<strong>Houd je sitemaps actueel.</strong> Meld een XML-sitemap met kloppende lastmod-datums aan bij Bing Webmaster Tools. Bing is de index achter Microsoft Copilot.",
            "<strong>Let op de snelheid van je server onder druk.</strong> Trage antwoorden en time-outs lijken op ontbrekende pagina's voor een crawler met een beperkt budget.",
            "<strong>Laat zien wie achter de content zit.</strong> Een auteur met naam en achtergrond en een over-ons-pagina met bedrijfsfeiten geven assistenten en lezers een reden om de pagina te vertrouwen.",
        ]),
        p("De eerste zes punten test de " + CHECK + " op de website van elk domein, met dezelfde checks als onze " + INDEX + "."),
        h2("Wat leerden we van het herbouwen van onze eigen site?"),
        cap("Onze belangrijkste les is dat de HTML eerst komt: content, schema en promptwerk leveren pas iets op als AI-crawlers die HTML kunnen lezen."),
        p("Na de herbouw liet onze eigen crawl de volledige tekst in de HTML zien, met koppen, meta descriptions en JSON-LD op hun plek. Daarvoor zagen bots die geen JavaScript uitvoeren weinig meer dan een leeg omhulsel."),
        p("Twee dingen verrasten ons. De limieten bij onze host beantwoordden onze auditcrawler met 503-fouten, en bots krijgen die ook. En onze robots.txt blokkeerde nog AI-trainingscrawlers die we later bewust toelieten voor de zichtbaarheid van ons merk. Check allebei, ook als je site er in de browser goed uitziet."),
    ] + faq([
        ("Voeren AI-crawlers JavaScript uit?", "In de analyse van Vercel uit december 2024 deed geen van de grote AI-crawlers dat. Googlebot wel, en daarom kan een JavaScript-site goed scoren in Google en toch slecht leesbaar zijn voor ChatGPT of Claude."),
        ("Verdwijnen we uit ChatGPT search als we GPTBot blokkeren?", "Nee. Volgens OpenAI kan een site OAI-SearchBot toelaten voor de zoekresultaten en GPTBot weren. Opname in ChatGPT search hangt af van OAI-SearchBot."),
        ("Moet een B2B-bedrijf trainingscrawlers toelaten?", "Dat is een zakelijke keuze. Toelaten brengt je openbare pagina's in de kennis van toekomstige modellen, wat kan helpen om in antwoorden genoemd te worden. Blokkeren beschermt content die je als eigendom ziet. Zoekcrawlers laat je in beide gevallen toe."),
        ("Houdt Perplexity zich aan robots.txt?", "Perplexity zegt dat PerplexityBot niet crawlt voor het trainen van modellen, en dat Perplexity-User robots.txt meestal negeert omdat een persoon om de pagina vroeg."),
        ("Hoe vaak moeten we deze check doen?", "Na elk nieuw ontwerp, elke migratie naar een ander CMS en elke wijziging in de firewall, en minstens eens per kwartaal."),
    ]) + [
        cta("Weet je niet zeker wat AI-crawlers op je site zien? " + QS + " en we kijken samen naar de HTML, robots.txt en de firewall. Bekijk ook ons " + GEO + " of lees " + a("hoe je een GEO-bureau kiest", blog("geo-bureau-kiezen-10-vragen")) + "."),
    ],
)

# ------------------------------------------------------------------ llms.txt
POSTS["helpt-llms-txt-ai-zichtbaarheid"] = dict(
    en="does-llms-txt-help-ai-visibility",
    title="Helpt llms.txt bij AI-zichtbaarheid? Wat het onderzoek zegt",
    excerpt="Bij bijna 300.000 domeinen gaf llms.txt geen meetbaar effect op AI-citaties, en 97% van de bestanden kreeg geen enkel verzoek. Waar je tijd beter naartoe gaat.",
    kw="llms.txt",
    blocks=[
        tldr("Tot nu toe geen meetbaar effect: " + a("SE Ranking", U["seranking"]) + " vond bij bijna 300.000 domeinen geen verband tussen llms.txt en AI-citaties (oktober 2026), en " + a("data van Ahrefs", U["sej_llms"]) + " liet zien dat 97% van de llms.txt-bestanden in mei 2026 geen enkel verzoek kreeg. Houd er een als het niets kost. Steek het budget in iets anders."),
        p("Weinig GEO-tactieken verspreidden zich zo snel als llms.txt, en weinig hebben zo weinig bewijs achter zich. SE Ranking zag dat 10,13% van de onderzochte domeinen het bestand had. Toen de onderzoekers llms.txt uit hun voorspelmodel voor AI-citaties haalden, werd het model nauwkeuriger in plaats van minder nauwkeurig (" + a("SE Ranking", U["seranking"]) + ")."),
        p("Ahrefs keek naar de vraagkant. Van zo'n 38.000 domeinen met een geldig bestand kregen er maar zo'n 1.100 in mei 2026 überhaupt een verzoek. Bots van ChatGPT en Perplexity die pagina's ophalen voor antwoorden deden 1,1% van de verzoeken (" + a("Search Engine Journal over data van Ahrefs", U["sej_llms"]) + ")."),
        p("Intussen groeit AI-zoeken als inkoopkanaal: volgens " + a("Forrester", U["forrester"]) + " gebruikt 94% van de zakelijke kopers AI in het aankoopproces. De vraag is dus waar je inspanning iets oplevert."),
        h2("Wat is llms.txt?"),
        cap("llms.txt is een voorgesteld tekstbestand in de hoofdmap van een website dat de belangrijkste pagina's in Markdown opsomt, zodat taalmodellen ze kunnen vinden."),
        p("Het staat naast robots.txt en sitemap.xml. Anders dan bij die twee is er geen openbaar bewijs dat zoekcrawlers van AI-bedrijven het gebruiken om bronnen te kiezen."),
        p("In Nederland is het nog geen gewoonte. In onze " + a("scan van 400 B2B-websites", REPORT) + " van 25 november 2026 had 19% van de Nederlandse bedrijven een llms.txt, tegen 16% in de internationale lijst. In de " + INDEX + " telt het daarom voor 2 van de 100 punten."),
        h2("Levert llms.txt meer AI-citaties op?"),
        cap("Het effect van llms.txt is volgens het huidige bewijs nul: een onderzoek naar 300.000 domeinen vond geen verband tussen het bestand en AI-citaties."),
        table(["Meting", "Uitkomst", "Bron"], [
            ["Onderzochte domeinen", "Ongeveer 300.000", "SE Ranking, oktober 2026"],
            ["Domeinen met llms.txt", "10,13%", "SE Ranking"],
            ["Verband met AI-citaties", "Niet gevonden", "SE Ranking"],
            ["Domeinen in de steekproef van serverlogs", "137.000", "Ahrefs, mei 2026"],
            ["Geldige bestanden zonder enig verzoek", "97%", "Ahrefs"],
            ["Verzoeken van ophaalbots van ChatGPT en Perplexity", "1,1%", "Ahrefs"],
            ["Verzoeken van SEO-audittools", "21%", "Ahrefs"],
        ], "Wat twee grote datasets over llms.txt laten zien."),
        h2("Waarom publiceren zoveel sites er toch een?"),
        cap("Een llms.txt publiceren is een goedkope gok: het kost een uur, doet geen meetbare schade en kan tellen als AI-bedrijven de standaard later gaan gebruiken."),
        p("We publiceren er zelf ook een, automatisch gemaakt door ons WordPress-thema, omdat het bijhouden niets kost. Er is één echte doelgroep: programmeeragents deden 10% van de verzoeken in de data van Ahrefs. Heb je documentatie voor developers, dan helpt een nette llms.txt die tools."),
        h2("Wat kun je beter doen?"),
        cap("De betere investering is wat assistenten wel lezen: leesbare HTML, pagina's die met het antwoord beginnen, gestructureerde data, actuele feiten en vermeldingen elders."),
        ol([
            "<strong>Laat de zoekcrawlers toe:</strong> OAI-SearchBot, Claude-SearchBot, PerplexityBot en Bingbot.",
            "<strong>Lever volledige HTML</strong>, zodat crawlers die geen JavaScript uitvoeren je tekst zien.",
            "<strong>Begin elke sectie met het antwoord</strong>, in twee zinnen onder een kop in vraagvorm.",
            "<strong>Zet kernfeiten in tekst en tabellen</strong>, niet in afbeeldingen of pdf's.",
            "<strong>Verdien vermeldingen op andere sites.</strong> Merkvermeldingen hadden een correlatie van 0,664 met zichtbaarheid in AI Overviews in het " + a("onderzoek van Ahrefs uit 2025", U["ahrefs_brand"]) + ".",
            "<strong>Meld je sitemap aan bij Bing</strong>, de index achter Microsoft Copilot.",
        ]),
        p("Onze " + a("checklist voor AI-crawlers", blog("ai-crawler-checklist-b2b-website")) + " maakt van de eerste vier punten een test van een half uur."),
    ] + faq([
        ("Gebruikt Google llms.txt?", "Googlebot deed 13% van de verzoeken naar llms.txt in de data van Ahrefs, maar een verzoek is nog geen gebruik. Geen enkel onderzoek liet een effect op posities of citaties zien."),
        ("Moeten we onze llms.txt weghalen?", "Dat hoeft niet. Het doet geen meetbare schade. Tel het alleen niet als GEO-werk."),
        ("En llms-full.txt?", "Daarvoor geldt hetzelfde: er is geen gepubliceerd bewijs dat het tot AI-citaties leidt."),
        ("Kan llms.txt later wel tellen?", "Dat kan, als AI-bedrijven het gaan gebruiken. Een eenvoudig, kloppend bestand is een goedkope verzekering."),
        ("Wie leest llms.txt nu?", "Vooral SEO-audittools, onbekende bots, Googlebot en programmeeragents, volgens de data van Ahrefs. AI-zoekassistenten deden een klein deel van de verzoeken."),
    ]) + [
        cta("Wil je weten wat jouw AI-zichtbaarheid echt verbetert? " + QS + " en we kijken naar de signalen die tellen. Bekijk ook ons " + GEO + "."),
    ],
)

# ------------------------------------------------------------------ cybersecurity
POSTS["ai-zichtbaarheid-cybersecurity-leveranciers"] = dict(
    en="ai-visibility-cybersecurity-vendors",
    title="AI-zichtbaarheid voor cybersecurityleveranciers: zo kom je in de antwoorden",
    excerpt="51% van de beslissers over security gebruikt chatbots bij productonderzoek. Zo komen securityleveranciers in de AI-antwoorden waar kopers op vertrouwen.",
    kw="AI-zichtbaarheid cybersecurity",
    blocks=[
        tldr("In een onderzoek van het " + a("Ponemon Institute en NOLA", U["ponemon"]) + " uit juni 2026 onder 320 beslissers over security in grote bedrijven gebruikte 51% chatbots bij productonderzoek. Securityleveranciers komen in AI-antwoorden als vakgenoten, analisten en onderzoeksrapporten hen eerst noemen, en als hun eigen pagina's technische vragen in gewone tekst beantwoorden."),
        p("Securitykopers zoeken anders dan de meeste B2B-kopers. Ze wantrouwen marketing, controleren beweringen en leunen op vakgenoten. Volgens het onderzoek van Ponemon waren aanbevelingen van vakgenoten met 55% de belangrijkste bron om leveranciers te ontdekken, en hadden onderzoeksrapporten met 46% de grootste invloed op de uiteindelijke keuze (" + a("Ponemon en NOLA", U["ponemon"]) + ")."),
        p("AI zit nu midden in dat proces: 51% gebruikt chatbots en 48% gebruikt AI-overzichten bij productonderzoek. Microsoft Copilot verdient extra aandacht, want " + a("Forrester", U["forrester"]) + " zag dat zakelijke kopers het vier keer zo vaak gebruiken als consumenten."),
        p("En vragen kopers om een shortlist, dan leunen assistenten op lijstjes. In een " + a("onderzoek van Ahrefs naar 750 prompts in ChatGPT", U["ahrefs_lists"]) + " waren beste-lijsten 43,8% van de geciteerde soorten pagina's."),
        h2("Wat is AI-zichtbaarheid voor een cybersecurityleverancier?"),
        cap("AI-zichtbaarheid voor een cybersecurityleverancier is hoe vaak assistenten als ChatGPT en Copilot je product noemen als securitykopers om opties vragen."),
        p("Het heeft twee kanten. Genoemd worden in antwoorden met een shortlist, zoals \"beste XDR voor middelgrote banken\". En goed beschreven worden als een koper direct naar jou vraagt: certificeringen, manier van uitrollen, koppelingen en hoe de prijs werkt."),
        h2("Waarom vertrouwen securitykopers sommige AI-antwoorden meer dan andere?"),
        cap("Vertrouwen in een AI-antwoord betekent dat de koper het kan controleren: securityleiders wegen vakgenoten, onderzoeksrapporten en technische diepgang zwaarder dan welke samenvatting ook."),
        p("Hetzelfde onderzoek liet zien dat 52% van de securityleiders vindt dat marketing van leveranciers technische diepgang mist, en 49% mist een onderbouwing van het rendement. Een AI-antwoord dat op vage productpagina's leunt, erft dat probleem. Een antwoord dat je architectuurpagina, je testresultaten of een onafhankelijk rapport citeert, geeft de koper iets om te controleren."),
        h2("Welke vragen moet een securityleverancier volgen?"),
        cap("Een set securityvragen is een lijst koopvragen per categorie, wettelijke eis en bedrijfsgrootte, zoals \"beste XDR voor middelgrote banken\", die je elke maand opnieuw stelt."),
        table(["Fase", "Voorbeeldvraag", "Pagina's om te maken of te beïnvloeden"], [
            ["Probleem", "Hoe verminderen we alert-moeheid in een klein SOC?", "Praktische gidsen met cijfers uit je eigen implementaties"],
            ["Categorie", "Wat is het verschil tussen EDR en XDR?", "Een heldere definitiepagina met een vergelijkingstabel"],
            ["Shortlist", "Beste XDR voor middelgrote banken", "Onafhankelijke beste-lijsten, reviewplatforms, notities van analisten"],
            ["Wetgeving", "Welke tools helpen bij het melden van incidenten onder NIS2?", "Een pagina per wet die functies koppelt aan verplichtingen"],
            ["Controle", "Is [jouw bedrijf] ISO 27001-gecertificeerd?", "Een trust center met certificeringen als HTML-tekst"],
        ], "Een eerste set vragen per fase van de aankoop."),
        h2("Hoe worden cybersecurityleveranciers aanbevolen?"),
        cap("Aanbevolen worden betekent genoemd worden in de bronnen die assistenten lezen: notities van analisten, reviews van vakgenoten, beste-lijsten, onderzoeksrapporten en je eigen technische pagina's."),
        ol([
            "<strong>Breng 50 tot 100 vragen in kaart</strong> over je categorieën, wetten en doelsectoren, in elke taal die je kopers gebruiken. Voor Nederlandse kopers dus ook in het Nederlands.",
            "<strong>Publiceer technische antwoordpagina's.</strong> Architectuur, koppelingen, implementatietijd en waar data staat, in HTML-tekst en tabellen, niet alleen in pdf's.",
            "<strong>Bouw een trust center.</strong> SOC 2, ISO 27001 en andere certificeringen als tekst, met datum en reikwijdte.",
            "<strong>Publiceer eigen onderzoek.</strong> Onderzoeksrapporten hadden de grootste directe invloed op de keuze. Zet de kernuitkomsten op een open pagina zodat assistenten ze kunnen citeren.",
            "<strong>Laat reviews groeien</strong> op G2, Gartner Peer Insights en PeerSpot. Vakgenoten zijn de belangrijkste bron om leveranciers te ontdekken.",
            "<strong>Kom op onafhankelijke beste-lijsten</strong> voor je categorie. Ons artikel over " + a("de lijsten die AI-assistenten citeren", blog("best-of-lijsten-ai-assistenten")) + " legt uit hoe.",
            "<strong>Controleer de toegang voor crawlers.</strong> Securityleveranciers hebben vaak strenge firewalls. Zorg dat OAI-SearchBot, Claude-SearchBot, PerplexityBot en Bingbot erdoor komen. Onze " + a("checklist voor AI-crawlers", blog("ai-crawler-checklist-b2b-website")) + " kost een half uur.",
        ]),
    ] + faq([
        ("Telt Microsoft Copilot voor securityleveranciers?", "Ja. Veel securityteams werken in Microsoft 365, en Forrester zag dat zakelijke kopers Copilot vier keer zo vaak gebruiken als consumenten. Copilot zoekt via Bing, dus indexering in Bing telt mee."),
        ("Moeten we onze onderzoeksrapporten achter een formulier zetten?", "Zet de volledige pdf achter een formulier als leads ervan afhangen, maar publiceer de kernuitkomsten, grafieken en methode op een open HTML-pagina. Assistenten kunnen niet citeren wat ze niet kunnen lezen."),
        ("Helpen reviewplatforms bij AI-zichtbaarheid?", "Ze horen bij de bronnen die assistenten lezen voor shortlistvragen, en vakgenoten waren de belangrijkste bron voor securitykopers in het onderzoek van Ponemon. Een vaste stroom recente reviews helpt bij allebei."),
        ("Hoe weten we wat AI over ons zegt?", "Stel elke maand dezelfde vragen aan de grote assistenten en leg de antwoorden vast, en kijk in Bing Webmaster Tools naar het rapport AI Performance voor citaties in Copilot."),
        ("Kunnen AI-antwoorden ons product verkeerd beschrijven?", "Ja, vooral bij certificeringen en koppelingen. Zet die feiten in gewone tekst op je eigen site en laat verouderde pagina's van anderen verbeteren waar dat kan."),
    ]) + [
        cta("Verkoop je security aan Europese bedrijven? " + QS + " en we kijken hoe ChatGPT, Copilot en Perplexity je nu beschrijven. Bekijk ook ons " + GEO + "."),
    ],
)

# ------------------------------------------------------------------ GA4
POSTS["ai-verkeer-meten-ga4-b2b"] = dict(
    en="measure-ai-referral-traffic-ga4-b2b",
    title="AI-verkeer meten in GA4: zo zie je wat ChatGPT je B2B-site oplevert",
    excerpt="AI-bronnen leveren zo'n 0,5% van de bezoeken aan B2B-sites, maar die bezoekers worden drie keer zo vaak een lead. Zo zet je een AI-kanaal op in GA4.",
    kw="AI-verkeer GA4",
    blocks=[
        tldr("AI-verkeer is klein maar converteert: in het " + a("onderzoek van Orbit Media naar 97 B2B-sites", U["orbit"]) + " leverden AI-bronnen zo'n 0,5% van de bezoeken, maar die bezoekers werden ongeveer drie keer zo vaak een lead. Zet een apart AI-kanaal op in GA4, zodat je het ziet."),
        p("Het aandeel verschilt per onderzoek, maar het is overal klein. Orbit Media zag dat zo'n 0,5% van 29 miljoen sessies tussen juli 2025 en juni 2026 uit AI-bronnen kwam (" + a("Orbit Media", U["orbit"]) + "). Leadfeeder mat AI op 1,8% van de bezoeken uit AI en organisch zoeken samen in 2026 (" + a("Leadfeeder", U["leadfeeder"]) + ")."),
        p("Het groeit wel. Demandbase zag tussen juni 2025 en juni 2026 303% meer doorverwijzingen van ChatGPT naar B2B-sites (" + a("PPC Land over data van Demandbase", U["demandbase"]) + ")."),
        p("En het is geconcentreerd: ChatGPT was goed voor zo'n 82% van de AI-sessies in de data van Orbit en 81% van de AI-bezoeken in de data van Leadfeeder."),
        h2("Wat is AI-verkeer?"),
        cap("AI-verkeer is bezoek dat binnenkomt via een klik op een link in het antwoord van een AI-assistent, zoals ChatGPT, Gemini, Perplexity, Claude of Copilot."),
        p("Niet alle invloed van AI zie je zo terug. Orbit Media wijst erop dat AI Mode en AI Overviews van Google als organisch zoekverkeer worden geteld, en dat sommige bezoeken vanuit AI-apps als direct verkeer binnenkomen."),
        h2("Hoe zet je AI-meting op in GA4?"),
        cap("AI-meting opzetten in GA4 betekent een eigen kanaalgroep maken die verwijzers van AI-assistenten herkent, en daarna leads per kanaal rapporteren."),
        ol([
            "<strong>Maak een eigen kanaalgroep</strong> in het beheer van GA4, onder gegevensweergave.",
            "<strong>Voeg een kanaal \"AI-assistenten\" toe boven Verwijzing</strong>, met bronnen als chatgpt.com, gemini.google.com, perplexity.ai, claude.ai en copilot.microsoft.com.",
            "<strong>Sla het op en gebruik het</strong> in je acquisitierapporten.",
            "<strong>Markeer je lead-events als belangrijke gebeurtenissen</strong>, zoals ingevulde formulieren en demo-aanvragen.",
            "<strong>Vergelijk elke maand de conversie</strong> per kanaal.",
            "<strong>Voeg een veld \"hoe vond je ons?\" toe aan formulieren</strong>, met AI-assistenten als keuze, om bezoeken te vangen die GA4 niet kan toewijzen.",
        ]),
        h2("Hoe lees je de cijfers?"),
        cap("AI-verkeer lezen betekent conversie afwegen tegen fit: AI-bezoekers converteren goed, maar minder van hen passen bij je ideale klantprofiel dan bezoekers uit organisch zoeken."),
        table(["Meting", "AI-verkeer", "Vergelijking", "Bron"], [
            ["Aandeel in het verkeer", "Ongeveer 0,5%", "Alle sessies", "Orbit Media"],
            ["Kans op een lead", "Ongeveer 3 keer zo groot", "Andere organische bronnen", "Orbit Media"],
            ["Bezoekende bedrijven die passen bij het klantprofiel", "15,8%", "Organisch zoeken: 20,4%", "Leadfeeder"],
            ["Bezoeken per bedrijf", "2,68", "Organisch zoeken: 3,78", "Leadfeeder"],
        ], "AI-verkeer: veel koopintentie, kleiner en minder gericht."),
        p("Wil je zien wat een paar extra leads per maand waard zijn? Reken het door met de " + a("Funnel Leak Calculator", "/nl/funnel-calculator/") + "."),
    ] + faq([
        ("Waarom laat GA4 zo weinig AI-verkeer zien?", "Veel AI-antwoorden leveren geen klik op, AI Mode telt als organisch zoeken en sommige bezoeken uit apps komen als direct binnen. Zie de cijfers in GA4 als ondergrens."),
        ("Welke AI-assistent stuurt het meeste B2B-verkeer?", "ChatGPT, met zo'n 81% tot 82% van de AI-bezoeken in de onderzoeken van Leadfeeder en Orbit Media."),
        ("Is AI-verkeer beter dan organisch verkeer?", "Het converteert vaker, volgens Orbit Media, maar Leadfeeder zag een kleiner deel bezoekers dat bij het ideale klantprofiel past."),
        ("Moeten we AI-verkeer aan het bestuur rapporteren?", "Rapporteer het naast cijfers over AI-zichtbaarheid en de antwoorden in je formulieren. Verkeer alleen onderschat de invloed van AI."),
        ("Hoe vaak moeten we ernaar kijken?", "Elke maand. De aantallen zijn klein, dus kijk naar trends over drie maanden voordat je conclusies trekt."),
    ]) + [
        cta("Wil je AI-verkeer en leads in één rapport? " + QS + ". Bekijk ook ons " + GEO + " of lees " + a("welke cijfers over AI-zichtbaarheid het bestuur wil zien", blog("ai-zichtbaarheid-cijfers-bestuur")) + "."),
    ],
)

# ------------------------------------------------------------------ buyers
POSTS["b2b-kopers-ai-voor-sales"] = dict(
    en="b2b-buyers-ai-before-sales",
    title="B2B-kopers vragen eerst AI, dan pas sales: wat dat betekent voor marketing",
    excerpt="45% van de B2B-kopers gebruikte generatieve AI bij een recente aankoop en 69% laat AI-inzichten controleren door een verkoper. Zo speel je daarop in.",
    kw="B2B-kopers AI",
    blocks=[
        tldr("45% van de B2B-kopers gebruikte generatieve AI bij een recente aankoop, en 69% laat inzichten uit AI liever controleren door een verkoper (" + a("Gartner, 2026", U["gartner_buyers"]) + "). Kopers onderzoeken met AI, controleren bij vakgenoten en in reviews, en komen bij sales met een shortlist."),
        p("Gartner ondervroeg in augustus en september 2025 645 B2B-kopers. Ze gebruikten gemiddeld zeven informatiebronnen bij een recente aankoop; 45% gebruikte generatieve AI, vooral om leveranciers en producten te onderzoeken (" + a("Gartner", U["gartner_buyers"]) + ")."),
        p("Ze doen het liefst alleen. In een ander onderzoek van Gartner gaf 67% van de B2B-kopers de voorkeur aan een aankoop zonder verkoper (" + a("Gartner", U["gartner_repfree"]) + ")."),
        p("Maar ze controleren wat AI zegt. TrustRadius zag dat 63% van de kopers van technologie AI gebruikte tijdens de aankoop, en 94% van hen controleert de antwoorden in elk geval een deel van de tijd (" + a("TrustRadius, 2026", U["trustradius"]) + ")."),
        h2("Hoe ziet een B2B-aankoop met AI eruit?"),
        cap("Een B2B-aankoop met AI is onderzoek via een assistent, controle via vakgenoten en reviews, en een salesgesprek dat begint met een kant-en-klare shortlist."),
        table(["Fase", "Wat kopers doen", "Wat marketing moet leveren"], [
            ["Probleem onderzoeken", "AI het probleem en de opties laten uitleggen", "Heldere uitleg en definities"],
            ["Shortlist maken", "AI vragen welke leveranciers bij hun situatie passen", "Zichtbaarheid op lijstjes, in reviews en vergelijkingen"],
            ["Controleren", "Reviews, vakgenoten en sites van leveranciers bekijken", "Reviews, cases en duidelijke prijzen"],
            ["Salesgesprek", "Verkopers vragen te bevestigen wat AI zei", "Een salesteam dat weet hoe AI je beschrijft"],
        ], "Vier fasen, elk met een eigen taak voor content."),
        h2("Waarom willen kopers nog steeds een verkoper spreken?"),
        cap("De belangrijkste reden om een verkoper te spreken is controle: de helft verwacht misleidende informatie uit generatieve AI, dus checken kopers feiten voor ze tekenen."),
        p("Gartner zag dat 51% van de kopers meer misleidende informatie uit generatieve AI verwacht, en 49% uit verkopers. Verkopers tellen het zwaarst als kopers een zakelijk probleem onderzoeken, een voorkeursleverancier kiezen, intern steun zoeken en de aankoop afronden."),
        h2("Wat moet B2B-marketing anders doen?"),
        cap("Marketing voor kopers die AI gebruiken betekent je feiten makkelijk vindbaar maken voor assistenten, makkelijk te controleren voor kopers en makkelijk te bevestigen voor verkopers."),
        ol([
            "<strong>Kijk hoe AI je beschrijft</strong> bij de vragen die kopers in elke fase stellen.",
            "<strong>Publiceer feiten die te controleren zijn:</strong> hoe de prijs werkt, koppelingen, certificeringen en klanten.",
            "<strong>Investeer in reviews.</strong> TrustRadius zag dat 74% van de kopers ze gebruikt bij een aankoopbeslissing.",
            "<strong>Vertel je salesteam</strong> wat AI over jou en je concurrenten zegt.",
            "<strong>Vraag elke nieuwe lead welke AI-tools hij gebruikte</strong> en leg dat vast in je CRM.",
            "<strong>Doe het in de taal van je kopers.</strong> Bij Nederlandstalige vragen kwam 62,2% van de citaties in ChatGPT uit Nederlandstalige bronnen (" + a("Temso, 2026", U["temso"]) + ").",
        ]),
        p("Welke bedrijven AI nu noemt bij koopvragen in jouw sector, zie je in de " + INDEX + ". Daar stellen we elke maand dezelfde vragen aan vier assistenten."),
    ] + faq([
        ("Hoeveel B2B-kopers gebruiken AI?", "45% in het onderzoek van Gartner onder 645 kopers en 63% in het onderzoek van TrustRadius onder 1.862 kopers van technologie."),
        ("Vertrouwen kopers AI-antwoorden?", "Gedeeltelijk. 94% van de AI-gebruikers in het onderzoek van TrustRadius controleert de antwoorden in elk geval soms."),
        ("Willen kopers nog met sales praten?", "67% heeft liever een aankoop zonder verkoper, maar 69% wil dat een verkoper inzichten uit AI bevestigt."),
        ("Welke bronnen vertrouwen kopers het meest?", "TrustRadius noemt demo's, gratis proefperiodes, eerdere ervaring en reviews van gebruikers als de bronnen met de meeste invloed."),
        ("Wat moet een salesteam veranderen?", "Reken op kopers die binnenkomen met een shortlist en beweringen uit AI. Wees klaar om die met bewijs te bevestigen of te verbeteren."),
    ]) + [
        cta("Wil je zien hoe je merk eruitziet in het AI-onderzoek van je kopers? " + QS + ". Bekijk ook ons " + GEO + " of lees " + a("hoe ChatGPT search bronnen kiest", blog("hoe-chatgpt-search-bronnen-kiest-b2b")) + "."),
    ],
)

# ------------------------------------------------------------------ schema
POSTS["schema-markup-b2b-ai-zoeken"] = dict(
    en="schema-markup-b2b-ai-search",
    title="Schema markup voor B2B en AI-zoeken: welke types je echt nodig hebt",
    excerpt="Google gebruikt gestructureerde data om pagina's te begrijpen en Microsoft zegt dat schema Copilot helpt. Dit zijn de types die een B2B-site nodig heeft.",
    kw="schema markup B2B",
    blocks=[
        tldr("Gestructureerde data helpt machines je pagina's te begrijpen: Google zegt dat het gestructureerde data gebruikt om de inhoud van een pagina te begrijpen (" + a("Google Search Central", U["google_sd"]) + "), en Microsoft zei dat schema markup zijn taalmodellen helpt, ook Copilot (" + a("Search Engine Land, 2025", U["sel_schema"]) + "). Een B2B-site heeft een kleine, kloppende set nodig, in de HTML die de server stuurt."),
        p("Google raadt JSON-LD aan voor gestructureerde data waar je site dat toelaat. De documentatie noemt ook resultaten van sites die markup toevoegden: Rotten Tomatoes mat een 25% hogere doorklikratio op 100.000 pagina's met gestructureerde data, en Nestlé een 82% hogere doorklikratio voor pagina's met rich results (" + a("Google Search Central", U["google_sd"]) + ")."),
        p("Fabrice Canel van Microsoft bevestigde op SMX München in 2025 dat schema markup de taalmodellen van Microsoft helpt content te begrijpen (" + a("Search Engine Land", U["sel_schema"]) + ")."),
        p("Waar je het toevoegt, telt. In de " + a("analyse van Vercel", U["vercel"]) + " voerde geen van de grote AI-crawlers JavaScript uit, dus markup die een script toevoegt, wordt misschien nooit gezien."),
        h2("Wat is schema markup?"),
        cap("Schema markup is gestructureerde data, meestal in JSON-LD, die aangeeft waar een pagina over gaat: een organisatie, artikel, persoon, product of dienst."),
        p("Bezoekers zien er niets van. Het vertelt crawlers in een vaste woordenlijst van Schema.org welke feiten op de pagina wat betekenen."),
        p("In Nederland is hier veel te winnen. In onze " + a("scan van 400 B2B-websites", REPORT) + " van 25 november 2026 had maar 45% van de Nederlandse homepages Organization-schema, en 36% had sameAs-links naar profielen als LinkedIn, Wikidata en de KvK."),
        h2("Welke schema-types heeft een B2B-website nodig?"),
        cap("De basisset schema voor een B2B-website is Organization, Article of BlogPosting, Person, Product of Service, en BreadcrumbList voor de navigatie."),
        table(["Schema-type", "Waar je het gebruikt", "Wat het machines vertelt"], [
            ["Organization", "Homepage, over-ons-pagina", "Bedrijfsnaam, logo, URL, contact, sociale profielen"],
            ["Article of BlogPosting", "Blogs en gidsen", "Kop, auteur, datum van publicatie en update"],
            ["Person", "Auteurs- en teampagina's", "Wie het schreef en welke rol die persoon heeft"],
            ["Product of SoftwareApplication", "Productpagina's", "Productnaam, categorie, aanbod"],
            ["Service", "Dienstpagina's", "Wat je aanbiedt en waar"],
            ["BreadcrumbList", "Alle pagina's", "Waar de pagina in de site staat"],
        ], "Een praktische startset voor B2B-sites."),
        h2("Hoe voeg je schema toe zonder iets kapot te maken?"),
        cap("Veilig schema toevoegen betekent het maken uit de echte pagina-inhoud, het in de HTML van de server zetten en het testen voor en na livegang."),
        ol([
            "<strong>Begin met Organization op de homepage</strong>, met overal dezelfde naam, hetzelfde logo en dezelfde profielen.",
            "<strong>Voeg Article en Person toe aan blogs</strong>, met echte auteurs en kloppende datums.",
            "<strong>Maak de markup op de server</strong>, vanuit je CMS of thema, niet met een tagmanager.",
            "<strong>Markeer alleen wat zichtbaar is</strong> op de pagina.",
            "<strong>Test met de Schema Markup Validator</strong> en de Rich Results Test van Google.",
            "<strong>Controleer opnieuw</strong> na elk nieuw ontwerp of elke wijziging in plugins.",
        ]),
    ] + faq([
        ("Garandeert schema AI-citaties?", "Nee. Het helpt machines pagina's te begrijpen; geciteerd worden hangt nog steeds af van inhoud, toegang en autoriteit."),
        ("Welk formaat moeten we gebruiken?", "JSON-LD, dat Google aanraadt waar je opzet dat toelaat."),
        ("Kan een plugin schema regelen?", "Ja, SEO-plugins als Yoast maken de basistypes. Controleer wat eruit komt en voorkom dat twee plugins tegenstrijdige markup schrijven."),
        ("Moeten we FAQ-markup toevoegen?", "Markeer echte vragen en antwoorden die op de pagina staan. Voeg geen markup toe voor content die er niet is."),
        ("Werkt Google Tag Manager voor schema?", "Markup die JavaScript toevoegt, kan worden gemist door AI-crawlers die geen scripts uitvoeren. Op de server is veiliger."),
    ]) + [
        cta("Wil je weten of je schema helpt of juist schaadt? " + QS + ". Bekijk ook ons " + GEO + " of lees de " + a("checklist voor AI-crawlers", blog("ai-crawler-checklist-b2b-website")) + "."),
    ],
)

# ------------------------------------------------------------------ publish pricing
POSTS["prijzen-publiceren-ai-zoeken-b2b"] = dict(
    en="publish-pricing-ai-search-b2b",
    title="Prijzen publiceren voor AI-zoeken: waarom B2B-bedrijven prijzen moeten tonen",
    excerpt="Kopers willen al sinds 2023 vooral duidelijke prijzen, en AI beantwoordt prijsvragen toch wel. Zo toon je prijzen zonder je onderhandelingsruimte weg te geven.",
    kw="prijzen publiceren B2B",
    blocks=[
        tldr("Kopers willen prijzen zien en AI-assistenten beantwoorden prijsvragen toch wel: duidelijke prijzen staan sinds 2023 bovenaan het verlanglijstje van kopers (" + a("TrustRadius, 2026", U["trustradius"]) + "). Publiceer je geen prijs of prijsmodel, dan beschrijft een assistent je met de cijfers van een ander."),
        p("Duidelijkheid over prijzen is de meest gevraagde verandering in B2B-inkoop. In het onderzoek van TrustRadius van januari 2025 onder 2.058 kopers van technologie noemde 45% het de belangrijkste verandering die ze van leveranciers willen (" + a("eMarketer over data van TrustRadius", U["emarketer"]) + ")."),
        p("Het staat sinds 2023 bovenaan de lijst, volgens het rapport van TrustRadius uit 2026 onder 1.862 kopers (" + a("TrustRadius", U["trustradius"]) + ")."),
        p("Kopers willen het werk zelf doen: " + a("Gartner", U["gartner_buyers"]) + " zag dat 70% de voorkeur geeft aan een volledig digitale aankoop in eigen regie."),
        h2("Wat verandert AI-zoeken aan prijzen?"),
        cap("De verandering bij prijzen is dat kopers een assistent nu vragen \"wat kost X?\" en een antwoord krijgen, of je een prijs publiceerde of niet."),
        p("Staat er geen bron op je eigen site, dan leunt de assistent op reviews, oude artikelen of pagina's van concurrenten. Zo komen verouderde of foute prijzen in een shortlist terecht."),
        h2("Welke prijsinformatie moet je publiceren?"),
        cap("Publiceerbare prijsinformatie is minimaal je prijsmodel en een vanafprijs, ook als de uiteindelijke offerte afhangt van omvang, gebruikers of volume."),
        table(["Optie", "Wat je publiceert", "Past als"], [
            ["Volledige prijslijst", "Pakketten, prijzen, limieten", "Software in eigen regie, standaarddiensten"],
            ["Vanafprijzen", "Vanafprijzen per pakket of dienst", "De meeste B2B-diensten en software voor het middensegment"],
            ["Alleen het prijsmodel", "Hoe je rekent: per gebruiker, per project, per maand", "Complexe deals met grote bedrijven"],
            ["Bandbreedtes met voorbeelden", "Gangbare projectgroottes met een bandbreedte", "Advies- en bureauwerk"],
        ], "Vier manieren om duidelijk te zijn zonder je onderhandelingsruimte weg te geven."),
        p("Wij doen het zelf ook: op de pagina van ons " + GEO + " staan de sprint en de maandelijkse begeleiding met prijs. En op " + a("Wat kost GEO?", "/nl/wat-kost-geo/") + " zetten we de openbare prijzen van Nederlandse GEO-bureaus naast elkaar."),
        h2("Hoe publiceer je prijzen zonder deals te verliezen?"),
        cap("Veilig prijzen publiceren betekent uitleggen wat de prijs bepaalt, een vanafprijs tonen en het makkelijk maken om een offerte aan te vragen."),
        ol([
            "<strong>Noem je prijsmodel in één zin</strong> bovenaan de pagina.",
            "<strong>Toon een vanafprijs of gangbare bandbreedte</strong> per pakket of dienst.",
            "<strong>Zet erbij wat de prijs bepaalt:</strong> gebruikers, volume, markten, implementatie.",
            "<strong>Leg uit wat erbij zit</strong> en wat extra kost.",
            "<strong>Zet een datum op de pagina</strong> en werk hem bij bij elke wijziging.",
            "<strong>Geef een duidelijke volgende stap:</strong> een rekenhulp, een offerteaanvraag of een gesprek.",
        ]),
    ] + faq([
        ("Gaan concurrenten onze prijzen tegen ons gebruiken?", "Ze vinden ze toch wel, via kopers. Als je duidelijk bent over wat erbij zit, concurreer je op waarde in plaats van op een verborgen getal."),
        ("Wat als elke deal maatwerk is?", "Publiceer het prijsmodel en gangbare bandbreedtes. Kopers willen vooral weten of je in hun budget past."),
        ("Helpt een prijspagina bij AI-zichtbaarheid?", "Het geeft assistenten een eigen bron voor prijsvragen, in plaats van gokken van anderen."),
        ("Hoe vaak moet een prijspagina worden bijgewerkt?", "Bij elke wijziging, plus een controle per kwartaal. Verouderde prijzen doen meer schade dan geen prijzen."),
        ("Kiezen kopers echt op prijs voor hun shortlist?", "TrustRadius zag dat 83% van de kopers drie of minder producten op de shortlist zet. Met zo'n korte lijst kan een onduidelijke prijs je die plek kosten."),
    ]) + [
        cta("Weet je niet hoe je prijzen toont bij complexe deals? " + QS + ". Of probeer de " + a("ROI Forecaster", "/nl/roi-forecaster/") + "."),
    ],
)

# ------------------------------------------------------------------ 30-day sprint
POSTS["ai-zichtbaarheid-sprint-30-dagen"] = dict(
    en="30-day-ai-visibility-sprint",
    title="Wat gebeurt er in een AI-zichtbaarheidssprint van 30 dagen? Een plan per week",
    excerpt="Een sprint van 30 dagen meet waar ChatGPT, Claude, Gemini en Perplexity je merk noemen, lost de blokkades op en zet een meting neer. Het plan per week.",
    kw="AI-zichtbaarheid sprint",
    blocks=[
        tldr("AI Overviews verlaagden de doorklikratio van het eerste resultaat in Google met 58% (" + a("Ahrefs", U["ahrefs_ctr"]) + ", data van december 2025). Een sprint van 30 dagen geeft B2B-teams een nulmeting, de eerste verbeteringen en een vaste meting, voordat ze aan maandelijkse begeleiding vastzitten."),
        p("B2B-kopers krijgen steeds vaker hun antwoord zonder klik. Het " + a("Pew Research Center", U["pew"]) + " zag dat 18% van de gevolgde zoekopdrachten in Google in maart 2025 een AI-samenvatting toonde. Bij een samenvatting eindigde 8% van de bezoeken met een klik op een resultaat, zonder samenvatting 15%."),
        p("Het effect op posities is meetbaar. " + a("Ahrefs", U["ahrefs_ctr"]) + " vergeleek 300.000 zoekwoorden en zag dat een AI Overview de doorklikratio van het eerste resultaat in december 2025 met 58% verlaagde. Tegelijk gebruikt volgens " + a("Forrester", U["forrester"]) + " 94% van de zakelijke kopers AI in het aankoopproces."),
        p("De meeste marketingteams weten dit. Weinig teams weten waar hun merk in de antwoorden staat. Een sprint lost dat in een maand op."),
        h2("Wat is een AI-zichtbaarheidssprint?"),
        cap("Een AI-zichtbaarheidssprint is een vast programma van 30 dagen dat meet waar AI-assistenten je merk noemen, de blokkades oplost en een maandelijkse meting neerzet."),
        p("De omvang en de einddatum liggen vast. Je krijgt een nulmeting die je kunt herhalen, een lijst verbeteringen met eigenaren, de eerste verbeteringen live en een meting die je houdt."),
        h2("Waarom beginnen met een sprint in plaats van maandelijkse begeleiding?"),
        cap("Beginnen met een sprint betekent betalen voor een nulmeting en de eerste verbeteringen, voordat je budget vastlegt voor maanden werk die je niet kunt beoordelen."),
        p("Maandelijkse GEO-begeleiding is makkelijk te verkopen en lastig te beoordelen. Een sprint geeft harde cijfers op je eigen koopvragen. Daarna kies je met data, niet op beloftes, voor begeleiding, een eigen plan of allebei."),
        h2("Wat gebeurt er in elke week van de sprint?"),
        cap("Een sprintweek is een blok met één taak: meten, toegang en opbouw regelen, de belangrijkste pagina's herschrijven en daarna autoriteit en rapportage."),
        table(["Week", "Focus", "Wat je krijgt"], [
            ["Week 1", "Nulmeting", "Minstens 20 koopvragen in ChatGPT, Claude, Gemini en Perplexity, meerdere keren per vraag; voor meerdere markten 50 tot 150 vragen. Je ziet hoe vaak je genoemd wordt naast drie concurrenten en welke bronnen elke assistent citeert"],
            ["Week 2", "Toegang en opbouw", "Check van de HTML, robots.txt en firewall; schema op de belangrijkste pagina's; verbeteringen live of klaar voor je developer"],
            ["Week 3", "Pagina's die antwoord geven", "Drie tot vijf belangrijke pagina's herschreven, met het antwoord bovenaan, vergelijkingstabellen en een FAQ"],
            ["Week 4", "Autoriteit en rapportage", "Tweede meting op dezelfde vragen, een lijst sites en lijstjes die assistenten in jouw categorie citeren, een plan om daar te komen en een maandelijkse meting"],
        ], "De vier sprintweken en wat ze opleveren."),
        h2("Welke koopvragen moet een B2B-bedrijf volgen?"),
        cap("Een set B2B-koopvragen is een lijst echte vragen van kopers in drie fasen: vragen over het probleem, vergelijkingen van oplossingen en shortlists van leveranciers."),
        ol([
            "<strong>Probleem:</strong> \"Waarom levert organisch zoeken ons steeds minder pipeline op?\"",
            "<strong>Oplossing:</strong> \"Wat is het verschil tussen GEO en SEO voor B2B-bedrijven?\"",
            "<strong>Shortlist:</strong> \"Beste aanbieders van security awareness-trainingen voor Nederlandse financiële dienstverleners\"",
            "<strong>Taal:</strong> schrijf de vragen in elke taal die je kopers gebruiken, dus voor Nederland ook in het Nederlands.",
            "<strong>Copilot:</strong> neem ook Microsoft Copilot mee. Forrester zag dat zakelijke kopers het vier keer zo vaak gebruiken als consumenten.",
        ]),
        p("Kijk daarna welke sites de assistenten citeren. In een " + a("onderzoek van Ahrefs naar 75.000 merken", U["ahrefs_brand"]) + " hadden merkvermeldingen op het web een correlatie van 0,664 met zichtbaarheid in AI Overviews, tegen 0,218 voor backlinks. Daarom gaat week vier naar vermeldingen, niet alleen naar links."),
        h2("Hoe meet je het resultaat na 30 dagen?"),
        cap("Een sprint meten betekent de nulmeting van week één vergelijken met dezelfde vragen in week vier, plus crawlertoegang, zoekopdrachten op je merk en salesfeedback."),
        p("Wees realistisch over de tijd. Assistenten die live op het web zoeken, kunnen verbeterde pagina's binnen weken oppikken. Wat modellen uit hun training onthouden, verandert langzamer. Beoordeel de sprint dus op wat er live staat en op de eerste beweging in antwoorden die op zoeken leunen, en meet daarna elke maand."),
        p("Voeg één vraag toe aan je formulieren en salesgesprekken: \"Hoe heb je ons gevonden?\", met ChatGPT, Perplexity en Copilot als keuze. Zo zie je leads die nooit klikten."),
    ] + faq([
        ("Wat kost een sprint van 30 dagen?", "Bij ons kost de AI Visibility Sprint €3.950 eenmalig, excl. btw, voor één markt en 20 koopvragen. Voor meer landen of vragen maken we een voorstel op maat. Prijzen van andere bureaus vind je op " + PRICES),
        ("Hebben we developers nodig voor de sprint?", "Meestal een paar uur voor technische verbeteringen. We briefen je developer, of voeren het zelf door als je site op WordPress draait."),
        ("Welke tools gebruik je voor de meting?", "Een vaste set koopvragen die op een vast moment in de grote AI-assistenten wordt gesteld. Google Search Console en Bing Webmaster Tools dekken de zoekkant."),
        ("Kunnen we zelf een sprint doen?", "Ja, met het plan hierboven. Het moeilijke deel is vragen kiezen die echt bij je kopers passen, en vermeldingen op andere sites verdienen."),
        ("Wat gebeurt er na de sprint?", "Je houdt de vragen, de data en de meting. Veel teams gaan door met maandelijkse begeleiding voor content en autoriteit; anderen nemen het zelf over."),
    ]) + [
        cta("Wil je dit plan voor je eigen merk? Bekijk ons " + GEO + ", of " + QS.replace("Vraag", "vraag") + " en we bepalen samen de omvang. Lees ook " + a("hoe je een GEO-bureau kiest", blog("geo-bureau-kiezen-10-vragen")) + " en de " + a("checklist voor AI-crawlers", blog("ai-crawler-checklist-b2b-website")) + "."),
    ],
)

# ------------------------------------------------------------------ GEO pricing models
POSTS["geo-prijsmodellen-budget-b2b"] = dict(
    en="geo-pricing-what-generative-engine-optimization-costs",
    title="GEO-prijzen: welke prijsmodellen bureaus gebruiken en wat je moet budgetteren",
    excerpt="De mediane GEO-retainer was $6.850 per maand in 1.146 offertes. Zo ontstaat de prijs, welke prijsmodellen er zijn en wat een goed voorstel bevat.",
    kw="GEO-prijzen",
    blocks=[
        tldr("De mediane maandprijs voor GEO of AEO was $6.850 in het " + a("onderzoek van First Page Sage van oktober 2026", U["fps"]) + " naar 1.146 offertes. De prijs volgt de omvang: markten, talen, het aantal gevolgde vragen en hoeveel uitvoering en outreach het bureau zelf doet."),
        p("GEO-budgetten bewegen snel. First Page Sage verzamelde tussen juli en oktober 2026 1.146 offertes van 214 bureaus. De mediane GEO-retainer van $6.850 per maand ligt ruim boven de mediane SEO-retainer van $4.740, en steeg van $4.120 voor GEO begin 2024 (" + a("First Page Sage", U["fps"]) + "). First Page Sage is zelf een GEO-bureau, dus zie deze cijfers als marktreferentie."),
        p("In Europa kan het lager beginnen. Het Nederlandse bureau Searchlab noemt AI-zichtbaarheid vanaf €1.500 per maand, maandelijks opzegbaar (" + a("Searchlab", U["searchlab"]) + "). Tools zijn de kleinste post: " + a("Peec AI", U["peec"]) + " rekent $95 per maand voor 50 gevolgde vragen en $495 voor 350."),
        p("Waarom kopers er geld voor over hebben: volgens " + a("Forrester", U["forrester"]) + " gebruikt 94% van de zakelijke kopers AI in het aankoopproces. Dit artikel laat zien waar je voor betaalt en hoe je voorstellen vergelijkt. De openbare prijzen van Nederlandse bureaus staan naast elkaar op " + PRICES),
        h2("Waar is de prijs van GEO op gebaseerd?"),
        cap("De prijs van GEO is het bedrag voor meten, verbeteren en laten groeien hoe AI-assistenten je merk noemen, vooral bepaald door omvang, markten en uitvoering."),
        p("Zes dingen bepalen de prijs. De meeste voorstellen bundelen ze, dus vraag elke regel apart op."),
        ol([
            "<strong>Gevolgde vragen en assistenten.</strong> 50 vragen in drie assistenten kost veel minder dan 300 vragen in vijf.",
            "<strong>Markten en talen.</strong> Elke taal heeft een eigen set vragen, eigen pagina's en eigen bronnen nodig.",
            "<strong>Uitvoering.</strong> Een bureau dat zelf je pagina's en schema aanpast, kost meer dan een bureau dat adviezen naar je developers stuurt.",
            "<strong>Content maken.</strong> Nieuwe antwoord- en vergelijkingspagina's vragen schrijvers en experts uit het vak.",
            "<strong>Vermeldingen op andere sites.</strong> Digitale PR, plekken op lijstjes en reviewprogramma's zijn vaak de duurste post, en vaak de meest effectieve.",
            "<strong>Rapportage en tools.</strong> Licenties en maandrapporten zitten meestal in de prijs, maar check of je toegang tot de data houdt.",
        ]),
        h2("Welke prijsmodellen gebruiken GEO-bureaus?"),
        cap("Een GEO-prijsmodel is de manier waarop een bureau factureert: een maandbedrag, een vaste sprint, een losse audit, advies per uur of betaling per resultaat."),
        table(["Prijsmodel", "Aandeel bureaus", "Gangbare bandbreedte", "Mediaan"], [
            ["Maandbedrag op maat", "41%", "$3.000 tot $15.000 per maand", "$7.200 per maand"],
            ["Maandbedrag in pakketten", "27%", "$2.500 tot $25.000 per maand", "$6.300 per maand"],
            ["Project of sprint", "13%", "$5.000 tot $40.000 per project", "$14.500"],
            ["Losse audit", "8%", "$1.500 tot $12.000", "$4.300"],
            ["Advies per uur", "6%", "$125 tot $325 per uur", "$205 per uur"],
            ["Op basis van resultaat", "5%", "$1.500 tot $4.000 basis per maand plus een vergoeding per resultaat", "$2.600 basis"],
        ], "Bron: " + a("First Page Sage, GEO and AEO Pricing Statistics 2026", U["fps"]) + ", 1.146 offertes van 214 bureaus."),
        h2("Hoeveel moet een B2B-bedrijf budgetteren?"),
        cap("Een haalbaar GEO-budget is het maandbedrag voor jouw bedrijfsgrootte plus tools, content en de interne uren om wijzigingen goed te keuren en te publiceren."),
        p("Per klantgrootte vond First Page Sage medianen van $2.950 per maand voor kleine bedrijven, $7.400 voor het middensegment, $14.600 voor het hogere middensegment en $31.500 voor grote ondernemingen. Financiële dienstverlening had de hoogste mediaan per sector: $9.850 per maand."),
        p("Tel drie kosten op die zelden in een voorstel staan. Licenties voor tools, als je zelf toegang wilt. Tijd van schrijvers of experts voor nieuwe pagina's. En interne tijd: iemand in je team moet wijzigingen goedkeuren, anders gaat er niets live. Reken daar een paar uur per week voor."),
        h2("Wat hoort er in een GEO-voorstel?"),
        cap("Een volledig GEO-voorstel is een voorstel dat de vragen, assistenten, maandelijkse opleveringen, uitvoerders en de rapportage noemt die je na het contract houdt."),
        ul([
            "De set vragen, of hoe die in week één wordt gemaakt, in elke taal die je kopers gebruiken.",
            "De assistenten die worden gemeten: ChatGPT, Perplexity, Gemini, Claude, Microsoft Copilot en AI Overviews van Google.",
            "Benoemde opleveringen per maand, zoals herschreven pagina's, verdiende vermeldingen en rapporten.",
            "Wie uitvoert: het bureau, je developers of allebei, met uren.",
            "Eigendom van de data: vragen, ruwe antwoorden en dashboards blijven van jou.",
        ]),
        p("Pas op met voorstellen die rekenen per aantal AI-artikelen. Het spambeleid van Google noemt het massaal maken van pagina's met generatieve AI zonder toegevoegde waarde scaled content abuse (" + a("Google Search Central", U["google_spam"]) + ")."),
        h2("Is een sprint goedkoper dan maandelijkse begeleiding?"),
        cap("Een GEO-sprint is een project met vaste omvang, meestal 30 dagen, dat een nulmeting en de eerste verbeteringen oplevert voordat je aan een maandbedrag vastzit."),
        p("Per maand is een sprint vaak duurder, omdat de eerste weken het opzetwerk dragen. In totaal is hij goedkoper dan begeleiding die je na drie maanden stopt omdat je hem niet kon beoordelen. Ons " + a("plan voor een sprint van 30 dagen", blog("ai-zichtbaarheid-sprint-30-dagen")) + " laat per week zien wat een sprint moet opleveren."),
    ] + faq([
        ("Waarom kost GEO meer dan SEO?", "GEO voegt werk toe dat zelden in een SEO-retainer zit: vragen stellen en analyseren in meerdere assistenten, en vermeldingen op andere sites verdienen. First Page Sage vond een mediaan van $6.850 per maand voor GEO tegen $4.740 voor SEO."),
        ("Kunnen we GEO alleen met tools doen?", "Tools laten zien waar je verschijnt. Ze herschrijven geen pagina's, openen geen toegang voor crawlers en verdienen geen vermeldingen. Veel teams beginnen met een tool en kopen hulp in voor de delen die ze zelf niet kunnen bemensen."),
        ("Zijn GEO-prijzen lager in Europa?", "Soms. Searchlab begint op €1.500 per maand voor AI-zichtbaarheid als losse dienst. Vergelijk de omvang, niet alleen de prijs."),
        ("Wat rekent Booming Venture?", "De AI Visibility Sprint kost €3.950 eenmalig, daarna kun je doorgaan met maandelijkse begeleiding vanaf €1.950 per maand, maandelijks opzegbaar. Alle bedragen zijn excl. btw. Zie ons " + GEO + "."),
        ("Hoe snel verdient GEO zich terug?", "Assistenten die live op het web zoeken, kunnen verbeterde pagina's binnen weken oppikken. Volg de pipeline met een veld \"Hoe heb je ons gevonden?\" waarin AI-assistenten staan, zodat je leads ziet die nooit klikten."),
    ]) + [
        cta("Vergelijk je GEO-voorstellen? " + QS + " en we lopen omvang en prijs samen door. Lees eerst " + a("hoe je een GEO-bureau kiest", blog("geo-bureau-kiezen-10-vragen")) + "."),
    ],
)

# ------------------------------------------------------------------ AI Overviews Europe
POSTS["ai-overzichten-europa-b2b"] = dict(
    en="ai-overviews-europe-b2b",
    title="AI-overzichten van Google in Europa: wat B2B-bedrijven in de Benelux moeten weten",
    excerpt="Google toont sinds mei 2025 AI-overzichten in het Nederlands. Waar ze verschijnen, verliest het eerste resultaat 58% van de kliks. Zo reageer je in B2B.",
    kw="AI-overzichten Google",
    blocks=[
        tldr("Google lanceerde AI-overzichten (AI Overviews) in mei 2025 in het Nederlands (" + a("Google Nederland", U["google_nl_aio"]) + "), na een uitrol in maart 2025 naar negen andere Europese landen (" + a("Google", U["google_eu_aio"]) + "). Waar ze verschijnen, verliest het eerste resultaat kliks: " + a("Ahrefs", U["ahrefs_ctr"]) + " mat een 58% lagere doorklikratio."),
        p("Europa kreeg AI-overzichten later dan de Verenigde Staten. In maart 2025 voegde Google Oostenrijk, België, Duitsland, Ierland, Italië, Polen, Portugal, Spanje en Zwitserland toe. België begon alleen in het Engels (" + a("Google", U["google_eu_aio"]) + ")."),
        p("Het Nederlands volgde op 20 mei 2025, aangekondigd op Google I/O (" + a("Google Nederland", U["google_nl_aio"]) + "). Voor B2B-bedrijven in de Benelux betekent dat: in veel markten waar ze verkopen, staat nu een AI-samenvatting boven de zoekresultaten."),
        p("Het effect op kliks is meetbaar. Het Pew Research Center zag dat 8% van de bezoeken een klik op een resultaat bevatte als Google een AI-samenvatting toonde, tegen 15% zonder (" + a("Pew Research Center", U["pew"]) + ")."),
        h2("Wat zijn AI-overzichten?"),
        cap("Een AI-overzicht is een door AI gemaakte samenvatting bovenaan de zoekresultaten van Google die de zoekvraag beantwoordt en naar een paar bronpagina's linkt."),
        p("Ze putten uit de index van Google. Pagina's die goed scoren en helder antwoord geven, hebben een betere kans op een link, maar veel gebruikers lezen de samenvatting en stoppen daar."),
        h2("Waar verschijnen AI-overzichten in Europa?"),
        cap("AI-overzichten zijn in Europa breed beschikbaar: in de meeste grote EU-markten, waaronder Nederland in het Nederlands, Duitsland in het Duits en België in het Engels."),
        table(["Markt", "Talen bij de lancering", "Lancering"], [
            ["Nederland", "Nederlands", "Mei 2025"],
            ["België", "Engels", "Maart 2025"],
            ["Duitsland", "Duits, Engels", "Maart 2025"],
            ["Oostenrijk", "Duits, Engels", "Maart 2025"],
            ["Zwitserland", "Frans, Duits, Italiaans, Engels", "Maart 2025"],
            ["Ierland", "Engels", "Maart 2025"],
        ], "Bronnen: aankondigingen van Google, maart en mei 2025."),
        h2("Hoe moeten B2B-bedrijven reageren?"),
        cap("Reageren op AI-overzichten betekent meten bij welke zoekvragen ze verschijnen, pagina's zo opbouwen dat jij de geciteerde bron bent en vraag opbouwen buiten kliks om."),
        ol([
            "<strong>Check je belangrijkste zoekvragen per markt</strong> op AI-overzichten, te beginnen met de commerciële.",
            "<strong>Begin pagina's met het antwoord</strong>, in twee zinnen onder een kop in vraagvorm.",
            "<strong>Versterk pagina's die al goed scoren</strong>, want AI-overzichten putten uit de index van Google.",
            "<strong>Volg zoekopdrachten op je merknaam</strong> per land in Search Console, als signaal van vraag.",
            "<strong>Verdien vermeldingen in lokale vakmedia</strong> in elke markt waar je verkoopt.",
            "<strong>Schrijf in de taal van de markt.</strong> Bij Nederlandstalige vragen kwam 62,2% van de citaties in ChatGPT uit Nederlandstalige bronnen (" + a("Temso, 2026", U["temso"]) + ").",
        ]),
    ] + faq([
        ("Verschijnen AI-overzichten bij B2B-zoekvragen?", "Ja, vooral bij informatieve en vergelijkende vragen. Check je eigen zoekvragen per markt."),
        ("Kunnen we ons afmelden voor AI-overzichten?", "Google biedt snippetinstellingen zoals nosnippet, maar die beperken ook hoe je pagina in de gewone resultaten verschijnt. Gebruik ze voorzichtig."),
        ("Verschijnen AI-overzichten in België in elke taal?", "België begon in maart 2025 met Engels. Check de actuele beschikbaarheid in het Nederlands en Frans voordat je plant."),
        ("Hoe zie je verkeer uit AI-overzichten?", "Search Console telt het mee als gewoon webverkeer; het staat niet apart. Volg de doorklikratio per zoekvraag."),
        ("Heeft dit invloed op betaald zoeken?", "Advertenties verschijnen nog steeds rond AI-overzichten. Let op de doorklikratio bij informatieve zoekwoorden en verschuif zo nodig budget naar commerciële."),
    ]) + [
        cta("Verkoop je in de Benelux en de DACH-landen? " + QS + " en we kijken per markt wat AI-overzichten met je zoekvragen doen. Bekijk ook ons " + GEO + "."),
    ],
)

# ------------------------------------------------------------------ Copilot and Bing
POSTS["microsoft-copilot-bing-b2b-ai-zichtbaarheid"] = dict(
    en="microsoft-copilot-bing-b2b-ai-visibility",
    title="Microsoft Copilot en Bing: zo word je zichtbaar bij B2B-kopers die Copilot gebruiken",
    excerpt="Zakelijke kopers gebruiken Copilot vier keer zo vaak als consumenten. Copilot zoekt via Bing, en Bing Webmaster Tools laat nu zien hoe vaak Copilot je citeert.",
    kw="Microsoft Copilot zichtbaarheid",
    blocks=[
        tldr(a("Forrester", U["forrester"]) + " zag dat zakelijke kopers Microsoft Copilot vier keer zo vaak gebruiken als consumenten (2026). Copilot stuurt zijn zoekvragen naar Bing, en sinds februari 2026 laat Bing Webmaster Tools zien hoe vaak Copilot je pagina's citeert."),
        p("Het meeste GEO-advies begint bij ChatGPT. Voor B2B mis je dan de assistent die in de software zit waar je kopers de hele dag in werken. Forrester zag ook dat 61% van de zakelijke kopers AI-tools gebruikt die hun eigen organisatie aanbiedt (" + a("Forrester", U["forrester"]) + ")."),
        p("De webantwoorden van Copilot draaien op Bing. Microsoft zegt dat Copilot uit je prompt een korte zoekvraag maakt en die naar Bing stuurt. Een knop Bronnen toont die zoekvraag en de gebruikte bronnen (" + a("Microsoft Support", U["ms_copilot"]) + ")."),
        p("En Microsoft deelt nu de data. Sinds 10 februari 2026 toont het rapport AI Performance in Bing Webmaster Tools citaties in Copilot, AI-samenvattingen in Bing en bij een aantal partners (" + a("Bing Webmaster Blog", U["bing_feb"]) + ")."),
        h2("Wat is zichtbaarheid in Copilot?"),
        cap("Zichtbaarheid in Copilot is hoe vaak Copilot je bedrijf citeert of noemt in antwoorden, wat grotendeels afhangt van hoe Bing je pagina's indexeert en rangschikt."),
        p("Bing heeft een eigen index. Een pagina die in Google scoort, staat niet automatisch in Bing of hoog in Bing. Copilot kan je dus missen terwijl Google je wel vindt."),
        h2("Waarom telt Copilot zwaarder in B2B dan bij consumenten?"),
        cap("Het gewicht van Copilot in B2B is dat het in Microsoft 365 zit, waar veel kopers werken, dus gebeurt onderzoek naast mail, documenten en Teams."),
        p("Een koper die in Word een vergelijking van leveranciers maakt, of een samenvatting voor een inkoopteam, kan Copilot vragen zonder een browser te openen. In organisaties die zoeken op het web toestaan in Copilot Chat, leunen die antwoorden op Bing."),
        h2("Hoe zie je je citaties in Copilot?"),
        cap("Je citaties in Copilot zien betekent het rapport AI Performance in Bing Webmaster Tools gebruiken, dat citaties, geciteerde pagina's en zoekvragen voor je site telt."),
        table(["Meting", "Wat het laat zien", "Toegevoegd"], [
            ["Total Citations", "Hoe vaak je site als bron in AI-antwoorden verschijnt", "Februari 2026"],
            ["Average Cited Pages", "Daggemiddelde van je unieke geciteerde pagina's", "Februari 2026"],
            ["Grounding queries", "Een steekproef van de zoekvragen waarmee AI je content ophaalde", "Februari 2026"],
            ["Intents", "Die zoekvragen gegroepeerd per intentie, zoals commercieel of onderzoek", "Juni 2026"],
            ["Topics", "Die zoekvragen gegroepeerd per thema", "Juni 2026"],
            ["Citation Share", "Jouw aandeel in alle citaties voor een zoekvraag", "Juni 2026"],
            ["Compare", "De huidige periode naast een eerdere", "Juni 2026"],
        ], "Bronnen: " + a("Bing, februari 2026", U["bing_feb"]) + " en " + a("Bing, juni 2026", U["bing_jun"]) + ". Microsoft zegt dat het rapport citaties toont, geen posities."),
        h2("Hoe verbeter je je zichtbaarheid in Copilot?"),
        cap("Zichtbaarheid in Copilot verbeteren betekent goed geïndexeerd en gerangschikt worden in Bing: geverifieerde site, actuele sitemap, IndexNow, leesbare HTML en pagina's die zakelijke vragen beantwoorden."),
        ol([
            "<strong>Verifieer je site in Bing Webmaster Tools.</strong> Je kunt hem in een paar klikken importeren uit Google Search Console.",
            "<strong>Dien je XML-sitemap in</strong> en controleer of de belangrijke pagina's geïndexeerd zijn.",
            "<strong>Zet IndexNow aan</strong>, zodat Bing direct hoort over nieuwe en bijgewerkte pagina's. Onze eigen site meldt elke publicatie zo.",
            "<strong>Controleer of Bingbot niet geblokkeerd wordt</strong> door robots.txt, een firewall of limieten bij je host.",
            "<strong>Lees je zoekvragen</strong> en koppel elke vraag aan de pagina die hem moet beantwoorden.",
            "<strong>Versterk de pagina's achter commerciële intenties:</strong> vergelijkingen, prijzen, koppelingen en cases.",
            "<strong>Meet elke maand met Compare</strong> en neem Copilot op in je vaste set koopvragen.",
        ]),
        p("Ons " + a("plan voor een sprint van 30 dagen", blog("ai-zichtbaarheid-sprint-30-dagen")) + " laat zien hoe je Copilot vanaf week één meeneemt."),
    ] + faq([
        ("Is Copilot hetzelfde als Bing Chat?", "Microsoft hernoemde Bing Chat eind 2023 naar Copilot. Nu zit Copilot in Windows, Edge, Bing en Microsoft 365."),
        ("Gebruikt Microsoft 365 Copilot Chat het web?", "Ja, als zoeken op het web aanstaat. Beheerders kunnen het uitzetten voor hun organisatie, en gebruikers in hun instellingen."),
        ("Toont het rapport AI Performance posities?", "Nee. Microsoft zegt dat het laat zien hoe vaak content geciteerd wordt, niet de positie, autoriteit of plek in een antwoord."),
        ("Neemt Bing een goede positie in Google over?", "Niet automatisch. Bing crawlt en rangschikt met eigen systemen, dus controleer de indexering in Bing Webmaster Tools apart."),
        ("Kunnen we zien hoe kopers Copilot privé gebruiken?", "Niet direct. Kopers die een antwoord van Copilot lezen en niet klikken, laten geen spoor achter op je site. Een veld \"Hoe heb je ons gevonden?\" met AI-assistenten als keuze is de praktische oplossing."),
    ]) + [
        cta("Weet je niet hoe Copilot je bedrijf beschrijft? " + QS + " en we kijken samen naar je zichtbaarheid in Bing en Copilot. Bekijk ook ons " + GEO + "."),
    ],
)

# ------------------------------------------------------------------ quotable passages
POSTS["passages-schrijven-die-ai-citeert"] = dict(
    en="write-passages-ai-assistants-quote",
    title="Zo schrijf je passages die AI-assistenten citeren: wat het GEO-onderzoek laat zien",
    excerpt="Citaten gaven 41% meer AI-zichtbaarheid, statistieken 31% en bronnen 28%. Zoekwoorden herhalen werkte averechts. Zo schrijf je citeerbare passages.",
    kw="passages voor AI",
    blocks=[
        tldr("In de " + a("GEO-studie", U["geo_paper"]) + " van Princeton en IIT Delhi verhoogden citaten de zichtbaarheid van een bron in generatieve antwoorden met zo'n 41%, statistieken met zo'n 31% en bronvermelding met zo'n 28%. Zoekwoorden herhalen verlaagde hem. Schrijf specifieke passages met een bron die elk één vraag beantwoorden."),
        p("De onderzoekers bouwden GEO-bench, een set van 10.000 zoekvragen, en testten hoe aanpassingen aan content de zichtbaarheid van een bron in antwoorden van generatieve zoekmachines veranderden. De studie werd geaccepteerd op KDD 2024 (" + a("arXiv", U["geo_abs"]) + ")."),
        p("Gemeten in woorden, gewogen naar positie, scoorde het toevoegen van citaten zo'n 41% boven de niet-geoptimaliseerde basis, statistieken zo'n 31%, bronnen noemen zo'n 28% en vloeiender schrijven ook zo'n 28%. Zoekwoorden herhalen scoorde zo'n 8% onder de basis (" + a("Aggarwal e.a.", U["geo_paper"]) + ")."),
        p("Assistenten citeren veel bronnen: ChatGPT noemt in 96% van de antwoorden bronnen, zo'n vijf per antwoord (" + a("Muck Rack, mei 2026", U["muckrack"]) + "). Elke bron is een passage die iemand schreef."),
        h2("Wat maakt een passage citeerbaar voor AI?"),
        cap("Een citeerbare passage is een kort blok dat op zichzelf staat en één vraag direct beantwoordt, met een specifiek feit, getal of citaat met naam."),
        p("Assistenten werken met passages, niet met hele pagina's. Een alinea die pas klopt als je drie andere hebt gelezen, is lastig te hergebruiken."),
        h2("Welke schrijftechnieken hadden het meeste effect?"),
        cap("De meest effectieve GEO-techniek is citaten toevoegen, gevolgd door statistieken en bronvermelding, terwijl zoekwoorden herhalen in de studie een negatief effect had."),
        table(["Techniek", "Verandering in zichtbaarheid tegenover de basis", "Hoe het eruitziet in B2B-teksten"], [
            ["Citaten toevoegen", "Ongeveer +41%", "Een citaat van een expert of klant met naam en context"],
            ["Statistieken toevoegen", "Ongeveer +31%", "Een specifiek getal met bron en jaar"],
            ["Bronnen noemen", "Ongeveer +28%", "Een link naar het oorspronkelijke onderzoek of document"],
            ["Vloeiender schrijven", "Ongeveer +28%", "Heldere, leesbare zinnen"],
            ["Zoekwoorden herhalen", "Ongeveer -8%", "De doelzin steeds herhalen; niet doen"],
        ], "Bron: GEO: Generative Engine Optimization, tabel 1, woorden gewogen naar positie."),
        h2("Hoe schrijf je passages die AI-assistenten citeren?"),
        cap("Citeerbare passages schrijven betekent één vraag per sectie, eerst het antwoord, dan een getal of citaat met bron, in gewone zinnen onder 25 woorden."),
        ol([
            "<strong>Gebruik een vraag als kop</strong>, geformuleerd zoals kopers hem stellen.",
            "<strong>Geef het antwoord in de eerste twee zinnen</strong>, zodat de passage op zichzelf staat.",
            "<strong>Voeg één specifiek getal toe</strong> met bron en jaar.",
            "<strong>Voeg een citaat toe</strong> van een expert, klant of document met naam, waar dat helpt.",
            "<strong>Link naar de oorspronkelijke bron</strong>, niet naar een samenvatting ervan.",
            "<strong>Schrap opvulling en herhaling.</strong> Zoekwoorden herhalen verlaagde de zichtbaarheid in de studie.",
        ]),
        p("Schrijf je voor Nederlandse kopers, doe het dan in het Nederlands. Bij Nederlandstalige vragen kwam 62,2% van de citaties in ChatGPT uit Nederlandstalige bronnen (" + a("Temso, 2026", U["temso"]) + ")."),
    ] + faq([
        ("Is schrijven voor GEO anders dan goed schrijven?", "Meestal niet. De technieken die werkten, citaten, getallen en bronnen, doen goede analisten al."),
        ("Hoe lang moet een passage zijn?", "Kort genoeg om op zichzelf te staan: een kop, een antwoord van twee zinnen en één onderbouwend feit."),
        ("Gelden de uitkomsten ook voor ChatGPT en Gemini?", "De studie testte generatieve zoekmachines in een onderzoeksopzet. De richting past bij wat onderzoek naar citaties laat zien, maar de precieze winst verschilt."),
        ("Moeten we in elke sectie een citaat zetten?", "Alleen waar een echt citaat bewijs toevoegt. Verzonnen of algemene citaten schaden het vertrouwen."),
        ("Schaadt zoekwoorden herhalen nog steeds?", "Het scoorde onder de basis in de GEO-studie, en het spambeleid van Google verbiedt het."),
    ]) + [
        cta("Wil je je belangrijkste pagina's laten herschrijven zodat AI ze citeert? " + QS + ". Bekijk ook ons " + GEO + " of lees " + a("welke bedrijfspagina's AI-assistenten citeren", blog("welke-bedrijfspaginas-ai-assistenten-citeren")) + "."),
    ],
)

# ------------------------------------------------------------------ board metrics
POSTS["ai-zichtbaarheid-cijfers-bestuur"] = dict(
    en="ai-visibility-metrics-for-the-board",
    title="AI-zichtbaarheid rapporteren aan het bestuur: vijf cijfers die tellen",
    excerpt="61% van de CMO's paste de contentstrategie aan voor AI, maar kliks blijven dalen. Rapporteer vijf cijfers: aandeel, citaties, juistheid, merkvraag en pipeline.",
    kw="AI-zichtbaarheid rapporteren",
    blocks=[
        tldr("61% van de CMO's heeft de contentstrategie aangepast voor AI-zichtbaarheid (" + a("Gartner, 2026", U["gartner_cmo"]) + "), maar kliks blijven dalen: " + a("Pew", U["pew"]) + " mat 8% doorklik als Google een AI-samenvatting toonde, tegen 15% zonder. Rapporteer vijf cijfers: aandeel in de antwoorden, aandeel in citaties, juistheid van antwoorden, merkvraag en pipeline uit AI."),
        p("Verkeer was ooit het makkelijke bewijs dat content werkte. AI-antwoorden breken die koppeling. In de " + a("data van Ahrefs van december 2025", U["ahrefs_ctr"]) + " verlaagde een AI Overview de doorklikratio van het eerste resultaat in Google met 58%."),
        p("Besturen willen toch een getal. Gartner ondervroeg in juli en augustus 2026 408 marketingleiders: 61% had content aangepast voor leesbaarheid door machines en AI-zichtbaarheid, en 42% volgt en verbetert foute informatie over hun merk in AI-antwoorden (" + a("Gartner", U["gartner_cmo"]) + ")."),
        p("Kopers zijn al verschoven: volgens " + a("Forrester", U["forrester"]) + " gebruikt 94% van de zakelijke kopers AI in het aankoopproces. Zo rapporteer je erover zonder ijdelheidscijfers."),
        h2("Wat is een cijfer voor AI-zichtbaarheid?"),
        cap("Een cijfer voor AI-zichtbaarheid is een herhaalbaar getal dat laat zien hoe AI-assistenten je merk presenteren, elke maand gemeten op een vaste set koopvragen."),
        p("Herhaalbaar is het sleutelwoord. AI-antwoorden verschillen per keer en per gebruiker, dus één screenshot bewijst niets. Een vaste set vragen, op een vast moment gesteld, geeft een trend."),
        h2("Welke vijf cijfers horen in een bestuursrapport?"),
        cap("Een bestuurswaardig cijfer is een cijfer dat aan omzet of risico gekoppeld is: aandeel, citaties, juistheid, merkvraag en pipeline uit AI."),
        table(["Cijfer", "Definitie", "Databron", "Waarom het bestuur het wil weten"], [
            ["Aandeel in AI-antwoorden", "Deel van de gevolgde vragen waarin je genoemd wordt, naast drie concurrenten", "Een tool voor AI-zichtbaarheid of een vaste eigen meting", "Plek op de shortlist van kopers"],
            ["Aandeel in citaties", "Jouw deel van alle citaties voor een zoekvraag", "Bing Webmaster Tools, rapport AI Performance", "Jouw pagina's als bron van de waarheid"],
            ["Juistheid van antwoorden", "Deel van de antwoorden dat product, prijs en positie goed beschrijft", "Maandelijkse controle van de gevolgde antwoorden", "Risico voor merk en verkoop"],
            ["Merkvraag", "Trend in vertoningen op je merknaam en directe bezoeken", "Google Search Console, Bing, analytics", "Bekendheid die AI-antwoorden opleveren"],
            ["Pipeline uit AI", "Pipeline van leads die een AI-assistent als bron noemen", "CRM met een veld voor de bron die de lead zelf opgeeft", "De koppeling met omzet"],
        ], "Vijf cijfers, elk met een databron die je kunt controleren."),
        h2("Hoe koppel je AI-zichtbaarheid aan pipeline?"),
        cap("AI-zichtbaarheid aan pipeline koppelen betekent een veld voor de opgegeven bron, kansen in je CRM en merkvraag combineren, omdat de meeste AI-antwoorden geen klik opleveren."),
        p("Zet \"Hoe heb je ons gevonden?\" op elk formulier en in elke salesintake, met ChatGPT, Perplexity, Copilot en Gemini als keuze. Label die kansen in het CRM. Rapporteer dan pipeline en winkans voor die groep naast het aandeel in AI-antwoorden. Met de " + a("ROI Forecaster", "/nl/roi-forecaster/") + " vertaal je de cijfers naar een businesscase."),
        h2("Wat moet een CMO niet rapporteren?"),
        cap("Een ijdelheidscijfer is een getal dat beweegt zonder dat kopers het merken, zoals vermeldingen bij irrelevante vragen of een score zonder de antwoorden erachter."),
        ol([
            "<strong>Aantallen vermeldingen</strong> bij vragen die geen koper stelt.",
            "<strong>Eén zichtbaarheidsscore uit een tool</strong>, zonder de antwoorden erachter.",
            "<strong>Screenshots van één goed antwoord.</strong>",
            "<strong>Alleen verkeer uit AI-verwijzingen.</strong> De meeste invloed van AI levert geen klik op.",
            "<strong>Een stijging zonder marge.</strong> Antwoorden verschillen per keer, dus toon hoeveel een cijfer kan schommelen, zoals we in de " + INDEX + " doen.",
        ]),
    ] + faq([
        ("Hoe vaak moeten we AI-zichtbaarheid rapporteren?", "Elke maand aan het marketingteam, elk kwartaal aan het bestuur. Maanddata laat trends zien; per kwartaal vlakt de ruis van wisselende antwoorden af."),
        ("Hoeveel vragen hebben we nodig?", "Meestal 50 tot 150 per markt, met vragen over het probleem, vergelijkingen van oplossingen en shortlistvragen, in elke taal die je kopers gebruiken. Voor een eerste nulmeting is 20 goed gekozen vragen genoeg."),
        ("Hoe gaan we om met antwoorden die per keer verschillen?", "Stel elke vraag meerdere keren per periode en rapporteer in welk deel van de keren je verschijnt. Trends over maanden tellen meer dan één keer."),
        ("Kunnen we ons vergelijken met concurrenten?", "Ja. Volg dezelfde vragen voor drie belangrijke concurrenten en rapporteer je aandeel als percentage van alle vermeldingen."),
        ("Wat is een goed aandeel in AI-antwoorden?", "Er is geen algemene norm. Vergelijk met je directe concurrenten en volg de richting in de tijd."),
    ]) + [
        cta("Wil je cijfers over AI-zichtbaarheid die je bestuur accepteert? " + QS + " en we zetten de vijf cijfers samen op. Bekijk ook ons " + GEO + " of lees over de " + a("sprint van 30 dagen", blog("ai-zichtbaarheid-sprint-30-dagen")) + "."),
    ],
)


# ------------------------------------------------------------------ build
LIVE_NL_POSTS = {
    "hoe-chatgpt-search-bronnen-kiest-b2b", "merkvermeldingen-vs-backlinks-ai-zichtbaarheid", "ai-zichtbaarheid-tools-vergeleken",
    "geo-bureau-kiezen-10-vragen", "best-of-lijsten-ai-assistenten", "vergelijkingspaginas-ai-assistenten", "foute-ai-antwoorden-over-je-bedrijf",
    "actuele-content-ai-zoekmachines-b2b", "persberichten-ai-citaties", "verdiende-media-ai-zichtbaarheid-b2b", "ai-verordening-b2b-marketingteams",
    "ai-content-google-spambeleid", "ai-vindbaarheid-400-b2b-websites-november-2026",
}
NL_PAGES = {
    "nl", "nl/over-ons", "nl/diensten", "nl/blog", "nl/unify-framework", "nl/funnel-calculator", "nl/roi-forecaster", "nl/gratis-groeigids",
    "nl/boardroom-quickscan", "nl/head-of-growth", "nl/b2b-geo-index",
    # new in 1.30.0 (pages_v2)
    "nl/diensten/geo", "nl/geo-bureau-rotterdam", "nl/cases", "nl/wat-kost-geo", "nl/ai-check", "nl/ai-zoeken-b2b-statistieken",
}


def internal_links_ok(content):
    bad = []
    for h in re.findall(r'href="(/[^"#?]*)', content):
        path = h.strip("/")
        if path.startswith("nl/blog/"):
            slug = path[len("nl/blog/"):]
            if slug not in LIVE_NL_POSTS and slug not in POSTS:
                bad.append(h)
        elif path not in NL_PAGES:
            bad.append(h)
    return bad


if __name__ == "__main__":
    ROOT = HERE.parents[1]
    FILE = ROOT / "wp-content/themes/booming-venture/import/nl/content.json"
    out, bad = [], 0
    for slug, post in POSTS.items():
        content = "\n".join(post["blocks"])
        r = nl.check(slug, post)
        links = internal_links_ok(content)
        if r["issues"] or links:
            bad += 1
        print(slug, r["words"], r["issues"], links)
        out.append({
            "en": post["en"], "slug": slug, "title": post["title"], "excerpt": post["excerpt"], "content": content,
            "meta": {"_yoast_wpseo_metadesc": post["excerpt"], "_yoast_wpseo_focuskw": post["kw"]},
        })
    if bad:
        raise SystemExit(f"{bad} posts with issues, content.json not written")
    data = json.loads(FILE.read_text(encoding="utf-8"))
    data["posts_nl_v2"] = out
    FILE.write_text(json.dumps(data, ensure_ascii=False), encoding="utf-8")
    print(f"{len(out)} Dutch posts written to posts_nl_v2")
