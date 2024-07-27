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

		$options = [ 'externalRepositoryEndpoints' => [] ];

		$this->assertInstanceOf(
			'\SEQL\HookRegistry',
			new HookRegistry( $options )
		);
	}

	public function testRegister() {

		$options = [ 'externalRepositoryEndpoints' => [] ];

		$instance = new HookRegistry(
			$options
		);

		$instance->register();

		$this->doTestRegisteredInterwikiLoadPrefixHandler( $instance );
	}

	public function doTestRegisteredInterwikiLoadPrefixHandler( HookRegistry $instance ) {

		$handler = 'InterwikiLoadPrefix';

		$prefix = '';
		$interwiki = [];

		$this->assertTrue(
			$instance->isRegistered( $handler )
		);

		$this->assertThatHookIsExcutable(
			$instance->getHandlerFor( $handler ),
			[ $prefix, &$interwiki ]
		);
	}

	private function assertThatHookIsExcutable( \Closure $handler, array $arguments ) {
		$this->assertInternalType(
			'boolean',
			call_user_func_array( $handler, $arguments )
		);
	}
}
