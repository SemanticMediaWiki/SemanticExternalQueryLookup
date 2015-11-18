<?php

namespace SEQL;

use SMW\ApplicationFactory;
use SMW\Store;
use Hooks;

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
	 * @param Store $store
	 * @param array $options
	 */
	public function __construct( Store $store, array $options ) {
		$this->addCallbackHandlers( $store, $options );
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
	 * @since  1.1
	 *
	 * @param string $name
	 *
	 * @return Callable|false
	 */
	public function getHandlerFor( $name ) {
		return isset( $this->handlers[$name] ) ? $this->handlers[$name] : false;
	}

	private function addCallbackHandlers( $store, $options ) {

		$dynamicInterwikiPrefixLoader = new DynamicInterwikiPrefixLoader(
			$options['externalRepositoryEndpoints']
		);

		$this->handlers['InterwikiLoadPrefix'] = function( $prefix, &$interwiki ) use( $dynamicInterwikiPrefixLoader ) {
			return $dynamicInterwikiPrefixLoader->tryToLoadIwMapForExternalRepository( $prefix, $interwiki );
		};
	}

}
