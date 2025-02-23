<?php

namespace SEQL\Tests\Integration\ByHttpRequest;

use SEQL\ByHttpRequest\JsonResponseParser;
use SEQL\DataValueDeserializer;
use SEQL\HookRegistry;
use SMW\DIProperty;
use SMW\DIWikiPage;
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

	protected function setUp(): void {
		$externalRepositoryEndpoints = [
			'api-foo' => [
				'http://example.org/index.php/$1',
				'http://example.org/api.php/',
				true
			]
		];

		$hookRegistry = new HookRegistry( [
			'externalRepositoryEndpoints' => $externalRepositoryEndpoints
		] );

		$hookRegistry->register();
	}

	/**
	 * @covers I18nJsonFileIntegrity
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
				$sub = DIWikiPage::doUnserialize( str_replace( " ", "_", $sub ) );
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
				$prop = new DIProperty( str_replace( " ", "_", $prop ) );
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
