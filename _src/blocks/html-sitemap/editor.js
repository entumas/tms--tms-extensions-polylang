// --------------------------------------------------
//	HTML sitemap: Editor.js
// --------------------------------------------------


( ( wp ) => {
	const { registerBlockType } = wp.blocks
	const {
		PanelBody,
		ToggleControl,
		TextControl,
		SelectControl,
		Dashicon,
	} = wp.components
	const { InspectorControls, useBlockProps } = wp.blockEditor
	const { createElement: el, Fragment } = wp.element
	const { ServerSideRender } = wp.serverSideRender || {}

	const htmlSitemapEditorI18nStrings = window.tepllHtmlSitemapEditorI18n || {}
	const { useState, useRef, useEffect } = wp.element

	// Editor preview mode (auto/default)
	const htmlSitemapEditorPreviewMode = 'auto'

	const htmlSitemapEditorPublicCptDefinitions = window.tepllHtmlSitemapEditorCptDefinitions && Array.isArray( window.tepllHtmlSitemapEditorCptDefinitions )
		? window.tepllHtmlSitemapEditorCptDefinitions
		: []
	const htmlSitemapEditorBlogTaxonomyDefinitions = window.tepllHtmlSitemapEditorBlogTaxonomies && Array.isArray( window.tepllHtmlSitemapEditorBlogTaxonomies )
		? window.tepllHtmlSitemapEditorBlogTaxonomies
		: []

	/** Stable reference for core-data getEntityRecords resolution cache. */
	const htmlSitemapEditorPageListQuery = {
		per_page: -1,
		status: 'publish',
		orderby: 'menu_order',
		order: 'asc',
	}

	const htmlSitemapEditorDefaultCptRow = ( def ) => {
		const htmlSitemapEditorResolvedDefaultTaxonomySlug = def.taxonomies && def.taxonomies.length
			? def.taxonomies[ 0 ].slug
			: ''
		return {
			slug: def.slug,
			show_taxonomy: !! htmlSitemapEditorResolvedDefaultTaxonomySlug,
			show_posts: true,
			taxonomy: htmlSitemapEditorResolvedDefaultTaxonomySlug,
			parent_page_id: 0,
		}
	}

	const htmlSitemapEditorFindRowIndex = ( rows, slug ) => {
		for ( let htmlSitemapEditorRowScanIndex = 0; htmlSitemapEditorRowScanIndex < rows.length; htmlSitemapEditorRowScanIndex++ ) {
			if ( rows[ htmlSitemapEditorRowScanIndex ] && rows[ htmlSitemapEditorRowScanIndex ].slug === slug ) {
				return htmlSitemapEditorRowScanIndex
			}
		}
		return -1
	}

	const htmlSitemapEditorMakePageSelectOptions = ( firstOpt, records ) => {
		const htmlSitemapEditorPageSelectOptionList = [ firstOpt ]
		if ( records && records.length ) {
			for ( let htmlSitemapEditorPageRecordIndex = 0; htmlSitemapEditorPageRecordIndex < records.length; htmlSitemapEditorPageRecordIndex++ ) {
				const htmlSitemapEditorPageRecord = records[ htmlSitemapEditorPageRecordIndex ]
				const htmlSitemapEditorPageRecordTitle = htmlSitemapEditorPageRecord.title && htmlSitemapEditorPageRecord.title.rendered
					? htmlSitemapEditorPageRecord.title.rendered
					: '#' + htmlSitemapEditorPageRecord.id
				htmlSitemapEditorPageSelectOptionList.push( { value: String( htmlSitemapEditorPageRecord.id ), label: htmlSitemapEditorPageRecordTitle } )
			}
		}
		return htmlSitemapEditorPageSelectOptionList
	}

	const htmlSitemapEditorEnsureSelectedPageOption = ( htmlSitemapEditorSelectOptions, pageId, notInListLabel ) => {
		const htmlSitemapEditorParsedPageId = parseInt( pageId, 10 )
		if ( ! htmlSitemapEditorParsedPageId ) {
			return htmlSitemapEditorSelectOptions
		}
		const htmlSitemapEditorPageIdString = String( htmlSitemapEditorParsedPageId )
		for ( let htmlSitemapEditorOptionIndex = 0; htmlSitemapEditorOptionIndex < htmlSitemapEditorSelectOptions.length; htmlSitemapEditorOptionIndex++ ) {
			if ( htmlSitemapEditorSelectOptions[ htmlSitemapEditorOptionIndex ].value === htmlSitemapEditorPageIdString ) {
				return htmlSitemapEditorSelectOptions
			}
		}
		const htmlSitemapEditorExtendedOptions = htmlSitemapEditorSelectOptions.slice()
		htmlSitemapEditorExtendedOptions.push( { value: htmlSitemapEditorPageIdString, label: notInListLabel + ' (ID ' + htmlSitemapEditorPageIdString + ')' } )
		return htmlSitemapEditorExtendedOptions
	}

	registerBlockType( 'tepll/html-sitemap', {
		edit: ( props ) => {
			const { attributes, setAttributes } = props

			const hideEmpty        = attributes.hide_empty
			const maxDepth         = attributes.max_depth
			const pageSort         = attributes.page_sort
			const blog             = attributes.blog
			const blogMaxDepth     = attributes.blog_max_depth
			const blogShowTaxonomy = attributes.blog_show_taxonomy
			const blogShowPosts    = attributes.blog_show_posts
			const blogTaxonomy     = attributes.blog_taxonomy
			const blogParentPageId = attributes.blog_parent_page_id
			const cptConfigs       = attributes.cpt_configs && Array.isArray( attributes.cpt_configs )
				? attributes.cpt_configs
				: []

			const showListBullets = attributes.show_list_bullets !== false
			const blogTaxOptions = ( htmlSitemapEditorBlogTaxonomyDefinitions || [] ).map( ( t ) => ( {
				value: t.slug,
				label: t.label,
			} ) )
			if ( blogTaxOptions.length === 0 ) {
				blogTaxOptions.push( {
					value: '',
					label: htmlSitemapEditorI18nStrings.cptNone || '—',
				} )
			}
			const currentBlogTax = blogTaxonomy
				? String( blogTaxonomy )
				: ''

			const htmlSitemapEditorPageListResolution = wp.data.useSelect( ( select ) => {
				const htmlSitemapEditorCoreDataStore = select( 'core' )
				return {
					records: htmlSitemapEditorCoreDataStore.getEntityRecords( 'postType', 'page', htmlSitemapEditorPageListQuery ),
					resolved: htmlSitemapEditorCoreDataStore.hasFinishedResolution( 'getEntityRecords', [
						'postType',
						'page',
						htmlSitemapEditorPageListQuery,
					] ),
				}
			}, [] )

			const initialDefault = htmlSitemapEditorPreviewMode === 'default' || ! ServerSideRender

			const [ previewState, setPreviewState ] = useState( initialDefault ? 'default' : 'preview' )

			const previewStateRef = useRef( previewState )
			useEffect( () => {
				previewStateRef.current = previewState
			}, [ previewState ] )

			const htmlSitemapEditorPreviewLayoutClassName = previewState === 'default'
				? 'is-default-mode'
				: 'is-preview-mode'

			const htmlSitemapEditorRootBlockProps = useBlockProps( {
				className: 'tepll-html-sitemap is-placeholder ' + htmlSitemapEditorPreviewLayoutClassName,
			} )

			const htmlSitemapEditorPageSortSelectOptions = [
				{ value: 'menu_order', label: htmlSitemapEditorI18nStrings.sortMenuOrder },
				{ value: 'alphabetical', label: htmlSitemapEditorI18nStrings.sortAlphabetical },
			]

			const htmlSitemapEditorSetCptRows = ( nextRows ) => {
				setAttributes( { cpt_configs: nextRows } )
			}

			const htmlSitemapEditorUpdateCptRow = ( slug, patch ) => {
				const rows = cptConfigs.slice()
				const ix   = htmlSitemapEditorFindRowIndex( rows, slug )
				if ( ix < 0 ) {
					return
				}
				const merged = Object.assign( {}, rows[ ix ], patch )
				rows[ ix ] = merged
				htmlSitemapEditorSetCptRows( rows )
			}

			const htmlSitemapEditorCptInspectorPanelElements = htmlSitemapEditorPublicCptDefinitions.map( ( def ) => {
				const slug = def.slug
				const ix   = htmlSitemapEditorFindRowIndex( cptConfigs, slug )
				const cfg  = ix >= 0
					? cptConfigs[ ix ]
					: null
				const enabled = cfg !== null
				const hasTax  = def.taxonomies && def.taxonomies.length > 0
				const taxOptions = ( def.taxonomies || [] ).map( ( t ) => ( {
					value: t.slug,
					label: t.label,
				} ) )
				const currentTax = cfg && cfg.taxonomy
					? String( cfg.taxonomy )
					: ''

				return el(
					PanelBody,
					{
						title: def.label + ' (' + slug + ')',
						initialOpen: false,
						key: slug,
						className: 'tepll-html-sitemap-cpt-panel',
					},
					el( ToggleControl, {
						label: htmlSitemapEditorI18nStrings.cptInclude,
						checked: enabled,
						onChange: ( on ) => {
							if ( on ) {
								const rows = cptConfigs.slice()
								if ( htmlSitemapEditorFindRowIndex( rows, slug ) >= 0 ) {
									return
								}
								rows.push( htmlSitemapEditorDefaultCptRow( def ) )
								htmlSitemapEditorSetCptRows( rows )
							} else {
								htmlSitemapEditorSetCptRows( cptConfigs.filter( ( r ) => r.slug !== slug ) )
							}
						},
					} ),
					enabled
						? el( SelectControl, {
							label: htmlSitemapEditorI18nStrings.cptParentPage,
							value: String( cfg.parent_page_id != null
								? cfg.parent_page_id
								: 0
							),
							options: htmlSitemapEditorPageListResolution.resolved
								? htmlSitemapEditorEnsureSelectedPageOption(
									htmlSitemapEditorMakePageSelectOptions(
										{ value: '0', label: htmlSitemapEditorI18nStrings.cptParentNotSet },
										htmlSitemapEditorPageListResolution.records
									),
									cfg.parent_page_id,
									htmlSitemapEditorI18nStrings.pageNotInList
								)
								: [
									{
										value: String( cfg.parent_page_id != null ? cfg.parent_page_id : 0 ),
										label: htmlSitemapEditorI18nStrings.pagesLoading,
									},
								],
							disabled: ! htmlSitemapEditorPageListResolution.resolved,
							onChange: ( v ) => {
								const n = parseInt( v, 10 )
								htmlSitemapEditorUpdateCptRow( slug, { parent_page_id: isNaN( n ) ? 0 : n, } )
							},
						  } )
						: null,
					enabled && hasTax
						? el( SelectControl, {
							label: htmlSitemapEditorI18nStrings.cptTaxonomy,
							value: currentTax,
							options: taxOptions,
							onChange: ( v ) => {
								htmlSitemapEditorUpdateCptRow( slug, { taxonomy: v } )
							},
						  } )
						: null,
					enabled && hasTax
						? el( ToggleControl, {
							label: htmlSitemapEditorI18nStrings.cptShowTaxonomy,
							checked: !! cfg.show_taxonomy,
							onChange: ( v ) => {
								htmlSitemapEditorUpdateCptRow( slug, { show_taxonomy: v } )
							},
						  } )
						: null,
					enabled
						? el( ToggleControl, {
							label: htmlSitemapEditorI18nStrings.cptShowPosts,
							checked: !! cfg.show_posts,
							onChange: ( v ) => {
								htmlSitemapEditorUpdateCptRow( slug, { show_posts: v } )
							},
						  } )
						: null,
					enabled
						? el( TextControl, {
							label: htmlSitemapEditorI18nStrings.cptMaxDepth,
							type: 'text',
							value: cfg.max_depth !== undefined && cfg.max_depth !== null && cfg.max_depth !== ''
								? String( cfg.max_depth )
								: '',
							onChange: ( v ) => {
								if ( v.trim() === '' ) {
									const next = Object.assign( {}, cfg )
									delete next.max_depth
									const rows = cptConfigs.slice()
									rows[ ix ] = next
									htmlSitemapEditorSetCptRows( rows )
								} else {
									const n = parseInt( v, 10 )
									if ( ! isNaN( n ) ) {
										htmlSitemapEditorUpdateCptRow( slug, { max_depth: Math.max( 1, n ) } )
									}
								}
							},
						  } )
						: null
				)
			} )

			return el(
				'div',
				htmlSitemapEditorRootBlockProps,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: htmlSitemapEditorI18nStrings.general, initialOpen: true },
						el( ToggleControl, {
							label: htmlSitemapEditorI18nStrings.hideEmpty,
							checked: hideEmpty,
							onChange: ( v ) => {
								setAttributes( { hide_empty: v } )
							},
						} ),
						el( ToggleControl, {
							label: htmlSitemapEditorI18nStrings.showListBullets,
							checked: showListBullets,
							onChange: ( v ) => {
								setAttributes( { show_list_bullets: v } )
							},
						} ),
						el( SelectControl, {
							label: htmlSitemapEditorI18nStrings.pageSort,
							value: pageSort,
							options: htmlSitemapEditorPageSortSelectOptions,
							onChange: ( v ) => {
								setAttributes( { page_sort: v } )
							},
						} ),
						el( TextControl, {
							label: htmlSitemapEditorI18nStrings.maxDepth,
							type: 'number',
							min: 1,
							value: maxDepth,
							onChange: ( v ) => {
								const n = parseInt( v, 10 )
								setAttributes( { max_depth: isNaN( n ) ? 3 : Math.max( 1, n ), } )
							},
						} )
					),
					el(
						PanelBody,
						{ title: htmlSitemapEditorI18nStrings.blogPanel, initialOpen: true },
						el( ToggleControl, {
							label: htmlSitemapEditorI18nStrings.cptInclude,
							checked: blog,
							onChange: ( v ) => {
								setAttributes( { blog: v } )
							},
						} ),
						blog
							? el( SelectControl, {
								label: htmlSitemapEditorI18nStrings.cptParentPage,
								value: String( blogParentPageId != null ? blogParentPageId : 0 ),
								options: htmlSitemapEditorPageListResolution.resolved
									? htmlSitemapEditorEnsureSelectedPageOption(
										htmlSitemapEditorMakePageSelectOptions(
											{ value: '0', label: htmlSitemapEditorI18nStrings.blogPageUseReading },
											htmlSitemapEditorPageListResolution.records
										),
										blogParentPageId,
										htmlSitemapEditorI18nStrings.pageNotInList
									)
									: [
										{
											value: String( blogParentPageId != null ? blogParentPageId : 0 ),
											label: htmlSitemapEditorI18nStrings.pagesLoading,
										},
									],
								disabled: ! htmlSitemapEditorPageListResolution.resolved,
								onChange: ( v ) => {
									const n = parseInt( v, 10 )
									setAttributes( { blog_parent_page_id: isNaN( n ) ? 0 : n } )
								},
							  } )
							: null,
						blog
							? el( SelectControl, {
								label: htmlSitemapEditorI18nStrings.blogTaxonomy,
								value: currentBlogTax,
								options: blogTaxOptions,
								onChange: ( v ) => {
									setAttributes( { blog_taxonomy: v } )
								},
							  } )
							: null,
						blog
							? el( ToggleControl, {
								label: htmlSitemapEditorI18nStrings.cptShowTaxonomy,
								checked: blogShowTaxonomy,
								onChange: ( v ) => {
									setAttributes( { blog_show_taxonomy: v } )
								},
							  } )
							: null,
						blog
							? el( ToggleControl, {
								label: htmlSitemapEditorI18nStrings.cptShowPosts,
								checked: blogShowPosts,
								onChange: ( v ) => {
									setAttributes( { blog_show_posts: v } )
								},
							  } )
							: null,
						blog
							? el( TextControl, {
								label: htmlSitemapEditorI18nStrings.cptMaxDepth,
								type: 'text',
								value: blogMaxDepth || '',
								onChange: ( v ) => {
									setAttributes( { blog_max_depth: v } )
								},
							  } )
							: null
					),
					el(
						PanelBody,
						{ title: htmlSitemapEditorI18nStrings.cptPanel, initialOpen: false },
						htmlSitemapEditorPublicCptDefinitions.length === 0
							? el( 'p', { className: 'description' }, htmlSitemapEditorI18nStrings.cptNone )
							: el( Fragment, null, htmlSitemapEditorCptInspectorPanelElements )
					)
				),
				( () => {
					const htmlSitemapEditorMarkDefaultPreview = () => {
						if ( previewStateRef.current === 'default' ) return
						setTimeout( () => setPreviewState( 'default' ), 0 )
					}

					const htmlSitemapEditorRenderPreviewFallback = () => {
						htmlSitemapEditorMarkDefaultPreview()
						return el(
							'div',
							{ className: 'components-placeholder__label' },
							el( Dashicon, { icon: 'networking' } ),
							el( 'span', null, htmlSitemapEditorI18nStrings.placeholderLabel )
						)
					}

					const htmlSitemapEditorRenderPreviewLoadingPlaceholder = () => null

					const content =
						ServerSideRender && htmlSitemapEditorPreviewMode !== 'default'
							? el( ServerSideRender, {
								block: 'tepll/html-sitemap',
								attributes: attributes,
								LoadingResponsePlaceholder: htmlSitemapEditorRenderPreviewLoadingPlaceholder,
								ErrorResponsePlaceholder: htmlSitemapEditorRenderPreviewFallback,
								EmptyResponsePlaceholder: htmlSitemapEditorRenderPreviewFallback,
							} )
							: htmlSitemapEditorRenderPreviewFallback()

					return el(
						'div',
						{ className: 'is-preview' },
						content
					)
				} )()
			)
		},
		save: () => null,
	} )
} )( window.wp )