<?php
/**
 * Includes -> Admin -> Plugin list
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


add_filter( 'plugin_row_meta', function( array $plugin_meta, string $plugin_file, array $plugin_data, string $status ): array {
	if ( plugin_basename( TEPLL_PLUGIN_FILE ) !== $plugin_file ) return $plugin_meta;

	$slug = 'tms-extensions-polylang';

	$plugin_meta[] = sprintf(
		'<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
		esc_url( 'https://es.wordpress.org/plugins/' . $slug . '/' ),
		__( 'Visit plugin site', 'tms-extensions-polylang' )
	);

	$plugin_meta[] = sprintf(
		'<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
		esc_url( 'https://wordpress.org/support/plugin/' . $slug . '/' ),
		__( 'Get help', 'tms-extensions-polylang' )
	);

	$plugin_meta[] = sprintf(
		'<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
		esc_url( 'https://wordpress.org/support/plugin/' . $slug . '/reviews/#new-post' ),
		__( 'Leave a review', 'tms-extensions-polylang' )
	);

	return $plugin_meta;
}, 10, 4 );
