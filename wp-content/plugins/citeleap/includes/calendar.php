<?php
/**
 * CiteLeap , calendar.php
 *
 * Month-grid overview of content that will be created or refreshed.
 *
 * Three marker tiers per day:
 *   GREEN check , a topic is planned for an EXACT date+time (operator
 *                  set publish_at via the Plan button, OR a CiteLeap
 *                  draft already has WP post_status=future at that
 *                  moment).
 *   BLUE  check , the day falls on the auto-tick cadence and a topic
 *                  WILL be drafted/scheduled there automatically if
 *                  the queue is non-empty.
 *   GRAY        , nothing.
 *
 * Refresh items use the same colour code with a small "R" badge.
 *
 * @package CiteLeap
 */

defined( 'ABSPATH' ) || exit;

class CiteLeap_Calendar {

	/** Build the per-day event map for a given month (Y-m). */
	public static function month_events( string $month ): array {
		$ts = strtotime( $month . '-01' );
		if ( ! $ts ) $ts = time();
		$days_in_month = (int) date( 't', $ts );
		$first_dow     = (int) date( 'w', $ts ); // 0..6 (Sun..Sat)
		$year  = (int) date( 'Y', $ts );
		$month_n = (int) date( 'n', $ts );

		$events = [];
		for ( $d = 1; $d <= $days_in_month; $d++ ) {
			$key = sprintf( '%04d-%02d-%02d', $year, $month_n, $d );
			$events[ $key ] = [ 'planned' => [], 'auto' => [], 'refresh_planned' => [], 'refresh_auto' => [] ];
		}

		/* 1. GREEN: queue rows with publish_at OR scheduled_for in this month. */
		$queue = (array) get_option( CITELEAP_OPTION_QUEUE, [] );
		foreach ( $queue as $row ) {
			$status = (string) ( $row['status'] ?? '' );
			$is_refresh = in_array( $status, [ 'queued_refresh', 'refreshing', 'refreshed', 'pending_review' ], true )
				|| ( ( $row['type'] ?? '' ) === 'refresh' );
			$dt = '';
			if ( ! empty( $row['publish_at'] ) ) {
				$dt = (string) $row['publish_at'];
			} elseif ( ! empty( $row['scheduled_for'] ) ) {
				$dt = (string) $row['scheduled_for'];
			}
			if ( ! $dt ) continue;
			$day = substr( $dt, 0, 10 );
			if ( ! isset( $events[ $day ] ) ) continue;
			$bucket = $is_refresh ? 'refresh_planned' : 'planned';
			$events[ $day ][ $bucket ][] = [
				'title'  => (string) ( $row['title'] ?? $row['slug'] ?? '' ),
				'slug'   => (string) ( $row['slug']  ?? '' ),
				'status' => $status,
			];
		}

		/* 2. GREEN: WP future-status posts (auto-scheduled by core) in
		 *    this month. Include only CiteLeap-source posts so the
		 *    calendar doesn't surface every theme-created future post. */
		$future = get_posts( [
			'post_type'      => 'post',
			'post_status'    => 'future',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'meta_key'       => CITELEAP_META_SOURCE,
			'date_query'     => [ [
				'after'     => $year . '-' . sprintf( '%02d', $month_n ) . '-01 00:00:00',
				'before'    => $year . '-' . sprintf( '%02d', $month_n ) . '-' . $days_in_month . ' 23:59:59',
				'inclusive' => true,
				'column'    => 'post_date',
			] ],
		] );
		foreach ( $future as $pid ) {
			$pid = (int) $pid;
			$day = substr( (string) get_post_field( 'post_date', $pid ), 0, 10 );
			if ( ! isset( $events[ $day ] ) ) continue;
			$events[ $day ]['planned'][] = [
				'title'  => (string) get_the_title( $pid ),
				'slug'   => (string) get_post_field( 'post_name', $pid ),
				'status' => 'wp_future',
			];
		}

		/* 3. BLUE: auto-tick cadence slots. Each posts-per-week interval
		 *    in this month gets a slot. We mark them as "auto" only if
		 *    the queue has at least one runnable item (queued, unpinned,
		 *    unpaused) for the new-content track. */
		$schedule = (array) get_option( CITELEAP_OPTION_SCHEDULE, [] );
		$mode     = class_exists( 'CiteLeap_Scheduler' ) ? CiteLeap_Scheduler::auto_mode( $schedule ) : 'off';
		$has_runnable = false;
		foreach ( $queue as $row ) {
			if ( ( $row['status'] ?? '' ) === 'queued' && empty( $row['publish_at'] ) && empty( $row['paused'] ) ) {
				$has_runnable = true; break;
			}
		}
		if ( in_array( $mode, [ 'draft', 'publish' ], true ) && $has_runnable ) {
			$start = isset( $schedule['start_date'] ) ? strtotime( (string) $schedule['start_date'] ) : 0;
			if ( ! $start ) $start = time();
			$interval = CiteLeap_Scheduler::cadence_interval( $schedule );
			$end_ts   = strtotime( sprintf( '%04d-%02d-%02d 23:59:59', $year, $month_n, $days_in_month ) );
			$slot     = $start;
			while ( $slot <= $end_ts ) {
				$day = wp_date( 'Y-m-d', $slot );
				if ( isset( $events[ $day ] ) ) {
					$events[ $day ]['auto'][] = [
						'time'  => wp_date( 'H:i', $slot ),
						'mode'  => $mode,
					];
				}
				$slot += $interval;
			}
		}

		/* 4. BLUE: refresh auto-cadence slots (same pattern, separate
		 *    track). Refresh tick is throttled to 1 per hourly cron tick,
		 *    so a "slot" maps roughly to "next due posts" times an
		 *    interval-by-week. */
		$rs = class_exists( 'CiteLeap_Refresh' ) ? CiteLeap_Refresh::settings() : [];
		$rmode = (string) ( $rs['auto_mode'] ?? 'off' );
		if ( in_array( $rmode, [ 'draft', 'live' ], true ) ) {
			$rstart    = time();
			$rinterval = CiteLeap_Scheduler::cadence_interval( $rs );
			$rend_ts   = strtotime( sprintf( '%04d-%02d-%02d 23:59:59', $year, $month_n, $days_in_month ) );
			$rslot     = $rstart;
			while ( $rslot <= $rend_ts ) {
				$day = wp_date( 'Y-m-d', $rslot );
				if ( isset( $events[ $day ] ) ) {
					$events[ $day ]['refresh_auto'][] = [
						'time' => wp_date( 'H:i', $rslot ),
						'mode' => $rmode,
					];
				}
				$rslot += $rinterval;
			}
		}

		return [
			'year'           => $year,
			'month'          => $month_n,
			'days_in_month'  => $days_in_month,
			'first_dow'      => $first_dow,
			'events'         => $events,
			'has_runnable'   => $has_runnable,
			'mode'           => $mode,
			'refresh_mode'   => $rmode,
		];
	}

