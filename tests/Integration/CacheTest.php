<?php

use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response as PsrResponse;
use Henzeb\Housepedia\Data\Intelligence\Client;
use Henzeb\Housepedia\Data\Intelligence\Concerns\ConfiguresCache;
use Henzeb\Housepedia\Data\Intelligence\Config;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\NotFoundException;
use Henzeb\Housepedia\Data\Intelligence\V5\Input\ScenarioRow;
use Henzeb\Housepedia\Data\Intelligence\Locators\Locator;
use Henzeb\Housepedia\Data\Intelligence\V5\Resources;
use Henzeb\Housepedia\Data\Intelligence\Transport\Transport;
use Http\Mock\Client as MockClient;
use Tests\Support\ArrayCache;

beforeEach(function () {
    $this->http = new MockClient();
    $this->cache = new ArrayCache();
});

/**
 * @param array<string, string> $headers
 */
function cacheableResponse(string $fixture, string $cacheControl = 'private, max-age=3600', array $headers = []): Psr\Http\Message\ResponseInterface
{
    return jsonResponse(jsonFixture($fixture), 200, $headers + ['Cache-Control' => $cacheControl]);
}

function cachingClient(MockClient $http, ArrayCache $cache, ?int $maxCacheAgeInSeconds = null, string $apiKey = 'hp_test', ?string $language = null, string $cachePrefix = 'housepedia-data-intelligence'): Client
{
    $factory = new HttpFactory();

    return new Client(new Transport($apiKey, $http, $factory, $factory, new Config(
        language: $language,
        retries: 0,
        cache: $cache,
        cachePrefix: $cachePrefix,
        maxCacheAgeInSeconds: $maxCacheAgeInSeconds,
    )));
}

it('reads a cached answer instead of sending a request', function () {
    $this->http->setDefaultResponse(cacheableResponse('suggestAddresses'));
    $client = cachingClient($this->http, $this->cache);

    $first = $client->addresses()->suggest('1076LJ 12');
    $second = $client->addresses()->suggest('1076LJ 12');

    expect($this->http->getRequests())->toHaveCount(1)
        ->and($second->data())->toEqual($first->data())
        ->and(array_values($this->cache->ttls()))->toBe([3600])
        ->and(array_keys($this->cache->values())[0])->toMatch('/^housepedia-data-intelligence\.[0-9a-f]{64}$/');
});

it('does not cache answers the api does not allow', function (?string $cacheControl) {
    $response = $cacheControl === null
        ? jsonResponse(jsonFixture('suggestAddresses'))
        : cacheableResponse('suggestAddresses', $cacheControl);
    $this->http->setDefaultResponse($response);
    $client = cachingClient($this->http, $this->cache);

    $client->addresses()->suggest('1076LJ 12');
    $client->addresses()->suggest('1076LJ 12');

    expect($this->http->getRequests())->toHaveCount(2)
        ->and($this->cache->values())->toBe([]);
})->with([
    'no header' => [null],
    'no-store' => ['private, no-store'],
    'no-store with max-age' => ['private, no-store, max-age=3600'],
    'no-cache with max-age' => ['private, no-cache, max-age=3600'],
    'max-age 0' => ['private, max-age=0'],
    'no max-age' => ['private'],
]);

it('does not cache error answers', function () {
    $this->http->setDefaultResponse(jsonResponse(['code' => 'not_found', 'title' => 'Not Found'], 404, ['Cache-Control' => 'private, max-age=3600']));
    $client = cachingClient($this->http, $this->cache);

    foreach ([1, 2] as $attempt) {
        expect(fn () => $client->addresses()->withPostcode('9999ZZ', 1)->find())->toThrow(NotFoundException::class);
    }

    expect($this->http->getRequests())->toHaveCount(2)
        ->and($this->cache->values())->toBe([]);
});

it('caches only 2xx answers', function (int $status, int $requests) {
    $this->http->setDefaultResponse(new PsrResponse($status, ['Cache-Control' => 'private, max-age=3600']));
    $client = cachingClient($this->http, $this->cache);

    $client->get('/v5/account');
    $client->get('/v5/account');

    expect($this->http->getRequests())->toHaveCount($requests);
})->with([
    '199' => [199, 2],
    '200' => [200, 1],
    '299' => [299, 1],
    '300' => [300, 2],
    '304' => [304, 2],
]);

it('reads max-age case-insensitively and from one second', function (string $cacheControl, int $expected) {
    $this->http->setDefaultResponse(cacheableResponse('suggestAddresses', $cacheControl));

    cachingClient($this->http, $this->cache)->addresses()->suggest('1076LJ 12');

    expect(array_values($this->cache->ttls()))->toBe([$expected]);
})->with([
    'one second' => ['private, max-age=1', 1],
    'upper case' => ['Private, Max-Age=60', 60],
]);

it('does not cache posts', function () {
    $this->http->setDefaultResponse(cacheableResponse('createScenarioValuations'));
    $client = cachingClient($this->http, $this->cache);
    $rows = [new ScenarioRow(Locator::postcode('1076LJ', 12, '2'))];

    $client->valuations()->scenarios($rows);
    $client->valuations()->scenarios($rows);

    expect($this->http->getRequests())->toHaveCount(2)
        ->and($this->cache->values())->toBe([]);
});

