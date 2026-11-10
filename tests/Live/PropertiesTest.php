<?php

use Henzeb\Housepedia\Data\Intelligence\V5\DTO\Condition;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\EnergyLabel;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\Environment;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\Property;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\References;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\Surroundings;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\Transactions;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\Valuation;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\ValuationHistory;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\ValuationPlus;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\Woz;
use Henzeb\Housepedia\Data\Intelligence\V5\Enums\PropertyFieldGroup;
use Henzeb\Housepedia\Data\Intelligence\V5\Resources\Properties;

function liveHome(): Properties
{
    return liveClient()->properties()->withPostcode('1076LJ', 12, '2');
}

it('finds a property', function () {
    $property = liveOrIncomplete(fn () => liveHome()->find());

    expect($property)->toBeInstanceOf(Property::class)
        ->and($property->address()->postcode())->toBe('1076LJ');
})->group('live-paid');

it('finds a property with selected field groups', function () {
    $property = liveOrIncomplete(fn () => liveHome()->find([PropertyFieldGroup::Dimensions]));

    expect($property)->toBeInstanceOf(Property::class)
        ->and($property->rooms())->toBeNull();
})->group('live-paid');

it('returns the energy label', function () {
    expect(liveOrIncomplete(fn () => liveHome()->energyLabel()))->toBeInstanceOf(EnergyLabel::class);
})->group('live-paid');

it('returns the WOZ values', function () {
    expect(liveOrIncomplete(fn () => liveHome()->woz()))->toBeInstanceOf(Woz::class);
})->group('live-paid');

it('returns the indexed WOZ values', function () {
    expect(liveOrIncomplete(fn () => liveHome()->woz(indexed: true)))->toBeInstanceOf(Woz::class);
})->group('live-paid');

it('returns the condition', function () {
    expect(liveOrIncomplete(fn () => liveHome()->condition()))->toBeInstanceOf(Condition::class);
})->group('live-paid');

it('returns the transactions', function () {
    $transactions = liveOrIncomplete(fn () => liveHome()->transactions());

    expect($transactions)->toBeInstanceOf(Transactions::class);

    liveFinding(2, 'transactions', livePriceTypes($transactions->transactions()));
})->group('live-paid');

it('checks whether the home has transactions', function () {
    expect(liveOrIncomplete(fn () => liveHome()->hasTransactions()))->toBeBool();
})->group('live-free');

it('returns the valuation', function () {
    expect(liveOrIncomplete(fn () => liveHome()->valuation()))->toBeInstanceOf(Valuation::class);
})->group('live-paid');

it('returns the valuation plus', function () {
    expect(liveOrIncomplete(fn () => liveHome()->valuationPlus()))->toBeInstanceOf(ValuationPlus::class);
})->group('live-paid');

it('returns the valuation history', function () {
    expect(liveOrIncomplete(fn () => liveHome()->valuationHistory()))->toBeInstanceOf(ValuationHistory::class);
})->group('live-paid');

it('returns the references', function () {
    $references = liveOrIncomplete(fn () => liveHome()->references(limit: 3));

    expect($references)->toBeInstanceOf(References::class);

    liveFinding(2, 'references', livePriceTypes($references->references()));
})->group('live-paid');

it('returns the surroundings', function () {
    expect(liveOrIncomplete(fn () => liveHome()->surroundings()))->toBeInstanceOf(Surroundings::class);
})->group('live-paid');

it('returns the environment', function () {
    expect(liveOrIncomplete(fn () => liveHome()->environment()))->toBeInstanceOf(Environment::class);
})->group('live-paid');
