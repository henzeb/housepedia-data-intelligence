<?php

use Henzeb\Housepedia\Data\Intelligence\Locators\AreaLocator;
use Henzeb\Housepedia\Data\Intelligence\Locators\Locator;
use Henzeb\Housepedia\Data\Intelligence\Locators\PostcodeAreaLocator;

it('formats a postcode locator', function (string $postcode, int $number, ?string $extension, string $path, string $value) {
    $locator = Locator::postcode($postcode, $number, $extension);

    expect((string)$locator)->toBe($path)
        ->and($locator->value())->toBe($value);
})->with([
    'without extension' => ['1076LJ', 12, null, '1076LJ-12', '1076LJ-12'],
    'with extension' => ['1076LJ', 12, '2', '1076LJ-12-2', '1076LJ-12-2'],
    'normalizes the postcode' => [' 1076 lj ', 12, 'a', '1076LJ-12-a', '1076LJ-12-a'],
    'sends the extension as given' => ['2514KP', 1, ' bis ', '2514KP-1-%20bis%20', '2514KP-1- bis '],
    'empty extension' => ['1076LJ', 12, '', '1076LJ-12', '1076LJ-12'],
    'encodes extension in path' => ['1076LJ', 12, 'A 1', '1076LJ-12-A%201', '1076LJ-12-A 1'],
]);

it('formats a bag id locator', function () {
    $locator = Locator::bagId(' 0363200000028422 ');

    expect((string)$locator)->toBe('bag:0363200000028422')
        ->and($locator->value())->toBe('bag:0363200000028422');
});

it('formats area locators', function (AreaLocator $area, string $expected) {
    expect((string)$area)->toBe($expected);
})->with([
    [fn () => AreaLocator::neighbourhood('BU0363KH05'), 'neighbourhood:BU0363KH05'],
    [fn () => AreaLocator::district('WK0363KH'), 'district:WK0363KH'],
    [fn () => AreaLocator::municipality('GM0363'), 'municipality:GM0363'],
    [fn () => AreaLocator::province('PV27'), 'province:PV27'],
    [fn () => AreaLocator::national(), 'national'],
]);

it('formats postcode area locators', function (PostcodeAreaLocator $area, string $expected) {
    expect((string)$area)->toBe($expected);
})->with([
    [fn () => PostcodeAreaLocator::postcode4(' 1076 '), 'postcode4:1076'],
    [fn () => PostcodeAreaLocator::postcode6('1076 lj'), 'postcode6:1076LJ'],
]);
