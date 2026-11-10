<?php

use Henzeb\Housepedia\Data\Intelligence\V5\DTO\Building;

it('finds the building of an address', function () {
    $client = liveClient();

    $bagPandId = $client->addresses()->withPostcode('1076LJ', 12, '2')->find()->bagPandId();

    expect($bagPandId)->toBeString();

    $building = liveOrIncomplete(fn () => $client->buildings()->withBagPandId((string)$bagPandId)->find());

    expect($building)->toBeInstanceOf(Building::class)
        ->and($building->bagPandId())->toBe($bagPandId);
})->group('live-paid');
