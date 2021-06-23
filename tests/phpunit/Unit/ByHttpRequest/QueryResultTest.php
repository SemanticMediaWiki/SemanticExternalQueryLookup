<?php

namespace SEQL\ByHttpRequest\Tests;

use SEQL\ByHttpRequest\QueryResult;
use SMW\DIWikiPage;

/**
 * @covers \SEQL\ByHttpRequest\QueryResult
 * @group semantic-external-query-lookup
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class QueryResultTest extends \PHPUnit_Framework_TestCase {

	private $store;

	protected function setUp() {

		$this->store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();
	}

	public function testCanConstruct() {

		$query = $this->getMockBuilder( '\SMWQuery' )
			->disableOriginalConstructor()
			->getMock();

		$printRequests = array();
		$results = array();

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

		$printRequests = array(
			$printRequest
		);

		$results = array(
			new DIWikiPage( 'Foo', NS_MAIN )
		);

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

		$expected = array(
			'Foo'
		);

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
			->will( $this->returnValue( $expected ) );

		$printRequests = array();
		$results = array();

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
			->will( $this->returnValue( '?ABC' ) );

		$query = $this->getMockBuilder( '\SMWQuery' )
			->disableOriginalConstructor()
			->getMock();

		$query->expects( $this->any() )
			->method( 'getExtraPrintouts' )
			->will( $this->returnValue( array( $printRequest ) ) );

		$printRequests = array();
		$results = array();

		$instance = new QueryResult( $printRequests, $query, $results, $this->store, false );
		$instance->setRemoteTargetUrl( 'http://example.org:8080' );

		$this->assertInstanceOf(
			'\SMWInfolink',
			$instance->getLink()
		);
	}

}
