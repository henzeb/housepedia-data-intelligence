<?php

use Henzeb\Housepedia\Data\Intelligence\Client;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO;
use Henzeb\Housepedia\Data\Intelligence\V5\Enums\PropertyFieldGroup;
use Henzeb\Housepedia\Data\Intelligence\V5\Enums\ReferenceSort;
use Henzeb\Housepedia\Data\Intelligence\V5\Enums\SuggestionType;
use Henzeb\Housepedia\Data\Intelligence\V5\Enums\TrendMetric;
use Henzeb\Housepedia\Data\Intelligence\V5\Enums\UsageGrouping;
use Henzeb\Housepedia\Data\Intelligence\V5\Input\Characteristics;
use Henzeb\Housepedia\Data\Intelligence\V5\Input\Scenario;
use Henzeb\Housepedia\Data\Intelligence\V5\Input\ScenarioRow;
use Henzeb\Housepedia\Data\Intelligence\Locators\Locator;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;
use Http\Mock\Client as MockClient;

/**
 * @return array<string, mixed>
 */
function openApiDocument(): array
{
    static $document = null;

    return $document ??= json_decode((string)file_get_contents(__DIR__ . '/../../snapshots/v5/openapi.json'), true, flags: JSON_THROW_ON_ERROR);
}

/**
 * @return array<string, (Closure(Payload): object)|null>
 */
function contractOperations(): array
{
    return [
        'getAccount' => DTO\Account::from(...),
        'getAccountRequests' => fn (Payload $payload) => DTO\Results::of($payload, DTO\RequestLogEntry::from(...)),
        'getAccountUsage' => fn (Payload $payload) => DTO\Results::of($payload, DTO\Usage::from(...)),
        'reverseGeocode' => DTO\ReverseResults::from(...),
        'suggestAddresses' => fn (Payload $payload) => DTO\Results::of($payload, DTO\AddressSuggestion::from(...)),
        'getAddress' => DTO\Address::from(...),
        'lookupArea' => DTO\AreaHierarchy::from(...),
        'getArea' => DTO\Area::from(...),
        'getAreaDemographics' => DTO\Demographics::from(...),
        'getAreaStats' => DTO\AreaStatistics::from(...),
        'getAreaTrends' => DTO\AreaTrends::from(...),
        'getBuilding' => DTO\Building::from(...),
        'getCatalog' => null,
        'getProperty' => DTO\Property::from(...),
        'getCondition' => DTO\Condition::from(...),
        'getEnergyLabel' => DTO\EnergyLabel::from(...),
        'getEnvironment' => DTO\Environment::from(...),
        'getReferences' => DTO\References::from(...),
        'getSurroundings' => DTO\Surroundings::from(...),
        'getTransactions' => DTO\Transactions::from(...),
        'headTransactions' => null,
        'getValuation' => DTO\Valuation::from(...),
        'getValuationHistory' => DTO\ValuationHistory::from(...),
        'getValuationPlus' => DTO\ValuationPlus::from(...),
        'getWoz' => DTO\Woz::from(...),
        'createScenarioValuations' => fn (Payload $payload) => DTO\Results::of($payload, DTO\ScenarioValuation::from(...)),
    ];
}

/**
 * @return array<string, array<string, mixed>>
 */
function openApiResponseSchemas(): array
{
    $schemas = [];

    foreach (openApiDocument()['paths'] as $operations) {
        foreach ($operations as $operation) {
            if (!is_array($operation) || !isset($operation['operationId'])) {
                continue;
            }

            $schemas[$operation['operationId']] = $operation['responses']['200']['content']['application/json']['schema'];
        }
    }

    return $schemas;
}

/**
 * @param array<string, mixed> $schema
 * @return array<string, mixed>
 */
function resolveOpenApiSchema(array $schema): array
{
    if (isset($schema['$ref'])) {
        $name = substr($schema['$ref'], strlen('#/components/schemas/'));

        return resolveOpenApiSchema(openApiDocument()['components']['schemas'][$name]);
    }

    foreach (['anyOf', 'oneOf'] as $keyword) {
        if (isset($schema[$keyword])) {
            foreach ($schema[$keyword] as $variant) {
                if (($variant['type'] ?? null) !== 'null') {
                    return resolveOpenApiSchema($variant);
                }
            }
        }
    }

    if (isset($schema['allOf'])) {
        $merged = [];

        foreach ($schema['allOf'] as $part) {
            $merged = array_merge_recursive($merged, resolveOpenApiSchema($part));
        }

        return $merged;
    }

    if (is_array($schema['type'] ?? null)) {
        $schema['type'] = array_values(array_filter($schema['type'], fn (string $type): bool => $type !== 'null'))[0];
    }

    return $schema;
}

