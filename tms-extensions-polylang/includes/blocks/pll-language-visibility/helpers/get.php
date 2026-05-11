<?php
/**
 * Includes -> Blocks -> Pll language visibility -> Helpers -> Get
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


function tepll_language_visibility_get_html( string $content = '', array $args = array() ) : string {
	$defaults = array(
		'lang' => '',
		'mode' => 'show_if',
	);
	$args = wp_parse_args( $args, $defaults );

	$lang = isset( $args['lang'] )
		? sanitize_key( (string) $args['lang'] )
		: '';
	$mode = isset( $args['mode'] )
		? sanitize_key( (string) $args['mode'] )
		: 'show_if';

	if ( ! in_array( $mode, array( 'show_if', 'hide_if' ), true ) ) :
		$mode = 'show_if';
	endif;

	if ( '' === $lang ) return $content;

	$current_lang   = (string) pll_current_language( 'slug' );
	$is_target_lang = ( $current_lang === $lang );

	if ( 'hide_if' === $mode ) :
		return $is_target_lang
			? ''
			: $content;
	endif;

	return $is_target_lang
		? $content
		: '';
}
