/**
 * ROI Forecaster — front-end React entry.
 *
 * Copy the Lovable React components into ./components/:
 *  - ROIForecaster.tsx, ROIForecastForm.tsx, ROIForecastResults.tsx,
 *    ROIFunnelChart.tsx, ROIMetricsPanel.tsx
 */
import { createRoot } from 'react-dom/client';
import ROIForecaster from './components/ROIForecaster';

function mount() {
	document
		.querySelectorAll<HTMLElement>('[data-bv-block="roi-forecaster"]')
		.forEach((el) => {
			if (el.dataset.bvMounted) return;
			el.dataset.bvMounted = '1';

			const root = createRoot(el);
			root.render(
				<ROIForecaster
					currency={el.dataset.currency || 'EUR'}
					horizonMonths={Number(el.dataset.horizon || 12)}
					showPdfExport={el.dataset.pdfExport !== 'false'}
				/>
			);
		});
}

if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', mount);
} else {
	mount();
}
