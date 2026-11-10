<?php

use GuzzleHttp\Psr7\Response as PsrResponse;
use Henzeb\Housepedia\Data\Intelligence\Client;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO;
use Henzeb\Housepedia\Data\Intelligence\V5\Enums\PropertyFieldGroup;
use Henzeb\Housepedia\Data\Intelligence\V5\Enums\ReferenceSort;
use Henzeb\Housepedia\Data\Intelligence\V5\Enums\SuggestionType;
use Henzeb\Housepedia\Data\Intelligence\V5\Enums\TrendMetric;
use Henzeb\Housepedia\Data\Intelligence\V5\Enums\UsageGrouping;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\ForbiddenException;
use Henzeb\Housepedia\Data\Intelligence\V5\Input\Characteristics;
use Henzeb\Housepedia\Data\Intelligence\V5\Input\Scenario;
use Henzeb\Housepedia\Data\Intelligence\V5\Input\ScenarioRow;
use Henzeb\Housepedia\Data\Intelligence\Locators\Locator;
use Http\Mock\Client as MockClient;

it('calls the documented endpoint and hydrates a dto', function (
    string $fixture,
    Closure $call,
    string $method,
    string $path,
    array $query,
    string $expected,
) {
    $http = new MockClient();
    $http->addResponse(jsonResponse(jsonFixture($fixture)));

    $result = $call(housepedia($http));
    $request = lastRequest($http);

    expect($request->getMethod())->toBe($method)
        ->and($request->getUri()->getPath())->toBe($path)
        ->and(queryOf($request))->toBe($query)
        ->and($http->getRequests())->toHaveCount(1);

    if ($result instanceof DTO\Results) {
        expect($result->data())->toBeList()->not->toBeEmpty()->each->toBeInstanceOf($expected)
            ->and($result->meta())->toBeInstanceOf(DTO\Meta::class);

        return;
    }

    expect($result)->toBeInstanceOf($expected);
})->with([
    'address suggest' => ['suggestAddresses', fn (Client $c) => $c->addresses()->suggest('1076LJ 12-2'), 'GET', '/v5/addresses/suggest', ['q' => '1076LJ 12-2'], DTO\AddressSuggestion::class],
    'address suggest options' => ['suggestAddresses', fn (Client $c) => $c->addresses()->suggest('1076LJ', 5, [SuggestionType::Address, SuggestionType::Street]), 'GET', '/v5/addresses/suggest', ['q' => '1076LJ', 'limit' => '5', 'types' => 'address,street'], DTO\AddressSuggestion::class],
    'address reverse' => ['reverseGeocode', fn (Client $c) => $c->addresses()->reverse(52.35, 4.87), 'GET', '/v5/addresses/reverse', ['lat' => '52.35', 'lon' => '4.87'], DTO\ReverseAddress::class],
    'address reverse strings' => ['reverseGeocode', fn (Client $c) => $c->addresses()->reverse('52.35', '4.87'), 'GET', '/v5/addresses/reverse', ['lat' => '52.35', 'lon' => '4.87'], DTO\ReverseAddress::class],
    'address reverse options' => ['reverseGeocode', fn (Client $c) => $c->addresses()->reverse(52.35, 4.87, 100, 3), 'GET', '/v5/addresses/reverse', ['lat' => '52.35', 'lon' => '4.87', 'radius_m' => '100', 'limit' => '3'], DTO\ReverseAddress::class],
    'address by postcode' => ['getAddress', fn (Client $c) => $c->addresses()->withPostcode('1076LJ', 12, '2')->find(), 'GET', '/v5/addresses/1076LJ-12-2', [], DTO\Address::class],
    'address by postcode with extension' => ['getAddress', fn (Client $c) => $c->addresses()->withPostcode('1076LJ', 12, '2')->find(true), 'GET', '/v5/addresses/1076LJ-12-2', ['include' => 'rd'], DTO\Address::class],
    'address by bag id' => ['getAddress', fn (Client $c) => $c->addresses()->withBagId('0363200000028422')->find(), 'GET', '/v5/addresses/bag:0363200000028422', [], DTO\Address::class],
    'address all' => ['getAddressAll', fn (Client $c) => $c->addresses()->withPostcode('1076LJ', 12)->findAll(), 'GET', '/v5/addresses/1076LJ-12', ['all' => 'true'], DTO\Address::class],
    'address all single' => ['getAddress', fn (Client $c) => $c->addresses()->withPostcode('1076LJ', 12, '2')->findAll(true), 'GET', '/v5/addresses/1076LJ-12-2', ['all' => 'true', 'include' => 'rd'], DTO\Address::class],
    'property' => ['getProperty', fn (Client $c) => $c->properties()->withPostcode('1076LJ', 12, '2')->find(), 'GET', '/v5/properties/1076LJ-12-2', [], DTO\Property::class],
    'property fields' => ['getProperty', fn (Client $c) => $c->properties()->withBagId('0363200000028422')->find([PropertyFieldGroup::Type, PropertyFieldGroup::Rooms], true), 'GET', '/v5/properties/bag:0363200000028422', ['fields' => 'type,rooms', 'include' => 'rd'], DTO\Property::class],
    'energy label' => ['getEnergyLabel', fn (Client $c) => $c->properties()->withPostcode('1076LJ', 12, '2')->energyLabel(), 'GET', '/v5/properties/1076LJ-12-2/energy-label', [], DTO\EnergyLabel::class],
    'woz' => ['getWoz', fn (Client $c) => $c->properties()->withPostcode('1076LJ', 12, '2')->woz(), 'GET', '/v5/properties/1076LJ-12-2/woz', [], DTO\Woz::class],
    'woz indexed' => ['getWoz', fn (Client $c) => $c->properties()->withPostcode('1076LJ', 12, '2')->woz(true), 'GET', '/v5/properties/1076LJ-12-2/woz', ['index' => 'true'], DTO\Woz::class],
    'condition' => ['getCondition', fn (Client $c) => $c->properties()->withPostcode('1076LJ', 12, '2')->condition(), 'GET', '/v5/properties/1076LJ-12-2/condition', [], DTO\Condition::class],
    'transactions' => ['getTransactions', fn (Client $c) => $c->properties()->withPostcode('1076LJ', 12, '2')->transactions(), 'GET', '/v5/properties/1076LJ-12-2/transactions', [], DTO\Transactions::class],
    'valuation' => ['getValuation', fn (Client $c) => $c->properties()->withBagId('0363200000028422')->valuation(), 'GET', '/v5/properties/bag:0363200000028422/valuation', [], DTO\Valuation::class],
    'valuation as of' => ['getValuation', fn (Client $c) => $c->properties()->withBagId('0363200000028422')->valuation(new DateTimeImmutable('2026-08-01')), 'GET', '/v5/properties/bag:0363200000028422/valuation', ['as_of_date' => '2026-08-01'], DTO\Valuation::class],
    'valuation plus' => ['getValuationPlus', fn (Client $c) => $c->properties()->withPostcode('1076LJ', 12, '2')->valuationPlus(new DateTimeImmutable('2026-08-01')), 'GET', '/v5/properties/1076LJ-12-2/valuation/plus', ['as_of_date' => '2026-08-01'], DTO\ValuationPlus::class],
    'valuation history' => ['getValuationHistory', fn (Client $c) => $c->properties()->withPostcode('1076LJ', 12, '2')->valuationHistory(new DateTimeImmutable('2024-01-01'), new DateTimeImmutable('2026-01-01'), true), 'GET', '/v5/properties/1076LJ-12-2/valuation/history', ['from' => '2024-01-01', 'to' => '2026-01-01', 'include' => 'rd'], DTO\ValuationHistory::class],
    'references' => ['getReferences', fn (Client $c) => $c->properties()->withPostcode('1076LJ', 12, '2')->references(), 'GET', '/v5/properties/1076LJ-12-2/references', [], DTO\References::class],
    'references filters' => ['getReferences', fn (Client $c) => $c->properties()->withPostcode('1076LJ', 12, '2')->references(5, 1000, 12, false, 60, 120, 1950, 2000, new DateTimeImmutable('2026-10-01'), ReferenceSort::Distance), 'GET', '/v5/properties/1076LJ-12-2/references', ['limit' => '5', 'max_distance_m' => '1000', 'max_age_months' => '12', 'same_type' => 'false', 'min_living_area_m2' => '60', 'max_living_area_m2' => '120', 'min_construction_year' => '1950', 'max_construction_year' => '2000', 'as_of_date' => '2026-10-01', 'sort' => 'distance'], DTO\References::class],
    'surroundings' => ['getSurroundings', fn (Client $c) => $c->properties()->withPostcode('1076LJ', 12, '2')->surroundings(), 'GET', '/v5/properties/1076LJ-12-2/surroundings', [], DTO\Surroundings::class],
    'environment' => ['getEnvironment', fn (Client $c) => $c->properties()->withPostcode('1076LJ', 12, '2')->environment(), 'GET', '/v5/properties/1076LJ-12-2/environment', [], DTO\Environment::class],
    'building' => ['getBuilding', fn (Client $c) => $c->buildings()->withBagPandId('0363100012345678')->find(), 'GET', '/v5/buildings/0363100012345678', [], DTO\Building::class],
    'area lookup postcode' => ['lookupArea', fn (Client $c) => $c->areas()->lookupPostcode('1076 LJ'), 'GET', '/v5/areas/lookup', ['postcode' => '1076LJ'], DTO\AreaHierarchy::class],
    'area lookup coordinates' => ['lookupArea', fn (Client $c) => $c->areas()->lookupCoordinates(52.35, 4.87), 'GET', '/v5/areas/lookup', ['lat' => '52.35', 'lon' => '4.87'], DTO\AreaHierarchy::class],
    'area' => ['getArea', fn (Client $c) => $c->areas()->withMunicipality('GM0363')->find(true), 'GET', '/v5/areas/municipality:GM0363', ['include' => 'rd'], DTO\Area::class],
    'area stats' => ['getAreaStats', fn (Client $c) => $c->areas()->withNeighbourhood('BU0363KH05')->stats(), 'GET', '/v5/areas/neighbourhood:BU0363KH05/stats', [], DTO\AreaStatistics::class],
    'area trends' => ['getAreaTrends', fn (Client $c) => $c->areas()->national()->trends(TrendMetric::AskingPricePerSquareMeter, new DateTimeImmutable('2022-01-01'), new DateTimeImmutable('2026-01-01')), 'GET', '/v5/areas/national/trends', ['metric' => 'asking_price_per_m2', 'from' => '2022-01-01', 'to' => '2026-01-01'], DTO\AreaTrends::class],
    'area demographics' => ['getAreaDemographics', fn (Client $c) => $c->areas()->withDistrict('WK0363KH')->demographics(), 'GET', '/v5/areas/district:WK0363KH/demographics', [], DTO\Demographics::class],
    'area province' => ['getArea', fn (Client $c) => $c->areas()->withProvince('PV27')->find(), 'GET', '/v5/areas/province:PV27', [], DTO\Area::class],
    'area postcode4' => ['getArea', fn (Client $c) => $c->areas()->withPostcode4('1076')->find(), 'GET', '/v5/areas/postcode4:1076', [], DTO\Area::class],
    'area postcode6' => ['getArea', fn (Client $c) => $c->areas()->withPostcode6('1076LJ')->find(), 'GET', '/v5/areas/postcode6:1076LJ', [], DTO\Area::class],
    'scenarios' => ['createScenarioValuations', fn (Client $c) => $c->valuations()->scenarios([new ScenarioRow(Locator::postcode('1076LJ', 12, '2'))]), 'POST', '/v5/valuations/scenarios', [], DTO\ScenarioValuation::class],
    'account' => ['getAccount', fn (Client $c) => $c->account()->details(), 'GET', '/v5/account', [], DTO\Account::class],
    'postcode area with rd' => ['getArea', fn (Client $c) => $c->areas()->withPostcode4('1076')->find(true), 'GET', '/v5/areas/postcode4:1076', ['include' => 'rd'], DTO\Area::class],
    'area code with spaces' => ['getArea', fn (Client $c) => $c->areas()->withMunicipality(' GM0363 ')->find(), 'GET', '/v5/areas/municipality:GM0363', [], DTO\Area::class],
    'building id with spaces' => ['getBuilding', fn (Client $c) => $c->buildings()->withBagPandId(' 0363100012345678 ')->find(), 'GET', '/v5/buildings/0363100012345678', [], DTO\Building::class],
    'postcode with tabs and newlines' => ['getAddress', fn (Client $c) => $c->addresses()->withPostcode("\t1076lj\n", 12)->find(), 'GET', '/v5/addresses/1076LJ-12', [], DTO\Address::class],
    'postcode area with tabs and newlines' => ['getArea', fn (Client $c) => $c->areas()->withPostcode6("\t1076lj\n")->find(), 'GET', '/v5/areas/postcode6:1076LJ', [], DTO\Area::class],
    'address suggest string types' => ['suggestAddresses', fn (Client $c) => $c->addresses()->suggest('1076LJ', types: [SuggestionType::Address, 'building']), 'GET', '/v5/addresses/suggest', ['q' => '1076LJ', 'types' => 'address,building'], DTO\AddressSuggestion::class],
    'property string fields' => ['getProperty', fn (Client $c) => $c->properties()->withBagId('0363200000028422')->find([PropertyFieldGroup::Type, 'parcel']), 'GET', '/v5/properties/bag:0363200000028422', ['fields' => 'type,parcel'], DTO\Property::class],
    'references string sort' => ['getReferences', fn (Client $c) => $c->properties()->withPostcode('1076LJ', 12, '2')->references(sort: 'price'), 'GET', '/v5/properties/1076LJ-12-2/references', ['sort' => 'price'], DTO\References::class],
    'area trends string metric' => ['getAreaTrends', fn (Client $c) => $c->areas()->national()->trends('rent_per_m2'), 'GET', '/v5/areas/national/trends', ['metric' => 'rent_per_m2'], DTO\AreaTrends::class],
    'account usage string grouping' => ['getAccountUsage', fn (Client $c) => $c->account()->usage(groupBy: [UsageGrouping::Day, 'country']), 'GET', '/v5/account/usage', ['group_by' => 'day,country'], DTO\Usage::class],
    'account usage' => ['getAccountUsage', fn (Client $c) => $c->account()->usage(new DateTimeImmutable('2026-10-01'), new DateTimeImmutable('2026-10-31'), [UsageGrouping::Day, UsageGrouping::Endpoint]), 'GET', '/v5/account/usage', ['from' => '2026-10-01', 'to' => '2026-10-31', 'group_by' => 'day,endpoint'], DTO\Usage::class],
    'account requests' => ['getAccountRequests', fn (Client $c) => $c->account()->requests(new DateTimeImmutable('2026-10-01T00:00:00+00:00'), null, 'key_1', 404, 50, 'cursor_1'), 'GET', '/v5/account/requests', ['from' => '2026-10-01T00:00:00+00:00', 'key_id' => 'key_1', 'status' => '404', 'limit' => '50', 'cursor' => 'cursor_1'], DTO\RequestLogEntry::class],
    'catalog' => ['getCatalog', fn (Client $c) => $c->catalog(), 'GET', '/v5/catalog', [], DTO\Catalog\Catalog::class],
]);

