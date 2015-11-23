<?php

namespace SEQL\ByAskApiHttpRequest\Tests;

use SEQL\ByAskApiHttpRequest\JsonResponseParser;
use SMW\DIProperty;

/**
 * @covers \SEQL\ByAskApiHttpRequest\JsonResponseParser
 * @group semantic-external-query-lookup
 *
 * @license GNU GPL v2+
 * @since 0.1
 *
 * @author mwjames
 */
class JsonResponseParserTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$dataValueDeserializer = $this->getMockBuilder( '\SEQL\DataValueDeserializer' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SEQL\ByAskApiHttpRequest\JsonResponseParser',
			new JsonResponseParser( $dataValueDeserializer )
		);
	}

	/**
	 * @dataProvider resultProvider
	 */
	public function testDoParse( $result, $rawResponseResult, $hasFurtherResults, $property ) {

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

	public function resultProvider() {

		$provider[] = array(
			array( 'query' => array() ),
			array(),
			false,
			null
		);

		$provider[] = array(
			array(
				'query-continue-offset' => 3,
				'query' => array()
			),
			array(),
			true,
			null
		);

		$provider[] = array(
			array(
				'query-continue-offset' => 3,
				'query' => array(
					'printrequests' => array(
						array( 'label' => 'Category', 'mode' => 0 )
					)
				)
			),
			array(
				'printrequests' => array(
					array( 'label' => 'Category', 'mode' => 0 )
				)
			),
			true,
			new DIProperty( '_INST' )
		);

		return $provider;
	}

}
