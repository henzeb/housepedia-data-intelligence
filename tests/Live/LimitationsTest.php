<?php

use GuzzleHttp\Client as GuzzleClient;
use Henzeb\Housepedia\Data\Intelligence\Client;
use Henzeb\Housepedia\Data\Intelligence\Config;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\ApiException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\DataUnavailableException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\NotFoundException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\RateLimitException;

it('returns no catalog fields the SDK does not map', function () {
    $catalog = liveClient()->get('/v5/catalog')->raw();

    $filled = [];

    if (! empty($catalog['data_points'])) {
        $filled[] = 'data_points';
    }

    foreach ($catalog['products'] ?? [] as $product) {
        foreach (['fields', 'coverage', 'freshness', 'product_url'] as $field) {
            if (! empty($product[$field])) {
                $filled[] = $product['id'] . '.' . $field;
            }
        }
    }

    expect($filled)->toBe([]);
})->group('live-free');

it('returns every home at a postcode and house number in a known shape', function () {
    $body = liveClient()->get('/v5/addresses/1076LJ-12', ['all' => true])->raw();

    $shape = array_is_list($body['data'] ?? null) ? 'data list' : 'single address';

    liveFinding(4, 'shape of all=true', [
        'shape' => $shape,
        'homes' => $shape === 'data list' ? count($body['data']) : 1,
        'homes with meta' => $shape === 'data list' ? count(array_filter($body['data'], fn (array $home) => isset($home['meta']))) : null,
    ]);

    expect($shape === 'data list' || isset($body['postcode']))->toBeTrue();
})->group('live-paid');

it('has no statistics for postcode areas', function () {
    liveClient()->get('/v5/areas/postcode4:1076/stats');
})->throws(DataUnavailableException::class)->group('live-free');

it('handles an unknown include value', function () {
    try {
        $response = liveClient()->get('/v5/addresses/1076LJ-12-2', ['include' => 'unknown']);

        liveFinding(7, 'include=unknown', ['result' => 'accepted', 'status' => $response->status()]);
    } catch (ApiException $exception) {
        liveFinding(7, 'include=unknown', [
            'result' => 'rejected',
            'status' => $exception->status(),
            'code' => $exception->errorCode(),
            'detail' => $exception->detail(),
            'errors' => $exception->errors(),
        ]);
    }

    expect(true)->toBeTrue();
})->group('live-paid');

it('returns rate limit headers', function () {
    $response = liveClient()->get('/v5/account');

    $headers = array_filter(
        $response->headers(),
        fn (string $name) => (bool)preg_match('/ratelimit|retry-after/i', $name),
        ARRAY_FILTER_USE_KEY,
    );

    liveFinding(9, 'headers of a successful response', $headers);

    expect($response->status())->toBe(200)
        ->and($response->rateLimit())->not->toBeNull();
})->group('live-free');

it('reports when to retry after a burst of requests', function () {
    liveClient();

    $unthrottled = Client::create(
        liveEnv('HOUSEPEDIA_API_KEY'),
        new GuzzleClient(),
        new Config(baseUrl: liveEnv('HOUSEPEDIA_BASE_URL'), retries: 0),
    );

    $requests = 0;

    try {
        while ($requests < 10) {
            $requests++;
            $unthrottled->get('/v5/account');
        }
    } catch (RateLimitException $exception) {
        liveFinding(9, 'burst of unthrottled requests', [
            'limited after' => $requests,
            'retryAfter' => $exception->retryAfter(),
            'code' => $exception->errorCode(),
        ]);

        sleep(min(max($exception->retryAfter() ?? 1, 1), 10));

        expect($exception->status())->toBe(429);

        return;
    }

    liveFinding(9, 'burst of unthrottled requests', ['limited after' => null, 'requests' => $requests]);

    expect($requests)->toBe(10);
})->group('live-free');

it('looks up an extension with a space', function () {
    liveClient()->addresses()->withPostcode('1076LJ', 12, 'A 1')->find();
})->throws(NotFoundException::class)->group('live-free');

it('does not recognise the realtor suffix hs', function () {
    liveClient()->addresses()->withPostcode('5308KH', 2, 'hs')->find();
})->throws(NotFoundException::class)->group('live-free');

it('finds a multi-letter extension in any case', function () {
    $address = liveClient()->addresses()->withPostcode('2514KP', 1, 'bis')->find();

    expect($address->houseNumber())->toBe(1)
        ->and($address->houseNumberExt())->toBe('BIS');
})->group('live-paid');
