<?php
/**
 * CiteLeap , plan.php
 *
 * Plan definitions + capability gate. Single source of truth for what
 * each plan slug is allowed to do, how many credits it ships with,
 * how many sites a license can activate on, and what overage costs.
 *
 * @package CiteLeap
 */

defined( 'ABSPATH' ) || exit;

class CiteLeap_Plan {

	/** Capability map per plan. Order matters for inheritance / UI sort. */
	public static function definitions(): array {
		return [
			'dev' => [
				'label'             => __( 'Developer (unlimited)', 'citeleap' ),
				'price_monthly'     => 0,
				'price_annual'      => 0,
				'credits_per_cycle' => PHP_INT_MAX,
				'lifetime_credits'  => false,
				'sites'             => PHP_INT_MAX,
				'seats'             => PHP_INT_MAX,
				'overage_per_credit'=> 0,
				'features'          => [ 'auto_publish', 'refresh', 'multilingual', 'calendar', 'scheduling', 'top_ups', 'white_label', 'priority_support', 'images_bulk' ],
			],
			'free' => [
				'label'             => __( 'Free', 'citeleap' ),
				'price_monthly'     => 0,
				'price_annual'      => 0,
				'credits_per_cycle' => 3,
				'lifetime_credits'  => true,
				'sites'             => 1,
				'seats'             => 1,
				'overage_per_credit'=> 0,
				'features'          => [ 'images_bulk' ],
			],
			'solo' => [
				'label'             => __( 'Solo', 'citeleap' ),
				'price_monthly'     => 29,
				'price_annual'      => 290,
				'credits_per_cycle' => 10,
				'lifetime_credits'  => false,
				'sites'             => 1,
				'seats'             => 1,
				'overage_per_credit'=> 5,
				'features'          => [ 'auto_publish', 'refresh', 'scheduling', 'top_ups', 'images_bulk' ],
			],
			'pro' => [
				'label'             => __( 'Pro', 'citeleap' ),
				'price_monthly'     => 99,
				'price_annual'      => 990,
				'credits_per_cycle' => 30,
				'lifetime_credits'  => false,
				'sites'             => 3,
				'seats'             => 3,
				'overage_per_credit'=> 4,
				'features'          => [ 'auto_publish', 'refresh', 'multilingual', 'calendar', 'scheduling', 'top_ups', 'images_bulk' ],
			],
			'agency' => [
				'label'             => __( 'Agency', 'citeleap' ),
				'price_monthly'     => 299,
				'price_annual'      => 2990,
				'credits_per_cycle' => 100,
				'lifetime_credits'  => false,
				'sites'             => 20,
				'seats'             => 10,
				'overage_per_credit'=> 3,
				'features'          => [ 'auto_publish', 'refresh', 'multilingual', 'calendar', 'scheduling', 'top_ups', 'white_label', 'images_bulk' ],
			],
			'enterprise' => [
				'label'             => __( 'Enterprise', 'citeleap' ),
				'price_monthly'     => 999,
				'price_annual'      => 9990,
				'credits_per_cycle' => 500,
				'lifetime_credits'  => false,
				'sites'             => PHP_INT_MAX,
				'seats'             => PHP_INT_MAX,
				'overage_per_credit'=> 2,
				'features'          => [ 'auto_publish', 'refresh', 'multilingual', 'calendar', 'scheduling', 'top_ups', 'white_label', 'priority_support', 'images_bulk' ],
			],
		];
	}

	public static function current(): string {
		return CiteLeap_License::plan_slug();
	}

	public static function definition( string $slug = '' ): array {
		$defs = self::definitions();
		$slug = $slug ?: self::current();
		return $defs[ $slug ] ?? $defs['free'];
	}

	/** Does the current (or named) plan have a capability? */
	public static function has( string $feature, string $slug = '' ): bool {
		$def = self::definition( $slug );
		return in_array( $feature, (array) ( $def['features'] ?? [] ), true );
	}

	public static function credits_per_cycle( string $slug = '' ): int {
		return (int) self::definition( $slug )['credits_per_cycle'];
	}

	public static function is_lifetime_credits( string $slug = '' ): bool {
		return (bool) self::definition( $slug )['lifetime_credits'];
	}

	public static function sites_allowed( string $slug = '' ): int {
		return (int) self::definition( $slug )['sites'];
	}