it('posts scenario rows in the documented format', function () {
    $http = new MockClient();
    $http->addResponse(jsonResponse(jsonFixture('createScenarioValuations')));

    housepedia($http)->valuations()->scenarios([
        new ScenarioRow(
            Locator::bagId('0363200000028422'),
            new Characteristics(livingAreaInSquareMeters: 98),
            [new Scenario('label A', new Characteristics(energyLabel: 'A'))],
        ),
    ]);

    expect(json_decode((string)lastRequest($http)->getBody(), true))->toBe([
        'data' => [
            [
                'locator' => 'bag:0363200000028422',
                'living_area_m2' => 98,
                'scenarios' => [['name' => 'label A', 'energy_label' => 'A']],
            ],
        ],
    ]);
});

it('checks whether a home has transactions', function (Closure $locate, string $path) {
    $http = new MockClient();
    $http->addResponse(new PsrResponse(200));
    $http->addResponse(new PsrResponse(404));

    $home = $locate(housepedia($http)->properties());

    expect($home->hasTransactions())->toBeTrue()
        ->and(lastRequest($http)->getMethod())->toBe('HEAD')
        ->and(lastRequest($http)->getUri()->getPath())->toBe($path)
        ->and($home->hasTransactions())->toBeFalse()
        ->and($http->getRequests())->toHaveCount(2);
})->with([
    'postcode' => [fn ($properties) => $properties->withPostcode('1076LJ', 12, '2'), '/v5/properties/1076LJ-12-2/transactions'],
    'bag id' => [fn ($properties) => $properties->withBagId('0363200000028422'), '/v5/properties/bag:0363200000028422/transactions'],
]);

