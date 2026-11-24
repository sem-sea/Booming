"""Build the REST payloads for the Dutch pages under /nl/.

    python3 tools/i18n/nl_pages_payload.py OUT_DIR

Writes OUT_DIR/parent.json (POST /wp/v2/pages, the Dutch home at /nl/) and
OUT_DIR/children.json (POST /batch/v1, every other Dutch page, parent id as
the placeholder __PARENT__). Pages are created private so they can be
reviewed while logged in before they go public.

Content comes from import/content.json (the English source of truth):
pattern references get the -nl suffix, text units are replaced with the
Dutch in nl_pages.NL through blocktext.apply (strict: a missing unit
stops the build), and internal links are mapped to their /nl/ URLs.
"""
import json
import pathlib
import re
import sys

sys.path.insert(0, str(pathlib.Path(__file__).resolve().parent))
import blocktext  # noqa: E402
from build_nl import ROOT, URLS, map_hrefs  # noqa: E402
from nl_pages import NL  # noqa: E402

CONTENT = ROOT / "wp-content/themes/booming-venture/import/content.json"

# English slug -> (EN page id, Dutch slug, Dutch title, template, SEO title, meta description)
PAGES = {
    "home": (7, "nl", "Home", "page-home-nl",
             "B2B-marketing- en GEO-bureau in Rotterdam | Booming Venture",
             "Booming Venture helpt B2B SaaS en scale-ups aan pipeline uit Google, LinkedIn en AI-zoekmachines zoals ChatGPT. Strategie en uitvoering vanuit Rotterdam."),
    "about": (8, "over-ons", "Over ons", "",
              "Over Booming Venture en oprichter Ben Verschuur",
              "Booming Venture is een marketingadviesbureau in Rotterdam, opgericht door Ben Verschuur, met 15+ jaar ervaring in performance marketing en werk met AI."),
    "services": (9, "diensten", "Diensten", "page-landing",
                 "Diensten: AI-marketing, GEO en groei | Booming Venture",
                 "Strategisch advies, performance marketing, AI-oplossingen en groeiprogramma's voor B2B- en premiummerken. Heldere scope, maandprijs vanaf € 2.500."),
    "blog": (10, "blog", "Blog", "page-blog-nl",
             "Blog over AI-marketing, GEO en B2B-groei | Booming Venture",
             "Artikelen over zichtbaarheid in AI-zoekmachines, GEO, B2B-demand generation, betaalde media en marketinganalytics, met bronnen en benchmarks."),
    "unify-framework": (11, "unify-framework", "UNIFY Framework", "page-landing",
                        "UNIFY Framework: AI-marketingsysteem in 5 fases",
                        "UNIFY is een framework in vijf fases (Understand, Nurture, Integrate, Forecast, Yield) om AI in je marketing als één systeem te laten werken."),
    "funnel-calculator": (12, "funnel-calculator", "Funnel Leak Calculator", "page-funnel-calculator-nl",
                          "Funnel Leak Calculator: vind gemiste omzet (gratis)",
                          "Vul je verkeer en conversieratio's in en zie waar je funnel omzet lekt en welke fase je eerst moet verbeteren. Een gratis tool van Booming Venture."),
    "roi-forecaster": (13, "roi-forecaster", "ROI Forecaster", "page-roi-forecaster-nl",
                       "Marketing ROI Forecaster: prognose voor 12 maanden (gratis)",
                       "Modelleer de marketing-ROI van de komende 12 maanden op basis van je eigen budget, CAC en conversiecijfers, en exporteer de prognose als pdf."),
    "free-growth-guide": (14, "gratis-groeigids", "Gratis groeigids", "page-landing",
                          "Gratis groeistrategiegids (pdf) | Booming Venture",
                          "Download de gratis groeigids: de frameworks, templates en checklists die we gebruiken voor B2B-marketing met AI, in één pdf."),
    "boardroom-quickscan": (15, "boardroom-quickscan", "Boardroom Quickscan", "page-boardroom-quickscan-nl",
                            "Boardroom Quickscan: groeidiagnose in 15 minuten",
                            "Een diagnose van 15 minuten op directieniveau voor bestuurders die willen weten waar de groei van het volgende kwartaal vandaan komt."),
    "head-of-growth": (16, "head-of-growth", "Head of Growth, fractional leiderschap", "page-head-of-growth-nl",
                       "Fractional Head of Growth | Booming Venture",
                       "Senior groeileiderschap wanneer je het nodig hebt: twee dagen per week, resultaten per kwartaal en kennisoverdracht aan je eigen team."),
    "privacy-policy": (3, "privacybeleid", "Privacybeleid", "page-landing",
                       "Privacybeleid | Booming Venture",
                       "Hoe Booming Venture persoonsgegevens verzamelt, gebruikt en beschermt als je boomingventure.com gebruikt of met ons werkt, in lijn met de AVG."),
    "cookie-policy": (349, "cookiebeleid", "Cookiebeleid", "page-landing",
                      "Cookiebeleid | Booming Venture",
                      "Welke cookies en vergelijkbare technieken boomingventure.com gebruikt, waarom we ze gebruiken en hoe je je toestemming altijd kunt aanpassen."),
    "terms-of-service": (17, "algemene-voorwaarden", "Algemene voorwaarden", "",
                         "Algemene voorwaarden | Booming Venture",
                         "De voorwaarden die gelden voor de diensten van Booming Venture en voor het gebruik van boomingventure.com."),
    "disclaimer": (18, "disclaimer", "Disclaimer", "",
                   "Disclaimer | Booming Venture",
                   "Disclaimer voor de artikelen, tools en calculators op boomingventure.com. De inhoud is algemene informatie, geen professioneel advies."),
}

