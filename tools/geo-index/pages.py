"""Page content for the B2B GEO Index (EN /b2b-geo-index/, NL /nl/b2b-geo-index/).

    python3 tools/geo-index/pages.py OUT_DIR [EN_ID NL_PARENT_ID]

The ranking itself is the theme block booming-venture/geo-index (server
rendered, measured monthly by the site). This file writes the text around it,
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


def _attrs(d):
    return (" " + json.dumps(d, ensure_ascii=False, separators=(",", ":"))) if d else ""


def para(text, cls=""):
    """Paragraph block, optionally with a class."""
    if not cls:
        return p(text)
    return f'<!-- wp:paragraph {{"className":"{cls}"}} -->\n<p class="{cls}">{text}</p>\n<!-- /wp:paragraph -->'


def head3(text, cls=""):
    if not cls:
        return h3(text)
    return (f'<!-- wp:heading {{"level":3,"className":"{cls}"}} -->\n'
            f'<h3 class="wp-block-heading {cls}">{text}</h3>\n<!-- /wp:heading -->')


def group(inner, cls, align=None, anchor=None, tag="div", gradient=None):
    """Plain (flow) group with our own class; the layout lives in theme.css."""
    a = {}
    if tag != "div":
        a["tagName"] = tag
    if anchor:
        a["anchor"] = anchor
    a["className"] = cls
    if gradient:
        a["gradient"] = gradient
    if align:
        a["align"] = align
    classes = ["wp-block-group"] + ([f"align{align}"] if align else []) + [cls]
    if gradient:
        classes += [f"has-{gradient}-gradient-background", "has-background"]
    idattr = f' id="{anchor}"' if anchor else ""
    body = "\n".join(inner) if isinstance(inner, list) else inner
    return (f"<!-- wp:group{_attrs(a)} -->\n<{tag} class=\"{' '.join(classes)}\"{idattr}>\n{body}\n</{tag}>\n<!-- /wp:group -->")


def buttons(items, cls=""):
    """items: (text, href, style) with style '', 'outline' or a class."""
    out = []
    for text, href, style in items:
        c = "is-style-outline" if style == "outline" else style
        out.append(f'<!-- wp:button{_attrs({"className": c} if c else {})} -->\n'
                   f'<div class="wp-block-button{(" " + c) if c else ""}"><a class="wp-block-button__link wp-element-button" href="{href}">{text}</a></div>\n'
                   "<!-- /wp:button -->")
    a = ({"className": cls} if cls else {}) | {"layout": {"type": "flex", "justifyContent": "center"}}
    return (f"<!-- wp:buttons{_attrs(a)} -->\n<div class=\"wp-block-buttons{(' ' + cls) if cls else ''}\">"
            + "\n".join(out) + "</div>\n<!-- /wp:buttons -->")


def hero(eyebrow, title, sub, facts, jump):
    fact_blocks = [group([para(n, "bv-gip-fact__num"), para(l, "bv-gip-fact__label")], "bv-gip-fact") for n, l in facts]
    return (
        '<!-- wp:group {"tagName":"section","className":"bv-page-hero bv-gip-hero","gradient":"hero-backdrop","align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained"}} -->\n'
        '<section class="wp-block-group alignfull bv-page-hero bv-gip-hero has-hero-backdrop-gradient-background has-background" style="padding-top:var(--wp--preset--spacing--70);padding-bottom:var(--wp--preset--spacing--60)">\n'
        + para(eyebrow, "bv-gip-eyebrow") + "\n"
        + '<!-- wp:heading {"textAlign":"center","level":1,"fontSize":"6xl"} -->\n'
        f'<h1 class="wp-block-heading has-text-align-center has-6-xl-font-size">{title}</h1>\n'
        "<!-- /wp:heading -->\n"
        + para(sub, "bv-gip-sub") + "\n"
        + group(fact_blocks, "bv-gip-facts") + "\n"
        + buttons(jump, "bv-gip-jump")
        + "\n</section>\n<!-- /wp:group -->"
    )


def h2a(text, anchor):
    """H2 with an anchor, so the ranking can link to the method."""
    return (f'<!-- wp:heading {{"anchor":"{anchor}"}} -->\n'
            f'<h2 class="wp-block-heading" id="{anchor}">{text}</h2>\n<!-- /wp:heading -->')


def sec_head(eyebrow, title=None, capsule=None, lead=None, anchor=None):
    inner = [para(eyebrow, "bv-gip-eyebrow")]
    if title:
        inner.append(h2a(title, anchor) if anchor else h2(title))
    if capsule:
        inner.append(cap(capsule))
    if lead:
        inner.append(para(lead, "bv-gip-lead"))
    return group(inner, "bv-gip-head")


def section(inner, anchor=None, extra=""):
    return group(inner, ("bv-gip-sec " + extra).strip(), align="wide", anchor=anchor)


def grid(cards, cols):
    return group(cards, f"bv-gip-grid bv-gip-grid--{cols}")


def card(inner, extra=""):
    return group(inner, ("bv-gip-card " + extra).strip())


def stat(num, text, src):
    return card([para(num, "bv-gip-num"), p(text), para(src, "bv-gip-src")], "bv-gip-card--stat")


def gtable(cols, rows, caption):
    head = "".join(f"<th>{c}</th>" for c in cols)
    body = "".join("<tr>" + "".join((f"<td><strong>{c}</strong></td>" if i == 0 else f"<td>{c}</td>") for i, c in enumerate(r)) + "</tr>" for r in rows)
    return (
        '<!-- wp:table {"className":"bv-gip-table"} -->\n'
        f'<figure class="wp-block-table bv-gip-table"><table><thead><tr>{head}</tr></thead><tbody>{body}</tbody></table>'
        f'<figcaption class="wp-element-caption">{caption}</figcaption></figure>\n'
        "<!-- /wp:table -->"
    )


def block(lst):
    return f'<!-- wp:booming-venture/geo-index {{"list":"{lst}","align":"wide"}} /-->'


def faq_grid(eyebrow, title, pairs):
    """Questions as equal cards. Each card keeps the H3 + paragraph pair, so
    bv_extract_faq_from_content() still builds the FAQPage schema."""
    return section([sec_head(eyebrow, title), grid([card([h3(q), p(ans)], "bv-gip-card--faq") for q, ans in pairs], 2)], anchor="faq")


def cta_band(title, text, btns, foot):
    return group(['<!-- wp:heading -->\n<h2 class="wp-block-heading">' + title + "</h2>\n<!-- /wp:heading -->",
                  p(text), buttons(btns), para(foot, "bv-gip-cta__foot")],
                 "bv-gip-cta", align="wide", gradient="brand-dark")


def data_band(title, text, btns):
    return group([group([head3(title), p(text)], "bv-gip-data__text"), buttons(btns, "bv-gip-data__btns")], "bv-gip-data")


CSV_NL = "/wp-json/bv/v1/geo-index?list=nl&amp;format=csv"
CSV_INTL = "/wp-json/bv/v1/geo-index?list=intl&amp;format=csv"

EN = [
    hero("Updated every month",
         'B2B GEO Index: <span class="bv-gradient-text">who is ready for AI search?</span>',
         "200 B2B companies, measured every month: how often ChatGPT, Claude, Gemini and Perplexity name them, and whether their websites are ready for AI search.",
         [("200", "B2B companies"), ("4", "AI assistants"), ("7", "website checks")],
         [("View the ranking", "#ranking", "bv-cta-gradient"), ("How the score works", "#methodology", "outline")]),
    section([
        sec_head("Why it matters", lead="Buyers now meet vendors in AI answers first. Whether a company shows up depends partly on things it controls on its own website."),
        grid([
            stat("94%", "of business buyers use AI in their buying process.", "Source: " + a("Forrester", "forrester")),
            stat("0", "of the major AI crawlers rendered JavaScript in Vercel's analysis. Text that only appears after scripts run stays invisible to them.", "Source: " + a("Vercel", "vercel")),
            stat("81.2%", "of the sources in Google AI Overviews for Dutch-language questions were Dutch-language pages. That is why the Dutch list checks for a Dutch version.", "Source: " + a("Temso, 2026", "temso_lang")),
        ], 3),
        para("And " + a("OpenAI", "openai_bots") + " says sites that block OAI-SearchBot will not appear in ChatGPT search answers.", "bv-gip-note"),
    ], extra="bv-gip-sec--why"),
    section([
        sec_head("The ranking", "What is the B2B GEO Index?",
                 "The B2B GEO Index is a monthly ranking of 200 B2B companies by how often AI assistants name them and how ready their websites are."),
        block("intl"),
    ], anchor="ranking"),
    section([
        sec_head("Methodology", "How is the score calculated?",
                 "The GEO score is a number from 0 to 100: half comes from how often AI assistants name the company, half from seven website checks.",
                 anchor="methodology"),
        grid([
            card([para("50% of the GEO score", "bv-gip-badge"), head3("AI score"),
                  p("Every month the site asks one buyer question per category to ChatGPT, Claude, Gemini and Perplexity, through their APIs with each assistant's own web search switched on. Dutch companies get the question in Dutch, international companies in English."),
                  p("The AI score is the share of answers that name the company (80%) or cite its website (20%).")]),
            card([para("50% of the GEO score", "bv-gip-badge"), head3("Website score"),
                  p("Seven checks on the company's own website, worth 100 points together: AI crawler access, text without JavaScript, structured data, entity, freshness, language and llms.txt."),
                  p("The table below shows what we measure for each check and why it matters.")]),
        ], 2),
        gtable(
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
            "The seven checks and their points. Every company is measured the same way, every month.",
        ),
        data_band("Open data",
                  "Download the full ranking as CSV. The files update every month. You may quote the numbers with a link to this page as the source.",
                  [("Top 100 Netherlands (CSV)", CSV_NL, ""), ("Top 100 international (CSV)", CSV_INTL, "")]),
    ], extra="bv-gip-sec--method"),
    section([
        sec_head("Selection", "Which companies are in the index?",
                 "The index is a fixed set of 100 Dutch B2B companies in eight sectors and 100 international B2B software companies in ten categories."),
        grid([
            card([para("100", "bv-gip-num"), head3("Dutch B2B companies"), p("Eight sectors, from SaaS and fintech to high-tech and logistics. Every company has its head office in the Netherlands.")]),
            card([para("100", "bv-gip-num"), head3("International B2B software companies"), p("Ten categories, from CRM and marketing to security, finance and developer tools.")]),
        ], 2),
        para("We picked well-known companies with a B2B model. Companies cannot pay for a place or a better score. Missing a company? Email " + a("info@boomingventure.com", "mailto:info@boomingventure.com") + " and we add it to the next measurement.", "bv-gip-note"),
    ]),
    faq_grid("FAQ", "Frequently asked questions", [
        ("How often is the index updated?", "Every month, on the first Monday. The site measures all 200 companies again and shows the change against the previous month."),
        ("Can I check my own company?", "Yes. Enter your domain in the check below the ranking. You get the same score as the companies in the index."),
        ("Which questions do you ask the AI assistants?", "One question per category, such as: What is the best CRM software for B2B companies? Name a few providers and say briefly why. The wording alternates each month, so no score depends on one phrasing."),
        ("Do the answers match what a user sees in ChatGPT?", "Every category gets the same question in the same way every month, so companies are compared fairly. Individual users can see other answers, depending on their location, account and model."),
        ("Why does blocking GPTBot cost fewer points than blocking OAI-SearchBot?", "OAI-SearchBot decides whether a site can appear in ChatGPT search. GPTBot collects training data, and OpenAI treats the two settings separately."),
        ("Can a company pay for a higher place?", "No. Scores come only from the measurements. Every company is measured the same way."),
        ("Why is Wikipedia part of the score?", "Muck Rack found Wikipedia to be ChatGPT's most cited domain. A Wikidata item and a Wikipedia article help AI systems recognize a company as an entity."),
        ("Can I use the data in my own article?", "Yes. Download the CSV files under Open data and quote the numbers with a link to this page as the source."),
    ]),
    cta_band("Raise your GEO score",
             "Want to show up in AI answers more often? Start with a free 15-minute Boardroom Quickscan.",
             [("Request a free Boardroom Quickscan", "/boardroom-quickscan/", ""), ("See our services", "/services/", "outline")],
             "Or read " + a("how ChatGPT search picks sources", "/how-chatgpt-search-picks-sources-b2b/") + "."),
]

NL = [
    hero("Elke maand bijgewerkt",
         'B2B GEO Index: <span class="bv-gradient-text">wie is klaar voor AI-zoekmachines?</span>',
         "200 B2B-bedrijven, elke maand gemeten: hoe vaak ChatGPT, Claude, Gemini en Perplexity ze noemen en of hun websites klaar zijn voor AI-zoekmachines.",
         [("200", "B2B-bedrijven"), ("4", "AI-assistenten"), ("7", "websitechecks")],
         [("Bekijk de ranglijst", "#ranglijst", "bv-cta-gradient"), ("Zo werkt de score", "#methode", "outline")]),
    section([
        sec_head("Waarom dit telt", lead="Kopers zien leveranciers nu eerst in AI-antwoorden. Of een bedrijf daarin verschijnt, hangt deels af van dingen die het op zijn eigen website regelt."),
        grid([
            stat("94%", "van de zakelijke kopers gebruikt AI tijdens het aankoopproces.", "Bron: " + a("Forrester", "forrester")),
            stat("0", "van de grote AI-crawlers renderde JavaScript in de analyse van Vercel. Tekst die pas na een script verschijnt, zien ze dus niet.", "Bron: " + a("Vercel", "vercel")),
            stat("81,2%", "van de bronnen in Google AI Overviews bij Nederlandstalige vragen waren Nederlandstalige pagina's. Daarom kijkt de Nederlandse lijst of er een Nederlandse versie is.", "Bron: " + a("Temso, 2026", "temso_lang")),
        ], 3),
        para("En volgens " + a("OpenAI", "openai_bots") + " verschijnen sites die OAI-SearchBot blokkeren niet in de antwoorden van ChatGPT search.", "bv-gip-note"),
    ], extra="bv-gip-sec--why"),
    section([
        sec_head("De ranglijst", "Wat is de B2B GEO Index?",
                 "De B2B GEO Index is een maandelijkse ranglijst van 200 B2B-bedrijven op hoe vaak AI-assistenten ze noemen en hoe klaar hun websites zijn."),
        block("nl"),
    ], anchor="ranglijst"),
    section([
        sec_head("Methode", "Hoe wordt de score berekend?",
                 "De GEO-score is een getal van 0 tot 100: de helft komt uit hoe vaak AI-assistenten het bedrijf noemen, de helft uit zeven websitechecks.",
                 anchor="methode"),
        grid([
            card([para("50% van de GEO-score", "bv-gip-badge"), head3("AI-score"),
                  p("Elke maand stelt de site per categorie één koopvraag aan ChatGPT, Claude, Gemini en Perplexity, via hun API's met de eigen zoekfunctie van elke assistent aan. Nederlandse bedrijven krijgen de vraag in het Nederlands, internationale bedrijven in het Engels."),
                  p("De AI-score is het aandeel antwoorden dat het bedrijf noemt (80%) of naar de website verwijst (20%).")]),
            card([para("50% van de GEO-score", "bv-gip-badge"), head3("Websitescore"),
                  p("Zeven checks op de eigen website van het bedrijf, samen goed voor 100 punten: toegang voor AI-crawlers, tekst zonder JavaScript, gestructureerde data, entiteit, actualiteit, taal en llms.txt."),
                  p("In de tabel hieronder staat per check wat we meten en waarom het telt.")]),
        ], 2),
        gtable(
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
            "De zeven checks en hun punten. Elk bedrijf wordt elke maand op dezelfde manier gemeten.",
        ),
        data_band("Open data",
                  "Download de volledige ranglijst als CSV. De bestanden worden elke maand bijgewerkt. Je mag de cijfers citeren met een link naar deze pagina als bron.",
                  [("Top 100 Nederland (CSV)", CSV_NL, ""), ("Top 100 internationaal (CSV)", CSV_INTL, "")]),
    ], extra="bv-gip-sec--method bv-gip--nl"),
    section([
        sec_head("Selectie", "Welke bedrijven staan in de index?",
                 "De index is een vaste groep van 100 Nederlandse B2B-bedrijven in acht sectoren en 100 internationale B2B-softwarebedrijven in tien categorieën."),
        grid([
            card([para("100", "bv-gip-num"), head3("Nederlandse B2B-bedrijven"), p("Acht sectoren, van SaaS en fintech tot hightech en logistiek. Elk bedrijf heeft zijn hoofdkantoor in Nederland.")]),
            card([para("100", "bv-gip-num"), head3("Internationale B2B-softwarebedrijven"), p("Tien categorieën, van CRM en marketing tot security, finance en tools voor developers.")]),
        ], 2),
        para("We kozen bekende bedrijven met een B2B-model. Bedrijven kunnen niet betalen voor een plek of een hogere score. Mis je een bedrijf? Mail " + a("info@boomingventure.com", "mailto:info@boomingventure.com") + ", dan nemen we het mee in de volgende meting.", "bv-gip-note"),
    ]),
    faq_grid("FAQ", "Veelgestelde vragen", [
        ("Hoe vaak wordt de index bijgewerkt?", "Elke maand, op de eerste maandag. De site meet alle 200 bedrijven opnieuw en toont het verschil met de maand ervoor."),
        ("Kan ik mijn eigen bedrijf checken?", "Ja. Vul je domein in bij de check onder de ranglijst. Je krijgt dezelfde score als de bedrijven in de index."),
        ("Welke vragen stellen jullie aan de AI-assistenten?", "Eén vraag per categorie, zoals: Wat is de beste boekhoudsoftware voor het mkb in Nederland? Noem een paar aanbieders en zeg kort waarom. De formulering wisselt per maand, zodat geen score van één formulering afhangt."),
        ("Komen de antwoorden overeen met wat een gebruiker in ChatGPT ziet?", "Elke categorie krijgt elke maand dezelfde vraag op dezelfde manier, dus bedrijven worden eerlijk vergeleken. Een individuele gebruiker kan andere antwoorden zien, afhankelijk van locatie, account en model."),
        ("Waarom kost het blokkeren van GPTBot minder punten dan het blokkeren van OAI-SearchBot?", "OAI-SearchBot bepaalt of een site in ChatGPT search kan verschijnen. GPTBot verzamelt trainingsdata, en OpenAI behandelt die twee instellingen apart."),
        ("Kan een bedrijf betalen voor een hogere plek?", "Nee. Scores komen alleen uit de metingen. Elk bedrijf wordt op dezelfde manier gemeten."),
        ("Waarom telt Wikipedia mee in de score?", "Muck Rack zag dat Wikipedia het meest geciteerde domein in ChatGPT is. Een Wikidata-item en een Wikipedia-artikel helpen AI-systemen een bedrijf als entiteit te herkennen."),
        ("Mag ik de data gebruiken in mijn eigen artikel?", "Ja. Download de CSV-bestanden bij Open data en citeer de cijfers met een link naar deze pagina als bron."),
    ]),
    cta_band("Verhoog je GEO-score",
             "Wil je vaker in AI-antwoorden verschijnen? Begin met een gratis Boardroom Quickscan van 15 minuten.",
             [("Vraag een gratis Boardroom Quickscan aan", "/nl/boardroom-quickscan/", ""), ("Bekijk onze diensten", "/nl/diensten/", "outline")],
             "Of lees " + a("hoe ChatGPT search bronnen kiest", "/nl/blog/hoe-chatgpt-search-bronnen-kiest-b2b/") + "."),
]

SEO = {
    "en": ("B2B GEO Index: 200 companies ranked for AI search", "Monthly ranking of 200 B2B companies: how often ChatGPT, Claude, Gemini and Perplexity name them, plus seven website checks. Check your own domain."),
    "nl": ("B2B GEO Index: 200 bedrijven gerangschikt op AI-zoekmachines", "Maandelijkse ranglijst van 200 B2B-bedrijven: hoe vaak ChatGPT, Claude, Gemini en Perplexity ze noemen, plus zeven websitechecks. Check je eigen domein."),
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
    m = __import__("re").search(r'<h2[^>]*>\s*(?:Frequently asked questions|Veelgestelde vragen)\s*</h2>(.*?)(?=<h2|\Z)', content, __import__("re").S | __import__("re").I)
    faqs = __import__("re").findall(r'<h3[^>]*>(.*?)</h3>\s*<!--\s*/wp:heading\s*-->\s*<!--\s*wp:paragraph\s*-->\s*<p[^>]*>(.*?)</p>', m.group(1) if m else "", __import__("re").S)
    if len(faqs) != 8:
        issues.append(f"faq pairs found: {len(faqs)}")
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
