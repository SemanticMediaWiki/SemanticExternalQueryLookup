<?php

namespace SEQL\Tests;

use SMWQuery as Query;

/**
 * @covers \SEQL\ByHttpRequestQueryLookup
 * @group semantic-external-query-lookup
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class ByHttpRequestQueryLookupTest extends \PHPUnit_Framework_TestCase {

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
			->will( $this->returnValue( $description ) );

		$instance = $this->getMockBuilder( '\SEQL\ByHttpRequestQueryLookup' )
			->disableOriginalConstructor()
			->setMethods( null )
			->getMock();

		$this->assertInstanceOf(
			'\SMWQueryResult',
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
			->will( $this->returnValue( $description ) );

		$query->expects( $this->once() )
			->method( 'addErrors' );

		$instance = $this->getMockBuilder( '\SEQL\ByHttpRequestQueryLookup' )
			->disableOriginalConstructor()
			->setMethods( null )
			->getMock();

		$query->querymode = Query::MODE_DEBUG;

		$this->assertInstanceOf(
			'\SMWQueryResult',
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
			->will( $this->returnValue( $description ) );

		$query->expects( $this->atLeastOnce() )
			->method( 'getExtraPrintouts' )
			->will( $this->returnValue( array() ) );

		$query->expects( $this->once() )
			->method( 'getSortKeys' )
			->will( $this->returnValue( array() ) );

		$instance = $this->getMockBuilder( '\SEQL\ByHttpRequestQueryLookup' )
			->disableOriginalConstructor()
			->setMethods( array( 'tryToMatchInterwikiFor' ) )
			->getMock();

		$instance->expects( $this->once() )
			->method( 'tryToMatchInterwikiFor' )
			->will( $this->returnValue( $interwiki ) );

		$this->assertInstanceOf(
			'\SMWQueryResult',
			$instance->getQueryResult( $query )
		);
	}

}
