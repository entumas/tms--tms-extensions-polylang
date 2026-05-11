<?php
/**
 * Includes -> Blocks -> Html sitemap -> Helpers -> Get
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


function tepll_html_sitemap_get_html( array $args = array() ) : string {
	$args = tepll_html_sitemap_normalize_args( $args );
	$lang = tepll_html_sitemap_get_current_lang_slug();

	$roots = tepll_html_sitemap_get_pages_by_parent( 0, $lang, $args['page_sort'] );
	$wrap  = ! empty( $args['wrap'] );

	if ( array() === $roots ) :
		return $wrap
			? '<div class="tepll-html-sitemap"></div>'
			: '<ul class="tepll-html-sitemap-root"></ul>';
	endif;

	$list = '';
	foreach ( $roots as $page ) :
		if ( ! $page instanceof WP_Post ) continue;
		$list .= tepll_html_sitemap_build_page_list_item( $page, 1, $args, $lang );
	endforeach;

	$inner = '<ul class="tepll-html-sitemap-root">' . $list . '</ul>';

	return $wrap
		? '<div class="tepll-html-sitemap">' . $inner . '</div>'
		: $inner;
}
