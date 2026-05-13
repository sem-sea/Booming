<?php
/**
 * CiteLeap , planner.
 *
 * @package CiteLeap
 */

defined( 'ABSPATH' ) || exit;

class CiteLeap_Planner {

	public static function render(): void {
		$queue    = (array) get_option( CITELEAP_OPTION_QUEUE, [] );
		$schedule = (array) get_option( CITELEAP_OPTION_SCHEDULE, [] );
		$next     = CiteLeap_Scheduler::next_slot( $schedule );
		?>
		<div class="citeleap-planner">
			<div class="notice" style="border-left:4px solid #0284c7;padding:0.75rem 1rem;background:#f0f9ff;margin:0 0 1rem;">
				<p style="margin:0;">
					<strong><?php echo esc_html__( 'Auto-publish:', 'citeleap' ); ?></strong>
					<?php echo empty( $schedule['auto'] ) ? '<span style="color:#b45309">OFF</span>' : '<span style="color:#16a34a">ON</span>'; ?>
					|
					<strong><?php echo esc_html__( 'Cadence:', 'citeleap' ); ?></strong>
					<?php echo (int) ( $schedule['posts_per_week'] ?? 3 ); ?> / <?php echo esc_html__( 'week', 'citeleap' ); ?>
					|
					<strong><?php echo esc_html__( 'Next publish slot:', 'citeleap' ); ?></strong>
					<?php echo esc_html( get_date_from_gmt( gmdate( 'Y-m-d H:i:s', $next ) ) ); ?>
				</p>
			</div>

			<h2 style="margin:1rem 0 0.5rem;"><?php echo esc_html__( 'Manual actions', 'citeleap' ); ?></h2>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline-block;margin-right:0.5rem;">
				<?php wp_nonce_field( CITELEAP_NONCE ); ?>
				<input type="hidden" name="action" value="citeleap_generate_ideas">
				<input type="number" name="count" value="10" min="1" max="30" style="width:5rem;">
				<button class="button button-primary"><?php echo esc_html__( 'Generate ideas now (LLM)', 'citeleap' ); ?></button>
			</form>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline-block;">
				<?php wp_nonce_field( CITELEAP_NONCE ); ?>
				<input type="hidden" name="action" value="citeleap_run_tick">
				<button class="button"><?php echo esc_html__( 'Run scheduler tick now', 'citeleap' ); ?></button>
			</form>

			<h2 style="margin:1.5rem 0 0.25rem;"><?php echo esc_html__( 'Add your own topics (no LLM call)', 'citeleap' ); ?></h2>
			<p style="margin:0 0 0.5rem;color:#64748b;"><?php echo esc_html__( 'Paste one topic per line. Each becomes a queued post in order. Duplicates against existing slugs and the queue are skipped automatically. No tokens are spent until you draft the post.', 'citeleap' ); ?></p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( CITELEAP_NONCE ); ?>
				<input type="hidden" name="action" value="citeleap_add_topics">
				<textarea name="topics" rows="6" class="large-text" style="font-family:ui-monospace,monospace;font-size:13px;" placeholder="<?php echo esc_attr__( "How to scale paid social with Advantage+\nB2B SaaS pricing models 2026\nWhat is a marketing capability assessment", 'citeleap' ); ?>"></textarea>
				<p style="margin-top:0.5rem;"><button class="button button-primary"><?php echo esc_html__( 'Queue these topics', 'citeleap' ); ?></button></p>
			</form>

			<h2 style="margin:2rem 0 0.5rem;"><?php echo esc_html__( 'Refresh existing posts', 'citeleap' ); ?></h2>
			<?php $refresh_settings = CiteLeap_Refresh::settings(); ?>
			<p style="margin:0 0 0.5rem;color:#64748b;">
				<?php echo esc_html__( 'Pick published posts below to add to the same planner as refresh items. Auto-refresh mode:', 'citeleap' ); ?>
				<strong><?php echo $refresh_settings['auto'] ? '<span style="color:#16a34a">ON</span>' : '<span style="color:#b45309">OFF</span>'; ?></strong>
				(<a href="<?php echo esc_url( admin_url( 'admin.php?page=citeleap&tab=settings' ) ); ?>"><?php echo esc_html__( 'change', 'citeleap' ); ?></a>)
			</p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-bottom:1rem;">
				<?php wp_nonce_field( CITELEAP_NONCE ); ?>
				<input type="hidden" name="action" value="citeleap_enqueue_refresh">
				<details>
					<summary style="cursor:pointer;font-weight:600;padding:0.5rem 0;"><?php echo esc_html__( 'Select posts to refresh', 'citeleap' ); ?></summary>
					<div style="max-height:280px;overflow:auto;border:1px solid #e2e8f0;padding:0.5rem;background:#f8fafc;border-radius:4px;">
						<?php
						$recent = get_posts( [
							'post_type'      => 'post',
							'post_status'    => 'publish',
							'posts_per_page' => 100,
							'orderby'        => 'modified',
							'order'          => 'ASC',
							'no_found_rows'  => true,
						] );
						if ( empty( $recent ) ) {
							echo '<p>' . esc_html__( 'No published posts yet.', 'citeleap' ) . '</p>';
						} else {
							foreach ( $recent as $p ) :
								$last_ref = get_post_meta( $p->ID, CITELEAP_META_REFRESH_AT, true );
								$n_ref    = (int) get_post_meta( $p->ID, CITELEAP_META_REFRESH_N, true );
						?>
								<label style="display:flex;align-items:center;gap:0.5rem;padding:0.25rem 0;font-size:13px;border-bottom:1px solid #e2e8f0;">
									<input type="checkbox" name="post_ids[]" value="<?php echo (int) $p->ID; ?>">
									<span style="flex:1;"><?php echo esc_html( $p->post_title ); ?></span>
									<span style="color:#64748b;font-size:12px;">
										<?php
										$mod = mysql2date( 'Y-m-d', $p->post_modified );
										echo esc_html( $mod );
										if ( $n_ref ) echo ' &middot; refreshed ' . (int) $n_ref . 'x';
										?>
									</span>
								</label>
						<?php
							endforeach;
						}
						?>
					</div>
				</details>
				<button class="button button-primary" style="margin-top:0.5rem;"><?php echo esc_html__( 'Queue selected for refresh', 'citeleap' ); ?></button>
			</form>

			<h2 style="margin:2rem 0 0.5rem;"><?php echo esc_html__( 'Queue', 'citeleap' ); ?> (<?php echo count( $queue ); ?>)</h2>

			<table class="widefat striped">
				<thead>
					<tr>
						<th style="width:35%;"><?php echo esc_html__( 'Title', 'citeleap' ); ?></th>
						<th><?php echo esc_html__( 'Slug', 'citeleap' ); ?></th>
						<th style="width:90px;"><?php echo esc_html__( 'Priority', 'citeleap' ); ?></th>
						<th style="width:110px;"><?php echo esc_html__( 'Status', 'citeleap' ); ?></th>
						<th style="width:160px;"><?php echo esc_html__( 'Scheduled for', 'citeleap' ); ?></th>
						<th style="width:240px;"><?php echo esc_html__( 'Actions', 'citeleap' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php if ( empty( $queue ) ) : ?>
					<tr><td colspan="6"><?php echo esc_html__( 'No ideas yet. Click "Generate ideas now" above.', 'citeleap' ); ?></td></tr>
				<?php else :
					/* Sort: refreshing/refreshed/scheduled at top, then drafted, then queued by priority desc */
					usort( $queue, function ( $a, $b ) {
						$order = [
							'refreshing'     => 0, 'refreshed'  => 1,
							'scheduled'      => 2, 'drafted'    => 3,
							'queued_refresh' => 4, 'queued'     => 5,
							'failed'         => 6,
						];
						$ao = $order[ $a['status'] ?? '' ] ?? 9;
						$bo = $order[ $b['status'] ?? '' ] ?? 9;
						if ( $ao !== $bo ) return $ao <=> $bo;
						return ( (int) ( $b['priority'] ?? 5 ) ) <=> ( (int) ( $a['priority'] ?? 5 ) );
					} );
					foreach ( $queue as $row ) :
						$status  = (string) ( $row['status'] ?? 'queued' );
						$post_id = (int) ( $row['post_id'] ?? 0 );
						$is_refresh = in_array( $status, [ 'queued_refresh', 'refreshing', 'refreshed' ], true );
				?>
					<tr>
						<td>
							<?php if ( $is_refresh ) : ?>
								<span style="background:#fef3c7;color:#92400e;padding:1px 6px;border-radius:3px;font-size:11px;font-weight:600;text-transform:uppercase;">REFRESH</span>
							<?php endif; ?>
							<strong><?php echo esc_html( (string) ( $row['title'] ?? '' ) ); ?></strong>
							<?php if ( ! empty( $row['angle'] ) ) : ?>
								<div style="color:#64748b;font-size:12px;margin-top:2px;"><?php echo esc_html( (string) $row['angle'] ); ?></div>
							<?php endif; ?>
							<?php if ( ! empty( $row['error'] ) ) : ?>
								<div style="color:#b91c1c;font-size:12px;margin-top:2px;">⚠ <?php echo esc_html( (string) $row['error'] ); ?></div>
							<?php endif; ?>
						</td>
						<td><code><?php echo esc_html( (string) ( $row['slug'] ?? '' ) ); ?></code></td>
						<td><?php echo (int) ( $row['priority'] ?? 5 ); ?></td>
						<td>
							<?php
							$color_map = [
								'queued'         => '#0369a1',
								'drafted'        => '#7c2d12',
								'scheduled'      => '#16a34a',
								'published'      => '#15803d',
								'queued_refresh' => '#b45309',
								'refreshing'     => '#0369a1',
								'refreshed'      => '#15803d',
								'failed'         => '#b91c1c',
							];
							$color = $color_map[ $status ] ?? '#475569';
							echo '<span style="color:' . esc_attr( $color ) . ';font-weight:600;">' . esc_html( str_replace( '_', ' ', $status ) ) . '</span>';
							?>
						</td>
						<td><?php echo esc_html( (string) ( $row['scheduled_for'] ?? $row['finished_at'] ?? '' ) ); ?></td>
						<td>
							<?php if ( 'queued' === $status ) : ?>
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;">
									<?php wp_nonce_field( CITELEAP_NONCE ); ?>
									<input type="hidden" name="action" value="citeleap_write_idea">
									<input type="hidden" name="idea_id" value="<?php echo esc_attr( (string) $row['id'] ); ?>">
									<button class="button button-small"><?php echo esc_html__( 'Write draft', 'citeleap' ); ?></button>
								</form>
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;">
									<?php wp_nonce_field( CITELEAP_NONCE ); ?>
									<input type="hidden" name="action" value="citeleap_remove_idea">
									<input type="hidden" name="idea_id" value="<?php echo esc_attr( (string) $row['id'] ); ?>">
									<button class="button button-small button-link-delete"><?php echo esc_html__( 'Remove', 'citeleap' ); ?></button>
								</form>
							<?php elseif ( 'queued_refresh' === $status ) : ?>
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;">
									<?php wp_nonce_field( CITELEAP_NONCE ); ?>
									<input type="hidden" name="action" value="citeleap_run_refresh">
									<input type="hidden" name="queue_id" value="<?php echo esc_attr( (string) $row['id'] ); ?>">
									<button class="button button-small button-primary"><?php echo esc_html__( 'Refresh now', 'citeleap' ); ?></button>
								</form>
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;">
									<?php wp_nonce_field( CITELEAP_NONCE ); ?>
									<input type="hidden" name="action" value="citeleap_remove_idea">
									<input type="hidden" name="idea_id" value="<?php echo esc_attr( (string) $row['id'] ); ?>">
									<button class="button button-small button-link-delete"><?php echo esc_html__( 'Remove', 'citeleap' ); ?></button>
								</form>
							<?php elseif ( $post_id ) : ?>
								<a class="button button-small" href="<?php echo esc_url( get_edit_post_link( $post_id ) ); ?>"><?php echo esc_html__( 'Edit', 'citeleap' ); ?></a>
								<a class="button button-small" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" target="_blank"><?php echo esc_html__( 'Preview', 'citeleap' ); ?></a>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}

/* admin-post handlers for planner buttons */
add_action( 'admin_post_citeleap_generate_ideas', function () {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );
	check_admin_referer( CITELEAP_NONCE );
	$count = isset( $_POST['count'] ) ? max( 1, min( 30, (int) $_POST['count'] ) ) : 10;
	$res   = CiteLeap_Generator::generate_ideas( $count );
	$msg   = $res['ok'] ? 'ideas:' . count( $res['ideas'] ) : 'err';
	wp_safe_redirect( add_query_arg( [ 'page' => 'citeleap', 'tab' => 'planner', 'citeleap_msg' => $msg ], admin_url( 'admin.php' ) ) );
	exit;
} );

add_action( 'admin_post_citeleap_write_idea', function () {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );
	check_admin_referer( CITELEAP_NONCE );
	$id  = sanitize_text_field( wp_unslash( (string) ( $_POST['idea_id'] ?? '' ) ) );
	$res = CiteLeap_Generator::write_post_from_idea( $id );
	$msg = $res['ok'] ? 'drafted:' . $res['post_id'] : 'err';
	wp_safe_redirect( add_query_arg( [ 'page' => 'citeleap', 'tab' => 'planner', 'citeleap_msg' => $msg ], admin_url( 'admin.php' ) ) );
	exit;
} );

add_action( 'admin_post_citeleap_remove_idea', function () {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );
	check_admin_referer( CITELEAP_NONCE );
	$id    = sanitize_text_field( wp_unslash( (string) ( $_POST['idea_id'] ?? '' ) ) );
	$queue = (array) get_option( CITELEAP_OPTION_QUEUE, [] );
	$queue = array_values( array_filter( $queue, fn( $r ) => ( $r['id'] ?? '' ) !== $id ) );
	update_option( CITELEAP_OPTION_QUEUE, $queue, false );
	wp_safe_redirect( add_query_arg( [ 'page' => 'citeleap', 'tab' => 'planner' ], admin_url( 'admin.php' ) ) );
	exit;
} );

add_action( 'admin_post_citeleap_run_tick', function () {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );
	check_admin_referer( CITELEAP_NONCE );
	CiteLeap_Scheduler::tick();
	wp_safe_redirect( add_query_arg( [ 'page' => 'citeleap', 'tab' => 'planner', 'citeleap_msg' => 'tick' ], admin_url( 'admin.php' ) ) );
	exit;
} );

add_action( 'admin_post_citeleap_add_topics', function () {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );
	check_admin_referer( CITELEAP_NONCE );
	$raw    = (string) wp_unslash( (string) ( $_POST['topics'] ?? '' ) );
	$lines  = preg_split( '/\r?\n/', $raw ) ?: [];
	$res    = CiteLeap_Generator::add_manual_topics( $lines );
	$msg    = sprintf( 'topics:%d:%d:%d', $res['added'], $res['skipped_dupes'], $res['total_in'] );
	wp_safe_redirect( add_query_arg( [ 'page' => 'citeleap', 'tab' => 'planner', 'citeleap_msg' => $msg ], admin_url( 'admin.php' ) ) );
	exit;
} );
