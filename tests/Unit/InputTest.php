<?php

use Henzeb\Housepedia\Data\Intelligence\V5\Input\Characteristics;
use Henzeb\Housepedia\Data\Intelligence\V5\Input\Scenario;
use Henzeb\Housepedia\Data\Intelligence\V5\Input\ScenarioRow;
use Henzeb\Housepedia\Data\Intelligence\Locators\Locator;

it('serializes a scenario row', function () {
    $row = new ScenarioRow(
        Locator::postcode('1076LJ', 12, 'A 1'),
        new Characteristics(livingAreaInSquareMeters: 98, energyLabel: 'C', asOfDate: new DateTimeImmutable('2026-10-01')),
        [
            new Scenario('insulated', new Characteristics(energyLabel: 'A')),
            new Scenario('as is'),
        ],
    );

    expect($row->toArray())->toBe([
        'locator' => '1076LJ-12-A 1',
        'living_area_m2' => 98,
        'energy_label' => 'C',
        'as_of_date' => '2026-10-01',
        'scenarios' => [
            ['name' => 'insulated', 'energy_label' => 'A'],
            ['name' => 'as is'],
        ],
    ]);
});

it('serializes every characteristic', function () {
    expect(array_keys((new Characteristics(1, 2, 3, 'detached', 'A', 1990, 5, 3, 1, 1, 50, 180, 1, 4, 3, 4, new DateTimeImmutable()))->toArray()))
        ->toBe([
            'living_area_m2', 'plot_area_m2', 'volume_m3', 'house_type', 'energy_label', 'construction_year',
            'rooms', 'bedrooms', 'bathrooms', 'separate_toilets', 'garden_area_m2', 'garden_orientation_degrees',
            'dwellings_in_building', 'maintenance_overall', 'maintenance_modernity', 'maintenance_finish', 'as_of_date',
        ]);
});
