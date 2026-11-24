"""Dutch blog posts for the /nl/ section (localized, not machine-translated).

Each post is a Dutch version of an English post in the daily calendar
(schedule_daily.py), written for Dutch B2B buyers: plain Dutch, Dutch number
style (2,1% and 75.000), Dutch-language sources where they exist, and a
local angle where the data supports one (Temso 2026: Dutch prompts get
mostly Dutch-language sources). Every statistic comes from the same
verified source as the English post, or from a source listed below.

Rules (geo-writer guide, Dutch): question H2s with a 20-25 word answer
capsule that opens with "[X] is", "betekent" or "verwijst naar"; a
"Belangrijkste punten" box; 3+ named sources in the first 30%; a table or
ordered list; "Veelgestelde vragen"; an internal link to a Dutch page.

Run: python3 tools/blog/posts_nl.py -> writes tools/blog/out-nl/<slug>.html
and prints the checks.
"""
import importlib.util
import json
import pathlib
import re

HERE = pathlib.Path(__file__).resolve().parent
OUT = HERE / "out-nl"


def _load(name, fname):
    spec = importlib.util.spec_from_file_location(name, HERE / fname)
    m = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(m)
    return m


q4 = _load("q4", "posts_2026_q4.py")
_da = _load("da", "posts_daily_a.py")
_db = _load("db", "posts_daily_b.py")
_q1 = _load("q1", "posts_2027_q1.py")

SRC = q4.SRC
for _m in (_da, _db, _q1):  # each module loads its own copy of the shared source list
    SRC.update(getattr(_m, "SRC", None) or getattr(getattr(_m, "da", None), "SRC", {}) or {})
SRC.update({
    "temso_lang": "https://www.temso.ai/data/-Lost-in-Translation-How-AI-Models-Handle-Local-Language-Sources",
})

a, p, h2, h3, cap, ol, ul, table, cta, text_of = (
    q4.a, q4.p, q4.h2, q4.h3, q4.cap, q4.ol, q4.ul, q4.table, q4.cta, q4.text_of)


def tldr(text):
    return q4.tldr(text).replace(">Key takeaways<", ">Belangrijkste punten<")


def faq(pairs):
    out = [h2("Veelgestelde vragen")]
    for q, ans in pairs:
        out += [h3(q), p(ans)]
    return out


QS = a("Vraag een gratis Boardroom Quickscan van 15 minuten aan", "/nl/boardroom-quickscan/")
DIENSTEN = a("diensten", "/nl/diensten/")

POSTS = {}

