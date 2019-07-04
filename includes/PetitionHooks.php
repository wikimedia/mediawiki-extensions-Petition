<?php

class PetitionHooks {

	/**
	 * Adds database table to updater
	 * @param DatabaseUpdater $updater
	 */
	public static function onLoadExtensionSchemaUpdates( DatabaseUpdater $updater ) {
		$updater->addExtensionTable(
			'petition_data', dirname( __DIR__ ) . '/sql/table.sql'
		);
	}
}
