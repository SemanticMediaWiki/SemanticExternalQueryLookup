<?php

namespace SEQL;

use SMW\DataValueFactory;
use SMW\DIProperty;
use SMW\DIWikiPage;
use SMWContainerSemanticData as ContainerSemanticData;
use SMWDIContainer as DIContainer;
use SMWDITime as DITime;
use SMWDIBlob as DIBlob;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class DataValueDeserializer {

	/**
	 * @var string
	 */
	private $querySource;

	/**
	 * @var EmbeddedLinksReplacer
	 */
	private $embeddedLinksReplacer;

	/**
	 * @since 1.0
	 *
	 * @param string $querySource
	 */
	public function __construct( $querySource ) {
		$this->querySource = $querySource;
		$this->embeddedLinksReplacer = new EmbeddedLinksReplacer( $querySource );
	}

	/**
	 * @since 1.0
	 *
	 * @return string
	 */
	public function getQuerySource() {
		return $this->querySource;
	}

	/**
	 * @since 1.0
	 *
	 * @param DIProperty $property
	 * @param array|string $value
	 *
	 * @return DataValue
	 */
	public function newDataValueFrom( DIProperty $property, $value ) {

		$dv = null;
		$propertyList = array();

		if ( $property->findPropertyTypeId() === '_wpg' || isset( $value['fulltext'] ) ) {
			$dv = $this->newDataValueFromDataItem( $property, $this->newDiWikiPage( $value ) );
		} elseif ( strpos( $property->findPropertyTypeId(), '_rec' ) !== false ) {
			$dv = $this->newDataValueFromDataItem( $property, $this->newDiContainerOnRecordType( $value, $propertyList ) );
			$dv->setFieldProperties( $propertyList );
		} elseif ( $property->findPropertyTypeId() === '_dat' ) {
			$dv = $this->newDataValueFromDataItem( $property, $this->newDiTime( $value ) );
		} elseif ( strpos( $property->findPropertyTypeId(), '_txt' ) !== false ) {
			$dv = $this->newDataValueFromDataItem( $property, $this->newDiBlob( $value ) );
		} elseif ( $property->findPropertyTypeId() === '_qty' ) {
			$dv = $this->newDataValueFromPropertyObject( $property, $value['value'] . ' ' . $value['unit']  );
		}

		if ( $dv === null ) {
			$dv = $this->newDataValueFromPropertyObject( $property, $value );
		}

		return $dv;
	}

	/**
	 * @since 1.0
	 *
	 * @param array $value
	 *
	 * @return DIWikiPage|false
	 */
	public function newDiWikiPage( array $value ) {

		if ( !isset( $value['namespace'] ) || !isset( $value['fulltext'] ) ) {
			return false;
		}

		$ns = (int)$value['namespace'] === NS_CATEGORY ? NS_CATEGORY : NS_MAIN;

		if ( $ns === NS_CATEGORY ) {
			$value['fulltext'] = substr( $value['fulltext'], ($pos = strpos( $value['fulltext'], ':') ) !== false ? $pos + 1 : 0 );
		}

		$title = \Title::newFromText( $this->querySource . ':' . str_replace(" ", "_", $value['fulltext'] ), $ns );

		return DIWikiPage::newFromTitle( $title );
	}

	private function newDiTime( $value ) {

		if ( isset( $value['raw'] ) ) {
			return DITime::doUnserialize( $value['raw'] );
		}

		// < 0.7 API format
		// Avoid something like "Part of the date is out of bounds" where the API
		// doesn't sent a raw format
		// return 9999 BC to indicate that we hit a bounds with the timespamp
		try{
			$dataItem = DITime::newFromTimestamp( $value );
		} catch ( \Exception $e ) {
			$dataItem = DITime::doUnserialize( '2/-9999' );
		}

		return $dataItem;
	}

	private function newDiBlob( $value ) {
		return new DIBlob( $this->embeddedLinksReplacer->replace( $value ) );
	}

	private function newDataValueFromPropertyObject( $property, $value ) {

		try{
			$dv = DataValueFactory::newPropertyObjectValue( $property, $value );
		} catch ( \Exception $e ) {
			$dv = false;
		}

		return $dv;
	}

	private function newDataValueFromDataItem( $property, $dataItem = false ) {

		if ( $dataItem === false ) {
			return false;
		}

		try{
			$dv = DataValueFactory::newDataItemValue( $dataItem, $property );
		} catch ( \Exception $e ) {
			$dv = false;
		}

		return $dv;
	}

	private function newDiContainerOnRecordType( array $value, &$propertyList ) {

		// Remote container to use an anonymous
		$semanticData = ContainerSemanticData::makeAnonymousContainer();

		foreach ( $value as $recValue ) {
			$recordProperty = DIProperty::newFromUserLabel( $recValue['label'] );
			$recordProperty->setInterwiki( $this->querySource );
			$recordProperty->setPropertyTypeId( $recValue['typeid'] );
			$propertyList[] = $recordProperty;

			foreach ( $recValue['item'] as $item ) {
				$dataValue = $this->newDataValueFrom( $recordProperty, $item );

				if ( $dataValue === false ) {
					continue;
				}

				$semanticData->addPropertyObjectValue( $recordProperty, $dataValue->getDataItem() );
			}
		}

		return new DIContainer( $semanticData );
	}

}
