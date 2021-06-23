<?php

namespace SEQL\ByHttpRequest;

use Onoi\HttpRequest\HttpRequestFactory;
use SEQL\QueryEncoder;
use SEQL\QueryResultFactory;
use SMWQuery as Query;

/**
 * @license GNU GPL v2+
 * @since 1.0
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
	 * @var string
	 */
	private $httpResponseCachePrefix;

	/**
	 * @var integer
	 */
	private $httpResponseCacheLifetime;

	/**
	 * @var array
	 */
	private $credentials;

	/**
	 * @var string
	 */
	private static $cookies;

	/**
	 * @since 1.0
	 *
	 * @param HttpRequestFactory $httpRequestFactory
	 * @param QueryResultFactory $queryResultFactory
	 * @param JsonResponseParser $jsonResponseParser
	 */
	public function __construct( HttpRequestFactory $httpRequestFactory, QueryResultFactory $queryResultFactory, JsonResponseParser $jsonResponseParser, $credentials ) {
		$this->httpRequestFactory = $httpRequestFactory;
		$this->queryResultFactory = $queryResultFactory;
		$this->jsonResponseParser = $jsonResponseParser;
		$this->credentials = $credentials;
	}

	/**
	 * @since 1.0
	 *
	 * @param string $httpRequestEndpoint
	 */
	public function setHttpRequestEndpoint( $httpRequestEndpoint ) {
		$this->httpRequestEndpoint = $httpRequestEndpoint;
	}

	/**
	 * @since 1.0
	 *
	 * @param string $repositoryTargetUrl
	 */
	public function setRepositoryTargetUrl( $repositoryTargetUrl ) {
		$this->repositoryTargetUrl = $repositoryTargetUrl;
	}

	/**
	 * @since 1.0
	 *
	 * @param string $httpResponseCachePrefix
	 */
	public function setHttpResponseCachePrefix( $httpResponseCachePrefix ) {
		$this->httpResponseCachePrefix = $httpResponseCachePrefix;
	}

	/**
	 * @since 1.0
	 *
	 * @param integer $httpResponseCacheLifetime
	 */
	public function setHttpResponseCacheLifetime( $httpResponseCacheLifetime ) {
		$this->httpResponseCacheLifetime = $httpResponseCacheLifetime;
	}

	/**
	 * Authenticates query against remote wiki using 'login' api and stores
	 * cookies to use on other requests
	 *
	 * @param array $credentials
	 */
	public function doAuthenticateRemoteWiki( $credentials ) {

		$cookiefile = 'seql_'.time();

		$httpRequest = $this->httpRequestFactory->newCurlRequest();

		$httpRequest->setOption( CURLOPT_FOLLOWLOCATION, true );

		$httpRequest->setOption( CURLOPT_RETURNTRANSFER, true );
		$httpRequest->setOption( CURLOPT_FAILONERROR, true );
		$httpRequest->setOption( CURLOPT_SSL_VERIFYPEER, false );
		$httpRequest->setOption( CURLOPT_COOKIESESSION, true );
		$httpRequest->setOption( CURLOPT_COOKIEJAR, $cookiefile );
		$httpRequest->setOption( CURLOPT_COOKIEFILE, $cookiefile );

		$httpRequest->setOption( CURLOPT_URL, $this->httpRequestEndpoint . '?action=query&format=json&meta=tokens&type=login' );

		$response = $httpRequest->execute();
		$result = json_decode( $response, true );

		if( isset( $result['query']['tokens']['logintoken'] ) ) {

			$token = $result['query']['tokens']['logintoken'];

			$httpRequest->setOption( CURLOPT_FOLLOWLOCATION, true );
			$httpRequest->setOption( CURLOPT_RETURNTRANSFER, true );
			$httpRequest->setOption( CURLOPT_FAILONERROR, true );
			$httpRequest->setOption( CURLOPT_SSL_VERIFYPEER, false );
			$httpRequest->setOption( CURLOPT_POST, true );
			$httpRequest->setOption( CURLOPT_URL, $this->httpRequestEndpoint );
			$httpRequest->setOption( CURLOPT_COOKIEJAR, $cookiefile );
			$httpRequest->setOption( CURLOPT_COOKIEFILE, $cookiefile );

			$httpRequest->setOption( CURLOPT_POSTFIELDS, http_build_query( array(
					'action' => 'login',
					'format' => 'json',
					'lgname' => $credentials['username'],
					'lgpassword' => $credentials['password'],
					'lgtoken' => $token
				))
			);

			$response = $httpRequest->execute();
			$result = json_decode( $response, true );

			if ( isset( $result['login']['lguserid'] ) ) {
				self::$cookies = $cookiefile;
			}

		}

	}

	/**
	 * @since 1.0
	 *
	 * @param Query $query
	 *
	 * @return QueryResult
	 */
	public function fetchQueryResult( Query $query ) {

		$this->doResetPrintRequestsToQuerySource( $query );

		if( $this->credentials && !self::$cookies ) {
			$this->doAuthenticateRemoteWiki( $this->credentials );
		}

		list( $result, $isFromCache ) = $this->doMakeHttpRequestFor( $query );

		if ( $result === array() || $result === false || $result === null ) {
			return $this->queryResultFactory->newEmptyQueryResult( $query );
		}

		if ( isset( $result['error'] ) ) {
			$query->addErrors(
				isset( $result['error']['info'] ) ? array( implode( ', ', $result['error'] ) ) : $result['error']['query']
			);

			return $this->queryResultFactory->newEmptyQueryResult( $query );
		}

		// Add the source from where the result was retrieved
		if ( isset( $result['query']['meta']['source'] ) ) {
			$result['query']['meta']['source'] = $query->getQuerySource();
		}

		$this->jsonResponseParser->doParse( $result );

		$queryResult = $this->queryResultFactory->newByHttpRequestQueryResult(
			$query,
			$this->jsonResponseParser
		);

		$queryResult->setFromCache( $isFromCache );

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

		$httpRequest = $this->httpRequestFactory->newCachedCurlRequest();

		$httpRequest->setOption( ONOI_HTTP_REQUEST_RESPONSECACHE_TTL, $this->httpResponseCacheLifetime );
		$httpRequest->setOption( ONOI_HTTP_REQUEST_RESPONSECACHE_PREFIX, $this->httpResponseCachePrefix . ':seql:' );

		$httpRequest->setOption( CURLOPT_FOLLOWLOCATION, true );

		$httpRequest->setOption( CURLOPT_RETURNTRANSFER, true );
		$httpRequest->setOption( CURLOPT_FAILONERROR, true );
		$httpRequest->setOption( CURLOPT_SSL_VERIFYPEER, false );

		$httpRequest->setOption( CURLOPT_URL, $this->httpRequestEndpoint . '?action=ask&format=json&query=' . QueryEncoder::rawUrlEncode( $query ) );

		$httpRequest->setOption( CURLOPT_HTTPHEADER, array(
			'Accept: application/json',
			'Content-Type: application/json; charset=utf-8'
		) );

		if( self::$cookies ) {
			$httpRequest->setOption( CURLOPT_COOKIEJAR, self::$cookies );
			$httpRequest->setOption( CURLOPT_COOKIEFILE, self::$cookies );
		}

		$response = $httpRequest->execute();

		return array( json_decode( $response, true ), $httpRequest->isFromCache() );
	}

}
