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


# ------------------------------------------------------------------ best-of lists
POSTS["best-of-lijsten-ai-assistenten"] = dict(
    en="get-on-best-of-lists-ai-assistants-cite",
    title="Zo komt je bedrijf op de best-of-lijsten die AI-assistenten citeren",
    excerpt="Best-of-lijsten waren 43,8% van de paginatypes die ChatGPT citeerde in een onderzoek van Ahrefs. Zo verdien je als B2B-bedrijf een plek op die lijsten.",
    kw="best-of-lijsten AI",
    blocks=[
        tldr("In een " + a("onderzoek van Ahrefs naar 750 prompts in ChatGPT", "ahrefs_lists") + " waren lijsten van het type \"beste X\" 43,8% van de geciteerde paginatypes. Een plek op onafhankelijke lijsten die vaak worden bijgewerkt, is een van de snelste manieren om genoemd te worden als kopers AI om een shortlist vragen."),
        p("Vraagt een koper ChatGPT naar de beste tools of bureaus in een categorie, dan leunt het antwoord vaak op bestaande ranglijsten. Ahrefs zag dat 79,1% van 1.100 geciteerde lijstartikelen in 2025 was bijgewerkt, en 26% in de twee maanden voor het onderzoek (" + a("Ahrefs", "ahrefs_lists") + "). Actuele lijsten winnen."),
        p("Autoriteit telt minder dan je zou denken: 35% van de geciteerde lijsten stond op domeinen met weinig autoriteit. Maar lijsten zijn geen zekerheid. " + a("Seer Interactive", "seer_listicles") + " mat tussen december 2025 en januari 2026 een daling van het aantal geciteerde lijstartikelen in ChatGPT van 160.000 naar 111.000 URL's, ongeveer 30%."),
        p("Lijsten zijn dus één kanaal van meerdere. Kopers gebruiken ze hoe dan ook: volgens " + a("Forrester", "forrester") + " gebruikt 94% van de zakelijke kopers AI tijdens het aankoopproces."),
        h2("Wat is een best-of-lijst in AI-zoekmachines?"),
        cap("Een best-of-lijst is een vergelijkingspagina met een rangorde, zoals \"beste CRM-tools\", die assistenten lezen en samenvatten als kopers om opties vragen."),
        p("Lijsten zijn er in verschillende vormen, en je hebt er niet overal evenveel invloed op."),
        table(
            ["Soort lijst", "Wie bepaalt het", "Moeite om erop te komen", "Let op"],
            [
                ["Overzicht in vakmedia", "Redacteur", "Gemiddeld: pitch met feiten en een toepassing", "Hooguit één keer per jaar bijgewerkt"],
                ["Categoriepagina op een reviewplatform (G2, Capterra)", "Platform, gerangschikt op reviews", "Doorlopend: een vaste stroom reviews", "Betaalde plaatsingen naast de organische ranglijst"],
                ["Lijst van een analist of adviseur", "Analist", "Hoog: briefings en referenties", "Vaak achter een betaalmuur"],
                ["Overzicht van partners of integraties", "Partner", "Laag: vul je vermelding volledig in", "Klein publiek"],
                ["Je eigen vergelijkingspagina", "Jij", "Laag om te schrijven, hoog om eerlijk te houden", "Lezers en assistenten geven er mogelijk minder gewicht aan"],
            ],
            "Lijsten naar hoeveel invloed je erop hebt.",
        ),
        h2("Waarom leunen AI-assistenten op deze lijsten?"),
        cap("Leunen op lijsten betekent dat assistenten een shortlist overnemen die iemand al maakte: een ranglijst beantwoordt \"welke?\" sneller dan tien losse sites van leveranciers."),
        p("Op een vraag naar een shortlist bestaat niet één juist antwoord. Daarom zoeken assistenten pagina's die opties al vergelijken. Weet je welke lijsten ze bij jouw prompts citeren, dan weet je waar je moet staan."),
        h2("Hoe kom je op de lijsten die ertoe doen?"),
        cap("Op lijsten komen betekent de pagina's vinden die assistenten voor je categorie citeren en de auteurs een reden en feiten geven."),
        ol([
            "<strong>Stel je shortlistvragen</strong> in ChatGPT, Perplexity, Gemini en Copilot en noteer elke lijst die ze citeren. Doe dat in het Nederlands en in het Engels als je in beide talen verkoopt.",
            "<strong>Orden de lijsten</strong> op hoe vaak ze verschijnen, hoe actueel ze zijn en hoe goed ze bij je segment passen.",
            "<strong>Kijk wat elke lijst nodig heeft</strong>: prijzen, een toepassing, screenshots, bewijs van klanten. De meeste auteurs zeggen waarop ze vergelijken.",
            "<strong>Maak een factsheet van één pagina</strong> met precies die feiten, zodat het de auteur tien minuten kost om je op te nemen.",
            "<strong>Pitch met een reden</strong>: een segment dat de lijst mist, een nieuwe functie of een klantresultaat dat de auteur kan citeren.",
            "<strong>Bouw onafhankelijke reviews op</strong>, zodat categoriepagina's die op reviews rangschikken ook zonder pitch bewegen.",
            "<strong>Volg op als lijsten worden bijgewerkt.</strong> Veel auteurs werken jaarlijks bij. Zorg dat je daarvoor in hun inbox zit.",
        ]),
        h2("Moet je je eigen best-of-lijst publiceren?"),
        cap("Een eigen lijst is een vergelijking op je eigen site waarin je jezelf rangschikt. Die kan geciteerd worden als hij eerlijk, onderbouwd en actueel is."),
        p("Veel bedrijven doen het. Van 250 Google-resultatenpagina's voor \"beste X-software\" die Ahrefs bekeek, toonden er 169 (67,6%) een lijst waarin de uitgever zichzelf op één zette. Een lijst van de leverancier zelf stond ook in 34% van de softwareantwoorden van ChatGPT die de uitgever aanraadden."),
        p("Publiceer je er een, houd het dan eerlijk: echte criteria, concurrenten eerlijk beschreven, een datum en bronnen. Een lijst die leest als advertentie helpt niemand, en kopers merken dat."),
    ] + faq([
        ("Kunnen we betalen om op lijsten te komen?", "Sommige lijsten verkopen plaatsingen of verdienen affiliatevergoedingen. Een gesponsorde plaatsing die duidelijk wordt gemeld, is toegestaan, maar links kopen die rankingwaarde doorgeven, gaat in tegen het spambeleid van Google. Verdiende plaatsingen op onafhankelijke lijsten zijn de veiligere weg."),
        ("Op hoeveel lijsten moeten we mikken?", "Begin met de vijf tot tien lijsten die het vaakst in je promptresultaten verschijnen. Voeg er meer toe als er nieuwe in je maandelijkse metingen opduiken."),
        ("Verliezen lijstartikelen hun waarde in AI-zoekmachines?", "Seer mat in één maand een daling van 30% in citaties van lijstartikelen in ChatGPT. Zie lijsten als één kanaal naast reviews, vermeldingen in media en je eigen pagina's met antwoorden."),
        ("Tellen Nederlandstalige lijsten mee?", "Ja, voor Nederlandse kopers. Bij Nederlandstalige vragen kwam 62,2% van de citaties in ChatGPT uit Nederlandstalige bronnen (" + a("Temso, 2026", "temso_lang") + "). Volg je Nederlandse prompts daarom apart."),
        ("Hoe weten we of een plaatsing op een lijst heeft gewerkt?", "Stel elke maand dezelfde prompts en volg of je genoemd wordt en welke bronnen worden geciteerd. Tools als Otterly.ai of Peec AI doen dat automatisch."),
    ]) + [
        cta("Wil je weten welke lijsten AI-assistenten in jouw categorie citeren? " + QS + " en we brengen ze samen in kaart. Bekijk ook onze " + DIENSTEN + ", of lees " + a("hoe je een GEO-bureau kiest", "/nl/blog/geo-bureau-kiezen-10-vragen/") + "."),
    ],
)

