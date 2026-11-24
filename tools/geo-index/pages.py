"""Page content for the B2B GEO Index (EN /b2b-geo-index/, NL /nl/b2b-geo-index/).

    python3 tools/geo-index/pages.py OUT_DIR [EN_ID NL_PARENT_ID]

The ranking itself is the theme block booming-venture/geo-index (server
rendered, measured weekly by the site). This file writes the text around it,
to the geo-writer rules: H1 with the entity, question H2s with answer
capsules, sources in the first 30%, a table, FAQ and a CTA.
Writes en.json and nl.json (REST bodies for POST /wp/v2/pages).
"""
import importlib.util
import json
import pathlib
import sys

ROOT = pathlib.Path(__file__).resolve().parents[2]
spec = importlib.util.spec_from_file_location("posts_nl", ROOT / "tools/blog/posts_nl.py")
B = importlib.util.module_from_spec(spec)
spec.loader.exec_module(B)
a, p, h2, h3, cap, table, cta, text_of = B.a, B.p, B.h2, B.h3, B.cap, B.table, B.cta, B.text_of


def hero(title, sub):
    return (
        '<!-- wp:group {"tagName":"section","className":"bv-page-hero","gradient":"hero-backdrop","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|40"}}},"layout":{"type":"constrained"}} -->\n'
        '<section class="wp-block-group bv-page-hero has-hero-backdrop-gradient-background has-background" style="padding-top:var(--wp--preset--spacing--70);padding-bottom:var(--wp--preset--spacing--40)">\n'
        '<!-- wp:heading {"textAlign":"center","level":1,"fontSize":"6xl"} -->\n'
        f'<h1 class="wp-block-heading has-text-align-center has-6-xl-font-size">{title}</h1>\n'
        "<!-- /wp:heading -->\n"
        '<!-- wp:paragraph {"align":"center","fontSize":"xl","textColor":"muted"} -->\n'
        f'<p class="has-text-align-center has-muted-color has-text-color has-xl-font-size">{sub}</p>\n'
        "<!-- /wp:paragraph -->\n</section>\n<!-- /wp:group -->"
    )


def block(lst):
    return f'<!-- wp:booming-venture/geo-index {{"list":"{lst}","align":"wide"}} /-->'


def faq(title, pairs):
    out = [h2(title)]
    for q, ans in pairs:
        out += [h3(q), p(ans)]
    return out


