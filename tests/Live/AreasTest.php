<?php

use Henzeb\Housepedia\Data\Intelligence\V5\DTO\Area;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\AreaStatistics;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\AreaTrends;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\Demographics;
use Henzeb\Housepedia\Data\Intelligence\V5\Enums\TrendMetric;

it('looks up the areas of a postcode', function () {
    $areas = liveClient()->areas()->lookupPostcode('1076LJ');

    expect($areas->municipality()?->code())->toBe('GM0363');
})->group('live-paid');

it('looks up the areas of coordinates', function () {
    $areas = liveClient()->areas()->lookupCoordinates(52.34536, 4.86274);

    expect($areas->municipality()?->code())->toBe('GM0363');
})->group('live-paid');

it('finds a municipality', function () {
    $area = liveOrIncomplete(fn () => liveClient()->areas()->withMunicipality('GM0363')->find());

    expect($area)->toBeInstanceOf(Area::class)
        ->and($area->code())->toBe('GM0363');
})->group('live-paid');

it('returns the statistics of a municipality', function () {
    expect(liveOrIncomplete(fn () => liveClient()->areas()->withMunicipality('GM0363')->stats()))
        ->toBeInstanceOf(AreaStatistics::class);
})->group('live-paid');

it('returns the trends of a municipality', function () {
    expect(liveOrIncomplete(fn () => liveClient()->areas()->withMunicipality('GM0363')->trends()))
        ->toBeInstanceOf(AreaTrends::class);
})->group('live-paid');

it('returns the demographics of a neighbourhood', function () {
    expect(liveOrIncomplete(fn () => liveClient()->areas()->withNeighbourhood('BU0363KH05')->demographics()))
        ->toBeInstanceOf(Demographics::class);
})->group('live-paid');

it('returns the national trends for one metric', function () {
    expect(liveOrIncomplete(fn () => liveClient()->areas()->national()->trends(TrendMetric::AskingPricePerSquareMeter)))
        ->toBeInstanceOf(AreaTrends::class);
})->group('live-paid');

it('finds a postcode4 area', function () {
    $area = liveOrIncomplete(fn () => liveClient()->areas()->withPostcode4('1076')->find());

    expect($area)->toBeInstanceOf(Area::class)
        ->and($area->code())->toBe('1076');
})->group('live-paid');

it('finds a postcode6 area', function () {
    $area = liveOrIncomplete(fn () => liveClient()->areas()->withPostcode6('1076LJ')->find());

    expect($area)->toBeInstanceOf(Area::class)
        ->and($area->code())->toBe('1076LJ');
})->group('live-paid');
