# Housepedia Data Intelligence SDK — Specification

| Item      | Value                                                                                    |
|-----------|------------------------------------------------------------------------------------------|
| Package   | `henzeb/housepedia-data-intelligence`                                                         |
| Namespace | `Henzeb\Housepedia\Data\Intelligence`                                                         |
| API       | Housepedia Data Intelligence API v5 (OpenAPI 3.1 document version 5.0.0)                 |
| Status    | Automated, live-free and live-paid tests passing (2026-11-11), manual test plan passed (2026-11-11) |
| Date      | 2026-11-10                                                                               |

- [1. Scope](#1-scope)
- [2. Sources of truth](#2-sources-of-truth)
- [3. Client](#3-client)
- [3a. API versions](#3a-api-versions)
- [4. Transport](#4-transport)
- [5. Locators](#5-locators)
- [6. Resources and builders](#6-resources-and-builders)
- [7. Endpoints](#7-endpoints)
- [8. Response objects](#8-response-objects)
- [9. Raw requests](#9-raw-requests)
- [10. Errors](#10-errors)
- [11. Pricing](#11-pricing)
- [12. Platform and dependencies](#12-platform-and-dependencies)
- [13. Quality requirements](#13-quality-requirements)
- [14. Release process](#14-release-process)
- [15. Known limitations](#15-known-limitations)
- [16. Manual test plan](#16-manual-test-plan)
- [17. Automated test status](#17-automated-test-status)

## 1. Scope

1.1. The package is a framework-independent PHP SDK for the Housepedia Data Intelligence API.

1.2. It covers every live v5 endpoint listed in [section 7](#7-endpoints) with typed
methods, and every other endpoint through raw requests ([section 9](#9-raw-requests)).

1.3. It returns immutable, typed response objects; list endpoints return a `Results` object
with the list and the `meta` of the answer.

1.4. It depends on PSR interfaces only; it has no dependency on Laravel or any other
framework.

1.5. The repository is independent: its own Git history, Composer configuration, CI,
documentation and release tags.

## 2. Sources of truth

2.1. The OpenAPI document `https://huispedia.dev/openapi.json` defines paths, parameters and
response schemas.

2.2. The documentation under `https://huispedia.dev/en/docs/`, starting at
`https://huispedia.dev/en/docs/quickstart/`, defines authentication, locators, errors, rate
limits, credits and versioning.

2.3. `GET https://huispedia.dev/v5/catalog` defines the price per endpoint and the plans.

2.4. Within v5 the API only adds endpoints, optional parameters, response fields and enum
values. The SDK accepts unknown response fields and unknown values of enum-like fields. A
change that breaks this comes only in a new major version (v6), announced at least 60 days
ahead; v5 keeps running next to it for a stated period.

## 3. Client

3.1. `Henzeb\Housepedia\Data\Intelligence\Client` is the entry point.

3.2. `Client::create(?string $apiKey = null, ?ClientInterface $httpClient = null, Config $config = new Config())`
creates a client. Without `$httpClient`, a PSR-18 client is discovered with
`php-http/discovery`; the PSR-17 request and stream factories are always discovered.

3.3. `new Client(Transport $transport)` accepts a transport built with any PSR-18 client and
PSR-17 factories: `new Transport(?string $apiKey, ClientInterface $client,
RequestFactoryInterface $requestFactory, StreamFactoryInterface $streamFactory, Config $config = new Config())`.

3.3a. `Henzeb\Housepedia\Data\Intelligence\Config` holds the settings of a client. Its constructor
takes named arguments with defaults, it has a getter per setting and no setters:

| Argument                  | Default | Getter                      |
|---------------------------|---------|-----------------------------|
| `baseUrl`                 | `null`  | `baseUrl()`: `Transport::BASE_URL` when `null` or empty |
| `language`                | `null`  | `language()`: `null` when empty |
| `retries`                 | `2`     | `retries()`                 |
| `retryServiceUnavailable` | `false` | `retryServiceUnavailable()` |
| `cache`                   | `null`  | `cache()`: a PSR-16 `Psr\SimpleCache\CacheInterface`; `null` disables caching |
| `cachePrefix`             | `'housepedia-data-intelligence'` | `cachePrefix()`        |
| `maxCacheAgeInSeconds`    | `null`  | `maxCacheAgeInSeconds()`: `null` follows the API |
| `version`                 | `null`  | `version()`: the normalised API version (3a), `Config::FALLBACK_VERSION` when `null` |

`shouldRetry(Throwable $exception)` is true for a `RateLimitException`, and for a
`ServiceUnavailableException` when `retryServiceUnavailable` is enabled.
`retryDelay(Throwable $exception)` is the number of seconds to wait: `retryAfter()` of an
`ApiException`, or 1 when it has none.
`withMaxCacheAge(int $seconds)` returns a copy with `maxCacheAgeInSeconds` set to `$seconds`;
`withoutCache()` returns a copy without cache; `withVersion(int|string $version)` returns a
copy with that API version. `api()` returns the `Contracts\Api` of the version (3a).

3.3b. `Client::retryUsing(?Closure $callback)` sets how requests are retried, for every
client. The callback receives a closure that sends the request once and the `Config` of the
client, and returns the `Response`: `Closure(Closure(): Response $attempt, Config $config): Response`.
`null` restores the default, which sends the request and, while `shouldRetry()` is true and
fewer than `retries()` retries were made, waits `retryDelay()` seconds with `sleep()` and
sends it again. After the last attempt the exception is thrown. `retries` `0` sends every
request once.

3.4. The client exposes `addresses()`, `properties()`, `buildings()`, `areas()`,
`valuations()`, `account()`, `catalog()`, `get()`, `post()`, `version()`, `withVersion()`,
`withMaxCacheAge()` and `withoutCache()`. The last three return a new client whose requests,
and the requests of the resources it creates, use that API version (3a) or cache setting
(4.13). The resource methods and `catalog()` return the interfaces of `Contracts` (3a).

3.5. The client holds no request-specific state; every resource method returns a new
resource.

## 3a. API versions

3a.1. Every API version has its own namespace, `Henzeb\Housepedia\Data\Intelligence\V5` for
v5, with its resources (`V5\Resources`), response objects (`V5\DTO`, `V5\DTO\Catalog`), input
enums (`V5\Enums`), input objects (`V5\Input`) and `V5\Api`. Versions do not extend each
other's classes. `Client`, `Config`, `Transport`, `Response`, `RateLimit`, `Support\Payload`,
`Locators` and `Exceptions` are shared by every version.

3a.2. `Henzeb\Housepedia\Data\Intelligence\Contracts` holds the interfaces the versions
implement: one per resource (`Addresses`, `Properties`, `Buildings`, `Areas`,
`PostcodeArea`, `Valuations`, `Account`, `LazyAccount`), one per response object in
`Contracts\DTO` and `Contracts\DTO\Catalog` (with the same names as the classes of the
version), one per input enum in `Contracts\Enums` (extending `BackedEnum`),
`Contracts\Input\ScenarioRow`, and `Contracts\Api`. Method signatures of the interfaces use
these interfaces as parameter and return types; the classes of a version return their own
classes, which implement them.

3a.3. `Contracts\Api` creates the resources of a version for a `Transport`
(`addresses()`, `properties()`, `buildings()`, `areas()`, `valuations()`, `account()`),
reads its catalog (`catalog()`) and hydrates an address (`address(Payload)`), which
`AddressAmbiguousException::candidates()` uses.

3a.4. A version is given as an integer or a string: `5`, `'5'`, `'v5'` and `'V5'` are the same
version. It is normalised to `V` plus the number (`'V5'`). A version the SDK does not contain,
or a value that is not a version, throws `UnsupportedVersionException` when the `Config` is
created or `withVersion()` is called, before any request.

3a.5. The version of a call is, from first to last: `withVersion()` on the client,
`Config(version: …)`, and `Config::FALLBACK_VERSION`, the version of the SDK major (`'V5'` in
1.x). `Client::version()` returns the version in use.

3a.6. The interfaces contain what every supported version offers. A version that adds an
endpoint or field adds it to its own classes only. When a new API version no longer offers
something the interfaces contain, it is removed from the interfaces in a new major version of
the SDK; the classes of the older versions keep it.

3a.7. The API path of a version starts with its prefix (`/v5`); answers of different versions
never share a cache entry (4.13).

## 4. Transport

4.1. `Henzeb\Housepedia\Data\Intelligence\Transport\Transport` is the only class that performs
HTTP requests.

4.2. Paths are appended to `Config::baseUrl()`, which defaults to `https://huispedia.dev`.

4.3. Every request carries `Accept: application/json` and
`User-Agent: henzeb/housepedia-data-intelligence/<version>`, where the version is the installed
version of the package from Composer's `InstalledVersions` (`v1.0.0`, `dev-main`), or
`unknown` when Composer has none or does not know the package.

4.4. Authenticated requests carry `Authorization: Bearer <api key>` (the `BearerAuth`
scheme of the OpenAPI document). Without an API key (`null` or an empty string), an
authenticated request throws `MissingApiKeyException` before it is sent.
`GET /v5/catalog` is sent without the header and works without an API key.

4.5. When `Config::language()` is set, requests carry `Accept-Language: <language>`.

4.6. Query parameters with a `null` value are left out; booleans are sent as `true`/`false`;
floats as the shortest decimal without exponent that converts back to the same float
(`52.1`, `0.30000000000000004`), whatever the `precision` and `serialize_precision`
settings; `NAN`, `INF` and `-INF` as those words, and a float that needs more than 53
decimals with `%.17G`. The query is encoded with RFC 3986.

4.6a. Coordinates (`latitude`, `longitude` of `reverse()` and `lookupCoordinates()`) accept a
float or a string. A string is sent unchanged and must be a decimal number: an optional `-`,
digits, and optionally `.` with digits (`52.34536032`, `-4.8`). A string of another form, or
a float that is `NAN` or infinite, throws `InvalidCoordinateException` naming the coordinate
and its value, before a request is sent. The range is checked by the API.

4.7. Request bodies are JSON with `Content-Type: application/json`.

4.7a. `Transport::head()` sends a `HEAD` request. Its answer has no body, so an error answer
has no problem document and its exception code is `http_<status>`.

4.8. A 2xx response body is decoded as a JSON object; an empty body is an empty object. Any
other body throws `UnexpectedResponseTypeException`.

4.9. A response with status 400 or higher throws the exception from
[section 10](#10-errors).

4.10. A failure of the HTTP client (`ClientExceptionInterface`) throws `TransportException`.

4.11. Every request, including `get()` and `post()`, goes through the retry callback of
[3.3b](#3-client). Only error responses are retried; a `TransportException` is thrown at once.

4.12. Timeouts, proxies and other connection settings are configured on the PSR-18 client.

4.13. Caching. With a `Config::cache()`, the transport caches answers as the API allows it
(the documentation: "Cache-Control says per endpoint how long you may reuse an answer"):

- Only `GET` and `HEAD` requests are read from and written to the cache. A cached answer is
  returned without a request, before the retry callback of 3.3b.
- An answer is written only when its status is 2xx and its `Cache-Control` header has a
  `max-age` above 0 and neither `no-store` nor `no-cache`. Answers without `Cache-Control`,
  error answers and `POST` answers are never written.
- The time to live is `max-age`, lowered to `maxCacheAgeInSeconds()` when that is set and
  smaller. A value of 0 or less writes nothing.
- The key is `<cachePrefix>.<sha256>`; the hash covers the method, the full URL with query,
  the `Accept-Language` and the API key. PSR-16 reserves `{}()/\@:` in keys; the prefix is
  not validated.
- The cached value is an array with the status, the headers and the decoded body. A cached
  `Response` returns the headers of the original answer, including `X-Credits-Charged`, except
  the `X-RateLimit-*` headers, which are not stored: a cached answer has no rate limit (4.14).
  A cached value of another shape is treated as missing.
- Exceptions of the cache are not caught.

4.14. Rate limit. When an answer carries `X-RateLimit-Limit`, `X-RateLimit-Remaining` and
`X-RateLimit-Reset`, all three as whole numbers, its `Response::rateLimit()` returns a
`RateLimit` with `limit()`, `remaining()`, `resetInSeconds()` (the seconds until the bucket is
full) and `resetsAt()` (the time the answer was received plus `resetInSeconds()`). Otherwise it
returns `null`. The `Payload` of the answer carries the rate limit to every `Meta` hydrated from
it, as `Meta::rateLimit()`.

## 5. Locators

5.1. A home locator is `Henzeb\Housepedia\Data\Intelligence\Locators\Locator`:

| Factory                                         | Path value            | Body value (`value()`) |
|-------------------------------------------------|-----------------------|------------------------|
| `Locator::postcode('1076 lj', 12, '2')`         | `1076LJ-12-2`         | `1076LJ-12-2`          |
| `Locator::postcode('1076LJ', 12)`               | `1076LJ-12`           | `1076LJ-12`            |
| `Locator::postcode('1076LJ', 12, '')`           | `1076LJ-12`           | `1076LJ-12`            |
| `Locator::postcode('1076LJ', 12, 'A 1')`        | `1076LJ-12-A%201`     | `1076LJ-12-A 1`        |
| `Locator::postcode('2514KP', 1, ' bis ')`       | `2514KP-1-%20bis%20`  | `2514KP-1- bis `       |
| `Locator::bagId('0363200000028422')`            | `bag:0363200000028422`| `bag:0363200000028422` |

Postcodes are upper-cased and stripped of spaces. The extension is sent as given: not
trimmed, not upper-cased and not validated, only URL-encoded in the path. `null` and `''`
mean no extension. Casting to string returns the path value.

5.1a. The SDK does not validate locator input; the API decides what exists and answers
`NotFoundException` or `ValidationException` otherwise.

5.2. An area locator is `Henzeb\Housepedia\Data\Intelligence\Locators\AreaLocator`:
`neighbourhood($code)`, `district($code)`, `municipality($code)`, `province($code)` and
`national()`, producing `level:code` or `national`. These are the levels with statistics,
price index and demographics.

5.2a. A postcode area locator is `Henzeb\Housepedia\Data\Intelligence\Locators\PostcodeAreaLocator`:
`postcode4($postcode)` and `postcode6($postcode)`, producing `postcode4:1076` and
`postcode6:1076LJ` (upper-cased, spaces removed).

5.3. A building is located by its 16-digit BAG pand id.

## 6. Resources and builders

6.0. The resources of section 6 and 7 are the classes of `V5\Resources`; `Client` returns them
as their `Contracts` interface.

6.1. Resources are immutable. Every `with*()` method returns a new instance; the
instance it is called on is unchanged.

6.2. A new locator replaces the previous one.

6.2a. Every resource has `withMaxCacheAge(int $seconds)` and `withoutCache()` from the
trait `Concerns\ConfiguresCache`. They return a copy with the same locator whose requests
use that cache setting: `withMaxCacheAge()` replaces `maxCacheAgeInSeconds`, also when it is
higher, and `max-age` of the answer still limits it; `withoutCache()` neither reads nor
writes the cache. The setting is kept by the locator methods. Without a `Config::cache()`
both have no effect.

6.3. Each resource exposes only the locator methods its endpoints support:

| Resource     | Locator methods                                                                 |
|--------------|---------------------------------------------------------------------------------|
| `Addresses`  | `withPostcode()`, `withBagId()`, `withLocator()`                                |
| `Properties` | `withPostcode()`, `withBagId()`, `withLocator()`                                |
| `Buildings`  | `withBagPandId()`                                                               |
| `Areas`      | `withNeighbourhood()`, `withDistrict()`, `withMunicipality()`, `withProvince()`, `national()`, `withArea()`; `withPostcode4()` and `withPostcode6()` return a `PostcodeArea` |
| `PostcodeArea` | none; created by `Areas::withPostcode4()` and `withPostcode6()` with its locator |
| `Valuations` | none                                                                            |
| `Account`    | none                                                                            |
| `LazyAccount` | none; created by `Account::lazy()`                                             |

6.4. `withPostcode(string $postcode, int $houseNumber, ?string $extension = null)` and
`withBagId(string $bagId)` are provided by the traits
`Concerns\WithPostcode` and `Concerns\WithBagId`.

6.5. Calling a method that needs a locator on a resource without one throws
`MissingLocatorException` without sending a request. The message names the class and method.

6.6. Operations without a locator are direct methods: `suggest()`, `reverse()`,
`lookupPostcode()`, `lookupCoordinates()`, `scenarios()` and the account methods.

## 7. Endpoints

7.1. Addresses

| Method                                                                   | Request                                        | Returns                   |
|--------------------------------------------------------------------------|------------------------------------------------|---------------------------|
| `find(bool $includeRd = false)`                                          | `GET /v5/addresses/{locator}` `include=rd`     | `Address`                 |
| `findAll(bool $includeRd = false)`                                       | `GET /v5/addresses/{locator}` `all=true`       | `Results<Address>`        |
| `suggest(string $query, ?int $limit = null, list<SuggestionType\|string> $types = [])` | `GET /v5/addresses/suggest` `q`, `limit`, `types` | `Results<AddressSuggestion>` |
| `reverse(float\|string $latitude, float\|string $longitude, ?int $radiusInMeters = null, ?int $limit = null)` | `GET /v5/addresses/reverse` `lat`, `lon`, `radius_m`, `limit` | `ReverseResults` |

`findAll()` accepts both a `{data: [...]}` answer and a single address answer; a single
address becomes a `Results` with that address and its `meta`. `ReverseResults::areas()` returns
the `areas` of the answer as `?AreaHierarchy`.

7.2. Properties

| Method                                                                   | Request                                              | Returns            |
|--------------------------------------------------------------------------|------------------------------------------------------|--------------------|
| `find(list<PropertyFieldGroup\|string> $fields = [], bool $includeRd = false)` | `GET /v5/properties/{locator}` `fields`, `include`   | `Property`         |
| `energyLabel()`                                                          | `GET /v5/properties/{locator}/energy-label`          | `EnergyLabel`      |
| `woz(bool $indexed = false)`                                             | `GET /v5/properties/{locator}/woz` `index`           | `Woz`              |
| `condition()`                                                            | `GET /v5/properties/{locator}/condition`             | `Condition`        |
| `transactions()`                                                         | `GET /v5/properties/{locator}/transactions`          | `Transactions`     |
| `hasTransactions()`                                                      | `HEAD /v5/properties/{locator}/transactions`         | `bool`             |
| `valuation(?DateTimeInterface $asOfDate = null)`                         | `GET /v5/properties/{locator}/valuation` `as_of_date`| `Valuation`        |
| `valuationPlus(?DateTimeInterface $asOfDate = null)`                     | `GET /v5/properties/{locator}/valuation/plus`        | `ValuationPlus`    |
| `valuationHistory(?DateTimeInterface $from, ?DateTimeInterface $to, bool $includeRd)` | `GET /v5/properties/{locator}/valuation/history` `from`, `to`, `include` | `ValuationHistory` |
| `references(?int $limit, ?int $maxDistanceInMeters, ?int $maxAgeMonths, ?bool $sameType, ?int $minLivingAreaInSquareMeters, ?int $maxLivingAreaInSquareMeters, ?int $minConstructionYear, ?int $maxConstructionYear, ?DateTimeInterface $asOfDate, ReferenceSort\|string\|null $sort)` | `GET /v5/properties/{locator}/references` | `References` |
| `surroundings()`                                                         | `GET /v5/properties/{locator}/surroundings`          | `Surroundings`     |
| `environment()`                                                          | `GET /v5/properties/{locator}/environment`           | `Environment`      |

`hasTransactions()` returns `true` on 200 and `false` on 404. The answer has no body, so an
unknown address also returns `false`. Other errors throw as usual. Like `transactions()`, it needs
a transaction-price contract: without one, the API answers 403 for every address, also an
unknown one (checked 2026-11-09), and it throws `ForbiddenException`.

7.3. Buildings: `find()` sends `GET /v5/buildings/{bag_pand_id}` and returns `Building`.

7.4. Areas

| Method                                                                  | Request                                         | Returns          |
|-------------------------------------------------------------------------|-------------------------------------------------|------------------|
| `lookupPostcode(string $postcode)`                                      | `GET /v5/areas/lookup` `postcode`               | `AreaHierarchy`  |
| `lookupCoordinates(float\|string $latitude, float\|string $longitude)`    | `GET /v5/areas/lookup` `lat`, `lon`             | `AreaHierarchy`  |
| `find(bool $includeRd = false)`                                         | `GET /v5/areas/{area}` `include`                | `Area`           |
| `stats()`                                                               | `GET /v5/areas/{area}/stats`                    | `AreaStatistics` |
| `trends(TrendMetric\|string\|null $metric, ?DateTimeInterface $from, ?DateTimeInterface $to, bool $includeRd)` | `GET /v5/areas/{area}/trends` `metric`, `from`, `to`, `include` | `AreaTrends` |
| `demographics()`                                                        | `GET /v5/areas/{area}/demographics`             | `Demographics`   |

`PostcodeArea` has only `find(bool $includeRd = false)`: `GET /v5/areas/{postcode area}`
`include`, returning `Area`. Statistics, price index and demographics do not exist for
postcode areas (limitation 5), so `PostcodeArea` has no `stats()`, `trends()` or
`demographics()`.

7.5. Valuations: `scenarios(list<ScenarioRow> $rows)` sends `POST /v5/valuations/scenarios`
with body `{"data": [row, …]}` and returns `Results<ScenarioValuation>`. A row is
`new ScenarioRow(Locator $locator, Characteristics $characteristics = new Characteristics(), list<Scenario> $scenarios = [])`;
a scenario is `new Scenario(string $name, Characteristics $characteristics = new Characteristics())`.
`Characteristics` accepts every characteristic of the `Scenario` schema as an optional named
argument; `null` values are left out of the body. The row's locator is sent as `locator`.

7.6. Account

| Method                                                                  | Request                                         | Returns          |
|-------------------------------------------------------------------------|-------------------------------------------------|------------------|
| `details()`                                                             | `GET /v5/account`                               | `Account`        |
| `usage(?DateTimeInterface $from, ?DateTimeInterface $to, list<UsageGrouping\|string> $groupBy)` | `GET /v5/account/usage` `from`, `to`, `group_by` | `Results<Usage>` |
| `requests(?DateTimeInterface $from, ?DateTimeInterface $to, ?string $keyId, ?int $status, ?int $limit, ?string $cursor)` | `GET /v5/account/requests` | `Results<RequestLogEntry>` |
| `lazy()`                                                                | none                                            | `LazyAccount`    |

`meta()?->nextCursor()` of the `requests()` results is the cursor of the next page.

7.6a. `Account::lazy()` returns `V5\Resources\LazyAccount` with the cache setting of the account
(6.2a). It reads the paginated account endpoints page by page:

| Method                                                                  | Request                                         | Returns          |
|-------------------------------------------------------------------------|-------------------------------------------------|------------------|
| `requests(?DateTimeInterface $from, ?DateTimeInterface $to, ?string $keyId, ?int $status, ?int $limit)` | `GET /v5/account/requests`, one per page | `Generator<int, RequestLogEntry>` |

`LazyAccount::requests()` sends the filters like `Account::requests()`; `limit` is the number
of entries per page (the API default of 25 when `null`). It yields every entry of a page with
keys `0…n-1` across all pages, then requests the next page with `cursor` set to
`meta()?->nextCursor()` of the page, until it is `null`. No request is sent before the first entry is
read, and a page is only requested when the entries before it have been read. A generator is
iterated once; a new call starts at the first page.

7.7. Catalog: `Client::catalog()` sends `GET /v5/catalog` without API key and returns
`V5\DTO\Catalog\Catalog`.

7.8. Parameter formats: dates as `Y-m-d`; date-times (`account()->requests()`) as RFC 3339;
enum lists as comma-separated values.

7.9. Input enums: `SuggestionType` (address, street, postcode), `PropertyFieldGroup` (type,
dimensions, rooms, outdoor, construction, energy, ownership, maintenance), `ReferenceSort`
(similarity, distance, date), `TrendMetric` (transaction_price_per_m2, asking_price_per_m2),
`UsageGrouping` (day, endpoint, key). Every parameter that takes one of these enums also
accepts a string, which is sent unchanged, so a value the API adds before the enum has a case
can be used; strings are not validated.

## 8. Response objects

8.1. Every response schema of the OpenAPI document maps to a class in
`Henzeb\Housepedia\Data\Intelligence\V5\DTO`; the catalog maps to classes in `V5\DTO\Catalog`. Each
class implements the interface of the same name in `Contracts\DTO` (3a.2). Class
names have no `Data` suffix.

8.2. Each field is read through a public method named after the field in camel case, with
abbreviations and units written out:

| API name part         | PHP name part                 | Example                                              |
|-----------------------|-------------------------------|------------------------------------------------------|
| `avg_`                | `average`                     | `avg_size` → `averageSize()`                         |
| `_m`                  | `InMeters`                    | `distance_m` → `distanceInMeters()`                  |
| `_m2`                 | `InSquareMeters`              | `living_area_m2` → `livingAreaInSquareMeters()`      |
| `_per_m2`             | `PerSquareMeter`              | `price_per_m2` → `pricePerSquareMeter()`             |
| `_m3`                 | `InCubicMeters`               | `volume_m3` → `volumeInCubicMeters()`                |
| `_ug_m3`              | `InMicrogramsPerCubicMeter`   | `pm25_ug_m3` → `pm25InMicrogramsPerCubicMeter()`     |
| `_ms`                 | `InMilliseconds`              | `duration_ms` → `durationInMilliseconds()`           |
| `_pct`                | `Percentage` (`Percent` after a number) | `score_pct` → `scorePercentage()`, `within_5_pct` → `within5Percent()` |
| `yoy`                 | `YearOverYear`                | `yoy_pct` → `yearOverYearPercentage()`               |
| `_eur`                | `InEuros`                     | `credit_price_eur` → `creditPriceInEuros()`          |
| `_db`                 | `InDecibels`                  | `road_lden_db` → `roadLdenInDecibels()`              |
| `_c`                  | `InDegreesCelsius`            | `urban_heat_island_c` → `urbanHeatIslandInDegreesCelsius()` |
| `_degrees`            | `InDegrees`                   | `garden_orientation_degrees` → `gardenOrientationInDegrees` |

The same rule names method parameters and `Characteristics` arguments
(`radius_m` → `$radiusInMeters`, `min_living_area_m2` → `$minLivingAreaInSquareMeters`) and
enum cases (`asking_price_per_m2` → `TrendMetric::AskingPricePerSquareMeter`); the API names
in requests and responses are unchanged. `huispedia_url` and `huispedia_id` are read through
`housepediaUrl()` and `housepediaId()`. A `data` list is named after its contents:
`Transactions::transactions()`, `References::references()`,
`ValuationHistory::points()`, `AreaTrends::points()`, `TrendSeries::points()`.

8.3. Types:

| Schema type                       | PHP type                         |
|-----------------------------------|----------------------------------|
| `string`                          | `string`                         |
| `string` with format date/date-time | `DateTimeImmutable`            |
| `integer`                         | `int`                            |
| `number`                          | `float`                          |
| `boolean`                         | `bool`                           |
| object reference                  | DTO class                        |
| array of objects                  | `list<DTO>`                      |
| `{data, meta}` answer of a list endpoint | `Results<DTO>`            |
| array of strings                  | `list<string>`                   |
| map                               | `array<string, T>`               |
| Kadaster price                    | `int\|string\|null`              |
| free-form object                  | `array<string, mixed>`           |

8.4. Fields that are required and not nullable in the schema are non-nullable; every other
field is nullable. Arrays that are absent become an empty list unless the schema allows
`null`.

8.5. A field with an unexpected type, or a missing required field, throws
`UnexpectedResponseTypeException` naming the field path.

8.6. Address blocks embedded in other responses use the `Address` class; fields that only
`GET /v5/addresses/{locator}` returns are `null` there.

8.7. Each object constructs itself with `static from(Payload $payload): self`.

8.8. Single-result objects expose `meta(): ?Meta` with `requestId()`, `asOf()`,
`credits()` (`charged()`, `balance()`), `nextCursor()`, `withheld()` and `rateLimit()`
(4.14). Items of a list have no `meta` of their own (limitation 4): the `meta` of a list answer
is on its `Results`.

8.8a. `V5\DTO\Results<T>` holds the answer of a list endpoint: `data(): list<T>` and
`meta(): ?Meta`. It implements `IteratorAggregate` (iterating `data()` with keys `0…n-1`) and
`Countable`. `V5\DTO\ReverseResults` extends `Results<ReverseAddress>` and adds
`areas(): ?AreaHierarchy` for the `areas` of the reverse answer. `Results::of(Payload, callable)`
hydrates a list answer with the given factory for the items; `ReverseResults::from(Payload)`
hydrates the reverse answer.

8.9. Each catalog product exposes `id()`, `name()`, `description()`, `family()`, `method()`,
`path()`, `credits()`, `multiplier()`, `status()`, `cacheMaxAge()`, `docsUrl()`,
`contract()` and `contact()`. Translated texts are `LocalizedText` with `en()` and `nl()`.

## 9. Raw requests

9.1. `Client::get(string $path, array $query = [])` and
`Client::post(string $path, array $body, array $query = [])` are the low-level layer below
the typed methods: they call any path with the transport of [section 4](#4-transport) and
return the raw response without hydrating a typed object. They exist for endpoints without
a typed method.

9.2. Both return `Henzeb\Housepedia\Data\Intelligence\Response` with:

| Method                     | Returns                          |
|----------------------------|----------------------------------|
| `status()`                 | `int`                            |
| `headers()`                | `array<string, list<string>>`    |
| `header(string $name)`     | first value, case-insensitive, or `null` |
| `meta()`                   | `?Meta`                          |
| `rateLimit()`              | `?RateLimit` (4.14)              |
| `raw()`                    | the decoded body                 |
| `payload()`                | `Support\Payload`                |
| `has(string $key)`         | `bool`                           |
| `string(string $key)`      | `?string`                        |
| `integer(string $key)`     | `?int`                           |
| `float(string $key)`       | `?float`                         |
| `boolean(string $key)`     | `?bool`                          |
| `array(string $key)`       | `?array`                         |

9.3. Keys use dot notation (`data.0.street`). A missing key returns `null`; a value of
another type throws `UnexpectedResponseTypeException`.

## 10. Errors

10.1. Every exception extends `Exceptions\HousepediaException` (a `RuntimeException`).

10.2. Error responses throw:

| HTTP           | Exception                     |
|----------------|-------------------------------|
| 400            | `ValidationException`         |
| 401            | `AuthenticationException`     |
| 402            | `PaymentRequiredException`    |
| 403            | `ForbiddenException`          |
| 404            | `NotFoundException`           |
| 409 `address_ambiguous` | `AddressAmbiguousException` (extends `ConflictException`) |
| 409            | `ConflictException`           |
| 422            | `DataUnavailableException`    |
| 429            | `RateLimitException`          |
| 503            | `ServiceUnavailableException` |
| other 5xx      | `ServerException`             |
| other 4xx      | `ApiException`                |

10.3. Every error-response exception extends `ApiException` and exposes `status()`,
`errorCode()` (`code`, or `http_<status>` without problem body), `title()` (`title`, or the
reason phrase), `detail()`, `requestId()`, `errors()`, `type()` and `retryAfter()`.
`getMessage()` is `detail` or else `title`; `getCode()` is the status.

10.4. `retryAfter()` reads `Retry-After` as seconds or as an HTTP date.

10.5. `AddressAmbiguousException::candidates()` returns the entries of `errors` as
`list<Address>`.

10.6. Other exceptions: `MissingLocatorException` ([6.5](#6-resources-and-builders)),
`MissingApiKeyException` ([4.4](#4-transport)), `InvalidCoordinateException` ([4.6a](#4-transport)),
`TransportException` ([4.10](#4-transport)), `UnexpectedResponseTypeException`
([4.8](#4-transport), [8.5](#8-response-objects), [9.3](#9-raw-requests)).

## 11. Pricing

11.1. Prices, plans and credit costs are set by Housepedia. `GET /v5/catalog` (`catalog()`) and
https://huispedia.dev/en/docs/credits/ are the authority. The SDK and its documentation state
no prices or credit costs; they only pass on what the API reports (`meta()->credits()`, the
catalog objects). The credits a live test run spends are estimated in 13.12.

## 12. Platform and dependencies

12.1. PHP `^8.3`, including PHP 8.6; no runtime or development dependency may block
installation on a supported PHP version. CI covers PHP 8.3, 8.4, 8.5 and 8.6; the 8.6 jobs
run with `continue-on-error` until PHP 8.6 is released (planned 19 November 2026), because
setup-php installs a nightly build until then. CI installs with Composer's `notify-on-install` disabled, so
CI runs do not count as Packagist downloads.

12.2. Runtime dependencies: `php-http/discovery ^1.19`, `psr/http-client ^1.0`,
`psr/http-client-implementation ^1.0`, `psr/http-factory ^1.1`,
`psr/http-factory-implementation ^1.0`, `psr/http-message ^1.1|^2.0`,
`psr/simple-cache ^2.0|^3.0`, `composer-runtime-api ^2.0`.

12.3. Development dependencies: `guzzlehttp/guzzle ^7.8|^8.0`, `pestphp/pest ^4.0|^5.0`,
`laravel/pint ^1.30`, `php-http/mock-client ^1.6.1`, `php-http/httplug ^2.4.1`,
`phpstan/phpstan ^2.1`.

12.4. `dev-main` is aliased as `1.x-dev`.

12.5. Pest 4 (PHPUnit 12) installs on PHP 8.3 to 8.5 and Pest 5 (PHPUnit 13) on PHP 8.4 and
later; PHP 8.6 needs Pest 5, because the paratest versions that support PHP 8.6 require
PHPUnit 13. `phpunit.xml` sets `failOnDeprecation`, so PHP deprecations fail the tests.

## 13. Quality requirements

13.1. PHPStan at level `max` with bleeding edge reports no errors on `src`.

13.2. Public methods have native parameter and return types; lists and maps carry PHPDoc
generics (`list<AddressSuggestion>`, `array<string, NearestPlace|null>`).

13.3. Classes and properties are not `final` and not `readonly`, so every class can be extended
and mocked. Properties are private and have no setters, which makes the objects immutable.
Source files carry no explanatory comments. An architecture test enforces this for
`src`.

13.3a. `docs/agents.md` is a compact usage reference for coding agents (client, rules,
locators, every resource, errors). It is part of the Composer package and linked from the
README.

13.3b. `docs/reference/` documents every public method of the `Contracts` interfaces of the
resources and response objects, the cases of the input enums, the constructor arguments of
`V5\Input\ScenarioRow`, `Scenario` and `Characteristics`, and `RateLimit`, one file per area: `addresses.md`, `properties.md`, `buildings.md`, `areas.md`, `valuations.md`,
`account.md`, `catalog.md` and `shared.md`. An area file lists the methods of its resources
with their parameters, return type and description, followed by every response object reachable from them, each with a table of its getters:
method, return type, API field and a description: the description of the field in
`snapshots/v5/openapi.json`, or else the one in `bin/reference-descriptions.php`. Every getter
has a description. An area file starts with a structure: per method, a nested list of the
objects it returns and the objects inside them, each object expanded the first time it appears
in the file. A response object reachable
from more than one area, and `Meta`, `Credits`, `Results`, `ReverseResults` and `RateLimit`,
are in `shared.md`; return types link to the section of their object. `bin/generate-reference`
writes the files; `tests/Unit/ReferenceTest.php` fails when they differ from its output, when
a method of an interface is missing from them, when a getter has no description, or when
`bin/reference-descriptions.php` describes a method that does not exist. The guides in `docs/` link to the reference. `bin/` is not part of the Composer package.

13.4. The Pest suite covers requests (method, path, query, headers, body, float encoding and
coordinate checks), paging of
`LazyAccount::requests()`, `Results` and the rate limit, locator formatting, immutability, locator availability per
resource, missing locators, DTO
hydration of every response schema, error mapping, caching (with an in-memory PSR-16 cache
in `tests/Support`) and framework independence. The `Unit`
and `Integration` suites use mocked HTTP responses only.

13.4a. `tests/Integration/OpenApiContractTest.php` checks the SDK against
`snapshots/v5/openapi.json` without network access:

- every operation of the document is mapped to the DTO its SDK method returns, and no mapped
  operation is missing from the document. Operations without a response schema (`getCatalog`,
  `headTransactions`) map to no DTO;
- for every operation, a sample response is built from its response schema with every
  property filled. Each field is then changed on its own; the output of the DTO's getters must
  change with it, so every field of the schema is read by a getter, including the top-level
  fields of list endpoints (`meta`, `areas`);
- the SDK methods send exactly the documented query parameters of each operation, except
  `period` and `interval` (limitations), which must keep a single allowed value;
- `UsageGrouping`, `TrendMetric` and `ReferenceSort` have exactly the values the patterns of
  `group_by`, `metric` and `sort` allow;
- list parameters without a pattern (`types`, `fields` and every `include`) keep the description
  the test pins, and each value of `SuggestionType`, `PropertyFieldGroup` and `rd` appears in it;
  a new list parameter without a pattern must be pinned too;
- `scenarios()` with every `Characteristics` argument set sends exactly the fields of the
  `ScenariosIn` schema, except the alternative address fields of a row (limitations);
- for every operation, each field that the schema does not require is left out of the sample
  on its own, and each nullable field is set to `null` on its own; the answer must still
  hydrate without an exception;
- `BearerAuth` is an HTTP `bearer` security scheme, and every operation except `getCatalog`
  accepts it.

13.4b. `tests/Unit/ContractsTest.php` checks that every class of `V5\Resources`, `V5\DTO`,
`V5\DTO\Catalog`, `V5\Enums`, `V5\Input\ScenarioRow` and `V5\Api` implements the `Contracts`
interface of the same name with exactly the same public instance methods, that every interface
has an implementation, and that the client returns the `Contracts` interfaces. `tests/Unit/VersionTest.php` covers the
normalisation and order of versions (3a.4, 3a.5).

13.5. CI (`.github/workflows/tests.yml`) runs `composer validate --strict`, installs with
`prefer-lowest` and `prefer-stable`, runs PHPStan and Pest on every PHP
version of 12.1.

13.5a. `.github/workflows/api-snapshot.yml` runs on working days at 17:17 UTC and on request. It fetches
`GET /v5/catalog` and `/openapi.json` without an API key, writes them with sorted keys to
`snapshots/v5/catalog.json` and `snapshots/v5/openapi.json`, and fails with the diff in its log when
either file differs from the committed snapshot. The new snapshots are committed together with the
changes to the SDK, this specification, the docs and `tests/Fixtures/v5` that the diff calls for.
Every run enables the workflow again through the GitHub API, so GitHub does not disable the
schedule after 60 days without repository activity. `snapshots/` is not part of the Composer package.

13.6. The `Live` suite (`tests/Live`) calls the real API. It is not part of the default test
run (`composer test`) or CI and only runs on request:

| Command                     | Group       | Runs                                         | Credits per run        |
|-----------------------------|-------------|----------------------------------------------|------------------------|
| `composer test-live`        | `live`      | every live test                              | about 90 (see 13.12)   |
| `composer test-live-free`   | `live-free` | the free endpoints and error responses only  | 0                      |
| `composer test-live-paid`   | `live-paid` | the tests that cost credits only             | about 90 (see 13.12)   |

Every live test is in the group `live` and in exactly one of `live-free` and `live-paid`.

13.7. The live tests read their configuration from environment variables, falling back to a
`.env` file in the repository root. `.env` is gitignored; `.env.example` lists the variables:

| Variable                         | Use                                                                    |
|----------------------------------|------------------------------------------------------------------------|
| `HOUSEPEDIA_API_KEY`             | API key; without it every test that needs a key is skipped             |
| `HOUSEPEDIA_BASE_URL`            | optional, defaults to `https://huispedia.dev`                          |
| `HOUSEPEDIA_REQUEST_INTERVAL_MS` | optional minimum time between requests, defaults to `1000`, which keeps the live tests within the Free plan's limit (see https://huispedia.dev/en/docs/rate-limits/) |

13.8. A live test passes when the call returns a response that hydrates into its DTO and
matches the request (postcode, house number, codes), or the error the test expects. A paid
call answered with `DataUnavailableException` or `ForbiddenException` (no data, no contract or
no entitlement) marks the test incomplete with the error code, because the DTO was not
checked.

13.9. The `live-free` group covers:

- `catalog()` with and without an API key;
- `account()->details()`, `usage()`, `usage(groupBy: [UsageGrouping::Endpoint])`, and
  `requests(limit: 2)` with its next page;
- `account()->lazy()->requests(limit: 2)` for at most six entries: the keys run `0…n-1`,
  no request ID repeats and it reads past the first page when there is one; and
  `lazy()->requests(status: 404, limit: 2)` for at most four entries, all with status 404;
- `addresses()->suggest()` with and without `limit` and `types`;
- `properties()->withPostcode('1076LJ', 12, '2')->hasTransactions()`;
- caching with a `Config::cache()`: `suggest()` is written with the `max-age` of the answer and
  read on the second call; `catalog()` is written (`public, max-age=300`, checked 2026-11-09);
- `get('/v5/account')`;
- the errors: a wrong key (`AuthenticationException`, code `unauthenticated`), no key
  (`MissingApiKeyException`), an ambiguous
  address (`AddressAmbiguousException` with candidates), an unknown address
  (`NotFoundException`), a too short query (`ValidationException`), a scenario request without
  rows (`ValidationException`), an unknown path (`ApiException`) and an error `detail()` in
  Dutch with `Config(language: 'nl')` that differs from the English one.

13.10. The `live-paid` group calls every other endpoint of [section 7](#7-endpoints)
once with the addresses of the manual test plan.

13.11. `tests/Live/LimitationsTest.php` and the transactions, references and scenario tests
check the undocumented behaviour of [section 15](#15-known-limitations). Where the SDK
assumes one answer, the test asserts it and fails otherwise. Where the SDK handles every
possible answer, the test records the answer as a finding in `build/live-findings.json`
(gitignored), keyed by limitation number:

| Limitation | Check                                                                                 | Kind      | Group       |
|------------|---------------------------------------------------------------------------------------|-----------|-------------|
| 1          | `get('/v5/catalog')`: `data_points` and every product's `fields`, `coverage`, `freshness` and `product_url` are empty | assertion | `live-free` |
| 2          | the PHP type of `price()` and `priceIndexed()` in `transactions()` and `references()` | finding   | `live-paid` |
| 3          | the candidates of the ambiguous address hydrate into `Address` (13.9)                 | assertion | `live-free` |
| 4          | `get('/v5/addresses/1076LJ-12', ['all' => true])`: a single address or a `data` list, and how many homes in the list carry a `meta` | finding   | `live-paid` |
| 5          | `get('/v5/areas/postcode4:1076/stats')` answers `DataUnavailableException`, so `PostcodeArea` correctly has no `stats()` | assertion | `live-free` |
| 6          | the balance drops by 20 or 40 credits for one row with one scenario                   | assertion and finding | `live-paid` |
| 7          | `get('/v5/addresses/1076LJ-12-2', ['include' => 'unknown'])`: rejected or ignored     | finding   | `live-paid` |
| 8          | every response hydrates (all live tests)                                              | assertion | all         |
| 9          | the `X-RateLimit-*` headers of a response and its `rateLimit()`, and `retryAfter()` of a `RateLimitException` after a burst of unthrottled requests with `retries: 0` | finding | `live-free` |
| 10         | `withPostcode('1076LJ', 12, 'A 1')->find()` answers `NotFoundException`, not a validation error; `withPostcode('5308KH', 2, 'hs')->find()` answers `NotFoundException` | assertion | `live-free` |
| 10         | `withPostcode('2514KP', 1, 'bis')->find()` returns extension `BIS`                     | assertion | `live-paid` |

13.12. A `live` or `live-paid` run costs about 90 credits without a transaction-price
contract (measured 2026-11-08) (the 40-credit scenario request included). With a contract, `valuationPlus()` (12)
and `references()` (8) add 20 credits and `transactions()` 2 credits per record. The requests are spaced by
`HOUSEPEDIA_REQUEST_INTERVAL_MS`; a `live` run takes about a minute on the default interval.

13.13. Laravel Pint (`laravel/pint`, a development dependency) checks the code style of `src`
and `tests` with the `psr12` preset from `pint.json`. `composer format` fixes the style and
`composer format-check` reports files that differ. A `style` job in
`.github/workflows/tests.yml` runs `vendor/bin/pint --test` once on PHP 8.4 with
`prefer-stable`. `pint.json` is not part of the Composer package.

13.14. `SECURITY.md` says which versions get security fixes (the latest minor release of the
current major version) and asks to report a vulnerability by email to
henzeberkheij@gmail.com instead of in a public issue, with the affected version, the steps to
reproduce it and its impact. It is part of the Composer package; the README links to it.

## 14. Release process

14.1. A release follows these steps:

1. Push the changes to `main` of `github.com/henzeb/housepedia-data-intelligence`.
2. CI passes on every PHP version of 12.1.
3. Run the [manual test plan](#16-manual-test-plan), or the parts the change affects.
4. Rename `[Unreleased]` in `CHANGELOG.md` to the version and date, and update the compare
   links.
5. Tag the release with a signed `vX.Y.Z` tag following semantic versioning; Packagist
   picks the tag up from GitHub.
6. Verify `composer require henzeb/housepedia-data-intelligence` in a fresh project.

## 15. Known limitations

### Behaviour of the SDK

- `account()->requests()` returns one page; the next page is requested with
  `meta()?->nextCursor()`, or read every page with `account()->lazy()->requests()`.
- The `period` (stats) and `interval` (trends) parameters accept one value each in v5 and are
  not exposed.
- A scenario row is sent with `locator`; its alternative address fields `bag_id`, `postcode`,
  `house_number` and `house_number_ext` are not sent.
- Ten endpoints the catalog marks as `planned` (parcel, condition scores on photos, energy
  label impact, batch valuations, valuation report, bidding, listing, listing history,
  monitors, jobs) have no typed method. `get()`/`post()` can call them once they are live.

### Undocumented or uncertain API behaviour

These follow from gaps in the documentation. The live tests check them (13.11); an entry
marked *Verified* records the answer of the live API.

1. **Catalog fields.** The OpenAPI document types `/v5/catalog` as a free-form object. The
   DTOs follow the live response of 2026-11-08. `fields`, `coverage`, `freshness`,
   `product_url` and `data_points` were always empty and are not mapped. *Verified
   2026-11-08:* still empty.
2. **Kadaster prices.** `price` and `price_indexed` in transactions and references are
   `int | string | null` in the spec without saying when a string is returned. The SDK
   returns the value as is. *Open:* transaction prices need a contract with Housepedia
   (sales@huispedia.nl), separate from the plans. Without it, `transactions()`,
   `references()` and `valuationPlus()` answer `403 entitlement_required`.
3. **Ambiguous address candidates.** The docs say a `409 address_ambiguous` "carries the
   candidates" in `errors`. `AddressAmbiguousException::candidates()` assumes each entry is an
   address object with the fields of `Address`. *Verified 2026-11-08:* the candidates of
   `1076LJ-12` hydrate into `Address`.
4. **`findAll()` response shape.** The spec allows either a single address or `{data: [...]}`
   for `all=true`. The SDK handles both. *Verified 2026-11-08:* `1076LJ-12?all=true` answers
   a `data` list with the three homes. *Verified 2026-11-10:* the homes in that list have no
   `meta` of their own, although the `AddressOut` schema of an item allows one; the `meta` of
   the answer is on the `Results`.
5. **Area levels for statistics.** The locators page lists statistics, price index and
   demographics for neighbourhood, district, municipality, province and national. The OpenAPI
   descriptions use `postcode4:1076` as an example. *Verified 2026-11-08:*
   `postcode4:1076/stats` answers `422 data_unavailable`, "Statistics are available from
   neighbourhood to national level." Postcode areas are a separate `PostcodeArea` builder
   with only `find()`. Demographics are not available at every level either:
   `municipality:GM0363/demographics` answered `data_unavailable`,
   `neighbourhood:BU0363KH05/demographics` returned data.
6. **Scenario cost.** The catalog lists 20 credits with multiplier `scenarios`; the docs say
   "per successful row and per scenario". Whether the base row and each scenario both cost 20
   is not stated. *Verified 2026-11-08:* one row with one scenario cost 40 credits, so the
   base valuation and every scenario cost 20 each; a row with an unknown address cost
   nothing. *Verified 2026-11-11:* the same request costs 40 again, but the API first requires
   a balance of 20 per row and per scenario (60 for these two rows) and answers
   `402 insufficient_credits` when the balance is lower.
7. **`include=rd`.** The spec describes `include` as "extras such as rd". The SDK only offers
   `rd`. Other extras are unknown. *Verified 2026-11-08:* an unknown value
   (`include=unknown`) is accepted and ignored (`200`).
8. **Fields that are always present.** Fields the spec marks as required (for example
   `Valuation::valuation()`, `Building::unitsCount()`) are non-nullable in the SDK. A
   response without them throws `UnexpectedResponseTypeException`.
9. **Rate limit and retry headers.** `Retry-After` is read as seconds or as an HTTP date.
   The `X-RateLimit-*` headers are read into `RateLimit` (4.14).
   The limits per plan are set by Housepedia (https://huispedia.dev/en/docs/rate-limits/).
   *Verified 2026-11-08:* the limit is a bucket per account. Every response carries
   `X-RateLimit-Limit` (the size of the bucket), `X-RateLimit-Remaining` (requests left in
   the bucket) and `X-RateLimit-Reset` (seconds until the bucket is full). An empty bucket
   answers `429 rate_limited` with `Retry-After`. The live test records the headers and a
   burst of unthrottled requests in `build/live-findings.json`.
10. **Locator encoding.** Extensions are URL-encoded in the path (`A 1` becomes `A%201`). How
    the API treats extensions with spaces or special characters is unknown. *Verified
    2026-11-08:* `1076LJ-12-A%201` answers `404`, not a validation error, so the encoded
    extension is accepted as a locator. Multi-letter extensions work in any case
    (`2514KP-1-bis` finds Kerkstraat 1-BIS). The realtor suffix `hs` is not recognised:
    `5308KH-2-hs` answers `404` although Kerkstraat 2 exists next to 2-A and 2-B, and suggest
    returns nothing for `5308KH 2hs`. BAG stores a separate ground-floor home as extension
    `H` (`1076LJ-12-H`). No BAG address with a space in its extension has been found.

## 16. Manual test plan

Use a real API key on the Free plan. One run of the core checklist costs about 100 credits
and fits in the monthly 200 free credits.

Test homes:

| Postcode / number | BAG id             | Use                                                        |
|-------------------|--------------------|------------------------------------------------------------|
| `1076LJ` 12-2     | `0363200000028422` | one home: suggest, `find()`, properties, valuations        |
| `1076LJ` 12       |                    | three homes (12-1, 12-2, 12-H): `AddressAmbiguousException`, `findAll()` |

Record for each step: pass/fail, the request id (`meta()->requestId()` or the exception's
`requestId()`) and anything unexpected. "Limitation N" refers to the numbered list in
[section 15](#15-known-limitations).

### 1. Setup (free)

- [ ] `Client::create($key)` works without passing an HTTP client (discovery finds Guzzle or
      Symfony).
- [ ] `account()->details()` returns the plan, balance and entitlements. Note the balance.
- [ ] `catalog()` hydrates without an exception; check limitation 1 for new non-empty fields.
- [ ] A wrong key throws `AuthenticationException` with `errorCode()` `unauthenticated`.
- [ ] `Client::create($key, config: new Config(language: 'nl'))`: the `detail()` of an error is in Dutch.
- [ ] `Client::create()->account()->details()` throws `MissingApiKeyException`; `Client::create()->catalog()` works.

### 2. Addresses

- [ ] `suggest('1076LJ 12-2')` returns street and city (free).
- [ ] `suggest('Amazonenstraat 12, Amsterdam', limit: 3, types: [SuggestionType::Address])`.
- [ ] `withPostcode('1076LJ', 12, '2')->find()` matches the quickstart sample.
- [ ] `withBagId('0363200000028422')->find(includeRd: true)` returns `rd()`.
- [ ] `withPostcode('1076LJ', 12)->find()` throws `AddressAmbiguousException`; check that
      `candidates()` returns addresses (limitation 3).
- [ ] `withPostcode('1076LJ', 12)->findAll()` returns every home and the `meta()` with the credits
      charged (limitation 4).
- [ ] `reverse(52.34536, 4.86274)` returns addresses with `distanceInMeters()` and `areas()`.
- [ ] `meta()?->rateLimit()` of a call shows `remaining()` dropping between two quick calls.
- [ ] A non-existent address throws `NotFoundException` and costs no credits.
- [ ] An extension with a space or special character (limitation 10).

### 3. Properties

- [ ] `find()` with and without `[PropertyFieldGroup::Dimensions]`: only the requested groups
      are filled.
- [ ] `energyLabel()`, `woz()`, `woz(indexed: true)` (indexed values filled).
- [ ] `condition()`.
- [ ] `valuation()` and `valuation(new DateTimeImmutable('first day of -3 months'))`.
- [ ] `valuationPlus()`, `valuationHistory()`, `references(limit: 3)`: either a result or a
      `ForbiddenException` with `entitlement_required`.
- [ ] `transactions()`: `ForbiddenException` without a contract; with a contract, check the
      type of `price()` (limitation 2).
- [ ] `hasTransactions()`: `ForbiddenException` without a contract; with a contract, a `bool`
      at no cost, `false` for an unknown address.
- [ ] `surroundings()` and `environment()`; check the keys of `nearest()` and
      `countsInNeighbourhood()`.
- [ ] A home without data throws `DataUnavailableException` (422).

### 4. Buildings and areas

- [ ] `buildings()->withBagPandId($address->bagPandId())->find()` with the id from step 2.
- [ ] `areas()->lookupPostcode('1076LJ')` and `lookupCoordinates(52.34536, 4.86274)`.
- [ ] `withMunicipality('GM0363')->find()`, `->stats()`, `->trends()`.
- [ ] `withNeighbourhood('BU0363KH05')->demographics()`.
- [ ] `national()->trends(TrendMetric::AskingPricePerSquareMeter)`.
- [ ] `withPostcode4('1076')->find()` and `withPostcode6('1076LJ')->find()` return an `Area`.

### 5. Scenario valuations

- [ ] One row with one scenario; compare the credits charged with limitation 6.
- [ ] A row with an unknown address returns a `ScenarioValuation` with `error()`.

### 6. Raw requests and errors

- [ ] `get('/v5/properties/1076LJ-12-2/energy-label')` returns the same data as
      `energyLabel()`; `header('X-RateLimit-Remaining')` is filled.
- [ ] `get('/v5/does-not-exist')` throws an `ApiException` (404 or 405).
- [ ] With `new Config(retries: 0)`, sending more requests than `meta()?->rateLimit()?->limit()`
      at once throws `RateLimitException` with `retryAfter()`
      (limitation 9). With the default `Config`, the
      same burst succeeds because the requests are retried.
- [ ] `suggest('ab')` (shorter than 3 characters) throws `ValidationException` with
      `errors()` per field.
- [ ] With a PSR-16 cache in the `Config`, a second `addresses()->withPostcode('1076LJ', 12, '2')->find()`
      sends no request and costs no credits; `withoutCache()->find()` sends one; a second
      `valuation()` is charged again.

### 7. Account

- [ ] `account()->usage(groupBy: [UsageGrouping::Endpoint])` lists the calls of this run.
- [ ] `account()->requests(limit: 5)` and the next page through `meta()?->nextCursor()`.
- [ ] The balance dropped by the credits of the successful calls only.

## 17. Automated test status

| Tests      | PHPStan (max) | Coverage | Dependency sets verified locally         |
|------------|---------------|----------|------------------------------------------|
| 649 passed | no errors     | 100%     | prefer-lowest and prefer-stable, PHP 8.4 |

The `Unit` and `Integration` suites use mocked HTTP responses: fixtures generated from the
OpenAPI schemas, the quickstart valuation sample and the live catalog response.

The `Live` suite has 59 tests: 28 in `live-free`, 31 in `live-paid`. On 2026-11-08 a
`live` run passed 51 tests; 3 were incomplete (`transactions()`, `references()` and
`valuationPlus()` need a transaction-price contract). It cost 90 credits. The manual test
plan passed the same day; its items outside the live tests (client discovery, a valuation
with a date, a raw request compared with its typed method, a 422 for a home without an
energy label, a burst with and without retries, the balance) cost 10 credits.
On 2026-11-09 the `hasTransactions()` live test was incomplete (403 without a transaction-price
contract). The live cache tests passed on 2026-11-09. The new manual items passed the same
day: a cached second `find()` sends no request, `withoutCache()` sends one, and two
`valuation()` calls are both charged (18 credits).
On 2026-11-10 a `live-free` run passed 27 tests with `hasTransactions()` incomplete (403
without a transaction-price contract), including `Results` with `meta()` and `rateLimit()`.
On 2026-11-11 a `live-paid` run passed 27 tests; `transactions()`, `valuationPlus()` and
`references()` were incomplete (no transaction-price contract) and the scenario test first
failed with `PaymentRequiredException` because the balance was below what the API requires up
front (limitation 6); after a top-up it passed the same day. The manual test plan passed the same day (26 items, 27 credits) for the items
the live tests do not cover: setup, errors in English and Dutch, a missing key, suggestions,
the rate limit (`remaining()` dropping, a burst limited after 30 requests with `retries: 0`
and succeeding with retries), the ambiguous address, `findAll()` and `reverse()` with their
`meta()` and `areas()`, a 404, an extension with a space, a property with one field group,
the keys of `surroundings()`, a raw request against `energyLabel()`, caching (a cached
`find()` without request and without `rateLimit()`, `withoutCache()`, a dated `valuation()`
charged twice), usage and the request log. The balance dropped by exactly the credits
`meta()` reported. Not run: the scenario item and a 422 for a home without data.
PHP 8.6.0RC4 was verified locally in a container: dependencies install (Pest 5.3.5,
PHPUnit 13.4.5, paratest 7.27.5), PHPStan reports no errors and all 162 tests pass with
`failOnDeprecation`. PHP 8.3 is only covered by CI.
