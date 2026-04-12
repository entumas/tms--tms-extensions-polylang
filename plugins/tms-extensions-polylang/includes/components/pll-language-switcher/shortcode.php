<?php
/**
 * Includes -> Components -> Pll Language Switcher -> Shortcode
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


add_shortcode( 'tepll-language-switcher', function( $atts = array() ) : string {
	if ( ! function_exists( 'tepll_language_switcher_get_html' ) ) return '';

	$atts = shortcode_atts(
		array(
			'display'                => 'list',
			'vertical'               => '0',
			'label'                  => 'code',
			'show_text'              => '1',
			'show_flags'             => '0',
			'hide_current'           => '0',
			'hide_if_no_translation' => '0',
		),
		(array) $atts,
		'tepll-language-switcher'
	);

	$bool_attr = static function ( $value ) : bool {
		return in_array( strtolower( trim( (string) $value ) ), array( '1', 'true', 'yes', 'on' ), true );
	};

	return tepll_language_switcher_get_html(
		array(
			'display'                => (string) $atts['display'],
			'vertical'               => $bool_attr( $atts['vertical'] ),
			'label'                  => (string) $atts['label'],
			'show_text'              => $bool_attr( $atts['show_text'] ),
			'show_flags'             => $bool_attr( $atts['show_flags'] ),
			'hide_current'           => $bool_attr( $atts['hide_current'] ),
			'hide_if_no_translation' => $bool_attr( $atts['hide_if_no_translation'] ),
		)
	);
} );
