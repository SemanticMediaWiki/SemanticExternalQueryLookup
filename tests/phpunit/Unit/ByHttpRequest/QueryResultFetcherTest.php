<?php

namespace SEQL\ByHttpRequest\Tests;

use MediaWiki\Status\Status;
use SEQL\ByHttpRequest\QueryResultFetcher;
use SEQL\QueryResultFactory;
use SMW\DataItems\Property;
use Wikimedia\ObjectCache\HashBagOStuff;

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
	private $cache;
	private $jsonResponseParser;

	protected function setUp(): void {
		$this->store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$this->httpRequest = $this->getMockBuilder( '\MWHttpRequest' )
			->disableOriginalConstructor()
			->getMock();

		$this->httpRequest->expects( $this->any() )
			->method( 'execute' )
			->willReturn( Status::newGood() );

		$this->httpRequestFactory = $this->getMockBuilder( '\MediaWiki\Http\HttpRequestFactory' )
			->disableOriginalConstructor()
			->getMock();

		$this->httpRequestFactory->expects( $this->any() )
			->method( 'create' )
			->willReturn( $this->httpRequest );

		$this->cache = new HashBagOStuff();

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
			new QueryResultFetcher( $this->httpRequestFactory, $this->cache, $queryResultFactory, $this->jsonResponseParser, [] )
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

		$query = $this->getMockBuilder( '\SMW\Query\Query' )
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
			$this->cache,
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
			->willReturn( new Property( 'Foo' ) );

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

		$query = $this->getMockBuilder( '\SMW\Query\Query' )
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
			$this->cache,
			$queryResultFactory,
			$this->jsonResponseParser,
			[]
		);

		$this->assertInstanceOf(
			'\SMW\Query\QueryResult',
			$instance->fetchQueryResult( $query )
		);
	}

	public function testFetchQueryResultUsesResponseCache() {
		$cache = new HashBagOStuff();
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

		$query = $this->getMockBuilder( '\SMW\Query\Query' )
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

		// The live fetch must happen exactly once across two identical queries;
		// the second call has to be served from the response cache.
		$this->httpRequest->expects( $this->once() )
			->method( 'getContent' )
			->willReturn( json_encode( [ 'query' => [] ] ) );

		$instance = new QueryResultFetcher(
			$this->httpRequestFactory,
			$cache,
			$queryResultFactory,
			$this->jsonResponseParser,
			[]
		);

		$instance->setHttpRequestEndpoint( 'http://example.org/api.php' );
		$instance->setRepositoryTargetUrl( 'http://example.org/$1' );
		$instance->setHttpResponseCacheLifetime( 42 );
		$instance->setHttpResponseCachePrefix( 'Foo' );

		$this->assertInstanceOf(
			'\SMW\Query\QueryResult',
			$instance->fetchQueryResult( $query )
		);

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

		$query = $this->getMockBuilder( '\SMW\Query\Query' )
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
			$this->cache,
			$queryResultFactory,
			$this->jsonResponseParser,
			[]
		);

		$instance->setHttpRequestEndpoint( 'http://example.org/api.php' );
		$instance->setRepositoryTargetUrl( 'http://example.org/$1' );

		$this->httpRequest->expects( $this->any() )
			->method( 'getContent' )
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

		$query = $this->getMockBuilder( '\SMW\Query\Query' )
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
			$this->cache,
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
			->method( 'getContent' )
			->willReturn( json_encode( $expected ) );

		$this->assertInstanceOf(
			'\SMW\Query\QueryResult',
			$instance->fetchQueryResult( $query )
		);
	}

}
