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

	public function testTransportFailureAddsErrorInsteadOfSilentEmptyResult() {
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
			->method( 'getQuerySource' )
			->willReturn( 'mw-foo' );

		$query->expects( $this->any() )
			->method( 'getSortKeys' )
			->willReturn( [] );

		$query->expects( $this->any() )
			->method( 'getExtraPrintouts' )
			->willReturn( [ $printRequest ] );

		$query->expects( $this->any() )
			->method( 'getDescription' )
			->willReturn( $description );

		$errors = [];

		$query->expects( $this->once() )
			->method( 'addErrors' )
			->willReturnCallback( static function ( $e ) use ( &$errors ) {
				$errors = $e;
			} );

		$this->httpRequest->expects( $this->any() )
			->method( 'execute' )
			->willReturn( Status::newFatal( 'http-bad-status', '503', 'Service Unavailable' ) );

		$instance = new QueryResultFetcher(
			$this->httpRequestFactory,
			$this->cache,
			$queryResultFactory,
			$this->jsonResponseParser,
			[]
		);

		$instance->setHttpRequestEndpoint( 'http://example.org/api.php' );
		$instance->setRepositoryTargetUrl( 'http://example.org/$1' );

		$this->assertInstanceOf(
			'\SMW\Query\QueryResult',
			$instance->fetchQueryResult( $query )
		);

		$this->assertCount( 1, $errors );
		$this->assertStringContainsString( 'mw-foo', $errors[0] );
	}

	public function testUndecodableResponseAddsErrorAndIsNotCached() {
		$queryResultFactory = new QueryResultFactory( $this->store );
		$cache = new HashBagOStuff();

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
			->method( 'getQueryString' )
			->willReturn( '[[Category:City]]' );

		$query->expects( $this->any() )
			->method( 'getQuerySource' )
			->willReturn( 'mw-foo' );

		$query->expects( $this->any() )
			->method( 'getLimit' )
			->willReturn( 10 );

		$query->expects( $this->any() )
			->method( 'getOffset' )
			->willReturn( 0 );

		$query->expects( $this->any() )
			->method( 'getMainLabel' )
			->willReturn( '' );

		$query->expects( $this->any() )
			->method( 'getSortKeys' )
			->willReturn( [] );

		$query->expects( $this->any() )
			->method( 'getExtraPrintouts' )
			->willReturn( [ $printRequest ] );

		$query->expects( $this->any() )
			->method( 'getDescription' )
			->willReturn( $description );

		$errors = [];

		$query->expects( $this->once() )
			->method( 'addErrors' )
			->willReturnCallback( static function ( $e ) use ( &$errors ) {
				$errors = $e;
			} );

		// A HTML error page or similar: the transport succeeded but the
		// body is no JSON
		$this->httpRequest->expects( $this->any() )
			->method( 'getContent' )
			->willReturn( '<html><body>Blocked by crawler protection</body></html>' );

		$instance = new QueryResultFetcher(
			$this->httpRequestFactory,
			$cache,
			$queryResultFactory,
			$this->jsonResponseParser,
			[]
		);

		$instance->setHttpRequestEndpoint( 'http://example.org/api.php' );
		$instance->setRepositoryTargetUrl( 'http://example.org/$1' );
		$instance->setHttpResponseCachePrefix( 'Foo' );
		$instance->setHttpResponseCacheLifetime( 42 );

		$this->assertInstanceOf(
			'\SMW\Query\QueryResult',
			$instance->fetchQueryResult( $query )
		);

		$this->assertCount( 1, $errors );
		$this->assertStringContainsString( 'mw-foo', $errors[0] );

		// The garbage response must not have been written to the response
		// cache, otherwise the error would be frozen for the cache lifetime
		$url = 'http://example.org/api.php?action=ask&format=json&query=' . rawurlencode(
			'[[Category:City]]|?ABC|limit=10|offset=0|mainlabel='
		);

		$this->assertFalse(
			$cache->get( $cache->makeKey( 'seql', 'Foo', md5( $url ) ) )
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
