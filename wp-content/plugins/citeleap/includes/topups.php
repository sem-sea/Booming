<?php
/**
 * CiteLeap , topups.php
 *
 * One-off credit-pack catalog + purchase flow. Top-up credits never
 * expire on the active license and are consumed AFTER the monthly
 * included credits. The catalog is defined here in one place so it
 * stays in lock-step with the pricing page on citeleap.boomingventure.com.
 *
 * Freemius integration: when the SDK is loaded, top-ups are sold as
 * Freemius "lifetime" addons. The webhook listeners below grant
 * credits on successful purchase. When the SDK is NOT loaded, the
 * Buy buttons route to a developer-mode simulator so the operator
 * can verify the credit-grant + ledger logic end-to-end before
 * connecting real billing.
 *
 * @package CiteLeap
 */

defined( 'ABSPATH' ) || exit;

class CiteLeap_TopUps {

	/** Catalog of one-off credit packs. Per-credit price drops as the
	 *  pack size grows so the operator is incentivised to buy in bulk. */
	public static function catalog(): array {
		return [
			'starter' => [
				'slug'         => 'starter',
				'label'        => __( 'Starter pack', 'citeleap' ),
				'credits'      => 10,
				'price_usd'    => 49,
				'per_credit'   => 4.90,
				'fs_plan_id'   => 'pack_starter',
				'description'  => __( 'Top up 10 credits for a small campaign. Per-credit price $4.90.', 'citeleap' ),
			],
			'growth' => [
				'slug'         => 'growth',
				'label'        => __( 'Growth pack', 'citeleap' ),
				'credits'      => 50,
				'price_usd'    => 199,
				'per_credit'   => 3.98,
				'fs_plan_id'   => 'pack_growth',
				'description'  => __( '50 credits, 20% off the per-credit overage rate. Best-seller for solo founders.', 'citeleap' ),
				'badge'        => __( 'Best value for solo', 'citeleap' ),
			],
			'scale' => [
				'slug'         => 'scale',
				'label'        => __( 'Scale pack', 'citeleap' ),
				'credits'      => 100,
				'price_usd'    => 349,
				'per_credit'   => 3.49,
				'fs_plan_id'   => 'pack_scale',
				'description'  => __( '100 credits, 30% off per-credit. For an agency running a client launch.', 'citeleap' ),
			],
			'bulk' => [
				'slug'         => 'bulk',
				'label'        => __( 'Bulk pack', 'citeleap' ),
				'credits'      => 500,
				'price_usd'    => 1499,
				'per_credit'   => 3.00,
				'fs_plan_id'   => 'pack_bulk',
				'description'  => __( '500 credits, 40% off per-credit. For multi-brand or annual content programmes.', 'citeleap' ),
				'badge'        => __( 'Most credits', 'citeleap' ),
			],
		];
	}

	public static function pack( string $slug ): array {
		return self::catalog()[ $slug ] ?? [];
	}

	/** Resolve the buy URL for a pack. Real Freemius checkout if SDK
	 *  loaded, otherwise the in-admin simulator (dev / pre-launch). */
	public static function buy_url( string $slug ): string {
		$pack = self::pack( $slug );
		if ( empty( $pack ) ) return admin_url( 'admin.php?page=citeleap&tab=license' );
		$fs = CiteLeap_License::freemius();
		if ( $fs && method_exists( $fs, 'checkout_url' ) ) {
			/* Freemius "lifetime" period = one-off purchase; pack slug is
			 * sent as a referrer the dashboard webhook can read back. */
			return (string) $fs->checkout_url( 'lifetime', $pack['fs_plan_id'] ?? false, [
				'pack'    => $pack['slug'],
				'credits' => (int) $pack['credits'],
			] );
		}
		/* Simulator URL , the operator clicks, the admin-post handler
		 * adds the credits to the local ledger and redirects back. */
		return wp_nonce_url( admin_url( 'admin-post.php?action=citeleap_simulate_topup&pack=' . rawurlencode( $slug ) ), CITELEAP_NONCE );
	}

