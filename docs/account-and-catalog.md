# Account and Catalog

Every method and every field of the response objects: [account reference](reference/account.md), [catalog reference](reference/catalog.md).

- [Account](#account)
- [Usage](#usage)
- [Request log](#request-log)
- [Catalog](#catalog)

<a name="account"></a>
## Account

```php
$account = $client->account()->details();

$account->plan();
$account->balance()->total();
$account->spendCap()->usedThisMonth();
$account->limits()->requestsPerSecond();
$account->entitlements(); // list<string>
```

<a name="usage"></a>
## Usage

Calls, credits and failures per day, endpoint or key:

```php
use Henzeb\Housepedia\Data\Intelligence\V5\Enums\UsageGrouping;

$usage = $client->account()->usage(
    from: new DateTimeImmutable('2026-10-01'),
    to: new DateTimeImmutable('2026-10-31'),
    groupBy: [UsageGrouping::Day, UsageGrouping::Endpoint],
); // Results<Usage>
```

<a name="request-log"></a>
## Request log

The request log covers the last 30 days. `lazy()->requests()` reads every entry of every page
and requests the next page only when the loop reaches it:

```php
foreach ($client->account()->lazy()->requests(status: 404) as $entry) {
    $entry->path();
    $entry->status();
    $entry->credits();
}
```

It takes the filters of `requests()`; `limit` is the number of entries per page (25 when
left out, at most 100). Each call starts at the first page.

`requests()` returns one page as `Results<RequestLogEntry>`; pass the `nextCursor()` of its
`meta()` to read the next one:

```php
$cursor = null;

do {
    $page = $client->account()->requests(status: 404, limit: 100, cursor: $cursor);

    foreach ($page as $entry) {
        $entry->path();
        $entry->status();
        $entry->credits();
    }

    $cursor = $page->meta()?->nextCursor();
} while ($cursor !== null);
```

<a name="catalog"></a>
## Catalog

`GET /v5/catalog` describes every endpoint with its price, the plans and the data sources, as
set by Housepedia.
It needs no API key.

```php
$catalog = $client->catalog();

$catalog->creditPriceInEuros();

foreach ($catalog->products() as $product) {
    $product->method();
    $product->path();
    $product->credits();
    $product->status();      // live or planned
    $product->name()->en();
}

$catalog->packs();           // list<Pack>
$catalog->freeTier()?->creditsPerMonth();
$catalog->limitsByPlan();    // array<string, PlanLimits|null>
```

The OpenAPI document types the catalog as a free-form object. The objects follow the fields
the live endpoint returns.
