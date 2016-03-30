/**
 * JavaScript code for Header Tabs extension.
 *
 * @file
 * @ingroup Extensions
 *
 * @author Sergey Chernyshev
 * @author Yaron Koren
 * @author Olivier Beaton
 */

/*jshint -W030 */

var tabName;

function tabNameEscape(tabName) {
	tabName = escape( tabName );
	// For some reason, the JS escape() function doesn't handle
	// '+', '/' or '@' - take care of these manually.
	tabName = tabName.replace( /\+/g, "%2B" );
	tabName = tabName.replace( /\//g, "%2F" );
	tabName = tabName.replace( /@/g, "%40" );
	tabName = tabName.replace( /%/g, "_" );
	tabName = tabName.replace( /\./g, "_" );
	return tabName;
}

var $tabs = jQuery("#headertabs").tabs();

// delete the rule hiding unselected tabs
var sheets = document.styleSheets;

var s;
var r;

// Could be somebody else inserted something, so we cannot just delete rule 0 of sheet 0
outer:
for (s = 0; s < sheets.length; s++ ) {
	var cursheet = sheets[s];
	var rules = cursheet.cssRules ? cursheet.cssRules: cursheet.rules; // Yay IE
	
	for (r = 0; r < rules.length; r++){
		if( rules[r].selectorText !== undefined ){
			if( rules[r].selectorText.toLowerCase() === ".unselected" ){ //find ".unselected" rule
				cursheet.deleteRule ? cursheet.deleteRule(r): cursheet.removeRule(r); // Yay IE
				break outer;
			}
		}
	}
}

/*
 * Get links to tabs in Table of Contents to work.
 * @author Chad Catlett
 */
jQuery(document).ready(function(){
	jQuery(".toc ul a").each(function(){
		jQuery(this).click(function() {
			// Don't escape #'s for our entries. Copied from:
			// http://totaldev.com/content/escaping-characters-get-valid-jquery-id
			var escapedHash = this.hash.replace(/([;&,\.\+\*\~':"\!\^$%@\[\]\(\)=>\|])/g, '\\$1');
			var tabId = jQuery(escapedHash).closest('.ui-tabs-panel').attr('id');
			$tabs.tabs('select', tabNameEscape(tabId));
		});
	});

});

/* follow a # anchor to a tab OR a heading */
var curHash = window.location.hash;
if ( curHash.indexOf( "#tab=" ) === 0 ) {
	// remove the fragment identifier, we're using it for the name of the tab.
	tabName = curHash.replace( "#tab=", "" );
	$tabs.tabs('select', tabName);
} else if (curHash !== '') {
	/* select tab in a fragment
	thanks kboudloche, Alphos
	http://forum.jquery.com/topic/jquery-ui-tabs-create-an-anchor-to-content-within-tab#14737000001187015
 */
	tabName = jQuery(curHash).closest('.ui-tabs-panel').attr('id');
	$tabs.tabs('select', tabNameEscape(tabName));
}

function tabEditTabLink(hash) {
	var section = '';
	if ( hash.indexOf( "#tab=" ) === 0 ) {
		// keep the fragment identifier, using it to do a jQuery find on the id
		hash = hash.replace( "#tab=", "#" );
	}

	if (hash !== '') {
		section = jQuery(hash).attr('class');
		var s = section.indexOf('section-')+8;
		section = section.substring(s, s+section.substring(s).indexOf(' '));
		if (section !== 0) {
			section = '&section='+section;
			// No way to edit anything before the first section
			// except to edit the entire article.
		}
	}

	if (!section || section === '0' || section === 0) {
		section = '';
	}
	// http://wiki.org/wiki/index.php?title=User_talk:Finlay&action=edit&section=1
	var $anchor = jQuery('#edittab').find('a');
	$anchor.attr('href', mediaWiki.config.get("wgScript")+'?title='+mediaWiki.config.get("wgPageName")+'&action=edit'+section);
}

// page load behaviour
if (mediaWiki.config.get("wgHeaderTabsEditTabLink")) {
	tabEditTabLink(window.location.hash);
}

// only fires when the user clicks on a tab, not on page load
$tabs.bind('tabsshow', function(event, ui) {
	// make the url show the current tab name for bookmarks
	if (mediaWiki.config.get( "wgHeaderTabsUseHistory" ) ) {
		window.location.hash = '#tab='+ui.tab.hash.slice( 1 );
	}

	if (mediaWiki.config.get( "wgHeaderTabsEditTabLink" ) ) {
		tabEditTabLink(ui.tab.hash);
	}
} );

/* click a tab parserhook link */
jQuery( ".tabLink" ).click( function () {
	var wgHeaderTabsTabIndexes;
	tabName = jQuery( this ).attr( 'href' ).replace( '#tab=', '' );
	var tabIndex = wgHeaderTabsTabIndexes[tabName];
	$tabs.tabs( 'select', tabIndex ); //tabNameEscape(href));
	return false;
} );
