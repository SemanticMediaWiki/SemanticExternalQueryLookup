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

## Features and limitations

- Images (`File` namespace) are only displayed as normal wiki links (as file information/location are not
  available outside of the original wiki)
- Historic dates are only displayed correctly for when the endpoint Ask API supports version `0.7` or later
