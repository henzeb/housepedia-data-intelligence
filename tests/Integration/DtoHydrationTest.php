<?php

use Henzeb\Housepedia\Data\Intelligence\V5\DTO;
use Henzeb\Housepedia\Data\Intelligence\RateLimit;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;
use Http\Mock\Client as MockClient;

function payloadOf(string $name): Payload
{
    return new Payload(jsonFixture($name), '', new RateLimit(20, 19, 1));
}

/**
 * @return list<string>
 */
function nullPaths(mixed $value, string $path = ''): array
{
    if ($value === null) {
        return [$path];
    }

    if (is_array($value)) {
        $paths = [];

        foreach ($value as $key => $item) {
            $paths = [...$paths, ...nullPaths($item, $path . '.' . $key)];
        }

        return $paths;
    }

    if (!is_object($value) || $value instanceof DateTimeInterface) {
        return [];
    }

    $paths = [];

    foreach ((new ReflectionClass($value))->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        if ($method->isStatic() || $method->getNumberOfParameters() > 0 || !str_starts_with($method->getDeclaringClass()->getName(), 'Henzeb\\Housepedia\\')) {
            continue;
        }

        $paths = [...$paths, ...nullPaths($method->invoke($value), $path . '.' . $method->getName())];
    }

    return $paths;
}

it('hydrates every field of a fully populated response', function (string $fixture, string $class, ?Closure $hydrate = null) {
    $dto = ($hydrate ?? $class::from(...))(payloadOf($fixture));

    $nulls = array_values(array_filter(
        nullPaths($dto),
        static fn (string $path): bool => !preg_match(
            '/\.address\.(bagVerblijfsobjectId|bagPandId|bagStatus|isResidential|neighbourhood|district|municipalityCode|provinceCode|meta)$|^\.areas\.meta$/',
            $path,
        ),
    ));

    expect($dto)->toBeInstanceOf($class)
        ->and($nulls)->toBe([]);
})->with([
    ['getAccount', DTO\Account::class],
    ['getAddress', DTO\Address::class],
    ['getArea', DTO\Area::class],
    ['lookupArea', DTO\AreaHierarchy::class],
    ['getAreaStats', DTO\AreaStatistics::class],
    ['getAreaTrends', DTO\AreaTrends::class],
    ['getAreaDemographics', DTO\Demographics::class],
    ['getBuilding', DTO\Building::class],
    ['getCondition', DTO\Condition::class],
    ['getEnergyLabel', DTO\EnergyLabel::class],
    ['getEnvironment', DTO\Environment::class],
    ['getProperty', DTO\Property::class],
    ['getReferences', DTO\References::class],
    ['getAccountRequests', DTO\Results::class, fn (Payload $payload) => DTO\Results::of($payload, DTO\RequestLogEntry::from(...))],
    ['getSurroundings', DTO\Surroundings::class],
    ['getTransactions', DTO\Transactions::class],
    ['getValuation', DTO\Valuation::class],
    ['getValuationHistory', DTO\ValuationHistory::class],
    ['getValuationPlus', DTO\ValuationPlus::class],
    ['getWoz', DTO\Woz::class],
    ['suggestAddresses', DTO\Results::class, fn (Payload $payload) => DTO\Results::of($payload, DTO\AddressSuggestion::from(...))],
    ['reverseGeocode', DTO\ReverseResults::class],
    ['createScenarioValuations', DTO\Results::class, fn (Payload $payload) => DTO\Results::of($payload, DTO\ScenarioValuation::from(...))],
    ['getAccountUsage', DTO\Results::class, fn (Payload $payload) => DTO\Results::of($payload, DTO\Usage::from(...))],
]);

it('reads every catalog field', function () {
    $catalog = DTO\Catalog\Catalog::from(payloadOf('getCatalog'));

    expect(nullPaths($catalog))->each->toMatch('/\.(contact|multiplier|cacheMaxAge|docsUrl|en|nl|creditPriceInEuros|interval|savingPercent|cadence|licence|attribution|url|requestsPerSecond|burst|concurrency|spendCapDefaultCredits|signalCallsPerDay)$/');
});

