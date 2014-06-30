<?php
// Petitions

$wgExtensionCredits['other'][] = array(
	'path'   => __FILE__,
	'name'   => 'Petition',
	'author' => array('Peter Coombe', 'Andrew Garrett'),
	'url'    => 'https://www.mediawiki.org/wiki/Extension:Petition',
	'descriptionmsg' => 'petition-desc',
	'license-name' => 'GPL',
);

$wgResourceModules['ext.Petition'] = array(
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'Petition',
	'scripts'       => 'petition.js',
	'styles'        => 'petition.css',
	'dependencies'  => 'mediawiki.ui'
);

## Petition data download page
$wgAutoloadClasses['SpecialPetitionData'] = __DIR__ . "/SpecialPetitionData.php";
$wgSpecialPages['PetitionData'] = 'SpecialPetitionData';

## Petition tag
$wgAutoloadClasses['Petition'] = __DIR__ . '/TagPetition.php';
$wgHooks['ParserFirstCallInit'][] = 'Petition::onParserInit';

$wgMessagesDirs['Petition'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['PetitionDataAlias'] = __DIR__ . '/Petition.alias.php';

$wgPetitionDatabase = false;

# Schema updates for update.php
$wgHooks['LoadExtensionSchemaUpdates'][] = function( DatabaseUpdater $updater ) {
	$updater->addExtensionTable( 'petition_data', __DIR__ . '/table.sql', true );
	return true;
};