"""Block markup helpers for the 1.30.0 growth pages.

Same components and classes as the GEO Index method pages (bv-gip-*), so the
new pages look like the rest of the site and stay editable in the editor.
"""
import json


def _attrs(d):
    return (" " + json.dumps(d, ensure_ascii=False, separators=(",", ":"))) if d else ""


def p(html, cls=None):
    if cls:
        return f'<!-- wp:paragraph {{"className":"{cls}"}} -->\n<p class="{cls}">{html}</p>\n<!-- /wp:paragraph -->'
    return f"<!-- wp:paragraph -->\n<p>{html}</p>\n<!-- /wp:paragraph -->"


def capsule(html):
    return (
        '<!-- wp:paragraph {"className":"bv-capsule","style":{"typography":{"fontWeight":"500"}},"fontSize":"lg"} -->\n'
        f'<p class="bv-capsule has-lg-font-size" style="font-weight:500">{html}</p>\n<!-- /wp:paragraph -->'
    )


def h2(text):
    return f'<!-- wp:heading -->\n<h2 class="wp-block-heading">{text}</h2>\n<!-- /wp:heading -->'


def h3(text):
    return f'<!-- wp:heading {{"level":3}} -->\n<h3 class="wp-block-heading">{text}</h3>\n<!-- /wp:heading -->'


def lst(items, ordered=False):
    tag = "ol" if ordered else "ul"
    head = '<!-- wp:list {"ordered":true} -->' if ordered else "<!-- wp:list -->"
    body = "\n".join(f"<!-- wp:list-item -->\n<li>{i}</li>\n<!-- /wp:list-item -->" for i in items)
    return f'{head}\n<{tag} class="wp-block-list">{body}</{tag}>\n<!-- /wp:list -->'


def table(headers, rows, caption=None, cls="bv-gip-table"):
    th = "".join(f"<th>{h}</th>" for h in headers)
    tr = "".join("<tr>" + "".join(f"<td>{c}</td>" for c in r) + "</tr>" for r in rows)
    cap = f'<figcaption class="wp-element-caption">{caption}</figcaption>' if caption else ""
    return (
        f'<!-- wp:table {{"className":"{cls}"}} -->\n'
        f'<figure class="wp-block-table {cls}"><table><thead><tr>{th}</tr></thead><tbody>{tr}</tbody></table>{cap}</figure>\n'
        "<!-- /wp:table -->"
    )


def group(cls, inner, wide=False, tag=None):
    a = {"className": cls}
    if tag:
        a = {"tagName": tag, "className": cls}
    if wide:
        a["align"] = "wide"
    t = tag or "div"
    al = "alignwide " if wide else ""
    return f"<!-- wp:group{_attrs(a)} -->\n<{t} class=\"wp-block-group {al}{cls}\">\n{inner}\n</{t}>\n<!-- /wp:group -->"


def button(text, href, style=""):
    if style == "outline":
        return (
            '<!-- wp:button {"className":"is-style-outline"} -->\n'
            f'<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="{href}">{text}</a></div>\n<!-- /wp:button -->'
        )
    if style == "gradient":
        return (
            '<!-- wp:button {"className":"bv-cta-gradient"} -->\n'
            f'<div class="wp-block-button bv-cta-gradient"><a class="wp-block-button__link wp-element-button" href="{href}">{text}</a></div>\n<!-- /wp:button -->'
        )
    return f'<!-- wp:button -->\n<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="{href}">{text}</a></div>\n<!-- /wp:button -->'


def buttons(btns, cls=None, center=True):
    a = {}
    if cls:
        a["className"] = cls
    if center:
        a["layout"] = {"type": "flex", "justifyContent": "center"}
    c = f" {cls}" if cls else ""
    inner = "\n".join(btns)
    return f'<!-- wp:buttons{_attrs(a)} -->\n<div class="wp-block-buttons{c}">{inner}</div>\n<!-- /wp:buttons -->'


