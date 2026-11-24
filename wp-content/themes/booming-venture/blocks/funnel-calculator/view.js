/**
 * Booming Venture, Funnel Leak Calculator
 * Vanilla JS, zero deps. Hydrates every <div data-bv-block="funnel-calculator">.
 */
(function () {
	'use strict';

	/* Dutch UI on Dutch pages (<html lang="nl-NL">), English everywhere else. */
	const NL  = /^nl/i.test(document.documentElement.lang || '');
	const LOC = NL ? 'nl-NL' : 'en-US';
	const T = {
		'Visitor → Lead': 'Bezoeker → lead',
		'Lead → MQL': 'Lead → MQL',
		'MQL → SQL': 'MQL → SQL',
		'SQL → Customer': 'SQL → klant',
		'lead': 'Je verkeer wordt minder vaak lead dan het branchegemiddelde. Check je landingspagina\'s: een heldere waardepropositie, één CTA en sociaal bewijs boven de vouw.',
		'mql': 'Leads groeien niet door tot MQL. Scherp je leadscoring aan en voeg een nurturereeks toe; de meeste leads haken af voordat ze klaar zijn om te kopen.',
		'sql': 'MQL\'s plannen geen gesprek in. Kijk naar de snelheid van overdracht, je kwalificatievragen en de drempels bij het inplannen (formulieren met veel stappen werken hier tegen).',
		'win': 'Deals gaan verloren op de laatste meters. Breng de bezwaren in de laatste fase in kaart, zorg voor een vaste opvolging door sales en kijk opnieuw naar prijs en pakketten.',
		'Funnel performance': 'Funnelprestaties',
		'Your funnel': 'Jouw funnel',
		'Last-90-day numbers work best. We compare against B2B benchmarks.': 'Cijfers van de laatste 90 dagen werken het best. We vergelijken met B2B-benchmarks.',
		'Monthly visitors': 'Bezoekers per maand',
		'Visitor → Lead rate (%)': 'Conversie bezoeker → lead (%)',
		'Lead → MQL rate (%)': 'Conversie lead → MQL (%)',
		'MQL → SQL rate (%)': 'Conversie MQL → SQL (%)',
		'SQL → Customer rate (%)': 'Conversie SQL → klant (%)',
		'Average order value': 'Gemiddelde orderwaarde',
		'Current monthly revenue': 'Huidige omzet per maand',
		'If your funnel hit benchmark': 'Als je funnel de benchmark haalt',
		'You\'re leaving on the table': 'Dit laat je liggen',
		'Biggest leak:': 'Grootste lek:',
		'Book a 30-min audit →': 'Plan een audit van 30 minuten →',
		'Save as PDF': 'Opslaan als pdf',
		'You\'re at or above benchmark, nice.': 'Je zit op of boven de benchmark. Goed bezig.',
	};
	const t = (s) => (NL && T[s]) || s;
	const dec = (n, d) => NL ? n.toFixed(d).replace('.', ',') : n.toFixed(d);

	const fmt = (n, ccy) => new Intl.NumberFormat(LOC, {
		style: 'currency', currency: ccy || 'EUR', maximumFractionDigits: 0
	}).format(Math.round(n));

	const num = (n) => new Intl.NumberFormat(LOC).format(Math.round(n));

	function calc(inputs) {
		const visitors  = Math.max(0, inputs.visitors);
		const leadRate  = clamp(inputs.leadRate, 0, 100) / 100;
		const mqlRate   = clamp(inputs.mqlRate, 0, 100) / 100;
		const sqlRate   = clamp(inputs.sqlRate, 0, 100) / 100;
		const winRate   = clamp(inputs.winRate, 0, 100) / 100;
		const aov       = Math.max(0, inputs.aov);

		const leads = visitors * leadRate;
		const mqls  = leads    * mqlRate;
		const sqls  = mqls     * sqlRate;
		const wins  = sqls     * winRate;
		const revenue = wins * aov;

		// Benchmarks (industry-average funnel), used to compute "what you could be making"
		const benchmarks = { lead: 0.04, mql: 0.30, sql: 0.40, win: 0.25 };
		const bLeads = visitors * benchmarks.lead;
		const bMqls  = bLeads   * benchmarks.mql;
		const bSqls  = bMqls    * benchmarks.sql;
		const bWins  = bSqls    * benchmarks.win;
		const bRev   = bWins * aov;

		const leakage = Math.max(0, bRev - revenue);
		const leakPct = bRev > 0 ? (leakage / bRev) * 100 : 0;

		// Stage-by-stage leak vs benchmark (in revenue terms, normalised to win-rate)
		const stages = [
			{ key: 'lead', label: t('Visitor → Lead'),  you: leadRate, bench: benchmarks.lead },
			{ key: 'mql',  label: t('Lead → MQL'),      you: mqlRate,  bench: benchmarks.mql  },
			{ key: 'sql',  label: t('MQL → SQL'),       you: sqlRate,  bench: benchmarks.sql  },
			{ key: 'win',  label: t('SQL → Customer'),  you: winRate,  bench: benchmarks.win  },
		].map(s => {
			const gap = Math.max(0, s.bench - s.you);
			return { ...s, gap, gapPct: s.bench > 0 ? (gap / s.bench) * 100 : 0 };
		});

		// Worst stage = biggest gap %
		const worst = stages.reduce((a, b) => a.gapPct > b.gapPct ? a : b, stages[0]);

		return {
			visitors, leads, mqls, sqls, wins,
			revenue, benchmarkRevenue: bRev, leakage, leakPct,
			stages, worst,
		};
	}

	function clamp(n, lo, hi) { n = +n || 0; return Math.min(hi, Math.max(lo, n)); }

	function recommendation(worst) {
		const map = {
			lead: 'Your traffic isn’t converting to leads at the industry rate. Audit landing pages: clarity of value prop, single CTA, social proof above the fold.',
			mql:  'Leads are not maturing into MQLs. Tighten lead scoring + add a nurture sequence; most of these are leaving before they’re ready to buy.',
			sql:  'MQLs are not booking calls. Look at handoff speed, qualification questions, and call-booking friction (multi-step forms kill this).',
			win:  'Lost deals at the finish line. Map the last-touch objections, install proper sales follow-up cadence, and revisit pricing/packaging.',
		};
		return (NL && T[worst.key]) || map[worst.key] || '';
	}

	function svgBars(stages) {
		const w = 100, h = 8, gap = 1.5;
		const rowH = h * 2 + gap;
		return `<svg viewBox="0 0 ${w} ${stages.length * (rowH + 6)}" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="${t('Funnel performance')}">
			${stages.map((s, i) => {
				const y = i * (rowH + 6);
				const youW   = clamp(s.you * 100 / s.bench, 0, 100);
				const benchW = 100;
				return `
					<g transform="translate(0, ${y})">
						<rect x="0" y="0"   width="${benchW}" height="${h}" rx="2" fill="#e0f2fe"/>
						<rect x="0" y="0"   width="${youW}"   height="${h}" rx="2" fill="url(#bvg)"/>
						<text x="0" y="${h + gap + h - 1}" font-size="3.6" fill="#475569">${NL ? `${s.label}: jij ${dec(s.you*100, 1)}% tegen benchmark ${dec(s.bench*100, 0)}%` : `${s.label}, you ${(s.you*100).toFixed(1)}% vs bench ${(s.bench*100).toFixed(0)}%`}</text>
					</g>
				`;
			}).join('')}
			<defs>
				<linearGradient id="bvg" x1="0" x2="1" y1="0" y2="0">
					<stop offset="0%"  stop-color="#0284c7"/>
					<stop offset="100%" stop-color="#14b8a6"/>
				</linearGradient>
			</defs>
		</svg>`;
	}

	function render(root) {
		const ccy = root.dataset.currency || 'EUR';
		const showPdf = root.dataset.pdfExport !== 'false';

		root.innerHTML = `
		<div class="bv-fc">
			<div class="bv-fc__grid">
				<aside class="bv-fc__inputs">
					<h3>${t('Your funnel')}</h3>
					<p class="bv-fc__hint">${t('Last-90-day numbers work best. We compare against B2B benchmarks.')}</p>
					<label>${t('Monthly visitors')}
						<input type="number" min="0" step="100" name="visitors" value="10000" inputmode="numeric">
					</label>
					<label>${t('Visitor → Lead rate (%)')}
						<input type="number" min="0" max="100" step="0.1" name="leadRate" value="2.5">
					</label>
					<label>${t('Lead → MQL rate (%)')}
						<input type="number" min="0" max="100" step="0.1" name="mqlRate" value="25">
					</label>
					<label>${t('MQL → SQL rate (%)')}
						<input type="number" min="0" max="100" step="0.1" name="sqlRate" value="35">
					</label>
					<label>${t('SQL → Customer rate (%)')}
						<input type="number" min="0" max="100" step="0.1" name="winRate" value="20">
					</label>
					<label>${t('Average order value')} (${ccy})
						<input type="number" min="0" step="50" name="aov" value="1200">
					</label>
				</aside>

				<section class="bv-fc__results" aria-live="polite">
					<div class="bv-fc__headline">
						<div>
							<div class="bv-fc__metric-label">${t('Current monthly revenue')}</div>
							<div class="bv-fc__metric bv-fc__metric--strong" data-out="revenue">, </div>
						</div>
						<div>
							<div class="bv-fc__metric-label">${t('If your funnel hit benchmark')}</div>
							<div class="bv-fc__metric" data-out="benchmark">, </div>
						</div>
						<div class="bv-fc__leak">
							<div class="bv-fc__metric-label">${t("You're leaving on the table")}</div>
							<div class="bv-fc__metric bv-fc__metric--leak" data-out="leak">, </div>
							<div class="bv-fc__metric-sub" data-out="leakPct">, </div>
						</div>
					</div>

					<div class="bv-fc__chart" data-out="chart"></div>

					<div class="bv-fc__rec">
						<strong>${t('Biggest leak:')}</strong> <span data-out="worstLabel">, </span><br>
						<span data-out="worstRec"></span>
					</div>

					<div class="bv-fc__cta">
						<a class="bv-fc__btn" href="${NL ? '/nl/#contact' : '/#contact'}">${t('Book a 30-min audit →')}</a>
						${showPdf ? '<button type="button" class="bv-fc__btn bv-fc__btn--ghost" data-action="print">' + t('Save as PDF') + '</button>' : ''}
					</div>
				</section>
			</div>
		</div>`;

		const inputs = root.querySelectorAll('input[name]');
		const out = (k) => root.querySelector(`[data-out="${k}"]`);

		const update = () => {
			const values = {};
			inputs.forEach(i => values[i.name] = parseFloat(i.value || '0'));
			const r = calc(values);
			out('revenue').textContent   = fmt(r.revenue, ccy);
			out('benchmark').textContent = fmt(r.benchmarkRevenue, ccy);
			out('leak').textContent      = fmt(r.leakage, ccy);
			out('leakPct').textContent   = r.leakPct > 0 ? (NL ? `≈ ${dec(r.leakPct, 0)}% onder de benchmark · nu ${num(r.wins)} klanten per maand` : `≈ ${r.leakPct.toFixed(0)}% below benchmark · ${num(r.wins)} customers/mo today`) : t('You\'re at or above benchmark, nice.');
			out('worstLabel').textContent = r.worst.label;
			out('worstRec').textContent   = recommendation(r.worst);
			out('chart').innerHTML        = svgBars(r.stages);
		};

		inputs.forEach(i => i.addEventListener('input', update));
		const printBtn = root.querySelector('[data-action="print"]');
		if (printBtn) printBtn.addEventListener('click', () => window.print());
		update();
	}

	function mount() {
		document.querySelectorAll('[data-bv-block="funnel-calculator"]').forEach((el) => {
			if (el.dataset.bvMounted) return;
			el.dataset.bvMounted = '1';
			render(el);
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', mount);
	} else {
		mount();
	}
})();
