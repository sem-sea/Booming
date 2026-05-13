#!/usr/bin/env python3
"""
Build a PowerPoint template (.pptx) themed to boomingventure.com.

Palette sourced from wp-content/themes/booming-venture/theme.json:
  Primary  : #0284c7  (Booming 600)
  Accent   : #14b8a6  (Venture 500)
  Dark     : #0c1a2e  (Contrast)
  Mid      : #1f2937  (Contrast 2)
  Muted    : #64748b  (Muted)
  Border   : #e2e8f0  (Border)
  Light-bg : #f8fafc  (Base 50)
  Tint-bg  : #f0f9ff  (Booming 50)

Typography sourced from theme.json:
  Headings : Space Grotesk (falls back to Inter, then sans-serif)
  Body     : Inter (falls back to Calibri / Arial)

Output: booming-venture-template.pptx with 8 ready-to-use slides:
  1. Cover (dark gradient feel with accent rule)
  2. Section divider
  3. Title + bullets
  4. Two-column comparison
  5. Big-stat highlight
  6. Quote / testimonial
  7. Three-card metric strip
  8. Closing CTA
"""

from pptx import Presentation
from pptx.util import Inches, Pt, Emu
from pptx.dml.color import RGBColor
from pptx.enum.shapes import MSO_SHAPE
from pptx.enum.text import PP_ALIGN, MSO_ANCHOR
from pptx.oxml.ns import qn
from lxml import etree

# ---- Booming Venture brand tokens -----------------------------------------
COLOR_PRIMARY   = RGBColor(0x02, 0x84, 0xC7)   # Booming 600
COLOR_PRIMARY_D = RGBColor(0x03, 0x69, 0xA1)   # Booming 700
COLOR_ACCENT    = RGBColor(0x14, 0xB8, 0xA6)   # Venture 500
COLOR_DARK      = RGBColor(0x0C, 0x1A, 0x2E)   # Contrast
COLOR_MID       = RGBColor(0x1F, 0x29, 0x37)   # Contrast 2
COLOR_MUTED     = RGBColor(0x64, 0x74, 0x8B)
COLOR_BORDER    = RGBColor(0xE2, 0xE8, 0xF0)
COLOR_BG        = RGBColor(0xF8, 0xFA, 0xFC)
COLOR_TINT      = RGBColor(0xF0, 0xF9, 0xFF)
COLOR_WHITE     = RGBColor(0xFF, 0xFF, 0xFF)

FONT_HEAD = "Space Grotesk"
FONT_BODY = "Inter"

# 16:9 widescreen
prs = Presentation()
prs.slide_width  = Inches(13.333)
prs.slide_height = Inches(7.5)

SLIDE_W = prs.slide_width
SLIDE_H = prs.slide_height

# ---- helpers --------------------------------------------------------------
def add_rect(slide, x, y, w, h, fill, line=None):
    shape = slide.shapes.add_shape(MSO_SHAPE.RECTANGLE, x, y, w, h)
    shape.fill.solid()
    shape.fill.fore_color.rgb = fill
    if line is None:
        shape.line.fill.background()
    else:
        shape.line.color.rgb = line
        shape.line.width = Pt(0.75)
    shape.shadow.inherit = False
    return shape

def add_text(slide, x, y, w, h, text, font=FONT_BODY, size=14, color=COLOR_MID, bold=False, align=PP_ALIGN.LEFT, anchor=MSO_ANCHOR.TOP):
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
    run.font.color.rgb = color
    return tb

def add_accent_rule(slide, x, y, w=Inches(0.6), thickness=Pt(3.5), color=COLOR_ACCENT):
    bar = slide.shapes.add_shape(MSO_SHAPE.RECTANGLE, x, y, w, thickness)
    bar.fill.solid()
    bar.fill.fore_color.rgb = color
    bar.line.fill.background()
    return bar

def add_footer(slide, page_no, total):
    add_rect(slide, Inches(0), Inches(7.35), SLIDE_W, Inches(0.15), COLOR_BORDER)
    add_text(slide, Inches(0.5), Inches(7.18), Inches(4), Inches(0.25),
             "Booming Venture", font=FONT_HEAD, size=9, color=COLOR_PRIMARY, bold=True)
    add_text(slide, Inches(11), Inches(7.18), Inches(2), Inches(0.25),
             f"{page_no} / {total}", font=FONT_BODY, size=9, color=COLOR_MUTED, align=PP_ALIGN.RIGHT)

def blank_slide():
    layout = prs.slide_layouts[6]    # blank
    return prs.slides.add_slide(layout)

