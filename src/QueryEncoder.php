<?php

namespace SEQL;

use SMWQuery as Query;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class QueryEncoder {

	/**
	 * @since 1.0
	 *
	 * @param Query $query
	 *
	 * @return string
	 */
	public static function rawUrlEncode( Query $query ) {
		return rawurlencode( self::encode( $query ) );
	}

	/**
	 * @since 1.0
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

		list( $serialized['sort'], $serialized['order'] ) = self::doSerializeSortKeys( $query );
		$serialized['printouts'] = self::doSerializePrintouts( $query );

		$encoded = $serialized['conditions'] . '|' .
			( $serialized['printouts'] !== array() ? implode( '|', $serialized['printouts'] ) . '|' : '' ) .
			implode( '|', $serialized['parameters'] ) .
			( $serialized['sort'] !==  array() ? '|sort=' . implode( ',', $serialized['sort'] ) : '' ) .
			( $serialized['order'] !== array() ? '|order=' . implode( ',', $serialized['order'] ) : '' );

		return $encoded;
	}

	private static function doSerializePrintouts( $query ) {

		$printouts = array();

		foreach ( $query->getExtraPrintouts() as $printout ) {
			$serialization = $printout->getSerialisation();
			if ( $serialization !== '?#' && $serialization !== '' ) {
				// #show adds an extra = at the end which is interpret as
				// requesting an empty result hence it is removed
				$printouts[] = substr( $serialization, -1 ) === '=' ? substr( $serialization, 0, -1 ) : $serialization;
			}
		}

		return $printouts;
	}

	private static function doSerializeSortKeys( $query ) {

		$sort = array();
		$order = array();

		foreach ( $query->getSortKeys() as $key => $value ) {

			if ( $key === '' ) {
				continue;
			}

			$sort[] = $key;
			$order[] = strtolower( $value );
		}

		return array( $sort, $order );
	}

}
