<?php
/**
 * Title: Services ,  Free business tools (2-up)
 * Slug: booming-venture/tools-cards
 * Categories: booming-venture/landing, booming-venture/sections
 * Viewport Width: 1400
 */
?>
<!-- wp:group {"tagName":"section","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained"}} -->
<section class="wp-block-group" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">
	<!-- wp:heading {"textAlign":"center","level":2,"fontSize":"5xl"} -->
	<h2 class="wp-block-heading has-text-align-center has-5-xl-font-size">Free Business Tools</h2>
	<!-- /wp:heading -->
	<!-- wp:paragraph {"align":"center","fontSize":"lg","textColor":"muted"} -->
	<p class="has-text-align-center has-muted-color has-text-color has-lg-font-size">Try our powerful calculators to understand your growth potential.</p>
	<!-- /wp:paragraph -->

	<!-- wp:spacer {"height":"32px"} --><div style="height:32px" aria-hidden="true" class="wp-block-spacer"></div><!-- /wp:spacer -->

	<!-- wp:html -->
	<div class="bv-tools-cards" style="max-width:880px;margin:0 auto;">
		<article class="bv-tool-card">
			<h4><?php echo bv_icon( "calculator", 22 ); ?>&nbsp;&nbsp;Funnel Leak Calculator</h4>
			<p>Discover funnel leak sources causing leaks for funnel inefficiencies.</p>
			<a href="/funnel-calculator/" class="bv-tool-cta">Try Now ,  Free →</a>
		</article>
		<article class="bv-tool-card">
			<h4><?php echo bv_icon( "trending-up", 22 ); ?>&nbsp;&nbsp;ROI Forecaster</h4>
			<p>Predict your marketing ROI and optimize your budget allocation.</p>
			<a href="/roi-forecaster/" class="bv-tool-cta">Try Now ,  Free →</a>
		</article>
	</div>
	<!-- /wp:html -->
</section>
<!-- /wp:group -->
