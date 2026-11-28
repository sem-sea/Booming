"""Sector pages of the B2B GEO Index (1.30.0): one page per sector and language.
The numbers come live from the geo-sector block; the intro is written per sector."""
from blocks import hero, raw_block

# key: (nl slug, en slug, nl name phrase, en name phrase, nl intro, en intro)
SECTORS = {
    "c20": ("chemie", "chemicals", "in de chemie", "in chemicals",
            "Inkopers van chemicaliën zoeken leveranciers met de juiste specificaties, certificeringen en leverzekerheid. Steeds vaker vragen ze AI-assistenten om een eerste shortlist van producenten en distributeurs.",
            "Chemical buyers look for suppliers with the right specifications, certifications and security of supply. More and more often they ask AI assistants for a first shortlist of producers and distributors."),
    "c26": ("elektronica", "electronics", "in elektronica en elektrotechniek", "in electronics and electrical equipment",
            "Technische kopers zoeken componenten, systemen en installateurs op specificatie en toepassing. Ze vragen AI om leveranciers te vergelijken voordat ze een offerte aanvragen.",
            "Technical buyers look for components, systems and installers by specification and application. They ask AI to compare suppliers before they request a quote."),
    "c28": ("machinebouw", "machinery", "in de machinebouw", "in machinery",
            "Kopers van machines en productielijnen zoeken een bouwer die hun proces begrijpt. AI-assistenten helpen hen om bouwers per toepassing en per land te vinden.",
            "Buyers of machines and production lines look for a builder that understands their process. AI assistants help them find builders by application and by country."),
    "c29": ("voertuigen-schepen-vliegtuigen", "vehicles-ships-aircraft", "in voertuigen, schepen en vliegtuigen", "in vehicles, ships and aircraft",
            "Vlootbeheerders, rederijen en luchtvaartbedrijven kiezen fabrikanten op betrouwbaarheid, service en levertijd. AI-assistenten geven hen een eerste beeld van de makers en hun reputatie.",
            "Fleet managers, shipping lines and aviation companies choose manufacturers on reliability, service and delivery time. AI assistants give them a first view of the makers and their reputation."),
    "c35": ("energie", "energy", "in de energiesector", "in energy",
            "Bedrijven zoeken een energieleverancier, netaansluiting of duurzame oplossing en vergelijken aanbieders op prijs en zekerheid. AI-assistenten worden gebruikt om die markt snel in kaart te brengen.",
            "Companies look for an energy supplier, a grid connection or a sustainable solution and compare providers on price and certainty. AI assistants are used to map that market quickly."),
    "c42": ("infrastructuur-waterbouw", "civil-engineering", "in infrastructuur en waterbouw", "in civil engineering",
            "Opdrachtgevers in infrastructuur en waterbouw zoeken aannemers met referenties en specialistische kennis. AI-assistenten noemen vooral bedrijven die online duidelijk laten zien wat ze bouwen en waar.",
            "Clients in infrastructure and hydraulic engineering look for contractors with references and specialist knowledge. AI assistants mostly name companies that show clearly online what they build and where."),
    "c46": ("groothandel", "wholesale", "in de groothandel", "in wholesale",
            "Zakelijke kopers zoeken een groothandel op assortiment, levertijd en voorwaarden. Met AI vergelijken ze leveranciers in hun branche zonder eerst tientallen websites te openen.",
            "Business buyers choose a wholesaler on range, delivery time and terms. With AI they compare suppliers in their trade without opening dozens of websites first."),
    "c49": ("transport-logistiek", "transport-logistics", "in transport en logistiek", "in transport and logistics",
            "Verladers zoeken vervoerders en logistieke dienstverleners op route, capaciteit en specialisme. AI-assistenten helpen hen om partijen per land en per type lading te vinden.",
            "Shippers look for carriers and logistics providers by route, capacity and specialism. AI assistants help them find providers by country and by type of cargo."),
    "c581": ("vakinformatie-uitgeverijen", "professional-publishing", "in vakinformatie en uitgeverijen", "in professional publishing",
             "Professionals zoeken betrouwbare vakinformatie, databanken en opleidingen. AI-assistenten concurreren hier met de uitgevers zelf en citeren tegelijk hun content.",
             "Professionals look for reliable expert information, databases and training. Here AI assistants compete with the publishers themselves while citing their content."),
    "c582": ("software", "software", "in software", "in software",
             "Softwarekopers vragen AI om alternatieven, vergelijkingen en prijzen voordat ze een demo aanvragen. Wie in die antwoorden ontbreekt, komt niet op de shortlist.",
             "Software buyers ask AI for alternatives, comparisons and prices before they book a demo. Vendors missing from those answers do not make the shortlist."),
    "c61": ("telecom", "telecommunications", "in telecom", "in telecommunications",
            "Bedrijven kiezen telecompartners op dekking, betrouwbaarheid en zakelijke diensten. AI-assistenten worden gebruikt om aanbieders voor de zakelijke markt naast elkaar te zetten.",
            "Companies choose telecom partners on coverage, reliability and business services. AI assistants are used to line up providers for the business market."),
    "c62": ("it-diensten", "it-services", "in IT-diensten", "in IT services",
            "Bedrijven zoeken IT-partners voor beheer, cloud, security en implementatie. AI-assistenten noemen daarbij vaak de bekende namen, en kleinere specialisten moeten die plek verdienen.",
            "Companies look for IT partners for managed services, cloud, security and implementation. AI assistants often name the familiar brands, and smaller specialists have to earn that place."),
    "c63": ("hosting-datacenters", "hosting-data-centers", "in hosting en datacenters", "in hosting and data centers",
            "Kopers vergelijken hosting- en datacenterpartijen op locatie, certificering en prijs. AI-assistenten helpen hen om aanbieders per land en per dienst te vinden.",
            "Buyers compare hosting and data center providers on location, certification and price. AI assistants help them find providers by country and by service."),
    "c64": ("banken-betalingen", "banking-payments", "in banken en betalingen", "in banking and payments",
            "Bedrijven kiezen banken, betaaldiensten en financieringspartners op kosten, gemak en betrouwbaarheid. AI-assistenten worden gebruikt om die opties naast elkaar te zetten.",
            "Companies choose banks, payment services and financing partners on cost, ease and reliability. AI assistants are used to put those options side by side."),
    "c65": ("verzekeringen", "insurance", "in verzekeringen", "in insurance",
            "Zakelijke klanten en adviseurs vergelijken verzekeraars op dekking, voorwaarden en schadeafhandeling. AI-assistenten geven hen een eerste overzicht van aanbieders.",
            "Business clients and brokers compare insurers on cover, terms and claims handling. AI assistants give them a first overview of providers."),
    "c69": ("juridische-dienstverlening", "legal-services", "in juridische dienstverlening", "in legal services",
            "Bedrijven zoeken advocatenkantoren en juridische dienstverleners op specialisme en reputatie. Rankings, gidsen en publicaties wegen daarbij zwaar, ook voor AI.",
            "Companies look for law firms and legal service providers by specialism and reputation. Rankings, directories and publications weigh heavily there, for AI as well."),
    "c70": ("managementadvies", "management-consulting", "in managementadvies", "in management consulting",
            "Directies zoeken adviesbureaus voor strategie, organisatie en transformatie. AI-assistenten geven hen een shortlist op basis van reputatie en specialisme.",
            "Executive teams look for consultancies for strategy, organization and transformation. AI assistants give them a shortlist based on reputation and specialism."),
    "c71": ("ingenieurs-testen-keuren", "engineering-testing-inspection", "in ingenieurs, testen en keuren", "in engineering, testing and inspection",
            "Opdrachtgevers zoeken ingenieursbureaus en keuringsinstanties met de juiste accreditaties. AI-assistenten moeten die erkenningen online kunnen vinden om een bureau te noemen.",
            "Clients look for engineering firms and inspection bodies with the right accreditations. AI assistants need to find those credentials online to name a firm."),
    "c73": ("reclame-marktonderzoek", "advertising-market-research", "in reclame en marktonderzoek", "in advertising and market research",
            "Marketingteams zoeken bureaus en onderzoekspartners op specialisme, cases en prijs. AI-assistenten noemen vaak bureaus die op lijstjes en in vakmedia staan.",
            "Marketing teams look for agencies and research partners by specialism, cases and price. AI assistants often name agencies that appear on lists and in trade media."),
    "c78": ("uitzenden-werving", "staffing-recruitment", "in uitzenden en werving", "in staffing and recruitment",
            "Bedrijven zoeken uitzend- en wervingsbureaus per vakgebied en regio. AI-assistenten helpen hen om bureaus te vergelijken op specialisme en bereik.",
            "Companies look for staffing and recruitment agencies by field and region. AI assistants help them compare agencies on specialism and reach."),
}

