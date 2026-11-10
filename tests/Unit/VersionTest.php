<?php

use Henzeb\Housepedia\Data\Intelligence\Config;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\UnsupportedVersionException;
use Henzeb\Housepedia\Data\Intelligence\V5\Api as V5Api;

it('normalises a version', function (int|string $version) {
    expect((new Config(version: $version))->version())->toBe('V5')
        ->and((new Config())->withVersion($version)->version())->toBe('V5');
})->with([5, '5', 'v5', 'V5']);

it('uses the fallback version without a version', function () {
    expect((new Config())->version())->toBe(Config::FALLBACK_VERSION)
        ->and(Config::FALLBACK_VERSION)->toBe('V5')
        ->and(Config::VERSIONS)->toContain(Config::FALLBACK_VERSION);
});

it('throws for a version that is not supported', function (int|string $version) {
    expect(fn () => new Config(version: $version))
        ->toThrow(UnsupportedVersionException::class, sprintf('API version "%s" is not supported; use one of V5.', $version))
        ->and(fn () => (new Config())->withVersion($version))->toThrow(UnsupportedVersionException::class);
})->with([4, 6, '6', 'v6', 'vv5', 'V05', '5.0', ' 5', 'v', '', 'latest', -5]);

it('keeps the config unchanged when a version is set', function () {
    $config = new Config(retries: 4);
    $copy = $config->withVersion('v5');

    expect($copy)->not->toBe($config)
        ->and($copy->retries())->toBe(4)
        ->and($config->version())->toBe('V5');
});

it('returns the api of the version', function () {
    expect((new Config(version: 5))->api())->toBeInstanceOf(V5Api::class);
});
