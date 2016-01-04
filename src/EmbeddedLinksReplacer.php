<?php

namespace SEQL;

use SMW\InTextAnnotationParser;

/**
 * Find and replace [[]] with an appropriate remote source link.
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class EmbeddedLinksReplacer {

	/**
	 * @var string
	 */
	private $querySource;

	/**
	 * @since 1.0
	 *
	 * @param string $querySource
	 */
	public function __construct( $querySource ) {
		$this->querySource = $querySource;
	}

	/**
	 * @since 1.0
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public function replace( $value ) {

		// Strip any [[ :: ]] from a value to avoid "foreign" annotation parsing
		// for annotation embedded within a value
		$value = InTextAnnotationParser::removeAnnotation(
			$value
		);

		return $this->replaceEmbeddedLinksWith( $this->querySource, $value );
	}

	private function replaceEmbeddedLinksWith( $source, $value ) {
		$value = preg_replace_callback(
			'/\[\[(.*)\]\]/xu',
			function( array $matches ) use( $source ) {
				$caption = false;
				$value = '';

				if ( array_key_exists( 1, $matches ) ) {
					$parts = explode( '|', $matches[1] );
					$value = array_key_exists( 0, $parts ) ? $parts[0] : '';
					$caption = array_key_exists( 1, $parts ) ? $parts[1] : false;
				}

				if ( $caption === false ) {
					$caption = $value;
				}

				return '[[' . $source . ':' . $value . '|'  . $caption . ']]';
			},
			$value
		);

		return $value;
	}

}