# ------------------------------------------------------------------ ChatGPT search
POSTS["hoe-chatgpt-search-bronnen-kiest-b2b"] = dict(
    en="how-chatgpt-search-picks-sources-b2b",
    title="Hoe ChatGPT search bronnen kiest: een gids voor B2B-marketeers",
    excerpt="ChatGPT levert 81% van de AI-bezoeken aan B2B-sites. Zo vindt en citeert ChatGPT search pagina's, en dit pak je als B2B-team eerst aan.",
    kw="ChatGPT search",
    blocks=[
        tldr("ChatGPT had in februari 2026 900 miljoen wekelijkse gebruikers (" + a("TechCrunch", "techcrunch_900m") + ") en levert 81% van de bezoeken die B2B-websites uit AI-assistenten krijgen (" + a("Leadfeeder, 2026", "leadfeeder_ai") + "). Wil je geciteerd worden, dan moet OAI-SearchBot je pagina's kunnen lezen zonder JavaScript en moeten ze direct antwoord geven."),
        p("Voor de meeste B2B-bedrijven is ChatGPT de enige AI-assistent die echt verkeer stuurt. In het onderzoek van Leadfeeder uit oktober 2026 kwam 81% van de AI-bezoeken aan B2B-sites uit ChatGPT, tegen 8% uit Gemini en 5% uit Claude (" + a("Leadfeeder", "leadfeeder_ai") + ")."),
        p("Die bezoekers converteren ook. Orbit Media analyseerde 97 B2B- en leadgeneratiesites. Bezoekers uit ChatGPT werden in ongeveer 2,1% van de gevallen een lead, tegen 0,5% bij Google. Google stuurde wel zo'n 100 keer meer bezoekers (" + a("Orbit Media", "orbit_ai") + ")."),
        p("En het volume groeit. Demandbase mat tussen juni 2025 en juni 2026 een stijging van 303% in het aantal maandelijkse doorverwijzingen vanuit ChatGPT naar B2B-sites (" + a("PPC Land over data van Demandbase", "demandbase_ppc") + ")."),
        p("Schrijf je voor Nederlandse kopers, dan telt ook de taal van je pagina. Bij Nederlandstalige vragen kwam 62,2% van de citaties in ChatGPT uit Nederlandstalige bronnen, en in Google AI Overview zelfs 81,2% (" + a("Temso, 2026", "temso_lang") + ")."),
        h2("Wat is ChatGPT search?"),
        cap("ChatGPT search is de functie waarmee ChatGPT actuele webpagina's opzoekt en die als bron met een link in zijn antwoord noemt."),
        p("ChatGPT gebruikt daarvoor de eigen crawler van OpenAI. Volgens OpenAI verschijnen sites die OAI-SearchBot blokkeren niet in de antwoorden van ChatGPT search, al kunnen ze nog wel als navigatielink opduiken (" + a("OpenAI", "openai_bots") + ")."),
        h2("Hoe kiest ChatGPT welke pagina's het citeert?"),
        cap("De bronkeuze van ChatGPT betekent: pagina's die het kan crawlen, die goed bij de vraag passen en die actueel ogen. OpenAI publiceert geen rankingformule."),
        p("Actualiteit is een factor die je kunt meten. In een onderzoek van Ahrefs naar 17 miljoen citaties had ChatGPT de sterkste voorkeur voor nieuwe content van alle geteste assistenten: de geciteerde pagina's waren gemiddeld 458 dagen nieuwer dan de organische resultaten van Google (" + a("Ahrefs, 2025", "ahrefs_fresh") + ")."),
        p("Citaties zijn de regel. Muck Rack zag dat ChatGPT in 96% van de antwoorden bronnen noemt, gemiddeld zo'n vijf per antwoord, met Wikipedia als meest geciteerde domein (" + a("Muck Rack, mei 2026", "muckrack_may26") + ")."),
        h2("Wat moeten B2B-teams als eerste aanpakken?"),
        cap("De eerste stap voor zichtbaarheid in ChatGPT is toegang: laat OAI-SearchBot toe, lever volledige HTML en zorg dat je belangrijkste pagina's vragen van kopers beantwoorden."),
        ol([
            "<strong>Laat OAI-SearchBot toe</strong> in robots.txt en in de instellingen van je firewall of CDN.",
            "<strong>Beslis apart over GPTBot.</strong> Die verzamelt trainingsdata. Volgens OpenAI staat elke crawlerinstelling los van de andere.",
            "<strong>Lever volledige HTML.</strong> In de " + a("analyse van Vercel", "vercel") + " renderde geen van de grote AI-crawlers JavaScript.",
            "<strong>Beantwoord vragen van kopers op de pagina</strong>: een kop in vraagvorm, daarna het antwoord in twee zinnen.",
            "<strong>Werk je belangrijkste pagina's bij</strong> met nieuwe feiten en een zichtbare datum van de laatste update.",
            "<strong>Publiceer in het Nederlands voor Nederlandse kopers.</strong> Bij een Nederlandse vraag kiest ChatGPT meestal een Nederlandstalige bron.",
            "<strong>Zorg voor vermeldingen op de sites die ChatGPT al citeert</strong> in jouw categorie: reviewplatforms, vakmedia en lijstjes.",
        ]),
        h2("Hoe meet je zichtbaarheid in ChatGPT?"),
        cap("Zichtbaarheid in ChatGPT meten betekent twee cijfers volgen: hoe vaak je genoemd wordt bij vragen van kopers en hoeveel bezoeken en leads ChatGPT oplevert."),
        table(
            ["Meetpunt", "Waar je het vindt", "Waarom het telt"],
            [
                ["Hoe vaak je genoemd wordt bij vragen van kopers", "Een prompttracker zoals Peec AI of Otterly.ai", "Aanwezigheid op shortlists"],
                ["Bezoeken vanuit chatgpt.com", "Google Analytics 4", "Verkeer dat je kunt toewijzen"],
                ["Leads uit ChatGPT-bezoeken", "GA4 plus je CRM", "De koppeling met je pipeline"],
                ["Pagina's die geciteerd worden", "Bronnenlijsten in je prompttracker", "Welke pagina's je als voorbeeld neemt en verbetert"],
            ],
            "Vier cijfers om elke maand te volgen.",
        ),
    ] + faq([
        ("Haalt het blokkeren van GPTBot ons uit ChatGPT search?", "Nee. Volgens OpenAI staan de crawlerinstellingen los van elkaar. Je kunt OAI-SearchBot toelaten voor search en GPTBot blokkeren voor training."),
        ("Is verkeer uit ChatGPT de moeite waard voor B2B?", "Het is klein maar waardevol. In de cijfers van Orbit Media converteerde het met 2,1% ongeveer vier keer zo goed als Google search met 0,5%. Leadfeeder zag wel iets minder bezoekers die bij het ideale klantprofiel passen dan uit organisch zoeken."),
        ("Hoe snel zie je wijzigingen terug in ChatGPT?", "Pagina's die OAI-SearchBot kan crawlen, kunnen binnen enkele weken worden opgepikt. Kennis uit de training van het model verandert pas met nieuwe modelversies."),
        ("Moeten Nederlandse bedrijven in het Nederlands publiceren?", "Voor Nederlandse kopers wel. Bij Nederlandstalige vragen kwam 62,2% van de citaties in ChatGPT uit Nederlandstalige bronnen, volgens het onderzoek van Temso uit 2026."),
        ("Welke pagina's citeert ChatGPT het vaakst?", "In de data van Muck Rack is Wikipedia het meest geciteerde domein. Voor jouw categorie stel je zelf de vragen van je kopers en noteer je de bronnen."),
    ]) + [
        cta("Wil je weten hoe ChatGPT de vragen van jouw kopers nu beantwoordt? " + QS + ". Bekijk ook onze " + DIENSTEN + "."),
    ],
)

