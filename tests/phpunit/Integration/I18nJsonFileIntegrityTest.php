<?php

namespace SEQL\Tests\Integration;

use SMW\Tests\Utils\UtilityFactory;

/**
 * @group semantic-external-query-lookup
 * @group medium
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class I18nJsonFileIntegrityTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider i18nFileProvider
	 */
	public function testI18NJsonDecodeEncode( $file ) {

		$jsonFileReader = UtilityFactory::getInstance()->newJsonFileReader( $file );

		$this->assertInternalType(
			'integer',
			$jsonFileReader->getModificationTime()
		);

		$this->assertInternalType(
			'array',
			$jsonFileReader->read()
		);
	}

	public function i18nFileProvider() {

		$provider = array();
		$location = $GLOBALS['wgMessagesDirs']['SemanticExternalQueryLookup'];

		$bulkFileProvider = UtilityFactory::getInstance()->newBulkFileProvider( $location );
		$bulkFileProvider->searchByFileExtension( 'json' );

		foreach ( $bulkFileProvider->getFiles() as $file ) {
			$provider[] = array( $file );
		}

		return $provider;
	}

}
