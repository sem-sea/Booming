/* "Ask Ben" AI chat panel (1.23.0). Loaded only after a click on the launcher.
 * Text is always set with textContent, links only to this site. */
(function () {
	'use strict';
	var cfg = window.bvChatCfg;
	if (!cfg || window.bvChat) return;
	var t = cfg.t;
	var KEY = 'bv-chat', OPEN = 'bv-chat-open', MAX = 600;
	var launch = document.querySelector('.bv-chat-launch');
	var msgs = [], busy = false, panel, log, form, input, sendBtn, counter, startersEl;

	function store(k, v) { try { v === null ? sessionStorage.removeItem(k) : sessionStorage.setItem(k, v); } catch (e) {} }
	function read(k) { try { return sessionStorage.getItem(k); } catch (e) { return null; } }
	try { msgs = JSON.parse(read(KEY) || '[]'); if (!Array.isArray(msgs)) msgs = []; } catch (e) { msgs = []; }

	function el(tag, cls, text) {
		var n = document.createElement(tag);
		if (cls) n.className = cls;
		if (text != null) n.textContent = text;
		return n;
	}
	function svg(path) {
		var ns = 'http://www.w3.org/2000/svg', s = document.createElementNS(ns, 'svg');
		s.setAttribute('viewBox', '0 0 24 24'); s.setAttribute('width', '20'); s.setAttribute('height', '20');
		s.setAttribute('fill', 'none'); s.setAttribute('stroke', 'currentColor'); s.setAttribute('stroke-width', '2');
		s.setAttribute('stroke-linecap', 'round'); s.setAttribute('stroke-linejoin', 'round'); s.setAttribute('aria-hidden', 'true');
		path.split('|').forEach(function (d) { var p = document.createElementNS(ns, 'path'); p.setAttribute('d', d); s.appendChild(p); });
		return s;
	}
	function avatar(size) {
		var i = el('img', 'bv-chat__avatar');
		i.src = cfg.avatar; i.alt = ''; i.width = size; i.height = size; i.decoding = 'async';
		return i;
	}
	function safeUrl(u) {
		try { var x = new URL(u, location.href); return x.origin === location.origin ? x.href : ''; } catch (e) { return ''; }
	}

	function build() {
		panel = el('div', 'bv-chat');
		panel.setAttribute('role', 'dialog');
		panel.setAttribute('aria-modal', 'false');
		panel.setAttribute('aria-labelledby', 'bv-chat-title');
		panel.hidden = true;

		var head = el('div', 'bv-chat__head');
		var who = el('div', 'bv-chat__who');
		var av = el('span', 'bv-chat__face'); av.appendChild(avatar(44));
		var names = el('div', 'bv-chat__names');
		var title = el('strong', 'bv-chat__title', t.title); title.id = 'bv-chat-title';
		names.appendChild(title);
		names.appendChild(el('span', 'bv-chat__badge', t.label));
		who.appendChild(av); who.appendChild(names);
		var tools = el('div', 'bv-chat__tools');
		var reset = el('button', 'bv-chat__icon'); reset.type = 'button'; reset.title = t.clear; reset.setAttribute('aria-label', t.clear);
		reset.appendChild(svg('M3 12a9 9 0 1 0 3-6.7L3 8|M3 3v5h5'));
		reset.addEventListener('click', function () { msgs = []; save(); render(); input.focus(); });
		var close = el('button', 'bv-chat__icon'); close.type = 'button'; close.title = t.close; close.setAttribute('aria-label', t.close);
		close.appendChild(svg('M18 6 6 18|M6 6l12 12'));
		close.addEventListener('click', hide);
		tools.appendChild(reset); tools.appendChild(close);
		head.appendChild(who); head.appendChild(tools);

		log = el('div', 'bv-chat__log');
		log.setAttribute('role', 'log');
		log.setAttribute('aria-live', 'polite');
		startersEl = el('div', 'bv-chat__starters');
		t.starters.forEach(function (q) {
			var b = el('button', 'bv-chat__starter', q); b.type = 'button';
			b.addEventListener('click', function () { send(q); });
			startersEl.appendChild(b);
		});

		form = el('form', 'bv-chat__form');
		form.setAttribute('novalidate', '');
		input = el('textarea', 'bv-chat__input');
		input.rows = 1; input.maxLength = MAX; input.placeholder = t.place;
		input.setAttribute('aria-label', t.place);
		input.setAttribute('enterkeyhint', 'send');
		input.addEventListener('input', grow);
		input.addEventListener('keydown', function (e) {
			if (e.key === 'Enter' && !e.shiftKey && !e.isComposing) { e.preventDefault(); form.requestSubmit ? form.requestSubmit() : submit(e); }
		});
		sendBtn = el('button', 'bv-chat__send'); sendBtn.type = 'submit'; sendBtn.setAttribute('aria-label', t.send);
		sendBtn.appendChild(svg('M5 12h14|M13 6l6 6-6 6'));
		form.appendChild(input); form.appendChild(sendBtn);
		form.addEventListener('submit', submit);

		var foot = el('div', 'bv-chat__foot');
		counter = el('span', 'bv-chat__count');
		var note = el('span', 'bv-chat__note', t.note + ' ');
		if (cfg.privacy && safeUrl(cfg.privacy)) {
			var a = el('a', '', t.privacy); a.href = safeUrl(cfg.privacy);
			note.appendChild(a);
		}
		foot.appendChild(note); foot.appendChild(counter);

		panel.appendChild(head);
		if (cfg.test) panel.appendChild(el('div', 'bv-chat__test', t.testmode));
		panel.appendChild(log);
		panel.appendChild(form);
		panel.appendChild(foot);
		document.body.appendChild(panel);

		document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && panel && !panel.hidden) hide(); });
		render();
	}

	function grow() {
		input.style.height = 'auto';
		input.style.height = Math.min(input.scrollHeight, 132) + 'px';
		var left = MAX - input.value.length;
		counter.textContent = left < 120 ? String(left) : '';
	}

	function bubble(m) {
		var row = el('div', 'bv-chat__row bv-chat__row--' + (m.role === 'user' ? 'user' : 'bot'));
		if (m.role !== 'user') row.appendChild(avatar(28));
		var b = el('div', 'bv-chat__bubble' + (m.error ? ' bv-chat__bubble--error' : ''));
		String(m.content).split(/\n{2,}/).forEach(function (para) {
			if (para.trim()) b.appendChild(el('p', '', para.trim()));
		});
		if (m.links && m.links.length) {
			var links = el('div', 'bv-chat__links');
			m.links.forEach(function (l) {
				var u = safeUrl(l.url);
				if (!u) return;
				var a = el('a', 'bv-chat__link', l.label); a.href = u;
				a.appendChild(svg('M5 12h14|M13 6l6 6-6 6'));
				links.appendChild(a);
			});
			b.appendChild(links);
		}
		row.appendChild(b);
		return row;
	}

	function render() {
		log.textContent = '';
		log.appendChild(bubble({ role: 'assistant', content: t.welcome }));
		if (!msgs.length) log.appendChild(startersEl);
		msgs.forEach(function (m) { log.appendChild(bubble(m)); });
		if (busy) {
			var row = el('div', 'bv-chat__row bv-chat__row--bot bv-chat__typing');
			row.appendChild(avatar(28));
			var b = el('div', 'bv-chat__bubble');
			b.setAttribute('aria-label', '...');
			for (var i = 0; i < 3; i++) b.appendChild(el('span', 'bv-chat__dot'));
			row.appendChild(b);
			log.appendChild(row);
		}
		sendBtn.disabled = busy;
		log.scrollTop = log.scrollHeight;
	}

	function save() { store(KEY, JSON.stringify(msgs.slice(-20))); }

	function submit(e) {
		if (e) e.preventDefault();
		send(input.value);
	}

	function send(text) {
		text = String(text || '').trim().slice(0, MAX);
		if (!text || busy) return;
		msgs.push({ role: 'user', content: text });
		input.value = ''; grow();
		busy = true; save(); render();
		var history = msgs.filter(function (m) { return !m.error; }).slice(-8).map(function (m) {
			return m.role === 'user' ? { role: 'user', content: m.content } : { role: 'assistant', content: m.content, sig: m.sig || '' };
		});
		var headers = { 'Content-Type': 'application/json' };
		if (cfg.nonce) headers['X-WP-Nonce'] = cfg.nonce;
		fetch(cfg.api, {
			method: 'POST', credentials: 'same-origin', headers: headers,
			body: JSON.stringify({ messages: history, lang: cfg.lang, page: location.pathname })
		}).then(function (r) {
			return r.json().catch(function () { return {}; }).then(function (d) { return { ok: r.ok, d: d }; });
		}).then(function (res) {
			if (res.ok && res.d && typeof res.d.reply === 'string') {
				msgs.push({ role: 'assistant', content: res.d.reply, sig: res.d.sig || '', links: Array.isArray(res.d.links) ? res.d.links.slice(0, 3) : [] });
			} else {
				msgs.push({ role: 'assistant', content: (res.d && typeof res.d.error === 'string' && res.d.error.length < 300) ? res.d.error : t.error, error: true });
			}
		}).catch(function () {
			msgs.push({ role: 'assistant', content: t.error, error: true });
		}).then(function () {
			busy = false; save(); render();
			input.focus({ preventScroll: true });
		});
	}

	function show() {
		if (!panel) build();
		panel.hidden = false;
		document.documentElement.classList.add('bv-chat-on');
		if (launch) launch.setAttribute('aria-expanded', 'true');
		store(OPEN, '1');
		requestAnimationFrame(function () { panel.classList.add('is-open'); log.scrollTop = log.scrollHeight; });
		if (window.matchMedia('(min-width: 601px)').matches) input.focus({ preventScroll: true });
	}
	function hide() {
		if (!panel) return;
		panel.classList.remove('is-open');
		panel.hidden = true;
		document.documentElement.classList.remove('bv-chat-on');
		if (launch) { launch.setAttribute('aria-expanded', 'false'); launch.focus({ preventScroll: true }); }
		store(OPEN, null);
	}

	window.bvChat = { open: show, close: hide };
})();