	public static function render(): void {
		if ( ! CiteLeap_Plan::has( 'calendar' ) ) {
			CiteLeap_Plan::render_locked_notice(
				'calendar',
				__( 'Calendar overview', 'citeleap' ),
				__( 'The month-grid calendar shows planned + auto-planned + refresh entries at a glance, with pin / drag rescheduling. Available on Pro and above. Solo accounts get the Planner queue with the same data in list form.', 'citeleap' )
			);
			return;
		}
		$today_month = wp_date( 'Y-m' );
		$month       = isset( $_GET['m'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['m'] ) ) : $today_month;
		if ( ! preg_match( '/^\d{4}-\d{2}$/', $month ) ) $month = $today_month;
		$d  = self::month_events( $month );

		$prev_ts = strtotime( $month . '-01 -1 month' );
		$next_ts = strtotime( $month . '-01 +1 month' );
		$prev_m  = wp_date( 'Y-m', $prev_ts );
		$next_m  = wp_date( 'Y-m', $next_ts );
		?>
		<div class="citeleap-calendar">
			<div style="display:flex;align-items:center;gap:0.75rem;margin:0 0 0.75rem;">
				<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=citeleap&tab=calendar&m=' . $prev_m ) ); ?>">&larr; <?php echo esc_html( wp_date( 'F Y', $prev_ts ) ); ?></a>
				<h2 style="margin:0;font-size:18px;flex:1;text-align:center;"><?php echo esc_html( wp_date( 'F Y', strtotime( $month . '-01' ) ) ); ?></h2>
				<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=citeleap&tab=calendar&m=' . $next_m ) ); ?>"><?php echo esc_html( wp_date( 'F Y', $next_ts ) ); ?> &rarr;</a>
			</div>

			<div style="display:flex;gap:1rem;flex-wrap:wrap;margin:0 0 0.75rem;font-size:13px;color:#475569;">
				<span><span style="color:#16a34a;font-weight:700;">&#10003;</span> <?php echo esc_html__( 'Planned (exact date and time set)', 'citeleap' ); ?></span>
				<span><span style="color:#0369a1;font-weight:700;">&#10003;</span> <?php echo esc_html__( 'Auto-planned (scheduler will fill this slot)', 'citeleap' ); ?></span>
				<span style="background:#fef3c7;color:#92400e;padding:0 5px;border-radius:3px;font-size:11px;font-weight:700;">R</span> <?php echo esc_html__( 'Refresh', 'citeleap' ); ?>
			</div>

			<table class="citeleap-cal" style="width:100%;border-collapse:separate;border-spacing:6px;table-layout:fixed;">
				<thead>
					<tr style="color:#64748b;font-size:12px;text-transform:uppercase;letter-spacing:0.05em;text-align:center;">
						<?php foreach ( [ 'Mon','Tue','Wed','Thu','Fri','Sat','Sun' ] as $wd ) : ?>
							<th><?php echo esc_html( $wd ); ?></th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
				<?php
				/* Render as Monday-first week. PHP date('w') is Sunday=0,
				 * so map Sun=6 to keep the grid Mon..Sun. */
				$first_dow = ( $d['first_dow'] + 6 ) % 7;
				$total_cells = $first_dow + $d['days_in_month'];
				$rows = (int) ceil( $total_cells / 7 );
				$day_num = 1;
				$today_str = wp_date( 'Y-m-d' );
				for ( $r = 0; $r < $rows; $r++ ) :
					echo '<tr>';
					for ( $c = 0; $c < 7; $c++ ) :
						$cell = $r * 7 + $c;
						if ( $cell < $first_dow || $day_num > $d['days_in_month'] ) {
							echo '<td style="background:#f8fafc;border-radius:6px;min-height:80px;"></td>';
							continue;
						}
						$day_key = sprintf( '%04d-%02d-%02d', $d['year'], $d['month'], $day_num );
						$e = $d['events'][ $day_key ] ?? [];
						$is_today = ( $day_key === $today_str );
						$n_planned = count( $e['planned'] ?? [] );
						$n_auto    = count( $e['auto'] ?? [] );
						$n_rplan   = count( $e['refresh_planned'] ?? [] );
						$n_rauto   = count( $e['refresh_auto'] ?? [] );
						$border = $is_today ? '2px solid #0284c7' : '1px solid #e2e8f0';
						echo '<td style="background:#fff;border:' . $border . ';border-radius:6px;padding:0.4rem;min-height:88px;vertical-align:top;">';
						echo '<div style="font-size:11px;font-weight:600;color:' . ( $is_today ? '#0284c7' : '#64748b' ) . ';">' . esc_html( (string) $day_num ) . '</div>';
						foreach ( ( $e['planned'] ?? [] ) as $p ) {
							echo '<div style="margin-top:3px;font-size:11px;line-height:1.3;color:#0f172a;" title="' . esc_attr( $p['title'] ) . '"><span style="color:#16a34a;font-weight:700;">&#10003;</span> ' . esc_html( mb_strimwidth( $p['title'], 0, 22, '…' ) ) . '</div>';
						}
						foreach ( ( $e['refresh_planned'] ?? [] ) as $p ) {
							echo '<div style="margin-top:3px;font-size:11px;line-height:1.3;color:#0f172a;" title="' . esc_attr( $p['title'] ) . '"><span style="background:#fef3c7;color:#92400e;padding:0 4px;border-radius:2px;font-size:9px;font-weight:700;">R</span> <span style="color:#16a34a;font-weight:700;">&#10003;</span> ' . esc_html( mb_strimwidth( $p['title'], 0, 18, '…' ) ) . '</div>';
						}
						if ( $n_auto > 0 ) {
							$first = $e['auto'][0]['time'] ?? '';
							echo '<div style="margin-top:3px;font-size:11px;color:#0369a1;" title="' . esc_attr( sprintf( __( '%d auto-planned slot(s)', 'citeleap' ), $n_auto ) ) . '"><span style="font-weight:700;">&#10003;</span> ' . esc_html( sprintf( __( 'auto %s', 'citeleap' ), $first ) ) . ( $n_auto > 1 ? ' +' . ( $n_auto - 1 ) : '' ) . '</div>';
						}
						if ( $n_rauto > 0 ) {
							$first = $e['refresh_auto'][0]['time'] ?? '';
							echo '<div style="margin-top:3px;font-size:11px;color:#0369a1;"><span style="background:#fef3c7;color:#92400e;padding:0 4px;border-radius:2px;font-size:9px;font-weight:700;">R</span> <span style="font-weight:700;">&#10003;</span> ' . esc_html( sprintf( __( 'auto %s', 'citeleap' ), $first ) ) . ( $n_rauto > 1 ? ' +' . ( $n_rauto - 1 ) : '' ) . '</div>';
						}
						echo '</td>';
						$day_num++;
					endfor;
					echo '</tr>';
				endfor;
				?>
				</tbody>
			</table>

			<p class="citeleap-help" style="margin-top:1rem;">
				<strong><?php echo esc_html__( 'Mode:', 'citeleap' ); ?></strong> <?php echo esc_html( strtoupper( (string) $d['mode'] ) ); ?>
				| <strong><?php echo esc_html__( 'Refresh mode:', 'citeleap' ); ?></strong> <?php echo esc_html( strtoupper( (string) $d['refresh_mode'] ) ); ?>
				<br><?php echo esc_html__( 'Auto-planned slots only appear when the queue has at least one runnable topic. Plan a topic from the Planner tab to lock a specific date and time.', 'citeleap' ); ?>
			</p>
		</div>
		<?php
	}
}
