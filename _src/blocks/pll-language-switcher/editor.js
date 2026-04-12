// --------------------------------------------------
//	Pll language switcher: Editor.js
// --------------------------------------------------


import '../../js/features/pll-language-switcher/dropdown.js'


;( ( wp ) => {
	const { registerBlockType }                               = wp.blocks,
		{ PanelBody, SelectControl, ToggleControl, Dashicon } = wp.components,
		{ InspectorControls, useBlockProps }                  = wp.blockEditor
	const { ServerSideRender } = wp.serverSideRender || {}

	const languageSwitcherEditorI18nStrings = window.tepllLanguageSwitcherEditorI18n

	const { useState, useRef, useEffect } = wp.element

	// Editor preview mode (auto/default)
	const languageSwitcherEditorPreviewMode = 'auto'

	registerBlockType( 'tepll/pll-language-switcher', {
		edit: ( props ) => {
			const { attributes: {
				display,
				vertical,
				label,
				show_text,
				show_flags,
				hide_current,
				hide_if_no_translation,
				redirect_to_home,
			}, setAttributes } = props

			const initialDefault = languageSwitcherEditorPreviewMode === 'default' || ! ServerSideRender

			const [ previewState, setPreviewState ] = useState( initialDefault ? 'default' : 'preview' )

			const previewStateRef = useRef( previewState )
			useEffect( () => {
				previewStateRef.current = previewState
			}, [ previewState ] )

			// Keep these options mutually exclusive
			useEffect( () => {
				if ( redirect_to_home && hide_if_no_translation ) {
					setAttributes( { hide_if_no_translation: false } )
				}
			}, [ redirect_to_home, hide_if_no_translation ] )

			const languageSwitcherEditorPreviewLayoutClassName = previewState === 'default'
				? 'is-default-mode'
				: 'is-preview-mode'

			const languageSwitcherEditorRootBlockProps = useBlockProps( {
				className: 'tepll-pll-language-switcher is-placeholder ' + languageSwitcherEditorPreviewLayoutClassName,
			} )

			const languageSwitcherEditorDisplayControlOptions = [
				{ value: 'list', label: languageSwitcherEditorI18nStrings.displayList },
				{ value: 'dropdown', label: languageSwitcherEditorI18nStrings.displayDropdown },
			]
			const languageSwitcherEditorLabelControlOptions = [
				{ value: 'code', label: languageSwitcherEditorI18nStrings.labelCode },
				{ value: 'name', label: languageSwitcherEditorI18nStrings.labelName },
			]

			const languageSwitcherEditorMarkDefaultPreview = () => {
				if ( previewStateRef.current === 'default' ) return
				setTimeout( () => setPreviewState( 'default' ), 0 )
			}

			const languageSwitcherEditorRenderPreviewFallback = () => {
				languageSwitcherEditorMarkDefaultPreview()

				return wp.element.createElement(
					'div',
					{ className: 'components-placeholder__label' },
					wp.element.createElement( Dashicon, { icon: 'translation' } ),
					wp.element.createElement( 'label', null, languageSwitcherEditorI18nStrings.panelTitle )
				)
			}

			const preview = ServerSideRender
				? wp.element.createElement(
					'div',
					{ className: 'is-preview' },
					languageSwitcherEditorPreviewMode === 'default'
						? languageSwitcherEditorRenderPreviewFallback()
						: wp.element.createElement( ServerSideRender, {
							block: 'tepll/pll-language-switcher',
							attributes: props.attributes,
							LoadingResponsePlaceholder: () => null,
							ErrorResponsePlaceholder: languageSwitcherEditorRenderPreviewFallback,
							EmptyResponsePlaceholder: languageSwitcherEditorRenderPreviewFallback,
						} )
				)
				: languageSwitcherEditorRenderPreviewFallback()

			return (
				wp.element.createElement( 'div', languageSwitcherEditorRootBlockProps,
					wp.element.createElement( InspectorControls, {},
						wp.element.createElement( PanelBody, { title: languageSwitcherEditorI18nStrings.panelTitle, initialOpen: true },
							wp.element.createElement( SelectControl, {
								label: languageSwitcherEditorI18nStrings.displayLabel,
								value: display,
								onChange: ( val ) => setAttributes( {
									display: val,
									vertical: val === 'list'
										? vertical
										: false,
									hide_current: val === 'dropdown'
										? false
										: hide_current,
								} ),
								options: languageSwitcherEditorDisplayControlOptions,
							} ),
							display === 'list'
								? wp.element.createElement( ToggleControl, {
									label: languageSwitcherEditorI18nStrings.vertical,
									checked: vertical,
									onChange: ( val ) => setAttributes( { vertical: val } ),
								} )
								: null,
							wp.element.createElement( ToggleControl, {
								label: languageSwitcherEditorI18nStrings.showText,
								checked: show_text,
								onChange: ( val ) => setAttributes( { show_text: val } ),
							} ),
							show_text
								? wp.element.createElement( SelectControl, {
									label: languageSwitcherEditorI18nStrings.textLabel,
									value: label,
									onChange: ( val ) => setAttributes( { label: val } ),
									options: languageSwitcherEditorLabelControlOptions,
								} )
								: null,
							wp.element.createElement( ToggleControl, {
								label: languageSwitcherEditorI18nStrings.showFlags,
								checked: show_flags,
								onChange: ( val ) => setAttributes( { show_flags: val } ),
							} ),
							display !== 'dropdown'
								? wp.element.createElement( ToggleControl, {
									label: languageSwitcherEditorI18nStrings.hideCurrent,
									checked: hide_current,
									onChange: ( val ) => setAttributes( { hide_current: val } ),
								} )
								: null,
							! redirect_to_home
								? wp.element.createElement( ToggleControl, {
									label: languageSwitcherEditorI18nStrings.hideIfNoTranslation,
									checked: hide_if_no_translation,
									onChange: ( val ) => setAttributes( {
										hide_if_no_translation: val,
										redirect_to_home: false,
									} ),
								} )
								: null,
							! hide_if_no_translation
								? wp.element.createElement( ToggleControl, {
									label: languageSwitcherEditorI18nStrings.redirectToHome,
									checked: redirect_to_home,
									onChange: ( val ) => setAttributes( {
										redirect_to_home: val,
										hide_if_no_translation: false,
									} ),
								} )
								: null
						)
					),
					preview
				)
			)
		},
		save: () => null,
	} )
} )( window.wp )