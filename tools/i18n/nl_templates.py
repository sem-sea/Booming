"""Dutch text for templates and template parts, aligned with the units that
blocktext.extract() returns for TEMPLATE_FILES (same order). None means
"keep as is" (structural units without words to translate)."""
TEMPLATE_FILES = [
    "templates/home.html", "templates/single.html", "templates/page-boardroom-quickscan.html",
    "templates/page-head-of-growth.html", "templates/page-funnel-calculator.html",
    "templates/page-roi-forecaster.html", "templates/404.html", "parts/header.html", "parts/footer.html",
]

NL = [
    '<span class="bv-gradient-text">Inzichten in groeimarketing</span>',
    'Diepgaande artikelen over zichtbaarheid in AI-zoekmachines, GEO, B2B-demand generation, betaalde media en marketinganalytics.',
    'Ontvang nieuw onderzoek over AI-zoekmachines en B2B-groei per e-mail',
    None,
    'Nog geen artikelen gevonden. Kom binnenkort terug.',
    'Blijf voorop in groei',
    'Nieuw onderzoek over AI-zoekmachines, B2B-groei en conversie, direct in je inbox. Geen spam, altijd af te melden.',
    'Lees verder',
    # 10
    'Gratis Boardroom Quickscan',
    'Een strategische diagnose van 15 minuten voor directieteams die willen weten waar groei weglekt.',
    '<a class="wp-block-button__link has-booming-700-color has-base-background-color has-text-color has-background wp-element-button" href="#scan-form">Start de Quickscan →</a>',
    'Vraag je gratis Boardroom Quickscan aan',
    'Fractional Head of Growth',
    'Senior groeileiderschap zonder het senior prijskaartje. Strategie, uitvoering en een sterker eigen team.',
    '<a class="wp-block-button__link has-booming-700-color has-base-background-color has-text-color has-background wp-element-button" href="#hog-apply">Vraag een kennismakingsgesprek aan →</a>',
    'Vertel ons over je bedrijf',
    'Gratis tool · diagnose in 90 seconden',
    # 20
    'Ontdek precies waar je funnel omzet verliest en wat je als eerste moet oplossen.',
    'Gratis tool · modelleer je groei',
    'Voorspel je ROI voor 12 maanden op basis van je eigen cijfers, exporteer naar pdf en deel het met je team.',
    'Deze pagina sloeg verkeerd af',
    'De pagina die je zoekt is er niet, ons groeiplaybook wel.',
    '<a class="wp-block-button__link wp-element-button" href="/nl/">Terug naar home</a>',
    '<a class="wp-block-button__link wp-element-button" href="/nl/blog/">Lees de blog</a>',
    None,
    '<a class="wp-block-button__link wp-element-button" href="/nl/#contact">Aan de slag</a>',
    None,
    # 30
    'Vanuit Rotterdam helpen we bedrijven groeien met vernieuwende marketingstrategieën en AI-oplossingen.',
    'Breedveldsingel 1<br>3055 PG Rotterdam<br>Nederland<br>E-mail: info@boomingventure.com',
    'Bedrijf',
    '<a href="/nl/over-ons/">Over ons</a>',
    '<a href="/nl/unify-framework/">UNIFY Framework</a>',
    '<a href="/nl/diensten/">Diensten</a>',
    '<a href="/nl/blog/">Blog</a>',
    '<a href="/nl/#contact">Contact</a>',
    'Diensten',
    '<a href="/nl/diensten/">Strategisch advies</a>',
    # 40
    '<a href="/nl/diensten/">Performance marketing</a>',
    '<a href="/nl/diensten/">AI-oplossingen</a>',
    '<a href="/nl/diensten/">Groeioptimalisatie</a>',
    'Tools',
    '<a href="/nl/funnel-calculator/">Funnel Leak Calculator</a>',
    '<a href="/nl/roi-forecaster/">ROI Forecaster</a>',
    '<a href="/nl/gratis-groeigids/">Gratis groeigids</a>',
    '<a href="/nl/boardroom-quickscan/">Boardroom Quickscan</a>',
    '© 2026 Booming Venture. Alle rechten voorbehouden.',
    '<a href="/nl/privacybeleid/">Privacybeleid</a>',
    # 50
    '<a href="/nl/disclaimer/">Disclaimer</a>',
    '<a href="/nl/algemene-voorwaarden/">Algemene voorwaarden</a>',
    None,  # [bv_lang_switch]
]

# Block-attribute strings (not in HTML text): exact replacements per file.
ATTRS = {
    "parts/header.html": [
        ('"label":"About","url":"/about/"', '"label":"Over ons","url":"/nl/over-ons/"'),
        ('"label":"UNIFY Framework","url":"/unify-framework/"', '"label":"UNIFY Framework","url":"/nl/unify-framework/"'),
        ('"label":"Services","url":"/services/"', '"label":"Diensten","url":"/nl/diensten/"'),
        ('"label":"Funnel Calculator","url":"/funnel-calculator/"', '"label":"Funnel Calculator","url":"/nl/funnel-calculator/"'),
        ('"label":"ROI Forecaster","url":"/roi-forecaster/"', '"label":"ROI Forecaster","url":"/nl/roi-forecaster/"'),
        ('"label":"Blog","url":"/blog/"', '"label":"Blog","url":"/nl/blog/"'),
        ('"label":"Contact","url":"/#contact"', '"label":"Contact","url":"/nl/#contact"'),
        ('<a href="/" class="bv-brand">', '<a href="/nl/" class="bv-brand">'),
    ],
    "parts/footer.html": [
        ('<a href="/" class="bv-brand">', '<a href="/nl/" class="bv-brand">'),
    ],
    "templates/home.html": [
        ('"label":"← Previous"', '"label":"← Vorige"'),
        ('"label":"Next →"', '"label":"Volgende →"'),
    ],
    "templates/single.html": [
        ('"byline":"By"', '"byline":"Door"'),
    ],
}
