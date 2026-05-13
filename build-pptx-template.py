#!/usr/bin/env python3
"""
Build a PowerPoint template (.pptx) themed to boomingventure.com.

This version mirrors the actual homepage layouts captured in the
provided screenshot, not just the colour tokens. Every slide is a
direct analogue of a visual unit operators see on the live site:

  - Cover slide reproduces the dark gradient hero with the geometric
    pen-line decoration and stats card.
  - Service-grid slide reproduces the four-card "Our Services" row.
  - 2x2 feature-grid slide reproduces the "Growth Frameworks /
    ROI Templates / AI Implementation / Case Studies" block.
  - Mission + Values slide reproduces the checkmark-bullet layout.
  - Testimonial slide reproduces the cyan quotation-mark + italic
    quote + author block from the "Client Success Stories" carousel.
  - Contact-style CTA slide reproduces the dark closing strip.

Palette + typography both sourced from
  wp-content/themes/booming-venture/theme.json
so any future brand update is one re-run of this script away.

  python3 build-pptx-template.py

Output: wp-content/themes/booming-venture/assets/booming-venture-template.pptx
"""

from pptx import Presentation
from pptx.util import Inches, Pt, Emu
from pptx.dml.color import RGBColor
from pptx.enum.shapes import MSO_SHAPE
from pptx.enum.text import PP_ALIGN, MSO_ANCHOR
from pptx.oxml.ns import qn
from lxml import etree

# ---- Booming Venture brand tokens -----------------------------------------
PRIMARY     = RGBColor(0x02, 0x84, 0xC7)   # Booming 600  , buttons + headings
PRIMARY_D   = RGBColor(0x03, 0x69, 0xA1)   # Booming 700  , hover / deeper
PRIMARY_L   = RGBColor(0x38, 0xBD, 0xF8)   # Booming 400  , icon-card fill
ACCENT      = RGBColor(0x14, 0xB8, 0xA6)   # Venture 500  , teal accent
ACCENT_L    = RGBColor(0x5E, 0xEA, 0xD4)   # Venture 300  , card chip
DARK        = RGBColor(0x0C, 0x1A, 0x2E)   # Contrast     , hero / footer
MID         = RGBColor(0x1F, 0x29, 0x37)   # Contrast 2   , body text on light
MUTED       = RGBColor(0x64, 0x74, 0x8B)
BORDER      = RGBColor(0xE2, 0xE8, 0xF0)
BG          = RGBColor(0xF8, 0xFA, 0xFC)   # Base 50      , card bg on light
TINT        = RGBColor(0xF0, 0xF9, 0xFF)   # Booming 50   , feature bg
TINT_2      = RGBColor(0xE0, 0xF2, 0xFE)   # Booming 100  , chip bg
WHITE       = RGBColor(0xFF, 0xFF, 0xFF)
CYAN_QUOTE  = RGBColor(0x7D, 0xD3, 0xFC)   # Booming 300  , quote icon

FONT_HEAD = "Space Grotesk"
FONT_BODY = "Inter"

# 16:9 widescreen
prs = Presentation()
prs.slide_width  = Inches(13.333)
prs.slide_height = Inches(7.5)
SLIDE_W = prs.slide_width
SLIDE_H = prs.slide_height

# ---- helpers --------------------------------------------------------------
def add_rect(slide, x, y, w, h, fill, line=None, shape=MSO_SHAPE.RECTANGLE):
    s = slide.shapes.add_shape(shape, x, y, w, h)
    s.fill.solid(); s.fill.fore_color.rgb = fill
    if line is None: s.line.fill.background()
    else:
        s.line.color.rgb = line; s.line.width = Pt(0.75)
    s.shadow.inherit = False
    return s

def add_round_rect(slide, x, y, w, h, fill, radius=0.08, line=None):
    s = slide.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, x, y, w, h)
    s.fill.solid(); s.fill.fore_color.rgb = fill
    if line is None: s.line.fill.background()
    else:
        s.line.color.rgb = line; s.line.width = Pt(0.75)
    s.shadow.inherit = False
    s.adjustments[0] = radius
    return s

