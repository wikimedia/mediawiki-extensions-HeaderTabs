<?php
/**
 * File for the HeaderTabsHooks class.
 *
 * @file
 * @ingroup Extensions
 *
 * @author Sergey Chernyshev
 * @author Yaron Koren
 * @author Finlay Beaton
 * @author Priyanshu Varshney
 */

class HeaderTabsHooks {

	/**
	 * @param Parser $parser
	 * @return true
	 */
	public static function registerParserFunctions( $parser ) {
		$parser->setHook( 'headertabs', [ 'HeaderTabs', 'tag' ] );
		$parser->setHook( 'notabtoc', [ 'HeaderTabs', 'noTabTOC' ] );
		$parser->setFunctionHook( 'switchtablink', [ 'HeaderTabs', 'renderSwitchTabLink' ] );
		return true;
	}

	/**
	 * A wrapper around HeaderTabs::replaceFirstLevelHeaders(), which does
	 * most of the actual work.
	 * This function mostly just determines if there are any header tabs
	 * on the cuurrent page, and exits if not.
	 * @param Parser &$parser
	 * @param string &$text
	 * @return true
	 */
	public static function replaceFirstLevelHeaders( &$parser, &$text ) {
		global $wgHeaderTabsAutomaticNamespaces;

		// Remove spans added if "auto-number headings" is enabled.
		$simplifiedText = preg_replace( '/\<span class="mw-headline-number"\>\d*\<\/span\>/', '', $text );

		// Where do we stop rendering tabs, and what is below it?
		// if we don't have a stop point, then bail out
		$aboveandbelow = explode( '<div id="nomoretabs"></div>', $simplifiedText, 2 );
		if ( count( $aboveandbelow ) <= 1 ) {
			if ( in_array( $parser->getTitle()->getNamespace(), $wgHeaderTabsAutomaticNamespaces ) ) {
				// We'll act as if the end of article is
				// nomoretabs.
				$aboveandbelow[] = '';
			} else {
				return true; // <headertabs/> tag is not found
			}
		}

		return HeaderTabs::replaceFirstLevelHeaders( $parser, $text, $aboveandbelow );
	}

	/**
	 * @param array &$vars
	 * @return true
	 */
	public static function addConfigVarsToJS( &$vars ) {
		global $wgHeaderTabsUseHistory, $wgHeaderTabsEditTabLink, $wgHeaderTabsNoTabsInToc;

		$vars['wgHeaderTabsUseHistory'] = $wgHeaderTabsUseHistory;
		$vars['wgHeaderTabsEditTabLink'] = $wgHeaderTabsEditTabLink;
		$vars['wgHeaderTabsNoTabsInToc'] = $wgHeaderTabsNoTabsInToc;

		return true;
	}
}
