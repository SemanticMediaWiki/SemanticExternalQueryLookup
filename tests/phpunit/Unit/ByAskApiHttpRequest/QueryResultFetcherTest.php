<?php

namespace SEQL\ByAskApiHttpRequest\Tests;

use SEQL\ByAskApiHttpRequest\QueryResultFetcher;
use SEQL\QueryResultFactory;
use SMW\DIWikiPage;

/**
 * @covers \SEQL\ByAskApiHttpRequest\QueryResultFetcher
 * @group semantic-external-query-lookup
 *
 * @license GNU GPL v2+
 * @since 0.1
 *
 * @author mwjames
 */
class QueryResultFetcherTest extends \PHPUnit_Framework_TestCase {

	private $store;
	private $httpRequestFactory;
	private $jsonResponseParser;

	protected function setUp() {

		$this->store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$httpRequest = $this->getMockBuilder( '\Onoi\HttpRequest\HttpRequest' )
			->disableOriginalConstructor()
			->getMock();

		$this->httpRequestFactory = $this->getMockBuilder( '\Onoi\HttpRequest\HttpRequestFactory' )
			->disableOriginalConstructor()
			->getMock();

		$this->httpRequestFactory->expects( $this->any() )
			->method( 'newCurlRequest' )
			->will( $this->returnValue( $httpRequest ) );

		$this->jsonResponseParser = $this->getMockBuilder( '\SEQL\ByAskApiHttpRequest\JsonResponseParser' )
			->disableOriginalConstructor()
			->getMock();
	}

	public function testCanConstruct() {

		$queryResultFactory = $this->getMockBuilder( '\SEQL\QueryResultFactory' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SEQL\ByAskApiHttpRequest\QueryResultFetcher',
			new QueryResultFetcher( $this->httpRequestFactory, $queryResultFactory, $this->jsonResponseParser )
		);
	}

	public function testFetchEmptyQueryResult() {

		$queryResultFactory = new QueryResultFactory( $this->store );

		$printRequest = $this->getMockBuilder( '\SMW\Query\PrintRequest' )
			->disableOriginalConstructor()
			->getMock();

		$printRequest->expects( $this->any() )
			->method( 'getSerialisation' )
			->will( $this->returnValue( '?ABC' ) );

		$description = $this->getMockBuilder( '\SMW\Query\Language\Description' )
			->disableOriginalConstructor()
			->getMock();

		$description->expects( $this->any() )
			->method( 'getPrintrequests' )
			->will( $this->returnValue( array( $printRequest ) ) );

		$query = $this->getMockBuilder( '\SMWQuery' )
			->disableOriginalConstructor()
			->getMock();

		$query->expects( $this->any() )
			->method( 'getSortKeys' )
			->will( $this->returnValue( array() ) );

		$query->expects( $this->any() )
			->method( 'getExtraPrintouts' )
			->will( $this->returnValue( array( $printRequest ) ) );

		$query->expects( $this->any() )
			->method( 'getDescription' )
			->will( $this->returnValue( $description ) );

		$instance = new QueryResultFetcher(
			$this->httpRequestFactory,
			$queryResultFactory,
			$this->jsonResponseParser
		);

		$this->assertInstanceOf(
			'\SMWQueryResult',
			$instance->fetchQueryResult( $query )
		);
	}

}
