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

wfLoadExtension( 'HeaderTabs' );

// Keep i18n globals so mergeMessageFileList.php doesn't break
$wgMessagesDirs['HeaderTabs'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['HeaderTabsMagic'] = __DIR__ . '/HeaderTabs.i18n.magic.php';
/* wfWarn(
	'Deprecated PHP entry point used for Header Tabs extension. Please use wfLoadExtension instead, ' .
	'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
); */
