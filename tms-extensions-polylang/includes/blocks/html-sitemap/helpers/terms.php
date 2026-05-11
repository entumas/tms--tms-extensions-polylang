<?php
/**
 * Includes -> Blocks -> Html sitemap -> Helpers -> Terms
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * List taxonomy terms by name with hide-empty flag and optional Polylang lang
 */
function tepll_html_sitemap_get_terms_for_taxonomy( string $taxonomy, bool $hide_empty, string $lang ) : array {
	$args = array(
		'taxonomy'   => $taxonomy,
		'hide_empty' => $hide_empty,
		'orderby'    => 'name',
		'order'      => 'ASC',
	);
	if ( '' !== $lang ) :
		$args['lang'] = $lang;
	endif;

	$terms = get_terms( $args );
	if ( is_wp_error( $terms ) || ! is_array( $terms ) ) return array();

	return $terms;
}