/**
 * @param array<string, mixed> $schema
 * @param array<string, mixed> $leaves
 */
function openApiSample(array $schema, string $path, array &$leaves): mixed
{
    $schema = resolveOpenApiSchema($schema);
    $type = $schema['type'] ?? (isset($schema['properties']) ? 'object' : null);

    if ($type === 'object') {
        $object = [];

        foreach ($schema['properties'] ?? [] as $name => $property) {
            $object[$name] = openApiSample($property, ltrim($path . '.' . $name, '.'), $leaves);
        }

        if (is_array($schema['additionalProperties'] ?? null)) {
            $object['key'] = openApiSample($schema['additionalProperties'], ltrim($path . '.key', '.'), $leaves);
        }

        return $object;
    }

    if ($type === 'array') {
        return [openApiSample($schema['items'] ?? [], ltrim($path . '.0', '.'), $leaves)];
    }

    $number = count($leaves) + 1;

    $value = match ($type) {
        'string' => match ($schema['format'] ?? null) {
            'date' => (new DateTimeImmutable('2020-01-01'))->modify('+' . $number . ' days')->format('Y-m-d'),
            'date-time' => (new DateTimeImmutable('2020-01-01T00:00:00+00:00'))->modify('+' . $number . ' hours')->format(DATE_ATOM),
            default => 'value-' . $number,
        },
        'integer' => $number,
        'number' => $number + 0.5,
        'boolean' => true,
        default => 'value-' . $number,
    };

    $leaves[$path] = $value;

    return $value;
}

function changedOpenApiValue(mixed $value): mixed
{
    return match (true) {
        is_bool($value) => !$value,
        is_int($value), is_float($value) => $value + 1000,
        preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1 => (new DateTimeImmutable($value))->modify('+1 year')->format('Y-m-d'),
        preg_match('/^\d{4}-\d{2}-\d{2}T/', $value) === 1 => (new DateTimeImmutable($value))->modify('+1 year')->format(DATE_ATOM),
        default => $value . '-changed',
    };
}

/**
 * @param array<array-key, mixed> $data
 * @return array<array-key, mixed>
 */
function withOpenApiValue(array $data, string $path, mixed $value): array
{
    $target = &$data;

    foreach (explode('.', $path) as $segment) {
        $target = &$target[$segment];
    }

    $target = $value;

    return $data;
}

function getterOutput(mixed $value): mixed
{
    if (is_array($value)) {
        return array_map(getterOutput(...), $value);
    }

    if ($value instanceof DateTimeInterface) {
        return $value->format(DATE_ATOM);
    }

    if (!is_object($value)) {
        return $value;
    }

    $output = [];

    foreach ((new ReflectionClass($value))->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        if ($method->isStatic() || $method->getNumberOfParameters() > 0 || !str_starts_with($method->getDeclaringClass()->getName(), 'Henzeb\\Housepedia\\')) {
            continue;
        }

        $output[$method->getName()] = getterOutput($method->invoke($value));
    }

    return $output;
}

/**
 * @param Closure(Payload): object $hydrate
 * @param array<array-key, mixed> $data
 */
function hydratedOutput(Closure $hydrate, array $data): mixed
{
    try {
        return getterOutput($hydrate(new Payload($data)));
    } catch (Throwable $exception) {
        return $exception::class . ': ' . $exception->getMessage();
    }
}

it('maps every operation of the OpenAPI document', function () {
    $documented = array_keys(openApiResponseSchemas());
    $mapped = array_keys(contractOperations());

    sort($documented);
    sort($mapped);

    expect(array_values(array_diff($documented, $mapped)))->toBe([])
        ->and(array_values(array_diff($mapped, $documented)))->toBe([]);
});

