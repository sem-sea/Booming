/**
 * Booming Venture — theme.js
 * Tiny, vanilla, defer-loaded.
 */
(function () {
	'use strict';

	const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	/* Scroll-progress indicator. */
	(function scrollProgress() {
		const bar = document.createElement('div');
		bar.className = 'bv-scroll-progress';
		bar.setAttribute('aria-hidden', 'true');
		document.body.appendChild(bar);

		let ticking = false;
		function update() {
			const max = document.documentElement.scrollHeight - window.innerHeight;
			const pct = max > 0 ? (window.scrollY / max) * 100 : 0;
			bar.style.setProperty('--bv-scroll', pct.toFixed(2) + '%');
			ticking = false;
		}
		window.addEventListener('scroll', function () {
			if (!ticking) { requestAnimationFrame(update); ticking = true; }
		}, { passive: true });
		update();
	})();

	/* Smooth scroll for in-page anchor links (respects reduced-motion). */
	document.addEventListener('click', function (e) {
		const a = e.target.closest('a[href^="#"]');
		if (!a) return;
		const id = a.getAttribute('href');
		if (id.length < 2) return;
		const target = document.querySelector(id);
		if (!target) return;
		e.preventDefault();
		target.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
		target.setAttribute('tabindex', '-1');
		target.focus({ preventScroll: true });
	});

	/* Inject floating action buttons (Calculator / ROI / Contact) on home. */
	(function fabs() {
		if (!document.body.classList.contains('home') && !document.body.classList.contains('page-template-front-page')) return;
		const wrap = document.createElement('div');
		wrap.className = 'bv-fabs';
		wrap.innerHTML = [
			'<a class="bv-fab" href="/roi-forecaster/" aria-label="ROI Forecaster">📈</a>',
			'<a class="bv-fab" href="/funnel-calculator/" aria-label="Funnel Leak Calculator">🧮</a>',
			'<a class="bv-fab" href="#contact" aria-label="Contact">💬</a>'
		].join('');
		document.body.appendChild(wrap);
	})();

	/* External-link safety. */
	document.querySelectorAll('a[href^="http"]').forEach(function (a) {
		if (a.hostname && a.hostname !== window.location.hostname) {
			if (!a.target) a.target = '_blank';
			const rel = (a.rel || '').split(' ').filter(Boolean);
			if (!rel.includes('noopener')) rel.push('noopener');
			if (!rel.includes('noreferrer')) rel.push('noreferrer');
			a.rel = rel.join(' ');
		}
	});
})();