PATTERN_RE = re.compile(r'(<!-- wp:pattern \{"slug":"booming-venture/)([a-z0-9-]+)("\} /-->)')


def dutch_content(page):
    text = page["content"]
    nl = NL.get(page["slug"])
    if nl is not None:
        keys = blocktext.extract(text)
        if len(keys) != len(nl):
            raise SystemExit(f"{page['slug']}: {len(keys)} units, {len(nl)} Dutch lines")
        text, _ = blocktext.apply(text, dict(zip(keys, nl)))
    text = PATTERN_RE.sub(lambda m: m.group(1) + m.group(2) + "-nl" + m.group(3), text)
    return map_hrefs(text)


def body(page, slug, title, template, seo_title, desc, en_id):
    assert len(desc) <= 155, (slug, len(desc))
    assert "—" not in desc + seo_title + title
    b = {
        "title": title,
        "slug": slug,
        "status": "private",
        "content": dutch_content(page),
        "comment_status": "closed",
        "meta": {
            "_bv_lang": "nl",
            "_bv_translation": en_id,
            "_yoast_wpseo_title": seo_title,
            "_yoast_wpseo_metadesc": desc,
        },
    }
    if template:
        b["template"] = template
    return b


def main(out):
    out = pathlib.Path(out)
    out.mkdir(parents=True, exist_ok=True)
    pages = {p["slug"]: p for p in json.loads(CONTENT.read_text(encoding="utf-8"))["pages"]}
    requests = []
    for en_slug, (en_id, slug, title, template, seo, desc) in PAGES.items():
        b = body(pages[en_slug], slug, title, template, seo, desc, en_id)
        # every internal link must point at a Dutch URL or a non-page target
        for href in re.findall(r'href="((?:https://boomingventure\.com)?/[^"]*)"', b["content"]):
            path = href.replace("https://boomingventure.com", "")
            assert path.startswith("/nl/") or path not in URLS, (slug, href)
        if en_slug == "home":
            (out / "parent.json").write_text(json.dumps(b, ensure_ascii=False), encoding="utf-8")
            continue
        b["parent"] = "__PARENT__"
        requests.append({"method": "POST", "path": "/wp/v2/pages", "body": b})
    batch = {"validation": "normal", "requests": requests}
    (out / "children.json").write_text(json.dumps(batch, ensure_ascii=False), encoding="utf-8")
    sizes = {r["body"]["slug"]: len(r["body"]["content"]) for r in requests}
    print(f"parent.json + {len(requests)} child pages", sizes)


if __name__ == "__main__":
    main(sys.argv[1] if len(sys.argv) > 1 else "build/nl-pages")
