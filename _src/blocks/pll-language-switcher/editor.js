// --------------------------------------------------
//	Pll language switcher: Editor.js
// --------------------------------------------------


import '../../js/features/pll-language-switcher/dropdown.js'


;( ( wp ) => {
	const { registerBlockType }                               = wp.blocks,
		{ PanelBody, SelectControl, ToggleControl, Dashicon } = wp.components,
		{ InspectorControls, useBlockProps }                  = wp.blockEditor
	const { ServerSideRender } = wp.serverSideRender || {}

	const tepllLanguageSwitcherEditorI18nStrings = window.tepllLanguageSwitcherEditorI18n

	const { useState, useRef, useEffect } = wp.element

	// Editor preview mode (auto/default)
	const tepllLanguageSwitcherEditorPreviewMode = 'auto'

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

			const initialDefault = tepllLanguageSwitcherEditorPreviewMode === 'default' || ! ServerSideRender

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

			const tepllLanguageSwitcherEditorPreviewLayoutClassName = previewState === 'default'
				? 'is-default-mode'
				: 'is-preview-mode'

			const tepllLanguageSwitcherEditorRootBlockProps = useBlockProps( {
				className: 'tepll-pll-language-switcher is-placeholder ' + tepllLanguageSwitcherEditorPreviewLayoutClassName,
			} )

			const tepllLanguageSwitcherEditorDisplayControlOptions = [
				{ value: 'list', label: tepllLanguageSwitcherEditorI18nStrings.displayList },
				{ value: 'dropdown', label: tepllLanguageSwitcherEditorI18nStrings.displayDropdown },
			]
			const tepllLanguageSwitcherEditorLabelControlOptions = [
				{ value: 'code', label: tepllLanguageSwitcherEditorI18nStrings.labelCode },
				{ value: 'name', label: tepllLanguageSwitcherEditorI18nStrings.labelName },
			]

			const tepllLanguageSwitcherEditorMarkDefaultPreview = () => {
				if ( previewStateRef.current === 'default' ) return
				setTimeout( () => setPreviewState( 'default' ), 0 )
			}

			const tepllLanguageSwitcherEditorRenderPreviewFallback = () => {
				tepllLanguageSwitcherEditorMarkDefaultPreview()

				return wp.element.createElement(
					'div',
					{ className: 'components-placeholder__label' },
					wp.element.createElement( Dashicon, { icon: 'translation' } ),
					wp.element.createElement( 'label', null, tepllLanguageSwitcherEditorI18nStrings.panelTitle )
				)
			}

			const preview = ServerSideRender
				? wp.element.createElement(
					'div',
					{ className: 'is-preview' },
					tepllLanguageSwitcherEditorPreviewMode === 'default'
						? tepllLanguageSwitcherEditorRenderPreviewFallback()
						: wp.element.createElement( ServerSideRender, {
							block: 'tepll/pll-language-switcher',
							attributes: props.attributes,
							LoadingResponsePlaceholder: () => null,
							ErrorResponsePlaceholder: tepllLanguageSwitcherEditorRenderPreviewFallback,
							EmptyResponsePlaceholder: tepllLanguageSwitcherEditorRenderPreviewFallback,
						} )
				)
				: tepllLanguageSwitcherEditorRenderPreviewFallback()

			return (
				wp.element.createElement( 'div', tepllLanguageSwitcherEditorRootBlockProps,
					wp.element.createElement( InspectorControls, {},
						wp.element.createElement( PanelBody, { title: tepllLanguageSwitcherEditorI18nStrings.panelTitle, initialOpen: true },
							wp.element.createElement( SelectControl, {
								label: tepllLanguageSwitcherEditorI18nStrings.displayLabel,
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
								options: tepllLanguageSwitcherEditorDisplayControlOptions,
							} ),
							display === 'list'
								? wp.element.createElement( ToggleControl, {
									label: tepllLanguageSwitcherEditorI18nStrings.vertical,
									checked: vertical,
									onChange: ( val ) => setAttributes( { vertical: val } ),
								} )
								: null,
							wp.element.createElement( ToggleControl, {
								label: tepllLanguageSwitcherEditorI18nStrings.showText,
								checked: show_text,
								onChange: ( val ) => setAttributes( { show_text: val } ),
							} ),
							show_text
								? wp.element.createElement( SelectControl, {
									label: tepllLanguageSwitcherEditorI18nStrings.textLabel,
									value: label,
									onChange: ( val ) => setAttributes( { label: val } ),
									options: tepllLanguageSwitcherEditorLabelControlOptions,
								} )
								: null,
							wp.element.createElement( ToggleControl, {
								label: tepllLanguageSwitcherEditorI18nStrings.showFlags,
								checked: show_flags,
								onChange: ( val ) => setAttributes( { show_flags: val } ),
							} ),
							display !== 'dropdown'
								? wp.element.createElement( ToggleControl, {
									label: tepllLanguageSwitcherEditorI18nStrings.hideCurrent,
									checked: hide_current,
									onChange: ( val ) => setAttributes( { hide_current: val } ),
								} )
								: null,
							! redirect_to_home
								? wp.element.createElement( ToggleControl, {
									label: tepllLanguageSwitcherEditorI18nStrings.hideIfNoTranslation,
									checked: hide_if_no_translation,
									onChange: ( val ) => setAttributes( {
										hide_if_no_translation: val,
										redirect_to_home: false,
									} ),
								} )
								: null,
							! hide_if_no_translation
								? wp.element.createElement( ToggleControl, {
									label: tepllLanguageSwitcherEditorI18nStrings.redirectToHome,
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