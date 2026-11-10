<?php

use Henzeb\Housepedia\Data\Intelligence\Config;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\NotFoundException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\RateLimitException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\ServerException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\ServiceUnavailableException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\TransportException;
use Henzeb\Housepedia\Data\Intelligence\Transport\Transport;
use Tests\Support\ArrayCache;

it('has defaults', function () {
    $config = new Config();

    expect($config->baseUrl())->toBe(Transport::BASE_URL)
        ->and($config->language())->toBeNull()
        ->and($config->retries())->toBe(2)
        ->and($config->retryServiceUnavailable())->toBeFalse()
        ->and($config->cache())->toBeNull()
        ->and($config->cachePrefix())->toBe('housepedia-data-intelligence')
        ->and($config->maxCacheAgeInSeconds())->toBeNull();
});

it('returns the given settings', function () {
    $cache = new ArrayCache();
    $config = new Config(baseUrl: 'https://example.test', language: 'nl', retries: 5, retryServiceUnavailable: true, cache: $cache, cachePrefix: 'my-app', maxCacheAgeInSeconds: 600);

    expect($config->baseUrl())->toBe('https://example.test')
        ->and($config->language())->toBe('nl')
        ->and($config->retries())->toBe(5)
        ->and($config->retryServiceUnavailable())->toBeTrue()
        ->and($config->cache())->toBe($cache)
        ->and($config->cachePrefix())->toBe('my-app')
        ->and($config->maxCacheAgeInSeconds())->toBe(600);
});

it('copies itself with another cache setting', function () {
    $cache = new ArrayCache();
    $config = new Config(language: 'nl', cache: $cache, maxCacheAgeInSeconds: 600);

    $limited = $config->withMaxCacheAge(60);
    $uncached = $config->withoutCache();

    expect($limited)->not->toBe($config)
        ->and($limited->maxCacheAgeInSeconds())->toBe(60)
        ->and($limited->cache())->toBe($cache)
        ->and($limited->language())->toBe('nl')
        ->and($uncached->cache())->toBeNull()
        ->and($uncached->language())->toBe('nl')
        ->and($config->cache())->toBe($cache)
        ->and($config->maxCacheAgeInSeconds())->toBe(600);
});

it('treats an empty base url and language as not set', function () {
    $config = new Config(baseUrl: '', language: '');

    expect($config->baseUrl())->toBe(Transport::BASE_URL)
        ->and($config->language())->toBeNull();
});

it('retries rate limits', function () {
    expect((new Config())->shouldRetry(new RateLimitException(429, 'rate_limited', 'Too Many Requests')))->toBeTrue();
});

it('retries an unavailable service only when enabled', function () {
    $exception = new ServiceUnavailableException(503, 'upstream_unavailable', 'Service Unavailable');

    expect((new Config())->shouldRetry($exception))->toBeFalse()
        ->and((new Config(retryServiceUnavailable: true))->shouldRetry($exception))->toBeTrue();
});

it('does not retry other exceptions', function (Throwable $exception) {
    expect((new Config(retryServiceUnavailable: true))->shouldRetry($exception))->toBeFalse();
})->with([
    'not found' => fn () => new NotFoundException(404, 'not_found', 'Not Found'),
    'server error' => fn () => new ServerException(500, 'http_500', 'Internal Server Error'),
    'transport' => fn () => new TransportException('timeout'),
    'other' => fn () => new RuntimeException('other'),
]);

it('waits the retry after of an api exception', function () {
    expect((new Config())->retryDelay(new RateLimitException(429, 'rate_limited', 'Too Many Requests', retryAfter: 3)))->toBe(3);
});

it('waits one second without a retry after', function (Throwable $exception) {
    expect((new Config())->retryDelay($exception))->toBe(1);
})->with([
    'api exception' => fn () => new RateLimitException(429, 'rate_limited', 'Too Many Requests'),
    'other' => fn () => new RuntimeException('other'),
]);
