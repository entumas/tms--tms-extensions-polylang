<?php
/**
 * Includes -> Admin enqueue
 * Backend scripts & styles
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


add_action( 'init', function (): void {

	$editor_css = 'assets/css/editor.css';

	wp_register_style(
		'tepll-editor',
		TEPLL_PLUGIN_URL . $editor_css,
		array(),
		tepll_plugin_get_asset_file_mtime( $editor_css )
	);
}, 5 );


/**
 * Post editor, site editor, etc
 */
function tepll_admin_is_block_editor_canvas(): bool {
	if ( ! is_admin() ) return false;

	global $pagenow;
	if ( isset( $pagenow ) && 'site-editor.php' === $pagenow ) return true;

	$screen = function_exists( 'get_current_screen' )
		? get_current_screen()
		: null;

	return $screen && $screen->is_block_editor();
}

add_action( 'admin_enqueue_scripts', function () {
	if ( ! tepll_admin_is_block_editor_canvas() ) return;

	wp_enqueue_style( 'tepll-editor' );
}, 20 );
