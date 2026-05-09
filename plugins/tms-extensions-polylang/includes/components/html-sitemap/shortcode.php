<?php
/**
 * Includes -> Components -> Sitemap -> Shortcode
 *
 * Blog (post type post): blog, blog_max_depth, blog_show_taxonomy, blog_show_posts,
 *   blog_taxonomy, blog_parent_page_id (0 = page_for_posts).
 * CPTs: post_types — JSON keyed by post type slug; "post" is ignored (use blog_*).
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


add_shortcode( 'tepll-html-sitemap', function( $atts = array() ) : string {
	if ( ! function_exists( 'tepll_html_sitemap_get_html' ) || ! function_exists( 'tepll_html_sitemap_args_from_shortcode_attributes' ) ) return '';

	$atts = shortcode_atts(
		array(
			'hide_empty'           => '1',
			'max_depth'            => '3',
			'page_sort'            => 'menu_order',
			'blog'                 => '1',
			'blog_max_depth'       => '',
			'blog_show_taxonomy'   => '1',
			'blog_show_posts'      => '1',
			'blog_taxonomy'        => 'category',
			'blog_parent_page_id'  => '0',
			'post_types'           => '',
		),
		(array) $atts,
		'tepll-html-sitemap'
	);

	return tepll_html_sitemap_get_html( tepll_html_sitemap_args_from_shortcode_attributes( $atts ) );
} );