# ------------------------------------------------------------------ mentions vs links
POSTS["merkvermeldingen-vs-backlinks-ai-zichtbaarheid"] = dict(
    en="brand-mentions-vs-backlinks-ai-visibility",
    title="Merkvermeldingen of backlinks: wat werkt voor de AI-zichtbaarheid van B2B-merken",
    excerpt="Merkvermeldingen hingen met 0,664 samen met zichtbaarheid in AI Overviews, backlinks met 0,218. Wat dat betekent voor je budget voor PR en links.",
    kw="merkvermeldingen AI-zichtbaarheid",
    blocks=[
        tldr("Bij 75.000 merken hing het aantal merkvermeldingen op het web met 0,664 samen met zichtbaarheid in AI Overviews, backlinks met 0,218 (" + a("Ahrefs, 2025", "ahrefs_mentions") + "). Links helpen pagina's nog steeds te ranken. AI-zichtbaarheid volgt hoe vaak en waar het web over je praat."),
        p("Twintig jaar lang was linkbuilding de kern van SEO buiten je eigen site. AI-zoekmachines verleggen dat gewicht. In de analyse van Ahrefs uit mei 2025 waren de drie sterkste factoren voor zichtbaarheid in AI Overviews allemaal merksignalen: merkvermeldingen op het web (0,664), ankerteksten met je merknaam (0,527) en zoekvolume op je merknaam (0,392) (" + a("Ahrefs", "ahrefs_mentions") + ")."),
        p("Een vervolgonderzoek uit december 2025 over ChatGPT, Google AI Mode en AI Overviews vond vermeldingen op YouTube de sterkste losse factor, met ongeveer 0,737. Domain Rating scoorde veel lager (" + a("Ahrefs", "ahrefs_factors") + "). Het gaat om samenhang en niet om bewezen oorzaak, maar het patroon is steeds hetzelfde."),
        p("Hoe AI over je merk praat, is inmiddels een onderwerp voor de directie. " + a("Gartner", "gartner_cmo") + " meldde in november 2026 dat 42% van de CMO's actief onjuiste informatie over hun merk in AI-antwoorden opspoort en laat corrigeren."),
        p("Voor Nederlandse merken telt ook de taal. Bij Nederlandstalige vragen kwam 81,2% van de bronnen in Google AI Overview uit Nederlandstalige pagina's (" + a("Temso, 2026", "temso_lang") + "). Wie Nederlandse kopers wil bereiken, heeft dus ook vermeldingen in Nederlandstalige bronnen nodig."),
        h2("Wat is een merkvermelding in AI-zoekmachines?"),
        cap("Een merkvermelding is elke plek op het web die je bedrijf bij naam noemt, met of zonder link: artikelen, reviews, forums, podcasts en video's."),
        p("De link is niet nodig. Een vakartikel dat je als voorbeeld noemt, een draad op Reddit die je aanraadt, het transcript van een podcast of een review op G2: het telt allemaal mee."),
        h2("Waarom tellen vermeldingen voor AI zwaarder dan links?"),
        cap("Het effect van vermeldingen is dat AI-modellen merken aan onderwerpen koppelen via hoe vaak en waar ze genoemd worden. Linktellingen missen dat."),
        table(
            ["Vergeleken op", "Merkvermeldingen", "Backlinks"],
            [
                ["Samenhang met zichtbaarheid in AI Overviews (Ahrefs, 2025)", "0,664", "0,218"],
                ["Wat het laat zien", "Bij welke onderwerpen je merk hoort", "Welke pagina's autoriteit verdienen"],
                ["Hoe je het verdient", "Onderzoek, PR, reviews, podcasts, community's", "Outreach, gastblogs, digitale PR"],
                ["Effect op posities in Google", "Indirect", "Direct"],
                ["Grootste risico", "Laag als je ze verdient", "Gekochte links gaan in tegen het spambeleid van Google"],
            ],
            "Vermeldingen en links doen elk iets anders.",
        ),
        h2("Hoe verdeel je als B2B-team je budget tussen PR en links?"),
        cap("Een verstandige verdeling is er een die eerst verdiende vermeldingen betaalt, via onderzoek, PR en reviews, en linkbuilding bewaart voor pagina's die moeten ranken."),
        p("De meeste B2B-teams kunnen een deel van het linkbudget naar vermeldingen verschuiven. Persaandacht en geciteerd onderzoek leveren vaak ook links op. Zes kanalen die werken:"),
        ol([
            "<strong>Eigen onderzoek</strong> dat journalisten en analisten citeren: een benchmark, een enquête, een index.",
            "<strong>Commentaar als expert</strong> in vakmedia, zoals columns en quotes in overzichtsartikelen.",
            "<strong>Podcasts en YouTube</strong>, gezien de sterke samenhang met YouTube in de data van Ahrefs.",
            "<strong>Reviews</strong> op de platforms die je kopers gebruiken.",
            "<strong>Community's</strong> zoals LinkedIn en relevante subreddits, met echte antwoorden in plaats van reclame.",
            "<strong>Partnerpagina's</strong>: overzichten van integraties, cases op sites van partners, gezamenlijke webinars.",
        ]),
        h2("Hoe meet je vermeldingen?"),
        cap("Vermeldingen meten betekent elke maand bijhouden waar je genoemd wordt, op het web en in AI-antwoorden, en je aandeel vergelijken met drie belangrijke concurrenten."),
        p("Gebruik een tool voor mediamonitoring voor vermeldingen op het web en een tracker voor AI-zichtbaarheid voor de antwoorden. Rapporteer beide als aandeel ten opzichte van dezelfde concurrenten, dan zegt de trend iets. Ons artikel over " + a("merklift meten via vermeldingen in LLM's (Engelstalig)", "/measure-brand-lift-llm-mentions/") + " gaat dieper in op het meten."),
    ] + faq([
        ("Helpen vermeldingen zonder link voor SEO?", "Google heeft geen direct effect op posities bevestigd. Voor AI-zichtbaarheid laat de data van Ahrefs zien dat merkvermeldingen, met of zonder link, veel sterker samenhangen dan backlinks."),
        ("Moeten we stoppen met linkbuilding?", "Nee. Pagina's hebben nog steeds links nodig om in Google te ranken, en de resultaten van Google voeden AI Overviews. Verschuif de verhouding, niet het hele budget."),
        ("Doen negatieve vermeldingen pijn?", "Ze kunnen bepalen hoe assistenten je beschrijven. Gartner zag dat 42% van de CMO's onjuiste informatie in AI-antwoorden nu volgt en laat corrigeren. Volg daarom ook de toon, niet alleen het aantal."),
        ("Telt YouTube mee voor B2B?", "In het onderzoek van Ahrefs uit december 2025 hingen vermeldingen op YouTube het sterkst samen met AI-zichtbaarheid, met ongeveer 0,737. Presentaties, demo's en fragmenten uit podcasts op YouTube tellen mee."),
        ("Hoe lang duurt het voordat vermeldingen in AI-antwoorden verschijnen?", "Assistenten die het web doorzoeken, kunnen nieuwe vermeldingen binnen enkele weken oppikken. Kennis uit de training van een model verandert pas met nieuwe modelversies."),
    ]) + [
        cta("Wil je weten waar je concurrenten genoemd worden en jij niet? " + QS + " en we vergelijken je aandeel in AI-antwoorden. Bekijk ook onze " + DIENSTEN + "."),
    ],
)

