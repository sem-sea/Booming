<?php
/**
 * Title: Growth Guide — lead magnet band (with 2x2 features grid)
 * Slug: booming-venture/growth-guide-cta
 * Categories: booming-venture/home, booming-venture/cta
 * Keywords: lead magnet, ebook, download
 * Viewport Width: 1400
 */
$theme = esc_url( BV_THEME_URI );
$img   = bv_image( 'growth-guide' );
?>
<!-- wp:group {"tagName":"section","gradient":"brand","textColor":"base","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained"}} -->
<section class="wp-block-group has-base-color has-brand-gradient-background has-text-color has-background" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">

	<!-- wp:columns {"align":"wide","verticalAlignment":"center"} -->
	<div class="wp-block-columns alignwide are-vertically-aligned-center">

		<!-- wp:column {"verticalAlignment":"center","width":"42%"} -->
		<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:42%">
			<!-- wp:image {"sizeSlug":"large","style":{"border":{"radius":"1rem"}}} -->
			<figure class="wp-block-image size-large" style="border-radius:1rem"><img src="<?php echo $img; ?>" alt="Booming Venture Growth Strategy Guide booklets on a desk with notes" loading="lazy" decoding="async" width="800" height="600"/></figure>
			<!-- /wp:image -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column {"verticalAlignment":"center"} -->
		<div class="wp-block-column is-vertically-aligned-center">

			<!-- wp:heading {"level":2,"fontSize":"4xl","textColor":"base"} -->
			<h2 class="wp-block-heading has-base-color has-text-color has-4-xl-font-size">Get Your Free Growth Strategy Guide</h2>
			<!-- /wp:heading -->
			<!-- wp:paragraph {"fontSize":"lg"} -->
			<p class="has-lg-font-size">Discover the exact framework our clients use to achieve consistent 48%+ growth. This comprehensive guide includes actionable strategies, templates, and real case studies.</p>
			<!-- /wp:paragraph -->

			<!-- wp:html -->
			<div class="bv-gg-features">
				<div class="bv-gg-feature">
					<span class="bv-gg-feature__icon" aria-hidden="true"><?php echo bv_icon( 'check', 16 ); ?></span>
					<div>
						<p class="bv-gg-feature__title">Growth Frameworks</p>
						<p class="bv-gg-feature__copy">Proven methodologies for sustainable growth</p>
					</div>
				</div>
				<div class="bv-gg-feature">
					<span class="bv-gg-feature__icon" aria-hidden="true"><?php echo bv_icon( 'check', 16 ); ?></span>
					<div>
						<p class="bv-gg-feature__title">ROI Templates</p>
						<p class="bv-gg-feature__copy">Calculate and track your marketing ROI</p>
					</div>
				</div>
				<div class="bv-gg-feature">
					<span class="bv-gg-feature__icon" aria-hidden="true"><?php echo bv_icon( 'check', 16 ); ?></span>
					<div>
						<p class="bv-gg-feature__title">AI Implementation</p>
						<p class="bv-gg-feature__copy">Step-by-step AI integration guide</p>
					</div>
				</div>
				<div class="bv-gg-feature">
					<span class="bv-gg-feature__icon" aria-hidden="true"><?php echo bv_icon( 'check', 16 ); ?></span>
					<div>
						<p class="bv-gg-feature__title">Case Studies</p>
						<p class="bv-gg-feature__copy">Real examples from successful clients</p>
					</div>
				</div>
			</div>
			<!-- /wp:html -->

			<!-- wp:group {"style":{"border":{"radius":"1rem"},"spacing":{"padding":{"top":"var:preset|spacing|30","right":"var:preset|spacing|40","bottom":"var:preset|spacing|30","left":"var:preset|spacing|40"}}},"backgroundColor":"base"} -->
			<div class="wp-block-group has-base-background-color has-background" style="border-radius:1rem;padding:var(--wp--preset--spacing--30) var(--wp--preset--spacing--40)">
				<!-- wp:shortcode -->[contact-form-7 id="growth-guide"]<!-- /wp:shortcode -->
				<!-- wp:paragraph {"fontSize":"xs","textColor":"muted"} -->
				<p class="has-muted-color has-text-color has-xs-font-size">No spam, unsubscribe anytime.</p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->

		</div>
		<!-- /wp:column -->

	</div>
	<!-- /wp:columns -->

</section>
<!-- /wp:group -->
