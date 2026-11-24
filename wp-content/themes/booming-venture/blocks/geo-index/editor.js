/**
 * Booming Venture, B2B GEO Index block (editor side).
 * Shows a placeholder in the editor; the ranking renders on the front end.
 */
(function (wp) {
	if (!wp || !wp.blocks) return;
	var el = wp.element.createElement;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var PanelBody = wp.components.PanelBody;
	var SelectControl = wp.components.SelectControl;

	wp.blocks.registerBlockType('booming-venture/geo-index', {
		edit: function (props) {
			var a = props.attributes;
			return el(wp.element.Fragment, null,
				el(InspectorControls, null,
					el(PanelBody, { title: 'B2B GEO Index' },
						el(SelectControl, {
							label: 'Default list',
							value: a.list,
							options: [
								{ label: 'By page language', value: '' },
								{ label: 'Netherlands', value: 'nl' },
								{ label: 'International', value: 'intl' }
							],
							onChange: function (v) { props.setAttributes({ list: v }); }
						})
					)
				),
				el('div', useBlockProps({ className: 'bv-block-editor-placeholder' }),
					el('strong', null, 'B2B GEO Index'),
					el('p', null, 'The ranking, filters and domain check render on the front end.')
				)
			);
		},
		save: function () { return null; }
	});
})(window.wp);