	public static function overage_rate( string $slug = '' ): float {
		return (float) self::definition( $slug )['overage_per_credit'];
	}

	public static function is_paid( string $slug = '' ): bool {
		$slug = $slug ?: self::current();
		return in_array( $slug, [ 'solo', 'pro', 'agency', 'enterprise', 'dev' ], true );
	}

	/** UI helper: list plans that are higher than $slug. */
	public static function upgrades_from( string $slug = '' ): array {
		$order = [ 'free', 'solo', 'pro', 'agency', 'enterprise' ];
		$slug  = $slug ?: self::current();
		$idx   = array_search( $slug, $order, true );
		if ( false === $idx ) return [];
		return array_slice( $order, (int) $idx + 1 );
	}

	/** Which is the LOWEST paid plan that has this feature? */
	public static function lowest_plan_with( string $feature ): string {
		foreach ( [ 'solo', 'pro', 'agency', 'enterprise' ] as $slug ) {
			if ( self::has( $feature, $slug ) ) return $slug;
		}
		return 'pro';
	}

	/**
	 * Render the standard "feature locked, upgrade to X" card. Used to
	 * gate Calendar, Languages, Refresh, etc. for Free / lower-tier
	 * plans without hiding what they could be getting. This is a sales
	 * surface, not just a guardrail.
	 */
	public static function render_locked_notice( string $feature, string $title = '', string $why = '' ): void {
		$lowest      = self::lowest_plan_with( $feature );
		$def         = self::definition( $lowest );
		$upgrade_url = class_exists( 'CiteLeap_License' ) ? CiteLeap_License::checkout_url( $lowest ) : admin_url( 'admin.php?page=citeleap&tab=license' );
		$title       = $title ?: ucfirst( str_replace( '_', ' ', $feature ) );
		?>
		<div style="border:2px dashed #0284c7;border-radius:0.625rem;padding:1.5rem 1.75rem;background:linear-gradient(135deg,#f0f9ff 0%,#eff6ff 100%);margin:1rem 0;">
			<div style="display:flex;gap:1rem;align-items:flex-start;flex-wrap:wrap;">
				<div style="font-size:32px;line-height:1;">&#128274;</div>
				<div style="flex:1;min-width:280px;">
					<h3 style="margin:0 0 0.35rem;font-size:18px;color:#0f172a;"><?php echo esc_html( $title ); ?> <span style="font-size:11px;background:#0284c7;color:#fff;padding:2px 8px;border-radius:999px;letter-spacing:0.04em;text-transform:uppercase;margin-left:0.35rem;vertical-align:middle;"><?php echo esc_html( $def['label'] ); ?>+</span></h3>
					<p style="margin:0 0 0.75rem;color:#475569;font-size:14px;max-width:640px;">
						<?php echo $why ? esc_html( $why ) : esc_html( sprintf( __( 'This feature is included on the %s plan and above. Your current plan does not include it.', 'citeleap' ), $def['label'] ) ); ?>
					</p>
					<p style="margin:0;">
						<a class="button button-primary" href="<?php echo esc_url( $upgrade_url ); ?>"><?php echo esc_html( sprintf( __( 'Upgrade to %s', 'citeleap' ), $def['label'] ) ); ?> , $<?php echo (int) $def['price_monthly']; ?>/mo</a>
						<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=citeleap&tab=license' ) ); ?>" style="margin-left:0.25rem;"><?php echo esc_html__( 'Compare plans', 'citeleap' ); ?></a>
					</p>
				</div>
			</div>
		</div>
		<?php
	}

	/** Small inline upgrade nudge for use INSIDE settings rows. */
	public static function render_inline_nudge( string $feature ): void {
		$lowest = self::lowest_plan_with( $feature );
		$def    = self::definition( $lowest );
		$url    = class_exists( 'CiteLeap_License' ) ? CiteLeap_License::checkout_url( $lowest ) : admin_url( 'admin.php?page=citeleap&tab=license' );
		echo ' <span style="display:inline-block;background:#fef3c7;color:#92400e;font-size:11px;padding:1px 8px;border-radius:999px;letter-spacing:0.04em;text-transform:uppercase;margin-left:0.35rem;">' . esc_html( $def['label'] ) . '+</span>';
		echo ' <a href="' . esc_url( $url ) . '" style="font-size:12px;margin-left:0.25rem;">' . esc_html__( 'Upgrade', 'citeleap' ) . '</a>';
	}
}
