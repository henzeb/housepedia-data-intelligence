# Housepedia Data Intelligence

[![Build Status](https://github.com/henzeb/housepedia-data-intelligence/workflows/tests/badge.svg)](https://github.com/henzeb/housepedia-data-intelligence/actions)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/henzeb/housepedia-data-intelligence.svg?style=flat-square)](https://packagist.org/packages/henzeb/housepedia-data-intelligence)
[![Total Downloads](https://img.shields.io/packagist/dt/henzeb/housepedia-data-intelligence.svg?style=flat-square)](https://packagist.org/packages/henzeb/housepedia-data-intelligence)
[![License](https://img.shields.io/packagist/l/henzeb/housepedia-data-intelligence)](https://packagist.org/packages/henzeb/housepedia-data-intelligence)

A framework-independent PHP SDK for the [Housepedia Data Intelligence API](https://huispedia.dev)
(v5): home values, property characteristics, WOZ values, energy labels, transactions,
buildings and neighbourhood statistics for every address in the Netherlands.

Create an account and an API key at [huispedia.dev](https://huispedia.dev). The
[API reference](https://huispedia.dev/en/docs/endpoints/) and
[pricing](https://huispedia.dev/en/docs/credits/) are in the
[documentation](https://huispedia.dev/en/docs/quickstart/).

Responses are returned as immutable, typed objects. Endpoints that return a list return a
plain PHP `list` of those objects.

Using Laravel? Install [henzeb/housepedia-data-intelligence-laravel](https://github.com/henzeb/housepedia-data-intelligence-laravel)
for configuration, a facade and Laravel Collections.

## Table of contents

- [Installation](#installation)
- [Usage](#usage)
- [Documentation](#documentation)
- [Changelog](#changelog)
- [Testing this package](#testing-this-package)
- [Security](#security)
- [Credits](#credits)
- [License](#license)

## Installation

You may install the package via Composer:

```bash
composer require henzeb/housepedia-data-intelligence
```

The SDK sends its requests through any PSR-18 HTTP client. When your project already has one,
such as Guzzle or the Symfony HTTP client, it is discovered automatically.

## Usage

```php
use Henzeb\Housepedia\Data\Intelligence\Client;

$client = Client::create($apiKey);

$address = $client->addresses()
    ->withPostcode('1076LJ', 12, '2')
    ->find();

$address->street();
$address->city();

$suggestions = $client->addresses()
    ->suggest('1076LJ 12-2'); // Results<AddressSuggestion>

$valuation = $client->properties()
    ->withPostcode('1076LJ', 12, '2')
    ->valuation();

$valuation->valuation()->value(); // 888264
```

## Documentation

- [Getting Started](docs/getting-started.md)
- [Addresses](docs/addresses.md)
- [Properties](docs/properties.md)
- [Buildings](docs/buildings.md)
- [Areas](docs/areas.md)
- [Scenario Valuations](docs/valuations.md)
- [Account and Catalog](docs/account-and-catalog.md)
- [Raw Requests](docs/raw-requests.md)
- [Caching](docs/caching.md)
- [Using this SDK with an AI agent](docs/agents.md)
- [Errors](docs/errors.md)
- Reference: [addresses](docs/reference/addresses.md), [properties](docs/reference/properties.md), [buildings](docs/reference/buildings.md), [areas](docs/reference/areas.md), [valuations](docs/reference/valuations.md), [account](docs/reference/account.md), [catalog](docs/reference/catalog.md), [shared objects](docs/reference/shared.md)

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

## Testing this package

```bash
composer test
composer analyse
composer format-check
```

## Security

See [SECURITY.md](SECURITY.md) for how to report a vulnerability.

## Credits

- [Henze Berkheij](https://github.com/henzeb)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
