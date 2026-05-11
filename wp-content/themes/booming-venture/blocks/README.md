# Booming Venture — Custom Gutenberg blocks

This workspace builds the two interactive React widgets from the original
Lovable site into self-contained Gutenberg blocks shipped with the theme.

## Build

```bash
cd wp-content/themes/booming-venture/blocks
npm install
npm run build       # produces funnel-calculator/build + roi-forecaster/build
```

`functions.php` auto-registers each block from its `block.json`, so the
moment the `build/` folders exist, the blocks appear in the inserter
under **Booming Venture**.

## Migrating the original components

The Lovable React source files were copied into:

- `funnel-calculator/src/components/` — `FunnelCalculator.tsx`,
  `FunnelChart.tsx`, `FunnelForm.tsx`, `FunnelLostOpportunities.tsx`,
  `FunnelPerformance.tsx`, `FunnelResults.tsx`, `FunnelPDFExport.tsx`,
  `ForecastPyramid.tsx`, `formatNumbers.ts`
- `roi-forecaster/src/components/` — `ROIForecaster.tsx`,
  `ROIForecastForm.tsx`, `ROIForecastResults.tsx`, `ROIFunnelChart.tsx`,
  `ROIMetricsPanel.tsx`

Before the first `npm run build` you'll need to:

1. Replace shadcn/ui imports (`@/components/ui/*`) with either a vendored
   subset or plain HTML/Tailwind equivalents. The blocks target a
   WordPress runtime, not a Vite/Tailwind dev server.
2. Replace `react-router-dom` imports (`Link`, `useNavigate`) with
   regular `<a href>` elements.
3. Replace `@/lib/utils` `cn()` with a tiny helper or `clsx`.
4. Replace `@/hooks/use-toast` with `window.alert()` for v1, or any
   notification library you prefer.

The intent: keep the heavy logic (charts, calculations, PDF export)
intact and only swap the UI primitives.

## Why a separate workspace

`@wordpress/scripts` already handles JSX/TSX, SCSS, asset hashing, and
the `webpack.config.js` it ships with knows how to externalise
`react`, `react-dom`, `@wordpress/*` against the WordPress runtime —
which means the bundles stay small and reuse the runtime React.