def add_text(slide, x, y, w, h, text, font=FONT_BODY, size=14, color=MID, bold=False,
             align=PP_ALIGN.LEFT, anchor=MSO_ANCHOR.TOP, italic=False):
    tb = slide.shapes.add_textbox(x, y, w, h)
    tf = tb.text_frame
    tf.word_wrap = True
    tf.margin_top = tf.margin_bottom = Pt(2)
    tf.margin_left = tf.margin_right = Pt(2)
    tf.vertical_anchor = anchor
    p = tf.paragraphs[0]
    p.alignment = align
    run = p.add_run()
    run.text = text
    run.font.name = font
    run.font.size = Pt(size)
    run.font.bold = bold
    run.font.italic = italic
    run.font.color.rgb = color
    return tb

def add_multiline(slide, x, y, w, h, lines, font=FONT_BODY, size=14, color=MID,
                  bold=False, align=PP_ALIGN.LEFT, anchor=MSO_ANCHOR.TOP,
                  line_spacing=1.25):
    tb = slide.shapes.add_textbox(x, y, w, h)
    tf = tb.text_frame; tf.word_wrap = True
    tf.margin_top = tf.margin_bottom = Pt(2)
    tf.margin_left = tf.margin_right = Pt(2)
    tf.vertical_anchor = anchor
    for i, line in enumerate(lines):
        p = tf.paragraphs[0] if i == 0 else tf.add_paragraph()
        p.alignment = align
        p.line_spacing = line_spacing
        run = p.add_run()
        run.text = line
        run.font.name = font
        run.font.size = Pt(size)
        run.font.bold = bold
        run.font.color.rgb = color
    return tb

def add_accent_rule(slide, x, y, w=Inches(0.6), thickness=Pt(3.5), color=ACCENT):
    bar = slide.shapes.add_shape(MSO_SHAPE.RECTANGLE, x, y, w, thickness)
    bar.fill.solid(); bar.fill.fore_color.rgb = color
    bar.line.fill.background()
    return bar

def add_pen_decoration(slide, x_start, y_start, w, h, color=PRIMARY, opacity=False):
    """Subtle diagonal-line decoration mimicking the hero pen-line motif."""
    # 5 thin diagonal lines, top-right corner
    for i in range(5):
        line = slide.shapes.add_connector(1, x_start + Inches(0.6 * i), y_start,
                                          x_start + Inches(0.6 * i + 1.2), y_start + h)
        line.line.color.rgb = color
        line.line.width = Pt(0.6)

def add_footer(slide, page_no, total):
    add_rect(slide, Inches(0), Inches(7.35), SLIDE_W, Inches(0.15), BORDER)
    add_text(slide, Inches(0.5), Inches(7.18), Inches(4), Inches(0.25),
             "Booming Venture", font=FONT_HEAD, size=9, color=PRIMARY, bold=True)
    add_text(slide, Inches(11), Inches(7.18), Inches(2), Inches(0.25),
             f"{page_no} / {total}", font=FONT_BODY, size=9, color=MUTED, align=PP_ALIGN.RIGHT)

def add_chip(slide, x, y, w, h, label, fill=PRIMARY, txt=WHITE, font=FONT_HEAD, size=13, bold=True):
    chip = add_round_rect(slide, x, y, w, h, fill, radius=0.5)
    add_text(slide, x, y, w, h, label, font=font, size=size, color=txt, bold=bold,
             align=PP_ALIGN.CENTER, anchor=MSO_ANCHOR.MIDDLE)
    return chip

def add_check_bullet(tf, text, color=PRIMARY, size=14):
    p = tf.add_paragraph()
    p.alignment = PP_ALIGN.LEFT
    pPr = p._p.get_or_add_pPr()
    for tag in ('buChar','buAutoNum','buNone'):
        for el in pPr.findall(qn(f'a:{tag}')): pPr.remove(el)
    buChar = etree.SubElement(pPr, qn('a:buChar')); buChar.set('char', '✓')
    buClr  = etree.SubElement(pPr, qn('a:buClr'))
    srgb   = etree.SubElement(buClr, qn('a:srgbClr'))
    srgb.set('val', f"{color[0]:02X}{color[1]:02X}{color[2]:02X}")
    pPr.set('marL', '342900'); pPr.set('indent', '-342900')
    run = p.add_run()
    run.text = text
    run.font.name = FONT_BODY
    run.font.size = Pt(size)
    run.font.color.rgb = MID
    p.space_after = Pt(8)

def blank_slide():
    return prs.slides.add_slide(prs.slide_layouts[6])

