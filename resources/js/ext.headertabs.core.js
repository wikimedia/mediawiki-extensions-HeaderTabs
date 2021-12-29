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

	/**
	 * We override window.print in order to remove tabs from the "printable version"
	 * of any page (which uses window.print), and display the page more or less as it
	 * would look if Header Tabs were not installed, so that all content can be seen.
	 * This has to be done in JS and not CSS, because the name of each tab (i.e., the
	 * section header) has become separated from the tab contents.
	 */
	var defaultPrinter = window.print;
	window.print = function () {
		var $actualContent, $section, $wrapper = null;
		$actualContent = $( '#headertabs' ).clone( true );
		$( '#headertabs' ).empty();
		$actualContent.find( '.oo-ui-tabOptionWidget > .oo-ui-labelElement-label' ).each(
			function ( index ) {
				$section = $actualContent.find( '.section-' + ( index + 1 ) ).clone( true )
					.find( '.oo-ui-fieldsetLayout' );
				$section.find( '.ht-editsection' ).remove();
				$wrapper = $( '<div>' );
				$( '<h1>' ).text( $( this ).text() ).appendTo( $wrapper );
				$section.appendTo( $wrapper );
				$( '#headertabs' ).append( $wrapper );
			}
		);
		defaultPrinter();
		// Reload the page so that the tabs appear again after the user has
		// finished printing.
		location.reload();
	};

	/**
	 * Remove tab headings from TOC when <notabtoc/> is passed in wikitext or
	 * when wgHeaderTabsNoTabsInToc is set to true
	 */
	if ( mw.config.get( 'wgHeaderTabsNoTabsInToc' ) || $( '#noTabTOC' ).length ) {
		var tabsArray = [];
		$( '.oo-ui-tabPanelLayout' ).each( function () {
			tabsArray.push( $( this ).attr( 'id' ) );
		} );
		$( '.toc' ).find( 'li' ).each( function () {
			var id = $( this ).find( 'a' ).attr( 'href' ).replace( '#', '' );
			if ( tabsArray.indexOf( id ) !== -1 ) {
				$( this ).remove();
			}
		} );
		$( '.toc' ).find( 'li' ).each( function ( index ) {
			$( this ).find( 'a' ).find( '.tocnumber' ).text( index + 1 );
		} );
	}

}( document ) );
