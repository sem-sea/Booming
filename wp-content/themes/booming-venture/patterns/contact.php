<?php
/**
 * Title: Contact — info + form
 * Slug: booming-venture/contact
 * Categories: booming-venture/home, booming-venture/sections
 * Keywords: contact, form, address
 * Viewport Width: 1400
 */
?>
<!-- wp:group {"tagName":"section","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70"}}},"backgroundColor":"booming-50","layout":{"type":"constrained"}} -->
<section id="contact" class="wp-block-group has-booming-50-background-color has-background" style="padding-top:var(--wp--preset--spacing--70);padding-bottom:var(--wp--preset--spacing--70)">

	<!-- wp:group {"layout":{"type":"constrained","contentSize":"880px"}} -->
	<div class="wp-block-group">
		<!-- wp:heading {"textAlign":"center","level":2,"fontSize":"5xl"} --><h2 class="wp-block-heading has-text-align-center has-5-xl-font-size">Get In Touch</h2><!-- /wp:heading -->
		<!-- wp:paragraph {"align":"center","fontSize":"xl","textColor":"muted"} -->
		<p class="has-text-align-center has-muted-color has-text-color has-xl-font-size">Ready to accelerate your business growth? Contact us today for a free consultation.</p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->

	<!-- wp:spacer {"height":"32px"} --><div style="height:32px" aria-hidden="true" class="wp-block-spacer"></div><!-- /wp:spacer -->

	<!-- wp:columns {"align":"wide"} -->
	<div class="wp-block-columns alignwide">

		<!-- wp:column {"width":"40%"} -->
		<div class="wp-block-column" style="flex-basis:40%">
			<!-- wp:group {"style":{"border":{"radius":"1rem"},"spacing":{"padding":{"top":"var:preset|spacing|40","right":"var:preset|spacing|40","bottom":"var:preset|spacing|40","left":"var:preset|spacing|40"}},"shadow":"var:preset|shadow|sm"},"backgroundColor":"base"} -->
			<div class="wp-block-group has-base-background-color has-background" style="border-radius:1rem;padding:var(--wp--preset--spacing--40);box-shadow:var(--wp--preset--shadow--sm)">
				<!-- wp:heading {"level":3,"fontSize":"2xl"} --><h3 class="wp-block-heading has-2-xl-font-size">Contact Information</h3><!-- /wp:heading -->

				<!-- wp:heading {"level":4,"fontSize":"lg"} --><h4 class="wp-block-heading has-lg-font-size">Email Us</h4><!-- /wp:heading -->
				<!-- wp:paragraph {"textColor":"muted"} --><p class="has-muted-color has-text-color"><a href="mailto:info@boomingventure.com">info@boomingventure.com</a></p><!-- /wp:paragraph -->

				<!-- wp:heading {"level":4,"fontSize":"lg"} --><h4 class="wp-block-heading has-lg-font-size">Visit Us</h4><!-- /wp:heading -->
				<!-- wp:paragraph {"textColor":"muted"} -->
				<p class="has-muted-color has-text-color">Breedveldsingel 1<br>3055 PG Rotterdam<br>The Netherlands</p>
				<!-- /wp:paragraph -->

				<!-- wp:separator --><hr class="wp-block-separator has-alpha-channel-opacity"/><!-- /wp:separator -->

				<!-- wp:heading {"level":4,"fontSize":"lg"} --><h4 class="wp-block-heading has-lg-font-size">Connect With Us</h4><!-- /wp:heading -->
				<!-- wp:social-links {"iconColor":"base","iconColorValue":"#ffffff","iconBackgroundColor":"booming-600","iconBackgroundColorValue":"#0284c7"} -->
				<ul class="wp-block-social-links has-icon-color has-icon-background-color">
					<!-- wp:social-link {"url":"https://www.linkedin.com/company/booming-venture/","service":"linkedin"} /-->
				</ul>
				<!-- /wp:social-links -->

				<!-- wp:buttons -->
				<div class="wp-block-buttons">
					<!-- wp:button {"className":"is-style-outline","width":100} --><div class="wp-block-button is-style-outline has-custom-width wp-block-button__width-100"><a class="wp-block-button__link wp-element-button" href="mailto:info@boomingventure.com">Email Us Directly</a></div><!-- /wp:button -->
				</div>
				<!-- /wp:buttons -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:group {"style":{"border":{"radius":"1rem"},"spacing":{"padding":{"top":"var:preset|spacing|40","right":"var:preset|spacing|40","bottom":"var:preset|spacing|40","left":"var:preset|spacing|40"}},"shadow":"var:preset|shadow|sm"},"backgroundColor":"base"} -->
			<div class="wp-block-group has-base-background-color has-background" style="border-radius:1rem;padding:var(--wp--preset--spacing--40);box-shadow:var(--wp--preset--shadow--sm)">
				<!-- wp:heading {"level":3,"fontSize":"2xl"} --><h3 class="wp-block-heading has-2-xl-font-size">Send Us a Message</h3><!-- /wp:heading -->
				<!-- wp:shortcode -->[contact-form-7 id="contact"]<!-- /wp:shortcode -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:column -->

	</div>
	<!-- /wp:columns -->

</section>
<!-- /wp:group -->
