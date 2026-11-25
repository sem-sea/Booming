"""Method pages of the B2B GEO Index (1.24.0).

    python3 tools/geo-index/method.py

EN /b2b-geo-index/methodology/ and NL /nl/b2b-geo-index/methode/. Written
to the geo-writer rules: H1 with the entity, question H2s each followed by
a 20 to 25 word capsule that starts with "[X] is", three sourced statistics
in the first 30%, an ordered list, a table, FAQ and a CTA. The live details
(questions, models, rounds, dates, change log) come from the shortcode
[bv_geo_method] in inc/geo-index-method.php.

Also writes the shipped page text into import/nl/content.json (keys "geo"
from pages.py and "geo_method"), used by POST /bv/v1/import-nl?update=geo
and ?update=geo-method.
"""
import importlib.util
import json
import pathlib
import re

ROOT = pathlib.Path(__file__).resolve().parents[2]
spec = importlib.util.spec_from_file_location("gip_pages", ROOT / "tools/geo-index/pages.py")
P = importlib.util.module_from_spec(spec)
spec.loader.exec_module(P)
B = P.B
a, p, cap, text_of = P.a, P.p, P.cap, P.text_of
ol, ul = B.ol, B.ul
hero, section, sec_head, grid, card, stat, gtable, faq_grid, cta_band, para, head3 = (
    P.hero, P.section, P.sec_head, P.grid, P.card, P.stat, P.gtable, P.faq_grid, P.cta_band, P.para, P.head3)

SEL = "https://searchengineland.com/ai-recommendation-lists-rarely-repeat-study-468076"
ARXIV = "https://arxiv.org/abs/2604.07585"
EVERTUNE = "https://www.evertune.ai/resources/insights-on-ai/how-many-repetitions-of-a-single-prompt-does-it-take-to-understand-your-ai-visibility"
SEJ = "https://www.searchenginejournal.com/ai-visibility-rankings-arent-stable-new-research-shows-its-mostly-statistical-noise/581905/"
SEMRUSH = "https://ai-visibility-index.semrush.com/methodology"
AHREFS = "https://ahrefs.com/blog/ai-overview-brand-correlation/"
PEEC = "https://docs.peec.ai/metrics-overview"
WILSON = "https://en.wikipedia.org/wiki/Binomial_proportion_confidence_interval#Wilson_score_interval"


def shortcode(lang):
    return f'<!-- wp:shortcode -->\n[bv_geo_method lang="{lang}"]\n<!-- /wp:shortcode -->'


