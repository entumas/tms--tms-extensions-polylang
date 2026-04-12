<?php
/**
 * Plugin Name:       TMS Extensions for Polylang
 * Plugin URI:        https://github.com/entumas/tms--tms-extensions-polylang
 * Description:       Extends Polylang with reusable multilingual tools for block-based sites.
 * Version:           1.0.0
 * Author:            Tumàs Muntané
 * Author URI:        https://tumasmuntane.com/
 * Text Domain:       tms-extensions-polylang
 * Domain Path:       /languages
 * Requires Plugins:  polylang
 * Requires at least: 6.0
 * Tested up to:      6.9
 * Requires PHP:      8.0
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */
if ( ! defined( 'ABSPATH' ) ) exit;


// Constants ========================================

define( 'TEPLL_PLUGIN_FILE', __FILE__ );
define( 'TEPLL_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'TEPLL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'TEPLL_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

if ( ! function_exists( 'get_plugin_data' ) ) require_once ABSPATH . 'wp-admin/includes/plugin.php';
$tepll_plugin_metadata = get_plugin_data( TEPLL_PLUGIN_FILE, false, false );
define( 'TEPLL_PLUGIN_VERSION', $tepll_plugin_metadata['Version'] ?? '1.0.2' );


// Early includes ========================================

// Hard dependencies (activation guard, notices, flush)
require_once TEPLL_PLUGIN_PATH . 'includes/dependencies.php';

// i18n (text domain)
require_once TEPLL_PLUGIN_PATH . 'includes/i18n.php';

// Utilities (safe include helper)
require_once TEPLL_PLUGIN_PATH . 'includes/utilities.php';

// Bootstrap (module loader)
require_once TEPLL_PLUGIN_PATH . 'includes/bootstrap.php';
