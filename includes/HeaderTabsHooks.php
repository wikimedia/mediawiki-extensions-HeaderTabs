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
 */

class HeaderTabsHooks {

	public static function registerParserFunctions( $parser ) {
		$parser->setHook( 'headertabs', [ 'HeaderTabs', 'tag' ] );
		$parser->setFunctionHook( 'switchtablink', [ 'HeaderTabs', 'renderSwitchTabLink' ] );
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

	static function setGlobalJSVariables( &$vars ) {
		global $wgHeaderTabsTabIndexes;
		$vars['wgHeaderTabsTabIndexes'] = $wgHeaderTabsTabIndexes;
		return true;
	}

	/**
	 * ResourceLoaderRegisterModules hook handler
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ResourceLoaderRegisterModules
	 *
	 * @param ResourceLoader &$resourceLoader The ResourceLoader object
	 * @return bool Always true
	 */
	public static function registerModules( ResourceLoader &$resourceLoader ) {
		global $wgVersion;

		$htDir = __DIR__;

		// In MW 1.34, all the jquery.ui.* modules were merged into one
		// big jquery.ui module.
		if ( version_compare( $wgVersion, '1.34', '>=' ) ) {
			$jquiTabsModule = 'jquery.ui';
		} else {
			$jquiTabsModule = 'jquery.ui.tabs';
		}

		$resourceLoader->register( [
			"ext.headertabs" => [
				'localBasePath' => $htDir,
				'remoteExtPath' => 'HeaderTabs/includes',
				"scripts" => "../resources/js/ext.headertabs.core.js",
				"dependencies" => [
					$jquiTabsModule
				]
			]
		] );

		return true;
	}

}
