/**
 * JavaScript code for Header Tabs extension.
 *
 * @file
 * @ingroup Extensions
 *
 * @author Sergey Chernyshev
 * @author Yaron Koren
 * @author Finlay Beaton
 */

( function ( d ) {
	var tabName,
		wgHeaderTabsTabIndexes = mw.config.get( 'wgHeaderTabsTabIndexes' );

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

	var $tabs = $( '#headertabs' ).tabs();
	$( '.unselected' ).removeClass( 'unselected' );

	/*
	 * Get links to tabs in Table of Contents to work.
	 * @author Chad Catlett
	 */
	$( d ).ready( function () {
		$( '.toc ul a' ).each( function () {
			$( this ).on( 'click', function () {
				// Don't escape #'s for our entries. Copied from:
				// http://totaldev.com/content/escaping-characters-get-valid-jquery-id
				var escapedHash = this.hash.replace( /([;&,\.\+\*\~':"\!\^$%@\[\]\(\)=>\|])/g, '\\$1' ),
					tabId = $( escapedHash ).closest( '.ui-tabs-panel' ).attr( 'id' );
				$tabs.tabs( 'select', tabNameEscape( tabId ) );
			} );
		} );

	} );

	/* follow a # anchor to a tab OR a heading */
	var curHash = window.location.hash;
	if ( curHash.indexOf( '#tab=' ) === 0 ) {
		// remove the fragment identifier, we're using it for the name of the tab.
		tabName = curHash.replace( '#tab=', '' );
		$tabs.tabs( 'select', tabName );
	} else if ( curHash !== '' ) {
		/* select tab in a fragment
		thanks kboudloche, Alphos
		http://forum.jquery.com/topic/jquery-ui-tabs-create-an-anchor-to-content-within-tab#14737000001187015
	 */
		tabName = $( curHash ).closest( '.ui-tabs-panel' ).attr( 'id' );
		$tabs.tabs( 'select', tabNameEscape( tabName ) );
	}

	function tabEditTabLink( hash ) {
		var section = '';
		if ( hash.indexOf( '#tab=' ) === 0 ) {
			// keep the fragment identifier, using it to do a $ find on the id
			hash = hash.replace( '#tab=', '#' );
		}

		if ( hash !== '' ) {
			section = $( hash ).attr( 'class' );
			var s = section.indexOf( 'section-' ) + 8;
			section = section.substring( s, s + section.substring( s ).indexOf( ' ' ) );
			if ( section !== 0 ) {
				section = '&section=' + section;
				// No way to edit anything before the first section
				// except to edit the entire article.
			}
		}

		if ( !section || section === '0' || section === 0 ) {
			section = '';
		}
		// http://wiki.org/wiki/index.php?title=User_talk:Finlay&action=edit&section=1
		var $anchor = $( '#edittab' ).find( 'a' );
		$anchor.attr(
			'href',
			mw.util.getUrl( 'Hauptseite', { action: 'edit' } ) + section
		);
	}

	// page load behaviour
	if ( mw.config.get( 'wgHeaderTabsEditTabLink' ) ) {
		tabEditTabLink( window.location.hash );
	}

	// only fires when the user clicks on a tab, not on page load
	$tabs.bind( 'tabsshow', function ( event, ui ) {
		// make the url show the current tab name for bookmarks
		if ( mw.config.get( 'wgHeaderTabsUseHistory' ) ) {
			window.location.hash = '#tab=' + ui.tab.hash.slice( 1 );
		}

		if ( mw.config.get( 'wgHeaderTabsEditTabLink' ) ) {
			tabEditTabLink( ui.tab.hash );
		}
	} );

	/* click a tab parserhook link */
	$( '.tabLink' ).on( 'click', function () {
		tabName = $( this ).attr( 'href' ).replace( '#tab=', '' );
		var tabIndex = wgHeaderTabsTabIndexes[ tabName ];
		$tabs.tabs( 'select', tabIndex ); // tabNameEscape(href));
		return false;
	} );
}( document ) );