# =========================================================================
# Slide 1 , Cover (mirrors the hero on the homepage)
# =========================================================================
def slide_cover():
    s = blank_slide()
    add_rect(s, 0, 0, SLIDE_W, SLIDE_H, DARK)
    # Subtle pen-line decoration top-right
    add_pen_decoration(s, Inches(9.5), Inches(0.4), Inches(3.4), Inches(2.4), color=PRIMARY)
    # Accent rule top
    add_rect(s, 0, 0, SLIDE_W, Inches(0.08), ACCENT)
    # Eyebrow
    add_text(s, Inches(0.9), Inches(2.0), Inches(8), Inches(0.5),
             "BOOMING VENTURE", font=FONT_HEAD, size=12, color=PRIMARY_L, bold=True)
    # Title (matching the homepage title vibe)
    add_text(s, Inches(0.9), Inches(2.5), Inches(11.5), Inches(2.0),
             "Smarter growth.\nClear strategy.\nCreative performance.",
             font=FONT_HEAD, size=44, color=WHITE, bold=True)
    # Subhead
    add_text(s, Inches(0.9), Inches(5.0), Inches(11), Inches(0.6),
             "AI-powered marketing that delivers results. Personality. Powered by AI.",
             font=FONT_BODY, size=18, color=BORDER)
    # CTA chips
    add_chip(s, Inches(0.9), Inches(6.0), Inches(2.4), Inches(0.6),
             "Start the Journey", fill=PRIMARY)
    add_text(s, Inches(3.45), Inches(6.0), Inches(2.6), Inches(0.6),
             "Email Us Directly", font=FONT_BODY, size=13, color=BORDER,
             anchor=MSO_ANCHOR.MIDDLE)
    # Footer-ish presenter line
    add_text(s, Inches(0.9), Inches(6.95), Inches(8), Inches(0.3),
             "Presenter Name  |  Rotterdam, The Netherlands",
             font=FONT_BODY, size=11, color=MUTED)

# =========================================================================
# Slide 2 , Hero stats card (the 4-stat panel that overlays the hero image)
# =========================================================================
def slide_hero_stats():
    s = blank_slide()
    add_rect(s, 0, 0, SLIDE_W, SLIDE_H, BG)
    add_accent_rule(s, Inches(1), Inches(1.0))
    add_text(s, Inches(1), Inches(1.15), Inches(11), Inches(0.7),
             "Numbers that frame the work",
             font=FONT_HEAD, size=32, color=DARK, bold=True)
    add_text(s, Inches(1), Inches(1.85), Inches(11), Inches(0.4),
             "Replace with your own metrics. The layout mirrors the homepage hero stats card.",
             font=FONT_BODY, size=15, color=MUTED)

    stats = [
        ("97%",  "Client satisfaction"),
        ("+48%", "Average growth"),
        ("15+",  "Businesses helped"),
        ("24/7", "Support when it's needed"),
    ]
    card_w = Inches(2.7); card_h = Inches(3.4); top = Inches(3.0); gap = Inches(0.25)
    total_w = card_w * 4 + gap * 3
    x = (SLIDE_W - total_w) / 2
    for stat, label in stats:
        add_round_rect(s, x, top, card_w, card_h, WHITE, radius=0.06, line=BORDER)
        add_rect(s, x, top, card_w, Inches(0.06), PRIMARY)
        add_text(s, x, top + Inches(0.6), card_w, Inches(1.4), stat,
                 font=FONT_HEAD, size=48, color=PRIMARY, bold=True,
                 align=PP_ALIGN.CENTER, anchor=MSO_ANCHOR.MIDDLE)
        add_rect(s, x + card_w/2 - Inches(0.3), top + Inches(2.0), Inches(0.6), Pt(2), ACCENT)
        add_text(s, x, top + Inches(2.2), card_w, Inches(1.0), label,
                 font=FONT_BODY, size=14, color=MID,
                 align=PP_ALIGN.CENTER, anchor=MSO_ANCHOR.TOP)
        x += card_w + gap

# =========================================================================
# Slide 3 , Section divider
# =========================================================================
def slide_section():
    s = blank_slide()
    add_rect(s, 0, 0, SLIDE_W, SLIDE_H, TINT)
    add_rect(s, 0, Inches(3.4), SLIDE_W, Inches(0.04), ACCENT)
    add_text(s, Inches(1), Inches(2.6), Inches(11), Inches(0.4),
             "SECTION 01", font=FONT_HEAD, size=14, color=ACCENT, bold=True)
    add_text(s, Inches(1), Inches(3.6), Inches(11), Inches(1.5),
             "Section title", font=FONT_HEAD, size=44, color=DARK, bold=True)
    add_text(s, Inches(1), Inches(4.8), Inches(11), Inches(0.5),
             "Short framing sentence that primes the audience for what follows.",
             font=FONT_BODY, size=18, color=MID)

