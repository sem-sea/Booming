<?php
/**
 * Server render for the Funnel Leak Calculator block.
 * Outputs a mount point; the React bundle in build/view.js hydrates it.
 *
 * @var array  $attributes
 * @var string $content
 * @var WP_Block $block
 */
defined( 'ABSPATH' ) || exit;

$id       = wp_unique_id( 'bv-funnel-' );
$currency = isset( $attributes['currency'] ) ? sanitize_text_field( $attributes['currency'] ) : 'EUR';
$stage    = isset( $attributes['defaultStage'] ) ? sanitize_text_field( $attributes['defaultStage'] ) : 'leads';
$pdf      = ! empty( $attributes['showPdfExport'] ) ? 'true' : 'false';

$wrapper = get_block_wrapper_attributes( [ 'class' => 'bv-block-funnel-calculator', 'id' => $id ] );
?>
<div <?php echo $wrapper; ?>
	data-bv-block="funnel-calculator"
	data-currency="<?php echo esc_attr( $currency ); ?>"
	data-default-stage="<?php echo esc_attr( $stage ); ?>"
	data-pdf-export="<?php echo esc_attr( $pdf ); ?>">
	<noscript>
		<p>The Funnel Leak Calculator needs JavaScript. Please enable it, or <a href="mailto:info@boomingventure.com">email us</a> for a manual analysis.</p>
	</noscript>
</div>