EN = [
    hero("Methodology",
         'B2B GEO Index <span class="bv-gradient-text">methodology</span>',
         "How we measure how often ChatGPT, Claude, Gemini and Perplexity name 400 B2B companies, how we deal with chance, and what every number means.",
         [("5", "buyer questions per category"), ("4", "AI assistants"), ("95%", "margin of error on every AI number"), ("12", "answers minimum for an AI score")],
         [("View the index", "/b2b-geo-index/#ranking", "bv-cta-gradient"), ("Measurement details", "#details", "outline")]),
    section([
        sec_head("Why repeat", "Why does the index ask every question several times?",
                 "Repeated measurement is needed because AI assistants name different companies for the same question, so one answer says little about real visibility."),
        grid([
            stat("Under 1 in 100", "is the chance that AI gives the same list of brands twice for the same prompt, in about 3,000 runs by 600 volunteers.", "Source: " + a("SparkToro and Gumshoe, via Search Engine Land", SEL)),
            stat("7", "runs per prompt per day is the minimum the University of St. Gallen advises for stable brand numbers.", "Source: " + a("Schulte et al., 2026", ARXIV)),
            stat("27 to 6", "points: the margin of error after 5 and after 100 repetitions of one prompt in tests by Evertune.", "Source: " + a("Evertune", EVERTUNE)),
        ], 3),
        para("And " + a("Search Engine Journal", SEJ) + " reported in July 2026 that a top 10 of AI citations often has a margin of about five places. That is why the index shows groups instead of exact places outside the top 10.", "bv-gip-note"),
    ], extra="bv-gip-sec--why"),
    section([
        sec_head("How it works", "How does the B2B GEO Index measure AI visibility?",
                 "The B2B GEO Index is measured by asking four AI assistants five buyer questions per category and counting how often each company is named."),
        ol([
            "<strong>A fixed group.</strong> 400 B2B companies: 200 Dutch and 200 international, ten in each of the same 20 sectors from " + a("SBI 2025", P.CBS_EN) + ". The group only changes with a new version of the method.",
            "<strong>A category per company.</strong> Each company has a buyer category, such as CRM software for B2B companies. Companies in the same category get the same questions, so they compete in the same answers.",
            "<strong>Five buyer questions.</strong> Each category gets five questions with a different intent: the best provider, a recommendation, a shortlist to compare, reputation and switching. Dutch companies get them in Dutch, international companies in English.",
            "<strong>Four assistants, web search on.</strong> Every question goes to ChatGPT, Claude, Gemini and Perplexity through their APIs, each with its own web search switched on.",
            "<strong>A second round where needed.</strong> Round 1 asks every question once. A category gets a second round when a company in it is named in some answers but not in others and its margin is still wider than 30 points.",
            "<strong>Names and sources.</strong> We look for the company name, known variants and the domain. Names that are also ordinary words only count in a list or in bold. We also record which websites each answer cites.",
            "<strong>Tone.</strong> For every mention, Claude Haiku rates how positively the answer describes the company, from 0 to 100, with 50 as neutral.",
            "<strong>Website checks.</strong> Seven checks on each company's own website, explained on the " + a("index page", "/b2b-geo-index/#methodology") + ".",
        ]),
    ]),
    section([
        sec_head("Measures", "Which numbers does the index show?",
                 "The AI score is a number from 0 to 100: 80% comes from how often assistants name the company and 20% from citing its website."),
        gtable(
            ["Measure", "What it means", "How we calculate it"],
            [
                ["GEO score", "The total score in the ranking", "Average of the AI score and the website score. With fewer than 12 answers, the website score alone."],
                ["AI score", "How strongly AI assistants put the company forward", "80% visibility plus 20% citation share."],
                ["Visibility", "How often the company is named", "Answers that name the company, divided by all answers to its category's questions."],
                ["Share of voice", "Its share of the conversation in its sector", "Its mentions, divided by all mentions of index companies in the answers about the sector."],
                ["Named first", "How often it comes first", "Answers in which it is the first index company from its sector, divided by all answers."],
                ["Tone", "How positive the answers are", "Average rating from 0 to 100 by Claude Haiku. 50 is neutral."],
                ["Citation share", "How often its own website is a source", "Answers that cite its domain, divided by all answers."],
                ["Margin of error", "How certain a number is", a("Wilson interval", WILSON) + " at 95%. For the GEO score, half the AI margin, because the website half is exact."],
            ],
            "The measures follow the definitions that AI visibility tools use, such as those documented by " + a("Peec AI", PEEC) + ".",
        ),
    ], extra="bv-gip-sec--method"),
    section([
        sec_head("Chance", "How does the index deal with chance?",
                 "A margin of error is the range in which the true value lies with 95% certainty; the index shows it next to every AI number."),
        ul([
            "Places 1 to 10 show a number. Outside the top 10 the index shows a group: strong (score 50 or more), middle (25 to 49) or low (under 25).",
            "A change since last month counts as real only when it is larger than the combined margin of both months. Smaller changes are grey: probably chance.",
            "With fewer than 12 answers a company gets no AI score that month, and its total is the website score alone.",
            "In the first month after a method change, the index does not compare with the month before.",
            "Every month is a snapshot. " + a("Semrush", SEMRUSH) + " also warns that month-to-month differences in AI answers should not be read as growth or decline on their own.",
        ]),
    ]),
    section([
        sec_head("Differences", "Which differences does the index show?",
                 "The difference insights are five comparisons per company and per list: last month, the assistants, the sector leader, website versus AI, and Dutch versus international."),
        grid([
            card([head3("Change since last month"), p("Only changes larger than the margin of error count as real. The rest is shown in grey.")]),
            card([head3("Per assistant"), p("Which assistant names the company, and how much two assistants overlap for the same question.")]),
            card([head3("Gap with the sector leader"), p("Points behind the leader, the sources that name the leader and not the company, and the website checks it misses. Brand mentions on other sites correlate most with AI visibility (0.664), more than backlinks (0.218), according to " + a("Ahrefs", AHREFS) + ".")]),
            card([head3("Website versus AI"), p("Four groups split at the median of the list, plus the rank correlation between both scores.")]),
            card([head3("Netherlands versus international"), p("The share of Dutch companies among the index companies named in answers to Dutch and to English questions.")]),
            card([head3("Sources per sector"), p("The websites AI cites most for each sector. Being mentioned there helps a company get named.")]),
        ], 3),
    ]),
    section([
        sec_head("Measurement details", "Which questions and models does the latest measurement use?",
                 "The measurement details are the live settings of the latest run: the questions per list, the assistants and models, the rounds and the dates.",
                 anchor="details"),
        shortcode("en"),
    ]),
    section([
        sec_head("Limits", "What are the limits of the method?",
                 "The main limit is that API answers can differ from what one person sees in an app, because of location, account, model and conversation history."),
        ul([
            "Share of voice and named first only count the index companies, not every company an answer mentions.",
            "Name recognition can miss a spelling or count a name that means something else. Ordinary words only count in a list or in bold.",
            "Tone is rated by an AI model. It shows a direction, not a judgment by people.",
            "Sources come from the links the assistants return. Links that hide the real website behind a redirect are left out.",
            "A correlation is not a cause. A link between website score and AI score does not prove that one causes the other.",
        ]),
        para("Nobody can pay for a place or a higher score, and Booming Venture is not in the index. Spot an error, such as a wrong domain or a missing name variant? " + a("Let us know", "/#contact") + " and we correct it in the next measurement.", "bv-gip-note"),
    ]),
    faq_grid("FAQ", "Frequently asked questions", [
        ("How many answers does a company get?", "Round 1 gives every company 20 answers: five questions to four assistants. Where the result is still uncertain, a second round brings it to 40."),
        ("Why do some companies show a group instead of a place?", "Outside the top 10 the margin of error is larger than the gap between places. A group is more honest than an exact place."),
        ("What does n/a mean in the AI column?", "The company got fewer than 12 answers that month, for example because an assistant did not answer. Its total score is then the website score alone."),
        ("Can I see the questions?", "Yes. The five questions per list are in the measurement details on this page, with the assistants and models of the latest measurement."),
    ]),
    cta_band("See your own gap",
             "Check your domain on the index page and see your gap with the sector leader. Or start with a free 15-minute Boardroom Quickscan.",
             [("Check your domain", "/b2b-geo-index/#ranking", ""), ("Request a free Boardroom Quickscan", "/boardroom-quickscan/", "outline")],
             "Or read " + a("how ChatGPT search picks sources", "/how-chatgpt-search-picks-sources-b2b/") + "."),
]

