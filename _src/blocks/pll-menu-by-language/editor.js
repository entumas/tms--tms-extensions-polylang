// --------------------------------------------------
// Pll menu by language: Editor.js
// --------------------------------------------------


( ( wp ) => {
	const { registerBlockType, createBlock } = wp.blocks
	const { PanelBody, SelectControl } = wp.components
	const { InspectorControls, InnerBlocks, useBlockProps } = wp.blockEditor
	const { useSelect, useDispatch } = wp.data
	const { useMemo, useEffect } = wp.element

	const cfg = window.tepllMenuByLanguageEditor || {}
	const languages = Array.isArray( cfg.languages ) ? cfg.languages : []
	const el = wp.element.createElement

	const TEPLL_MENU_BY_LANGUAGE_PARENT_SVG_PATH = 'M12 4c-4.4 0-8 3.6-8 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm0 14.5c-3.6 0-6.5-2.9-6.5-6.5S8.4 5.5 12 5.5s6.5 2.9 6.5 6.5-2.9 6.5-6.5 6.5zM9 16l4.5-3L15 8.4l-4.5 3L9 16z'
	const TEPLL_MENU_BY_LANGUAGE_SLOT_SVG_PATH = 'M21.3 10.8l-5.6-5.6c-.7-.7-1.8-.7-2.5 0l-5.6 5.6c-.7.7-.7 1.8 0 2.5l5.6 5.6c.3.3.8.5 1.2.5s.9-.2 1.2-.5l5.6-5.6c.8-.7.8-1.9.1-2.5zm-17.6 1L10 5.5l-1-1-6.3 6.3c-.7.7-.7 1.8 0 2.5L9 19.5l1.1-1.1-6.3-6.3c-.2 0-.2-.2-.1-.3z'

	const tepllMenuByLanguageIconSvg = ( pathD ) => el(
		'svg',
		{ xmlns: 'http://www.w3.org/2000/svg', viewBox: '0 0 24 24', 'aria-hidden': 'true' },
		el( 'path', { fill: 'currentColor', d: pathD } )
	)

	const tepllMenuByLanguageSlotBlockIcon = {
		foreground: '#1e1e1e',
		src: tepllMenuByLanguageIconSvg( TEPLL_MENU_BY_LANGUAGE_SLOT_SVG_PATH )
	}

	const tepllMenuByLanguageBlockIcon = {
		foreground: '#1e1e1e',
		src: tepllMenuByLanguageIconSvg( TEPLL_MENU_BY_LANGUAGE_PARENT_SVG_PATH )
	}

	const tepllMenuByLanguageSmallestNavId = ( records ) => {
		if ( ! records || ! records.length ) return null
		let min = null
		for ( let i = 0; i < records.length; i++ ) {
			const id = Number( records[ i ].id )
			if ( ! id || id < 1 ) continue
			if ( min === null || id < min ) min = id
		}
		return min
	}

	const tepllMenuByLanguageNavMenuSelectOptions = ( records ) => {
		if ( ! records || ! records.length ) return []
		const sorted = [ ...records ].sort( ( a, b ) => {
			const ta = ( ( a.title && ( a.title.rendered || a.title.raw ) ) || '' ).toString().toLocaleLowerCase()
			const tb = ( ( b.title && ( b.title.rendered || b.title.raw ) ) || '' ).toString().toLocaleLowerCase()
			return ta.localeCompare( tb )
		} )
		return sorted.map( ( post ) => ( {
			value: String( post.id ),
			label: ( ( post.title && ( post.title.rendered || post.title.raw ) ) || ( '#' + post.id ) ).toString()
		} ) )
	}

	const tepllMenuByLanguageResolveServerSideRender = () => {
		const raw = wp.serverSideRender
		return raw && raw.default ? raw.default : raw
	}

	const tepllMenuByLanguageExpectedSlugsKey = languages.map( ( l ) => l.slug ).join( '|' )

	const tepllMenuByLanguageNavigationClientIdForSlug = ( parentClientId, langSlug ) => {
		const editor = wp.data.select( 'core/block-editor' )
		if ( ! editor || typeof editor.getBlockOrder !== 'function' || typeof editor.getBlock !== 'function' ) return null
		const order = editor.getBlockOrder( parentClientId )
		if ( ! Array.isArray( order ) ) return null
		for ( let i = 0; i < order.length; i++ ) {
			const blk = editor.getBlock( order[ i ] )
			if ( ! blk || blk.name !== 'tepll/pll-menu-by-language-slot' ) continue
			if ( String( blk.attributes?.lang || '' ) !== String( langSlug ) ) continue
			const nav = blk.innerBlocks && blk.innerBlocks[0]
			return nav && nav.clientId ? nav.clientId : null
		}
		return null
	}

	registerBlockType( 'tepll/pll-menu-by-language-slot', {
		icon: tepllMenuByLanguageSlotBlockIcon,
		edit: ( props ) => {
			const { attributes: { lang } } = props
			const blockProps = useBlockProps( {
				className: 'tepll-pll-menu-by-language-slot'
			} )
			return wp.element.createElement(
				'div',
				blockProps,
				wp.element.createElement(
					'div',
					{ className: 'tepll-pll-menu-by-language-slot-label', 'aria-hidden': 'true' },
					lang || ''
				),
				wp.element.createElement( InnerBlocks, {
					allowedBlocks: [ 'core/navigation' ],
					template: [ [ 'core/navigation', {} ] ],
					templateLock: false
				} )
			)
		},
		save: () => wp.element.createElement( InnerBlocks.Content, null )
	} )

	registerBlockType( 'tepll/pll-menu-by-language', {
		icon: tepllMenuByLanguageBlockIcon,
		edit: ( props ) => {
			const { clientId } = props
			const { updateBlockAttributes, replaceInnerBlocks } = useDispatch( 'core/block-editor' )

			const innerTemplate = useMemo( () => {
				return languages.map( ( l ) => [
					'tepll/pll-menu-by-language-slot',
					{ lang: l.slug },
					[ [ 'core/navigation', {} ] ]
				] )
			}, [ tepllMenuByLanguageExpectedSlugsKey ] )

			const navigationRecords = useSelect( ( select ) => {
				const core = select( 'core' )
				if ( ! core || typeof core.getEntityRecords !== 'function' ) return null
				return core.getEntityRecords( 'postType', 'wp_navigation', {
					per_page: -1,
					status: 'publish'
				} )
			}, [] )

			const navigationMenuOptions = useMemo(
				() => tepllMenuByLanguageNavMenuSelectOptions( navigationRecords ),
				[ navigationRecords ]
			)

			const refsByLang = useSelect( ( select ) => {
				const editor = select( 'core/block-editor' )
				const map = {}
				if ( ! clientId || ! editor || typeof editor.getBlockOrder !== 'function' ) return map
				const order = editor.getBlockOrder( clientId )
				if ( ! Array.isArray( order ) ) return map
				for ( let i = 0; i < order.length; i++ ) {
					const blk = editor.getBlock( order[ i ] )
					if ( ! blk || blk.name !== 'tepll/pll-menu-by-language-slot' ) continue
					const slug = blk.attributes && blk.attributes.lang ? String( blk.attributes.lang ) : ''
					if ( ! slug ) continue
					const nav = blk.innerBlocks && blk.innerBlocks[0]
					const r = nav && nav.attributes ? nav.attributes.ref : null
					map[ slug ] = r && Number( r ) > 0 ? String( r ) : ''
				}
				return map
			}, [ clientId ] )

			const previewLang = cfg.currentLang || ''

			const previewForCanvas = useSelect( ( select ) => {
				const editor = select( 'core/block-editor' )
				if ( ! clientId || typeof editor.getBlockOrder !== 'function' ) {
					return { navAttributes: null, hasRef: false }
				}
				const order = editor.getBlockOrder( clientId )
				if ( ! Array.isArray( order ) ) return { navAttributes: null, hasRef: false }
				let slotBlk = null
				if ( previewLang ) {
					for ( let i = 0; i < order.length; i++ ) {
						const b = editor.getBlock( order[ i ] )
						if ( ! b || b.name !== 'tepll/pll-menu-by-language-slot' ) continue
						if ( String( b.attributes?.lang || '' ) !== String( previewLang ) ) continue
						slotBlk = b
						break
					}
				}
				if ( ! slotBlk ) {
					for ( let i = 0; i < order.length; i++ ) {
						const b = editor.getBlock( order[ i ] )
						if ( b && b.name === 'tepll/pll-menu-by-language-slot' ) {
							slotBlk = b
							break
						}
					}
				}
				const nav = slotBlk && slotBlk.innerBlocks && slotBlk.innerBlocks[0]
				if ( ! nav || nav.name !== 'core/navigation' ) return { navAttributes: null, hasRef: false }
				const r = nav.attributes && nav.attributes.ref
				const hasRef = r && Number( r ) > 0
				return { navAttributes: nav.attributes || {}, hasRef }
			}, [ clientId, previewLang ] )

			useEffect( () => {
				if ( languages.length < 1 || ! clientId ) return
				const minId = Array.isArray( navigationRecords ) && navigationRecords.length
					? tepllMenuByLanguageSmallestNavId( navigationRecords )
					: null
				const editorSelect = wp.data.select( 'core/block-editor' )
				const order = editorSelect.getBlockOrder( clientId )
				const slotBlocks = []
				if ( Array.isArray( order ) ) {
					for ( let i = 0; i < order.length; i++ ) {
						const b = editorSelect.getBlock( order[ i ] )
						if ( b && b.name === 'tepll/pll-menu-by-language-slot' ) slotBlocks.push( b )
					}
				}
				const expectedSlugs = languages.map( ( l ) => l.slug )
				const slotSlugs = slotBlocks.map( ( s ) => ( s.attributes && s.attributes.lang ) ? String( s.attributes.lang ) : '' )
				const slotsMatch = expectedSlugs.length === slotSlugs.length
					&& expectedSlugs.every( ( slug, i ) => slug === slotSlugs[ i ] )
				if ( ! slotsMatch ) {
					const navAttrs = minId ? { ref: minId } : {}
					const newBlocks = expectedSlugs.map( ( slug ) => {
						const existing = slotBlocks.find( ( s ) => s.attributes.lang === slug )
						if ( existing ) {
							const tree = editorSelect.getBlock( existing.clientId )
							return tree || createBlock( 'tepll/pll-menu-by-language-slot', { lang: slug }, [
								createBlock( 'core/navigation', navAttrs )
							] )
						}
						return createBlock( 'tepll/pll-menu-by-language-slot', { lang: slug }, [
							createBlock( 'core/navigation', navAttrs )
						] )
					} )
					replaceInnerBlocks( clientId, newBlocks, false )
					return
				}
				if ( ! minId ) return
				for ( let i = 0; i < order.length; i++ ) {
					const blk = editorSelect.getBlock( order[ i ] )
					if ( ! blk || blk.name !== 'tepll/pll-menu-by-language-slot' ) continue
					const nav = blk.innerBlocks && blk.innerBlocks[0]
					if ( ! nav || nav.name !== 'core/navigation' || ! nav.clientId ) continue
					const existingRef = nav.attributes && nav.attributes.ref
					if ( existingRef != null && Number( existingRef ) > 0 ) continue
					updateBlockAttributes( nav.clientId, { ref: minId } )
				}
			}, [ clientId, tepllMenuByLanguageExpectedSlugsKey, navigationRecords, replaceInnerBlocks, updateBlockAttributes ] )

			const hasPreviewRef = previewForCanvas.hasRef
			const ServerSideRender = tepllMenuByLanguageResolveServerSideRender()

			const tepllMenuByLanguageEditorWrapperBlockProps = useBlockProps( {
				className: 'tepll-pll-menu-by-language' + ( ! hasPreviewRef ? ' is-empty' : '' ),
				title: cfg.menuBlockTitle || ''
			} )

			const tepllMenuByLanguageOnNavChange = ( langSlug, value ) => {
				const navId = tepllMenuByLanguageNavigationClientIdForSlug( clientId, langSlug )
				if ( ! navId ) return
				const n = parseInt( value, 10 )
				if ( ! isNaN( n ) && n > 0 ) {
					updateBlockAttributes( navId, { ref: n } )
				}
			}

			const minNavIdStr = ( () => {
				const mid = tepllMenuByLanguageSmallestNavId(
					Array.isArray( navigationRecords ) ? navigationRecords : null
				)
				return mid != null ? String( mid ) : ''
			} )()

			const navigationMenusLoaded = Array.isArray( navigationRecords )
			const inspectorControlsChildren = ! navigationMenusLoaded
				? null
				: navigationMenuOptions.length < 1
					? wp.element.createElement( 'p', null, cfg.noNavigationMenus || '' )
					: languages.map( ( langRow ) => {
						const slug = String( langRow.slug )
						const rawRef = Object.prototype.hasOwnProperty.call( refsByLang, slug )
							? refsByLang[ slug ]
							: ''
						const strRef = rawRef != null && rawRef !== '' ? String( rawRef ) : ''
						const selectValue = strRef && navigationMenuOptions.some( ( o ) => o.value === strRef )
							? strRef
							: ( minNavIdStr && navigationMenuOptions.some( ( o ) => o.value === minNavIdStr ) )
								? minNavIdStr
								: navigationMenuOptions[ 0 ].value
						return wp.element.createElement( SelectControl, {
							key: slug,
							id: `tepll-pll-menu-by-language-nav-${ clientId }-${ slug }`,
							label: langRow.name,
							value: selectValue,
							options: navigationMenuOptions,
							onChange: ( val ) => tepllMenuByLanguageOnNavChange( slug, val )
						} )
					} )

			let canvasInner = null
			if ( hasPreviewRef && ServerSideRender ) {
				canvasInner = wp.element.createElement( ServerSideRender, {
					block: 'core/navigation',
					attributes: previewForCanvas.navAttributes || {}
				} )
			}

			const previewClassNames = [ 'tepll-pll-menu-by-language-preview' ]
			if ( hasPreviewRef ) previewClassNames.push( 'has-nav-preview' )
			const previewClassName = previewClassNames.join( ' ' )

			if ( languages.length < 1 ) {
				return wp.element.createElement(
					'div',
					tepllMenuByLanguageEditorWrapperBlockProps,
					wp.element.createElement( InspectorControls, {},
						wp.element.createElement( PanelBody, { title: cfg.languageAssignments, initialOpen: true },
							wp.element.createElement( 'p', null, cfg.noLanguages || '' )
						)
					),
					wp.element.createElement( 'div', { className: previewClassName } )
				)
			}

			return wp.element.createElement(
				'div',
				tepllMenuByLanguageEditorWrapperBlockProps,
				wp.element.createElement( InspectorControls, {},
					wp.element.createElement(
						PanelBody,
						{ title: cfg.languageAssignments, initialOpen: true },
						inspectorControlsChildren
					)
				),
				wp.element.createElement( 'div', { className: previewClassName }, canvasInner ),
				wp.element.createElement(
					'div',
					{ className: 'tepll-pll-menu-by-language-inner-structure' },
					wp.element.createElement( InnerBlocks, {
						allowedBlocks: [ 'tepll/pll-menu-by-language-slot' ],
						template: innerTemplate,
						templateLock: 'all',
						renderAppender: false
					} )
				)
			)
		},
		save: () => wp.element.createElement( InnerBlocks.Content, null )
	} )
} )( window.wp )