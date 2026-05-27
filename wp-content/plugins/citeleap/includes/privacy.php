<?php
/**
 * CiteLeap , privacy.php
 *
 * GDPR + WP privacy-tools compliance layer.
 *
 *   - Adds CiteLeap-specific text to the site Privacy Policy via
 *     wp_add_privacy_policy_content() so the site admin can paste it
 *     into their published policy from Tools , Privacy.
 *   - Registers a personal-data exporter so the admin can export the
 *     CiteLeap-side data tied to an email (currently: the credit
 *     ledger + license + log entries that reference that email).
 *   - Registers a personal-data eraser that clears the same data.
 *   - Ships a first-run + ongoing data-flow disclosure notice on
 *     plugin admin pages: the operator must know exactly what is
 *     sent to which third-party (Claude / OpenAI / Gemini / Freemius)
 *     before drafting.
 *   - Provides DPA + privacy-policy URLs the operator can link from
 *     their own site.
 *
 * Important: CiteLeap stores no end-user data. The data being
 * disclosed here is the OPERATOR'S (the WordPress admin running the
 * plugin) , API keys (encrypted), license info, credit ledger, log
 * entries. The processing chain is:
 *
 *   site visitor , (nothing)
 *   operator , CiteLeap , Claude/OpenAI/Gemini APIs
 *   operator , CiteLeap , Freemius (only when SDK loaded)
 *
 * @package CiteLeap
 */

defined( 'ABSPATH' ) || exit;

class CiteLeap_Privacy {

	const DPA_URL = 'https://citeleap.com/dpa';
	const POLICY_URL = 'https://citeleap.com/privacy';
	const DISCLOSURE_DISMISSED_META = 'citeleap_disclosure_dismissed';

	public static function init(): void {
		add_action( 'admin_init',                            [ __CLASS__, 'register_policy_content' ] );
		add_filter( 'wp_privacy_personal_data_exporters',    [ __CLASS__, 'register_exporter' ] );
		add_filter( 'wp_privacy_personal_data_erasers',      [ __CLASS__, 'register_eraser' ] );
		add_action( 'admin_notices',                         [ __CLASS__, 'maybe_render_disclosure' ] );
		add_action( 'admin_post_citeleap_dismiss_disclosure',[ __CLASS__, 'handle_dismiss_disclosure' ] );
	}

