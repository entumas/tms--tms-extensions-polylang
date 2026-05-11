<?php
/**
 * Includes -> Blocks -> Html sitemap -> Helpers -> Posts
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Query posts assigned to one taxonomy term with optional lang scope and transient cache
 */
function tepll_html_sitemap_get_posts_for_term( string $post_type, string $taxonomy, int $term_id, string $lang ) : array {
	$cache_key = 'tepll_html_sitemap_get_posts_for_term_' . md5( wp_json_encode( array( $post_type, $taxonomy, $term_id, $lang ) ) );
	$cached    = get_transient( $cache_key );
	if ( is_array( $cached ) ) return $cached;

	$q = array(
		'post_type'              => $post_type,
		'posts_per_page'         => -1,
		'post_status'            => 'publish',
		'orderby'                => 'date',
		'order'                  => 'DESC',
		'no_found_rows'          => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
		'fields'                 => 'ids',
		'tax_query'              => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Limited usage + cached via transient to avoid repeated expensive queries.
			array(
				'taxonomy' => $taxonomy,
				'field'    => 'term_id',
				'terms'    => $term_id,
			),
		),
	);
	if ( '' !== $lang ) :
		$q['lang'] = $lang;
	endif;
	$post_ids = get_posts( $q );
	$post_ids = is_array( $post_ids )
		? $post_ids
		: array();
	$posts = array();
	foreach ( $post_ids as $post_id ) :
		$post = get_post( (int) $post_id );
		if ( $post instanceof WP_Post ) :
			$posts[] = $post;
		endif;
	endforeach;

	set_transient( $cache_key, $posts, HOUR_IN_SECONDS );

	return $posts;
}


/**
 * Query all published posts for a post type ordered by date with optional lang scope
 */
function tepll_html_sitemap_get_posts_for_post_type( string $post_type, string $lang ) : array {
	$q = array(
		'post_type'              => $post_type,
		'posts_per_page'         => -1,
		'post_status'            => 'publish',
		'orderby'                => 'date',
		'order'                  => 'DESC',
		'no_found_rows'          => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
	);
	if ( '' !== $lang ) :
		$q['lang'] = $lang;
	endif;
	$posts = get_posts( $q );

	return is_array( $posts )
		? $posts
		: array();
}
