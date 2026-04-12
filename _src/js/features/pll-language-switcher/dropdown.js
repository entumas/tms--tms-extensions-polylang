// --------------------------------------------------
//	Features: Pll language switcher: Dropdown
// --------------------------------------------------


( ( languageSwitcherDropdownRoot ) => {
	'use strict'

	const languageSwitcherDropdownRootDocument         = languageSwitcherDropdownRoot.document
	const languageSwitcherDropdownInitDatasetKey    = 'tepllLanguageSwitcherDropdownInit'
	const languageSwitcherDropdownObserverStartedWindowKey = '__tepllLanguageSwitcherDropdownObserverStarted'

	let languageSwitcherDropdownActiveWrapper = null
	let languageSwitcherDropdownGlobalHandlersBound = false

	const languageSwitcherDropdownGetParts = ( wrapper ) => {
		if ( ! wrapper || ! wrapper.querySelector ) return null
		const languageSwitcherDropdownToggleButton = wrapper.querySelector( '.tepll-pll-language-switcher-toggle' )
		const languageSwitcherDropdownPanel = wrapper.querySelector( '.tepll-pll-language-switcher-dropdown' )
		if ( ! languageSwitcherDropdownToggleButton || ! languageSwitcherDropdownPanel ) return null
		return { btn: languageSwitcherDropdownToggleButton, dropdown: languageSwitcherDropdownPanel }
	}

	const languageSwitcherDropdownCloseWrapper = ( wrapper ) => {
		const languageSwitcherDropdownToggleAndPanel = languageSwitcherDropdownGetParts( wrapper )
		if ( ! languageSwitcherDropdownToggleAndPanel ) return

		languageSwitcherDropdownToggleAndPanel.btn.setAttribute( 'aria-expanded', 'false' )
		languageSwitcherDropdownToggleAndPanel.dropdown.classList.remove( 'is-open' )
		languageSwitcherDropdownToggleAndPanel.dropdown.setAttribute( 'aria-hidden', 'true' )
	}

	const languageSwitcherDropdownOpenWrapper = ( wrapper ) => {
		const languageSwitcherDropdownToggleAndPanel = languageSwitcherDropdownGetParts( wrapper )
		if ( ! languageSwitcherDropdownToggleAndPanel ) return

		languageSwitcherDropdownToggleAndPanel.btn.setAttribute( 'aria-expanded', 'true' )
		languageSwitcherDropdownToggleAndPanel.dropdown.classList.add( 'is-open' )
		languageSwitcherDropdownToggleAndPanel.dropdown.setAttribute( 'aria-hidden', 'false' )
	}

	const languageSwitcherDropdownBindGlobalHandlers = () => {
		if ( languageSwitcherDropdownGlobalHandlersBound || ! languageSwitcherDropdownRootDocument ) return
		languageSwitcherDropdownGlobalHandlersBound = true

		languageSwitcherDropdownRootDocument.addEventListener(
			'click',
			( clickEvent ) => {
				if ( ! languageSwitcherDropdownActiveWrapper ) return
				if ( languageSwitcherDropdownActiveWrapper.contains && languageSwitcherDropdownActiveWrapper.contains( clickEvent.target ) ) return

				languageSwitcherDropdownCloseWrapper( languageSwitcherDropdownActiveWrapper )
				languageSwitcherDropdownActiveWrapper = null
			},
			true
		)

		languageSwitcherDropdownRootDocument.addEventListener(
			'keydown',
			( keyEvent ) => {
				if ( keyEvent.key !== 'Escape' ) return
				if ( ! languageSwitcherDropdownActiveWrapper ) return

				const languageSwitcherDropdownToggleAndPanel = languageSwitcherDropdownGetParts( languageSwitcherDropdownActiveWrapper )
				languageSwitcherDropdownCloseWrapper( languageSwitcherDropdownActiveWrapper )
				languageSwitcherDropdownActiveWrapper = null

				if ( languageSwitcherDropdownToggleAndPanel && languageSwitcherDropdownToggleAndPanel.btn && languageSwitcherDropdownToggleAndPanel.btn.focus ) {
					languageSwitcherDropdownToggleAndPanel.btn.focus()
				}
			},
			true
		)
	}

	const languageSwitcherDropdownInitWithin = ( contextRoot ) => {
		const languageSwitcherDropdownQueryContext = contextRoot && contextRoot.querySelectorAll
			? contextRoot
			: languageSwitcherDropdownRootDocument
		if ( ! languageSwitcherDropdownQueryContext || ! languageSwitcherDropdownQueryContext.querySelectorAll ) return

		const languageSwitcherDropdownWrappers = languageSwitcherDropdownQueryContext.querySelectorAll(
			'.tepll-pll-language-switcher.is-dropdown'
		)
		if ( ! languageSwitcherDropdownWrappers || ! languageSwitcherDropdownWrappers.length ) return

		languageSwitcherDropdownWrappers.forEach( ( wrapper ) => {
			if ( wrapper.dataset && wrapper.dataset[ languageSwitcherDropdownInitDatasetKey ] === '1' ) return

			const languageSwitcherDropdownToggleAndPanel = languageSwitcherDropdownGetParts( wrapper )
			if ( ! languageSwitcherDropdownToggleAndPanel ) return

			if ( wrapper.dataset ) {
				wrapper.dataset[ languageSwitcherDropdownInitDatasetKey ] = '1'
			}

			languageSwitcherDropdownToggleAndPanel.btn.addEventListener( 'click', ( clickEvent ) => {
				clickEvent.stopPropagation()

				const languageSwitcherDropdownIsOpen =
					wrapper === languageSwitcherDropdownActiveWrapper &&
					languageSwitcherDropdownToggleAndPanel.dropdown.classList.contains( 'is-open' )

				if ( languageSwitcherDropdownIsOpen ) {
					languageSwitcherDropdownCloseWrapper( wrapper )
					languageSwitcherDropdownActiveWrapper = null
					return
				}

				if ( languageSwitcherDropdownActiveWrapper && languageSwitcherDropdownActiveWrapper !== wrapper ) {
					languageSwitcherDropdownCloseWrapper( languageSwitcherDropdownActiveWrapper )
				}

				languageSwitcherDropdownOpenWrapper( wrapper )
				languageSwitcherDropdownActiveWrapper = wrapper
			} )
		} )
	}

	const languageSwitcherDropdownStartObserver = () => {
		if ( ! languageSwitcherDropdownRootDocument || ! ( 'MutationObserver' in languageSwitcherDropdownRoot ) ) return
		if ( languageSwitcherDropdownRoot[ languageSwitcherDropdownObserverStartedWindowKey ] ) return
		languageSwitcherDropdownRoot[ languageSwitcherDropdownObserverStartedWindowKey ] = true

		let languageSwitcherDropdownObserverScheduled = false
		const languageSwitcherDropdownDomObserver = new MutationObserver( () => {
			if ( languageSwitcherDropdownObserverScheduled ) return
			languageSwitcherDropdownObserverScheduled = true

			setTimeout( () => {
				languageSwitcherDropdownObserverScheduled = false
				languageSwitcherDropdownInitWithin( languageSwitcherDropdownRootDocument )
			}, 50 )
		} )

		languageSwitcherDropdownDomObserver.observe( languageSwitcherDropdownRootDocument.documentElement, { childList: true, subtree: true } )
	}

	languageSwitcherDropdownRoot.tepllLanguageSwitcherInitDropdown = ( contextRoot ) => {
		languageSwitcherDropdownBindGlobalHandlers()
		languageSwitcherDropdownInitWithin( contextRoot )
		languageSwitcherDropdownStartObserver()
	}

	if ( languageSwitcherDropdownRootDocument ) {
		if ( languageSwitcherDropdownRootDocument.readyState === 'loading' ) {
			languageSwitcherDropdownRootDocument.addEventListener( 'DOMContentLoaded', () => languageSwitcherDropdownRoot.tepllLanguageSwitcherInitDropdown() )
		} else {
			languageSwitcherDropdownRoot.tepllLanguageSwitcherInitDropdown()
		}
	}
} )( window )
