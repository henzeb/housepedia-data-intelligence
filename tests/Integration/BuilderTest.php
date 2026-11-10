<?php

use Henzeb\Housepedia\Data\Intelligence\Concerns\WithBagId;
use Henzeb\Housepedia\Data\Intelligence\Concerns\WithPostcode;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\MissingLocatorException;
use Henzeb\Housepedia\Data\Intelligence\V5\Resources\Account;
use Henzeb\Housepedia\Data\Intelligence\V5\Resources\Addresses;
use Henzeb\Housepedia\Data\Intelligence\V5\Resources\Areas;
use Henzeb\Housepedia\Data\Intelligence\V5\Resources\Buildings;
use Henzeb\Housepedia\Data\Intelligence\V5\Resources\LazyAccount;
use Henzeb\Housepedia\Data\Intelligence\V5\Resources\PostcodeArea;
use Henzeb\Housepedia\Data\Intelligence\V5\Resources\Properties;
use Henzeb\Housepedia\Data\Intelligence\V5\Resources\Valuations;
use Http\Mock\Client as MockClient;

beforeEach(function () {
    $this->http = new MockClient();
    $this->http->setDefaultResponse(jsonResponse(jsonFixture('getProperty')));
    $this->client = housepedia($this->http);
});

it('keeps builders immutable', function () {
    $properties = $this->client->properties();

    $first = $properties->withPostcode('1076LJ', 12, '2');
    $second = $properties->withPostcode('2511BT', 10);

    $first->find();
    $second->find();

    expect($first)->not->toBe($properties)
        ->and($second)->not->toBe($first)
        ->and(array_map(fn ($request) => $request->getUri()->getPath(), $this->http->getRequests()))
        ->toBe(['/v5/properties/1076LJ-12-2', '/v5/properties/2511BT-10'])
        ->and(fn () => $properties->find())->toThrow(MissingLocatorException::class);
});

it('replaces the previous locator', function () {
    $this->client->properties()->withPostcode('1076LJ', 12, '2')->withBagId('0363200000028422')->find();

    expect(lastRequest($this->http)->getUri()->getPath())->toBe('/v5/properties/bag:0363200000028422');
});

it('keeps area and building builders immutable', function () {
    $areas = $this->client->areas();
    $municipality = $areas->withMunicipality('GM0363');
    $buildings = $this->client->buildings();
    $building = $buildings->withBagPandId('0363100012345678');

    expect($municipality)->not->toBe($areas)
        ->and(fn () => $areas->stats())->toThrow(MissingLocatorException::class)
        ->and($building)->not->toBe($buildings)
        ->and(fn () => $buildings->find())->toThrow(MissingLocatorException::class);
});

it('requires a locator', function (Closure $call, string $message) {
    expect(fn () => $call($this->client))->toThrow(MissingLocatorException::class, $message)
        ->and($this->http->getRequests())->toBe([]);
})->with([
    [fn ($c) => $c->addresses()->find(), Addresses::class . '::find() requires a locator'],
    [fn ($c) => $c->addresses()->findAll(), Addresses::class . '::findAll() requires a locator'],
    [fn ($c) => $c->properties()->find(), Properties::class . '::find() requires a locator'],
    [fn ($c) => $c->properties()->woz(), Properties::class . '::woz() requires a locator'],
    [fn ($c) => $c->properties()->valuation(), Properties::class . '::valuation() requires a locator'],
    [fn ($c) => $c->properties()->hasTransactions(), Properties::class . '::hasTransactions() requires a locator'],
    [fn ($c) => $c->buildings()->find(), Buildings::class . '::find() requires a locator'],
    [fn ($c) => $c->areas()->stats(), Areas::class . '::stats() requires a locator'],
    [fn ($c) => $c->areas()->trends(), Areas::class . '::trends() requires a locator'],
]);

it('only exposes supported locators', function (string $class, array $allowed) {
    $locators = array_values(array_filter(
        array_map(fn (ReflectionMethod $method) => $method->getName(), (new ReflectionClass($class))->getMethods(ReflectionMethod::IS_PUBLIC)),
        fn (string $name) => (str_starts_with($name, 'with') || $name === 'national') && !in_array($name, ['withMaxCacheAge', 'withoutCache'], true),
    ));

    sort($locators);
    sort($allowed);

    expect($locators)->toBe($allowed);
})->with([
    [Addresses::class, ['withBagId', 'withLocator', 'withPostcode']],
    [Properties::class, ['withBagId', 'withLocator', 'withPostcode']],
    [Buildings::class, ['withBagPandId']],
    [Areas::class, ['withArea', 'withDistrict', 'withMunicipality', 'withNeighbourhood', 'withPostcode4', 'withPostcode6', 'withProvince', 'national']],
    [PostcodeArea::class, []],
    [Valuations::class, []],
    [Account::class, []],
    [LazyAccount::class, []],
]);

it('uses the home locator traits', function () {
    expect(class_uses(Addresses::class))->toContain(WithPostcode::class, WithBagId::class)
        ->and(class_uses(Properties::class))->toContain(WithPostcode::class, WithBagId::class);
});

it('offers only find() on postcode areas', function () {
    $areas = housepedia(new MockClient())->areas();
    $area = $areas->withPostcode4('1076');

    expect($area)->toBeInstanceOf(PostcodeArea::class)
        ->and(get_class_methods($area))->toBe(['__construct', 'find', 'withMaxCacheAge', 'withoutCache'])
        ->and($areas->withPostcode6('1076LJ'))->toBeInstanceOf(PostcodeArea::class);
});
