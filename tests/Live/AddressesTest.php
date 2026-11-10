<?php

use Henzeb\Housepedia\Data\Intelligence\V5\DTO\Address;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\AddressSuggestion;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\AreaHierarchy;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\ReverseAddress;
use Henzeb\Housepedia\Data\Intelligence\V5\Enums\SuggestionType;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\AddressAmbiguousException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\NotFoundException;

it('suggests addresses', function () {
    $suggestions = liveClient()->addresses()->suggest('1076LJ 12-2');

    expect($suggestions->data())->toHaveCount(1)
        ->each->toBeInstanceOf(AddressSuggestion::class)
        ->and($suggestions->data()[0]->bagId())->toBe('0363200000028422')
        ->and($suggestions->data()[0]->street())->toBe('Amazonenstraat')
        ->and($suggestions->data()[0]->city())->toBe('Amsterdam')
        ->and($suggestions->meta()?->requestId())->not->toBeNull()
        ->and($suggestions->meta()?->rateLimit())->not->toBeNull();
})->group('live-free');

it('suggests addresses with a limit and types', function () {
    $suggestions = liveClient()->addresses()->suggest('Amazonenstraat 12, Amsterdam', limit: 3, types: [SuggestionType::Address]);

    expect(count($suggestions))->toBeLessThanOrEqual(3);

    foreach ($suggestions as $suggestion) {
        expect($suggestion->type())->toBe(SuggestionType::Address->value);
    }
})->group('live-free');

it('throws for an ambiguous address', function () {
    try {
        liveClient()->addresses()->withPostcode('1076LJ', 12)->find();
    } catch (AddressAmbiguousException $exception) {
        expect($exception->candidates())->not->toBeEmpty()
            ->each->toBeInstanceOf(Address::class);

        return;
    }

    test()->fail('Expected an AddressAmbiguousException.');
})->group('live-free');

it('throws for an unknown address', function () {
    liveClient()->addresses()->withPostcode('1076LJ', 9999)->find();
})->throws(NotFoundException::class)->group('live-free');

it('finds an address by postcode', function () {
    $address = liveClient()->addresses()->withPostcode('1076LJ', 12, '2')->find();

    expect($address->postcode())->toBe('1076LJ')
        ->and($address->houseNumber())->toBe(12);
})->group('live-paid');

it('finds an address by BAG id with RD coordinates', function () {
    $address = liveClient()->addresses()->withBagId('0363200000028422')->find(includeRd: true);

    expect($address->bagId())->toBe('0363200000028422')
        ->and($address->rd())->not->toBeNull();
})->group('live-paid');

it('finds every home at a postcode and house number', function () {
    $addresses = liveClient()->addresses()->withPostcode('1076LJ', 12)->findAll();

    expect($addresses->data())->not->toBeEmpty()
        ->each->toBeInstanceOf(Address::class)
        ->and($addresses->meta()?->credits())->not->toBeNull();
})->group('live-paid');

it('reverse geocodes coordinates', function () {
    $addresses = liveClient()->addresses()->reverse(52.34536, 4.86274, limit: 3);

    expect($addresses->data())->not->toBeEmpty()
        ->each->toBeInstanceOf(ReverseAddress::class)
        ->and($addresses->areas())->toBeInstanceOf(AreaHierarchy::class)
        ->and($addresses->meta()?->credits())->not->toBeNull();
})->group('live-paid');
