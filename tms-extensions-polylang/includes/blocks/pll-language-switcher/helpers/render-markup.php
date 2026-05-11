<?php
/**
 * Includes -> Blocks -> Pll language switcher -> Helpers -> Render markup
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Accessible `<nav>` shell when Polylang returns no languages but hide-if-empty messaging applies
 */
function tepll_language_switcher_render_no_translations_notice_nav( string $display, bool $vertical, array $state_classes, array $block_nav = array() ) : string {
	$root_classes = array_merge(
		array(
			'tepll-pll-language-switcher',
			( 'dropdown' === $display )
				? 'is-dropdown'
				: 'is-list',
		),
		$state_classes
	);
	if ( 'list' === $display && $vertical ) :
		$root_classes[] = 'is-vertical';
	endif;

	$bn_class = isset( $block_nav['class'] ) && is_string( $block_nav['class'] )
		? trim( $block_nav['class'] )
		: '';
	$bn_style = isset( $block_nav['style'] ) && is_string( $block_nav['style'] )
		? trim( $block_nav['style'] )
		: '';
	$bn_other = isset( $block_nav['other_attrs'] ) && is_array( $block_nav['other_attrs'] )
		? $block_nav['other_attrs']
		: array();
	$nav_m    = tepll_language_switcher_dropdown_merge_ul_surface_attrs(
		implode( ' ', $root_classes ),
		$bn_class,
		'',
		$bn_style
	);
	$nav_rest = tepll_language_switcher_dropdown_build_html_attributes_fragment(
		tepll_language_switcher_dropdown_filter_empty_attr_pairs( $bn_other )
	);

	$note_id = wp_unique_id( 'tepll-lang-note-' );
	ob_start();
	?>
	<nav
		class="<?php echo esc_attr( (string) $nav_m['class'] ); ?>"
		<?php echo ( isset( $nav_m['style'] ) && '' !== trim( (string) $nav_m['style'] ) )
			? ' style="' . esc_attr( (string) $nav_m['style'] ) . '"'
			: ''; ?>
		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $nav_rest is built from esc_attr() pairs.
		echo '' !== $nav_rest ? ' ' . $nav_rest : '';
		?>
		aria-label="<?php echo esc_attr__( 'Languages', 'tms-extensions-polylang' ); ?>"
		aria-describedby="<?php echo esc_attr( $note_id ); ?>"
	>
		<p id="<?php echo esc_attr( $note_id ); ?>" class="screen-reader-text">
			<?php echo esc_html__( 'No translation is available for this page.', 'tms-extensions-polylang' ); ?>
		</p>
	</nav>
	<?php
	return trim( (string) ob_get_clean() );
}
