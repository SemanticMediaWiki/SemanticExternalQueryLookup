<?php

namespace SEQL\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Guards the `extension.json` hook registration shape accepted by
 * MediaWiki's `ExtensionProcessor::setArrayHookHandler()`: a hook value
 * must be a string naming a top-level `HookHandlers` entry (or a legacy
 * callable), never an inline `handler` object.
 *
 * An inline `"handler": { "class": ... }` value makes MW >= 1.41 (and
 * current 1.43) fail during extension registration with
 * `TypeError: Cannot access offset of type array on array` in
 * `ExtensionProcessor.php` before any SEQL code runs.
 *
 * @covers \SEQL\MediaWikiServicesHandler
 * @group semantic-external-query-lookup
 *
 * @license GPL-2.0-or-later
 * @since 2.0.1
 */
class ExtensionJsonHookRegistrationTest extends TestCase {

	private const EXTENSION_JSON_PATH = __DIR__ . '/../../../extension.json';

	public function testExtensionJsonIsValid() {
		$extensionJson = json_decode( file_get_contents( self::EXTENSION_JSON_PATH ), true );

		$this->assertNotNull(
			$extensionJson,
			'extension.json contains invalid JSON'
		);
	}

	public function testMediaWikiServicesHookReferencesHookHandlersEntry() {
		$extensionJson = json_decode( file_get_contents( self::EXTENSION_JSON_PATH ), true );

		$this->assertArrayHasKey(
			'Hooks',
			$extensionJson,
			'extension.json must register the MediaWikiServices hook'
		);

		$hook = $extensionJson['Hooks']['MediaWikiServices'] ?? null;

		$this->assertIsString(
			$hook,
			'`Hooks.MediaWikiServices` must be a string naming a `HookHandlers` entry; '
			. 'an inline `handler` object crashes MW\'s ExtensionProcessor'
		);

		$this->assertArrayHasKey(
			'HookHandlers',
			$extensionJson
		);

		$handler = $extensionJson['HookHandlers'][$hook] ?? null;

		$this->assertIsArray(
			$handler,
			"`Hooks.MediaWikiServices` references missing `HookHandlers.$hook` entry"
		);

		$this->assertArrayHasKey(
			'class',
			$handler
		);

		$this->assertTrue(
			is_a( $handler['class'], 'MediaWiki\Hook\MediaWikiServicesHook', true ),
			$handler['class'] . ' must implement MediaWikiServicesHook'
		);
	}

}