# =========================================================================
# Slide 4 , Title + bullets
# =========================================================================
def slide_bullets():
    s = blank_slide()
    add_rect(s, 0, 0, SLIDE_W, SLIDE_H, WHITE)
    add_accent_rule(s, Inches(1), Inches(1.0))
    add_text(s, Inches(1), Inches(1.15), Inches(10), Inches(0.8),
             "Slide title", font=FONT_HEAD, size=32, color=DARK, bold=True)
    add_text(s, Inches(1), Inches(1.95), Inches(11), Inches(0.4),
             "Short supporting sentence under the title.",
             font=FONT_BODY, size=16, color=MUTED)
    tb = s.shapes.add_textbox(Inches(1), Inches(2.8), Inches(11.3), Inches(4))
    tf = tb.text_frame; tf.word_wrap = True
    # Override first paragraph to be a real check bullet
    p0 = tf.paragraphs[0]
    p0.text = ''
    for item in [
        "Headline statement that owns the point",
        "Secondary supporting evidence with a number",
        "Third point that earns its space",
        "Fourth point only if it survives the cut",
    ]:
        add_check_bullet(tf, item, color=PRIMARY, size=18)

# =========================================================================
# Slide 5 , Four-service grid (mirrors "Our Services")
# =========================================================================
def slide_service_grid():
    s = blank_slide()
    add_rect(s, 0, 0, SLIDE_W, SLIDE_H, WHITE)
    add_text(s, Inches(1), Inches(0.7), Inches(11.3), Inches(0.7),
             "Our Services", font=FONT_HEAD, size=32, color=DARK, bold=True,
             align=PP_ALIGN.CENTER)
    add_text(s, Inches(1), Inches(1.4), Inches(11.3), Inches(0.5),
             "Comprehensive solutions designed to help your business thrive in today's competitive landscape.",
             font=FONT_BODY, size=14, color=MUTED, align=PP_ALIGN.CENTER)
    services = [
        ("Strategic Consulting",  "Tailored growth strategies to help your business reach its full potential."),
        ("Performance Marketing", "Data-driven marketing campaigns that deliver measurable results."),
        ("AI-Powered Solutions",  "Cutting-edge AI technology to optimize your business operations."),
        ("Growth Optimization",   "Comprehensive programs that scale your business efficiently and sustainably."),
    ]
    card_w = Inches(2.85); card_h = Inches(4.5); top = Inches(2.4); gap = Inches(0.2)
    total_w = card_w * 4 + gap * 3
    x = (SLIDE_W - total_w) / 2
    for title, desc in services:
        add_round_rect(s, x, top, card_w, card_h, WHITE, radius=0.05, line=BORDER)
        # Icon disc
        disc = add_round_rect(s, x + Inches(0.3), top + Inches(0.3),
                               Inches(0.55), Inches(0.55), TINT_2, radius=0.5)
        add_text(s, x + Inches(0.3), top + Inches(0.3), Inches(0.55), Inches(0.55),
                 "▶", font=FONT_HEAD, size=14, color=PRIMARY, bold=True,
                 align=PP_ALIGN.CENTER, anchor=MSO_ANCHOR.MIDDLE)
        # Title
        add_text(s, x + Inches(0.25), top + Inches(1.05), card_w - Inches(0.5), Inches(0.7),
                 title, font=FONT_HEAD, size=15, color=DARK, bold=True)
        # Description
        add_text(s, x + Inches(0.25), top + Inches(1.65), card_w - Inches(0.5), Inches(1.4),
                 desc, font=FONT_BODY, size=11, color=MUTED)
        # Mini checklist
        tb = s.shapes.add_textbox(x + Inches(0.25), top + Inches(3.0),
                                   card_w - Inches(0.5), Inches(1.4))
        tf = tb.text_frame; tf.word_wrap = True
        tf.paragraphs[0].text = ''
        for line in ["Custom growth plan", "Market analysis", "Revenue strategy"]:
            add_check_bullet(tf, line, color=ACCENT, size=10)
        x += card_w + gap

