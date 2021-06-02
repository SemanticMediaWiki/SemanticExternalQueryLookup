<?php

/**
 * DO NOT EDIT!
 *
 * The following default settings are to be used by the extension itself,
 * please modify settings in the LocalSettings file.
 *
 * @codeCoverageIgnore
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This file is part of the SemanticExternalQueryLookup extension, it is not a valid entry point.' );
}

// Alias to keep LocalSettings independent from the internal NS usage
class_alias( 'SEQL\ByHttpRequestQueryLookup', 'SMWExternalQueryLookup' ); // deprecated
class_alias( 'SEQL\ByHttpRequestQueryLookup', 'SMWExternalAskQueryLookup' );

/**
 * Specifies how long a response is cached before a new request is routed
 * to the endpoint. This avoids that repeated requests with the same signature
 * are made to an endpoint.
 *
 * This is important if the endpoint has an API access request limitation.
 */
$GLOBALS['seqlgHttpResponseCacheLifetime'] = 60 * 5; // in seconds

/**
 * Type of the cache to be used, using CACHE_NONE will disable the caching
 * and reroutes every request to the endpoint.
 *
 * @see https://www.mediawiki.org/wiki/Manual:$wgMainCacheType
 */
$GLOBALS['seqlgHttpResponseCacheType'] = CACHE_ANYTHING;

/**
 *  An array that identifies valid endpoints with a key expecting yo corresponds
 *  to an interwiki prefix. Details of that prefix can be either inserted directly
 *  into MediaWiki's interwiki table or through this setting.
 */
$GLOBALS['seqlgExternalRepositoryEndpoints'] = array();

/**
 *  An array that defines credentials to access remote wikis in case they're read-protected
 *  Array keys should be named after interwiki prefixes from "seqlgExternalRepositoryEndpoints"
 *  and contain an array with "username" and "password" keys
 */
$GLOBALS['seqlgExternalRepositoryCredentials'] = array();

/**
 *  An array defines list of namespaces allowed to execute queries against remote sources.
 *  Keep empty to allow every namespace.
 */
$GLOBALS['seqlgExternalQueryEnabledNamespaces'] = array();
