// --------------------------------------------------
//	Features: Pll language switcher: Dropdown
// --------------------------------------------------


( ( tepllLanguageSwitcherDropdownRoot ) => {
	'use strict'

	const tepllLanguageSwitcherDropdownRootDocument             = tepllLanguageSwitcherDropdownRoot.document
	const tepllLanguageSwitcherDropdownInitDatasetKey           = 'tepllLanguageSwitcherDropdownInit'
	const tepllLanguageSwitcherDropdownObserverStartedWindowKey = '__tepllLanguageSwitcherDropdownObserverStarted'

	let tepllLanguageSwitcherDropdownActiveWrapper       = null
	let tepllLanguageSwitcherDropdownGlobalHandlersBound = false

	const tepllLanguageSwitcherDropdownGetParts = ( wrapper ) => {
		if ( ! wrapper || ! wrapper.querySelector ) return null
		const tepllLanguageSwitcherDropdownToggleButton = wrapper.querySelector( '.tepll-pll-language-switcher-toggle' )
		const tepllLanguageSwitcherDropdownPanel = wrapper.querySelector( '.tepll-pll-language-switcher-dropdown' )
		if ( ! tepllLanguageSwitcherDropdownToggleButton || ! tepllLanguageSwitcherDropdownPanel ) return null
		return { btn: tepllLanguageSwitcherDropdownToggleButton, dropdown: tepllLanguageSwitcherDropdownPanel }
	}

	const tepllLanguageSwitcherDropdownCloseWrapper = ( wrapper ) => {
		const tepllLanguageSwitcherDropdownToggleAndPanel = tepllLanguageSwitcherDropdownGetParts( wrapper )
		if ( ! tepllLanguageSwitcherDropdownToggleAndPanel ) return

		tepllLanguageSwitcherDropdownToggleAndPanel.btn.setAttribute( 'aria-expanded', 'false' )
		tepllLanguageSwitcherDropdownToggleAndPanel.dropdown.classList.remove( 'is-open' )
		tepllLanguageSwitcherDropdownToggleAndPanel.dropdown.setAttribute( 'aria-hidden', 'true' )
	}

	const tepllLanguageSwitcherDropdownOpenWrapper = ( wrapper ) => {
		const tepllLanguageSwitcherDropdownToggleAndPanel = tepllLanguageSwitcherDropdownGetParts( wrapper )
		if ( ! tepllLanguageSwitcherDropdownToggleAndPanel ) return

		tepllLanguageSwitcherDropdownToggleAndPanel.btn.setAttribute( 'aria-expanded', 'true' )
		tepllLanguageSwitcherDropdownToggleAndPanel.dropdown.classList.add( 'is-open' )
		tepllLanguageSwitcherDropdownToggleAndPanel.dropdown.setAttribute( 'aria-hidden', 'false' )
	}

	const tepllLanguageSwitcherDropdownBindGlobalHandlers = () => {
		if ( tepllLanguageSwitcherDropdownGlobalHandlersBound || ! tepllLanguageSwitcherDropdownRootDocument ) return
		tepllLanguageSwitcherDropdownGlobalHandlersBound = true

		tepllLanguageSwitcherDropdownRootDocument.addEventListener(
			'click',
			( clickEvent ) => {
				if ( ! tepllLanguageSwitcherDropdownActiveWrapper ) return
				if ( tepllLanguageSwitcherDropdownActiveWrapper.contains && tepllLanguageSwitcherDropdownActiveWrapper.contains( clickEvent.target ) ) return

				tepllLanguageSwitcherDropdownCloseWrapper( tepllLanguageSwitcherDropdownActiveWrapper )
				tepllLanguageSwitcherDropdownActiveWrapper = null
			},
			true
		)

		tepllLanguageSwitcherDropdownRootDocument.addEventListener(
			'keydown',
			( keyEvent ) => {
				if ( keyEvent.key !== 'Escape' ) return
				if ( ! tepllLanguageSwitcherDropdownActiveWrapper ) return

				const tepllLanguageSwitcherDropdownToggleAndPanel = tepllLanguageSwitcherDropdownGetParts( tepllLanguageSwitcherDropdownActiveWrapper )
				tepllLanguageSwitcherDropdownCloseWrapper( tepllLanguageSwitcherDropdownActiveWrapper )
				tepllLanguageSwitcherDropdownActiveWrapper = null

				if ( tepllLanguageSwitcherDropdownToggleAndPanel && tepllLanguageSwitcherDropdownToggleAndPanel.btn && tepllLanguageSwitcherDropdownToggleAndPanel.btn.focus ) {
					tepllLanguageSwitcherDropdownToggleAndPanel.btn.focus()
				}
			},
			true
		)
	}

	const tepllLanguageSwitcherDropdownInitWithin = ( contextRoot ) => {
		const tepllLanguageSwitcherDropdownQueryContext = contextRoot && contextRoot.querySelectorAll
			? contextRoot
			: tepllLanguageSwitcherDropdownRootDocument
		if ( ! tepllLanguageSwitcherDropdownQueryContext || ! tepllLanguageSwitcherDropdownQueryContext.querySelectorAll ) return

		const tepllLanguageSwitcherDropdownWrappers = tepllLanguageSwitcherDropdownQueryContext.querySelectorAll(
			'.tepll-pll-language-switcher.is-dropdown'
		)
		if ( ! tepllLanguageSwitcherDropdownWrappers || ! tepllLanguageSwitcherDropdownWrappers.length ) return

		tepllLanguageSwitcherDropdownWrappers.forEach( ( wrapper ) => {
			if ( wrapper.dataset && wrapper.dataset[ tepllLanguageSwitcherDropdownInitDatasetKey ] === '1' ) return

			const tepllLanguageSwitcherDropdownToggleAndPanel = tepllLanguageSwitcherDropdownGetParts( wrapper )
			if ( ! tepllLanguageSwitcherDropdownToggleAndPanel ) return

			if ( wrapper.dataset ) {
				wrapper.dataset[ tepllLanguageSwitcherDropdownInitDatasetKey ] = '1'
			}

			tepllLanguageSwitcherDropdownToggleAndPanel.btn.addEventListener( 'click', ( clickEvent ) => {
				clickEvent.stopPropagation()

				const tepllLanguageSwitcherDropdownIsOpen =
					wrapper === tepllLanguageSwitcherDropdownActiveWrapper &&
					tepllLanguageSwitcherDropdownToggleAndPanel.dropdown.classList.contains( 'is-open' )

				if ( tepllLanguageSwitcherDropdownIsOpen ) {
					tepllLanguageSwitcherDropdownCloseWrapper( wrapper )
					tepllLanguageSwitcherDropdownActiveWrapper = null
					return
				}

				if ( tepllLanguageSwitcherDropdownActiveWrapper && tepllLanguageSwitcherDropdownActiveWrapper !== wrapper ) {
					tepllLanguageSwitcherDropdownCloseWrapper( tepllLanguageSwitcherDropdownActiveWrapper )
				}

				tepllLanguageSwitcherDropdownOpenWrapper( wrapper )
				tepllLanguageSwitcherDropdownActiveWrapper = wrapper
			} )
		} )
	}

	const tepllLanguageSwitcherDropdownStartObserver = () => {
		if ( ! tepllLanguageSwitcherDropdownRootDocument || ! ( 'MutationObserver' in tepllLanguageSwitcherDropdownRoot ) ) return
		if ( tepllLanguageSwitcherDropdownRoot[ tepllLanguageSwitcherDropdownObserverStartedWindowKey ] ) return
		tepllLanguageSwitcherDropdownRoot[ tepllLanguageSwitcherDropdownObserverStartedWindowKey ] = true

		let tepllLanguageSwitcherDropdownObserverScheduled = false
		const tepllLanguageSwitcherDropdownDomObserver = new MutationObserver( () => {
			if ( tepllLanguageSwitcherDropdownObserverScheduled ) return
			tepllLanguageSwitcherDropdownObserverScheduled = true

			setTimeout( () => {
				tepllLanguageSwitcherDropdownObserverScheduled = false
				tepllLanguageSwitcherDropdownInitWithin( tepllLanguageSwitcherDropdownRootDocument )
			}, 50 )
		} )

		tepllLanguageSwitcherDropdownDomObserver.observe( tepllLanguageSwitcherDropdownRootDocument.documentElement, { childList: true, subtree: true } )
	}

	tepllLanguageSwitcherDropdownRoot.tepllLanguageSwitcherInitDropdown = ( contextRoot ) => {
		tepllLanguageSwitcherDropdownBindGlobalHandlers()
		tepllLanguageSwitcherDropdownInitWithin( contextRoot )
		tepllLanguageSwitcherDropdownStartObserver()
	}

	if ( tepllLanguageSwitcherDropdownRootDocument ) {
		if ( tepllLanguageSwitcherDropdownRootDocument.readyState === 'loading' ) {
			tepllLanguageSwitcherDropdownRootDocument.addEventListener( 'DOMContentLoaded', () => tepllLanguageSwitcherDropdownRoot.tepllLanguageSwitcherInitDropdown() )
		} else {
			tepllLanguageSwitcherDropdownRoot.tepllLanguageSwitcherInitDropdown()
		}
	}
} )( window )