it('reads every field of the response schema', function (string $operationId) {
    $hydrate = contractOperations()[$operationId];

    if ($hydrate === null) {
        expect(resolveOpenApiSchema(openApiResponseSchemas()[$operationId]))->not->toHaveKey('properties');

        return;
    }

    $leaves = [];
    $sample = openApiSample(openApiResponseSchemas()[$operationId], '', $leaves);
    $base = hydratedOutput($hydrate, $sample);

    expect($base)->toBeArray();

    $unread = [];

    foreach ($leaves as $path => $value) {
        if (hydratedOutput($hydrate, withOpenApiValue($sample, $path, changedOpenApiValue($value))) === $base) {
            $unread[] = $path;
        }
    }

    expect($leaves)->not->toBe([])
        ->and($unread)->toBe([]);
})->with(fn (): array => array_keys(contractOperations()));

/**
 * @return array<string, list<Closure(Client): mixed>>
 */
function contractQueryCalls(): array
{
    $date = new DateTimeImmutable('2026-01-01');
    $home = fn (Client $client) => $client->properties()->withPostcode('1076LJ', 12, '2');

    return [
        'getAccountRequests' => [fn (Client $client) => $client->account()->requests($date, $date, 'key', 200, 10, 'cursor')],
        'getAccountUsage' => [fn (Client $client) => $client->account()->usage($date, $date, [UsageGrouping::Endpoint])],
        'reverseGeocode' => [fn (Client $client) => $client->addresses()->reverse(52.35, 4.87, 100, 3)],
        'suggestAddresses' => [fn (Client $client) => $client->addresses()->suggest('1076LJ', 5, [SuggestionType::Address])],
        'getAddress' => [
            fn (Client $client) => $client->addresses()->withPostcode('1076LJ', 12, '2')->find(true),
            fn (Client $client) => $client->addresses()->withPostcode('1076LJ', 12)->findAll(),
        ],
        'lookupArea' => [
            fn (Client $client) => $client->areas()->lookupPostcode('1076LJ'),
            fn (Client $client) => $client->areas()->lookupCoordinates(52.35, 4.87),
        ],
        'getArea' => [fn (Client $client) => $client->areas()->withMunicipality('GM0363')->find(true)],
        'getAreaTrends' => [fn (Client $client) => $client->areas()->national()->trends(TrendMetric::AskingPricePerSquareMeter, $date, $date, true)],
        'getProperty' => [fn (Client $client) => $home($client)->find([PropertyFieldGroup::Energy], true)],
        'getReferences' => [fn (Client $client) => $home($client)->references(5, 1000, 12, false, 60, 120, 1950, 2000, $date, ReferenceSort::Date)],
        'getValuation' => [fn (Client $client) => $home($client)->valuation($date)],
        'getValuationHistory' => [fn (Client $client) => $home($client)->valuationHistory($date, $date, true)],
        'getValuationPlus' => [fn (Client $client) => $home($client)->valuationPlus($date)],
        'getWoz' => [fn (Client $client) => $home($client)->woz(true)],
    ];
}

/**
 * @return array<string, list<string>>
 */
function omittedQueryParameters(): array
{
    return [
        'getAreaStats' => ['period'],
        'getAreaTrends' => ['interval'],
    ];
}

/**
 * @return array<string, array<string, mixed>>
 */
function openApiQueryParameters(string $operationId): array
{
    foreach (openApiDocument()['paths'] as $operations) {
        foreach ($operations as $operation) {
            if (is_array($operation) && ($operation['operationId'] ?? null) === $operationId) {
                $parameters = [];

                foreach ($operation['parameters'] ?? [] as $parameter) {
                    if ($parameter['in'] === 'query') {
                        $parameters[$parameter['name']] = resolveOpenApiSchema($parameter['schema']) + ['description' => $parameter['description'] ?? null];
                    }
                }

                return $parameters;
            }
        }
    }

    return [];
}

/**
 * @return list<string>
 */
function patternValues(string $pattern): array
{
    preg_match('/^\^\(?([a-z0-9_|]+)\)?/', $pattern, $matches);

    $values = explode('|', $matches[1] ?? '');
    sort($values);

    return $values;
}

it('sends every documented query parameter', function (string $operationId) {
    $documented = array_values(array_diff(array_keys(openApiQueryParameters($operationId)), omittedQueryParameters()[$operationId] ?? []));
    $http = new MockClient();
    $http->setDefaultResponse(jsonResponse([]));
    $sent = [];

    foreach (contractQueryCalls()[$operationId] ?? [] as $call) {
        try {
            $call(housepedia($http));
        } catch (Throwable) {
        }

        $sent = [...$sent, ...array_keys(queryOf(lastRequest($http)))];
    }

    $sent = array_values(array_unique($sent));

    sort($documented);
    sort($sent);

    expect($sent)->toBe($documented);
})->with(fn (): array => array_keys(openApiResponseSchemas()));

