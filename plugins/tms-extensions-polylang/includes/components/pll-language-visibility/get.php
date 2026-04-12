<?php
/**
 * Includes -> Components -> Pll Language Visibility -> Get
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Get
 */

if ( ! function_exists( 'tepll_language_visibility_get_html' ) ) :
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
endif;


/**
 * KSES allowlist for Language Visibility inner HTML.
 *
 * Uses wp_kses_allowed_html( 'post' ) as the base so any Gutenberg-safe markup stays
 * allowed, then merges form-related tags and attributes wp_kses_post strips (Spectra,
 * contact forms, etc.).
 */
if ( ! function_exists( 'language_visibility_get_kses_allowed_html' ) ) :
	function language_visibility_get_kses_allowed_html() : array {
		$allowed = wp_kses_allowed_html( 'post' );

		$common = array(
			'class'    => true,
			'id'       => true,
			'style'    => true,
			'title'    => true,
			'role'     => true,
			'tabindex' => true,
			'lang'     => true,
			'dir'      => true,
			'hidden'   => true,
		);

		$aria = array(
			'aria-label'            => true,
			'aria-labelledby'       => true,
			'aria-describedby'      => true,
			'aria-required'         => true,
			'aria-invalid'          => true,
			'aria-hidden'           => true,
			'aria-expanded'         => true,
			'aria-controls'         => true,
			'aria-haspopup'         => true,
			'aria-live'             => true,
			'aria-atomic'           => true,
			'aria-relevant'         => true,
			'aria-busy'             => true,
			'aria-checked'          => true,
			'aria-disabled'         => true,
			'aria-selected'         => true,
			'aria-pressed'          => true,
			'aria-current'          => true,
			'aria-valuemin'         => true,
			'aria-valuemax'         => true,
			'aria-valuenow'         => true,
			'aria-valuetext'        => true,
			'aria-modal'            => true,
			'aria-autocomplete'     => true,
			'aria-multiline'        => true,
			'aria-orientation'      => true,
			'aria-readonly'         => true,
			'aria-sort'             => true,
			'aria-colcount'         => true,
			'aria-colindex'         => true,
			'aria-colspan'          => true,
			'aria-rowcount'         => true,
			'aria-rowindex'         => true,
			'aria-rowspan'          => true,
		);

		$data_block = array(
			'data-block'            => true,
			'data-type'             => true,
			'data-id'               => true,
			'data-title'            => true,
			'data-placeholder'      => true,
			'data-link'             => true,
			'data-url'              => true,
			'data-href'             => true,
			'data-target'           => true,
			'data-rel'              => true,
			'data-value'            => true,
			'data-name'             => true,
			'data-label'            => true,
			'data-toggle'           => true,
			'data-dismiss'          => true,
			'data-trigger'          => true,
			'data-animation'        => true,
			'data-delay'            => true,
			'data-interval'         => true,
			'data-keyboard'         => true,
			'data-wp-interactive'   => true,
			'data-wp-context'       => true,
			'data-wp-bind--hidden'  => true,
			'data-wp-on--click'     => true,
			'data-wp-on--focusout'  => true,
			'data-wp-on--keydown'   => true,
			'data-wp-on--mouseenter' => true,
			'data-wp-on--mouseleave' => true,
			'data-wp-on--focus'     => true,
			'data-wp-watch'         => true,
		);

		$extras = array_merge( $common, $aria, $data_block );

		$form_field = array_merge(
			$extras,
			array(
				'type'             => true,
				'name'             => true,
				'value'            => true,
				'placeholder'      => true,
				'required'         => true,
				'disabled'         => true,
				'readonly'         => true,
				'checked'          => true,
				'maxlength'        => true,
				'min'              => true,
				'max'              => true,
				'step'             => true,
				'pattern'          => true,
				'inputmode'        => true,
				'autocomplete'     => true,
				'multiple'         => true,
				'accept'           => true,
				'size'             => true,
				'width'            => true,
				'height'           => true,
				'align'            => true,
				'form'             => true,
				'formaction'       => true,
				'formenctype'      => true,
				'formmethod'       => true,
				'formnovalidate'  => true,
				'formtarget'       => true,
				'list'             => true,
				'spellcheck'       => true,
			)
		);

		$merge = function ( string $tag, array $add ) use ( &$allowed ) {
			$base = isset( $allowed[ $tag ] ) && is_array( $allowed[ $tag ] )
				? $allowed[ $tag ]
				: array();
			$allowed[ $tag ] = array_merge( $base, $add );
		};

		$merge( 'form', array_merge( $extras, array(
			'action'     => true,
			'method'     => true,
			'enctype'    => true,
			'name'       => true,
			'target'     => true,
			'novalidate' => true,
		) ) );

		$merge( 'input', $form_field );
		$merge( 'select', array_merge( $extras, array(
			'name'         => true,
			'required'     => true,
			'disabled'     => true,
			'multiple'     => true,
			'size'         => true,
			'autocomplete' => true,
		) ) );
		$merge( 'option', array_merge( $extras, array(
			'value'    => true,
			'selected' => true,
			'disabled' => true,
			'label'    => true,
		) ) );
		$merge( 'optgroup', array_merge( $extras, array(
			'label'    => true,
			'disabled' => true,
		) ) );
		$merge( 'textarea', array_merge( $form_field, array(
			'rows' => true,
			'cols' => true,
			'wrap' => true,
		) ) );
		$merge( 'label', array_merge( $extras, array( 'for' => true ) ) );
		$merge( 'fieldset', array_merge( $extras, array(
			'disabled' => true,
			'name'     => true,
		) ) );
		$merge( 'legend', $extras );
		$merge( 'datalist', array_merge( $extras, array( 'id' => true ) ) );
		$merge( 'output', array_merge( $extras, array(
			'for'  => true,
			'name' => true,
		) ) );
		$merge( 'button', array_merge( $form_field, array(
			'type'  => true,
			'name'  => true,
			'value' => true,
		) ) );
		$merge( 'dialog', array_merge( $extras, array( 'open' => true ) ) );

		return apply_filters( 'tepll_language_visibility_kses_allowed_html', $allowed );
	}
endif;


/**
 * Print
 */

if ( ! function_exists( 'tepll_language_visibility_print_html' ) ) :
	function tepll_language_visibility_print_html( string $content = '', array $args = array() ) : void {
		echo wp_kses(
			tepll_language_visibility_get_html( $content, $args ),
			language_visibility_get_kses_allowed_html()
		);
	}
endif;
