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

	/**
	 *
	 * @param string $input
	 * @param array $args
	 * @param \Parser $parser
	 * @return string
	 */
	public static function tag( $input, $args, $parser ) {
		$out = $parser->getOutput();
		$out->addModules( 'ext.headertabs' );

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
	 * @return true
	 */
	public static function replaceFirstLevelHeaders( &$parser, &$text, $aboveandbelow ) {
		global $wgHeaderTabsRenderSingleTab, $wgHeaderTabsDefaultFirstTab,
			$wgHeaderTabsDisableDefaultToc, $wgHeaderTabsGenerateTabTocs, $wgHeaderTabsEditTabLink;

		// ! @todo handle __NOTABTOC__, __TABTOC__, __FORCETABTOC__ here (2011-12-12, ofb)

		$below = $aboveandbelow[1];

		wfDebugLog( 'headertabs', __METHOD__ . ': detected header handling, checking' );

		if ( $below !== '' ) {
			wfDebugLog( 'headertabs', __METHOD__ . ': we have text below our tabs' );
		}

		// grab the TOC
		$toc = '';
		$tocpattern = '%<div id="toc" class="toc"><div id="toctitle"><h2>.+?</h2></div>' .
			"\n+" . '(<ul>' . "\n+" . '.+?</ul>)' . "\n+" . '</div>' . "\n+" . '%ms';
		if ( preg_match( $tocpattern, $aboveandbelow[0], $tocmatches, PREG_OFFSET_CAPTURE ) === 1 ) {
			wfDebugLog( 'headertabs', __METHOD__ . ': found the toc: ' . $tocmatches[0][1] );
			$toc = $tocmatches[0][0];
			// toc is first thing
			if ( $tocmatches[0][1] === 0 ) {
				wfDebugLog( 'headertabs', __METHOD__ . ': removed standard-pos TOC' );
				$aboveandbelow[0] = substr_replace( $aboveandbelow[0], '', $tocmatches[0][1],
					strlen( $tocmatches[0][0] ) );
			}
		}
		// toc is tricky, if you allow the auto-gen-toc,
		//	 and it's not at the top, but you end up with tabs... it could be embedded in a tab
		//	 but if it is at the top, and you have auto-first-tab, but only a toc is there,
		//	 	you don't really have an auto-tab

		// how many headers parts do we have? if not enough, bail out
		// text -- with defaulttab off = 1 parts
		//--render singletab=on here
		// text -- with defaulttab on = 2 parts
		// 1 header -- with defaulttab off = 2 parts
		// above, 1 header -- with defaulttab off = 3 parts
		//--render singletab=off here
		// above, 1 header -- with defaulttab on = 4 parts
		// 2 header -- with defaulttab on/off = 4 parts
		// above, 2 header -- with defaulttab off = 5 parts
		// above, 2 header -- with defaulttab on = 6 parts

		$tabpatternsplit = '/(<h1.+?<span[^>]+class="mw-headline"[^>]+id="[^"]+"[^>]*>\s*.*?\s*<\/span>.*?<\/h1>)/';
		$tabpatternmatch = '/<h(1).+?<span[^>]+class="mw-headline"[^>]+id="([^"]+)"[^>]*>\s*(.*?)\s*<\/span>.*?<\/h1>/';
		$parts = preg_split( $tabpatternsplit, trim( $aboveandbelow[0] ), -1, PREG_SPLIT_DELIM_CAPTURE );
		$above = '';

		// auto tab and the first thing isn't a header
		// (note we already removed the default toc, add it back later if needed)
		if ( $wgHeaderTabsDefaultFirstTab !== false && $parts[0] !== '' ) {
			// add the default header
			$firstTabID = str_replace( ' ', '_', $wgHeaderTabsDefaultFirstTab );
			$headline = "<h1><span class=\"mw-headline\" id=\"$firstTabID\">$wgHeaderTabsDefaultFirstTab</span></h1>";
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
			return true;
		}

		wfDebugLog( 'headertabs', __METHOD__ . ': split count OK, continuing' );

		// disable default TOC
		if ( $wgHeaderTabsDisableDefaultToc ) {
			// if it was somewhere else, we need to remove it
			if ( count( $tocmatches ) > 0 && $tocmatches[0][1] !== 0 ) {
				wfDebugLog( 'headertabs', __METHOD__ . ': removed non-standard-pos TOC' );
				// remove from above
				if ( $tocmatches[0][1] < strlen( $above ) ) {
					$above = substr_replace( $above, '', $tocmatches[0][1], strlen( $tocmatches[0][0] ) );
				} else {
					$tocmatches[0][1] -= strlen( $above );
					// it's in a tab
					for ( $i = 0; ( $i < count( $parts ) / 2 ); $i++ ) {
						if ( $tocmatches[0][1] < strlen( $parts[( $i * 2 ) + 1] ) ) {
							$parts[( $i * 2 ) + 1] = substr_replace( $parts[( $i * 2 ) + 1], '',
								$tocmatches[0][1], strlen( $tocmatches[0][0] ) );
							break;
						}
						$tocmatches[0][1] -= strlen( $parts[( $i * 2 ) + 1] );
					}
				}
			}
		} elseif ( count( $tocmatches ) > 0 && $tocmatches[0][1] === 0 ) {
			// add back a default-pos toc
			$above = $toc . $above;
		}

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

			if ( $wgHeaderTabsGenerateTabTocs ) {
				// @TODO This parsing no longer works in MW
				// 1.35 (and maybe even earlier versions).
				// @TODO Handle __TOC__, __FORCETOC__, __NOTOC__ here
				$tocparser = clone $parser;
				$tabtocraw = $tocparser->internalParse( $content );
				if ( preg_match( $tocpattern, $tabtocraw, $tabtocmatches ) === 1 ) {
					wfDebugLog( 'headertabs', __METHOD__ . ': generated toc for tab' );
					$tabtocraw = $tabtocmatches[0];
					$tabtoc = $tabtocraw;
					$itempattern = '/<li class="toclevel-[0-9]+"><a href="(#[^"]+)">' .
						'<span class="tocnumber">[0-9.]+<\/span> ' .
						'<span class="toctext">(<span>([^<]+)<\/span>[^<]+)<\/span><\/a>/';
					if ( preg_match_all( $itempattern, $tabtocraw, $tabtocitemmatches, PREG_SET_ORDER ) > 0 ) {
						foreach ( $tabtocitemmatches as $match ) {
							$newitem = $match[0];

							if ( count( $matches ) == 4 ) {
								$oldHref = $match[1];
								$newHref = '#' . trim( substr( $oldHref, ( strlen( $oldHref ) / 2 ) + 1 ) );
								$newitem = str_replace( $oldHref, $newHref, $newitem );
								$newitem = str_replace( $match[2], trim( $match[3] ), $newitem );
							}
							$tabtoc = str_replace( $match[0], $newitem, $tabtoc );
						}
						$content = $tabtoc . $content;
					}
				}
			}

			array_push( $tabs, [
				'tabid' => $tabid,
				'title' => $tabtitle,
				'tabcontent' => $content,
				'section' => $tabsection,
			] );
		}

		// ! @todo see if we can't add the SMW factbox stuff back in (2011-12-12, ofb)

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
		return true;
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
