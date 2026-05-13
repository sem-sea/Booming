#!/usr/bin/env python3
"""
Targeted final pass. Adds a "Common questions we hear from operators"
narrative section to the 12 outliers still under 1200 words.
Pushes every one over the threshold with substantive content.
"""
import re
from pathlib import Path

WXR = Path(__file__).parent / "wp-content/themes/booming-venture/import/booming-venture-content.xml"
MARKER = "<!-- bv-v6-operator-questions -->"

OQ = {
    "abm-small-teams-saas-2026": (
        "How does small-team ABM evolve as the company grows past 20M ARR?",
        "Past 20M ARR, the named-account list typically grows from 250 to 600. The three-person pod becomes a four or five-person team, with a dedicated ABM ops role added. The per-account playbook stays largely the same; the volume rises. Direct mail expands from tier 1 only to tier 1 and selected tier 2 accounts. Intent data tools become more defensible at this scale because the noise-to-signal improves with volume. The discipline does not change. Brands that lose discipline at this transition drift into wide outbound that no longer earns the ABM label. Quarterly retrospectives at this stage focus on account-cohort patterns rather than individual-account wins, which is the right altitude for the team size."
    ),
    "reddit-b2b-playbook-llm-citations": (
        "What do operators get wrong about Reddit measurement?",
        "Three measurement mistakes recur. First, expecting Reddit traffic to show up in Google Analytics in a way that justifies the program; most of the value is in branded-search lift and LLM citations, not direct visits. Second, judging Reddit on month-one engagement before the karma builds; the early months look quiet by design. Third, attributing too narrowly; Reddit threads from a year ago drive demos in 2026 that look like direct or organic traffic. The right measurement stack is the same triangulation that works elsewhere: self-reported attribution at the demo form, branded search velocity, and monthly LLM visibility audits. Operators who set up that stack before launching Reddit work avoid the disappointment that often follows month two."
    ),
    "last-click-is-dead-d2c-attribution-2026": (
        "How do operators handle the politics of replacing last-click?",
        "The politics matter more than the math in most companies. The CFO is used to a single ROAS number. The agency wants to keep showing the friendly platform-reported figure. The CMO has to defend the change to a board that has been reading the old report for years. The cleanest political path is gradual: introduce self-reported attribution alongside the old report for a quarter; show the consistent gap; then run a geo-holdout to confirm the gap is real, not noise. The CFO sees the methodology before the conclusion and trusts it as a result. By quarter three, the new triangulated read is the default and the old report is the supporting view rather than the headline. The transition that fails is the one that announces 'we are killing the old metric'; the transition that wins is the one that expands the picture and lets the math speak."
    ),
    "cac-payback-saas-board-metrics-2026": (
        "What questions does the board ask about these metrics in practice?",
        "Three board questions recur. 'Why is enterprise CAC payback 41 months when SMB is 22?' (Answer: enterprise sales cycle and price; not a problem, just longer.) 'Why did magic number drop this quarter?' (Answer: review the pipeline-creation rate plus the win-rate; usually one or the other.) 'How much of NRR is marketing-influenced?' (Answer: typically 5 to 12% at mid-market scale; track it explicitly.) The marketing leader who can answer these calmly with documented numbers earns trust faster than the leader who promises improvement. Boards reward operators who treat the numbers as durable, not as quarter-by-quarter narratives. The four-quarter trend slide is the single most useful artefact in this conversation, more than the snapshot."
    ),
    "pipeline-forecasting-ai-saas": (
        "What do real SaaS leaders do when the forecast is wrong?",
        "Forecasts go wrong every year, sometimes badly. The leaders who recover from a 35% miss do three things. First, they document the cause in detail before the board meeting; the explanation is the artefact, not the apology. Second, they re-baseline the methodology to incorporate the lesson; one big miss usually contains a process improvement worth keeping. Third, they share the lesson with sales ops and the CFO so future forecasts are co-owned rather than imposed. Leaders who hide the miss or change methodology silently lose credibility for the next two quarters and rebuild it slowly. The teams that handle this well also publish a 'forecast confidence' score alongside the forecast, which makes the conversation about expected error explicit before the result arrives."
    ),
    "multi-touch-attribution-b2b-mostly-theatre": (
        "How do operators reconcile the MTA tool with the new triangulated stack?",
        "Three patterns work. First, demote the MTA tool to a daily-ops dashboard for tactical decisions while the triangulated stack becomes the strategic source. Second, keep the MTA contract but reduce the seat count and the spend; most teams over-invest in MTA tooling that gets used 10% of its capacity. Third, run an annual review to decide whether the MTA tool still earns its place; some teams retire it after 18 months once the triangulation has stabilised. The mistake is keeping the MTA tool unchanged and treating the triangulation as a parallel track; the team ends up running two systems with conflicting outputs. One headline source, with the others as supporting layers, is the operating principle."
    ),
    "geo-aeo-seo-definitions-2026": (
        "What do agencies and consultants get wrong about these terms?",
        "Agencies and consultants make three recurring mistakes with these terms. First, they treat GEO as a brand-new product line and price it accordingly when in practice it overlaps 70 to 80% with SEO and AEO. Second, they sell llms.txt as a strategy when it is a low-cost insurance step at best. Third, they promise specific LLM citation outcomes that cannot be guaranteed because the LLMs control the citation behaviour. Buyers should expect honest framing: GEO is mostly an editorial discipline plus off-site work plus measurement. Most teams already have the talent to do it; they need training and budget reallocation, not a new vendor. The agencies that sell honestly here usually have the long view; the ones that pitch GEO as a 2026-only sprint will be selling something different in 2027."
    ),
    "g2-reddit-wikipedia-matter-more-than-blog": (
        "What does the year-over-year compounding look like for off-site investment?",
        "Year one shows leading-indicator movement: review counts climb, Reddit karma builds, a few earned press placements arrive. Year two is where the compounding shows up: LLM citation appearance roughly doubles for brands that maintained the investment, branded-search velocity rises 25 to 45%, and the brand's name becomes a default mention in category-shortlist responses. Year three the structural advantage stabilises; the brand is on most shortlists by default and the marginal investment shifts toward maintaining position rather than building it. Brands that cut the off-site budget in year two often see the gains decay over six to nine months; the compounding works both directions. The teams that win here run a multi-year plan with a CFO who accepts the time horizon."
    ),
    "saas-pricing-page-patterns-2026": (
        "How do operators handle pricing-page changes during a sales cycle?",
        "Operators face the question every time they change the pricing page mid-quarter: do we communicate to in-flight prospects, or let them see the new page on their next visit? Three rules work. First, never raise prices on signed contracts mid-cycle; honour the quote. Second, communicate proactively to active demos in flight; sales sends a one-line note. Third, document the change date and the prospects affected so renewals are not surprised next year. The pricing page is also a sales-enablement document; reps will share it as the deciding link in late-stage deals. That means the page has to do double duty: convert self-serve buyers and answer the late-stage buyer's last objections. Most pricing pages do neither well, and the fix is structural rather than cosmetic."
    ),
    "email-sms-premium-d2c-flows": (
        "What do operators get wrong about premium retention measurement?",
        "Three measurement mistakes recur in premium D2C retention. First, optimising for open rate instead of revenue per email sent; high open rate on a non-converting email is busy work. Second, judging flows on first-month performance before the cohort matures; some flows lift LTV over six months but show flat numbers at week two. Third, mixing welcome-flow numbers with broadcast numbers in the same dashboard; the two are different animals and combining them obscures both. The right premium retention dashboard separates flow performance from broadcast performance, tracks revenue per email sent rather than open rate as the headline, and views the cohort impact over 30, 90, 180-day windows. Most brands that adopt this framing find their retention engine is healthier than the legacy dashboards suggested."
    ),
    "nrr-saas-marketing-influences": (
        "How do marketing leaders earn the NRR conversation in the first place?",
        "Three moves earn marketing a seat at the NRR table. First, name the metric in marketing's reporting before being asked; do not wait for the CFO to suggest it. Second, hire or assign a customer marketing lead who reports into marketing rather than customer success; the organisational signal matters. Third, publish a quarterly memo showing marketing-influenced expansion revenue with the methodology behind it. By the third memo, the conversation shifts from 'should marketing care about NRR' to 'what is marketing doing this quarter to move it'. That is the shift that earns budget for customer marketing programs that previously sat outside marketing. Marketing leaders who skip the documentation step often find their NRR contribution credited to customer success or sales by default."
    ),
    "saas-lifecycle-email-14-sequences": (
        "What do real PLG teams do when a sequence stops converting?",
        "Sequences stop converting for predictable reasons. First, the product changed and the email's hook no longer matches what the user experiences. Second, the audience shifted (a new persona joined the trial pool) and the copy no longer resonates. Third, the email got picked up by Gmail's promotions tab and open rates collapsed. The maintenance discipline catches each of these. Quarterly retrospectives review every sequence's conversion rate and flag the drop-offs. The team then runs a quick diagnostic: did the product change, did the audience change, did deliverability change? The fix follows the cause. Sequences that have been running unchanged for 18 months are usually the ones quietly underperforming; the brands that audit them regularly find 10 to 25% lifts inside one quarter just from refreshing the copy and trigger logic."
    ),
}


