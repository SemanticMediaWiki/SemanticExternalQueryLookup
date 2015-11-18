<?php

namespace SEQL\Tests;

use SEQL\ByHttpRequestQueryLookup;

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

}
