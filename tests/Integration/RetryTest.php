<?php

use Henzeb\Housepedia\Data\Intelligence\Client;
use Henzeb\Housepedia\Data\Intelligence\Config;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\NotFoundException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\RateLimitException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\ServiceUnavailableException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\TransportException;
use Henzeb\Housepedia\Data\Intelligence\Response;
use Http\Client\Exception\NetworkException;

beforeEach(function () {
    $this->http = new Http\Mock\Client();
});

afterEach(function () {
    Client::retryUsing(null);
});

function rateLimited(): Psr\Http\Message\ResponseInterface
{
    return jsonResponse(['code' => 'rate_limited', 'title' => 'Too Many Requests'], 429, ['Retry-After' => '0']);
}

function unavailable(): Psr\Http\Message\ResponseInterface
{
    return jsonResponse(['code' => 'upstream_unavailable', 'title' => 'Service Unavailable'], 503, ['Retry-After' => '0']);
}

it('retries a rate limited request', function () {
    $this->http->addResponse(rateLimited());
    $this->http->addResponse(rateLimited());
    $this->http->addResponse(jsonResponse(jsonFixture('getAccount')));

    $response = housepedia($this->http, new Config())->get('/v5/account');

    expect($response->status())->toBe(200)
        ->and($this->http->getRequests())->toHaveCount(3);
});

it('throws after the last retry', function () {
    $this->http->addResponse(rateLimited());
    $this->http->addResponse(rateLimited());
    $this->http->addResponse(rateLimited());

    expect(fn () => housepedia($this->http, new Config(retries: 2))->get('/v5/account'))
        ->toThrow(RateLimitException::class)
        ->and($this->http->getRequests())->toHaveCount(3);
});

it('sends once without retries', function () {
    $this->http->addResponse(rateLimited());

    expect(fn () => housepedia($this->http, new Config(retries: 0))->get('/v5/account'))
        ->toThrow(RateLimitException::class)
        ->and($this->http->getRequests())->toHaveCount(1);
});

it('retries typed methods and posts', function () {
    $this->http->addResponse(rateLimited());
    $this->http->addResponse(jsonResponse(jsonFixture('suggestAddresses')));
    $this->http->addResponse(rateLimited());
    $this->http->addResponse(jsonResponse(['data' => []]));

    $client = housepedia($this->http, new Config());

    expect($client->addresses()->suggest('1076LJ 12-2')->data())->not->toBeEmpty()
        ->and($client->post('/v5/valuations/scenarios', ['rows' => []])->status())->toBe(200)
        ->and($this->http->getRequests())->toHaveCount(4);
});

it('does not retry an unavailable service by default', function () {
    $this->http->addResponse(unavailable());

    expect(fn () => housepedia($this->http, new Config())->get('/v5/account'))
        ->toThrow(ServiceUnavailableException::class)
        ->and($this->http->getRequests())->toHaveCount(1);
});

it('retries an unavailable service when enabled', function () {
    $this->http->addResponse(unavailable());
    $this->http->addResponse(jsonResponse(jsonFixture('getAccount')));

    expect(housepedia($this->http, new Config(retryServiceUnavailable: true))->get('/v5/account')->status())->toBe(200)
        ->and($this->http->getRequests())->toHaveCount(2);
});

it('does not retry other errors', function () {
    $this->http->addResponse(jsonResponse(['code' => 'not_found', 'title' => 'Not Found'], 404));

    expect(fn () => housepedia($this->http, new Config())->get('/v5/account'))
        ->toThrow(NotFoundException::class)
        ->and($this->http->getRequests())->toHaveCount(1);
});

it('does not retry transport failures', function () {
    $this->http->addException(new NetworkException('timeout', new GuzzleHttp\Psr7\Request('GET', '/')));

    expect(fn () => housepedia($this->http, new Config())->get('/v5/account'))
        ->toThrow(TransportException::class)
        ->and($this->http->getRequests())->toHaveCount(1);
});

it('uses a custom retry callback', function () {
    $this->http->addResponse(rateLimited());
    $this->http->addResponse(jsonResponse(jsonFixture('getAccount')));
    $config = new Config(retries: 7);
    $received = null;

    Client::retryUsing(function (Closure $attempt, Config $given) use (&$received): Response {
        $received = $given;

        try {
            return $attempt();
        } catch (RateLimitException) {
            return $attempt();
        }
    });

    expect(housepedia($this->http, $config)->get('/v5/account')->status())->toBe(200)
        ->and($received)->toBe($config)
        ->and($this->http->getRequests())->toHaveCount(2);
});

it('restores the default retry callback', function () {
    Client::retryUsing(fn (Closure $attempt): Response => $attempt());
    Client::retryUsing(null);

    $this->http->addResponse(rateLimited());
    $this->http->addResponse(jsonResponse(jsonFixture('getAccount')));

    expect(housepedia($this->http, new Config())->get('/v5/account')->status())->toBe(200);
});

it('waits the retry after before retrying', function () {
    $this->http->addResponse(jsonResponse(['code' => 'rate_limited', 'title' => 'Too Many Requests'], 429, ['Retry-After' => '1']));
    $this->http->addResponse(jsonResponse(jsonFixture('getAccount')));

    $start = microtime(true);
    housepedia($this->http, new Config(retries: 1))->get('/v5/account');

    expect(microtime(true) - $start)->toBeGreaterThanOrEqual(0.9);
});
