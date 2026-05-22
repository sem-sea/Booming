<?php
/**
 * CiteLeap , settings + admin shell.
 *
 * @package CiteLeap
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', function () {
	add_menu_page(
		__( 'CiteLeap', 'citeleap' ),
		__( 'CiteLeap', 'citeleap' ),
		CiteLeap_Caps::USE_CAP,
		'citeleap',
		'citeleap_render_admin',
		'dashicons-edit-page',
		30
	);
} );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( strpos( (string) $hook, 'citeleap' ) === false ) return;
	$css = CITELEAP_URL . 'assets/admin.css';
	wp_enqueue_style( 'citeleap-admin', $css, [], (string) filemtime( CITELEAP_DIR . 'assets/admin.css' ) );
	$js = CITELEAP_URL . 'assets/admin.js';
	if ( file_exists( CITELEAP_DIR . 'assets/admin.js' ) ) {
		wp_enqueue_script( 'citeleap-admin', $js, [], (string) filemtime( CITELEAP_DIR . 'assets/admin.js' ), true );
	}
} );

function citeleap_render_admin(): void {
	CiteLeap_Caps::guard_use();
	$tab = isset( $_GET['tab'] ) ? sanitize_key( (string) $_GET['tab'] ) : 'dashboard';
	$tab = in_array( $tab, [ 'dashboard', 'settings', 'planner', 'calendar', 'prompts', 'images', 'seo', 'research', 'languages', 'license', 'log' ], true ) ? $tab : 'dashboard';
	/* Editors can use planner / calendar / dashboard / log; everything
	 * else is for administrators. */
	$manage_only_tabs = [ 'settings', 'prompts', 'images', 'seo', 'research', 'languages', 'license' ];
	if ( in_array( $tab, $manage_only_tabs, true ) && ! CiteLeap_Caps::can_manage() ) {
		$tab = 'dashboard';
	}
	?>
	<div class="wrap">
		<h1><?php echo esc_html__( 'CiteLeap', 'citeleap' ); ?> <span style="font-size:0.6em;color:#64748b;font-weight:normal;">v<?php echo esc_html( CITELEAP_VERSION ); ?></span></h1>

		<?php citeleap_render_flash(); ?>

		<?php
		$tabs = [
			'dashboard' => [ 'label' => __( 'Dashboard', 'citeleap' ),         'manage_only' => false ],
			'planner'   => [ 'label' => __( 'Planner', 'citeleap' ),           'manage_only' => false ],
			'calendar'  => [ 'label' => __( 'Calendar', 'citeleap' ),          'manage_only' => false ],
			'prompts'   => [ 'label' => __( 'Prompts', 'citeleap' ),           'manage_only' => true  ],
			'research'  => [ 'label' => __( 'Research', 'citeleap' ),          'manage_only' => true  ],
			'images'    => [ 'label' => __( 'Images', 'citeleap' ),            'manage_only' => true  ],
			'seo'       => [ 'label' => __( 'SEO', 'citeleap' ),               'manage_only' => true  ],
			'languages' => [ 'label' => __( 'Languages', 'citeleap' ),         'manage_only' => true  ],
			'settings'  => [ 'label' => __( 'Settings', 'citeleap' ),          'manage_only' => true  ],
			'license'   => [ 'label' => __( 'License & Credits', 'citeleap' ), 'manage_only' => true  ],
			'log'       => [ 'label' => __( 'Log', 'citeleap' ),               'manage_only' => false ],
		];
		$can_manage = CiteLeap_Caps::can_manage();
		?>
		<nav class="nav-tab-wrapper" style="margin-top:1rem;" aria-label="<?php echo esc_attr__( 'CiteLeap sections', 'citeleap' ); ?>">
			<?php foreach ( $tabs as $slug => $t ) :
				if ( $t['manage_only'] && ! $can_manage ) continue;
				$is_active = ( $slug === $tab );
			?>
				<a class="nav-tab <?php echo $is_active ? 'nav-tab-active' : ''; ?>"
					href="<?php echo esc_url( admin_url( 'admin.php?page=citeleap&tab=' . $slug ) ); ?>"
					<?php echo $is_active ? 'aria-current="page"' : ''; ?>>
					<?php echo esc_html( $t['label'] ); ?>
				</a>
			<?php endforeach; ?>
		</nav>

		<div style="background:#fff;padding:1.25rem 1.5rem;border:1px solid #e2e8f0;border-top:0;">
			<?php
			switch ( $tab ) {
				case 'dashboard':  CiteLeap_Dashboard::render(); break;
				case 'planner':    CiteLeap_Planner::render(); break;
				case 'calendar':   CiteLeap_Calendar::render(); break;
				case 'prompts':    citeleap_render_prompts(); break;
				case 'images':     citeleap_render_images_tab(); break;
				case 'seo':        citeleap_render_seo_tab(); break;
				case 'research':   citeleap_render_research_tab(); break;
				case 'languages':  citeleap_render_languages_tab(); break;
				case 'license':    citeleap_render_license_tab(); break;
				case 'log':        citeleap_render_log(); break;
				default:           citeleap_render_settings();
			}
			?>
		</div>
	</div>
	<?php
}

function citeleap_render_flash(): void {
	if ( empty( $_GET['citeleap_msg'] ) ) return;
	$msg = sanitize_text_field( wp_unslash( (string) $_GET['citeleap_msg'] ) );
	$kind = 'success';
	$text = $msg;
	if ( 0 === strpos( $msg, 'test-ok:' ) ) {
		$parts = explode( ':', $msg, 3 );
		$text = sprintf( __( '%s connection OK , model replied: %s', 'citeleap' ), ucfirst( (string) ( $parts[1] ?? '' ) ), rawurldecode( (string) ( $parts[2] ?? '' ) ) );
	} elseif ( 0 === strpos( $msg, 'test-err:' ) ) {
		$parts = explode( ':', $msg, 3 );
		$kind  = 'error';
		$text  = sprintf( __( '%s connection failed: %s', 'citeleap' ), ucfirst( (string) ( $parts[1] ?? '' ) ), rawurldecode( (string) ( $parts[2] ?? '' ) ) );
	} elseif ( 0 === strpos( $msg, 'refresh-err' ) ) {
		$kind = 'error'; $text = __( 'Refresh failed , see the Log tab for details.', 'citeleap' );
	} elseif ( 'saved' === $msg ) {
		$text = __( 'Saved.', 'citeleap' );
	} elseif ( 0 === strpos( $msg, 'topics:' ) ) {
		$parts = explode( ':', $msg );
		$added = (int) ( $parts[1] ?? 0 );
		$dupes = (int) ( $parts[2] ?? 0 );
		$total = (int) ( $parts[3] ?? 0 );
		$text  = sprintf( __( 'Queued %d topic(s) from %d submitted. Skipped %d duplicate(s).', 'citeleap' ), $added, $total, $dupes );
	} elseif ( 0 === strpos( $msg, 'bulk-refresh:' ) ) {
		$parts     = explode( ':', $msg );
		$added     = (int) ( $parts[1] ?? 0 );
		$dupes     = (int) ( $parts[2] ?? 0 );
		$not_found = (int) ( $parts[3] ?? 0 );
		$total     = (int) ( $parts[4] ?? 0 );
		$text      = sprintf( __( 'Queued %d post(s) for refresh from %d submitted. Skipped %d duplicate(s), could not resolve %d line(s).', 'citeleap' ), $added, $total, $dupes, $not_found );
	} elseif ( 0 === strpos( $msg, 'refresh-approved:' ) ) {
		$parts = explode( ':', $msg );
		$text  = sprintf( __( 'Refresh approved and applied to post #%d.', 'citeleap' ), (int) ( $parts[1] ?? 0 ) );
	} elseif ( 'refresh-rejected' === $msg ) {
		$text = __( 'Refresh rejected. Live post unchanged.', 'citeleap' );
	} elseif ( 'reset' === $msg ) {
		$text = __( 'Stuck refresh reset to failed. You can retry it.', 'citeleap' );
	} elseif ( 'pinned' === $msg ) {
		$text = __( 'Planned. The auto-tick will draft and publish this topic at exactly that date and time.', 'citeleap' );
	} elseif ( 0 === strpos( $msg, 'distributed:' ) ) {
		$parts = explode( ':', $msg );
		$n     = (int) ( $parts[1] ?? 0 );
		$text  = sprintf( __( 'Spread %d queued topic(s) evenly across the calendar.', 'citeleap' ), $n );
	} elseif ( 0 === strpos( $msg, 'topup-ok:' ) ) {
		$parts   = explode( ':', $msg, 3 );
		$label   = rawurldecode( (string) ( $parts[1] ?? '' ) );
		$credits = (int) ( $parts[2] ?? 0 );
		$text    = sprintf( __( '%1$s purchased , %2$d credits added to your reserve.', 'citeleap' ), $label, $credits );
	} elseif ( 'topup-unknown' === $msg ) {
		$kind = 'error'; $text = __( 'Top-up pack not recognised.', 'citeleap' );
	} elseif ( 'topup-blocked' === $msg ) {
		$kind = 'error'; $text = __( 'Simulator blocked: Freemius is live. Use the real checkout to buy credits.', 'citeleap' );
	} elseif ( 0 === strpos( $msg, 'action-' ) ) {
		$labels = [
			'action-pause'           => __( 'Paused. Cron tick will skip this row until you resume.', 'citeleap' ),
			'action-resume'          => __( 'Resumed. Cron tick will pick this row up again on the next pass.', 'citeleap' ),
			'action-publish_now'     => __( 'Published immediately.', 'citeleap' ),
			'action-unschedule'      => __( 'Unscheduled. Post is back to draft state.', 'citeleap' ),
			'action-retry'           => __( 'Retry queued.', 'citeleap' ),
			'action-scheduled'       => __( 'Draft scheduled to publish at the picked datetime.', 'citeleap' ),
			'action-rescheduled'     => __( 'Rescheduled to the new datetime.', 'citeleap' ),
		];
		$text = $labels[ $msg ] ?? __( 'Action completed.', 'citeleap' );
		if ( 'action-err' === $msg ) { $kind = 'error'; $text = __( 'Action failed. Check the log.', 'citeleap' ); }
	}
	$class     = ( 'error' === $kind ) ? 'notice-error' : 'notice-success';
	$role      = ( 'error' === $kind ) ? 'alert' : 'status';
	$aria_live = ( 'error' === $kind ) ? 'assertive' : 'polite';
	$sr_prefix = ( 'error' === $kind ) ? __( 'Error: ', 'citeleap' ) : __( 'Success: ', 'citeleap' );
	echo '<div class="notice ' . esc_attr( $class ) . ' is-dismissible" role="' . esc_attr( $role ) . '" aria-live="' . esc_attr( $aria_live ) . '" style="margin-top:1rem;"><p><span class="screen-reader-text">' . esc_html( $sr_prefix ) . '</span>' . esc_html( $text ) . '</p></div>';
}

