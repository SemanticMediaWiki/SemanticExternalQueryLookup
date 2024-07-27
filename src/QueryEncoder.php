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
	public static function rawUrlEncode( Query $query ): string {
		return rawurlencode( self::encode( $query ) );
	}

	/**
	 * @since 1.0
	 *
	 * @param Query $query
	 *
	 * @return string
	 */
	public static function encode( Query $query ): string {
		$serialized = [];

		$serialized['conditions'] = $query->getQueryString();

		$serialized['parameters'] = [
			'limit=' . $query->getLimit(),
			'offset=' . $query->getOffset(),
			'mainlabel=' . $query->getMainlabel(),
		//	'source=' . $query->getQuerySource()
		];

		[ $serialized['sort'], $serialized['order'] ] = self::doSerializeSortKeys( $query );
		$serialized['printouts'] = self::doSerializePrintouts( $query );

		return $serialized['conditions'] . '|' .
			( count( $serialized['printouts'] ) > 0 ? implode( '|', $serialized['printouts'] ) . '|' : '' ) .
			implode( '|', $serialized['parameters'] ) .
			( count( $serialized['sort'] ) > 0 ? '|sort=' . implode( ',', $serialized['sort'] ) : '' ) .
			( count( $serialized['order'] ) > 0 ? '|order=' . implode( ',', $serialized['order'] ) : '' );
	}

	private static function doSerializePrintouts( Query $query ): array {

		$printouts = [];

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

	private static function doSerializeSortKeys( Query $query ): array {

		$sort = [];
		$order = [];

		foreach ( $query->getSortKeys() as $key => $value ) {

			if ( $key === '' ) {
				continue;
			}

			$sort[] = $key;
			$order[] = strtolower( $value );
		}

		return [ $sort, $order ];
	}

}
