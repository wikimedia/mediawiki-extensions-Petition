<?php
class Petition {

	public static function onParserInit( Parser $parser ) {
		$parser->setHook( 'petition', array( __CLASS__, 'petitionRender' ) );
		return true;
	}

	public static function petitionRender( $input, array $args, Parser $parser, PPFrame $frame ) {
		global $wgRequest;

		// Can have multiple named petitions using <petition name="foo"/>
		$petitionName = isset($args['name']) ? $args['name'] : 'default';
		// Optional "linksource" URL parameter, to track how people arrived at petition
		$linkSource = $wgRequest->getVal( 'linksource' );

		$parser->disableCache(); // Need cache disabled for number of signatures to update, and for thank you message

		$parser->getOutput()->addModules( 'ext.Petition' );

		$countries = Petition::getCountryArray( $parser->getOptions()->getUserLang() );
		$form = Petition::defineForm( $petitionName, $parser->getTitle()->getPrefixedText(), $linkSource, $countries );

		$form->prepareForm();

		$result = $form->tryAuthorizedSubmit();
		if ( $result === true || ( $result instanceof Status && $result->isGood() ) ) {
			return '<span class="petition-done">' . wfMessage('petition-done')->text() . '</span>';
		}

		$htmlOut = '<div class="petition-form">' . "\n";
		// Add the number of signatures first above the form.
		$numberOfSignatures = Petition::getNumberOfSignatures( $petitionName );
		$htmlOut .= '<div id="petition-num-signatures">';
		$htmlOut .= wfMessage('petition-num-signatures', $numberOfSignatures)->text();
		$htmlOut .= '</div>' . "\n";
		// Add the form, with any errors if there was an attempted submission
		$htmlOut .= $form->getHtml($result) . "\n";
		$htmlOut .= '</div>' . "\n";

		return $htmlOut;
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
				'pt_pagetitle'    => $formData['pagetitle'],
				'pt_source'       => $formData['linksource'],
				'pt_name'         => $formData['name'],
				'pt_email'        => $formData['email'],
				'pt_country'      => $formData['country'],
				'pt_message'      => $formData['personalmessage'],
				'pt_share'        => $formData['share'],
				'pt_timestamp'    => wfTimestampNow(TS_DB)
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
			'count(*)',
			array('pt_petitionname' => $petitionName)
			);
		return $num;
	}

	/**
	 * Retrieve the list of countries in given language via CLDR
	 * If CLDR not available, use a fallback list in English
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

	static function defineForm( $petitionName, $pageTitle, $linkSource, $countries ) {
		$formDescriptor = array(
			'petitionname' => array(
				'type' => 'hidden',
				'default' => $petitionName,
			),
			'pagetitle' => array(
				'type' => 'hidden',
				'default' => $pageTitle,
			),
			'linksource' => array(
				'type' => 'hidden',
				'default' => $linkSource,
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
		$form->setSubmitCallback( array( 'Petition', 'petitionSubmit' ) );

		return $form;
	}

}