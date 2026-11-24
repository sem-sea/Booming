"""Build the Dutch theme files from the English ones.

Patterns:  patterns/<slug>.php  -> patterns/<slug>-nl.php
Templates and parts are written by hand (tools/i18n/templates_nl/) because
they also carry block attributes (nav labels, pagination labels).

Run from the repo root:  python3 tools/i18n/build_nl.py
"""
import json
import pathlib
import re
import sys

sys.path.insert(0, str(pathlib.Path(__file__).parent))
import blocktext  # noqa: E402
from nl_patterns import NL  # noqa: E402

ROOT = pathlib.Path(__file__).resolve().parents[2]
THEME = ROOT / "wp-content/themes/booming-venture"

PATTERNS = (
    "hero services-grid growth-guide-cta about testimonials blog-grid contact "
    "about-story about-founder core-values approach-steps why-choose-checklist cta-band "
    "services-detail additional-services process-steps tools-cards unify-hero "
    "unify-friction-to-flow unify-market-clarity unify-phases unify-modules unify-stats "
    "newsletter-cta newsletter-inline"
).split()

URLS = {
    "/": "/nl/",
    "/#contact": "/nl/#contact",
    "/about/": "/nl/over-ons/",
    "/services/": "/nl/diensten/",
    "/unify-framework/": "/nl/unify-framework/",
    "/funnel-calculator/": "/nl/funnel-calculator/",
    "/roi-forecaster/": "/nl/roi-forecaster/",
    "/blog/": "/nl/blog/",
    "/free-growth-guide/": "/nl/gratis-groeigids/",
    "/boardroom-quickscan/": "/nl/boardroom-quickscan/",
    "/head-of-growth/": "/nl/head-of-growth/",
    "/privacy-policy/": "/nl/privacybeleid/",
    "/cookie-policy/": "/nl/cookiebeleid/",
    "/terms-of-service/": "/nl/algemene-voorwaarden/",
    "/disclaimer/": "/nl/disclaimer/",
}


def map_hrefs(text):
    def rep(m):
        q, url = m.group(1), m.group(2)
        path = re.sub(r"^https://boomingventure\.com", "", url)
        if path in URLS:
            return f"href={q}{URLS[path]}{q}"
        return m.group(0)

    return re.sub(r'href=(["\'])((?:https://boomingventure\.com)?/[^"\']*)\1', rep, text)


def pattern_table():
    keys = []
    for name in PATTERNS:
        for k in blocktext.extract((THEME / "patterns" / f"{name}.php").read_text(encoding="utf-8")):
            if k not in keys:
                keys.append(k)
    if len(keys) != len(NL):
        raise SystemExit(f"unit count {len(keys)} != translations {len(NL)}")
    return dict(zip(keys, NL))


def nl_pattern(name, table):
    src = (THEME / "patterns" / f"{name}.php").read_text(encoding="utf-8")
    out, _ = blocktext.apply(src, table)
    out = re.sub(r"(\* Slug: booming-venture/)([a-z0-9-]+)", lambda m: m.group(1) + m.group(2) + "-nl", out, count=1)
    out = re.sub(r"(\* Title: )(.+)", lambda m: m.group(1) + m.group(2) + " (NL)", out, count=1)
    if "* Inserter:" not in out:
        out = out.replace(" * Categories:", " * Inserter: no\n * Categories:", 1)
    out = re.sub(r'("slug":"booming-venture/)([a-z0-9-]+)(")', lambda m: m.group(1) + m.group(2) + "-nl" + m.group(3), out)
    out = map_hrefs(out)
    return out


TEMPLATE_OUT = {
    "templates/home.html": "templates/page-blog-nl.html",
    "templates/single.html": "templates/single-nl.html",
    "templates/page-boardroom-quickscan.html": "templates/page-boardroom-quickscan-nl.html",
    "templates/page-head-of-growth.html": "templates/page-head-of-growth-nl.html",
    "templates/page-funnel-calculator.html": "templates/page-funnel-calculator-nl.html",
    "templates/page-roi-forecaster.html": "templates/page-roi-forecaster-nl.html",
    "templates/404.html": "templates/404-nl.html",
    "parts/header.html": "parts/header-nl.html",
    "parts/footer.html": "parts/footer-nl.html",
}

