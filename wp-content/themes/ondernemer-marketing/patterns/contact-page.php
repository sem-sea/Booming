<?php
/**
 * Title: Contact , volledige pagina
 * Slug: ondernemer-marketing/contact-page
 * Categories: ondm-contact, featured
 * Description: Contact pagina , intro, 2-koloms form + contactgegevens, FAQ.
 * Viewport Width: 1200
 */
$msg = isset( $_GET['ondm_msg'] ) ? sanitize_key( wp_unslash( (string) $_GET['ondm_msg'] ) ) : '';
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"4rem","bottom":"2rem","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"800px"}} -->
<div class="wp-block-group alignfull" style="padding-top:4rem;padding-right:var(--wp--preset--spacing--40);padding-bottom:2rem;padding-left:var(--wp--preset--spacing--40)">
	<!-- wp:heading {"textAlign":"center","level":1,"style":{"typography":{"fontSize":"clamp(2rem, 4vw, 3rem)","fontWeight":"700"}}} -->
	<h1 class="wp-block-heading has-text-align-center" style="font-size:clamp(2rem, 4vw, 3rem);font-weight:700">Contact</h1>
	<!-- /wp:heading -->
	<!-- wp:paragraph {"align":"center","textColor":"primary","style":{"typography":{"fontSize":"1.125rem","fontWeight":"600"},"spacing":{"margin":{"top":"0.5rem"}}}} -->
	<p class="has-text-align-center has-primary-color has-text-color" style="margin-top:0.5rem;font-size:1.125rem;font-weight:600">Neem contact met ons op.</p>
	<!-- /wp:paragraph -->
	<!-- wp:paragraph {"align":"center","textColor":"muted","style":{"typography":{"fontSize":"1rem","lineHeight":"1.6"},"spacing":{"margin":{"top":"0.75rem"}}}} -->
	<p class="has-text-align-center has-muted-color has-text-color" style="margin-top:0.75rem;font-size:1rem;line-height:1.6">Wil je weten wat wij voor jou kunnen betekenen? Vul het formulier in of neem direct contact met ons op.</p>
	<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"2rem","bottom":"4rem","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"1100px"}} -->
