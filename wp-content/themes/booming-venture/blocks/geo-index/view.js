/**
 * B2B GEO Index: tabs, filters, sorting and the domain check.
 * The ranking itself is server-rendered; this only adds behaviour.
 */
(function () {
	'use strict';

	function init(root) {
		var cfgEl = root.querySelector('.bv-gi__i18n');
		var T = cfgEl ? JSON.parse(cfgEl.textContent) : {};
		var tabs = root.querySelectorAll('.bv-gi__tab');
		var lists = root.querySelectorAll('.bv-gi__list');
		var filters = root.querySelector('.bv-gi__filters');
		var q = root.querySelector('.bv-gi__q');
		var sector = root.querySelector('.bv-gi__sector');
		var sort = root.querySelector('.bv-gi__sort');
		var chips = root.querySelectorAll('.bv-gi__chip');
		var missing = root.querySelector('.bv-gi__missing');
		var count = root.querySelector('.bv-gi__count');
		var reset = root.querySelector('.bv-gi__reset');
		var current = root.getAttribute('data-default-list');

		function activeList() {
			return root.querySelector('.bv-gi__list[data-list="' + current + '"]');
		}

		function rows() {
			var list = activeList();
			return list ? Array.prototype.slice.call(list.querySelectorAll('tbody tr')) : [];
		}

		function fillSectors() {
			var seen = {};
			var keep = sector.value;
			while (sector.options.length > 1) sector.remove(1);
			rows().forEach(function (tr) {
				var s = tr.getAttribute('data-sector');
				if (s && !seen[s]) seen[s] = true;
			});
			Object.keys(seen).sort().forEach(function (s) {
				var o = document.createElement('option');
				o.value = s;
				o.textContent = s;
				sector.appendChild(o);
			});
			sector.value = seen[keep] ? keep : '';
		}

		function apply() {
			var term = (q.value || '').trim().toLowerCase();
			var sec = sector.value;
			var need = [];
			chips.forEach(function (c) {
				if (c.getAttribute('aria-pressed') === 'true') need.push(c.getAttribute('data-check').toLowerCase());
			});
			var want = missing.checked ? '0' : '1';
			var all = rows();
			var shown = 0;
			all.forEach(function (tr) {
				var ok = true;
				if (term && tr.getAttribute('data-name').indexOf(term) === -1) ok = false;
				if (ok && sec && tr.getAttribute('data-sector') !== sec) ok = false;
				for (var i = 0; ok && i < need.length; i++) {
					if (tr.getAttribute('data-c-' + need[i]) !== want) ok = false;
				}
				tr.hidden = !ok;
				if (ok) shown++;
			});
			var tbody = all.length ? all[0].parentNode : null;
			if (tbody) {
				var key = sort.value;
				all.sort(function (a, b) {
					if (key === 'name') return a.getAttribute('data-name').localeCompare(b.getAttribute('data-name'));
					if (key === 'change') {
						var ca = a.getAttribute('data-change'), cb = b.getAttribute('data-change');
						ca = ca === '' ? -999 : +ca;
						cb = cb === '' ? -999 : +cb;
						if (cb !== ca) return cb - ca;
					}
					var attr = key === 'ai' || key === 'ready' ? 'data-' + key : 'data-score';
					var va = a.getAttribute(attr), vb = b.getAttribute(attr);
					va = va === '' ? -1 : +va;
					vb = vb === '' ? -1 : +vb;
					return (vb - va) || a.getAttribute('data-name').localeCompare(b.getAttribute('data-name'));
				}).forEach(function (tr) { tbody.appendChild(tr); });
			}
			if (count && T.count) count.textContent = T.count.replace('%1$s', shown).replace('%2$s', all.length);
		}

		function select(list) {
			current = list;
			tabs.forEach(function (t) { t.setAttribute('aria-selected', t.getAttribute('data-list') === list ? 'true' : 'false'); });
			lists.forEach(function (l) { l.hidden = l.getAttribute('data-list') !== list; });
			fillSectors();
			apply();
		}

		if (filters && rows().length) {
			filters.hidden = false;
			tabs.forEach(function (t) { t.addEventListener('click', function () { select(t.getAttribute('data-list')); }); });
			q.addEventListener('input', apply);
			sector.addEventListener('change', apply);
			sort.addEventListener('change', apply);
			missing.addEventListener('change', apply);
			chips.forEach(function (c) {
				c.addEventListener('click', function () {
					c.setAttribute('aria-pressed', c.getAttribute('aria-pressed') === 'true' ? 'false' : 'true');
					apply();
				});
			});
			reset.addEventListener('click', function () {
				q.value = '';
				sector.value = '';
				sort.value = 'score';
				missing.checked = false;
				chips.forEach(function (c) { c.setAttribute('aria-pressed', 'false'); });
				apply();
			});
			select(current);
		} else {
			tabs.forEach(function (t) { t.addEventListener('click', function () { select(t.getAttribute('data-list')); }); });
		}

		/* -------- domain check -------- */
		var form = root.querySelector('.bv-gi__form');
		var out = root.querySelector('.bv-gi__result');
		var gate = root.querySelector('.bv-gi__gate');
		var plan = root.querySelector('.bv-gi__plan');
		var api = root.getAttribute('data-api');
		var lang = root.getAttribute('data-lang');
		var last = null;

		function esc(s) {
			return String(s).replace(/[&<>"']/g, function (c) {
				return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
			});
		}

		function renderResult(d) {
			var html = '<p class="bv-gi__big"><strong>' + esc(d.score) + '</strong> ' + esc(T.outOf) + '</p>';
			html += '<p class="bv-gi__for">' + esc(T.scoreFor.replace('%s', d.domain)) + '</p><ul class="bv-gi__checks">';
			Object.keys(T.labels).forEach(function (k) {
				if (!(k in d.checks)) return;
				var v = d.checks[k];
				var cls = v === true ? 'yes' : (v === false ? 'no' : 'na');
				var word = v === true ? T.pass : (v === false ? T.fail : T.unknown);
				html += '<li class="bv-gi__li--' + cls + '"><span>' + esc(word) + '</span> ' + esc(T.labels[k]) + '</li>';
			});
			out.innerHTML = html + '</ul>';
			out.hidden = false;
			gate.hidden = false;
			plan.hidden = true;
		}

		function renderPlan(d) {
			var items = Object.keys(T.advice).filter(function (k) { return d.checks[k] === false; });
			plan.innerHTML = items.length
				? '<ol>' + items.map(function (k) { return '<li><strong>' + esc(T.labels[k] || k) + '.</strong> ' + esc(T.advice[k]) + '</li>'; }).join('') + '</ol>'
				: '<p>' + esc(T.pass) + '.</p>';
			plan.hidden = false;
			gate.hidden = true;
		}

		if (form && api) {
			form.addEventListener('submit', function (e) {
				e.preventDefault();
				var dom = (form.domain.value || '').trim().toLowerCase().replace(/^[a-z]+:\/\//, '').replace(/^www\./, '').replace(/[\/?#:].*$/, '');
				if (!/^([a-z0-9-]+\.)+[a-z]{2,24}$/.test(dom)) {
					out.textContent = T.invalid;
					out.hidden = false;
					return;
				}
				out.textContent = T.checking;
				out.hidden = false;
				gate.hidden = true;
				plan.hidden = true;
				var btn = form.querySelector('button');
				btn.disabled = true;
				fetch(api + (api.indexOf('?') === -1 ? '?' : '&') + 'domain=' + encodeURIComponent(dom) + '&lang=' + encodeURIComponent(lang), { credentials: 'same-origin' })
					.then(function (r) {
						if (r.status === 429) throw new Error('limit');
						if (!r.ok) throw new Error('error');
						return r.json();
					})
					.then(function (d) { last = d; renderResult(d); })
					.catch(function (err) { out.textContent = err.message === 'limit' ? T.limit : T.error; })
					.then(function () { btn.disabled = false; });
			});
			document.addEventListener('wpcf7mailsent', function (ev) {
				if (last && gate.contains(ev.target)) renderPlan(last);
			});
		}
	}

	function boot() {
		document.querySelectorAll('[data-bv-block="geo-index"]').forEach(init);
	}
	if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
	else boot();
})();