SINGLE_EYEBROW_EN = '<!-- wp:post-terms {"term":"category","style":{"typography":{"textTransform":"uppercase","fontSize":"0.75rem","letterSpacing":"0.08em","fontWeight":"600"}},"textColor":"venture-700"} /-->'
SINGLE_EYEBROW_NL = (
    '<!-- wp:paragraph {"style":{"typography":{"textTransform":"uppercase","fontSize":"0.75rem","letterSpacing":"0.08em","fontWeight":"600"}},"textColor":"venture-700"} -->\n'
    '\t\t<p class="has-venture-700-color has-text-color" style="font-size:0.75rem;font-weight:600;letter-spacing:0.08em;text-transform:uppercase"><a href="/nl/blog/">Blog</a></p>\n'
    '\t\t<!-- /wp:paragraph -->'
)


def template_table():
    from nl_templates import NL as TNL, TEMPLATE_FILES

    keys = []
    for rel in TEMPLATE_FILES:
        for k in blocktext.extract((THEME / rel).read_text(encoding="utf-8")):
            if k not in keys:
                keys.append(k)
    if len(keys) != len(TNL):
        raise SystemExit(f"template units {len(keys)} != translations {len(TNL)}")
    return {k: (k if v is None else v) for k, v in zip(keys, TNL)}


def build_templates():
    from nl_templates import ATTRS

    table = template_table()
    for src_rel, dst_rel in TEMPLATE_OUT.items():
        src = (THEME / src_rel).read_text(encoding="utf-8")
        out, _ = blocktext.apply(src, table)
        for old, new in ATTRS.get(src_rel, []):
            if old not in out:
                raise SystemExit(f"{src_rel}: attribute string not found: {old}")
            out = out.replace(old, new)
        out = re.sub(r'("slug":"booming-venture/)([a-z0-9-]+)(")', lambda m: m.group(1) + m.group(2) + "-nl" + m.group(3), out)
        out = re.sub(r'(<!-- wp:template-part \{"slug":")(header|footer)(")', r"\1\2-nl\3", out)
        if src_rel == "templates/home.html":
            # A page, not the posts index: its own query, Dutch posts only (inc/i18n.php).
            out = out.replace('{"queryId":1,"query":{"perPage":12,"postType":"post","order":"desc","orderBy":"date","inherit":true}',
                              '{"queryId":2,"query":{"perPage":12,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","inherit":false}')
            if '"inherit":false' not in out:
                raise SystemExit("blog query not rewritten")
            out = out.replace('"format":"F j, Y"', '"format":"j F Y"')  # Dutch date order
        if src_rel == "templates/single.html":
            if SINGLE_EYEBROW_EN not in out:
                raise SystemExit("single eyebrow not found")
            out = out.replace(SINGLE_EYEBROW_EN, SINGLE_EYEBROW_NL)
            out = out.replace('<!-- wp:post-terms {"term":"post_tag"} /-->', "")
        if src_rel == "parts/footer.html" and out.count("[bv_lang_switch]") != 1:
            raise SystemExit("footer language switch missing or doubled")
        out = map_hrefs(out)
        (THEME / dst_rel).write_text(out, encoding="utf-8")
    # Dutch home: the English front page with the Dutch patterns.
    fp = (THEME / "templates/front-page.html").read_text(encoding="utf-8")
    fp = re.sub(r'("slug":"booming-venture/)([a-z0-9-]+)(")', lambda m: m.group(1) + m.group(2) + "-nl" + m.group(3), fp)
    fp = re.sub(r'(<!-- wp:template-part \{"slug":")(header|footer)(")', r"\1\2-nl\3", fp)
    (THEME / "templates/page-home-nl.html").write_text(fp, encoding="utf-8")
    print(f"templates: {len(TEMPLATE_OUT) + 1} Dutch files written")


PHP_RE = re.compile(r"<\?php.*?\?>", re.S)


def segment_pairs(table):
    """Exact English source segments -> Dutch, for runtime translation of the
    English pattern files (inc/patterns-nl.php). Units are split at PHP tags
    because the PHP output (icons, image URLs, forms) is the same in both
    languages."""
    pairs = {}

    def add(k, v):
        if k in pairs and pairs[k] != v:
            raise SystemExit(f"conflicting translations for {k!r}")
        pairs[k] = v

    for name in PATTERNS:
        src = (THEME / "patterns" / f"{name}.php").read_text(encoding="utf-8")
        masked, tokens = blocktext.mask(src)
        for s_, e_ in blocktext.units(masked):
            raw = blocktext.unmask(masked[s_:e_], tokens)
            key = blocktext.norm(raw)
            nl = table[key]
            if nl == key:
                continue
            rs, ns = PHP_RE.split(raw), PHP_RE.split(nl)
            if len(rs) != len(ns) or PHP_RE.findall(raw) != PHP_RE.findall(nl):
                raise SystemExit(f"{name}: PHP tags differ in {key!r}")
            for a, b in zip(rs, ns):
                if a.strip() == b.strip() or not re.search(r"[A-Za-z]", a):
                    continue
                lead = re.match(r"\s*", a).group(0)
                trail = re.search(r"\s*$", a).group(0)
                # Bounded by the tags around the unit, so a word never matches inside a URL or attribute.
                add('>' + a + '<', '>' + lead + b.strip() + trail + '<')
        for m in blocktext.ATTR_RE.finditer(masked):
            k = "@" + m.group(2)
            if k in table:
                add(f'{m.group(1)}="{m.group(2)}"', f'{m.group(1)}="{table[k][1:]}"')
    for en, nl in URLS.items():
        add(f'href="{en}"', f'href="{nl}"')
    for name in PATTERNS:
        add(f'"slug":"booming-venture/{name}"', f'"slug":"booming-venture/{name}-nl"')
    return pairs