# =========================================================================
# Slide 1 , Cover
# =========================================================================
def slide_cover():
    s = blank_slide()
    # Dark canvas
    add_rect(s, 0, 0, SLIDE_W, SLIDE_H, COLOR_DARK)
    # Subtle accent bar at top
    add_rect(s, 0, 0, SLIDE_W, Inches(0.12), COLOR_PRIMARY)
    # Vertical accent rule lower-left
    add_rect(s, Inches(0.85), Inches(5.2), Inches(0.04), Inches(1.4), COLOR_ACCENT)
    # Eyebrow
    add_text(s, Inches(1.0), Inches(2.3), Inches(8), Inches(0.5),
             "BOOMING VENTURE", font=FONT_HEAD, size=14, color=COLOR_PRIMARY, bold=True)
    # Title
    add_text(s, Inches(1.0), Inches(2.8), Inches(11.3), Inches(2),
             "Presentation title goes here", font=FONT_HEAD, size=54, color=COLOR_WHITE, bold=True)
    # Subhead
    add_text(s, Inches(1.0), Inches(4.7), Inches(11), Inches(0.6),
             "Subtitle or value proposition in one sentence.",
             font=FONT_BODY, size=20, color=COLOR_BORDER)
    # Date + author
    add_text(s, Inches(1.0), Inches(6.5), Inches(6), Inches(0.3),
             "Presenter Name  |  Date", font=FONT_BODY, size=12, color=COLOR_MUTED)

# =========================================================================
# Slide 2 , Section divider
# =========================================================================
def slide_section():
    s = blank_slide()
    add_rect(s, 0, 0, SLIDE_W, SLIDE_H, COLOR_TINT)
    add_rect(s, 0, Inches(3.4), SLIDE_W, Inches(0.04), COLOR_ACCENT)
    add_text(s, Inches(1), Inches(2.6), Inches(11), Inches(0.4),
             "SECTION 01", font=FONT_HEAD, size=14, color=COLOR_ACCENT, bold=True)
    add_text(s, Inches(1), Inches(3.6), Inches(11), Inches(1.5),
             "Section title", font=FONT_HEAD, size=44, color=COLOR_DARK, bold=True)
    add_text(s, Inches(1), Inches(4.8), Inches(11), Inches(0.5),
             "Short framing sentence that primes the audience for what follows.",
             font=FONT_BODY, size=18, color=COLOR_MID)

# =========================================================================
# Slide 3 , Title + bullets
# =========================================================================
def slide_bullets():
    s = blank_slide()
    add_rect(s, 0, 0, SLIDE_W, SLIDE_H, COLOR_WHITE)
    add_accent_rule(s, Inches(1), Inches(1.0))
    add_text(s, Inches(1), Inches(1.15), Inches(10), Inches(0.8),
             "Slide title", font=FONT_HEAD, size=32, color=COLOR_DARK, bold=True)
    add_text(s, Inches(1), Inches(1.95), Inches(11), Inches(0.4),
             "Short supporting sentence under the title.",
             font=FONT_BODY, size=16, color=COLOR_MUTED)
    # Bullets
    bullets = [
        "Headline statement that owns the point",
        "Secondary supporting evidence with a number",
        "Third point that earns its space",
        "Fourth point only if it survives the cut",
    ]
    tb = s.shapes.add_textbox(Inches(1), Inches(2.8), Inches(11.3), Inches(4))
    tf = tb.text_frame
    tf.word_wrap = True
    for i, text in enumerate(bullets):
        p = tf.add_paragraph() if i else tf.paragraphs[0]
        p.alignment = PP_ALIGN.LEFT
        p.level = 0
        # Bullet character via XML (python-pptx does not expose it cleanly)
        pPr = p._pPr if p._pPr is not None else p._p.get_or_add_pPr()
        # Strip any existing bullet defs
        for tag in ('buChar','buAutoNum','buNone'):
            for el in pPr.findall(qn(f'a:{tag}')): pPr.remove(el)
        buChar = etree.SubElement(pPr, qn('a:buChar'))
        buChar.set('char', '•')
        buClr = etree.SubElement(pPr, qn('a:buClr'))
        srgb = etree.SubElement(buClr, qn('a:srgbClr'))
        srgb.set('val', '0284C7')
        pPr.set('marL', '342900')
        pPr.set('indent', '-342900')
        run = p.add_run()
        run.text = text
        run.font.name = FONT_BODY
        run.font.size = Pt(20)
        run.font.color.rgb = COLOR_DARK
        p.space_after = Pt(14)

