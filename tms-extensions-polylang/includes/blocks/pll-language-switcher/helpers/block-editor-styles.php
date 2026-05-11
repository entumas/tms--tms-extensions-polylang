<?php
/**
 * Includes -> Blocks -> Pll language switcher -> Helpers -> Block editor styles
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Link color string from saved block attributes under `style.elements.link`
 */
function tepll_language_switcher_extract_link_color_from_attrs( array $attrs ) : string {
	if ( empty( $attrs['style'] ) || ! is_array( $attrs['style'] ) ) return '';
	$elements = $attrs['style']['elements'] ?? null;
	if ( ! is_array( $elements ) || empty( $elements['link'] ) || ! is_array( $elements['link'] ) ) return '';
	$color = $elements['link']['color'] ?? null;
	if ( is_string( $color ) && '' !== trim( $color ) ) return trim( $color );
	if ( is_array( $color ) && ! empty( $color['text'] ) && is_string( $color['text'] ) ) return trim( $color['text'] );

	return '';
}


/**
 * Link hover color string from nested block style payloads (supports multiple Gutenberg shapes)
 */
function tepll_language_switcher_extract_link_hover_color_from_attrs( array $attrs ) : string {
	if ( empty( $attrs['style'] ) || ! is_array( $attrs['style'] ) ) return '';

	$elements = $attrs['style']['elements'] ?? null;
	if ( ! is_array( $elements ) ) return '';

	// Helper: extract color string from an arbitrary node.
	$extract_from_node = static function( $node ) : string {
		if ( ! is_array( $node ) ) return '';

		if ( isset( $node['color'] ) ) :
			$color = $node['color'];
			if ( is_string( $color ) && '' !== trim( $color ) ) return trim( $color );
			if ( is_array( $color ) && isset( $color['text'] ) && is_string( $color['text'] ) ) :
				return trim( (string) $color['text'] );
			endif;
		endif;

		// Sometimes Gutenberg stores direct "text".
		if ( isset( $node['text'] ) && is_string( $node['text'] ) ) :
			return trim( (string) $node['text'] );
		endif;

		return '';
	};

	// Most common: style.elements.link[':hover']...
	$link = $elements['link'] ?? null;
	if ( is_array( $link ) ) :
		// Sometimes it can be nested under `color[':hover']`.
		if ( isset( $link['color'] ) && is_array( $link['color'] ) ) :
			foreach ( array( ':hover', 'hover' ) as $hover_key ) :
				if ( isset( $link['color'][ $hover_key ] ) ) :
					$found = $extract_from_node( $link['color'][ $hover_key ] );
					if ( '' !== $found ) return $found;
				endif;
			endforeach;
		endif;

		foreach ( array( ':hover', 'hover' ) as $hover_key ) :
			if ( isset( $link[ $hover_key ] ) ) :
				$found = $extract_from_node( $link[ $hover_key ] );
				if ( '' !== $found ) return $found;
			endif;
		endforeach;
	endif;

	// Alternative: style.elements['link:hover']...
	foreach ( array( 'link:hover', 'link:hover:' ) as $key ) :
		if ( isset( $elements[ $key ] ) ) :
			$found = $extract_from_node( $elements[ $key ] );
			if ( '' !== $found ) return $found;
		endif;
	endforeach;

	// Fallback: ordered DFS search under elements.link for keys containing "hover".
	if ( is_array( $link ) ) :
		$dfs = null;
		$dfs = static function( $node ) use ( &$dfs, $extract_from_node ) : string {
			if ( ! is_array( $node ) ) return '';
			foreach ( $node as $k => $v ) :
				if ( is_string( $k ) && false !== stripos( (string) $k, 'hover' ) ) :
					$found = $extract_from_node( $v );
					if ( '' !== $found ) return $found;
				endif;
				if ( is_array( $v ) ) :
					$found = $dfs( $v );
					if ( '' !== $found ) return $found;
				endif;
			endforeach;
			return '';
		};

		$found = $dfs( $link );
		if ( '' !== $found ) return $found;
	endif;

	return '';
}


/**
 * Sanitize a CSS color token for safe inline `color` on nav (hex, rgb(a), hsl(a), `var(--token)`)
 */
function tepll_language_switcher_sanitize_nav_css_color( string $value ) : string {
	$t = trim( wp_strip_all_tags( $value ) );
	if ( '' === $t ) return '';

	$hex = sanitize_hex_color( $t );
	if ( is_string( $hex ) && '' !== $hex ) return $hex;

	if ( preg_match( '/^rgba?\(\s*[\d.]+\s*,\s*[\d.]+\s*,\s*[\d.]+(\s*,\s*[\d.]+\s*)?\)$/i', $t ) ) return $t;
	if ( preg_match( '/^hsla?\(\s*[^)]+\)$/i', $t ) ) return $t;

	if ( preg_match( '/^var\(\s*(?:--[a-zA-Z0-9_-]+)(?:\s*,[^)]*)?\)$/i', $t ) ) return $t;

	return '';
}


/**
 * Map serialized preset-style link color value to a `--wp--preset--color--*` slug fragment
 */
function tepll_language_switcher_extract_link_color_preset_slug_for_css( array $attrs ) : string {
	$raw = tepll_language_switcher_extract_link_color_from_attrs( $attrs );
	if ( '' === $raw ) return '';

	$slug = '';
	if ( preg_match( '/^var:preset\|color\|(.+)$/i', $raw, $m ) ) :
		$slug = str_replace( '/', '-', strtolower( trim( (string) $m[1] ) ) );
	elseif ( preg_match( '/^var:wp\|preset\|color\|(.+)$/i', $raw, $m ) ) :
		$slug = str_replace( '/', '-', strtolower( trim( (string) $m[1] ) ) );
	elseif ( preg_match( '/^var\(\s*--wp--preset--color--([a-z0-9\-]+)\s*\)$/i', $raw, $m ) ) :
		$slug = strtolower( (string) $m[1] );
	endif;

	$slug = preg_replace( '/[^a-z0-9\-]/', '', (string) $slug );

	return ( '' !== $slug )
		? $slug
		: '';
}


