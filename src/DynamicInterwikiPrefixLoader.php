<?php

namespace SEQL;

/**
 * Allows to dynamically to assign interwiki prefixes without having to
 * create an interwiki entry.
 * 
 * @license GNU GPL v2+
 * @since 0.1
 *
 * @author mwjames
 */
class DynamicInterwikiPrefixLoader {

	/**
	 * @var array
	 */
	private $interwikiPrefixMap = array();

	/**
	 * @since 0.1
	 *
	 * @param array $interwikiPrefixMap
	 */
	public function __construct( array $interwikiPrefixMap = array() ) {
		$this->interwikiPrefixMap = $interwikiPrefixMap;
	}

	/**
	 * @since 0.1
	 *
	 * @param string $prefix
	 */
	public function isPrefixForExternalRepository( $prefix ) {
		return isset( $this->interwikiPrefixMap[$prefix] );
	}

	/**
	 * @since 0.1
	 *
	 * @param string $prefix
	 * @param array &$interwiki
	 */
	public function tryToLoadIwMapForExternalRepository( $prefix, &$interwiki ) {

		if ( !$this->isPrefixForExternalRepository( $prefix ) ) {
			return true;
		}

		list( $iw_url, $iw_api, $iw_local ) = $this->interwikiPrefixMap[$prefix];

		$interwiki = array(
			'iw_prefix' => $prefix,
			'iw_url'    => $iw_url,
			'iw_api'    => $iw_api,
			'iw_wikiid' => $prefix,
			'iw_local'  => $iw_local,
			'iw_trans'  => false,
		);

		return false;
	}

}
