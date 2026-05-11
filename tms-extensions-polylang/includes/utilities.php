<?php
/**
 * Includes -> Utilities
 * Utilities and helpers
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Get plugin name
 */

function tepll_plugin_get_name() : string {
	static $tepll_plugin_name_cache = null;

	if ( null !== $tepll_plugin_name_cache ) return $tepll_plugin_name_cache;

	$data = get_plugin_data( TEPLL_PLUGIN_FILE, false, false );
	$tepll_plugin_name_cache = $data['Name'] ?? 'Plugin';

	return $tepll_plugin_name_cache;
}


/**
 * File modification time for a path under the plugin directory, or plugin version if missing
 */
function tepll_plugin_get_asset_file_mtime( string $relative ): int {
	$path = TEPLL_PLUGIN_PATH . $relative;
	return file_exists( $path )
		? (int) filemtime( $path )
		: (int) TEPLL_PLUGIN_VERSION;
}


/**
 * Safely require a file from /includes relative path
 */

function tepll_include_file( string $relative ): void {
	$tepll_include_file_path = TEPLL_PLUGIN_PATH . 'includes/' . ltrim( $relative, '/' );

	if ( file_exists( $tepll_include_file_path ) ) :
		require_once $tepll_include_file_path;
	endif;
}
