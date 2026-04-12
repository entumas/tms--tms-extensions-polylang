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

if ( ! function_exists( 'tepll_plugin_get_name' ) ) :
	function tepll_plugin_get_name() : string {
		static $tepll_plugin_name_cache = null;

		if ( null !== $tepll_plugin_name_cache ) return $tepll_plugin_name_cache;

		$data = get_plugin_data( TEPLL_PLUGIN_FILE, false, false );
		$tepll_plugin_name_cache = $data['Name'] ?? 'Plugin';

		return $tepll_plugin_name_cache;
	}
endif;


/**
 * Safely require a file from /includes relative path
 */

if ( ! function_exists( 'tepll_include_file' ) ) :
	function tepll_include_file( string $relative ): void {
		$tepll_include_file_path = TEPLL_PLUGIN_PATH . 'includes/' . ltrim( $relative, '/' );

		if ( file_exists( $tepll_include_file_path ) ) :
			require_once $tepll_include_file_path;
		endif;
	}
endif;
