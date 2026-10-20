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
	 * Builds the tabs for pages rendered by Parsoid.
	 *
	 * Parsoid does not run the ParserAfterTidy hook, so the handler above never
	 * sees its output. ContentAlterParserOutput runs after either parser has
	 * finished. For the legacy parser the tabs are already built at this point
	 * (the marker is gone) and this does nothing.
	 *
	 * Called by the ContentAlterParserOutput hook.
	 *
	 * @param Content $content
	 * @param Title $title
	 * @param ParserOutput $parserOutput
	 */
	public static function onContentAlterParserOutput( $content, $title, $parserOutput ) {
		global $wgHeaderTabsAutomaticNamespaces;

		if ( !$parserOutput->hasText() ) {
			return;
		}
		$text = $parserOutput->getContentHolderText();
		// Parsoid keeps extra attributes on the marker (typeof, about, data-mw).
		$parts = preg_split( '/<div id="nomoretabs"[^>]*><\/div>/', $text, 2 );
		if ( count( $parts ) <= 1 ) {
			if ( !in_array( $title->getNamespace(), $wgHeaderTabsAutomaticNamespaces ) ) {
				return; // no <headertabs/> tag, or the legacy parser already built the tabs
			}
			if ( strpos( $text, 'id="headertabs"' ) !== false || strpos( $text, '<section' ) === false ) {
				return; // already built, or not Parsoid output
			}
			$parts[] = '';
		}
		[ $above, $below ] = HeaderTabs::unwrapParsoidSections( $parts[0], $parts[1] );
		$newText = $above . $below;
		HeaderTabs::replaceFirstLevelHeaders( $title, $newText, [ $above, $below ] );
		if ( $newText !== $above . $below ) {
			$parserOutput->setContentHolderText( $newText );
		}
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
