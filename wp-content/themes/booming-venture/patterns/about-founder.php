<?php
/**
 * Title: About , Meet the Founder (Ben Verschuur)
 * Slug: booming-venture/about-founder
 * Categories: booming-venture/sections, booming-venture/landing
 * Viewport Width: 1400
 */
$img = bv_image( 'ben-founder' );
?>
<!-- wp:group {"tagName":"section","className":"bv-founder","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained"}} -->
<section class="wp-block-group bv-founder" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">

	<!-- wp:heading {"textAlign":"center","level":2,"fontSize":"5xl"} -->
	<h2 class="wp-block-heading has-text-align-center has-5-xl-font-size">Meet the <span class="bv-gradient-text">Founder</span></h2>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"align":"center","fontSize":"lg","textColor":"muted"} -->
	<p class="has-text-align-center has-muted-color has-text-color has-lg-font-size">The person behind Booming Venture, the strategy, and the work.</p>
	<!-- /wp:paragraph -->

	<!-- wp:spacer {"height":"32px"} --><div style="height:32px" aria-hidden="true" class="wp-block-spacer"></div><!-- /wp:spacer -->

	<!-- wp:html -->
	<div class="bv-founder__card" itemscope itemtype="https://schema.org/Person">
		<div class="bv-founder__photo">
			<img src="<?php echo esc_url( $img ); ?>" alt="Ben Verschuur, founder of Booming Venture" loading="lazy" decoding="async" width="640" height="640" itemprop="image">
		</div>
		<div class="bv-founder__body">
			<p class="bv-founder__eyebrow">Founder &amp; Hands-on AI Strategist</p>
			<h3 class="bv-founder__name" itemprop="name">Ben Verschuur</h3>
			<p class="bv-founder__role"><span itemprop="jobTitle">Founder of Booming Venture</span> , based in <span itemprop="homeLocation" itemscope itemtype="https://schema.org/Place"><span itemprop="name">Rotterdam, the Netherlands</span></span>.</p>

			<p itemprop="description"><strong>Ben Verschuur is a hands-on AI marketing strategist and the founder of Booming Venture.</strong> He helps premium brands and B2B SaaS companies turn AI from a buzzword into a working operating system for growth. His approach is strategy-first, measurement-honest, and brand-protective. No AI gimmicks, no vanity metrics, no copy-pasted playbooks.</p>

			<p>Across 15+ years in digital growth, Ben has led performance and growth marketing for some of the most recognisable names in the Netherlands. He served as <strong>Director of Marketing at Zeelander Yachts</strong> (2023 to 2024), where he launched the Zeelander 8 and elevated the brand across global yachting markets. Before that, he was <strong>Head of Performance &amp; Growth Marketing at DPDK Digital Agency</strong> (2020 to 2022), leading data and performance teams for clients across B2B and B2C. From 2014 to 2020, he ran digital sales and growth marketing at <strong>Knab and Aegon Bank</strong>, leading lead generation, CRO, and predictive modelling for investment, banking, and pension products. Earlier, he managed digital marketing and web analytics across Europe for <strong>Hallmark Cards</strong> (NL, BE, DE).</p>

			<p>Today, Ben builds Booming Venture on three pillars: <em>Performance</em> (measurable outcomes, not vanity metrics), <em>Personality</em> (creative that converts and resonates), and <em>Powered by AI</em> (automation, augmentation, acceleration). He also founded <a href="https://verantwoordai.nl/" rel="noopener" target="_blank" itemprop="sameAs">VerantwoordAI</a>, which translates EU AI Act compliance and AI governance into practical team workflows, and <strong>PromptingPro</strong>, a programme that makes AI reliable and predictable for professionals without technical training. He holds an <span itemprop="alumniOf" itemscope itemtype="https://schema.org/EducationalOrganization"><span itemprop="name">MSc and BSc in Economics and Business from Erasmus University Rotterdam</span></span>.</p>

			<p class="bv-founder__honors"><strong>Recognition:</strong> #1 Digital Excellence Sales / Online Marketing, Platinum MarCom Awards for Product Launch and Digital Marketing, AVA Digital Awards Platinum Winner 2022 for Best Performance Team Achievement.</p>

			<ul class="bv-founder__chips" aria-label="Specialisms">
				<li>Enterprise AI</li>
				<li>AI Governance</li>
				<li>AI Agents</li>
				<li>Performance Marketing</li>
				<li>Growth Marketing</li>
				<li>Premium Brand Strategy</li>
				<li>CRO &amp; Funnel Design</li>
				<li>Marketing Automation</li>
				<li>SEO &amp; GEO</li>
			</ul>

			<div class="bv-founder__cta">
				<a class="bv-btn bv-btn--primary" href="/#contact" itemprop="contactPoint">Talk to Ben directly</a>
				<a class="bv-btn bv-btn--ghost" href="https://www.linkedin.com/in/benverschuur" rel="noopener" target="_blank" itemprop="sameAs">Connect on LinkedIn</a>
			</div>

			<meta itemprop="worksFor" content="Booming Venture">
			<meta itemprop="knowsAbout" content="AI Marketing, Performance Marketing, Growth Marketing, AI Governance, EU AI Act, CRO, SEO, Generative Engine Optimization">
			<meta itemprop="nationality" content="Dutch">
		</div>
	</div>
	<!-- /wp:html -->

</section>
<!-- /wp:group -->
