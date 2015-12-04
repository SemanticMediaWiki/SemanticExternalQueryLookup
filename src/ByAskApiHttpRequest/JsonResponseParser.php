<?php

namespace SEQL\ByAskApiHttpRequest;

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
	 * @var array
	 */
	private $subjectList = array();

	/**
	 * @var array
	 */
	private $printRequestPropertyList = array();

	/**
	 * @var boolean
	 */
	private $furtherResults = false;

	/**
	 * @var array
	 */
	private $printouts = array();

	/**
	 * @var array
	 */
	private $internalLabelToKeyMap = array();

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
	}

	/**
	 * @since 1.0
	 *
	 * @param DIProperty $property
	 *
	 * @return DIProperty
	 */
	public function findPropertyFromInMemoryExternalRepositoryCache( DIProperty $property ) {
		return isset( $this->printRequestPropertyList[$property->getKey()] ) ? $this->printRequestPropertyList[$property->getKey()] : $property;
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
		return $this->printRequestPropertyList;
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

		$key  = $property->getKey();
		$hash = $subject->getHash();

		if ( isset( $this->internalLabelToKeyMap[$property->getKey()] ) ) {
			$key = $this->internalLabelToKeyMap[$property->getKey()];
		}

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
				$this->addPrintRequestToPropertyList( $value );
			}

			foreach ( $item['results'] as $k => $value ) {
				$this->addResultsToPrintoutList( $value );
			}
		}
	}

	private function addResultsToPrintoutList( $value ) {

		// Most likely caused by `mainlabel=-` therefore mark it as special and
		// restore row integrity
		if ( !isset( $value['namespace'] ) || !isset( $value['fulltext'] ) ) {
			 $value['namespace'] = 0;
			 $value['fulltext'] = 'NO_SUBJECT';
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

		// Need to match the property to its possible internal
		// representation (_INST etc.()
		if ( isset( $this->internalLabelToKeyMap[$pk] ) ) {
			$pk = $this->internalLabelToKeyMap[$pk];
		}

		if ( !isset( $this->printRequestPropertyList[$pk] ) ) {
			return;
		}

		$property = $this->printRequestPropertyList[$pk];

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

	private function addPrintRequestToPropertyList( $value ) {

		if ( $value['label'] === '' ) {
			return;
		}

		if ( $value['mode'] == 0 ) {
			$property = new DIProperty( '_INST' );
			$this->internalLabelToKeyMap[$value['label']] = $property->getKey();
		} else {
			$property = DIProperty::newFromUserLabel( $value['label'] );
			$property->setPropertyTypeId( $value['typeid'] );
		}

		$property->setInterwiki( $this->dataValueDeserializer->getQuerySource() );
		$this->printRequestPropertyList[$property->getKey()] = $property;
	}

}