it('hydrates the documented valuation sample', function () {
    $http = new MockClient();
    $http->addResponse(jsonResponse(jsonFixture('quickstartValuation')));

    $valuation = housepedia($http)->properties()->withPostcode('1076LJ', 12, '2')->valuation();

    expect($valuation->address()->bagId())->toBe('0363200000028422')
        ->and($valuation->address()->street())->toBe('Amazonenstraat')
        ->and($valuation->address()->houseNumber())->toBe(12)
        ->and($valuation->address()->houseNumberExt())->toBe('2')
        ->and($valuation->address()->location()?->lat())->toBe(52.34536032)
        ->and($valuation->address()->housepediaUrl())->toBe('https://huispedia.nl/amsterdam/1076LJ/amazonenstraat/12-2')
        ->and($valuation->address()->rd())->toBeNull()
        ->and($valuation->valuation()->value())->toBe(888264)
        ->and($valuation->valuation()->lower())->toBe(838099)
        ->and($valuation->valuation()->upper())->toBe(941431)
        ->and($valuation->valuation()->interval())->toBe(0.8)
        ->and($valuation->valuation()->pricePerSquareMeter())->toBe(9064)
        ->and($valuation->valuation()->asOfDate()?->format('Y-m-d'))->toBe('2026-10-26')
        ->and($valuation->valuation()->model())->toBe('avm4')
        ->and($valuation->confidence()?->scorePercentage())->toBe(90.3)
        ->and($valuation->confidence()?->class())->toBe('high')
        ->and($valuation->confidence()?->within20Percent())->toBe(100.0)
        ->and($valuation->meta()?->requestId())->toBe('req_01M5W27EFJTGG4DQDJ6DVK3NVY')
        ->and($valuation->meta()?->credits()?->charged())->toBe(8)
        ->and($valuation->meta()?->credits()?->balance())->toBe(192);
});

it('hydrates nested dtos and lists', function () {
    $property = DTO\Property::from(payloadOf('getProperty'));
    $plus = DTO\ValuationPlus::from(payloadOf('getValuationPlus'));
    $surroundings = DTO\Surroundings::from(payloadOf('getSurroundings'));
    $transactions = DTO\Transactions::from(payloadOf('getTransactions'));
    $trends = DTO\AreaTrends::from(payloadOf('getAreaTrends'));
    $demographics = DTO\Demographics::from(payloadOf('getAreaDemographics'));

    expect($property->ownership()?->leasehold())->toBeInstanceOf(DTO\Leasehold::class)
        ->and($property->construction()?->heating())->toBe(['heating-value'])
        ->and($plus->references())->each->toBeInstanceOf(DTO\Reference::class)
        ->and($plus->references()[0]->price())->toBe(450000)
        ->and($plus->woz())->toBeInstanceOf(DTO\WozValue::class)
        ->and($plus->neighbourhood())->toBeInstanceOf(DTO\NeighbourhoodMarket::class)
        ->and($surroundings->nearest()['school'])->toBeInstanceOf(DTO\NearestPlace::class)
        ->and($surroundings->countsInNeighbourhood())->toBe(['school' => 7])
        ->and($transactions->transactions())->each->toBeInstanceOf(DTO\Transaction::class)
        ->and($trends->points())->each->toBeInstanceOf(DTO\TrendPoint::class)
        ->and($trends->parents()[0] ?? null)->toBeInstanceOf(DTO\TrendSeries::class)
        ->and($demographics->age()?->share0To15())->toBe(1.5);
});

it('hydrates scenario valuations with errors', function () {
    $http = new MockClient();
    $http->addResponse(jsonResponse(['data' => [
        jsonFixture('createScenarioValuations')['data'][0],
        ['error' => ['code' => 'not_found', 'message' => 'No home at this address']],
    ]]));

    $rows = housepedia($http)->valuations()->scenarios([]);

    [$first, $second] = $rows->data();

    expect($rows)->toHaveCount(2)
        ->and($first->scenarios()[0] ?? null)->toBeInstanceOf(DTO\ScenarioResult::class)
        ->and($first->valuation())->toBeInstanceOf(DTO\Estimate::class)
        ->and($second->valuation())->toBeNull()
        ->and($second->scenarios())->toBeNull()
        ->and($second->error()?->code())->toBe('not_found')
        ->and($second->error()?->message())->toBe('No home at this address');
});

it('keeps kadaster prices that are not numbers', function () {
    $fixture = jsonFixture('getTransactions');
    $fixture['data'][0]['price'] = 'withheld';
    $fixture['data'][0]['price_indexed'] = null;

    $transaction = DTO\Transactions::from(new Payload($fixture))->transactions()[0];

    expect($transaction->price())->toBe('withheld')
        ->and($transaction->priceIndexed())->toBeNull();
});