# =========================================================================
# Slide 6 , 2x2 feature grid (mirrors the "Growth Frameworks / ROI Templates
#                             / AI Implementation / Case Studies" block)
# =========================================================================
def slide_2x2_features():
    s = blank_slide()
    add_rect(s, 0, 0, SLIDE_W, SLIDE_H, WHITE)
    add_accent_rule(s, Inches(1), Inches(0.85))
    add_text(s, Inches(1), Inches(1.0), Inches(11), Inches(0.7),
             "What you get", font=FONT_HEAD, size=32, color=DARK, bold=True)
    add_text(s, Inches(1), Inches(1.7), Inches(11), Inches(0.4),
             "Four named deliverables, every engagement.",
             font=FONT_BODY, size=15, color=MUTED)
    features = [
        ("Growth Frameworks", "Proven methodologies for sustainable growth."),
        ("ROI Templates",     "Calculate and track your marketing ROI."),
        ("AI Implementation", "Step-by-step AI integration guide."),
        ("Case Studies",      "Real examples from successful clients."),
    ]
    card_w = Inches(5.6); card_h = Inches(2.0); gap_x = Inches(0.3); gap_y = Inches(0.3)
    start_x = Inches(1); start_y = Inches(2.7)
    for i, (title, desc) in enumerate(features):
        row, col = divmod(i, 2)
        x = start_x + col * (card_w + gap_x)
        y = start_y + row * (card_h + gap_y)
        add_round_rect(s, x, y, card_w, card_h, TINT, radius=0.04)
        # Icon disc
        add_round_rect(s, x + Inches(0.35), y + Inches(0.4),
                        Inches(0.55), Inches(0.55), PRIMARY, radius=0.5)
        add_text(s, x + Inches(0.35), y + Inches(0.4),
                 Inches(0.55), Inches(0.55), "✓",
                 font=FONT_HEAD, size=16, color=WHITE, bold=True,
                 align=PP_ALIGN.CENTER, anchor=MSO_ANCHOR.MIDDLE)
        add_text(s, x + Inches(1.1), y + Inches(0.4), card_w - Inches(1.3), Inches(0.5),
                 title, font=FONT_HEAD, size=18, color=DARK, bold=True)
        add_text(s, x + Inches(1.1), y + Inches(0.95), card_w - Inches(1.3), Inches(0.9),
                 desc, font=FONT_BODY, size=13, color=MID)

# =========================================================================
# Slide 7 , Mission + Values (mirrors the "Our Mission / Our Values"
#                             panel on the homepage)
# =========================================================================
def slide_mission_values():
    s = blank_slide()
    add_rect(s, 0, 0, SLIDE_W, SLIDE_H, WHITE)
    # Left tinted panel
    add_round_rect(s, Inches(0.7), Inches(0.7), Inches(5.8), Inches(6.1), TINT, radius=0.03)
    add_accent_rule(s, Inches(1.1), Inches(1.1), w=Inches(0.6))
    add_text(s, Inches(1.1), Inches(1.25), Inches(5), Inches(0.6),
             "Our Mission", font=FONT_HEAD, size=22, color=DARK, bold=True)
    add_text(s, Inches(1.1), Inches(1.9), Inches(5), Inches(1.4),
             "Based from Rotterdam, The Netherlands, we are committed to empowering "
             "businesses with innovative marketing strategies and AI-driven solutions "
             "that drive sustainable growth.",
             font=FONT_BODY, size=13, color=MID)

    add_text(s, Inches(1.1), Inches(3.7), Inches(5), Inches(0.5),
             "Our Values", font=FONT_HEAD, size=22, color=DARK, bold=True)
    tb = s.shapes.add_textbox(Inches(1.1), Inches(4.3), Inches(5), Inches(2.5))
    tf = tb.text_frame; tf.word_wrap = True
    tf.paragraphs[0].text = ''
    for item in [
        "Innovation at the core of everything we do",
        "Results-oriented approach to business growth",
        "Transparency and integrity in all partnerships",
        "Continuous learning and improvement",
    ]:
        add_check_bullet(tf, item, color=PRIMARY, size=13)

    # Right column , 2x2 feature blocks
    add_text(s, Inches(7.0), Inches(0.85), Inches(5.5), Inches(0.7),
             "Why Choose Booming Venture",
             font=FONT_HEAD, size=22, color=DARK, bold=True)
    add_text(s, Inches(7.0), Inches(1.55), Inches(5.5), Inches(0.5),
             "We combine strategic thinking with cutting-edge technology to help businesses achieve exceptional growth.",
             font=FONT_BODY, size=12, color=MUTED)
    pillars = [
        ("Expertise",         "Decades of combined experience in business growth, marketing, and AI."),
        ("Personalization",   "We create custom strategies tailored to your specific business goals."),
        ("AI Technology",     "Leverage advanced AI and data analytics to make informed decisions."),
        ("Proven Results",    "We focus on delivering measurable outcomes with clear KPIs."),
    ]
    cw = Inches(2.7); ch = Inches(2.05); top = Inches(2.3); gx = Inches(0.2); gy = Inches(0.15)
    for i, (t, d) in enumerate(pillars):
        row, col = divmod(i, 2)
        x = Inches(7.0) + col * (cw + gx)
        y = top + row * (ch + gy)
        add_round_rect(s, x, y, cw, ch, TINT, radius=0.04)
        add_text(s, x + Inches(0.25), y + Inches(0.25), cw - Inches(0.4), Inches(0.4),
                 t, font=FONT_HEAD, size=14, color=PRIMARY, bold=True)
        add_text(s, x + Inches(0.25), y + Inches(0.75), cw - Inches(0.4), Inches(1.2),
                 d, font=FONT_BODY, size=11, color=MID)

