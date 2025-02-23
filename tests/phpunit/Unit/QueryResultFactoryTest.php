<?php

namespace SEQL\Tests;

use SEQL\QueryResultFactory;

/**
 * @covers \SEQL\QueryResultFactory
 * @group semantic-external-query-lookup
 *
 * @license GPL-2.0-or-later
 * @since 1.0
 *
 * @author mwjames
 */
class QueryResultFactoryTest extends \PHPUnit\Framework\TestCase {

	private $store;

	protected function setUp(): void {
		$this->store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();
	}

	public function testCanConstruct() {
		$this->assertInstanceOf(
			'\SEQL\QueryResultFactory',
			new QueryResultFactory( $this->store )
		);
	}

	public function testNewEmptyQueryResult() {
		$description = $this->getMockBuilder( '\SMW\Query\Language\Description' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$query = $this->getMockBuilder( '\SMWQuery' )
			->disableOriginalConstructor()
			->getMock();

		$query->expects( $this->any() )
			->method( 'getDescription' )
			->willReturn( $description );

		$instance = new QueryResultFactory( $this->store );

		$this->assertInstanceOf(
			'\SMW\Query\QueryResult',
			$instance->newEmptyQueryResult( $query )
		);
	}

	public function testNewByHttpLookupQueryResult() {
		$jsonResponseParser = $this->getMockBuilder( '\SEQL\ByHttpRequest\JsonResponseParser' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$description = $this->getMockBuilder( '\SMW\Query\Language\Description' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$query = $this->getMockBuilder( '\SMWQuery' )
			->disableOriginalConstructor()
			->getMock();

		$query->expects( $this->any() )
			->method( 'getDescription' )
			->willReturn( $description );

		$instance = new QueryResultFactory( $this->store );

		$this->assertInstanceOf(
			'\SMW\Query\QueryResult',
			$instance->newByHttpRequestQueryResult( $query, $jsonResponseParser )
		);
	}

}
