<?php

namespace SEQL\Tests;

use SEQL\HookRegistry;

/**
 * @covers \SEQL\HookRegistry
 * @group semantic-external-query-lookup
 *
 * @license GPL-2.0-or-later
 * @since 1.0
 *
 * @author mwjames
 */
class HookRegistryTest extends \PHPUnit\Framework\TestCase {

	public function testCanConstruct() {
		$options = [
			'externalRepositoryEndpoints' => [],
		];

		$this->assertInstanceOf(
			'\SEQL\HookRegistry',
			new HookRegistry( $options )
		);
	}

	public function testRegister() {
		$options = [
			'externalRepositoryEndpoints' => [],
		];

		$instance = new HookRegistry(
			$options
		);

		$instance->register();

		$this->doTestRegisteredInterwikiLoadPrefixHandler( $instance );
	}

	public function doTestRegisteredInterwikiLoadPrefixHandler( $instance ) {
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

	private function assertThatHookIsExcutable( \Closure $handler, $arguments ) {
		$this->assertIsBool(
			call_user_func_array( $handler, $arguments )
		);
	}

}
