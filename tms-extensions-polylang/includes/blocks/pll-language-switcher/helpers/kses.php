<?php
/**
 * Includes -> Blocks -> Pll language switcher -> Helpers -> KSES
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Extended `wp_kses_allowed_html` map for switcher markup when output passes through `wp_kses()`
 */
function tepll_language_switcher_get_kses_allowed_html() : array {
	$allowed = wp_kses_allowed_html( 'post' );
	if ( ! isset( $allowed['button'] ) || ! is_array( $allowed['button'] ) ) :
		$allowed['button'] = array();
	endif;
	$allowed['button'] = array_merge(
		$allowed['button'],
		array(
			'class'           => true,
			'style'           => true,
			'id'              => true,
			'type'            => true,
			'disabled'        => true,
			'name'            => true,
			'value'           => true,
			'aria-expanded'   => true,
			'aria-controls'   => true,
			'aria-haspopup'   => true,
			'aria-label'      => true,
			'aria-labelledby' => true,
		)
	);

	if ( ! isset( $allowed['nav'] ) || ! is_array( $allowed['nav'] ) ) :
		$allowed['nav'] = array();
	endif;
	$allowed['nav'] = array_merge(
		$allowed['nav'],
		array(
			'class'            => true,
			'style'            => true,
			'aria-label'       => true,
			'aria-describedby' => true,
		)
	);

	return $allowed;
}
