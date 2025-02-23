<?php

namespace SEQL\Tests;

use SMWQuery as Query;

/**
 * @covers \SEQL\ByHttpRequestQueryLookup
 * @group semantic-external-query-lookup
 *
 * @license GPL-2.0-or-later
 * @since 1.0
 *
 * @author mwjames
 */
class ByHttpRequestQueryLookupTest extends \PHPUnit\Framework\TestCase {

	public function testCanConstruct() {
		$instance = $this->getMockBuilder( '\SEQL\ByHttpRequestQueryLookup' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SMW\Store',
			$instance
		);
	}

	public function testGetQueryResult() {
		$description = $this->getMockBuilder( '\SMW\Query\Language\Description' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$query = $this->getMockBuilder( '\SMWQuery' )
			->disableOriginalConstructor()
			->getMock();

		$query->expects( $this->any() )
			->method( 'getDescription' )
			->willReturn( $description );

		$instance = $this->getMockBuilder( '\SEQL\ByHttpRequestQueryLookup' )
			->disableOriginalConstructor()
			->onlyMethods( [] )
			->getMock();

		$this->assertInstanceOf(
			'\SMW\Query\QueryResult',
			$instance->getQueryResult( $query )
		);
	}

	public function testGetEmptyQueryResult_MODE_DEBUG() {
		$description = $this->getMockBuilder( '\SMW\Query\Language\Description' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$query = $this->getMockBuilder( '\SMWQuery' )
			->disableOriginalConstructor()
			->getMock();

		$query->expects( $this->any() )
			->method( 'getDescription' )
			->willReturn( $description );

		$query->expects( $this->once() )
			->method( 'addErrors' );

		$instance = $this->getMockBuilder( '\SEQL\ByHttpRequestQueryLookup' )
			->disableOriginalConstructor()
			->onlyMethods( [] )
			->getMock();

		$query->querymode = Query::MODE_DEBUG;

		$this->assertInstanceOf(
			'\SMW\Query\QueryResult',
			$instance->getQueryResult( $query )
		);
	}

	public function testGetQueryResultForSimulatedInterwikiMatch() {
		$interwiki = $this->getMockBuilder( '\Interwiki' )
			->disableOriginalConstructor()
			->getMock();

		$description = $this->getMockBuilder( '\SMW\Query\Language\Description' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$query = $this->getMockBuilder( '\SMWQuery' )
			->disableOriginalConstructor()
			->getMock();

		$query->expects( $this->any() )
			->method( 'getDescription' )
			->willReturn( $description );

		$query->expects( $this->atLeastOnce() )
			->method( 'getExtraPrintouts' )
			->willReturn( [] );

		$query->expects( $this->once() )
			->method( 'getSortKeys' )
			->willReturn( [] );

		$instance = $this->getMockBuilder( '\SEQL\ByHttpRequestQueryLookup' )
			->disableOriginalConstructor()
			->onlyMethods( [ 'tryToMatchInterwikiFor' ] )
			->getMock();

		$instance->expects( $this->once() )
			->method( 'tryToMatchInterwikiFor' )
			->willReturn( $interwiki );

		$this->assertInstanceOf(
			'\SMW\Query\QueryResult',
			$instance->getQueryResult( $query )
		);
	}

}