it('caches head requests', function () {
    $this->http->setDefaultResponse(new PsrResponse(200, ['Cache-Control' => 'private, max-age=3600']));
    $home = cachingClient($this->http, $this->cache)->properties()->withPostcode('1076LJ', 12, '2');

    expect($home->hasTransactions())->toBeTrue()
        ->and($home->hasTransactions())->toBeTrue()
        ->and($this->http->getRequests())->toHaveCount(1);
});

it('keeps the status and headers of the cached answer', function () {
    $this->http->setDefaultResponse(cacheableResponse('getEnergyLabel', headers: ['X-Credits-Charged' => '1']));
    $client = cachingClient($this->http, $this->cache);

    $client->get('/v5/properties/1076LJ-12-2/energy-label');
    $cached = $client->get('/v5/properties/1076LJ-12-2/energy-label');

    expect($this->http->getRequests())->toHaveCount(1)
        ->and($cached->status())->toBe(200)
        ->and($cached->header('X-Credits-Charged'))->toBe('1')
        ->and($cached->raw())->toBe(jsonFixture('getEnergyLabel'));
});

it('stores no rate limit with a cached answer', function () {
    $this->http->setDefaultResponse(cacheableResponse('suggestAddresses', headers: [
        'X-RateLimit-Limit' => '20',
        'X-RateLimit-Remaining' => '19',
        'X-RateLimit-Reset' => '1',
        'X-Credits-Charged' => '0',
    ]));
    $client = cachingClient($this->http, $this->cache);

    $first = $client->addresses()->suggest('1076LJ 12');
    $second = $client->addresses()->suggest('1076LJ 12');
    $raw = $client->get('/v5/addresses/suggest', ['q' => '1076LJ 12']);
    $stored = array_values($this->cache->values())[0];

    expect($this->http->getRequests())->toHaveCount(1)
        ->and($first->meta()?->rateLimit()?->remaining())->toBe(19)
        ->and($second->meta()?->rateLimit())->toBeNull()
        ->and($second->meta()?->requestId())->toBe($first->meta()?->requestId())
        ->and($raw->rateLimit())->toBeNull()
        ->and($raw->header('X-Credits-Charged'))->toBe('0')
        ->and(array_keys($stored['headers']))->toEqualCanonicalizing(['Cache-Control', 'X-Credits-Charged', 'Content-Type']);
});

it('separates answers per url, query, language and api key', function () {
    $this->http->setDefaultResponse(cacheableResponse('suggestAddresses'));

    cachingClient($this->http, $this->cache)->addresses()->suggest('1076LJ 12');
    cachingClient($this->http, $this->cache)->addresses()->suggest('1076LJ 12', 5);
    cachingClient($this->http, $this->cache)->addresses()->suggest('1076LJ 14');
    cachingClient($this->http, $this->cache, language: 'nl')->addresses()->suggest('1076LJ 12');
    cachingClient($this->http, $this->cache, apiKey: 'hp_other')->addresses()->suggest('1076LJ 12');
    cachingClient($this->http, $this->cache)->get('/v5/addresses/suggest', ['q' => '1076LJ 12', 'limit' => 3]);

    expect($this->http->getRequests())->toHaveCount(6)
        ->and($this->cache->values())->toHaveCount(6);
});

it('separates head and get requests to the same url', function () {
    $this->http->addResponse(new PsrResponse(200, ['Cache-Control' => 'private, max-age=3600']));
    $this->http->addResponse(cacheableResponse('getTransactions'));
    $home = cachingClient($this->http, $this->cache)->properties()->withPostcode('1076LJ', 12, '2');

    $home->hasTransactions();
    $home->transactions();

    expect($this->http->getRequests())->toHaveCount(2)
        ->and($this->cache->values())->toHaveCount(2);
});

it('does not put the api key in the cache key', function () {
    $this->http->setDefaultResponse(cacheableResponse('suggestAddresses'));

    cachingClient($this->http, $this->cache)->addresses()->suggest('1076LJ 12');

    expect(implode(' ', array_keys($this->cache->values())))->not->toContain('hp_test');
});

it('uses the configured prefix', function () {
    $this->http->setDefaultResponse(cacheableResponse('suggestAddresses'));

    cachingClient($this->http, $this->cache, cachePrefix: 'my-app')->addresses()->suggest('1076LJ 12');

    expect(array_keys($this->cache->values())[0])->toMatch('/^my-app\.[0-9a-f]{64}$/');
});

it('lowers the time to live to the configured maximum', function (int $maximum, int $expected) {
    $this->http->setDefaultResponse(cacheableResponse('suggestAddresses'));

    cachingClient($this->http, $this->cache, $maximum)->addresses()->suggest('1076LJ 12');

    expect(array_values($this->cache->ttls()))->toBe([$expected]);
})->with([
    'lower than max-age' => [600, 600],
    'higher than max-age' => [7200, 3600],
]);

