<?php

if ( PHP_SAPI !== 'cli' ) {
	die( 'Not an entry point' );
}

if ( !is_readable( $autoloaderClassPath = __DIR__ . '/../../SemanticMediaWiki/tests/autoloader.php' ) ) {
	die( 'The SemanticMediaWiki test autoloader is not available' );
}

print sprintf( "\n%-20s%s\n", "Semantic External Query Lookup: ", SEQL_VERSION );

$autoloader = require $autoloaderClassPath;
$autoloader->addPsr4( 'SEQL\\Tests\\', __DIR__ . '/phpunit/Unit' );
$autoloader->addPsr4( 'SEQL\\Tests\\Integration\\', __DIR__ . '/phpunit/Integration' );
