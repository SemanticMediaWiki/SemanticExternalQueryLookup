<?php

namespace SEQL;

use Onoi\HttpRequest\HttpRequestFactory;
use SEQL\ByAskApiHttpRequest\JsonResponseParser;
use SEQL\ByAskApiHttpRequest\QueryResultFetcher;
use SMW\SQLStore\SQLStore;
use SMWQuery as Query;
use SMWQueryResult as QueryResult;
use Interwiki;

/**
 * @license GNU GPL v2+
 * @since 0.1
 *
 * @author mwjames
 */
class ByAskApiHttpRequestQueryLookup extends SQLStore {

	/**
	 * @var QueryResultFactory
	 */
	private $queryResultFactory;

	/**
	 * @since 0.1
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
			new HttpRequestFactory(),
			$this->queryResultFactory,
			new JsonResponseParser( new DataValueDeserializer( $query->getQuerySource() ) )
		);

		$queryResultFetcher->setHttpRequestEndpoint( $interwiki->getApi() );
		$queryResultFetcher->setRepositoryTargetUrl( $interwiki->getUrl() );

		return $queryResultFetcher->fetchQueryResult( $query );
	}

}
