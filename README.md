# Semantic External Query Lookup

[![Build Status](https://secure.travis-ci.org/SemanticMediaWiki/SemanticExternalQueryLookup.svg?branch=master)](http://travis-ci.org/SemanticMediaWiki/SemanticExternalQueryLookup)
[![Code Coverage](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticExternalQueryLookup/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticExternalQueryLookup/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticExternalQueryLookup/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/SemanticMediaWiki/SemanticExternalQueryLookup/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/mediawiki/semantic-external-query-lookup/version.png)](https://packagist.org/packages/mediawiki/semantic-external-query-lookup)
[![Packagist download count](https://poser.pugx.org/mediawiki/semantic-external-query-lookup/d/total.png)](https://packagist.org/packages/mediawiki/semantic-external-query-lookup)

Semantic External Query Lookup (a.k.a. SEQL) is a [Semantic Mediawiki][smw] extension to seamlessly integrate
query results from an external query source to a local repository or wiki.

The following [video](https://youtu.be/sOCh9M2sSvU) demonstrates the features of this extension.

## Requirements

- PHP 5.3.2 or later
- MediaWiki 1.28 or later
- [Semantic MediaWiki][smw] __3.0__ or later

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
2. Add `wfLoadExtension('SemenaticExternalQueryLookup');` to the bottom of `LocalSettings.php`
3. Navigate to _Special:Version_ on your wiki and verify that the package
   have been successfully installed.

## Usage

![image](https://cloud.githubusercontent.com/assets/1245473/16213390/37da5728-374f-11e6-900c-267279e4a2b7.png)

```
{{#ask:[[Modification date::+]][[~CR*]]
 |?#-
 |?Modification date
 |format=broadtable
 |source=mw-core
 |link=all
 |headers=show
}}
```

The `#ask`/`#show` query only requires to add a `source` parameter (assuming that
a query source has been registered and enabled) to retrieve query results from a
selected external endpoint. `Special:Ask` will provide a selection box to list
enabled query sources.

Information about required settings can be found [here](docs/00-configurations.md).

## Contribution and support

If you want to contribute work to the project please subscribe to the developers mailing list and
have a look at the contribution guideline.

* [File an issue](https://github.com/SemanticMediaWiki/SemanticExternalQueryLookup/issues)
* [Submit a pull request](https://github.com/SemanticMediaWiki/SemanticExternalQueryLookup/pulls)
* Ask a question on [the mailing list](https://www.semantic-mediawiki.org/wiki/Mailing_list)
* Ask a question on the #semantic-mediawiki IRC channel on Libera.

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
