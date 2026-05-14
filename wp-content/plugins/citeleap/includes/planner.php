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
					<strong><?php echo esc_html__( 'Auto mode:', 'citeleap' ); ?></strong>
					<?php $mode_label = CiteLeap_Scheduler::auto_mode( $schedule ); ?>
					<span style="color:<?php echo 'off' === $mode_label ? '#b45309' : '#16a34a'; ?>;font-weight:600;text-transform:uppercase;"><?php echo esc_html( $mode_label ); ?></span>
					|
					<strong><?php echo esc_html__( 'Cadence:', 'citeleap' ); ?></strong>
					<?php echo (int) ( $schedule['posts_per_week'] ?? 3 ); ?> / <?php echo esc_html__( 'week', 'citeleap' ); ?>
					|
					<strong><?php echo esc_html__( 'Next publish slot:', 'citeleap' ); ?></strong>
					<?php echo esc_html( citeleap_format( $next ) ); ?>
				</p>
			</div>

			<details style="background:#fffbeb;border:1px solid #fde68a;border-radius:6px;padding:0.5rem 0.875rem;margin:0 0 1rem;">
				<summary style="cursor:pointer;font-weight:600;color:#92400e;"><?php echo esc_html__( 'How CiteLeap works (click to expand)', 'citeleap' ); ?></summary>
				<div class="citeleap-flow" style="margin-top:0.75rem;">
					<div class="citeleap-flow-step"><strong><span class="citeleap-flow-num">1</span> Generate or paste topics</strong>Click "Generate ideas" to have the reasoning model brainstorm, OR paste your own topics one per line. Both land in the queue with status=queued.</div>
					<div class="citeleap-flow-step"><strong><span class="citeleap-flow-num">2</span> Plan or pin</strong>Optionally pin a specific publish datetime per row. Pinned rows fire ahead of the auto cadence at exactly that time.</div>
					<div class="citeleap-flow-step"><strong><span class="citeleap-flow-num">3</span> Write draft</strong>Click "Write draft" to draft manually, OR enable Auto mode = Draft / Publish to let the hourly cron tick do it. The writing model produces a 1,200 to 1,600 word GEO-compliant post.</div>
					<div class="citeleap-flow-step"><strong><span class="citeleap-flow-num">4</span> Schedule or publish</strong>Drafted rows can be scheduled to a specific datetime, published immediately, paused, or removed. Scheduled rows can be rescheduled or unscheduled.</div>
					<div class="citeleap-flow-step"><strong><span class="citeleap-flow-num">5</span> Refresh anytime</strong>Pick existing posts to refresh (checkbox list, paste-list, or auto-pick). Refresh mode = Draft parks the rewrite as pending review; Live overwrites the post.</div>
				</div>
				<p class="citeleap-help" style="margin:0.5rem 0 0;"><strong><?php echo esc_html__( 'Per-row coherence:', 'citeleap' ); ?></strong> <?php echo esc_html__( 'every queue row supports Pause / Resume / Remove. Queued rows add Write draft + Pin. Drafted rows add Schedule + Publish now. Scheduled rows add Reschedule + Unschedule + Publish now. Failed rows add Retry. Pending-review rows add Approve + Reject. Refreshing rows add Reset stuck.', 'citeleap' ); ?></p>
			</details>

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
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline-block;" onsubmit="return confirm('<?php echo esc_js( __( 'Spread every unplanned queued topic across the cadence (e.g. 30 posts / month becomes one every 24 hours). Manually planned topics are not touched. Continue?', 'citeleap' ) ); ?>');">
				<?php wp_nonce_field( CITELEAP_NONCE ); ?>
				<input type="hidden" name="action" value="citeleap_distribute_queue">
				<button class="button"><?php echo esc_html__( 'Auto-distribute across calendar', 'citeleap' ); ?></button>
			</form>
			<p class="citeleap-help"><strong><?php echo esc_html__( 'Generate ideas now:', 'citeleap' ); ?></strong> <?php echo esc_html__( 'calls your reasoning model with the idea prompt, returns N unique titles, dedupes against existing slugs, persists to the queue. Takes 5 to 15 seconds. Cost: roughly $0.005 per idea on Claude.', 'citeleap' ); ?>
			<br><strong><?php echo esc_html__( 'Run scheduler tick now:', 'citeleap' ); ?></strong> <?php echo esc_html__( 'manually fires the hourly cron. Useful if you do not want to wait. Runs refresh tick first, then content tick. Honors Auto mode (Off / Draft / Publish).', 'citeleap' ); ?></p>

			<h2 style="margin:1.5rem 0 0.25rem;"><?php echo esc_html__( 'Add your own topics (no LLM call)', 'citeleap' ); ?></h2>
			<p style="margin:0 0 0.5rem;color:#64748b;"><?php echo esc_html__( 'Paste one topic per line. Each becomes a queued post in order. Duplicates against existing slugs and the queue are skipped automatically. No tokens are spent until you draft the post.', 'citeleap' ); ?></p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( CITELEAP_NONCE ); ?>
				<input type="hidden" name="action" value="citeleap_add_topics">
				<textarea name="topics" rows="6" class="large-text" style="font-family:ui-monospace,monospace;font-size:13px;" placeholder="<?php echo esc_attr__( "How to scale paid social with Advantage+\nB2B SaaS pricing models 2026\nWhat is a marketing capability assessment", 'citeleap' ); ?>"></textarea>
				<p style="margin-top:0.5rem;"><button class="button button-primary"><?php echo esc_html__( 'Queue these topics', 'citeleap' ); ?></button></p>
			</form>

			<h2 style="margin:2rem 0 0.5rem;"><?php echo esc_html__( 'Refresh existing posts', 'citeleap' ); ?></h2>

			<details style="margin:0 0 1rem;background:#f8fafc;border:1px solid #e2e8f0;border-radius:4px;padding:0.5rem 0.75rem;">
				<summary style="cursor:pointer;font-weight:600;"><?php echo esc_html__( 'Bulk add by paste (post IDs, slugs, or URLs)', 'citeleap' ); ?></summary>
				<p style="margin:0.5rem 0;color:#64748b;font-size:13px;"><?php echo esc_html__( 'One per line. Each line can be a numeric post ID, a slug (e.g. "my-post"), or a full permalink URL. Duplicates skipped automatically.', 'citeleap' ); ?></p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( CITELEAP_NONCE ); ?>
					<input type="hidden" name="action" value="citeleap_bulk_refresh">
					<textarea name="paste" rows="5" class="large-text" style="font-family:ui-monospace,monospace;font-size:13px;" placeholder="123&#10;my-blog-post-slug&#10;https://example.com/blog/another-post/"></textarea>
					<p style="margin-top:0.5rem;"><button class="button button-primary"><?php echo esc_html__( 'Queue these for refresh', 'citeleap' ); ?></button></p>
				</form>
			</details>

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
						<th style="width:32%;"><?php echo esc_html__( 'Title', 'citeleap' ); ?></th>
						<th><?php echo esc_html__( 'Slug', 'citeleap' ); ?></th>
						<th style="width:80px;"><?php echo esc_html__( 'Priority', 'citeleap' ); ?></th>
						<th style="width:110px;"><?php echo esc_html__( 'Status', 'citeleap' ); ?></th>
						<th style="width:170px;"><?php echo esc_html__( 'Next / Scheduled for', 'citeleap' ); ?></th>
						<th style="width:320px;"><?php echo esc_html__( 'Actions', 'citeleap' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php if ( empty( $queue ) ) : ?>
					<tr><td colspan="6"><?php echo esc_html__( 'No ideas yet. Click "Generate ideas now" above.', 'citeleap' ); ?></td></tr>
				<?php else :
					/* Sort: refreshing/refreshed/scheduled at top, then drafted, then queued by priority desc */
					usort( $queue, function ( $a, $b ) {
						$order = [
							'refreshing'     => 0, 'pending_review' => 1, 'refreshed' => 2,
							'scheduled'      => 3, 'drafted'        => 4,
							'queued_refresh' => 5, 'queued'         => 6,
							'failed'         => 7,
						];
						$ao = $order[ $a['status'] ?? '' ] ?? 9;
						$bo = $order[ $b['status'] ?? '' ] ?? 9;
						if ( $ao !== $bo ) return $ao <=> $bo;
						return ( (int) ( $b['priority'] ?? 5 ) ) <=> ( (int) ( $a['priority'] ?? 5 ) );
					} );
					foreach ( $queue as $row ) :
						$status  = (string) ( $row['status'] ?? 'queued' );
						$post_id = (int) ( $row['post_id'] ?? 0 );
						$is_paused = ! empty( $row['paused'] );
						$is_refresh = in_array( $status, [ 'queued_refresh', 'refreshing', 'refreshed', 'pending_review' ], true );
				?>
					<tr>
						<td>
							<?php if ( $is_refresh ) : ?>
								<span style="background:#fef3c7;color:#92400e;padding:1px 6px;border-radius:3px;font-size:11px;font-weight:600;text-transform:uppercase;">REFRESH</span>
							<?php endif; ?>
							<?php if ( $is_paused ) : ?>
								<span style="background:#e0e7ff;color:#3730a3;padding:1px 6px;border-radius:3px;font-size:11px;font-weight:600;text-transform:uppercase;">PAUSED</span>
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
								'pending_review' => '#9333ea',
								'refreshed'      => '#15803d',
								'failed'         => '#b91c1c',
							];
							$color = $color_map[ $status ] ?? '#475569';
							echo '<span style="color:' . esc_attr( $color ) . ';font-weight:600;">' . esc_html( str_replace( '_', ' ', $status ) ) . '</span>';
							?>
						</td>
						<td>
							<?php
							if ( ! empty( $row['scheduled_for'] ) ) {
								echo esc_html( (string) $row['scheduled_for'] );
							} elseif ( ! empty( $row['finished_at'] ) ) {
								echo esc_html( (string) $row['finished_at'] );
							} elseif ( 'queued' === $status ) {
								$eta = CiteLeap_Scheduler::eta_for( $row, $schedule );
								if ( $eta > 0 ) {
									$pinned = ! empty( $row['publish_at'] );
									echo $pinned
									? '<span title="' . esc_attr__( 'Manually planned for a specific date and time.', 'citeleap' ) . '" style="color:#16a34a;font-weight:700;font-size:13px;">&#10003;</span> <small style="color:#16a34a;font-weight:600;">' . esc_html__( 'planned: ', 'citeleap' ) . '</small>'
									: '<span title="' . esc_attr__( 'Auto-planned by the scheduler at the next slot.', 'citeleap' ) . '" style="color:#0369a1;font-weight:700;font-size:13px;">&#10003;</span> <small style="color:#0369a1;font-weight:600;">' . esc_html__( 'auto-planned: ', 'citeleap' ) . '</small>';
									echo esc_html( citeleap_format( $eta ) );
								} else {
									echo '<small style="color:#9ca3af;">' . esc_html__( 'auto mode off', 'citeleap' ) . '</small>';
								}
							}
							?>
						</td>
						<td>
							<?php if ( 'queued' === $status ) :
								$pinned_at = (string) ( $row['publish_at'] ?? '' );
								$pinned_local = '';
								if ( $pinned_at ) {
									$pl = ( new DateTimeImmutable( $pinned_at, new DateTimeZone( 'UTC' ) ) )->setTimezone( citeleap_tz() );
									$pinned_local = $pl->format( 'Y-m-d\TH:i' );
								}
							?>
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;">
									<?php wp_nonce_field( CITELEAP_NONCE ); ?>
									<input type="hidden" name="action" value="citeleap_write_idea">
									<input type="hidden" name="idea_id" value="<?php echo esc_attr( (string) $row['id'] ); ?>">
									<button class="button button-small"><?php echo esc_html__( 'Write draft', 'citeleap' ); ?></button>
								</form>
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline-flex;align-items:center;gap:0.25rem;">
									<?php wp_nonce_field( CITELEAP_NONCE ); ?>
									<input type="hidden" name="action" value="citeleap_pin_datetime">
									<input type="hidden" name="idea_id" value="<?php echo esc_attr( (string) $row['id'] ); ?>">
									<input type="datetime-local" name="publish_at" value="<?php echo esc_attr( $pinned_local ); ?>" style="font-size:11px;padding:1px 2px;">
									<button class="button button-small" title="<?php echo esc_attr__( 'Plan a specific publish date and time for this topic. The auto-tick will draft and schedule it for exactly that moment. Clear the field and click Plan again to release.', 'citeleap' ); ?>"><?php echo esc_html__( 'Plan', 'citeleap' ); ?></button>
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
							<?php elseif ( 'pending_review' === $status ) : ?>
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;">
									<?php wp_nonce_field( CITELEAP_NONCE ); ?>
									<input type="hidden" name="action" value="citeleap_approve_refresh">
									<input type="hidden" name="queue_id" value="<?php echo esc_attr( (string) $row['id'] ); ?>">
									<input type="hidden" name="post_id"  value="<?php echo (int) $post_id; ?>">
									<button class="button button-small button-primary"><?php echo esc_html__( 'Approve', 'citeleap' ); ?></button>
								</form>
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;">
									<?php wp_nonce_field( CITELEAP_NONCE ); ?>
									<input type="hidden" name="action" value="citeleap_reject_refresh">
									<input type="hidden" name="queue_id" value="<?php echo esc_attr( (string) $row['id'] ); ?>">
									<input type="hidden" name="post_id"  value="<?php echo (int) $post_id; ?>">
									<button class="button button-small button-link-delete" onclick="return confirm('<?php echo esc_js( __( 'Reject the pending refresh? The live post is not touched and the proposed content will be deleted.', 'citeleap' ) ); ?>');"><?php echo esc_html__( 'Reject', 'citeleap' ); ?></button>
								</form>
								<a class="button button-small" href="<?php echo esc_url( get_edit_post_link( $post_id ) ); ?>"><?php echo esc_html__( 'View live post', 'citeleap' ); ?></a>
							<?php elseif ( 'refreshing' === $status ) : ?>
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;">
									<?php wp_nonce_field( CITELEAP_NONCE ); ?>
									<input type="hidden" name="action" value="citeleap_reset_stuck">
									<input type="hidden" name="queue_id" value="<?php echo esc_attr( (string) $row['id'] ); ?>">
									<button class="button button-small" onclick="return confirm('<?php echo esc_js( __( 'Reset this stuck refresh? Use only if a previous tick crashed mid-flight.', 'citeleap' ) ); ?>');"><?php echo esc_html__( 'Reset stuck', 'citeleap' ); ?></button>
								</form>
							<?php elseif ( 'drafted' === $status && $post_id ) :
								$pin_local = '';
								if ( ! empty( $row['publish_at'] ) ) {
									$pl = ( new DateTimeImmutable( (string) $row['publish_at'], new DateTimeZone( 'UTC' ) ) )->setTimezone( citeleap_tz() );
									$pin_local = $pl->format( 'Y-m-d\TH:i' );
								}
							?>
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline-flex;align-items:center;gap:0.25rem;">
									<?php wp_nonce_field( CITELEAP_NONCE ); ?>
									<input type="hidden" name="action" value="citeleap_schedule_drafted">
									<input type="hidden" name="id" value="<?php echo esc_attr( (string) $row['id'] ); ?>">
									<input type="datetime-local" name="when" value="<?php echo esc_attr( $pin_local ); ?>" required style="font-size:11px;padding:1px 2px;">
									<button class="button button-small button-primary"><?php echo esc_html__( 'Schedule', 'citeleap' ); ?></button>
								</form>
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;" onsubmit="return confirm('<?php echo esc_js( __( 'Publish this draft immediately?', 'citeleap' ) ); ?>');">
									<?php wp_nonce_field( CITELEAP_NONCE ); ?>
									<input type="hidden" name="action" value="citeleap_publish_now">
									<input type="hidden" name="id" value="<?php echo esc_attr( (string) $row['id'] ); ?>">
									<button class="button button-small"><?php echo esc_html__( 'Publish now', 'citeleap' ); ?></button>
								</form>
								<a class="button button-small" href="<?php echo esc_url( get_edit_post_link( $post_id ) ); ?>"><?php echo esc_html__( 'Edit', 'citeleap' ); ?></a>
								<a class="button button-small" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" target="_blank"><?php echo esc_html__( 'Preview', 'citeleap' ); ?></a>
							<?php elseif ( 'scheduled' === $status && $post_id ) :
								$pin_local = '';
								if ( ! empty( $row['scheduled_for'] ) ) {
									$pl = strtotime( (string) $row['scheduled_for'] );
									if ( $pl ) {
										$pi = ( new DateTimeImmutable( '@' . $pl ) )->setTimezone( citeleap_tz() );
										$pin_local = $pi->format( 'Y-m-d\TH:i' );
									}
								}
							?>
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline-flex;align-items:center;gap:0.25rem;">
									<?php wp_nonce_field( CITELEAP_NONCE ); ?>
									<input type="hidden" name="action" value="citeleap_reschedule">
									<input type="hidden" name="id" value="<?php echo esc_attr( (string) $row['id'] ); ?>">
									<input type="datetime-local" name="when" value="<?php echo esc_attr( $pin_local ); ?>" required style="font-size:11px;padding:1px 2px;">
									<button class="button button-small"><?php echo esc_html__( 'Reschedule', 'citeleap' ); ?></button>
								</form>
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;">
									<?php wp_nonce_field( CITELEAP_NONCE ); ?>
									<input type="hidden" name="action" value="citeleap_unschedule">
									<input type="hidden" name="id" value="<?php echo esc_attr( (string) $row['id'] ); ?>">
									<button class="button button-small"><?php echo esc_html__( 'Unschedule', 'citeleap' ); ?></button>
								</form>
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;" onsubmit="return confirm('<?php echo esc_js( __( 'Publish now and ignore the scheduled time?', 'citeleap' ) ); ?>');">
									<?php wp_nonce_field( CITELEAP_NONCE ); ?>
									<input type="hidden" name="action" value="citeleap_publish_now">
									<input type="hidden" name="id" value="<?php echo esc_attr( (string) $row['id'] ); ?>">
									<button class="button button-small"><?php echo esc_html__( 'Publish now', 'citeleap' ); ?></button>
								</form>
								<a class="button button-small" href="<?php echo esc_url( get_edit_post_link( $post_id ) ); ?>"><?php echo esc_html__( 'Edit', 'citeleap' ); ?></a>
							<?php elseif ( 'failed' === $status ) : ?>
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;">
									<?php wp_nonce_field( CITELEAP_NONCE ); ?>
									<input type="hidden" name="action" value="citeleap_retry">
									<input type="hidden" name="id" value="<?php echo esc_attr( (string) $row['id'] ); ?>">
									<button class="button button-small button-primary"><?php echo esc_html__( 'Retry', 'citeleap' ); ?></button>
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
							<?php /* Pause / Resume is universal for any non-terminal row. */
							if ( ! in_array( $status, [ 'published', 'refreshed' ], true ) ) :
								$paction = $is_paused ? 'citeleap_resume' : 'citeleap_pause';
								$plabel  = $is_paused ? __( 'Resume', 'citeleap' ) : __( 'Pause', 'citeleap' );
							?>
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;">
									<?php wp_nonce_field( CITELEAP_NONCE ); ?>
									<input type="hidden" name="action" value="<?php echo esc_attr( $paction ); ?>">
									<input type="hidden" name="id" value="<?php echo esc_attr( (string) $row['id'] ); ?>">
									<button class="button button-small"><?php echo esc_html( $plabel ); ?></button>
								</form>
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

