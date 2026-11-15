<?php

namespace SEQL\Tests;

use PHPUnit\Framework\MockObject\MockObject;
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
		$this->assertInstanceOf(
			'\SEQL\HookRegistry',
			new HookRegistry( [] )
		);
	}

	public function testRegister() {
		$instance = new HookRegistry(
			[]
		);

		$instance->register();

		$this->doTestRegisteredSmwAskParserFunctionHandler( $instance );
		$this->doTestRegisteredSmwShowParserFunctionHandler( $instance );
	}

	public function doTestRegisteredSmwAskParserFunctionHandler( $instance ) {
		$handler = 'smwAskParserFunction';

		$this->assertTrue(
			$instance->isRegistered( $handler )
		);

		$override = false;

		$this->assertThatHookIsExcutable(
			$instance->getHandlerFor( $handler ),
			[ $this->newParserMock(), false, [], &$override ]
		);
	}

	public function doTestRegisteredSmwShowParserFunctionHandler( $instance ) {
		$handler = 'smwShowParserFunction';

		$this->assertTrue(
			$instance->isRegistered( $handler )
		);

		$override = false;

		$this->assertThatHookIsExcutable(
			$instance->getHandlerFor( $handler ),
			[ $this->newParserMock(), false, [], &$override ]
		);
	}

	/**
	 * The `smwAskParserFunction`/`smwShowParserFunction` handlers communicate
	 * through the `$override` reference and return no value.
	 */
	private function assertThatHookIsExcutable( \Closure $handler, $arguments ) {
		$this->assertNull(
			call_user_func_array( $handler, $arguments )
		);
	}

	private function newParserMock(): MockObject {
		$parser = $this->getMockBuilder( '\Parser' )
			->disableOriginalConstructor()
			->getMock();

		return $parser;
	}

}
