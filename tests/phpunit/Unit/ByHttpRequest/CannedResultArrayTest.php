<?php

namespace SEQL\ByHttpRequest\Tests;

use SEQL\ByHttpRequest\CannedResultArray;
use SMW\DIProperty;
use SMWDINumber as DINumber;

/**
 * @covers \SEQL\ByHttpRequest\CannedResultArray
 * @group semantic-external-query-lookup
 *
 * @license GNU GPL v2+
 * @since 0.1
 *
 * @author mwjames
 */
class CannedResultArrayTest extends \PHPUnit_Framework_TestCase {

	private $jsonResponseParser;

	protected function setUp() {

		$this->jsonResponseParser = $this->getMockBuilder( '\SEQL\ByHttpRequest\JsonResponseParser' )
			->disableOriginalConstructor()
			->getMock();
	}

	public function testCanConstruct() {

		$printRequest = $this->getMockBuilder( '\SMW\Query\PrintRequest' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SEQL\ByHttpRequest\CannedResultArray',
			new CannedResultArray( new DIWiKiPage( 'Foo', NS_MAIN ), $printRequest, $this->jsonResponseParser )
		);
	}

	public function testGetResultSubject() {

		$subject = new DIWiKiPage( 'Foo', NS_MAIN );

		$printRequest = $this->getMockBuilder( '\SMW\Query\PrintRequest' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new CannedResultArray(
			$subject,
			$printRequest,
			$this->jsonResponseParser
		);

		$this->assertEquals(
			$subject,
			$instance->getResultSubject()
		);
	}

	public function testGetContentForMode_PRINT_THIS() {

		$subject = new DIWiKiPage( 'Foo', NS_MAIN );

		$printRequest = $this->getMockBuilder( '\SMW\Query\PrintRequest' )
			->disableOriginalConstructor()
			->getMock();

		$printRequest->expects( $this->any() )
			->method( 'getMode' )
			->will( $this->returnValue( \SMW\Query\PrintRequest::PRINT_THIS ) );

		$instance = new CannedResultArray(
			$subject,
			$printRequest,
			$this->jsonResponseParser
		);

		$this->assertDataItem(
			$subject,
			$instance
		);
	}

	public function testGetContentForMode_PRINT_CCAT() {

		$subject = new DIWiKiPage( 'Foo', NS_MAIN );
		$category = new DIWiKiPage( 'Bar', NS_CATEGORY, 'mw-foo' );

		$this->jsonResponseParser->expects( $this->any() )
			->method( 'getPropertyValuesFor' )
			->with(
				$this->equalTo( $subject ),
				$this->equalTo( new DIProperty( '_INST' ) ) )
			->will( $this->returnValue( array( $category ) ) );

		$printRequest = $this->getMockBuilder( '\SMW\Query\PrintRequest' )
			->disableOriginalConstructor()
			->getMock();

		$printRequest->expects( $this->any() )
			->method( 'getMode' )
			->will( $this->returnValue( \SMW\Query\PrintRequest::PRINT_CCAT ) );

		$instance = new CannedResultArray(
			$subject,
			$printRequest,
			$this->jsonResponseParser
		);

		$this->assertDataItem(
			$category,
			$instance
		);
	}

	public function testGetContentForMode_PRINT_PROP() {

		$subject = new DIWiKiPage( 'Foo', NS_MAIN );
		$dataItem = new DINumber( 1001 );

		$propertyValue = $this->getMockBuilder( '\SMWPropertyValue' )
			->disableOriginalConstructor()
			->getMock();

		$propertyValue->expects( $this->any() )
			->method( 'isValid' )
			->will( $this->returnValue( true ) );

		$propertyValue->expects( $this->any() )
			->method( 'getDataItem' )
			->will( $this->returnValue( new DIProperty( 'ABC' ) ) );

		$dataValue = $this->getMockBuilder( '\SMWDataValue' )
			->disableOriginalConstructor()
			->setMethods( array( 'getDataItem' ) )
			->getMockForAbstractClass();

		$dataValue->expects( $this->any() )
			->method( 'getDataItem' )
			->will( $this->returnValue( $dataItem ) );

		$this->jsonResponseParser->expects( $this->any() )
			->method( 'getPropertyValuesFor' )
			->with(
				$this->equalTo( $subject ),
				$this->equalTo( new DIProperty( 'ABC' ) ) )
			->will( $this->returnValue( array( $dataValue ) ) );

		$this->jsonResponseParser->expects( $this->any() )
			->method( 'findPropertyFromInMemoryExternalRepositoryCache' )
			->with(
				$this->equalTo( new DIProperty( 'ABC' ) ) )
			->will( $this->returnValue( new DIProperty( 'DEF' ) ) );

		$printRequest = $this->getMockBuilder( '\SMW\Query\PrintRequest' )
			->disableOriginalConstructor()
			->getMock();

		$printRequest->expects( $this->any() )
			->method( 'getMode' )
			->will( $this->returnValue( \SMW\Query\PrintRequest::PRINT_PROP ) );

		$printRequest->expects( $this->any() )
			->method( 'getData' )
			->will( $this->returnValue( $propertyValue ) );

		$instance = new CannedResultArray(
			$subject,
			$printRequest,
			$this->jsonResponseParser
		);

		$this->assertDataItem(
			$dataItem,
			$instance
		);
	}

	public function testGetContentOnQuantityTypeForMode_PRINT_PROP() {

		$subject = new DIWiKiPage( 'Foo', NS_MAIN );

		$property = new DIProperty( 'QuantityType' );
		$property->setPropertyTypeId( '_qty' );

		$dataItem = new DINumber( 1001 );

		$propertyValue = $this->getMockBuilder( '\SMWPropertyValue' )
			->disableOriginalConstructor()
			->getMock();

		$propertyValue->expects( $this->any() )
			->method( 'isValid' )
			->will( $this->returnValue( true ) );

		$propertyValue->expects( $this->any() )
			->method( 'getDataItem' )
			->will( $this->returnValue( new DIProperty( 'ABC' ) ) );

		$dataValue = $this->getMockBuilder( '\SMWDataValue' )
			->disableOriginalConstructor()
			->setMethods( array( 'getDataItem' ) )
			->getMockForAbstractClass();

		$dataValue->expects( $this->any() )
			->method( 'getDataItem' )
			->will( $this->returnValue( $dataItem ) );

		$this->jsonResponseParser->expects( $this->any() )
			->method( 'getPropertyValuesFor' )
			->with(
				$this->equalTo( $subject ),
				$this->equalTo( new DIProperty( 'ABC' ) ) )
			->will( $this->returnValue( array( $dataValue ) ) );

		$this->jsonResponseParser->expects( $this->any() )
			->method( 'findPropertyFromInMemoryExternalRepositoryCache' )
			->with(
				$this->equalTo( new DIProperty( 'ABC' ) ) )
			->will( $this->returnValue( $property ) );

		$printRequest = $this->getMockBuilder( '\SMW\Query\PrintRequest' )
			->disableOriginalConstructor()
			->getMock();

		$printRequest->expects( $this->any() )
			->method( 'getMode' )
			->will( $this->returnValue( \SMW\Query\PrintRequest::PRINT_PROP ) );

		$printRequest->expects( $this->any() )
			->method( 'getData' )
			->will( $this->returnValue( $propertyValue ) );

		$instance = new CannedResultArray(
			$subject,
			$printRequest,
			$this->jsonResponseParser
		);

		$this->assertDataItem(
			$dataItem,
			$instance
		);
	}

	private function assertDataItem( $dataItem, $instance ) {

		$this->assertEquals(
			$dataItem,
			$instance->getNextDataItem()
		);

		$instance->reset();

		$this->assertInstanceOf(
			'\SMWDataValue',
			$instance->getNextDataValue()
		);

		$this->assertEquals(
			array( $dataItem ),
			$instance->getContent()
		);
	}

}
