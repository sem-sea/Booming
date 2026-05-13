<?php
/**
 * Title: UNIFY ,  Hero with 5-node diagram
 * Slug: booming-venture/unify-hero
 * Categories: booming-venture/landing, booming-venture/sections
 * Viewport Width: 1400
 */
?>
<!-- wp:group {"tagName":"section","gradient":"hero-backdrop","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained"}} -->
<section class="wp-block-group has-hero-backdrop-gradient-background has-background" style="padding-top:var(--wp--preset--spacing--70);padding-bottom:var(--wp--preset--spacing--60)">

	<!-- wp:paragraph {"align":"center","style":{"typography":{"textTransform":"uppercase","letterSpacing":"0.12em","fontWeight":"600"}},"fontSize":"sm","textColor":"venture-700"} -->
	<p class="has-text-align-center has-venture-700-color has-text-color has-sm-font-size" style="font-weight:600;text-transform:uppercase;letter-spacing:0.12em">Introducing the UNIFY Framework™</p>
	<!-- /wp:paragraph -->

	<!-- wp:columns {"align":"wide","verticalAlignment":"center","className":"bv-unify-hero"} -->
	<div class="wp-block-columns alignwide are-vertically-aligned-center bv-unify-hero">

		<!-- wp:column {"verticalAlignment":"center"} -->
		<div class="wp-block-column is-vertically-aligned-center">
			<!-- wp:heading {"level":1,"style":{"typography":{"fontSize":"clamp(2.25rem, 1.8rem + 2.5vw, 4rem)","lineHeight":"1.05","letterSpacing":"-0.02em"}}} -->
			<h1 class="wp-block-heading" style="font-size:clamp(2.25rem, 1.8rem + 2.5vw, 4rem);line-height:1.05;letter-spacing:-0.02em">One System.<br>Five Phases.<br><span class="bv-gradient-text">Zero Guesswork.</span></h1>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"fontSize":"xl","textColor":"contrast-2"} -->
			<p class="has-contrast-2-color has-text-color has-xl-font-size">The UNIFY Framework™ helps you implement AI into your marketing with systems, not more tools.</p>
			<!-- /wp:paragraph -->

			<!-- wp:group {"style":{"border":{"radius":"1rem","width":"1px","color":"var:preset|color|booming-100"},"spacing":{"padding":{"top":"var:preset|spacing|30","right":"var:preset|spacing|40","bottom":"var:preset|spacing|30","left":"var:preset|spacing|40"}},"shadow":"var:preset|shadow|lg"},"backgroundColor":"base","layout":{"type":"constrained","contentSize":"480px"}} -->
			<div class="wp-block-group has-base-background-color has-background" style="border-color:var(--wp--preset--color--booming-100);border-width:1px;border-radius:1rem;padding-top:var(--wp--preset--spacing--30);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--30);padding-left:var(--wp--preset--spacing--40);box-shadow:var(--wp--preset--shadow--lg)">
				<!-- wp:heading {"level":3,"fontSize":"lg"} --><h3 class="wp-block-heading has-lg-font-size">Get the free UNIFY Framework™ Guide</h3><!-- /wp:heading -->
				<!-- wp:paragraph {"fontSize":"sm","textColor":"muted"} --><p class="has-muted-color has-text-color has-sm-font-size">Complete framework with implementation templates.</p><!-- /wp:paragraph -->
				<!-- wp:shortcode -->[contact-form-7 id="growth-guide"]<!-- /wp:shortcode -->
				<!-- wp:paragraph {"fontSize":"xs","textColor":"muted"} --><p class="has-muted-color has-text-color has-xs-font-size">✓ No spam, unsubscribe anytime</p><!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column {"verticalAlignment":"center"} -->
		<div class="wp-block-column is-vertically-aligned-center">
			<!-- wp:html -->
			<div class="bv-unify-diagram" role="img" aria-label="UNIFY framework: Understand, Nurture, Integrate, Forecast, Yield around one central system">
				<svg viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
					<defs>
						<linearGradient id="bvg-line" x1="0" x2="1" y1="0" y2="1">
							<stop offset="0%" stop-color="#0284c7" stop-opacity="0.5"/>
							<stop offset="100%" stop-color="#14b8a6" stop-opacity="0.5"/>
						</linearGradient>
					</defs>
					<g stroke="url(#bvg-line)" stroke-width="1.5" fill="none">
						<line x1="200" y1="200" x2="200" y2="60"/>
						<line x1="200" y1="200" x2="340" y2="140"/>
						<line x1="200" y1="200" x2="340" y2="280"/>
						<line x1="200" y1="200" x2="60"  y2="280"/>
						<line x1="200" y1="200" x2="60"  y2="140"/>
					</g>
				</svg>
				<div class="bv-unify-node" style="top:15%;left:50%;"><span class="bv-unify-node-icon" aria-hidden="true"><?php echo bv_icon( "search", 18 ); ?></span>Understand</div>
				<div class="bv-unify-node" style="top:35%;left:85%;"><span class="bv-unify-node-icon" aria-hidden="true"><?php echo bv_icon( "message-circle", 18 ); ?></span>Nurture</div>
				<div class="bv-unify-node" style="top:70%;left:85%;"><span class="bv-unify-node-icon" aria-hidden="true"><?php echo bv_icon( "settings-2", 18 ); ?></span>Integrate</div>
				<div class="bv-unify-node" style="top:70%;left:15%;"><span class="bv-unify-node-icon" aria-hidden="true"><?php echo bv_icon( "bar-chart-3", 18 ); ?></span>Forecast</div>
				<div class="bv-unify-node" style="top:35%;left:15%;"><span class="bv-unify-node-icon" aria-hidden="true"><?php echo bv_icon( "rotate-cw", 18 ); ?></span>Yield</div>
				<div class="bv-unify-node bv-unify-node--center" style="top:50%;left:50%;">UNIFY</div>
			</div>
			<!-- /wp:html -->
		</div>
		<!-- /wp:column -->

	</div>
	<!-- /wp:columns -->

</section>
<!-- /wp:group -->
