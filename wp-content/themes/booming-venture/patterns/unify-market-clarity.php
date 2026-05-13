<?php
/**
 * Title: UNIFY, Market clarity map (3 personas)
 * Slug: booming-venture/unify-market-clarity
 * Categories: booming-venture/landing, booming-venture/sections
 * Viewport Width: 1400
 */
?>
<!-- wp:group {"tagName":"section","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"backgroundColor":"base-50","layout":{"type":"constrained"}} -->
<section class="wp-block-group has-base-50-background-color has-background" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">
	<!-- wp:heading {"textAlign":"center","level":2,"fontSize":"5xl"} -->
	<h2 class="wp-block-heading has-text-align-center has-5-xl-font-size"><?php echo bv_icon( "target", 28, "bv-section-icon" ); ?> Market Clarity Map</h2>
	<!-- /wp:heading -->
	<!-- wp:paragraph {"align":"center","fontSize":"lg","textColor":"muted"} -->
	<p class="has-text-align-center has-muted-color has-text-color has-lg-font-size">Understand your buyers&#8217; behavior, objections, and decision triggers to build systems that convert.</p>
	<!-- /wp:paragraph -->

	<!-- wp:spacer {"height":"40px"} --><div style="height:40px" aria-hidden="true" class="wp-block-spacer"></div><!-- /wp:spacer -->

	<!-- wp:html -->
	<div class="bv-personas alignwide">
		<details class="bv-persona" open>
			<summary><?php echo bv_icon( "rocket", 22 ); ?>&nbsp;&nbsp;Startup Founder</summary>
			<div class="bv-persona__body">
				<p><strong>Wants:</strong> Fast traction, repeatable acquisition.<br>
				<strong>Objections:</strong> Limited budget, no time to learn ten tools.<br>
				<strong>Triggers:</strong> Frameworks that show ROI inside 60 days.</p>
			</div>
		</details>
		<details class="bv-persona">
			<summary><?php echo bv_icon( "building-2", 22 ); ?>&nbsp;&nbsp;SMB Owner</summary>
			<div class="bv-persona__body">
				<p><strong>Wants:</strong> Predictable pipeline, less marketing chaos.<br>
				<strong>Objections:</strong> Been burned by agencies, doesn&#8217;t want another vendor.<br>
				<strong>Triggers:</strong> Transparent reporting, clear hand-off to in-house team.</p>
			</div>
		</details>
		<details class="bv-persona">
			<summary><?php echo bv_icon( "landmark", 22 ); ?>&nbsp;&nbsp;Enterprise Manager</summary>
			<div class="bv-persona__body">
				<p><strong>Wants:</strong> Org-wide alignment, AI adoption without risk.<br>
				<strong>Objections:</strong> Procurement red tape, security and compliance.<br>
				<strong>Triggers:</strong> Pilot scope, named experts, governance baked in.</p>
			</div>
		</details>
	</div>
	<!-- /wp:html -->
</section>
<!-- /wp:group -->
