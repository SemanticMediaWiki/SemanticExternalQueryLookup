<?php

namespace SEQL\ByHttpRequest\Tests;

use SEQL\ByHttpRequest\QueryResultFetcher;
use SEQL\QueryResultFactory;
use SMW\DIProperty;

/**
 * @covers \SEQL\ByHttpRequest\QueryResultFetcher
 * @group semantic-external-query-lookup
 *
 * @license GPL-2.0-or-later
 * @since 1.0
 *
 * @author mwjames
 */
class QueryResultFetcherTest extends \PHPUnit\Framework\TestCase {

	private $store;
	private $httpRequest;
	private $httpRequestFactory;
	private $jsonResponseParser;

	protected function setUp(): void {
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
			->willReturn( $this->httpRequest );

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
			new QueryResultFetcher( $this->httpRequestFactory, $queryResultFactory, $this->jsonResponseParser, [] )
		);
	}

	public function testFetchEmptyQueryResult() {
		$queryResultFactory = new QueryResultFactory( $this->store );

		$printRequest = $this->getMockBuilder( '\SMW\Query\PrintRequest' )
			->disableOriginalConstructor()
			->getMock();

		$printRequest->expects( $this->any() )
			->method( 'getSerialisation' )
			->willReturn( '?ABC' );

		$description = $this->getMockBuilder( '\SMW\Query\Language\Description' )
			->disableOriginalConstructor()
			->getMock();

		$description->expects( $this->any() )
			->method( 'getPrintrequests' )
			->willReturn( [ $printRequest ] );

		$query = $this->getMockBuilder( '\SMWQuery' )
			->disableOriginalConstructor()
			->getMock();

		$query->expects( $this->any() )
			->method( 'getSortKeys' )
			->willReturn( [] );

		$query->expects( $this->any() )
			->method( 'getExtraPrintouts' )
			->willReturn( [ $printRequest ] );

		$query->expects( $this->any() )
			->method( 'getDescription' )
			->willReturn( $description );

		$instance = new QueryResultFetcher(
			$this->httpRequestFactory,
			$queryResultFactory,
			$this->jsonResponseParser,
			[]
		);

		$this->assertInstanceOf(
			'\SMW\Query\QueryResult',
			$instance->fetchQueryResult( $query )
		);
	}

	public function testResetOfPrintRequest() {
		$queryResultFactory = new QueryResultFactory( $this->store );

		$dataValue = $this->getMockBuilder( '\SMW\DataValues\PropertyValue' )
			->disableOriginalConstructor()
			->getMock();

		$dataValue->expects( $this->once() )
			->method( 'getDataItem' )
			->willReturn( new DIProperty( 'Foo' ) );

		$printRequest = $this->getMockBuilder( '\SMW\Query\PrintRequest' )
			->disableOriginalConstructor()
			->getMock();

		$printRequest->expects( $this->any() )
			->method( 'getSerialisation' )
			->willReturn( '?ABC' );

		$printRequest->expects( $this->atLeastOnce() )
			->method( 'getData' )
			->willReturn( $dataValue );

		$description = $this->getMockBuilder( '\SMW\Query\Language\Description' )
			->disableOriginalConstructor()
			->getMock();

		$description->expects( $this->any() )
			->method( 'getPrintrequests' )
			->willReturn( [ $printRequest ] );

		$query = $this->getMockBuilder( '\SMWQuery' )
			->disableOriginalConstructor()
			->getMock();

		$query->expects( $this->any() )
			->method( 'getSortKeys' )
			->willReturn( [] );

		$query->expects( $this->any() )
			->method( 'getExtraPrintouts' )
			->willReturn( [ $printRequest ] );

		$query->expects( $this->any() )
			->method( 'getDescription' )
			->willReturn( $description );

		$instance = new QueryResultFetcher(
			$this->httpRequestFactory,
			$queryResultFactory,
			$this->jsonResponseParser,
			[]
		);

		$this->assertInstanceOf(
			'\SMW\Query\QueryResult',
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
			->willReturn( '?ABC' );

		$description = $this->getMockBuilder( '\SMW\Query\Language\Description' )
			->disableOriginalConstructor()
			->getMock();

		$description->expects( $this->any() )
			->method( 'getPrintrequests' )
			->willReturn( [ $printRequest ] );

		$query = $this->getMockBuilder( '\SMWQuery' )
			->disableOriginalConstructor()
			->getMock();

		$query->expects( $this->any() )
			->method( 'getSortKeys' )
			->willReturn( [] );

		$query->expects( $this->any() )
			->method( 'getExtraPrintouts' )
			->willReturn( [ $printRequest ] );

		$query->expects( $this->any() )
			->method( 'getDescription' )
			->willReturn( $description );

		$instance = new QueryResultFetcher(
			$this->httpRequestFactory,
			$queryResultFactory,
			$this->jsonResponseParser,
			[]
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
			->with( $this->anything(), 42 );

		$this->httpRequest->expects( $this->at( 1 ) )
			->method( 'setOption' )
			->with( $this->anything(), 'Foo:seql:' );

		$this->assertInstanceOf(
			'\SMW\Query\QueryResult',
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
			->willReturn( '?ABC' );

		$description = $this->getMockBuilder( '\SMW\Query\Language\Description' )
			->disableOriginalConstructor()
			->getMock();

		$description->expects( $this->any() )
			->method( 'getPrintrequests' )
			->willReturn( [ $printRequest ] );

		$query = $this->getMockBuilder( '\SMWQuery' )
			->disableOriginalConstructor()
			->getMock();

		$query->expects( $this->any() )
			->method( 'getSortKeys' )
			->willReturn( [] );

		$query->expects( $this->any() )
			->method( 'getExtraPrintouts' )
			->willReturn( [ $printRequest ] );

		$query->expects( $this->any() )
			->method( 'getDescription' )
			->willReturn( $description );

		$instance = new QueryResultFetcher(
			$this->httpRequestFactory,
			$queryResultFactory,
			$this->jsonResponseParser,
			[]
		);

		$instance->setHttpRequestEndpoint( 'http://example.org/api.php' );
		$instance->setRepositoryTargetUrl( 'http://example.org/$1' );

		$this->httpRequest->expects( $this->any() )
			->method( 'execute' )
			->willReturn( json_encode( [ 'error' => [ 'info' => 'error' ] ] ) );

		$this->assertInstanceOf(
			'\SMW\Query\QueryResult',
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
			->willReturn( '?ABC' );

		$description = $this->getMockBuilder( '\SMW\Query\Language\Description' )
			->disableOriginalConstructor()
			->getMock();

		$description->expects( $this->any() )
			->method( 'getPrintrequests' )
			->willReturn( [ $printRequest ] );

		$query = $this->getMockBuilder( '\SMWQuery' )
			->disableOriginalConstructor()
			->getMock();

		$query->expects( $this->any() )
			->method( 'getSortKeys' )
			->willReturn( [] );

		$query->expects( $this->any() )
			->method( 'getExtraPrintouts' )
			->willReturn( [ $printRequest ] );

		$query->expects( $this->any() )
			->method( 'getDescription' )
			->willReturn( $description );

		$this->jsonResponseParser->expects( $this->any() )
			->method( 'getResultSubjectList' )
			->willReturn( [] );

		$instance = new QueryResultFetcher(
			$this->httpRequestFactory,
			$queryResultFactory,
			$this->jsonResponseParser,
			[]
		);

		$instance->setHttpRequestEndpoint( 'http://example.org/api.php' );
		$instance->setRepositoryTargetUrl( 'http://example.org/$1' );

		$expected = [
			'query-continue-offset' => 3,
			'query' => []
		];

		$this->httpRequest->expects( $this->once() )
			->method( 'execute' )
			->willReturn( json_encode( $expected ) );

		$this->assertInstanceOf(
			'\SMW\Query\QueryResult',
			$instance->fetchQueryResult( $query )
		);
	}

}
