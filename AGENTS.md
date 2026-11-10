# AGENTS.md

Guidance for coding agents working in `housepedia-data-intelligence`.

## Project

`henzeb/housepedia-data-intelligence` — a framework-independent PHP SDK for the Housepedia Data Intelligence API v5
(`https://huispedia.dev`). The Laravel integration lives in the separate repository
`../housepedia-data-intelligence-laravel` and depends on this package through Composer.

The product is branded **Housepedia** everywhere (classes, namespaces, docs, env vars). "huispedia" only appears in
API URLs, Composer keywords and API field names (`huispedia_url` is exposed as `housepediaUrl()`).

## Spec-driven

`SPEC.md` is the specification: numbered requirements, endpoint table, pricing, known limitations and the manual
test plan. Change `SPEC.md` first, then tests, then code. The API's own source of truth is
`https://huispedia.dev/openapi.json` plus the docs under `https://huispedia.dev/en/docs/` (start at `quickstart/`); never add endpoints,
parameters or response fields that are not documented there.

## Commands

```bash
composer install
composer test                                   # vendor/bin/pest
composer analyse                                # vendor/bin/phpstan analyse (level max, bleeding edge)
composer format                                 # vendor/bin/pint (psr12)
composer format-check                           # vendor/bin/pint --test
composer test-coverage-txt                      # pest --coverage (pcov)
vendor/bin/pest tests/Integration/EndpointsTest.php
vendor/bin/pest --filter='keeps builders immutable'
composer update --prefer-lowest                 # CI also runs the lowest dependency set
composer test-live-free                         # live API, free endpoints and errors only (0 credits)
composer test-live-paid                         # live API, only the tests that cost credits (about 90 credits)
composer test-live                              # live API, everything (about 90 credits)
php bin/generate-reference                      # rewrite docs/reference/*.md from the Contracts and snapshots/v5
php bin/generate-reference --check              # fail when docs/reference is out of date (ReferenceTest runs it)
```

`composer test` runs the `Unit` and `Integration` suites only (`defaultTestSuite` in `phpunit.xml`). The live
tests read `HOUSEPEDIA_API_KEY` (and optionally `HOUSEPEDIA_BASE_URL`, `HOUSEPEDIA_REQUEST_INTERVAL_MS`) from the
environment or from a gitignored `.env` (copy `.env.example`). Never run `composer test-live` or `composer test-live-paid`
without the user asking for it: they spend credits.

## Architecture

- `Client` creates resources; `Client::create()` discovers a PSR-18 client and PSR-17 factories via
  `php-http/discovery`. `Transport\Transport` is the only class doing HTTP: base URL, `Authorization: Bearer`,
  `Accept-Language`, query encoding (nulls dropped, bools as `true`/`false`), JSON bodies, decoding, and mapping
  error statuses/problem codes to `Exceptions\*`. It retries through `Client::retryUsing()` and caches
  `GET`/`HEAD` answers in `Config::cache()` (PSR-16) for the `max-age` of the answer. Resources and the client get
  `withMaxCacheAge()`/`withoutCache()` from `Concerns\ConfiguresCache`, which swaps the transport's `Config`.
