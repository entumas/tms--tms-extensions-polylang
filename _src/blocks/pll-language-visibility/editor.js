// --------------------------------------------------
//	Pll lang visibility: Editor.js
// --------------------------------------------------


( ( wp ) => {
	const { registerBlockType }                           = wp.blocks,
		{ PanelBody, SelectControl, Dashicon }            = wp.components,
		{ InspectorControls, InnerBlocks, useBlockProps } = wp.blockEditor
	const { useSelect } = wp.data

	const tepllLanguageVisibilityEditorI18nStrings           = window.tepllLanguageVisibilityEditorI18n
	const tepllLanguageVisibilityEditorLanguageSelectOptions = window.tepllLanguageVisibilityEditorLanguageOptions

	registerBlockType( 'tepll/pll-language-visibility', {
		edit: ( props ) => {
			const { attributes: { lang, mode }, setAttributes, clientId } = props
			const tepllLanguageVisibilityEditorModeControlOptions = [
				{ value: 'show_if', label: tepllLanguageVisibilityEditorI18nStrings.showInSelectedLanguage },
				{ value: 'hide_if', label: tepllLanguageVisibilityEditorI18nStrings.hideInSelectedLanguage }
			]

			const tepllLanguageVisibilityEditorSanitizedMode  = mode || 'show_if'
			const tepllLanguageVisibilityEditorHasInnerBlocks = useSelect( ( select ) => {
				const tepllLanguageVisibilityEditorBlockEditorStore = select( 'core/block-editor' )
				if ( ! tepllLanguageVisibilityEditorBlockEditorStore || typeof tepllLanguageVisibilityEditorBlockEditorStore.getBlock !== 'function' ) return false
				const tepllLanguageVisibilityEditorBlockRecord = tepllLanguageVisibilityEditorBlockEditorStore.getBlock( clientId )
				return !! ( tepllLanguageVisibilityEditorBlockRecord && Array.isArray( tepllLanguageVisibilityEditorBlockRecord.innerBlocks ) && tepllLanguageVisibilityEditorBlockRecord.innerBlocks.length )
			}, [ clientId ] )

			const tepllLanguageVisibilityEditorDisplayLangLabel = lang === ''
				? tepllLanguageVisibilityEditorI18nStrings.allLanguages
				: ( tepllLanguageVisibilityEditorLanguageSelectOptions.find( ( opt ) => String( opt.value ) === String( lang ) ) || {} ).label || String( lang )
			const tepllLanguageVisibilityEditorActionLabelFull = tepllLanguageVisibilityEditorSanitizedMode === 'show_if'
				? tepllLanguageVisibilityEditorI18nStrings.showInSelectedLanguage
				: tepllLanguageVisibilityEditorI18nStrings.hideInSelectedLanguage
			const tepllLanguageVisibilityEditorActionLabelWord = tepllLanguageVisibilityEditorActionLabelFull && tepllLanguageVisibilityEditorActionLabelFull.trim
				? tepllLanguageVisibilityEditorActionLabelFull.trim().split( /\s+/ )[ 0 ]
				: tepllLanguageVisibilityEditorActionLabelFull
			const tepllLanguageVisibilityEditorPlaceholderTitleText = `${ tepllLanguageVisibilityEditorActionLabelWord }: ${ tepllLanguageVisibilityEditorDisplayLangLabel }`
			const tepllLanguageVisibilityEditorBadgeLangCode = lang === ''
				? 'all'
				: lang
			const tepllLanguageVisibilityEditorWrapperBlockProps = useBlockProps( {
				className: 'tepll-pll-language-visibility' + ( tepllLanguageVisibilityEditorSanitizedMode === 'show_if' ? ' is-show' : ' is-hide' ) + ( ! tepllLanguageVisibilityEditorHasInnerBlocks ? ' is-empty' : '' ),
				title: tepllLanguageVisibilityEditorPlaceholderTitleText,
			} )

			// Placeholder text is shown when empty; tooltip works always via wrapper `title`.
			const tepllLanguageVisibilityEditorInnerBlocksPlaceholder = wp.element.createElement(
				'span',
				{ className: 'tepll-pll-language-visibility-placeholder' },
				wp.element.createElement( Dashicon, {
					icon: tepllLanguageVisibilityEditorSanitizedMode === 'show_if'
						? 'visibility'
						: 'hidden',
					style: { fontSize: '16px', verticalAlign: 'middle', marginRight: '6px' },
				} ),
				wp.element.createElement( 'span', null, tepllLanguageVisibilityEditorBadgeLangCode )
			)

			return (
				wp.element.createElement( 'div', tepllLanguageVisibilityEditorWrapperBlockProps,
					// Inspector
					wp.element.createElement( InspectorControls, {},
						wp.element.createElement( PanelBody, { title: tepllLanguageVisibilityEditorI18nStrings.languageVisibility, initialOpen: true },
							wp.element.createElement( SelectControl, {
								label: tepllLanguageVisibilityEditorI18nStrings.language,
								value: lang,
								onChange: ( val ) => setAttributes( {
									lang: val,
									mode: val === ''
										? 'show_if'
										: mode
								} ),
								options: tepllLanguageVisibilityEditorLanguageSelectOptions
							} ),
							lang !== ''
								? wp.element.createElement( SelectControl, {
									label: tepllLanguageVisibilityEditorI18nStrings.visibilityMode,
									value: mode,
									onChange: ( val ) => setAttributes( { mode: val } ),
									options: tepllLanguageVisibilityEditorModeControlOptions
								} )
								: null
						)
					),
					// InnerBlocks
					wp.element.createElement( InnerBlocks, {
						renderAppender: InnerBlocks.ButtonBlockAppender,
						placeholder: tepllLanguageVisibilityEditorInnerBlocksPlaceholder
					} )
				)
			)
		},
		save: () => wp.element.createElement( InnerBlocks.Content, null )
	} )
} )( window.wp )