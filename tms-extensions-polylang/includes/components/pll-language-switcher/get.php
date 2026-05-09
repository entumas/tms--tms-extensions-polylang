<?php
/**
 * Includes -> Components -> Pll Language Switcher -> Get
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


require_once __DIR__ . '/dropdown-wrapper-split.php';


/**
 * Get
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

function tepll_language_switcher_get_html( array $args = array() ) : string {
	$defaults = array(
		'display'                 => 'list',
		'vertical'                => false,
		'label'                   => 'code',
		'show_text'               => true,
		'show_flags'              => false,
		'hide_current'            => false,
		'hide_if_no_translation'  => false,
		'redirect_to_home'        => false,
		'dropdown_surface_class'  => '',
		'dropdown_surface_style'  => '',
		'clickable_class_extra'   => '',
		'clickable_style_extra'   => '',
		'block_nav'               => array(),
	);
	$args = wp_parse_args( $args, $defaults );

	$block_nav = isset( $args['block_nav'] ) && is_array( $args['block_nav'] )
		? $args['block_nav']
		: array();

	$dropdown_surface_class = is_string( $args['dropdown_surface_class'] )
		? trim( $args['dropdown_surface_class'] )
		: '';
	$dropdown_surface_style = is_string( $args['dropdown_surface_style'] )
		? trim( $args['dropdown_surface_style'] )
		: '';
	$clickable_class_extra = is_string( $args['clickable_class_extra'] )
		? trim( $args['clickable_class_extra'] )
		: '';
	$clickable_style_extra = is_string( $args['clickable_style_extra'] )
		? trim( $args['clickable_style_extra'] )
		: '';

	$display = sanitize_key( (string) $args['display'] );
	if ( ! in_array( $display, array( 'list', 'dropdown' ), true ) ) :
		$display = 'list';
	endif;

	$label = sanitize_key( (string) $args['label'] );
	if ( ! in_array( $label, array( 'code', 'name' ), true ) ) :
		$label = 'code';
	endif;

	$vertical               = ! empty( $args['vertical'] );
	$show_text              = ! empty( $args['show_text'] );
	$show_flags             = ! empty( $args['show_flags'] );
	$hide_current           = ! empty( $args['hide_current'] );
	$hide_if_no_translation = ! empty( $args['hide_if_no_translation'] );
	$redirect_to_home       = ! empty( $args['redirect_to_home'] );

	$r_hide_current = $hide_current
		? 1
		: 0;
	$r_hide_if_no_translation = $hide_if_no_translation
		? 1
		: 0;

	if ( 'list' !== $display ) $vertical = false;

	$parsed_args = array(
		'display'    => $display,
		'vertical'   => $vertical,
		'label'      => $label,
		'show_text'  => $show_text,
		'show_flags' => $show_flags,
	);

	$state_classes = array();
	if ( $show_flags ) :
		$state_classes[] = 'has-flag';
	endif;

	if ( $show_text && 'name' === $label ) :
		$state_classes[] = 'has-name';
	elseif ( ! $show_text || ( $show_text && 'code' === $label ) ) :
		$state_classes[] = 'has-code';
	endif;

	$lang_rows = pll_the_languages(
		array(
			'raw'                    => 1,
			'hide_if_empty'          => 0,
			'hide_current'           => $r_hide_current,
			'hide_if_no_translation' => $r_hide_if_no_translation,
		)
	);

	if ( empty( $lang_rows ) || ! is_array( $lang_rows ) ) :
		if ( $hide_if_no_translation ) :
			return tepll_language_switcher_render_no_translations_notice_nav( $display, $vertical, $state_classes, $block_nav );
		endif;

		if ( ! is_admin() ) return '';
		if ( is_admin() ) :
			$locale = determine_locale();
			$slug   = strtolower( (string) substr( (string) $locale, 0, 2 ) );
			$lang_rows = array(
				array(
					'id'           => 0,
					'slug'         => $slug
						? $slug
						: 'en',
					'locale'       => $locale
						? $locale
						: 'en_US',
					'name'         => $locale
						? $locale
						: 'English',
					'url'          => '#',
					'current_lang' => false,
				),
			);
		else :
			return '';
		endif;
	endif;

	$languages = array_values( $lang_rows );
	$current   = null;
	$others    = array();
	foreach ( $languages as $lang ) :
		if ( ! is_array( $lang ) ) continue;
		if ( ! empty( $lang['current_lang'] ) ) :
			$current = $lang;
		else :
			$others[] = $lang;
		endif;
	endforeach;

	if ( $hide_if_no_translation && empty( $others ) ) :
		return tepll_language_switcher_render_no_translations_notice_nav( $display, $vertical, $state_classes, $block_nav );
	endif;

	if ( 'list' === $display ) :
		$root_classes = array_merge( array( 'tepll-pll-language-switcher', 'is-list' ), $state_classes );
		if ( $vertical ) :
			$root_classes[] = 'is-vertical';
		endif;
		$total   = count( $languages );
		$list_a = tepll_language_switcher_dropdown_merge_ul_surface_attrs(
			'',
			$clickable_class_extra,
			'',
			$clickable_style_extra
		);

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
		>
			<ul class="tepll-pll-language-switcher-list">
				<?php foreach ( $languages as $i => $lang ) : ?>
					<?php if ( ! is_array( $lang ) ) continue; ?>
					<li class="<?php echo esc_attr( tepll_language_switcher_get_li_class( $lang, (int) $i, $total ) ); ?>">
						<a
							href="<?php echo esc_url( (string) ( $redirect_to_home
								? pll_home_url( (string) ( $lang['slug'] ?? '' ) )
								: ( $lang['url'] ?? '' )
							) ); ?>"
							hreflang="<?php echo esc_attr( (string) ( $lang['locale'] ?? '' ) ); ?>"
							lang="<?php echo esc_attr( (string) ( $lang['locale'] ?? '' ) ); ?>"
							rel="alternate"
							<?php echo ( '' !== trim( (string) $list_a['class'] ) )
								? 'class="' . esc_attr( (string) $list_a['class'] ) . '"'
								: ''; ?>
							<?php echo ( '' !== trim( (string) $list_a['style'] ) )
								? ' style="' . esc_attr( (string) $list_a['style'] ) . '"'
								: ''; ?>
							<?php if ( ! empty( $lang['current_lang'] ) ) : ?>
								aria-current="page"
							<?php endif; ?>
						>
							<?php echo wp_kses_post( tepll_language_switcher_get_inner_html( $lang, $parsed_args, false ) ); ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</nav>
		<?php
		return trim( (string) ob_get_clean() );
	endif;

	$toggle_lang    = $current;
	$dropdown_langs = $others;

	if ( $hide_current ) :
		$toggle_lang = $toggle_lang
			? $toggle_lang
			: tepll_language_switcher_get_current_language_pll_row( $r_hide_if_no_translation );
		$dropdown_langs = $languages;
	endif;

	if ( ! $toggle_lang || empty( $dropdown_langs ) ) return '';

	$toggle_plain = ( 'name' === $parsed_args['label'] && ! empty( $toggle_lang['name'] ) )
		? (string) $toggle_lang['name']
		: (string) ( $toggle_lang['slug'] ?? '' );
	$btn_label    = sprintf(
		/* translators: %s: current language name or code. */
		__( 'Choose language. Current: %s', 'tms-extensions-polylang' ),
		$toggle_plain
	);
	$btn_id  = wp_unique_id( 'tepll-lang-toggle-' );
	$list_id = wp_unique_id( 'tepll-lang-menu-' );

	$dropdown_ul = tepll_language_switcher_dropdown_merge_ul_surface_attrs(
		'tepll-pll-language-switcher-dropdown',
		$dropdown_surface_class,
		'',
		$dropdown_surface_style
	);

	$toggle_el = tepll_language_switcher_dropdown_merge_ul_surface_attrs(
		'tepll-pll-language-switcher-toggle',
		$clickable_class_extra,
		'',
		$clickable_style_extra
	);

	$dd_a = tepll_language_switcher_dropdown_merge_ul_surface_attrs(
		'',
		$clickable_class_extra,
		'',
		$clickable_style_extra
	);

	$root_dd_classes = array_merge( array( 'tepll-pll-language-switcher', 'is-dropdown' ), $state_classes );
	$bn_class = isset( $block_nav['class'] ) && is_string( $block_nav['class'] )
		? trim( $block_nav['class'] )
		: '';
	$bn_style = isset( $block_nav['style'] ) && is_string( $block_nav['style'] )
		? trim( $block_nav['style'] )
		: '';
	$bn_other = isset( $block_nav['other_attrs'] ) && is_array( $block_nav['other_attrs'] )
		? $block_nav['other_attrs']
		: array();
	$nav_m = tepll_language_switcher_dropdown_merge_ul_surface_attrs(
		implode( ' ', $root_dd_classes ),
		$bn_class,
		'',
		$bn_style
	);
	$nav_rest = tepll_language_switcher_dropdown_build_html_attributes_fragment(
		tepll_language_switcher_dropdown_filter_empty_attr_pairs( $bn_other )
	);

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
	>
		<button
			type="button"
			id="<?php echo esc_attr( $btn_id ); ?>"
			class="<?php echo esc_attr( (string) $toggle_el['class'] ); ?>"
			<?php echo ( '' !== trim( (string) $toggle_el['style'] ) )
				? ' style="' . esc_attr( (string) $toggle_el['style'] ) . '"'
				: ''; ?>
			aria-expanded="false"
			aria-controls="<?php echo esc_attr( $list_id ); ?>"
			aria-haspopup="true"
			aria-label="<?php echo esc_attr( $btn_label ); ?>"
		>
			<?php echo wp_kses_post( tepll_language_switcher_get_inner_html( $toggle_lang, $parsed_args, true ) ); ?>
		</button>
		<ul
			id="<?php echo esc_attr( $list_id ); ?>"
			class="<?php echo esc_attr( $dropdown_ul['class'] ); ?>"
			<?php echo ( isset( $dropdown_ul['style'] ) && '' !== trim( (string) $dropdown_ul['style'] ) )
				? ' style="' . esc_attr( (string) $dropdown_ul['style'] ) . '"'
				: ''; ?>
			aria-labelledby="<?php echo esc_attr( $btn_id ); ?>"
			aria-hidden="true"
		>
			<?php foreach ( $dropdown_langs as $i => $lang ) : ?>
				<?php if ( ! is_array( $lang ) ) continue; ?>
				<?php $li_class = tepll_language_switcher_get_li_class( $lang, (int) $i, count( $dropdown_langs ) ); ?>
				<li class="<?php echo esc_attr( $li_class ); ?>">
					<a
						href="<?php echo esc_url( (string) ( $redirect_to_home ? pll_home_url( (string) ( $lang['slug'] ?? '' ) ) : ( $lang['url'] ?? '' ) ) ); ?>"
						hreflang="<?php echo esc_attr( (string) ( $lang['locale'] ?? '' ) ); ?>"
						lang="<?php echo esc_attr( (string) ( $lang['locale'] ?? '' ) ); ?>"
						rel="alternate"
						<?php echo ( '' !== trim( (string) $dd_a['class'] ) )
							? 'class="' . esc_attr( (string) $dd_a['class'] ) . '"'
							: ''; ?>
						<?php echo ( '' !== trim( (string) $dd_a['style'] ) )
							? ' style="' . esc_attr( (string) $dd_a['style'] ) . '"'
							: ''; ?>
					>
						<?php echo wp_kses_post( tepll_language_switcher_get_inner_html( $lang, $parsed_args, false ) ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</nav>
	<?php
	return trim( (string) ob_get_clean() );
}


/**
 * Print
 */

function tepll_language_switcher_print_html( array $args = array() ) : void {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo wp_kses( tepll_language_switcher_get_html( $args ), tepll_language_switcher_get_kses_allowed_html() );
}
