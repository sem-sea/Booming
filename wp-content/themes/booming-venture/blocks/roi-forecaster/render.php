<?php
/**
 * Server render for the ROI Forecaster block.
 *
 * @var array $attributes
 */
defined( 'ABSPATH' ) || exit;

$id       = wp_unique_id( 'bv-roi-' );
$currency = isset( $attributes['currency'] ) ? sanitize_text_field( $attributes['currency'] ) : 'EUR';
$horizon  = isset( $attributes['horizonMonths'] ) ? (int) $attributes['horizonMonths'] : 12;
$pdf      = ! empty( $attributes['showPdfExport'] ) ? 'true' : 'false';

$wrapper = get_block_wrapper_attributes( [ 'class' => 'bv-block-roi-forecaster', 'id' => $id ] );
?>
<div <?php echo $wrapper; ?>
	data-bv-block="roi-forecaster"
	data-currency="<?php echo esc_attr( $currency ); ?>"
	data-horizon="<?php echo esc_attr( (string) $horizon ); ?>"
	data-pdf-export="<?php echo esc_attr( $pdf ); ?>">
	<noscript>
		<p>The ROI Forecaster needs JavaScript. <a href="mailto:info@boomingventure.com">Email us</a> for a manual projection.</p>
	</noscript>
</div>
