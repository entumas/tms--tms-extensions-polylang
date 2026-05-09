<?php
/**
 * Includes -> Bootstrap
 * Plugin bootstrap loader
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


add_action( 'plugins_loaded', function() {

	// i18n
	tepll_include_file( 'blocks/i18n/block-json-strings.php' );

	// Components

	// Language switcher
	tepll_include_file( 'components/pll-language-switcher/helpers.php' );
	tepll_include_file( 'components/pll-language-switcher/dropdown-wrapper-split.php' );
	tepll_include_file( 'components/pll-language-switcher/get.php' );
	tepll_include_file( 'components/pll-language-switcher/shortcode.php' );

	// Language visibility
	tepll_include_file( 'components/pll-language-visibility/get.php' );
	tepll_include_file( 'components/pll-language-visibility/shortcode.php' );

	// Sitemap
	tepll_include_file( 'components/html-sitemap/helpers.php' );
	tepll_include_file( 'components/html-sitemap/get.php' );
	tepll_include_file( 'components/html-sitemap/shortcode.php' );
}, 20 );


// Blocks ========================================

add_action( 'init', function() {
	if ( ! function_exists( 'register_block_type' ) ) return;

	tepll_include_file( 'blocks/block-categories.php' );

	tepll_include_file( 'blocks/pll-language-visibility/register.php' );
	tepll_include_file( 'blocks/pll-language-switcher/register.php' );
	tepll_include_file( 'blocks/html-sitemap/register.php' );
}, 25 );
