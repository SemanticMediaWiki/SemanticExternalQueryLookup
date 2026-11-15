<?php

namespace SEQL;

use MediaWiki\Hook\MediaWikiServicesHook;
use MediaWiki\MediaWikiServices;

/**
 * MediaWikiServices hook handler to decorate the `InterwikiLookup` service so
 * that endpoints configured in $seqlgExternalRepositoryEndpoints are resolvable
 * as interwiki prefixes without requiring an entry in the `interwiki` table.
 *
 * The handler is registered via the `MediaWikiServices` hook so that the
 * manipulator is available before the `InterwikiLookup` service is
 * instantiated, which replaces the deprecated `InterwikiLoadPrefix` hook.
 *
 * @license GPL-2.0-or-later
 * @since 2.0.1
 */
class MediaWikiServicesHandler implements MediaWikiServicesHook {

	/**
	 * @since 2.0.1
	 *
	 * @param MediaWikiServices $services
	 */
	public function onMediaWikiServices( $services ) {
		$services->addServiceManipulator(
			'InterwikiLookup',
			static function ( $interwikiLookup, MediaWikiServices $serviceContainer ) {
				$dynamicInterwikiPrefixLoader = new DynamicInterwikiPrefixLoader(
					$GLOBALS['seqlgExternalRepositoryEndpoints'] ?? []
				);

				return new InterwikiLookupDecorator(
					$interwikiLookup,
					$dynamicInterwikiPrefixLoader,
					$serviceContainer->getContentLanguage()
				);
			}
		);
	}

}
