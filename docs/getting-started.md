# Getting Started

- [Getting an API key](#getting-an-api-key)
- [Creating a client](#creating-a-client)
- [API versions](#api-versions)
- [Using your own HTTP client](#using-your-own-http-client)
- [Resources](#resources)
- [Locators](#locators)
- [Immutable builders](#immutable-builders)
- [Response objects](#response-objects)
- [Lists](#lists)
- [Metadata](#metadata)
- [Rate limit](#rate-limit)

<a name="getting-an-api-key"></a>
## Getting an API key

Create an account at [huispedia.dev](https://huispedia.dev) and create an API key (`hp_…`)
in the dashboard. The
[API reference](https://huispedia.dev/en/docs/endpoints/), the
[pricing](https://huispedia.dev/en/docs/credits/) and the
[credit costs per endpoint](https://huispedia.dev/en/docs/catalogue/) are in the
[documentation](https://huispedia.dev/en/docs/quickstart/).

<a name="creating-a-client"></a>
## Creating a client

Pass the API key to `Client::create()`:

```php
use Henzeb\Housepedia\Data\Intelligence\Client;

$client = Client::create($apiKey);
```

The key is sent as a bearer token in the `Authorization` header with every request.

Without an API key, `catalog()` works; every other endpoint throws a
`MissingApiKeyException`.

`Client::create()` also accepts a PSR-18 client and a `Config`:

```php
use Henzeb\Housepedia\Data\Intelligence\Config;

$client = Client::create(
    apiKey: $apiKey,
    httpClient: $psr18Client,
    config: new Config(
        baseUrl: 'https://huispedia.dev',
        language: 'en',
        retries: 2,
        retryServiceUnavailable: false,
    ),
);
```

| Argument     | Description                                                                         |
|--------------|-------------------------------------------------------------------------------------|
| `httpClient` | A PSR-18 client. When omitted, an installed client is discovered.                   |
| `config`     | The settings below. When omitted, every setting has its default.                    |

| `Config` argument         | Description                                                             |
|---------------------------|-------------------------------------------------------------------------|
| `baseUrl`                 | The API host. `null` uses `Transport::BASE_URL` (`https://huispedia.dev`). |
| `language`                | Sent as `Accept-Language`; the API writes the `detail` of errors in this language. |
| `retries`                 | How often a rate-limited request is retried. Defaults to `2`; `0` never retries. See [retrying](errors.md#retrying). |
| `retryServiceUnavailable` | Also retry a `ServiceUnavailableException` (503). Defaults to `false`.  |
| `cache`                   | A PSR-16 cache for answers. Defaults to `null`, no caching. See [caching](caching.md). |
| `cachePrefix`             | The prefix of the cache keys. Defaults to `housepedia-data-intelligence`.    |
| `maxCacheAgeInSeconds`    | The longest time an answer stays in the cache. Defaults to `null`, the time the API allows. |
| `version`                 | The API version: `5`, `'5'`, `'v5'` or `'V5'`. Defaults to version 5. See [API versions](#api-versions). |

<a name="api-versions"></a>
## API versions

The client uses version 5 of the API by default. Set the version in the `Config`, or per client
with `withVersion()`, which returns a copy, to pin it:

```php
$client = Client::create($apiKey, config: new Config(version: 5));

$client->version();                  // 'V5'
$client->withVersion('v5')->addresses()->suggest('1076LJ');
```

`5`, `'5'`, `'v5'` and `'V5'` are the same version. An unknown version throws an
`UnsupportedVersionException` before any request.

The resources and response objects of a version are in its namespace
(`Henzeb\Housepedia\Data\Intelligence\V5`). The client returns them as the interfaces in
`Henzeb\Housepedia\Data\Intelligence\Contracts`; type against those interfaces to keep your
code independent of the version:

```php
use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Valuation;

function value(Valuation $valuation): int
{
    return $valuation->valuation()->value();
}
```

The enums and input objects of a version, such as `V5\Enums\TrendMetric` and
`V5\Input\ScenarioRow`, implement the interfaces in `Contracts\Enums` and `Contracts\Input`.

<a name="using-your-own-http-client"></a>
## Using your own HTTP client

Timeouts, proxies and other connection settings belong to the HTTP client. Configure the
client and pass it in, or build the transport yourself with your own PSR-17 factories:

```php
use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Psr7\HttpFactory;
use Henzeb\Housepedia\Data\Intelligence\Client;
use Henzeb\Housepedia\Data\Intelligence\Transport\Transport;

$factory = new HttpFactory();

$client = new Client(
    new Transport(
        apiKey: $apiKey,
        client: new Guzzle(['timeout' => 10]),
        requestFactory: $factory,
        streamFactory: $factory,
    ),
);
```

<a name="resources"></a>
## Resources

| Method                   | Endpoints                                                    |
|--------------------------|--------------------------------------------------------------|
| `$client->addresses()`   | [Address validation, autocomplete, reverse geocoding](addresses.md) |
| `$client->properties()`  | [Characteristics, WOZ, energy label, valuations and more](properties.md) |
| `$client->buildings()`   | [Buildings and their units](buildings.md)                    |
| `$client->areas()`       | [Areas, statistics, price index and demographics](areas.md)  |
| `$client->valuations()`  | [Scenario valuations](valuations.md)                         |
| `$client->account()`     | [Account, usage and request log](account-and-catalog.md)     |
| `$client->catalog()`     | [Products, prices and plans](account-and-catalog.md)         |
| `$client->get()`/`post()`| [Raw requests to any endpoint](raw-requests.md)              |

<a name="locators"></a>
## Locators

Endpoints about a single home need a locator. Addresses and properties accept a postcode
with house number, or a BAG id:

```php
$client->properties()->withPostcode('1076LJ', 12, '2');
$client->properties()->withBagId('0363200000028422');
```

Postcodes are normalized: case and spaces do not matter. The third argument of
`withPostcode()` is the house number extension.

Buildings take a BAG pand id, and areas take an area code. Each resource only offers the
locator methods its endpoints support. Calling an endpoint before setting a locator throws
a `MissingLocatorException`.

A locator value object, described below, can be passed directly: `withLocator()` on
addresses and properties, `withArea()` on areas:

```php
$client->properties()->withLocator(Locator::bagId('0363200000028422'));
$client->areas()->withArea(AreaLocator::municipality('GM0363'));
```

The locators are also available as value objects, for example when building a path for
a [raw request](raw-requests.md):

```php
use Henzeb\Housepedia\Data\Intelligence\Locators\AreaLocator;
use Henzeb\Housepedia\Data\Intelligence\Locators\Locator;

(string) Locator::postcode('1076LJ', 12, '2');   // 1076LJ-12-2
(string) Locator::bagId('0363200000028422');      // bag:0363200000028422
(string) AreaLocator::municipality('GM0363');     // municipality:GM0363
```

<a name="immutable-builders"></a>
## Immutable builders

Every `with*()` method returns a new instance. The original stays untouched, so a
resource can be reused safely:

```php
$properties = $client->properties();

$first = $properties->withPostcode('1076LJ', 12, '2');
$second = $properties->withPostcode('2511BT', 10);

$first->find();
$second->find();
```

Setting a second locator replaces the first.

## Enums and strings

Options with a fixed set of values take an enum from `Henzeb\Housepedia\Data\Intelligence\V5\Enums`:
`SuggestionType`, `PropertyFieldGroup`, `ReferenceSort`, `TrendMetric` and `UsageGrouping`.
Each of them also accepts the API value as a string, for values the API offers before the enum
has a case:

```php
use Henzeb\Housepedia\Data\Intelligence\V5\Enums\TrendMetric;

$area->trends(TrendMetric::AskingPricePerSquareMeter);
$area->trends('asking_price_per_m2');
```

A string is sent as it is; the API answers a `ValidationException` when it does not know it.

<a name="response-objects"></a>
## Response objects

The [reference](reference/shared.md) lists every method and every field of every response
object, one file per area: [addresses](reference/addresses.md),
[properties](reference/properties.md), [buildings](reference/buildings.md),
[areas](reference/areas.md), [valuations](reference/valuations.md),
[account](reference/account.md), [catalog](reference/catalog.md) and
[shared objects](reference/shared.md).

Responses are hydrated into immutable objects in `Henzeb\Housepedia\Data\Intelligence\V5\DTO`,
which implement the interfaces in `Contracts\DTO`.
Every field is read through a method named after it in camel case:

```php
$property = $client->properties()->withPostcode('1076LJ', 12, '2')->find();

$property->address()->street();
$property->dimensions()?->livingAreaInSquareMeters();
$property->construction()?->constructionYear();
```

Fields the API may leave out are nullable. Dates are `DateTimeImmutable` instances.

<a name="lists"></a>
## Lists

Endpoints that return a list return `Results`, with the list in `data()` and the metadata of
the answer in `meta()`. `Results` can be iterated and counted:

```php
$suggestions = $client->addresses()->suggest('1076LJ 12-2'); // Results<AddressSuggestion>

foreach ($suggestions as $suggestion) {
    echo $suggestion->label();
}

count($suggestions);
$suggestions->data();  // list<AddressSuggestion>
$suggestions->meta();  // ?Meta
```

<a name="metadata"></a>
## Metadata

Every answer carries metadata about the call. Read it with `meta()` on the object a call
returns: a single result such as `Valuation`, or the `Results` of a list.

```php
$valuation = $client->properties()->withPostcode('1076LJ', 12, '2')->valuation();
$meta = $valuation->meta();

$meta?->requestId();             // the id of this call; quote it to Housepedia support
$meta?->asOf();                  // ?DateTimeImmutable, the date the data describes
$meta?->credits()?->charged();   // credits debited for this call
$meta?->credits()?->balance();   // credits left on your account after this call
$meta?->withheld();              // ?list<string>, fields left out because a source does not permit passing them on
$meta?->nextCursor();            // cursor of the next page, on paged lists only
$meta?->rateLimit();             // ?RateLimit, see below
```

A field in `withheld()` is `null` in the response object for that reason, not because the
data is missing.

For a list, the metadata belongs to the whole answer and is on the `Results`. The items in
the list have no metadata of their own, so their `meta()` returns `null`:

```php
$homes = $client->addresses()->withPostcode('1076LJ', 12)->findAll();

$homes->meta()?->credits()?->charged();  // the cost of the call
$homes->data()[0]->meta();               // null
```

`meta()` returns `null` when the answer has no metadata. `hasTransactions()` returns a `bool`
and has no metadata, and objects nested in an answer, such as the `address()` of a
`Valuation` or the `areas()` of a reverse lookup, have none either.

An answer read from the [cache](caching.md) returns the metadata of the call that was
cached: the same `requestId()` and `credits()`, although it was read from the cache without a
request. Its `rateLimit()` is `null`.

Errors have no `meta()`; an exception has the request id and, for a 429, the retry time
itself. See [errors](errors.md).

<a name="rate-limit"></a>
## Rate limit

`meta()?->rateLimit()` returns the rate limit of your account right after the call, from the
`X-RateLimit-*` headers of the answer. The API limits requests per account with a bucket that
holds `limit()` requests and refills over time; the limits per plan are in the
[rate limits](https://huispedia.dev/en/docs/rate-limits/) documentation of Housepedia:

```php
$rateLimit = $valuation->meta()?->rateLimit();

$rateLimit?->limit();           // 20, the size of the bucket
$rateLimit?->remaining();       // requests left in the bucket
$rateLimit?->resetInSeconds();  // seconds until the bucket is full
$rateLimit?->resetsAt();        // DateTimeImmutable
```

It is `null` for an answer read from the [cache](caching.md), which does not count toward the
limit, and when the answer has no rate limit headers. `Response::rateLimit()` returns the same
for a [raw request](raw-requests.md).
