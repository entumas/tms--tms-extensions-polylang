<?php
/**
 * Includes -> Blocks -> Html sitemap -> Helpers -> Polylang
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Resolve Polylang language slug for frontend or editor preview context
 */
function tepll_html_sitemap_get_current_lang_slug() : string {
	$lang = pll_current_language();
	if ( is_string( $lang ) && $lang !== '' ) return $lang;

	// Block SSR runs via REST without a resolved PLL frontend language; fall back to default.
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST && function_exists( 'pll_default_language' ) ) :
		$tepll_html_sitemap_fallback_lang = pll_default_language();
		if ( is_string( $tepll_html_sitemap_fallback_lang ) && '' !== $tepll_html_sitemap_fallback_lang ) :
			return $tepll_html_sitemap_fallback_lang;
		endif;
	endif;

	$post_id = 0;
	if ( isset( $GLOBALS['post'] ) && $GLOBALS['post'] instanceof WP_Post ) :
		$post_id = (int) $GLOBALS['post']->ID;
	else :
		$post_id = (int) get_queried_object_id();
	endif;
	if ( $post_id > 0 && function_exists( 'pll_get_post_language' ) ) :
		$post_lang = pll_get_post_language( $post_id, 'slug' );
		return is_string( $post_lang )
			? $post_lang
			: '';
	endif;

	return '';
}


/**
 * Map a page ID to its translation in the active language when Polylang applies
 */
function tepll_html_sitemap_resolve_post_id( int $post_id, string $lang ) : int {
	if ( $post_id <= 0 ) return 0;

	if ( '' !== $lang ) :
		$translated = pll_get_post( $post_id, $lang );
		return is_int( $translated ) && $translated > 0
			? $translated
			: 0;
	endif;

	return $post_id;
}


/**
 * Resolve anchor page ID for a branch using config plus Posts page fallback for `post`
 */
function tepll_html_sitemap_resolve_parent_page_id( string $post_type, array $cfg, string $lang ) : int {
	if ( 'post' === $post_type ) :
		$raw = (int) ( $cfg['parent_page_id'] ?? 0 );
		if ( $raw > 0 ) return tepll_html_sitemap_resolve_post_id( $raw, $lang );
		$blog = (int) get_option( 'page_for_posts' );
		if ( $blog <= 0 ) return 0;
		return tepll_html_sitemap_resolve_post_id( $blog, $lang );
	endif;
	$parent = (int) ( $cfg['parent_page_id'] ?? 0 );
	if ( $parent <= 0 ) return 0;
	return tepll_html_sitemap_resolve_post_id( $parent, $lang );
}
