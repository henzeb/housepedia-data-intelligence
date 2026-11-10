<?php

use Henzeb\Housepedia\Data\Intelligence\Exceptions\AuthenticationException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\MissingApiKeyException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\ValidationException;

it('throws for a wrong API key', function () {
    try {
        liveClient(apiKey: 'hp_invalid')->account()->details();
    } catch (AuthenticationException $exception) {
        expect($exception->errorCode())->toBe('unauthenticated');

        return;
    }

    test()->fail('Expected an AuthenticationException.');
})->group('live-free');

it('throws for a too short query', function () {
    liveClient()->addresses()->suggest('ab');
})->throws(ValidationException::class)->group('live-free');

it('throws for a scenario request without rows', function () {
    liveClient()->valuations()->scenarios([]);
})->throws(ValidationException::class)->group('live-free');

it('returns error details in the requested language', function () {
    $detail = function (?string $language): ?string {
        try {
            liveClient($language)->addresses()->suggest('ab');
        } catch (ValidationException $exception) {
            return $exception->detail();
        }

        return null;
    };

    $english = $detail('en');
    $dutch = $detail('nl');

    expect($english)->not->toBeNull()
        ->and($dutch)->not->toBeNull()
        ->and($dutch)->not->toBe($english);
})->group('live-free');

it('throws without an API key', function () {
    liveClient(apiKey: '')->account()->details();
})->throws(MissingApiKeyException::class)->group('live-free');
