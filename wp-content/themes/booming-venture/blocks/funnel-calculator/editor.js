/**
 * Booming Venture ,  Funnel Calculator block (editor side).
 * Renders a static preview in the editor; the real app runs on the front end.
 */
(function (wp) {
	if (!wp || !wp.blocks) return;
	const { registerBlockType } = wp.blocks;
	const { useBlockProps, InspectorControls } = wp.blockEditor;
	const { PanelBody, ToggleControl, SelectControl } = wp.components;
	const { createElement: el, Fragment } = wp.element;
	const __ = (s) => (wp.i18n && wp.i18n.__ ? wp.i18n.__(s, 'booming-venture') : s);

	registerBlockType('booming-venture/funnel-calculator', {
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
						{ title: __('Funnel Calculator') },
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
					el('strong', null, '📉 Funnel Leak Calculator'),
					el('p', null, __('Interactive calculator ,  renders on the front end. Use the sidebar to set currency.'))
				)
			);
		},
		save: function () { return null; }, // server-rendered
	});
})(window.wp);
