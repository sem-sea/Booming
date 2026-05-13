/**
 * Booming Venture, ROI Forecaster
 * Vanilla JS, zero deps. Projects revenue + ROI over N months.
 */
(function () {
	'use strict';

	const fmt = (n, ccy) => new Intl.NumberFormat('en-US', { style: 'currency', currency: ccy || 'EUR', maximumFractionDigits: 0 }).format(Math.round(n));
	const fmtPct = (n) => `${(n * 100).toFixed(0)}%`;
	const fmtX = (n) => `${n.toFixed(2)}×`;
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

		return `<svg viewBox="0 0 ${w} ${h}" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Cumulative revenue forecast">
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
					<h3>Your business</h3>
					<p class="bv-roi__hint">Conservative estimates only. We model a linear ramp.</p>
					<label>Monthly qualified traffic
						<input type="number" min="0" step="100" name="traffic" value="8000" inputmode="numeric">
					</label>
					<label>Current conversion rate (%)
						<input type="number" min="0" max="100" step="0.1" name="baseConv" value="1.5">
					</label>
					<label>Target uplift from optimisation (%)
						<input type="number" min="0" max="200" step="5" name="uplift" value="35">
					</label>
					<label>Average order value (${ccy})
						<input type="number" min="0" step="50" name="aov" value="850">
					</label>
					<label>LTV multiplier (× AOV)
						<input type="number" min="1" max="10" step="0.1" name="ltvX" value="2.4">
					</label>
					<label>Monthly programme budget (${ccy})
						<input type="number" min="0" step="100" name="budget" value="4500">
					</label>
					<label>Ramp period (months)
						<input type="number" min="1" max="12" step="1" name="ramp" value="3">
					</label>
					<input type="hidden" name="months" value="${horizon}">
				</aside>

				<section class="bv-roi__results" aria-live="polite">
					<div class="bv-roi__headline">
						<div>
							<div class="bv-roi__metric-label">Cumulative revenue at month ${horizon}</div>
							<div class="bv-roi__metric bv-roi__metric--strong" data-out="cumRevenue">, </div>
							<div class="bv-roi__metric-sub" data-out="cumRevSub"></div>
						</div>
						<div>
							<div class="bv-roi__metric-label">Incremental revenue vs. doing nothing</div>
							<div class="bv-roi__metric" data-out="incremental">, </div>
						</div>
						<div class="bv-roi__roi">
							<div class="bv-roi__metric-label">ROI multiple</div>
							<div class="bv-roi__metric bv-roi__metric--roi" data-out="roi">, </div>
							<div class="bv-roi__metric-sub" data-out="breakeven"></div>
						</div>
					</div>

					<div class="bv-roi__chart" data-out="chart"></div>

					<table class="bv-roi__table">
						<thead><tr><th>Month</th><th>Customers</th><th>Revenue</th><th>Cumulative</th><th>ROI</th></tr></thead>
						<tbody data-out="rows"></tbody>
					</table>

					<div class="bv-roi__cta">
						<a class="bv-roi__btn" href="/#contact">Book a 30-min strategy call →</a>
						${showPdf ? '<button type="button" class="bv-roi__btn bv-roi__btn--ghost" data-action="print">Save as PDF</button>' : ''}
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
			out('cumRevSub').textContent   = `Baseline if you do nothing: ${fmt(r.baselineTotal, ccy)}`;
			out('incremental').textContent = fmt(r.incremental, ccy);
			out('roi').textContent         = fmtX(r.roiMultiple);
			out('breakeven').textContent   = r.breakevenMonth ? `Break-even at month ${r.breakevenMonth}` : 'No break-even inside this horizon, extend the budget or uplift assumption.';
			out('chart').innerHTML         = svgChart(r.series, ccy);

			const rowStep = Math.max(1, Math.floor(r.series.length / 8));
			out('rows').innerHTML = r.series
				.filter((s, i) => i % rowStep === 0 || i === r.series.length - 1)
				.map(s => `<tr><td>M${s.month}</td><td>${Math.round(s.customers).toLocaleString('en-US')}</td><td>${fmt(s.revenue, ccy)}</td><td>${fmt(s.cumulativeRevenue, ccy)}</td><td>${fmtX(s.roi)}</td></tr>`)
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
