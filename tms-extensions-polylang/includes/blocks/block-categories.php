<?php
/**
 * Includes -> Blocks -> Block categories
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


add_filter( 'block_categories_all', function( $categories ) {
	if ( ! is_array( $categories ) ) return $categories;

	$new_cat = array(
		'slug'  => 'tms-blocks',
		'title' => __( 'TMS Blocks', 'tms-extensions-polylang' ),
		'icon'  => null,
	);
	foreach ( $categories as $cat ) :
		if ( isset( $cat['slug'] ) && $cat['slug'] === $new_cat['slug'] ) return $categories;
	endforeach;

	$new_categories = array();
	$inserted       = false;
	foreach ( $categories as $cat ) :
		$new_categories[] = $cat;
		if ( isset( $cat['slug'] ) && $cat['slug'] === 'design' && ! $inserted ) :
			$new_categories[] = $new_cat;
			$inserted         = true;
		endif;
	endforeach;
	if ( ! $inserted ) $new_categories[] = $new_cat;

	return $new_categories;
}, 15, 1 );
