<?php

namespace SEQL;

use Hooks;
use SMW\Store;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class HookRegistry {

	/**
	 * @var array
	 */
	private $handlers = array();

	/**
	 * @since 1.0
	 *
	 * @param array $options
	 */
	public function __construct( array $options ) {
		$this->addCallbackHandlers( $options );
	}

	/**
	 * @since  1.0
	 */
	public function register() {
		foreach ( $this->handlers as $name => $callback ) {
			Hooks::register( $name, $callback );
		}
	}

	/**
	 * @since  1.0
	 *
	 * @param string $name
	 *
	 * @return boolean
	 */
	public function isRegistered( $name ) {
		return Hooks::isRegistered( $name );
	}

	/**
	 * @since  1.0
	 *
	 * @param string $name
	 *
	 * @return Callable|false
	 */
	public function getHandlerFor( $name ) {
		return isset( $this->handlers[$name] ) ? $this->handlers[$name] : false;
	}

	private function addCallbackHandlers( $options ) {

		$dynamicInterwikiPrefixLoader = new DynamicInterwikiPrefixLoader(
			$options['externalRepositoryEndpoints']
		);

		$this->handlers['InterwikiLoadPrefix'] = function( $prefix, &$interwiki ) use( $dynamicInterwikiPrefixLoader ) {
			return $dynamicInterwikiPrefixLoader->tryToLoadIwMapForExternalRepository( $prefix, $interwiki );
		};

		/**
		 * Prevents ask parser function with "source" parameter defined from
		 * being executed outside of allowed namespaces. This supports transclusion too.
		 *
		 * @param \Parser $parser
		 * @param \PPFrame $frame
		 * @param $args
		 * @param $override
		 */
		$this->handlers['smwAskParserFunction'] = $this->handlers['smwShowParserFunction'] = function( $parser, $frame, $args, &$override ) {
			if( $frame ) {
				$params = [];
				foreach ($args as $key => $value) {
					if ( $key == 0 || ( $value !== '' && $value{0} === '?' ) ) {
						continue;
					}
					if ( strpos( $value, '=' ) === false ) {
						continue;
					}
					$pair = explode('=', $value);
					$params[$pair[0]] = $pair[1];
				}

				if( array_key_exists('source', $params) && !in_array( $frame->getTitle()->getNamespace(), $GLOBALS['seqlgExternalQueryEnabledNamespaces'] ) ) {
					$override = 'Warning: source parameter is not allowed in the namespace!';
				}
			}
		};

	}

}
