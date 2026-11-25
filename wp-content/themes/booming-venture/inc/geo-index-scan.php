<?php
/**
 * GEO Index scan panel (1.22.0): start, follow and stop a scan from
 * Settings > GEO Index.
 *
 * Two progress bars (websites and AI questions), the company or question
 * being handled, answers and errors per assistant, the monthly report and the
 * date of the next automatic scan. While the page is open it moves the scan
 * on step by step, so a manual scan finishes even when nobody visits the
 * site; closed, the scan continues through WordPress cron.
 *
 * @package BoomingVenture
 */

defined( 'ABSPATH' ) || exit;

/** When the next automatic (first Monday) scan starts. */
function bv_gi_next_monthly(): int {
	$now   = time();
	$first = strtotime( 'first monday of this month 05:00 UTC', $now );
	if ( $now < $first && get_option( 'bv_gi_monthly_last' ) !== gmdate( 'Y-m', $now ) ) {
		return $first;
	}
	return strtotime( 'first monday of next month 05:00 UTC', strtotime( gmdate( 'Y-m-01 12:00', $now ) . ' UTC' ) );
}

/** Everything the panel shows, as one array (also the AJAX answer). */
function bv_gi_scan_status(): array {
	$fmt     = static fn( $t ) => $t ? wp_date( 'j F Y, H:i', (int) $t ) : '';
	$co      = bv_gi_companies();
	$meta    = (array) get_option( 'bv_gi_meta', [] );
	$wq      = count( (array) get_option( 'bv_gi_queue', [] ) );
	$wtot    = (int) ( $meta['total'] ?? 0 ) ?: count( (array) ( $co['nl'] ?? [] ) ) + count( (array) ( $co['intl'] ?? [] ) );
	$work    = (array) get_option( 'bv_gi_ai_work', [] );
	$aq      = count( (array) get_option( 'bv_gi_ai_queue', [] ) );
	$atot    = (int) ( $work['total'] ?? 0 ) ?: count( bv_gi_ai_categories() );
	$active  = bv_gi_ai_active();
	$engines = [];
	foreach ( $active as $e ) {
		$ok        = (int) ( $work['ok'][ $e ] ?? 0 );
		$failed    = (int) ( $work['failed'][ $e ] ?? 0 );
		$engines[] = [
			'label'   => bv_gi_ai_engines()[ $e ]['label'] . ( 'openrouter' === bv_gi_ai_via( $e ) ? ' (OpenRouter)' : '' ),
			'ok'      => $ok,
			'failed'  => $failed,
			'used'    => bv_gi_ai_used( $e ),
			'cap'     => bv_gi_ai_cap(),
			'error'   => (string) ( $work['errors'][ $e ] ?? '' ),
			'skipped' => ! $ok && ( (int) ( $work['hard'][ $e ] ?? 0 ) >= 5 || $failed >= 15 ),
		];
	}
	$web_running = $wq > 0;
	$ai_running  = $aq > 0 && (bool) $active;

	$reports = (array) get_option( 'bv_gi_report', [] );
	$links   = [];
	if ( $reports ) {
		$ids = (array) end( $reports );
		foreach ( [ 'en' => 'EN', 'nl' => 'NL' ] as $lang => $label ) {
			if ( ! empty( $ids[ $lang ] ) && get_post( $ids[ $lang ] ) ) {
				$links[] = [ 'label' => $label . ': ' . get_the_title( $ids[ $lang ] ), 'url' => (string) get_edit_post_link( $ids[ $lang ], 'raw' ), 'status' => (string) get_post_status( $ids[ $lang ] ) ];
			}
		}
	}

	return [
		'running' => $web_running || $ai_running,
		'source'  => (string) ( $meta['source'] ?? '' ),
		'next'    => $fmt( bv_gi_next_monthly() ),
		'web'     => [
			'total'     => $wtot,
			'done'      => ! empty( $meta['started'] ) ? max( 0, $wtot - $wq ) : 0,
			'running'   => $web_running,
			'current'   => $web_running ? (string) ( $meta['current'] ?? '' ) : '',
			'started'   => $fmt( $meta['started'] ?? 0 ),
			'completed' => $web_running ? '' : $fmt( $meta['completed'] ?? 0 ),
			'stopped'   => ! empty( $meta['stopped'] ),
		],
		'ai'      => [
			'active'    => (bool) $active,
			'total'     => $atot,
			'done'      => ! empty( $work['started'] ) ? max( 0, $atot - $aq ) : 0,
			'running'   => $ai_running,
			'current'   => $ai_running ? (string) ( $work['current'] ?? '' ) : '',
			'started'   => $fmt( $work['started'] ?? 0 ),
			'completed' => $ai_running ? '' : $fmt( $work['completed'] ?? 0 ),
			'stopped'   => ! empty( $work['stopped'] ),
			'engines'   => $engines,
		],
		'report'  => [ 'mode' => function_exists( 'bv_gi_report_opts' ) ? bv_gi_report_opts()['mode'] : 'off', 'links' => $links ],
	];
}

