<?php

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response as PsrResponse;
use Henzeb\Housepedia\Data\Intelligence\Client;
use Henzeb\Housepedia\Data\Intelligence\Config;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\DataUnavailableException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\ForbiddenException;
use Henzeb\Housepedia\Data\Intelligence\Transport\Transport;
use Http\Mock\Client as MockClient;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Tests\Support\ThrottledClient;

/**
 * @return array<string, mixed>
 */
function jsonFixture(string $name): array
{
    return json_decode((string)file_get_contents(__DIR__ . '/Fixtures/v5/' . $name . '.json'), true, flags: JSON_THROW_ON_ERROR);
}

/**
 * @param array<array-key, mixed>|string $body
 * @param array<string, string> $headers
 */
function jsonResponse(array|string $body, int $status = 200, array $headers = []): ResponseInterface
{
    $contentType = $status >= 400 ? 'application/problem+json' : 'application/json';

    return new PsrResponse(
        $status,
        $headers + ['Content-Type' => $contentType],
        is_string($body) ? $body : json_encode($body, JSON_THROW_ON_ERROR),
    );
}

function transport(MockClient $http, ?Config $config = null): Transport
{
    $factory = new HttpFactory();

    return new Transport('hp_test', $http, $factory, $factory, $config ?? new Config(retries: 0));
}

function housepedia(MockClient $http, ?Config $config = null): Client
{
    return new Client(transport($http, $config));
}

function lastRequest(MockClient $http): RequestInterface
{
    $request = $http->getLastRequest();

    expect($request)->toBeInstanceOf(RequestInterface::class);

    return $request;
}

/**
 * @return array<string, string>
 */
function queryOf(RequestInterface $request): array
{
    parse_str($request->getUri()->getQuery(), $query);

    return $query;
}

pest()->group('live')->in('Live');

function liveEnv(string $name): ?string
{
    static $file = null;

    $value = getenv($name);

    if (is_string($value) && $value !== '') {
        return $value;
    }

    if ($file === null) {
        $path = dirname(__DIR__) . '/.env';
        $file = is_file($path) ? (parse_ini_file($path, false, INI_SCANNER_RAW) ?: []) : [];
    }

    $value = $file[$name] ?? null;

    return is_string($value) && $value !== '' ? $value : null;
}

function liveHttpClient(): ThrottledClient
{
    static $client = null;

    return $client ??= new ThrottledClient(
        new GuzzleClient(),
        (int)(liveEnv('HOUSEPEDIA_REQUEST_INTERVAL_MS') ?? 1000),
    );
}

function liveClient(?string $language = null, ?string $apiKey = null, ?Psr\SimpleCache\CacheInterface $cache = null): Client
{
    $apiKey ??= liveEnv('HOUSEPEDIA_API_KEY');

    if ($apiKey === null) {
        test()->markTestSkipped('HOUSEPEDIA_API_KEY is not set in the environment or in .env.');
    }

    return Client::create(
        $apiKey,
        liveHttpClient(),
        new Config(baseUrl: liveEnv('HOUSEPEDIA_BASE_URL'), language: $language, cache: $cache),
    );
}

/**
 * @template T
 * @param Closure(): T $call
 * @return T
 */
function liveOrIncomplete(Closure $call): mixed
{
    try {
        return $call();
    } catch (DataUnavailableException|ForbiddenException $exception) {
        test()->markTestIncomplete(sprintf('%s: %s', $exception->errorCode(), $exception->detail() ?? $exception->title()));
    }
}

function liveFinding(int $limitation, string $check, mixed $finding): void
{
    $path = dirname(__DIR__) . '/build/live-findings.json';

    if (! is_dir(dirname($path))) {
        mkdir(dirname($path), 0777, true);
    }

    $findings = is_file($path) ? json_decode((string)file_get_contents($path), true) : [];
    $findings = is_array($findings) ? $findings : [];

    $findings[$limitation][$check] = [
        'at' => date(DATE_ATOM),
        'finding' => $finding,
    ];

    ksort($findings);

    file_put_contents($path, json_encode($findings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL);
}

/**
 * @param list<object> $items
 * @return array<string, array<string, int>>
 */
function livePriceTypes(array $items): array
{
    $types = ['price' => [], 'priceIndexed' => []];

    foreach ($items as $item) {
        foreach (array_keys($types) as $getter) {
            $type = get_debug_type($item->{$getter}());
            $types[$getter][$type] = ($types[$getter][$type] ?? 0) + 1;
        }
    }

    return $types;
}
