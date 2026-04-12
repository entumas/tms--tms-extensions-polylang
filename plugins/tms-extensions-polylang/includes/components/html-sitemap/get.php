<?php
/**
 * Includes -> Components -> Sitemap -> Get
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Get
 */

 if ( ! function_exists( 'tepll_html_sitemap_get_html' ) ) :
	function tepll_html_sitemap_get_html( array $args = array() ) : string {
		$args = html_sitemap_normalize_args( $args );
		$lang = html_sitemap_current_lang();

		$roots = html_sitemap_get_pages_at( 0, $lang, $args['page_sort'] );
		$wrap  = ! empty( $args['wrap'] );

		if ( array() === $roots ) :
			return $wrap
				? '<div class="tepll-html-sitemap"></div>'
				: '<ul class="tepll-html-sitemap-root"></ul>';
		endif;

		$list = '';
		foreach ( $roots as $page ) :
			if ( ! $page instanceof WP_Post ) continue;
			$list .= html_sitemap_build_page_li( $page, 1, $args, $lang );
		endforeach;

		$inner = '<ul class="tepll-html-sitemap-root">' . $list . '</ul>';

		return $wrap
			? '<div class="tepll-html-sitemap">' . $inner . '</div>'
			: $inner;
	}
endif;


/**
 * Print
 */

 if ( ! function_exists( 'tepll_html_sitemap_print_html' ) ) :
	function tepll_html_sitemap_print_html( array $args = array() ) : void {
		echo wp_kses_post( tepll_html_sitemap_get_html( $args ) );
	}
endif;
