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
	 * on the cuurrent page, and exits if not.
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
	 * @param array &$vars
	 */
	public static function addConfigVarsToJS( &$vars ) {
		global $wgHeaderTabsEditTabLink, $wgHeaderTabsNoTabsInToc;

		$vars['wgHeaderTabsEditTabLink'] = $wgHeaderTabsEditTabLink;
		$vars['wgHeaderTabsNoTabsInToc'] = $wgHeaderTabsNoTabsInToc;
	}

	/**
	 * This method is a hook handler for the "EditPageGetPreviewContent" hook.
	 * It is used to add the "ext.headertabs" module to the page output if HeaderTabs tagging is enabled.
	 *
	 * @param EditPage $editPage The EditPage object.
	 * @param string &$content The preview content.
	 */
	public static function onEditPageGetPreviewContent( $editPage, &$content ) {
		if ( HeaderTabs::$isUsed ) {
			$editPage->getContext()->getOutput()->addModules( [ 'ext.headertabs' ] );
		}
	}

	/**
	 * Retrieves the diff content for the edit page.
	 *
	 * This function checks if the HeaderTabs are tagged, and if true, adds the 'ext.headertabs' module to the
	 * page's output.
	 *
	 * @param EditPage $editPage The edit page object.
	 * @param string &$newtext The new text being edited.
	 */
	public static function onEditPageGetDiffContent( $editPage, &$newtext ) {
		if ( HeaderTabs::$isUsed ) {
			$editPage->getContext()->getOutput()->addModules( [ 'ext.headertabs' ] );
		}
	}
}
