# Using this SDK with an AI agent

A compact reference for coding agents that write code with `henzeb/housepedia-data-intelligence`.
The other pages in `docs/` describe each part in detail.

## Client

```php
use Henzeb\Housepedia\Data\Intelligence\Client;
use Henzeb\Housepedia\Data\Intelligence\Config;

$client = Client::create($apiKey);                                        // API key from https://huispedia.dev
$client = Client::create($apiKey, config: new Config(language: 'nl'));    // error details in Dutch
```

Resources: `addresses()`, `properties()`, `buildings()`, `areas()`, `valuations()`,
`account()`, `catalog()`. Raw requests: `get()`, `post()`.

## Rules

- The API version comes from `Config(version: 5)` or `$client->withVersion('v5')` (`5`, `'5'`,
  `'v5'`, `'V5'`), else `Config::FALLBACK_VERSION`. Classes of a version are in its namespace (`V5\`);
  type against the interfaces in `Contracts\` and `Contracts\DTO\`, which the client returns.
- Resources are immutable builders. A `with*()` call returns a new instance; assign it.
- Set a locator before calling an endpoint that needs one, or a `MissingLocatorException`
  is thrown.
- Coordinates take a float or a decimal string (`'52.34536032'`); strings are sent exactly as
  given.
- Use the typed methods. `get()` and `post()` are the low-level request methods for
  endpoints without a typed method; they return a `Response` with the decoded JSON body.
- Lists are returned as `Results<T>`: iterable and countable, with `data(): list<T>` and
  `meta()`. `reverse()` returns `ReverseResults`, which adds `areas()`. Single results are a
  typed object with `meta()`.
- `meta()` holds the request id, the credits charged, the balance and `rateLimit()`
  (`limit()`, `remaining()`, `resetInSeconds()`, `resetsAt()`); `rateLimit()` is `null` for a
  cached answer.
- Getters spell units out: `distanceInMeters()`, `livingAreaInSquareMeters()`,
  `pricePerSquareMeter()`, `scorePercentage()`, `averageAskingPrice()`.
- Prices and credit costs are set by Housepedia: see `catalog()` and
  https://huispedia.dev/en/docs/credits/. `meta()?->credits()` shows what a call was charged.
- The API limits requests per account (https://huispedia.dev/en/docs/rate-limits/);
  `meta()?->rateLimit()` shows what is left. A `RateLimitException` is retried twice after `retryAfter()` seconds; then it is thrown.
  `Config(retries: 0)` disables retrying, `Config(retryServiceUnavailable: true)` also retries
  a 503, and `Client::retryUsing()` replaces the strategy.
- `account()->requests()` returns one page of the request log (`meta()?->nextCursor()` for the
  next page); `account()->lazy()->requests()`
  is a generator over every entry of every page.
- Without an API key only `catalog()` works; other calls throw `MissingApiKeyException`.
- Enum options (`SuggestionType`, `PropertyFieldGroup`, `ReferenceSort`, `TrendMetric`,
  `UsageGrouping`) also accept the API value as a string.
- `Config(cache: $psr16Cache)` caches answers for the `max-age` the API sends;
  `maxCacheAgeInSeconds` shortens that. `withMaxCacheAge($seconds)` and `withoutCache()` on a
  resource or the client change it per call and return a copy.

## Locators

```php
$client->addresses()->withPostcode('1076LJ', 12, '2');   // postcode, number, extension
$client->addresses()->withPostcode('1076LJ', 12);        // without extension
$client->addresses()->withBagId('0363200000028422');
$client->buildings()->withBagPandId('0363100012345678');
$client->areas()->withNeighbourhood('BU0363KH05');       // also withDistrict, withMunicipality, withProvince, national
$client->areas()->withPostcode4('1076');                 // PostcodeArea: find() only
```

The postcode is upper-cased and stripped of spaces. The extension is sent as given; the
API ignores case and spaces. A separate ground-floor home has extension `H`.

## Addresses

```php
$client->addresses()->suggest('1076LJ 12-2');                       // autocomplete
$client->addresses()->withPostcode('1076LJ', 12, '2')->find();      // Address
$client->addresses()->withPostcode('1076LJ', 12)->findAll();        // every home at a number
$client->addresses()->reverse(52.34536, 4.86274, radiusInMeters: 50);
```

`find()` throws `AddressAmbiguousException` when several homes share the number without
extension; `candidates()` returns them as `Address` objects.

## Properties

```php
$home = $client->properties()->withPostcode('1076LJ', 12, '2');

$home->find();               // Property, optionally find([PropertyFieldGroup::Dimensions])
$home->energyLabel();
$home->woz(indexed: true);
$home->condition();
$home->valuation();          // valuation(asOfDate: new DateTimeImmutable('2025-01-01'))
$home->valuationHistory();
$home->surroundings();
$home->environment();
$home->transactions();       // needs a transaction-price contract
$home->hasTransactions();    // bool, free, needs a transaction-price contract
$home->valuationPlus();      // needs a transaction-price contract
$home->references(limit: 5, maxDistanceInMeters: 1000, sort: ReferenceSort::Distance);
```

## Buildings, areas and valuations

```php
$client->buildings()->withBagPandId($address->bagPandId())->find();

$client->areas()->lookupPostcode('1076LJ');                         // AreaHierarchy
$client->areas()->withMunicipality('GM0363')->stats();
$client->areas()->national()->trends(TrendMetric::AskingPricePerSquareMeter);
$client->areas()->withNeighbourhood('BU0363KH05')->demographics();

$client->valuations()->scenarios([
    new ScenarioRow(
        Locator::postcode('1076LJ', 12, '2'),
        scenarios: [new Scenario('extra-bedroom', new Characteristics(bedrooms: 3))],
    ),
]);
```

Statistics, trends and demographics exist from neighbourhood to national level.

## Errors

Every API error throws a subclass of `ApiException` with `status()`, `errorCode()`,
`title()`, `detail()`, `requestId()`, `errors()` and `retryAfter()`:

| Exception                     | Status |
|-------------------------------|--------|
| `ValidationException`         | 400    |
| `AuthenticationException`     | 401    |
| `PaymentRequiredException`    | 402    |
| `ForbiddenException`          | 403    |
| `NotFoundException`           | 404    |
| `ConflictException`, `AddressAmbiguousException` | 409 |
| `DataUnavailableException`    | 422    |
| `RateLimitException`          | 429    |
| `ServiceUnavailableException` | 503    |
| `ServerException`             | other 5xx |
| `ApiException`                | any other error status |

Network failures throw `TransportException`; a call without API key throws
`MissingApiKeyException`; a coordinate that is not a decimal number (`'52.3'`, not `'52,3'`)
throws `InvalidCoordinateException`. All exceptions extend `HousepediaException`.