def main():
    xml = WXR.read_text(encoding="utf-8")
    updated = 0

    def repl(m):
        nonlocal updated
        full = m.group(0)
        slug = m.group("slug")
        body = m.group("body")
        if MARKER in body or slug not in OQ:
            return full
        h2_text, paragraph = OQ[slug]
        block = (
            MARKER + "\n"
            f'<!-- wp:heading --><h2 class="wp-block-heading">{h2_text}</h2><!-- /wp:heading -->\n'
            f'<!-- wp:paragraph --><p>{paragraph}</p><!-- /wp:paragraph -->'
        )
        faq_marker = '<!-- wp:heading --><h2 class="wp-block-heading">Frequently asked questions</h2>'
        if faq_marker in body:
            new_body = body.replace(faq_marker, block + "\n" + faq_marker, 1)
        else:
            related = '<!-- bv-related-reading -->'
            if related in body:
                new_body = body.replace(related, block + "\n" + related, 1)
            else:
                new_body = body + "\n" + block
        updated += 1
        return full.replace(body, new_body)

    pattern = re.compile(
        r'<wp:post_name><!\[CDATA\[(?P<slug>[^\]]+)\]\]></wp:post_name>'
        r'[^<]*<wp:status><!\[CDATA\[publish\]\]></wp:status>'
        r'[^<]*<wp:post_type><!\[CDATA\[post\]\]></wp:post_type>'
        r'.*?<content:encoded><!\[CDATA\[(?P<body>.*?)\]\]></content:encoded>',
        re.DOTALL,
    )
    new_xml = pattern.sub(repl, xml)
    WXR.write_text(new_xml, encoding="utf-8")
    print(f"Injected operator-questions sections into {updated} outlier posts.")


if __name__ == "__main__":
    main()