def hero(eyebrow, h1_html, sub, facts=None, btns=None):
    parts = [
        p(eyebrow, "bv-gip-eyebrow"),
        f'<!-- wp:heading {{"textAlign":"center","level":1,"fontSize":"6xl"}} -->\n<h1 class="wp-block-heading has-text-align-center has-6-xl-font-size">{h1_html}</h1>\n<!-- /wp:heading -->',
        p(sub, "bv-gip-sub"),
    ]
    if facts:
        cards = "\n".join(group("bv-gip-fact", p(n, "bv-gip-fact__num") + "\n" + p(l, "bv-gip-fact__label")) for n, l in facts)
        parts.append(group("bv-gip-facts", cards))
    if btns:
        parts.append(buttons(btns, "bv-gip-jump"))
    inner = "\n".join(parts)
    a = {"tagName": "section", "className": "bv-page-hero bv-gip-hero", "gradient": "hero-backdrop", "align": "full",
         "style": {"spacing": {"padding": {"top": "var:preset|spacing|70", "bottom": "var:preset|spacing|60"}}}, "layout": {"type": "constrained"}}
    return (
        f"<!-- wp:group{_attrs(a)} -->\n"
        '<section class="wp-block-group alignfull bv-page-hero bv-gip-hero has-hero-backdrop-gradient-background has-background" '
        'style="padding-top:var(--wp--preset--spacing--70);padding-bottom:var(--wp--preset--spacing--60)">\n'
        f"{inner}\n</section>\n<!-- /wp:group -->"
    )


def sec(eyebrow, title, cap=None, body=None, extra_cls="", anchor=None):
    head = [p(eyebrow, "bv-gip-eyebrow"), h2(title)]
    if cap:
        head.append(capsule(cap))
    inner = group("bv-gip-head", "\n".join(head))
    if body:
        inner += "\n" + "\n".join(body)
    cls = "bv-gip-sec" + (f" {extra_cls}" if extra_cls else "")
    out = group(cls, inner, wide=True)
    if anchor:
        out = out.replace(f'<div class="wp-block-group alignwide {cls}">', f'<div id="{anchor}" class="wp-block-group alignwide {cls}">', 1)
        out = out.replace(f'<!-- wp:group {{"className":"{cls}","align":"wide"}} -->', f'<!-- wp:group {{"anchor":"{anchor}","className":"{cls}","align":"wide"}} -->', 1)
    return out


def stats(cards, cols=3):
    """cards: [(num, text, source_html)]"""
    items = []
    for num, text, src in cards:
        inner = p(num, "bv-gip-num") + "\n" + p(text) + ("\n" + p(src, "bv-gip-src") if src else "")
        items.append(group("bv-gip-card bv-gip-card--stat", inner))
    return group(f"bv-gip-grid bv-gip-grid--{cols}", "\n".join(items))


def cards(items, cols=2):
    """items: [(title, [body blocks])]"""
    out = []
    for title, body in items:
        out.append(group("bv-gip-card", h3(title) + "\n" + "\n".join(body)))
    return group(f"bv-gip-grid bv-gip-grid--{cols}", "\n".join(out))


def faq(nl, qa):
    title = "Veelgestelde vragen" if nl else "Frequently asked questions"
    eyebrow = "Vragen" if nl else "Questions"
    items = [group("bv-gip-card bv-gip-card--faq", h3(q) + "\n" + p(a)) for q, a in qa]
    head = group("bv-gip-head", p(eyebrow, "bv-gip-eyebrow") + "\n" + h2(title))
    return group("bv-gip-sec", head + "\n" + group("bv-gip-grid bv-gip-grid--2", "\n".join(items)), wide=True)


def cta(title, text, btns, foot=None):
    inner = [h2(title), p(text), buttons(btns)]
    if foot:
        inner.append(p(foot, "bv-gip-cta__foot"))
    a = {"className": "bv-gip-cta", "gradient": "brand-dark", "align": "wide"}
    return (
        f"<!-- wp:group{_attrs(a)} -->\n"
        '<div class="wp-block-group alignwide bv-gip-cta has-brand-dark-gradient-background has-background">\n'
        + "\n".join(inner)
        + "\n</div>\n<!-- /wp:group -->"
    )


def note(html):
    return p(html, "bv-gip-note")


def src(text, url):
    return f'<a href="{url}" target="_blank" rel="noopener">{text}</a>'


def raw_block(name, attrs=None):
    return f"<!-- wp:{name}{_attrs(attrs or {})} /-->"
