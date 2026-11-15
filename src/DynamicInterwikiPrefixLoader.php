<?php

namespace SEQL;

use Interwiki;

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
	 * @since 2.0.1
	 *
	 * @param string $prefix
	 */
	public function isEnabledPrefixForExternalRepository( $prefix ) {
		return isset( $this->enabledExternalRepositoryEndpoints[$prefix] );
	}

	/**
	 * @since 2.0.1
	 *
	 * @param string $prefix
	 *
	 * @return Interwiki|null Interwiki instance for a known external
	 *  repository endpoint or null when the prefix holds no such endpoint
	 */
	public function newInterwiki( $prefix ) {
		if ( !$this->isEnabledPrefixForExternalRepository( $prefix ) ) {
			return null;
		}

		[ $iw_url, $iw_api, $iw_local ] = $this->enabledExternalRepositoryEndpoints[$prefix];

		return new Interwiki(
			$prefix,
			$iw_url,
			$iw_api,
			$prefix,
			$iw_local
		);
	}

	/**
	 * @since 1.0
	 * @deprecated since 2.0.1, use newInterwiki() instead. Kept for
	 *  compatibility with the removed InterwikiLoadPrefix hook.
	 *
	 * @param string $prefix
	 * @param array &$interwiki
	 *
	 * @return bool
	 */
	public function tryToLoadIwMapForExternalRepository( $prefix, &$interwiki ) {
		$interwikiObject = $this->newInterwiki( $prefix );

		if ( $interwikiObject === null ) {
			return true;
		}

		$interwiki = [
			'iw_prefix' => $prefix,
			'iw_url'    => $interwikiObject->getURL(),
			'iw_api'    => $interwikiObject->getAPI(),
			'iw_wikiid' => $interwikiObject->getWikiID(),
			'iw_local'  => $interwikiObject->isLocal(),
			'iw_trans'  => $interwikiObject->isTranscludable()
		];

		return false;
	}

}
