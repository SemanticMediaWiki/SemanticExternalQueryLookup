<?php

namespace SEQL;

use SEQL\ByHttpRequest\JsonResponseParser;
use SEQL\ByHttpRequest\QueryResult as ByHttpRequestQueryResult;
use SMW\Store;
use SMWQuery as Query;
use SMWQueryResult as QueryResult;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class QueryResultFactory {

	/**
	 * @var
	 */
	private $store;

	/**
	 * @since 1.0
	 *
	 * @param Store $store
	 */
	public function __construct( Store $store ) {
		$this->store = $store;
	}

	/**
	 * @since 1.0
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
	 * @since 1.0
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
			$jsonResponseParser->getResultSubjectList(),
			$this->store,
			$jsonResponseParser->hasFurtherResults()
		);

		$queryResult->setJsonResponseParser( $jsonResponseParser );

		return $queryResult;
	}

}
