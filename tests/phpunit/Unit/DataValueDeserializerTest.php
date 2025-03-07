<?php

namespace SEQL\Tests;

use SEQL\DataValueDeserializer;
use SMW\DIProperty;
use SMW\DIWikiPage;
use SMWDITime as DITime;

/**
 * @covers \SEQL\DataValueDeserializer
 * @group semantic-external-query-lookup
 *
 * @license GPL-2.0-or-later
 * @since 1.0
 *
 * @author mwjames
 */
class DataValueDeserializerTest extends \PHPUnit\Framework\TestCase {

	public function testCanConstruct() {
		$this->assertInstanceOf(
			'\SEQL\DataValueDeserializer',
			new DataValueDeserializer( 'foo' )
		);
	}

	public function testNewDiWikiPage() {
		$instance = new DataValueDeserializer( 'foo' );

		$value = [
			'namespace' => NS_MAIN,
			'fulltext'  => 'abc def'
		];

		$this->assertEquals(
			new DIWikiPage( 'Foo:abc_def', NS_MAIN ),
			$instance->newDiWikiPage( $value )
		);
	}

	public function testTryNewDiWikiPageForInvalidSeralization() {
		$instance = new DataValueDeserializer( 'foo' );

		$this->assertFalse(
			$instance->newDiWikiPage( [ 'Foo' ] )
		);
	}

	public function testNewTimeValueForOutOfRangeTimestamp() {
		$instance = new DataValueDeserializer( 'foo' );

		$property = new DIProperty( 'Bar' );
		$property->setPropertyTypeId( '_dat' );

		$this->assertNotEquals(
			DITime::doUnserialize( '2/-200' ),
			$instance->newDataValueFrom( $property, '-2000101000000' )
		);
	}

	public function testNewTimeValueForRawTimeFromat() {
		$instance = new DataValueDeserializer( 'foo' );

		$property = new DIProperty( 'Bar' );
		$property->setPropertyTypeId( '_dat' );

		$this->assertEquals(
			DITime::doUnserialize( '2/-200' ),
			$instance->newDataValueFrom( $property, [ 'raw' => '2/-200' ] )->getDataItem()
		);
	}

	public function testNewRecordValue() {
		$instance = new DataValueDeserializer( 'foo' );

		$property = new DIProperty( 'Foo' );
		$property->setPropertyTypeId( '_rec' );

		$item = [
			'namespace' => NS_MAIN,
			'fulltext'  => 'abc def'
		];

		$record[] = [
			'label'  => 'Foo',
			'typeid' => '_wpg',
			'item'   => [ $item ]
		];

		$this->assertInstanceOf(
			'\SMWRecordValue',
			$instance->newDataValueFrom( $property, $record )
		);
	}

	public function testTextValueWithEmbeddedLink() {
		$instance = new DataValueDeserializer( 'abc' );

		$property = new DIProperty( 'Bar' );
		$property->setPropertyTypeId( '_txt' );

		$dataValue = $instance->newDataValueFrom( $property, 'Foo [[42]] bar' );

		$this->assertInstanceOf(
			'\SMW\DataValues\StringValue',
			$dataValue
		);

		$this->assertEquals(
			'Foo [[abc:42|42]] bar',
			$dataValue->getDataItem()->getString()
		);
	}

}
