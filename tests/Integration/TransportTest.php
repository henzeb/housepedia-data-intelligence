<?php

use Composer\Autoload\ClassLoader;
use Composer\InstalledVersions;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response as PsrResponse;
use Henzeb\Housepedia\Data\Intelligence\Client;
use Henzeb\Housepedia\Data\Intelligence\Config;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\AddressAmbiguousException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\ApiException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\AuthenticationException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\ConflictException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\DataUnavailableException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\ForbiddenException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\HousepediaException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\MissingApiKeyException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\NotFoundException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\PaymentRequiredException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\RateLimitException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\ServerException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\ServiceUnavailableException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\TransportException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\UnexpectedResponseTypeException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\ValidationException;
use Henzeb\Housepedia\Data\Intelligence\Transport\Transport;
use Http\Client\Exception\NetworkException;
use Http\Mock\Client as MockClient;

beforeEach(function () {
    $this->http = new MockClient();
    $this->http->setDefaultResponse(jsonResponse(['data' => []]));
});

it('authenticates with the api key as bearer token', function () {
    housepedia($this->http)->get('/v5/account');

    $request = lastRequest($this->http);

    expect($request->getHeaderLine('Authorization'))->toBe('Bearer hp_test')
        ->and($request->hasHeader('X-API-Key'))->toBeFalse()
        ->and($request->getHeaderLine('Accept'))->toBe('application/json')
        ->and($request->getHeaderLine('User-Agent'))->toBe('henzeb/housepedia-data-intelligence/' . InstalledVersions::getPrettyVersion('henzeb/housepedia-data-intelligence'))
        ->and($request->hasHeader('Accept-Language'))->toBeFalse()
        ->and((string)$request->getUri())->toBe('https://huispedia.dev/v5/account');
});

it('sends unknown as version when composer does not know the package', function () {
    $installed = InstalledVersions::getAllRawData()[0];
    $without = $installed;
    unset($without['versions']['henzeb/housepedia-data-intelligence']);
    $without['root']['name'] = '__root__';
    $byVendor = new ReflectionProperty(InstalledVersions::class, 'installedByVendor');

    InstalledVersions::reload($without);
    $byVendor->setValue(null, array_fill_keys(
        array_map(fn (string $vendorDir): string => strtr($vendorDir, '\\', '/'), array_keys(ClassLoader::getRegisteredLoaders())),
        $without,
    ));

    try {
        housepedia($this->http)->get('/v5/account');
    } finally {
        InstalledVersions::reload($installed);
    }

    expect(lastRequest($this->http)->getHeaderLine('User-Agent'))->toBe('henzeb/housepedia-data-intelligence/unknown');
});

it('sends the configured language', function () {
    housepedia($this->http, new Config(language: 'nl', retries: 0))->get('/v5/account');

    expect(lastRequest($this->http)->getHeaderLine('Accept-Language'))->toBe('nl');
});

it('uses a custom base url', function () {
    $factory = new HttpFactory();

    (new Transport('hp_test', $this->http, $factory, $factory, new Config(baseUrl: 'https://example.test/')))->get('v5/account');

    expect((string)lastRequest($this->http)->getUri())->toBe('https://example.test/v5/account');
});

it('throws before sending when the api key is missing', function (?string $apiKey) {
    $factory = new HttpFactory();

    expect(fn () => (new Transport($apiKey, $this->http, $factory, $factory))->get('/v5/account'))
        ->toThrow(MissingApiKeyException::class)
        ->and($this->http->getRequests())->toBe([]);
})->with([null, '']);

it('sends unauthenticated requests without an api key', function (?string $apiKey) {
    $factory = new HttpFactory();
    $this->http->addResponse(jsonResponse(jsonFixture('getCatalog')));

    (new Client(new Transport($apiKey, $this->http, $factory, $factory)))->catalog();

    expect(lastRequest($this->http)->hasHeader('Authorization'))->toBeFalse();
})->with([null, '']);

it('omits the api key header for unauthenticated calls', function () {
    transport($this->http)->get('/v5/catalog', authenticate: false);

    expect(lastRequest($this->http)->hasHeader('Authorization'))->toBeFalse();
});

it('builds the query string', function () {
    housepedia($this->http)->get('/v5/addresses/suggest', [
        'q' => 'Amazonenstraat 12, Amsterdam',
        'all' => true,
        'same_type' => false,
        'lat' => 52.35,
        'limit' => 5,
        'types' => null,
    ]);

    expect(lastRequest($this->http)->getUri()->getQuery())
        ->toBe('q=Amazonenstraat%2012%2C%20Amsterdam&all=true&same_type=false&lat=52.35&limit=5');
});

it('posts a json body', function () {
    housepedia($this->http)->post('/v5/batch/valuations', ['data' => [['locator' => '1076LJ-12-2', 'value' => 1.0]]]);

    $request = lastRequest($this->http);

    expect($request->getMethod())->toBe('POST')
        ->and($request->getHeaderLine('Content-Type'))->toBe('application/json')
        ->and((string)$request->getBody())->toBe('{"data":[{"locator":"1076LJ-12-2","value":1.0}]}');
});

