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
	$tab = in_array( $tab, [ 'dashboard', 'settings', 'planner', 'calendar', 'prompts', 'images', 'seo', 'research', 'languages', 'log' ], true ) ? $tab : 'dashboard';
	?>
	<div class="wrap">
		<h1><?php echo esc_html__( 'CiteLeap', 'citeleap' ); ?> <span style="font-size:0.6em;color:#64748b;font-weight:normal;">v<?php echo esc_html( CITELEAP_VERSION ); ?></span></h1>

		<?php citeleap_render_flash(); ?>

		<nav class="nav-tab-wrapper" style="margin-top:1rem;">
			<a class="nav-tab <?php echo 'dashboard' === $tab ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=citeleap&tab=dashboard' ) ); ?>"><?php echo esc_html__( 'Dashboard', 'citeleap' ); ?></a>
			<a class="nav-tab <?php echo 'planner' === $tab ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=citeleap&tab=planner' ) ); ?>"><?php echo esc_html__( 'Planner', 'citeleap' ); ?></a>
			<a class="nav-tab <?php echo 'calendar' === $tab ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=citeleap&tab=calendar' ) ); ?>"><?php echo esc_html__( 'Calendar', 'citeleap' ); ?></a>
			<a class="nav-tab <?php echo 'prompts' === $tab ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=citeleap&tab=prompts' ) ); ?>"><?php echo esc_html__( 'Prompts', 'citeleap' ); ?></a>
			<a class="nav-tab <?php echo 'research' === $tab ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=citeleap&tab=research' ) ); ?>"><?php echo esc_html__( 'Research', 'citeleap' ); ?></a>
			<a class="nav-tab <?php echo 'images' === $tab ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=citeleap&tab=images' ) ); ?>"><?php echo esc_html__( 'Images', 'citeleap' ); ?></a>
			<a class="nav-tab <?php echo 'seo' === $tab ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=citeleap&tab=seo' ) ); ?>"><?php echo esc_html__( 'SEO', 'citeleap' ); ?></a>
			<a class="nav-tab <?php echo 'languages' === $tab ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=citeleap&tab=languages' ) ); ?>"><?php echo esc_html__( 'Languages', 'citeleap' ); ?></a>
			<a class="nav-tab <?php echo 'settings' === $tab ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=citeleap&tab=settings' ) ); ?>"><?php echo esc_html__( 'Settings', 'citeleap' ); ?></a>
			<a class="nav-tab <?php echo 'log' === $tab ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=citeleap&tab=log' ) ); ?>"><?php echo esc_html__( 'Log', 'citeleap' ); ?></a>
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
	$s = CiteLeap_Images::settings();
	$pool_ids = $s['pool'];
	?>
	<h2><?php echo esc_html__( 'Featured-image pool', 'citeleap' ); ?></h2>
	<p style="color:#64748b;"><?php echo esc_html__( 'Pick a pool of Media Library images. Every new blog post (manual or CiteLeap-generated) without a Featured image gets one at random from this pool. The picked image becomes the og:image automatically and renders as a hero on single posts and as a card on the blog archive. Manual operator picks always win , the moment you set a Featured image yourself, the random flag drops.', 'citeleap' ); ?></p>
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
				<div data-id="<?php echo (int) $id; ?>" style="border:1px solid #e2e8f0;border-radius:6px;overflow:hidden;background:#f8fafc;">
					<img src="<?php echo esc_url( $src[0] ); ?>" alt="" style="display:block;width:100%;height:90px;object-fit:cover;">
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
		<p><button class="button button-primary"><?php echo esc_html__( 'Save', 'citeleap' ); ?></button></p>
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
					$preview.append( '<div data-id="' + a.id + '" style="border:1px solid #e2e8f0;border-radius:6px;overflow:hidden;background:#f8fafc;"><img src="' + url + '" alt="" style="display:block;width:100%;height:90px;object-fit:cover;"></div>' );
				} );
			} );
			frame.open();
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

/* ---- handlers ----------------------------------------------------------- */

add_action( 'admin_post_citeleap_save_research', function () {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );
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
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );
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
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );
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
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );
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