	/** Render the top-up grid on the License & Credits tab. */
	public static function render_grid(): void {
		$packs = self::catalog();
		$has_fs = (bool) CiteLeap_License::freemius();
		?>
		<h3 style="margin-top:2rem;"><?php echo esc_html__( 'Top-up credit packs', 'citeleap' ); ?></h3>
		<p style="color:#64748b;max-width:780px;"><?php echo esc_html__( 'One-off purchases on top of any plan. Top-up credits never expire while your license is active, and are consumed AFTER your monthly included credits each cycle.', 'citeleap' ); ?></p>
		<?php if ( ! $has_fs && ! ( defined( 'CITELEAP_DEV_MODE' ) && CITELEAP_DEV_MODE ) ) : ?>
			<div class="notice notice-warning" style="margin:0 0 1rem;"><p>
				<strong><?php echo esc_html__( 'Simulator mode.', 'citeleap' ); ?></strong>
				<?php echo esc_html__( 'Freemius SDK is not loaded yet, so the Buy buttons below add credits directly to your local ledger for testing. Once the SDK + plugin IDs are wired in wp-config.php, these route to the real Freemius checkout.', 'citeleap' ); ?>
			</p></div>
		<?php endif; ?>
		<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem;max-width:1100px;margin-top:0.75rem;">
		<?php foreach ( $packs as $slug => $p ) :
			$url = self::buy_url( $slug );
			$is_featured = ! empty( $p['badge'] );
		?>
			<div style="border:<?php echo $is_featured ? '2px solid #0284c7' : '1px solid #e2e8f0'; ?>;border-radius:0.625rem;padding:1.25rem;background:<?php echo $is_featured ? '#f0f9ff' : '#fff'; ?>;display:flex;flex-direction:column;gap:0.5rem;position:relative;">
				<?php if ( $is_featured ) : ?>
					<span style="position:absolute;top:-10px;left:50%;transform:translateX(-50%);background:#0284c7;color:#fff;font-size:11px;padding:2px 10px;border-radius:999px;letter-spacing:0.04em;text-transform:uppercase;"><?php echo esc_html( $p['badge'] ); ?></span>
				<?php endif; ?>
				<h4 style="margin:0;font-size:18px;"><?php echo esc_html( $p['label'] ); ?></h4>
				<div style="font-size:28px;font-weight:700;color:#0f172a;">$<?php echo (int) $p['price_usd']; ?></div>
				<div style="color:#64748b;font-size:13px;">
					<strong><?php echo (int) $p['credits']; ?></strong> <?php echo esc_html__( 'credits', 'citeleap' ); ?>
					&middot; $<?php echo esc_html( number_format( (float) $p['per_credit'], 2 ) ); ?>/<?php echo esc_html__( 'credit', 'citeleap' ); ?>
				</div>
				<p style="color:#475569;font-size:13px;margin:0.25rem 0;flex:1;"><?php echo esc_html( $p['description'] ); ?></p>
				<a class="button button-primary" style="text-align:center;" href="<?php echo esc_url( $url ); ?>"><?php
					echo $has_fs ? esc_html__( 'Buy pack', 'citeleap' ) : esc_html__( 'Buy (simulated)', 'citeleap' );
				?></a>
			</div>
		<?php endforeach; ?>
		</div>
		<?php
	}

	/* ---------------------------------------------------------------------
	 * Freemius webhook listeners.
	 *
	 * Freemius fires these hooks against the plugin-specific slug. We
	 * attach when citeleap_fs_loaded fires (see citeleap.php). The
	 * handlers read the pack slug from the order metadata and call
	 * CiteLeap_Credits::add_top_up().
	 * ------------------------------------------------------------------- */
	public static function register_freemius_listeners(): void {
		add_action( 'fs_after_purchase_citeleap',        [ __CLASS__, 'on_freemius_purchase' ] );
		add_action( 'fs_after_account_plan_change_citeleap', [ __CLASS__, 'on_freemius_plan_change' ], 10, 2 );
		add_action( 'fs_after_account_user_change_citeleap', [ __CLASS__, 'on_freemius_user_change' ] );
	}

