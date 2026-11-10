<?php

use Henzeb\Housepedia\Data\Intelligence\RateLimit;

it('reads the rate limit headers', function () {
    $rateLimit = RateLimit::fromHeaders(
        ['x-ratelimit-limit' => ['20'], 'X-RateLimit-Remaining' => [' 19 '], 'X-RATELIMIT-RESET' => ['3']],
        new DateTimeImmutable('2026-11-10T12:00:00+00:00'),
    );

    expect($rateLimit?->limit())->toBe(20)
        ->and($rateLimit?->remaining())->toBe(19)
        ->and($rateLimit?->resetInSeconds())->toBe(3)
        ->and($rateLimit?->resetsAt()->format(DATE_ATOM))->toBe('2026-11-10T12:00:03+00:00');
});

it('returns no rate limit without all three headers as whole numbers', function (array $headers) {
    expect(RateLimit::fromHeaders($headers))->toBeNull();
})->with([
    'none' => [[]],
    'without limit' => [['X-RateLimit-Remaining' => ['19'], 'X-RateLimit-Reset' => ['1']]],
    'without remaining' => [['X-RateLimit-Limit' => ['20'], 'X-RateLimit-Reset' => ['1']]],
    'without reset' => [['X-RateLimit-Limit' => ['20'], 'X-RateLimit-Remaining' => ['19']]],
    'empty value list' => [['X-RateLimit-Limit' => [], 'X-RateLimit-Remaining' => ['19'], 'X-RateLimit-Reset' => ['1']]],
    'not a number' => [['X-RateLimit-Limit' => ['20'], 'X-RateLimit-Remaining' => ['many'], 'X-RateLimit-Reset' => ['1']]],
    'negative' => [['X-RateLimit-Limit' => ['20'], 'X-RateLimit-Remaining' => ['19'], 'X-RateLimit-Reset' => ['-1']]],
    'fraction' => [['X-RateLimit-Limit' => ['20'], 'X-RateLimit-Remaining' => ['19'], 'X-RateLimit-Reset' => ['0.5']]],
]);

it('counts the reset from the time it was received', function () {
    $before = new DateTimeImmutable();
    $resetsAt = (new RateLimit(5, 0, 5))->resetsAt();

    expect($resetsAt >= $before->modify('+5 seconds'))->toBeTrue()
        ->and($resetsAt <= (new DateTimeImmutable())->modify('+5 seconds'))->toBeTrue();
});
