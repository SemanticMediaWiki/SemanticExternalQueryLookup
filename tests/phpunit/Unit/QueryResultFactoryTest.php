<?php

namespace SEQL\Tests;

use SEQL\QueryResultFactory;

/**
 * @covers \SEQL\QueryResultFactory
 * @group semantic-external-query-lookup
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class QueryResultFactoryTest extends \PHPUnit_Framework_TestCase {

	private $store;

	protected function setUp() {

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
			->will( $this->returnValue( $description ) );

		$instance = new QueryResultFactory( $this->store );

		$this->assertInstanceOf(
			'\SMWQueryResult',
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
			->will( $this->returnValue( $description ) );

		$instance = new QueryResultFactory( $this->store );

		$this->assertInstanceOf(
			'\SMWQueryResult',
			$instance->newByHttpRequestQueryResult( $query, $jsonResponseParser )
		);
	}

}
