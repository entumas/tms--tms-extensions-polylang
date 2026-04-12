<?php
/**
 * Includes -> Components -> Sitemap -> Helpers
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Main
 */

// Coerce mixed value to bool (for block attrs + shortcode mapped values)
if ( ! function_exists( 'html_sitemap_mixed_to_bool' ) ) :
	function html_sitemap_mixed_to_bool( $value, bool $default = false ) : bool {
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
endif;

// Normalize and validate sitemap arguments
if ( ! function_exists( 'html_sitemap_normalize_args' ) ) :
	function html_sitemap_normalize_args( array $args ) : array {
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

		$out['hide_empty'] = html_sitemap_mixed_to_bool( $out['hide_empty'], true );
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
				'show_taxonomy'  => html_sitemap_mixed_to_bool( $cfg['show_taxonomy'], true ),
				'show_posts'     => html_sitemap_mixed_to_bool( $cfg['show_posts'], true ),
				'taxonomy'       => isset( $cfg['taxonomy'] )
					? sanitize_key( (string) $cfg['taxonomy'] )
					: 'category',
				'parent_page_id' => isset( $cfg['parent_page_id'] )
					? (int) $cfg['parent_page_id']
					: 0,
			);

			if ( ! html_sitemap_taxonomy_applies_to_type( $clean[ $post_type ]['taxonomy'], $post_type ) ) :
				$clean[ $post_type ]['taxonomy'] = 'post' === $post_type
					? 'category'
					: '';
				if ( '' === $clean[ $post_type ]['taxonomy'] || ! html_sitemap_taxonomy_applies_to_type( $clean[ $post_type ]['taxonomy'], $post_type ) ) :
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
endif;

// Whether a taxonomy is registered for the post type
if ( ! function_exists( 'html_sitemap_taxonomy_applies_to_type' ) ) :
	function html_sitemap_taxonomy_applies_to_type( string $taxonomy, string $post_type ) : bool {
		if ( '' === $taxonomy ) return false;

		$tax = get_taxonomy( $taxonomy );
		if ( ! $tax || ! is_array( $tax->object_type ) ) return false;

		return in_array( $post_type, $tax->object_type, true );
	}
endif;

// Effective max depth for a post-type section under its anchor page
if ( ! function_exists( 'html_sitemap_section_max_depth' ) ) :
	function html_sitemap_section_max_depth( array $cfg, array $args ) : int {
		return null !== ( $cfg['max_depth'] ?? null )
			? (int) $cfg['max_depth']
			: (int) $args['max_depth'];
	}
endif;


/**
 * Polylang
 */

// Current language slug
if ( ! function_exists( 'html_sitemap_current_lang' ) ) :
	function html_sitemap_current_lang() : string {
		$lang = pll_current_language();
		if ( is_string($lang) && $lang !== '' ) return $lang;

		// Fallback for admin/editor: language of the post being edited
		$post_id = 0;
		if ( isset($GLOBALS['post']) && $GLOBALS['post'] instanceof WP_Post ) :
			$post_id = (int) $GLOBALS['post']->ID;
		else :
			$post_id = (int) get_queried_object_id();
		endif;
		if ( $post_id > 0 && function_exists('pll_get_post_language') ) :
			$post_lang = pll_get_post_language($post_id, 'slug');
			return is_string($post_lang)
				? $post_lang
				: '';
		endif;

		return '';
	}
endif;

// Translated post ID for the active language
if ( ! function_exists( 'html_sitemap_resolve_post_id' ) ) :
	function html_sitemap_resolve_post_id( int $post_id, string $lang ) : int {
		if ( $post_id <= 0 ) return 0;

		if ( '' !== $lang ) :
			$translated = pll_get_post( $post_id, $lang );
			return is_int( $translated ) && $translated > 0
				? $translated
				: 0;
		endif;

		return $post_id;
	}
endif;

// Anchor page ID for a post type (blog page or CPT parent)
if ( ! function_exists( 'html_sitemap_resolve_parent_page_id' ) ) :
	function html_sitemap_resolve_parent_page_id( string $post_type, array $cfg, string $lang ) : int {
		if ( 'post' === $post_type ) :
			$raw = (int) ( $cfg['parent_page_id'] ?? 0 );
			if ( $raw > 0 ) return html_sitemap_resolve_post_id( $raw, $lang );
			$blog = (int) get_option( 'page_for_posts' );
			if ( $blog <= 0 ) return 0;
			return html_sitemap_resolve_post_id( $blog, $lang );
		endif;
		$parent = (int) ( $cfg['parent_page_id'] ?? 0 );
		if ( $parent <= 0 ) return 0;
		return html_sitemap_resolve_post_id( $parent, $lang );
	}
endif;


/**
 * Pages
 */

// Sort column and order for `get_pages`
if ( ! function_exists( 'html_sitemap_get_pages_sort' ) ) :
	function html_sitemap_get_pages_sort( string $page_sort ) : array {
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
endif;

// Published children of a parent (optional Polylang `lang`)
if ( ! function_exists( 'html_sitemap_get_pages_at' ) ) :
	function html_sitemap_get_pages_at( int $parent_id, string $lang, string $page_sort ) : array {
		$sort  = html_sitemap_get_pages_sort( $page_sort );
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
endif;


/**
 * Posts
 */

// By term (date desc)
if ( ! function_exists( 'html_sitemap_posts_for_term' ) ) :
	function html_sitemap_posts_for_term( string $post_type, string $taxonomy, int $term_id, string $lang ) : array {
		$cache_key = 'html_sitemap_posts_for_term_' . md5( wp_json_encode( array( $post_type, $taxonomy, $term_id, $lang ) ) );
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
endif;

// All of type (date desc), no tax filter
if ( ! function_exists( 'html_sitemap_posts_flat' ) ) :
	function html_sitemap_posts_flat( string $post_type, string $lang ) : array {
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
endif;


/**
 * Terms
 */

// Taxonomy list (name asc)
if ( ! function_exists( 'html_sitemap_terms_list' ) ) :
	function html_sitemap_terms_list( string $taxonomy, bool $hide_empty, string $lang ) : array {
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
endif;


/**
 * HTML
 */

// `<ul>` of post links
if ( ! function_exists( 'html_sitemap_render_post_links' ) ) :
	function html_sitemap_render_post_links( array $posts ) : string {
		if ( array() === $posts ) return '';

		$html = '<ul class="tepll-html-sitemap-posts">';
		foreach ( $posts as $post ) :
			if ( ! $post instanceof WP_Post ) continue;
			$html .= '<li><a href="' . esc_url( get_permalink( $post ) ) . '">' . esc_html( get_the_title( $post ) ) . '</a></li>';
		endforeach;
		$html .= '</ul>';

		return $html;
	}
endif;

// post-type block under an anchor page (taxonomy and/or posts)
if ( ! function_exists( 'html_sitemap_render_post_type_section' ) ) :
	function html_sitemap_render_post_type_section( string $post_type, array $cfg, array $args, string $lang ) : string {
		if ( ! $cfg['show_taxonomy'] && ! $cfg['show_posts'] ) return '';

		$section_max = html_sitemap_section_max_depth( $cfg, $args );
		if ( $section_max < 2 ) return '';

		$taxonomy = $cfg['taxonomy'];
		$show_tax = $cfg['show_taxonomy'] && '' !== $taxonomy && html_sitemap_taxonomy_applies_to_type( $taxonomy, $post_type );
		$show_pts = $cfg['show_posts'];

		if ( $show_tax && $show_pts && $section_max < 3 ) :
			$show_pts = false;
		endif;

		$inner = '';

		if ( $show_tax ) :
			$terms = html_sitemap_terms_list( $taxonomy, $args['hide_empty'], $lang );
			if ( array() !== $terms ) :
				foreach ( $terms as $term ) :
					if ( ! $term instanceof WP_Term ) continue;
					$tlink = get_term_link( $term );
					if ( is_wp_error( $tlink ) ) continue;
					$inner .= '<li><a href="' . esc_url( $tlink ) . '">' . esc_html( $term->name ) . '</a>';
					if ( $show_pts ) :
						$posts  = html_sitemap_posts_for_term( $post_type, $taxonomy, (int) $term->term_id, $lang );
						$inner .= html_sitemap_render_post_links( $posts );
					endif;
					$inner .= '</li>';
				endforeach;
			endif;
		elseif ( $show_pts ) :
			$posts  = html_sitemap_posts_flat( $post_type, $lang );
			$inner .= html_sitemap_render_post_links( $posts );
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
endif;

// One page `<li>` with post-type sections and child pages
if ( ! function_exists( 'html_sitemap_build_page_li' ) ) :
	function html_sitemap_build_page_li( WP_Post $page, int $depth, array $args, string $lang ) : string {
		$html  = '<li><a href="' . esc_url( get_permalink( $page ) ) . '">' . esc_html( $page->post_title ) . '</a>';
		$extra = '';

		foreach ( $args['post_types'] as $pt => $cfg ) :
			$parent_id = html_sitemap_resolve_parent_page_id( $pt, $cfg, $lang );
			if ( $parent_id !== (int) $page->ID ) continue;
			$extra .= html_sitemap_render_post_type_section( $pt, $cfg, $args, $lang );
		endforeach;

		if ( $depth < (int) $args['max_depth'] ) :
			$children = html_sitemap_get_pages_at( (int) $page->ID, $lang, $args['page_sort'] );
			if ( array() !== $children ) :
				$sub = '';
				foreach ( $children as $child ) :
					if ( ! $child instanceof WP_Post ) continue;
					$sub .= html_sitemap_build_page_li( $child, $depth + 1, $args, $lang );
				endforeach;
				if ( '' !== $sub ) :
					$extra .= '<ul class="tepll-html-sitemap-pages">' . $sub . '</ul>';
				endif;
			endif;
		endif;

		$html .= $extra . '</li>';
		return $html;
	}
endif;


/**
 * Shortcode
 */

// Coerce attribute string to bool
if ( ! function_exists( 'html_sitemap_shortcode_to_bool' ) ) :
	function html_sitemap_shortcode_to_bool( string $value, bool $default ) : bool {
		$value = strtolower( trim( $value ) );
		if ( '' === $value ) return $default;

		if ( in_array( $value, array( '1', 'true', 'yes', 'on' ), true ) ) return true;
		if ( in_array( $value, array( '0', 'false', 'no', 'off' ), true ) ) return false;

		return $default;
	}
endif;

// Build `$args` for `tepll_html_sitemap_get_html()` from `shortcode_atts()` output
if ( ! function_exists( 'html_sitemap_args_from_shortcode_attributes' ) ) :
	function html_sitemap_args_from_shortcode_attributes( array $atts ) : array {
		$args = array(
			'hide_empty' => html_sitemap_shortcode_to_bool( (string) ( $atts['hide_empty'] ?? '1' ), true ),
			'max_depth'  => max( 1, (int) ( $atts['max_depth'] ?? 3 ) ),
		);

		$page_sort = isset( $atts['page_sort'] )
			? sanitize_key( (string) $atts['page_sort'] )
			: 'menu_order';
		$args['page_sort'] = in_array( $page_sort, array( 'menu_order', 'alphabetical' ), true )
			? $page_sort
			: 'menu_order';

		$post_types     = array();
		$blog_on        = html_sitemap_shortcode_to_bool( (string) ( $atts['blog'] ?? '1' ), true );
		$post_types_raw = isset( $atts['post_types'] )
			? (string) $atts['post_types']
			: '';

		if ( $blog_on ) :
			$blog_cfg = array(
				'show_taxonomy'  => html_sitemap_shortcode_to_bool( (string) ( $atts['blog_show_taxonomy'] ?? '1' ), true ),
				'show_posts'     => html_sitemap_shortcode_to_bool( (string) ( $atts['blog_show_posts'] ?? '1' ), true ),
				'taxonomy'       => isset( $atts['blog_taxonomy'] )
					? sanitize_key( (string) $atts['blog_taxonomy'] )
					: 'category',
				'parent_page_id' => isset( $atts['blog_parent_page_id'] )
					? (int) $atts['blog_parent_page_id']
					: 0,
			);
			$bd = isset( $atts['blog_max_depth'] )
				? trim( (string) $atts['blog_max_depth'] )
				: '';
			$blog_cfg['max_depth'] = '' !== $bd
				? max( 1, (int) $bd )
				: null;
			$post_types['post']    = $blog_cfg;
		endif;

		if ( '' !== $post_types_raw ) :
			$decoded = json_decode( wp_unslash( $post_types_raw ), true );
			if ( is_array( $decoded ) ) :
				unset( $decoded['post'] );
				foreach ( $decoded as $slug => $cfg ) :
					if ( ! is_string( $slug ) || ! is_array( $cfg ) ) continue;
					$post_types[ $slug ] = $cfg;
				endforeach;
			endif;
		endif;

		if ( array() !== $post_types || ! $blog_on ) :
			$args['post_types'] = $post_types;
		endif;

		return $args;
	}
endif;


/**
 * Block editor
 */

// Public custom post types for sitemap block inspector (built-in excluded except we skip attachment)
if ( ! function_exists( 'html_sitemap_get_public_cpt_definitions_for_editor' ) ) :
	function html_sitemap_get_public_cpt_definitions_for_editor() : array {
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
endif;

if ( ! function_exists( 'html_sitemap_get_blog_taxonomy_definitions_for_editor' ) ) :
	function html_sitemap_get_blog_taxonomy_definitions_for_editor() : array {
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
endif;

// Map block `$attributes` to `tepll_html_sitemap_get_html()` args (reuses shortcode merge logic)
if ( ! function_exists( 'html_sitemap_args_from_block_attributes' ) ) :
	function html_sitemap_args_from_block_attributes( array $attrs ) : array {
		$cpt_only  = array();
		$cpt_rows  = isset( $attrs['cpt_configs'] ) && is_array( $attrs['cpt_configs'] )
			? $attrs['cpt_configs']
			: array();

		foreach ( $cpt_rows as $row ) :
			if ( ! is_array( $row ) || empty( $row['slug'] ) ) continue;
			$slug = sanitize_key( (string) $row['slug'] );
			if ( '' === $slug || 'post' === $slug ) continue;

			$cfg = array(
				'show_taxonomy'  => html_sitemap_mixed_to_bool( $row['show_taxonomy'] ?? true, true ),
				'show_posts'     => html_sitemap_mixed_to_bool( $row['show_posts'] ?? true, true ),
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

		$post_types_json = '';
		if ( array() !== $cpt_only ) :
			$post_types_json = wp_json_encode( $cpt_only );
		endif;

		$sc = array(
			'hide_empty' => html_sitemap_mixed_to_bool( $attrs['hide_empty'] ?? true, true )
				? '1'
				: '0',
			'max_depth' => (string) max( 1, (int) ( $attrs['max_depth'] ?? 3 ) ),
			'page_sort' => isset( $attrs['page_sort'] )
				? (string) $attrs['page_sort']
				: 'menu_order',
			'blog' => html_sitemap_mixed_to_bool( $attrs['blog'] ?? true, true )
				? '1'
				: '0',
			'blog_max_depth' => isset( $attrs['blog_max_depth'] )
				? (string) $attrs['blog_max_depth']
				: '',
			'blog_show_taxonomy' => html_sitemap_mixed_to_bool( $attrs['blog_show_taxonomy'] ?? true, true )
				? '1'
				: '0',
			'blog_show_posts' => html_sitemap_mixed_to_bool( $attrs['blog_show_posts'] ?? true, true )
				? '1'
				: '0',
			'blog_taxonomy' => isset( $attrs['blog_taxonomy'] )
				? (string) $attrs['blog_taxonomy']
				: 'category',
			'blog_parent_page_id' => isset( $attrs['blog_parent_page_id'] )
				? (string) (int) $attrs['blog_parent_page_id']
				: '0',
			'post_types' => $post_types_json,
		);
		$args = html_sitemap_args_from_shortcode_attributes( $sc );
		$args['wrap'] = false;
		return $args;
	}
endif;