# =========================================================================
# Slide 4 , Two-column comparison
# =========================================================================
def slide_two_column():
    s = blank_slide()
    add_rect(s, 0, 0, SLIDE_W, SLIDE_H, COLOR_WHITE)
    add_accent_rule(s, Inches(1), Inches(1.0))
    add_text(s, Inches(1), Inches(1.15), Inches(11), Inches(0.7),
             "Two-column comparison", font=FONT_HEAD, size=32, color=COLOR_DARK, bold=True)
    add_text(s, Inches(1), Inches(1.85), Inches(11), Inches(0.4),
             "Use this slide when the choice is binary.",
             font=FONT_BODY, size=16, color=COLOR_MUTED)

    # Left card
    card_w = Inches(5.6); card_h = Inches(4.2); card_y = Inches(2.7)
    add_rect(s, Inches(1.0), card_y, card_w, card_h, COLOR_TINT)
    add_rect(s, Inches(1.0), card_y, Inches(0.08), card_h, COLOR_PRIMARY)
    add_text(s, Inches(1.3), card_y + Inches(0.3), card_w - Inches(0.5), Inches(0.5),
             "OPTION A", font=FONT_HEAD, size=12, color=COLOR_PRIMARY, bold=True)
    add_text(s, Inches(1.3), card_y + Inches(0.75), card_w - Inches(0.5), Inches(0.7),
             "Headline takeaway", font=FONT_HEAD, size=22, color=COLOR_DARK, bold=True)
    add_text(s, Inches(1.3), card_y + Inches(1.7), card_w - Inches(0.5), Inches(2.2),
             "Three to five short lines describing this option, its strengths, its trade-offs, and the named tool or framework you would use to deliver it.",
             font=FONT_BODY, size=14, color=COLOR_MID)

    # Right card
    rx = Inches(6.7)
    add_rect(s, rx, card_y, card_w, card_h, COLOR_BG)
    add_rect(s, rx, card_y, Inches(0.08), card_h, COLOR_ACCENT)
    add_text(s, rx + Inches(0.3), card_y + Inches(0.3), card_w - Inches(0.5), Inches(0.5),
             "OPTION B", font=FONT_HEAD, size=12, color=COLOR_ACCENT, bold=True)
    add_text(s, rx + Inches(0.3), card_y + Inches(0.75), card_w - Inches(0.5), Inches(0.7),
             "Headline takeaway", font=FONT_HEAD, size=22, color=COLOR_DARK, bold=True)
    add_text(s, rx + Inches(0.3), card_y + Inches(1.7), card_w - Inches(0.5), Inches(2.2),
             "Three to five short lines describing this option, its strengths, its trade-offs, and the named tool or framework you would use to deliver it.",
             font=FONT_BODY, size=14, color=COLOR_MID)

# =========================================================================
# Slide 5 , Big-stat highlight
# =========================================================================
def slide_big_stat():
    s = blank_slide()
    add_rect(s, 0, 0, SLIDE_W, SLIDE_H, COLOR_DARK)
    add_text(s, Inches(1), Inches(1.0), Inches(11), Inches(0.5),
             "THE NUMBER THAT MATTERS", font=FONT_HEAD, size=14, color=COLOR_PRIMARY, bold=True)
    # Big stat
    add_text(s, Inches(1), Inches(1.8), Inches(11), Inches(2.8),
             "527%", font=FONT_HEAD, size=170, color=COLOR_WHITE, bold=True)
    add_rect(s, Inches(1), Inches(4.7), Inches(0.6), Pt(4), COLOR_ACCENT)
    add_text(s, Inches(1), Inches(4.85), Inches(11), Inches(0.7),
             "Year-over-year growth in AI-referred sessions",
             font=FONT_HEAD, size=26, color=COLOR_WHITE)
    add_text(s, Inches(1), Inches(5.7), Inches(11), Inches(0.5),
             "Source: Frase, January 2026",
             font=FONT_BODY, size=14, color=COLOR_MUTED)

# =========================================================================
# Slide 6 , Quote
# =========================================================================
def slide_quote():
    s = blank_slide()
    add_rect(s, 0, 0, SLIDE_W, SLIDE_H, COLOR_BG)
    # Oversized quotation mark
    add_text(s, Inches(0.7), Inches(0.3), Inches(2), Inches(2.5),
             "“", font=FONT_HEAD, size=200, color=COLOR_PRIMARY, bold=True)
    # Quote body
    add_text(s, Inches(2.0), Inches(2.0), Inches(10.5), Inches(2.6),
             "Brand authority is the single strongest predictor of AI citation. "
             "Brand mentions matter roughly three times more than backlinks.",
             font=FONT_HEAD, size=28, color=COLOR_DARK, bold=True)
    # Attribution
    add_rect(s, Inches(2.0), Inches(5.2), Inches(0.5), Pt(3), COLOR_ACCENT)
    add_text(s, Inches(2.0), Inches(5.35), Inches(10), Inches(0.4),
             "Princeton GEO Study  |  ACM KDD 2024",
             font=FONT_BODY, size=14, color=COLOR_MUTED, bold=True)

