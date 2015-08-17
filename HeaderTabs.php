<?php
/**
 * Header Tabs extension
 *
 * @file
 * @ingroup Extensions
 *
 * @author Sergey Chernyshev
 * @author Yaron Koren
 * @author Olivier Finlay Beaton
 */

if ( !defined( 'MEDIAWIKI' ) ) die();

$wgExtensionCredits['parserhook'][] = array(
	'path' => __FILE__,
	'name' => 'Header Tabs',
	'descriptionmsg' => 'headertabs-desc',
	'version' => '1.0.2',
	'author' => array( '[http://www.sergeychernyshev.com Sergey Chernyshev]', 'Yaron Koren', '[http://olivierbeaton.com Olivier Finlay Beaton]' ),
	'url' => 'https://www.mediawiki.org/wiki/Extension:Header_Tabs'
);

$dir = dirname( __FILE__ );

// Translations
$wgMessagesDirs['HeaderTabs'] = $dir . '/i18n';
$wgExtensionMessagesFiles['HeaderTabs'] = $dir . '/HeaderTabs.i18n.php';

//! @todo implement in tab parsing code instead... but problems like nowiki (2011-12-12, ofb)
// if you make them here, it will be article wide instead of tab-wide
// __NOTABTOC__, __TABTOC__, __NOEDITTAB__
// and one day with a special page: __NEWTABLINK__, __NONEWTABLINK__
// and one day if we can force toc generation: __FORCETABTOC__
$wgExtensionMessagesFiles['HeaderTabsMagic'] = $dir . '/HeaderTabs.i18n.magic.php';

// Config
$wgHeaderTabsScriptPath = $wgScripPath . "/extensions/HeaderTabs";
$wgHeaderTabsUseHistory = true;
$wgHeaderTabsRenderSingleTab = false;
$wgHeaderTabsAutomaticNamespaces = array();
$wgHeaderTabsDefaultFirstTab = false;
$wgHeaderTabsDisableDefaultToc = true;
$wgHeaderTabsGenerateTabTocs = false;
$wgHeaderTabsStyle = 'large';
$wgHeaderTabsEditTabLink = true;

// Other variables
$wgHeaderTabsTabIndexes = array();

// Extension:Configure
if ( isset( $wgConfigureAdditionalExtensions ) && is_array( $wgConfigureAdditionalExtensions ) ) {

	/**
	 * attempt to tell Extension:Configure how to web configure our extension
	 * @since 2011-09-22, 0.2
	 */
	$wgConfigureAdditionalExtensions[] = array(
			'name' => 'HeaderTabs',
			'settings' => array(
					'wgHeaderTabsUseHistory' => 'bool',
					'wgHeaderTabsRenderSingleTab' => 'bool',
					'wgHeaderTabsAutomaticNamespaces' => 'array',
					'wgHeaderTabsDefaultFirstTab' => 'string',
					'wgHeaderTabsDisableDefaultToc' => 'bool',
					'wgHeaderTabsGenerateTabTocs' => 'bool',
					'wgHeaderTabsStyle' => 'string',
					'wgHeaderTabsEditTabLink' => 'bool',
				),
			'array' => array(
					'wgHeaderTabsAutomaticNamespaces' => 'simple',
				),
			'schema' => false,
			'url' => 'https://www.mediawiki.org/wiki/Extension:Header_Tabs',
		);

} // $wgConfigureAdditionalExtensions exists

$wgHooks['ParserFirstCallInit'][] = 'HeaderTabsHooks::registerParserFunctions';
$wgHooks['BeforePageDisplay'][] = 'HeaderTabsHooks::addHTMLHeader';
$wgHooks['ParserAfterTidy'][] = 'HeaderTabsHooks::replaceFirstLevelHeaders';
$wgHooks['ResourceLoaderGetConfigVars'][] = 'HeaderTabsHooks::addConfigVarsToJS';
$wgHooks['MakeGlobalVariablesScript'][] = 'HeaderTabsHooks::setGlobalJSVariables';

$wgAutoloadClasses['HeaderTabsHooks'] = "$dir/HeaderTabs.hooks.php";
$wgAutoloadClasses['HeaderTabs'] = "$dir/HeaderTabs_body.php";

$wgResourceModules['ext.headertabs'] = array(
	// JavaScript and CSS styles. To combine multiple files, just list them as an array.
	'scripts' => 'skins/ext.headertabs.core.js',
	// 'styles' => // the style is added in HeaderTabs::addHTMLHeader

	// If your scripts need code from other modules, list their identifiers as dependencies
	// and ResourceLoader will make sure they're loaded before you.
	// You don't need to manually list 'mediawiki' or 'jquery', which are always loaded.
	'dependencies' => array( 'jquery.ui.tabs' ),

	// ResourceLoader needs to know where your files are; specify your
	// subdir relative to "/extensions" (or $wgExtensionAssetsPath)
	'localBasePath' => dirname( __FILE__ ),
	'remoteExtPath' => 'HeaderTabs',
);