# ------------------------------------------------------------------ comparison pages
SRC["ondernemersplein_reclame"] = "https://ondernemersplein.overheid.nl/regels-voor-reclame"

POSTS["vergelijkingspaginas-ai-assistenten"] = dict(
    en="comparison-pages-ai-assistants-cite",
    title="Zo schrijf je vergelijkingspagina's die AI-assistenten citeren",
    excerpt="Vergelijkingen naast elkaar horen bij de vaakst geciteerde formats in AI-antwoorden. Zo schrijf je pagina's die kopers en AI-assistenten vertrouwen.",
    kw="vergelijkingspagina's",
    blocks=[
        tldr("In een " + a("onderzoek van Meltwater en LinkedIn", "meltwater_linkedin") + " naar 9,5 miljoen AI-citaties hoorden vergelijkingen naast elkaar (50%) en best-of-lijsten (54%) bij de vaakst geciteerde formats op LinkedIn. Eerlijke, onderbouwde vergelijkingspagina's helpen kopers die drie of minder producten op hun shortlist zetten."),
        p("Kopers beslissen snel. In het onderzoek van TrustRadius uit 2026 onder 1.862 kopers van technologie zette 83% drie of minder producten op de shortlist (" + a("TrustRadius", "trustradius_2026") + "). Het vergelijken gebeurt vóór het eerste salesgesprek."),
        p("AI-assistenten doen nu veel van dat vergelijkwerk. In een " + a("onderzoek van Ahrefs naar 750 prompts in ChatGPT", "ahrefs_lists") + " waren lijsten van het type \"beste X\" 43,8% van de geciteerde paginatypes. En in het onderzoek van Meltwater hoorden vergelijkingen naast elkaar met 50% bij de vaakst geciteerde formats op LinkedIn (" + a("Meltwater", "meltwater_linkedin") + ")."),
        p("Publiceer je de vergelijking niet zelf, dan doet een ander het. Met zijn feiten over jou."),
        h2("Wat is een vergelijkingspagina?"),
        cap("Een vergelijkingspagina is een pagina die je product op dezelfde criteria naast alternatieven zet, met een titel als \"X vs Y\" of \"alternatieven voor X\"."),
        p("Goed gedaan is het een hulpmiddel bij de aankoop: de criteria waar kopers om geven, eerlijke antwoorden per optie en een duidelijke uitspraak over voor wie elke optie geschikt is."),
        h2("Waarom citeren AI-assistenten vergelijkingspagina's?"),
        cap("Een vergelijking citeren betekent dat de assistent gestructureerde antwoorden hergebruikt: criteria, een tabel en een oordeel beantwoorden \"wat is beter voor mij?\" op één plek."),
        p("Een tabel met dezelfde rijen voor elke optie is makkelijk te citeren en samen te vatten. Dat format gebruiken assistenten als een koper om een aanbeveling vraagt."),
        h2("Hoe schrijf je een vergelijkingspagina die geciteerd wordt?"),
        cap("Een citeerbare vergelijking schrijven betekent echte criteria van kopers kiezen, de tabel voor elke optie eerlijk invullen en duidelijk zeggen wie wat moet kiezen."),
        ol([
            "<strong>Kies de vergelijkingen die kopers echt maken.</strong> Kijk naar salesgesprekken, verloren deals en je prompttracker.",
            "<strong>Gebruik de criteria van de koper</strong>: prijsmodel, integraties, implementatietijd, support, compliance.",
            "<strong>Maak één tabel</strong> met dezelfde rijen voor elke optie, als HTML-tekst.",
            "<strong>Onderbouw feiten over concurrenten</strong> met hun eigen prijs- en documentatiepagina's, met een datum.",
            "<strong>Geef per segment een oordeel</strong>: \"Kies X als je ... nodig hebt\", ook voor situaties waarin de concurrent beter past.",
            "<strong>Controleer elk kwartaal</strong>, want prijzen en functies veranderen.",
        ]),
        table(
            ["Onderdeel", "Zwakke vergelijkingspagina", "Sterke vergelijkingspagina"],
            [
                ["Criteria", "Gekozen zodat jij wint", "Gekozen door kopers"],
                ["Feiten over concurrenten", "Zonder bron, verouderd", "Gelinkt naar hun pagina's, met datum"],
                ["Oordeel", "\"Wij zijn beter\"", "Voor wie elke optie geschikt is"],
                ["Vorm", "Afbeeldingen en sliders", "HTML-tabel en tekst"],
            ],
            "Wat een pagina die kopers vertrouwen onderscheidt van een advertentie.",
        ),
    ] + faq([
        ("Is het riskant om concurrenten te noemen?", "Vergelijkende reclame mag in Nederland onder voorwaarden: ze mag niet misleiden, de producten moeten hetzelfde doel hebben en je moet objectief vergelijken op controleerbare eigenschappen (" + a("Ondernemersplein", "ondernemersplein_reclame") + "). Link naar hun openbare pagina's, zet een datum bij je feiten en vermijd claims die je niet kunt bewijzen."),
        ("Hoeveel vergelijkingspagina's moeten we publiceren?", "Begin met de drie tot vijf vergelijkingen die je kopers het vaakst maken. Kwaliteit telt meer dan aantal."),
        ("Moeten we toegeven waar een concurrent beter is?", "Ja. Kopers zien eenzijdige pagina's direct, en een eerlijk oordeel maakt de rest van de pagina geloofwaardig."),
        ("Waar horen vergelijkingspagina's te staan?", "Op je eigen site, met links vanaf product- en prijspagina's, zodat kopers en crawlers ze vinden."),
        ("Werken vergelijkingspagina's ook voor diensten?", "Ja. Vergelijk samenwerkingsvormen, zoals bureau, eigen team of freelancer, op kosten, snelheid en controle."),
    ]) + [
        cta("Wil je een vergelijkingspagina die kopers vertrouwen? " + QS + " en we kiezen de vergelijkingen die ertoe doen. Bekijk ook onze " + DIENSTEN + ", of lees " + a("hoe je op de lijsten komt die AI-assistenten citeren", "/nl/blog/best-of-lijsten-ai-assistenten/") + "."),
    ],
)

