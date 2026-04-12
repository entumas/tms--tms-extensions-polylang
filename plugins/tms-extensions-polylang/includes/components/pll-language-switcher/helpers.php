<?php
/**
 * Includes -> Components -> Pll Language Switcher -> Helpers
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Custom flag URL from pll-flags (uploads, then theme)
 */

if ( ! function_exists( 'language_switcher_resolve_custom_flag_url' ) ) :
	function language_switcher_resolve_custom_flag_url( string $slug ) : string {
		$slug = sanitize_file_name( $slug );
		if ( '' === $slug ) return '';

		$locations = array();

		$uploads = wp_upload_dir();
		if ( empty( $uploads['error'] ) ) :
			$locations[] = array(
				'dir' => trailingslashit( $uploads['basedir'] ) . 'pll-flags',
				'url' => trailingslashit( $uploads['baseurl'] ) . 'pll-flags',
			);
		endif;

		$stylesheet_dir = wp_normalize_path( get_stylesheet_directory() );
		$template_dir   = wp_normalize_path( get_template_directory() );
		$theme_dirs     = array( $stylesheet_dir );
		if ( $template_dir !== $stylesheet_dir ) :
			$theme_dirs[] = $template_dir;
		endif;

		foreach ( $theme_dirs as $theme_path ) :
			$uri_base = ( $theme_path === $stylesheet_dir )
				? get_stylesheet_directory_uri()
				: get_template_directory_uri();
			foreach ( array( 'images', 'assets' ) as $sub ) :
				$locations[] = array(
					'dir' => trailingslashit( $theme_path ) . $sub . '/pll-flags',
					'url' => trailingslashit( $uri_base ) . $sub . '/pll-flags',
				);
			endforeach;
		endforeach;

		foreach ( $locations as $loc ) :
			$svg = $loc['dir'] . '/' . $slug . '.svg';
			if ( file_exists( $svg ) ) :
				return $loc['url'] . '/' . $slug . '.svg';
			endif;
		endforeach;

		$raster_exts = array( 'png', 'jpg', 'jpeg', 'webp' );
		foreach ( $locations as $loc ) :
			foreach ( $raster_exts as $ext ) :
				$file = $loc['dir'] . '/' . $slug . '.' . $ext;
				if ( file_exists( $file ) ) :
					return $loc['url'] . '/' . $slug . '.' . $ext;
				endif;
			endforeach;
		endforeach;

		return '';
	}
endif;


/**
 * Escaped visible label for a language row (name or code)
 */

if ( ! function_exists( 'language_switcher_get_language_label' ) ) :
	function language_switcher_get_language_label( array $lang, string $label_mode ) : string {
		if ( 'name' === $label_mode && ! empty( $lang['name'] ) ) :
			return esc_html( (string) $lang['name'] );
		endif;
		return esc_html( (string) ( $lang['slug'] ?? '' ) );
	}
endif;


/**
 * Class string for a language `<li>`
 */

if ( ! function_exists( 'language_switcher_get_li_class' ) ) :
	function language_switcher_get_li_class( array $lang, int $index, int $total ) : string {
		$classes = array(
			'tepll-pll-language-switcher-item',
			'tepll-pll-language-switcher-item-' . esc_attr( (string) ( $lang['id'] ?? '' ) ),
			'tepll-pll-language-switcher-item-' . esc_attr( (string) ( $lang['slug'] ?? '' ) ),
		);
		if ( ! empty( $lang['current_lang'] ) ) :
			$classes[] = 'tepll-pll-language-switcher-item-current';
		endif;
		if ( $total === 1 ) :
			$classes[] = 'tepll-pll-language-switcher-item-first';
			$classes[] = 'tepll-pll-language-switcher-item-last';
		else :
			if ( 0 === $index ) $classes[] = 'tepll-pll-language-switcher-item-first';
			if ( $index === $total - 1 ) $classes[] = 'tepll-pll-language-switcher-item-last';
		endif;
		return implode( ' ', $classes );
	}
endif;


/**
 * Inner HTML for a switcher link or toggle
 */

if ( ! function_exists( 'language_switcher_get_inner_html' ) ) :
	function language_switcher_get_inner_html( array $lang, array $args, bool $for_button = false ) : string {
		$slug       = isset( $lang['slug'] )
			? (string) $lang['slug']
			: '';
		$label_mode = $args['label'];
		$show_text  = ! empty( $args['show_text'] );
		$parts      = array();

		if ( ! empty( $args['show_flags'] ) ) :
			$flag_url = language_switcher_resolve_custom_flag_url( $slug );
			if ( '' === $flag_url && ! empty( $lang['flag'] ) ) :
				$flag_url = (string) $lang['flag'];
			endif;
			if ( '' !== $flag_url ) :
				$alt = ! empty( $lang['name'] )
					? (string) $lang['name']
					: $slug;
				$img_attrs = array(
					'src'      => esc_url( $flag_url ),
					'alt'      => esc_attr( $alt ),
					'class'    => 'tepll-pll-language-switcher-flag',
					'decoding' => 'async',
					'loading'  => 'lazy',
				);
				if ( $for_button ) :
					$img_attrs['aria-hidden'] = 'true';
					$img_attrs['alt']         = '';
				endif;
				$img_html = '<img';
				foreach ( $img_attrs as $attr => $val ) :
					$img_html .= ' ' . $attr . '="' . $val . '"';
				endforeach;
				$img_html .= ' />';
				$parts[] = $img_html;
			endif;
		endif;

		$label_span_open = $for_button
			? '<span class="tepll-pll-language-switcher-label" aria-hidden="true">'
			: '<span class="tepll-pll-language-switcher-label">';

		if ( $show_text ) :
			$parts[] = $label_span_open . language_switcher_get_language_label( $lang, $label_mode ) . '</span>';
		endif;

		if ( empty( $parts ) ) :
			$parts[] = $label_span_open . esc_html( $slug ) . '</span>';
		endif;

		return implode( ' ', $parts );
	}
