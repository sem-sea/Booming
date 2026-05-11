/**
 * Editor placeholder for the ROI Forecaster block.
 */
import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl, SelectControl, RangeControl } from '@wordpress/components';

type Attrs = {
	currency: string;
	horizonMonths: number;
	showPdfExport: boolean;
};

interface EditProps {
	attributes: Attrs;
	setAttributes: (a: Partial<Attrs>) => void;
}

export default function Edit({ attributes, setAttributes }: EditProps) {
	const blockProps = useBlockProps({ className: 'bv-block-roi-forecaster bv-block-editor-placeholder' });
	return (
		<>
			<InspectorControls>
				<PanelBody title={__('ROI Forecaster', 'booming-venture')}>
					<SelectControl
						label={__('Currency', 'booming-venture')}
						value={attributes.currency}
						options={[
							{ label: 'EUR (€)', value: 'EUR' },
							{ label: 'USD ($)', value: 'USD' },
							{ label: 'GBP (£)', value: 'GBP' },
						]}
						onChange={(v) => setAttributes({ currency: v })}
					/>
					<RangeControl
						label={__('Forecast horizon (months)', 'booming-venture')}
						value={attributes.horizonMonths}
						min={3}
						max={24}
						onChange={(v) => setAttributes({ horizonMonths: Number(v) })}
					/>
					<ToggleControl
						label={__('Show PDF export', 'booming-venture')}
						checked={attributes.showPdfExport}
						onChange={(v) => setAttributes({ showPdfExport: v })}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<div className="bv-placeholder-card">
					<strong>📈 ROI Forecaster</strong>
					<p>{__('Renders on the front-end. Use the sidebar to tweak defaults.', 'booming-venture')}</p>
				</div>
			</div>
		</>
	);
}
