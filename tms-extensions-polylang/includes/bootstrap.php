<?php
/**
 * Includes -> Bootstrap
 * Plugin bootstrap loader
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'plugins_loaded', function() {

	if ( is_admin() ) :
		tepll_include_file( 'admin/plugin-list.php' );
	endif;

	// i18n
	tepll_include_file( 'blocks/i18n/block-json-strings.php' );

	// Block helpers ========================================

	tepll_include_file( 'blocks/pll-menu-by-language/helpers/get.php' );

	tepll_include_file( 'blocks/pll-language-switcher/helpers/dropdown-wrapper.php' );
	tepll_include_file( 'blocks/pll-language-switcher/helpers/flags.php' );
	tepll_include_file( 'blocks/pll-language-switcher/helpers/pll-languages.php' );
	tepll_include_file( 'blocks/pll-language-switcher/helpers/block-editor-styles.php' );
	tepll_include_file( 'blocks/pll-language-switcher/helpers/kses.php' );
	tepll_include_file( 'blocks/pll-language-switcher/helpers/render-markup.php' );
	tepll_include_file( 'blocks/pll-language-switcher/helpers/get.php' );

	tepll_include_file( 'blocks/pll-language-visibility/helpers/kses.php' );
	tepll_include_file( 'blocks/pll-language-visibility/helpers/get.php' );

	tepll_include_file( 'blocks/html-sitemap/helpers/taxonomy.php' );
	tepll_include_file( 'blocks/html-sitemap/helpers/normalize.php' );
	tepll_include_file( 'blocks/html-sitemap/helpers/block-editor.php' );
	tepll_include_file( 'blocks/html-sitemap/helpers/polylang.php' );
	tepll_include_file( 'blocks/html-sitemap/helpers/pages.php' );
	tepll_include_file( 'blocks/html-sitemap/helpers/posts.php' );
	tepll_include_file( 'blocks/html-sitemap/helpers/terms.php' );
	tepll_include_file( 'blocks/html-sitemap/helpers/render-markup.php' );
	tepll_include_file( 'blocks/html-sitemap/helpers/get.php' );
}, 20 );


// Blocks ========================================

add_action( 'init', function() {
	if ( ! function_exists( 'register_block_type' ) ) return;

	tepll_include_file( 'blocks/block-categories.php' );

	tepll_include_file( 'blocks/pll-menu-by-language/register.php' );
	tepll_include_file( 'blocks/pll-language-visibility/register.php' );
	tepll_include_file( 'blocks/pll-language-switcher/register.php' );
	tepll_include_file( 'blocks/html-sitemap/register.php' );
}, 25 );
