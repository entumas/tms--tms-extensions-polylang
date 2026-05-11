<?php
/**
 * Includes -> Blocks -> Pll menu by language -> Render
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


if ( isset( $block ) && $block instanceof WP_Block ) :
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Markup from core navigation render_block().
	echo tepll_menu_by_language_get_html( $block );
endif;