it('returns raw responses for any endpoint', function () {
    $this->http->addResponse(jsonResponse(['parcel' => ['area_m2' => 140]], 200, ['X-RateLimit-Remaining' => '4']));

    $response = housepedia($this->http)->get('/v5/properties/1076LJ-12-2/parcel');

    expect($response->status())->toBe(200)
        ->and($response->integer('parcel.area_m2'))->toBe(140)
        ->and($response->header('X-RateLimit-Remaining'))->toBe('4');
});

it('accepts an empty success body', function () {
    $this->http->addResponse(jsonResponse(''));

    expect(housepedia($this->http)->get('/v5/account')->raw())->toBe([]);
});

it('rejects a success body that is not a json object', function (string $body) {
    $this->http->addResponse(jsonResponse($body));

    housepedia($this->http)->get('/v5/account');
})->with(['<html></html>', '"text"'])->throws(UnexpectedResponseTypeException::class);

it('maps problem responses to exceptions', function (int $status, string $code, string $class) {
    $this->http->addResponse(jsonResponse([
        'type' => 'https://huispedia.dev/en/docs/errors#' . $code,
        'title' => 'Title ' . $code,
        'status' => $status,
        'code' => $code,
        'detail' => 'Detail ' . $code,
        'request_id' => 'req_1',
        'errors' => [['field' => 'q', 'message' => 'too short']],
    ], $status));

    try {
        housepedia($this->http)->get('/v5/account');
        $this->fail('No exception thrown');
    } catch (ApiException $exception) {
        expect($exception)->toBeInstanceOf($class)
            ->toBeInstanceOf(HousepediaException::class)
            ->and($exception->status())->toBe($status)
            ->and($exception->getCode())->toBe($status)
            ->and($exception->errorCode())->toBe($code)
            ->and($exception->title())->toBe('Title ' . $code)
            ->and($exception->detail())->toBe('Detail ' . $code)
            ->and($exception->getMessage())->toBe('Detail ' . $code)
            ->and($exception->requestId())->toBe('req_1')
            ->and($exception->type())->toBe('https://huispedia.dev/en/docs/errors#' . $code)
            ->and($exception->errors())->toBe([['field' => 'q', 'message' => 'too short']]);
    }
})->with([
    [400, 'invalid_request', ValidationException::class],
    [401, 'unauthenticated', AuthenticationException::class],
    [401, 'key_revoked', AuthenticationException::class],
    [402, 'insufficient_credits', PaymentRequiredException::class],
    [402, 'spend_cap_reached', PaymentRequiredException::class],
    [403, 'entitlement_required', ForbiddenException::class],
    [404, 'not_found', NotFoundException::class],
    [405, 'method_not_allowed', ApiException::class],
    [409, 'address_ambiguous', AddressAmbiguousException::class],
    [409, 'conflict', ConflictException::class],
    [422, 'data_unavailable', DataUnavailableException::class],
    [429, 'rate_limited', RateLimitException::class],
    [500, 'internal_error', ServerException::class],
    [503, 'upstream_unavailable', ServiceUnavailableException::class],
]);

it('maps errors without a problem body', function () {
    $this->http->addResponse(jsonResponse('<html>Bad gateway</html>', 502));

    try {
        housepedia($this->http)->get('/v5/account');
        $this->fail('No exception thrown');
    } catch (ServerException $exception) {
        expect($exception->errorCode())->toBe('http_502')
            ->and($exception->title())->toBe('Bad Gateway')
            ->and($exception->detail())->toBeNull()
            ->and($exception->errors())->toBe([]);
    }
});

it('sends head requests without a body', function () {
    $this->http->addResponse(new PsrResponse(200));

    $response = transport($this->http)->head('/v5/properties/1076LJ-12-2/transactions');
    $request = lastRequest($this->http);

    expect($request->getMethod())->toBe('HEAD')
        ->and((string)$request->getUri())->toBe('https://huispedia.dev/v5/properties/1076LJ-12-2/transactions')
        ->and($request->getHeaderLine('Authorization'))->toBe('Bearer hp_test')
        ->and((string)$request->getBody())->toBe('')
        ->and($response->status())->toBe(200)
        ->and($response->raw())->toBe([]);
});

it('maps head errors by status', function () {
    $this->http->addResponse(new PsrResponse(404));

    try {
        transport($this->http)->head('/v5/properties/1076LJ-12-2/transactions');
        $this->fail('No exception thrown');
    } catch (NotFoundException $exception) {
        expect($exception->errorCode())->toBe('http_404');
    }
});

it('hydrates the candidates of an ambiguous address created without api with the fallback version', function () {
    $exception = new AddressAmbiguousException(409, 'address_ambiguous', 'Ambiguous', errors: [['bag_id' => '0363200000028421']]);

    expect($exception->candidates()[0])->toBeInstanceOf(Henzeb\Housepedia\Data\Intelligence\V5\DTO\Address::class)
        ->and($exception->candidates()[0]->bagId())->toBe('0363200000028421');
});

