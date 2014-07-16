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

## Petition form
$wgAutoloadClasses['SpecialPetition'] = __DIR__ . "/SpecialPetition.php";
$wgSpecialPages['Petition'] = 'SpecialPetition';

## Petition data download page
$wgAutoloadClasses['SpecialPetitionData'] = __DIR__ . "/SpecialPetitionData.php";
$wgSpecialPages['PetitionData'] = 'SpecialPetitionData';

$wgMessagesDirs['Petition'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['PetitionDataAlias'] = __DIR__ . '/Petition.alias.php';

$wgPetitionDatabase = false;

$wgLogTypes[] = 'petition';
$wgLogActionsHandlers['petition/sign'] = 'LogFormatter';

$wgLogRestrictions['petition'] = 'view-petition-data';

# Schema updates for update.php
$wgHooks['LoadExtensionSchemaUpdates'][] = function( DatabaseUpdater $updater ) {
	$updater->addExtensionTable( 'petition_data', __DIR__ . '/table.sql', true );
	return true;
};

$wgGroupPermissions['petitiondata']['view-petition-data'] = true;
$wgAvailableRights[] = 'view-petition-data';

/**
 * Options:
 *
 * $wgPetitionCountCacheTime
 * 	time in seconds that the number of signatures count will be cached
 * 	in memcached (if available). Default is 86400 i.e. 24 hours
 */

$wgPetitionCountCacheTime = 86400;
