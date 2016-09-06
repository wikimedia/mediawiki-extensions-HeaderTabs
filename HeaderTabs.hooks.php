<?php
/**
 * File for the HeaderTabs class.
 *
 * @file
 * @ingroup Extensions
 *
 * @author Sergey Chernyshev
 * @author Yaron Koren
 * @author Olivier Finlay Beaton
 */

class HeaderTabsHooks {

	public static function registerParserFunctions( $parser ) {
		$parser->setHook( 'headertabs', array( 'HeaderTabs', 'tag' ) );
		$parser->setFunctionHook( 'switchtablink', array( 'HeaderTabs', 'renderSwitchTabLink' ) );
		return true;
	}

	/**
	 * A wrapper around HeaderTabs::replaceFirstLevelHeaders(), which does
	 * most of the actual work.
	 * This function mostly just determines if there are any header tabs
	 * on the cuurrent page, and exits if not.
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

	public static function addConfigVarsToJS( &$vars ) {
		global $wgHeaderTabsUseHistory, $wgHeaderTabsEditTabLink;

		$vars['wgHeaderTabsUseHistory'] = $wgHeaderTabsUseHistory;
		$vars['wgHeaderTabsEditTabLink'] = $wgHeaderTabsEditTabLink;

		return true;
	}

	/**
	 * @param $out OutputPage
	 * @return bool
	 */
	public static function addHTMLHeader( &$out ) {
		global $wgScriptPath, $wgHeaderTabsStyle;

		//! @todo we might be able to only load our js and styles if we are rendering tabs, speeding up pages that don't use it? but what about cached pages? (2011-12-12, ofb)

		$out->addModules( 'ext.headertabs' );

		// Add the module for the specified style.
		if ( $wgHeaderTabsStyle == 'bare' ) {
			$out->addModules( 'ext.headertabs.bare' );
		} elseif ( $wgHeaderTabsStyle == 'large' ) {
			$out->addModules( 'ext.headertabs.large' );
		}

		return true;
	}

	static function setGlobalJSVariables( &$vars ) {
		global $wgHeaderTabsTabIndexes;
		$vars['wgHeaderTabsTabIndexes'] = $wgHeaderTabsTabIndexes;
		return true;
	}
}
