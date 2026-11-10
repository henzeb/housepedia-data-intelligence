<?php

use Henzeb\Housepedia\Data\Intelligence\Client;
use Henzeb\Housepedia\Data\Intelligence\Contracts;

/**
 * @return array<string, string>
 */
function versionClassesWithContracts(): array
{
    $root = 'Henzeb\\Housepedia\\Data\\Intelligence\\';
    $source = dirname(__DIR__, 2) . '/src/';
    $pairs = [];

    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source . 'V5', FilesystemIterator::SKIP_DOTS)) as $file) {
        $relative = str_replace(['/', '.php'], ['\\', ''], substr($file->getPathname(), strlen($source)));

        $contract = match (true) {
            str_starts_with($relative, 'V5\\Resources\\') => 'Contracts\\' . substr($relative, strlen('V5\\Resources\\')),
            str_starts_with($relative, 'V5\\DTO\\'), str_starts_with($relative, 'V5\\Enums\\') => 'Contracts\\' . substr($relative, strlen('V5\\')),
            $relative === 'V5\\Input\\ScenarioRow' => 'Contracts\\Input\\ScenarioRow',
            $relative === 'V5\\Api' => 'Contracts\\Api',
            default => null,
        };

        if ($contract !== null) {
            $pairs[$root . $relative] = $root . $contract;
        }
    }

    ksort($pairs);

    return $pairs;
}

/**
 * @return list<string>
 */
function publicInstanceMethods(string $class): array
{
    $methods = [];

    foreach ((new ReflectionClass($class))->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        if (!$method->isStatic() && !$method->isConstructor() && !str_starts_with($method->getName(), 'cases')) {
            $methods[] = $method->getName();
        }
    }

    sort($methods);

    return $methods;
}

it('implements the contract of the same name with every public method', function (string $class, string $contract) {
    expect(interface_exists($contract))->toBeTrue($contract)
        ->and(is_subclass_of($class, $contract))->toBeTrue($class . ' implements ' . $contract)
        ->and(publicInstanceMethods($contract))->toBe(publicInstanceMethods($class));
})->with(fn () => array_map(null, array_keys(versionClassesWithContracts()), array_values(versionClassesWithContracts())));

it('has an implementation for every contract', function () {
    $source = dirname(__DIR__, 2) . '/src/Contracts/';
    $contracts = [];

    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS)) as $file) {
        $contracts[] = 'Henzeb\\Housepedia\\Data\\Intelligence\\Contracts\\' . str_replace(['/', '.php'], ['\\', ''], substr($file->getPathname(), strlen($source)));
    }

    sort($contracts);
    $implemented = array_values(array_unique(versionClassesWithContracts()));
    sort($implemented);

    expect($implemented)->toBe($contracts);
});

it('returns the contracts from the client', function (string $method, string $contract) {
    expect((string)(new ReflectionMethod(Client::class, $method))->getReturnType())->toBe($contract);
})->with([
    ['addresses', Contracts\Addresses::class],
    ['properties', Contracts\Properties::class],
    ['buildings', Contracts\Buildings::class],
    ['areas', Contracts\Areas::class],
    ['valuations', Contracts\Valuations::class],
    ['account', Contracts\Account::class],
    ['catalog', Contracts\DTO\Catalog\Catalog::class],
]);
