<?php
/**
 * ROI Forecaster , SSR.
 *
 * @package OndernemerMarketing
 */
defined( 'ABSPATH' ) || exit;
$id = wp_unique_id( 'ondm-roi-' );
?>
<section class="ondm-tool ondm-roi" id="<?php echo esc_attr( $id ); ?>" <?php echo get_block_wrapper_attributes(); ?>>
	<div class="ondm-tool__inner">
		<h2 class="ondm-tool__title">ROI Forecaster</h2>
		<p class="ondm-tool__lead">Voorspel je return op een ad-budget, je payback in maanden en je 12-maands resultaat.</p>

		<div class="ondm-tool__grid">
			<label class="ondm-tool__field">
				<span class="ondm-tool__label">Maandelijks ad-budget (&euro;)</span>
				<input type="number" min="0" step="100" value="2000" data-ondm="budget">
			</label>
			<label class="ondm-tool__field">
				<span class="ondm-tool__label">Verwachte CAC (&euro;)</span>
				<input type="number" min="0" step="10" value="150" data-ondm="cac">
			</label>
			<label class="ondm-tool__field">
				<span class="ondm-tool__label">LTV per klant (&euro;)</span>
				<input type="number" min="0" step="50" value="1500" data-ondm="ltv">
			</label>
			<label class="ondm-tool__field">
				<span class="ondm-tool__label">Looptijd (maanden)</span>
				<input type="number" min="1" max="60" step="1" value="12" data-ondm="months">
			</label>
		</div>

		<div class="ondm-tool__results" aria-live="polite">
			<div class="ondm-tool__result">
				<span class="ondm-tool__result-label">Klanten per maand</span>
				<span class="ondm-tool__result-value" data-ondm-out="customers-month">13</span>
			</div>
			<div class="ondm-tool__result">
				<span class="ondm-tool__result-label">Totale klanten</span>
				<span class="ondm-tool__result-value" data-ondm-out="customers-total">160</span>
			</div>
			<div class="ondm-tool__result ondm-tool__result--highlight">
				<span class="ondm-tool__result-label">Totale omzet</span>
				<span class="ondm-tool__result-value" data-ondm-out="revenue-total">&euro;240.000</span>
			</div>
			<div class="ondm-tool__result">
				<span class="ondm-tool__result-label">ROI</span>
				<span class="ondm-tool__result-value" data-ondm-out="roi">900%</span>
			</div>
			<div class="ondm-tool__result">
				<span class="ondm-tool__result-label">Payback</span>
				<span class="ondm-tool__result-value" data-ondm-out="payback">1.2 mnd</span>
			</div>
		</div>

		<div class="ondm-tool__whatif" data-ondm-out="health-band">
			<p class="ondm-tool__whatif-label">Gezond profiel.</p>
			<p class="ondm-tool__whatif-value">Met deze LTV/CAC ratio &gt; 3 is dit ad-budget veilig op te schalen.</p>
		</div>

		<div class="ondm-tool__cta">
			<a class="wp-block-button__link wp-element-button ondm-tool__cta-btn" href="/contact/">Bespreek met een expert</a>
		</div>
	</div>
</section>
