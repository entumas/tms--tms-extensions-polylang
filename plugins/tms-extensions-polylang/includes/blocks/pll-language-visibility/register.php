<?php
/**
 * Blocks -> Pll Language Visibility -> Register
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


$tepll_language_visibility_block_directory = __DIR__;
$tepll_language_visibility_block_url = plugin_dir_url( __FILE__ );

$tepll_language_visibility_i18n_all_languages = __( 'All languages', 'tms-extensions-polylang' );

// Editor assets
wp_register_style(
	'tepll-pll-language-visibility-editor',
	$tepll_language_visibility_block_url . 'editor.css',
	array(),
	filemtime( $tepll_language_visibility_block_directory . '/editor.css' )
);
wp_register_script(
	'tepll-pll-language-visibility-editor',
	$tepll_language_visibility_block_url . 'editor.js',
	array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-data' ),
	filemtime( $tepll_language_visibility_block_directory . '/editor.js' ),
	true
);
wp_localize_script(
	'tepll-pll-language-visibility-editor',
	'tepllLanguageVisibilityEditorI18n',
	array(
		'allLanguages'              => $tepll_language_visibility_i18n_all_languages,
		'languageVisibility'        => __( 'Language Visibility', 'tms-extensions-polylang' ),
		'language'                  => __( 'Language', 'tms-extensions-polylang' ),
		'visibilityMode'            => __( 'Visibility mode', 'tms-extensions-polylang' ),
		'showInSelectedLanguage'    => __( 'Show in selected language', 'tms-extensions-polylang' ),
		'hideInSelectedLanguage'    => __( 'Hide in selected language', 'tms-extensions-polylang' ),
		'languageVisibilityContent' => __( 'Language Visibility content', 'tms-extensions-polylang' ),
	)
);

// Language select options from Polylang
$tepll_language_visibility_language_options = array(
	array(
		'value' => '',
		'label' => $tepll_language_visibility_i18n_all_languages,
	),
);
$tepll_language_visibility_polylang_slugs = pll_languages_list( array( 'fields' => 'slug' ) );
$tepll_language_visibility_polylang_names = pll_languages_list( array( 'fields' => 'name' ) );

if ( is_array( $tepll_language_visibility_polylang_slugs ) && is_array( $tepll_language_visibility_polylang_names ) ) :
	foreach ( $tepll_language_visibility_polylang_slugs as $tepll_language_visibility_lang_index => $tepll_language_visibility_lang_slug ) :
		$tepll_language_visibility_language_options[] = array(
			'value' => $tepll_language_visibility_lang_slug,
			'label' => $tepll_language_visibility_polylang_names[ $tepll_language_visibility_lang_index ] ?? strtoupper( $tepll_language_visibility_lang_slug ),
		);
	endforeach;
endif;
wp_add_inline_script(
	'tepll-pll-language-visibility-editor',
	'window.tepllLanguageVisibilityEditorLanguageOptions = ' . wp_json_encode( $tepll_language_visibility_language_options ) . ';',
	'before'
);

// Register block
register_block_type( $tepll_language_visibility_block_directory );
