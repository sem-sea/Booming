<?php
/**
 * CiteLeap , dashboard tab.
 *
 * Shows the operator-relevant signal at a glance:
 *   - This month's token + cost ledger per provider, cap progress bars.
 *   - Counts by status (queued / drafted / scheduled / published /
 *     queued_refresh / refreshing / refreshed / failed).
 *   - Last 10 successes + last 10 errors with timestamps.
 *
 * @package CiteLeap
 */

defined( 'ABSPATH' ) || exit;

class CiteLeap_Dashboard {

	public static function render(): void {
		$usage = CiteLeap_Usage::month_usage();
		$caps  = CiteLeap_Usage::caps();
		$month = CiteLeap_Usage::current_month();
		$queue = (array) get_option( CITELEAP_OPTION_QUEUE, [] );
		$log   = (array) get_option( CITELEAP_OPTION_LOG, [] );

		/* Status counts. */
		$counts = [
			'queued' => 0, 'drafted' => 0, 'scheduled' => 0,
			'queued_refresh' => 0, 'refreshing' => 0, 'pending_review' => 0, 'refreshed' => 0,
			'failed' => 0,
		];
		foreach ( $queue as $row ) {
			$s = (string) ( $row['status'] ?? '' );
			if ( isset( $counts[ $s ] ) ) $counts[ $s ]++;
		}
		$published_this_month = self::published_count_this_month();

		?>
		<div class="citeleap-dashboard">
			<?php
			/* Pre-cap alert: 80%+ on any provider or overall. */
			foreach ( [ 'claude', 'openai', 'gemini' ] as $p ) {
				$cap = (float) ( $caps[ $p ] ?? 0 );
				if ( $cap <= 0 ) continue;
				$cost = (float) ( $usage[ $p ]['cost_usd'] ?? 0 );
				if ( $cost >= $cap ) {
					echo '<div class="notice notice-error" style="margin:0 0 1rem;padding:0.75rem 1rem;"><p style="margin:0;"><strong>' . esc_html( ucfirst( $p ) ) . ':</strong> ' . esc_html__( 'monthly budget cap reached. All new generation refused until next calendar month or until you raise the cap.', 'citeleap' ) . '</p></div>';
				} elseif ( $cost >= $cap * 0.8 ) {
					echo '<div class="notice notice-warning" style="margin:0 0 1rem;padding:0.75rem 1rem;"><p style="margin:0;"><strong>' . esc_html( ucfirst( $p ) ) . ':</strong> ' . sprintf( esc_html__( '%1$s%% of monthly cap used ($%2$s of $%3$s). Plan accordingly.', 'citeleap' ), esc_html( (string) number_format( ( $cost / $cap ) * 100, 1 ) ), esc_html( number_format( $cost, 2 ) ), esc_html( number_format( $cap, 2 ) ) ) . '</p></div>';
				}
			}
			?>
			<h2 style="margin-top:0;"><?php echo esc_html__( 'This month', 'citeleap' ); ?> , <?php echo esc_html( $month ); ?></h2>

			<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:0.75rem;">
				<?php
				$total_cost = CiteLeap_Usage::month_cost();
				self::render_kpi( __( 'Total cost', 'citeleap' ), '$' . number_format( $total_cost, 2 ), $caps['overall'] > 0 ? '/$' . number_format( $caps['overall'], 2 ) : '' );
				self::render_kpi( __( 'Posts published', 'citeleap' ), (string) $published_this_month, '' );
				self::render_kpi( __( 'Pending in queue', 'citeleap' ), (string) ( $counts['queued'] + $counts['drafted'] + $counts['scheduled'] ), '' );
				self::render_kpi( __( 'Refresh queue', 'citeleap' ), (string) ( $counts['queued_refresh'] + $counts['refreshing'] ), '' );
				?>
			</div>

			<h2 style="margin-top:1.5rem;"><?php echo esc_html__( 'Budget by provider', 'citeleap' ); ?></h2>
			<table class="widefat striped">
				<thead><tr>
					<th><?php echo esc_html__( 'Provider', 'citeleap' ); ?></th>
					<th><?php echo esc_html__( 'Calls', 'citeleap' ); ?></th>
					<th><?php echo esc_html__( 'Input tokens', 'citeleap' ); ?></th>
					<th><?php echo esc_html__( 'Output tokens', 'citeleap' ); ?></th>
					<th><?php echo esc_html__( 'Cost', 'citeleap' ); ?></th>
					<th style="width:200px;"><?php echo esc_html__( 'Cap', 'citeleap' ); ?></th>
				</tr></thead>
				<tbody>
				<?php foreach ( [ 'claude', 'openai', 'gemini' ] as $p ) :
					$row  = (array) ( $usage[ $p ] ?? [] );
					$cost = (float) ( $row['cost_usd'] ?? 0 );
					$cap  = (float) ( $caps[ $p ] ?? 0 );
					$pct  = $cap > 0 ? min( 100, ( $cost / $cap ) * 100 ) : 0;
					$color = $pct >= 100 ? '#b91c1c' : ( $pct >= 80 ? '#b45309' : '#16a34a' );
				?>
					<tr>
						<td><strong><?php echo esc_html( ucfirst( $p ) ); ?></strong></td>
						<td><?php echo (int) ( $row['calls'] ?? 0 ); ?></td>
						<td><?php echo number_format( (int) ( $row['input_tokens']  ?? 0 ) ); ?></td>
						<td><?php echo number_format( (int) ( $row['output_tokens'] ?? 0 ) ); ?></td>
						<td>$<?php echo number_format( $cost, 4 ); ?></td>
						<td>
							<?php if ( $cap > 0 ) : ?>
								<div style="background:#e2e8f0;border-radius:4px;height:14px;position:relative;overflow:hidden;">
									<div style="background:<?php echo esc_attr( $color ); ?>;width:<?php echo (int) $pct; ?>%;height:100%;"></div>
								</div>
								<small><?php echo number_format( $pct, 1 ); ?>% of $<?php echo number_format( $cap, 2 ); ?></small>
							<?php else : ?>
								<em style="color:#64748b;"><?php echo esc_html__( 'unlimited', 'citeleap' ); ?></em>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>

			<h2 style="margin-top:1.5rem;"><?php echo esc_html__( 'Status counts', 'citeleap' ); ?></h2>
			<div style="display:flex;flex-wrap:wrap;gap:0.5rem;">
				<?php foreach ( $counts as $k => $v ) : ?>
					<span style="background:#f1f5f9;border-radius:4px;padding:0.25rem 0.75rem;font-size:13px;">
						<strong><?php echo (int) $v; ?></strong> <?php echo esc_html( str_replace( '_', ' ', $k ) ); ?>
					</span>
				<?php endforeach; ?>
				<span style="background:#dcfce7;border-radius:4px;padding:0.25rem 0.75rem;font-size:13px;">
					<strong><?php echo (int) $published_this_month; ?></strong> <?php echo esc_html__( 'published (this month)', 'citeleap' ); ?>
				</span>
			</div>

			<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-top:1.5rem;">
				<div>
					<h2 style="margin:0 0 0.5rem;"><?php echo esc_html__( 'Recent successes', 'citeleap' ); ?></h2>
					<?php self::render_log_filtered( $log, 'success' ); ?>
				</div>
				<div>
					<h2 style="margin:0 0 0.5rem;"><?php echo esc_html__( 'Recent errors', 'citeleap' ); ?></h2>
					<?php self::render_log_filtered( $log, 'error' ); ?>
				</div>
			</div>
		</div>
		<?php
	}

