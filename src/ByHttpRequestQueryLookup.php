<?php

namespace SEQL;

use Onoi\HttpRequest\HttpRequestFactory;
use SMWQuery as Query;
use SMW\SQLStore\SQLStore;
use SMWQueryResult as QueryResult;
use SEQL\ByHttpRequest\QueryResultFetcher;
use SEQL\ByHttpRequest\JsonResponseParser;

/**
 * @license GNU GPL v2+
 * @since 0.1
 *
 * @author mwjames
 */
class ByHttpRequestQueryLookup extends SQLStore {

	/**
	 * @since 0.1
	 * 
	 * @param Query $query
	 *
	 * @return QueryResult
	 */
	public function getQueryResult( Query $query ) {

		$queryResultFactory = new QueryResultFactory( $this );

		$querySource = $query->getQuerySource();
		$interwiki = \Interwiki::fetch( $querySource );

		if ( $interwiki === false || $interwiki === null ) {
			$query->addErrors( array( wfMessage( 'seql-interwiki-prefix-is-missing', $querySource )->text() ) );
			return $queryResultFactory->newEmptyQueryResult( $query );
		}

		$queryResultFetcher = new QueryResultFetcher(
			new HttpRequestFactory(),
			$queryResultFactory,
			new JsonResponseParser( new DataValueDeserializer( $querySource ) )
		);

		$queryResultFetcher->setHttpRequestEndpoint( $interwiki->getApi() );
		$queryResultFetcher->setRepositoryTargetUrl( $interwiki->getUrl() );

		return $queryResultFetcher->fetchQueryResult( $query );
	}

}
