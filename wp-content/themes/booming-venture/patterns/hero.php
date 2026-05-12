<?php
/**
 * Title: Hero — Smarter growth
 * Slug: booming-venture/hero
 * Categories: booming-venture/home, booming-venture/sections
 * Keywords: hero, home, headline
 * Block Types: core/post-content
 * Viewport Width: 1400
 */
?>
<!-- wp:group {"className":"bv-hero","gradient":"hero-backdrop","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group bv-hero has-hero-backdrop-gradient-background has-background" style="padding-top:var(--wp--preset--spacing--70);padding-bottom:var(--wp--preset--spacing--60)">

	<!-- wp:columns {"verticalAlignment":"center","align":"wide"} -->
	<div class="wp-block-columns alignwide are-vertically-aligned-center">

		<!-- wp:column {"verticalAlignment":"center","width":"55%"} -->
		<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:55%">
			<!-- wp:heading {"level":1,"fontSize":"7xl","style":{"typography":{"letterSpacing":"-0.025em","lineHeight":"1.05"}}} -->
			<h1 class="wp-block-heading has-7-xl-font-size" style="letter-spacing:-0.025em;line-height:1.05">Smarter growth.<br>Clear strategy.<br><span class="bv-gradient-text">Creative performance.</span></h1>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"fontSize":"xl","textColor":"contrast-2","style":{"typography":{"lineHeight":"1.6"}}} -->
			<p class="has-contrast-2-color has-text-color has-xl-font-size" style="line-height:1.6">We implement AI-powered marketing that delivers results. <strong>Performance. Personality. Powered by AI.</strong></p>
			<!-- /wp:paragraph -->

			<!-- wp:group {"className":"bv-hero-form","style":{"spacing":{"padding":{"top":"var:preset|spacing|30","right":"var:preset|spacing|40","bottom":"var:preset|spacing|30","left":"var:preset|spacing|40"}},"border":{"radius":"1rem","width":"1px","color":"var:preset|color|booming-100"},"shadow":"var:preset|shadow|lg"},"backgroundColor":"base","layout":{"type":"constrained"}} -->
			<div class="wp-block-group bv-hero-form has-border-color has-base-background-color has-background" style="border-color:var(--wp--preset--color--booming-100);border-width:1px;border-radius:1rem;padding-top:var(--wp--preset--spacing--30);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--30);padding-left:var(--wp--preset--spacing--40);box-shadow:var(--wp--preset--shadow--lg)">
				<!-- wp:heading {"level":3,"fontSize":"lg"} --><h3 class="wp-block-heading has-lg-font-size">Get Our Free Growth Strategy Guide</h3><!-- /wp:heading -->
				<!-- wp:paragraph {"fontSize":"sm","textColor":"muted"} --><p class="has-muted-color has-text-color has-sm-font-size">Learn the exact strategies our clients use to achieve 48%+ growth.</p><!-- /wp:paragraph -->
				<!-- wp:shortcode -->[contact-form-7 id="growth-guide"]<!-- /wp:shortcode -->
				<!-- wp:list {"className":"bv-check-list"} -->
				<ul class="wp-block-list bv-check-list">
					<!-- wp:list-item --><li>Actionable growth strategies</li><!-- /wp:list-item -->
					<!-- wp:list-item --><li>AI implementation guide</li><!-- /wp:list-item -->
					<!-- wp:list-item --><li>ROI calculation templates</li><!-- /wp:list-item -->
				</ul>
				<!-- /wp:list -->
			</div>
			<!-- /wp:group -->

			<!-- wp:buttons -->
			<div class="wp-block-buttons">
				<!-- wp:button {"className":"bv-cta-gradient"} -->
				<div class="wp-block-button bv-cta-gradient"><a class="wp-block-button__link wp-element-button" href="#contact">Start the Journey →</a></div>
				<!-- /wp:button -->
				<!-- wp:button {"className":"is-style-outline"} -->
				<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="mailto:info@boomingventure.com">Email Us Directly</a></div>
				<!-- /wp:button -->
			</div>
			<!-- /wp:buttons -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column {"verticalAlignment":"center","width":"45%"} -->
		<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:45%">
			<!-- wp:group {"className":"bv-hero-card","style":{"border":{"radius":"1rem"},"shadow":"var:preset|shadow|xl","spacing":{"padding":{"top":"var:preset|spacing|40","right":"var:preset|spacing|40","bottom":"var:preset|spacing|40","left":"var:preset|spacing|40"}}},"backgroundColor":"base","layout":{"type":"constrained"}} -->
				<div class="wp-block-group bv-hero-card has-base-background-color has-background" style="border-radius:1rem;padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--40);box-shadow:var(--wp--preset--shadow--xl)">
				<!-- wp:image {"sizeSlug":"large","style":{"border":{"radius":"0.5rem"}}} -->
				<figure class="wp-block-image size-large" style="border-radius:0.5rem"><img src="<?php echo esc_url( BV_THEME_URI ); ?>/assets/images/hero-team.jpg" alt="Booming Venture team collaborating in a modern office"/></figure>
				<!-- /wp:image -->
				<!-- wp:columns -->
				<div class="wp-block-columns">
					<!-- wp:column -->
					<div class="wp-block-column">
						<!-- wp:group {"backgroundColor":"booming-50","style":{"border":{"radius":"0.5rem"},"spacing":{"padding":{"top":"var:preset|spacing|30","right":"var:preset|spacing|30","bottom":"var:preset|spacing|30","left":"var:preset|spacing|30"}}}} -->
						<div class="wp-block-group has-booming-50-background-color has-background" style="border-radius:0.5rem;padding:var(--wp--preset--spacing--30)">
							<!-- wp:heading {"level":3,"fontSize":"2xl","textColor":"booming-700"} --><h3 class="wp-block-heading has-booming-700-color has-text-color has-2-xl-font-size">97%</h3><!-- /wp:heading -->
							<!-- wp:paragraph {"fontSize":"sm","textColor":"muted"} --><p class="has-muted-color has-text-color has-sm-font-size">Client satisfaction</p><!-- /wp:paragraph -->
						</div>
						<!-- /wp:group -->
					</div>
					<!-- /wp:column -->
					<!-- wp:column -->
					<div class="wp-block-column">
						<!-- wp:group {"backgroundColor":"venture-50","style":{"border":{"radius":"0.5rem"},"spacing":{"padding":{"top":"var:preset|spacing|30","right":"var:preset|spacing|30","bottom":"var:preset|spacing|30","left":"var:preset|spacing|30"}}}} -->
						<div class="wp-block-group has-venture-50-background-color has-background" style="border-radius:0.5rem;padding:var(--wp--preset--spacing--30)">
							<!-- wp:heading {"level":3,"fontSize":"2xl","textColor":"venture-700"} --><h3 class="wp-block-heading has-venture-700-color has-text-color has-2-xl-font-size">+48%</h3><!-- /wp:heading -->
							<!-- wp:paragraph {"fontSize":"sm","textColor":"muted"} --><p class="has-muted-color has-text-color has-sm-font-size">Average growth</p><!-- /wp:paragraph -->
						</div>
						<!-- /wp:group -->
					</div>
					<!-- /wp:column -->
				</div>
				<!-- /wp:columns -->
				<!-- wp:columns -->
				<div class="wp-block-columns">
					<!-- wp:column -->
					<div class="wp-block-column">
						<!-- wp:group {"backgroundColor":"venture-50","style":{"border":{"radius":"0.5rem"},"spacing":{"padding":{"top":"var:preset|spacing|30","right":"var:preset|spacing|30","bottom":"var:preset|spacing|30","left":"var:preset|spacing|30"}}}} -->
						<div class="wp-block-group has-venture-50-background-color has-background" style="border-radius:0.5rem;padding:var(--wp--preset--spacing--30)">
							<!-- wp:heading {"level":3,"fontSize":"2xl","textColor":"venture-700"} --><h3 class="wp-block-heading has-venture-700-color has-text-color has-2-xl-font-size">15+</h3><!-- /wp:heading -->
							<!-- wp:paragraph {"fontSize":"sm","textColor":"muted"} --><p class="has-muted-color has-text-color has-sm-font-size">Businesses helped</p><!-- /wp:paragraph -->
						</div>
						<!-- /wp:group -->
					</div>
					<!-- /wp:column -->
					<!-- wp:column -->
					<div class="wp-block-column">
						<!-- wp:group {"backgroundColor":"booming-50","style":{"border":{"radius":"0.5rem"},"spacing":{"padding":{"top":"var:preset|spacing|30","right":"var:preset|spacing|30","bottom":"var:preset|spacing|30","left":"var:preset|spacing|30"}}}} -->
						<div class="wp-block-group has-booming-50-background-color has-background" style="border-radius:0.5rem;padding:var(--wp--preset--spacing--30)">
							<!-- wp:heading {"level":3,"fontSize":"xl","textColor":"booming-700"} --><h3 class="wp-block-heading has-booming-700-color has-text-color has-xl-font-size">Support</h3><!-- /wp:heading -->
							<!-- wp:paragraph {"fontSize":"sm","textColor":"muted"} --><p class="has-muted-color has-text-color has-sm-font-size">When it is needed</p><!-- /wp:paragraph -->
						</div>
						<!-- /wp:group -->
					</div>
					<!-- /wp:column -->
				</div>
				<!-- /wp:columns -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:column -->

	</div>
	<!-- /wp:columns -->

</div>
<!-- /wp:group -->