# ------------------------------------------------------------------ tools compared
POSTS["ai-zichtbaarheid-tools-vergeleken"] = dict(
    en="ai-visibility-tools-compared-peec-otterly-profound",
    title="Tools voor AI-zichtbaarheid vergeleken: Peec AI, Otterly.ai, Profound en Bing Webmaster Tools",
    excerpt="Peec AI begint bij 95 dollar per maand, Profound bij 99 dollar. Een praktische vergelijking van trackers voor AI-zichtbaarheid, plus de gratis optie van Bing.",
    kw="tools AI-zichtbaarheid",
    blocks=[
        tldr("Je kunt AI-zichtbaarheid al volgen voor minder dan 100 dollar per maand: " + a("Peec AI", "peec_pricing") + " begint bij 95 dollar voor 50 prompts en " + a("Profound", "profound_pricing") + " bij 99 dollar. " + a("Bing Webmaster Tools", "bing_ai_perf") + " laat gratis zien hoe vaak Copilot je citeert. Kies op prompts, assistenten en toegang tot data, niet op het dashboard."),
        p("De meeste tools voor AI-zichtbaarheid hebben nu abonnementen die je zelf afsluit. Peec AI vraagt 95 dollar per maand voor 50 prompts op drie AI-modellen, 245 dollar voor 150 prompts en 495 dollar voor 350 (" + a("Peec AI", "peec_pricing") + ")."),
        p("Profound lanceerde in oktober 2025 goedkopere Starter- en Growth-abonnementen, vanaf 99 dollar per maand (" + a("Profound", "profound_pricing") + "). Otterly.ai rekent op het aantal prompts: 16 prompts op vier assistenten in Lite, 120 in Standard en 403 in Premium (" + a("Otterly.ai", "otterly_plans") + ")."),
        p("En één bron is gratis. Sinds februari 2026 laat Bing Webmaster Tools zien hoe vaak Copilot en de AI-samenvattingen van Bing je pagina's citeren (" + a("Bing", "bing_ai_perf") + ")."),
        p("Volg je Nederlandse kopers, stel je prompts dan ook in het Nederlands op. Bij Nederlandstalige vragen komen de meeste citaties uit Nederlandstalige bronnen: 81,2% in Google AI Overview en 66,4% in Copilot (" + a("Temso, 2026", "temso_lang") + "). Een Engelse promptset geeft dan een vertekend beeld."),
        h2("Wat is een tool voor AI-zichtbaarheid?"),
        cap("Een tool voor AI-zichtbaarheid is software die de vragen van je kopers op vaste momenten in ChatGPT en Perplexity stelt en bijhoudt wie genoemd wordt."),
        p("De meeste tools rapporteren drie dingen: of je merk verschijnt, welke concurrenten in plaats daarvan verschijnen en welke bronnen de assistent citeerde. Die bronnenlijst is vaak het nuttigste deel, want die vertelt je waar je vermeldingen moet verdienen."),
        h2("Hoe verhouden de belangrijkste tools zich tot elkaar?"),
        cap("Tools voor AI-zichtbaarheid vergelijken betekent kijken naar het aantal prompts, de gedekte assistenten, data-export en prijs per prompt, want de dashboards lijken op elkaar."),
        table(
            ["Tool", "Instapprijs", "Capaciteit bij instap", "Opmerkingen"],
            [
                ["Peec AI", "95 dollar per maand", "50 prompts, 3 modellen", "Modellen te kiezen uit een lijst met onder meer ChatGPT, Perplexity en Gemini"],
                ["Otterly.ai", "Zie de prijspagina", "16 prompts op 4 assistenten (Lite)", "Minder assistenten betekent meer prompts per abonnement"],
                ["Profound", "Vanaf 99 dollar per maand", "Niet gepubliceerd voor Starter", "Starter- en Growth-abonnementen sinds oktober 2025"],
                ["Bing Webmaster Tools", "Gratis", "Citaties van je eigen site", "Alleen Copilot en AI-samenvattingen van Bing; toont citaties, geen posities"],
            ],
            "Prijzen en capaciteit zoals elke aanbieder ze in 2026 publiceert.",
        ),
        h2("Welke tool past bij een B2B-team?"),
        cap("De juiste tool voor een B2B-team is er een die de assistenten en talen van je kopers dekt, met prompts die je elke maand nakijkt."),
        ol([
            "<strong>Tel eerst je prompts.</strong> 50 tot 150 vragen van kopers per markt is een gebruikelijke start.",
            "<strong>Maak een lijst van de assistenten die je kopers gebruiken.</strong> Neem voor B2B naast ChatGPT, Perplexity en Gemini ook Microsoft Copilot mee.",
            "<strong>Test in elke taal van je kopers.</strong> Voor de Nederlandse markt betekent dat prompts in het Nederlands.",
            "<strong>Controleer de data-export.</strong> Je moet ruwe antwoorden en bronnen kunnen downloaden.",
            "<strong>Controleer het volgen van concurrenten.</strong> Voor je aandeel in de antwoorden heb je minstens drie concurrenten op dezelfde prompts nodig.",
            "<strong>Voeg hoe dan ook Bing Webmaster Tools toe.</strong> Het kost niets en dekt Copilot.",
            "<strong>Test een maand</strong> met je echte prompts voordat je een jaarcontract tekent.",
        ]),
    ] + faq([
        ("Hebben we wel een betaalde tool nodig?", "Voor een eerste blik werkt het om 20 vragen van kopers met de hand te stellen. Voor maandelijkse rapportages over meerdere assistenten en concurrenten bespaart een tool dagen werk."),
        ("Welke tool gebruikt Booming Venture?", "We draaien promptsets in Otterly.ai of Peec AI, afhankelijk van de assistenten en het volume van de klant, plus Bing Webmaster Tools en Google Search Console."),
        ("Zijn de resultaten betrouwbaar?", "AI-antwoorden verschillen per keer. Goede tools stellen prompts herhaaldelijk en rapporteren trends. Zie een los resultaat als een steekproef."),
        ("Hoeveel moet een middelgroot B2B-bedrijf aan tools uitgeven?", "Instapabonnementen beginnen rond 95 tot 99 dollar per maand. Het " + a("overzicht van Nutshell uit 2026", "nutshell_cost") + " noemt 50 tot meer dan 1.000 dollar per maand voor het volgen van AI-zichtbaarheid, afhankelijk van de schaal."),
        ("Kunnen deze tools je zichtbaarheid verbeteren?", "Nee. Ze meten. Verbetering komt van betere pagina's, toegang voor crawlers en vermeldingen op andere sites."),
    ]) + [
        cta("Twijfel je welke prompts je moet volgen? " + QS + " en we stellen samen een eerste promptset op. Bekijk ook onze " + DIENSTEN + "."),
    ],
)

