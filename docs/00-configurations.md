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

#### Names must align

The `source=` value used in a `#ask`/`#show` query, the key in `$GLOBALS['smwgQuerySources']`, and the key in
`$GLOBALS['seqlgExternalRepositoryEndpoints']` (or the interwiki table) must carry **the same name**. In the examples
above that name is `mw-foo`; queries use `{{#ask: ... |source=mw-foo }}`.

The failure modes of a mismatch are silent:

- If `source=X` names no key in `$GLOBALS['smwgQuerySources']`, Semantic MediaWiki resets the source to its default and
  runs the query against the **local** store without raising an error. The rendered output is an empty result whenever
  the local wiki cannot answer the query, which is indistinguishable from a broken endpoint.
- If `X` names a query source but is not resolvable as an interwiki prefix, SEQL renders the
  `seql-interwiki-prefix-is-missing` warning instead of results.

When adding a source with `= array( ... )`, use the array-append operator (`$GLOBALS['smwgQuerySources']['mw-foo'] = ...`)
or merge with existing entries. A wholesale re-assignment in a settings file that is loaded **after** this registration
(for example a farm- or host-level configuration layered on top of `LocalSettings.php`) discards the query source
without any notice.

A quick check for a suspected fallback: `{{#ask: ... |source=X |format=debug }}`. Debug queries are not supported by
external query sources, so a *normal local* debug pane is the signature of the local-store fallback; an error message
about debug queries confirms that the external source was selected.

### Cache

To help limit the amount of request made to an endpoint, SEQL provides:

- `$GLOBALS['seqlgHttpResponseCacheType']` to specify a cache type to filter repeated requests
 of the same signature (== same query to the same API endpoint), using `CACHE_NONE` will disable
the cache entirely and reroute each request to the selected endpoint
- `$GLOBALS['seqlgHttpResponseCacheLifetime']` specifies the duration of how long a response is
  kept before a new "live" request is made to the endpoint (default is set to 5 min)

## Features and limitations

- Images (`File` namespace) are only displayed as normal wiki links (as file information/location are not
  available outside of the original wiki)
- Historic dates are only displayed correctly for when the endpoint Ask API supports version `0.7` or later
