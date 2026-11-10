<?php

use Tests\Support\ArrayCache;

it('caches answers for the max-age the api sends', function () {
    $cache = new ArrayCache();
    $client = liveClient(cache: $cache);

    $first = $client->addresses()->suggest('1076LJ 12');
    $second = $client->addresses()->suggest('1076LJ 12');

    expect($cache->values())->toHaveCount(1)
        ->and(array_values($cache->ttls())[0])->toBeGreaterThan(0)
        ->and($second->data())->toEqual($first->data())
        ->and($first->meta()?->rateLimit())->not->toBeNull()
        ->and($second->meta()?->rateLimit())->toBeNull();
})->group('live-free');

it('caches the catalog', function () {
    $cache = new ArrayCache();

    liveClient(cache: $cache)->catalog();

    expect($cache->values())->toHaveCount(1)
        ->and(array_values($cache->ttls())[0])->toBeGreaterThan(0);
})->group('live-free');
