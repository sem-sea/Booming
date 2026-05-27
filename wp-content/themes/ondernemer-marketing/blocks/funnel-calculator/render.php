<?php
/**
 * Funnel Calculator , server-side render.
 *
 * @package OndernemerMarketing
 * @var array $attributes
 */
defined( 'ABSPATH' ) || exit;

$id = wp_unique_id( 'ondm-funnel-' );
?>
<section class="ondm-tool ondm-funnel" id="<?php echo esc_attr( $id ); ?>" <?php echo get_block_wrapper_attributes(); ?>>
	<div class="ondm-tool__inner">
		<h2 class="ondm-tool__title">Funnel Calculator</h2>
		<p class="ondm-tool__lead">Vul je cijfers in en zie direct hoeveel klanten + omzet jouw marketingfunnel kan opleveren.</p>

		<div class="ondm-tool__grid">
			<label class="ondm-tool__field">
				<span class="ondm-tool__label">Bezoekers per maand</span>
				<input type="number" min="0" step="100" value="1000" data-ondm="visitors">
			</label>
			<label class="ondm-tool__field">
				<span class="ondm-tool__label">Bezoeker , lead (%)</span>
				<input type="number" min="0" max="100" step="0.5" value="3" data-ondm="lead-rate">
			</label>
			<label class="ondm-tool__field">
				<span class="ondm-tool__label">Lead , klant (%)</span>
				<input type="number" min="0" max="100" step="0.5" value="15" data-ondm="customer-rate">
			</label>
			<label class="ondm-tool__field">
				<span class="ondm-tool__label">Gem. klantwaarde (&euro;)</span>
				<input type="number" min="0" step="50" value="500" data-ondm="customer-value">
			</label>
		</div>

		<div class="ondm-tool__results" aria-live="polite">
			<div class="ondm-tool__result">
				<span class="ondm-tool__result-label">Leads / maand</span>
				<span class="ondm-tool__result-value" data-ondm-out="leads">30</span>
			</div>
			<div class="ondm-tool__result">
				<span class="ondm-tool__result-label">Klanten / maand</span>
				<span class="ondm-tool__result-value" data-ondm-out="customers">4</span>
			</div>
			<div class="ondm-tool__result ondm-tool__result--highlight">
				<span class="ondm-tool__result-label">Omzet / maand</span>
				<span class="ondm-tool__result-value" data-ondm-out="revenue">&euro;2.250</span>
			</div>
			<div class="ondm-tool__result">
				<span class="ondm-tool__result-label">Omzet / jaar</span>
				<span class="ondm-tool__result-value" data-ondm-out="revenue-year">&euro;27.000</span>
			</div>
		</div>

		<div class="ondm-tool__whatif">
			<p class="ondm-tool__whatif-label">Wat als we de conversie verdubbelen?</p>
			<p class="ondm-tool__whatif-value" data-ondm-out="whatif">+ <strong>&euro;27.000</strong> extra omzet per jaar.</p>
		</div>

		<div class="ondm-tool__cta">
			<a class="wp-block-button__link wp-element-button ondm-tool__cta-btn" href="/contact/">Plan gratis intake</a>
		</div>
	</div>
</section>
