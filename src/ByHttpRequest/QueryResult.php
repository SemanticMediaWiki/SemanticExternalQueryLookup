<?php

namespace SEQL\ByHttpRequest;

use SMW\Formatters\Infolink;
use SMW\Query\QueryResult as RootQueryResult;

/**
 * @license GPL-2.0-or-later
 * @since 1.0
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
	 * @since 1.0
	 *
	 * @param string $remoteTargetUrl
	 */
	public function setRemoteTargetUrl( $remoteTargetUrl ) {
		$this->remoteTargetUrl = $remoteTargetUrl;
	}

	/**
	 * @since 1.0
	 *
	 * @param JsonResponseParser $jsonResponseParser
	 */
	public function setJsonResponseParser( JsonResponseParser $jsonResponseParser ) {
		$this->jsonResponseParser = $jsonResponseParser;
	}

	/**
	 * @see QueryResult::toArray
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	public function toArray(): array {
		return $this->jsonResponseParser->getRawResponseResult();
	}

	/**
	 * @see QueryResult::serializeToArray
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	public function serializeToArray(): array {
		return $this->toArray();
	}

	/**
	 * @since 1.0
	 *
	 * @return CannedResultArray[]|false
	 */
	public function getNext(): false|array {
		$page = current( $this->mResults );
		next( $this->mResults );

		if ( $page === false ) {
			return false;
		}

		$row = [];

		foreach ( $this->mPrintRequests as $p ) {
			$row[] = new CannedResultArray( $page, $p, $this->jsonResponseParser );
		}

		return $row;
	}

	/**
	 * @see QueryResult::getQueryLink
	 *
	 * @since 1.0
	 *
	 * @param string|false $caption
	 *
	 * @return Infolink
	 */
	public function getQueryLink( $caption = false ): Infolink {
		$params = [ trim( $this->getQuery()->getQueryString() ?? '' ) ];

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