# ------------------------------------------------------------------ wrong AI answers
POSTS["foute-ai-antwoorden-over-je-bedrijf"] = dict(
    en="fix-wrong-ai-answers-about-your-company",
    title="Als AI je bedrijf verkeerd beschrijft: een correctieplan voor B2B-merken",
    excerpt="AI-zoektools gaven in een test op meer dan 60% van de vragen een fout antwoord. Zo vind en herstel je foute AI-antwoorden over je bedrijf.",
    kw="foute AI-antwoorden",
    blocks=[
        tldr("AI-assistenten maken vaak fouten: in een " + a("test van het Tow Center", "cjr_tow") + " gaven acht AI-zoektools op meer dan 60% van de vragen een fout antwoord, en een " + a("onderzoek van de EBU en de BBC", "ebu_study") + " vond grote problemen in 45% van de antwoorden. Controleer elke maand wat assistenten over je bedrijf zeggen en verbeter de bronnen waarop ze leunen."),
        p("Foute antwoorden zijn geen zeldzame uitzondering. Het Tow Center for Digital Journalism stelde begin 2025 1.600 vragen aan acht AI-zoektools. Samen beantwoordden ze meer dan 60% verkeerd (" + a("Columbia Journalism Review", "cjr_tow") + ")."),
        p("Een groter onderzoek van 22 publieke omroepen beoordeelde meer dan 3.000 antwoorden van ChatGPT, Copilot, Gemini en Perplexity in 14 talen. 45% had minstens één groot probleem, en 31% had ernstige problemen met bronnen (" + a("EBU", "ebu_study") + ")."),
        p("Marketingleiders reageren. " + a("Gartner", "gartner_cmo") + " zag in november 2026 dat 42% van de CMO's onjuiste informatie over hun merk in AI-antwoorden volgt en laat corrigeren."),
        h2("Wat telt als een fout AI-antwoord over je bedrijf?"),
        cap("Een fout AI-antwoord is elke uitspraak over je bedrijf die feiten verkeerd weergeeft waarop kopers beslissen: prijzen, functies, certificeringen, klanten, vestigingen of eigendom."),
        p("De bekende fouten in B2B: verouderde prijzen, een gestopt product dat als actueel wordt gepresenteerd, ontbrekende certificeringen, een functie van een concurrent die aan jou wordt toegeschreven of het verkeerde land van je hoofdkantoor."),
        h2("Waarom beschrijven AI-assistenten bedrijven verkeerd?"),
        cap("Een AI-fout over een bedrijf betekent meestal dat de assistent op verouderde, magere of tegenstrijdige bronnen leunde, of een gat vulde waar geen bron was."),
        p("Verbergt je prijspagina de prijzen, dan wordt een reviewsite uit 2023 de bron. Staan je certificeringen in een pdf, dan vindt de assistent ze misschien niet. Elk gat is een uitnodiging om te gokken."),
        h2("Hoe herstel je foute AI-antwoorden?"),
        cap("Foute AI-antwoorden herstellen betekent de bronnen verbeteren, niet de assistent: publiceer de juiste feiten duidelijk, werk verouderde pagina's van anderen bij en controleer maandelijks."),
        ol([
            "<strong>Doe elke maand een feitencheck.</strong> Stel ChatGPT, Copilot, Gemini en Perplexity tien vragen die kopers over je stellen, in het Nederlands en in het Engels als je in beide talen verkoopt.",
            "<strong>Leg elke fout vast</strong> met de assistent, de foute uitspraak en de geciteerde bron.",
            "<strong>Publiceer het juiste feit</strong> op je eigen site als gewone HTML-tekst: een prijspagina, een pagina over beveiliging en certificeringen, een over-ons-pagina met KvK-nummer en adres.",
            "<strong>Verbeter bronnen van anderen</strong>: werk reviewprofielen, bedrijvengidsen, partnerpagina's en verouderde artikelen bij waar dat kan.",
            "<strong>Gebruik de feedbackknoppen</strong> in elke assistent om het foute antwoord te melden.",
            "<strong>Controleer na vier weken opnieuw</strong> en volg het aantal fouten in de tijd.",
        ]),
    ] + faq([
        ("Kunnen we OpenAI of Google vragen een antwoord te corrigeren?", "Je kunt antwoorden melden via de feedbackopties in elk product. De blijvende oplossing is het verbeteren van de bronnen waarop assistenten leunen."),
        ("Welke feiten tellen het zwaarst?", "De feiten die deals laten mislukken: prijsmodel, certificeringen, integraties, klantsegmenten en of een product nog bestaat."),
        ("Hoe vaak moeten we controleren?", "Maandelijks voor de kernfeiten, en na elke lancering, prijswijziging of naamswijziging."),
        ("Merken kopers foute AI-antwoorden op?", "Veel wel. TrustRadius zag dat 94% van de kopers die AI gebruiken de antwoorden minstens af en toe controleert."),
        ("Wie is hiervoor verantwoordelijk?", "Meestal marketing, met input van product en sales. Sales hoort de foute claims vaak als eerste."),
    ]) + [
        cta("Wil je weten wat AI-assistenten over je bedrijf zeggen? " + QS + " en we controleren de feiten samen. Bekijk ook onze " + DIENSTEN + ", of lees " + a("merkvermeldingen of backlinks", "/nl/blog/merkvermeldingen-vs-backlinks-ai-zichtbaarheid/") + "."),
    ],
)

