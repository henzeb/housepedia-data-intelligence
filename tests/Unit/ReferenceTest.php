<?php

/**
 * @return array<string, string>
 */
function referenceSections(): array
{
    $sections = [];

    foreach (glob(dirname(__DIR__, 2) . '/docs/reference/*.md') ?: [] as $file) {
        $parts = preg_split('/^### `([A-Za-z]+)`$/m', (string)file_get_contents($file), -1, PREG_SPLIT_DELIM_CAPTURE) ?: [];

        for ($index = 1; $index < count($parts); $index += 2) {
            $sections[$parts[$index]] = ($sections[$parts[$index]] ?? '') . $parts[$index + 1];
        }
    }

    return $sections;
}

/**
 * @return list<string>
 */
function documentedContracts(): array
{
    $source = dirname(__DIR__, 2) . '/src/Contracts/';
    $contracts = [];

    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS)) as $file) {
        $relative = str_replace(['/', '.php'], ['\\', ''], substr($file->getPathname(), strlen($source)));

        if ($relative !== 'Api' && !str_starts_with($relative, 'Enums\\') && !str_starts_with($relative, 'Input\\')) {
            $contracts[] = 'Henzeb\\Housepedia\\Data\\Intelligence\\Contracts\\' . $relative;
        }
    }

    sort($contracts);

    return $contracts;
}

it('is generated from the current sdk and snapshots', function () {
    exec(PHP_BINARY . ' ' . escapeshellarg(dirname(__DIR__, 2) . '/bin/generate-reference') . ' --check 2>&1', $output, $status);

    expect($status)->toBe(0, implode("\n", $output));
});

it('documents every method of every resource and response object', function (string $contract) {
    $reflection = new ReflectionClass($contract);
    $section = referenceSections()[$reflection->getShortName()] ?? null;

    expect($section)->not->toBeNull('No section for ' . $reflection->getShortName());

    foreach ($reflection->getMethods() as $method) {
        if (!$method->isStatic() && $method->getDeclaringClass()->getName() === $contract) {
            expect(str_contains((string)$section, '| `' . $method->getName() . '('))->toBeTrue($reflection->getShortName() . '::' . $method->getName() . ' is not documented');
        }
    }
})->with(fn () => documentedContracts());

it('documents the input enums and objects', function (string $name) {
    expect(referenceSections())->toHaveKey($name);
})->with(['SuggestionType', 'PropertyFieldGroup', 'ReferenceSort', 'TrendMetric', 'UsageGrouping', 'ScenarioRow', 'Scenario', 'Characteristics', 'RateLimit']);

it('describes every method of every response object', function () {
    $empty = [];

    foreach (referenceSections() as $name => $section) {
        foreach (explode("\n", $section) as $line) {
            $cells = array_map('trim', explode('|', $line));

            if (count($cells) === 6 && preg_match('/^`(\w+)\(\)`$/', $cells[1], $method) && $cells[4] === '') {
                $empty[] = $name . '::' . $method[1];
            }
        }
    }

    expect($empty)->toBe([]);
});

it('only has descriptions for methods that exist', function () {
    $descriptions = require dirname(__DIR__, 2) . '/bin/reference-descriptions.php';
    $unknown = [];

    foreach (array_keys($descriptions) as $key) {
        [$name, $method] = explode('::', $key);
        $section = referenceSections()[$name] ?? '';

        if (!str_contains($section, '| `' . $method . '()` |')) {
            $unknown[] = $key;
        }
    }

    expect($unknown)->toBe([]);
});