# ------------------------------------------------------------------ GEO agency
POSTS["geo-bureau-kiezen-10-vragen"] = dict(
    en="how-to-choose-a-geo-agency",
    title="Een GEO-bureau kiezen: 10 vragen om te stellen voordat je tekent",
    excerpt="Op zoek naar een GEO-bureau? Stel deze 10 vragen over prompts, meten, techniek, contentkwaliteit, autoriteit en prijs voordat je tekent.",
    kw="GEO-bureau",
    blocks=[
        tldr("94% van de zakelijke kopers gebruikt nu AI tijdens het aankoopproces, volgens " + a("Forrester (2026)", "forrester") + ". Een goed GEO-bureau laat zien waar je merk nu in AI-antwoorden staat, lost op wat citaties blokkeert en rapporteert elke maand je aandeel. Stel de 10 vragen hieronder voordat je tekent."),
        p("Je volgende koper ziet je merk waarschijnlijk eerder in een AI-antwoord dan dat hij je salesteam spreekt. In het Buyers' Journey Survey 2025 van Forrester waren generatieve AI en zoeken via een chatassistent de belangrijkste informatiebron voor zakelijke kopers, vóór websites van leveranciers, productexperts en verkopers (" + a("Forrester", "forrester") + ")."),
        p("Tegelijk neemt het aantal klikken af. " + a("Pew Research Center", "pew") + " volgde in maart 2025 het surfgedrag van 900 Amerikaanse volwassenen. Als Google een AI-samenvatting toonde, eindigde 8% van de bezoeken met een klik op een zoekresultaat, tegen 15% zonder samenvatting."),
        p("Ook in Nederland toont Google " + a("AI-overzichten in het Nederlands", "google_aio_nl") + ". Bij Nederlandstalige vragen kwam 81,2% van de bronnen in die overzichten uit Nederlandstalige pagina's (" + a("Temso, 2026", "temso_lang") + "). Een bureau dat alleen in het Engels meet, mist dus je markt."),
        p("Daarom verkoopt nu elk bureau GEO. Sommige doen echt werk aan prompts, toegang voor crawlers en autoriteit. Andere plakken een nieuw label op een SEO-retainer of verkopen AI-artikelen per honderd. Deze vragen laten het verschil zien."),
        h2("Wat is een GEO-bureau?"),
        cap("Een GEO-bureau is een marketingpartner die verbetert hoe vaak en hoe juist AI-assistenten als ChatGPT, Perplexity en Gemini je merk noemen en citeren."),
        p("GEO staat voor generative engine optimization. De term komt uit een onderzoek uit 2024 van onderzoekers van Princeton en partners, gepresenteerd op KDD. Daaruit bleek dat aanpassingen in content de zichtbaarheid van een bron in antwoorden van generatieve zoekmachines tot 40% kunnen verhogen (" + a("Aggarwal e.a., 2024", "geo_paper") + ")."),
        p("GEO overlapt met SEO, want Google AI Overviews en ChatGPT search lezen nog steeds geïndexeerde pagina's. Maar GEO voegt twee lagen toe die SEO-retainers vaak overslaan: antwoorden meten in plaats van posities en vermeldingen van je merk op andere sites opbouwen. Die tweede laag telt. In een " + a("analyse van Ahrefs van 75.000 merken (2025)", "ahrefs_mentions") + " hingen merkvermeldingen met 0,664 samen met zichtbaarheid in AI Overviews, tegen 0,218 voor backlinks."),
        h2("Waarom kost het verkeerde GEO-bureau je pipeline?"),
        cap("Het verkeerde GEO-bureau is er een dat rapporten en volume factureert terwijl je merk ontbreekt in de AI-antwoorden die kopers voor hun shortlist gebruiken."),
        p("Volume is het risico dat kopers het vaakst onderschatten. Het spambeleid van Google noemt het inzetten van generatieve AI om veel pagina's te maken zonder toegevoegde waarde misbruik van geschaalde content. Sites die dat beleid overtreden, kunnen lager ranken of uit de resultaten verdwijnen (" + a("Google Search Central", "google_spam") + "). Een bureau dat tientallen AI-artikelen per maand publiceert, kan het domein beschadigen waar je AI-zichtbaarheid van afhangt."),
        p("De andere valkuil is een rapport zonder verbeteringen. Een promptaudit vertelt waar je ontbreekt. Er verandert niets tot iemand pagina's aanpast, crawlers toegang geeft en vermeldingen verdient."),
        h2("Welke 10 vragen stel je een GEO-bureau?"),
        cap("Een GEO-bureau toetsen betekent tien vragen stellen over prompts, meten, technische toegang, contentkwaliteit, autoriteit, toewijzing en prijs voordat je tekent."),
        ol([
            "<strong>Welke prompts gaan jullie volgen, en hoe hebben jullie die gekozen?</strong> Een goed antwoord koppelt 50 tot 200 prompts aan je aankooptraject, van vragen over het probleem tot shortlists als \"beste [categorie] voor [sector]\", in elke taal die je kopers gebruiken. Voor de Nederlandse markt horen daar Nederlandse prompts bij. Algemene prompts geven vleiende maar nutteloze scores.",
            "<strong>Hoe meten jullie AI-zichtbaarheid, en hoe vaak?</strong> Vraag welke assistenten ze volgen (ChatGPT, Perplexity, Gemini, Microsoft Copilot, Google AI Overviews), welke tool ze gebruiken en of je de ruwe antwoorden krijgt. Volgens Forrester gebruiken zakelijke kopers Microsoft Copilot vier keer zo vaak als consumenten. Een B2B-tracker zonder Copilot mist dus een deel van je markt.",
            "<strong>Kunnen AI-crawlers onze site nu lezen?</strong> In de " + a("analyse van Vercel uit 2024", "vercel") + " renderde geen van de grote AI-crawlers JavaScript. Het bureau hoort in week één de HTML vanaf de server te controleren, de regels in robots.txt voor OAI-SearchBot, Claude-SearchBot en PerplexityBot, en de instellingen van je firewall.",
            "<strong>Wat veranderen jullie in de eerste 30 dagen op onze site?</strong> Verwacht concrete pagina's en concrete verbeteringen: dienstpagina's die met het antwoord beginnen, vergelijkingstabellen, auteurspagina's, schema. Een eerste maand die alleen een presentatie oplevert, is een onderzoeksproject.",
            "<strong>Hoe zorgen jullie dat ons merk op andere sites genoemd wordt?</strong> Vraag naar digitale PR, branchelijsten, reviewplatforms, podcasts en community's, met voorbeelden van vermeldingen die ze voor andere klanten verdienden.",
            "<strong>Wie schrijft de content, en wie controleert de feiten?</strong> Volgens Google is het cruciaal om alle content die met AI is gemaakt met de hand op feiten te controleren en na te lezen (" + a("Google Search Central", "google_genai") + "). Je wilt schrijvers met een naam, een controleur van jouw kant en een bron bij elk getal.",
            "<strong>Hoeveel pagina's publiceren jullie per maand, en waarom dat aantal?</strong> Het juiste aantal volgt uit de gaten in je prompts, niet uit een pakket. Wees voorzichtig met elk plan dat op volume draait.",
            "<strong>Hoe koppelen jullie AI-zichtbaarheid aan pipeline?</strong> AI-antwoorden leveren vaak geen klik op. Goede bureaus combineren je aandeel in antwoorden met trends in zoekopdrachten op je merknaam, een veld \"Hoe heb je ons gevonden?\" waarin AI-assistenten staan, en notities uit salesgesprekken.",
            "<strong>Wat zit er precies in de prijs?</strong> Scheid de diagnose, vaak een sprint met vaste prijs, van de doorlopende retainer. Controleer ook wie het werk uitvoert: het bureau, je eigen developers of allebei.",
            "<strong>Wat houden we als we stoppen?</strong> De promptset, de ruwe antwoorden, de dashboards en elke pagina horen van jou te zijn. Staat de tracking alleen in het account van het bureau, dan vertrekt je nulmeting met hen mee.",
        ]),
        h2("Wat is het verschil tussen een GEO-bureau, een SEO-bureau en een eigen team?"),
        cap("Het verschil tussen een GEO-bureau en een SEO-bureau is wat ze meten: citaties in AI-antwoorden, geen posities en klikken in zoekresultaten."),
        table(
            ["Vergeleken op", "SEO-bureau", "GEO-bureau", "Eigen team"],
            [
                ["Hoofddoel", "Posities en organische klikken", "Vermeldingen en citaties in AI-antwoorden", "Allebei, binnen de capaciteit van het team"],
                ["Wat er gemeten wordt", "Posities, verkeer, conversies", "Aandeel per prompt en assistent, geciteerde bronnen", "Hangt af van tools en kennis"],
                ["Gebruikelijk werk", "Technische SEO, content, links", "Prompts volgen, pagina's klaar voor antwoorden, toegang voor crawlers, merkvermeldingen", "Content en aanpassingen aan de site, weinig outreach"],
                ["Sterk punt", "Bewezen methodes en tools", "Gemaakt voor aankooptrajecten zonder klikken", "Productkennis en snelheid"],
                ["Let op", "Negeert antwoorden zonder klik", "Volumepakketten, rapporten zonder verbeteringen", "Geen trackingtools of PR-bereik"],
            ],
            "Hoe de drie opties in de praktijk verschillen.",
        ),
        h2("Welke rode vlaggen zijn een reden om te stoppen?"),
        cap("Een rode vlag bij GEO is elke belofte die je niet kunt controleren, zoals gegarandeerde posities in ChatGPT, geheime prompttrucs of contentpakketten per honderd artikelen."),
        ul([
            "Gegarandeerde posities in ChatGPT of Gemini. Antwoorden verschillen per gebruiker, locatie en modelversie.",
            "Een zichtbaarheidsscore zonder toegang tot de ruwe antwoorden erachter.",
            "Contentpakketten met een prijs per volume.",
            "Geen technische controle van rendering en toegang voor bots.",
            "Een contract waarin je trackingdata bij het bureau blijft.",
        ]),
    ] + faq([
        ("Hoe lang duurt het voordat GEO resultaat oplevert?", "Assistenten die het live web doorzoeken, zoals Perplexity en ChatGPT search, kunnen verbeterde pagina's binnen enkele weken oppikken. Wat modellen uit hun training weten, verandert langzamer, omdat dat afhangt van nieuwe modelversies."),
        ("Kan mijn SEO-bureau ook GEO doen?", "Vaak gedeeltelijk. Technische SEO, gestructureerde content en indexering gaan mee. Kijk of ze AI-antwoorden per prompt volgen en merkvermeldingen opbouwen: de twee onderdelen die SEO-retainers meestal laten liggen."),
        ("Welke AI-assistenten tellen het zwaarst voor B2B?", "Begin waar je kopers werken. Volgens Forrester gebruiken zakelijke kopers ChatGPT twee keer zo vaak als consumenten en Microsoft Copilot vier keer zo vaak. 61% gebruikt AI-tools die hun eigen organisatie aanbiedt."),
        ("Wat kost een GEO-bureau?", "Prijzen hangen af van de scope, het aantal markten en hoeveel uitvoering erin zit. Vraag eerst een diagnose met vaste scope, daarna een retainer met concrete maandelijkse resultaten. Onze eigen prijzen staan op de pagina met " + DIENSTEN + "."),
        ("Is GEO hetzelfde als AEO?", "Ze overlappen. AEO, answer engine optimization, draait om het directe antwoord op een vraag zijn. GEO is breder: genoemd en geciteerd worden door generatieve AI, inclusief je reputatie op andere sites. We vergelijken alle drie in " + a("GEO vs SEO vs AEO (Engelstalig)", "/geo-vs-seo-vs-aeo-b2b-budget-2026/") + "."),
        ("Hebben we GEO nodig als onze salescyclus lang is en om relaties draait?", "Ja. Shortlists ontstaan vóór het eerste salesgesprek, en volgens Forrester gebruikt 94% van de zakelijke kopers AI in het aankoopproces."),
    ]) + [
        cta("Wil je een second opinion voordat je met een bureau tekent? " + QS + " en we bekijken samen je AI-zichtbaarheid. Bekijk ook onze " + a("diensten en prijzen", "/nl/diensten/") + "."),
    ],
)


