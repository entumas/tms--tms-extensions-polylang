<?php
/**
 * Includes -> Dependencies
 * Plugin dependencies guard
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! function_exists( 'is_plugin_active' ) ) require_once ABSPATH . 'wp-admin/includes/plugin.php';


/**
 * Polylang Detection
 */

function tepll_polylang_is_active(): bool {
	return defined( 'POLYLANG_VERSION' )
		|| ( is_multisite() && function_exists( 'is_plugin_active_for_network' )
			&& is_plugin_active_for_network( 'polylang/polylang.php' ) );
}


/**
 * Dependency Constants
 */

if ( ! defined( 'TEPLL_POLYLANG_PLUGIN_BASENAME' ) ) define( 'TEPLL_POLYLANG_PLUGIN_BASENAME', 'polylang/polylang.php' );


/**
 * Activation Guard
 */

register_activation_hook( TEPLL_PLUGIN_FILE, function(): void {
	if ( tepll_polylang_is_active() ) return;

	deactivate_plugins( TEPLL_PLUGIN_BASENAME );
	wp_die(
		sprintf(
			/* translators: %s: plugin name. */
			esc_html__( '%s requires Polylang to be active before activation.', 'tms-extensions-polylang' ),
			esc_html( tepll_plugin_get_name() )
		),
		esc_html__( 'Unmet dependency', 'tms-extensions-polylang' ),
		array(
			'back_link' => true,
		)
	);
} );


/**
 * Runtime Dependency Guard
 */

add_action( 'admin_init', function(): void {
	// Run only in admin non-AJAX requests
	if ( ! is_admin() || wp_doing_ajax() ) return;
	// Skip when plugin is already inactive or dependency is present
	if ( ! is_plugin_active( TEPLL_PLUGIN_BASENAME ) || tepll_polylang_is_active() ) return;

	deactivate_plugins( TEPLL_PLUGIN_BASENAME );
	set_transient( 'tepll_dependency_notice', 'missing_polylang', 30 );
}, 1 );


/**
 * Polylang Deactivation Lock
 */

function tepll_is_verified_polylang_deactivation_request(): bool {
	$action = isset( $_REQUEST['action'] )
		? sanitize_key( wp_unslash( $_REQUEST['action'] ) )
		: '';
	$action2 = isset( $_REQUEST['action2'] )
		? sanitize_key( wp_unslash( $_REQUEST['action2'] ) )
		: '';

	$nonce_raw = isset( $_REQUEST['_wpnonce'] )
		? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) )
		: '';
	if ( '' === $nonce_raw ) return false;

	$plugin = isset( $_REQUEST['plugin'] )
		? sanitize_text_field( wp_unslash( $_REQUEST['plugin'] ) )
		: '';

	if ( 'deactivate' === $action ) :
		if ( TEPLL_POLYLANG_PLUGIN_BASENAME !== $plugin ) return false;
		return (bool) wp_verify_nonce( $nonce_raw, 'deactivate-plugin_' . TEPLL_POLYLANG_PLUGIN_BASENAME );
	endif;

	if ( in_array( 'deactivate-selected', array( $action, $action2 ), true ) ) :
		if ( ! wp_verify_nonce( $nonce_raw, 'bulk-plugins' ) ) return false;
		$checked_raw = isset( $_REQUEST['checked'] )
			? (array) wp_unslash( $_REQUEST['checked'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized immediately via array_map( 'sanitize_text_field', ... ).
			: array();
		$checked = array_map( 'sanitize_text_field', $checked_raw );
		return in_array( TEPLL_POLYLANG_PLUGIN_BASENAME, $checked, true );
	endif;

	return false;
}

add_action( 'admin_init', function(): void {
	if ( ! is_admin() || wp_doing_ajax() ) return;
	global $pagenow;
	if ( 'plugins.php' !== $pagenow ) return;
	if ( is_multisite() && is_network_admin() ) :
		if ( ! current_user_can( 'manage_network_plugins' ) ) :
			return;
		endif;
	else :
		if ( ! current_user_can( 'deactivate_plugins' ) ) :
			return;
		endif;
	endif;
	if ( ! is_plugin_active( TEPLL_PLUGIN_BASENAME ) || ! is_plugin_active( TEPLL_POLYLANG_PLUGIN_BASENAME ) ) return;
	if ( ! tepll_is_verified_polylang_deactivation_request() ) return;

	set_transient( 'tepll_dependency_notice', 'blocked_polylang_deactivation', 30 );
	$plugins_screen = ( is_multisite() && is_network_admin() )
		? network_admin_url( 'plugins.php' )
		: admin_url( 'plugins.php' );
	wp_safe_redirect( $plugins_screen );
	exit;
}, 0 );

add_filter( 'plugin_action_links_' . TEPLL_POLYLANG_PLUGIN_BASENAME, function( array $actions ): array {
	// Hide direct deactivate link for Polylang while dependency is active
	if ( is_plugin_active( TEPLL_PLUGIN_BASENAME ) ) :
		unset( $actions['deactivate'] );
	endif;
	return $actions;
} );


/**
 * Admin Notices
 */

add_action( 'admin_notices', function(): void {
	$notice = get_transient( 'tepll_dependency_notice' );
	if ( ! $notice ) return;

	delete_transient( 'tepll_dependency_notice' );

	if ( 'blocked_polylang_deactivation' === $notice ) :
		echo '<div class="notice notice-error"><p>';
		echo esc_html(
			sprintf(
				/* translators: %s: plugin name. */
				__( 'You cannot deactivate Polylang while %s is active.', 'tms-extensions-polylang' ),
				tepll_plugin_get_name()
			)
		);
		echo '</p></div>';
		return;
	endif;

	if ( 'missing_polylang' === $notice ) :
		echo '<div class="notice notice-error"><p>';
		echo esc_html(
			sprintf(
				/* translators: %s: plugin name. */
				__( '%s was automatically deactivated because Polylang is not active.', 'tms-extensions-polylang' ),
				tepll_plugin_get_name()
			)
		);
		echo '</p></div>';
	endif;
} );