# ------------------------------------------------------------------ freshness
POSTS["actuele-content-ai-zoekmachines-b2b"] = dict(
    en="content-freshness-ai-search-b2b",
    title="Actuele content en AI-zoekmachines: hoe vaak B2B-teams pagina's moeten bijwerken",
    excerpt="AI-assistenten citeren content die 25,7% actueler is dan de organische resultaten van Google. Welke B2B-pagina's je bijwerkt, hoe vaak en wat echt telt.",
    kw="actuele content AI",
    blocks=[
        tldr("AI-assistenten citeren nieuwere content dan Google rankt: in een " + a("onderzoek van Ahrefs naar 17 miljoen citaties", "ahrefs_fresh") + " waren geciteerde pagina's 25,7% actueler dan de organische resultaten. Werk je belangrijkste commerciële pagina's elk kwartaal echt bij, niet alleen met een nieuwe datum."),
        p("Actualiteit komt in grote onderzoeken naar citaties steeds terug. Ahrefs vergeleek 17 miljoen citaties uit ChatGPT, Perplexity, Gemini, Copilot en Google AI Overviews met de organische resultaten van Google. Content die AI citeerde, was gemiddeld 25,7% actueler (" + a("Ahrefs, 2025", "ahrefs_fresh") + ")."),
        p("Bij lijsten zie je hetzelfde. Van 1.100 lijstartikelen die ChatGPT citeerde, was 79,1% in 2025 bijgewerkt, en 26% in de twee maanden voor het onderzoek (" + a("Ahrefs", "ahrefs_lists") + ")."),
        p("Ook bij journalistiek: 57% van de citaties van journalistieke artikelen met een bekende datum was in de laatste 12 maanden gepubliceerd. Citaties pieken in de eerste maand na publicatie (" + a("Muck Rack, mei 2026", "muckrack_may26") + ")."),
        h2("Wat is actuele content in AI-zoekmachines?"),
        cap("Actuele content verwijst naar hoe recent een pagina is gepubliceerd of inhoudelijk bijgewerkt. AI-assistenten lijken dat mee te wegen bij het kiezen van bronnen."),
        p("Niet elke assistent doet het op dezelfde manier. Ahrefs zag dat ChatGPT de sterkste voorkeur had voor nieuwere content, terwijl Google AI Overviews als enige systeem iets oudere content citeerde dan de organische resultaten van Google."),
        h2("Welke B2B-pagina's werk je als eerste bij?"),
        cap("Je eerste prioriteit zijn de pagina's waarop kopers beslissen: prijzen, vergelijkingen, productpagina's, pagina's over integraties en je meest bezochte gidsen."),
        table(
            ["Soort pagina", "Hoe vaak bijwerken", "Hoe een echte update eruitziet"],
            [
                ["Prijzen", "Bij elke wijziging, elk kwartaal controleren", "Actuele prijzen, limieten per abonnement, datum"],
                ["Vergelijkingspagina's", "Elk kwartaal", "Feiten over concurrenten opnieuw gecontroleerd, nieuwe criteria"],
                ["Product- en integratiepagina's", "Bij elke release", "Nieuwe functies, screenshots, ondersteunde tools"],
                ["Belangrijkste gidsen en artikelen met cijfers", "Elke 6 maanden", "Nieuwe data, verouderde claims verwijderd"],
                ["Cases", "Elk jaar", "Bijgewerkte resultaten, klant nog actief"],
            ],
            "Een praktisch ritme voor B2B-sites.",
        ),
        h2("Wat telt als een echte update?"),
        cap("Een echte update betekent dat de inhoud is veranderd: nieuwe data, verbeterde feiten, nieuwe onderdelen of verwijderde verouderde stukken, niet alleen een andere datum."),
        ol([
            "<strong>Vervang verouderde cijfers</strong> door de nieuwste data en link naar de bron.",
            "<strong>Voeg toe wat er veranderd is</strong>: nieuwe functies, prijzen, wet- en regelgeving of marktdata.",
            "<strong>Verwijder wat niet meer klopt</strong>, ook producten die niet meer bestaan.",
            "<strong>Toon de datum van de update</strong> zichtbaar op de pagina.",
            "<strong>Laat zoekmachines het weten</strong> via je sitemap en IndexNow, dat Bing ondersteunt.",
        ]),
    ] + faq([
        ("Helpt het om alleen de datum aan te passen?", "Dat is geen echte update en het misleidt lezers. Verander de inhoud en pas daarna de datum aan."),
        ("Geven alle AI-assistenten de voorkeur aan actuele content?", "In de data van Ahrefs doen de meeste dat. ChatGPT had de sterkste voorkeur. Google AI Overviews gaf geen voorkeur aan nieuwere content."),
        ("Moeten we oude blogs bijwerken of nieuwe schrijven?", "Werk artikelen bij die kopers nog helpen. Voeg magere artikelen samen of verwijder ze. Nieuwe artikelen zijn zinvol voor nieuwe vragen."),
        ("Hoeveel pagina's kan een klein team actueel houden?", "Richt je op 10 tot 20 commerciële pagina's. Die elk kwartaal controleren werkt beter dan één keer per jaar alles doorlopen."),
        ("Telt actualiteit ook bij tijdloze onderwerpen?", "Minder, maar ook daar was geciteerde content in het onderzoek van Ahrefs gemiddeld actueler. Controleer de feiten minstens één keer per jaar."),
    ]) + [
        cta("Twijfel je welke pagina's je eerst moet bijwerken? " + QS + " en we bepalen samen de volgorde. Bekijk ook onze " + DIENSTEN + ", of lees " + a("hoe ChatGPT search bronnen kiest", "/nl/blog/hoe-chatgpt-search-bronnen-kiest-b2b/") + "."),
    ],
)