NL = [
    hero("Methode",
         'Methode van de <span class="bv-gradient-text">B2B GEO Index</span>',
         "Hoe we meten hoe vaak ChatGPT, Claude, Gemini en Perplexity 400 B2B-bedrijven noemen, hoe we omgaan met toeval en wat elk getal betekent.",
         [("5", "koopvragen per categorie"), ("4", "AI-assistenten"), ("95%", "foutmarge bij elk AI-getal"), ("12", "antwoorden minimaal voor een AI-score")],
         [("Bekijk de index", "/nl/b2b-geo-index/#ranglijst", "bv-cta-gradient"), ("Meetgegevens", "#meetgegevens", "outline")]),
    section([
        sec_head("Waarom herhalen", "Waarom stelt de index elke vraag vaker?",
                 "Herhaald meten is nodig omdat AI-assistenten bij dezelfde vraag steeds andere bedrijven noemen, dus één antwoord zegt weinig over echte zichtbaarheid."),
        grid([
            stat("Minder dan 1 op 100", "is de kans dat AI bij dezelfde vraag twee keer dezelfde lijst merken geeft, in ongeveer 3.000 metingen door 600 vrijwilligers.", "Bron: " + a("SparkToro en Gumshoe, via Search Engine Land", SEL)),
            stat("7", "metingen per vraag per dag is het minimum dat de Universiteit van St. Gallen adviseert voor stabiele cijfers per merk.", "Bron: " + a("Schulte e.a., 2026", ARXIV)),
            stat("27 naar 6", "punten: de foutmarge na 5 en na 100 herhalingen van één vraag in tests van Evertune.", "Bron: " + a("Evertune", EVERTUNE)),
        ], 3),
        para("En " + a("Search Engine Journal", SEJ) + " meldde in juli 2026 dat een top 10 van AI-bronnen vaak een marge van ongeveer vijf plaatsen heeft. Daarom toont de index buiten de top 10 groepen in plaats van exacte plaatsen.", "bv-gip-note"),
    ], extra="bv-gip-sec--why"),
    section([
        sec_head("Zo werkt het", "Hoe meet de B2B GEO Index AI-zichtbaarheid?",
                 "De B2B GEO Index is een meting waarbij vier AI-assistenten per categorie vijf koopvragen krijgen en we tellen hoe vaak elk bedrijf genoemd wordt."),
        ol([
            "<strong>Een vaste groep.</strong> 400 B2B-bedrijven: 200 Nederlandse en 200 internationale, tien in elk van dezelfde 20 sectoren uit de " + a("SBI 2025", P.CBS_NL) + ". De groep verandert alleen met een nieuwe versie van de methode.",
            "<strong>Een categorie per bedrijf.</strong> Elk bedrijf heeft een koopcategorie, zoals boekhoudsoftware voor het mkb. Bedrijven in dezelfde categorie krijgen dezelfde vragen en strijden dus in dezelfde antwoorden.",
            "<strong>Vijf koopvragen.</strong> Elke categorie krijgt vijf vragen met een andere koopreden: de beste aanbieder, een aanrader, een shortlist om te vergelijken, reputatie en overstappen. Nederlandse bedrijven krijgen ze in het Nederlands, internationale in het Engels.",
            "<strong>Vier assistenten, zoeken aan.</strong> Elke vraag gaat via de API naar ChatGPT, Claude, Gemini en Perplexity, elk met de eigen zoekfunctie aan.",
            "<strong>Een tweede ronde waar nodig.</strong> Ronde 1 stelt elke vraag één keer. Een categorie krijgt een tweede ronde als een bedrijf daarin soms wel en soms niet genoemd wordt en de marge nog groter is dan 30 punten.",
            "<strong>Namen en bronnen.</strong> We zoeken op de bedrijfsnaam, bekende varianten en het domein. Namen die ook gewone woorden zijn, tellen alleen in een opsomming of vetgedrukt. We noteren ook welke websites elk antwoord als bron geeft.",
            "<strong>Toon.</strong> Bij elke vermelding beoordeelt Claude Haiku hoe positief het antwoord over het bedrijf is, van 0 tot 100, met 50 als neutraal.",
            "<strong>Websitechecks.</strong> Zeven checks op de eigen website van elk bedrijf, uitgelegd op de " + a("indexpagina", "/nl/b2b-geo-index/#methode") + ".",
        ]),
    ]),
    section([
        sec_head("Maten", "Welke getallen toont de index?",
                 "De AI-score is een getal van 0 tot 100: 80% komt uit hoe vaak assistenten het bedrijf noemen, 20% uit verwijzingen naar de website."),
        gtable(
            ["Maat", "Wat het betekent", "Hoe we rekenen"],
            [
                ["GEO-score", "De totaalscore in de ranglijst", "Gemiddelde van de AI-score en de websitescore. Met minder dan 12 antwoorden alleen de websitescore."],
                ["AI-score", "Hoe sterk AI-assistenten het bedrijf naar voren schuiven", "80% zichtbaarheid plus 20% bronaandeel."],
                ["Zichtbaarheid", "Hoe vaak het bedrijf genoemd wordt", "Antwoorden die het bedrijf noemen, gedeeld door alle antwoorden op de vragen over zijn categorie."],
                ["Share of voice", "Zijn aandeel in het gesprek over de sector", "Zijn vermeldingen, gedeeld door alle vermeldingen van indexbedrijven in de antwoorden over de sector."],
                ["Eerst genoemd", "Hoe vaak het als eerste staat", "Antwoorden waarin het het eerste indexbedrijf uit zijn sector is, gedeeld door alle antwoorden."],
                ["Toon", "Hoe positief de antwoorden zijn", "Gemiddelde beoordeling van 0 tot 100 door Claude Haiku. 50 is neutraal."],
                ["Bronaandeel", "Hoe vaak de eigen website een bron is", "Antwoorden die het eigen domein als bron geven, gedeeld door alle antwoorden."],
                ["Foutmarge", "Hoe zeker een getal is", a("Wilson-interval", WILSON) + " van 95%. Bij de GEO-score de helft van de AI-marge, omdat de websitehelft exact is."],
            ],
            "De maten volgen de definities die AI-zichtbaarheidstools gebruiken, zoals die van " + a("Peec AI", PEEC) + ".",
        ),
    ], extra="bv-gip-sec--method bv-gip--nl"),
    section([
        sec_head("Toeval", "Hoe gaat de index om met toeval?",
                 "Een foutmarge is het bereik waarin de echte waarde met 95% zekerheid ligt; de index toont die marge bij elk AI-getal."),
        ul([
            "Plaats 1 tot 10 toont een getal. Buiten de top 10 toont de index een groep: sterk (score 50 of meer), midden (25 tot 49) of laag (onder 25).",
            "Een verschil met vorige maand telt alleen als echt als het groter is dan de gecombineerde marge van beide maanden. Kleinere verschillen zijn grijs: waarschijnlijk toeval.",
            "Met minder dan 12 antwoorden krijgt een bedrijf die maand geen AI-score, en is de totaalscore alleen de websitescore.",
            "In de eerste maand na een wijziging van de methode vergelijkt de index niet met de maand ervoor.",
            "Elke maand is een momentopname. Ook " + a("Semrush", SEMRUSH) + " waarschuwt dat verschillen in AI-antwoorden tussen maanden niet zomaar als groei of daling te lezen zijn.",
        ]),
    ]),
    section([
        sec_head("Verschillen", "Welke verschillen laat de index zien?",
                 "De verschilinzichten zijn vijf vergelijkingen per bedrijf en per lijst: vorige maand, de assistenten, de sectorleider, website versus AI en Nederland versus internationaal."),
        grid([
            card([head3("Verschil met vorige maand"), p("Alleen verschillen die groter zijn dan de foutmarge tellen als echt. De rest staat in het grijs.")]),
            card([head3("Per assistent"), p("Welke assistent het bedrijf noemt, en hoeveel twee assistenten bij dezelfde vraag overlappen.")]),
            card([head3("Verschil met de sectorleider"), p("De punten achterstand op de leider, de bronnen die de leider noemen en het bedrijf niet, en de websitechecks die ontbreken. Vermeldingen op andere websites hangen het sterkst samen met AI-zichtbaarheid (0,664), sterker dan backlinks (0,218), volgens " + a("Ahrefs", AHREFS) + ".")]),
            card([head3("Website versus AI"), p("Vier groepen, gesplitst op de mediaan van de lijst, plus de rangcorrelatie tussen beide scores.")]),
            card([head3("Nederland versus internationaal"), p("Het aandeel Nederlandse bedrijven onder de indexbedrijven die genoemd worden bij Nederlandse en bij Engelse vragen.")]),
            card([head3("Bronnen per sector"), p("De websites die AI per sector het vaakst als bron geeft. Daar genoemd worden helpt om genoemd te worden.")]),
        ], 3),
    ]),
    section([
        sec_head("Meetgegevens", "Welke vragen en modellen gebruikt de laatste meting?",
                 "De meetgegevens zijn de live instellingen van de laatste meting: de vragen per lijst, de assistenten en modellen, de rondes en de datums.",
                 anchor="meetgegevens"),
        shortcode("nl"),
    ]),
    section([
        sec_head("Beperkingen", "Wat zijn de beperkingen van de methode?",
                 "De belangrijkste beperking is dat API-antwoorden kunnen afwijken van wat één persoon in een app ziet, door locatie, account, model en eerdere gesprekken."),
        ul([
            "Share of voice en eerst genoemd tellen alleen de indexbedrijven, niet elk bedrijf dat een antwoord noemt.",
            "Naamherkenning kan een schrijfwijze missen of een naam tellen die iets anders betekent. Gewone woorden tellen alleen in een opsomming of vetgedrukt.",
            "De toon wordt beoordeeld door een AI-model. Het geeft een richting, geen oordeel van mensen.",
            "Bronnen komen uit de links die de assistenten teruggeven. Links die de echte website achter een doorverwijzing verbergen, laten we weg.",
            "Samenhang is geen oorzaak. Een verband tussen websitescore en AI-score bewijst niet dat het een het ander veroorzaakt.",
        ]),
        para("Niemand kan betalen voor een plek of een hogere score, en Booming Venture staat zelf niet in de index. Zie je een fout, zoals een verkeerd domein of een ontbrekende naamvariant? " + a("Laat het ons weten", "/nl/#contact") + ", dan passen we het aan bij de volgende meting.", "bv-gip-note"),
    ]),
    faq_grid("FAQ", "Veelgestelde vragen", [
        ("Hoeveel antwoorden krijgt een bedrijf?", "Ronde 1 geeft elk bedrijf 20 antwoorden: vijf vragen aan vier assistenten. Waar de uitkomst nog onzeker is, brengt een tweede ronde dat op 40."),
        ("Waarom staat bij sommige bedrijven een groep in plaats van een plaats?", "Buiten de top 10 is de foutmarge groter dan het verschil tussen plaatsen. Een groep is dan eerlijker dan een exacte plaats."),
        ("Wat betekent n.b. in de AI-kolom?", "Het bedrijf kreeg die maand minder dan 12 antwoorden, bijvoorbeeld omdat een assistent niet antwoordde. De totaalscore is dan alleen de websitescore."),
        ("Kan ik de vragen zien?", "Ja. De vijf vragen per lijst staan bij de meetgegevens op deze pagina, met de assistenten en modellen van de laatste meting."),
    ]),
    cta_band("Zie je eigen verschil",
             "Check je domein op de indexpagina en zie je verschil met de sectorleider. Of begin met een gratis Boardroom Quickscan van 15 minuten.",
             [("Check je domein", "/nl/b2b-geo-index/#ranglijst", ""), ("Vraag een gratis Boardroom Quickscan aan", "/nl/boardroom-quickscan/", "outline")],
             "Of lees " + a("hoe ChatGPT search bronnen kiest", "/nl/blog/hoe-chatgpt-search-bronnen-kiest-b2b/") + "."),
]

