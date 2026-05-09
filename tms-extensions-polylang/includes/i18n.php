<?php
/**
 * Includes -> i18n
 * Internationalization loader
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


add_action( 'init', function() {
	$slug   = 'tms-extensions-polylang';
	$locale = function_exists( 'get_user_locale' )
		? get_user_locale()
		: get_locale();
	$mofile = WP_PLUGIN_DIR . "/{$slug}/languages/{$slug}-{$locale}.mo";
	if ( file_exists( $mofile ) ) :
		load_textdomain( $slug, $mofile );
	else :
		// phpcs:ignore PluginCheck.CodeAnalysis.DiscouragedFunctions.load_plugin_textdomainFound -- Fallback for environments where there are no language packs in wp-content/languages/plugins.
		load_plugin_textdomain( $slug, false, "{$slug}/languages" );
	endif;
}, 5 );
