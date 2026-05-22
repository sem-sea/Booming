<?php
/**
 * CiteLeap , caps.php
 *
 * Two-tier capability model. Required so the plugin is usable on
 * sites where a non-admin Editor runs the content programme.
 *
 *   - citeleap_use     , content operations: queue, draft, schedule,
 *                        publish-now, refresh, pause/resume/retry,
 *                        approve/reject, calendar, log view, image
 *                        pool. Granted to Editor + Administrator on
 *                        activation.
 *   - citeleap_manage  , settings, API keys, prompts, model picks,
 *                        license + plan changes, top-up purchases.
 *                        Maps to the core "manage_options" cap so it
 *                        stays Administrator-only.
 *
 * Helpers:
 *   CiteLeap_Caps::USE_CAP       , constant 'citeleap_use'
 *   CiteLeap_Caps::can_use()     , bool: may run content operations
 *   CiteLeap_Caps::can_manage()  , bool: may change settings/license
 *   CiteLeap_Caps::guard_use()   , wp_die's on failure (use in
 *                                  admin-post handlers)
 *   CiteLeap_Caps::guard_manage(), same, manager-tier
 *   CiteLeap_Caps::grant_on_activation()    , adds USE cap to Editor
 *   CiteLeap_Caps::revoke_on_uninstall()    , strips USE cap from
 *                                              every role
 *
 * @package CiteLeap
 */

defined( 'ABSPATH' ) || exit;

class CiteLeap_Caps {

	const USE_CAP    = 'citeleap_use';
	const MANAGE_CAP = 'manage_options';

	public static function can_use(): bool {
		return current_user_can( self::USE_CAP ) || current_user_can( self::MANAGE_CAP );
	}

	public static function can_manage(): bool {
		return current_user_can( self::MANAGE_CAP );
	}

	public static function guard_use(): void {
		if ( ! self::can_use() ) wp_die( esc_html__( 'You do not have permission to perform this CiteLeap action.', 'citeleap' ), 403 );
	}

	public static function guard_manage(): void {
		if ( ! self::can_manage() ) wp_die( esc_html__( 'Only administrators can change CiteLeap settings.', 'citeleap' ), 403 );
	}

	public static function grant_on_activation(): void {
		foreach ( [ 'administrator', 'editor' ] as $slug ) {
			$role = get_role( $slug );
			if ( $role && ! $role->has_cap( self::USE_CAP ) ) {
				$role->add_cap( self::USE_CAP );
			}
		}
	}

	public static function revoke_on_uninstall(): void {
		global $wp_roles;
		if ( ! ( $wp_roles instanceof WP_Roles ) ) return;
		foreach ( $wp_roles->roles as $slug => $_meta ) {
			$role = get_role( $slug );
			if ( $role && $role->has_cap( self::USE_CAP ) ) {
				$role->remove_cap( self::USE_CAP );
			}
		}
	}
}
