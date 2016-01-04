<?php

namespace SEQL\ByHttpRequest;

use SEQL\DataValueDeserializer;
use SMW\DIProperty;
use SMW\DIWikiPage;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class JsonResponseParser {

	/**
	 * @var DataValueDeserializer
	 */
	private $dataValueDeserializer;

	/**
	 * @var ResponsePropertyList
	 */
	private $responsePropertyList;

	/**
	 * @var array
	 */
	private $subjectList = array();

	/**
	 * @var boolean
	 */
	private $furtherResults = false;

	/**
	 * @var array
	 */
	private $printouts = array();

	/**
	 * @var string
	 */
	private $rawResponseResult = array();

	/**
	 * @since 1.0
	 *
	 * @param DataValueDeserializer $dataValueDeserializer
	 */
	public function __construct( DataValueDeserializer $dataValueDeserializer ) {
		$this->dataValueDeserializer = $dataValueDeserializer;
		$this->responsePropertyList = new ResponsePropertyList( $dataValueDeserializer->getQuerySource() );
	}

	/**
	 * @since 1.0
	 *
	 * @param DIProperty $property
	 *
	 * @return DIProperty
	 */
	public function findPropertyFromInMemoryExternalRepositoryCache( DIProperty $property ) {

		$key = $property->getKey();

		if ( $this->responsePropertyList->hasProperty( $key ) ) {
			return $this->responsePropertyList->getProperty( $key );
		}

		return $property;
	}

	/**
	 * @since 1.0
	 *
	 * @param DIWikiPage[]
	 */
	public function getResultSubjectList() {
		return $this->subjectList;
	}

	/**
	 * @since 1.0
	 *
	 * @param []
	 */
	public function getPrintouts() {
		return $this->printouts;
	}

	/**
	 * @since 1.0
	 *
	 * @param []
	 */
	public function getPrintRequestPropertyList() {
		return $this->responsePropertyList->getPropertyList();
	}

	/**
	 * @since 1.0
	 *
	 * @return boolean
	 */
	public function hasFurtherResults() {
		return $this->furtherResults;
	}

	/**
	 * @since 1.0
	 *
	 * @return array
	 */
	public function getRawResponseResult() {
		return $this->rawResponseResult;
	}

	/**
	 * @since 1.0
	 *
	 * @param DIWikiPage $subject
	 * @param DIProperty $property
	 *
	 * @return array
	 */
	public function getPropertyValuesFor( DIWikiPage $subject, DIProperty $property ) {

		$hash = $subject->getHash();
		$key = $this->responsePropertyList->findPropertyKey( $property->getKey() );

		return isset( $this->printouts[$hash][$key] ) ? $this->printouts[$hash][$key] : array();
	}

	/**
	 * @since 1.0
	 *
	 * @param array $result
	 */
	public function doParse( array $result ) {

		if ( isset( $result['query'] ) ) {
			$this->rawResponseResult = $result['query'] ;
		}

		foreach ( $result as $key => $item ) {

			if ( $key === 'query-continue-offset' ) {
				$this->furtherResults = true;
				continue;
			}

			if ( !isset( $item['printrequests'] ) || !isset( $item['results'] ) ) {
				continue;
			}

			foreach ( $item['printrequests'] as $k => $value ) {
				$this->responsePropertyList->addToPropertyList( $value );
			}

			foreach ( $item['results'] as $k => $value ) {
				$this->addResultsToPrintoutList( $k, $value );
			}
		}
	}

	private function addResultsToPrintoutList( $k, $value ) {

		// Most likely caused by `mainlabel=-` therefore mark it as special and
		// restore row integrity
		if ( !isset( $value['namespace'] ) || !isset( $value['fulltext'] ) ) {
			 $value['namespace'] = 0;
			 $value['fulltext'] = $k;
		}

		$subject = $this->dataValueDeserializer->newDiWikiPage( $value );

		if ( !$subject ) {
			return;
		}

		$hash = $subject->getHash();
		$this->subjectList[] = $subject;

		if ( !isset( $value['printouts'] ) ) {
			return;
		}

		foreach ( $value['printouts'] as $pk => $pvalues ) {
			$this->addPropertyValues( $hash, $pk, $pvalues );
		}
	}

	private function addPropertyValues( $hash, $pk, $pvalues ) {

		$property = DIProperty::newFromUserLabel( $pk );
		$pk = $property->getKey();

		if ( !$this->responsePropertyList->hasProperty( $pk ) ) {
			return;
		}

		$property = $this->responsePropertyList->getProperty( $pk );
		$pk = $property->getKey();

		foreach ( $pvalues as $pvalue ) {

			if ( !isset( $this->printouts[$hash][$pk] ) ) {
				$this->printouts[$hash][$pk] = array();
			}

			// Unique row value display
			$vhash = md5( json_encode( $pvalue ) );

			if ( !isset( $this->printouts[$hash][$pk][$vhash] ) ) {
				$this->printouts[$hash][$pk][$vhash] = $this->dataValueDeserializer->newDataValueFrom( $property, $pvalue );
			}
		}
	}

}