# =========================================================================
# Slide 8 , Big stat
# =========================================================================
def slide_big_stat():
    s = blank_slide()
    add_rect(s, 0, 0, SLIDE_W, SLIDE_H, DARK)
    add_pen_decoration(s, Inches(0.4), Inches(0.4), Inches(3), Inches(2), color=PRIMARY_L)
    add_text(s, Inches(1), Inches(1.0), Inches(11), Inches(0.5),
             "THE NUMBER THAT MATTERS",
             font=FONT_HEAD, size=14, color=PRIMARY_L, bold=True)
    add_text(s, Inches(1), Inches(1.8), Inches(11), Inches(2.8),
             "527%", font=FONT_HEAD, size=170, color=WHITE, bold=True)
    add_rect(s, Inches(1), Inches(4.7), Inches(0.6), Pt(4), ACCENT)
    add_text(s, Inches(1), Inches(4.85), Inches(11), Inches(0.7),
             "Year-over-year growth in AI-referred sessions",
             font=FONT_HEAD, size=26, color=WHITE)
    add_text(s, Inches(1), Inches(5.7), Inches(11), Inches(0.5),
             "Source: Frase, January 2026",
             font=FONT_BODY, size=14, color=MUTED)

# =========================================================================
# Slide 9 , Testimonial (mirrors the Client Success Stories card)
# =========================================================================
def slide_testimonial():
    s = blank_slide()
    add_rect(s, 0, 0, SLIDE_W, SLIDE_H, TINT)
    add_text(s, Inches(1), Inches(0.7), Inches(11), Inches(0.7),
             "Client Success Stories",
             font=FONT_HEAD, size=28, color=DARK, bold=True,
             align=PP_ALIGN.CENTER)
    add_text(s, Inches(1), Inches(1.4), Inches(11), Inches(0.5),
             "Hear from businesses we've helped scale through innovative strategies",
             font=FONT_BODY, size=13, color=MUTED, align=PP_ALIGN.CENTER)
    # Card
    card_x = Inches(1.3); card_y = Inches(2.4); card_w = Inches(10.7); card_h = Inches(4.2)
    add_round_rect(s, card_x, card_y, card_w, card_h, WHITE, radius=0.03, line=BORDER)
    # Oversized cyan quote mark
    add_text(s, card_x + Inches(0.7), card_y + Inches(0.2), Inches(2), Inches(2),
             '“', font=FONT_HEAD, size=140, color=CYAN_QUOTE, bold=True)
    # Quote
    add_text(s, card_x + Inches(2.5), card_y + Inches(0.8), card_w - Inches(3.5), Inches(2.0),
             "Booming Venture transformed our marketing strategy completely. "
             "Their AI-driven approach increased our conversion rates by 42% in just three months.",
             font=FONT_HEAD, size=20, color=DARK, bold=True, italic=True)
    # Author
    add_rect(s, card_x + Inches(2.5), card_y + Inches(3.05), Inches(0.5), Pt(3), ACCENT)
    add_text(s, card_x + Inches(2.5), card_y + Inches(3.2), card_w - Inches(3.5), Inches(0.4),
             "Sarah Johnson  ,  CEO", font=FONT_BODY, size=14, color=MID, bold=True)
    add_text(s, card_x + Inches(2.5), card_y + Inches(3.55), card_w - Inches(3.5), Inches(0.4),
             "Strategic Partnership", font=FONT_BODY, size=11, color=MUTED)

