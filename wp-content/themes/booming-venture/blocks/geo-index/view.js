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
		function detailHtml(tr, x, lkey) {
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
					if (l[6] && l[6].length) lt += '<p>' + esc(fill(TD.miss, l[1])) + ' ' + esc(l[6].map(function (k) { return k === 'lang' && T.langList && T.langList[lkey] ? T.langList[lkey] : (T.labels[k] || k); }).join(', ')) + '</p>';
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
		var RM = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		function pdfUrl(domain, list, sec) {
			if (!T.pdf) return '';
			return T.pdf + (T.pdf.indexOf('?') === -1 ? '?' : '&') + 'domain=' + encodeURIComponent(domain) + (list ? '&list=' + encodeURIComponent(list) : '') + '&lang=' + encodeURIComponent(T.pdfLang || 'en') + (sec ? '&sector=' + encodeURIComponent(sec) : '');
		}
		function pdfLink(url, label) {
			return url ? '<a class="bv-gi__pdf" href="' + esc(url) + '" download rel="nofollow">' + esc(label) + '</a>' : '';
		}
		/* PDF links download through fetch, so an expired check shows a message instead of a broken file. */
		root.addEventListener('click', function (e) {
			var a = e.target.closest ? e.target.closest('.bv-gi__pdf') : null;
			if (!a || !window.fetch || !window.URL || !URL.createObjectURL) return;
			e.preventDefault();
			if (a.classList.contains('is-busy')) return;
			var label = a.textContent;
			a.classList.add('is-busy');
			a.textContent = T.pdfBusy || label;
			fetch(a.href, { credentials: 'same-origin' })
				.then(function (r) {
					if (!r.ok) throw new Error(r.status === 429 ? 'limit' : (r.status === 404 ? 'gone' : 'error'));
					var cd = r.headers.get('Content-Disposition') || '';
					var m = cd.match(/filename="?([^";]+)"?/);
					return r.blob().then(function (b) { return { b: b, name: m ? m[1] : 'geo-index.pdf' }; });
				})
				.then(function (f) {
					var u = URL.createObjectURL(f.b);
					var tmp = document.createElement('a');
					tmp.href = u;
					tmp.download = f.name;
					document.body.appendChild(tmp);
					tmp.click();
					tmp.remove();
					setTimeout(function () { URL.revokeObjectURL(u); }, 4000);
					a.textContent = label;
				})
				.catch(function (err) {
					a.textContent = err.message === 'gone' ? (T.pdfGone || label) : (err.message === 'limit' ? (T.limit || label) : (T.error || label));
					setTimeout(function () { a.textContent = label; }, 5000);
				})
				.then(function () { a.classList.remove('is-busy'); });
		});

		/* Open: show the row, then let CSS grow it. Close: shrink, then hide when the transition ends. */
		function setOpen(det, open) {
			if (!det) return;
			clearTimeout(det._bvT);
			if (open) {
				det.hidden = false;
				void det.offsetHeight;
				det.classList.add('is-open');
			} else {
				det.classList.remove('is-open');
				det._bvT = setTimeout(function () { if (!det.classList.contains('is-open')) det.hidden = true; }, RM ? 0 : 360);
			}
		}
		root.querySelectorAll('.bv-gi__dbtn').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var tr = btn.closest('tr');
				var open = btn.getAttribute('aria-expanded') !== 'true';
				var det = detailOf(tr);
				if (!det && open) {
					var list = tr.closest('.bv-gi__list');
					var lkey = list ? list.getAttribute('data-list') : '';
					var dom = (tr.getAttribute('data-name') || '').split(' ').pop();
					var x = (D[lkey] || {})[dom];
					det = document.createElement('tr');
					det.className = 'bv-gi__detail';
					det.id = btn.getAttribute('aria-controls');
					det.hidden = true;
					var td = document.createElement('td');
					td.colSpan = tr.cells.length;
					var act = pdfLink(pdfUrl(dom, lkey, ''), T.pdfBtn);
					td.innerHTML = '<div class="bv-gi__dwrap"><div class="bv-gi__din"><div class="bv-gi__dgrid">' + (x ? detailHtml(tr, x, lkey) : '') + (act ? '<div class="bv-gi__dact">' + act + '</div>' : '') + '</div></div></div>';
					det.appendChild(td);
					tr.parentNode.insertBefore(det, tr.nextSibling);
				}
				btn.setAttribute('aria-expanded', open ? 'true' : 'false');
				setOpen(det, open);
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
					var show = !tr.hidden && btn && btn.getAttribute('aria-expanded') === 'true';
					det.hidden = !show;
					det.classList.toggle('is-open', !!show);
				}
			});
			var next = activeList() && activeList().querySelector('.bv-gi__next');
			if (next) {
				var rest = match - limit;
				next.hidden = rest <= 0;
				if (rest > 0 && T.more) next.textContent = T.more.replace('%s', Math.min(rest, PAGE));
			}
			if (count && T.count) count.textContent = T.count.replace('%1$s', match).replace('%2$s', all.length);
			/* With a sector picked, offer the PDF report for that sector in this list. */
			var sp = root.querySelector('.bv-gi__secpdf');
			if (sp) {
				var opt = sector.options[sector.selectedIndex];
				sp.innerHTML = sec && T.secPdf && opt && opt.getAttribute('data-key') ? pdfLink(pdfUrl('', current, opt.getAttribute('data-key')), T.secPdf.replace('%s', opt.text)) : '';
				sp.hidden = !sec;
			}
			if (tbody && !RM) {
				tbody.classList.remove('bv-gi--sw');
				void tbody.offsetWidth;
				tbody.classList.add('bv-gi--sw');
			}
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

		/* The score counts up from 0, quick and eased; instant when the visitor prefers less motion. */
		function countUp(el, to) {
			if (!el || RM || !window.requestAnimationFrame) return;
			var start = null, dur = 700;
			el.textContent = '0';
			function step(ts) {
				if (start === null) start = ts;
				var p = Math.min(1, (ts - start) / dur);
				el.textContent = Math.round(to * (1 - Math.pow(1 - p, 3)));
				if (p < 1) window.requestAnimationFrame(step);
			}
			window.requestAnimationFrame(step);
		}

		/* Points a check is worth (see inc/geo-index.php), to say how much a refused homepage hides. */
		var WORTH = { html: 15, schema_org: 20, lang: 8, fresh: 10 };

		function renderResult(d) {
			var partial = d.status && d.status !== 'ok';
			var html = '<p class="bv-gi__big"><strong>' + esc(d.score) + '</strong> ' + esc(T.outOf) + (partial && T.partial ? ' <span class="bv-gi__inc">(' + esc(T.partial) + ')</span>' : '') + '</p>';
			html += '<p class="bv-gi__for">' + esc(T.scoreFor.replace('%s', d.domain)) + '</p>';
			if (partial && T.refused) {
				var lost = 0;
				Object.keys(WORTH).forEach(function (k) { if (d.checks[k] === null || d.checks[k] === undefined) lost += WORTH[k]; });
				var code = d.home_code ? 'HTTP ' + d.home_code : T.noAnswer;
				var botsOpen = d.robots_code >= 200 && d.robots_code < 300 && ['bot_OAI-SearchBot', 'bot_Claude-SearchBot', 'bot_PerplexityBot'].every(function (k) { return d.checks[k] === true; });
				html += '<div class="bv-gi__warn"><p><strong>' + esc(T.refused.replace('%s', code).replace('%s', lost)) + '</strong></p><p>' + esc(botsOpen ? T.botWarn : T.retry) + '</p></div>';
			}
			html += '<ul class="bv-gi__checks">';
			var n = 0;
			Object.keys(T.labels).forEach(function (k) {
				if (!(k in d.checks)) return;
				var v = d.checks[k];
				var cls = v === true ? 'yes' : (v === false ? 'no' : 'na');
				var word = v === true ? T.pass : (v === false ? T.fail : T.unknown);
				html += '<li class="bv-gi__li--' + cls + '" style="--i:' + (n++) + '"><span>' + esc(word) + '</span> ' + esc(T.labels[k]) + '</li>';
			});
			html += '</ul>';
			if (T.listNote) {
				html += '<p class="bv-gi__note">' + esc(T.listNote) + (T.otherUrl ? ' <a href="' + esc(T.otherUrl) + '">' + esc(T.otherTxt) + '</a>' : '') + '</p>';
			}
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
					}
					if (g.partial && T.gapPart) {
						html += '<p>' + esc(T.gapPart) + '</p>';
					} else if (!g.missing || !g.missing.length) {
						html += '<p>' + esc(T.gapNone) + '</p>';
					}
				}
				if (g.sources && g.sources.length) {
					html += '<p>' + esc(T.gapSrc) + '</p><ol class="bv-gi__gapl">' + g.sources.map(function (h) { return '<li><span class="bv-gi__host">' + esc(h.host) + '</span> ' + esc(T.gapSrcP.replace('%s', h.pct)) + '</li>'; }).join('') + '</ol>';
				}
				html += '</div>';
			}
			out.classList.remove('is-loading', 'is-in');
			out.innerHTML = html;
			out.hidden = false;
			void out.offsetWidth;
			out.classList.add('is-in');
			countUp(out.querySelector('.bv-gi__big strong'), +d.score || 0);
			gate.hidden = false;
			plan.hidden = true;
		}

		function renderPlan(d) {
			var items = Object.keys(T.advice).filter(function (k) { return d.checks[k] === false; });
			var csec = form ? form.querySelector('.bv-gi__csec') : null;
			plan.innerHTML = (T.planHead ? '<h4>' + esc(T.planHead) + '</h4>' : '') + (items.length
				? '<ol>' + items.map(function (k) { return '<li><strong>' + esc(T.labels[k] || k) + '.</strong> ' + esc(T.advice[k]) + '</li>'; }).join('') + '</ol>'
				: '<p>' + esc(T.pass) + '.</p>') + pdfLink(pdfUrl(d.domain, '', csec && csec.value ? csec.value : ''), T.pdfPlan);
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
				out.classList.remove('is-in');
				out.classList.add('is-loading');
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
					.catch(function (err) { out.classList.remove('is-loading'); out.textContent = err.message === 'limit' ? T.limit : T.error; })
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
