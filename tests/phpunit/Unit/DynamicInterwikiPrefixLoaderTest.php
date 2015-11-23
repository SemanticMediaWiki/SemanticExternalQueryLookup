<?php

namespace SEQL\Tests;

use SEQL\DynamicInterwikiPrefixLoader;

/**
 * @covers \SEQL\DynamicInterwikiPrefixLoader
 * @group semantic-external-query-lookup
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class DynamicInterwikiPrefixLoaderTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$interwikiPrefixMap = array();

		$this->assertInstanceOf(
			'\SEQL\DynamicInterwikiPrefixLoader',
			new DynamicInterwikiPrefixLoader( $interwikiPrefixMap )
		);
	}

	public function testLoadIwMapForExternalRepositoryMatch() {

		$interwikiPrefixMap = array(
			'mw-foo' => array(
				'http://example.org:8080/mw-foo/index.php/$1', // corresponds to iw_url
				'http://example.org:8080/mw-foo/api.php',      // corresponds to iw_api
				true                                           // corresponds to iw_local
			)
		);

		$instance = new DynamicInterwikiPrefixLoader( $interwikiPrefixMap );

		$expected = array(
			'iw_prefix' => 'mw-foo',
			'iw_url' => 'http://example.org:8080/mw-foo/index.php/$1',
			'iw_api' => 'http://example.org:8080/mw-foo/api.php',
			'iw_wikiid' => 'mw-foo',
			'iw_local' => true,
			'iw_trans' => false
		);

		$interwiki = array();

		$this->assertFalse(
			$instance->tryToLoadIwMapForExternalRepository( 'mw-foo', $interwiki )
		);

		$this->assertEquals(
			$expected,
			$interwiki
		);
	}

	public function testTryLoadIwMapForNoExternalRepositoryMatch() {

		$interwikiPrefixMap = array();

		$instance = new DynamicInterwikiPrefixLoader( $interwikiPrefixMap );

		$interwiki = array();

		$this->assertTrue(
			$instance->tryToLoadIwMapForExternalRepository( 'mw-foo', $interwiki )
		);

		$this->assertEmpty(
			$interwiki
		);
	}

}
