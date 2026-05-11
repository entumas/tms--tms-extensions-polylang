<?php
/**
 * Includes -> Blocks -> Html sitemap -> Helpers -> Pages
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Map page sort mode to `get_pages` sort_column and sort_order keys
 */
function tepll_html_sitemap_get_page_query_sort( string $page_sort ) : array {
	if ( 'alphabetical' === $page_sort ) :
		return array(
			'sort_column' => 'post_title',
			'sort_order'  => 'ASC',
		);
	endif;

	return array(
		'sort_column' => 'menu_order,post_title',
		'sort_order'  => 'ASC',
	);
}


/**
 * Fetch published child pages for one parent optionally filtered by Polylang lang
 */
function tepll_html_sitemap_get_pages_by_parent( int $parent_id, string $lang, string $page_sort ) : array {
	$sort  = tepll_html_sitemap_get_page_query_sort( $page_sort );
	$query = array(
		'parent'      => $parent_id,
		'post_status' => 'publish',
		'sort_column' => $sort['sort_column'],
		'sort_order'  => $sort['sort_order'],
	);
	if ( '' !== $lang ) :
		$query['lang'] = $lang;
	endif;
	$pages = get_pages( $query );

	return is_array( $pages )
		? $pages
		: array();
}
