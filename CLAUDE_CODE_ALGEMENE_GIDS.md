# Claude Code , de algemene gids

> **Doel:** één praktische Nederlandse handleiding voor het werken
> met Claude Code op softwareprojecten. Van repository-setup tot
> prompten, tools, iteratie, security en release-workflow.
>
> **Positief geframed:** deze gids beschrijft alleen wat je WEL moet
> doen. Elke regel is een concrete stap of best practice.
>
> **Gebaseerd op:** de OndernemerMarketing.nl + Booming Venture +
> CiteLeap plugin projecten in dit repo. Real-world lessons, geen
> theorie.

---

## Deel 1 , Wat Claude Code is en wanneer je het gebruikt

### 1.1 Wat het is

Claude Code is een AI coding-assistent die in jouw repository leeft.
Het:
- Leest en schrijft bestanden direct in je werkdirectory.
- Runt shell-commando's (php lint, npm build, git, python).
- Doet web-research (WebSearch, WebFetch) om buiten zijn
  training-cutoff te kijken.
- Coördineert multi-step taken zoals "bouw een custom WordPress
  block theme met 12 pagina's, 3 custom blocks, WCAG 2.2 AA
  compliance en ship als zip".
- Kan met subagents parallel werken op onafhankelijke deel-taken.

### 1.2 Wanneer je Claude Code inzet

**Doe:** gebruik Claude Code voor:
- Complete features implementeren (theme, plugin, API integratie).
- Refactoring cross-file (rename, API-migratie, dependency-upgrade).
- Research + spec writing (dive in nieuwe docs, samenvatting).
- Bug-fixing waar je de oorzaak nog niet weet (Grep + Read exploratie).
- Repetitief werk automatiseren (asset optimization, image
  conversion, translation string extraction).
- Ship-ready releases: van code naar getest-gebouwd-gezipt-gepushed.

**Doe:** voor micro-taken (één regel wijzigen) , doe het zelf sneller.

---

## Deel 2 , Repository setup , doe dit één keer per repo

### 2.1 CLAUDE.md in de repo root

**Doe:** creëer `CLAUDE.md` bovenin je repo. Dit is de eerste file
die Claude bij elke sessie leest. Zet erin:

- **Project shape** , CMS/framework/language versies, directory
  layout, key files.
- **Required behaviour** , voor content-projecten: voice rules,
  brand tokens, GEO/AEO regels; voor code-projecten: style guide,
  linter rules.
- **Never** , een korte lijst harde regels ("nooit `curl`",
  "nooit Google Fonts CDN"). Dit is dé plek voor "no exceptions"
  policies.
- **Dev commands** , exacte shell-regels voor lint, test, build,
  zip. Copy-paste voorbeelden.
- **Commit conventions** , semver, prefix (`feat:`/`fix:`/`chore:`/
  `docs:`/`content:`), 1 concern per commit.
- **Version bump rules** , welke bestanden moeten mee bumpen bij
  elke release (style.css + constant + zip filename, bijvoorbeeld).
- **Versioned artifact rules** , elke release een nieuwe zip met
  versie in filename; nooit een versie hergebruiken.
- **Branch** , welke feature-branch is de sessie assigned aan.

**Doe:** houd CLAUDE.md onder de 500 regels. Wat niet in CLAUDE.md
past, verwijs naar aparte docs (`REBUILD.md`, `SECURITY.md`, etc.).

### 2.2 .claude/settings.json voor hooks

**Doe:** installeer minimaal deze hooks:

- **`session-start` hook** , runt bij sessie-start. Doe hier
  environment checks (juiste PHP versie, node_modules geïnstalleerd,
  git branch check).
- **`stop` hook** , runt aan het einde van elke assistant turn.
  Doe hier `git status` check + waarschuw voor unpushed commits +
  waarschuw voor untracked files.
- **`post-tool-use` hook** (optioneel) , runt na elke Edit/Write.
  Doe hier lint checks op de gewijzigde files.

