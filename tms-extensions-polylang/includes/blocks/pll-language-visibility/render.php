<?php
/**
 * Includes -> Blocks -> Pll language visibility -> Render
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


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
	tepll_language_visibility_get_kses_allowed_html()
);
