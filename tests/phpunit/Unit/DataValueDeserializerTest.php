<?php

namespace SEQL\Tests;

use SEQL\DataValueDeserializer;
use SMW\DIWikiPage;

/**
 * @covers \SEQL\DataValueDeserializer
 * @group semantic-external-query-lookup
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class DataValueDeserializerTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'\SEQL\DataValueDeserializer',
			new DataValueDeserializer( 'foo' )
		);
	}

	public function testNewDiWikiPage() {

		$instance = new DataValueDeserializer( 'foo' );

		$value = array(
			'namespace' => NS_MAIN,
			'fulltext'  => 'abc def'
		);

		$this->assertEquals(
			new DIWikiPage( 'Foo:abc_def', NS_MAIN ),
			$instance->newDiWikiPage( $value )
		);
	}

}
