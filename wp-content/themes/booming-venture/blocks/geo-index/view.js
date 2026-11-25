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
		var more = root.querySelector('.bv-gi__more');
		var active = root.querySelector('.bv-gi__active');
		var current = root.getAttribute('data-default-list');
		var how = root.querySelector('.bv-gi__how');
		if (how && root.clientWidth >= 720) how.open = true;

		function activeList() {
			return root.querySelector('.bv-gi__list[data-list="' + current + '"]');
		}

		function rows() {
			var list = activeList();
			return list ? Array.prototype.slice.call(list.querySelectorAll('.bv-gi__table > tbody > tr:not(.bv-gi__detail)')) : [];
		}

		/* The sector list is rendered on the server: the same 20 sectors, in the
		   standard's order, for both lists. Nothing to rebuild per tab. */
		function fillSectors() {}

		/* Details and gaps per company: built from compact data when the button is pressed. */
		var D = {};
		var dEl = root.querySelector('.bv-gi__i18d');
		var TD = dEl ? JSON.parse(dEl.textContent) : {};
		root.querySelectorAll('.bv-gi__dd').forEach(function (el) {
			var list = el.closest('.bv-gi__list');
			D[list ? list.getAttribute('data-list') : ''] = JSON.parse(el.textContent);
		});
		function fill(str) {
			var args = arguments;
			return String(str).replace(/\{(\d)\}/g, function (m, i) { return args[+i] !== undefined ? args[+i] : m; });
		}
		function blk(title, inner) { return '<div class="bv-gi__dblk"><h4>' + esc(title) + '</h4>' + inner + '</div>'; }
		function detailHtml(tr, x) {
			var name = (tr.querySelector('.bv-gi__co strong') || {}).textContent || '';
			var sec = tr.getAttribute('data-sector') || '';
			var h = '';
			if (x.a) {
				var a = x.a, li = [];
				li.push(fill(TD.named, a[1], a[0], a[2], a[3], a[4]));
				if (a[5] !== null) li.push(fill(TD.sov, sec, a[5]));
				li.push(fill(TD.first, a[6]));
				if (a[7] !== null) li.push(fill(TD.tone, a[7], TD.tones[a[7] >= 65 ? 2 : (a[7] >= 45 ? 1 : 0)]));
				li.push(fill(TD.cite, a[8]));
				h += blk(TD.ai, (a[9] ? '<p class="bv-gi__warn">' + esc(fill(TD.low, a[0], TD.min)) + '</p>' : '') + '<ul class="bv-gi__facts">' + li.map(function (t) { return '<li>' + esc(t) + '</li>'; }).join('') + '</ul>');
			} else {
				h += blk(TD.ai, '<p>' + esc(TD.noAi) + '</p>');
			}
			if (x.e && x.e.length) {
				h += blk(TD.perEng, '<ul class="bv-gi__bars">' + x.e.map(function (e) {
					var pct = e[1] ? Math.round(100 * e[2] / e[1]) : 0;
					return '<li><span class="bv-gi__bl">' + esc(TD.eng[e[0]] || e[0]) + '</span><span class="bv-gi__mini" style="--p:' + pct + '" aria-hidden="true"></span><span class="bv-gi__bv">' + esc(fill(TD.of, e[2], e[1])) + '</span></li>';
				}).join('') + '</ul>');
			}
			var c = x.c, ct;
			if (c === 'm') ct = TD.chgM;
			else if (c === 'n' || !c) ct = TD.chgN;
			else if (c[0] === 0) ct = TD.chgSame;
			else ct = fill(c[1] ? TD.chgSig : TD.chgNs, (c[0] > 0 ? '+' : '') + c[0]);
			h += blk(TD.chg, '<p>' + esc(ct) + '</p>');
			if (x.l) {
				var l = x.l, lt;
				if (l[0] === 1) {
					lt = '<p>' + esc(fill(TD.leadSelf, name) + (l[1] ? ' ' + fill(TD.leadTwo, l[1]) : '')) + '</p>';
				} else {
					lt = '<p>' + esc(fill(TD.leadGap, l[1], l[2], l[3]) + (l[4] ? TD.tied : '.')) + '</p>';
					if (l[5] && l[5].length) lt += '<p>' + esc(fill(TD.srcs, l[1], name)) + ' <span class="bv-gi__hosts">' + esc(l[5].join(', ')) + '</span></p>';
					if (l[6] && l[6].length) lt += '<p>' + esc(fill(TD.miss, l[1])) + ' ' + esc(l[6].map(function (k) { return T.labels[k] || k; }).join(', ')) + '</p>';
				}
				h += blk(fill(TD.lead, sec), lt);
			}
			if (x.q && TD.quads[x.q]) h += blk(TD.quad, '<p>' + esc(TD.quads[x.q]) + '</p><p class="bv-gi__small">' + esc(TD.quadNote) + '</p>');
			return h;
		}
		function detailOf(tr) {
			var btn = tr.querySelector('.bv-gi__dbtn');
			return btn ? document.getElementById(btn.getAttribute('aria-controls')) : null;
		}
		root.querySelectorAll('.bv-gi__dbtn').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var tr = btn.closest('tr');
				var open = btn.getAttribute('aria-expanded') !== 'true';
				var det = detailOf(tr);
				if (!det && open) {
					var list = tr.closest('.bv-gi__list');
					var x = (D[list ? list.getAttribute('data-list') : ''] || {})[(tr.getAttribute('data-name') || '').split(' ').pop()];
					det = document.createElement('tr');
					det.className = 'bv-gi__detail';
					det.id = btn.getAttribute('aria-controls');
					var td = document.createElement('td');
					td.colSpan = tr.cells.length;
					td.innerHTML = '<div class="bv-gi__dgrid">' + (x ? detailHtml(tr, x) : '') + '</div>';
					det.appendChild(td);
					tr.parentNode.insertBefore(det, tr.nextSibling);
				}
				btn.setAttribute('aria-expanded', open ? 'true' : 'false');
				if (det) det.hidden = !open;
			});
		});

		/* The sector filter also opens that sector in the list of most cited sources. */
		function syncSources() {
			var list = activeList();
			if (!list || !sector) return;
			list.querySelectorAll('.bv-gi__srcs .bv-gi__fold').forEach(function (d) {
				d.open = !!sector.value && d.getAttribute('data-sector') === sector.value;
			});
		}

		var PAGE = 25;
		var limit = PAGE;

		function apply(keep) {
			if (keep !== true) limit = PAGE;
			var term = (q.value || '').trim().toLowerCase();
			var sec = sector.value;
			var need = [];
			chips.forEach(function (c) {
				if (c.getAttribute('aria-pressed') === 'true') need.push(c.getAttribute('data-check').toLowerCase());
			});
			if (active) {
				active.textContent = need.length;
				active.hidden = !need.length;
			}
			var want = missing.checked ? '0' : '1';
			var all = rows();
			var tbody = all.length ? all[0].parentNode : null;
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
			});
			var match = 0;
			all.forEach(function (tr) {
				var ok = true;
				if (term && tr.getAttribute('data-name').indexOf(term) === -1) ok = false;
				if (ok && sec && tr.getAttribute('data-sector') !== sec) ok = false;
				for (var i = 0; ok && i < need.length; i++) {
					if (tr.getAttribute('data-c-' + need[i]) !== want) ok = false;
				}
				if (ok) match++;
				/* Long lists show 25 at a time; the full list stays in the HTML for crawlers. */
				tr.hidden = !ok || match > limit;
				var det = detailOf(tr);
				if (tbody) {
					tbody.appendChild(tr);
					if (det) tbody.appendChild(det); // the details travel with their company
				}
				if (det) {
					var btn = tr.querySelector('.bv-gi__dbtn');
					det.hidden = tr.hidden || !btn || btn.getAttribute('aria-expanded') !== 'true';
				}
			});
			var next = activeList() && activeList().querySelector('.bv-gi__next');
			if (next) {
				var rest = match - limit;
				next.hidden = rest <= 0;
				if (rest > 0 && T.more) next.textContent = T.more.replace('%s', Math.min(rest, PAGE));
			}
			if (count && T.count) count.textContent = T.count.replace('%1$s', match).replace('%2$s', all.length);
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
			/* Wide screens show all filters; phones start with them folded away. */
			if (more && root.clientWidth >= 720) more.open = true;
			tabs.forEach(function (t) { t.addEventListener('click', function () { select(t.getAttribute('data-list')); }); });
			q.addEventListener('input', apply);
			sector.addEventListener('change', function () { apply(); syncSources(); });
			sort.addEventListener('change', apply);
			missing.addEventListener('change', apply);
			chips.forEach(function (c) {
				c.addEventListener('click', function () {
					c.setAttribute('aria-pressed', c.getAttribute('aria-pressed') === 'true' ? 'false' : 'true');
					apply();
				});
			});
			root.querySelectorAll('.bv-gi__next').forEach(function (b) {
				b.addEventListener('click', function () {
					limit += PAGE;
					apply(true);
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
			html += '</ul>';
			if (d.index && T.inIndex) {
				html += '<p class="bv-gi__inidx">' + esc(T.inIndex.replace('%1$s', d.index.name).replace('%2$s', d.index.score));
				if (d.index.ai && !d.index.ai.low && T.inAi) {
					html += ' ' + esc(T.inAi.replace('%1$s', d.index.ai.m).replace('%2$s', d.index.ai.n).replace('%3$s', d.index.ai.vis).replace('%4$s', d.index.ai.lo).replace('%5$s', d.index.ai.hi));
				}
				html += '</p>';
			}
			if (d.gap && T.gapTitle) {
				var g = d.gap;
				html += '<div class="bv-gi__gap"><h4>' + esc(T.gapTitle.replace('%s', g.sector)) + '</h4>';
				if (g.self) {
					html += '<p>' + esc(T.gapSelf) + '</p>';
				} else {
					html += '<p>' + esc(T.gapLead.replace('%1$s', g.leader.name).replace('%2$s', g.leader.score));
					if (g.points !== null && g.points !== undefined) html += ' ' + esc(T.gapPts.replace('%s', g.points)) + (g.tied ? ' ' + esc(T.gapTied) : '');
					html += '</p>';
					if (g.missing && g.missing.length) {
						html += '<p>' + esc(T.gapMiss) + '</p><ul class="bv-gi__gapl">' + g.missing.map(function (k) { return '<li>' + esc(T.labels[k] || k) + '</li>'; }).join('') + '</ul>';
					} else {
						html += '<p>' + esc(T.gapNone) + '</p>';
					}
				}
				if (g.sources && g.sources.length) {
					html += '<p>' + esc(T.gapSrc) + '</p><ol class="bv-gi__gapl">' + g.sources.map(function (h) { return '<li><span class="bv-gi__host">' + esc(h.host) + '</span> ' + esc(T.gapSrcP.replace('%s', h.pct)) + '</li>'; }).join('') + '</ol>';
				}
				html += '</div>';
			}
			out.innerHTML = html;
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
				var csec = form.querySelector('.bv-gi__csec');
				fetch(api + (api.indexOf('?') === -1 ? '?' : '&') + 'domain=' + encodeURIComponent(dom) + '&lang=' + encodeURIComponent(lang) + (csec && csec.value ? '&sector=' + encodeURIComponent(csec.value) : ''), { credentials: 'same-origin' })
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