# ------------------------------------------------------------------ press releases
POSTS["persberichten-ai-citaties"] = dict(
    en="do-press-releases-get-cited-by-ai",
    title="Worden persberichten geciteerd door AI? Wat de cijfers laten zien",
    excerpt="Persberichten waren 1,1% van de AI-citaties in de data van Muck Rack uit mei 2026, verdiende media 84%. Wat dat betekent voor B2B-pr-teams.",
    kw="persberichten AI",
    blocks=[
        tldr("Zelden: persberichten waren 1,1% van de AI-citaties in de " + a("analyse van Muck Rack uit mei 2026", "muckrack_may26") + " van meer dan 25 miljoen links, tegen 84% voor verdiende media. Een persbericht helpt als het tot publicaties leidt, niet als losse bron."),
        p("Muck Rack analyseerde meer dan 25 miljoen links die ChatGPT, Claude en Gemini citeerden, verdeeld over 17 sectoren. Verdiende media, waaronder journalistiek, wetenschappelijke bronnen, overheidsbronnen en bronnen van derden, waren goed voor 84% van de citaties. Journalistiek alleen was ongeveer 27% (" + a("Muck Rack, mei 2026", "muckrack_may26") + ")."),
        p("Het aandeel van persberichten schommelde. Het steeg van ongeveer 1,2% in juli 2025 naar 6% in december 2025 en zakte daarna terug naar 1,1% in mei 2026 (" + a("Muck Rack", "muckrack_may26") + "). Persberichten via persbureaus gingen in de tweede helft van 2025 van 0,2% naar 1% van de citaties (" + a("persbericht van GlobeNewswire over data van Muck Rack", "muckrack_dec25") + ")."),
        p("Een persbericht is dus een zwakke bron en een sterke aanleiding. Wat telt, is de aandacht die het oplevert."),
        h2("Welke rol speelt een persbericht in AI-zoekmachines?"),
        cap("Een persbericht is in AI-zoekmachines vooral een aanleiding: het wordt zelf zelden geciteerd, maar de artikelen in vakmedia die erop volgen vaak wel."),
        p("Muck Rack zag dat persberichten ongeveer 3,5 keer vaker verschijnen in antwoorden over trends in een sector dan in antwoorden over de beste aanbieders. Ze helpen het meest als jouw nieuws de trend is."),
        h2("Welke bronnen citeren AI-assistenten in plaats daarvan?"),
        cap("Het favoriete brontype is verdiende media: journalistiek, wetenschap en overheid, encyclopedieën, sociale platforms en content van andere bedrijven, samen 84% van de citaties."),
        table(
            ["Brontype", "Aandeel of bevinding", "Bron"],
            [
                ["Verdiende media totaal", "84% van de citaties", "Muck Rack, mei 2026"],
                ["Journalistiek", "Ongeveer 27% van de citaties", "Muck Rack, mei 2026"],
                ["Persberichten", "1,1% van de citaties", "Muck Rack, mei 2026"],
                ["Betaalde content en advertorials", "0,3% van de citaties", "Muck Rack, mei 2026"],
                ["Technologiejournalistiek", "24,2% van de citaties over technologie; vakbladen wegen zwaarder dan algemeen nieuws", "Muck Rack, mei 2026"],
            ],
            "Waar AI-citaties vandaan komen.",
        ),
        h2("Hoe passen B2B-pr-teams zich aan?"),
        cap("Pr aanpassen voor AI betekent publiciteit meten in de media die assistenten citeren, vakmedia benaderen met data en persberichten zien als één middel van meerdere."),
        ol([
            "<strong>Zoek de media die assistenten citeren</strong> voor je categorie door de vragen van je kopers te stellen. Doe dat ook in het Nederlands, dan zie je welke Nederlandse vakmedia meetellen.",
            "<strong>Benader die media met data</strong>: een benchmark, een enquête of een klantresultaat.",
            "<strong>Houd persberichten feitelijk en gedateerd</strong>, zodat ze overeind blijven als ze worden geciteerd.",
            "<strong>Zet het volledige verhaal op je eigen site</strong>, in HTML en met de cijfers.",
            "<strong>Volg vermeldingen in AI-antwoorden</strong> na elke aankondiging, niet alleen knipsels in de pers.",
        ]),
    ] + faq([
        ("Moeten we stoppen met persberichten?", "Nee. Gebruik ze voor echt nieuws en als basis voor pitches. Verwacht geen AI-citaties van het persbericht zelf."),
        ("Helpen persbureaus voor AI-zichtbaarheid?", "Persberichten via persbureaus waren eind 2025 ongeveer 1% van de citaties. De artikelen die erop volgen, zijn veel meer waard."),
        ("Welke media tellen voor B2B-technologie?", "Muck Rack zag dat vakbladen in antwoorden over technologie zwaarder wegen dan algemeen nieuws. Kijk welke titels assistenten bij jouw prompts citeren."),
        ("Hoe snel pikken AI-assistenten nieuws op?", "Muck Rack zag dat citaties van journalistiek pieken in de eerste maand na publicatie."),
        ("Is betaalde plaatsing de moeite waard?", "Betaalde content en advertorials waren 0,3% van de citaties. Verdiende publiciteit is de betere investering."),
    ]) + [
        cta("Wil je pr die ook je AI-zichtbaarheid helpt? " + QS + " en we brengen in kaart welke media assistenten in jouw categorie citeren. Bekijk ook onze " + DIENSTEN + "."),
    ],
)

# ------------------------------------------------------------------ earned media
POSTS["verdiende-media-ai-zichtbaarheid-b2b"] = dict(
    en="earned-media-ai-visibility-b2b",
    title="Verdiende media voor AI-zichtbaarheid: waar B2B-merken genoemd moeten worden",
    excerpt="Verdiende media waren 84% van de AI-citaties in een onderzoek naar 25 miljoen links. Waar B2B-merken vermeldingen verdienen en hoe je daarvoor pitcht.",
    kw="verdiende media AI-zichtbaarheid",
    blocks=[
        tldr("84% van de AI-citaties komt uit verdiende media (" + a("Muck Rack, mei 2026", "muckrack_may26") + "), en vermeldingen in nieuwsartikelen hangen met 0,67 samen met AI-zichtbaarheid (" + a("Ahrefs, 2026", "ahrefs_mention_types") + "). Verdien vermeldingen in de vakmedia, community's en lijsten die assistenten al citeren voor je categorie."),
        p("Eigen content is een klein deel van wat AI-assistenten citeren. De analyse van Muck Rack van meer dan 25 miljoen links liet zien dat verdiende media 84% van de citaties uitmaakten, en eigen en betaalde content samen ongeveer 16% (" + a("Muck Rack", "muckrack_may26") + ")."),
        p("Vermeldingen gaan samen met zichtbaarheid. In een onderzoek van Ahrefs naar 10.000 merken en 260 miljoen AI-prompts hingen vermeldingen in Reddit-draden met ranglijsten met 0,75 samen met vermeldingen in AI, vermeldingen op webpagina's met 0,70 en vermeldingen in nieuwsartikelen met 0,67 (" + a("Ahrefs", "ahrefs_mention_types") + ")."),
        p("En de journalisten die ertoe doen, staan misschien niet op je lijst. Muck Rack vond gemiddeld maar 2% overlap tussen de journalisten die merken benaderden en de journalisten die AI citeerde (" + a("persbericht van GlobeNewswire over data van Muck Rack", "muckrack_dec25") + ")."),
        p("Voor de Nederlandse markt telt ook de taal van die bronnen. Bij Nederlandstalige vragen kwam 66,4% van de citaties in Copilot uit Nederlandstalige bronnen (" + a("Temso, 2026", "temso_lang") + "). Nederlandse vakmedia horen dus op je lijst, naast internationale titels."),
        h2("Wat zijn verdiende media in AI-zoekmachines?"),
        cap("Verdiende media zijn vermeldingen van je merk waarvoor je niet betaalde en die je niet publiceerde: artikelen, reviews, forumdraden, lijsten en content van andere bedrijven."),
        p("De definitie is bewust breed. Muck Rack telt journalistiek, wetenschappelijke bronnen, overheidsbronnen, encyclopedieën, sociale media en bedrijfsbronnen van derden mee."),
        h2("Waar moeten B2B-merken vermeldingen verdienen?"),
        cap("De beste plek voor een vermelding is een bron die assistenten al citeren bij vragen van je kopers. Stel die vragen en noteer de bronnen."),
        table(
            ["Kanaal", "Samenhang met vermeldingen in AI (Ahrefs, 2026)", "Hoe B2B-merken het verdienen"],
            [
                ["Reddit-draden met ranglijsten", "0,75", "Echte antwoorden in relevante subreddits, nooit reclame"],
                ["Vermeldingen op webpagina's", "0,70", "Partnerpagina's, lijsten, gastbijdragen"],
                ["Nieuwsartikelen", "0,67", "Pitches met data aan vakmedia"],
                ["YouTube", "0,64", "Presentaties, demo's, interviews"],
            ],
            "Samenhang, geen bewijs van oorzaak.",
        ),
        h2("Hoe pitch je voor vermeldingen die AI citeert?"),
        cap("Pitchen voor AI-citaties betekent je richten op de media en auteurs die assistenten al citeren, met data of kennis die ze nergens anders krijgen."),
        ol([
            "<strong>Maak je doellijst op basis van AI-antwoorden</strong>, niet alleen vanuit een mediadatabase.",
            "<strong>Begin met data</strong>: een benchmark, een enquête, geanonimiseerde resultaten.",
            "<strong>Bied een expert aan</strong> met een duidelijke mening en tijd om te reageren.",
            "<strong>Maak feiten makkelijk te controleren</strong> met een bronpagina op je site.",
            "<strong>Volg lijsten en overzichtsartikelen op</strong> voordat ze worden bijgewerkt.",
            "<strong>Volg elke maand je vermeldingen in AI</strong> om te zien welke plaatsingen het verschil maakten.",
        ]),
    ] + faq([
        ("Telt een kleine vermelding mee?", "Ahrefs zag dat vermeldingen gewogen naar verkeer ongeveer even sterk samenhingen als het ruwe aantal vermeldingen. Een vermelding op een kleinere, relevante site telt dus nog steeds."),
        ("Zijn verdiende media belangrijker dan onze eigen site?", "Allebei tellen. Je site levert de feiten. Verdiende media laten assistenten zien dat anderen je vertrouwen."),
        ("Hoeveel vermeldingen hebben we nodig?", "Ahrefs zag dat de opbrengst afneemt: tien keer meer vermeldingen op webpagina's ging samen met ongeveer 3,7 keer meer vermeldingen in AI."),
        ("Moeten B2B-merken op Reddit actief zijn?", "Waar kopers over je categorie praten wel, als echte deelnemer. Reclame wordt verwijderd en schaadt het vertrouwen."),
        ("Kan een bureau dit voor ons doen?", "Ja, zolang het echte verhalen en data pitcht. Massale outreach levert zelden publiciteit op."),
    ]) + [
        cta("Wil je weten waar je concurrenten genoemd worden en jij niet? " + QS + ". Bekijk ook onze " + DIENSTEN + ", of lees " + a("worden persberichten geciteerd door AI?", "/nl/blog/persberichten-ai-citaties/") + "."),
    ],
)