it('exposes the next cursor of the request log', function () {
    $log = DTO\Results::of(new Payload(['data' => [], 'meta' => ['next_cursor' => 'abc']]), DTO\RequestLogEntry::from(...));

    expect($log->data())->toBe([])
        ->and($log->meta()?->nextCursor())->toBe('abc')
        ->and(DTO\Results::of(new Payload(['data' => []]), DTO\RequestLogEntry::from(...))->meta())->toBeNull();
});

it('hydrates the live catalog', function () {
    $catalog = DTO\Catalog\Catalog::from(payloadOf('getCatalog'));
    $address = array_values(array_filter($catalog->products(), fn (DTO\Catalog\Product $product) => $product->id() === 'address'))[0];

    expect($catalog->creditPriceInEuros())->toBe(0.05)
        ->and($catalog->products())->not->toBeEmpty()->each->toBeInstanceOf(DTO\Catalog\Product::class)
        ->and($catalog->packs())->not->toBeEmpty()->each->toBeInstanceOf(DTO\Catalog\Pack::class)
        ->and($catalog->sources())->not->toBeEmpty()->each->toBeInstanceOf(DTO\Catalog\Source::class)
        ->and($catalog->freeTier()?->creditsPerMonth())->toBe(200)
        ->and($catalog->freeTier()?->conditions()?->en())->toBeString()
        ->and($catalog->limits()?->burst())->toBe(20)
        ->and($catalog->limitsByPlan())->toHaveKeys(['free', 'standard'])
        ->and($catalog->limitsByPlan()['free']?->signalCallsPerDay())->toBe(500)
        ->and($address->path())->toBe('/v5/addresses/{locator}')
        ->and($address->method())->toBe('GET')
        ->and($address->credits())->toBe(1)
        ->and($address->status())->toBe('live')
        ->and($address->name()->en())->toBe('Address validation')
        ->and($address->docsUrl()?->nl())->toStartWith('https://')
        ->and($address->contract())->toBeFalse();
});

it('ignores response fields it does not know', function () {
    $payload = jsonFixture('getAddress');
    $payload['field_added_later'] = ['nested' => true];
    $payload['location']['field_added_later'] = 1;

    $address = DTO\Address::from(new Payload($payload));

    expect($address->street())->toBe(DTO\Address::from(payloadOf('getAddress'))->street())
        ->and($address->location()?->lat())->toBe(DTO\Address::from(payloadOf('getAddress'))->location()?->lat());
});

it('reads the catalog values', function () {
    $catalog = DTO\Catalog\Catalog::from(payloadOf('getCatalog'));
    $products = [];

    foreach ($catalog->products() as $product) {
        $products[$product->id()] = $product;
    }

    $suggest = $products['address_suggest'];
    $plus = $products['valuation_plus'];
    $pack = $catalog->packs()[0];
    $source = $catalog->sources()[0];
    $limits = $catalog->limits();
    $free = $catalog->limitsByPlan()['free'];

    expect($suggest->multiplier())->toBe('none')
        ->and($suggest->cacheMaxAge())->toBe(3600)
        ->and($suggest->docsUrl()?->en())->toBe('https://huispedia.dev/en/docs/endpoints/#tag/addresses/GET/v5/addresses/suggest')
        ->and($suggest->contract())->toBeFalse()
        ->and($suggest->contact())->toBeNull()
        ->and($plus->contract())->toBeTrue()
        ->and($plus->contact())->toBe('sales@huispedia.nl')
        ->and($pack->creditPriceInEuros())->toBe(0.0417)
        ->and($pack->interval())->toBe('month')
        ->and($pack->savingPercent())->toBe(17)
        ->and($source->cadence())->toBe('hourly')
        ->and($source->licence())->toBe('Proprietary')
        ->and($source->attribution())->toBe('Huispedia')
        ->and($source->url())->toBe('https://huispedia.nl')
        ->and($limits?->requestsPerSecond())->toBe(6.0)
        ->and($limits?->burst())->toBe(20)
        ->and($limits?->concurrency())->toBe(6)
        ->and($limits?->spendCapDefaultCredits())->toBe(2000)
        ->and($free?->requestsPerSecond())->toBe(1.0)
        ->and($free?->concurrency())->toBe(2)
        ->and($free?->signalCallsPerDay())->toBe(500);
});
