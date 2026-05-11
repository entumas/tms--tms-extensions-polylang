<?php
/**
 * Includes -> Blocks -> Pll menu by language -> Helpers -> Get
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


function tepll_menu_by_language_get_html( WP_Block $block ) : string {
	$lang = (string) pll_current_language( 'slug' );
	if ( '' === $lang ) return '';

	foreach ( $block->inner_blocks as $inner ) :
		if ( ! $inner instanceof WP_Block || 'tepll/pll-menu-by-language-slot' !== $inner->name ) continue;

		$slot_lang = isset( $inner->attributes['lang'] )
			? sanitize_key( (string) $inner->attributes['lang'] )
			: '';
		if ( $slot_lang !== $lang ) continue;

		foreach ( $inner->inner_blocks as $nav_block ) :
			if ( ! $nav_block instanceof WP_Block || 'core/navigation' !== $nav_block->name ) continue;

			$parsed = $nav_block->parsed_block;
			if ( ! is_array( $parsed ) ) return '';

			$ref = isset( $parsed['attrs']['ref'] )
				? (int) $parsed['attrs']['ref']
				: 0;
			if ( $ref < 1 ) return '';

			$post = get_post( $ref );
			if ( ! $post instanceof WP_Post || 'wp_navigation' !== $post->post_type || 'publish' !== $post->post_status ) return '';

			return (string) render_block( $parsed );
		endforeach;
	endforeach;

	return '';
}
