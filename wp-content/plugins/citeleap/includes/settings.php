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
		'manage_options',
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
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );
	$tab = isset( $_GET['tab'] ) ? sanitize_key( (string) $_GET['tab'] ) : 'dashboard';
	$tab = in_array( $tab, [ 'dashboard', 'settings', 'planner', 'prompts', 'log' ], true ) ? $tab : 'dashboard';
	?>
	<div class="wrap">
		<h1><?php echo esc_html__( 'CiteLeap', 'citeleap' ); ?> <span style="font-size:0.6em;color:#64748b;font-weight:normal;">v<?php echo esc_html( CITELEAP_VERSION ); ?></span></h1>

		<?php citeleap_render_flash(); ?>

		<nav class="nav-tab-wrapper" style="margin-top:1rem;">
			<a class="nav-tab <?php echo 'dashboard' === $tab ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=citeleap&tab=dashboard' ) ); ?>"><?php echo esc_html__( 'Dashboard', 'citeleap' ); ?></a>
			<a class="nav-tab <?php echo 'planner' === $tab ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=citeleap&tab=planner' ) ); ?>"><?php echo esc_html__( 'Planner', 'citeleap' ); ?></a>
			<a class="nav-tab <?php echo 'prompts' === $tab ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=citeleap&tab=prompts' ) ); ?>"><?php echo esc_html__( 'Prompts', 'citeleap' ); ?></a>
			<a class="nav-tab <?php echo 'settings' === $tab ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=citeleap&tab=settings' ) ); ?>"><?php echo esc_html__( 'Settings', 'citeleap' ); ?></a>
			<a class="nav-tab <?php echo 'log' === $tab ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=citeleap&tab=log' ) ); ?>"><?php echo esc_html__( 'Log', 'citeleap' ); ?></a>
		</nav>

		<div style="background:#fff;padding:1.25rem 1.5rem;border:1px solid #e2e8f0;border-top:0;">
			<?php
			switch ( $tab ) {
				case 'dashboard': CiteLeap_Dashboard::render(); break;
				case 'planner':   CiteLeap_Planner::render(); break;
				case 'prompts':   citeleap_render_prompts(); break;
				case 'log':       citeleap_render_log(); break;
				default:          citeleap_render_settings();
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
		$text = __( 'Publish datetime pinned. The auto-tick will pick this topic at exactly that time.', 'citeleap' );
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
	$class = ( 'error' === $kind ) ? 'notice-error' : 'notice-success';
	echo '<div class="notice ' . esc_attr( $class ) . ' is-dismissible" style="margin-top:1rem;"><p>' . esc_html( $text ) . '</p></div>';
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
			<?php foreach ( [ 'reasoning' => __( 'Reasoning model (ideas)', 'citeleap' ), 'writing' => __( 'Writing model (drafts)', 'citeleap' ) ] as $role => $label ) : ?>
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

		<h2 style="margin-top:1.5rem;"><?php echo esc_html__( 'Auto-publish schedule', 'citeleap' ); ?></h2>
		<table class="form-table">
			<tr>
				<th><label for="auto_mode"><?php echo esc_html__( 'Auto mode', 'citeleap' ); ?></label></th>
				<td>
					<?php $auto_mode = (string) ( $schedule['auto_mode'] ?? ( ! empty( $schedule['auto'] ) ? 'publish' : 'off' ) ); ?>
					<select id="auto_mode" name="auto_mode">
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
				<th><label for="posts_per_week"><?php echo esc_html__( 'Posts per week', 'citeleap' ); ?></label></th>
				<td><input type="number" id="posts_per_week" name="posts_per_week" min="1" max="14" value="<?php echo (int) ( $schedule['posts_per_week'] ?? 3 ); ?>"></td>
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

		<h2 style="margin-top:1.5rem;"><?php echo esc_html__( 'Refresh existing content', 'citeleap' ); ?></h2>
		<p style="color:#64748b;"><?php echo esc_html__( 'Periodically refresh older posts. Posts queue from the Planner tab (or auto-pick the oldest-modified once cadence is met). Existing slug, ID, date, comments, and meta are preserved.', 'citeleap' ); ?></p>
		<?php $r = CiteLeap_Refresh::settings(); ?>
		<table class="form-table">
			<tr>
				<th><label for="refresh_auto_mode"><?php echo esc_html__( 'Refresh mode', 'citeleap' ); ?></label></th>
				<td>
					<select id="refresh_auto_mode" name="refresh_auto_mode">
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
				<th><label for="refresh_posts_per_week"><?php echo esc_html__( 'Refresh posts per week', 'citeleap' ); ?></label></th>
				<td><input type="number" id="refresh_posts_per_week" name="refresh_posts_per_week" min="1" max="14" value="<?php echo (int) $r['posts_per_week']; ?>"></td>
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
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );
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
		'provider_writing'   => sanitize_key( (string) ( $_POST['provider_writing']   ?? 'claude' ) ),
	];
	foreach ( $defaults['providers'] as $p ) {
		$models[ $p ] = [
			'reasoning' => sanitize_text_field( wp_unslash( (string) ( $_POST[ 'model_' . $p . '_reasoning' ] ?? $defaults['models'][ $p ]['reasoning'] ) ) ),
			'writing'   => sanitize_text_field( wp_unslash( (string) ( $_POST[ 'model_' . $p . '_writing' ]   ?? $defaults['models'][ $p ]['writing'] ) ) ),
		];
	}
	update_option( CITELEAP_OPTION_MODELS, $models, false );

	$mode = sanitize_key( (string) ( $_POST['auto_mode'] ?? 'off' ) );
	if ( ! in_array( $mode, [ 'off', 'draft', 'publish' ], true ) ) $mode = 'off';
	$schedule = [
		'auto'           => ( 'off' !== $mode ) ? 1 : 0,    // legacy boolean kept for back-compat
		'auto_mode'      => $mode,
		'start_date'     => sanitize_text_field( wp_unslash( (string) ( $_POST['start_date'] ?? '' ) ) ),
		'posts_per_week' => max( 1, min( 14, (int) ( $_POST['posts_per_week'] ?? 3 ) ) ),
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

	/* Refresh module settings. */
	CiteLeap_Refresh::save_settings( [
		'auto_mode'      => $_POST['refresh_auto_mode']      ?? 'off',
		'cadence_days'   => $_POST['refresh_cadence_days']   ?? 90,
		'posts_per_week' => $_POST['refresh_posts_per_week'] ?? 2,
	] );

	wp_safe_redirect( add_query_arg( [ 'page' => 'citeleap', 'tab' => 'settings', 'citeleap_msg' => 'saved' ], admin_url( 'admin.php' ) ) );
	exit;
} );

/* Test-connection handlers , one per provider. Sends a minimal
 * "say hello" request and reports the round-trip success or error
 * back to the operator. Logs the result. */
foreach ( [ 'claude', 'openai', 'gemini' ] as $__p ) {
	add_action( 'admin_post_citeleap_test_key_' . $__p, function () use ( $__p ) {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );
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
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );
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
