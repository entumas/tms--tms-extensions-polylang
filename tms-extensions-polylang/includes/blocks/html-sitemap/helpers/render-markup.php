<?php
/**
 * Includes -> Blocks -> Html sitemap -> Helpers -> Render markup
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Render one page `<li>` with CPT sections plus nested child page subtree when depth allows
 */
function tepll_html_sitemap_build_page_list_item( WP_Post $page, int $depth, array $args, string $lang ) : string {
	$html  = '<li><a href="' . esc_url( get_permalink( $page ) ) . '">' . esc_html( $page->post_title ) . '</a>';
	$extra = '';

	foreach ( $args['post_types'] as $pt => $cfg ) :
		$parent_id = tepll_html_sitemap_resolve_parent_page_id( $pt, $cfg, $lang );
		if ( $parent_id !== (int) $page->ID ) continue;
		$extra .= tepll_html_sitemap_render_post_type_section( $pt, $cfg, $args, $lang );
	endforeach;

	if ( $depth < (int) $args['max_depth'] ) :
		$children = tepll_html_sitemap_get_pages_by_parent( (int) $page->ID, $lang, $args['page_sort'] );
		if ( array() !== $children ) :
			$sub = '';
			foreach ( $children as $child ) :
				if ( ! $child instanceof WP_Post ) continue;
				$sub .= tepll_html_sitemap_build_page_list_item( $child, $depth + 1, $args, $lang );
			endforeach;
			if ( '' !== $sub ) :
				$extra .= '<ul class="tepll-html-sitemap-pages">' . $sub . '</ul>';
			endif;
		endif;
	endif;

	$html .= $extra . '</li>';
	return $html;
}


/**
 * Effective section depth limit using per-type override or global `max_depth`
 */
function tepll_html_sitemap_get_section_max_depth( array $cfg, array $args ) : int {
	return null !== ( $cfg['max_depth'] ?? null )
		? (int) $cfg['max_depth']
		: (int) $args['max_depth'];
}


/**
 * Render taxonomy-grouped term lists or flat post lists for one CPT branch under an anchor page
 */
function tepll_html_sitemap_render_post_type_section( string $post_type, array $cfg, array $args, string $lang ) : string {
	if ( ! $cfg['show_taxonomy'] && ! $cfg['show_posts'] ) return '';

	$section_max = tepll_html_sitemap_get_section_max_depth( $cfg, $args );
	if ( $section_max < 2 ) return '';

	$taxonomy = $cfg['taxonomy'];
	$show_tax = $cfg['show_taxonomy'] && '' !== $taxonomy && tepll_html_sitemap_taxonomy_is_valid_for_post_type( $taxonomy, $post_type );
	$show_pts = $cfg['show_posts'];

	if ( $show_tax && $show_pts && $section_max < 3 ) :
		$show_pts = false;
	endif;

	$inner = '';

	if ( $show_tax ) :
		$terms = tepll_html_sitemap_get_terms_for_taxonomy( $taxonomy, $args['hide_empty'], $lang );
		if ( array() !== $terms ) :
			foreach ( $terms as $term ) :
				if ( ! $term instanceof WP_Term ) continue;
				$tlink = get_term_link( $term );
				if ( is_wp_error( $tlink ) ) continue;
				$inner .= '<li><a href="' . esc_url( $tlink ) . '">' . esc_html( $term->name ) . '</a>';
				if ( $show_pts ) :
					$posts  = tepll_html_sitemap_get_posts_for_term( $post_type, $taxonomy, (int) $term->term_id, $lang );
					$inner .= tepll_html_sitemap_render_post_links( $posts );
				endif;
				$inner .= '</li>';
			endforeach;
		endif;
	elseif ( $show_pts ) :
		$posts  = tepll_html_sitemap_get_posts_for_post_type( $post_type, $lang );
		$inner .= tepll_html_sitemap_render_post_links( $posts );
	endif;

	if ( '' === $inner ) return '';

	if ( $show_tax ) :
		$ul_class = 'category' === $taxonomy
			? 'tepll-html-sitemap-categories tepll-html-sitemap-post-type-' . esc_attr( $post_type )
			: 'tepll-html-sitemap-terms tepll-html-sitemap-terms-' . esc_attr( $taxonomy ) . ' tepll-html-sitemap-post-type-' . esc_attr( $post_type );
		return '<ul class="' . esc_attr( $ul_class ) . '">' . $inner . '</ul>';
	endif;

	return $inner;
}


/**
 * Render `<ul>` of linked post titles from `WP_Post` objects
 */
function tepll_html_sitemap_render_post_links( array $posts ) : string {
	if ( array() === $posts ) return '';

	$html = '<ul class="tepll-html-sitemap-posts">';
	foreach ( $posts as $post ) :
		if ( ! $post instanceof WP_Post ) continue;
		$html .= '<li><a href="' . esc_url( get_permalink( $post ) ) . '">' . esc_html( get_the_title( $post ) ) . '</a></li>';
	endforeach;
	$html .= '</ul>';

	return $html;
}
