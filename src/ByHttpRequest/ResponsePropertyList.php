<?php

namespace SEQL\ByHttpRequest;

use SMW\DIProperty;
use SMW\DIWikiPage;
use RuntimeException;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class ResponsePropertyList {

	/**
	 * @var string
	 */
	private $querySource = '';

	/**
	 * @var array
	 */
	private $propertyList = array();

	/**
	 * @var array
	 */
	private $internalLabelToKeyMap = array();

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
	 * @return DIProperty[]
	 */
	public function getPropertyList() {
		return $this->propertyList;
	}

	/**
	 * @since 1.0
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	public function findPropertyKey( $key ) {

		if ( isset( $this->internalLabelToKeyMap[$key] ) ) {
			return $this->internalLabelToKeyMap[$key];
		}

		return $key;
	}

	/**
	 * @since 1.0
	 *
	 * @param string $key
	 *
	 * @return boolean
	 */
	public function hasProperty( $key ) {
		return isset( $this->propertyList[$this->findPropertyKey( $key )] );
	}

	/**
	 * @since 1.0
	 *
	 * @param string $key
	 *
	 * @return DIProperty|null
	 */
	public function getProperty( $key ) {

		$key = $this->findPropertyKey( $key );

		if ( isset( $this->propertyList[$key] ) ) {
			return $this->propertyList[$key];
		}

		return null;
	}

	/**
	 * @since 1.0
	 *
	 * @param array $value
	 */
	public function addToPropertyList( array $value ) {

		if ( $value['label'] === '' ) {
			return;
		}

		if ( $value['mode'] == 0 ) {
			$property = new DIProperty( '_INST' );
			$this->internalLabelToKeyMap[$value['label']] = $property->getKey();
		} else {
			$property = $this->newProperty( $value );
		}

		if ( isset( $value['redi'] ) ) {
			$this->internalLabelToKeyMap[$value['redi']] = $property->getKey();
		}

		$property->setInterwiki( $this->querySource );
		$this->propertyList[$property->getKey()] = $property;
	}

	private function newProperty( $value ) {

		$property = DIProperty::newFromUserLabel( $value['label'] );

		if ( $property->isUserDefined() ) {
			return $property->setPropertyTypeId( $value['typeid'] );
		}

		if ( $property->findPropertyTypeID() === $value['typeid'] ) {
			return $property;
		}

		// Something like |Has foo=Text where `Text` is mapped to a DataType
		// cannot be redeclared when the type of `Has foo` (as `_wpg`) doesn't
		// correspond to the predefined property type
		throw new RuntimeException( 'Cannot redeclare type "' .  $value['typeid'] . '" for "' . $value['label'] . '" (as predefined property/type)' );
	}

}