SEO = {
    "en": ("Methodology: how the B2B GEO Index measures AI visibility", "How the B2B GEO Index measures AI visibility: five buyer questions per category, four assistants, a 95% margin of error and five kinds of difference."),
    "nl": ("Methode: zo meet de B2B GEO Index AI-zichtbaarheid", "Zo meet de B2B GEO Index AI-zichtbaarheid: vijf koopvragen per categorie, vier assistenten, een foutmarge van 95% en vijf soorten verschillen."),
}


def check(blocks):
    content = "\n".join(blocks)
    issues = []
    if "—" in content or "–" in content:
        issues.append("dash")
    for c in re.findall(r'class="bv-capsule[^"]*"[^>]*>(.*?)</p>', content, re.S):
        words = text_of(c).split()
        if not 20 <= len(words) <= 25:
            issues.append(f"capsule {len(words)}: {text_of(c)[:50]}")
        if not re.search(r"\b(is|are|zijn)\b", " ".join(words[:8])):
            issues.append("capsule without is/are: " + text_of(c)[:40])
    cut = content[: int(len(content) * 0.30)]
    if len(re.findall(r'href="https?://(?!boomingventure)', cut)) < 3:
        issues.append("fewer than 3 sources in the first 30%")
    banned = re.findall(r"\b(delve|elevate|harness|leverage|navigate|tapestry|unlock|realm|landscape|robust|seamless|foster|moreover|furthermore)\b", text_of(content), re.I)
    if banned:
        issues.append("banned words: " + ", ".join(sorted(set(banned))))
    for k, (t, d) in SEO.items():
        assert len(d) <= 155 and len(t) <= 65, (k, len(t), len(d))
    return issues