Zodra je hooks werkende hebt, gaat je code-kwaliteit meetbaar
omhoog omdat elke sessie automatisch dezelfde discipline volgt.

### 2.3 .gitignore voor Claude-generated artefacten

**Doe:** voeg minimaal deze patterns toe:
```
# Python
__pycache__/
*.pyc

# Node
node_modules/
dist/
build/

# PHP dev tooling
vendor/
composer.lock
.phpunit.cache/

# OS + editor
.DS_Store
.idea/
.vscode/
*.swp
*.log

# Reference material (niet essentieel voor de build)
lovable-source/
```

Zo blijven per-sessie build-artefacten uit je git-history.

### 2.4 Skills en slash commands (herbruikbaar proces)

**Doe:** voor terugkerende taken , registreer een skill in
`.claude/skills/`. Bijvoorbeeld:
- `geo-writer` , triggert automatisch als Claude blog-content
  bewerkt en enforced GEO/AEO regels.
- `citation-finder` , zoekt bronnen op wanneer een claim mist.
- `verify` , runt de app en checkt of een change echt werkt.

Slash commands in `.claude/commands/` , kort schrift dat een
volledige workflow in één commando aanroept:
- `/geo-audit <slug>` , runt een 8-stappen GEO/AEO audit op een
  post.
- `/optimize-passage <file> <heading>` , herschrijft een passage
  naar cite-worthy.

---

## Deel 3 , De juiste manier van prompten

### 3.1 Wees specifiek over het eindresultaat

**Doe:** noem het doel, de context, en de done-conditie in één
prompt.

Goed:
> "Bouw een custom WordPress block theme `ondernemer-marketing`
> voor de OndernemerMarketing.nl brand. WordPress 6.7+, WCAG 2.2
> AA, self-hosted fonts. Alle 6 screenshots op de branch moeten
> pixel-close matchen. Ship als uploadable zip aan het einde."

Minder goed:
> "Maak een WordPress theme."

Waarom: Claude interpreteert vaag prompts als "ik wil dat je een
scaffold maakt". Specifieke prompts krijgen production-grade output.

### 3.2 Geef context expliciet

**Doe:** noem bestandspaden, branch-namen, versies, gerelateerde
prior work. Voorbeelden:

- "Op branch `Ondernemermarketing`. De 6 design screenshots staan
  in de repo root (`Ondernemermarketing-nl-...screen-*.png`)."
- "Gebruik dezelfde install-hook pattern als in
  `wp-content/themes/ondernemer-marketing/inc/install.php`."
- "Referentie: het CiteLeap plugin `REBUILD.md` op
  `claude/citeleap-2` branch heeft de rebuild pattern."

Zo hoeft Claude niet te gokken , hij springt direct naar de juiste
bestanden.

### 3.3 Corrigeer expliciet als iets niet klopt

**Doe:** als Claude iets doet dat je niet wilt, noem in de volgende
prompt EN wat je wilt EN wat je NIET wilt:

Goed:
> "De patterns zijn te heavy met inline `style="..."` attributes.
> Refactor ze naar utility-classes uit `theme.json` presets. Doel:
> minder dan 50% inline style per pattern."

Minder goed:
> "Doe het beter."

### 3.4 Verwacht multi-step werk in één prompt

**Doe:** Claude Code werkt goed met samengestelde prompts:
> "1. Optimaliseer alle PNG's naar WebP q82.
>  2. Update de install.php map filenames.
>  3. Regenerate de screenshot.png.
>  4. Bump theme versie naar 1.2.0.
>  5. Rebuild de zip.
>  6. Commit en push naar Ondernemermarketing."

Claude planned dit als een sequence, doet stap 1-6 in één turn, en
rapporteert onderweg.

### 3.5 Vraag om verificatie

**Doe:** eindig met "en verifieer" of "en test" zodat Claude niet
alleen code schrijft maar ook checkt of het werkt:

> "Bouw de contact-form handler. Verifieer met `php -l` en test dat
> nonce + honeypot + PRG-pattern allemaal werken."

