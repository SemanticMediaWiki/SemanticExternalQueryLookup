<?php

namespace SEQL\Tests;

use SEQL\HookRegistry;

/**
 * @covers \SEQL\HookRegistry
 * @group semantic-external-query-lookup
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class HookRegistryTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$options = array(
			'externalRepositoryEndpoints' => array(),
		);

		$this->assertInstanceOf(
			'\SEQL\HookRegistry',
			new HookRegistry( $options )
		);
	}

	public function testRegister() {

		$options = array(
			'externalRepositoryEndpoints' => array(),
		);

		$instance = new HookRegistry(
			$options
		);

		$instance->register();

		$this->doTestRegisteredInterwikiLoadPrefixHandler( $instance );
	}

	public function doTestRegisteredInterwikiLoadPrefixHandler( $instance ) {

		$handler = 'InterwikiLoadPrefix';

		$prefix = '';
		$interwiki = array();

		$this->assertTrue(
			$instance->isRegistered( $handler )
		);

		$this->assertThatHookIsExcutable(
			$instance->getHandlerFor( $handler ),
			array( $prefix, &$interwiki )
		);
	}

	private function assertThatHookIsExcutable( \Closure $handler, $arguments ) {
		$this->assertInternalType(
			'boolean',
			call_user_func_array( $handler, $arguments )
		);
	}

}