<div class="wp-block-group alignfull" style="padding-top:2rem;padding-right:var(--wp--preset--spacing--40);padding-bottom:4rem;padding-left:var(--wp--preset--spacing--40)">

	<!-- wp:columns {"style":{"spacing":{"blockGap":{"top":"2rem","left":"2rem"}}}} -->
	<div class="wp-block-columns">

		<!-- wp:column {"width":"58%"} -->
		<div class="wp-block-column" style="flex-basis:58%">
			<!-- wp:heading {"level":2,"textColor":"primary","style":{"typography":{"fontSize":"1.125rem","fontWeight":"700"}}} -->
			<h2 class="wp-block-heading has-primary-color has-text-color" style="font-size:1.125rem;font-weight:700">Stuur ons een bericht</h2>
			<!-- /wp:heading -->

			<!-- wp:html -->
			<?php
			if ( 'ok' === $msg ) {
				echo '<div role="status" aria-live="polite" style="background:#f0f9ff;border-left:4px solid #16a34a;padding:1rem 1.25rem;border-radius:0.5rem;margin-bottom:1rem"><strong>Bedankt!</strong> Je bericht is verzonden. We nemen binnen 2 werkdagen contact met je op.</div>';
			} elseif ( 'err' === $msg ) {
				echo '<div role="alert" aria-live="assertive" style="background:#fef2f2;border-left:4px solid #b91c1c;padding:1rem 1.25rem;border-radius:0.5rem;margin-bottom:1rem;color:#7f1d1d"><strong>Er ging iets mis.</strong> Controleer je invoer en probeer opnieuw, of stuur ons direct een e-mail.</div>';
			}
			?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="background:#fff;border:1px solid #e2e8f0;border-radius:0.75rem;padding:2rem;display:flex;flex-direction:column;gap:1rem;">
				<?php wp_nonce_field( 'ondm_contact', 'ondm_nonce' ); ?>
				<input type="hidden" name="action" value="ondm_contact">
				<input type="text" name="ondm_hp" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">
				<label style="display:flex;flex-direction:column;gap:0.35rem;font-size:0.875rem;font-weight:600;color:#334155;">Voornaam <span aria-hidden="true" style="color:#b91c1c">*</span>
					<input required type="text" name="ondm_first" autocomplete="given-name" style="padding:0.65rem 0.9rem;border:1px solid #cbd5e1;border-radius:0.5rem;font-size:1rem;font-family:inherit;">
				</label>
				<label style="display:flex;flex-direction:column;gap:0.35rem;font-size:0.875rem;font-weight:600;color:#334155;">Achternaam <span aria-hidden="true" style="color:#b91c1c">*</span>
					<input required type="text" name="ondm_last" autocomplete="family-name" style="padding:0.65rem 0.9rem;border:1px solid #cbd5e1;border-radius:0.5rem;font-size:1rem;font-family:inherit;">
				</label>
				<label style="display:flex;flex-direction:column;gap:0.35rem;font-size:0.875rem;font-weight:600;color:#334155;">E-mailadres <span aria-hidden="true" style="color:#b91c1c">*</span>
					<input required type="email" name="ondm_email" autocomplete="email" style="padding:0.65rem 0.9rem;border:1px solid #cbd5e1;border-radius:0.5rem;font-size:1rem;font-family:inherit;">
				</label>
				<label style="display:flex;flex-direction:column;gap:0.35rem;font-size:0.875rem;font-weight:600;color:#334155;">Bericht <span aria-hidden="true" style="color:#b91c1c">*</span>
					<textarea required name="ondm_message" rows="5" style="padding:0.65rem 0.9rem;border:1px solid #cbd5e1;border-radius:0.5rem;font-size:1rem;font-family:inherit;resize:vertical;"></textarea>
				</label>
				<button type="submit" style="background:#f17a3c;color:#fff;border:0;padding:0.85rem 1.5rem;border-radius:0.5rem;font-size:1rem;font-weight:600;cursor:pointer;align-self:flex-start;">Verzenden</button>
			</form>
			<!-- /wp:html -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column {"width":"42%"} -->
		<div class="wp-block-column" style="flex-basis:42%">
			<!-- wp:heading {"level":2,"textColor":"primary","style":{"typography":{"fontSize":"1.125rem","fontWeight":"700"}}} -->
			<h2 class="wp-block-heading has-primary-color has-text-color" style="font-size:1.125rem;font-weight:700">Contactgegevens</h2>
			<!-- /wp:heading -->
			<!-- wp:paragraph {"style":{"typography":{"fontSize":"1rem","lineHeight":"1.8"},"spacing":{"margin":{"top":"1rem"}}}} -->
			<p style="margin-top:1rem;font-size:1rem;line-height:1.8"><strong style="color:#f17a3c">E-mail</strong><br><a href="mailto:info@ondernemermarketing.nl">info@ondernemermarketing.nl</a><br><br><strong style="color:#f17a3c">Telefoon</strong><br><a href="tel:+31613013266">+31 6 1301 3266</a><br><br><strong style="color:#f17a3c">Adres</strong><br>Breedveldsingel 1<br>3055PG Rotterdam<br>Nederland</p>
			<!-- /wp:paragraph -->
			<!-- wp:image {"sizeSlug":"large","style":{"border":{"radius":"0.75rem"}}} -->
			<figure class="wp-block-image size-large" style="border-radius:0.75rem;overflow:hidden;aspect-ratio:4/3;margin:1.5rem 0 0">
				<img src="<?php echo esc_url( ONDM_Install::img( 'klaar-start-pack' ) ); ?>" alt="Klaar voor je intake" loading="lazy" decoding="async" style="width:100%;height:100%;object-fit:cover;border-radius:0.75rem">
			</figure>
			<!-- /wp:image -->
		</div>
		<!-- /wp:column -->

	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"3rem","bottom":"4rem","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"backgroundColor":"soft","layout":{"type":"constrained","contentSize":"800px"}} -->
