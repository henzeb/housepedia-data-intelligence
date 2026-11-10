# Scenario Valuations

Every method and every field of the response objects: [valuations reference](reference/valuations.md).

`POST /v5/valuations/scenarios` values up to 100 homes with the characteristics you give,
and each named scenario against the same base. You pay per successful row and per scenario.

```php
use Henzeb\Housepedia\Data\Intelligence\V5\Input\Characteristics;
use Henzeb\Housepedia\Data\Intelligence\V5\Input\Scenario;
use Henzeb\Housepedia\Data\Intelligence\V5\Input\ScenarioRow;
use Henzeb\Housepedia\Data\Intelligence\Locators\Locator;

$rows = $client->valuations()->scenarios([
    new ScenarioRow(
        Locator::postcode('1076LJ', 12, '2'),
        new Characteristics(livingAreaInSquareMeters: 98, energyLabel: 'C'),
        [
            new Scenario('insulated', new Characteristics(energyLabel: 'A')),
            new Scenario('extension', new Characteristics(livingAreaInSquareMeters: 120)),
        ],
    ),
]); // Results<ScenarioValuation>

foreach ($rows as $row) {
    if ($row->error() !== null) {
        echo $row->error()->message();
        continue;
    }

    $row->valuation()?->value();

    foreach ($row->scenarios() ?? [] as $scenario) {
        $scenario->name();
        $scenario->valuation()?->value();
        $scenario->delta();
    }
}
```

`Characteristics` accepts every characteristic the endpoint supports as a named argument:
`livingAreaInSquareMeters`, `plotAreaInSquareMeters`, `volumeInCubicMeters`, `houseType`, `energyLabel`, `constructionYear`,
`rooms`, `bedrooms`, `bathrooms`, `separateToilets`, `gardenAreaInSquareMeters`,
`gardenOrientationInDegrees`, `dwellingsInBuilding`, `maintenanceOverall`,
`maintenanceModernity`, `maintenanceFinish` and `asOfDate`.
