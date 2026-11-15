<?php

namespace SEQL\Tests\Integration\ByHttpRequest;

use MediaWiki\MediaWikiServices;
use SEQL\ByHttpRequest\JsonResponseParser;
use SEQL\DataValueDeserializer;
use SMW\DataItems\Property;
use SMW\DataItems\WikiPage;
use SMW\Tests\Utils\UtilityFactory;

/**
 * @group semantic-external-query-lookup
 * @group medium
 *
 * @license GPL-2.0-or-later
 * @since 1.0
 *
 * @author mwjames
 */
class JsonParseOnFauxHttpResponseTest extends \PHPUnit\Framework\TestCase {

	private $oldExternalRepositoryEndpoints;

	protected function setUp(): void {
		$externalRepositoryEndpoints = [
			'api-foo' => [
				'http://example.org/index.php/$1',
				'http://example.org/api.php/',
				true
			]
		];

		// The `api-foo` prefix is resolved by the InterwikiLookup decorator
		// (registered via the `MediaWikiServices` hook), which reads the
		// endpoints from the global when the decorator is instantiated.
		// `Title::newFromText` consults the `TitleParser`/`_MediaWikiTitleCodec`
		// services and those captured the `InterwikiLookup` at their own
		// instantiation, therefore the whole chain has to be reset to make
		// the endpoints above visible to title parsing.
		$this->oldExternalRepositoryEndpoints = $GLOBALS['seqlgExternalRepositoryEndpoints'] ?? false;
		$GLOBALS['seqlgExternalRepositoryEndpoints'] = $externalRepositoryEndpoints;

		$services = MediaWikiServices::getInstance();
		$services->resetServiceForTesting( 'InterwikiLookup' );
		$services->resetServiceForTesting( '_MediaWikiTitleCodec' );
		$services->resetServiceForTesting( 'TitleParser' );
	}

	protected function tearDown(): void {
		if ( $this->oldExternalRepositoryEndpoints === false ) {
			unset( $GLOBALS['seqlgExternalRepositoryEndpoints'] );
		} else {
			$GLOBALS['seqlgExternalRepositoryEndpoints'] = $this->oldExternalRepositoryEndpoints;
		}

		if ( MediaWikiServices::hasInstance() ) {
			$services = MediaWikiServices::getInstance();
			$services->resetServiceForTesting( 'InterwikiLookup' );
			$services->resetServiceForTesting( '_MediaWikiTitleCodec' );
			$services->resetServiceForTesting( 'TitleParser' );
		}

		parent::tearDown();
	}

	/**
	 * @covers \SEQL\ByHttpRequest\JsonResponseParser
	 * @dataProvider jsonFileProvider
	 */
	public function testQueryResultFetcherFromCannedJsonResponse( $file ) {
		$jsonFileReader = UtilityFactory::getInstance()->newJsonFileReader( $file );
		$content = $jsonFileReader->read();

		$instance = new JsonResponseParser(
			new DataValueDeserializer( $content['query-source'] )
		);

		$instance->doParse( $content['api-response'] );

		$this->assertSubjectList(
			$content['expected']['subjectList'],
			$instance->getResultSubjectList()
		);

		$this->assertEquals(
			$content['expected']['hasFurtherResults'],
			$instance->hasFurtherResults()
		);

		$this->assertPrintRequestPropertyList(
			$content['expected']['printRequestPropertyList'],
			$instance->getPrintRequestPropertyList()
		);
	}

	private function assertSubjectList( $expectedSubjectList, $subjectList ) {
		foreach ( $subjectList as $subject ) {
			foreach ( $expectedSubjectList as $key => $sub ) {
				$sub = WikiPage::doUnserialize( str_replace( " ", "_", $sub ) );
				if ( $subject->equals( $sub ) ) {
					unset( $expectedSubjectList[$key] );
					break;
				}
			}
		}

		$this->assertEmpty(
			$expectedSubjectList,
			'Failed because of a missing match: ' . implode( ',', $expectedSubjectList )
		);
	}

	private function assertPrintRequestPropertyList( $expectedPropertyList, $printRequestPropertyList ) {
		foreach ( $printRequestPropertyList as $property ) {
			foreach ( $expectedPropertyList as $key => $prop ) {
				$prop = new Property( str_replace( " ", "_", $prop ) );
				if ( $property->equals( $prop ) ) {
					unset( $expectedPropertyList[$key] );
					break;
				}
			}
		}

		$this->assertEmpty(
			$expectedPropertyList,
			'Failed because of a missing match: ' . implode( ',', $expectedPropertyList )
		);
	}

	public function jsonFileProvider() {
		$provider = [];
		$location = __DIR__ . '/Fixtures';

		$bulkFileProvider = UtilityFactory::getInstance()->newBulkFileProvider( $location );
		$bulkFileProvider->searchByFileExtension( 'json' );

		foreach ( $bulkFileProvider->getFiles() as $file ) {
			$provider[] = [ $file ];
		}

		return $provider;
	}

}
