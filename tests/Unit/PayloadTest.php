<?php

use Henzeb\Housepedia\Data\Intelligence\V5\DTO\Location;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\UnexpectedResponseTypeException;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

beforeEach(function () {
    $this->payload = new Payload([
        'name' => 'Amsterdam',
        'count' => 12,
        'whole' => 12.0,
        'ratio' => 1.5,
        'flag' => false,
        'date' => '2026-10-26',
        'price' => 'withheld',
        'nested' => ['location' => ['lat' => 52, 'lon' => 4.8]],
        'items' => [['lat' => 1, 'lon' => 2], ['lat' => 3, 'lon' => 4]],
        'labels' => ['A', 'B'],
        'map' => ['school' => ['lat' => 1, 'lon' => 2], 'shop' => null],
        'counts' => ['schools' => 3, 'shops' => null],
        'sources' => ['noise' => 'RIVM'],
        'null' => null,
    ]);
});

it('reads typed values', function () {
    expect($this->payload->string('name'))->toBe('Amsterdam')
        ->and($this->payload->integer('count'))->toBe(12)
        ->and($this->payload->integer('whole'))->toBe(12)
        ->and($this->payload->float('count'))->toBe(12.0)
        ->and($this->payload->float('ratio'))->toBe(1.5)
        ->and($this->payload->boolean('flag'))->toBeFalse()
        ->and($this->payload->date('date')?->format('Y-m-d'))->toBe('2026-10-26')
        ->and($this->payload->integerOrString('price'))->toBe('withheld')
        ->and($this->payload->integerOrString('count'))->toBe(12)
        ->and($this->payload->strings('labels'))->toBe(['A', 'B'])
        ->and($this->payload->integerMap('counts'))->toBe(['schools' => 3, 'shops' => null])
        ->and($this->payload->stringMap('sources'))->toBe(['noise' => 'RIVM']);
});

it('returns null for missing and null values', function () {
    expect($this->payload->string('missing'))->toBeNull()
        ->and($this->payload->string('null'))->toBeNull()
        ->and($this->payload->has('null'))->toBeFalse()
        ->and($this->payload->has('name'))->toBeTrue()
        ->and($this->payload->list('missing', Location::from(...)))->toBe([])
        ->and($this->payload->nullableList('missing', Location::from(...)))->toBeNull()
        ->and($this->payload->nested('missing', Location::from(...)))->toBeNull();
});

it('reads values with dot notation', function () {
    expect($this->payload->float('nested.location.lat'))->toBe(52.0)
        ->and($this->payload->integer('items.1.lat'))->toBe(3)
        ->and($this->payload->string('nested.missing.deeper'))->toBeNull();
});

it('hydrates nested objects, lists and maps', function () {
    $location = $this->payload->requireNested('nested.location', Location::from(...));
    $items = $this->payload->list('items', Location::from(...));
    $map = $this->payload->objectMap('map', Location::from(...));

    expect($location->lat())->toBe(52.0)
        ->and($items)->toHaveCount(2)->each->toBeInstanceOf(Location::class)
        ->and($items[1]->lon())->toBe(4.0)
        ->and($map['school'])->toBeInstanceOf(Location::class)
        ->and($map['shop'])->toBeNull();
});

it('throws on unexpected types', function (string $method, string $key, string $message) {
    expect(fn () => $this->payload->{$method}($key))
        ->toThrow(UnexpectedResponseTypeException::class, $message);
})->with([
    ['string', 'count', 'Expected [count] to be of type string, got int.'],
    ['integer', 'ratio', 'Expected [ratio] to be of type int, got float.'],
    ['float', 'name', 'Expected [name] to be of type float, got string.'],
    ['boolean', 'count', 'Expected [count] to be of type bool, got int.'],
    ['date', 'name', 'Expected [name] to be of type date, got string.'],
    ['array', 'name', 'Expected [name] to be of type array, got string.'],
    ['integerOrString', 'ratio', 'Expected [ratio] to be of type int|string, got float.'],
    ['requireString', 'missing', 'Expected [missing] to be present in the response.'],
    ['requireInteger', 'missing', 'Expected [missing] to be present in the response.'],
    ['requireFloat', 'missing', 'Expected [missing] to be present in the response.'],
    ['requireBoolean', 'missing', 'Expected [missing] to be present in the response.'],
    ['requireDate', 'missing', 'Expected [missing] to be present in the response.'],
    ['strings', 'items', 'Expected [items.0] to be of type string, got array.'],
]);

