<?php

class SpecialPetition extends IncludableSpecialPage {
	function __construct() {
		parent::__construct( 'Petition' );
	}

	function execute($par) {

		$request = $this->getRequest();
		$out = $this->getOutput();

		// Can have multiple named petitions using {{Special:Petition/foo}}
		// Can also specify am optional tracking parameter e.g. {{Special:Petition/foo/email}}
		$arr = explode('/', $par);
		$petitionName = isset($arr[0]) ? $arr[0] : 'default';
		$source = isset($arr[1]) ? $arr[1] : '';

		$this->setHeaders();
		$this->outputHeader();

		$user = $this->getUser();
		if ( $user->isBlocked() ) {
			$out->addWikiMsg( 'petition-form-blocked' );
			return;
		}

		$out->addModules( 'ext.Petition' );

		$countries = SpecialPetition::getCountryArray( $this->getLanguage()->getCode() );
		$form = SpecialPetition::defineForm( $petitionName, $source, $countries );

		$form->prepareForm();

		$result = $form->tryAuthorizedSubmit();

		if ( $result === true || ( $result instanceof Status && $result->isGood() ) ) {
			$htmlOut = '<span class="petition-done">' . wfMessage('petition-done')->text() . '</span>';
		} else {
			$htmlOut = '<div class="petition-form">' . "\n";
			$numberOfSignatures = SpecialPetition::getNumberOfSignatures( $petitionName );
			$htmlOut .= '<div id="petition-num-signatures">';
			$htmlOut .= wfMessage('petition-num-signatures', $numberOfSignatures)->escaped();
			$htmlOut .= '</div>' . "\n";
			// Add the form, with any errors if there was an attempted submission
			$htmlOut .= $form->getHtml($result) . "\n";
			$htmlOut .= '</div>' . "\n";
		}

		$out->addHtml($htmlOut);

	}

	/**
	 * Save into petition_data table
	 *
	 * @param $formData
	 * @return true if success
	 */
	static function petitionSubmit( $formData ) {
		global $wgPetitionDatabase;

		$dbw = wfGetDB( DB_MASTER, array(), $wgPetitionDatabase );
		$dbw->insert( 'petition_data', array(
				'pt_petitionname' => $formData['petitionname'],
				'pt_source'       => $formData['source'],
				'pt_name'         => $formData['name'],
				'pt_email'        => $formData['email'],
				'pt_country'      => $formData['country'],
				'pt_message'      => $formData['personalmessage'],
				'pt_share'        => $formData['share'],
				'pt_timestamp'    => $dbw->timestamp()
				),
			__METHOD__ );

		return true;
	}

	/**
	 * Get the number of signatures for a given petition.
	 *
	 * @param string $petitionName
	 * @return int The current number of signatures
	 */
	static function getNumberOfSignatures( $petitionName ) {
		$dbr = wfGetDB( DB_SLAVE );
		$num = $dbr->selectField( 'petition_data',
			'count(pt_id)',
			array('pt_petitionname' => $petitionName)
			);
		return $num;
	}

	/**
	 * Retrieve the list of countries in given language via CLDR
	 *
	 * @param string $language ISO code of required language
	 * @return array Countries with names as keys and ISO codes as values
	 */
	static function getCountryArray( $language ) {
		$countries = array();
		if ( is_callable( array( 'CountryNames', 'getNames' ) ) ) {
			// Need to flip as HTMLForm requires display name as the key
			$countries = array_flip( CountryNames::getNames( $language ) );
			ksort($countries);
		} else {
			throw new MWException( 'Petition requires Extension:CLDR to be installed.' );
		}

		return $countries;
	}

	static function defineForm( $petitionName, $source, $countries ) {
		$formDescriptor = array(
			'petitionname' => array(
				'type' => 'hidden',
				'default' => $petitionName,
			),
			'source' => array(
				'type' => 'hidden',
				'default' => $source,
			),
			'name' => array(
				'type' => 'text',
				'label-message' => 'petition-form-name',
				'required' => true,
			),
			'email' => array(
				'type' => 'text',
				'label-message' => 'petition-form-email',
				'required' => true,
			),
			'country' => array(
				'type' => 'select',
				'label-message' => 'petition-form-country',
				'options' => $countries,
			),
			'personalmessage' => array(
				'type' => 'textarea',
				'label-message' => 'petition-form-message',
				'rows' => 4,
			),
			'share' => array(
				'type' => 'check',
				'hidelabel' => true, // otherwise get an extra empty <label> element
				'label-raw' => wfMessage('petition-form-share')->parse(),
				'cssclass'  => 'plainlinks',
			),
			'privacy' => array(
				'type' => 'info',
				'default' => wfMessage('petition-form-privacy')->parse(),
				'raw' => true,
			),
		);

		$form = new HTMLForm( $formDescriptor, RequestContext::getMain(), 'petition' );
		$form->setDisplayFormat( 'vform' );
		$form->setId( 'petition-form' );
		$form->setSubmitText( wfMessage( 'petition-form-submit' )->text() );
		$form->setSubmitCallback( array( 'SpecialPetition', 'petitionSubmit' ) );

		return $form;
	}

}
