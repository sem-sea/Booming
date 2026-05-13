<?php
/**
 * Title: About ,  Why choose us (checklist + photo collage)
 * Slug: booming-venture/why-choose-checklist
 * Categories: booming-venture/sections, booming-venture/landing
 * Viewport Width: 1400
 */
?>
<!-- wp:group {"tagName":"section","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained"}} -->
<section class="wp-block-group" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">
	<!-- wp:columns {"align":"wide","verticalAlignment":"center"} -->
	<div class="wp-block-columns alignwide are-vertically-aligned-center">
		<!-- wp:column {"verticalAlignment":"center"} -->
		<div class="wp-block-column is-vertically-aligned-center">
			<!-- wp:heading {"level":2,"fontSize":"4xl"} -->
			<h2 class="wp-block-heading has-4-xl-font-size">Why Choose Booming Venture?</h2>
			<!-- /wp:heading -->
			<!-- wp:html -->
			<ul class="bv-checklist">
				<li>Proven track record with 200+ successful projects</li>
				<li>AI-powered strategies that deliver 3× better results</li>
				<li>Rotterdam-based team with global expertise</li>
				<li>Transparent communication and regular reporting</li>
				<li>Custom solutions tailored to your industry</li>
				<li>Ongoing support and optimization</li>
			</ul>
			<!-- /wp:html -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column {"verticalAlignment":"center"} -->
		<div class="wp-block-column is-vertically-aligned-center">
			<!-- wp:html -->
			<div class="bv-photo-grid">
				<img src="<?php echo bv_image( 'service-4' ); ?>" alt="Diverse team collaborating around laptops" loading="lazy" decoding="async" width="600" height="450">
				<img src="<?php echo bv_image( 'service-1' ); ?>" alt="Consultants reviewing strategy on a tablet" loading="lazy" decoding="async" width="600" height="450">
				<img src="<?php echo bv_image( 'service-3' ); ?>" alt="Two professionals reviewing AI dashboard insights" loading="lazy" decoding="async" width="600" height="450">
				<img src="<?php echo bv_image( 'hero-extra' ); ?>" alt="Working session inside a modern office" loading="lazy" decoding="async" width="600" height="450">
			</div>
			<!-- /wp:html -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</section>
<!-- /wp:group -->
