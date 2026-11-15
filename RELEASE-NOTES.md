This file contains the RELEASE-NOTES of the Semantic External Query Lookup (a.k.a. SEQL) extension.

### 2.0.1 (2026-11-20)

- Fixed `source=` queries failing silently: a transport failure or an
  unparseable response from the external repository is now reported as a
  query error (`seql-http-request-failed` / `seql-http-response-invalid`)
  instead of rendering an empty result without any diagnostic
- A response that does not decode as JSON is no longer written to the
  HTTP response cache, so a failed fetch is retried after the error is
  fixed instead of being frozen for the cache lifetime
- Added a "Names must align" section to the configuration documentation
  explaining that the `source=` value, the `$smwgQuerySources` key, and
  the `$seqlgExternalRepositoryEndpoints` prefix must carry the same
  name, and that a mismatch (or a later wholesale re-assignment of
  `$smwgQuerySources`) silently falls back to the local store

### 2.0.0 (2026-06-08)

- Added support for Semantic MediaWiki 7.0
- Raised the minimum requirement to MediaWiki 1.43
- Raised the minimum requirement to Semantic MediaWiki 7.0
- Raised the minimum requirement to PHP 8.1
- Replaced the `mediawiki/http-request` dependency with MediaWiki core's `HttpRequestFactory`
- Removed the `InterwikiLoadPrefix` hook registration (deprecated since MediaWiki 1.36);
  endpoints in `$seqlgExternalRepositoryEndpoints` are now exposed by decorating the
  `InterwikiLookup` service via the `MediaWikiServices` hook

### 1.0.0 (2016-??-??)

- Initial release
- Added `ByHttpRequestQueryLookup` to retrieve query results from an external
  endpoint specified in `$GLOBALS['seqlgExternalRepositoryEndpoints']`
