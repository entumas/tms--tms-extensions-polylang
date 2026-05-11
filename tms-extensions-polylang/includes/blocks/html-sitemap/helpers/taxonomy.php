<?php
/**
 * Includes -> Blocks -> Html sitemap -> Helpers -> Taxonomy
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Whether the taxonomy slug is registered for the given post type
 */
function tepll_html_sitemap_taxonomy_is_valid_for_post_type( string $taxonomy, string $post_type ) : bool {
	if ( '' === $taxonomy ) return false;

	$tax = get_taxonomy( $taxonomy );
	if ( ! $tax || ! is_array( $tax->object_type ) ) return false;

	return in_array( $post_type, $tax->object_type, true );
}
