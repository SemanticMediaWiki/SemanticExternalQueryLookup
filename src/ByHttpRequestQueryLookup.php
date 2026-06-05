<?php

namespace SEQL;

use MediaWiki\MediaWikiServices;
use SEQL\ByHttpRequest\JsonResponseParser;
use SEQL\ByHttpRequest\QueryResultFetcher;
use SMW\CacheFactory;
use SMW\Query\Query;
use SMW\Query\QueryResult;
use SMW\Services\ServicesFactory as ApplicationFactory;
use SMW\SQLStore\SQLStore;

/**
 * @license GPL-2.0-or-later
 * @since 1.0
 *
 * @author mwjames
 */
class ByHttpRequestQueryLookup extends SQLStore {

	/**
	 * @var QueryResultFactory
	 */
	private $queryResultFactory;

	/**
	 * @var CacheFactory
	 */
	private $cacheFactory;

	/**
	 * @since 1.0
	 *
	 * @param Query $query
	 *
	 * @return QueryResult
	 */
	public function getQueryResult( Query $query ) {
		$this->queryResultFactory = new QueryResultFactory( $this );

		if ( $query->querymode === Query::MODE_DEBUG ) {
			$query->addErrors( [ wfMessage( 'seql-debug-query-not-supported' )->text() ] );
			return $this->queryResultFactory->newEmptyQueryResult( $query );
		}

		$interwiki = $this->tryToMatchInterwikiFor( $query );

		if ( $interwiki === false || $interwiki === null ) {
			$query->addErrors( [ wfMessage( 'seql-interwiki-prefix-is-missing', $query->getQuerySource() )->text() ] );
			return $this->queryResultFactory->newEmptyQueryResult( $query );
		}

		$credentials = false;
		if ( isset( $GLOBALS['seqlgExternalRepositoryCredentials'][ $interwiki->getWikiID() ] ) ) {
			$credentials = $GLOBALS['seqlgExternalRepositoryCredentials'][ $interwiki->getWikiID() ];
		}

		return $this->fetchQueryResultFor( $query, $interwiki, $credentials );
	}

	protected function tryToMatchInterwikiFor( Query $query ) {
		return MediaWikiServices::getInstance()
			->getInterwikiLookup()
			->fetch( $query->getQuerySource() );
	}

	protected function fetchQueryResultFor( Query $query, $interwiki, $credentials = false ) {
		$services = MediaWikiServices::getInstance();

		$queryResultFetcher = new QueryResultFetcher(
			$services->getHttpRequestFactory(),
			$services->getObjectCacheFactory()->getInstance( $GLOBALS['seqlgHttpResponseCacheType'] ),
			$this->queryResultFactory,
			new JsonResponseParser( new DataValueDeserializer( $query->getQuerySource() ) ),
			$credentials
		);

		$queryResultFetcher->setHttpRequestEndpoint( $interwiki->getApi() );
		$queryResultFetcher->setRepositoryTargetUrl( $interwiki->getUrl() );

		$queryResultFetcher->setHttpResponseCachePrefix( $this->getCacheFactory()->getCachePrefix() );
		$queryResultFetcher->setHttpResponseCacheLifetime( $GLOBALS['seqlgHttpResponseCacheLifetime'] );

		return $queryResultFetcher->fetchQueryResult( $query );
	}

	private function getCacheFactory() {
		if ( $this->cacheFactory === null ) {
			$this->cacheFactory = ApplicationFactory::getInstance()->newCacheFactory();
		}

		return $this->cacheFactory;
	}

}
