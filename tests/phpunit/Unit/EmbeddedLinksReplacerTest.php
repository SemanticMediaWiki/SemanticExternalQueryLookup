<?php

namespace SEQL\Tests;

use SEQL\EmbeddedLinksReplacer;

/**
 * @covers \SEQL\EmbeddedLinksReplacer
 * @group semantic-external-query-lookup
 *
 * @license GPL-2.0-or-later
 * @since 1.0
 *
 * @author mwjames
 */
class EmbeddedLinksReplacerTest extends \PHPUnit\Framework\TestCase {

	private $querySource;

	protected function setUp(): void {
		$this->querySource = 'abc';
	}

	public function testCanConstruct() {
		$this->assertInstanceOf(
			'\SEQL\EmbeddedLinksReplacer',
			new EmbeddedLinksReplacer( $this->querySource )
		);
	}

	/**
	 * @dataProvider textProvider
	 */
	public function testReplace( $text, $expected ) {
		$instance = new EmbeddedLinksReplacer( $this->querySource );

		$this->assertEquals(
			$expected,
			$instance->replace( $text )
		);
	}

	public function textProvider() {
		# 0
		$provider[] = [
			'Foo bar',
			'Foo bar'
		];

		# 1
		$provider[] = [
			'Foo [42] bar',
			'Foo [42] bar'
		];

		# 2
		$provider[] = [
			'Foo [42 1001] bar',
			'Foo [42 1001] bar'
		];

		# 3
		$provider[] = [
			'Foo [[42]] bar',
			'Foo [[abc:42|42]] bar'
		];

		# 4
		$provider[] = [
			'Foo [[42|1001]] bar',
			'Foo [[abc:42|1001]] bar'
		];

		// We can't guess the type of a remote annotation therefore it is turned
		// into an simple text value

		#5
		$provider[] = [
			'Foo [[Has number::42]] bar',
			'Foo 42 bar'
		];

		# 6
		$provider[] = [
			'Foo [[Has number::42|1001]] bar',
			'Foo 1001 bar'
		];

		return $provider;
	}

}
