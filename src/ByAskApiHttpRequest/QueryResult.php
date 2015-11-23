<?php

namespace SEQL\ByAskApiHttpRequest;

use SMWInfolink as Infolink;
use SMWQueryResult as RootQueryResult;

/**
 * @license GNU GPL v2+
 * @since 0.1
 *
 * @author mwjames
 */
class QueryResult extends RootQueryResult {

	/**
	 * @var string
	 */
	private $remoteTargetUrl = '';

	/**
	 * @var JsonResponseParser
	 */
	private $jsonResponseParser;

	/**
	 * @since 0.1
	 *
	 * @param string $remoteTargetUrl
	 */
	public function setRemoteTargetUrl( $remoteTargetUrl ) {
		$this->remoteTargetUrl = $remoteTargetUrl;
	}

	/**
	 * @since 0.1
	 *
	 * @param JsonResponseParser $jsonResponseParser
	 */
	public function setJsonResponseParser( JsonResponseParser $jsonResponseParser ) {
		$this->jsonResponseParser = $jsonResponseParser;
	}

	/**
	 * @since 0.1
	 *
	 * @return array
	 */
	public function toArray() {
		return $this->jsonResponseParser->getRawResponseResult();
	}

	/**
	 * @since 0.1
	 *
	 * @return CannedResultArray[]|false
	 */
	public function getNext() {
		$page = current( $this->mResults );
		next( $this->mResults );

		if ( $page === false ) {
			return false;
		}

		$row = array();

		foreach ( $this->mPrintRequests as $p ) {
			$row[] = new CannedResultArray( $page, $p, $this->jsonResponseParser );
		}

		return $row;
	}

	/**
	 * @since 0.1
	 *
	 * @return SMWInfolink
	 */
	public function getLink() {
		$params = array( trim( $this->getQuery()->getQueryString() ) );

		foreach ( $this->getQuery()->getExtraPrintouts() as $printout ) {
			$serialization = $printout->getSerialisation();

			// TODO: this is a hack to get rid of the mainlabel param in case it was automatically added
			// by SMWQueryProcessor::addThisPrintout. Should be done nicer when this link creation gets redone.
			if ( $serialization !== '?#' ) {
				$params[] = $serialization;
			}
		}

		// Note: the initial : prevents SMW from reparsing :: in the query string.
		return Infolink::newExternalLink( '', $this->remoteTargetUrl . 'Special:Ask', false, $params );
	}

}
