<?php
/**
 * Includes -> Blocks -> Pll language switcher -> Render
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


$tepll_language_switcher_block_instance = ( isset( $block ) && $block instanceof WP_Block )
	? $block
	: null;

$tepll_language_switcher_parsed_block_attributes = ( $tepll_language_switcher_block_instance instanceof WP_Block )
	? (array) ( $tepll_language_switcher_block_instance->parsed_block['attrs'] ?? array() )
	: array();
$tepll_language_switcher_block_attributes = wp_parse_args(
	isset( $attributes ) && is_array( $attributes ) ? $attributes : array(),
	$tepll_language_switcher_parsed_block_attributes
);

$tepll_language_switcher_display_mode = isset( $tepll_language_switcher_block_attributes['display'] )
	? (string) $tepll_language_switcher_block_attributes['display']
	: 'list';
$tepll_language_switcher_label_mode = isset( $tepll_language_switcher_block_attributes['label'] )
	? (string) $tepll_language_switcher_block_attributes['label']
	: 'code';
$tepll_language_switcher_show_text = isset( $tepll_language_switcher_block_attributes['show_text'] )
	? (bool) $tepll_language_switcher_block_attributes['show_text']
	: true;
$tepll_language_switcher_hide_current = isset( $tepll_language_switcher_block_attributes['hide_current'] )
	? (bool) $tepll_language_switcher_block_attributes['hide_current']
	: false;
$tepll_language_switcher_hide_if_no_translation = isset( $tepll_language_switcher_block_attributes['hide_if_no_translation'] )
	? (bool) $tepll_language_switcher_block_attributes['hide_if_no_translation']
	: false;
$tepll_language_switcher_redirect_to_home = isset( $tepll_language_switcher_block_attributes['redirect_to_home'] )
	? (bool) $tepll_language_switcher_block_attributes['redirect_to_home']
	: false;

$tepll_language_switcher_wrapper_attributes_raw = function_exists( 'get_block_wrapper_attributes' )
	? get_block_wrapper_attributes( array(), $tepll_language_switcher_block_instance )
	: '';
if ( '' === trim( (string) $tepll_language_switcher_wrapper_attributes_raw ) ) :
	$tepll_language_switcher_wrapper_attributes_raw = 'class="wp-block-tepll-pll-language-switcher"';
endif;

$tepll_language_switcher_is_dropdown = 'dropdown' === sanitize_key( $tepll_language_switcher_display_mode );
$tepll_language_switcher_wrapper_split       = tepll_language_switcher_dropdown_split_block_wrapper_attributes( $tepll_language_switcher_wrapper_attributes_raw, $tepll_language_switcher_is_dropdown );

$tepll_language_switcher_wrapper_shell = $tepll_language_switcher_wrapper_split['shell'];
$tepll_language_switcher_block_nav = array(
	'class' => isset( $tepll_language_switcher_wrapper_shell['class'] )
		? trim( (string) $tepll_language_switcher_wrapper_shell['class'] )
		: '',
	'style' => isset( $tepll_language_switcher_wrapper_shell['style'] )
		? trim( (string) $tepll_language_switcher_wrapper_shell['style'] )
		: '',
	'other_attrs' => isset( $tepll_language_switcher_wrapper_shell['other_attrs'] ) && is_array( $tepll_language_switcher_wrapper_shell['other_attrs'] )
		? $tepll_language_switcher_wrapper_shell['other_attrs']
		: array(),
);

$tepll_language_switcher_block_nav = tepll_language_switcher_apply_block_nav_link_color_as_text( $tepll_language_switcher_block_attributes, $tepll_language_switcher_block_nav );

$tepll_language_switcher_dropdown_ul_class = $tepll_language_switcher_is_dropdown ? (string) $tepll_language_switcher_wrapper_split['dropdown_ul']['class'] : '';
$tepll_language_switcher_dropdown_ul_style = $tepll_language_switcher_is_dropdown ? (string) $tepll_language_switcher_wrapper_split['dropdown_ul']['style'] : '';
$tepll_language_switcher_clickable_surface_class = (string) $tepll_language_switcher_wrapper_split['clickable']['class'];
$tepll_language_switcher_clickable_surface_style = (string) $tepll_language_switcher_wrapper_split['clickable']['style'];

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Inner markup escaped in `tepll_language_switcher_get_html()`
$tepll_language_switcher_markup = tepll_language_switcher_get_html(
	array(
		'display'                 => $tepll_language_switcher_display_mode,
		'vertical'                => ! empty( $tepll_language_switcher_block_attributes['vertical'] ),
		'label'                   => $tepll_language_switcher_label_mode,
		'show_text'               => $tepll_language_switcher_show_text,
		'show_flags'              => ! empty( $tepll_language_switcher_block_attributes['show_flags'] ),
		'hide_current'            => $tepll_language_switcher_hide_current,
		'hide_if_no_translation'  => $tepll_language_switcher_hide_if_no_translation,
		'redirect_to_home'        => $tepll_language_switcher_redirect_to_home,
		'dropdown_surface_class'  => $tepll_language_switcher_dropdown_ul_class,
		'dropdown_surface_style'  => $tepll_language_switcher_dropdown_ul_style,
		'clickable_class_extra'   => $tepll_language_switcher_clickable_surface_class,
		'clickable_style_extra'   => $tepll_language_switcher_clickable_surface_style,
		'block_nav'               => $tepll_language_switcher_block_nav,
	)
);

if ( '' === trim( (string) $tepll_language_switcher_markup ) ) return;

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo $tepll_language_switcher_markup;

$tepll_language_switcher_link_hover_style_markup = tepll_language_switcher_build_link_hover_style_tag( $tepll_language_switcher_block_attributes, $tepll_language_switcher_block_nav );

if ( '' !== trim( (string) $tepll_language_switcher_link_hover_style_markup ) ) :
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $tepll_language_switcher_link_hover_style_markup;
endif;