it('throws other errors when checking for transactions', function () {
    $http = new MockClient();
    $http->addResponse(new PsrResponse(403));

    housepedia($http)->properties()->withPostcode('1076LJ', 12, '2')->hasTransactions();
})->throws(ForbiddenException::class);

it('does not send the api key for the catalog', function () {
    $http = new MockClient();
    $http->addResponse(jsonResponse(jsonFixture('getCatalog')));

    housepedia($http)->catalog();

    expect(lastRequest($http)->hasHeader('Authorization'))->toBeFalse();
});

it('returns every address of a list answer', function () {
    $fixture = jsonFixture('getAddressAll');
    $second = $fixture['data'][0];
    $second['bag_id'] = '0363200000028421';
    $fixture['data'][] = $second;

    $http = new MockClient();
    $http->addResponse(jsonResponse($fixture));

    $addresses = housepedia($http)->addresses()->withPostcode('1076LJ', 12)->findAll();

    expect($addresses)->toHaveCount(2)
        ->and($addresses->data()[0]->bagId())->toBe($fixture['data'][0]['bag_id'])
        ->and($addresses->data()[1]->bagId())->toBe('0363200000028421')
        ->and($addresses->data()[0]->meta())->toBeNull()
        ->and($addresses->meta()?->requestId())->toBe($fixture['meta']['request_id']);
});

it('returns a single address answer of findAll with its meta', function () {
    $http = new MockClient();
    $http->addResponse(jsonResponse(jsonFixture('getAddress')));

    $addresses = housepedia($http)->addresses()->withPostcode('1076LJ', 12, '2')->findAll();

    expect($addresses)->toHaveCount(1)
        ->and($addresses->meta()?->requestId())->toBe(jsonFixture('getAddress')['meta']['request_id']);
});

it('returns the areas of the reverse answer', function () {
    $http = new MockClient();
    $http->addResponse(jsonResponse(jsonFixture('reverseGeocode')));

    $nearby = housepedia($http)->addresses()->reverse(52.35, 4.87);

    expect($nearby)->toBeInstanceOf(DTO\ReverseResults::class)
        ->and($nearby->areas())->toBeInstanceOf(DTO\AreaHierarchy::class)
        ->and($nearby->areas()?->municipality()?->code())->toBe(jsonFixture('reverseGeocode')['areas']['municipality']['code']);
});