- API versions: everything that follows the API's schemas lives per version in `V5\` (`V5\Resources`, `V5\DTO`,
  `V5\DTO\Catalog`, `V5\Enums`, `V5\Input`, `V5\Api`); `Client`, `Config`, `Transport`, `Response`, `RateLimit`,
  `Support`, `Locators`, `Concerns` and `Exceptions` are shared. Versions never extend each other's classes.
  `Contracts\` holds an interface per resource, DTO (`Contracts\DTO`), enum (`Contracts\Enums`, extending
  `BackedEnum`), `Contracts\Input\ScenarioRow` and `Contracts\Api`; the interfaces contain what every supported
  version offers. `Config` normalises `5`/`'5'`/`'v5'`/`'V5'` to `'V5'`, falls back to `Config::FALLBACK_VERSION`
  and returns the version's `Contracts\Api` from `api()`; `Client` creates resources through it and returns the
  interfaces, and `withVersion()` swaps the transport's `Config`. Resource methods take the `Contracts` enum and
  input types and return their own version's classes. `ContractsTest` requires every `V5\` class to have exactly
  the public methods of its interface. A new API version is a new namespace (copy, never extend); dropping
  something from an interface is a new SDK major.
- `V5\Resources\*` are immutable builders. `with*()` returns `new static(...)` (hence
  `@phpstan-consistent-constructor`); the locator is a constructor property. Each resource only exposes the
  locator methods its endpoints support — that restriction is the type-safety mechanism, so do not add locator
  methods to resources whose endpoints don't accept them. Home locators come from the `Concerns\WithPostcode` /
  `WithBagId` traits, which call the resource's `withLocator()`. A call without locator throws
  `MissingLocatorException` before any request.
- `Locators\Locator` / `AreaLocator` format path segments. `Locator` keeps two values: `__toString()` is
  URL-encoded for paths, `value()` is raw for JSON bodies (scenario rows).
- `Support\Payload` wraps decoded arrays with typed, dot-notation accessors (`string()`/`requireString()`,
  `nested()`, `list()`, `objectMap()`, …) that throw `UnexpectedResponseTypeException` with the field path.
  Every DTO hydrates through `static from(Payload $payload): self`, and the raw `Response` delegates its readers
  to it.
- `V5\DTO\*` mirror the OpenAPI response schemas one-to-one (schema → class name mapping, `data` lists renamed to
  `transactions()`, `references()`, `points()`). They were generated once from `openapi.json`; the generator
  is not in the repo, so edit them by hand following the existing pattern: private constructor
  properties, a getter per field named by the rule in `SPEC.md` 8.2 (units and abbreviations written out:
  `distance_m` → `distanceInMeters()`), required-and-non-null schema fields are non-nullable, dates are
  `DateTimeImmutable`, enum-like response values stay `string` (the API adds values within v5).
  `V5\DTO\Catalog\*` are hand-written from the live `/v5/catalog` response because the spec types it as free-form.
- List endpoints (`{data, meta}` answers) return `V5\DTO\Results<T>` (`data()`, `meta()`, iterable, countable),
  built with `Results::of($payload, Item::from(...))`; `reverse()` returns `V5\DTO\ReverseResults`, which adds
  `areas()`. The Laravel package maps exactly `Results` to its own `Results` Collection, so a method's
  return type is part of the cross-package contract. List items have no `meta` of their own.
- `RateLimit` is read from the `X-RateLimit-*` headers by `Response`, which hands it to its `Payload`; child
  payloads pass it on and `Meta::from()` exposes it as `Meta::rateLimit()`. The cache does not store those
  headers, so a cached answer has no rate limit.

## Tests

Pest. `Unit` and `Integration` use mocked HTTP only (`php-http/mock-client` + Guzzle PSR-7). Helpers in `tests/Pest.php`: `housepedia($http)`,
`transport($http)`, `jsonResponse()`, `jsonFixture()` (named so because Pest already defines `fixture()`),
`lastRequest()`, `queryOf()`. `tests/Fixtures/v5/*.json` are fully populated responses generated from the OpenAPI
schemas (plus the docs' quickstart valuation and a live catalog snapshot); `DtoHydrationTest` asserts every
getter of a fixture is non-null, so a new DTO field needs a fixture value and the same getter on its `Contracts\DTO` interface. `BuilderTest` asserts the exact set of
locator methods per resource. `OpenApiContractTest` checks `snapshots/v5/openapi.json` offline: every operation
must be mapped in `contractOperations()` to the closure that hydrates its whole answer, every field of every response schema (top-level
fields of list answers included) must be read by a getter, every
query parameter must be sent by a call in `contractQueryCalls()` (or be listed in `omittedQueryParameters()` with a
single allowed value), input enums must match their parameter patterns (or, without a pattern, the pinned parameter description), `scenarios()` must send every field of
the request body schema except `omittedBodyFields()`, and every answer must still hydrate with any single optional field
left out or any single nullable field set to `null` (`SPEC.md` 13.4a). After refreshing the snapshots it lists what the SDK misses.

`tests/Live` calls the real API (requirements in `SPEC.md` 13.6–13.12). Every file there is in the Pest group
`live` (set in `tests/Pest.php`) and gets exactly one of `->group('live-free')` and `->group('live-paid')`. Use
`liveClient()` (skips without a key; `apiKey: ''` for unauthenticated calls) and wrap paid calls in
`liveOrIncomplete()`, which marks the test incomplete on `DataUnavailableException`/`ForbiddenException`.
`Tests\Support\ThrottledClient` spaces the requests for the rate limit of the account (https://huispedia.dev/en/docs/rate-limits/). Checks of undocumented API
behaviour that the SDK handles either way record their answer with `liveFinding()` in `build/live-findings.json`;
read that file after a live run and update the known limitations in `SPEC.md`.

## API snapshots

`snapshots/v5/catalog.json` and `snapshots/v5/openapi.json` are the live catalog and OpenAPI document with
sorted keys. `.github/workflows/api-snapshot.yml` fetches them on working days at 17:17 UTC and fails with the diff when the API changed
(`SPEC.md` 13.5a); each run re-enables the workflow so the schedule survives 60 days without commits. Refresh them with

```bash
curl -fsS https://huispedia.dev/v5/catalog | python3 -c 'import json,sys; json.dump(json.load(sys.stdin), sys.stdout, indent=2, sort_keys=True, ensure_ascii=False); print()' > snapshots/v5/catalog.json
curl -fsS https://huispedia.dev/openapi.json | python3 -c 'import json,sys; json.dump(json.load(sys.stdin), sys.stdout, indent=2, sort_keys=True, ensure_ascii=False); print()' > snapshots/v5/openapi.json
```

and commit them together with the SDK, `SPEC.md`, docs and `tests/Fixtures` changes the diff calls for.

## Conventions

- No `final` or `readonly` classes or properties; properties are private without setters. No explanatory code
  comments.
- Docs in `docs/*.md` describe usage, not design rationale. `docs/reference/*.md` are generated: never edit them by
  hand, run `php bin/generate-reference` after changing a `Contracts` interface, a DTO's `from()` or the snapshots.
  A new resource method needs an entry in `REQUESTS` (or `BUILDERS`) of the generator; a new getter needs a
  description in `bin/reference-descriptions.php` when the OpenAPI document has none (`ReferenceTest` fails
  otherwise). Descriptions say what the value is, without prices or figures that Housepedia may change.
- Keep PHPStan clean at level max without baseline or ignores.
