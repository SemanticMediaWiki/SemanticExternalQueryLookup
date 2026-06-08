This file contains the RELEASE-NOTES of the Semantic External Query Lookup (a.k.a. SEQL) extension.

### 2.0.0 (2026-06-08)

- Added support for Semantic MediaWiki 7.0
- Raised the minimum requirements to MediaWiki 1.43 and Semantic MediaWiki 7.0
- Replaced the `mediawiki/http-request` dependency with MediaWiki core's `HttpRequestFactory`

### 1.0.0 (2016-??-??)

- Initial release
- Added `ByHttpRequestQueryLookup` to retrieve query results from an external
  endpoint specified in `$GLOBALS['seqlgExternalRepositoryEndpoints']`