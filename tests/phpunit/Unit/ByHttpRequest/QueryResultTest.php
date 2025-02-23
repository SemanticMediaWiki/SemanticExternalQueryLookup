<?php

namespace SEQL\ByHttpRequest\Tests;

use SEQL\ByHttpRequest\QueryResult;
use SMW\DIWikiPage;

/**
 * @covers \SEQL\ByHttpRequest\QueryResult
 * @group semantic-external-query-lookup
 *
 * @license GPL-2.0-or-later
 * @since 1.0
 *
 * @author mwjames
 */
class QueryResultTest extends \PHPUnit\Framework\TestCase {

	private $store;

	protected function setUp(): void {
		$this->store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();
	}

	public function testCanConstruct() {
		$query = $this->getMockBuilder( '\SMWQuery' )
			->disableOriginalConstructor()
			->getMock();

		$printRequests = [];
		$results = [];

		$this->assertInstanceOf(
			'\SEQL\ByHttpRequest\QueryResult',
			new QueryResult( $printRequests, $query, $results, $this->store, false )
		);
	}

	public function testGetNext() {
		$jsonResponseParser = $this->getMockBuilder( '\SEQL\ByHttpRequest\JsonResponseParser' )
			->disableOriginalConstructor()
			->getMock();

		$query = $this->getMockBuilder( '\SMWQuery' )
			->disableOriginalConstructor()
			->getMock();

		$printRequest = $this->getMockBuilder( '\SMW\Query\PrintRequest' )
			->disableOriginalConstructor()
			->getMock();

		$printRequests = [
			$printRequest
		];

		$results = [
			new DIWikiPage( 'Foo', NS_MAIN )
		];

		$instance = new QueryResult( $printRequests, $query, $results, $this->store, false );
		$instance->setJsonResponseParser( $jsonResponseParser );

		foreach ( $instance->getNext() as $result ) {
			$this->assertInstanceOf(
				'\SEQL\ByHttpRequest\CannedResultArray',
				$result
			);
		}
	}

	public function testToArray() {
		$expected = [
			'Foo'
		];

		$query = $this->getMockBuilder( '\SMWQuery' )
			->disableOriginalConstructor()
			->getMock();

		$printRequest = $this->getMockBuilder( '\SMW\Query\PrintRequest' )
			->disableOriginalConstructor()
			->getMock();

		$jsonResponseParser = $this->getMockBuilder( '\SEQL\ByHttpRequest\JsonResponseParser' )
			->disableOriginalConstructor()
			->getMock();

		$jsonResponseParser->expects( $this->exactly( 2 ) )
			->method( 'getRawResponseResult' )
			->willReturn( $expected );

		$printRequests = [];
		$results = [];

		$instance = new QueryResult( $printRequests, $query, $results, $this->store, false );
		$instance->setJsonResponseParser( $jsonResponseParser );

		$this->assertEquals(
			$expected,
			$instance->toArray()
		);

		$this->assertEquals(
			$expected,
			$instance->serializeToArray()
		);
	}

	public function testGetLink() {
		$printRequest = $this->getMockBuilder( '\SMW\Query\PrintRequest' )
			->disableOriginalConstructor()
			->getMock();

		$printRequest->expects( $this->once() )
			->method( 'getSerialisation' )
			->willReturn( '?ABC' );

		$query = $this->getMockBuilder( '\SMWQuery' )
			->disableOriginalConstructor()
			->getMock();

		$query->expects( $this->any() )
			->method( 'getExtraPrintouts' )
			->willReturn( [ $printRequest ] );

		$printRequests = [];
		$results = [];

		$instance = new QueryResult( $printRequests, $query, $results, $this->store, false );
		$instance->setRemoteTargetUrl( 'http://example.org:8080' );

		$this->assertInstanceOf(
			'\SMWInfolink',
			$instance->getLink()
		);
	}

}
