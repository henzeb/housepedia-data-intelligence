<?php

use Henzeb\Housepedia\Data\Intelligence\Exceptions\ApiException;

it('sends a raw GET request', function () {
    $response = liveClient()->get('/v5/account');

    expect($response->status())->toBe(200)
        ->and($response->string('plan'))->not->toBeNull();
})->group('live-free');

it('throws for an unknown path', function () {
    liveClient()->get('/v5/does-not-exist');
})->throws(ApiException::class)->group('live-free');
