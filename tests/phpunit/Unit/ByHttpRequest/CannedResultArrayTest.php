<?php

namespace SEQL\ByHttpRequest\Tests;

use SEQL\ByHttpRequest\CannedResultArray;
use SMW\DataItems\Number;
use SMW\DataItems\Property;
use SMW\DataItems\WikiPage;

/**
 * @covers \SEQL\ByHttpRequest\CannedResultArray
 * @group semantic-external-query-lookup
 *
 * @license GPL-2.0-or-later
 * @since 1.0
 *
 * @author mwjames
 */
class CannedResultArrayTest extends \PHPUnit\Framework\TestCase {

	private $jsonResponseParser;

	protected function setUp(): void {
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
			new CannedResultArray( new WikiPage( 'Foo', NS_MAIN ), $printRequest, $this->jsonResponseParser )
		);
	}

	public function testGetResultSubject() {
		$subject = new WikiPage( 'Foo', NS_MAIN );

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
		$subject = new WikiPage( 'Foo', NS_MAIN );

		$printRequest = $this->getMockBuilder( '\SMW\Query\PrintRequest' )
			->disableOriginalConstructor()
			->getMock();

		$printRequest->expects( $this->any() )
			->method( 'getMode' )
			->willReturn( \SMW\Query\PrintRequest::PRINT_THIS );

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
		$subject = new WikiPage( 'Foo', NS_MAIN );
		$category = new WikiPage( 'Bar', NS_CATEGORY, 'mw-foo' );

		$this->jsonResponseParser->expects( $this->any() )
			->method( 'getPropertyValuesFor' )
			->with(
				$subject,
				new Property( '_INST' ) )
			->willReturn( [ $category ] );

		$printRequest = $this->getMockBuilder( '\SMW\Query\PrintRequest' )
			->disableOriginalConstructor()
			->getMock();

		$printRequest->expects( $this->any() )
			->method( 'getMode' )
			->willReturn( \SMW\Query\PrintRequest::PRINT_CCAT );

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

	public function testGetContentOnDifferentPropertyLabelForMode_PRINT_PROP() {
		$subject = new WikiPage( 'Foo', NS_MAIN );
		$dataItem = new Number( 1001 );

		$propertyValue = $this->getMockBuilder( '\SMW\DataValues\PropertyValue' )
			->disableOriginalConstructor()
			->getMock();

		$propertyValue->expects( $this->any() )
			->method( 'isValid' )
			->willReturn( true );

		$propertyValue->expects( $this->any() )
			->method( 'getDataItem' )
			->willReturn( new Property( 'ABC' ) );

		$dataValue = $this->getMockBuilder( '\SMW\DataValues\DataValue' )
			->disableOriginalConstructor()
			->onlyMethods( [ 'getDataItem' ] )
			->getMockForAbstractClass();

		$dataValue->expects( $this->any() )
			->method( 'getDataItem' )
			->willReturn( $dataItem );

		$this->jsonResponseParser->expects( $this->any() )
			->method( 'getPropertyValuesFor' )
			->with(
				$subject,
				new Property( 'isPropertyFromInMemoryExternalRepositoryCache' ) )
			->willReturn( [ $dataValue ] );

		$this->jsonResponseParser->expects( $this->any() )
			->method( 'findPropertyFromInMemoryExternalRepositoryCache' )
			->with(
				new Property( 'withDifferentPropertyLabel' ) )
			->willReturn( new Property( 'isPropertyFromInMemoryExternalRepositoryCache' ) );

		$printRequest = $this->getMockBuilder( '\SMW\Query\PrintRequest' )
			->disableOriginalConstructor()
			->getMock();

		$printRequest->expects( $this->any() )
			->method( 'getMode' )
			->willReturn( \SMW\Query\PrintRequest::PRINT_PROP );

		$printRequest->expects( $this->any() )
			->method( 'getData' )
			->willReturn( $propertyValue );

		$printRequest->expects( $this->atLeastOnce() )
			->method( 'getLabel' )
			->willReturn( 'withDifferentPropertyLabel' );

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

	public function testGetContentOnQuantityTypeWithSameLabelForMode_PRINT_PROP() {
		$subject = new WikiPage( 'Foo', NS_MAIN );

		$property = new Property( 'QuantityType' );
		$property->setPropertyValueType( '_qty' );

		$dataItem = new Number( 1001 );

		$propertyValue = $this->getMockBuilder( '\SMW\DataValues\PropertyValue' )
			->disableOriginalConstructor()
			->getMock();

		$propertyValue->expects( $this->any() )
			->method( 'isValid' )
			->willReturn( true );

		$propertyValue->expects( $this->any() )
			->method( 'getDataItem' )
			->willReturn( new Property( 'ABC' ) );

		$dataValue = $this->getMockBuilder( '\SMW\DataValues\DataValue' )
			->disableOriginalConstructor()
			->onlyMethods( [ 'getDataItem' ] )
			->getMockForAbstractClass();

		$dataValue->expects( $this->any() )
			->method( 'getDataItem' )
			->willReturn( $dataItem );

		$this->jsonResponseParser->expects( $this->any() )
			->method( 'getPropertyValuesFor' )
			->with(
				$subject,
				$property )
			->willReturn( [ $dataValue ] );

		$this->jsonResponseParser->expects( $this->any() )
			->method( 'findPropertyFromInMemoryExternalRepositoryCache' )
			->with(
				new Property( 'ABC' ) )
			->willReturn( $property );

		$printRequest = $this->getMockBuilder( '\SMW\Query\PrintRequest' )
			->disableOriginalConstructor()
			->getMock();

		$printRequest->expects( $this->any() )
			->method( 'getMode' )
			->willReturn( \SMW\Query\PrintRequest::PRINT_PROP );

		$printRequest->expects( $this->any() )
			->method( 'getData' )
			->willReturn( $propertyValue );

		$printRequest->expects( $this->atLeastOnce() )
			->method( 'getLabel' )
			->willReturn( '' );

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

	public function testOptions() {
		$subject = new WikiPage( 'Foo', NS_MAIN );

		$printRequest = $this->getMockBuilder( '\SMW\Query\PrintRequest' )
			->disableOriginalConstructor()
			->getMock();

		$printRequest->expects( $this->any() )
			->method( 'getMode' )
			->willReturn( \SMW\Query\PrintRequest::PRINT_THIS );

		$instance = new CannedResultArray(
			$subject,
			$printRequest,
			$this->jsonResponseParser
		);

		$dataValue = $instance->getNextDataValue();

		$this->assertIsString(

			$dataValue->getOption( 'user.language' )
		);

		$this->assertIsString(

			$dataValue->getOption( 'content.language' )
		);
	}

	private function assertDataItem( $dataItem, $instance ) {
		$this->assertEquals(
			$dataItem,
			$instance->getNextDataItem()
		);

		$instance->reset();

		$this->assertInstanceOf(
			'\SMW\DataValues\DataValue',
			$instance->getNextDataValue()
		);

		$this->assertEquals(
			[ $dataItem ],
			$instance->getContent()
		);
	}

}
