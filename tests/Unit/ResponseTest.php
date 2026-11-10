<?php

use Henzeb\Housepedia\Data\Intelligence\V5\DTO\Meta;
use Henzeb\Housepedia\Data\Intelligence\Response;

it('exposes status, headers and typed body values', function () {
    $response = new Response(
        200,
        ['X-RateLimit-Remaining' => ['5'], 'Content-Type' => ['application/json']],
        [
            'data' => [['street' => 'Amazonenstraat', 'house_number' => 12, 'lat' => 52.3, 'residential' => true]],
            'meta' => ['request_id' => 'req_1', 'credits' => ['charged' => 2, 'balance' => 198]],
        ],
    );

    expect($response->status())->toBe(200)
        ->and($response->header('x-ratelimit-remaining'))->toBe('5')
        ->and($response->header('Retry-After'))->toBeNull()
        ->and($response->headers())->toHaveKey('Content-Type')
        ->and($response->string('data.0.street'))->toBe('Amazonenstraat')
        ->and($response->integer('data.0.house_number'))->toBe(12)
        ->and($response->float('data.0.lat'))->toBe(52.3)
        ->and($response->boolean('data.0.residential'))->toBeTrue()
        ->and($response->array('data'))->toHaveCount(1)
        ->and($response->has('meta.request_id'))->toBeTrue()
        ->and($response->raw())->toHaveKeys(['data', 'meta'])
        ->and($response->meta())->toBeInstanceOf(Meta::class)
        ->and($response->meta()?->credits()?->balance())->toBe(198);
});

it('returns no meta when the body has none', function () {
    expect((new Response(200, [], []))->meta())->toBeNull();
});

it('reads the rate limit into the response and the meta', function () {
    $response = new Response(
        200,
        ['X-RateLimit-Limit' => ['20'], 'X-RateLimit-Remaining' => ['19'], 'X-RateLimit-Reset' => ['1']],
        ['meta' => ['request_id' => 'req_1']],
    );

    expect($response->rateLimit()?->remaining())->toBe(19)
        ->and($response->meta()?->rateLimit())->toBe($response->rateLimit())
        ->and($response->payload()->rateLimit())->toBe($response->rateLimit());
});

it('has no rate limit without the headers', function () {
    $response = new Response(200, [], ['meta' => ['request_id' => 'req_1']]);

    expect($response->rateLimit())->toBeNull()
        ->and($response->meta()?->rateLimit())->toBeNull();
});
