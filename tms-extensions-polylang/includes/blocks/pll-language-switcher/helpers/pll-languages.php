<?php
/**
 * Includes -> Blocks -> Pll language switcher -> Helpers -> Pll languages
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Whether block preview markup should mirror admin/editor behaviour (includes REST block renderer).
 *
 * Server-side block previews call render callbacks via REST without loading wp-admin, so is_admin() is false.
 */
function tepll_language_switcher_context_is_editor_preview() : bool {
	if ( is_admin() ) :
		return true;
	endif;
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) :
		return true;
	endif;
	return false;
}


/**
 * Minimal Polylang-shaped rows when pll_the_languages() is empty but the editor needs a preview (admin or REST).
 *
 * Two rows are required so dropdown mode has both a toggle language and menu items.
 */
function tepll_language_switcher_build_placeholder_language_rows() : array {
	$locale        = determine_locale();
	$slug_primary  = strtolower( (string) substr( (string) $locale, 0, 2 ) );
	$slug_primary  = '' !== $slug_primary
		? $slug_primary
		: 'en';
	$slug_secondary = 'en' === $slug_primary
		? 'es'
		: 'en';

	return array(
		array(
			'id'           => 0,
			'slug'         => $slug_primary,
			'locale'       => '' !== (string) $locale
				? (string) $locale
				: 'en_US',
			'name'         => __( 'Preview: current language', 'tms-extensions-polylang' ),
			'url'          => '#',
			'current_lang' => true,
		),
		array(
			'id'           => 1,
			'slug'         => $slug_secondary,
			'locale'       => $slug_secondary . '_' . strtoupper( $slug_secondary ),
			'name'         => __( 'Preview: alternate language', 'tms-extensions-polylang' ),
			'url'          => '#',
			'current_lang' => false,
		),
	);
}


/**
 * Escaped visible label string for a Polylang language row (name or slug code)
 */
function tepll_language_switcher_get_language_label( array $lang, string $label_mode ) : string {
	if ( 'name' === $label_mode && ! empty( $lang['name'] ) ) :
		return esc_html( (string) $lang['name'] );
	endif;
	return esc_html( (string) ( $lang['slug'] ?? '' ) );
}


/**
 * Space-separated class string for one switcher `<li>` including current/first/last modifiers
 */
function tepll_language_switcher_get_li_class( array $lang, int $index, int $total ) : string {
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


/**
 * Inner markup for a language anchor or dropdown toggle (flag img plus optional label span)
 */
function tepll_language_switcher_get_inner_html( array $lang, array $args, bool $for_button = false ) : string {
	$slug       = isset( $lang['slug'] )
		? (string) $lang['slug']
		: '';
	$label_mode = $args['label'];
	$show_text  = ! empty( $args['show_text'] );
	$parts      = array();

	if ( ! empty( $args['show_flags'] ) ) :
		$flag_url = tepll_language_switcher_resolve_custom_flag_url( $slug );
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
		$parts[] = $label_span_open . tepll_language_switcher_get_language_label( $lang, $label_mode ) . '</span>';
	endif;

	if ( empty( $parts ) ) :
		$parts[] = $label_span_open . esc_html( $slug ) . '</span>';
	endif;

	return implode( ' ', $parts );
}


/**
 * Raw Polylang row array for the current frontend language slug
 */
function tepll_language_switcher_get_current_language_pll_row( int $pll_hide_if_no_translation ) : ?array {
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
