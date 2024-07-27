<?php

namespace SEQL;

/**
 * Allows to dynamically assign interwiki prefixes without having to
 * create an interwiki table entry.
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class DynamicInterwikiPrefixLoader {

	/**
	 * @var array
	 */
	private $enabledExternalRepositoryEndpoints = [];

	/**
	 * @since 1.0
	 *
	 * @param array $enabledExternalRepositoryEndpoints
	 */
	public function __construct( array $enabledExternalRepositoryEndpoints = [] ) {
		$this->enabledExternalRepositoryEndpoints = $enabledExternalRepositoryEndpoints;
	}

	/**
	 * @since 1.0
	 *
	 * @param string $prefix
	 * @return bool
	 */
	public function isEnabledPrefixForExternalRepository( string $prefix ): bool {
		return isset( $this->enabledExternalRepositoryEndpoints[$prefix] );
	}

	/**
	 * @since 1.0
	 *
	 * @param string $prefix
	 * @param array &$interwiki
	 * @return bool
	 */
	public function tryToLoadIwMapForExternalRepository( string $prefix, array &$interwiki ): bool {

		if ( !$this->isEnabledPrefixForExternalRepository( $prefix ) ) {
			return true;
		}

		[ $iw_url, $iw_api, $iw_local ] = $this->enabledExternalRepositoryEndpoints[$prefix];

		$interwiki = [
			'iw_prefix' => $prefix,
			'iw_url'    => $iw_url,
			'iw_api'    => $iw_api,
			'iw_wikiid' => $prefix,
			'iw_local'  => $iw_local,
			'iw_trans'  => false,
		];

		return false;
	}

}