it('does not write with a maximum of zero', function () {
    $this->http->setDefaultResponse(cacheableResponse('suggestAddresses'));

    cachingClient($this->http, $this->cache, 0)->addresses()->suggest('1076LJ 12');

    expect($this->cache->values())->toBe([]);
});

it('sets the maximum age per call', function (int $seconds, ?int $expected) {
    $this->http->setDefaultResponse(cacheableResponse('getEnergyLabel'));

    cachingClient($this->http, $this->cache, 600)->properties()->withPostcode('1076LJ', 12, '2')->withMaxCacheAge($seconds)->energyLabel();

    expect(array_values($this->cache->ttls()))->toBe($expected === null ? [] : [$expected]);
})->with([
    'higher than the config' => [1200, 1200],
    'higher than max-age' => [7200, 3600],
    'lower than the config' => [60, 60],
    'zero' => [0, null],
]);

it('skips the cache per call', function () {
    $this->http->setDefaultResponse(cacheableResponse('getEnergyLabel'));
    $home = cachingClient($this->http, $this->cache)->properties()->withPostcode('1076LJ', 12, '2');

    $home->withoutCache()->energyLabel();

    expect($this->cache->values())->toBe([]);

    $home->energyLabel();
    $home->withoutCache()->energyLabel();

    expect($this->http->getRequests())->toHaveCount(3)
        ->and($this->cache->values())->toHaveCount(1);
});

it('keeps the cache setting across locator methods', function () {
    $this->http->setDefaultResponse(cacheableResponse('getEnergyLabel'));
    $client = cachingClient($this->http, $this->cache);

    $client->properties()->withoutCache()->withPostcode('1076LJ', 12, '2')->energyLabel();
    $this->http->addResponse(cacheableResponse('getArea'));
    $client->areas()->withoutCache()->withPostcode4('1076')->find();
    $client->properties()->withPostcode('1076LJ', 12, '2')->withoutCache()->energyLabel();

    expect($this->cache->values())->toBe([])
        ->and(lastRequest($this->http)->getUri()->getPath())->toBe('/v5/properties/1076LJ-12-2/energy-label');
});

it('leaves the resource it is called on unchanged', function () {
    $this->http->setDefaultResponse(cacheableResponse('getEnergyLabel'));
    $home = cachingClient($this->http, $this->cache)->properties()->withPostcode('1076LJ', 12, '2');

    $uncached = $home->withoutCache();
    $limited = $home->withMaxCacheAge(60);

    $home->energyLabel();
    $home->energyLabel();

    expect($uncached)->not->toBe($home)
        ->and($limited)->not->toBe($home)
        ->and($this->http->getRequests())->toHaveCount(1)
        ->and(array_values($this->cache->ttls()))->toBe([3600]);
});

it('sets the cache per client', function () {
    $this->http->setDefaultResponse(cacheableResponse('getEnergyLabel'));
    $client = cachingClient($this->http, $this->cache);

    $client->withoutCache()->get('/v5/properties/1076LJ-12-2/energy-label');
    $client->withoutCache()->properties()->withPostcode('1076LJ', 12, '2')->energyLabel();

    expect($this->cache->values())->toBe([]);

    $client->withMaxCacheAge(60)->properties()->withPostcode('1076LJ', 12, '2')->energyLabel();

    expect(array_values($this->cache->ttls()))->toBe([60])
        ->and($client->withoutCache())->not->toBe($client);
});

it('treats a cached value of another shape as missing', function (mixed $value) {
    $this->http->setDefaultResponse(cacheableResponse('getEnergyLabel'));
    $client = cachingClient($this->http, $this->cache);

    $client->get('/v5/properties/1076LJ-12-2/energy-label');
    $this->cache->set(array_keys($this->cache->values())[0], $value);
    $response = $client->get('/v5/properties/1076LJ-12-2/energy-label');

    expect($this->http->getRequests())->toHaveCount(2)
        ->and($response->raw())->toBe(jsonFixture('getEnergyLabel'));
})->with([
    'string' => ['cached'],
    'missing data' => [['status' => 200, 'headers' => []]],
    'status as string' => [['status' => '200', 'headers' => [], 'data' => []]],
    'header values not a list' => [['status' => 200, 'headers' => ['Age' => '1'], 'data' => []]],
    'header value not a string' => [['status' => 200, 'headers' => ['Age' => [1]], 'data' => []]],
]);

it('ignores the cache settings without a cache', function () {
    $this->http->setDefaultResponse(cacheableResponse('getEnergyLabel'));
    $home = housepedia($this->http)->properties()->withPostcode('1076LJ', 12, '2');

    $home->withMaxCacheAge(60)->energyLabel();
    $home->withMaxCacheAge(60)->energyLabel();
    $home->withoutCache()->energyLabel();

    expect($this->http->getRequests())->toHaveCount(3);
});

it('offers the cache settings on every resource and the client', function (string $class) {
    expect(class_uses($class))->toContain(ConfiguresCache::class);
})->with([
    Client::class,
    Resources\Addresses::class,
    Resources\Properties::class,
    Resources\Buildings::class,
    Resources\Areas::class,
    Resources\PostcodeArea::class,
    Resources\Valuations::class,
    Resources\Account::class,
]);