def strtr(text, pairs):
    keys = sorted(pairs, key=len, reverse=True)
    rx = re.compile("|".join(re.escape(k) for k in keys))
    return rx.sub(lambda m: pairs[m.group(0)], text)


def php_str(s):
    if "\n" in s or "\t" in s:  # escapes keep whitespace exact and visible
        e = s.replace("\\", "\\\\").replace('"', '\\"').replace("$", "\\$").replace("\n", "\\n").replace("\t", "\\t")
        return '"' + e + '"'
    return "'" + s.replace("\\", "\\\\").replace("'", "\\'") + "'"


def patterns_php(table):
    pairs = segment_pairs(table)
    # Proof: the runtime translation gives byte-identical output to the
    # unit-by-unit translation for every pattern.
    for name in PATTERNS:
        src = (THEME / "patterns" / f"{name}.php").read_text(encoding="utf-8")
        body_rt = strtr(src.split("?>", 1)[1], pairs)
        body_ref = nl_pattern(name, table).split("?>", 1)[1]
        if body_rt != body_ref:
            import difflib
            d = "\n".join(list(difflib.unified_diff(body_ref.splitlines(), body_rt.splitlines(), lineterm=""))[:30])
            raise SystemExit(f"{name}: runtime translation differs\n{d}")
    rows = "\n".join(f"\t\t{php_str(k)} => {php_str(v)}," for k, v in sorted(pairs.items(), key=lambda kv: -len(kv[0])))
    names = ", ".join(php_str(n) for n in PATTERNS)
    return (
        "<?php\n/**\n * Dutch patterns (1.11.0). Each English pattern file is rendered as\n"
        " * WordPress renders it, then its text is swapped for the Dutch text below\n"
        " * (strtr, longest match first). Same blocks, classes and styles; only the\n"
        " * words change. Generated by tools/i18n/build_nl.py, which proves the\n"
        " * output matches a unit-by-unit translation. Do not edit by hand.\n *\n"
        " * @package BoomingVenture\n */\n\ndefined( 'ABSPATH' ) || exit;\n\n"
        "function bv_nl_pattern_pairs(): array {\n\treturn [\n" + rows + "\n\t];\n}\n\n"
        "function bv_nl_pattern_render( string $name ): string {\n"
        "\t$file = get_theme_file_path( 'patterns/' . $name . '.php' );\n"
        "\tif ( ! is_readable( $file ) ) return '';\n"
        "\tob_start();\n\tinclude $file;\n\treturn strtr( (string) ob_get_clean(), bv_nl_pattern_pairs() );\n}\n\n"
        "add_action( 'init', static function () {\n"
        "\t$uri = (string) ( $_SERVER['REQUEST_URI'] ?? '' );\n"
        "\tif ( ! is_admin() && 'nl' !== bv_lang() && ! str_contains( $uri, '/wp-json/' ) && ! str_contains( $uri, 'rest_route=' ) ) return;\n"
        "\tforeach ( [ " + names + " ] as $name ) {\n"
        "\t\tregister_block_pattern( 'booming-venture/' . $name . '-nl', [\n"
        "\t\t\t'title'    => ucfirst( str_replace( '-', ' ', $name ) ) . ' (NL)',\n"
        "\t\t\t'content'  => bv_nl_pattern_render( $name ),\n"
        "\t\t\t'inserter' => false,\n\t\t] );\n\t}\n}, 9 );\n"
    )


def main():
    table = pattern_table()
    (THEME / "inc" / "patterns-nl.php").write_text(patterns_php(table), encoding="utf-8")
    print(f"patterns: {len(PATTERNS)} Dutch patterns in inc/patterns-nl.php")
    build_templates()


if __name__ == "__main__":
    main()