	public static function on_freemius_purchase( $purchase ): void {
		/* Freemius posts a purchase object that includes the plan slug
		 * + optional metadata. We mapped each pack to fs_plan_id so a
		 * one-shot Lifetime purchase of pack_growth grants 50 credits. */
		$plan_id = '';
		if ( is_object( $purchase ) ) {
			$plan_id = (string) ( $purchase->plan_id ?? $purchase->plan_name ?? '' );
		} elseif ( is_array( $purchase ) ) {
			$plan_id = (string) ( $purchase['plan_id'] ?? $purchase['plan_name'] ?? '' );
		}
		if ( '' === $plan_id ) return;

		foreach ( self::catalog() as $slug => $pack ) {
			if ( $plan_id === $pack['fs_plan_id'] ) {
				CiteLeap_Credits::add_top_up( (int) $pack['credits'] );
				CiteLeap_Log::add( 'topup_purchase', sprintf( 'pack=%s credits=%d source=freemius', $slug, $pack['credits'] ), 'info' );
				return;
			}
		}
	}

	public static function on_freemius_plan_change( $old_plan, $new_plan ): void {
		/* On a subscription plan change, reset the monthly cycle so the
		 * new plan's included-credits start fresh. */
		CiteLeap_Credits::reset_cycle();
		$old = is_object( $old_plan ) ? (string) ( $old_plan->name ?? '' ) : (string) $old_plan;
		$new = is_object( $new_plan ) ? (string) ( $new_plan->name ?? '' ) : (string) $new_plan;
		CiteLeap_Log::add( 'plan_changed', sprintf( 'old=%s new=%s , credit cycle reset', $old, $new ), 'info' );
	}

	public static function on_freemius_user_change(): void {
		/* Operator linked a different Freemius account. The plan slug
		 * may have changed, so reset the cycle. */
		CiteLeap_Credits::reset_cycle();
		CiteLeap_Log::add( 'fs_user_changed', 'cycle reset on account swap', 'info' );
	}

	/* ---------------------------------------------------------------------
	 * Simulator , adds the pack credits straight to the local ledger.
	 * Active only when the SDK is not loaded OR Developer mode is on.
	 * This makes it possible to test the credit-grant + spend pipeline
	 * before connecting real billing.
	 * ------------------------------------------------------------------- */
	public static function handle_simulator(): void {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden', 403 );
		check_admin_referer( CITELEAP_NONCE );

		$slug = isset( $_GET['pack'] ) ? sanitize_key( (string) $_GET['pack'] ) : '';
		$pack = self::pack( $slug );
		if ( empty( $pack ) ) {
			wp_safe_redirect( add_query_arg( [ 'page' => 'citeleap', 'tab' => 'license', 'citeleap_msg' => 'topup-unknown' ], admin_url( 'admin.php' ) ) );
			exit;
		}

		/* Guardrail: refuse simulator purchases when the real Freemius
		 * SDK is loaded and live (avoid grant-without-charge). Operator
		 * must use the real checkout in that case. */
		$fs = CiteLeap_License::freemius();
		if ( $fs && method_exists( $fs, 'is_live' ) && $fs->is_live() && ! ( defined( 'CITELEAP_DEV_MODE' ) && CITELEAP_DEV_MODE ) ) {
			wp_safe_redirect( add_query_arg( [ 'page' => 'citeleap', 'tab' => 'license', 'citeleap_msg' => 'topup-blocked' ], admin_url( 'admin.php' ) ) );
			exit;
		}

		CiteLeap_Credits::add_top_up( (int) $pack['credits'] );
		CiteLeap_Log::add( 'topup_simulated', sprintf( 'pack=%s credits=%d', $slug, $pack['credits'] ), 'info' );
		wp_safe_redirect( add_query_arg( [
			'page' => 'citeleap', 'tab' => 'license',
			'citeleap_msg' => 'topup-ok:' . rawurlencode( $pack['label'] ) . ':' . (int) $pack['credits'],
		], admin_url( 'admin.php' ) ) );
		exit;
	}
}

add_action( 'admin_post_citeleap_simulate_topup', [ 'CiteLeap_TopUps', 'handle_simulator' ] );
add_action( 'citeleap_fs_loaded', [ 'CiteLeap_TopUps', 'register_freemius_listeners' ] );
