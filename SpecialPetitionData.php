<?php

class SpecialPetitionData extends SpecialPage {
	public function __construct() {
		parent::__construct( 'PetitionData', 'view-petition-data' );
	}

	public function execute( $par ) {
		$this->checkPermissions();

		$this->getOutput()->setPageTitle( $this->msg( 'petitiondata' ) );
		$this->getOutput()->addWikiMsg( 'petition-data-intro' );

		$downloadTitle = $this->getPageTitle( 'csv' );
		$downloadText = $this->msg( 'petition-data-download' )->parse();
		$downloadLink = Linker::link( $downloadTitle, $downloadText,
			[ 'class' => 'mw-ui-button mw-ui-progressive' ] );
		$this->getOutput()->addHTML( $downloadLink );

		if ( $par == 'csv' ) {
			$dbr = wfGetDB( DB_REPLICA );
			$res = $dbr->select( 'petition_data', '*', 1, __METHOD__ );
			$this->csvOutput( $res );
		}
	}

	public function getSubpagesForPrefixSearch() {
		return [ 'csv' ];
	}

	private function csvOutput( $res ) {
		$ts = wfTimestampNow();
		$filename = "petition_data_$ts.csv";
		$this->getOutput()->disable();
		wfResetOutputBuffers();
		$response = $this->getRequest()->response();

		// Explicitly disable caching, just to be sure
		$response->header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', 0 ) . ' GMT' );
		$response->header( 'Cache-Control: no-cache, no-store, max-age=0, must-revalidate' );
		$response->header( 'Pragma: no-cache' );

		$response->header( "Content-disposition: attachment;filename={$filename}" );
		$response->header( "Content-type: text/csv; charset=utf-8" );
		$fh = fopen( 'php://output', 'w' );

		fputcsv( $fh, [ 'id', 'petitionname', 'source', 'name',
			'email', 'country', 'message', 'share', 'timestamp' ] );

		foreach ( $res as $row ) {
			fputcsv( $fh, [
				$row->pt_id,
				preg_replace( "/^=/", "'=", $row->pt_petitionname ),
				preg_replace( "/^=/", "'=", $row->pt_source ),
				preg_replace( "/^=/", "'=", $row->pt_name ),
				preg_replace( "/^=/", "'=", $row->pt_email ),
				preg_replace( "/^=/", "'=", $row->pt_country ),
				preg_replace( "/^=/", "'=", $row->pt_message ),
				$row->pt_share,
				wfTimestamp( TS_MW, $row->pt_timestamp )
			] );
		}

		fclose( $fh );
	}

}
