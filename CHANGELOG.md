# Changelog

All notable changes to `henzeb/housepedia-data-intelligence` are documented in this file. The
project follows [semantic versioning](https://semver.org).

## [1.0.0] - 2026-11-11

First release.

- A typed client for every endpoint of the Housepedia Data Intelligence API: addresses,
  properties, buildings, areas, scenario valuations, account and catalog, with immutable
  builders for the address and area locators. See [getting started](docs/getting-started.md).
- API versions: version 5 by default, pinned with `Config(version: 5)` or `withVersion()` on
  the client; the client returns version-independent interfaces from `Contracts`. See
  [API versions](docs/getting-started.md#api-versions).
- List endpoints return `Results` with the list (`data()`) and the `meta()` of the answer;
  `Results` can be iterated and counted. `reverse()` returns `ReverseResults`, which adds the
  `areas()` of the coordinate. See [lists](docs/getting-started.md#lists).
- `meta()` with the request id, the credits charged, the balance and the rate limit of the
  account. See [metadata](docs/getting-started.md#metadata).
- Coordinates as a float or as a decimal string, which is sent exactly as given. See
  [reverse geocoding](docs/addresses.md#reverse-geocoding).
- `Properties::hasTransactions()` checks for free whether a home has transactions. It needs a
  transaction-price contract.
- `account()->lazy()->requests()` reads every entry of the request log, page by page, as a
  generator. See [request log](docs/account-and-catalog.md#request-log).
- Caching of answers in a PSR-16 cache for the time the API allows. See
  [caching](docs/caching.md).
- An exception per API error, and retries of rate-limited calls. See [errors](docs/errors.md).
- A reference of every method and response object, per area. See
  [reference](docs/reference/shared.md).
- `get()` and `post()` for endpoints without a typed method. See
  [raw requests](docs/raw-requests.md).

[1.0.0]: https://github.com/henzeb/housepedia-data-intelligence/releases/tag/v1.0.0
