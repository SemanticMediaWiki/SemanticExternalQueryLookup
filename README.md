# Semantic External Query Lookup

[![Build Status](https://secure.travis-ci.org/SemanticMediaWiki/SemanticExternalQueryLookup.svg?branch=master)](http://travis-ci.org/SemanticMediaWiki/SemanticExternalQueryLookup)
[![Code Coverage](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticExternalQueryLookup/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticExternalQueryLookup/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticExternalQueryLookup/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticExternalQueryLookup/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/mediawiki/semantic-external-query-lookup/version.png)](https://packagist.org/packages/mediawiki/semantic-external-query-lookup)
[![Packagist download count](https://poser.pugx.org/mediawiki/semantic-external-query-lookup/d/total.png)](https://packagist.org/packages/mediawiki/semantic-external-query-lookup)
[![Dependency Status](https://www.versioneye.com/php/mediawiki:semantic-external-query-lookup/badge.png)](https://www.versioneye.com/php/mediawiki:semantic-external-query-lookup)

Semantic External Query Lookup (a.k.a. SEQL) is a [Semantic Mediawiki][smw] extension to seamlessly integrate
query results from an external query source to a local repository or wiki.

The following [video](https://youtu.be/sOCh9M2sSvU) demonstrates the features of this extension.

## Requirements

- PHP 5.3.2 or later
- MediaWiki 1.23 or later
- [Semantic MediaWiki][smw] __2.4__ or later

## Installation

The recommended way to install Semantic External Query Lookup is by using [Composer][composer] with:

```json
{
	"require": {
		"mediawiki/semantic-external-query-lookup": "~1.0"
	}
}
```
1. From your MediaWiki installation directory, execute
   `composer require mediawiki/semantic-external-query-lookup:~1.0`
2. Navigate to _Special:Version_ on your wiki and verify that the package
   have been successfully installed.

## Usage

The `#ask`/`#show` query only requires to add a `source` parameter (assuming that a query source has
been registered and enabled) to retrieve query results from a selected external endpoint. `Special:Ask` will provide a selection box to list enabled query sources.

```
{{#ask: [[Modification date::+]]
 |?Modification date
 |limit=5
 |source=mw-foo
}}
```

### Features and limitations

- Images (`File` namespace) are only displayed as normal wiki links (as file information/location are not
  available outside of the original wiki)
- Historic dates are only displayed correctly for when the endpoint Ask API supports version `0.7` or later

## Configuration

### Query sources

For a `#ask` query to retrieve results from a remote location, an external query source is required to be registered
with a unique key and assigned a lookup processor as in:

```
$GLOBALS['smwgQuerySources'] = array(
    'mw-foo' => 'SMWExternalAskQueryLookup',
);
```
The key used to identify an endpoint is expected to correspond to an [interwiki prefix][iwp]. Details of that prefix can be
either inserted directly into MediaWiki's interwiki table or if it is more convenient the setting
`$GLOBALS['seqlgExternalRepositoryEndpoints']` can be used in form of:

```
$GLOBALS['seqlgExternalRepositoryEndpoints'] = array(
    'mw-foo' => array(
        'http://example.org:8080/mw-foo/index.php/$1', // corresponds to iw_url
        'http://example.org:8080/mw-foo/api.php',      // corresponds to iw_api
        true                                           // corresponds to iw_local
    )
);
````
### Cache

To help limit the amount of request made to an endpoint, SEQL provides:

- `$GLOBALS['seqlgHttpResponseCacheType']` to specify a cache type to filter repeated requests
 of the same signature (== same query to the same API endpoint), using `CACHE_NONE` will disable
the cache entirely and reroute each request to the selected endpoint
- `$GLOBALS['seqlgHttpResponseCacheLifetime']` specifies the duration of how long a response is
  kept before a new "live" request is made to the endpoint (default is set to 5 min)

## Contribution and support

If you want to contribute work to the project please subscribe to the developers mailing list and
have a look at the contribution guideline.

* [File an issue](https://github.com/SemanticMediaWiki/SemanticExternalQueryLookup/issues)
* [Submit a pull request](https://github.com/SemanticMediaWiki/SemanticExternalQueryLookup/pulls)
* Ask a question on [the mailing list](https://semantic-mediawiki.org/wiki/Mailing_list)
* Ask a question on the #semantic-mediawiki IRC channel on Freenode.

## Tests

This extension provides unit and integration tests that are run by a [continues integration platform][travis]
but can also be executed using `composer phpunit` from the extension base directory.

## License

[GNU General Public License, version 2 or later][gpl-licence].

[smw]: https://github.com/SemanticMediaWiki/SemanticMediaWiki
[contributors]: https://github.com/SemanticMediaWiki/SemanticExternalQueryLookup/graphs/contributors
[travis]: https://travis-ci.org/SemanticMediaWiki/SemanticExternalQueryLookup
[gpl-licence]: https://www.gnu.org/copyleft/gpl.html
[composer]: https://getcomposer.org/
[iwp]: https://www.mediawiki.org/wiki/Manual:Interwiki
