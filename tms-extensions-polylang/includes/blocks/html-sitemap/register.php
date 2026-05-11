<?php
/**
 * Includes -> Blocks -> Html sitemap -> Register
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


$tepll_html_sitemap_block_directory = __DIR__;
$tepll_html_sitemap_block_url       = plugin_dir_url( __FILE__ );

// Editor assets
wp_register_style(
	'tepll-html-sitemap-editor',
	$tepll_html_sitemap_block_url . 'editor.css',
	array(),
	filemtime( $tepll_html_sitemap_block_directory . '/editor.css' )
);
wp_register_script(
	'tepll-html-sitemap-editor',
	$tepll_html_sitemap_block_url . 'editor.js',
	array(
		'wp-blocks',
		'wp-element',
		'wp-components',
		'wp-block-editor',
		'wp-data',
		'wp-core-data',
		'wp-server-side-render',
	),
	filemtime( $tepll_html_sitemap_block_directory . '/editor.js' ),
	true
);
wp_localize_script(
	'tepll-html-sitemap-editor',
	'tepllHtmlSitemapEditorI18n',
	array(
		'panelTitle'         => __( 'HTML Sitemap for Polylang', 'tms-extensions-polylang' ),
		'placeholderLabel'   => __( 'HTML Sitemap for Polylang', 'tms-extensions-polylang' ),
		'general'            => __( 'General', 'tms-extensions-polylang' ),
		'showListBullets'    => __( 'Show list bullets', 'tms-extensions-polylang' ),
		'hideEmpty'          => __( 'Hide empty taxonomy terms', 'tms-extensions-polylang' ),
		'maxDepth'           => __( 'Max depth (page hierarchy)', 'tms-extensions-polylang' ),
		'pageSort'           => __( 'Page sort', 'tms-extensions-polylang' ),
		'sortMenuOrder'      => __( 'Menu order', 'tms-extensions-polylang' ),
		'sortAlphabetical'   => __( 'Alphabetical (title)', 'tms-extensions-polylang' ),
		'blogPanel'          => __( 'Blog (posts)', 'tms-extensions-polylang' ),
		'blog'               => __( 'Include blog section', 'tms-extensions-polylang' ),
		'blogMaxDepth'       => __( 'Section max depth (leave empty to use global max depth)', 'tms-extensions-polylang' ),
		'blogShowTaxonomy'   => __( 'Show taxonomy under blog page', 'tms-extensions-polylang' ),
		'blogShowPosts'      => __( 'Show posts', 'tms-extensions-polylang' ),
		'blogTaxonomy'       => __( 'Grouping taxonomy', 'tms-extensions-polylang' ),
		'blogParentPage'     => __( 'Posts anchor page', 'tms-extensions-polylang' ),
		'blogPageUseReading' => __( 'Default (Settings → Reading)', 'tms-extensions-polylang' ),
		'cptPanel'           => __( 'Custom post types', 'tms-extensions-polylang' ),
		'cptInclude'         => __( 'Include section in sitemap', 'tms-extensions-polylang' ),
		'cptMaxDepth'        => __( 'Section max depth (empty = global)', 'tms-extensions-polylang' ),
		'cptShowTaxonomy'    => __( 'Show taxonomy', 'tms-extensions-polylang' ),
		'cptShowPosts'       => __( 'Show entries', 'tms-extensions-polylang' ),
		'cptTaxonomy'        => __( 'Grouping taxonomy', 'tms-extensions-polylang' ),
		'cptParentPage'      => __( 'Anchor page in sitemap', 'tms-extensions-polylang' ),
		'cptParentNotSet'    => __( '— Select a page —', 'tms-extensions-polylang' ),
		'pagesLoading'       => __( 'Loading pages…', 'tms-extensions-polylang' ),
		'pageNotInList'      => __( 'Page (not in list)', 'tms-extensions-polylang' ),
		'cptNone'            => __( 'No public custom post types are registered.', 'tms-extensions-polylang' ),
	)
);

$tepll_html_sitemap_editor_public_cpt_definitions    = tepll_html_sitemap_get_editor_cpt_definitions();
$tepll_html_sitemap_editor_blog_taxonomy_definitions = tepll_html_sitemap_get_editor_blog_taxonomies();
wp_add_inline_script(
	'tepll-html-sitemap-editor',
	'window.tepllHtmlSitemapEditorCptDefinitions = ' . wp_json_encode( $tepll_html_sitemap_editor_public_cpt_definitions ) . ';'
	. 'window.tepllHtmlSitemapEditorBlogTaxonomies = ' . wp_json_encode( $tepll_html_sitemap_editor_blog_taxonomy_definitions ) . ';',
	'before'
);

register_block_type( $tepll_html_sitemap_block_directory );
