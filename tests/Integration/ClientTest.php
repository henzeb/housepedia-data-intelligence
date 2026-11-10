<?php

use Henzeb\Housepedia\Data\Intelligence\Client;
use Henzeb\Housepedia\Data\Intelligence\Config;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\AddressSuggestion;
use Http\Mock\Client as MockClient;

it('creates a client with a discovered http client', function () {
    expect(Client::create('hp_test'))->toBeInstanceOf(Client::class);
});

it('creates a client with a given http client and options', function () {
    $http = new MockClient();
    $http->addResponse(jsonResponse(jsonFixture('suggestAddresses')));

    $suggestions = Client::create('hp_key', $http, new Config(baseUrl: 'https://sandbox.test', language: 'en'))
        ->addresses()
        ->suggest('1076LJ 12-2');

    $request = lastRequest($http);

    expect($suggestions->data())->toBeList()->each->toBeInstanceOf(AddressSuggestion::class)
        ->and((string)$request->getUri())->toStartWith('https://sandbox.test/v5/addresses/suggest')
        ->and($request->getHeaderLine('Authorization'))->toBe('Bearer hp_key')
        ->and($request->getHeaderLine('Accept-Language'))->toBe('en');
});

it('returns street and city for a postcode and house number', function () {
    $http = new MockClient();
    $http->addResponse(jsonResponse(['data' => [[
        'type' => 'address',
        'label' => 'Amazonenstraat 12-2, 1076LJ Amsterdam',
        'postcode' => '1076LJ',
        'house_number' => 12,
        'house_number_ext' => '2',
        'street' => 'Amazonenstraat',
        'city' => 'Amsterdam',
    ]]]));

    $suggestion = housepedia($http)->addresses()->suggest('1076LJ 12-2')->data()[0];

    expect($suggestion->street())->toBe('Amazonenstraat')
        ->and($suggestion->city())->toBe('Amsterdam')
        ->and($suggestion->houseNumberExt())->toBe('2')
        ->and($suggestion->location())->toBeNull();
});

it('works without laravel', function () {
    $composer = json_decode((string)file_get_contents(__DIR__ . '/../../composer.json'), true);

    expect(array_filter(
        array_keys($composer['require']),
        fn (string $package) => str_starts_with($package, 'illuminate/') || str_starts_with($package, 'laravel/'),
    ))->toBe([]);

    $sources = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/../../src', FilesystemIterator::SKIP_DOTS));

    foreach ($sources as $file) {
        expect((string)file_get_contents($file->getPathname()))->not->toContain('Illuminate\\');
    }
});

it('uses the version of the config', function () {
    $client = housepedia(new MockClient(), new Config(version: 'v5'));

    expect($client->version())->toBe('V5')
        ->and($client->addresses())->toBeInstanceOf(Henzeb\Housepedia\Data\Intelligence\V5\Resources\Addresses::class)
        ->and($client->properties())->toBeInstanceOf(Henzeb\Housepedia\Data\Intelligence\V5\Resources\Properties::class)
        ->and($client->buildings())->toBeInstanceOf(Henzeb\Housepedia\Data\Intelligence\V5\Resources\Buildings::class)
        ->and($client->areas())->toBeInstanceOf(Henzeb\Housepedia\Data\Intelligence\V5\Resources\Areas::class)
        ->and($client->valuations())->toBeInstanceOf(Henzeb\Housepedia\Data\Intelligence\V5\Resources\Valuations::class)
        ->and($client->account())->toBeInstanceOf(Henzeb\Housepedia\Data\Intelligence\V5\Resources\Account::class);
});

it('uses the fallback version without a version in the config', function () {
    expect(housepedia(new MockClient())->version())->toBe(Config::FALLBACK_VERSION);
});

it('sets the version at runtime on a copy', function () {
    $http = new MockClient();
    $http->setDefaultResponse(jsonResponse(jsonFixture('suggestAddresses')));
    $client = housepedia($http, new Config(retries: 0));
    $copy = $client->withVersion(5);

    $copy->addresses()->suggest('1076LJ');

    expect($copy)->not->toBe($client)
        ->and($copy->version())->toBe('V5')
        ->and($client->version())->toBe('V5')
        ->and(lastRequest($http)->getUri()->getPath())->toBe('/v5/addresses/suggest');
});

it('keeps the cache settings when the version is set', function () {
    $cache = new Tests\Support\ArrayCache();
    $http = new MockClient();
    $http->setDefaultResponse(jsonResponse(jsonFixture('suggestAddresses'), 200, ['Cache-Control' => 'max-age=60']));
    $client = housepedia($http, new Config(cache: $cache))->withoutCache()->withVersion('V5');

    $client->addresses()->suggest('1076LJ');

    expect($cache->values())->toBe([]);
});

it('throws for an unsupported version at runtime', function () {
    housepedia(new MockClient())->withVersion('v6');
})->throws(Henzeb\Housepedia\Data\Intelligence\Exceptions\UnsupportedVersionException::class);