# ------------------------------------------------------------------ EU AI Act
POSTS["ai-verordening-b2b-marketingteams"] = dict(
    en="eu-ai-act-b2b-marketing-teams",
    title="De AI-verordening voor B2B-marketingteams: wat geldt sinds 2 augustus 2026",
    excerpt="Sinds 2 augustus 2026 gelden de transparantieregels van de AI-verordening. Wat dat betekent voor chatbots, AI-beelden, deepfakes en AI-teksten in B2B-marketing.",
    kw="AI-verordening marketing",
    blocks=[
        tldr("Artikel 50 van de Europese AI-verordening (AI Act) geldt sinds 2 augustus 2026. Voor marketingteams betekent dat: chatbots die zeggen dat ze AI zijn, gelabelde deepfakes, leveranciers die AI-output markeren (2 december 2026 voor tools die al op de markt waren) en menselijke redactionele controle. Boetes lopen op tot € 15 miljoen of 3% van de omzet."),
        p("Marketing is in de meeste bedrijven een van de grootste gebruikers van AI. Het eerste deel van de AI-verordening dat het dagelijkse marketingwerk raakt, geldt nu. Artikel 50, de transparantieregel, is van toepassing sinds 2 augustus 2026 (" + a("Europese Commissie", "ec_faq_art50") + ")."),
        p("De Digital Omnibus van juli 2026, Verordening (EU) 2026/1744 (" + a("White &amp; Case", "whitecase_omnibus") + "), schoof de regels voor systemen met een hoog risico uit bijlage III op naar 2 december 2027 (" + a("Orrick", "orrick_omnibus") + "). Artikel 50 schoof niet op. Wie deze verplichtingen overtreedt, riskeert een boete tot € 15 miljoen of 3% van de wereldwijde jaaromzet, afhankelijk van wat hoger is (" + a("Goodwin", "goodwin_art50") + ")."),
        h2("Wat is artikel 50 van de AI-verordening?"),
        cap("Artikel 50 is de transparantieregel van de AI-verordening: mensen moeten weten wanneer ze met een AI-systeem te maken hebben of bepaalde AI-content zien."),
        p("Het artikel bevat vier plichten, verdeeld over aanbieders (die het AI-systeem bouwen of verkopen) en gebruiksverantwoordelijken (de bedrijven die het inzetten). De volledige tekst staat bij de " + a("AI Act Service Desk", "ai_act_art50") + "."),
        ol([
            "<strong>Interactie.</strong> Aanbieders moeten systemen die met mensen praten, zoals chatbots, zo ontwerpen dat gebruikers weten dat ze met AI te maken hebben, tenzij dat duidelijk is.",
            "<strong>Markering.</strong> Aanbieders van systemen die audio, beeld, video of tekst maken, moeten de output machineleesbaar en detecteerbaar markeren. Tools die alleen helpen bij bewerken, zijn uitgezonderd.",
            "<strong>Emotieherkenning.</strong> Gebruiksverantwoordelijken van emotieherkenning of biometrische categorisering moeten de mensen informeren die eraan worden blootgesteld.",
            "<strong>Deepfakes en tekst over zaken van algemeen belang.</strong> Gebruiksverantwoordelijken moeten deepfakes melden. Dat geldt ook voor AI-tekst die het publiek informeert over zaken van algemeen belang, tenzij een mens die tekst heeft nagekeken onder redactionele verantwoordelijkheid.",
            "<strong>Timing.</strong> Alle vier gelden vanaf 2 augustus 2026, met één overgangsperiode voor markering door bestaande tools.",
        ]),
        h2("Wat veranderde de Digital Omnibus?"),
        cap("De Digital Omnibus is de wijziging van juli 2026 die de meeste regels voor AI met een hoog risico uitstelde, maar artikel 50 liet staan."),
        ul([
            "Ze trad in werking op 27 juli 2026.",
            "Systemen met een hoog risico uit bijlage III, zoals tools voor werving en onderwijs, vallen er nu vanaf 2 december 2027 onder. Systemen uit bijlage I in gereguleerde producten volgen op 2 augustus 2028.",
            "Generatieve tools die al vóór 2 augustus 2026 op de markt waren, hebben tot 2 december 2026 om hun output te markeren.",
            "De plicht tot AI-geletterdheid in artikel 4 is afgezwakt: organisaties moeten maatregelen nemen die AI-geletterdheid ondersteunen, maar hoeven niet elke medewerker te certificeren (" + a("Lewis Silkin", "lewis_silkin") + ").",
        ]),
        h2("Welke marketingactiviteiten leiden tot verplichtingen?"),
        cap("Een verplichting onder artikel 50 is er bij vier soorten gebruik: websitechatbots, deepfakes van echte mensen, ongecontroleerde AI-tekst over algemeen belang en generatieve tools."),
        table(
            ["Gebruik in marketing", "Wat de regel vraagt", "Wie verantwoordelijk is", "Geldt vanaf"],
            [
                ["Chatbot op je website of in berichten", "Mensen moeten weten dat ze met AI praten, tenzij dat duidelijk is", "Aanbieder, vaak je leverancier; controleer je eigen instelling", "2 augustus 2026"],
                ["AI-beelden, -video, -audio of -tekst", "Output machineleesbaar en detecteerbaar gemarkeerd", "Aanbieder van de generatieve tool", "2 augustus 2026; 2 december 2026 voor tools die al op de markt waren"],
                ["Deepfake van een echte persoon, plek of gebeurtenis", "Melden dat het met AI is gemaakt of bewerkt", "Gebruiksverantwoordelijke: je eigen bedrijf", "2 augustus 2026"],
                ["AI-tekst over zaken van algemeen belang", "Melden dat AI de tekst maakte, tenzij er menselijke controle en redactionele verantwoordelijkheid is", "Gebruiksverantwoordelijke: je eigen bedrijf", "2 augustus 2026"],
                ["Emotieherkenning in advertentie- of UX-tests", "De betrokken mensen informeren", "Gebruiksverantwoordelijke: je eigen bedrijf", "2 augustus 2026"],
            ],
            "Samenvatting van artikel 50 voor marketing. Algemene informatie, geen juridisch advies.",
        ),
        h2("Moet je AI-blogs volgens de AI-verordening labelen?"),
        cap("Een AI-melding bij een blog is alleen verplicht als de tekst het publiek informeert over zaken van algemeen belang en niemand hem redactioneel heeft nagekeken."),
        p("De FAQ van de Europese Commissie omschrijft zaken van algemeen belang als consumentenveiligheid en economische, financiële, politieke, wetenschappelijke of culturele ontwikkelingen die onderwerp van publiek debat kunnen zijn (" + a("Europese Commissie", "ec_faq_art50") + "). Een gewone B2B-handleiding of productpagina is meestal niet zo'n tekst. Marktcommentaar en onderzoeksresultaten kunnen dat wel zijn."),
        p("De uitzondering heeft twee voorwaarden: de tekst is door een mens nagekeken of redactioneel gecontroleerd, en een persoon of bedrijf draagt de redactionele verantwoordelijkheid voor de publicatie. Benoem in de praktijk een redacteur en houd een kort logboek van controles bij. Google verwacht dezelfde discipline voor zoekresultaten: alle AI-content hoort door een mens op feiten te worden gecontroleerd en nagelezen (" + a("Google Search Central", "google_genai") + ")."),
        h2("Hoe bereidt een marketingteam zich voor?"),
        cap("Een marketingteam voorbereiden betekent elke AI-tool en elk gebruik in kaart brengen, de markering door leveranciers controleren, meldingen toevoegen waar nodig en een redacteur aanwijzen."),
        ol([
            "Maak een lijst van elke AI-tool die het team gebruikt, ook AI-functies in software waarvoor je al betaalt.",
            "Geef aan welk gebruik klanten of het publiek bereikt: chatbots, beelden, video, stem en gepubliceerde tekst.",
            "Vraag elke leverancier van een generatieve tool hoe die output markeert en of hij de deadline van 2 december 2026 haalt.",
            "Zet een duidelijke zin als \"Je chat met een AI-assistent\" bij chatbots als de leverancier die niet toont.",
            "Maak een regel voor deepfakes: geen nagemaakte echte mensen, plekken of gebeurtenissen zonder melding.",
            "Laat AI-tekst voor het publiek via een vaste redacteur lopen en houd een eenvoudig logboek bij.",
            "Leg de AI-training die je het team geeft vast als bewijs van maatregelen voor AI-geletterdheid onder artikel 4.",
        ]),
    ] + faq([
        ("Geldt artikel 50 ook voor bedrijven buiten de EU?", "Ja, als hun AI-output in de EU wordt gebruikt. De AI-verordening geldt voor aanbieders en gebruiksverantwoordelijken in derde landen als de output van het AI-systeem in de Unie wordt gebruikt."),
        ("Moeten we elke AI-afbeelding labelen?", "In de meeste gevallen niet met een zichtbaar label. De aanbieder van de generatieve tool moet de output machineleesbaar markeren. Je eigen bedrijf moet deepfakes melden: content die lijkt op echte mensen, plekken of gebeurtenissen en ten onrechte echt kan lijken."),
        ("Wat zijn de boetes voor overtreding van artikel 50?", "Tot € 15 miljoen of 3% van de wereldwijde jaaromzet, afhankelijk van wat hoger is. Voor mkb-bedrijven en start-ups geldt het laagste van de twee."),
        ("Wanneer gelden de regels voor hoog risico?", "Vanaf 2 december 2027 voor systemen uit bijlage III, zoals tools voor werving en onderwijs, en vanaf 2 augustus 2028 voor systemen uit bijlage I in gereguleerde producten. Gewone marketingtools staan niet op deze lijsten."),
        ("Is training in AI-geletterdheid verplicht?", "Artikel 4 vraagt aanbieders en gebruiksverantwoordelijken om maatregelen te nemen die AI-geletterdheid ondersteunen. Sinds de Omnibus hoef je niet elke medewerker te certificeren. Trainingen en bewustwordingssessies vastleggen is de praktische aanpak."),
        ("Valt AI-advertentietekst onder artikel 50?", "Advertentietekst is zelden tekst die het publiek informeert over zaken van algemeen belang, dus die meldplicht geldt meestal niet. Deepfakes in advertenties moet je altijd melden."),
    ]) + [
        p("Dit artikel is algemene informatie, geen juridisch advies. Laat je eigen verplichtingen controleren door een jurist."),
        cta("Booming Venture helpt B2B-marketingteams AI in te zetten voor content, zoeken en campagnes, met werkwijzen die binnen deze regels passen. Voor compliance en training in AI-geletterdheid adviseert onze zusterpraktijk " + a("VerantwoordAI", "https://verantwoordai.nl/") + " grote organisaties en de publieke sector. " + QS + ", of bekijk onze " + DIENSTEN + "."),
    ],
)

