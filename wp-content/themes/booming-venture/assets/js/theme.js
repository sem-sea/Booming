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

	/* Hero animated backdrop: third orb + canvas particle layer.
	 * Brand-coloured dots float slowly upward, fading in and out.
	 * Auto-disabled under prefers-reduced-motion. Vanilla, no deps. */
	(function heroAnim() {
		if (reduceMotion) return;
		var hero = document.querySelector('.bv-hero');
		if (!hero) return;

		/* Inject third orb (CSS pseudo-elements only allow 2). */
		var orb = document.createElement('div');
		orb.className = 'bv-hero-orb-3';
		orb.setAttribute('aria-hidden', 'true');
		hero.appendChild(orb);

		/* Canvas particle layer. */
		var canvas = document.createElement('canvas');
		canvas.className = 'bv-hero-particles';
		canvas.setAttribute('aria-hidden', 'true');
		hero.insertBefore(canvas, hero.firstChild);

		var ctx = canvas.getContext('2d');
		if (!ctx) return;
		var dpr = Math.min(window.devicePixelRatio || 1, 2);
		var particles = [];
		var w = 0, h = 0, count = 0;
		var palette = ['rgba(14,165,233,', 'rgba(20,184,166,', 'rgba(56,189,248,', 'rgba(45,212,191,'];

		function resize() {
			var rect = hero.getBoundingClientRect();
			w = rect.width; h = rect.height;
			canvas.width  = Math.floor(w * dpr);
			canvas.height = Math.floor(h * dpr);
			canvas.style.width  = w + 'px';
			canvas.style.height = h + 'px';
			ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
			count = Math.min(60, Math.floor((w * h) / 18000));
			particles = [];
			for (var i = 0; i < count; i++) particles.push(spawn(true));
		}
		function spawn(initial) {
			return {
				x: Math.random() * w,
				y: initial ? Math.random() * h : h + 20,
				r: 1 + Math.random() * 2.5,
				vy: 0.15 + Math.random() * 0.35,
				vx: (Math.random() - 0.5) * 0.15,
				life: 0,
				maxLife: 600 + Math.random() * 900,
				color: palette[Math.floor(Math.random() * palette.length)]
			};
		}

		var running = true;
		var observer = new IntersectionObserver(function (entries) {
			entries.forEach(function (e) { running = e.isIntersecting; });
		}, { threshold: 0 });
		observer.observe(hero);

		function tick() {
			if (!running) { requestAnimationFrame(tick); return; }
			ctx.clearRect(0, 0, w, h);
			for (var i = 0; i < particles.length; i++) {
				var p = particles[i];
				p.x += p.vx;
				p.y -= p.vy;
				p.life++;
				var alpha = Math.min(1, p.life / 100) * Math.min(1, (p.maxLife - p.life) / 100) * 0.55;
				ctx.beginPath();
				ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
				ctx.fillStyle = p.color + alpha.toFixed(3) + ')';
				ctx.fill();
				if (p.y < -10 || p.life > p.maxLife || p.x < -10 || p.x > w + 10) {
					particles[i] = spawn(false);
				}
			}
			requestAnimationFrame(tick);
		}

		resize();
		window.addEventListener('resize', resize, { passive: true });
		requestAnimationFrame(tick);
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
