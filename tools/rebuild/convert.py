"""Convert rendered WordPress post HTML (from the REST API) back to
serialized Gutenberg block markup.

The block vocabulary used on boomingventure.com is small and generated
by the same pipeline, so the mapping from a rendered element to its block
comment is learned from posts where the original block markup is known
(the 83 posts in the repo WXR). Unknown elements are reported, never
guessed.
"""
from __future__ import annotations

import html
import json
import re
from html.parser import HTMLParser

VOID = {"br", "img", "hr", "input", "meta", "link", "source", "wbr", "col", "area", "base", "embed", "param", "track"}

# Classes WordPress adds at render time that are not part of saved markup.
RENDER_ONLY = re.compile(
    r"^(wp-block-paragraph|has-global-padding|is-layout-[a-z\-]+|wp-container-[a-z0-9\-]+|"
    r"wp-block-group-is-layout-[a-z\-]+|wp-elements-[a-f0-9]+|is-content-justification-[a-z\-]+)$"
)


class _Splitter(HTMLParser):
    """Find top-level element spans (start, end offsets) in a fragment."""

    def __init__(self, text: str):
        super().__init__(convert_charrefs=False)
        self.text = text
        self.line_offsets = [0]
        for m in re.finditer("\n", text):
            self.line_offsets.append(m.end())
        self.depth = 0
        self.spans: list[list] = []  # [tag, start, start_tag_end, end_tag_start, end]
        self.cur = None

    def _off(self):
        line, col = self.getpos()
        return self.line_offsets[line - 1] + col

    def handle_starttag(self, tag, attrs):
        start = self._off()
        raw = self.get_starttag_text() or ""
        if self.depth == 0:
            self.cur = [tag, start, start + len(raw), None, None]
            if tag in VOID:
                self.cur[3] = self.cur[4] = start + len(raw)
                self.spans.append(self.cur)
                self.cur = None
                return
        if tag not in VOID:
            self.depth += 1

    def handle_startendtag(self, tag, attrs):
        start = self._off()
        raw = self.get_starttag_text() or ""
        if self.depth == 0:
            self.spans.append([tag, start, start + len(raw), start + len(raw), start + len(raw)])

    def handle_endtag(self, tag):
        if tag in VOID:
            return
        start = self._off()
        self.depth -= 1
        if self.depth == 0 and self.cur is not None:
            end = self.text.index(">", start) + 1
            self.cur[3] = start
            self.cur[4] = end
            self.spans.append(self.cur)
            self.cur = None


def split_top(fragment: str):
    p = _Splitter(fragment)
    p.feed(fragment)
    p.close()
    return p.spans


ATTR_RE = re.compile(r'([a-zA-Z_:][-a-zA-Z0-9_:.]*)\s*=\s*"([^"]*)"')


def parse_start_tag(tag_text: str):
    m = re.match(r"<([a-zA-Z0-9]+)", tag_text)
    attrs = dict(ATTR_RE.findall(tag_text))
    return m.group(1).lower(), attrs


def clean_classes(cls: str) -> list[str]:
    return [c for c in cls.split() if c and not RENDER_ONLY.match(c)]


def norm_style(style: str) -> str:
    parts = [p.strip() for p in style.split(";") if p.strip()]
    return ";".join(parts)


def signature(tag: str, attrs: dict) -> tuple:
    cls = tuple(sorted(clean_classes(attrs.get("class", ""))))
    return (tag, cls, norm_style(attrs.get("style", "")))


def rebuild_start_tag(tag_text: str, attrs: dict) -> str:
    """Rewrite only the class attribute, keep everything else verbatim."""
    if "class" not in attrs:
        return tag_text
    kept = " ".join(clean_classes(attrs["class"]))
    new = re.sub(r'\sclass="[^"]*"', (f' class="{kept}"' if kept else ""), tag_text, count=1)
    return new


COMMENT_OPEN = re.compile(r"<!-- wp:([a-z0-9/\-]+)(?:\s+(\{.*?\}))?\s*(/)?-->", re.S)


def learn_signatures(raw_posts: list[str]) -> dict:
    """Map rendered-element signature -> (block name, attrs json string)."""
    table: dict = {}
    for raw in raw_posts:
        for m in COMMENT_OPEN.finditer(raw):
            name, js, selfclose = m.group(1), m.group(2), m.group(3)
            if selfclose:
                continue
            rest = raw[m.end():]
            t = re.match(r"\s*(<[a-zA-Z0-9]+[^>]*>)", rest)
            if not t:
                continue
            tag, attrs = parse_start_tag(t.group(1))
            sig = signature(tag, attrs)
            table.setdefault(sig, (name, js or ""))
    return table


class Unknown(Exception):
    pass


def to_blocks(fragment: str, table: dict, unknown: list) -> str:
    spans = split_top(fragment)
    out = []
    for tag, s, ste, ets, e in spans:
        start_tag = fragment[s:ste]
        _, attrs = parse_start_tag(start_tag)
        sig = signature(tag, attrs)
        if sig not in table:
            unknown.append(sig)
            out.append("<!-- wp:html -->" + fragment[s:e] + "<!-- /wp:html -->")
            continue
        name, js = table[sig]
        open_c = f"<!-- wp:{name} {js} -->" if js else f"<!-- wp:{name} -->"
        close_c = f"<!-- /wp:{name} -->"
        new_start = rebuild_start_tag(start_tag, attrs)
        if name == "group":
            inner = fragment[ste:ets]
            inner_blocks = to_blocks(inner, table, unknown)
            out.append(open_c + new_start + inner_blocks + fragment[ets:e] + close_c)
        else:
            out.append(open_c + new_start + fragment[ste:e] + close_c)
    return "\n".join(out)


TEXTURIZE = {
    "’": "'", "‘": "'", "“": '"', "”": '"', "–": "-", "—": "-",
    "…": "...", "×": "x", "′": "'", "″": '"', " ": " ",
}


def normalize(markup: str) -> str:
    t = html.unescape(markup)
    for k, v in TEXTURIZE.items():
        t = t.replace(k, v)
    t = re.sub(r">\s+<", "><", t)
    t = re.sub(r"\s+", " ", t)
    return t.strip()
