<?php
/**
 * File for the HeaderTabsHooks class.
 *
 * @file
 * @ingroup Extensions
 */

class HeaderTabsHooks {

	/**
	 * Called by the ParserFirstCallInit hook.
	 *
	 * @param Parser $parser
	 */
	public static function registerParserFunctions( $parser ) {
		$parser->setHook( 'headertabs', [ 'HeaderTabs', 'tag' ] );
		$parser->setHook( 'notabtoc', [ 'HeaderTabs', 'noTabTOC' ] );
		$parser->setFunctionHook( 'switchtablink', [ 'HeaderTabs', 'renderSwitchTabLink' ] );
	}

	/**
	 * A wrapper around HeaderTabs::replaceFirstLevelHeaders(), which does
	 * most of the actual work.
	 * This function mostly just determines if there are any header tabs
	 * on the current page, and exits if not.
	 *
	 * Called by the ParserAfterTidy hook.
	 *
	 * @param Parser &$parser
	 * @param string &$text
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
				return; // <headertabs/> tag is not found
			}
		}

		HeaderTabs::replaceFirstLevelHeaders( $parser, $text, $aboveandbelow );
	}

	/**
	 * Called by the ResourceLoaderGetConfigVars hook.
	 *
	 * @param array &$vars
	 */
	public static function addConfigVarsToJS( &$vars ) {
		global $wgHeaderTabsEditTabLink, $wgHeaderTabsNoTabsInToc;

		$vars['wgHeaderTabsEditTabLink'] = $wgHeaderTabsEditTabLink;
		$vars['wgHeaderTabsNoTabsInToc'] = $wgHeaderTabsNoTabsInToc;
	}

	/**
	 * Adds the ext.headertabs ResourceLoader module to the preview display
	 * if this page uses Header Tabs.
	 *
	 * Called by the EditPageGetPreviewContent hook.
	 *
	 * @param EditPage $editPage The EditPage object
	 * @param string &$content The preview content
	 */
	public static function onEditPageGetPreviewContent( $editPage, &$content ) {
		if ( HeaderTabs::$isUsed ) {
			$editPage->getContext()->getOutput()->addModules( [ 'ext.headertabs' ] );
		}
	}

	/**
	 * Adds the ext.headertabs ResourceLoader module to the diff display
	 * if this page uses Header Tabs.
	 *
	 * Called by the EditPageGetDiffContent hook.
	 *
	 * @param EditPage $editPage The edit page object
	 * @param string &$newtext The new text being edited
	 */
	public static function onEditPageGetDiffContent( $editPage, &$newtext ) {
		if ( HeaderTabs::$isUsed ) {
			$editPage->getContext()->getOutput()->addModules( [ 'ext.headertabs' ] );
		}
	}
}
