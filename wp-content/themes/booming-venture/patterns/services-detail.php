<?php
/**
 * Title: Services — 4 services as alternating detail rows
 * Slug: booming-venture/services-detail
 * Categories: booming-venture/landing, booming-venture/sections
 * Viewport Width: 1400
 */
$theme = esc_url( BV_THEME_URI );
?>
<!-- wp:group {"tagName":"section","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained"}} -->
<section class="wp-block-group" style="padding-top:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--60)">

	<!-- wp:html -->
	<div class="alignwide">

		<article class="bv-service-row">
			<div>
				<div style="display:inline-flex;align-items:center;gap:.5rem;color:var(--wp--preset--color--booming-700);font-weight:600;font-size:.875rem;text-transform:uppercase;letter-spacing:.08em;">📊 Strategic Consulting</div>
				<h3>Strategic Consulting</h3>
				<p class="bv-price">From €2,500/month</p>
				<p style="color:var(--wp--preset--color--muted);margin:0 0 1rem;">Comprehensive business growth strategies tailored to your unique market position and goals.</p>
				<ul class="bv-check-list">
					<li>Market analysis and competitive research</li>
					<li>Business model optimization</li>
					<li>Growth strategy development</li>
					<li>Revenue stream diversification</li>
					<li>Performance benchmarking</li>
					<li>Strategic roadmap creation</li>
				</ul>
				<a href="/#contact" class="wp-block-button__link wp-element-button" style="display:inline-block;margin-top:1.25rem;background:var(--wp--preset--gradient--brand);color:#fff;padding:.75rem 1.5rem;border-radius:.625rem;text-decoration:none;font-weight:600;">Get Started →</a>
			</div>
			<div>
				<img src="<?php echo $theme; ?>/assets/images/service-1.jpg" alt="Two consultants reviewing a strategy document" loading="lazy" decoding="async" width="800" height="600">
			</div>
		</article>

		<article class="bv-service-row bv-service-row--flip">
			<div>
				<div style="display:inline-flex;align-items:center;gap:.5rem;color:var(--wp--preset--color--booming-700);font-weight:600;font-size:.875rem;text-transform:uppercase;letter-spacing:.08em;">🚀 Performance Marketing</div>
				<h3>Performance Marketing</h3>
				<p class="bv-price">From €3,000/month</p>
				<p style="color:var(--wp--preset--color--muted);margin:0 0 1rem;">Data-driven marketing campaigns that deliver measurable results and maximize your ROI.</p>
				<ul class="bv-check-list">
					<li>Multi-channel campaign management</li>
					<li>Conversion rate optimization</li>
					<li>Customer acquisition strategies</li>
					<li>Retargeting and remarketing</li>
					<li>A/B testing and optimization</li>
					<li>Performance analytics and reporting</li>
				</ul>
				<a href="/#contact" class="wp-block-button__link wp-element-button" style="display:inline-block;margin-top:1.25rem;background:var(--wp--preset--gradient--brand);color:#fff;padding:.75rem 1.5rem;border-radius:.625rem;text-decoration:none;font-weight:600;">Get Started →</a>
			</div>
			<div>
				<img src="<?php echo $theme; ?>/assets/images/service-2.jpg" alt="Marketing team presenting campaign results on a screen" loading="lazy" decoding="async" width="800" height="600">
			</div>
		</article>

		<article class="bv-service-row">
			<div>
				<div style="display:inline-flex;align-items:center;gap:.5rem;color:var(--wp--preset--color--booming-700);font-weight:600;font-size:.875rem;text-transform:uppercase;letter-spacing:.08em;">🧠 AI-Powered Solutions</div>
				<h3>AI-Powered Solutions</h3>
				<p class="bv-price">From €4,000/month</p>
				<p style="color:var(--wp--preset--color--muted);margin:0 0 1rem;">Leverage cutting-edge artificial intelligence to optimize your business operations and decision-making.</p>
				<ul class="bv-check-list">
					<li>Predictive analytics implementation</li>
					<li>Automated workflow optimization</li>
					<li>Customer behavior analysis</li>
					<li>AI-driven personalization</li>
					<li>Machine learning model development</li>
					<li>Data-driven decision support</li>
				</ul>
				<a href="/#contact" class="wp-block-button__link wp-element-button" style="display:inline-block;margin-top:1.25rem;background:var(--wp--preset--gradient--brand);color:#fff;padding:.75rem 1.5rem;border-radius:.625rem;text-decoration:none;font-weight:600;">Get Started →</a>
			</div>
			<div>
				<img src="<?php echo $theme; ?>/assets/images/service-3.jpg" alt="Diverse team discussing AI dashboard insights" loading="lazy" decoding="async" width="800" height="600">
			</div>
		</article>

		<article class="bv-service-row bv-service-row--flip">
			<div>
				<div style="display:inline-flex;align-items:center;gap:.5rem;color:var(--wp--preset--color--booming-700);font-weight:600;font-size:.875rem;text-transform:uppercase;letter-spacing:.08em;">📈 Growth Optimization</div>
				<h3>Growth Optimization</h3>
				<p class="bv-price">From €3,500/month</p>
				<p style="color:var(--wp--preset--color--muted);margin:0 0 1rem;">Comprehensive programs to scale your business efficiently and sustainably across all channels.</p>
				<ul class="bv-check-list">
					<li>Scalable growth framework development</li>
					<li>Process automation and optimization</li>
					<li>Cross-functional team alignment</li>
					<li>Customer lifetime value optimization</li>
					<li>Retention strategy development</li>
					<li>Long-term growth planning</li>
				</ul>
				<a href="/#contact" class="wp-block-button__link wp-element-button" style="display:inline-block;margin-top:1.25rem;background:var(--wp--preset--gradient--brand);color:#fff;padding:.75rem 1.5rem;border-radius:.625rem;text-decoration:none;font-weight:600;">Get Started →</a>
			</div>
			<div>
				<img src="<?php echo $theme; ?>/assets/images/service-4.jpg" alt="Team celebrating campaign success around a laptop" loading="lazy" decoding="async" width="800" height="600">
			</div>
		</article>

	</div>
	<!-- /wp:html -->

</section>
<!-- /wp:group -->