def main():
    data_file = ROOT / "wp-content/themes/booming-venture/import/nl/content.json"
    data = json.loads(data_file.read_text(encoding="utf-8"))
    for lang, blocks in (("en", EN), ("nl", NL)):
        issues = check(blocks)
        print(lang, "method issues:", issues or "none", "| words:", len(text_of("\n".join(blocks)).split()))
        data.setdefault("geo_method", {})[lang] = {
            "title": "B2B GEO Index methodology" if lang == "en" else "Methode B2B GEO Index",
            "slug": "methodology" if lang == "en" else "methode",
            "content": "\n".join(blocks),
            "meta": {"_yoast_wpseo_title": SEO[lang][0], "_yoast_wpseo_metadesc": SEO[lang][1]},
        }
    for lang, blocks in (("en", P.EN), ("nl", P.NL)):
        issues = P.check(blocks, lang)
        print(lang, "index issues:", issues or "none")
        data["geo"][lang]["content"] = "\n".join(blocks)
        data["geo"][lang]["meta"].update({"_yoast_wpseo_title": P.SEO[lang][0], "_yoast_wpseo_metadesc": P.SEO[lang][1]})
    data_file.write_text(json.dumps(data, ensure_ascii=False), encoding="utf-8")
    print("content.json updated")


if __name__ == "__main__":
    main()
