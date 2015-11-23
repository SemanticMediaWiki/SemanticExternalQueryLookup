<?php

use SEQL\HookRegistry;

/**
 * @see https://github.com/SemanticMediaWiki/SemanticExternalQueryLookup/
 *
 * @defgroup SEQL Semantic External Query Lookup
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This file is part of the SemanticExternalQueryLookup extension, it is not a valid entry point.' );
}

if ( version_compare( $GLOBALS[ 'wgVersion' ], '1.23', 'lt' ) ) {
	die( '<b>Error:</b> This version of <a href="https://github.com/SemanticMediaWiki/SemanticExternalQueryLookup/">SemanticExternalQueryLookup</a> is only compatible with MediaWiki 1.23 or above. You need to upgrade MediaWiki first.' );
}

if ( defined( 'SEQL_VERSION' ) ) {
	// Do not initialize more than once.
	return 1;
}

define( 'SEQL_VERSION', '0.1' );

/**
 * @codeCoverageIgnore
 */
call_user_func( function () {

	// Register extension info
	$GLOBALS['wgExtensionCredits']['semantic'][] = array(
		'path'           => __FILE__,
		'name'           => 'Semantic External Query Lookup',
		'author'         => array( 'James Hong Kong' ),
		'url'            => 'https://github.com/SemanticMediaWiki/SemanticExternalQueryLookup/',
		'descriptionmsg' => 'seql-desc',
		'version'        => SEQL_VERSION,
		'license-name'   => 'GPL-2.0+',
	);

	// Alias to keep LocalSettings independent from the internal NS usage
	class_alias( 'SEQL\ByHttpRequestQueryLookup', 'SMWExternalQueryLookup' ); // deprecated
	class_alias( 'SEQL\ByHttpRequestQueryLookup', 'SMWExternalAskQueryLookup' );

	// Register message files
	$GLOBALS['wgMessagesDirs']['semantic-external-query-lookup'] = __DIR__ . '/i18n';

	$GLOBALS['seqlgExternalRepositoryEndpoints'] = array();

	// Finalize extension setup
	$GLOBALS['wgExtensionFunctions'][] = function() {

		$options = array(
			'externalRepositoryEndpoints' => $GLOBALS['seqlgExternalRepositoryEndpoints']
		);

		$hookRegistry = new HookRegistry(
			$options
		);

		$hookRegistry->register();
	};

} );