it('leaves out only parameters with a single allowed value', function (string $operationId, string $parameter) {
    $schema = openApiQueryParameters($operationId)[$parameter] ?? null;

    expect($schema)->toBeArray()
        ->and($schema['pattern'] ?? '')->toMatch('/^\^[a-z0-9_]+\$$/');
})->with(function (): array {
    $omitted = [];

    foreach (omittedQueryParameters() as $operationId => $parameters) {
        foreach ($parameters as $parameter) {
            $omitted[] = [$operationId, $parameter];
        }
    }

    return $omitted;
});

it('offers every allowed value of a parameter as an enum case', function (string $operationId, string $parameter, string $enum) {
    $values = array_map(fn (BackedEnum $case) => $case->value, $enum::cases());
    sort($values);

    expect(patternValues(openApiQueryParameters($operationId)[$parameter]['pattern'] ?? ''))->toBe($values);
})->with([
    ['getAccountUsage', 'group_by', UsageGrouping::class],
    ['getAreaTrends', 'metric', TrendMetric::class],
    ['getReferences', 'sort', ReferenceSort::class],
]);

/**
 * @return list<string>
 */
function valuePaths(mixed $value, string $path = ''): array
{
    if (!is_array($value) || $value === []) {
        return [$path];
    }

    $paths = [];

    foreach ($value as $key => $item) {
        $paths = [...$paths, ...valuePaths($item, ltrim($path . '.' . $key, '.'))];
    }

    return $paths;
}

/**
 * @return list<string>
 */
function omittedBodyFields(): array
{
    return ['data.0.bag_id', 'data.0.postcode', 'data.0.house_number', 'data.0.house_number_ext'];
}

it('leaves out only documented body fields', function () {
    $leaves = [];
    openApiSample(openApiDocument()['paths']['/v5/valuations/scenarios']['post']['requestBody']['content']['application/json']['schema'], '', $leaves);

    expect(array_values(array_diff(omittedBodyFields(), array_keys($leaves))))->toBe([]);
});

function filledCharacteristics(): Characteristics
{
    $arguments = [];

    foreach ((new ReflectionClass(Characteristics::class))->getConstructor()?->getParameters() ?? [] as $parameter) {
        $type = $parameter->getType();

        $arguments[$parameter->getName()] = match ($type instanceof ReflectionNamedType ? $type->getName() : null) {
            'int' => 1,
            'string' => 'value',
            DateTimeInterface::class => new DateTimeImmutable('2026-01-01'),
        };
    }

    return new Characteristics(...$arguments);
}

it('sends every field of the scenario request body', function () {
    $http = new MockClient();
    $http->setDefaultResponse(jsonResponse([]));

    try {
        housepedia($http)->valuations()->scenarios([
            new ScenarioRow(Locator::postcode('1076LJ', 12, '2'), filledCharacteristics(), [new Scenario('name', filledCharacteristics())]),
        ]);
    } catch (Throwable) {
    }

    $leaves = [];
    openApiSample(openApiDocument()['paths']['/v5/valuations/scenarios']['post']['requestBody']['content']['application/json']['schema'], '', $leaves);
    $documented = array_values(array_diff(array_keys($leaves), omittedBodyFields()));
    $sent = valuePaths(json_decode((string)lastRequest($http)->getBody(), true, flags: JSON_THROW_ON_ERROR));

    sort($documented);
    sort($sent);

    expect($sent)->toBe($documented);
});

it('documents the values of a parameter without pattern as known', function (string $operationId, string $parameter, string $description, array $values) {
    $documented = openApiQueryParameters($operationId)[$parameter]['description'] ?? null;

    expect($documented)->toBe($description);

    foreach ($values as $value) {
        expect($documented)->toMatch('/\\b' . preg_quote($value, '/') . '\\b/');
    }
})->with(function (): array {
    $include = 'Comma list of the extras this endpoint offers, such as rd.';
    $cases = fn (string $enum): array => array_map(fn (BackedEnum $case) => (string)$case->value, $enum::cases());

    return [
        ['suggestAddresses', 'types', 'Comma list of address, street, postcode (all three by default).', $cases(SuggestionType::class)],
        ['getProperty', 'fields', 'Comma list of the groups to return: type, dimensions, rooms, outdoor, construction, energy, ownership, maintenance. All of them by default.', $cases(PropertyFieldGroup::class)],
        ['getAddress', 'include', $include, ['rd']],
        ['getArea', 'include', $include, ['rd']],
        ['getAreaTrends', 'include', $include, ['rd']],
        ['getProperty', 'include', $include, ['rd']],
        ['getValuationHistory', 'include', $include, ['rd']],
    ];
});

