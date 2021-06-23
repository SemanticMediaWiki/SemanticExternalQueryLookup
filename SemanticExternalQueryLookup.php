<?php

use SEQL\HookRegistry;

/**
 * @see https://github.com/SemanticMediaWiki/SemanticExternalQueryLookup/
 *
 * @defgroup SEQL Semantic External Query Lookup
 */

/**
 * @codeCoverageIgnore
 */
class SemanticExternalQueryLookup {

	/**
	 * @since 1.0
	 */
	public static function onExtensionFunction() {

		define( 'SEQL_VERSION', '1.0.0-alpha' );
		class_alias( 'SEQL\ByHttpRequestQueryLookup', 'SMWExternalQueryLookup' ); // deprecated
		class_alias( 'SEQL\ByHttpRequestQueryLookup', 'SMWExternalAskQueryLookup' );

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
