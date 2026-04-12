<?php
/**
 * Blocks -> Pll Language Visibility -> Render
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! function_exists( 'tepll_language_visibility_get_html' ) ) return '';

$tepll_language_visibility_block_attributes = isset( $attributes ) && is_array( $attributes )
	? $attributes
	: array();

echo wp_kses(
	tepll_language_visibility_get_html(
		(string) ( $content ?? '' ),
		array(
			'lang' => (string) ( $tepll_language_visibility_block_attributes['lang'] ?? '' ),
			'mode' => (string) ( $tepll_language_visibility_block_attributes['mode'] ?? 'show_if' ),
		)
	),
	language_visibility_get_kses_allowed_html()
);
