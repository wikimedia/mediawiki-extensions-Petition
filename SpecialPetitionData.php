<?php

class SpecialPetitionData extends SpecialPage {
	function __construct() {
		parent::__construct( 'PetitionData', 'view-petition-data' );
	}

	function execute($par) {

		$this->checkPermissions();

		$this->getOutput()->setPageTitle( $this->msg( 'petitiondata' ) );
		$this->getOutput()->addWikiMsg( 'petition-data-intro' );

		$downloadTitle = $this->getPageTitle( 'csv' );
		$downloadText = $this->msg( 'petition-data-download' )->parse();
		$downloadLink = Linker::link( $downloadTitle, $downloadText, array( 'class' => 'mw-ui-button mw-ui-primary') );
		$this->getOutput()->addHTML( $downloadLink );

		if ( $par == 'csv' ) {
			$dbr = wfGetDB( DB_SLAVE );
			$res = $dbr->select( 'petition_data', '*', 1, __METHOD__ );
			$this->csvOutput( $res );
		}
	}

	function csvOutput( $res ) {

		$ts = wfTimestampNow();
		$filename = "petition_data_$ts.csv";
		$this->getOutput()->disable();
		wfResetOutputBuffers();
		$this->getRequest()->response()->header( "Content-disposition: attachment;filename={$filename}" );
		$this->getRequest()->response()->header( "Content-type: text/csv; charset=utf-8" );
		$fh = fopen( 'php://output', 'w' );

		fputcsv( $fh, array('id', 'petitionname', 'pagetitle', 'source', 'name',
			'email', 'country', 'message', 'share', 'timestamp'));

		foreach( $res as $row ) {

			fputcsv( $fh, array(
				$row->pt_id,
				preg_replace("/^=/", "'=", $row->pt_petitionname),
				preg_replace("/^=/", "'=", $row->pt_pagetitle),
				preg_replace("/^=/", "'=", $row->pt_source),
				preg_replace("/^=/", "'=", $row->pt_name),
				preg_replace("/^=/", "'=", $row->pt_email),
				preg_replace("/^=/", "'=", $row->pt_country),
				preg_replace("/^=/", "'=", $row->pt_message),
				$row->pt_share,
				wfTimestamp( TS_MW, $row->pt_timestamp )
				)
			);

		}

		fclose( $fh );
	}

}