/* ------------------------------------------------------------------ AJAX */

function bv_gi_scan_guard(): void {
	if ( ! current_user_can( 'manage_options' ) || ! check_ajax_referer( 'bv_gi_scan', '_ajax_nonce', false ) ) {
		wp_send_json_error( [ 'message' => 'Not allowed.' ], 403 );
	}
}

add_action( 'wp_ajax_bv_gi_scan_status', static function () {
	bv_gi_scan_guard();
	wp_send_json_success( bv_gi_scan_status() );
} );

add_action( 'wp_ajax_bv_gi_scan_start', static function () {
	bv_gi_scan_guard();
	if ( bv_gi_scan_status()['running'] ) {
		wp_send_json_error( [ 'message' => 'Er loopt al een scan.' ] );
	}
	if ( ! empty( $_POST['ai'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- checked in bv_gi_scan_guard()
		do_action( 'bv_gi_run', 'manual' ); // websites and AI
	} else {
		bv_gi_start_web( 'manual' );
	}
	wp_send_json_success( bv_gi_scan_status() );
} );

/* One step while the panel is open: alternately a website block and an AI question. */
add_action( 'wp_ajax_bv_gi_scan_step', static function () {
	bv_gi_scan_guard();
	if ( function_exists( 'set_time_limit' ) ) {
		@set_time_limit( 110 ); // phpcs:ignore
	}
	$web = (bool) get_option( 'bv_gi_queue' ) && ! get_transient( 'bv_gi_lock' );
	$ai  = (bool) get_option( 'bv_gi_ai_queue' ) && bv_gi_ai_active() && ! get_transient( 'bv_gi_ai_lock' );
	$did = '';
	if ( $ai && ( ! $web || 'web' === get_transient( 'bv_gi_step_last' ) ) ) {
		do_action( 'bv_gi_ai_batch' );
		$did = 'ai';
	} elseif ( $web ) {
		do_action( 'bv_gi_batch' );
		$did = 'web';
	}
	set_transient( 'bv_gi_step_last', $did ?: 'none', 10 * MINUTE_IN_SECONDS );
	wp_send_json_success( bv_gi_scan_status() + [ 'did' => $did ] );
} );

/* Stop: websites keep what is measured; an unfinished AI run is dropped, so the last full AI results stay. */
add_action( 'wp_ajax_bv_gi_scan_stop', static function () {
	bv_gi_scan_guard();
	update_option( 'bv_gi_queue', [], false );
	wp_clear_scheduled_hook( 'bv_gi_batch' );
	$meta = (array) get_option( 'bv_gi_meta', [] );
	if ( empty( $meta['completed'] ) ) {
		update_option( 'bv_gi_meta', [ 'completed' => time(), 'stopped' => 1, 'current' => '' ] + $meta, false );
	}
	update_option( 'bv_gi_ai_queue', [], false );
	wp_clear_scheduled_hook( 'bv_gi_ai_batch' );
	$work = (array) get_option( 'bv_gi_ai_work', [] );
	if ( ! empty( $work['started'] ) && empty( $work['completed'] ) ) {
		update_option( 'bv_gi_ai_work', [ 'completed' => time(), 'stopped' => 1, 'current' => '' ] + $work, false );
	}
	wp_send_json_success( bv_gi_scan_status() );
} );

/* ------------------------------------------------------------------ panel */

add_action( 'bv_gi_settings_top', static function () {
	$cats = count( bv_gi_ai_categories() );
	$ai   = (bool) bv_gi_ai_active();
	$cfg  = [
		'ajax'    => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'bv_gi_scan' ),
		'confirm' => $ai
			? sprintf( 'Volledige scan starten? De site meet 200 websites en stelt %d vragen aan elke actieve AI-assistent. Bij OpenRouter kost dat ongeveer $4 tot $6.', $cats )
			: 'Scan starten? De site meet 200 websites. Zonder actieve AI-assistent kost dit niets.',
		'stop'    => 'Scan stoppen? Gemeten websites blijven staan. Een onafgemaakte AI-meting vervalt; de laatste volledige AI-resultaten blijven staan.',
	];
	?>
	<div id="bv-gi-scan" class="bv-gi-scan" aria-live="polite">
		<div class="bv-gi-scan__head">
			<h2>Scan</h2>
			<span class="bv-gi-scan__state" data-k="state">Laden...</span>
		</div>
		<p class="bv-gi-scan__next">Volgende automatische scan: <strong data-k="next"></strong>. Het maandbericht volgt automatisch na die scan.</p>
		<div class="bv-gi-scan__bar">
			<div class="bv-gi-scan__label"><span>Websites</span><span data-k="web-count"></span></div>
			<div class="bv-gi-scan__track"><div class="bv-gi-scan__fill" data-k="web-fill"></div></div>
			<div class="bv-gi-scan__sub" data-k="web-sub"></div>
		</div>
		<div class="bv-gi-scan__bar">
			<div class="bv-gi-scan__label"><span>AI-vragen</span><span data-k="ai-count"></span></div>
			<div class="bv-gi-scan__track"><div class="bv-gi-scan__fill bv-gi-scan__fill--ai" data-k="ai-fill"></div></div>
			<div class="bv-gi-scan__sub" data-k="ai-sub"></div>
		</div>
		<ul class="bv-gi-scan__engines" data-k="engines"></ul>
		<div class="bv-gi-scan__report" data-k="report"></div>
		<p class="bv-gi-scan__actions">
			<?php if ( $ai ) : ?>
				<button type="button" class="button button-primary" data-act="start-ai">Volledige scan starten</button>
			<?php endif; ?>
			<button type="button" class="button<?php echo $ai ? '' : ' button-primary'; ?>" data-act="start-web">Alleen websites scannen (gratis)</button>
			<button type="button" class="button" data-act="stop" hidden>Scan stoppen</button>
		</p>
		<p class="description">Laat deze pagina open tijdens een handmatige scan: dan gaat hij stap voor stap door, ook als er geen bezoekers zijn. Sluit je de pagina, dan loopt de scan verder via WordPress zelf.</p>
	</div>
	<style>
		.bv-gi-scan { max-width: 760px; margin: 16px 0 24px; padding: 16px 20px; background: #fff; border: 1px solid #dcdcde; border-radius: 8px; }
		.bv-gi-scan__head { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
		.bv-gi-scan__head h2 { margin: 0; }
		.bv-gi-scan__state { padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; background: #f0f0f1; color: #50575e; }
		.bv-gi-scan__state.is-running { background: #e7f5fe; color: #0a4b78; }
		.bv-gi-scan__state.is-done { background: #edfaef; color: #00450c; }
		.bv-gi-scan__bar { margin: 14px 0 0; }
		.bv-gi-scan__label { display: flex; justify-content: space-between; font-weight: 600; margin-bottom: 6px; }
		.bv-gi-scan__track { height: 12px; background: #f0f0f1; border-radius: 999px; overflow: hidden; }
		.bv-gi-scan__fill { height: 100%; width: 0; background: #2271b1; border-radius: 999px; transition: width .4s ease; }
		.bv-gi-scan__fill--ai { background: #1e8c7e; }
		.bv-gi-scan__fill.is-active { background-image: linear-gradient(45deg, rgba(255,255,255,.25) 25%, transparent 25%, transparent 50%, rgba(255,255,255,.25) 50%, rgba(255,255,255,.25) 75%, transparent 75%); background-size: 16px 16px; animation: bv-gi-stripes 1s linear infinite; }
		@keyframes bv-gi-stripes { to { background-position: 16px 0; } }
		@media (prefers-reduced-motion: reduce) { .bv-gi-scan__fill.is-active { animation: none; } }
		.bv-gi-scan__sub { margin-top: 4px; color: #50575e; font-size: 13px; }
		.bv-gi-scan__engines { margin: 14px 0 0; padding: 0; list-style: none; display: grid; gap: 4px; }
		.bv-gi-scan__engines li { font-size: 13px; }
		.bv-gi-scan__engines .is-bad { color: #b32d2e; }
		.bv-gi-scan__report { margin-top: 12px; font-size: 13px; }
		.bv-gi-scan__actions { display: flex; flex-wrap: wrap; gap: 8px; margin: 16px 0 8px; }
	</style>
	<script>
	(function () {
		var cfg = <?php echo wp_json_encode( $cfg ); ?>;
		var root = document.getElementById('bv-gi-scan');
		var el = function (k) { return root.querySelector('[data-k="' + k + '"]'); };
		var stepping = false, timer = null;
		function post(action, extra) {
			var fd = new FormData();
			fd.append('action', action);
			fd.append('_ajax_nonce', cfg.nonce);
			Object.keys(extra || {}).forEach(function (k) { fd.append(k, extra[k]); });
			return fetch(cfg.ajax, { method: 'POST', credentials: 'same-origin', body: fd }).then(function (r) { return r.json(); });
		}
		function text(node, value) { node.textContent = value; }
		function render(s) {
			var w = s.web, a = s.ai;
			root.classList.toggle('is-running', s.running);
			var state = el('state');
			state.className = 'bv-gi-scan__state' + (s.running ? ' is-running' : ' is-done');
			text(state, s.running ? 'Bezig' + (s.source === 'monthly' ? ' (maandelijkse scan)' : '') : 'Geen scan bezig');
			text(el('next'), s.next);
			text(el('web-count'), w.done + ' / ' + w.total);
			el('web-fill').style.width = (w.total ? Math.round(100 * w.done / w.total) : 0) + '%';
			el('web-fill').classList.toggle('is-active', w.running);
			el('ai-fill').classList.toggle('is-active', !!(a.active && a.running));
			text(el('web-sub'), w.running ? 'Nu: ' + (w.current || 'volgende stap wordt ingepland') : (w.completed ? (w.stopped ? 'Gestopt op ' : 'Klaar op ') + w.completed : 'Nog niet gemeten'));
			if (!a.active) {
				text(el('ai-count'), 'uit');
				el('ai-fill').style.width = '0%';
				text(el('ai-sub'), 'AI-meting staat uit of er is geen sleutel ingesteld.');
			} else {
				text(el('ai-count'), a.done + ' / ' + a.total);
				el('ai-fill').style.width = (a.total ? Math.round(100 * a.done / a.total) : 0) + '%';
				text(el('ai-sub'), a.running ? 'Nu: ' + (a.current || 'volgende vraag wordt ingepland') : (a.completed ? (a.stopped ? 'Gestopt op ' : 'Klaar op ') + a.completed : 'Nog geen AI-meting'));
			}
			var ul = el('engines');
			ul.innerHTML = '';
			(a.engines || []).forEach(function (e) {
				var li = document.createElement('li');
				var bad = e.skipped || (e.failed && !e.ok);
				li.className = bad ? 'is-bad' : '';
				li.textContent = e.label + ': ' + e.ok + ' goed' + (e.failed ? ', ' + e.failed + ' fout' : '') + ' · deze maand ' + e.used + ' / ' + e.cap + ' vragen' + (e.skipped ? ' · overgeslagen voor deze scan' : '') + (e.error && bad ? ' · ' + e.error : '');
				ul.appendChild(li);
			});
			var rep = el('report');
			rep.innerHTML = '';
			var p = document.createElement('div');
			p.textContent = 'Maandbericht: ' + (s.report.mode === 'publish' ? 'automatisch publiceren' : (s.report.mode === 'draft' ? 'als concept' : 'uit')) + (s.report.links.length ? '. Laatste:' : '. Nog geen bericht.');
			rep.appendChild(p);
			s.report.links.forEach(function (l) {
				var a2 = document.createElement('a');
				a2.href = l.url; a2.textContent = l.label + ' (' + l.status + ')';
				a2.style.display = 'block';
				rep.appendChild(a2);
			});
			root.querySelectorAll('[data-act^="start"]').forEach(function (b) { b.disabled = s.running; });
			root.querySelector('[data-act="stop"]').hidden = !s.running;
		}
		function step() {
			timer = null;
			if (stepping) return;
			stepping = true;
			post('bv_gi_scan_step').then(function (r) {
				stepping = false;
				if (!r || !r.success) return;
				render(r.data);
				if (r.data.running) timer = setTimeout(step, r.data.did ? 1500 : 8000);
			}).catch(function () { stepping = false; timer = setTimeout(step, 15000); });
		}
		function refresh() {
			post('bv_gi_scan_status').then(function (r) {
				if (!r || !r.success) return;
				render(r.data);
				if (r.data.running && !timer && !stepping) step();
			});
		}
		root.addEventListener('click', function (ev) {
			var b = ev.target.closest('[data-act]');
			if (!b) return;
			var act = b.getAttribute('data-act');
			if (act === 'stop') {
				if (!window.confirm(cfg.stop)) return;
				clearTimeout(timer); timer = null;
				post('bv_gi_scan_stop').then(function (r) { if (r && r.success) render(r.data); });
				return;
			}
			if (act === 'start-ai' && !window.confirm(cfg.confirm)) return;
			b.disabled = true;
			post('bv_gi_scan_start', { ai: act === 'start-ai' ? 1 : 0 }).then(function (r) {
				if (r && r.success) { render(r.data); clearTimeout(timer); timer = null; step(); }
				else { b.disabled = false; window.alert((r && r.data && r.data.message) || 'Starten lukte niet.'); }
			});
		});
		refresh();
		setInterval(function () { if (!stepping) refresh(); }, 20000);
	})();
	</script>
	<?php
} );
