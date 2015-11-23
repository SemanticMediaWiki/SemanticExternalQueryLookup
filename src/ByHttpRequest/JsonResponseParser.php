<?php

namespace SEQL\ByHttpRequest;

use SEQL\DataValueDeserializer;
use SMW\DIProperty;
use SMW\DIWikiPage;

/**
 * @license GNU GPL v2+
 * @since 0.1
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
	private $categoryLabelMap = array();

	/**
	 * @var string
	 */
	private $rawResponseResult = array();

	/**
	 * @since 0.1
	 *
	 * @param DataValueDeserializer $dataValueDeserializer
	 */
	public function __construct( DataValueDeserializer $dataValueDeserializer ) {
		$this->dataValueDeserializer = $dataValueDeserializer;
	}

	/**
	 * @since 0.1
	 *
	 * @param DIProperty $property
	 *
	 * @return DIProperty
	 */
	public function findPropertyFromInMemoryExternalRepositoryCache( DIProperty $property ) {
		return isset( $this->printRequestPropertyList[$property->getKey()] ) ? $this->printRequestPropertyList[$property->getKey()] : $property;
	}

	/**
	 * @since 0.1
	 *
	 * @param DIWikiPage[]
	 */
	public function getResultSubjects() {
		return $this->subjectList;
	}

	/**
	 * @since 0.1
	 *
	 * @return boolean
	 */
	public function hasFurtherResults() {
		return $this->furtherResults;
	}

	/**
	 * @since 0.1
	 *
	 * @return array
	 */
	public function getRawResponseResult() {
		return $this->rawResponseResult;
	}

	/**
	 * @since 0.1
	 *
	 * @param DIWikiPage $subject
	 * @param DIProperty $property
	 *
	 * @return array
	 */
	public function getPropertyValuesFor( DIWikiPage $subject, DIProperty $property ) {

		$key  = $property->getKey();
		$hash = $subject->getHash();

		if ( isset( $this->categoryLabelMap[$property->getKey()] ) ) {
			$key = $this->categoryLabelMap[$property->getKey()];
		}

		return isset( $this->printouts[$hash][$key] ) ? $this->printouts[$hash][$key] : array();
	}

	/**
	 * @since 0.1
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

		$subject = $this->dataValueDeserializer->newDiWikiPage( $value );

		$hash = $subject->getHash();
		$this->subjectList[] = $subject;

		if ( !isset( $value['printouts'] ) ) {
			return;
		}

		foreach ( $value['printouts'] as $pk => $pvalues ) {

			$property = DIProperty::newFromUserLabel( $pk );
			$pk = $property->getKey();

			// Need to match the property to its orignal API label as internally
			// it is only represented as _INST
			if ( isset( $this->categoryLabelMap[$pk] ) ) {
				$pk = $this->categoryLabelMap[$pk];
			}

			if ( !isset( $this->printRequestPropertyList[$pk] ) ) {
				continue;
			}

			$property = $this->printRequestPropertyList[$pk];

			foreach ( $pvalues as $pvalue ) {

				if ( !isset( $this->printouts[$hash][$pk] ) ) {
					$this->printouts[$hash][$pk] = array();
				}

				$this->printouts[$hash][$pk][] = $this->dataValueDeserializer->newDataValueFrom( $property, $pvalue );
			}
		}
	}

	private function addPrintRequestToPropertyList( $value ) {

		if ( $value['label'] === '' ) {
			return;
		}

		if ( $value['mode'] == 0 ) {
			$property = new DIProperty( '_INST' );
			$this->categoryLabelMap[$value['label']] = $property->getKey();
		} else {
			$property = DIProperty::newFromUserLabel( $value['label'] );
			$property->setPropertyTypeId( $value['typeid'] );
		}

		$property->setInterwiki( $this->dataValueDeserializer->getQuerySource() );
		$this->printRequestPropertyList[$property->getKey()] = $property;
	}

}
