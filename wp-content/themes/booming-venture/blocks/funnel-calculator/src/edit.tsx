/**
 * Editor-side placeholder for the Funnel Calculator block.
 * Heavy React app is loaded only on the front-end (viewScript) for performance.
 */
import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl, SelectControl } from '@wordpress/components';

type Attrs = {
	currency: string;
	defaultStage: string;
	showPdfExport: boolean;
};

interface EditProps {
	attributes: Attrs;
	setAttributes: (a: Partial<Attrs>) => void;
}

export default function Edit({ attributes, setAttributes }: EditProps) {
	const blockProps = useBlockProps({ className: 'bv-block-funnel-calculator bv-block-editor-placeholder' });

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Funnel Calculator', 'booming-venture')}>
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
					<SelectControl
						label={__('Default stage', 'booming-venture')}
						value={attributes.defaultStage}
						options={[
							{ label: 'Leads',       value: 'leads' },
							{ label: 'MQLs',        value: 'mqls' },
							{ label: 'Opportunities', value: 'opps' },
						]}
						onChange={(v) => setAttributes({ defaultStage: v })}
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
					<strong>📉 Funnel Leak Calculator</strong>
					<p>{__('Renders on the front-end. Use the sidebar to tweak defaults.', 'booming-venture')}</p>
				</div>
			</div>
		</>
	);
}
