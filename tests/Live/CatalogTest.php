<?php

use Henzeb\Housepedia\Data\Intelligence\V5\DTO\Catalog\Catalog;

it('returns the catalog without an API key', function () {
    $catalog = liveClient(apiKey: '')->catalog();

    expect($catalog)->toBeInstanceOf(Catalog::class)
        ->and($catalog->creditPriceInEuros())->toBeGreaterThan(0)
        ->and($catalog->products())->not->toBeEmpty();
})->group('live-free');

it('returns the catalog with an API key', function () {
    expect(liveClient()->catalog()->products())->not->toBeEmpty();
})->group('live-free');
