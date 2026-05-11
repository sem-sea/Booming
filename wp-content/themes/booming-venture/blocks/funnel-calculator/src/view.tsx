/**
 * Funnel Leak Calculator — front-end React entry.
 *
 * The original Lovable React components live alongside this file:
 *  - FunnelCalculator.tsx, FunnelChart.tsx, FunnelForm.tsx,
 *    FunnelLostOpportunities.tsx, FunnelPerformance.tsx,
 *    FunnelResults.tsx, FunnelPDFExport.tsx, ForecastPyramid.tsx
 *
 * Copy them into ./components/ during the migration and import them here.
 * The Vite build will tree-shake unused exports and produce build/view.js.
 */
import { createRoot } from 'react-dom/client';
import FunnelCalculator from './components/FunnelCalculator';

function mount() {
	document
		.querySelectorAll<HTMLElement>('[data-bv-block="funnel-calculator"]')
		.forEach((el) => {
			if (el.dataset.bvMounted) return;
			el.dataset.bvMounted = '1';

			const root = createRoot(el);
			root.render(
				<FunnelCalculator
					currency={el.dataset.currency || 'EUR'}
					defaultStage={el.dataset.defaultStage || 'leads'}
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
