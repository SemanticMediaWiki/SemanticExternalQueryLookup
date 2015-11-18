<?php

namespace SEQL;

use SMWQuery as Query;

/**
 * @license GNU GPL v2+
 * @since 0.1
 *
 * @author mwjames
 */
class QueryEncoder {

	/**
	 * @since 0.1
	 *
	 * @param Query $query
	 *
	 * @return string
	 */
	public static function rawUrlEncode( Query $query ) {
		return rawurlencode( self::encode( $query ) );
	}

	/**
	 * @since 0.1
	 *
	 * @param Query $query
	 *
	 * @return string
	 */
	public static function encode( Query $query ) {
		$serialized = array();

		$serialized['conditions'] = $query->getQueryString();

		$serialized['parameters'] = array(
			'limit=' . $query->getLimit(),
			'offset=' . $query->getOffset(),
			'mainlabel=' . $query->getMainlabel(),
		//	'source=' . $query->getQuerySource()
		);

		$serialized['sort'] = array();
		$serialized['order'] = array();

		foreach ( $query->getSortKeys() as $key => $value ) {

			if ( $key === '' ) {
				continue;
			}

			$serialized['sort'][] = $key;
			$serialized['order'][] = $value;
		}

		$serialized['printouts'] = array();

		foreach ( $query->getExtraPrintouts() as $printout ) {
			$serialization = $printout->getSerialisation();
			if ( $serialization !== '?#' ) {
				$serialized['printouts'][] = $serialization;
			}
		}

		return $serialized['conditions'] . '|' .
			( $serialized['printouts'] !== array() ? implode( '|', $serialized['printouts'] ) . '|' : '' ) .
			implode( '|', $serialized['parameters'] ) .
			( $serialized['sort'] !==  array() ? '|sort=' . implode( ',', $serialized['sort'] ) : '' ) .
			( $serialized['order'] !== array() ? '|order=' . implode( ',', $serialized['order'] ) : '' );
	}

}
