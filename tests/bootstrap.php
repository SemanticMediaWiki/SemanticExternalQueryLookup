<?php

if ( PHP_SAPI !== 'cli' ) {
	die( 'Not an entry point' );
}

error_reporting( E_ALL | E_STRICT );
date_default_timezone_set( 'UTC' );
ini_set( 'display_errors', 1 );

if ( !is_readable( $autoloaderClassPath = __DIR__ . '/../../SemanticMediaWiki/tests/autoloader.php' ) ) {
	die( 'The SemanticMediaWiki test autoloader is not available' );
}

if ( !class_exists( 'SemanticExternalQueryLookup' ) || ( $version = SemanticExternalQueryLookup::getVersion() ) === null ) {
	die( "\nSemantic External Query Lookup is not available, please check your Composer or LocalSettings.\n" );
}

print sprintf( "\n%-20s%s\n", "Semantic External Query Lookup: ", SEQL_VERSION );

$autoLoader = require $autoloaderClassPath;
$autoLoader->addPsr4( 'SEQL\\Tests\\', __DIR__ . '/phpunit/Unit' );
$autoLoader->addPsr4( 'SEQL\\Tests\\Integration\\', __DIR__ . '/phpunit/Integration' );
unset( $autoLoader );
