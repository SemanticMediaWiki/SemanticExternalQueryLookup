<?php

namespace SEQL\ByHttpRequest\Tests;

use SEQL\ByHttpRequest\JsonResponseParser;
use SMW\DIProperty;

/**
 * @covers \SEQL\ByHttpRequest\JsonResponseParser
 * @group semantic-external-query-lookup
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class JsonResponseParserTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$dataValueDeserializer = $this->getMockBuilder( '\SEQL\DataValueDeserializer' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SEQL\ByHttpRequest\JsonResponseParser',
			new JsonResponseParser( $dataValueDeserializer )
		);
	}

	/**
	 * @dataProvider resultProvider
	 */
	public function testDoParse( array $result, $rawResponseResult, $hasFurtherResults, ?string $property ) {

		$dataValueDeserializer = $this->getMockBuilder( '\SEQL\DataValueDeserializer' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new JsonResponseParser( $dataValueDeserializer );

		$instance->doParse( $result );

		$this->assertEquals(
			$rawResponseResult,
			$instance->getRawResponseResult()
		);

		$this->assertEquals(
			$hasFurtherResults,
			$instance->hasFurtherResults()
		);

		if ( $property !== null ) {
			$this->assertEquals(
				$property,
				$instance->findPropertyFromInMemoryExternalRepositoryCache( $property )
			);
		}
	}

	public function testDoParseForRedirect() {

		$result = [
			'query' => [
				'printrequests' => [ [ 'label' => 'Foo', 'mode' => 2, 'redi' => 'Bar' , 'typeid' => '_wpg' ] ],
				'results' => []
			]
		];

		$rawResponseResult = [
			'printrequests' => [ [ 'label' => 'Foo', 'mode' => 2, 'redi' => 'Bar' , 'typeid' => '_wpg' ] ],
			'results' => []
		];

		$property = new DIProperty( 'Foo' );
		$property->setPropertyTypeId( '_wpg' );
		$property->setInterwiki( 'abc' );

		$dataValueDeserializer = $this->getMockBuilder( '\SEQL\DataValueDeserializer' )
			->disableOriginalConstructor()
			->getMock();

		$dataValueDeserializer->expects( $this->once() )
			->method( 'getQuerySource' )
			->will( $this->returnValue( 'abc' ) );

		$instance = new JsonResponseParser( $dataValueDeserializer );

		$instance->doParse( $result );

		$this->assertEquals(
			$rawResponseResult,
			$instance->getRawResponseResult()
		);

		$this->assertEquals(
			$property,
			$instance->findPropertyFromInMemoryExternalRepositoryCache( new DIProperty( 'Bar' ) )
		);

		$this->assertEquals(
			$property,
			$instance->findPropertyFromInMemoryExternalRepositoryCache( new DIProperty( 'Foo' ) )
		);
	}

	public function resultProvider(): array {

		#0
		$provider[] = [ [ 'query' => [] ], [], false, null ];

		#1
		$provider[] = [ [ 'query-continue-offset' => 3, 'query' => [] ], [], true, null ];

		#2
		$provider[] = [
			[
				'query-continue-offset' => 3,
				'query' => [ 'printrequests' => [ [ 'label' => 'Category', 'mode' => 0 ] ] ]
			],
			[ 'printrequests' => [ [ 'label' => 'Category', 'mode' => 0 ] ] ],
			true,
			new DIProperty( '_INST' )
		];

		#3
		$provider[] = [
			[
				'query' => [
					'printrequests' => [ [ 'label' => 'Category', 'mode' => 0 ] ],
					'results' => []
				],
			],
			[
				'printrequests' => [ [ 'label' => 'Category', 'mode' => 0 ] ],
				'results' => []
			],
			false,
			new DIProperty( '_INST' )
		];

		return $provider;
	}

}
