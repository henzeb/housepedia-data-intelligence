<?php

use Henzeb\Housepedia\Data\Intelligence\Client;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\InvalidCoordinateException;
use Http\Mock\Client as MockClient;

beforeEach(function () {
    $this->http = new MockClient();
    $this->http->setDefaultResponse(jsonResponse(jsonFixture('reverseGeocode')));
});

it('sends coordinates given as strings unchanged', function (Closure $call, string $path) {
    $call(housepedia($this->http));

    expect(lastRequest($this->http)->getUri()->getPath())->toBe($path)
        ->and(queryOf(lastRequest($this->http)))->toMatchArray(['lat' => '52.345360320000001', 'lon' => '-4.80']);
})->with([
    'reverse' => [fn (Client $client) => $client->addresses()->reverse('52.345360320000001', '-4.80'), '/v5/addresses/reverse'],
    'lookup' => [fn (Client $client) => $client->areas()->lookupCoordinates('52.345360320000001', '-4.80'), '/v5/areas/lookup'],
]);

it('sends float coordinates the same whatever the precision setting', function (string $precision) {
    $previous = [ini_get('precision'), ini_get('serialize_precision')];
    ini_set('precision', $precision);
    ini_set('serialize_precision', $precision);

    try {
        housepedia($this->http)->addresses()->reverse(52.1, 4.862740123456789);
    } finally {
        ini_set('precision', (string)$previous[0]);
        ini_set('serialize_precision', (string)$previous[1]);
    }

    expect(queryOf(lastRequest($this->http)))->toMatchArray(['lat' => '52.1', 'lon' => '4.862740123456789']);
})->with(['5', '14', '17', '-1']);

it('throws for a coordinate that is not a decimal number without sending a request', function (Closure $call, string $message) {
    expect(fn () => $call(housepedia($this->http)))->toThrow(InvalidCoordinateException::class, $message)
        ->and($this->http->getRequests())->toBe([]);
})->with([
    'comma' => [fn (Client $client) => $client->addresses()->reverse('52,3', '4.8'), 'The latitude "52,3" is not a decimal number such as 52.34536.'],
    'space' => [fn (Client $client) => $client->addresses()->reverse(' 52.3', '4.8'), 'The latitude " 52.3"'],
    'exponent' => [fn (Client $client) => $client->addresses()->reverse('5.2e1', '4.8'), 'The latitude "5.2e1"'],
    'plus' => [fn (Client $client) => $client->addresses()->reverse('+52.3', '4.8'), 'The latitude "+52.3"'],
    'no leading digit' => [fn (Client $client) => $client->addresses()->reverse('.5', '4.8'), 'The latitude ".5"'],
    'trailing point' => [fn (Client $client) => $client->addresses()->reverse('52.', '4.8'), 'The latitude "52."'],
    'empty' => [fn (Client $client) => $client->addresses()->reverse('', '4.8'), 'The latitude ""'],
    'longitude' => [fn (Client $client) => $client->addresses()->reverse('52.3', 'east'), 'The longitude "east"'],
    'not a number' => [fn (Client $client) => $client->addresses()->reverse(NAN, 4.8), 'The latitude "NAN"'],
    'infinite' => [fn (Client $client) => $client->addresses()->reverse(52.3, -INF), 'The longitude "-INF"'],
    'lookup latitude' => [fn (Client $client) => $client->areas()->lookupCoordinates('north', 4.8), 'The latitude "north"'],
    'lookup longitude' => [fn (Client $client) => $client->areas()->lookupCoordinates(52.3, INF), 'The longitude "INF"'],
]);

it('sends floats in raw requests as the shortest exact decimal', function (float $value, string $expected) {
    housepedia($this->http)->get('/v5/addresses/reverse', ['value' => $value]);

    expect(queryOf(lastRequest($this->http))['value'])->toBe($expected);
})->with([
    'sum' => [0.1 + 0.2, '0.30000000000000004'],
    'whole' => [-52.0, '-52'],
    'small' => [0.0000001, '0.0000001'],
    'large' => [1e20, '100000000000000000000'],
    'tiny' => [1e-300, '1.0E-300'],
    'not a number' => [NAN, 'NAN'],
    'infinite' => [INF, 'INF'],
    'negative infinite' => [-INF, '-INF'],
]);
