<?php

namespace SEQL\Tests;

use SEQL\QueryEncoder;

/**
 * @covers \SEQL\QueryEncoder
 * @group semantic-external-query-lookup
 *
 * @license GPL-2.0-or-later
 * @since 1.0
 *
 * @author mwjames
 */
class QueryEncoderTest extends \PHPUnit\Framework\TestCase {

	/**
	 * @dataProvider queryElementProvider
	 */
	public function testEncode( $sortKeys, $extraPrintouts, $expectedEncode, $expectedRawEncode ) {
		$query = $this->getMockBuilder( '\SMWQuery' )
			->disableOriginalConstructor()
			->getMock();

		$query->expects( $this->any() )
			->method( 'getQueryString' )
			->willReturn( '[[Foo::bar]]' );

		$query->expects( $this->any() )
			->method( 'getLimit' )
			->willReturn( 42 );

		$query->expects( $this->any() )
			->method( 'getOffset' )
			->willReturn( 0 );

		$query->expects( $this->any() )
			->method( 'getMainlabel' )
			->willReturn( '' );

		$query->expects( $this->any() )
			->method( 'getSortKeys' )
			->willReturn( $sortKeys );

		$query->expects( $this->any() )
			->method( 'getExtraPrintouts' )
			->willReturn( $extraPrintouts );

		$this->assertSame(
			$expectedEncode,
			QueryEncoder::encode( $query )
		);

		$this->assertSame(
			$expectedRawEncode,
			QueryEncoder::rawUrlEncode( $query )
		);
	}

	public function queryElementProvider() {
		# 0
		$provider[] = [
			[],
			[],
			'[[Foo::bar]]|limit=42|offset=0|mainlabel=',
			'%5B%5BFoo%3A%3Abar%5D%5D%7Climit%3D42%7Coffset%3D0%7Cmainlabel%3D'
		];

		# 1
		$provider[] = [
			[ 'Foobar' => 'DESC' ],
			[],
			'[[Foo::bar]]|limit=42|offset=0|mainlabel=|sort=Foobar|order=desc',
			'%5B%5BFoo%3A%3Abar%5D%5D%7Climit%3D42%7Coffset%3D0%7Cmainlabel%3D%7Csort%3DFoobar%7Corder%3Ddesc'
		];

		# 2
		$provider[] = [
			[ 'Foobar' => 'DESC', 'Foobaz' => 'ASC' ],
			[],
			'[[Foo::bar]]|limit=42|offset=0|mainlabel=|sort=Foobar,Foobaz|order=desc,asc',
			'%5B%5BFoo%3A%3Abar%5D%5D%7Climit%3D42%7Coffset%3D0%7Cmainlabel%3D%7Csort%3DFoobar%2CFoobaz%7Corder%3Ddesc%2Casc'
		];

		# 3
		$printRequest = $this->getMockBuilder( '\SMW\Query\PrintRequest' )
			->disableOriginalConstructor()
			->getMock();

		$printRequest->expects( $this->any() )
			->method( 'getSerialisation' )
			->willReturn( '?ABC' );

		$provider[] = [
			[],
			[ $printRequest ],
			'[[Foo::bar]]|?ABC|limit=42|offset=0|mainlabel=',
			'%5B%5BFoo%3A%3Abar%5D%5D%7C%3FABC%7Climit%3D42%7Coffset%3D0%7Cmainlabel%3D'
		];

		# 4 (#show returns with an extra =)
		$printRequest = $this->getMockBuilder( '\SMW\Query\PrintRequest' )
			->disableOriginalConstructor()
			->getMock();

		$printRequest->expects( $this->any() )
			->method( 'getSerialisation' )
			->willReturn( '?ABC=' );

		$provider[] = [
			[],
			[ $printRequest ],
			'[[Foo::bar]]|?ABC|limit=42|offset=0|mainlabel=',
			'%5B%5BFoo%3A%3Abar%5D%5D%7C%3FABC%7Climit%3D42%7Coffset%3D0%7Cmainlabel%3D'
		];

		return $provider;
	}

}
