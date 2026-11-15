<?php

namespace SEQL\Tests;

use MediaWiki\Language\Language;
use SEQL\DynamicInterwikiPrefixLoader;
use SEQL\InterwikiLookupDecorator;

/**
 * @covers \SEQL\InterwikiLookupDecorator
 * @group semantic-external-query-lookup
 *
 * @license GPL-2.0-or-later
 * @since 2.0.1
 */
class InterwikiLookupDecoratorTest extends \PHPUnit\Framework\TestCase {

	private $interwikiPrefixMap = [
		'mw-foo' => [
			'http://example.org:8080/mw-foo/index.php/$1',
			'http://example.org:8080/mw-foo/api.php',
			true
		]
	];

	public function testCanConstruct() {
		$interwikiLookup = $this->createMock( '\MediaWiki\Interwiki\InterwikiLookup' );

		$this->assertInstanceOf(
			'\SEQL\InterwikiLookupDecorator',
			new InterwikiLookupDecorator(
				$interwikiLookup,
				new DynamicInterwikiPrefixLoader( [] ),
				$this->newContentLanguageMock()
			)
		);
	}

	public function testFetchOnExternalRepositoryPrefix() {
		$interwikiLookup = $this->createMock( '\MediaWiki\Interwiki\InterwikiLookup' );

		$interwikiLookup->expects( $this->never() )
			->method( 'fetch' );

		$instance = new InterwikiLookupDecorator(
			$interwikiLookup,
			new DynamicInterwikiPrefixLoader( $this->interwikiPrefixMap ),
			$this->newContentLanguageMock()
		);

		// Mirrors `ClassicInterwikiLookup::fetch()` resolving the prefix
		// with the content language (case-insensitive match)
		$interwiki = $instance->fetch( 'MW-FOO' );

		$this->assertInstanceOf(
			'\Interwiki',
			$interwiki
		);

		$this->assertEquals(
			'http://example.org:8080/mw-foo/api.php',
			$interwiki->getAPI()
		);

		$this->assertEquals(
			'http://example.org:8080/mw-foo/index.php/$1',
			$interwiki->getURL()
		);
	}

	public function testFetchOnNonExternalRepositoryPrefixIsDelegated() {
		$interwiki = $this->createMock( '\Interwiki' );

		$interwikiLookup = $this->createMock( '\MediaWiki\Interwiki\InterwikiLookup' );

		$interwikiLookup->expects( $this->once() )
			->method( 'fetch' )
			->with( 'mw-bar' )
			->willReturn( $interwiki );

		$instance = new InterwikiLookupDecorator(
			$interwikiLookup,
			new DynamicInterwikiPrefixLoader( $this->interwikiPrefixMap ),
			$this->newContentLanguageMock()
		);

		$this->assertSame(
			$interwiki,
			$instance->fetch( 'mw-bar' )
		);
	}

	public function testIsValidInterwikiOnExternalRepositoryPrefix() {
		$interwikiLookup = $this->createMock( '\MediaWiki\Interwiki\InterwikiLookup' );

		// Only the delegation for a non-external prefix is expected to hit
		// the decorated InterwikiLookup
		$interwikiLookup->expects( $this->once() )
			->method( 'isValidInterwiki' )
			->with( 'mw-bar' )
			->willReturn( true );

		$instance = new InterwikiLookupDecorator(
			$interwikiLookup,
			new DynamicInterwikiPrefixLoader( $this->interwikiPrefixMap ),
			$this->newContentLanguageMock()
		);

		$this->assertTrue(
			$instance->isValidInterwiki( 'mw-foo' )
		);

		$this->assertTrue(
			$instance->isValidInterwiki( 'mw-bar' )
		);
	}

	public function testGetAllPrefixesIsDelegated() {
		$interwikiLookup = $this->createMock( '\MediaWiki\Interwiki\InterwikiLookup' );

		$interwikiLookup->expects( $this->once() )
			->method( 'getAllPrefixes' )
			->with( null )
			->willReturn( [] );

		$instance = new InterwikiLookupDecorator(
			$interwikiLookup,
			new DynamicInterwikiPrefixLoader( $this->interwikiPrefixMap ),
			$this->newContentLanguageMock()
		);

		$this->assertSame(
			[],
			$instance->getAllPrefixes()
		);
	}

	public function testFetchAndIsValidInterwikiWithNullOrEmptyPrefixIssueNoWarning() {
		$interwikiLookup = $this->createMock( '\MediaWiki\Interwiki\InterwikiLookup' );

		$fetchArgs = [];
		$interwikiLookup->method( 'fetch' )
			->willReturnCallback( static function ( $prefix ) use ( &$fetchArgs ) {
				$fetchArgs[] = $prefix;
				return $prefix === null ? null : false;
			} );

		$isValidArgs = [];
		$interwikiLookup->method( 'isValidInterwiki' )
			->willReturnCallback( static function ( $prefix ) use ( &$isValidArgs ) {
				$isValidArgs[] = $prefix;
				return false;
			} );

		// Any PHP warning, deprecation, or notice (e.g. `strtolower( null )`
		// on PHP 8.1+) fails the test
		set_error_handler( static function ( $errno, $errstr ) {
			throw new \ErrorException( $errstr );
		} );

		try {
			$instance = new InterwikiLookupDecorator(
				$interwikiLookup,
				new DynamicInterwikiPrefixLoader( $this->interwikiPrefixMap ),
				$this->newContentLanguageMock()
			);

			$this->assertNull( $instance->fetch( null ) );
			$this->assertFalse( $instance->fetch( '' ) );
			$this->assertFalse( $instance->isValidInterwiki( null ) );
			$this->assertFalse( $instance->isValidInterwiki( '' ) );
		} finally {
			restore_error_handler();
		}

		// Null/empty prefixes are delegated unaltered, never resolved against
		// the content language or the external repository endpoints
		$this->assertSame( [ null, '' ], $fetchArgs );
		$this->assertSame( [ null, '' ], $isValidArgs );
	}

	public function testInvalidateCacheIsDelegated() {
		$interwikiLookup = $this->createMock( '\MediaWiki\Interwiki\InterwikiLookup' );

		$interwikiLookup->expects( $this->once() )
			->method( 'invalidateCache' )
			->with( 'mw-bar' );

		$instance = new InterwikiLookupDecorator(
			$interwikiLookup,
			new DynamicInterwikiPrefixLoader( $this->interwikiPrefixMap ),
			$this->newContentLanguageMock()
		);

		$instance->invalidateCache( 'mw-bar' );
	}

	private function newContentLanguageMock(): Language {
		$contentLanguage = $this->createMock( Language::class );

		$contentLanguage->method( 'lc' )
			->willReturnCallback( static function ( $str ) {
				return strtolower( $str );
			} );

		return $contentLanguage;
	}

}
