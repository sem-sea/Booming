/**
 * Booming Venture, ROI Forecaster
 * Vanilla JS, zero deps. Projects revenue + ROI over N months.
 */
(function () {
	'use strict';

	/* Dutch UI on Dutch pages (<html lang="nl-NL">), English everywhere else. */
	const NL  = /^nl/i.test(document.documentElement.lang || '');
	const LOC = NL ? 'nl-NL' : 'en-US';
	const T = {
		'Cumulative revenue forecast': 'Prognose van de cumulatieve omzet',
		'Your business': 'Jouw bedrijf',
		'Conservative estimates only. We model a linear ramp.': 'Gebruik voorzichtige schattingen. We rekenen met een lineaire opbouw.',
		'Monthly qualified traffic': 'Gekwalificeerd verkeer per maand',
		'Current conversion rate (%)': 'Huidige conversieratio (%)',
		'Target uplift from optimisation (%)': 'Beoogde groei door optimalisatie (%)',
		'Average order value': 'Gemiddelde orderwaarde',
		'LTV multiplier (× AOV)': 'LTV-factor (× orderwaarde)',
		'Monthly programme budget': 'Programmabudget per maand',
		'Ramp period (months)': 'Opbouwperiode (maanden)',
		'Incremental revenue vs. doing nothing': 'Extra omzet ten opzichte van niets doen',
		'ROI multiple': 'ROI-factor',
		'Month': 'Maand',
		'Customers': 'Klanten',
		'Revenue': 'Omzet',
		'Cumulative': 'Cumulatief',
		'ROI': 'ROI',
		'Book a 30-min strategy call →': 'Plan een strategiegesprek van 30 minuten →',
		'Save as PDF': 'Opslaan als pdf',
		'No break-even inside this horizon, extend the budget or uplift assumption.': 'Geen break-even binnen deze periode. Verleng de periode of pas het budget of de groeiaanname aan.',
	};
	const t = (s) => (NL && T[s]) || s;

	const fmt = (n, ccy) => new Intl.NumberFormat(LOC, { style: 'currency', currency: ccy || 'EUR', maximumFractionDigits: 0 }).format(Math.round(n));
	const fmtPct = (n) => `${(n * 100).toFixed(0)}%`;
	const fmtX = (n) => NL ? `${n.toFixed(2).replace('.', ',')}×` : `${n.toFixed(2)}×`;
	const clamp = (n, lo, hi) => { n = +n || 0; return Math.min(hi, Math.max(lo, n)); };

	/**
	 * Forecast model.
	 * - We assume the optimisation programme lifts conversion by `uplift` linearly
	 *   over `rampMonths`, then steady state for the rest of the horizon.
	 * - Customer LTV adds a tail to the per-customer revenue (capped at LTV cycles).
	 */
	function forecast(inputs) {
		const months   = clamp(Math.round(inputs.months), 1, 60);
		const traffic  = Math.max(0, inputs.traffic);
		const baseConv = clamp(inputs.baseConv, 0, 100) / 100;
		const uplift   = clamp(inputs.uplift,   0, 200) / 100; // up to +200%
		const aov      = Math.max(0, inputs.aov);
		const ltvX     = clamp(inputs.ltvX,    1, 10);
		const budget   = Math.max(0, inputs.budget);
		const ramp     = clamp(Math.round(inputs.ramp), 1, 12);

		const series = [];
		let cumulativeRevenue = 0;
		let cumulativeSpend   = 0;
		const baseRevenuePerMonth = traffic * baseConv * aov * ltvX;

		for (let m = 1; m <= months; m++) {
			const rampFactor = Math.min(1, m / ramp);
			const conv       = baseConv * (1 + uplift * rampFactor);
			const customers  = traffic * conv;
			const revenue    = customers * aov * ltvX;
			const monthSpend = budget;
			cumulativeRevenue += revenue;
			cumulativeSpend   += monthSpend;

			series.push({
				month: m,
				customers,
				revenue,
				cumulativeRevenue,
				cumulativeSpend,
				netGain: cumulativeRevenue - cumulativeSpend - (baseRevenuePerMonth * m),
				roi: cumulativeSpend > 0 ? cumulativeRevenue / cumulativeSpend : 0,
			});
		}

		const last = series[series.length - 1];
		const baselineTotal = baseRevenuePerMonth * months;
		const incremental   = last.cumulativeRevenue - baselineTotal;
		const breakeven     = series.find(s => (s.cumulativeRevenue - baselineTotal) >= s.cumulativeSpend);

		return {
			series, last, baselineTotal, incremental,
			roiMultiple: last.cumulativeSpend > 0 ? incremental / last.cumulativeSpend : 0,
			breakevenMonth: breakeven ? breakeven.month : null,
		};
	}

	function svgChart(series, ccy) {
		const w = 320, h = 140, pad = { l: 36, r: 8, t: 10, b: 22 };
		const maxRev = Math.max(...series.map(s => s.cumulativeRevenue), 1);
		const xStep  = (w - pad.l - pad.r) / Math.max(1, series.length - 1);

		const xy = (i, v) => {
			const x = pad.l + i * xStep;
			const y = pad.t + (h - pad.t - pad.b) * (1 - v / maxRev);
			return [x, y];
		};

		const path = series.map((s, i) => {
			const [x, y] = xy(i, s.cumulativeRevenue);
			return `${i === 0 ? 'M' : 'L'} ${x.toFixed(1)} ${y.toFixed(1)}`;
		}).join(' ');

		const area = `${path} L ${pad.l + (series.length - 1) * xStep} ${h - pad.b} L ${pad.l} ${h - pad.b} Z`;

		const ticks = 4;
		const yAxis = Array.from({ length: ticks + 1 }, (_, i) => {
			const v = (maxRev * i) / ticks;
			const y = pad.t + (h - pad.t - pad.b) * (1 - i / ticks);
			return `
				<line x1="${pad.l}" x2="${w - pad.r}" y1="${y}" y2="${y}" stroke="#e2e8f0" stroke-width="0.5"/>
				<text x="${pad.l - 4}" y="${y + 3}" text-anchor="end" font-size="7" fill="#94a3b8">${fmt(v, ccy)}</text>
			`;
		}).join('');

		const xAxis = series.map((s, i) => {
			if (series.length > 12 && i % 2 !== 0 && i !== series.length - 1) return '';
			const [x] = xy(i, 0);
			return `<text x="${x}" y="${h - 8}" text-anchor="middle" font-size="7" fill="#94a3b8">M${s.month}</text>`;
		}).join('');

		return `<svg viewBox="0 0 ${w} ${h}" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="${t('Cumulative revenue forecast')}">
			${yAxis}
			<path d="${area}" fill="url(#bvg-roi)" opacity="0.35"/>
			<path d="${path}" fill="none" stroke="url(#bvg-roi-stroke)" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
			${xAxis}
			<defs>
				<linearGradient id="bvg-roi" x1="0" x2="0" y1="0" y2="1">
					<stop offset="0%"  stop-color="#0284c7" stop-opacity="0.65"/>
					<stop offset="100%" stop-color="#14b8a6" stop-opacity="0"/>
				</linearGradient>
				<linearGradient id="bvg-roi-stroke" x1="0" x2="1" y1="0" y2="0">
					<stop offset="0%"  stop-color="#0284c7"/>
					<stop offset="100%" stop-color="#14b8a6"/>
				</linearGradient>
			</defs>
		</svg>`;
	}

	function render(root) {
		const ccy     = root.dataset.currency || 'EUR';
		const horizon = Math.max(3, Math.min(24, parseInt(root.dataset.horizon || '12', 10)));
		const showPdf = root.dataset.pdfExport !== 'false';

		root.innerHTML = `
		<div class="bv-roi">
			<div class="bv-roi__grid">
				<aside class="bv-roi__inputs">
					<h3>${t('Your business')}</h3>
					<p class="bv-roi__hint">${t('Conservative estimates only. We model a linear ramp.')}</p>
					<label>${t('Monthly qualified traffic')}
						<input type="number" min="0" step="100" name="traffic" value="8000" inputmode="numeric">
					</label>
					<label>${t('Current conversion rate (%)')}
						<input type="number" min="0" max="100" step="0.1" name="baseConv" value="1.5">
					</label>
					<label>${t('Target uplift from optimisation (%)')}
						<input type="number" min="0" max="200" step="5" name="uplift" value="35">
					</label>
					<label>${t('Average order value')} (${ccy})
						<input type="number" min="0" step="50" name="aov" value="850">
					</label>
					<label>${t('LTV multiplier (× AOV)')}
						<input type="number" min="1" max="10" step="0.1" name="ltvX" value="2.4">
					</label>
					<label>${t('Monthly programme budget')} (${ccy})
						<input type="number" min="0" step="100" name="budget" value="4500">
					</label>
					<label>${t('Ramp period (months)')}
						<input type="number" min="1" max="12" step="1" name="ramp" value="3">
					</label>
					<input type="hidden" name="months" value="${horizon}">
				</aside>

				<section class="bv-roi__results" aria-live="polite">
					<div class="bv-roi__headline">
						<div>
							<div class="bv-roi__metric-label">${NL ? `Cumulatieve omzet in maand ${horizon}` : `Cumulative revenue at month ${horizon}`}</div>
							<div class="bv-roi__metric bv-roi__metric--strong" data-out="cumRevenue">, </div>
							<div class="bv-roi__metric-sub" data-out="cumRevSub"></div>
						</div>
						<div>
							<div class="bv-roi__metric-label">${t('Incremental revenue vs. doing nothing')}</div>
							<div class="bv-roi__metric" data-out="incremental">, </div>
						</div>
						<div class="bv-roi__roi">
							<div class="bv-roi__metric-label">${t('ROI multiple')}</div>
							<div class="bv-roi__metric bv-roi__metric--roi" data-out="roi">, </div>
							<div class="bv-roi__metric-sub" data-out="breakeven"></div>
						</div>
					</div>

					<div class="bv-roi__chart" data-out="chart"></div>

					<table class="bv-roi__table">
						<thead><tr><th>${t('Month')}</th><th>${t('Customers')}</th><th>${t('Revenue')}</th><th>${t('Cumulative')}</th><th>${t('ROI')}</th></tr></thead>
						<tbody data-out="rows"></tbody>
					</table>

					<div class="bv-roi__cta">
						<a class="bv-roi__btn" href="${NL ? '/nl/#contact' : '/#contact'}">${t('Book a 30-min strategy call →')}</a>
						${showPdf ? '<button type="button" class="bv-roi__btn bv-roi__btn--ghost" data-action="print">' + t('Save as PDF') + '</button>' : ''}
					</div>
				</section>
			</div>
		</div>`;

		const inputs = root.querySelectorAll('input[name]');
		const out = (k) => root.querySelector(`[data-out="${k}"]`);

		const update = () => {
			const v = {};
			inputs.forEach(i => v[i.name] = parseFloat(i.value || '0'));
			const r = forecast(v);
			const last = r.last;

			out('cumRevenue').textContent  = fmt(last.cumulativeRevenue, ccy);
			out('cumRevSub').textContent   = NL ? `Basislijn als je niets doet: ${fmt(r.baselineTotal, ccy)}` : `Baseline if you do nothing: ${fmt(r.baselineTotal, ccy)}`;
			out('incremental').textContent = fmt(r.incremental, ccy);
			out('roi').textContent         = fmtX(r.roiMultiple);
			out('breakeven').textContent   = r.breakevenMonth ? (NL ? `Break-even in maand ${r.breakevenMonth}` : `Break-even at month ${r.breakevenMonth}`) : t('No break-even inside this horizon, extend the budget or uplift assumption.');
			out('chart').innerHTML         = svgChart(r.series, ccy);

			const rowStep = Math.max(1, Math.floor(r.series.length / 8));
			out('rows').innerHTML = r.series
				.filter((s, i) => i % rowStep === 0 || i === r.series.length - 1)
				.map(s => `<tr><td>M${s.month}</td><td>${Math.round(s.customers).toLocaleString(LOC)}</td><td>${fmt(s.revenue, ccy)}</td><td>${fmt(s.cumulativeRevenue, ccy)}</td><td>${fmtX(s.roi)}</td></tr>`)
				.join('');
		};

		inputs.forEach(i => i.addEventListener('input', update));
		const printBtn = root.querySelector('[data-action="print"]');
		if (printBtn) printBtn.addEventListener('click', () => window.print());
		update();
	}

	function mount() {
		document.querySelectorAll('[data-bv-block="roi-forecaster"]').forEach((el) => {
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