# ------------------------------------------------------------------ checks
BANNED_NL = [
    "naadloos", "naadloze", "baanbrekend", "revolutionair", "ontgrendel", "duik in", "duiken in",
    "in de huidige snelle wereld", "in het huidige digitale", "snel veranderende", "steeds veranderende",
    "tapijt", "robuust", "robuuste", "holistisch", "synergie", "bovendien", "gamechanger", "game-changer",
    "speelveld", "landschap", "het is belangrijk om op te merken", "tot slot", "kortom",
]


def check(slug, post):
    content = "\n".join(post["blocks"])
    txt = text_of(content)
    low = txt.lower()
    issues, warn = [], []
    if "—" in content or "–" in content:
        issues.append("dash found")
    for b in BANNED_NL + q4.BANNED:
        if re.search(r"(?<![\w-])" + re.escape(b) + r"(?![\w-])", low):
            issues.append("banned: " + b)
    caps = re.findall(r'class="bv-capsule[^"]*"[^>]*>(.*?)</p>', content, re.S)
    for c in caps:
        t = text_of(c).strip()
        n = len(t.split())
        if not 20 <= n <= 25:
            issues.append(f"capsule {n} words: {t[:60]}")
        if not re.match(r"^\S.*?\b(is|zijn|betekent|verwijst naar)\b", t):
            issues.append("capsule opening: " + t[:50])
    h2s = re.findall(r"<h2[^>]*>(.*?)</h2>", content)
    qh2 = [h for h in h2s if h.strip().endswith("?")]
    if len(qh2) < 3:
        issues.append("question H2s: %d" % len(qh2))
    cut = content[: int(len(content) * 0.30)]
    first30 = len(re.findall(r'href="https?://(?!boomingventure)', cut))
    if first30 < 3:
        issues.append(f"source links in first 30%: {first30}")
    if "Veelgestelde vragen" not in content or "Belangrijkste punten" not in content:
        issues.append("missing FAQ or takeaways box")
    if not re.search(r'href="/nl/(diensten|funnel-calculator|roi-forecaster|unify-framework|boardroom-quickscan)/"', content):
        issues.append("no Dutch internal link")
    if re.search(r'href="/(?!nl/)(services|boardroom-quickscan|funnel-calculator|roi-forecaster|unify-framework|about|blog)/"', content):
        issues.append("link to an English page that has a Dutch version")
    if re.search(r"\d\.\d+\s?%|\b[1-9]\d{0,2},\d{3}\b", txt):
        issues.append("English number style")
    if not ("<table" in content or re.search(r"<ol[^>]*>(?:.*?<li>){5}", content, re.S)):
        issues.append("no table or list of 5+")
    if len(post["excerpt"]) > 160:
        issues.append("excerpt %d chars" % len(post["excerpt"]))
    # serial comma: ", en" right after a list item is US style in Dutch
    serial = re.findall(r"\b\w+, \w+(?: \w+)?, (?:en|of) \w+", txt)
    if serial:
        warn.append(f"check serial comma: {serial[:2]}")
    sents = [s for s in re.split(r"(?<=[.!?])\s+", txt) if len(s.split()) > 2]
    avg = sum(len(s.split()) for s in sents) / max(1, len(sents))
    return dict(words=len(txt.split()), avg_sentence=round(avg, 1), capsules=len(caps),
                question_h2=len(qh2), first30_links=first30, issues=issues, warn=warn)


if __name__ == "__main__":
    OUT.mkdir(exist_ok=True)
    manifest = {}
    bad = 0
    for slug, post in POSTS.items():
        content = "\n".join(post["blocks"])
        (OUT / f"{slug}.html").write_text(content, encoding="utf-8")
        manifest[slug] = {k: post[k] for k in ("en", "title", "excerpt", "kw")}
        r = check(slug, post)
        bad += bool(r["issues"])
        print(slug, json.dumps(r, ensure_ascii=False))
    (OUT / "manifest.json").write_text(json.dumps(manifest, indent=1, ensure_ascii=False), encoding="utf-8")
    print(f"{len(POSTS)} posts, {bad} with issues")
