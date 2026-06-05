<?php

namespace SEQL;

/**
 * @license GPL-2.0-or-later
 * @since 2.0
 *
 * @author mwjames
 */
class Setup {

	/**
	 * @since 2.0
	 */
	public static function onExtensionFunction() {
		class_alias( ByHttpRequestQueryLookup::class, 'SMWExternalQueryLookup' ); // deprecated
		class_alias( ByHttpRequestQueryLookup::class, 'SMWExternalAskQueryLookup' );

		$options = [
			'externalRepositoryEndpoints' => $GLOBALS['seqlgExternalRepositoryEndpoints']
		];

		$hookRegistry = new HookRegistry(
			$options
		);

		$hookRegistry->register();
	}

}
