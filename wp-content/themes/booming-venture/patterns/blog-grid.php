<?php
/**
 * Title: Blog ,  3-column grid
 * Slug: booming-venture/blog-grid
 * Categories: booming-venture/sections
 * Viewport Width: 1400
 */
?>
<!-- wp:group {"tagName":"section","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained"}} -->
<section class="wp-block-group" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">
	<!-- wp:heading {"textAlign":"center","level":2,"fontSize":"5xl"} --><h2 class="wp-block-heading has-text-align-center has-5-xl-font-size">Latest insights</h2><!-- /wp:heading -->
	<!-- wp:paragraph {"align":"center","fontSize":"xl","textColor":"muted"} -->
	<p class="has-text-align-center has-muted-color has-text-color has-xl-font-size">Growth strategies, AI marketing playbooks, and conversion optimization from the trenches.</p>
	<!-- /wp:paragraph -->

	<!-- wp:query {"query":{"perPage":6,"postType":"post","order":"desc","orderBy":"date"},"align":"wide","layout":{"type":"constrained"}} -->
	<div class="wp-block-query alignwide">
		<!-- wp:post-template {"layout":{"type":"grid","columnCount":3}} -->
			<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"16/9","style":{"border":{"radius":"0.75rem"}}} /-->
			<!-- wp:post-terms {"term":"category","style":{"typography":{"textTransform":"uppercase","fontSize":"0.75rem","letterSpacing":"0.08em","fontWeight":"600"}},"textColor":"venture-700"} /-->
			<!-- wp:post-title {"isLink":true,"level":3,"fontSize":"lg"} /-->
			<!-- wp:post-excerpt {"moreOnNewLine":false,"excerptLength":20,"textColor":"muted"} /-->
		<!-- /wp:post-template -->
	</div>
	<!-- /wp:query -->

	<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
	<div class="wp-block-buttons">
		<!-- wp:button {"className":"is-style-outline"} --><div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="/blog/">Browse all articles →</a></div><!-- /wp:button -->
	</div>
	<!-- /wp:buttons -->
</section>
<!-- /wp:group -->
