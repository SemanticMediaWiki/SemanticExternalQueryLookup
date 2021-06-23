<?php

namespace SEQL\ByHttpRequest;

use SMW\DataValueFactory;
use SMW\DIProperty;
use SMW\DIWikiPage;
use SMW\Query\PrintRequest;
use SMWDataItem;
use SMWDataValue as DataValue;
use SMWResultArray as ResultArray;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class CannedResultArray extends ResultArray {

	/**
	 * @var PrintRequest
	 */
	private $mPrintRequest;

	/**
	 * @var DIWikiPage
	 */
	private $mResult;

	/**
	 * @var JsonResponseParser
	 */
	private $jsonResponseParser;

	/**
	 * @var DataValue[]|false
	 */
	private $mContent;

	/**
	 * @since 1.0
	 *
	 * @param DIWikiPage $resultPage
	 * @param PrintRequest $printRequest
	 * @param JsonResponseParser $jsonResponseParser
	 */
	public function __construct( DIWikiPage $resultPage, PrintRequest $printRequest, JsonResponseParser $jsonResponseParser ) {
		$this->mResult = $resultPage;
		$this->mPrintRequest = $printRequest;
		$this->jsonResponseParser = $jsonResponseParser;
		$this->mContent = false;
	}

	/**
	 * @see ResultArray::getResultSubject
	 *
	 * @return DIWikiPage
	 */
	public function getResultSubject() {
		return $this->mResult;
	}

	/**
	 * @see ResultArray::getContent
	 *
	 * @return SMWDataItem[]|false
	 */
	public function getContent() {
		$this->loadContent();

		if ( !$this->mContent ) {
			return $this->mContent;
		}

		$content = array();

		foreach ( $this->mContent as $value ) {
			$content[] = $value instanceof DataValue ? $value->getDataItem() : $value;
		}

		return $content;
	}

	/**
	 * @see ResultArray::getPrintRequest
	 *
	 * @return PrintRequest
	 */
	public function getPrintRequest() {
		return $this->mPrintRequest;
	}

	/**
	 * @see ResultArray::getNextDataItem
	 *
	 * @since 1.6
	 *
	 * @return SMWDataItem|false
	 */
	public function getNextDataItem() {

		$this->loadContent();
		$result = current( $this->mContent );
		next( $this->mContent );

		return $result instanceof DataValue ? $result->getDataItem() : $result;
	}

	/**
	 * @see ResultArray::reset
	 *
	 * @since 1.7.1
	 *
	 * @return SMWDataItem|false
	 */
	public function reset() {

		$this->loadContent();
		$result = reset( $this->mContent );

		return $result instanceof DataValue ? $result->getDataItem() : $result;
	}

	/**
	 * @see ResultArray::getNextDataValue
	 *
	 * @since 1.6
	 *
	 * @return DataValue|false
	 */
	public function getNextDataValue() {

		$this->loadContent();
		$content = current( $this->mContent );
		next( $this->mContent );

		if ( $content === false ) {
			return false;
		}

		$property = $this->getMatchablePropertyFromPrintRequest();

		// Units of measurement can not be assumed to be declared on a wiki
		// therefore don't try to recreate a DataValue and use the DV created
		// from the raw API response
		if ( $this->mPrintRequest->getMode() === PrintRequest::PRINT_PROP &&
		    $property->findPropertyTypeId() === '_qty' ) {
			return $content;
		}

		if ( $this->mPrintRequest->getMode() === PrintRequest::PRINT_PROP &&
		    strpos( $property->findPropertyTypeId(), '_rec' ) !== false ) {

			if ( $this->mPrintRequest->getParameter( 'index' ) === false ) {
				return $content;
			}

			$pos = $this->mPrintRequest->getParameter( 'index' ) - 1;
			$dataItems = $content->getDataItems();

			if ( !array_key_exists( $pos, $dataItems ) ) {
				return $content;
			}

			$diProperties = $content->getPropertyDataItems();
			$content = $dataItems[$pos];

			if ( array_key_exists( $pos, $diProperties ) &&
				!is_null( $diProperties[$pos] ) ) {
				$diProperty = $diProperties[$pos];
			} else {
				$diProperty = null;
			}

		} elseif ( $this->mPrintRequest->getMode() == PrintRequest::PRINT_PROP ) {
			$diProperty = $property;
		} else {
			$diProperty = null;
		}

		if ( $content instanceof DataValue ) {
			$content = $content->getDataItem();
		}

		$dataValue = DataValueFactory::getInstance()->newDataItemValue(
			$content,
			$diProperty
		);

		$dataValue->setContextPage(
			$this->mResult
		);

		if ( $this->mPrintRequest->getOutputFormat() ) {
			$dataValue->setOutputFormat( $this->mPrintRequest->getOutputFormat() );
		}

		return $dataValue;
	}

	/**
	 * Load results of the given print request and result subject. This is only
	 * done when needed.
	 */
	protected function loadContent() {
		if ( $this->mContent !== false ) {
			return;
		}

		$this->mContent = array();

		switch ( $this->mPrintRequest->getMode() ) {
			case PrintRequest::PRINT_THIS:
				$this->mContent = array( $this->mResult );
			break;
			case PrintRequest::PRINT_CCAT:
			case PrintRequest::PRINT_CATS:

				$this->mContent = $this->jsonResponseParser->getPropertyValuesFor(
					$this->mResult,
					new DIProperty( '_INST' )
				);

			break;
			case PrintRequest::PRINT_PROP:
				$propertyValue = $this->mPrintRequest->getData();

				if ( $propertyValue->isValid() ) {
					$this->mContent = $this->jsonResponseParser->getPropertyValuesFor(
						$this->mResult,
						$this->getMatchablePropertyFromPrintRequest()
					);
				}

			break;
			default:
				$this->mContent = array(); // Unknown print request.
		}

		reset( $this->mContent );
	}

	private function getMatchablePropertyFromPrintRequest() {

		if ( $this->mPrintRequest->getMode() !== PrintRequest::PRINT_PROP ) {
			return null;
		}

		$property = $this->mPrintRequest->getData()->getDataItem();

		// The API may not deploy the natural property key (until 0.8+) hence something
		// like |?Has population=Population (in K) does not return a valid result from
		// the parser because "Has population" cannot be connected to "Population (in K)"
		// without the extra "key" field therefore construct a new property to match the
		// label
		if ( $this->mPrintRequest->getLabel() !== '' && $this->mPrintRequest->getLabel() !== $property->getLabel() ) {
			return $this->jsonResponseParser->findPropertyFromInMemoryExternalRepositoryCache(
				DIProperty::newFromUserLabel( $this->mPrintRequest->getLabel() )
			);
		}

		return $this->jsonResponseParser->findPropertyFromInMemoryExternalRepositoryCache(
			$property
		);
	}

}
