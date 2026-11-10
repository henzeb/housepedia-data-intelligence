# Properties

Every method and every field of the response objects: [properties reference](reference/properties.md).

Every property endpoint takes a [locator](getting-started.md#locators):

```php
$property = $client->properties()->withPostcode('1076LJ', 12, '2');
$property = $client->properties()->withBagId('0363200000028422');
```

| Method                  | Endpoint                                         | Returns            |
|-------------------------|--------------------------------------------------|--------------------|
| `find()`                | `GET /v5/properties/{locator}`                   | `Property`         |
| `energyLabel()`         | `GET /v5/properties/{locator}/energy-label`      | `EnergyLabel`      |
| `woz()`                 | `GET /v5/properties/{locator}/woz`               | `Woz`              |
| `condition()`           | `GET /v5/properties/{locator}/condition`         | `Condition`        |
| `transactions()`        | `GET /v5/properties/{locator}/transactions`      | `Transactions`     |
| `hasTransactions()`     | `HEAD /v5/properties/{locator}/transactions`     | `bool`             |
| `valuation()`           | `GET /v5/properties/{locator}/valuation`         | `Valuation`        |
| `valuationPlus()`       | `GET /v5/properties/{locator}/valuation/plus`    | `ValuationPlus`    |
| `valuationHistory()`    | `GET /v5/properties/{locator}/valuation/history` | `ValuationHistory` |
| `references()`          | `GET /v5/properties/{locator}/references`        | `References`       |
| `surroundings()`        | `GET /v5/properties/{locator}/surroundings`      | `Surroundings`     |
| `environment()`         | `GET /v5/properties/{locator}/environment`       | `Environment`      |

Each result contains the `address()` of the home.

## Characteristics

```php
use Henzeb\Housepedia\Data\Intelligence\V5\Enums\PropertyFieldGroup;

$property = $client->properties()->withPostcode('1076LJ', 12, '2')->find();

$property->type()?->houseType();
$property->dimensions()?->livingAreaInSquareMeters();
$property->rooms()?->bedrooms();
$property->ownership()?->leasehold()?->yearlyCanon();
```

Request only some groups, and optionally RD coordinates:

```php
$client->properties()
    ->withPostcode('1076LJ', 12, '2')
    ->find([PropertyFieldGroup::Dimensions, PropertyFieldGroup::Rooms], includeRd: true);
```

## WOZ

```php
$woz = $client->properties()->withPostcode('1076LJ', 12, '2')->woz(indexed: true);

$woz->latest()?->value();
$woz->latest()?->indexedValue(); // at today's price level
$woz->values();                  // list<WozValue>
```

## Valuation

```php
$valuation = $client->properties()->withPostcode('1076LJ', 12, '2')->valuation();

$valuation->valuation()->value();    // 888264
$valuation->valuation()->lower();    // 838099
$valuation->valuation()->upper();    // 941431
$valuation->confidence()?->class();  // high
```

The actual value lies between `lower()` and `upper()` with 80% certainty. Pass a date up to
six months back for the value at that month's price level:

```php
$client->properties()->withPostcode('1076LJ', 12, '2')->valuation(new DateTimeImmutable('2026-06-01'));
```

`valuationPlus()` adds reference homes, neighbourhood market figures and the latest WOZ
value. `valuationHistory()` returns a monthly series:

```php
$history = $client->properties()
    ->withPostcode('1076LJ', 12, '2')
    ->valuationHistory(from: new DateTimeImmutable('2024-01-01'));

foreach ($history->points() as $point) {
    echo $point->date()->format('Y-m'), ': ', $point->value();
}
```

## Reference homes

```php
use Henzeb\Housepedia\Data\Intelligence\V5\Enums\ReferenceSort;

$references = $client->properties()
    ->withPostcode('1076LJ', 12, '2')
    ->references(limit: 5, maxDistanceInMeters: 1000, sameType: true, sort: ReferenceSort::Distance);

foreach ($references->references() as $reference) {
    $reference->address()->street();
    $reference->price();
    $reference->distanceInMeters();
}
```

| Argument              | Range       | Default    |
|-----------------------|-------------|------------|
| `limit`               | 3 – 30      | 10         |
| `maxDistanceInMeters`         | 50 – 20000  | 2000       |
| `maxAgeMonths`        | 1 – 120     | 24         |
| `sameType`            |             | `true`     |
| `minLivingAreaInSquareMeters`, `maxLivingAreaInSquareMeters` | m² |         |
| `minConstructionYear`, `maxConstructionYear` | |  |
| `asOfDate`            |             | today      |
| `sort`                | similarity, distance, date | similarity |

## Transactions

Transaction prices require a contract with Housepedia. Kadaster prices are returned as an
integer, or as a string when the API returns one.

```php
foreach ($client->properties()->withPostcode('1076LJ', 12, '2')->transactions()->transactions() as $transaction) {
    $transaction->transactionDate();
    $transaction->price();
}
```

`hasTransactions()` checks whether a home has transactions. It returns `false` when
it has none, and also for an unknown address.

```php
if ($client->properties()->withPostcode('1076LJ', 12, '2')->hasTransactions()) {
    // ...
}
```

## Surroundings and environment

```php
$surroundings = $client->properties()->withPostcode('1076LJ', 12, '2')->surroundings();

$surroundings->nearest();              // array<string, NearestPlace|null>
$surroundings->countsInNeighbourhood(); // array<string, int|null>|null

$environment = $client->properties()->withPostcode('1076LJ', 12, '2')->environment();

$environment->noise()?->roadLdenInDecibels();
$environment->foundation()?->atRisk();
```
