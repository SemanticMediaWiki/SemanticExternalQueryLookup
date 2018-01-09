<?php

use SEQL\HookRegistry;

/**
 * @see https://github.com/SemanticMediaWiki/SemanticExternalQueryLookup/
 *
 * @defgroup SEQL Semantic External Query Lookup
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This file is part of the Semantic External Query Lookup extension, it is not a valid entry point.' );
}

if ( version_compare( $GLOBALS[ 'wgVersion' ], '1.23', 'lt' ) ) {
	die( '<b>Error:</b> This version of <a href="https://github.com/SemanticMediaWiki/SemanticExternalQueryLookup/">Semantic External Query Lookup</a> is only compatible with MediaWiki 1.23 or above. You need to upgrade MediaWiki first.' );
}

if ( defined( 'SEQL_VERSION' ) ) {
	// Do not initialize more than once.
	return 1;
}

SemanticExternalQueryLookup::initExtension();

$GLOBALS['wgExtensionFunctions'][] = function() {
	SemanticExternalQueryLookup::onExtensionFunction();
};

/**
 * @codeCoverageIgnore
 */
class SemanticExternalQueryLookup {

	/**
	 * @since 1.0
	 */
	public static function initExtension() {

		// Load DefaultSettings
		require_once __DIR__ . '/DefaultSettings.php';

		define( 'SEQL_VERSION', '1.0.0-alpha' );

		// Register extension info
		$GLOBALS['wgExtensionCredits']['semantic'][] = array(
			'path'           => __FILE__,
			'name'           => 'Semantic External Query Lookup',
			'author'         => array( 'James Hong Kong' ),
			'url'            => 'https://github.com/SemanticMediaWiki/SemanticExternalQueryLookup/',
			'descriptionmsg' => 'seql-desc',
			'version'        => SEQL_VERSION,
			'license-name'   => 'GPL-2.0-or-later',
		);

		// Register message files
		$GLOBALS['wgMessagesDirs']['SemanticExternalQueryLookup'] = __DIR__ . '/i18n';
	}

	/**
	 * @since 1.0
	 */
	public static function onExtensionFunction() {

		$options = array(
			'externalRepositoryEndpoints' => $GLOBALS['seqlgExternalRepositoryEndpoints']
		);

		$hookRegistry = new HookRegistry(
			$options
		);

		$hookRegistry->register();
	}

	/**
	 * @since 1.0
	 *
	 * @return string|null
	 */
	public static function getVersion() {
		return SEQL_VERSION;
	}

}