EN = [
    hero('B2B GEO Index: <span class="bv-gradient-text">who is ready for AI search?</span>',
         "200 B2B companies, measured every Monday: how often ChatGPT, Claude, Gemini and Perplexity name them, and whether their websites are ready for AI search."),
    p("Buyers now meet vendors in AI answers first. " + a("Forrester", "forrester") + " reports that 94% of business buyers use AI in their buying process. Whether a company shows up depends partly on things it controls on its own website."),
    p("Some of those things are easy to check and often missing. In " + a("Vercel's analysis", "vercel") + ", none of the major AI crawlers rendered JavaScript, so text that only appears after scripts run stays invisible to them. And " + a("OpenAI", "openai_bots") + " says sites that block OAI-SearchBot will not appear in ChatGPT search answers."),
    p("Language matters as well. For Dutch-language questions, 81.2% of the sources in Google AI Overviews were Dutch-language pages (" + a("Temso, 2026", "temso_lang") + "). That is why the Dutch list checks for a Dutch version."),
    h2("What is the B2B GEO Index?"),
    cap("The B2B GEO Index is a weekly ranking of 200 B2B companies by how often AI assistants name them and how ready their websites are."),
    block("intl"),
    h2("How is the score calculated?"),
    cap("The GEO score is a number from 0 to 100: half comes from how often AI assistants name the company, half from seven website checks."),
    p("<strong>AI score.</strong> Every Monday the site asks one buyer question per category to ChatGPT, Claude, Gemini and Perplexity, through their official APIs with web search switched on. Dutch companies get the question in Dutch, international companies in English. The AI score is the share of answers that name the company (80%) or cite its website (20%)."),
    p("<strong>Website score.</strong> Seven checks on the company's own website, with these points:"),
    table(
        ["Check", "Points", "What we measure", "Why it matters"],
        [
            ["AI crawlers allowed", "30", "robots.txt rules for OAI-SearchBot (6), ChatGPT-User (3), GPTBot (3), Claude-SearchBot (3), ClaudeBot (2), PerplexityBot (5), Google-Extended (3) and Bingbot (5), for the homepage and an inner page", "Blocked crawlers cannot cite you. " + a("OpenAI", "openai_bots") + " confirms this for ChatGPT search."],
            ["Text without JavaScript", "15", "Words of visible text in the homepage HTML: 300 or more for full points, 150 or more for half", "AI crawlers did not render JavaScript in " + a("Vercel's data", "vercel") + "."],
            ["Structured data", "20", "Organization schema (8), sameAs links (4), WebSite (4), other types such as Product, Service or FAQPage (4)", "Helps search engines and assistants understand the page (" + a("Google", "google_sd_intro") + ", " + a("Bing via Search Engine Land", "sel_canel_schema") + ")."],
            ["Entity", "15", "A Wikidata item with the matching official website (7) and a Wikipedia article in English or Dutch (8)", "Wikipedia is ChatGPT's most cited domain (" + a("Muck Rack, May 2026", "muckrack_may26") + ")."],
            ["Freshness", "10", "Newest lastmod date in the sitemap: within 30 days (10), 90 days (6) or a year (3)", "AI-cited content was 25.7% fresher than Google's organic results (" + a("Ahrefs", "ahrefs_fresh") + ")."],
            ["Language", "8", "Dutch list: a Dutch version (html lang or hreflang). International list: two or more language versions", "Assistants mostly cite sources in the language of the question (" + a("Temso, 2026", "temso_lang") + ")."],
            ["llms.txt", "2", "A text file at /llms.txt", "Low weight: Ahrefs data showed 97% of llms.txt files got no requests (" + a("Search Engine Journal", "sej_ahrefs_llms") + ")."],
        ],
        "The seven checks and their points. Every company is measured the same way, every Monday.",
    ),
    h2("Which companies are in the index?"),
    cap("The index is a fixed set of 100 Dutch B2B companies in eight sectors and 100 international B2B software companies in ten categories."),
    p("We picked well-known companies with a B2B model. For the Dutch list, the head office is in the Netherlands. Companies cannot pay for a place or a better score. Missing a company? Email " + a("info@boomingventure.com", "mailto:info@boomingventure.com") + " and we add it to the next measurement."),
] + faq("Frequently asked questions", [
    ("How often is the index updated?", "Every Monday. The site measures all 200 companies again and shows the change against the previous week."),
    ("Can I check my own company?", "Yes. Enter your domain in the check above the methodology. You get the same score as the companies in the index."),
    ("Which questions do you ask the AI assistants?", "One question per category, such as: What is the best CRM software for B2B companies? Name a few providers and say briefly why. The wording alternates each week, so no score depends on one phrasing."),
    ("Do the answers match what a user sees in ChatGPT?", "Every category gets the same question in the same way every week, so companies are compared fairly. Individual users can see other answers, depending on their location, account and model."),
    ("Why does blocking GPTBot cost fewer points than blocking OAI-SearchBot?", "OAI-SearchBot decides whether a site can appear in ChatGPT search. GPTBot collects training data, and OpenAI treats the two settings separately."),
    ("Can a company pay for a higher place?", "No. Scores come only from the measurements."),
    ("Why is Wikipedia part of the score?", "Muck Rack found Wikipedia to be ChatGPT's most cited domain. A Wikidata item and a Wikipedia article help AI systems recognize a company as an entity."),
]) + [
    cta("Want to raise your score and show up in AI answers? " + a("Request a free 15-minute Boardroom Quickscan", "/boardroom-quickscan/") + ". See our " + a("services", "/services/") + ", or read " + a("how ChatGPT search picks sources", "/how-chatgpt-search-picks-sources-b2b/") + "."),
]

