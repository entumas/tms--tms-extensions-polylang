<?php
/**
 * Includes -> Blocks -> Html sitemap -> Helpers -> Block editor
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Build internal sitemap `$args` from saved block attributes before normalization
 */
function tepll_html_sitemap_get_args_from_block_attributes( array $attrs ) : array {
	$cpt_only = array();
	$cpt_rows = isset( $attrs['cpt_configs'] ) && is_array( $attrs['cpt_configs'] )
		? $attrs['cpt_configs']
		: array();

	foreach ( $cpt_rows as $row ) :
		if ( ! is_array( $row ) || empty( $row['slug'] ) ) continue;
		$slug = sanitize_key( (string) $row['slug'] );
		if ( '' === $slug || 'post' === $slug ) continue;

		$cfg = array(
			'show_taxonomy'  => tepll_html_sitemap_parse_bool_from_value( $row['show_taxonomy'] ?? true, true ),
			'show_posts'     => tepll_html_sitemap_parse_bool_from_value( $row['show_posts'] ?? true, true ),
			'taxonomy'       => isset( $row['taxonomy'] )
				? sanitize_key( (string) $row['taxonomy'] )
				: '',
			'parent_page_id' => isset( $row['parent_page_id'] )
				? (int) $row['parent_page_id']
				: 0,
		);
		if ( isset( $row['max_depth'] ) && '' !== $row['max_depth'] && null !== $row['max_depth'] ) :
			$cfg['max_depth'] = max( 1, (int) $row['max_depth'] );
		endif;
		$cpt_only[ $slug ] = $cfg;
	endforeach;

	$post_types = array();

	$blog_on = tepll_html_sitemap_parse_bool_from_value( $attrs['blog'] ?? true, true );

	if ( $blog_on ) :
		$blog_cfg = array(
			'show_taxonomy'  => tepll_html_sitemap_parse_bool_from_value( $attrs['blog_show_taxonomy'] ?? true, true ),
			'show_posts'     => tepll_html_sitemap_parse_bool_from_value( $attrs['blog_show_posts'] ?? true, true ),
			'taxonomy'       => isset( $attrs['blog_taxonomy'] )
				? sanitize_key( (string) $attrs['blog_taxonomy'] )
				: 'category',
			'parent_page_id' => isset( $attrs['blog_parent_page_id'] )
				? (int) $attrs['blog_parent_page_id']
				: 0,
		);
		$bd = isset( $attrs['blog_max_depth'] )
			? trim( (string) $attrs['blog_max_depth'] )
			: '';
		$blog_cfg['max_depth'] = '' !== $bd
			? max( 1, (int) $bd )
			: null;
		$post_types['post'] = $blog_cfg;
	endif;

	foreach ( $cpt_only as $slug => $cfg ) :
		$post_types[ $slug ] = $cfg;
	endforeach;

	$page_sort = isset( $attrs['page_sort'] )
		? sanitize_key( (string) $attrs['page_sort'] )
		: 'menu_order';
	$page_sort = in_array( $page_sort, array( 'menu_order', 'alphabetical' ), true )
		? $page_sort
		: 'menu_order';

	$args = array(
		'hide_empty' => tepll_html_sitemap_parse_bool_from_value( $attrs['hide_empty'] ?? true, true ),
		'max_depth'  => max( 1, (int) ( $attrs['max_depth'] ?? 3 ) ),
		'page_sort'  => $page_sort,
		'wrap'       => false,
	);

	if ( array() !== $post_types || ! $blog_on ) :
		$args['post_types'] = $post_types;
	endif;

	return $args;
}


/**
 * Public CPT labels and taxonomy metadata for the inspector inline script
 */
function tepll_html_sitemap_get_editor_cpt_definitions() : array {
	$objects = get_post_types(
		array(
			'public'   => true,
			'_builtin' => false,
		),
		'objects'
	);
	$list = array();

	foreach ( $objects as $slug => $obj ) :
		if ( ! $obj instanceof WP_Post_Type || 'attachment' === $slug ) continue;

		$tax_entries = array();
		foreach ( get_object_taxonomies( $slug, 'objects' ) as $tax_slug => $tax_obj ) :
			if ( ! $tax_obj instanceof WP_Taxonomy || ! $tax_obj->public ) continue;
			$tax_entries[] = array(
				'slug'  => $tax_slug,
				'label' => $tax_obj->label
					? $tax_obj->label
					: $tax_slug,
			);
		endforeach;

		$list[] = array(
			'slug'       => $slug,
			'label'      => $obj->labels->singular_name ?? $obj->label ?? $slug,
			'name'       => $obj->label ?? $slug,
			'taxonomies' => $tax_entries,
		);
	endforeach;

	return $list;
}


/**
 * Public taxonomies on `post` for the blog panel inspector dropdown
 */
function tepll_html_sitemap_get_editor_blog_taxonomies() : array {
	$list = array();
	foreach ( get_object_taxonomies( 'post', 'objects' ) as $tax_slug => $tax_obj ) :
		if ( ! $tax_obj instanceof WP_Taxonomy || ! $tax_obj->public ) continue;
		$list[] = array(
			'slug'  => $tax_slug,
			'label' => $tax_obj->label ? $tax_obj->label : $tax_slug,
		);
	endforeach;
	return $list;
}
