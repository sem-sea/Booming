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
	echo '<div class="notice notice-success is-dismissible" style="margin-top:1rem;"><p>' . esc_html( $msg ) . '</p></div>';
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
		<p style="color:#64748b;"><?php echo esc_html__( 'Bring your own keys. Stored locally; never sent to anyone other than the provider during generation.', 'citeleap' ); ?></p>
		<table class="form-table">
			<tr>
				<th><label for="claude_key">Anthropic Claude</label></th>
				<td><input type="password" id="claude_key" name="claude_key" value="<?php echo esc_attr( (string) ( $keys['claude'] ?? '' ) ); ?>" class="regular-text" autocomplete="off" placeholder="sk-ant-..."></td>
			</tr>
			<tr>
				<th><label for="openai_key">OpenAI</label></th>
				<td><input type="password" id="openai_key" name="openai_key" value="<?php echo esc_attr( (string) ( $keys['openai'] ?? '' ) ); ?>" class="regular-text" autocomplete="off" placeholder="sk-..."></td>
			</tr>
			<tr>
				<th><label for="gemini_key">Google Gemini</label></th>
				<td><input type="password" id="gemini_key" name="gemini_key" value="<?php echo esc_attr( (string) ( $keys['gemini'] ?? '' ) ); ?>" class="regular-text" autocomplete="off" placeholder="AIza..."></td>
			</tr>
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
				<th><label for="auto"><?php echo esc_html__( 'Auto-publish', 'citeleap' ); ?></label></th>
				<td>
					<label><input type="checkbox" id="auto" name="auto" value="1" <?php checked( ! empty( $schedule['auto'] ) ); ?>>
					<?php echo esc_html__( 'Enable scheduled idea generation + draft + future-publish via WP cron.', 'citeleap' ); ?></label>
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
				<th><label for="refresh_auto"><?php echo esc_html__( 'Auto-refresh mode', 'citeleap' ); ?></label></th>
				<td>
					<label><input type="checkbox" id="refresh_auto" name="refresh_auto" value="1" <?php checked( ! empty( $r['auto'] ) ); ?>>
					<?php echo esc_html__( 'Automatically refresh due posts on the hourly cron tick.', 'citeleap' ); ?></label>
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
			<thead><tr><th style="width:160px;"><?php echo esc_html__( 'Time', 'citeleap' ); ?></th><th style="width:200px;"><?php echo esc_html__( 'Event', 'citeleap' ); ?></th><th><?php echo esc_html__( 'Detail', 'citeleap' ); ?></th></tr></thead>
			<tbody>
			<?php foreach ( $log as $row ) : ?>
				<tr>
					<td><code><?php echo esc_html( (string) ( $row['time'] ?? '' ) ); ?></code></td>
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

	$keys = [
		'claude' => sanitize_text_field( wp_unslash( (string) ( $_POST['claude_key'] ?? '' ) ) ),
		'openai' => sanitize_text_field( wp_unslash( (string) ( $_POST['openai_key'] ?? '' ) ) ),
		'gemini' => sanitize_text_field( wp_unslash( (string) ( $_POST['gemini_key'] ?? '' ) ) ),
	];
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

	$schedule = [
		'auto'           => ! empty( $_POST['auto'] ) ? 1 : 0,
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
		'auto'           => $_POST['refresh_auto']           ?? 0,
		'cadence_days'   => $_POST['refresh_cadence_days']   ?? 90,
		'posts_per_week' => $_POST['refresh_posts_per_week'] ?? 2,
	] );

	wp_safe_redirect( add_query_arg( [ 'page' => 'citeleap', 'tab' => 'settings', 'citeleap_msg' => 'saved' ], admin_url( 'admin.php' ) ) );
	exit;
} );

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