# ------------------------------------------------------------------ AI content and Google
POSTS["ai-content-google-spambeleid"] = dict(
    en="ai-written-content-google-spam-policies",
    title="AI-content en Google: wat het spambeleid echt toestaat",
    excerpt="Google verbiedt AI-content niet, maar straft massa zonder toegevoegde waarde. Wat het spambeleid en de spamupdate van juni 2026 betekenen voor B2B.",
    kw="AI-content Google",
    blocks=[
        tldr(a("Het spambeleid van Google", "google_spam") + " noemt het inzetten van generatieve AI om veel pagina's te maken zonder toegevoegde waarde misbruik van geschaalde content (scaled content abuse). Content gemaakt met hulp van AI mag. Volume zonder eigen waarde is het risico. De spamupdate van juni 2026 werd in twee dagen wereldwijd uitgerold (" + a("Search Engine Roundtable", "ser_spam_june") + ")."),
        p("Veel B2B-marketingteams laten AI nu concepten schrijven, en veel teams publiceren meer dan ooit. De lijn van Google staat in de eigen documentatie: het gaat erom of pagina's mensen helpen, niet hoe ze zijn gemaakt. De richtlijnen voor content met generatieve AI vragen site-eigenaren om de juistheid te controleren en waarde toe te voegen (" + a("Google Search Central", "google_genai") + ")."),
        p("De menselijke kwaliteitsbeoordelaars van Google volgen dezelfde logica. Sinds de update van hun richtlijnen in januari 2025 krijgen pagina's die met weinig tot geen moeite, originaliteit of toegevoegde waarde zijn gemaakt de laagste beoordeling. Geautomatiseerde tools gebruiken om met weinig moeite veel pagina's te maken, wordt daarbij als voorbeeld genoemd (" + a("Search Engine Roundtable", "ser_raters") + ")."),
        p("De handhaving komt in golven. De spamupdate van juni 2026 begon op 24 juni en was op 26 juni klaar. Hij gold voor alle talen, dus ook voor Nederlandstalige sites, en Google zei dat herstel vele maanden kan duren (" + a("Search Engine Roundtable", "ser_spam_june") + ")."),
        h2("Straft Google content die met AI is gemaakt?"),
        cap("Het standpunt van Google is dat AI niet het probleem is: massaal gemaakte content om te ranken, zonder toegevoegde waarde, overtreedt het spambeleid."),
        p("Een artikel met een AI-concept dat een expert heeft gecontroleerd, aangevuld met echte voorbeelden en actueel gehouden, wordt beoordeeld op dezelfde kwaliteitssignalen als elk ander artikel. Honderd AI-pagina's die herhalen wat al rankt, zijn waar het beleid op mikt."),
        h2("Wat is misbruik van geschaalde content?"),
        cap("Misbruik van geschaalde content is Googles term voor massaal pagina's maken om rankings te beïnvloeden, met AI, scraping of mensen, zonder gebruikers te helpen."),
        table(
            ["Signaal", "Lager risico", "Hoger risico"],
            [
                ["Publicatietempo", "Wat je experts kunnen nakijken", "Tientallen artikelen per week van een klein team"],
                ["Onderwerpen", "Vragen die je kopers stellen en die je kent", "Elk onderwerp met zoekvolume"],
                ["Feiten", "Elk getal heeft een bron", "Cijfers zonder bron of verzonnen cijfers"],
                ["Ervaring", "Je eigen voorbeelden, data en meningen", "Herschreven samenvattingen van wat al bovenaan staat"],
                ["Auteurschap", "Auteur met naam en relevante achtergrond", "Geen auteur, of alleen de merknaam"],
                ["Onderhoud", "Pagina's bijgewerkt of verwijderd als ze verouderd zijn", "Eén keer gepubliceerd, daarna nooit meer aangeraakt"],
            ],
            "Patronen die de kans vergroten of verkleinen dat content als geschaald wordt gezien.",
        ),
        h2("Hoe beoordelen beoordelaars pagina's die met AI zijn gemaakt?"),
        cap("Het oordeel van beoordelaars betekent dat mensen pagina's scoren op moeite, originaliteit en toegevoegde waarde. Gekopieerde of automatische pagina's met weinig daarvan scoren het laagst."),
        p("Scores van beoordelaars veranderen de rankings niet direct. Google gebruikt ze om te testen of zijn rankingsystemen werken. Maar ze laten zien wat Google die systemen wil laten belonen. De richtlijnen zeggen ook dat generatieve AI kan helpen bij werk van hoge kwaliteit. De tool zelf bepaalt de score niet."),
        h2("Hoe gebruikt een B2B-team AI veilig voor content?"),
        cap("AI veilig gebruiken betekent dat een mens eigenaar is van elke pagina: AI maakt het concept, een expert controleert, vult aan en beslist."),
        ol([
            "<strong>Begin bij een vraag van een koper</strong>, niet bij een lijst met zoekwoorden.",
            "<strong>Schrijf de briefing vanuit eigen ervaring</strong>: een project, een getal, een mening die alleen jouw team heeft.",
            "<strong>Laat AI het concept en het vooronderzoek doen</strong>: structuur, definities en bronnen.",
            "<strong>Laat een expert redigeren</strong> en voorbeelden, data en oordeel toevoegen.",
            "<strong>Controleer elk getal</strong> bij de oorspronkelijke bron en link ernaar.",
            "<strong>Publiceer onder een auteur met naam</strong> en relevante ervaring.",
            "<strong>Kies een tempo dat je kunt nakijken</strong>, en werk artikelen bij of verwijder ze als ze niemand meer helpen.",
        ]),
        p("Publiceer je in de EU, dan dekt diezelfde redactionele controle je ook onder de AI-verordening. Ons artikel over " + a("de AI-verordening voor marketingteams", "/nl/blog/ai-verordening-b2b-marketingteams/") + " legt uit wanneer AI-tekst een label nodig heeft."),
    ] + faq([
        ("Moeten we AI-blogs labelen?", "Niet voor het spambeleid van Google. Onder de AI-verordening is een label alleen nodig voor AI-tekst over zaken van algemeen belang die geen mens onder redactionele verantwoordelijkheid heeft nagekeken."),
        ("Hoeveel artikelen per week is veilig?", "Google noemt geen aantal. Publiceer wat je experts goed kunnen nakijken. Kwaliteit en passende onderwerpen tellen, niet volume."),
        ("Kan een site herstellen na een spamupdate?", "Ja, maar volgens Google kan dat vele maanden duren. Verbeter of verwijder pagina's met weinig waarde en blijf nuttige content publiceren."),
        ("Moeten we oude AI-artikelen verwijderen?", "Bekijk ze eerst. Verbeter artikelen die je kopers helpen, voeg magere artikelen samen en verwijder of noindex wat niets toevoegt."),
        ("Gelden de richtlijnen van Google ook voor AI-antwoorden?", "AI Overviews en AI Mode putten uit de index van Google. Pagina's die in de rankings dalen, verliezen daar dus ook zichtbaarheid."),
    ]) + [
        cta("Gebruik je AI voor content en wil je dat het overeind blijft in Google en AI-zoekmachines? " + QS + " en we bekijken je werkwijze. Bekijk ook onze " + DIENSTEN + "."),
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
