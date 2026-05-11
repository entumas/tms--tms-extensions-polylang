<?php
/**
 * Includes -> Blocks -> Pll menu by language -> Register
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


$tepll_menu_by_language_block_directory = __DIR__;
$tepll_menu_by_language_block_url       = plugin_dir_url( __FILE__ );
$tepll_menu_by_language_slot_directory  = dirname( __DIR__ ) . '/pll-menu-by-language-slot';

wp_register_style(
	'tepll-pll-menu-by-language-editor',
	$tepll_menu_by_language_block_url . 'editor.css',
	array(),
	filemtime( $tepll_menu_by_language_block_directory . '/editor.css' )
);

wp_register_script(
	'tepll-pll-menu-by-language-editor',
	$tepll_menu_by_language_block_url . 'editor.js',
	array(
		'wp-blocks',
		'wp-element',
		'wp-components',
		'wp-block-editor',
		'wp-data',
		'wp-core-data',
		'wp-server-side-render',
	),
	filemtime( $tepll_menu_by_language_block_directory . '/editor.js' ),
	true
);

$tepll_menu_by_language_editor_languages = array();
$tepll_menu_by_language_polylang_slugs   = pll_languages_list( array( 'fields' => 'slug' ) );
$tepll_menu_by_language_polylang_names   = pll_languages_list( array( 'fields' => 'name' ) );

if ( is_array( $tepll_menu_by_language_polylang_slugs ) && is_array( $tepll_menu_by_language_polylang_names ) ) :
	foreach ( $tepll_menu_by_language_polylang_slugs as $tepll_menu_by_language_lang_index => $tepll_menu_by_language_lang_slug ) :
		$tepll_menu_by_language_editor_languages[] = array(
			'slug' => sanitize_key( (string) $tepll_menu_by_language_lang_slug ),
			'name' => (string) ( $tepll_menu_by_language_polylang_names[ $tepll_menu_by_language_lang_index ] ?? $tepll_menu_by_language_lang_slug ),
		);
	endforeach;
endif;

$tepll_menu_by_language_current_admin_lang = '';
if ( function_exists( 'pll_get_current_language' ) ) :
	$tepll_menu_by_language_current_admin_lang = sanitize_key( (string) pll_get_current_language( 'slug' ) );
endif;
if ( '' === $tepll_menu_by_language_current_admin_lang && function_exists( 'pll_current_language' ) ) :
	$tepll_menu_by_language_current_admin_lang = sanitize_key( (string) pll_current_language( 'slug' ) );
endif;
if ( '' === $tepll_menu_by_language_current_admin_lang && function_exists( 'pll_default_language' ) ) :
	$tepll_menu_by_language_current_admin_lang = sanitize_key( (string) pll_default_language( 'slug' ) );
endif;

wp_localize_script(
	'tepll-pll-menu-by-language-editor',
	'tepllMenuByLanguageEditor',
	array(
		'languageAssignments' => __( 'Language assignments', 'tms-extensions-polylang' ),
		'menuBlockTitle'      => _x( 'Menu by Language', 'block title in editor placeholder', 'tms-extensions-polylang' ),
		'noNavigationMenus'   => __( 'No published navigation menus found. Create one under Appearance → Editor or the Navigation menu screen.', 'tms-extensions-polylang' ),
		'currentLang'         => $tepll_menu_by_language_current_admin_lang,
		'noLanguages'         => __( 'No languages are configured in Polylang.', 'tms-extensions-polylang' ),
		'languages'           => $tepll_menu_by_language_editor_languages,
	)
);

register_block_type( $tepll_menu_by_language_slot_directory );
register_block_type( $tepll_menu_by_language_block_directory );