it('reports nested paths in errors', function () {
    expect(fn () => $this->payload->requireNested('nested', fn (Payload $payload) => $payload->requireString('location.lat')))
        ->toThrow(UnexpectedResponseTypeException::class, 'Expected [nested.location.lat] to be of type string, got int.');

    expect(fn () => $this->payload->list('labels', Location::from(...)))
        ->toThrow(UnexpectedResponseTypeException::class, 'Expected [labels.0] to be of type array, got string.');
});

it('throws when a list of arrays holds another type', function () {
    expect(fn () => $this->payload->arrays('labels'))
        ->toThrow(UnexpectedResponseTypeException::class, 'Expected [labels.0] to be of type array, got string.');
});

it('throws when an object map holds another type', function () {
    expect(fn () => $this->payload->objectMap('sources', Location::from(...)))
        ->toThrow(UnexpectedResponseTypeException::class, 'Expected [sources.noise] to be of type array, got string.');
});

it('returns null for missing maps', function () {
    expect($this->payload->associative('missing'))->toBeNull()
        ->and($this->payload->integerMap('missing'))->toBeNull()
        ->and($this->payload->objectMap('missing', Location::from(...)))->toBe([]);
});

it('names the path of list items and map entries in errors', function () {
    $payload = new Payload(['outer' => [
        'data' => [['street' => 'Amazonenstraat'], ['street' => 12]],
        'map' => ['school' => ['name' => 7]],
        'keyed' => ['first' => ['street' => 12]],
        'names' => ['first' => 12],
        'rows' => ['first' => 12],
    ]]);
    $outer = $payload->object('outer');

    expect(fn () => $outer?->list('data', fn (Payload $item) => $item->requireString('street')))
        ->toThrow(UnexpectedResponseTypeException::class, 'Expected [outer.data.1.street] to be of type string, got int.')
        ->and(fn () => $outer?->objectMap('map', fn (Payload $item) => $item->requireString('name')))
        ->toThrow(UnexpectedResponseTypeException::class, 'Expected [outer.map.school.name] to be of type string, got int.')
        ->and(fn () => $outer?->list('keyed', fn (Payload $item) => $item->requireString('street')))
        ->toThrow(UnexpectedResponseTypeException::class, 'Expected [outer.keyed.0.street] to be of type string, got int.')
        ->and(fn () => $outer?->strings('names'))
        ->toThrow(UnexpectedResponseTypeException::class, 'Expected [outer.names.0] to be of type string, got int.')
        ->and(fn () => $outer?->arrays('rows'))
        ->toThrow(UnexpectedResponseTypeException::class, 'Expected [outer.rows.0] to be of type array, got int.');
});

it('does not read through a string with dot notation', function () {
    expect((new Payload(['name' => 'Amazonenstraat']))->value('name.0'))->toBeNull();
});

it('passes the rate limit to nested payloads', function () {
    $rateLimit = new Henzeb\Housepedia\Data\Intelligence\RateLimit(20, 19, 1);
    $payload = new Payload(['meta' => [], 'items' => [[]], 'map' => ['key' => []]], '', $rateLimit);
    $rateLimitOf = fn (Payload $item) => $item->rateLimit();

    expect($payload->rateLimit())->toBe($rateLimit)
        ->and($payload->object('meta')?->rateLimit())->toBe($rateLimit)
        ->and($payload->nested('meta', $rateLimitOf))->toBe($rateLimit)
        ->and($payload->list('items', $rateLimitOf))->toBe([$rateLimit])
        ->and($payload->objectMap('map', $rateLimitOf))->toBe(['key' => $rateLimit])
        ->and((new Payload([]))->rateLimit())->toBeNull();
});
