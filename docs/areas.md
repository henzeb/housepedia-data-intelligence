# Areas

Every method and every field of the response objects: [areas reference](reference/areas.md).

- [Selecting an area](#selecting-an-area)
- [Lookup](#lookup)
- [Area details](#area-details)
- [Statistics](#statistics)
- [Price index](#price-index)
- [Demographics](#demographics)

<a name="selecting-an-area"></a>
## Selecting an area

```php
$client->areas()->withNeighbourhood('BU0363KH05');
$client->areas()->withDistrict('WK0363KH');
$client->areas()->withMunicipality('GM0363');
$client->areas()->withProvince('PV27');
$client->areas()->national();
```

The codes are CBS codes. Every address answer contains them, or use the lookup below.
Statistics, price index and demographics are available for these levels.

Postcode areas return a `PostcodeArea`, which offers `find()` only:

```php
$area = $client->areas()->withPostcode4('1076')->find();
$area = $client->areas()->withPostcode6('1076LJ')->find();
```

<a name="lookup"></a>
## Lookup

`GET /v5/areas/lookup` returns the neighbourhood, district, municipality and province of a
postcode or coordinate:

```php
$areas = $client->areas()->lookupPostcode('1076LJ');
$areas = $client->areas()->lookupCoordinates(52.3453, 4.8627);
$areas = $client->areas()->lookupCoordinates('52.34536032', '4.86274012');

$areas->municipality()?->code(); // GM0363
$areas->neighbourhood()?->name();
```

<a name="area-details"></a>
## Area details

```php
$area = $client->areas()->withMunicipality('GM0363')->find();

$area->name();
$area->parents();  // list<AreaParent>
$area->geometry(); // array<string, mixed>|null
```

<a name="statistics"></a>
## Statistics

Market figures over the last twelve months, housing stock and environment averages:

```php
$stats = $client->areas()->withNeighbourhood('BU0363KH05')->stats();

$stats->market()->averageTransactionPricePerSquareMeter();
$stats->stock()?->medianConstructionYear();
$stats->environment()?->sharePublicGreen();
```

<a name="price-index"></a>
## Price index

A monthly series of the transaction price per m² (default) or the asking price per m²:

```php
use Henzeb\Housepedia\Data\Intelligence\V5\Enums\TrendMetric;

$trends = $client->areas()
    ->withMunicipality('GM0363')
    ->trends(TrendMetric::AskingPricePerSquareMeter, from: new DateTimeImmutable('2022-01-01'));

foreach ($trends->points() as $point) {
    $point->date();
    $point->value();
    $point->yearOverYearPercentage();
}

$trends->parents(); // the same series for the parent areas
```

<a name="demographics"></a>
## Demographics

```php
$demographics = $client->areas()->withDistrict('WK0363KH')->demographics();

$demographics->population();
$demographics->households()?->averageSize();
$demographics->age()?->share65Plus();
```