endif;


/**
 * Current language row from Polylang
 */

if ( ! function_exists( 'language_switcher_get_current_language_pll_row' ) ) :
	function language_switcher_get_current_language_pll_row( int $pll_hide_if_no_translation ) : ?array {
		$rows = pll_the_languages(
			array(
				'raw'                    => 1,
				'hide_if_empty'          => 0,
				'hide_current'           => 0,
				'hide_if_no_translation' => $pll_hide_if_no_translation,
			)
		);
		if ( empty( $rows ) || ! is_array( $rows ) ) return null;

		$slug = pll_current_language( 'slug' );
		if ( ! is_string( $slug ) || '' === $slug ) return null;

		foreach ( $rows as $lang ) :
			if ( ! is_array( $lang ) ) continue;
			if ( (string) ( $lang['slug'] ?? '' ) === $slug ) return $lang;
		endforeach;

		return null;
	}
endif;


/**
 * Link color from block attributes (`style.elements.link`), Gutenberg serialize format.
 */

if ( ! function_exists( 'language_switcher_extract_link_color_from_attrs' ) ) :
	function language_switcher_extract_link_color_from_attrs( array $attrs ) : string {
		if ( empty( $attrs['style'] ) || ! is_array( $attrs['style'] ) ) return '';
		$elements = $attrs['style']['elements'] ?? null;
		if ( ! is_array( $elements ) || empty( $elements['link'] ) || ! is_array( $elements['link'] ) ) return '';
		$color = $elements['link']['color'] ?? null;
		if ( is_string( $color ) && '' !== trim( $color ) ) return trim( $color );
		if ( is_array( $color ) && ! empty( $color['text'] ) && is_string( $color['text'] ) ) return trim( $color['text'] );

		return '';
	}
endif;

if ( ! function_exists( 'language_switcher_extract_link_hover_color_from_attrs' ) ) :
	function language_switcher_extract_link_hover_color_from_attrs( array $attrs ) : string {
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
endif;

if ( ! function_exists( 'language_switcher_sanitize_nav_css_color' ) ) :
	function language_switcher_sanitize_nav_css_color( string $value ) : string {
		$t = trim( wp_strip_all_tags( $value ) );
		if ( '' === $t ) return '';

		$hex = sanitize_hex_color( $t );
		if ( is_string( $hex ) && '' !== $hex ) return $hex;

		if ( preg_match( '/^rgba?\(\s*[\d.]+\s*,\s*[\d.]+\s*,\s*[\d.]+(\s*,\s*[\d.]+\s*)?\)$/i', $t ) ) return $t;
		if ( preg_match( '/^hsla?\(\s*[^)]+\)$/i', $t ) ) return $t;

		if ( preg_match( '/^var\(\s*(?:--[a-zA-Z0-9_-]+)(?:\s*,[^)]*)?\)$/i', $t ) ) return $t;

		return '';
	}
endif;

if ( ! function_exists( 'language_switcher_extract_link_color_preset_slug_for_css' ) ) :
	function language_switcher_extract_link_color_preset_slug_for_css( array $attrs ) : string {
		$raw = language_switcher_extract_link_color_from_attrs( $attrs );
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

		return ( '' !== $slug ) ? $slug : '';
	}
endif;

if ( ! function_exists( 'language_switcher_extract_wp_elements_class_token' ) ) :
	// Returns first `wp-elements-*` token from wrapper class string
	function language_switcher_extract_wp_elements_class_token( string $class ) : string {
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
endif;

if ( ! function_exists( 'language_switcher_apply_block_nav_link_color_as_text' ) ) :
	function language_switcher_apply_block_nav_link_color_as_text( array $attrs, array $block_nav ) : array {
		$raw = language_switcher_extract_link_color_from_attrs( $attrs );

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
			$safe_color = language_switcher_sanitize_nav_css_color( $raw );
			if ( '' !== $safe_color && function_exists( 'language_switcher_dropdown_parse_inline_style_declarations' ) ) :
				$decl = language_switcher_dropdown_parse_inline_style_declarations( (string) $block_nav['style'] );
				$decl['color'] = $safe_color;
				$block_nav['style'] = function_exists( 'language_switcher_dropdown_join_inline_style' )
					? language_switcher_dropdown_join_inline_style( $decl )
					: '';
			endif;
		endif;

		$tokens             = array_values( array_unique( array_filter( $tokens ) ) );
		$block_nav['class'] = implode( ' ', $tokens );

		return $block_nav;
	}
endif;

if ( ! function_exists( 'language_switcher_build_link_hover_style_tag' ) ) :
	function language_switcher_build_link_hover_style_tag( array $attrs, array $block_nav ) : string {
		$wp_token = language_switcher_extract_wp_elements_class_token(
			isset( $block_nav['class'] ) ? (string) $block_nav['class'] : ''
		);
		if ( '' === $wp_token ) return '';

		$raw_hover = language_switcher_extract_link_hover_color_from_attrs( $attrs );
		$color_slug = language_switcher_extract_link_color_preset_slug_for_css( array( 'style' => array( 'elements' => array( 'link' => array( 'color' => $raw_hover ) ) ) ) );
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
endif;


/**
 * KSES allowlist for switcher markup (shortcode / tepll_language_switcher_print_html).
 */
if ( ! function_exists( 'language_switcher_get_kses_allowed_html' ) ) :
	function language_switcher_get_kses_allowed_html() : array {
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
endif;
