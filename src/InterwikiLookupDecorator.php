<?php

namespace SEQL;

use Interwiki;
use MediaWiki\Interwiki\InterwikiLookup;
use MediaWiki\Language\Language;

/**
 * InterwikiLookup decorator to expose endpoints configured in
 * $seqlgExternalRepositoryEndpoints as interwiki prefixes without having
 * to create an interwiki table entry.
 *
 * Prefixes found in the external repository endpoints setting take precedence
 * over those found in the `interwiki` table, any other prefix is delegated to
 * the decorated InterwikiLookup.
 *
 * Prefix matching follows `ClassicInterwikiLookup` and is case-insensitive,
 * with the prefix being resolved against the content language.
 *
 * @license GPL-2.0-or-later
 * @since 2.0.1
 */
class InterwikiLookupDecorator implements InterwikiLookup {

	/**
	 * @var InterwikiLookup
	 */
	private $interwikiLookup;

	/**
	 * @var DynamicInterwikiPrefixLoader
	 */
	private $dynamicInterwikiPrefixLoader;

	/**
	 * @var Language
	 */
	private $contentLanguage;

	/**
	 * @since 2.0.1
	 *
	 * @param InterwikiLookup $interwikiLookup
	 * @param DynamicInterwikiPrefixLoader $dynamicInterwikiPrefixLoader
	 * @param Language $contentLanguage
	 */
	public function __construct( InterwikiLookup $interwikiLookup, DynamicInterwikiPrefixLoader $dynamicInterwikiPrefixLoader, Language $contentLanguage ) {
		$this->interwikiLookup = $interwikiLookup;
		$this->dynamicInterwikiPrefixLoader = $dynamicInterwikiPrefixLoader;
		$this->contentLanguage = $contentLanguage;
	}

	/**
	 * @since 2.0.1
	 *
	 * @param string|null $prefix
	 *
	 * @return bool
	 */
	public function isValidInterwiki( $prefix ) {
		// Guarded like `ClassicInterwikiLookup` so that neither the content
		// language nor the prefix loader are consulted with `null` or an
		// empty prefix
		if ( $prefix === null || $prefix === '' ) {
			return $this->interwikiLookup->isValidInterwiki( $prefix );
		}

		if ( $this->dynamicInterwikiPrefixLoader->isEnabledPrefixForExternalRepository( $this->contentLanguage->lc( $prefix ) ) ) {
			return true;
		}

		return $this->interwikiLookup->isValidInterwiki( $prefix );
	}

	/**
	 * @since 2.0.1
	 *
	 * @param string|null $prefix
	 *
	 * @return Interwiki|null|false
	 */
	public function fetch( $prefix ) {
		if ( $prefix !== null && $prefix !== '' ) {
			$interwiki = $this->dynamicInterwikiPrefixLoader->newInterwiki( $this->contentLanguage->lc( $prefix ) );

			if ( $interwiki !== null ) {
				return $interwiki;
			}
		}

		return $this->interwikiLookup->fetch( $prefix );
	}

	/**
	 * @since 2.0.1
	 *
	 * @param bool|null $local
	 *
	 * @return array[]
	 */
	public function getAllPrefixes( $local = null ) {
		return $this->interwikiLookup->getAllPrefixes( $local );
	}

	/**
	 * @since 2.0.1
	 *
	 * @param string $prefix
	 */
	public function invalidateCache( $prefix ) {
		$this->interwikiLookup->invalidateCache( $prefix );
	}

}
