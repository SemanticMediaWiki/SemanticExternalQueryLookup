<?php

namespace SEQL;

/**
 * Allows to dynamically assign interwiki prefixes without having to
 * create an interwiki table entry.
 *
 * @license GPL-2.0-or-later
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
	 */
	public function isEnabledPrefixForExternalRepository( $prefix ) {
		return isset( $this->enabledExternalRepositoryEndpoints[$prefix] );
	}

	/**
	 * @since 1.0
	 *
	 * @param string $prefix
	 * @param array &$interwiki
	 */
	public function tryToLoadIwMapForExternalRepository( $prefix, &$interwiki ) {
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
