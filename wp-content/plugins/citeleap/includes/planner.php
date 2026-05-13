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
				<button class="button button-primary"><?php echo esc_html__( 'Generate ideas now', 'citeleap' ); ?></button>
			</form>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline-block;">
				<?php wp_nonce_field( CITELEAP_NONCE ); ?>
				<input type="hidden" name="action" value="citeleap_run_tick">
				<button class="button"><?php echo esc_html__( 'Run scheduler tick now', 'citeleap' ); ?></button>
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
					/* Sort: drafted/scheduled at top, then queued by priority desc */
					usort( $queue, function ( $a, $b ) {
						$order = [ 'scheduled' => 0, 'drafted' => 1, 'queued' => 2 ];
						$ao = $order[ $a['status'] ?? '' ] ?? 9;
						$bo = $order[ $b['status'] ?? '' ] ?? 9;
						if ( $ao !== $bo ) return $ao <=> $bo;
						return ( (int) ( $b['priority'] ?? 5 ) ) <=> ( (int) ( $a['priority'] ?? 5 ) );
					} );
					foreach ( $queue as $row ) :
						$status = (string) ( $row['status'] ?? 'queued' );
						$post_id = (int) ( $row['post_id'] ?? 0 );
				?>
					<tr>
						<td>
							<strong><?php echo esc_html( (string) ( $row['title'] ?? '' ) ); ?></strong>
							<?php if ( ! empty( $row['angle'] ) ) : ?>
								<div style="color:#64748b;font-size:12px;margin-top:2px;"><?php echo esc_html( (string) $row['angle'] ); ?></div>
							<?php endif; ?>
						</td>
						<td><code><?php echo esc_html( (string) ( $row['slug'] ?? '' ) ); ?></code></td>
						<td><?php echo (int) ( $row['priority'] ?? 5 ); ?></td>
						<td>
							<?php
							$color = [ 'queued' => '#0369a1', 'drafted' => '#7c2d12', 'scheduled' => '#16a34a', 'published' => '#15803d' ][ $status ] ?? '#475569';
							echo '<span style="color:' . esc_attr( $color ) . ';font-weight:600;">' . esc_html( $status ) . '</span>';
							?>
						</td>
						<td><?php echo esc_html( (string) ( $row['scheduled_for'] ?? '' ) ); ?></td>
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