NAMES = {  # display names from companies.json: (nl, en)
    "c20": ("Chemie", "Chemicals"), "c26": ("Elektronica en elektrotechniek", "Electronics and electrical equipment"),
    "c28": ("Machinebouw", "Machinery"), "c29": ("Voertuigen, schepen en vliegtuigen", "Vehicles, ships and aircraft"),
    "c35": ("Energie", "Energy"), "c42": ("Infrastructuur en waterbouw", "Civil engineering"), "c46": ("Groothandel", "Wholesale"),
    "c49": ("Transport en logistiek", "Transport and logistics"), "c581": ("Vakinformatie en uitgeverijen", "Professional publishing"),
    "c582": ("Software", "Software"), "c61": ("Telecom", "Telecommunications"), "c62": ("IT-diensten", "IT services"),
    "c63": ("Hosting en datacenters", "Hosting and data centers"), "c64": ("Banken en betalingen", "Banking and payments"),
    "c65": ("Verzekeringen", "Insurance"), "c69": ("Juridische dienstverlening", "Legal services"), "c70": ("Managementadvies", "Management consulting"),
    "c71": ("Ingenieurs, testen en keuren", "Engineering, testing and inspection"), "c73": ("Reclame en marktonderzoek", "Advertising and market research"),
    "c78": ("Uitzenden en werving", "Staffing and recruitment"),
}


