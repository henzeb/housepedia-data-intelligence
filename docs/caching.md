# Caching

The API says per answer how long it may be reused, in its `Cache-Control` header: valuations
not at all, characteristics an hour, area statistics a day (see
[rate limits](https://huispedia.dev/en/docs/rate-limits/)). With a PSR-16 cache in the
`Config`, the SDK keeps answers for that time. A cached answer is read without a request to
the API, so it does not count toward the rate limit.

```php
use Henzeb\Housepedia\Data\Intelligence\Client;
use Henzeb\Housepedia\Data\Intelligence\Config;

$client = Client::create($apiKey, config: new Config(
    cache: $psr16Cache,
    cachePrefix: 'housepedia-data-intelligence',
    maxCacheAgeInSeconds: 600,
));

$home = $client->properties()->withPostcode('1076LJ', 12, '2');

$home->find();   // sent to the API
$home->find();   // read from the cache
```

## What is cached

- `GET` and `HEAD` requests with a 2xx answer whose `Cache-Control` has a `max-age` above 0.
- Not: answers with `no-store` or `no-cache`, answers without `Cache-Control`, errors and
  `POST` requests such as scenario valuations.

The answer is kept for its `max-age`, or for `maxCacheAgeInSeconds` when that is shorter. An
answer is never kept longer than the API allows.

A cached answer is returned as it was received, headers included: `X-Credits-Charged` and the
`requestId()` and `credits()` of its `meta()` show the call that was cached and what it cost.
The rate limit headers are not kept, so `meta()?->rateLimit()` is `null` for a cached answer.
See [metadata](getting-started.md#metadata).

## Per call

Every resource and the client have `withMaxCacheAge()` and `withoutCache()`. Both return a
copy; the resource or client they are called on is unchanged.

```php
$home->withMaxCacheAge(60)->find();   // kept for at most 60 seconds
$home->withoutCache()->find();        // always sent to the API, not written to the cache

$client->withoutCache()->get('/v5/account');
$client->withMaxCacheAge(60)->catalog();
```

`withMaxCacheAge()` replaces `maxCacheAgeInSeconds` for those calls, also with a higher
value; the `max-age` of the answer still applies. Without a cache in the `Config`, both have
no effect.

## Cache keys

A key is the prefix, a dot and a SHA-256 hash of the method, the URL with its query, the
language and the API key: `housepedia-data-intelligence.3f1c…`. Answers for different API keys or
languages are kept apart. PSR-16 does not allow the characters `{}()/\@:` in keys, so keep
them out of the prefix.