# =========================================================================
# Slide 7 , Three-card metric strip
# =========================================================================
def slide_three_cards():
    s = blank_slide()
    add_rect(s, 0, 0, SLIDE_W, SLIDE_H, COLOR_WHITE)
    add_accent_rule(s, Inches(1), Inches(1.0))
    add_text(s, Inches(1), Inches(1.15), Inches(11), Inches(0.7),
             "Three numbers that frame the case",
             font=FONT_HEAD, size=32, color=COLOR_DARK, bold=True)
    add_text(s, Inches(1), Inches(1.9), Inches(11), Inches(0.4),
             "Each card is a self-contained passage. Quotable, citable, scannable.",
             font=FONT_BODY, size=16, color=COLOR_MUTED)

    cards = [
        ("41%", "AI visibility lift from statistics-addition", "Princeton GEO study, 10,000 queries"),
        ("44.2%", "Of AI citations land in the first 30% of body content", "Kevin Indig, 1.2M ChatGPT answers"),
        ("3.49%", "Conversion rate from AI-search referrals (22% higher than organic)", "Martal 2026 benchmark"),
    ]
    card_w = Inches(3.7); card_h = Inches(3.6); top = Inches(2.9)
    gap = Inches(0.25)
    x = Inches(1.0)
    for stat, line, source in cards:
        add_rect(s, x, top, card_w, card_h, COLOR_TINT)
        add_rect(s, x, top, card_w, Inches(0.08), COLOR_PRIMARY)
        add_text(s, x + Inches(0.3), top + Inches(0.4), card_w - Inches(0.5), Inches(1.4),
                 stat, font=FONT_HEAD, size=56, color=COLOR_PRIMARY_D, bold=True)
        add_text(s, x + Inches(0.3), top + Inches(1.9), card_w - Inches(0.5), Inches(1.0),
                 line, font=FONT_BODY, size=16, color=COLOR_DARK, bold=True)
        add_text(s, x + Inches(0.3), top + Inches(3.0), card_w - Inches(0.5), Inches(0.4),
                 source, font=FONT_BODY, size=11, color=COLOR_MUTED)
        x += card_w + gap

# =========================================================================
# Slide 8 , Closing CTA
# =========================================================================
def slide_cta():
    s = blank_slide()
    add_rect(s, 0, 0, SLIDE_W, SLIDE_H, COLOR_DARK)
    add_rect(s, 0, Inches(7.38), SLIDE_W, Inches(0.12), COLOR_ACCENT)
    add_text(s, Inches(1), Inches(2.0), Inches(11), Inches(0.5),
             "NEXT STEP", font=FONT_HEAD, size=14, color=COLOR_PRIMARY, bold=True)
    add_text(s, Inches(1), Inches(2.5), Inches(11), Inches(2),
             "Let's plug the leaks in your funnel.",
             font=FONT_HEAD, size=44, color=COLOR_WHITE, bold=True)
    add_text(s, Inches(1), Inches(4.5), Inches(11), Inches(0.6),
             "Book a free UNIFY audit, 30 minutes, no slide deck on our side.",
             font=FONT_BODY, size=20, color=COLOR_BORDER)
    # CTA chip
    chip_x = Inches(1); chip_y = Inches(5.6); chip_w = Inches(3.2); chip_h = Inches(0.7)
    chip = s.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, chip_x, chip_y, chip_w, chip_h)
    chip.fill.solid(); chip.fill.fore_color.rgb = COLOR_PRIMARY
    chip.line.fill.background(); chip.shadow.inherit = False
    chip.adjustments[0] = 0.5
    add_text(s, chip_x, chip_y, chip_w, chip_h, "boomingventure.com",
             font=FONT_HEAD, size=16, color=COLOR_WHITE, bold=True,
             align=PP_ALIGN.CENTER, anchor=MSO_ANCHOR.MIDDLE)

# ---- Build all slides + footers ------------------------------------------
builders = [slide_cover, slide_section, slide_bullets, slide_two_column,
            slide_big_stat, slide_quote, slide_three_cards, slide_cta]
total = len(builders)
for fn in builders:
    fn()
for i, slide in enumerate(prs.slides, start=1):
    # No footer on cover (1) or CTA (last). Footer on the rest.
    if i not in (1, total):
        add_footer(slide, i, total)

out = "wp-content/themes/booming-venture/assets/booming-venture-template.pptx"
import os
os.makedirs(os.path.dirname(out), exist_ok=True)
prs.save(out)
print(f"Wrote {out}")
