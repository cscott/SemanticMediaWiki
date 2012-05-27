<?php

/**
 * API module to query SMW by providing a query in the ask language. 
 *
 * @since 1.6.2
 *
 * @file ApiAsk.php
 * @ingroup SMW
 * @ingroup API
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ApiAsk extends ApiSMWQuery {
	
	public function execute() {
		$params = $this->extractRequestParams();

		// TODO: this prevents doing [[Category:Foo||bar||baz]], must document.
		$rawParams = explode( '|', $params['query'] );
		$queryString = '';
		$printouts = array();
		
		SMWQueryProcessor::processFunctionParams( $rawParams, $queryString, $this->parameters, $printouts );

		$queryResult = $this->getQueryResult( $this->getQuery(
			$queryString,
			$printouts
		) );
		
		$this->addQueryResult( $queryResult );
	}

	public function getAllowedParams() {
		return array(
			'query' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			),
		);
	}
	
	public function getParamDescription() {
		return array(
			'query' => 'The query string in ask-language'
		);
	}
	
	public function getDescription() {
		return array(
			'API module to query SMW by providing a query in the ask language.
			This API module is in alpha stage, and likely to see changes in upcomming versions of SMW.'
		);
	}

	protected function getExamples() {
		return array(
			'api.php?action=ask&query=[[Modification%20date::%2B]]|%3FModification%20date|sort%3DModification%20date|order%3Ddesc',
		);
	}	
	
	public function getVersion() {
		return __CLASS__ . ': $Id$';
	}		
	
}
