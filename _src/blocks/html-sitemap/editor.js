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
	const { createElement: el, Fragment }      = wp.element
	const { ServerSideRender }                 = wp.serverSideRender || {}

	const tepllHtmlSitemapEditorI18nStrings = window.tepllHtmlSitemapEditorI18n || {}

	// Editor preview mode (auto/default)
	const tepllHtmlSitemapEditorPreviewMode = 'auto'

	const tepllHtmlSitemapEditorPublicCptDefinitions = window.tepllHtmlSitemapEditorCptDefinitions && Array.isArray( window.tepllHtmlSitemapEditorCptDefinitions )
		? window.tepllHtmlSitemapEditorCptDefinitions
		: []
	const tepllHtmlSitemapEditorBlogTaxonomyDefinitions = window.tepllHtmlSitemapEditorBlogTaxonomies && Array.isArray( window.tepllHtmlSitemapEditorBlogTaxonomies )
		? window.tepllHtmlSitemapEditorBlogTaxonomies
		: []

	/** Stable reference for core-data getEntityRecords resolution cache. */
	const tepllHtmlSitemapEditorPageListQuery = {
		per_page: -1,
		status: 'publish',
		orderby: 'menu_order',
		order: 'asc',
	}

	const tepllHtmlSitemapEditorDefaultCptRow = ( def ) => {
		const tepllHtmlSitemapEditorResolvedDefaultTaxonomySlug = def.taxonomies && def.taxonomies.length
			? def.taxonomies[ 0 ].slug
			: ''
		return {
			slug: def.slug,
			show_taxonomy: !! tepllHtmlSitemapEditorResolvedDefaultTaxonomySlug,
			show_posts: true,
			taxonomy: tepllHtmlSitemapEditorResolvedDefaultTaxonomySlug,
			parent_page_id: 0,
		}
	}

	const tepllHtmlSitemapEditorFindRowIndex = ( rows, slug ) => {
		for ( let tepllHtmlSitemapEditorRowScanIndex = 0; tepllHtmlSitemapEditorRowScanIndex < rows.length; tepllHtmlSitemapEditorRowScanIndex++ ) {
			if ( rows[ tepllHtmlSitemapEditorRowScanIndex ] && rows[ tepllHtmlSitemapEditorRowScanIndex ].slug === slug ) {
				return tepllHtmlSitemapEditorRowScanIndex
			}
		}
		return -1
	}

	const tepllHtmlSitemapEditorMakePageSelectOptions = ( firstOpt, records ) => {
		const tepllHtmlSitemapEditorPageSelectOptionList = [ firstOpt ]
		if ( records && records.length ) {
			for ( let tepllHtmlSitemapEditorPageRecordIndex = 0; tepllHtmlSitemapEditorPageRecordIndex < records.length; tepllHtmlSitemapEditorPageRecordIndex++ ) {
				const tepllHtmlSitemapEditorPageRecord = records[ tepllHtmlSitemapEditorPageRecordIndex ]
				const tepllHtmlSitemapEditorPageRecordTitle = tepllHtmlSitemapEditorPageRecord.title && tepllHtmlSitemapEditorPageRecord.title.rendered
					? tepllHtmlSitemapEditorPageRecord.title.rendered
					: '#' + tepllHtmlSitemapEditorPageRecord.id
				tepllHtmlSitemapEditorPageSelectOptionList.push( { value: String( tepllHtmlSitemapEditorPageRecord.id ), label: tepllHtmlSitemapEditorPageRecordTitle } )
			}
		}
		return tepllHtmlSitemapEditorPageSelectOptionList
	}

	const tepllHtmlSitemapEditorEnsureSelectedPageOption = ( tepllHtmlSitemapEditorSelectOptions, pageId, notInListLabel ) => {
		const tepllHtmlSitemapEditorParsedPageId = parseInt( pageId, 10 )
		if ( ! tepllHtmlSitemapEditorParsedPageId ) {
			return tepllHtmlSitemapEditorSelectOptions
		}
		const tepllHtmlSitemapEditorPageIdString = String( tepllHtmlSitemapEditorParsedPageId )
		for ( let tepllHtmlSitemapEditorOptionIndex = 0; tepllHtmlSitemapEditorOptionIndex < tepllHtmlSitemapEditorSelectOptions.length; tepllHtmlSitemapEditorOptionIndex++ ) {
			if ( tepllHtmlSitemapEditorSelectOptions[ tepllHtmlSitemapEditorOptionIndex ].value === tepllHtmlSitemapEditorPageIdString ) {
				return tepllHtmlSitemapEditorSelectOptions
			}
		}
		const tepllHtmlSitemapEditorExtendedOptions = tepllHtmlSitemapEditorSelectOptions.slice()
		tepllHtmlSitemapEditorExtendedOptions.push( { value: tepllHtmlSitemapEditorPageIdString, label: notInListLabel + ' (ID ' + tepllHtmlSitemapEditorPageIdString + ')' } )
		return tepllHtmlSitemapEditorExtendedOptions
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
			const blogTaxOptions = ( tepllHtmlSitemapEditorBlogTaxonomyDefinitions || [] ).map( ( t ) => ( {
				value: t.slug,
				label: t.label,
			} ) )
			if ( blogTaxOptions.length === 0 ) {
				blogTaxOptions.push( {
					value: '',
					label: tepllHtmlSitemapEditorI18nStrings.cptNone || '—',
				} )
			}
			const currentBlogTax = blogTaxonomy
				? String( blogTaxonomy )
				: ''

			const tepllHtmlSitemapEditorPageListResolution = wp.data.useSelect( ( select ) => {
				const tepllHtmlSitemapEditorCoreDataStore = select( 'core' )
				return {
					records: tepllHtmlSitemapEditorCoreDataStore.getEntityRecords( 'postType', 'page', tepllHtmlSitemapEditorPageListQuery ),
					resolved: tepllHtmlSitemapEditorCoreDataStore.hasFinishedResolution( 'getEntityRecords', [
						'postType',
						'page',
						tepllHtmlSitemapEditorPageListQuery,
					] ),
				}
			}, [] )

			const tepllHtmlSitemapEditorPreviewLayoutClassName =
				tepllHtmlSitemapEditorPreviewMode === 'default' || ! ServerSideRender
					? 'is-default-mode'
					: 'is-preview-mode'

			const tepllHtmlSitemapEditorRootBlockProps = useBlockProps( {
				className: 'tepll-html-sitemap is-placeholder ' + tepllHtmlSitemapEditorPreviewLayoutClassName,
			} )

			const tepllHtmlSitemapEditorPageSortSelectOptions = [
				{ value: 'menu_order', label: tepllHtmlSitemapEditorI18nStrings.sortMenuOrder },
				{ value: 'alphabetical', label: tepllHtmlSitemapEditorI18nStrings.sortAlphabetical },
			]

			const tepllHtmlSitemapEditorSetCptRows = ( nextRows ) => {
				setAttributes( { cpt_configs: nextRows } )
			}

			const tepllHtmlSitemapEditorUpdateCptRow = ( slug, patch ) => {
				const rows = cptConfigs.slice()
				const ix   = tepllHtmlSitemapEditorFindRowIndex( rows, slug )
				if ( ix < 0 ) {
					return
				}
				const merged = Object.assign( {}, rows[ ix ], patch )
				rows[ ix ] = merged
				tepllHtmlSitemapEditorSetCptRows( rows )
			}

			const tepllHtmlSitemapEditorCptInspectorPanelElements = tepllHtmlSitemapEditorPublicCptDefinitions.map( ( def ) => {
				const slug = def.slug
				const ix   = tepllHtmlSitemapEditorFindRowIndex( cptConfigs, slug )
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
						label: tepllHtmlSitemapEditorI18nStrings.cptInclude,
						checked: enabled,
						onChange: ( on ) => {
							if ( on ) {
								const rows = cptConfigs.slice()
								if ( tepllHtmlSitemapEditorFindRowIndex( rows, slug ) >= 0 ) {
									return
								}
								rows.push( tepllHtmlSitemapEditorDefaultCptRow( def ) )
								tepllHtmlSitemapEditorSetCptRows( rows )
							} else {
								tepllHtmlSitemapEditorSetCptRows( cptConfigs.filter( ( r ) => r.slug !== slug ) )
							}
						},
					} ),
					enabled
						? el( SelectControl, {
							label: tepllHtmlSitemapEditorI18nStrings.cptParentPage,
							value: String( cfg.parent_page_id != null
								? cfg.parent_page_id
								: 0
							),
							options: tepllHtmlSitemapEditorPageListResolution.resolved
								? tepllHtmlSitemapEditorEnsureSelectedPageOption(
									tepllHtmlSitemapEditorMakePageSelectOptions(
										{ value: '0', label: tepllHtmlSitemapEditorI18nStrings.cptParentNotSet },
										tepllHtmlSitemapEditorPageListResolution.records
									),
									cfg.parent_page_id,
									tepllHtmlSitemapEditorI18nStrings.pageNotInList
								)
								: [
									{
										value: String( cfg.parent_page_id != null ? cfg.parent_page_id : 0 ),
										label: tepllHtmlSitemapEditorI18nStrings.pagesLoading,
									},
								],
							disabled: ! tepllHtmlSitemapEditorPageListResolution.resolved,
							onChange: ( v ) => {
								const n = parseInt( v, 10 )
								tepllHtmlSitemapEditorUpdateCptRow( slug, { parent_page_id: isNaN( n ) ? 0 : n, } )
							},
						  } )
						: null,
					enabled && hasTax
						? el( SelectControl, {
							label: tepllHtmlSitemapEditorI18nStrings.cptTaxonomy,
							value: currentTax,
							options: taxOptions,
							onChange: ( v ) => {
								tepllHtmlSitemapEditorUpdateCptRow( slug, { taxonomy: v } )
							},
						  } )
						: null,
					enabled && hasTax
						? el( ToggleControl, {
							label: tepllHtmlSitemapEditorI18nStrings.cptShowTaxonomy,
							checked: !! cfg.show_taxonomy,
							onChange: ( v ) => {
								tepllHtmlSitemapEditorUpdateCptRow( slug, { show_taxonomy: v } )
							},
						  } )
						: null,
					enabled
						? el( ToggleControl, {
							label: tepllHtmlSitemapEditorI18nStrings.cptShowPosts,
							checked: !! cfg.show_posts,
							onChange: ( v ) => {
								tepllHtmlSitemapEditorUpdateCptRow( slug, { show_posts: v } )
							},
						  } )
						: null,
					enabled
						? el( TextControl, {
							label: tepllHtmlSitemapEditorI18nStrings.cptMaxDepth,
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
									tepllHtmlSitemapEditorSetCptRows( rows )
								} else {
									const n = parseInt( v, 10 )
									if ( ! isNaN( n ) ) {
										tepllHtmlSitemapEditorUpdateCptRow( slug, { max_depth: Math.max( 1, n ) } )
									}
								}
							},
						  } )
						: null
				)
			} )

			return el(
				'div',
				tepllHtmlSitemapEditorRootBlockProps,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: tepllHtmlSitemapEditorI18nStrings.general, initialOpen: true },
						el( ToggleControl, {
							label: tepllHtmlSitemapEditorI18nStrings.hideEmpty,
							checked: hideEmpty,
							onChange: ( v ) => {
								setAttributes( { hide_empty: v } )
							},
						} ),
						el( ToggleControl, {
							label: tepllHtmlSitemapEditorI18nStrings.showListBullets,
							checked: showListBullets,
							onChange: ( v ) => {
								setAttributes( { show_list_bullets: v } )
							},
						} ),
						el( SelectControl, {
							label: tepllHtmlSitemapEditorI18nStrings.pageSort,
							value: pageSort,
							options: tepllHtmlSitemapEditorPageSortSelectOptions,
							onChange: ( v ) => {
								setAttributes( { page_sort: v } )
							},
						} ),
						el( TextControl, {
							label: tepllHtmlSitemapEditorI18nStrings.maxDepth,
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
						{ title: tepllHtmlSitemapEditorI18nStrings.blogPanel, initialOpen: true },
						el( ToggleControl, {
							label: tepllHtmlSitemapEditorI18nStrings.cptInclude,
							checked: blog,
							onChange: ( v ) => {
								setAttributes( { blog: v } )
							},
						} ),
						blog
							? el( SelectControl, {
								label: tepllHtmlSitemapEditorI18nStrings.cptParentPage,
								value: String( blogParentPageId != null ? blogParentPageId : 0 ),
								options: tepllHtmlSitemapEditorPageListResolution.resolved
									? tepllHtmlSitemapEditorEnsureSelectedPageOption(
										tepllHtmlSitemapEditorMakePageSelectOptions(
											{ value: '0', label: tepllHtmlSitemapEditorI18nStrings.blogPageUseReading },
											tepllHtmlSitemapEditorPageListResolution.records
										),
										blogParentPageId,
										tepllHtmlSitemapEditorI18nStrings.pageNotInList
									)
									: [
										{
											value: String( blogParentPageId != null ? blogParentPageId : 0 ),
											label: tepllHtmlSitemapEditorI18nStrings.pagesLoading,
										},
									],
								disabled: ! tepllHtmlSitemapEditorPageListResolution.resolved,
								onChange: ( v ) => {
									const n = parseInt( v, 10 )
									setAttributes( { blog_parent_page_id: isNaN( n ) ? 0 : n } )
								},
							  } )
							: null,
						blog
							? el( SelectControl, {
								label: tepllHtmlSitemapEditorI18nStrings.blogTaxonomy,
								value: currentBlogTax,
								options: blogTaxOptions,
								onChange: ( v ) => {
									setAttributes( { blog_taxonomy: v } )
								},
							  } )
							: null,
						blog
							? el( ToggleControl, {
								label: tepllHtmlSitemapEditorI18nStrings.cptShowTaxonomy,
								checked: blogShowTaxonomy,
								onChange: ( v ) => {
									setAttributes( { blog_show_taxonomy: v } )
								},
							  } )
							: null,
						blog
							? el( ToggleControl, {
								label: tepllHtmlSitemapEditorI18nStrings.cptShowPosts,
								checked: blogShowPosts,
								onChange: ( v ) => {
									setAttributes( { blog_show_posts: v } )
								},
							  } )
							: null,
						blog
							? el( TextControl, {
								label: tepllHtmlSitemapEditorI18nStrings.cptMaxDepth,
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
						{ title: tepllHtmlSitemapEditorI18nStrings.cptPanel, initialOpen: false },
						tepllHtmlSitemapEditorPublicCptDefinitions.length === 0
							? el( 'p', { className: 'description' }, tepllHtmlSitemapEditorI18nStrings.cptNone )
							: el( Fragment, null, tepllHtmlSitemapEditorCptInspectorPanelElements )
					)
				),
				( () => {
					const tepllHtmlSitemapEditorRenderPreviewFallback = () =>
						el(
							'div',
							{ className: 'components-placeholder__label' },
							el( Dashicon, { icon: 'networking' } ),
							el( 'span', null, tepllHtmlSitemapEditorI18nStrings.placeholderLabel )
						)

					const tepllHtmlSitemapEditorRenderPreviewLoadingPlaceholder = () => null

					const content =
						ServerSideRender && tepllHtmlSitemapEditorPreviewMode !== 'default'
							? el( ServerSideRender, {
								block: 'tepll/html-sitemap',
								attributes: attributes,
								LoadingResponsePlaceholder: tepllHtmlSitemapEditorRenderPreviewLoadingPlaceholder,
								ErrorResponsePlaceholder: tepllHtmlSitemapEditorRenderPreviewFallback,
								EmptyResponsePlaceholder: tepllHtmlSitemapEditorRenderPreviewFallback,
							} )
							: tepllHtmlSitemapEditorRenderPreviewFallback()

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