	/* ------------------------------------------------------------------
	 * Privacy Policy text (operator copies this into Tools , Privacy).
	 * ---------------------------------------------------------------- */
	public static function register_policy_content(): void {
		if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) return;
		$content  = '<h3>' . esc_html__( 'CiteLeap , AI content engine', 'citeleap' ) . '</h3>';
		$content .= '<p>' . esc_html__( 'This site uses the CiteLeap plugin to draft and refresh blog content via third-party large language model providers. The processing chain is:', 'citeleap' ) . '</p>';
		$content .= '<ul>';
		$content .= '<li><strong>' . esc_html__( 'Data we collect', 'citeleap' ) . ':</strong> ' . esc_html__( 'API keys (stored AES-256-CBC encrypted at rest), license + plan info, credit-usage ledger, activity log. No end-user / visitor data.', 'citeleap' ) . '</li>';
		$content .= '<li><strong>' . esc_html__( 'Data sent to LLM providers', 'citeleap' ) . ':</strong> ' . esc_html__( 'Each time you click Draft or Refresh, the topic, master prompt, recent post bodies used as voice samples, and the configured custom instructions are sent to the provider you selected (Claude / OpenAI / Gemini). Their privacy policies apply at:', 'citeleap' );
		$content .= ' <a href="https://www.anthropic.com/legal/privacy">Anthropic</a>, <a href="https://openai.com/policies/privacy-policy">OpenAI</a>, <a href="https://policies.google.com/privacy">Google</a>.</li>';
		$content .= '<li><strong>' . esc_html__( 'Data sent to Freemius (if billing is enabled)', 'citeleap' ) . ':</strong> ' . esc_html__( 'Admin email, site URL, plan, and license activation events. Freemius acts as the merchant of record. Their policy:', 'citeleap' );
		$content .= ' <a href="https://freemius.com/privacy/">freemius.com/privacy</a>.</li>';
		$content .= '<li><strong>' . esc_html__( 'Telemetry', 'citeleap' ) . ':</strong> ' . esc_html__( 'CiteLeap itself does NOT phone home. No analytics, no usage beacons.', 'citeleap' ) . '</li>';
		$content .= '<li><strong>' . esc_html__( 'Data retention', 'citeleap' ) . ':</strong> ' . esc_html__( 'Stored in wp_options on this site only. Removed entirely on plugin uninstall. The activity log keeps the last 500 entries. The credit + token ledger keeps the last 12 months.', 'citeleap' ) . '</li>';
		$content .= '<li><strong>' . esc_html__( 'Your rights', 'citeleap' ) . ':</strong> ' . esc_html__( 'Use Tools , Export Personal Data and Tools , Erase Personal Data to retrieve or delete the CiteLeap-side data linked to your administrator email. A signed DPA is available from the CiteLeap site.', 'citeleap' ) . '</li>';
		$content .= '</ul>';
		wp_add_privacy_policy_content( 'CiteLeap', $content );
	}

	/* ------------------------------------------------------------------
	 * Personal-data exporter (Tools , Export Personal Data).
	 * ---------------------------------------------------------------- */
	public static function register_exporter( array $exporters ): array {
		$exporters['citeleap'] = [
			'exporter_friendly_name' => __( 'CiteLeap', 'citeleap' ),
			'callback'               => [ __CLASS__, 'export_personal_data' ],
		];
		return $exporters;
	}

	public static function export_personal_data( string $email, int $page = 1 ): array {
		$data_to_export = [];

		/* Only the WP admin email gets a meaningful export , CiteLeap
		 * does not store per-user data, just the operator's plugin
		 * state. Match against the site's admin_email or any user
		 * whose email matches. */
		$user = get_user_by( 'email', $email );
		if ( ! $user ) {
			return [ 'data' => [], 'done' => true ];
		}
		if ( ! user_can( $user->ID, CiteLeap_Caps::MANAGE_CAP ) ) {
			/* Editors don't get CiteLeap data , they only USE it; the
			 * data is owned by the administrator. */
			return [ 'data' => [], 'done' => true ];
		}

		$credits = (array) get_option( CITELEAP_OPTION_CREDITS, [] );
		$caps    = (array) get_option( CITELEAP_OPTION_CAPS, [] );
		$usage   = (array) get_option( CITELEAP_OPTION_USAGE, [] );

		$data = [
			[ 'name' => __( 'Plan', 'citeleap' ),          'value' => class_exists( 'CiteLeap_License' ) ? CiteLeap_License::plan_label() : 'unknown' ],
			[ 'name' => __( 'Credits used', 'citeleap' ),  'value' => (string) ( $credits['used']          ?? 0 ) ],
			[ 'name' => __( 'Top-up balance', 'citeleap' ),'value' => (string) ( $credits['top_up']        ?? 0 ) ],
			[ 'name' => __( 'Lifetime credits used', 'citeleap' ), 'value' => (string) ( $credits['lifetime_used'] ?? 0 ) ],
			[ 'name' => __( 'Cycle start', 'citeleap' ),   'value' => (string) ( $credits['cycle_start']   ?? '' ) ],
			[ 'name' => __( 'Last consumed at', 'citeleap' ), 'value' => (string) ( $credits['last_consumed_at'] ?? '' ) ],
			[ 'name' => __( 'Monthly budget caps', 'citeleap' ), 'value' => wp_json_encode( $caps ) ],
			[ 'name' => __( 'Token usage ledger (last 12mo)', 'citeleap' ), 'value' => wp_json_encode( $usage ) ],
		];

		$data_to_export[] = [
			'group_id'    => 'citeleap-plan',
			'group_label' => __( 'CiteLeap , plan + usage', 'citeleap' ),
			'item_id'     => 'citeleap-plan-1',
			'data'        => $data,
		];

		return [ 'data' => $data_to_export, 'done' => true ];
	}

	/* ------------------------------------------------------------------
	 * Personal-data eraser (Tools , Erase Personal Data).
	 * ---------------------------------------------------------------- */
	public static function register_eraser( array $erasers ): array {
		$erasers['citeleap'] = [
			'eraser_friendly_name' => __( 'CiteLeap', 'citeleap' ),
			'callback'             => [ __CLASS__, 'erase_personal_data' ],
		];
		return $erasers;
	}

	public static function erase_personal_data( string $email, int $page = 1 ): array {
		$user = get_user_by( 'email', $email );
		if ( ! $user || ! user_can( $user->ID, CiteLeap_Caps::MANAGE_CAP ) ) {
			return [
				'items_removed'  => false,
				'items_retained' => false,
				'messages'       => [],
				'done'           => true,
			];
		}

		/* Reset the credit ledger + clear stored API keys + clear the
		 * log. The license + plan slug stay since they live with
		 * Freemius, not on the site. */
		delete_option( CITELEAP_OPTION_CREDITS );
		delete_option( CITELEAP_OPTION_API_KEYS );
		delete_option( CITELEAP_OPTION_LOG );
		delete_option( CITELEAP_OPTION_USAGE );

		return [
			'items_removed'  => true,
			'items_retained' => false,
			'messages'       => [ __( 'CiteLeap credit ledger, encrypted API keys, activity log, and token usage history have been removed.', 'citeleap' ) ],
			'done'           => true,
		];
	}

	/* ------------------------------------------------------------------
	 * First-run disclosure notice. Shown ONCE on the main plugin
	 * pages until the operator clicks "I understand". Stored in
	 * user_meta so each operator on a multi-admin site sees + accepts
	 * independently.
	 * ---------------------------------------------------------------- */
	public static function maybe_render_disclosure(): void {
		if ( ! is_admin() ) return;
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['page'] ) ) : '';
		if ( 'citeleap' !== $page ) return;

		$uid = get_current_user_id();
		if ( ! $uid ) return;
		if ( '1' === (string) get_user_meta( $uid, self::DISCLOSURE_DISMISSED_META, true ) ) return;

		$dismiss_url = wp_nonce_url(
			admin_url( 'admin-post.php?action=citeleap_dismiss_disclosure' ),
			'citeleap_dismiss_disclosure'
		);
		?>
		<div class="notice notice-info" role="region" aria-label="<?php echo esc_attr__( 'CiteLeap data-flow disclosure', 'citeleap' ); ?>" style="border-left:4px solid #0284c7;padding:1rem 1.25rem;">
			<h3 style="margin:0 0 0.5rem;font-size:15px;"><?php echo esc_html__( 'CiteLeap , what gets sent where', 'citeleap' ); ?></h3>
			<p style="margin:0 0 0.5rem;">
				<?php echo esc_html__( 'Every time you click Draft or Refresh, the topic + master prompt + recent post bodies (used as voice samples) are sent to the LLM provider you configured. Pick yours carefully:', 'citeleap' ); ?>
			</p>
			<ul style="margin:0 0 0.75rem 1.25rem;">
				<li><strong>Anthropic Claude</strong> , <a href="https://www.anthropic.com/legal/privacy">privacy policy</a></li>
				<li><strong>OpenAI</strong> , <a href="https://openai.com/policies/privacy-policy">privacy policy</a></li>
				<li><strong>Google Gemini</strong> , <a href="https://policies.google.com/privacy">privacy policy</a></li>
			</ul>
			<p style="margin:0 0 0.75rem;color:#475569;">
				<?php echo esc_html__( 'CiteLeap itself does not phone home. If you wire Freemius for billing, Freemius receives your admin email + site URL + license events as merchant of record. Activity log + credit ledger live in wp_options on this site only.', 'citeleap' ); ?>
			</p>
			<p style="margin:0;">
				<a class="button button-primary" href="<?php echo esc_url( $dismiss_url ); ?>"><?php echo esc_html__( 'I understand , dismiss', 'citeleap' ); ?></a>
				<a class="button" href="<?php echo esc_url( admin_url( 'tools.php?wp-privacy-policy-guide' ) ); ?>" style="margin-left:0.25rem;"><?php echo esc_html__( 'Open WP privacy guide', 'citeleap' ); ?></a>
				<a class="button" href="<?php echo esc_url( self::DPA_URL ); ?>" style="margin-left:0.25rem;"><?php echo esc_html__( 'Download DPA template', 'citeleap' ); ?></a>
			</p>
		</div>
		<?php
	}

	public static function handle_dismiss_disclosure(): void {
		CiteLeap_Caps::guard_use();
		check_admin_referer( 'citeleap_dismiss_disclosure' );
		update_user_meta( get_current_user_id(), self::DISCLOSURE_DISMISSED_META, '1' );
		wp_safe_redirect( admin_url( 'admin.php?page=citeleap' ) );
		exit;
	}
}

CiteLeap_Privacy::init();
