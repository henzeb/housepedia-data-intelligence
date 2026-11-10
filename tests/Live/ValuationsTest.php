<?php

use Henzeb\Housepedia\Data\Intelligence\V5\DTO\ScenarioValuation;
use Henzeb\Housepedia\Data\Intelligence\V5\Input\Characteristics;
use Henzeb\Housepedia\Data\Intelligence\V5\Input\Scenario;
use Henzeb\Housepedia\Data\Intelligence\V5\Input\ScenarioRow;
use Henzeb\Housepedia\Data\Intelligence\Locators\Locator;

it('values scenarios per row', function () {
    $client = liveClient();
    $before = $client->account()->details()->balance()->total();

    $valuations = liveOrIncomplete(fn () => $client->valuations()->scenarios([
        new ScenarioRow(
            Locator::postcode('1076LJ', 12, '2'),
            scenarios: [new Scenario('extra-bedroom', new Characteristics(bedrooms: 3))],
        ),
        new ScenarioRow(Locator::postcode('1076LJ', 9999)),
    ]));

    expect($valuations->data())->toHaveCount(2)
        ->each->toBeInstanceOf(ScenarioValuation::class)
        ->and($valuations->data()[1]->error())->not->toBeNull();

    $charged = $before - $client->account()->details()->balance()->total();

    liveFinding(6, 'one row with one scenario and one failed row', ['charged' => $charged]);

    if ($charged === 0) {
        test()->markTestIncomplete('The balance did not change yet; check limitation 6 in the request log.');
    }

    expect($charged)->toBeIn([20, 40]);
})->group('live-paid');