/**
 * First `wp-elements-*` class token from a wrapper class string (for scoped hover CSS)
 */
function tepll_language_switcher_extract_wp_elements_class_token( string $class ) : string {
	$tokens = preg_split( '/\s+/', trim( $class ), -1, PREG_SPLIT_NO_EMPTY );
	if ( empty( $tokens ) || ! is_array( $tokens ) ) return '';

	foreach ( $tokens as $t ) :
		$t = trim( (string) $t );
		if ( '' === $t ) continue;
		if ( 0 !== strpos( $t, 'wp-elements-' ) ) continue;

		$clean = preg_replace( '/[^a-zA-Z0-9\-_]/', '', $t );
		return $clean;
	endforeach;

	return '';
}


/**
 * Merge block link color into nav shell classes or inline style using presets when possible
 */
function tepll_language_switcher_apply_block_nav_link_color_as_text( array $attrs, array $block_nav ) : array {
	$raw = tepll_language_switcher_extract_link_color_from_attrs( $attrs );

	$block_nav = wp_parse_args(
		$block_nav,
		array(
			'class'       => '',
			'style'       => '',
			'other_attrs' => array(),
		)
	);
	$block_nav['other_attrs'] = isset( $block_nav['other_attrs'] ) && is_array( $block_nav['other_attrs'] )
		? $block_nav['other_attrs']
		: array();

	$tokens = preg_split( '/\s+/', trim( (string) $block_nav['class'] ), -1, PREG_SPLIT_NO_EMPTY );
	if ( ! is_array( $tokens ) ) :
		$tokens = array();
	endif;

	if ( '' === $raw ) :
		$block_nav['class'] = implode( ' ', $tokens );
		return $block_nav;
	endif;

	$tokens = array_values(
		array_diff( $tokens, array( 'has-link-color' ) )
	);

	$slug_for_classes = '';

	if ( preg_match( '/^var:preset\|color\|(.+)$/i', $raw, $m ) ) :
		$slug_for_classes = str_replace( '/', '-', strtolower( trim( $m[1] ) ) );
	elseif ( preg_match( '/^var:wp\|preset\|color\|(.+)$/i', $raw, $m ) ) :
		$slug_for_classes = str_replace( '/', '-', strtolower( trim( $m[1] ) ) );
	elseif ( preg_match( '/^var\(\s*--wp--preset--color--([a-z0-9\-]+)\s*\)$/i', $raw, $m ) ) :
		$slug_for_classes = strtolower( $m[1] );
	endif;

	$slug_for_classes = preg_replace( '/[^a-z0-9\-]/', '', (string) $slug_for_classes );

	if ( '' !== $slug_for_classes ) :
		$tokens[] = 'has-text-color';
		$tokens[] = 'has-' . $slug_for_classes . '-color';
	else :
		$safe_color = tepll_language_switcher_sanitize_nav_css_color( $raw );
		if ( '' !== $safe_color ) :
			$decl = tepll_language_switcher_dropdown_parse_inline_style_declarations( (string) $block_nav['style'] );
			$decl['color'] = $safe_color;
			$block_nav['style'] = tepll_language_switcher_dropdown_join_inline_style( $decl );
		endif;
	endif;

	$tokens             = array_values( array_unique( array_filter( $tokens ) ) );
	$block_nav['class'] = implode( ' ', $tokens );

	return $block_nav;
}


/**
 * Inline `<style>` for button hover color when hover uses a theme preset and wrapper has `wp-elements-*`
 */
function tepll_language_switcher_build_link_hover_style_tag( array $attrs, array $block_nav ) : string {
	$wp_token = tepll_language_switcher_extract_wp_elements_class_token(
		isset( $block_nav['class'] ) ? (string) $block_nav['class'] : ''
	);
	if ( '' === $wp_token ) return '';

	$raw_hover = tepll_language_switcher_extract_link_hover_color_from_attrs( $attrs );
	$color_slug = tepll_language_switcher_extract_link_color_preset_slug_for_css( array( 'style' => array( 'elements' => array( 'link' => array( 'color' => $raw_hover ) ) ) ) );
	if ( '' === $color_slug ) :
		$color_slug = '';
		$raw = (string) $raw_hover;
		if ( preg_match( '/^var:preset\|color\|(.+)$/i', $raw, $m ) ) :
			$color_slug = str_replace( '/', '-', strtolower( trim( $m[1] ) ) );
		elseif ( preg_match( '/^var:wp\|preset\|color\|(.+)$/i', $raw, $m ) ) :
			$color_slug = str_replace( '/', '-', strtolower( trim( $m[1] ) ) );
		elseif ( preg_match( '/^var\(\s*--wp--preset--color--([a-z0-9\-]+)\s*\)$/i', $raw, $m ) ) :
			$color_slug = strtolower( $m[1] );
		endif;
		$color_slug = preg_replace( '/[^a-z0-9\-]/', '', (string) $color_slug );
	endif;

	if ( '' === $color_slug ) return '';

	$css = sprintf(
		'.%1$s button:hover { color: var(--wp--preset--color--%2$s); }',
		$wp_token,
		$color_slug
	);

	$css = trim( (string) $css );
	if ( '' === $css ) return '';

	return '<style>' . $css . '</style>';
}