function citeleap_render_settings(): void {
	$keys     = (array) get_option( CITELEAP_OPTION_API_KEYS, [] );
	$models   = (array) get_option( CITELEAP_OPTION_MODELS, [] );
	$schedule = (array) get_option( CITELEAP_OPTION_SCHEDULE, [] );
	$defaults = CiteLeap_LLM::defaults();
	?>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( CITELEAP_NONCE ); ?>
		<input type="hidden" name="action" value="citeleap_save_settings">

		<h2><?php echo esc_html__( 'API keys', 'citeleap' ); ?></h2>
		<p style="color:#64748b;"><?php echo esc_html__( 'Bring your own keys. Encrypted at rest with AES-256-CBC using your AUTH_KEY salt. Leave a field blank to keep the existing key.', 'citeleap' ); ?></p>
		<table class="form-table">
			<?php foreach ( [ 'claude' => 'Anthropic Claude', 'openai' => 'OpenAI', 'gemini' => 'Google Gemini' ] as $p => $label ) :
				$decrypted = CiteLeap_LLM::get_api_key( $p );
				$mask      = CiteLeap_Crypto::mask( $decrypted );
				$placeholder = $decrypted ? sprintf( __( 'Keep current key (%s)', 'citeleap' ), $mask ) : __( 'Paste your key', 'citeleap' );
			?>
				<tr>
					<th><label for="<?php echo esc_attr( $p ); ?>_key"><?php echo esc_html( $label ); ?></label></th>
					<td>
						<input type="password" id="<?php echo esc_attr( $p ); ?>_key" name="<?php echo esc_attr( $p ); ?>_key" value="" class="regular-text" autocomplete="new-password" placeholder="<?php echo esc_attr( $placeholder ); ?>">
						<?php if ( $decrypted ) : ?>
							<button type="submit" name="action" value="citeleap_test_key_<?php echo esc_attr( $p ); ?>" formaction="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="button button-small" formnovalidate>
								<?php echo esc_html__( 'Test connection', 'citeleap' ); ?>
							</button>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</table>

		<h2 style="margin-top:1.5rem;"><?php echo esc_html__( 'Model selection', 'citeleap' ); ?></h2>
		<p style="color:#64748b;"><?php echo esc_html__( 'Pick the provider per role + the model within that provider. Reasoning = idea brainstorm. Writing = full draft.', 'citeleap' ); ?></p>
		<table class="form-table">
			<?php foreach ( [
				'reasoning' => __( 'Reasoning model (idea brainstorming, planning)', 'citeleap' ),
				'research'  => __( 'Research model (real web search, sources, citations)', 'citeleap' ),
				'writing'   => __( 'Writing model (full long-form draft)', 'citeleap' ),
			] as $role => $label ) : ?>
				<tr>
					<th><label><?php echo esc_html( $label ); ?></label></th>
					<td>
						<select name="provider_<?php echo esc_attr( $role ); ?>" data-role="<?php echo esc_attr( $role ); ?>">
							<?php foreach ( $defaults['providers'] as $p ) :
								$selected = ( $models[ 'provider_' . $role ] ?? 'claude' ) === $p; ?>
								<option value="<?php echo esc_attr( $p ); ?>" <?php selected( $selected ); ?>><?php echo esc_html( ucfirst( $p ) ); ?></option>
							<?php endforeach; ?>
						</select>
						&nbsp;
						<?php foreach ( $defaults['providers'] as $p ) :
							$current = $models[ $p ][ $role ] ?? $defaults['models'][ $p ][ $role ]; ?>
							<select name="model_<?php echo esc_attr( $p ); ?>_<?php echo esc_attr( $role ); ?>" data-provider="<?php echo esc_attr( $p ); ?>" data-role="<?php echo esc_attr( $role ); ?>">
								<?php foreach ( $defaults['available_models'][ $p ] as $m ) : ?>
									<option value="<?php echo esc_attr( $m ); ?>" <?php selected( $current, $m ); ?>><?php echo esc_html( $m ); ?></option>
								<?php endforeach; ?>
							</select>
						<?php endforeach; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</table>

		<h2 style="margin-top:1.5rem;"><?php echo esc_html__( 'Auto-publish schedule', 'citeleap' ); ?><?php if ( ! CiteLeap_Plan::has( 'auto_publish' ) ) CiteLeap_Plan::render_inline_nudge( 'auto_publish' ); ?></h2>
		<table class="form-table">
			<tr>
				<th><label for="auto_mode"><?php echo esc_html__( 'Auto mode', 'citeleap' ); ?></label></th>
				<td>
					<?php $auto_mode = (string) ( $schedule['auto_mode'] ?? ( ! empty( $schedule['auto'] ) ? 'publish' : 'off' ) ); ?>
					<select id="auto_mode" name="auto_mode"<?php echo CiteLeap_Plan::has( 'auto_publish' ) ? '' : ' disabled'; ?>>
						<option value="off"     <?php selected( $auto_mode, 'off' );     ?>><?php echo esc_html__( 'Off (manual only)', 'citeleap' ); ?></option>
						<option value="draft"   <?php selected( $auto_mode, 'draft' );   ?>><?php echo esc_html__( 'Auto-generate to DRAFT only (you publish manually)', 'citeleap' ); ?></option>
						<option value="publish" <?php selected( $auto_mode, 'publish' ); ?>><?php echo esc_html__( 'Auto-generate + auto-publish on schedule', 'citeleap' ); ?></option>
					</select>
					<p class="description"><?php echo esc_html__( 'Draft mode generates and refills the queue continuously but leaves every post as a draft for your review. Publish mode also schedules each draft to go live at the next slot.', 'citeleap' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="start_date"><?php echo esc_html__( 'Start date', 'citeleap' ); ?></label></th>
				<td><input type="datetime-local" id="start_date" name="start_date" value="<?php echo esc_attr( (string) ( $schedule['start_date'] ?? '' ) ); ?>"></td>
			</tr>
			<tr>
				<th><label for="posts_per_unit"><?php echo esc_html__( 'Cadence', 'citeleap' ); ?></label></th>
				<td>
					<input type="number" id="posts_per_unit" name="posts_per_unit" min="1" max="365" value="<?php echo (int) ( $schedule['posts_per_unit'] ?? ( $schedule['posts_per_week'] ?? 3 ) ); ?>" style="width:5rem;"> posts per
					<select name="cadence_unit" style="margin-left:0.25rem;">
						<?php $cu = (string) ( $schedule['cadence_unit'] ?? 'week' ); ?>
						<option value="day"       <?php selected( $cu, 'day' );       ?>><?php echo esc_html__( 'day',        'citeleap' ); ?></option>
						<option value="week"      <?php selected( $cu, 'week' );      ?>><?php echo esc_html__( 'week',       'citeleap' ); ?></option>
						<option value="month"     <?php selected( $cu, 'month' );     ?>><?php echo esc_html__( 'month',      'citeleap' ); ?></option>
						<option value="half_year" <?php selected( $cu, 'half_year' ); ?>><?php echo esc_html__( 'half year',  'citeleap' ); ?></option>
						<option value="year"      <?php selected( $cu, 'year' );      ?>><?php echo esc_html__( 'year',       'citeleap' ); ?></option>
					</select>
					<p class="description"><?php echo esc_html__( 'The scheduler spreads posts evenly across this period. Example: 30 posts per month publishes one every 24 hours; 6 posts per year publishes one every 2 months.', 'citeleap' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="publish_hour"><?php echo esc_html__( 'Publish hour (local time)', 'citeleap' ); ?></label></th>
				<td>
					<select id="publish_hour" name="publish_hour">
						<?php $ph = (int) ( $schedule['publish_hour'] ?? 10 );
						for ( $h = 0; $h < 24; $h++ ) : ?>
							<option value="<?php echo $h; ?>" <?php selected( $ph, $h ); ?>><?php echo sprintf( '%02d:00', $h ); ?></option>
						<?php endfor; ?>
					</select>
					<p class="description"><?php echo esc_html__( 'Each auto-planned slot snaps to this hour in your local timezone.', 'citeleap' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="audience"><?php echo esc_html__( 'Audience', 'citeleap' ); ?></label></th>
				<td><input type="text" id="audience" name="audience" class="regular-text" value="<?php echo esc_attr( (string) ( $schedule['audience'] ?? '' ) ); ?>" placeholder="B2B CMOs and growth leads"></td>
			</tr>
			<tr>
				<th><label for="topics"><?php echo esc_html__( 'Seed topics', 'citeleap' ); ?></label></th>
				<td>
					<textarea id="topics" name="topics" rows="4" class="large-text" placeholder="AI marketing, conversion optimization, lead generation, paid media, content distribution"><?php echo esc_textarea( (string) ( $schedule['topics'] ?? '' ) ); ?></textarea>
					<p class="description"><?php echo esc_html__( 'Comma-separated topic anchors. The reasoning model uses these to brainstorm fresh ideas.', 'citeleap' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="internal_links"><?php echo esc_html__( 'Internal-link allowlist', 'citeleap' ); ?></label></th>
				<td><input type="text" id="internal_links" name="internal_links" class="large-text" value="<?php echo esc_attr( (string) ( $schedule['internal_links'] ?? '/services/, /#contact, /blog/' ) ); ?>" placeholder="/services/, /#contact, /blog/"></td>
			</tr>
		</table>

		<h2 style="margin-top:1.5rem;"><?php echo esc_html__( 'Monthly budget caps (USD)', 'citeleap' ); ?></h2>
		<p style="color:#64748b;"><?php echo esc_html__( 'Hard cap per provider for the current calendar month. 0 = unlimited. Generation requests are refused (and logged) once a cap is reached. Resets automatically on the 1st.', 'citeleap' ); ?></p>
		<?php $caps = CiteLeap_Usage::caps(); ?>
		<table class="form-table">
			<?php foreach ( [ 'claude' => 'Anthropic Claude', 'openai' => 'OpenAI', 'gemini' => 'Google Gemini', 'overall' => __( 'Overall cap (all providers)', 'citeleap' ) ] as $k => $label ) : ?>
				<tr>
					<th><label for="cap_<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $label ); ?></label></th>
					<td>$ <input type="number" min="0" step="0.01" id="cap_<?php echo esc_attr( $k ); ?>" name="cap_<?php echo esc_attr( $k ); ?>" value="<?php echo esc_attr( (string) $caps[ $k ] ); ?>" style="width:9rem;"></td>
				</tr>
			<?php endforeach; ?>
		</table>

		<h2 style="margin-top:1.5rem;"><?php echo esc_html__( 'Refresh existing content', 'citeleap' ); ?><?php if ( ! CiteLeap_Plan::has( 'refresh' ) ) CiteLeap_Plan::render_inline_nudge( 'refresh' ); ?></h2>
		<p style="color:#64748b;"><?php echo esc_html__( 'Periodically refresh older posts. Posts queue from the Planner tab (or auto-pick the oldest-modified once cadence is met). Existing slug, ID, date, comments, and meta are preserved.', 'citeleap' ); ?></p>
		<?php $r = CiteLeap_Refresh::settings(); ?>
		<table class="form-table">
			<tr>
				<th><label for="refresh_auto_mode"><?php echo esc_html__( 'Refresh mode', 'citeleap' ); ?></label></th>
				<td>
					<select id="refresh_auto_mode" name="refresh_auto_mode"<?php echo CiteLeap_Plan::has( 'refresh' ) ? '' : ' disabled'; ?>>
						<option value="off"   <?php selected( $r['auto_mode'], 'off' );   ?>><?php echo esc_html__( 'Off (manual only)', 'citeleap' ); ?></option>
						<option value="draft" <?php selected( $r['auto_mode'], 'draft' ); ?>><?php echo esc_html__( 'Auto-research to PENDING REVIEW (you approve before live)', 'citeleap' ); ?></option>
						<option value="live"  <?php selected( $r['auto_mode'], 'live' );  ?>><?php echo esc_html__( 'Auto-research and OVERWRITE the live post directly', 'citeleap' ); ?></option>
					</select>
					<p class="description"><?php echo esc_html__( 'Draft mode stores the refreshed content as a pending review on the post. The live post is untouched until you click Approve. Live mode overwrites immediately.', 'citeleap' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="refresh_cadence_days"><?php echo esc_html__( 'Refresh cadence (days)', 'citeleap' ); ?></label></th>
				<td><input type="number" id="refresh_cadence_days" name="refresh_cadence_days" min="7" max="365" value="<?php echo (int) $r['cadence_days']; ?>"> <span class="description"><?php echo esc_html__( 'A post is "due" if its last-modified date is older than this.', 'citeleap' ); ?></span></td>
			</tr>
			<tr>
				<th><label><?php echo esc_html__( 'Refresh cadence', 'citeleap' ); ?></label></th>
				<td>
					<input type="number" name="refresh_posts_per_unit" min="1" max="365" value="<?php echo (int) ( $r['posts_per_unit'] ?? ( $r['posts_per_week'] ?? 2 ) ); ?>" style="width:5rem;"> posts per
					<select name="refresh_cadence_unit" style="margin-left:0.25rem;">
						<?php $rcu = (string) ( $r['cadence_unit'] ?? 'week' ); ?>
						<option value="day"       <?php selected( $rcu, 'day' );       ?>><?php echo esc_html__( 'day',        'citeleap' ); ?></option>
						<option value="week"      <?php selected( $rcu, 'week' );      ?>><?php echo esc_html__( 'week',       'citeleap' ); ?></option>
						<option value="month"     <?php selected( $rcu, 'month' );     ?>><?php echo esc_html__( 'month',      'citeleap' ); ?></option>
						<option value="half_year" <?php selected( $rcu, 'half_year' ); ?>><?php echo esc_html__( 'half year',  'citeleap' ); ?></option>
						<option value="year"      <?php selected( $rcu, 'year' );      ?>><?php echo esc_html__( 'year',       'citeleap' ); ?></option>
					</select>
				</td>
			</tr>
		</table>

		<p style="margin-top:1rem;"><button class="button button-primary"><?php echo esc_html__( 'Save settings', 'citeleap' ); ?></button></p>
	</form>
	<?php
}

function citeleap_render_prompts(): void {
	$prompts = (array) get_option( CITELEAP_OPTION_PROMPTS, [] );
	$master  = (string) ( $prompts['master_prompt'] ?? citeleap_default_master_prompt() );
	$idea    = (string) ( $prompts['idea_prompt']   ?? citeleap_idea_prompt_template() );
	$custom  = (string) ( $prompts['custom_prompt'] ?? '' );
	?>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( CITELEAP_NONCE ); ?>
		<input type="hidden" name="action" value="citeleap_save_prompts">

		<h2><?php echo esc_html__( 'Custom additional instructions', 'citeleap' ); ?></h2>
		<p style="color:#64748b;"><?php echo esc_html__( 'Bolted on to BOTH the idea prompt and the writing prompt. Use for site-specific tone, banned phrases, named tools to favour, etc.', 'citeleap' ); ?></p>
		<textarea name="custom_prompt" rows="6" class="large-text" placeholder="<?php echo esc_attr__( 'e.g. Always reference the Funnel Leak Calculator. Never mention competitors by name.', 'citeleap' ); ?>"><?php echo esc_textarea( $custom ); ?></textarea>

		<h2 style="margin-top:1.5rem;"><?php echo esc_html__( 'Master writing prompt', 'citeleap' ); ?></h2>
		<p style="color:#64748b;"><?php echo esc_html__( 'The full system prompt for the writing model. Tokens like {site_name}, {topic}, {user_additional}, {category_list}, {internal_links} are substituted at generation time. Restore the default by clearing the field and saving.', 'citeleap' ); ?></p>
		<textarea name="master_prompt" rows="20" class="large-text code"><?php echo esc_textarea( $master ); ?></textarea>

		<h2 style="margin-top:1.5rem;"><?php echo esc_html__( 'Idea-generation prompt', 'citeleap' ); ?></h2>
		<p style="color:#64748b;"><?php echo esc_html__( 'Prompt for the reasoning model. Substitutes {site_name}, {audience}, {category_list}, {seed_topics}, {existing_slugs}, {count}, {user_additional}.', 'citeleap' ); ?></p>
		<textarea name="idea_prompt" rows="14" class="large-text code"><?php echo esc_textarea( $idea ); ?></textarea>

		<p style="margin-top:1rem;"><button class="button button-primary"><?php echo esc_html__( 'Save prompts', 'citeleap' ); ?></button></p>
	</form>
	<?php
}

function citeleap_render_log(): void {
	$log = array_reverse( (array) get_option( CITELEAP_OPTION_LOG, [] ) );
	?>
	<h2><?php echo esc_html__( 'Activity log', 'citeleap' ); ?> (<?php echo count( $log ); ?>)</h2>
	<?php if ( empty( $log ) ) : ?>
		<p><?php echo esc_html__( 'No activity yet.', 'citeleap' ); ?></p>
	<?php else : ?>
		<table class="widefat striped">
			<thead><tr><th style="width:160px;"><?php echo esc_html__( 'Time', 'citeleap' ); ?></th><th style="width:80px;"><?php echo esc_html__( 'Level', 'citeleap' ); ?></th><th style="width:200px;"><?php echo esc_html__( 'Event', 'citeleap' ); ?></th><th><?php echo esc_html__( 'Detail', 'citeleap' ); ?></th></tr></thead>
			<tbody>
			<?php foreach ( $log as $row ) :
				$sev = (string) ( $row['severity'] ?? 'info' );
				$color = [ 'info' => '#0369a1', 'warn' => '#b45309', 'error' => '#b91c1c', 'critical' => '#7f1d1d' ][ $sev ] ?? '#475569';
			?>
				<tr>
					<td><code><?php echo esc_html( (string) ( $row['time'] ?? '' ) ); ?></code></td>
					<td><span style="color:<?php echo esc_attr( $color ); ?>;font-weight:600;text-transform:uppercase;font-size:11px;"><?php echo esc_html( $sev ); ?></span></td>
					<td><?php echo esc_html( (string) ( $row['event'] ?? '' ) ); ?></td>
					<td><?php echo esc_html( (string) ( $row['detail'] ?? '' ) ); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif;
}

/* admin-post handlers */
add_action( 'admin_post_citeleap_save_settings', function () {
	CiteLeap_Caps::guard_manage();
	check_admin_referer( CITELEAP_NONCE );

	/* Encrypt API keys at rest. An empty submitted value means "keep
	 * the existing key" (because the form is masked, the operator
	 * never sees the actual value and an unintentional blank should
	 * NOT wipe a valid key). */
	$existing = (array) get_option( CITELEAP_OPTION_API_KEYS, [] );
	$keys     = [];
	foreach ( [ 'claude', 'openai', 'gemini' ] as $p ) {
		$submitted = sanitize_text_field( wp_unslash( (string) ( $_POST[ $p . '_key' ] ?? '' ) ) );
		if ( '' === $submitted ) {
			$keys[ $p ] = (string) ( $existing[ $p ] ?? '' );          // preserve
		} else {
			$keys[ $p ] = CiteLeap_Crypto::encrypt( $submitted );      // encrypt new value
		}
	}
	update_option( CITELEAP_OPTION_API_KEYS, $keys, false );

	$defaults = CiteLeap_LLM::defaults();
	$models = [
		'provider_reasoning' => sanitize_key( (string) ( $_POST['provider_reasoning'] ?? 'claude' ) ),
		'provider_research'  => sanitize_key( (string) ( $_POST['provider_research']  ?? 'claude' ) ),
		'provider_writing'   => sanitize_key( (string) ( $_POST['provider_writing']   ?? 'claude' ) ),
	];
	foreach ( $defaults['providers'] as $p ) {
		$models[ $p ] = [
			'reasoning' => sanitize_text_field( wp_unslash( (string) ( $_POST[ 'model_' . $p . '_reasoning' ] ?? $defaults['models'][ $p ]['reasoning'] ) ) ),
			'research'  => sanitize_text_field( wp_unslash( (string) ( $_POST[ 'model_' . $p . '_research'  ] ?? $defaults['models'][ $p ]['research']  ?? $defaults['models'][ $p ]['reasoning'] ) ) ),
			'writing'   => sanitize_text_field( wp_unslash( (string) ( $_POST[ 'model_' . $p . '_writing' ]   ?? $defaults['models'][ $p ]['writing'] ) ) ),
		];
	}
	update_option( CITELEAP_OPTION_MODELS, $models, false );

	$mode = sanitize_key( (string) ( $_POST['auto_mode'] ?? 'off' ) );
	if ( ! in_array( $mode, [ 'off', 'draft', 'publish' ], true ) ) $mode = 'off';
	if ( ! CiteLeap_Plan::has( 'auto_publish' ) && 'off' !== $mode ) $mode = 'off';
	$schedule = [
		'auto'           => ( 'off' !== $mode ) ? 1 : 0,    // legacy boolean kept for back-compat
		'auto_mode'      => $mode,
		'start_date'     => sanitize_text_field( wp_unslash( (string) ( $_POST['start_date'] ?? '' ) ) ),
		'posts_per_unit' => max( 1, min( 365, (int) ( $_POST['posts_per_unit'] ?? 3 ) ) ),
		'cadence_unit'   => in_array( (string) ( $_POST['cadence_unit'] ?? '' ), [ 'day', 'week', 'month', 'half_year', 'year' ], true ) ? (string) $_POST['cadence_unit'] : 'week',
		'publish_hour'   => max( 0, min( 23, (int) ( $_POST['publish_hour'] ?? 10 ) ) ),
		/* legacy back-compat , derive a posts_per_week from the new
		 * fields so v2.0/v2.1 code paths that still read it stay sane. */
		'posts_per_week' => max( 1, min( 14, (int) ceil(
			( (int) ( $_POST['posts_per_unit'] ?? 3 ) )
			* ( WEEK_IN_SECONDS / match ( (string) ( $_POST['cadence_unit'] ?? 'week' ) ) {
				'day'       => DAY_IN_SECONDS,
				'month'     => MONTH_IN_SECONDS,
				'half_year' => MONTH_IN_SECONDS * 6,
				'year'      => YEAR_IN_SECONDS,
				default     => WEEK_IN_SECONDS,
			} )
		) ) ),
		'audience'       => sanitize_text_field( wp_unslash( (string) ( $_POST['audience'] ?? '' ) ) ),
		'topics'         => sanitize_textarea_field( wp_unslash( (string) ( $_POST['topics'] ?? '' ) ) ),
		'internal_links' => sanitize_text_field( wp_unslash( (string) ( $_POST['internal_links'] ?? '' ) ) ),
	];
	update_option( CITELEAP_OPTION_SCHEDULE, $schedule, false );

	/* Budget caps. */
	update_option( CITELEAP_OPTION_CAPS, [
		'claude'  => max( 0, (float) ( $_POST['cap_claude']  ?? 0 ) ),
		'openai'  => max( 0, (float) ( $_POST['cap_openai']  ?? 0 ) ),
		'gemini'  => max( 0, (float) ( $_POST['cap_gemini']  ?? 0 ) ),
		'overall' => max( 0, (float) ( $_POST['cap_overall'] ?? 0 ) ),
	], false );

	/* Refresh module settings , server-side plan gate so a Free user
	 * posting directly can't enable a paid feature. */
	$refresh_mode = (string) ( $_POST['refresh_auto_mode'] ?? 'off' );
	if ( ! CiteLeap_Plan::has( 'refresh' ) ) $refresh_mode = 'off';
	CiteLeap_Refresh::save_settings( [
		'auto_mode'      => $refresh_mode,
		'cadence_days'   => $_POST['refresh_cadence_days']   ?? 90,
		'posts_per_unit' => $_POST['refresh_posts_per_unit'] ?? 2,
		'cadence_unit'   => $_POST['refresh_cadence_unit']   ?? 'week',
	] );

	wp_safe_redirect( add_query_arg( [ 'page' => 'citeleap', 'tab' => 'settings', 'citeleap_msg' => 'saved' ], admin_url( 'admin.php' ) ) );
	exit;
} );

/* Test-connection handlers , one per provider. Sends a minimal
 * "say hello" request and reports the round-trip success or error
 * back to the operator. Logs the result. */
foreach ( [ 'claude', 'openai', 'gemini' ] as $__p ) {
	add_action( 'admin_post_citeleap_test_key_' . $__p, function () use ( $__p ) {
		CiteLeap_Caps::guard_manage();
		check_admin_referer( CITELEAP_NONCE );
		$res = CiteLeap_LLM::chat( 'writing', 'You are a test responder.', 'Reply with the single word OK.', 16 );
		$msg = $res['ok']
			? 'test-ok:' . $__p . ':' . rawurlencode( mb_substr( trim( $res['text'] ), 0, 60 ) )
			: 'test-err:' . $__p . ':' . rawurlencode( mb_substr( $res['error'], 0, 200 ) );
		CiteLeap_Log::add(
			$res['ok'] ? 'test_ok' : 'test_failed',
			$__p . ' / ' . ( $res['model'] ?? '' ) . ' / ' . ( $res['ok'] ? trim( $res['text'] ) : $res['error'] ),
			$res['ok'] ? 'info' : 'error'
		);
		wp_safe_redirect( add_query_arg( [ 'page' => 'citeleap', 'tab' => 'settings', 'citeleap_msg' => $msg ], admin_url( 'admin.php' ) ) );
		exit;
	} );
}

add_action( 'admin_post_citeleap_save_prompts', function () {
	CiteLeap_Caps::guard_manage();
	check_admin_referer( CITELEAP_NONCE );

	$prompts = [
		'master_prompt' => (string) wp_unslash( (string) ( $_POST['master_prompt'] ?? '' ) ),
		'idea_prompt'   => (string) wp_unslash( (string) ( $_POST['idea_prompt']   ?? '' ) ),
		'custom_prompt' => (string) wp_unslash( (string) ( $_POST['custom_prompt'] ?? '' ) ),
	];
	/* Empty value -> revert to default on next read. */
	if ( '' === trim( $prompts['master_prompt'] ) ) unset( $prompts['master_prompt'] );
	if ( '' === trim( $prompts['idea_prompt'] ) )   unset( $prompts['idea_prompt'] );
	update_option( CITELEAP_OPTION_PROMPTS, $prompts, false );

	wp_safe_redirect( add_query_arg( [ 'page' => 'citeleap', 'tab' => 'prompts', 'citeleap_msg' => 'saved' ], admin_url( 'admin.php' ) ) );
	exit;
} );

/* =====================================================================
 * v2.0 tab renderers + admin-post handlers
 * ===================================================================== */

function citeleap_render_research_tab(): void {
	$r = CiteLeap_Research::settings();
	$decrypted_key = CiteLeap_Research::decrypt_key();
	$mask = $decrypted_key ? CiteLeap_Crypto::mask( $decrypted_key ) : '';
	?>
	<h2><?php echo esc_html__( 'Research module', 'citeleap' ); ?></h2>
	<p style="color:#64748b;"><?php echo esc_html__( 'The writer model uses real research with named-source citations. Two paths: (1) Claude\'s native web-search tool , no extra key, no extra config , just keep Provider = "claude_native" and pick claude-sonnet-4-6 or claude-opus-4-7 as your writing model. (2) An operator-supplied SERP API key (Serper.dev / Brave Search / Tavily) for OpenAI or Gemini writing.', 'citeleap' ); ?></p>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( CITELEAP_NONCE ); ?>
		<input type="hidden" name="action" value="citeleap_save_research">
		<table class="form-table">
			<tr><th><label for="enabled"><?php echo esc_html__( 'Research enabled', 'citeleap' ); ?></label></th>
				<td><label><input type="checkbox" name="enabled" value="1" <?php checked( $r['enabled'] ); ?>> <?php echo esc_html__( 'Off = no research, generic prose. On = real sources with named-source citations.', 'citeleap' ); ?></label></td></tr>
			<tr><th><label for="provider"><?php echo esc_html__( 'Research provider', 'citeleap' ); ?></label></th>
				<td>
					<select name="provider">
						<option value="claude_native" <?php selected( $r['provider'], 'claude_native' ); ?>>Claude native web search (recommended, no API key)</option>
						<option value="serper" <?php selected( $r['provider'], 'serper' ); ?>>Serper.dev (Google SERP)</option>
						<option value="brave"  <?php selected( $r['provider'], 'brave' );  ?>>Brave Search API</option>
						<option value="tavily" <?php selected( $r['provider'], 'tavily' ); ?>>Tavily</option>
						<option value="off"    <?php selected( $r['provider'], 'off' );    ?>>Off</option>
					</select>
				</td></tr>
			<tr><th><label for="api_key"><?php echo esc_html__( 'SERP API key (only for serper / brave / tavily)', 'citeleap' ); ?></label></th>
				<td><input type="password" name="api_key" class="regular-text" autocomplete="new-password" placeholder="<?php echo $mask ? esc_attr( sprintf( __( 'Keep current (%s)', 'citeleap' ), $mask ) ) : esc_attr__( 'Paste your key', 'citeleap' ); ?>"><br>
				<span class="description"><?php echo esc_html__( 'Stored encrypted (AES-256-CBC, AUTH_KEY-derived). Leave blank to keep the existing key.', 'citeleap' ); ?></span></td></tr>
			<tr><th><label for="max_searches"><?php echo esc_html__( 'Max searches per post', 'citeleap' ); ?></label></th>
				<td><input type="number" name="max_searches" value="<?php echo (int) $r['max_searches']; ?>" min="1" max="10"></td></tr>
			<tr><th><label for="min_citations"><?php echo esc_html__( 'Min source citations per post', 'citeleap' ); ?></label></th>
				<td><input type="number" name="min_citations" value="<?php echo (int) $r['min_citations']; ?>" min="1" max="10"></td></tr>
			<tr><th><label for="min_internal"><?php echo esc_html__( 'Min internal links per post', 'citeleap' ); ?></label></th>
				<td><input type="number" name="min_internal" value="<?php echo (int) $r['min_internal']; ?>" min="0" max="10"></td></tr>
		</table>
		<p><button class="button button-primary"><?php echo esc_html__( 'Save research settings', 'citeleap' ); ?></button></p>
	</form>
	<?php
}

function citeleap_render_images_tab(): void {
	$s             = CiteLeap_Images::settings();
	$pool_ids      = $s['pool'];
	$posts_total   = CiteLeap_Images::count_posts_total();
	$posts_without = CiteLeap_Images::count_posts_without_thumbnail();
	$random_count  = CiteLeap_Images::count_random_assigned();
	$msg           = isset( $_GET['citeleap_msg'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['citeleap_msg'] ) ) : '';
	$n             = isset( $_GET['n'] ) ? (int) $_GET['n'] : 0;
	?>
	<h2><?php echo esc_html__( 'Featured-image pool', 'citeleap' ); ?></h2>
	<p style="color:#64748b;"><?php echo esc_html__( 'Pick a pool of Media Library images. Every new blog post (manual or CiteLeap-generated) without a Featured image gets one at random from this pool. The picked image becomes the og:image automatically and renders as a hero on single posts and as a card on the blog archive. Manual operator picks always win , the moment you set a Featured image yourself, the random flag drops.', 'citeleap' ); ?></p>

	<?php if ( 'assigned' === $msg ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php echo esc_html( sprintf( __( 'Assigned random images to %d post(s).', 'citeleap' ), $n ) ); ?></p></div>
	<?php elseif ( 'rerolled' === $msg ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php echo esc_html( sprintf( __( 'Re-randomised %d post(s).', 'citeleap' ), $n ) ); ?></p></div>
	<?php endif; ?>

	<div class="notice" style="border-left:4px solid #0284c7;padding:1rem 1.25rem;background:#f0f9ff;margin:1rem 0 1.5rem;">
		<h3 style="margin:0 0 0.5rem;font-size:16px;"><?php echo esc_html__( 'Pool status', 'citeleap' ); ?></h3>
		<table class="form-table" style="margin:0;">
			<tr><th style="width:280px;"><?php echo esc_html__( 'Images in pool', 'citeleap' ); ?></th><td><strong><?php echo (int) count( $pool_ids ); ?></strong></td></tr>
			<tr><th><?php echo esc_html__( 'Published blog posts', 'citeleap' ); ?></th><td><strong><?php echo (int) $posts_total; ?></strong></td></tr>
			<tr><th><?php echo esc_html__( 'Posts without a Featured image', 'citeleap' ); ?></th><td><strong><?php echo (int) $posts_without; ?></strong></td></tr>
			<tr><th><?php echo esc_html__( 'Posts with a random-assigned image', 'citeleap' ); ?></th><td><strong><?php echo (int) $random_count; ?></strong></td></tr>
		</table>
	</div>

	<h3><?php echo esc_html__( 'Step 1 , pick the pool of allowed images', 'citeleap' ); ?></h3>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( CITELEAP_NONCE ); ?>
		<input type="hidden" name="action" value="citeleap_save_images">
		<input type="hidden" name="pool_ids" id="citeleap-pool-ids" value="<?php echo esc_attr( implode( ',', $pool_ids ) ); ?>">
		<p>
			<button type="button" class="button button-primary" id="citeleap-pick-images"><?php echo esc_html__( 'Open Media Library', 'citeleap' ); ?></button>
			<button type="submit" class="button"><?php echo esc_html__( 'Save pool', 'citeleap' ); ?></button>
		</p>
		<div id="citeleap-pool-preview" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:0.5rem;margin:0.75rem 0;">
			<?php foreach ( $pool_ids as $id ) :
				$src = wp_get_attachment_image_src( (int) $id, 'thumbnail' );
				if ( ! $src ) continue;
			?>
				<div class="citeleap-pool-thumb" data-id="<?php echo (int) $id; ?>" style="position:relative;border:1px solid #e2e8f0;border-radius:6px;overflow:hidden;background:#f8fafc;">
					<img src="<?php echo esc_url( $src[0] ); ?>" alt="" style="display:block;width:100%;height:90px;object-fit:cover;">
					<button type="button" class="citeleap-pool-remove" aria-label="<?php echo esc_attr__( 'Remove from pool', 'citeleap' ); ?>" style="position:absolute;top:4px;right:4px;border:0;background:rgba(15,23,42,0.85);color:#fff;border-radius:50%;width:22px;height:22px;line-height:1;cursor:pointer;font-size:14px;padding:0;">&times;</button>
				</div>
			<?php endforeach; ?>
		</div>
		<h3><?php echo esc_html__( 'Toggles', 'citeleap' ); ?></h3>
		<table class="form-table">
			<tr><th><label><?php echo esc_html__( 'Auto-assign on new post', 'citeleap' ); ?></label></th>
				<td><label><input type="checkbox" name="auto_assign" value="1" <?php checked( $s['auto_assign'] ); ?>> <?php echo esc_html__( 'Pick from the pool whenever a post is saved without a Featured image.', 'citeleap' ); ?></label></td></tr>
			<tr><th><label><?php echo esc_html__( 'Hero on single post', 'citeleap' ); ?></label></th>
				<td><label><input type="checkbox" name="render_hero" value="1" <?php checked( $s['render_hero'] ); ?>> <?php echo esc_html__( 'Inject a hero figure above the_content. Skips when the theme already renders core/post-featured-image.', 'citeleap' ); ?></label></td></tr>
			<tr><th><label><?php echo esc_html__( 'Card on archive', 'citeleap' ); ?></label></th>
				<td><label><input type="checkbox" name="render_card" value="1" <?php checked( $s['render_card'] ); ?>> <?php echo esc_html__( 'Inject a clickable card thumbnail in the blog archive.', 'citeleap' ); ?></label></td></tr>
		</table>
		<p><button class="button button-primary"><?php echo esc_html__( 'Save settings', 'citeleap' ); ?></button></p>
	</form>

	<h3 style="margin-top:2rem;"><?php echo esc_html__( 'Step 2 , assign random images now', 'citeleap' ); ?></h3>
	<p style="color:#64748b;max-width:780px;"><?php echo wp_kses( __( 'Walks every published blog post. If the post has <strong>no</strong> Featured image, picks one at random from the pool and assigns it. Posts with a manually-picked Featured image are <strong>never touched</strong>.', 'citeleap' ), [ 'strong' => [] ] ); ?></p>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( CITELEAP_NONCE ); ?>
		<input type="hidden" name="action" value="citeleap_assign_images">
		<button type="submit" class="button button-primary"<?php echo empty( $pool_ids ) ? ' disabled' : ''; ?>>
			<?php echo esc_html__( 'Assign random images now', 'citeleap' ); ?>
		</button>
		<?php if ( empty( $pool_ids ) ) : ?>
			<span style="color:#b91c1c;margin-left:0.5rem;"><?php echo esc_html__( 'Pool is empty , pick images first.', 'citeleap' ); ?></span>
		<?php endif; ?>
	</form>

	<h3 style="margin-top:2rem;"><?php echo esc_html__( 'Step 3 , re-randomise (optional)', 'citeleap' ); ?></h3>
	<p style="color:#64748b;max-width:780px;"><?php echo wp_kses( __( 'Re-picks a new random image, from the current pool, ONLY for posts that were previously random-assigned. Posts where you set a Featured image manually are <strong>not touched</strong>.', 'citeleap' ), [ 'strong' => [] ] ); ?></p>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('<?php echo esc_js( __( 'Re-pick random images for every post previously random-assigned?', 'citeleap' ) ); ?>');">
		<?php wp_nonce_field( CITELEAP_NONCE ); ?>
		<input type="hidden" name="action" value="citeleap_reroll_images">
		<button type="submit" class="button"<?php echo ( empty( $pool_ids ) || ! $random_count ) ? ' disabled' : ''; ?>>
			<?php echo esc_html__( 'Re-randomise random-assigned posts', 'citeleap' ); ?>
		</button>
	</form>

	<script>
	jQuery( function ( $ ) {
		if ( typeof wp === 'undefined' || ! wp.media ) return;
		var $btn = $( '#citeleap-pick-images' ), $hidden = $( '#citeleap-pool-ids' ), $preview = $( '#citeleap-pool-preview' ), frame = null;
		function setIds( ids ) {
			ids = ids.filter( function ( id, i, a ) { return id > 0 && a.indexOf( id ) === i; } );
			$hidden.val( ids.join( ',' ) );
		}
		function ids() {
			var raw = ($hidden.val() || '').trim();
			return raw ? raw.split(',').map(function(s){return parseInt(s,10);}).filter(function(n){return n>0;}) : [];
		}
		function thumbMarkup( id, url ) {
			return '<div class="citeleap-pool-thumb" data-id="' + id + '" style="position:relative;border:1px solid #e2e8f0;border-radius:6px;overflow:hidden;background:#f8fafc;">' +
				'<img src="' + url + '" alt="" style="display:block;width:100%;height:90px;object-fit:cover;">' +
				'<button type="button" class="citeleap-pool-remove" aria-label="Remove from pool" style="position:absolute;top:4px;right:4px;border:0;background:rgba(15,23,42,0.85);color:#fff;border-radius:50%;width:22px;height:22px;line-height:1;cursor:pointer;font-size:14px;padding:0;">&times;</button>' +
			'</div>';
		}
		$btn.on( 'click', function () {
			if ( frame ) { frame.open(); return; }
			frame = wp.media({ title: 'Pick images for the blog pool', multiple: 'add', library: { type: 'image' }, button: { text: 'Use these' } });
			frame.on( 'open', function () {
				var sel = frame.state().get( 'selection' );
				ids().forEach( function ( id ) { var att = wp.media.attachment( id ); att.fetch(); sel.add( att ? [att] : [] ); } );
			} );
			frame.on( 'select', function () {
				var sel = frame.state().get( 'selection' ).toJSON();
				setIds( sel.map( function ( a ) { return a.id; } ) );
				$preview.empty();
				sel.forEach( function ( a ) {
					var url = (a.sizes && a.sizes.thumbnail && a.sizes.thumbnail.url) || a.url;
					$preview.append( thumbMarkup( a.id, url ) );
				} );
			} );
			frame.open();
		} );
		$preview.on( 'click', '.citeleap-pool-remove', function ( e ) {
			e.preventDefault();
			var $t = $( this ).closest( '.citeleap-pool-thumb' );
			var id = parseInt( $t.data( 'id' ), 10 );
			$t.remove();
			setIds( ids().filter( function ( x ) { return x !== id; } ) );
		} );
	} );
	</script>
	<?php
	wp_enqueue_media();
}

function citeleap_render_seo_tab(): void {
	$s = CiteLeap_SEO::settings();
	$d = CiteLeap_SEO::detected_seo_sources();
	?>
	<h2><?php echo esc_html__( 'SEO module', 'citeleap' ); ?></h2>
	<p style="color:#64748b;"><?php echo esc_html__( 'Auto-injects JSON-LD (Organization + WebSite + BlogPosting + auto FAQ/HowTo), Open Graph, Twitter Card, canonical, and meta-description on every page render , BUT only when no other SEO plugin already wrote them. On every post publish, pings Google + Bing sitemaps and fires IndexNow.', 'citeleap' ); ?></p>
	<h3><?php echo esc_html__( 'Detected SEO plugins (CiteLeap stands down per-tag if any are active)', 'citeleap' ); ?></h3>
	<ul style="margin:0 0 1rem 1.25rem;">
		<?php foreach ( $d as $k => $v ) : ?>
			<li><?php echo esc_html( ucfirst( str_replace( '_', ' ', $k ) ) ); ?>: <?php echo $v ? '<span style="color:#16a34a">&#10003; active</span>' : '<span style="color:#9ca3af">&mdash; not installed</span>'; ?></li>
		<?php endforeach; ?>
	</ul>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( CITELEAP_NONCE ); ?>
		<input type="hidden" name="action" value="citeleap_save_seo">
		<table class="form-table">
			<tr><th><label><?php echo esc_html__( 'SEO module enabled', 'citeleap' ); ?></label></th>
				<td><label><input type="checkbox" name="enabled" value="1" <?php checked( $s['enabled'] ); ?>> <?php echo esc_html__( 'Master switch for the SEO module.', 'citeleap' ); ?></label></td></tr>
			<tr><th><?php echo esc_html__( 'Inject', 'citeleap' ); ?></th>
				<td>
					<label><input type="checkbox" name="inject_schema"    value="1" <?php checked( $s['inject_schema'] ); ?>> <?php echo esc_html__( 'JSON-LD schema (Organization + WebSite + BlogPosting + Breadcrumb + auto FAQ/HowTo)', 'citeleap' ); ?></label><br>
					<label><input type="checkbox" name="inject_og"        value="1" <?php checked( $s['inject_og'] ); ?>> <?php echo esc_html__( 'Open Graph + Twitter Card', 'citeleap' ); ?></label><br>
					<label><input type="checkbox" name="inject_canonical" value="1" <?php checked( $s['inject_canonical'] ); ?>> <?php echo esc_html__( 'Canonical URL', 'citeleap' ); ?></label>
				</td></tr>
			<tr><th><?php echo esc_html__( 'On every publish', 'citeleap' ); ?></th>
				<td>
					<label><input type="checkbox" name="ping_sitemap" value="1" <?php checked( $s['ping_sitemap'] ); ?>> <?php echo esc_html__( 'Ping Google + Bing sitemap', 'citeleap' ); ?></label><br>
					<label><input type="checkbox" name="indexnow"     value="1" <?php checked( $s['indexnow'] ); ?>> <?php echo esc_html__( 'Fire IndexNow (Bing / Yandex / Naver)', 'citeleap' ); ?></label>
				</td></tr>
		</table>
		<p><button class="button button-primary"><?php echo esc_html__( 'Save SEO settings', 'citeleap' ); ?></button></p>
	</form>
	<?php
}

function citeleap_render_languages_tab(): void {
	if ( ! CiteLeap_Plan::has( 'multilingual' ) ) {
		CiteLeap_Plan::render_locked_notice(
			'multilingual',
			__( 'Multilingual (7 languages)', 'citeleap' ),
			__( 'Generate posts in English, Spanish, Portuguese, French, German, Italian, or Dutch. Automatic hreflang emission, Polylang + WPML integration. Available on Pro and above. Solo accounts ship in your single default language.', 'citeleap' )
		);
		return;
	}
	$i = CiteLeap_I18n::settings();
	$supported = CiteLeap_I18n::supported();
	?>
	<h2><?php echo esc_html__( 'Languages + hreflang', 'citeleap' ); ?></h2>
	<p style="color:#64748b;"><?php echo esc_html__( 'Pick one or more target languages. Each queued topic carries a language code. The writer produces native-quality output with locally adapted statistics and sources. If Polylang or WPML is installed the post is assigned to the right language automatically. If neither is installed, CiteLeap emits hreflang tags itself based on the per-post language tag.', 'citeleap' ); ?></p>
	<p>
		<?php if ( CiteLeap_I18n::has_polylang() ) : ?>
			<span style="color:#16a34a;font-weight:600;">&#10003; <?php echo esc_html__( 'Polylang detected. New posts will be assigned to the target language via pll_set_post_language.', 'citeleap' ); ?></span>
		<?php elseif ( CiteLeap_I18n::has_wpml() ) : ?>
			<span style="color:#16a34a;font-weight:600;">&#10003; <?php echo esc_html__( 'WPML detected. New posts will be assigned to the target language via the wpml_set_element_language_details action.', 'citeleap' ); ?></span>
		<?php else : ?>
			<span style="color:#b45309;font-weight:600;"><?php echo esc_html__( 'Neither Polylang nor WPML detected. CiteLeap will emit hreflang tags directly from its own _citeleap_lang post meta if you enable "Inject hreflang" below.', 'citeleap' ); ?></span>
		<?php endif; ?>
	</p>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( CITELEAP_NONCE ); ?>
		<input type="hidden" name="action" value="citeleap_save_i18n">
		<table class="form-table">
			<tr><th><label><?php echo esc_html__( 'Default language', 'citeleap' ); ?></label></th>
				<td><select name="default_lang">
					<?php foreach ( $supported as $code => $row ) : ?>
						<option value="<?php echo esc_attr( $code ); ?>" <?php selected( $i['default_lang'], $code ); ?>><?php echo esc_html( $row[1] . ' (' . $row[2] . ')' ); ?></option>
					<?php endforeach; ?>
				</select></td></tr>
			<tr><th><label><?php echo esc_html__( 'Enabled languages', 'citeleap' ); ?></label></th>
				<td>
					<?php foreach ( $supported as $code => $row ) : ?>
						<label style="display:inline-block;margin:0 1rem 0.5rem 0;">
							<input type="checkbox" name="enabled_langs[]" value="<?php echo esc_attr( $code ); ?>" <?php checked( in_array( $code, $i['enabled_langs'], true ) ); ?>>
							<?php echo esc_html( $row[1] . ' (' . $row[2] . ')' ); ?>
						</label>
					<?php endforeach; ?>
					<p class="description"><?php echo esc_html__( 'Operators can pick from this list per topic on the Planner tab.', 'citeleap' ); ?></p>
				</td></tr>
			<tr><th><label><?php echo esc_html__( 'Auto-translate refresh', 'citeleap' ); ?></label></th>
				<td><label><input type="checkbox" name="auto_translate" value="1" <?php checked( $i['auto_translate'] ); ?>> <?php echo esc_html__( 'When a post is refreshed, also produce a refreshed version in every enabled language (uses additional tokens).', 'citeleap' ); ?></label></td></tr>
			<tr><th><label><?php echo esc_html__( 'Inject hreflang', 'citeleap' ); ?></label></th>
				<td><label><input type="checkbox" name="inject_hreflang" value="1" <?php checked( $i['inject_hreflang'] ); ?>> <?php echo esc_html__( 'Emit hreflang tags in wp_head when no other multilingual plugin handles it. Skipped automatically if Yoast / Polylang / WPML are detected.', 'citeleap' ); ?></label></td></tr>
		</table>
		<p><button class="button button-primary"><?php echo esc_html__( 'Save language settings', 'citeleap' ); ?></button></p>
	</form>
	<?php
}

function citeleap_render_license_tab(): void {
	$plan_slug  = CiteLeap_Plan::current();
	$plan_def   = CiteLeap_Plan::definition( $plan_slug );
	$plan_label = CiteLeap_License::plan_label();
	$inc        = CiteLeap_Credits::included();
	$used       = CiteLeap_Credits::used();
	$top_up     = CiteLeap_Credits::top_up();
	$remain     = CiteLeap_Credits::remaining();
	$lifetime   = CiteLeap_Credits::lifetime_used();
	$pct        = CiteLeap_Credits::percent_used();
	$is_paying  = CiteLeap_License::is_paying();
	$is_trial   = CiteLeap_License::is_trial();
	$trial_days = CiteLeap_License::trial_days_left();
	$fs_loaded  = (bool) CiteLeap_License::freemius();
	$upgrade    = CiteLeap_License::checkout_url();
	$top_url    = CiteLeap_License::top_up_url( 'growth' );
	$account    = CiteLeap_License::account_url();
	?>
	<h2><?php echo esc_html__( 'License & credits', 'citeleap' ); ?></h2>
	<p style="color:#64748b;max-width:780px;"><?php echo esc_html__( 'Each draft and each refresh consumes one credit. Monthly credits reset on the 1st of each calendar month. Top-up packs never expire and are consumed after monthly credits. Manual operator actions (pause / resume / publish-now / approve / reject) are free.', 'citeleap' ); ?></p>

	<div class="notice" style="border-left:4px solid #0284c7;padding:1rem 1.25rem;background:#f0f9ff;margin:1rem 0 1.5rem;">
		<h3 style="margin:0 0 0.5rem;font-size:16px;"><?php echo esc_html__( 'Current plan', 'citeleap' ); ?></h3>
		<table class="form-table" style="margin:0;">
			<tr><th style="width:280px;"><?php echo esc_html__( 'Plan', 'citeleap' ); ?></th><td><strong><?php echo esc_html( $plan_label ); ?></strong> <code style="background:#e2e8f0;padding:0.125rem 0.375rem;border-radius:3px;"><?php echo esc_html( $plan_slug ); ?></code></td></tr>
			<tr><th><?php echo esc_html__( 'Billing status', 'citeleap' ); ?></th><td>
				<?php if ( defined( 'CITELEAP_DEV_MODE' ) && CITELEAP_DEV_MODE ) : ?>
					<span style="color:#16a34a;">&#10003; <?php echo esc_html__( 'Developer mode , unlimited, no billing.', 'citeleap' ); ?></span>
				<?php elseif ( $is_paying ) : ?>
					<span style="color:#16a34a;">&#10003; <?php echo esc_html__( 'Active paid subscription.', 'citeleap' ); ?></span>
				<?php elseif ( $is_trial ) : ?>
					<span style="color:#0284c7;">&#9201; <?php echo esc_html( sprintf( __( 'Trial , %d day(s) remaining.', 'citeleap' ), $trial_days ) ); ?></span>
				<?php else : ?>
					<span style="color:#b45309;">&#9888; <?php echo esc_html__( 'Free plan , upgrade to unlock more credits + features.', 'citeleap' ); ?></span>
				<?php endif; ?>
			</td></tr>
			<tr><th><?php echo esc_html__( 'Included per cycle', 'citeleap' ); ?></th><td><strong><?php echo ( PHP_INT_MAX === $inc ) ? esc_html__( 'unlimited', 'citeleap' ) : (int) $inc; ?></strong> <?php echo esc_html__( 'credits', 'citeleap' ); ?> <?php echo CiteLeap_Plan::is_lifetime_credits() ? '<span style="color:#64748b;">(' . esc_html__( 'lifetime, does not reset', 'citeleap' ) . ')</span>' : '<span style="color:#64748b;">(' . esc_html__( 'resets on the 1st of every month', 'citeleap' ) . ')</span>'; ?></td></tr>
			<tr><th><?php echo esc_html__( 'Used this cycle', 'citeleap' ); ?></th><td><strong><?php echo (int) $used; ?></strong> (<?php echo (int) $pct; ?>%)</td></tr>
			<tr><th><?php echo esc_html__( 'Top-up balance', 'citeleap' ); ?></th><td><strong><?php echo (int) $top_up; ?></strong> <?php echo esc_html__( 'credits in reserve', 'citeleap' ); ?></td></tr>
			<tr><th><?php echo esc_html__( 'Remaining now', 'citeleap' ); ?></th><td><strong style="color:<?php echo $remain > 0 ? '#16a34a' : '#b91c1c'; ?>;"><?php echo (int) $remain; ?></strong></td></tr>
			<tr><th><?php echo esc_html__( 'Lifetime consumed', 'citeleap' ); ?></th><td><?php echo (int) $lifetime; ?></td></tr>
			<tr><th><?php echo esc_html__( 'Sites allowed on this license', 'citeleap' ); ?></th><td><?php echo ( PHP_INT_MAX === (int) $plan_def['sites'] ) ? esc_html__( 'unlimited', 'citeleap' ) : (int) $plan_def['sites']; ?></td></tr>
			<tr><th><?php echo esc_html__( 'Overage rate', 'citeleap' ); ?></th><td><?php echo $plan_def['overage_per_credit'] > 0 ? '$' . esc_html( number_format( (float) $plan_def['overage_per_credit'], 2 ) ) . ' / ' . esc_html__( 'extra credit', 'citeleap' ) : esc_html__( 'no overage (paid via top-up packs only)', 'citeleap' ); ?></td></tr>
		</table>
		<p style="margin-top:1rem;">
			<a class="button button-primary" href="<?php echo esc_url( $upgrade ); ?>"><?php echo $is_paying ? esc_html__( 'Change plan', 'citeleap' ) : esc_html__( 'Upgrade plan', 'citeleap' ); ?></a>
			<a class="button" href="<?php echo esc_url( $top_url ); ?>"><?php echo esc_html__( 'Buy top-up pack', 'citeleap' ); ?></a>
			<a class="button" href="<?php echo esc_url( $account ); ?>"><?php echo esc_html__( 'Account & invoices', 'citeleap' ); ?></a>
		</p>
	</div>

	<h3><?php echo esc_html__( 'Capabilities on the current plan', 'citeleap' ); ?></h3>
	<table class="form-table" style="max-width:780px;">
		<?php foreach ( [
			'auto_publish'    => __( 'Auto-publish (scheduler publishes drafts on cadence)', 'citeleap' ),
			'refresh'         => __( 'Refresh existing posts', 'citeleap' ),
			'scheduling'      => __( 'Per-row pinning + Plan datetimes', 'citeleap' ),
			'calendar'        => __( 'Calendar overview', 'citeleap' ),
			'multilingual'    => __( 'Multilingual (7 languages)', 'citeleap' ),
			'top_ups'         => __( 'Buy top-up credit packs', 'citeleap' ),
			'white_label'     => __( 'White-label admin (remove CiteLeap branding)', 'citeleap' ),
			'priority_support'=> __( 'Priority support (12h SLA)', 'citeleap' ),
			'images_bulk'     => __( 'Bulk image pool operations', 'citeleap' ),
		] as $cap => $label ) : ?>
			<tr><th style="width:360px;"><?php echo esc_html( $label ); ?></th><td>
				<?php if ( CiteLeap_Plan::has( $cap ) ) : ?>
					<span style="color:#16a34a;">&#10003; <?php echo esc_html__( 'included', 'citeleap' ); ?></span>
				<?php else : ?>
					<span style="color:#9ca3af;">&mdash; <?php echo esc_html__( 'not on this plan', 'citeleap' ); ?></span>
				<?php endif; ?>
			</td></tr>
		<?php endforeach; ?>
	</table>

	<h3 style="margin-top:2rem;"><?php echo esc_html__( 'Compare plans', 'citeleap' ); ?></h3>
	<table class="widefat striped" style="max-width:920px;">
		<thead><tr>
			<th><?php echo esc_html__( 'Plan', 'citeleap' ); ?></th>
			<th><?php echo esc_html__( 'Monthly', 'citeleap' ); ?></th>
			<th><?php echo esc_html__( 'Annual', 'citeleap' ); ?></th>
			<th><?php echo esc_html__( 'Credits / cycle', 'citeleap' ); ?></th>
			<th><?php echo esc_html__( 'Sites', 'citeleap' ); ?></th>
			<th><?php echo esc_html__( 'Overage', 'citeleap' ); ?></th>
		</tr></thead>
		<tbody>
			<?php foreach ( [ 'free', 'solo', 'pro', 'agency', 'enterprise' ] as $slug ) :
				$d = CiteLeap_Plan::definition( $slug );
				$is_current = ( $slug === $plan_slug );
			?>
				<tr<?php echo $is_current ? ' style="background:#f0f9ff;font-weight:600;"' : ''; ?>>
					<td><?php echo esc_html( $d['label'] ); ?><?php echo $is_current ? ' <span style="color:#0284c7;">&larr; ' . esc_html__( 'current', 'citeleap' ) . '</span>' : ''; ?></td>
					<td><?php echo $d['price_monthly'] > 0 ? '$' . esc_html( (string) $d['price_monthly'] ) : esc_html__( 'free', 'citeleap' ); ?></td>
					<td><?php echo $d['price_annual']  > 0 ? '$' . esc_html( (string) $d['price_annual'] )  : '&mdash;'; ?></td>
					<td><?php echo (int) $d['credits_per_cycle']; ?><?php echo ! empty( $d['lifetime_credits'] ) ? ' <small>(' . esc_html__( 'lifetime', 'citeleap' ) . ')</small>' : ''; ?></td>
					<td><?php echo ( PHP_INT_MAX === (int) $d['sites'] ) ? esc_html__( 'unlimited', 'citeleap' ) : (int) $d['sites']; ?></td>
					<td><?php echo $d['overage_per_credit'] > 0 ? '$' . esc_html( number_format( (float) $d['overage_per_credit'], 2 ) ) : '&mdash;'; ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?php CiteLeap_TopUps::render_grid(); ?>

	<?php if ( ! $fs_loaded && ! ( defined( 'CITELEAP_DEV_MODE' ) && CITELEAP_DEV_MODE ) ) : ?>
		<div class="notice notice-info" style="margin-top:1.5rem;"><p>
			<strong><?php echo esc_html__( 'Freemius SDK not yet loaded.', 'citeleap' ); ?></strong>
			<?php echo esc_html__( 'Drop the SDK at vendor/freemius/wordpress-sdk/start.php and set CITELEAP_FS_ID + CITELEAP_FS_PUBLIC_KEY in wp-config.php to activate real billing + checkout. The top-up grid above runs in simulator mode until then.', 'citeleap' ); ?>
		</p></div>
	<?php endif; ?>
	<?php
}

/* ---- handlers ----------------------------------------------------------- */

add_action( 'admin_post_citeleap_save_research', function () {
	CiteLeap_Caps::guard_manage();
	check_admin_referer( CITELEAP_NONCE );
	CiteLeap_Research::save_settings( [
		'enabled'       => $_POST['enabled']       ?? 0,
		'provider'      => $_POST['provider']      ?? 'claude_native',
		'api_key'       => $_POST['api_key']       ?? '',
		'max_searches'  => $_POST['max_searches']  ?? 5,
		'min_citations' => $_POST['min_citations'] ?? 3,
		'min_internal'  => $_POST['min_internal']  ?? 2,
	] );
	wp_safe_redirect( add_query_arg( [ 'page' => 'citeleap', 'tab' => 'research', 'citeleap_msg' => 'saved' ], admin_url( 'admin.php' ) ) );
	exit;
} );

add_action( 'admin_post_citeleap_save_images', function () {
	CiteLeap_Caps::guard_manage();
	check_admin_referer( CITELEAP_NONCE );
	$raw  = isset( $_POST['pool_ids'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['pool_ids'] ) ) : '';
	$ids  = array_filter( array_map( 'intval', explode( ',', $raw ) ) );
	CiteLeap_Images::save_settings( [
		'pool'         => $ids,
		'auto_assign'  => $_POST['auto_assign']  ?? 0,
		'render_hero'  => $_POST['render_hero']  ?? 0,
		'render_card'  => $_POST['render_card']  ?? 0,
	] );
	wp_safe_redirect( add_query_arg( [ 'page' => 'citeleap', 'tab' => 'images', 'citeleap_msg' => 'saved' ], admin_url( 'admin.php' ) ) );
	exit;
} );

add_action( 'admin_post_citeleap_save_seo', function () {
	CiteLeap_Caps::guard_manage();
	check_admin_referer( CITELEAP_NONCE );
	CiteLeap_SEO::save_settings( [
		'enabled'          => $_POST['enabled']          ?? 0,
		'inject_schema'    => $_POST['inject_schema']    ?? 0,
		'inject_og'        => $_POST['inject_og']        ?? 0,
		'inject_canonical' => $_POST['inject_canonical'] ?? 0,
		'ping_sitemap'     => $_POST['ping_sitemap']     ?? 0,
		'indexnow'         => $_POST['indexnow']         ?? 0,
	] );
	wp_safe_redirect( add_query_arg( [ 'page' => 'citeleap', 'tab' => 'seo', 'citeleap_msg' => 'saved' ], admin_url( 'admin.php' ) ) );
	exit;
} );

add_action( 'admin_post_citeleap_save_i18n', function () {
	CiteLeap_Caps::guard_manage();
	check_admin_referer( CITELEAP_NONCE );
	CiteLeap_I18n::save_settings( [
		'default_lang'    => $_POST['default_lang']    ?? 'en',
		'enabled_langs'   => $_POST['enabled_langs']   ?? [],
		'auto_translate'  => $_POST['auto_translate']  ?? 0,
		'inject_hreflang' => $_POST['inject_hreflang'] ?? 0,
	] );
	wp_safe_redirect( add_query_arg( [ 'page' => 'citeleap', 'tab' => 'languages', 'citeleap_msg' => 'saved' ], admin_url( 'admin.php' ) ) );
	exit;
} );
