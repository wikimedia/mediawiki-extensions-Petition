<?php

class PetitionHooks {

	/**
	 * Adds database table to updater
	 */
	public static function getUpdates( DatabaseUpdater $updater ) {
		$updater->addExtensionTable( 'petition_data', __DIR__ . '/table.sql', true );
		return true;
	}
}
