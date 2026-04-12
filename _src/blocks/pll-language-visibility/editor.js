// --------------------------------------------------
//	Pll lang visibility: Editor.js
// --------------------------------------------------


( ( wp ) => {
	const { registerBlockType }                           = wp.blocks,
		{ PanelBody, SelectControl, Dashicon }            = wp.components,
		{ InspectorControls, InnerBlocks, useBlockProps } = wp.blockEditor
	const { useSelect } = wp.data

	const languageVisibilityEditorI18nStrings = window.tepllLanguageVisibilityEditorI18n
	const languageVisibilityEditorLanguageSelectOptions = window.tepllLanguageVisibilityEditorLanguageOptions

	registerBlockType( 'tepll/pll-language-visibility', {
		edit: ( props ) => {
			const { attributes: { lang, mode }, setAttributes, clientId } = props
			const languageVisibilityEditorModeControlOptions = [
				{ value: 'show_if', label: languageVisibilityEditorI18nStrings.showInSelectedLanguage },
				{ value: 'hide_if', label: languageVisibilityEditorI18nStrings.hideInSelectedLanguage }
			]

			const languageVisibilityEditorSanitizedMode       = mode || 'show_if'
			const languageVisibilityEditorHasInnerBlocks = useSelect( ( select ) => {
				const languageVisibilityEditorBlockEditorStore = select( 'core/block-editor' )
				if ( ! languageVisibilityEditorBlockEditorStore || typeof languageVisibilityEditorBlockEditorStore.getBlock !== 'function' ) return false
				const languageVisibilityEditorBlockRecord = languageVisibilityEditorBlockEditorStore.getBlock( clientId )
				return !! ( languageVisibilityEditorBlockRecord && Array.isArray( languageVisibilityEditorBlockRecord.innerBlocks ) && languageVisibilityEditorBlockRecord.innerBlocks.length )
			}, [ clientId ] )

			const languageVisibilityEditorDisplayLangLabel = lang === ''
				? languageVisibilityEditorI18nStrings.allLanguages
				: ( languageVisibilityEditorLanguageSelectOptions.find( ( opt ) => String( opt.value ) === String( lang ) ) || {} ).label || String( lang )
			const languageVisibilityEditorActionLabelFull = languageVisibilityEditorSanitizedMode === 'show_if'
				? languageVisibilityEditorI18nStrings.showInSelectedLanguage
				: languageVisibilityEditorI18nStrings.hideInSelectedLanguage
			const languageVisibilityEditorActionLabelWord = languageVisibilityEditorActionLabelFull && languageVisibilityEditorActionLabelFull.trim
				? languageVisibilityEditorActionLabelFull.trim().split( /\s+/ )[ 0 ]
				: languageVisibilityEditorActionLabelFull
			const languageVisibilityEditorPlaceholderTitleText = `${ languageVisibilityEditorActionLabelWord }: ${ languageVisibilityEditorDisplayLangLabel }`
			const languageVisibilityEditorBadgeLangCode = lang === ''
				? 'all'
				: lang
			const languageVisibilityEditorWrapperBlockProps = useBlockProps( {
				className: 'tepll-pll-language-visibility' + ( languageVisibilityEditorSanitizedMode === 'show_if' ? ' is-show' : ' is-hide' ) + ( ! languageVisibilityEditorHasInnerBlocks ? ' is-empty' : '' ),
				title: languageVisibilityEditorPlaceholderTitleText,
			} )

			// Placeholder text is shown when empty; tooltip works always via wrapper `title`.
			const languageVisibilityEditorInnerBlocksPlaceholder = wp.element.createElement(
				'span',
				{ className: 'tepll-pll-language-visibility-placeholder' },
				wp.element.createElement( Dashicon, {
					icon: languageVisibilityEditorSanitizedMode === 'show_if'
						? 'visibility'
						: 'hidden',
					style: { fontSize: '16px', verticalAlign: 'middle', marginRight: '6px' },
				} ),
				wp.element.createElement( 'span', null, languageVisibilityEditorBadgeLangCode )
			)

			return (
				wp.element.createElement( 'div', languageVisibilityEditorWrapperBlockProps,
					// Inspector
					wp.element.createElement( InspectorControls, {},
						wp.element.createElement( PanelBody, { title: languageVisibilityEditorI18nStrings.languageVisibility, initialOpen: true },
							wp.element.createElement( SelectControl, {
								label: languageVisibilityEditorI18nStrings.language,
								value: lang,
								onChange: ( val ) => setAttributes( {
									lang: val,
									mode: val === ''
										? 'show_if'
										: mode
								} ),
								options: languageVisibilityEditorLanguageSelectOptions
							} ),
							lang !== ''
								? wp.element.createElement( SelectControl, {
									label: languageVisibilityEditorI18nStrings.visibilityMode,
									value: mode,
									onChange: ( val ) => setAttributes( { mode: val } ),
									options: languageVisibilityEditorModeControlOptions
								} )
								: null
						)
					),
					// InnerBlocks
					wp.element.createElement( InnerBlocks, {
						renderAppender: InnerBlocks.ButtonBlockAppender,
						placeholder: languageVisibilityEditorInnerBlocksPlaceholder
					} )
				)
			)
		},
		save: () => wp.element.createElement( InnerBlocks.Content, null )
	} )
} )( window.wp )
