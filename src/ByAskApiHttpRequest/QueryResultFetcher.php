<?php

namespace SEQL\ByAskApiHttpRequest;

use Onoi\HttpRequest\HttpRequestFactory;
use SEQL\QueryEncoder;
use SEQL\QueryResultFactory;
use SMWQuery as Query;

/**
 * @license GNU GPL v2+
 * @since 0.1
 *
 * @author mwjames
 */
class QueryResultFetcher {

	/**
	 * @var HttpRequestFactory
	 */
	private $httpRequestFactory;

	/**
	 * @var QueryResultFactory
	 */
	private $queryResultFactory;

	/**
	 * @var JsonResponseParser
	 */
	private $jsonResponseParser;

	/**
	 * @var string
	 */
	private $httpRequestEndpoint = '';

	/**
	 * @var string
	 */
	private $repositoryTargetUrl = '';

	/**
	 * @since 0.1
	 *
	 * @param HttpRequestFactory $httpRequestFactory
	 * @param QueryResultFactory $queryResultFactory
	 * @param JsonResponseParser $jsonResponseParser
	 */
	public function __construct( HttpRequestFactory $httpRequestFactory, QueryResultFactory $queryResultFactory, JsonResponseParser $jsonResponseParser ) {
		$this->httpRequestFactory = $httpRequestFactory;
		$this->queryResultFactory = $queryResultFactory;
		$this->jsonResponseParser = $jsonResponseParser;
	}

	/**
	 * @since 0.1
	 *
	 * @param string $httpRequestEndpoint
	 */
	public function setHttpRequestEndpoint( $httpRequestEndpoint ) {
		$this->httpRequestEndpoint = $httpRequestEndpoint;
	}

	/**
	 * @since 0.1
	 *
	 * @param string $repositoryTargetUrl
	 */
	public function setRepositoryTargetUrl( $repositoryTargetUrl ) {
		$this->repositoryTargetUrl = $repositoryTargetUrl;
	}

	/**
	 * @since 0.1
	 *
	 * @param Query $query
	 *
	 * @return QueryResult
	 */
	public function fetchQueryResult( Query $query ) {

		$this->doResetPrintRequestsToQuerySource( $query );

		$result = $this->doMakeHttpRequestFor( $query );

		if ( $result === array() || $result === false || $result === null ) {
			return $this->queryResultFactory->newEmptyQueryResult( $query );
		}

		if ( isset( $result['error'] ) ) {
			$query->addErrors( isset( $result['error']['info'] ) ? array( $result['error']['info'] ) : $result['error']['query'] );
			return $this->queryResultFactory->newEmptyQueryResult( $query );
		}

		$this->jsonResponseParser->doParse( $result  );

		$queryResult = $this->queryResultFactory->newByAskApiHttpRequestQueryResult(
			$query,
			$this->jsonResponseParser
		);

		$queryResult->setRemoteTargetUrl(
			str_replace( '$1', '',  $this->repositoryTargetUrl )
		);

		return $queryResult;
	}

	private function doResetPrintRequestsToQuerySource( $query ) {

		$querySource = $query->getQuerySource();

		foreach ( $query->getExtraPrintouts() as $printRequest ) {

			if ( $printRequest->getData() === null ) {
				continue;
			}

			$property = $printRequest->getData()->getDataItem();
			$property->setInterwiki( $querySource );

			$printRequest->getData()->setDataItem( $property );

			// Reset label after dataItem was re-added
			$printRequest->setLabel( $printRequest->getLabel() );
		}
	}

	private function doMakeHttpRequestFor( $query ) {

		$httpRequest = $this->httpRequestFactory->newCurlRequest();

		$httpRequest->setOption( CURLOPT_FOLLOWLOCATION, true );

		$httpRequest->setOption( CURLOPT_RETURNTRANSFER, true );
		$httpRequest->setOption( CURLOPT_FAILONERROR, true );
		$httpRequest->setOption( CURLOPT_SSL_VERIFYPEER, false );

		$httpRequest->setOption( CURLOPT_URL, $this->httpRequestEndpoint . '?action=ask&format=json&query=' . QueryEncoder::rawUrlEncode( $query ) );

		$httpRequest->setOption( CURLOPT_HTTPHEADER, array(
			'Accept: application/json',
			'Content-Type: application/json; charset=utf-8'
		) );

		$response = $httpRequest->execute();

		return json_decode( $response, true );
	}

}
