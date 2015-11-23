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
 * @since 1.0
 *
 * @author mwjames
 */
class QueryResultFetcherTest extends \PHPUnit_Framework_TestCase {

	private $store;
	private $httpRequest;
	private $httpRequestFactory;
	private $jsonResponseParser;

	protected function setUp() {

		$this->store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$this->httpRequest = $this->getMockBuilder( '\Onoi\HttpRequest\HttpRequest' )
			->disableOriginalConstructor()
			->getMock();

		$this->httpRequestFactory = $this->getMockBuilder( '\Onoi\HttpRequest\HttpRequestFactory' )
			->disableOriginalConstructor()
			->getMock();

		$this->httpRequestFactory->expects( $this->any() )
			->method( 'newCachedCurlRequest' )
			->will( $this->returnValue( $this->httpRequest ) );

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

	public function testFetchQueryResultWithResponseCache() {

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

		$instance->setHttpResponseCacheLifetime( 42 );
		$instance->setHttpResponseCachePrefix( 'Foo' );

		// PHUNIT 4.1
	//	$this->httpRequest->expects( $this->any() )
	//		->method( 'setOption' )
	//		->withConsecutive(
	//			array( $this->anything(), $this->equalTo( 42 ) ),
	//			array( $this->anything(), $this->equalTo( 'Foo:' ) ) );

		$this->httpRequest->expects( $this->at( 0 ) )
			->method( 'setOption' )
			->with( $this->anything(), $this->equalTo( 42 ) );

		$this->httpRequest->expects( $this->at( 1 ) )
			->method( 'setOption' )
			->with( $this->anything(), $this->equalTo( 'Foo:' ) );

		$this->assertInstanceOf(
			'\SMWQueryResult',
			$instance->fetchQueryResult( $query )
		);
	}

}
