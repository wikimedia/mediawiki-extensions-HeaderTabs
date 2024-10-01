<?php
/**
 * File for the HeaderTabs class.
 *
 * @file
 * @ingroup Extensions
 *
 * @author Sergey Chernyshev
 * @author Yaron Koren
 * @author Finlay Beaton
 * @author Priyanshu Varshney
 */
use OOUI\WikimediaUITheme;

class HeaderTabs {

	public static bool $isUsed = false;

	/**
	 *
	 * @param string $input
	 * @param array $args
	 * @param \Parser $parser
	 * @return string
	 */
	public static function tag( $input, $args, $parser ) {
		$out = $parser->getOutput();
		$out->addModules( [ 'ext.headertabs' ] );

		self::$isUsed = true;

		// This tag, besides just enabling tabs, also designates
		// the end of tabs. Can be used even if automatic namespaced.
		return '<div id="nomoretabs"></div>';
	}

	public static function noTabTOC() {
		return '<div id="noTabTOC"></div>';
	}

	/**
	 * @param Parser &$parser
	 * @param string &$text
	 * @param string[] $aboveandbelow
	 */
	public static function replaceFirstLevelHeaders( &$parser, &$text, $aboveandbelow ) {
		global $wgHeaderTabsRenderSingleTab, $wgHeaderTabsDefaultFirstTab,
			$wgHeaderTabsEditTabLink;

		$below = $aboveandbelow[1];

		wfDebugLog( 'headertabs', __METHOD__ . ': detected header handling, checking' );

		if ( $below !== '' ) {
			wfDebugLog( 'headertabs', __METHOD__ . ': we have text below our tabs' );
		}

		$hasNewStructure = strpos( $aboveandbelow[0], 'data-mw-anchor' ) !== false;
		if ( $hasNewStructure ) {
			// MW 1.42+ or so
			$tabpatternsplit = '/(<h1 data-mw-anchor="[^"]+"[^>]*>.*<mw:editsection .*<\/h1>)/';
			$tabpatternmatch = '/<h(1) data-mw-anchor="([^"]+)"[^>]*>(.*)<mw:editsection .*<\/h1>/';
		} else {
			$tabpatternsplit = '/(<h1.+?<span[^>]+class="mw-headline"[^>]+id="[^"]+"[^>]' .
				'*>\s*.*?\s*<\/span>.*?<\/h1>)/';
			$tabpatternmatch = '/<h(1).+?<span[^>]+class="mw-headline"[^>]+id="([^"]+)"[^>]' .
				'*>\s*(.*?)\s*<\/span>.*?<\/h1>/';
		}
		$parts = preg_split( $tabpatternsplit, trim( $aboveandbelow[0] ), -1, PREG_SPLIT_DELIM_CAPTURE );
		$above = '';

		// auto tab and the first thing isn't a header
		if ( $wgHeaderTabsDefaultFirstTab !== false && $parts[0] !== '' ) {
			// add the default header
			$firstTabID = str_replace( ' ', '_', $wgHeaderTabsDefaultFirstTab );
			if ( $hasNewStructure ) {
				// MW 1.42+ or so
				$pageName = $parser->getTitle()->getFullText();
				$headline = "<h1 data-mw-anchor=\"$firstTabID\">$wgHeaderTabsDefaultFirstTab" .
					"<mw:editsection page=\"$pageName\">$wgHeaderTabsDefaultFirstTab</mw:editsection></h1>";
			} else {
				$headline = "<h1><span class=\"mw-headline\" id=\"$firstTabID\">" .
					"$wgHeaderTabsDefaultFirstTab</span></h1>";
			}

			array_unshift( $parts, $headline );
			$above = ''; // explicit
		} else {
			$above = $parts[0];
			// discard first part blank part
			array_shift( $parts ); // don't need above part anyway
		}

		$partslimit = $wgHeaderTabsRenderSingleTab ? 2 : 4;

		wfDebugLog( 'headertabs', __METHOD__ . ': parts (limit ' . $partslimit . '): ' . count( $parts ) );
		if ( $above !== '' ) {
			wfDebugLog( 'headertabs', __METHOD__ . ': we have text above our tabs' );
		}

		if ( count( $parts ) < $partslimit ) {
			return;
		}

		wfDebugLog( 'headertabs', __METHOD__ . ': split count OK, continuing' );

		// we have level 1 headers to parse, we'll want to render tabs
		$tabs = [];

		$s = 0;

		for ( $i = 0; $i < ( count( $parts ) / 2 ); $i++ ) {
			preg_match( $tabpatternmatch, $parts[$i * 2], $matches );

			// if this is a default tab, don't increment our section number
			if ( $s !== 0 || $i !== 0 || $wgHeaderTabsDefaultFirstTab === false ||
				$matches[3] !== $wgHeaderTabsDefaultFirstTab ) {
				++$s;
			}

			$tabsection = $s;
			$content = $parts[$i * 2 + 1];

			$tabid = $matches[2];
			$tabtitle = $matches[3];

			wfDebugLog( 'headertabs', __METHOD__ . ': found tab: ' . $tabtitle );

			// toc and section counter
			$subpatternsplit = '/(<h[2-6].+?<span[^>]+class="mw-headline"[^>]+id="[^"]+"[^>]*>' .
				'\s*.*?\s*<\/span>.*?<\/h[2-6]>)/';
			$subpatternmatch = '/<h([2-6]).+?<span[^>]+class="mw-headline"[^>]+id="([^"]+)"[^>]*>' .
				'\s*(.*?)\s*<\/span>.*?<\/h[2-6]>/';
			$subparts = preg_split( $subpatternsplit, $content, -1, PREG_SPLIT_DELIM_CAPTURE );
			if ( ( count( $subparts ) % 2 ) !== 0 ) {
				// don't need anything above first header
				array_shift( $subparts );
			}
			for ( $p = 0; $p < ( count( $subparts ) / 2 ); $p++ ) {
				preg_match( $subpatternmatch, $subparts[$p * 2], $submatches );
				++$s;
			}

			array_push( $tabs, [
				'tabid' => $tabid,
				'title' => $tabtitle,
				'tabcontent' => $content,
				'section' => $tabsection,
			] );
		}

		wfDebugLog( 'headertabs', __METHOD__ . ': generated ' . count( $tabs ) . ' tabs' );

		OOUI\Theme::setSingleton( new WikimediaUITheme() );
		OOUI\Element::setDefaultDir( 'ltr' );

		foreach ( $tabs as $i => $tab ) {
			$editHTML = '';
			if ( $wgHeaderTabsEditTabLink ) {
				$url = $parser->getTitle()->getInternalURL( [ 'action' => 'edit', 'section' => $tab['section'] ] );
				$editLink = Html::element( 'a', [ 'href' => $url ], wfMessage( 'headertabs-edittab' )->text() );
				$editHTML = Html::rawElement( 'span', [ 'class' => 'ht-editsection', 'id' => 'edittab' ],
					"[$editLink]" );
			}
			$tabPanels[] = new OOUI\TabPanelLayout( $tab['tabid'], [
				'classes' => [ 'section-' . $tab['section'] ],
				'label' => $tab['title'],
				'id' => $tab['tabid'],
				'content' => new OOUI\FieldsetLayout( [
					// 'label' => $tab['title'],
					'items' => [
						new OOUI\Widget( [
							'content' => new OOUI\HtmlSnippet( $editHTML . $tab['tabcontent'] )
						] ),
					],
				] ),
				'expanded' => false,
				'framed' => true,
			] );
		}
		$tabsIndexLayout = new OOUI\IndexLayout( [
			'infusable' => true,
			'expanded' => false,
			'autoFocus' => false,
			'id' => 'mw-tabs-id',
			'classes' => [ 'mw-tabs' ],
		] );
		$tabsIndexLayout->addTabPanels( $tabPanels );
		$tabsIndexLayout->setInfusable( true );
		$tabsPanelLayout = new OOUI\PanelLayout( [
			'framed' => true,
			'expanded' => false,
			'classes' => [ 'mw-header-tabs-wrapper' ],
			'content' => $tabsIndexLayout
		] );
		$tabHTML = Html::rawElement( 'div', [ 'id' => 'headertabs' ], $tabsPanelLayout );

		$text = $above . $tabHTML . $below;
	}

	/**
	 * @param Parser &$parser
	 * @param string $tabName
	 * @param string $linkText
	 * @param string $anotherTarget
	 * @return array
	 */
	public static function renderSwitchTabLink( &$parser, $tabName, $linkText, $anotherTarget = '' ) {
		// The cache unfortunately needs to be disabled for the
		// JavaScript for such links to work.
		$parser->getOutput()->updateCacheExpiry( 0 );

		$tabTitle = Title::newFromText( $tabName );
		$tabKey = $tabTitle->getDBkey();
		$sanitizedLinkText = $parser->recursiveTagParse( $linkText );

		if ( $anotherTarget != '' ) {
			$targetTitle = Title::newFromText( $anotherTarget );
			$targetURL = $targetTitle->getFullURL();
			$linkAttrs = [ 'href' => $targetURL . '#tab=' . $tabKey ];
		} else {
			$linkAttrs = [ 'href' => '#tab=' . $tabKey, 'class' => 'tabLink' ];
		}

		$sanitizedLinkText = $parser->recursiveTagParse( $linkText );
		$output = Html::element( 'a', $linkAttrs, $sanitizedLinkText );

		return [ $output, 'noparse' => true, 'isHTML' => true ];
	}

}
