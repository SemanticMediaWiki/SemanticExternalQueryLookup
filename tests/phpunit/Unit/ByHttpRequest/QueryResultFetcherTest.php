<?php

namespace SEQL\ByHttpRequest\Tests;

use SEQL\ByHttpRequest\QueryResultFetcher;
use SEQL\QueryResultFactory;
use SMW\DIWikiPage;
use SMW\DIProperty;

/**
 * @covers \SEQL\ByHttpRequest\QueryResultFetcher
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

		$this->httpRequest = $this->getMockBuilder( '\Onoi\HttpRequest\CachedCurlRequest' )
			->disableOriginalConstructor()
			->getMock();

		$this->httpRequestFactory = $this->getMockBuilder( '\Onoi\HttpRequest\HttpRequestFactory' )
			->disableOriginalConstructor()
			->getMock();

		$this->httpRequestFactory->expects( $this->any() )
			->method( 'newCachedCurlRequest' )
			->will( $this->returnValue( $this->httpRequest ) );

		$this->jsonResponseParser = $this->getMockBuilder( '\SEQL\ByHttpRequest\JsonResponseParser' )
			->disableOriginalConstructor()
			->getMock();
	}

	public function testCanConstruct() {

		$queryResultFactory = $this->getMockBuilder( '\SEQL\QueryResultFactory' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SEQL\ByHttpRequest\QueryResultFetcher',
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

	public function testResetOfPrintRequest() {

		$queryResultFactory = new QueryResultFactory( $this->store );

		$dataValue = $this->getMockBuilder( '\SMWPropertyValue' )
			->disableOriginalConstructor()
			->getMock();

		$dataValue->expects( $this->once() )
			->method( 'getDataItem' )
			->will( $this->returnValue( new DIProperty( 'Foo' ) ) );

		$printRequest = $this->getMockBuilder( '\SMW\Query\PrintRequest' )
			->disableOriginalConstructor()
			->getMock();

		$printRequest->expects( $this->any() )
			->method( 'getSerialisation' )
			->will( $this->returnValue( '?ABC' ) );

		$printRequest->expects( $this->atLeastOnce() )
			->method( 'getData' )
			->will( $this->returnValue( $dataValue ) );

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
			->with( $this->anything(), $this->equalTo( 'Foo:seql:' ) );

		$this->assertInstanceOf(
			'\SMWQueryResult',
			$instance->fetchQueryResult( $query )
		);
	}

	public function testHttpRequestToReturnWithError() {

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

		$instance->setHttpRequestEndpoint( 'http://example.org/api.php' );
		$instance->setRepositoryTargetUrl( 'http://example.org/$1' );

		$this->httpRequest->expects( $this->any() )
			->method( 'execute' )
			->will( $this->returnValue( json_encode( array( 'error' => array( 'info' => 'error' ) ) ) ) );

		$this->assertInstanceOf(
			'\SMWQueryResult',
			$instance->fetchQueryResult( $query )
		);
	}

	public function testHttpRequestToReturnWithValidJson() {

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

		$this->jsonResponseParser->expects( $this->any() )
			->method( 'getResultSubjectList' )
			->will( $this->returnValue( array() ) );

		$instance = new QueryResultFetcher(
			$this->httpRequestFactory,
			$queryResultFactory,
			$this->jsonResponseParser
		);

		$instance->setHttpRequestEndpoint( 'http://example.org/api.php' );
		$instance->setRepositoryTargetUrl( 'http://example.org/$1' );

		$expected = array(
			'query-continue-offset' => 3,
			'query' => array()
		);

		$this->httpRequest->expects( $this->once() )
			->method( 'execute' )
			->will( $this->returnValue( json_encode( $expected ) ) );

		$this->assertInstanceOf(
			'\SMWQueryResult',
			$instance->fetchQueryResult( $query )
		);
	}

}