Voor een landing page:
> "Deploy lokaal en maak een screenshot van de homepage op mobile
> viewport."

### 3.6 Referenties naar externe docs

**Doe:** als Claude buiten zijn training-cutoff moet werken (2026
WordPress features, nieuwe API's), vraag expliciet om
web-research:

> "Doe eerst een WebSearch naar de huidige WCAG 2.2 AA
> requirements voor WordPress themes (mei 2026). Baseer je
> implementatie daarop, niet op wat je uit je training weet."

Claude zal dan real WebSearch calls doen en de resultaten
verwerken.

### 3.7 Geef acceptatie-criteria als checklist

**Doe:** voor complexe features , geef expliciet een
checklist die "done" definieert:

> "De theme is done als:
>  1. `php -l` slaagt op elke .php file
>  2. `theme.json` valideert tegen schema v3
>  3. Lighthouse mobile > 90 op Performance
>  4. axe-core: 0 serious/critical violations
>  5. Zip < 1 MB
>  6. Alle 11 pagina's auto-created op activatie"

Claude kan de lijst dan systematisch afwerken.

---

## Deel 4 , Werken met bestanden , de tools

### 4.1 Read voor grote bestanden

**Doe:** vóór je een file bewerkt , eerst Read. Claude's Edit-tool
weigert Edit zonder voorafgaande Read. Dat is bewust: het voorkomt
"blind edit" bugs.

**Doe:** voor bestanden > 2000 regels , gebruik Read met
`offset` + `limit` om alleen relevante secties te bekijken.

### 4.2 Grep voor code-exploratie

**Doe:** gebruik Grep (niet `grep` in Bash) voor content search.
Grep is ripgrep onder de motorkap , sneller en met betere output-
modes:

- `output_mode: "files_with_matches"` , alleen paths.
- `output_mode: "content"` , volledige matches met context.
- `output_mode: "count"` , alleen match-counts per file.
- `-n` toggle voor regelnummers.
- `type: "php"` beperkt tot bestandstype.

### 4.3 Glob voor bestandspatronen

**Doe:** Glob voor "vind alle X" queries: `**/*.tsx`,
`wp-content/themes/*/style.css`. Sneller dan Bash `find`.

### 4.4 Edit met exacte context

**Doe:** Edit vereist een `old_string` die uniek is in het bestand.
Als je een generieke regel wilt vervangen, geef 2-3 regels context
mee zodat de match uniek is.

Voorbeeld goed:
```
old_string: "function foo() {
    return true;
}"
```

Voorbeeld fout (te generiek):
```
old_string: "return true;"
```

### 4.5 Write voor nieuwe files of complete overschrijven

**Doe:** gebruik Write voor:
- Nieuwe bestanden aanmaken.
- Complete overschrijven van een file (bijv. `theme.json` regenereren
  vanuit scratch).

Voor incrementele wijzigingen , altijd Edit, nooit Write.

### 4.6 Bash voor shell-commando's

**Doe:** Bash voor:
- `git` operations (status, add, commit, push).
- Build/lint/test (`php -l`, `python3 -c`, `zip`, `unzip`).
- File-system operations die geen dedicated tool voor is
  (`mkdir -p`, `cp`, `mv`).

**Doe:** gebruik Read/Edit/Write/Glob/Grep in plaats van Bash's
`cat`/`sed`/`awk`/`find`/`grep` , die dedicated tools geven betere
UX (permissions, error handling, output-parsing).

### 4.7 WebSearch voor actuele info

**Doe:** WebSearch voor:
- Nieuwe API's voorbij training-cutoff.
- Actuele policy-updates (WCAG, GDPR, WordPress core features).
- Prijsvergelijkingen (LLM tiers, hosting).
- Best-practice benchmarks.

### 4.8 WebFetch voor specifieke documentatie-pagina's

**Doe:** WebFetch als je één specifieke URL wilt samenvatten:
- "Vat de WCAG 2.2 succes-criteria samen die nieuw zijn t.o.v.
  2.1."
- "Lees deze API-doc en geef me de payload shape."

Let op: WebFetch faalt op authenticated URLs (Google Docs,
GitHub private repos). Gebruik dan de MCP-tool voor die service.

### 4.9 MCP-tools voor externe services

**Doe:** als de git-proxy in je sessie geen push-auth heeft ,
gebruik `mcp__github__create_or_update_file` of
`mcp__github__push_files` om files direct via de GitHub API te
committen. Werkt onafhankelijk van git-auth.

Voor andere MCP-servers (Supabase, Google Drive, Slack) , zelfde
patroon: als je server-side moet interacteren, MCP-tools zijn de
route.

---

## Deel 5 , Werken met branches en commits

### 5.1 Één branch per product/feature

**Doe:** voor elke coherente eenheid werk , een dedicated branch:
- `claude/lovable-to-wordpress-theme-lnXza` , theme development
- `Ondernemermarketing` , specifieke merk-conversie
- `claude/citeleap-2` , plugin development
- `claude/blog-images-plugin` , losse plugin

Branch-per-project maakt cherry-picking + code review + zip-shipping
overzichtelijk.

### 5.2 Commit vaak, elk met één concern

**Doe:** één logische wijziging per commit. Voorbeelden goede
commits:

- `feat: OndernemerMarketing theme v1.1.0 , images + auto-install`
- `perf: OndernemerMarketing theme v1.2.0 , WebP conversie 90%`
- `docs: add LOVABLE_NAAR_WORDPRESS_GIDS.md`
- `chore: bump PHP requirement to 8.1+`

Minder goed:
- `updates` (te vaag)
- `misc changes` (meerdere concerns)

### 5.3 Semver bij elke release

**Doe:** volg strak:
- **`feat:`** , minor bump (1.2.0 , 1.3.0)
- **`fix:`** of **`perf:`** of **`chore:`** , patch bump (1.2.0 , 1.2.1)
- Breaking change , major bump (1.2.0 , 2.0.0)

**Doe:** bij een theme-release bump je in 3 plekken tegelijk:
1. `style.css` , `Version: 1.2.0`
2. `functions.php` , `const XXX_THEME_VERSION = '1.2.0'`
3. Zip filename , `theme-slug-1.2.0.zip`

Als deze uit sync raken breekt WordPress' update-detectie.

### 5.4 Push na elke commit

**Doe:** setup een stop-hook die je waarschuwt bij unpushed
commits. Zo eindig je nooit een sessie met werk dat alleen lokaal
staat. Als git-push faalt (auth issue) , gebruik MCP als backup.

### 5.5 Commit-messages met context

**Doe:** een goede commit-message bevat:
- **Wat** veranderd is (in 1 regel titel)
- **Waarom** de wijziging nodig was (in de body)
- **Details** per file als het niet obvious is
- **Session-URL** onderaan zodat je de historie kunt herleiden

---

## Deel 6 , Iteratie , van v0.1 naar v1.0

### 6.1 Ship vroeg, verbeter incrementeel

**Doe:** shippe de eerste versie zodra hij "werkt", ook al is hij
niet perfect. Voor OndernemerMarketing.nl:

- **v1.0.0** , theme met emoji placeholders. Werkt, ziet er niet
  af uit.
- **v1.1.0** , real photos + auto-install + 3 custom blocks.
  Ziet er af uit, images zijn traag.
- **v1.2.0** , images geoptimaliseerd naar WebP. Fast + af.

Elke versie is uploadbaar en meetbaar. Als je eerst v1.2.0 had
proberen te bouwen, was je 3x zo lang bezig zonder tussentijdse
validatie.

### 6.2 Vraag om een score, niet om perfectie

**Doe:** ná een release , vraag Claude om een objectieve score:
> "Geef de theme een score van 1-10 en breek af per dimensie
> (architectuur, security, accessibility, performance, design
> fidelity, docs)."

Claude zal eerlijk zijn , een 7.6/10 is een reëler startpunt om
te verbeteren dan een geclaimde 10/10.

### 6.3 Verbeter de laagste-scorende dimensie eerst

**Doe:** na de score , pak de laagste dimensie en verbeter die.
Bij OndernemerMarketing was dat Performance (5/10 door zware
PNG's). Één WebP-conversie sessie later: 8/10, overall van 7.6
naar 8.0.

### 6.4 Versioneer per iteratie

**Doe:** elke verbetering = nieuwe patch/minor. Niet één grote
"v2.0" na maanden werk, maar v1.0.1, v1.0.2, v1.1.0, v1.2.0.
Elke versie een aparte zip, elk aparte commit. Zo kun je
terug-rollen naar elk punt.

---

## Deel 7 , Security tijdens Claude-sessies

### 7.1 Scan externe exports altijd op secrets

**Doe:** voordat je een Lovable-zip / Bolt-export / v0-download
committet, run:
```bash
grep -rE "xkeysib-|sk-[a-zA-Z0-9]{20,}|sk-ant-[a-zA-Z0-9-]{40,}|AIza[a-zA-Z0-9_-]{30,}|Bearer [a-zA-Z0-9]{20,}" .
```

Vindt Brevo/OpenAI/Anthropic/Google API keys + Bearer tokens.

### 7.2 GitHub push-protection is een safety net

**Doe:** neem GitHub's secret-scanning push protection serieus.
Als een push wordt geweigerd met "Sendinblue API Key" of soortgelijk,
er zit ECHT een secret in je commit. Fix:
1. Reset de commit soft: `git reset --soft HEAD~1`
2. Redigeer het secret uit de source (vervang met env var lookup)
3. Re-commit + re-push
4. **Rotate de key** in de provider dashboard (hij zit in git
   history + lokale zip)

### 7.3 Nooit keys hardcoderen in code die Claude schrijft

**Doe:** in Claude-generated code , altijd via env / config
constant / encrypted option:
```php
$key = defined( 'MJM_BREVO_KEY' ) ? MJM_BREVO_KEY : (string) get_option( 'mjm_brevo_key_encrypted' );
```

Vraag Claude expliciet: "Nooit een key hardcoderen. Lees via
constant of encrypted option."

### 7.4 API keys uit je sessie-transcript

**Doe:** als je per ongeluk een key in een prompt hebt geplakt ,
rotate hem sowieso. Sessie-transcripts kunnen worden bewaard voor
model-verbetering. Wat je pastet, veronderstel dat het bewaard is.

---

## Deel 8 , Wanneer subagents inzetten

### 8.1 Explore agent voor code-exploratie

**Doe:** voor "waar is X gedefinieerd" / "welke files gebruiken Y":

```
Task tool met subagent_type=Explore:
"Zoek alle plekken in dit repo waar CITELEAP_META_LANG wordt
gelezen of geschreven. Geef paths + regelnummers."
```

De Explore agent doet Grep + Glob rounds op de achtergrond en
levert een gestructureerd rapport. Bespaart context in je main
sessie.

### 8.2 Plan agent voor architectuur-beslissingen

**Doe:** voor complexe features , eerst Plan agent inzetten:

```
"Ontwerp een plan voor een SaaS licentie-server die CiteLeap
plugins per site autoriseert. Overweeg Freemius vs Stripe direct
vs self-hosted EDD-SL. Recommendatie graag met trade-offs."
```

Krijg je een implementatieplan terug met stappen en beslispunten
zonder dat er meteen code geschreven wordt.

### 8.3 General-purpose voor open-eindige research

**Doe:** voor multi-round zoekwerk:

```
"Onderzoek wat de current-2026 best practice is voor WordPress
plugin licensing servers. Vergelijk Freemius / EDD-SL /
plugin-update-checker. Geef me een aanbeveling."
```

### 8.4 Parallel subagents voor onafhankelijke deel-taken

**Doe:** als je 3 onafhankelijke research-vragen hebt , start 3
subagents in één message (multiple tool-calls in dezelfde
response). Ze runnen parallel , 3x sneller dan sequentieel.

---

## Deel 9 , Verifieer dat het werkt

### 9.1 PHP: `php -l` op elk PHP bestand

**Doe:** na elke PHP wijziging:
```bash
find wp-content/plugins/xxx -name "*.php" -exec php -l {} \; 2>&1 | grep -v "No syntax errors"
```

Verwacht: zero output (elke file is OK).

### 9.2 JSON: validate elk theme.json + block.json

**Doe:**
```bash
python3 -c "import json,glob; [json.load(open(f)) for f in glob.glob('wp-content/themes/*/theme.json') + glob.glob('wp-content/themes/*/blocks/*/block.json')]; print('OK')"
```

### 9.3 XML: validate WXR imports

**Doe:**
```bash
python3 -c "import xml.etree.ElementTree as ET; ET.parse('import/content.xml'); print('OK')"
```

### 9.4 Unit-tests waar aanwezig

**Doe:** voor projecten met PHPUnit / vitest / jest:
```bash
cd wp-content/plugins/citeleap && ./vendor/bin/phpunit
```

Streef naar 100% pass rate voor elke release.

### 9.5 Visual verify: screenshot of live view

**Doe:** voor UI-changes , vraag Claude om te "verify":
- Lokale server runnen (Studio.wordpress.com, LocalWP)
- Screenshot maken
- Vergelijken met design

Claude kan de screenshot bekijken en zien of het klopt met het
oorspronkelijke design.

### 9.6 Lighthouse + axe voor productie

**Doe:** ná elke release , run Lighthouse mobile op de deploy:
- Performance ≥ 90
- Accessibility = 100
- SEO ≥ 95
- Best Practices ≥ 95

Als een score onder target zakt , dat is de volgende sessie's
prioriteit.

---

## Deel 10 , Ship-workflow , van commit naar release

### 10.1 De 12-stappen ship-checklist

**Doe:** voor elke release, voer deze volgorde uit:

1. **Bump versies** in de 3 markers (style.css / constant / zip).
2. **PHP lint** op elk .php bestand.
3. **JSON validate** op theme.json + block.json.
4. **Unit tests** runnen (indien aanwezig).
5. **Rebuild zip** met `zip -rq theme-slug.zip theme-slug -x "*.DS_Store" "*/node_modules/*"`.
6. **Verifieer versie IN de zip** via `unzip -p theme-slug.zip theme-slug/style.css | grep Version`.
7. **Commit** met semver-conform message + session-URL footer.
8. **Push** naar origin (git-push OF MCP als fallback).
9. **Deliver** de zip aan de operator (SendUserFile).
10. **Update de changelog** (readme.txt bij WP-plugins, README.md
    bij themes).
11. **Tag de release** (optioneel , `git tag theme-slug-1.2.0`).
12. **Rapporteer** wat er in v1.2.0 nieuw is, in de sessie output.

### 10.2 De zip-naming conventie

**Doe:** ship altijd twee bestanden:
- **Rolling:** `theme-slug.zip` (zelfde naam elke release).
- **Versioned:** `theme-slug-1.2.0.zip` (versie in filename).

De versioned zip is het bewijs welke versie de operator uploadt +
voorkomt cache-confusion in WP-admin. Ship ze allebei aan het
einde van de sessie.

---

## Deel 11 , Documenteer terwijl je bouwt

### 11.1 REBUILD.md per major project

**Doe:** voor elk theme/plugin , maak `REBUILD.md` in de root:
- Wat het project is (mission in 1 paragraaf).
- Waar te beginnen met lezen als je from-scratch moet rebuilden.
- Architectuur diagram (ASCII data-flow OK).
- User stories per persona.
- Interdependencies (module , module).
- Sprint plan om te rebuilden.
- Common pitfalls + hoe te vermijden.

Zo kan een toekomstige Claude Code sessie (of contractor) de
volledige stack rebuilden zonder je oude chat-historie.

### 11.2 Changelog per release

**Doe:** in de main file van het project (`readme.txt` voor
WP-plugins, `README.md` voor themes), houd een changelog bij:

```
= 1.2.0 =
* PERF: images geconverteerd naar WebP (90% zip-besparing).

= 1.1.0 =
* NEW: bundled brand images + auto-install hook.
* NEW: 3 custom blocks voor Tools pagina.

= 1.0.0 =
* Initial release.
```

Één bullet per notable wijziging. Bumped bij elke release.

### 11.3 Docs die de sessie overleven

**Doe:** commit deze docs die je uit sessie-kennis kunt ophalen:
- **CLAUDE.md** , project-wide instructies voor toekomstige sessies.
- **REBUILD.md** , complete rebuild-blueprint per project.
- **RESEARCH_*.md** , research briefs (WCAG 2026, LLM pricing, etc.)
  met sources cited.
- **BUILD_PLAN_*.md** , specifieke project-plannen.

Sessies verdwijnen; commits blijven. Wat niet gecommit is, is
verloren op sessie-einde.

---

## Deel 12 , Vragen aan de operator

### 12.1 Wanneer je de user echt moet vragen

**Doe:** gebruik `AskUserQuestion` alleen als:
- Er een beslissing is die de user moet nemen (branding, prijzen,
  domein).
- Er meerdere geldige interpretaties zijn van de instructie.
- De keuze operationeel is (welke hoster, welke SMTP provider).

**Doe niet vragen** voor:
- Technische executie waar één correcte manier voor is.
- Details die uit de context af te leiden zijn.
- Verificatie of iets "goed genoeg" is , doe het zelf en presenteer.

### 12.2 Stel gerichte multi-choice vragen

**Doe:** als je vraagt , geef 2-4 concrete opties met korte
beschrijving:

Goed:
> "Voor de contact-form mail-integratie:
>  A. WP Mail SMTP + Brevo (aanbevolen, EU-hosted)
>  B. WP Mail SMTP + SendGrid (US-hosted)
>  C. Direct Brevo PHP SDK (geen SMTP-plugin nodig)"

Minder goed:
> "Welke mail-integratie wil je?"

### 12.3 Recommend altijd een default

**Doe:** eerste optie = de aanbeveling, gemarkeerd als
"(Aanbevolen)". Zo hoeft de user niet te kiezen als hij twijfelt.

---

## Deel 13 , Als er iets misgaat

### 13.1 Foutmeldingen lezen niet skippen

**Doe:** als een tool-call faalt (Bash error, Edit rejected,
push declined) , lees de error message volledig, snap wat er
gebeurt, en fix root cause. Niet werk-arounds proberen totdat
het toevallig werkt.

Voorbeeld , push declined omdat GitHub secret-scanning een key
detecteert: root cause = secret in code. Fix = redigeer + rotate.
Niet: `git push --force` proberen.

### 13.2 Rollback via git

**Doe:** als een commit vernietigend werk introduceert:
```bash
git reset --soft HEAD~1  # ongedaan maar changes blijven staged
git reset --hard HEAD~1  # ongedaan + changes weg (VOORZICHTIG)
git revert HEAD          # nieuwe commit die de vorige teniet doet
```

Vraag Claude expliciet welke reset-vorm je nodig hebt , hij zal
uitleggen wat waarnaar wijst.

### 13.3 Sessie afsluiten met open werk

**Doe:** als je stopt met werk-in-progress:
1. Commit alles met een clear "WIP:" prefix message.
2. Push naar de branch.
3. Voeg toe aan CLAUDE.md of aparte file: "Volgende sessie ,
   pick up bij X, met status Y."

Zo pak je (of de volgende Claude sessie) waar je gebleven was.

---

## Deel 14 , Multi-project repos

### 14.1 Branch = product/feature

**Doe:** in een monorepo met meerdere producten , één branch per
product:
- `Ondernemermarketing` , OM.nl theme
- `claude/citeleap-2` , CiteLeap plugin
- `claude/blog-images-plugin` , losse plugin
- `main` , de canonical / production branch

### 14.2 CLAUDE.md kan wijzen naar branch-specifieke docs

**Doe:** in CLAUDE.md , noem welke branches actief zijn en waar
de per-branch docs staan:
```
Actieve product-branches:
- Ondernemermarketing , zie WORDPRESS_SITE_REBUILD_GUIDE.md
- claude/citeleap-2 , zie wp-content/plugins/citeleap/REBUILD.md
```

### 14.3 Cross-branch merges vermijden

**Doe:** houd product-branches onafhankelijk. Als een fix in beide
branches moet , cherry-pick expliciet. Pull-requests + merges tussen
product-branches leiden tot spaghetti.

---

## Deel 15 , De algemene workflow , stap voor stap

Voor elk substantieel Claude Code project:

1. **Setup** , CLAUDE.md + `.claude/settings.json` hooks + `.gitignore`.
2. **Research** , als de tech nieuw is , WebSearch + WebFetch +
   Plan agent voor de aanpak.
3. **Sprint 1: skeleton** , de kern van het project, minimalistisch,
   ship v0.1.0 zodra het "werkt".
4. **Sprint 2-N: features** , per sprint één commit + versie-bump +
   zip + push.
5. **Score** , vraag Claude om objectieve score op je hoofdproject
   dimensies.
6. **Verbeter** , pak de laagste dimensie, één sprint per
   verbetering.
7. **Documenteer** , REBUILD.md + changelog + research briefs.
8. **Ship** , finale versie + versioned zip + release-tag.
9. **Post-launch** , monitor + itereer op basis van real-world data.

---

## Samenvatting , de gouden regels

**Doe:** volg deze 10 regels bij elk Claude Code project:

1. **CLAUDE.md** in de repo root , de eerste file van elke sessie.
2. **Wees specifiek** in prompts , eindresultaat + context + done-conditie.
3. **Corrigeer expliciet** , noem wat je wilt EN wat je niet wilt.
4. **Verifieer altijd** , php lint, JSON validate, tests, Lighthouse.
5. **Semver bumpen** in 3 markers tegelijk , style.css + constant + zip.
6. **Commit vaak, één concern per commit** , met session-URL footer.
7. **Push na elke commit** , stop-hook waarschuwt bij unpushed werk.
8. **Documenteer terwijl je bouwt** , REBUILD.md + changelog + research briefs.
9. **Ship vroeg, verbeter incrementeel** , v0.1 werkt, dan v1.0 polish, dan v1.2 perf.
10. **Nooit secrets in code** , env constant of encrypted option; scan
    externe exports voor commit.

Volg deze regels en Claude Code wordt van "handig assistent" tot
"volwaardige junior/mid developer die 4-6 uur werk per sessie in
1 uur real-time doet".

---

## Referenties

- **Claude Code docs**: <https://docs.claude.com/en/docs/claude-code>
- **Anthropic API docs**: <https://docs.claude.com/en/api>
- **WordPress Theme Handbook**: <https://developer.wordpress.org/themes/>
- **Deze branch's verwante docs**:
  - `LOVABLE_NAAR_WORDPRESS_GIDS.md` , specifieke Lovable-conversie
  - `WORDPRESS_SITE_REBUILD_GUIDE.md` , abstract WP-site pattern
  - `CUSTOM_THEME_RESEARCH_2026.md` , 2026 WP-theme policy research
  - `ONDERNEMERMARKETING_BUILD_PLAN.md` , dit project's spec
  - `wp-content/plugins/citeleap/REBUILD.md` (op branch
    `claude/citeleap-2`) , plugin rebuild blueprint

---

*Einde gids. Deze werkwijze is gevalideerd op de OndernemerMarketing
theme conversie (7 sessies, 1.279-regels documentatie, WCAG 2.2 AA
theme in ~8 uur real-time). Volgende iteratie: update deze gids op
basis van de eerstvolgende Claude Code sessie waar iets nieuws is
geleerd.*
