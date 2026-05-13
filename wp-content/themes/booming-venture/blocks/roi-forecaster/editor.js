/**
 * Booming Venture, ROI Forecaster block (editor side).
 */
(function (wp) {
	if (!wp || !wp.blocks) return;
	const { registerBlockType } = wp.blocks;
	const { useBlockProps, InspectorControls } = wp.blockEditor;
	const { PanelBody, ToggleControl, SelectControl, RangeControl } = wp.components;
	const { createElement: el, Fragment } = wp.element;
	const __ = (s) => (wp.i18n && wp.i18n.__ ? wp.i18n.__(s, 'booming-venture') : s);

	registerBlockType('booming-venture/roi-forecaster', {
		edit: function (props) {
			const a = props.attributes;
			const setA = props.setAttributes;
			const blockProps = useBlockProps({ className: 'bv-block-editor-placeholder' });

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __('ROI Forecaster') },
						el(SelectControl, {
							label: __('Currency'),
							value: a.currency,
							options: [
								{ label: 'EUR (€)', value: 'EUR' },
								{ label: 'USD ($)', value: 'USD' },
								{ label: 'GBP (£)', value: 'GBP' },
							],
							onChange: (v) => setA({ currency: v }),
						}),
						el(RangeControl, {
							label: __('Forecast horizon (months)'),
							value: a.horizonMonths,
							min: 3, max: 24,
							onChange: (v) => setA({ horizonMonths: Number(v) }),
						}),
						el(ToggleControl, {
							label: __('Show PDF export'),
							checked: !!a.showPdfExport,
							onChange: (v) => setA({ showPdfExport: v }),
						})
					)
				),
				el(
					'div',
					blockProps,
					el('strong', null, '📈 ROI Forecaster'),
					el('p', null, __('Interactive forecaster, renders on the front end. Adjust defaults from the sidebar.'))
				)
			);
		},
		save: function () { return null; },
	});
})(window.wp);