	private static function render_kpi( string $label, string $value, string $sub ): void {
		echo '<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:0.875rem 1rem;">';
		echo '<div style="color:#64748b;font-size:12px;text-transform:uppercase;letter-spacing:0.05em;">' . esc_html( $label ) . '</div>';
		echo '<div style="font-size:24px;font-weight:700;color:#0f172a;line-height:1.2;margin-top:0.25rem;">' . esc_html( $value );
		if ( $sub ) echo '<span style="font-size:14px;font-weight:500;color:#64748b;"> ' . esc_html( $sub ) . '</span>';
		echo '</div></div>';
	}

	private static function render_log_filtered( array $log, string $kind ): void {
		/* Severity-driven split: any entry with severity info is a
		 * success row; warn / error / critical are errors. Legacy
		 * entries (no severity) fall back to event-name allowlist. */
		$success_events = [ 'ideas_generated', 'post_drafted', 'post_scheduled', 'post_refreshed', 'refresh_queued', 'test_ok' ];
		$rows = [];
		foreach ( array_reverse( $log ) as $row ) {
			$sev = (string) ( $row['severity'] ?? '' );
			$ev  = (string) ( $row['event'] ?? '' );
			if ( $sev ) {
				$is_success = ( 'info' === $sev );
			} else {
				$is_success = in_array( $ev, $success_events, true );
			}
			if ( 'success' === $kind && $is_success ) $rows[] = $row;
			if ( 'error' === $kind && ! $is_success ) $rows[] = $row;
			if ( count( $rows ) >= 10 ) break;
		}
		if ( empty( $rows ) ) {
			echo '<p style="color:#64748b;font-style:italic;">' . esc_html__( 'No recent activity.', 'citeleap' ) . '</p>';
			return;
		}
		echo '<table class="widefat striped"><tbody>';
		foreach ( $rows as $row ) {
			$color = 'error' === $kind ? '#b91c1c' : '#16a34a';
			echo '<tr>';
			echo '<td style="width:120px;"><code style="font-size:11px;">' . esc_html( (string) ( $row['time'] ?? '' ) ) . '</code></td>';
			echo '<td><strong style="color:' . esc_attr( $color ) . ';">' . esc_html( (string) ( $row['event'] ?? '' ) ) . '</strong></td>';
			echo '<td style="font-size:12px;color:#475569;">' . esc_html( (string) ( $row['detail'] ?? '' ) ) . '</td>';
			echo '</tr>';
		}
		echo '</tbody></table>';
	}

	private static function published_count_this_month(): int {
		$start = wp_date( 'Y-m-01 00:00:00' );
		$end   = wp_date( 'Y-m-t 23:59:59' );
		$ids   = get_posts( [
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'meta_key'       => CITELEAP_META_SOURCE,
			'date_query'     => [ [ 'after' => $start, 'before' => $end, 'inclusive' => true ] ],
		] );
		return count( $ids );
	}
}
