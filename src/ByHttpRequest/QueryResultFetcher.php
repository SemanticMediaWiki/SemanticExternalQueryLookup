<?php

namespace SEQL\ByHttpRequest;

use Onoi\HttpRequest\HttpRequestFactory;
use SMWQuery as Query;
use SEQL\QueryEncoder;
use SEQL\QueryResultFactory;

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
	 * @var HttpResponseParser
	 */
	private $httpResponseParser;

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
	 * @param HttpResponseParser $httpResponseParser
	 */
	public function __construct( HttpRequestFactory $httpRequestFactory, QueryResultFactory $queryResultFactory, JsonResponseParser $httpResponseParser ) {
		$this->httpRequestFactory = $httpRequestFactory;
		$this->queryResultFactory = $queryResultFactory;
		$this->httpResponseParser = $httpResponseParser;
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

		$querySource = $query->getQuerySource();

		// Reset to get the correct IW
		foreach ( $query->getExtraPrintouts() as $printrequests ) {

			if ( $printrequests->getData() === null ) {
				continue;
			}

			$property = $printrequests->getData()->getDataItem();
			$property->setInterwiki( $querySource );

			$printrequests->getData()->setDataItem( $property );
		}

		$result = $this->doMakeHttpRequestFor( $query );

		if ( $result === array() || $result === false || $result === null ) {
			return $this->queryResultFactory->newEmptyQueryResult( $query );
		}

		if ( isset( $result['error'] ) ) {
			$query->addErrors( isset( $result['error']['info'] ) ? array( $result['error']['info'] ) : $result['error']['query'] );
			return $this->queryResultFactory->newEmptyQueryResult( $query );
		}

		$this->httpResponseParser->doParse( $result  );

		$queryResult = $this->queryResultFactory->newByHttpRequestQueryResult(
			$query,
			$this->httpResponseParser
		);

		$queryResult->setRemoteTargetUrl(
			str_replace( '$1', '',  $this->repositoryTargetUrl )
		);

		return $queryResult;
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