# =========================================================================
# Slide 10 , Three-card metric strip
# =========================================================================
def slide_three_cards():
    s = blank_slide()
    add_rect(s, 0, 0, SLIDE_W, SLIDE_H, WHITE)
    add_accent_rule(s, Inches(1), Inches(1.0))
    add_text(s, Inches(1), Inches(1.15), Inches(11), Inches(0.7),
             "Three numbers that frame the case",
             font=FONT_HEAD, size=32, color=DARK, bold=True)
    add_text(s, Inches(1), Inches(1.9), Inches(11), Inches(0.4),
             "Each card is a self-contained passage. Quotable, citable, scannable.",
             font=FONT_BODY, size=16, color=MUTED)
    cards = [
        ("41%",   "AI visibility lift from statistics-addition", "Princeton GEO study, 10,000 queries"),
        ("44.2%", "Of AI citations land in the first 30% of body", "Kevin Indig, 1.2M ChatGPT answers"),
        ("3.49%", "Conversion rate from AI-search referrals (22% higher than organic)", "Martal 2026 benchmark"),
    ]
    card_w = Inches(3.7); card_h = Inches(3.6); top = Inches(2.9); gap = Inches(0.25)
    x = Inches(1.0)
    for stat, line, source in cards:
        add_round_rect(s, x, top, card_w, card_h, TINT, radius=0.04)
        add_rect(s, x, top, card_w, Inches(0.08), PRIMARY)
        add_text(s, x + Inches(0.3), top + Inches(0.4), card_w - Inches(0.5), Inches(1.4),
                 stat, font=FONT_HEAD, size=56, color=PRIMARY_D, bold=True)
        add_text(s, x + Inches(0.3), top + Inches(1.9), card_w - Inches(0.5), Inches(1.0),
                 line, font=FONT_BODY, size=15, color=DARK, bold=True)
        add_text(s, x + Inches(0.3), top + Inches(3.0), card_w - Inches(0.5), Inches(0.4),
                 source, font=FONT_BODY, size=11, color=MUTED)
        x += card_w + gap

