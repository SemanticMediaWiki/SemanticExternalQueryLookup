# Semantic External Query Lookup

[![Build Status](https://img.shields.io/github/actions/workflow/status/SemanticMediaWiki/SemanticExternalQueryLookup/ci.yaml?branch=master)](https://github.com/SemanticMediaWiki/SemanticExternalQueryLookup/actions/workflows/ci.yaml)
[![Code Coverage](https://codecov.io/gh/SemanticMediaWiki/SemanticExternalQueryLookup/branch/master/graph/badge.svg)](https://codecov.io/gh/SemanticMediaWiki/SemanticExternalQueryLookup)
[![Latest Stable Version](https://poser.pugx.org/mediawiki/semantic-external-query-lookup/v/stable)](https://packagist.org/packages/mediawiki/semantic-external-query-lookup)
[![Download count](https://poser.pugx.org/mediawiki/semantic-external-query-lookup/downloads)](https://packagist.org/packages/mediawiki/semantic-external-query-lookup)

Semantic External Query Lookup (a.k.a. SEQL) is a [Semantic MediaWiki][smw] extension to seamlessly integrate
query results from an external query source to a local repository or wiki.

The following [video](https://youtu.be/sOCh9M2sSvU) demonstrates the features of this extension.

## Requirements

- PHP 8.1 or later
- MediaWiki 1.43 or later
- [Semantic MediaWiki][smw] 7.0 or later

## Installation

The recommended way to install Semantic External Query Lookup is by using [Composer][composer] with:

```json
{
	"require": {
		"mediawiki/semantic-external-query-lookup": "~2"
	}
}
```
1. From your MediaWiki installation directory, execute
   `composer require mediawiki/semantic-external-query-lookup:~2`
2. Add `wfLoadExtension( 'SemanticExternalQueryLookup' );` to the bottom of `LocalSettings.php`
3. Navigate to _Special:Version_ on your wiki and verify that the package
   has been successfully installed.

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

This extension provides unit and integration tests that are run by a [continuous integration platform][github-actions]
but can also be executed using `composer phpunit` from the extension base directory.

## License

[GNU General Public License, version 2 or later][gpl-licence].

[smw]: https://github.com/SemanticMediaWiki/SemanticMediaWiki
[github-actions]: https://github.com/SemanticMediaWiki/SemanticExternalQueryLookup/actions/workflows/ci.yaml
[gpl-licence]: https://www.gnu.org/copyleft/gpl.html
[composer]: https://getcomposer.org/
[iwp]: https://www.mediawiki.org/wiki/Manual:Interwiki
