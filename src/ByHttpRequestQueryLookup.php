<?php

namespace SEQL;

use Onoi\HttpRequest\HttpRequestFactory;
use SEQL\ByHttpRequest\JsonResponseParser;
use SEQL\ByHttpRequest\QueryResultFetcher;
use SMW\SQLStore\SQLStore;
use SMW\ApplicationFactory;
use SMWQuery as Query;
use SMWQueryResult as QueryResult;
use Interwiki;

/**
 * @license GNU GPL v2+
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
			$query->addErrors( array( wfMessage( 'seql-debug-query-not-supported' )->text() ) );
			return $this->queryResultFactory->newEmptyQueryResult( $query );
		}

		$interwiki = $this->tryToMatchInterwikiFor( $query );

		if ( $interwiki === false || $interwiki === null ) {
			$query->addErrors( array( wfMessage( 'seql-interwiki-prefix-is-missing', $query->getQuerySource() )->text() ) );
			return $this->queryResultFactory->newEmptyQueryResult( $query );
		}

		return $this->fetchQueryResultFor( $query, $interwiki );
	}

	protected function tryToMatchInterwikiFor( Query $query ) {
		return Interwiki::fetch( $query->getQuerySource() );
	}

	protected function fetchQueryResultFor( Query $query, $interwiki ) {

		$queryResultFetcher = new QueryResultFetcher(
			new HttpRequestFactory( $this->getCacheFactory()->newMediaWikiCompositeCache( $GLOBALS['seqlgHttpResponseCacheType'] ) ),
			$this->queryResultFactory,
			new JsonResponseParser( new DataValueDeserializer( $query->getQuerySource() ) )
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
