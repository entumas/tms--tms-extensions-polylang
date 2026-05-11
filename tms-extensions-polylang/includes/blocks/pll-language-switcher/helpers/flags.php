<?php
/**
 * Includes -> Blocks -> Pll language switcher -> Helpers -> Flags
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Resolve custom flag URL for a language slug from uploads `pll-flags` or theme image/asset folders
 */
function tepll_language_switcher_resolve_custom_flag_url( string $slug ) : string {
	$slug = sanitize_file_name( $slug );
	if ( '' === $slug ) return '';

	$locations = array();

	$uploads = wp_upload_dir();
	if ( empty( $uploads['error'] ) ) :
		$locations[] = array(
			'dir' => trailingslashit( $uploads['basedir'] ) . 'pll-flags',
			'url' => trailingslashit( $uploads['baseurl'] ) . 'pll-flags',
		);
	endif;

	$stylesheet_dir = wp_normalize_path( get_stylesheet_directory() );
	$template_dir   = wp_normalize_path( get_template_directory() );
	$theme_dirs     = array( $stylesheet_dir );
	if ( $template_dir !== $stylesheet_dir ) :
		$theme_dirs[] = $template_dir;
	endif;

	foreach ( $theme_dirs as $theme_path ) :
		$uri_base = ( $theme_path === $stylesheet_dir )
			? get_stylesheet_directory_uri()
			: get_template_directory_uri();
		foreach ( array( 'images', 'assets' ) as $sub ) :
			$locations[] = array(
				'dir' => trailingslashit( $theme_path ) . $sub . '/pll-flags',
				'url' => trailingslashit( $uri_base ) . $sub . '/pll-flags',
			);
		endforeach;
	endforeach;

	foreach ( $locations as $loc ) :
		$svg = $loc['dir'] . '/' . $slug . '.svg';
		if ( file_exists( $svg ) ) :
			return $loc['url'] . '/' . $slug . '.svg';
		endif;
	endforeach;

	$raster_exts = array( 'png', 'jpg', 'jpeg', 'webp' );
	foreach ( $locations as $loc ) :
		foreach ( $raster_exts as $ext ) :
			$file = $loc['dir'] . '/' . $slug . '.' . $ext;
			if ( file_exists( $file ) ) :
				return $loc['url'] . '/' . $slug . '.' . $ext;
			endif;
		endforeach;
	endforeach;

	return '';
}
