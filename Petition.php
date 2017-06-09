<?php
if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'Petition' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['Petition'] = __DIR__ . '/i18n';
	$wgExtensionMessagesFiles['PetitionAlias'] = __DIR__ . '/Petition.alias.php';
	/*wfWarn(
		'Deprecated PHP entry point used for Petition extension. ' .
		'Please use wfLoadExtension instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);*/
	return;
} else {
	die( 'This version of the Petition extension requires MediaWiki 1.25+' );
}
