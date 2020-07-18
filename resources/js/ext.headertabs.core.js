/**
 * JavaScript code for Header Tabs extension.
 *
 * @file
 * @ingroup Extensions
 *
 * @author Sergey Chernyshev
 * @author Yaron Koren
 * @author Finlay Beaton
 * @author Priyanshu Varshney
 */

( function ( d ) {
	var tabName;
	var tabs = OO.ui.infuse( $( '.mw-tabs' ) );

	function tabNameEscape( tabName ) {
		tabName = escape( tabName );
		// For some reason, the JS escape() function doesn't handle
		// '+', '/' or '@' - take care of these manually.
		tabName = tabName.replace( /\+/g, '%2B' );
		tabName = tabName.replace( /\//g, '%2F' );
		tabName = tabName.replace( /@/g, '%40' );
		tabName = tabName.replace( /%/g, '_' );
		tabName = tabName.replace( /\./g, '_' );
		return tabName;
	}

	/*
	 * Get links to tabs in Table of Contents to work.
	 * @author Chad Catlett
	 */
	$( d ).ready( function () {
		$( '.toc ul a' ).each( function () {
			$( this ).on( 'click', function () {
				// Don't escape #'s for our entries. Copied from:
				// http://totaldev.com/content/escaping-characters-get-valid-jquery-id
				var escapedHash = this.hash.replace( /([;&,\.\+\*\~':"\!\^$%@\[\]\(\)=>\|])/g, '\\$1' );
				tabs.setTabPanel( escapedHash.substr( 1 ) );
			} );
		} );

	} );

	$( window ).on( 'hashchange', function () {
		tabName = window.location.hash.replace( '#tab=', '' );
		tabs.setTabPanel( tabName );
	} );

	/* follow a # anchor to a tab OR a heading */
	var curHash = window.location.hash;
	if ( curHash.indexOf( '#tab=' ) === 0 ) {
		// remove the fragment identifier, we're using it for the name of the tab.
		tabName = curHash.replace( '#tab=', '' );
		tabs.setTabPanel( tabName );
	}

	// only fires when the user clicks on a tab, not on page load
	$( '.mw-tabs' ).on( 'click', function () {
		var tabCurrentTabPanelName = tabs.getCurrentTabPanelName();
		if ( mw.config.get( 'wgHeaderTabsUseHistory' ) ) {
			window.location.hash = '#tab=' + tabNameEscape( tabCurrentTabPanelName );
		}
	} );

	/* click a tab parserhook link */
	$( '.tabLink' ).on( 'click', function () {
		tabName = $( this ).attr( 'href' ).replace( '#tab=', '' );
		tabs.setTabPanel( tabNameEscape( tabName ) );
		return false;
	} );
}( document ) );
