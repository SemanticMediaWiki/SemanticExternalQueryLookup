<?php

namespace SEQL;

use SMWQueryResult as QueryResult;
use SEQL\ByHttpRequest\QueryResult as ByHttpRequestQueryResult;
use SEQL\ByHttpRequest\JsonResponseParser;
use SMWQuery as Query;
use SMW\Store;

/**
 * @license GNU GPL v2+
 * @since 0.1
 *
 * @author mwjames
 */
class QueryResultFactory {

	/**
	 * @var
	 */
	private $store;

	/**
	 * @since 0.1
	 *
	 * @param Store $store
	 */
	public function __construct( Store $store ) {
		$this->store = $store;
	}

	/**
	 * @since 0.1
	 *
	 * @param Query $query
	 *
	 * @return QueryResult
	 */
	public function newEmptyQueryResult( Query $query ) {
		return new QueryResult(
			$query->getDescription()->getPrintrequests(),
			$query,
			array(),
			$this->store,
			false
		);
	}

	/**
	 * @since 0.1
	 *
	 * @param Query $query
	 * @param JsonResponseParser $jsonResponseParser
	 *
	 * @return QueryResult
	 */
	public function newByHttpRequestQueryResult( Query $query, JsonResponseParser $jsonResponseParser ) {

		$queryResult = new ByHttpRequestQueryResult(
			$query->getDescription()->getPrintrequests(),
			$query,
			$jsonResponseParser->getResultSubjects(),
			$this->store,
			$jsonResponseParser->hasFurtherResults()
		);

		$queryResult->setJsonResponseParser( $jsonResponseParser );

		return $queryResult;
	}

}