add_action( 'admin_post_citeleap_distribute_queue', function () {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );
	check_admin_referer( CITELEAP_NONCE );
	$n = CiteLeap_Scheduler::distribute_queue();
	wp_safe_redirect( add_query_arg( [ 'page' => 'citeleap', 'tab' => 'planner', 'citeleap_msg' => 'distributed:' . $n ], admin_url( 'admin.php' ) ) );
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

/* Set or clear the per-row publish_at override. */
add_action( 'admin_post_citeleap_pin_datetime', function () {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );
	check_admin_referer( CITELEAP_NONCE );
	$idea_id = sanitize_text_field( wp_unslash( (string) ( $_POST['idea_id'] ?? '' ) ) );
	$dt      = sanitize_text_field( wp_unslash( (string) ( $_POST['publish_at'] ?? '' ) ) );
	$queue   = (array) get_option( CITELEAP_OPTION_QUEUE, [] );
	$found   = false;
	foreach ( $queue as $i => $row ) {
		if ( ( $row['id'] ?? '' ) !== $idea_id ) continue;
		if ( '' === $dt ) {
			unset( $queue[ $i ]['publish_at'] );
		} else {
			$ts = strtotime( $dt );
			if ( $ts > 0 ) $queue[ $i ]['publish_at'] = gmdate( 'Y-m-d H:i:s', $ts );
		}
		$found = true;
		break;
	}
	if ( $found ) {
		update_option( CITELEAP_OPTION_QUEUE, array_values( $queue ), false );
		CiteLeap_Log::add( 'pin_datetime', $idea_id . ' -> ' . ( $dt ?: 'cleared' ), 'info' );
	}
	wp_safe_redirect( add_query_arg( [ 'page' => 'citeleap', 'tab' => 'planner', 'citeleap_msg' => 'pinned' ], admin_url( 'admin.php' ) ) );
	exit;
} );