NL = [
    hero('B2B GEO Index: <span class="bv-gradient-text">wie is klaar voor AI-zoekmachines?</span>',
         "200 B2B-bedrijven, elke maandag gemeten: hoe vaak ChatGPT, Claude, Gemini en Perplexity ze noemen en of hun websites klaar zijn voor AI-zoekmachines."),
    p("Kopers zien leveranciers nu eerst in AI-antwoorden. Volgens " + a("Forrester", "forrester") + " gebruikt 94% van de zakelijke kopers AI tijdens het aankoopproces. Of een bedrijf in die antwoorden verschijnt, hangt deels af van dingen die het op zijn eigen website regelt."),
    p("Een deel daarvan is makkelijk te controleren en ontbreekt vaak. In de " + a("analyse van Vercel", "vercel") + " renderde geen van de grote AI-crawlers JavaScript. Tekst die pas na een script verschijnt, zien ze dus niet. En volgens " + a("OpenAI", "openai_bots") + " verschijnen sites die OAI-SearchBot blokkeren niet in de antwoorden van ChatGPT search."),
    p("Ook de taal telt. Bij Nederlandstalige vragen kwam 81,2% van de bronnen in Google AI Overviews uit Nederlandstalige pagina's (" + a("Temso, 2026", "temso_lang") + "). Daarom kijkt de Nederlandse lijst of er een Nederlandse versie is."),
    h2("Wat is de B2B GEO Index?"),
    cap("De B2B GEO Index is een wekelijkse ranglijst van 200 B2B-bedrijven op hoe vaak AI-assistenten ze noemen en hoe klaar hun websites zijn."),
    block("nl"),
    h2("Hoe wordt de score berekend?"),
    cap("De GEO-score is een getal van 0 tot 100: de helft komt uit hoe vaak AI-assistenten het bedrijf noemen, de helft uit zeven websitechecks."),
    p("<strong>AI-score.</strong> Elke maandag stelt de site per categorie één koopvraag aan ChatGPT, Claude, Gemini en Perplexity, via hun officiële API's met zoeken op internet aan. Nederlandse bedrijven krijgen de vraag in het Nederlands, internationale bedrijven in het Engels. De AI-score is het aandeel antwoorden dat het bedrijf noemt (80%) of naar de website verwijst (20%)."),
    p("<strong>Websitescore.</strong> Zeven checks op de eigen website van het bedrijf, met deze punten:"),
    table(
        ["Check", "Punten", "Wat we meten", "Waarom het telt"],
        [
            ["AI-crawlers toegestaan", "30", "Regels in robots.txt voor OAI-SearchBot (6), ChatGPT-User (3), GPTBot (3), Claude-SearchBot (3), ClaudeBot (2), PerplexityBot (5), Google-Extended (3) en Bingbot (5), voor de homepage en een onderliggende pagina", "Geblokkeerde crawlers kunnen je niet citeren. " + a("OpenAI", "openai_bots") + " bevestigt dat voor ChatGPT search."],
            ["Tekst zonder JavaScript", "15", "Aantal woorden zichtbare tekst in de HTML van de homepage: 300 of meer voor alle punten, 150 of meer voor de helft", "AI-crawlers renderden geen JavaScript in de " + a("data van Vercel", "vercel") + "."],
            ["Gestructureerde data", "20", "Organization-schema (8), sameAs-links (4), WebSite (4), andere types zoals Product, Service of FAQPage (4)", "Helpt zoekmachines en assistenten de pagina te begrijpen (" + a("Google", "google_sd_intro") + ", " + a("Bing via Search Engine Land", "sel_canel_schema") + ")."],
            ["Entiteit", "15", "Een Wikidata-item met de juiste officiële website (7) en een Wikipedia-artikel in het Engels of Nederlands (8)", "Wikipedia is het meest geciteerde domein in ChatGPT (" + a("Muck Rack, mei 2026", "muckrack_may26") + ")."],
            ["Actualiteit", "10", "Nieuwste lastmod-datum in de sitemap: binnen 30 dagen (10), 90 dagen (6) of een jaar (3)", "Content die AI citeerde, was 25,7% actueler dan de organische resultaten van Google (" + a("Ahrefs", "ahrefs_fresh") + ")."],
            ["Taal", "8", "Lijst Nederland: een Nederlandse versie (html lang of hreflang). Internationale lijst: twee of meer taalversies", "Assistenten citeren vooral bronnen in de taal van de vraag (" + a("Temso, 2026", "temso_lang") + ")."],
            ["llms.txt", "2", "Een tekstbestand op /llms.txt", "Weinig gewicht: in data van Ahrefs kreeg 97% van de llms.txt-bestanden geen enkel verzoek (" + a("Search Engine Journal", "sej_ahrefs_llms") + ")."],
        ],
        "De zeven checks en hun punten. Elk bedrijf wordt elke maandag op dezelfde manier gemeten.",
    ),
    h2("Welke bedrijven staan in de index?"),
    cap("De index is een vaste groep van 100 Nederlandse B2B-bedrijven in acht sectoren en 100 internationale B2B-softwarebedrijven in tien categorieën."),
    p("We kozen bekende bedrijven met een B2B-model. Voor de Nederlandse lijst zit het hoofdkantoor in Nederland. Bedrijven kunnen niet betalen voor een plek of een hogere score. Mis je een bedrijf? Mail " + a("info@boomingventure.com", "mailto:info@boomingventure.com") + ", dan nemen we het mee in de volgende meting."),
] + faq("Veelgestelde vragen", [
    ("Hoe vaak wordt de index bijgewerkt?", "Elke maandag. De site meet alle 200 bedrijven opnieuw en toont het verschil met de week ervoor."),
    ("Kan ik mijn eigen bedrijf checken?", "Ja. Vul je domein in bij de check boven de uitleg van de methode. Je krijgt dezelfde score als de bedrijven in de index."),
    ("Welke vragen stellen jullie aan de AI-assistenten?", "Eén vraag per categorie, zoals: Wat is de beste boekhoudsoftware voor het mkb in Nederland? Noem een paar aanbieders en zeg kort waarom. De formulering wisselt per week, zodat geen score van één formulering afhangt."),
    ("Komen de antwoorden overeen met wat een gebruiker in ChatGPT ziet?", "Elke categorie krijgt elke week dezelfde vraag op dezelfde manier, dus bedrijven worden eerlijk vergeleken. Een individuele gebruiker kan andere antwoorden zien, afhankelijk van locatie, account en model."),
    ("Waarom kost het blokkeren van GPTBot minder punten dan het blokkeren van OAI-SearchBot?", "OAI-SearchBot bepaalt of een site in ChatGPT search kan verschijnen. GPTBot verzamelt trainingsdata, en OpenAI behandelt die twee instellingen apart."),
    ("Kan een bedrijf betalen voor een hogere plek?", "Nee. Scores komen alleen uit de metingen."),
    ("Waarom telt Wikipedia mee in de score?", "Muck Rack zag dat Wikipedia het meest geciteerde domein in ChatGPT is. Een Wikidata-item en een Wikipedia-artikel helpen AI-systemen een bedrijf als entiteit te herkennen."),
]) + [
    cta("Wil je je score verhogen en in AI-antwoorden verschijnen? " + a("Vraag een gratis Boardroom Quickscan van 15 minuten aan", "/nl/boardroom-quickscan/") + ". Bekijk ook onze " + a("diensten", "/nl/diensten/") + ", of lees " + a("hoe ChatGPT search bronnen kiest", "/nl/blog/hoe-chatgpt-search-bronnen-kiest-b2b/") + "."),
]

