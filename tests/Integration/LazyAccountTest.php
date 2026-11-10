<?php

use Henzeb\Housepedia\Data\Intelligence\Config;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\RequestLogEntry;
use Henzeb\Housepedia\Data\Intelligence\V5\Resources\LazyAccount;
use Http\Mock\Client as MockClient;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Tests\Support\ArrayCache;

beforeEach(function () {
    $this->http = new MockClient();
});

/**
 * @param list<string> $requestIds
 */
function requestLogPage(array $requestIds, ?string $nextCursor, string $cacheControl = 'no-store'): ResponseInterface
{
    return jsonResponse([
        'data' => array_map(fn (string $requestId) => [
            'request_id' => $requestId,
            'at' => '2026-10-26T12:00:00+00:00',
            'method' => 'GET',
            'path' => '/v5/account',
            'status' => 200,
            'credits' => 0,
        ], $requestIds),
        'meta' => ['request_id' => 'req_page', 'next_cursor' => $nextCursor],
    ], headers: ['Cache-Control' => $cacheControl]);
}

/**
 * @return list<array<string, string>>
 */
function sentQueries(MockClient $http): array
{
    return array_map(queryOf(...), $http->getRequests());
}

it('creates a lazy account', function () {
    expect(housepedia($this->http)->account()->lazy())->toBeInstanceOf(LazyAccount::class);
});

it('reads every entry of every page', function () {
    $this->http->addResponse(requestLogPage(['req_1', 'req_2'], 'cursor_1'));
    $this->http->addResponse(requestLogPage(['req_3'], null));

    $entries = iterator_to_array(housepedia($this->http)->account()->lazy()->requests(
        new DateTimeImmutable('2026-10-01T00:00:00+00:00'),
        new DateTimeImmutable('2026-10-31T00:00:00+00:00'),
        'key_1',
        404,
        2,
    ));

    $filters = ['from' => '2026-10-01T00:00:00+00:00', 'to' => '2026-10-31T00:00:00+00:00', 'key_id' => 'key_1', 'status' => '404', 'limit' => '2'];

    expect($entries)->toHaveCount(3)
        ->each->toBeInstanceOf(RequestLogEntry::class)
        ->and(array_keys($entries))->toBe([0, 1, 2])
        ->and(array_map(fn (RequestLogEntry $entry) => $entry->requestId(), $entries))->toBe(['req_1', 'req_2', 'req_3'])
        ->and(array_map(fn (RequestInterface $request) => $request->getMethod() . ' ' . $request->getUri()->getPath(), $this->http->getRequests()))
        ->toBe(['GET /v5/account/requests', 'GET /v5/account/requests'])
        ->and(sentQueries($this->http))->toBe([$filters, $filters + ['cursor' => 'cursor_1']]);
});

it('leaves out the filters that are not given', function () {
    $this->http->addResponse(requestLogPage(['req_1'], null));

    iterator_to_array(housepedia($this->http)->account()->lazy()->requests());

    expect(sentQueries($this->http))->toBe([[]]);
});

it('sends no request before the first entry is read', function () {
    housepedia($this->http)->account()->lazy()->requests();

    expect($this->http->getRequests())->toBe([]);
});

it('requests a page only when the entries before it are read', function () {
    $this->http->addResponse(requestLogPage(['req_1', 'req_2'], 'cursor_1'));
    $this->http->addResponse(requestLogPage(['req_3'], null));

    $entries = housepedia($this->http)->account()->lazy()->requests();

    expect($entries->current()->requestId())->toBe('req_1')
        ->and($this->http->getRequests())->toHaveCount(1);

    $entries->next();

    expect($entries->current()->requestId())->toBe('req_2')
        ->and($this->http->getRequests())->toHaveCount(1);

    $entries->next();

    expect($entries->current()->requestId())->toBe('req_3')
        ->and($this->http->getRequests())->toHaveCount(2);
});

it('stops after an empty last page', function () {
    $this->http->addResponse(requestLogPage([], null));

    expect(iterator_to_array(housepedia($this->http)->account()->lazy()->requests()))->toBe([])
        ->and($this->http->getRequests())->toHaveCount(1);
});

it('starts at the first page on a new call', function () {
    $this->http->addResponse(requestLogPage(['req_1'], null));
    $this->http->addResponse(requestLogPage(['req_1'], null));

    $lazy = housepedia($this->http)->account()->lazy();

    iterator_to_array($lazy->requests());
    iterator_to_array($lazy->requests());

    expect(sentQueries($this->http))->toBe([[], []]);
});

it('keeps the cache setting of the account', function () {
    $cache = new ArrayCache();
    $this->http->setDefaultResponse(requestLogPage(['req_1'], null, 'private, max-age=3600'));

    $account = housepedia($this->http, new Config(cache: $cache))->account();

    iterator_to_array($account->withoutCache()->lazy()->requests());
    iterator_to_array($account->lazy()->withoutCache()->requests());

    expect($this->http->getRequests())->toHaveCount(2);

    iterator_to_array($account->lazy()->requests());
    iterator_to_array($account->lazy()->requests());

    expect($this->http->getRequests())->toHaveCount(3);
});