it('exposes address candidates on ambiguous addresses', function () {
    $this->http->addResponse(jsonResponse([
        'type' => 'about:blank', 'title' => 'Ambiguous', 'status' => 409, 'code' => 'address_ambiguous',
        'errors' => [
            ['bag_id' => '0363200000028421', 'postcode' => '1076LJ', 'house_number' => 12, 'house_number_ext' => '1'],
            ['bag_id' => '0363200000028422', 'postcode' => '1076LJ', 'house_number' => 12, 'house_number_ext' => '2'],
        ],
    ], 409));

    try {
        housepedia($this->http)->addresses()->withPostcode('1076LJ', 12)->find();
        $this->fail('No exception thrown');
    } catch (AddressAmbiguousException $exception) {
        expect($exception->candidates())->toHaveCount(2)
            ->and($exception->candidates()[1]->houseNumberExt())->toBe('2');
    }
});

it('reads retry after from the header', function (string $header, int $expected) {
    $this->http->addResponse(jsonResponse(['title' => 'Slow down', 'status' => 429, 'code' => 'rate_limited'], 429, ['Retry-After' => $header]));

    try {
        housepedia($this->http)->get('/v5/account');
    } catch (RateLimitException $exception) {
        expect($exception->retryAfter())->toBeGreaterThanOrEqual($expected - 1)->toBeLessThanOrEqual($expected);
    }
})->with([
    ['3', 3],
    [fn () => gmdate('D, d M Y H:i:s \G\M\T', time() + 10), 10],
]);

it('wraps network failures', function () {
    $request = (new HttpFactory())->createRequest('GET', 'https://huispedia.dev');
    $this->http->addException(new NetworkException('Connection refused', $request));

    try {
        housepedia($this->http)->get('/v5/account');
        $this->fail('No exception thrown');
    } catch (TransportException $exception) {
        expect($exception->getMessage())->toBe('Connection refused')
            ->and($exception->getCode())->toBe(0)
            ->and($exception->getPrevious())->toBeInstanceOf(NetworkException::class);
    }
});

it('requires an api key for posts', function () {
    $factory = new HttpFactory();
    $transport = new Transport(null, $this->http, $factory, $factory);

    expect(fn () => $transport->post('/v5/valuations/scenarios', ['data' => []]))
        ->toThrow(MissingApiKeyException::class, 'POST /v5/valuations/scenarios requires an API key; none is configured.')
        ->and($this->http->getRequests())->toBe([]);
});

it('encodes the body without escaping slashes', function () {
    housepedia($this->http)->post('/v5/batch/valuations', ['url' => 'https://huispedia.nl/a/b', 'value' => 1.0]);

    expect((string)lastRequest($this->http)->getBody())->toBe('{"url":"https://huispedia.nl/a/b","value":1.0}');
});

it('treats a body of whitespace as empty', function () {
    $this->http->addResponse(jsonResponse("  \n"));

    expect(housepedia($this->http)->get('/v5/account')->raw())->toBe([]);
});

it('treats statuses below 400 as answers', function () {
    $this->http->addResponse(new PsrResponse(399, [], '{"ok":true}'));

    expect(housepedia($this->http)->get('/v5/account')->boolean('ok'))->toBeTrue();
});

it('maps only address_ambiguous conflicts to ambiguous addresses', function (int $status, string $code, string $class) {
    $this->http->addResponse(jsonResponse(['title' => 'Title', 'status' => $status, 'code' => $code], $status));

    try {
        housepedia($this->http)->get('/v5/account');
        $this->fail('No exception thrown');
    } catch (ApiException $exception) {
        expect($exception::class)->toBe($class);
    }
})->with([
    [409, 'conflict', ConflictException::class],
    [400, 'address_ambiguous', ValidationException::class],
]);

it('maps 499 to an api exception and 500 to a server exception', function (int $status, string $class) {
    $this->http->addResponse(new PsrResponse($status));

    try {
        housepedia($this->http)->get('/v5/account');
        $this->fail('No exception thrown');
    } catch (ApiException $exception) {
        expect($exception::class)->toBe($class);
    }
})->with([
    [499, ApiException::class],
    [500, ServerException::class],
]);

it('titles an error without title or reason phrase with its status', function () {
    $this->http->addResponse(new PsrResponse(599));

    try {
        housepedia($this->http)->get('/v5/account');
        $this->fail('No exception thrown');
    } catch (ServerException $exception) {
        expect($exception->title())->toBe('HTTP 599');
    }
});

it('reads retry after dates in the past and invalid values', function (string $header, ?int $expected) {
    $this->http->addResponse(jsonResponse(['title' => 'Slow down', 'status' => 429, 'code' => 'rate_limited'], 429, ['Retry-After' => $header]));

    try {
        housepedia($this->http)->get('/v5/account');
        $this->fail('No exception thrown');
    } catch (RateLimitException $exception) {
        expect($exception->retryAfter())->toBe($expected);
    }
})->with([
    'past date' => [fn () => gmdate('D, d M Y H:i:s \G\M\T', time() - 60), 0],
    'invalid' => ['soon', null],
]);
