<?php
/**
 * Includes -> Blocks -> Pll language switcher -> Register
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


$tepll_language_switcher_block_directory = __DIR__;
$tepll_language_switcher_block_url       = plugin_dir_url( __FILE__ );

// Editor assets
wp_register_style(
	'tepll-pll-language-switcher-editor',
	$tepll_language_switcher_block_url . 'editor.css',
	array(),
	filemtime( $tepll_language_switcher_block_directory . '/editor.css' )
);
wp_register_script(
	'tepll-pll-language-switcher-editor',
	$tepll_language_switcher_block_url . 'editor.js',
	array(
		'wp-blocks',
		'wp-element',
		'wp-components',
		'wp-block-editor',
		'wp-server-side-render',
	),
	filemtime( $tepll_language_switcher_block_directory . '/editor.js' ),
	true
);
wp_localize_script(
	'tepll-pll-language-switcher-editor',
	'tepllLanguageSwitcherEditorI18n',
	array(
		'panelTitle'          => __( 'Language Switcher Advanced', 'tms-extensions-polylang' ),
		'displayLabel'        => __( 'Display', 'tms-extensions-polylang' ),
		'displayList'         => __( 'List', 'tms-extensions-polylang' ),
		'displayDropdown'     => __( 'Dropdown', 'tms-extensions-polylang' ),
		'showText'            => __( 'Show text', 'tms-extensions-polylang' ),
		'textLabel'           => __( 'Link text', 'tms-extensions-polylang' ),
		'labelCode'           => __( 'Language code', 'tms-extensions-polylang' ),
		'labelName'           => __( 'Language name', 'tms-extensions-polylang' ),
		'vertical'            => __( 'Vertical list', 'tms-extensions-polylang' ),
		'showFlags'           => __( 'Show flags', 'tms-extensions-polylang' ),
		'hideCurrent'         => __( 'Hide current language', 'tms-extensions-polylang' ),
		'hideIfNoTranslation' => __( 'Hide if this page has no translation', 'tms-extensions-polylang' ),
		'redirectToHome'      => __( 'Redirect to language home', 'tms-extensions-polylang' ),
	)
);

// Frontend assets
wp_register_style(
	'tepll-pll-language-switcher',
	$tepll_language_switcher_block_url . 'style.css',
	array(),
	filemtime( $tepll_language_switcher_block_directory . '/style.css' )
);
wp_register_script(
	'tepll-pll-language-switcher',
	$tepll_language_switcher_block_url . 'script.js',
	array(),
	filemtime( $tepll_language_switcher_block_directory . '/script.js' ),
	true
);

// Register block
register_block_type( $tepll_language_switcher_block_directory );
