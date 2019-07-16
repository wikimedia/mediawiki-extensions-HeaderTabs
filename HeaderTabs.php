<?php
/**
 * Header Tabs extension
 *
 * @file
 * @ingroup Extensions
 *
 * @author Sergey Chernyshev
 * @author Yaron Koren
 * @author Finlay Beaton
 */

// Protect against entries
if ( !defined( 'MEDIAWIKI' ) ) {
	die();
}

// Allow exension registration mechanism
if ( function_exists( 'wfLoadExtension' ) ) {
        wfLoadExtension( 'HeaderTabs' );
        // Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['HeaderTabs'] = __DIR__ . '/i18n';
	$wgExtensionMessagesFiles['HeaderTabsMagic'] = __DIR__ . '/HeaderTabs.i18n.magic.php';
        /* wfWarn(
                'Deprecated PHP entry point used for Semanti Forms extension. Please use wfLoadExtension instead, ' .
                'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
        ); */
        return;
}

// Show extension credits
$wgExtensionCredits['parserhook'][] = array(
	'path' => __FILE__,
	'name' => 'Header Tabs',
	'descriptionmsg' => 'headertabs-desc',
	'version' => '1.2',
	'author' => array(
		'[https://www.sergeychernyshev.com Sergey Chernyshev]',
		'Yaron Koren',
		'[https://ofbeaton.com Finlay Beaton]',
		'...'
	),
	'url' => 'https://www.mediawiki.org/wiki/Extension:Header_Tabs',
	'license-name' => 'GPL-2.0-or-later'
);

// Translations
$wgMessagesDirs['HeaderTabs'] = __DIR__ . '/i18n';

//! @todo implement in tab parsing code instead... but problems like nowiki (2011-12-12, ofb)
// if you make them here, it will be article wide instead of tab-wide
// __NOTABTOC__, __TABTOC__, __NOEDITTAB__
// and one day with a special page: __NEWTABLINK__, __NONEWTABLINK__
// and one day if we can force toc generation: __FORCETABTOC__
$wgExtensionMessagesFiles['HeaderTabsMagic'] = __DIR__ . '/HeaderTabs.i18n.magic.php';

// Config
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

// Register hooks
$wgHooks['ParserFirstCallInit'][] = 'HeaderTabsHooks::registerParserFunctions';
$wgHooks['ParserAfterTidy'][] = 'HeaderTabsHooks::replaceFirstLevelHeaders';
$wgHooks['ResourceLoaderGetConfigVars'][] = 'HeaderTabsHooks::addConfigVarsToJS';
$wgHooks['MakeGlobalVariablesScript'][] = 'HeaderTabsHooks::setGlobalJSVariables';

// Load classes
$wgAutoloadClasses['HeaderTabsHooks'] = __DIR__ . '/HeaderTabs.hooks.php';
$wgAutoloadClasses['HeaderTabs'] = __DIR__ . '/HeaderTabs_body.php';

// Register modules
$wgResourceModules['ext.headertabs'] = array(
	'scripts' => 'skins/ext.headertabs.core.js',
	'dependencies' => array( 'jquery.ui.tabs' ),
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'HeaderTabs',
);

$wgResourceModules['ext.headertabs.bare'] = array(
	'styles' => 'skins/ext.headertabs.bare.css',
	'dependencies' => array( 'ext.headertabs' ),
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'HeaderTabs',
);

$wgResourceModules['ext.headertabs.large'] = array(
	'styles' => 'skins/ext.headertabs.large.css',
	'dependencies' => array( 'ext.headertabs' ),
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'HeaderTabs',
);

$wgResourceModules['ext.headertabs.timeless'] = array(
	'styles' => 'skins/ext.headertabs.timeless.css',
	'dependencies' => array( 'ext.headertabs' ),
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'HeaderTabs',
);
