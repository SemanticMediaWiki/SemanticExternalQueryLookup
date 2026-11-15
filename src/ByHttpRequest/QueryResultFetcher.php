<?php

namespace SEQL\ByHttpRequest;

use MediaWiki\Http\HttpRequestFactory;
use SEQL\QueryEncoder;
use SEQL\QueryResultFactory;
use SMW\Query\Query;
use Wikimedia\ObjectCache\BagOStuff;

/**
 * @license GPL-2.0-or-later
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
	 * @var BagOStuff
	 */
	private $cache;

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
	private $httpResponseCachePrefix = '';

	/**
	 * @var int
	 */
	private $httpResponseCacheLifetime = 0;

	/**
	 * @var array
	 */
	private $credentials;

	/**
	 * @var \CookieJar|null
	 */
	private static $cookieJar;

	/**
	 * @since 1.0
	 *
	 * @param HttpRequestFactory $httpRequestFactory
	 * @param BagOStuff $cache
	 * @param QueryResultFactory $queryResultFactory
	 * @param JsonResponseParser $jsonResponseParser
	 * @param array|false $credentials
	 */
	public function __construct( HttpRequestFactory $httpRequestFactory, BagOStuff $cache, QueryResultFactory $queryResultFactory, JsonResponseParser $jsonResponseParser, $credentials ) {
		$this->httpRequestFactory = $httpRequestFactory;
		$this->cache = $cache;
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
	 * @param int $httpResponseCacheLifetime
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
		$cookieJar = new \CookieJar();

		// (1) Fetch a login token while collecting session cookies into the jar.
		$tokenRequest = $this->httpRequestFactory->create(
			$this->httpRequestEndpoint . '?action=query&format=json&meta=tokens&type=login',
			[
				'method' => 'GET',
				'followRedirects' => true,
				'sslVerifyCert' => false,
				'sslVerifyHost' => false,
			],
			__METHOD__
		);

		$tokenRequest->setCookieJar( $cookieJar );

		if ( !$tokenRequest->execute()->isOK() ) {
			return;
		}

		// Pull the same jar back so the Set-Cookie response is merged in.
		$cookieJar = $tokenRequest->getCookieJar();
		$result = json_decode( $tokenRequest->getContent() ?? '', true );

		if ( !isset( $result['query']['tokens']['logintoken'] ) ) {
			return;
		}

		$token = $result['query']['tokens']['logintoken'];

		// (2) Log in, reusing the same jar (sends the session cookies).
		$loginRequest = $this->httpRequestFactory->create(
			$this->httpRequestEndpoint,
			[
				'method' => 'POST',
				'sslVerifyCert' => false,
				'sslVerifyHost' => false,
				'postData' => http_build_query( [
					'action' => 'login',
					'format' => 'json',
					'lgname' => $credentials['username'],
					'lgpassword' => $credentials['password'],
					'lgtoken' => $token
				] ),
			],
			__METHOD__
		);

		$loginRequest->setCookieJar( $cookieJar );

		if ( !$loginRequest->execute()->isOK() ) {
			return;
		}

		$cookieJar = $loginRequest->getCookieJar();
		$result = json_decode( $loginRequest->getContent() ?? '', true );

		if ( isset( $result['login']['lguserid'] ) ) {
			self::$cookieJar = $cookieJar;
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

		if ( $this->credentials && self::$cookieJar === null ) {
			$this->doAuthenticateRemoteWiki( $this->credentials );
		}

		[ $result, $isFromCache, $failureReason ] = $this->doMakeHttpRequestFor( $query );

		if ( $result === null ) {
			// The response could not be fetched or decoded; make the failure
			// visible instead of silently rendering an empty result.
			$query->addErrors( [ wfMessage(
				$failureReason === 'decode' ? 'seql-http-response-invalid' : 'seql-http-request-failed',
				$query->getQuerySource()
			)->text() ] );

			return $this->queryResultFactory->newEmptyQueryResult( $query );
		}

		if ( $result === [] ) {
			return $this->queryResultFactory->newEmptyQueryResult( $query );
		}

		if ( isset( $result['error'] ) ) {
			$query->addErrors(
				isset( $result['error']['info'] ) ? [ implode( ', ', $result['error'] ) ] : $result['error']['query']
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
			str_replace( '$1', '', $this->repositoryTargetUrl )
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
			$property->setInterwiki( $querySource ?? '' );

			$printRequest->getData()->setDataItem( $property );

			// Reset label after dataItem was re-added
			$printRequest->setLabel( $printRequest->getLabel() );
		}
	}

	private function doMakeHttpRequestFor( $query ) {
		$url = $this->httpRequestEndpoint . '?action=ask&format=json&query=' . QueryEncoder::rawUrlEncode( $query );

		// Replicate the former ':seql:' response-cache namespace as key
		// components; makeKey() is wiki-scoped and escapes the separators.
		$key = $this->cache->makeKey( 'seql', $this->httpResponseCachePrefix, md5( $url ) );

		$isFromCache = true;
		$response = $this->cache->get( $key );

		if ( $response === false ) {
			$isFromCache = false;
			$response = $this->fetchHttpResponse( $url );

			// Only cache responses that at least decode as JSON so that a
			// failed or corrupted fetch is retried on the next request
			// instead of being served from the cache.
			if ( is_string( $response ) && $response !== '' && is_array( json_decode( $response, true ) ) ) {
				$this->cache->set( $key, $response, $this->httpResponseCacheLifetime );
			}
		}

		if ( $response === false || $response === null || $response === '' ) {
			// The response could not be fetched at all (transport failure)
			return [ null, $isFromCache, 'request' ];
		}

		$result = json_decode( $response, true );

		if ( !is_array( $result ) ) {
			// The response was fetched but is no valid JSON
			return [ null, $isFromCache, 'decode' ];
		}

		return [ $result, $isFromCache, '' ];
	}

	private function fetchHttpResponse( $url ) {
		$request = $this->httpRequestFactory->create(
			$url,
			[
				'method' => 'GET',
				'followRedirects' => true,
				// Preserve the legacy "do not verify the remote certificate"
				// behaviour. MediaWiki's Guzzle backend only skips verification
				// when both flags are false, so both are required here.
				'sslVerifyCert' => false,
				'sslVerifyHost' => false,
			],
			__METHOD__
		);

		$request->setHeader( 'Accept', 'application/json' );
		$request->setHeader( 'Content-Type', 'application/json; charset=utf-8' );

		if ( self::$cookieJar !== null ) {
			$request->setCookieJar( self::$cookieJar );
		}

		if ( !$request->execute()->isOK() ) {
			return false;
		}

		return $request->getContent();
	}

}
