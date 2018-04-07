<?php

class PetitionHooks {

	/**
	 * Adds database table to updater
	 * @param DatabaseUpdater $updater
	 * @return true
	 */
	public static function getUpdates( DatabaseUpdater $updater ) {
		$updater->addExtensionTable( 'petition_data', __DIR__ . '/table.sql' );
		return true;
	}
}
