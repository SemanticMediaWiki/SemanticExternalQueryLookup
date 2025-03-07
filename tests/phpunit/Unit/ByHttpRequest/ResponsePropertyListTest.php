<?php

namespace SEQL\ByHttpRequest\Tests;

use SEQL\ByHttpRequest\ResponsePropertyList;
use SMW\DIProperty;

/**
 * @covers \SEQL\ByHttpRequest\ResponsePropertyList
 * @group semantic-external-query-lookup
 *
 * @license GPL-2.0-or-later
 * @since 1.0
 *
 * @author mwjames
 */
class ResponsePropertyListTest extends \PHPUnit\Framework\TestCase {

	public function testCanConstruct() {
		$this->assertInstanceOf(
			'\SEQL\ByHttpRequest\ResponsePropertyList',
			new ResponsePropertyList( 'abc' )
		);
	}

	public function testGetPropertyForCategory() {
		$value = [
			'label' => 'Category', 'mode' => 0
		];

		$property = new DIProperty( '_INST' );
		$property->setInterwiki( 'abc' );

		$instance = new ResponsePropertyList( 'abc' );
		$instance->addToPropertyList( $value );

		$this->assertEquals(
			$property,
			$instance->getProperty( 'Category' )
		);

		$this->assertEquals(
			$property,
			$instance->getProperty( '_INST' )
		);
	}

	public function testGetPropertyForRedirectedProperty() {
		$value = [
			'label' => 'Foo', 'mode' => 2, 'redi' => 'was redirect from Bar', 'typeid' => '_wpg'
		];

		$property = new DIProperty( 'Foo' );
		$property->setInterwiki( 'abc' );
		$property->setPropertyTypeId( '_wpg' );

		$instance = new ResponsePropertyList( 'abc' );
		$instance->addToPropertyList( $value );

		$this->assertEquals(
			$property,
			$instance->getProperty( 'Foo' )
		);

		$this->assertEquals(
			$property,
			$instance->getProperty( 'was redirect from Bar' )
		);

		$this->assertEquals(
			[ 'Foo' => $property ],
			$instance->getPropertyList()
		);
	}

	public function testTryToRedeclareTypeOfPredefinedPropertyThrowsException() {
		$value = [
			'label' => 'Modification date', 'mode' => 2, 'typeid' => '_wpg'
		];

		$instance = new ResponsePropertyList( 'abc' );

		$this->expectException( 'RuntimeException' );
		$instance->addToPropertyList( $value );
	}

}