it('pins every parameter without pattern that takes a list of values', function () {
    $pinned = ['suggestAddresses.types', 'getProperty.fields', 'getAddress.include', 'getArea.include', 'getAreaTrends.include', 'getProperty.include', 'getValuationHistory.include'];
    $lists = [];

    foreach (array_keys(openApiResponseSchemas()) as $operationId) {
        foreach (openApiQueryParameters($operationId) as $name => $schema) {
            if (!isset($schema['pattern']) && str_starts_with((string)($schema['description'] ?? ''), 'Comma list')) {
                $lists[] = $operationId . '.' . $name;
            }
        }
    }

    sort($pinned);
    sort($lists);

    expect($lists)->toBe($pinned);
});

it('accepts the api key as bearer token on every authenticated operation', function () {
    $document = openApiDocument();
    $withoutBearer = [];

    foreach ($document['paths'] as $item) {
        foreach ($item as $operation) {
            if (is_array($operation) && isset($operation['operationId']) && !in_array(['BearerAuth' => []], $operation['security'] ?? [], true)) {
                $withoutBearer[] = $operation['operationId'];
            }
        }
    }

    expect($document['components']['securitySchemes']['BearerAuth'] ?? null)->toMatchArray(['type' => 'http', 'scheme' => 'bearer'])
        ->and($withoutBearer)->toBe(['getCatalog']);
});

/**
 * @param array<string, mixed> $schema
 * @param list<array{0: string, 1: bool, 2: bool}> $paths
 */
function optionalOpenApiPaths(array $schema, string $path, array &$paths): void
{
    $resolved = resolveOpenApiSchema($schema);
    $type = $resolved['type'] ?? (isset($resolved['properties']) ? 'object' : null);

    if ($type === 'array') {
        optionalOpenApiPaths($resolved['items'] ?? [], ltrim($path . '.0', '.'), $paths);

        return;
    }

    if ($type !== 'object') {
        return;
    }

    foreach ($resolved['properties'] ?? [] as $name => $property) {
        $child = ltrim($path . '.' . $name, '.');
        $nullable = in_array('null', (array)($property['type'] ?? []), true);

        foreach (['anyOf', 'oneOf'] as $keyword) {
            foreach ($property[$keyword] ?? [] as $variant) {
                $nullable = $nullable || ($variant['type'] ?? null) === 'null';
            }
        }

        $optional = !in_array($name, $resolved['required'] ?? [], true);

        if ($optional || $nullable) {
            $paths[] = [$child, $optional, $nullable];
        }

        optionalOpenApiPaths($property, $child, $paths);
    }
}

/**
 * @param array<array-key, mixed> $data
 * @return array<array-key, mixed>
 */
function withoutOpenApiValue(array $data, string $path): array
{
    $segments = explode('.', $path);
    $last = array_pop($segments);
    $target = &$data;

    foreach ($segments as $segment) {
        $target = &$target[$segment];
    }

    unset($target[$last]);

    return $data;
}

it('hydrates when an optional field is absent or a nullable field is null', function (string $operationId) {
    $hydrate = contractOperations()[$operationId];
    $leaves = [];
    $sample = openApiSample(openApiResponseSchemas()[$operationId], '', $leaves);
    $paths = [];
    optionalOpenApiPaths(openApiResponseSchemas()[$operationId], '', $paths);
    $failures = [];

    foreach ($paths as [$path, $optional, $nullable]) {
        $variants = array_filter([
            'absent' => $optional ? withoutOpenApiValue($sample, $path) : null,
            'null' => $nullable ? withOpenApiValue($sample, $path, null) : null,
        ]);

        foreach ($variants as $kind => $data) {
            $output = hydratedOutput($hydrate, $data);

            if (is_string($output)) {
                $failures[] = $path . ' ' . $kind . ': ' . $output;
            }
        }
    }

    expect($paths)->not->toBe([])
        ->and($failures)->toBe([]);
})->with(fn (): array => array_keys(array_filter(contractOperations())));
