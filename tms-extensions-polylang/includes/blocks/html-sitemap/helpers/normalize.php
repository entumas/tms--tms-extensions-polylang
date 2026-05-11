<?php
/**
 * Includes -> Blocks -> Html sitemap -> Helpers -> Normalize
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Coerce mixed block-like values to boolean using common string truthy tokens
 */
function tepll_html_sitemap_parse_bool_from_value( $value, bool $default = false ) : bool {
	if ( is_bool( $value ) ) return $value;
	if ( is_int( $value ) || is_float( $value ) ) return ( (int) $value ) !== 0;
	if ( null === $value ) return $default;
	if ( is_string( $value ) ) :
		$v = strtolower( trim( $value ) );
		if ( '' === $v ) return $default;
		if ( in_array( $v, array( '1', 'true', 'yes', 'on' ), true ) ) return true;
		if ( in_array( $v, array( '0', 'false', 'no', 'off' ), true ) ) return false;
	endif;
	return ! empty( $value );
}


/**
 * Merge defaults and sanitize post-type configs before markup reads `$args`
 */
function tepll_html_sitemap_normalize_args( array $args ) : array {
	$explicit_empty_post_types = array_key_exists( 'post_types', $args )
		&& is_array( $args['post_types'] )
		&& array() === $args['post_types'];

	$defaults = array(
		'hide_empty' => true,
		'max_depth'  => 3,
		'page_sort'  => 'menu_order',
		'wrap'       => true,
		'post_types' => array(
			'post' => array(
				'max_depth'      => null,
				'show_taxonomy'  => true,
				'show_posts'     => true,
				'taxonomy'       => 'category',
				'parent_page_id' => 0,
			),
		),
	);
	$out = wp_parse_args( $args, $defaults );

	$out['hide_empty'] = tepll_html_sitemap_parse_bool_from_value( $out['hide_empty'], true );
	$out['max_depth']  = max( 1, (int) $out['max_depth'] );
	$out['page_sort']  = ( 'alphabetical' === $out['page_sort'] )
		? 'alphabetical'
		: 'menu_order';

	if ( ! is_array( $out['post_types'] ) ) :
		$out['post_types'] = $defaults['post_types'];
	endif;

	$clean = array();
	foreach ( $out['post_types'] as $post_type => $cfg ) :
		if ( ! is_string( $post_type ) || ! post_type_exists( $post_type ) ) continue;
		if ( ! is_array( $cfg ) ) continue;

		$clean[ $post_type ] = array(
			'max_depth'      => isset( $cfg['max_depth'] ) && null !== $cfg['max_depth']
				? max( 1, (int) $cfg['max_depth'] )
				: null,
			'show_taxonomy'  => tepll_html_sitemap_parse_bool_from_value( $cfg['show_taxonomy'], true ),
			'show_posts'     => tepll_html_sitemap_parse_bool_from_value( $cfg['show_posts'], true ),
			'taxonomy'       => isset( $cfg['taxonomy'] )
				? sanitize_key( (string) $cfg['taxonomy'] )
				: 'category',
			'parent_page_id' => isset( $cfg['parent_page_id'] )
				? (int) $cfg['parent_page_id']
				: 0,
		);

		if ( ! tepll_html_sitemap_taxonomy_is_valid_for_post_type( $clean[ $post_type ]['taxonomy'], $post_type ) ) :
			$clean[ $post_type ]['taxonomy'] = 'post' === $post_type
				? 'category'
				: '';
			if ( '' === $clean[ $post_type ]['taxonomy'] || ! tepll_html_sitemap_taxonomy_is_valid_for_post_type( $clean[ $post_type ]['taxonomy'], $post_type ) ) :
				$clean[ $post_type ]['show_taxonomy'] = false;
			endif;
		endif;
	endforeach;

	if ( array() === $clean ) :
		$out['post_types'] = $explicit_empty_post_types
			? array()
			: $defaults['post_types'];
	else :
		$out['post_types'] = $clean;
	endif;

	return $out;
}