SEO = {
    "en": ("B2B GEO Index: 200 companies ranked for AI search", "Weekly ranking of 200 B2B companies: how often ChatGPT, Claude, Gemini and Perplexity name them, plus seven website checks. Check your own domain."),
    "nl": ("B2B GEO Index: 200 bedrijven gerangschikt op AI-zoekmachines", "Wekelijkse ranglijst van 200 B2B-bedrijven: hoe vaak ChatGPT, Claude, Gemini en Perplexity ze noemen, plus zeven websitechecks. Check je eigen domein."),
}


def check(blocks, lang):
    content = "\n".join(blocks)
    issues = []
    if "—" in content or "–" in content:
        issues.append("dash")
    for c in __import__("re").findall(r'class="bv-capsule[^"]*"[^>]*>(.*?)</p>', content, __import__("re").S):
        n = len(text_of(c).split())
        if not 20 <= n <= 25:
            issues.append(f"capsule {n}: {text_of(c)[:50]}")
    cut = content[: int(len(content) * 0.30)]
    links = len(__import__("re").findall(r'href="https?://(?!boomingventure)', cut))
    if links < 3:
        issues.append(f"links in first 30%: {links}")
    for k, (t, d) in SEO.items():
        assert len(d) <= 155, (k, len(d))
    return issues


def main(out, en_id=None, parent=None):
    out = pathlib.Path(out)
    out.mkdir(parents=True, exist_ok=True)
    for lang, blocks in (("en", EN), ("nl", NL)):
        issues = check(blocks, lang)
        print(lang, "issues:", issues or "none")
        body = {
            "title": "B2B GEO Index", "slug": "b2b-geo-index", "status": "private", "template": "page-landing",
            "content": "\n".join(blocks), "comment_status": "closed",
            "meta": {"_yoast_wpseo_title": SEO[lang][0], "_yoast_wpseo_metadesc": SEO[lang][1]},
        }
        if lang == "nl":
            body["meta"].update({"_bv_lang": "nl", "_bv_translation": int(en_id) if en_id else 0})
            body["parent"] = int(parent) if parent else "__NL_PARENT__"
        (out / f"{lang}.json").write_text(json.dumps(body, ensure_ascii=False), encoding="utf-8")


if __name__ == "__main__":
    main(*(sys.argv[1:] or ["build/geo-index"]))
