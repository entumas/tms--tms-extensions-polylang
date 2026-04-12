<?php
/**
 * Includes -> Components -> Pll Language Switcher -> Dropdown wrapper split
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Parsing helpers
 */

if ( ! function_exists( 'language_switcher_dropdown_parse_wrapper_attributes_string' ) ) :
	function language_switcher_dropdown_parse_wrapper_attributes_string( string $attrs ) : array {
		$attrs = trim( $attrs );
		$out   = array();
		if ( '' === $attrs ) return $out;

		if ( preg_match( '/\bclass\s*=\s*"([^"]*)"/i', $attrs, $m ) ) :
			$out['class'] = html_entity_decode( $m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		elseif ( preg_match( "/\\bclass\\s*=\\s*'([^']*)'/i", $attrs, $m ) ) :
			$out['class'] = html_entity_decode( $m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		endif;

		if ( preg_match( '/\bstyle\s*=\s*"([^"]*)"/i', $attrs, $m ) ) :
			$out['style'] = html_entity_decode( $m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		elseif ( preg_match( "/\\bstyle\\s*=\\s*'([^']*)'/i", $attrs, $m ) ) :
			$out['style'] = html_entity_decode( $m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		endif;

		if ( preg_match_all( '/([\w:-]+)\s*=\s*(?:"([^"]*)"|\'([^\']*)\')/', $attrs, $matches, PREG_SET_ORDER ) ) :
			foreach ( $matches as $set ) :
				$k  = strtolower( (string) $set[1] );
				$v2 = isset( $set[2] ) && $set[2] !== ''
					? $set[2]
					: ( isset( $set[3] ) ? $set[3] : '' );
				if ( '' === $k || 'class' === $k || 'style' === $k ) continue;
				if ( isset( $out[ $k ] ) && '' !== (string) $out[ $k ] ) continue;
				$out[ $k ] = html_entity_decode( (string) $v2, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
			endforeach;
		endif;

		return $out;
	}
endif;

if ( ! function_exists( 'language_switcher_dropdown_parse_inline_style_declarations' ) ) :
	function language_switcher_dropdown_parse_inline_style_declarations( string $style ) : array {
		$out   = array();
		$style = trim( (string) $style );
		if ( '' === $style ) return $out;

		$parts = preg_split( '/\s*;\s*/', $style );
		if ( ! is_array( $parts ) ) return $out;

		foreach ( $parts as $chunk ) :
			$chunk = trim( $chunk );
			if ( '' === $chunk ) continue;
			$colon = strpos( $chunk, ':' );
			if ( false === $colon ) continue;

			$p = trim( substr( $chunk, 0, $colon ) );
			$v = trim( substr( $chunk, $colon + 1 ) );
			if ( '' !== $p ) :
				$out[ $p ] = $v;
			endif;
		endforeach;

		return $out;
	}
endif;

if ( ! function_exists( 'language_switcher_dropdown_join_inline_style' ) ) :
	function language_switcher_dropdown_join_inline_style( array $declarations ) : string {
		if ( empty( $declarations ) ) return '';

		$bits = array();
		foreach ( $declarations as $prop => $value ) :
			$prop = trim( (string) $prop );
			$value = trim( (string) $value );
			if ( '' === $prop || '' === $value ) continue;
			$bits[] = $prop . ': ' . $value;
		endforeach;

		return implode( '; ', $bits );
	}
endif;

if ( ! function_exists( 'language_switcher_dropdown_filter_empty_attr_pairs' ) ) :
	function language_switcher_dropdown_filter_empty_attr_pairs( array $pairs ) : array {
		$out = array();
		foreach ( $pairs as $k => $v ) :
			if ( null === $v ) continue;
			if ( ! is_string( $v ) || '' === trim( $v ) ) continue;
			$out[ (string) $k ] = $v;
		endforeach;

		return $out;
	}
endif;

if ( ! function_exists( 'language_switcher_dropdown_build_html_attributes_fragment' ) ) :
	function language_switcher_dropdown_build_html_attributes_fragment( array $pairs ) : string {
		$bits = array();
		foreach ( $pairs as $name => $value ) :
			$name = trim( (string) $name );
			if ( '' === $name || ! preg_match( '/^[a-zA-Z][a-zA-Z0-9_:-]*$/', $name ) ) continue;
			$bits[] = sprintf( '%s="%s"', esc_attr( $name ), esc_attr( (string) $value ) );
		endforeach;

		return implode( ' ', $bits );
	}
endif;


/**
 * Classification helpers
 */

if ( ! function_exists( 'language_switcher_dropdown_is_surface_class' ) ) :
	function language_switcher_dropdown_is_surface_class( string $class ) : bool {
		$class = trim( $class );
		if ( '' === $class ) return false;
		if ( 'has-background' === $class ) return true;
		if ( preg_match( '/^has-[a-z0-9\-]+-background-color$/', $class ) ) return true;
		if ( preg_match( '/^has-background-dim/', $class ) ) return true;
		if ( false !== strpos( $class, 'gradient' ) && false !== strpos( $class, 'background' ) ) return true;
		if ( 0 === strpos( $class, 'has-border' ) ) return true;
		if ( preg_match( '/^has-[a-z0-9\-]+-border-color$/', $class ) ) return true;

		return false;
	}
endif;

if ( ! function_exists( 'language_switcher_dropdown_is_surface_style_property' ) ) :
	function language_switcher_dropdown_is_surface_style_property( string $prop ) : bool {
		$p = strtolower( trim( $prop ) );
		if ( '' === $p ) return false;

		if ( 0 === strpos( $p, '--wp--' ) ) :
			if ( false !== strpos( $p, 'color--text' ) || false !== strpos( $p, 'color--link' ) ) return false;
			if ( false !== strpos( $p, 'typography' ) || false !== strpos( $p, 'font-size' ) || false !== strpos( $p, 'font-family' ) ) return false;
			if ( false !== strpos( $p, 'padding' ) || false !== strpos( $p, 'root--padding' ) ) return false;
			if ( false !== strpos( $p, 'color--background' )
				|| false !== strpos( $p, 'gradient' )
				|| false !== strpos( $p, 'border' )
				|| false !== strpos( $p, 'shadow' )
				|| false !== strpos( $p, 'margin' ) ) :
				return true;
			endif;
			return false;
		endif;

		if ( 'filter' === $p ) return true;
		if ( false !== strpos( $p, 'box-shadow' ) ) return true;

		$roots = array( 'margin', 'border', 'outline', 'background' );
		foreach ( $roots as $root ) :
			$len = strlen( $root );
			if ( $p === $root ) return true;
			if ( strlen( $p ) > $len && '-' === $p[ $len ] && 0 === strpos( $p, $root . '-' ) ) return true;
		endforeach;

		if ( preg_match( '/^-(?:webkit|moz|ms|o)-(?:margin|border|outline|background|box-shadow)(?:$|-)/', $p ) ) return true;

		return false;
	}
endif;

if ( ! function_exists( 'language_switcher_dropdown_is_clickable_padding_style_property' ) ) :
	function language_switcher_dropdown_is_clickable_padding_style_property( string $prop ) : bool {
		$p = strtolower( trim( $prop ) );
		if ( '' === $p ) return false;

		if ( 0 === strpos( $p, '--wp--' ) ) :
			if ( false !== strpos( $p, 'margin' ) ) return false;
			if ( false !== strpos( $p, 'padding' ) || false !== strpos( $p, 'root--padding' ) ) return true;
			return false;
		endif;

		if ( 0 === strpos( $p, 'padding' ) ) return true;
		if ( preg_match( '/^-(?:webkit|moz|ms|o)-padding/', $p ) ) return true;

		return false;
	}
endif;

if ( ! function_exists( 'language_switcher_dropdown_is_clickable_padding_class' ) ) :
	function language_switcher_dropdown_is_clickable_padding_class( string $class ) : bool {
		$class = trim( $class );
		if ( '' === $class ) return false;
		if ( preg_match( '/^has-[a-z0-9\-]+-padding$/', $class ) ) return true;
		if ( 0 === strpos( $class, 'has-custom-padding' ) ) return true;

		return false;
	}
endif;

if ( ! function_exists( 'language_switcher_dropdown_should_use_color_class_not_shell_style' ) ) :
	function language_switcher_dropdown_should_use_color_class_not_shell_style( string $prop ) : bool {
		$p = strtolower( trim( $prop ) );
		if ( '' === $p ) return false;

		if ( 'color' === $p ) return true;
		if ( preg_match( '/^-(?:webkit|moz|ms|o)-text-fill-color/', $p ) ) return true;
		if ( preg_match( '/^-(?:webkit|moz|ms|o)-text-stroke-color/', $p ) ) return true;

		if ( 0 === strpos( $p, '--wp--' ) ) :
			if ( false !== strpos( $p, 'color--text' ) || false !== strpos( $p, 'color--link' ) ) return true;
			if ( false !== strpos( $p, 'elements--link' ) && false !== strpos( $p, 'color' ) ) return true;
			return false;
		endif;

		return false;
	}
endif;


/**
 * Merge helpers
 */

if ( ! function_exists( 'language_switcher_dropdown_merge_ul_surface_attrs' ) ) :
	function language_switcher_dropdown_merge_ul_surface_attrs( string $base_class, string $extra_class, string $base_style, string $extra_style ) : array {
		$classes = preg_split( '/\s+/', trim( $base_class ), -1, PREG_SPLIT_NO_EMPTY );
		if ( ! is_array( $classes ) ) :
			$classes = array();
		endif;
		$extra_c = preg_split( '/\s+/', trim( $extra_class ), -1, PREG_SPLIT_NO_EMPTY );
		if ( is_array( $extra_c ) && ! empty( $extra_c ) ) :
			$classes = array_values( array_unique( array_merge( $classes, $extra_c ) ) );
		endif;

		$dec_base  = language_switcher_dropdown_parse_inline_style_declarations( $base_style );
		$dec_extra = language_switcher_dropdown_parse_inline_style_declarations( $extra_style );
		$merged    = array_merge( $dec_base, $dec_extra );

		return array(
			'class' => implode( ' ', $classes ),
			'style' => language_switcher_dropdown_join_inline_style( $merged ),
		);
	}
endif;


/**
 * Main split function
 */

if ( ! function_exists( 'language_switcher_dropdown_split_block_wrapper_attributes' ) ) :
	function language_switcher_dropdown_split_block_wrapper_attributes( string $wrapper_attrs, bool $is_dropdown ) : array {
		$empty = array(
			'shell'       => array(
				'class'       => '',
				'style'       => '',
				'other_attrs' => array(),
			),
			'dropdown_ul' => array(
				'class' => '',
				'style' => '',
			),
			'clickable'   => array(
				'class' => '',
				'style' => '',
			),
		);
		if ( '' === trim( $wrapper_attrs ) ) return $empty;

		$parsed = language_switcher_dropdown_parse_wrapper_attributes_string( $wrapper_attrs );

		$class_tokens = isset( $parsed['class'] ) && is_string( $parsed['class'] )
			? preg_split( '/\s+/', trim( $parsed['class'] ), -1, PREG_SPLIT_NO_EMPTY )
			: array();
		$style_decl = isset( $parsed['style'] ) && is_string( $parsed['style'] )
			? language_switcher_dropdown_parse_inline_style_declarations( $parsed['style'] )
			: array();

		$shell_classes     = array();
		$ul_classes        = array();
		$clickable_classes = array();
		foreach ( $class_tokens as $c ) :
			if ( $is_dropdown && language_switcher_dropdown_is_surface_class( $c ) ) :
				$ul_classes[] = $c;
			elseif ( language_switcher_dropdown_is_clickable_padding_class( $c ) ) :
				$clickable_classes[] = $c;
			else :
				$shell_classes[] = $c;
			endif;
		endforeach;

		$shell_styles     = array();
		$ul_styles        = array();
		$clickable_styles = array();
		foreach ( $style_decl as $prop => $value ) :
			$p = (string) $prop;
			if ( $is_dropdown && language_switcher_dropdown_is_surface_style_property( $p ) ) :
				$ul_styles[ $p ] = $value;
			elseif ( language_switcher_dropdown_is_clickable_padding_style_property( $p ) ) :
				$clickable_styles[ $p ] = $value;
			else :
				$shell_styles[ $p ] = $value;
			endif;
		endforeach;

		unset( $parsed['class'], $parsed['style'] );

		foreach ( array_keys( $shell_styles ) as $shell_prop ) :
			if ( language_switcher_dropdown_should_use_color_class_not_shell_style( (string) $shell_prop ) ) :
				unset( $shell_styles[ $shell_prop ] );
			endif;
		endforeach;

		return array(
			'shell' => array(
				'class'       => implode( ' ', $shell_classes ),
				'style'       => language_switcher_dropdown_join_inline_style( $shell_styles ),
				'other_attrs' => language_switcher_dropdown_filter_empty_attr_pairs( $parsed ),
			),
			'dropdown_ul' => array(
				'class' => implode( ' ', $ul_classes ),
				'style' => language_switcher_dropdown_join_inline_style( $ul_styles ),
			),
			'clickable' => array(
				'class' => implode( ' ', $clickable_classes ),
				'style' => language_switcher_dropdown_join_inline_style( $clickable_styles ),
			),
		);
	}
endif;