# =========================================================================
# Slide 11 , Contact CTA (mirrors the closing "Get In Touch" panel)
# =========================================================================
def slide_contact_cta():
    s = blank_slide()
    add_rect(s, 0, 0, SLIDE_W, SLIDE_H, BG)
    add_text(s, Inches(1), Inches(0.8), Inches(11.3), Inches(0.7),
             "Get In Touch", font=FONT_HEAD, size=32, color=DARK, bold=True,
             align=PP_ALIGN.CENTER)
    add_text(s, Inches(1), Inches(1.5), Inches(11.3), Inches(0.5),
             "Ready to accelerate your business growth? Contact us today for a free consultation.",
             font=FONT_BODY, size=15, color=MUTED, align=PP_ALIGN.CENTER)
    # Left card , Contact Information
    add_round_rect(s, Inches(1), Inches(2.5), Inches(5.5), Inches(4.0), WHITE, radius=0.03, line=BORDER)
    add_text(s, Inches(1.3), Inches(2.7), Inches(5), Inches(0.5),
             "Contact Information",
             font=FONT_HEAD, size=18, color=DARK, bold=True)
    add_text(s, Inches(1.3), Inches(3.3), Inches(5), Inches(0.4),
             "Email Us", font=FONT_HEAD, size=12, color=PRIMARY, bold=True)
    add_text(s, Inches(1.3), Inches(3.65), Inches(5), Inches(0.4),
             "info@boomingventure.com", font=FONT_BODY, size=13, color=MID)
    add_text(s, Inches(1.3), Inches(4.2), Inches(5), Inches(0.4),
             "Visit Us", font=FONT_HEAD, size=12, color=PRIMARY, bold=True)
    add_text(s, Inches(1.3), Inches(4.55), Inches(5), Inches(0.6),
             "Westvlietweg 1\n3055 PG Rotterdam, The Netherlands",
             font=FONT_BODY, size=13, color=MID)
    add_chip(s, Inches(1.3), Inches(5.7), Inches(2), Inches(0.55), "Email Us Directly",
             fill=PRIMARY, txt=WHITE, size=12)
    # Right card , Send Us a Message
    add_round_rect(s, Inches(6.85), Inches(2.5), Inches(5.5), Inches(4.0), WHITE, radius=0.03, line=BORDER)
    add_text(s, Inches(7.1), Inches(2.7), Inches(5), Inches(0.5),
             "Send Us a Message", font=FONT_HEAD, size=18, color=DARK, bold=True)
    # Two-up fields
    for i, (lbl, ph) in enumerate([("Your Name *", "John Doe"), ("Email Address *", "john@example.com")]):
        x = Inches(7.1) + i * Inches(2.65)
        add_text(s, x, Inches(3.3), Inches(2.5), Inches(0.3), lbl,
                 font=FONT_BODY, size=10, color=MUTED, bold=True)
        add_round_rect(s, x, Inches(3.6), Inches(2.5), Inches(0.4), TINT, radius=0.2, line=BORDER)
        add_text(s, x + Inches(0.15), Inches(3.6), Inches(2.5), Inches(0.4),
                 ph, font=FONT_BODY, size=11, color=MUTED, anchor=MSO_ANCHOR.MIDDLE)
    add_text(s, Inches(7.1), Inches(4.2), Inches(5), Inches(0.3),
             "Your Message *", font=FONT_BODY, size=10, color=MUTED, bold=True)
    add_round_rect(s, Inches(7.1), Inches(4.5), Inches(5.15), Inches(1.2), TINT, radius=0.05, line=BORDER)
    add_text(s, Inches(7.25), Inches(4.55), Inches(5), Inches(0.4),
             "How can we help you?", font=FONT_BODY, size=11, color=MUTED)
    add_chip(s, Inches(7.1), Inches(5.85), Inches(5.15), Inches(0.55), "Send Message ▶",
             fill=PRIMARY, txt=WHITE, size=13)

# =========================================================================
# Slide 12 , Closing thank-you (dark with accent rule)
# =========================================================================
def slide_thankyou():
    s = blank_slide()
    add_rect(s, 0, 0, SLIDE_W, SLIDE_H, DARK)
    add_rect(s, 0, Inches(7.38), SLIDE_W, Inches(0.12), ACCENT)
    add_pen_decoration(s, Inches(9.5), Inches(0.4), Inches(3.4), Inches(2.4), color=PRIMARY_L)
    add_text(s, Inches(1), Inches(2.0), Inches(11), Inches(0.5),
             "NEXT STEP", font=FONT_HEAD, size=14, color=PRIMARY_L, bold=True)
    add_text(s, Inches(1), Inches(2.5), Inches(11), Inches(2),
             "Let's plug the leaks in your funnel.",
             font=FONT_HEAD, size=44, color=WHITE, bold=True)
    add_text(s, Inches(1), Inches(4.5), Inches(11), Inches(0.6),
             "Book a free UNIFY audit. 30 minutes. No slide deck on our side.",
             font=FONT_BODY, size=20, color=BORDER)
    add_chip(s, Inches(1), Inches(5.6), Inches(3.5), Inches(0.7),
             "boomingventure.com", fill=PRIMARY, size=16)
    add_text(s, Inches(1), Inches(6.55), Inches(11), Inches(0.4),
             "info@boomingventure.com  |  Rotterdam, The Netherlands",
             font=FONT_BODY, size=13, color=MUTED)

# ---- Build all slides + footers ------------------------------------------
builders = [
    slide_cover, slide_hero_stats, slide_section, slide_bullets,
    slide_service_grid, slide_2x2_features, slide_mission_values,
    slide_big_stat, slide_testimonial, slide_three_cards,
    slide_contact_cta, slide_thankyou,
]
total = len(builders)
for fn in builders:
    fn()
for i, slide in enumerate(prs.slides, start=1):
    if i not in (1, total):
        add_footer(slide, i, total)

out = "wp-content/themes/booming-venture/assets/booming-venture-template.pptx"
import os
os.makedirs(os.path.dirname(out), exist_ok=True)
prs.save(out)
print(f"Wrote {out} ({total} slides, 16:9)")
