<?php
/**
 * Title: Final CTA , Klaar om marketing uit te besteden
 * Slug: ondernemer-marketing/final-cta
 * Categories: ondm-cta, featured
 * Description: Grote afsluitende CTA met 3 bullets en knop.
 * Viewport Width: 1200
 */
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"4rem","bottom":"4rem","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"backgroundColor":"soft","layout":{"type":"constrained","contentSize":"1100px"}} -->
<div class="wp-block-group alignfull has-soft-background-color has-background" style="padding-top:4rem;padding-right:var(--wp--preset--spacing--40);padding-bottom:4rem;padding-left:var(--wp--preset--spacing--40)">
	<!-- wp:columns {"verticalAlignment":"center","style":{"spacing":{"blockGap":{"top":"2rem","left":"3rem"}}}} -->
	<div class="wp-block-columns are-vertically-aligned-center">

		<!-- wp:column {"verticalAlignment":"center","width":"60%"} -->
		<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:60%">
			<!-- wp:heading {"level":2,"style":{"typography":{"fontSize":"clamp(1.75rem, 3vw, 2.5rem)","fontWeight":"700"}}} -->
			<h2 class="wp-block-heading" style="font-size:clamp(1.75rem, 3vw, 2.5rem);font-weight:700">Klaar om marketing uit te besteden?</h2>
			<!-- /wp:heading -->
			<!-- wp:paragraph {"textColor":"body","style":{"typography":{"fontSize":"1.0625rem","lineHeight":"1.6"},"spacing":{"margin":{"top":"0.75rem","bottom":"1.25rem"}}}} -->
			<p class="has-body-color has-text-color" style="margin-top:0.75rem;margin-bottom:1.25rem;font-size:1.0625rem;line-height:1.6">We nemen het stuur over zodat jij kan ondernemen.</p>
			<!-- /wp:paragraph -->
			<!-- wp:list {"style":{"typography":{"fontSize":"1rem","lineHeight":"1.9"},"spacing":{"margin":{"top":"0","bottom":"1.5rem"}}}} -->
			<ul class="wp-block-list" style="margin-top:0;margin-bottom:1.5rem;font-size:1rem;line-height:1.9">
				<!-- wp:list-item --><li>Persoonlijke marketeer als aanspreekpunt</li><!-- /wp:list-item -->
				<!-- wp:list-item --><li>Maandelijkse strategie + uitvoering</li><!-- /wp:list-item -->
				<!-- wp:list-item --><li>Transparante rapportage in jouw dashboard</li><!-- /wp:list-item -->
			</ul>
			<!-- /wp:list -->
			<!-- wp:buttons -->
			<div class="wp-block-buttons">
				<!-- wp:button {"backgroundColor":"primary","style":{"border":{"radius":"0.5rem"},"spacing":{"padding":{"top":"0.875rem","bottom":"0.875rem","left":"1.75rem","right":"1.75rem"}},"typography":{"fontWeight":"600"}}} -->
				<div class="wp-block-button"><a class="wp-block-button__link has-primary-background-color has-background wp-element-button" href="/contact/" style="border-radius:0.5rem;padding-top:0.875rem;padding-right:1.75rem;padding-bottom:0.875rem;padding-left:1.75rem;font-weight:600">Plan gratis intake</a></div>
				<!-- /wp:button -->
			</div>
			<!-- /wp:buttons -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column {"verticalAlignment":"center","width":"40%"} -->
		<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:40%">
			<!-- wp:image {"sizeSlug":"large","style":{"border":{"radius":"1rem"}}} -->
			<figure class="wp-block-image size-large" style="border-radius:1rem;overflow:hidden;aspect-ratio:4/3;margin:0">
				<img src="<?php echo esc_url( ONDM_Install::img( 'cta-illustration' ) ); ?>" alt="Tevreden klant met laptop" loading="lazy" decoding="async" style="width:100%;height:100%;object-fit:cover;border-radius:1rem">
			</figure>
			<!-- /wp:image -->
		</div>
		<!-- /wp:column -->

	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