def meta(intro, extra):
    first = intro.split(". ")[0].rstrip(".") + "."
    return first + extra if len(first + extra) <= 158 else first


def sector_items():
    items = []
    for key, (nls, ens, nlp, enp, nli, eni) in SECTORS.items():
        nln, enn = NAMES[key]
        nl_body = "\n".join([
            hero("B2B GEO Index", "AI-zichtbaarheid " + nlp, nli),
            raw_block("booming-venture/geo-sector", {"sector": key, "align": "wide"}),
        ])
        en_body = "\n".join([
            hero("B2B GEO Index", "AI visibility " + enp, eni),
            raw_block("booming-venture/geo-sector", {"sector": key, "align": "wide"}),
        ])
        items.append({
            "key": "sector-" + key, "sector": key,
            "en": {"parent": "b2b-geo-index", "slug": ens, "title": "AI visibility " + enp, "content": en_body,
                   "meta": {"_yoast_wpseo_title": "AI visibility " + enp + ": ranking and checks | B2B GEO Index",
                            "_yoast_wpseo_metadesc": meta(eni, " Ranking and checks, measured monthly.")}},
            "nl": {"parent": "nl/b2b-geo-index", "slug": nls, "title": "AI-zichtbaarheid " + nlp, "content": nl_body,
                   "meta": {"_yoast_wpseo_title": "AI-zichtbaarheid " + nlp + ": ranglijst en checks | B2B GEO Index",
                            "_yoast_wpseo_metadesc": meta(nli, " Ranglijst en checks, elke maand gemeten.")}},
        })
    return items
