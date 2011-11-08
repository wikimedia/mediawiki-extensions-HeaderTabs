<?php
/**
 * Header Tabs extension
 *
 * @file
 * @ingroup Extensions
 *
 * @author Sergey Chernyshev
 * @author Yaron Koren
 */

$htScriptPath = $wgScriptPath . '/extensions/HeaderTabs';

$dir = dirname( __FILE__ );
// the file loaded depends on whether the ResourceLoader exists, which in
// turn depends on what version of MediaWiki this is - for MW 1.17+,
// HeaderTabs_body.jq.php will get loaded
$rlMethod = array( 'OutputPage', 'addModules' );
if ( is_callable( $rlMethod ) ) {
	$wgAutoloadClasses['HeaderTabs'] = "$dir/HeaderTabs_body.jq.php";
} else {
	$wgAutoloadClasses['HeaderTabs'] = "$dir/HeaderTabs_body.yui.php";
}

$wgExtensionCredits['parserhook'][] = array(
	'path' => __FILE__,
	'name' => 'Header Tabs',
	'descriptionmsg' => 'headertabs-desc',
	'version' => '0.8.3',
	'author' => array( '[http://www.sergeychernyshev.com Sergey Chernyshev]', 'Yaron Koren' ),
	'url' => 'http://www.mediawiki.org/wiki/Extension:Header_Tabs'
);
// Translations
$wgExtensionMessagesFiles['HeaderTabs'] = $dir . '/HeaderTabs.i18n.php';

$htUseHistory = true;

$wgHooks['ParserFirstCallInit'][] = 'headerTabsParserFunctions';
$wgHooks['LanguageGetMagic'][] = 'headerTabsLanguageGetMagic';
$wgHooks['BeforePageDisplay'][] = 'HeaderTabs::addHTMLHeader';
$wgHooks['ParserAfterTidy'][] = 'HeaderTabs::replaceFirstLevelHeaders';

# Parser function to insert a link changing a tab:
function headerTabsParserFunctions( $parser ) {
	$parser->setHook( 'headertabs', array( 'HeaderTabs', 'tag' ) );
	$parser->setFunctionHook( 'switchtablink', array( 'HeaderTabs', 'renderSwitchTabLink' ) );
	return true;
}

function headerTabsLanguageGetMagic( &$magicWords, $langCode = "en" ) {
	switch ( $langCode ) {
	default:
		$magicWords['switchtablink']	= array ( 0, 'switchtablink' );
	}
	return true;
}
