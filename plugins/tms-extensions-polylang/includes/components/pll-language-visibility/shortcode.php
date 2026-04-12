<?php
/**
 * Includes -> Components -> Pll Language Visibility -> Shortcode
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


add_shortcode( 'tepll-language-visibility', function( $atts = array(), $content = null ) : string {
	if ( ! function_exists( 'tepll_language_visibility_get_html' ) ) return '';

	$atts = shortcode_atts(
		array(
			'lang' => '',
			'mode' => 'show_if',
		),
		(array) $atts,
		'tepll-language-visibility'
	);

	return wp_kses(
		tepll_language_visibility_get_html(
			do_shortcode( (string) ( $content ?? '' ) ),
			array(
				'lang' => (string) $atts['lang'],
				'mode' => (string) $atts['mode'],
			)
		),
		language_visibility_get_kses_allowed_html()
	);
} );