<div class="wp-block-group alignfull has-soft-background-color has-background" style="padding-top:3rem;padding-right:var(--wp--preset--spacing--40);padding-bottom:4rem;padding-left:var(--wp--preset--spacing--40)">
	<!-- wp:heading {"textAlign":"center","level":2,"style":{"typography":{"fontSize":"clamp(1.5rem, 2.5vw, 2rem)","fontWeight":"700"},"spacing":{"margin":{"bottom":"2rem"}}}} -->
	<h2 class="wp-block-heading has-text-align-center" style="margin-bottom:2rem;font-size:clamp(1.5rem, 2.5vw, 2rem);font-weight:700">Veelgestelde vragen</h2>
	<!-- /wp:heading -->

	<!-- wp:details {"style":{"border":{"radius":"0.5rem","color":"#e2e8f0","width":"1px"},"spacing":{"padding":"1.25rem","margin":{"bottom":"0.75rem"}}},"backgroundColor":"white"} -->
	<details class="wp-block-details has-white-background-color has-background" style="border-color:#e2e8f0;border-width:1px;border-radius:0.5rem;padding:1.25rem;margin-bottom:0.75rem"><summary style="color:#1d6bd1;font-weight:600;cursor:pointer">Hoe snel kunnen jullie starten?</summary>
		<!-- wp:paragraph {"textColor":"body","style":{"typography":{"lineHeight":"1.6"},"spacing":{"margin":{"top":"0.75rem"}}}} -->
		<p class="has-body-color has-text-color" style="margin-top:0.75rem;line-height:1.6">Meestal kunnen we binnen 2 dagen na het intakegesprek starten met de implementatie van je marketingstrategie.</p>
		<!-- /wp:paragraph -->
	</details>
	<!-- /wp:details -->

	<!-- wp:details {"style":{"border":{"radius":"0.5rem","color":"#e2e8f0","width":"1px"},"spacing":{"padding":"1.25rem","margin":{"bottom":"0.75rem"}}},"backgroundColor":"white"} -->
	<details class="wp-block-details has-white-background-color has-background" style="border-color:#e2e8f0;border-width:1px;border-radius:0.5rem;padding:1.25rem;margin-bottom:0.75rem"><summary style="color:#1d6bd1;font-weight:600;cursor:pointer">Werken jullie met contracten?</summary>
		<!-- wp:paragraph {"textColor":"body","style":{"typography":{"lineHeight":"1.6"},"spacing":{"margin":{"top":"0.75rem"}}}} -->
		<p class="has-body-color has-text-color" style="margin-top:0.75rem;line-height:1.6">Voor de Social Media Funnel Pack werken we met een eenmalig project. Voor andere diensten werken we met een minimale periode van 3 maanden, daarna maandelijks opzegbaar.</p>
		<!-- /wp:paragraph -->
	</details>
	<!-- /wp:details -->

	<!-- wp:details {"style":{"border":{"radius":"0.5rem","color":"#e2e8f0","width":"1px"},"spacing":{"padding":"1.25rem","margin":{"bottom":"0.75rem"}}},"backgroundColor":"white"} -->
	<details class="wp-block-details has-white-background-color has-background" style="border-color:#e2e8f0;border-width:1px;border-radius:0.5rem;padding:1.25rem;margin-bottom:0.75rem"><summary style="color:#1d6bd1;font-weight:600;cursor:pointer">Wat als ik niet tevreden ben?</summary>
		<!-- wp:paragraph {"textColor":"body","style":{"typography":{"lineHeight":"1.6"},"spacing":{"margin":{"top":"0.75rem"}}}} -->
		<p class="has-body-color has-text-color" style="margin-top:0.75rem;line-height:1.6">We werken met duidelijke resultaatafspraken. Als we deze niet halen, kijken we samen naar een passende oplossing of compensatie.</p>
		<!-- /wp:paragraph -->
	</details>
	<!-- /wp:details -->
</div>
<!-- /wp:group -->
