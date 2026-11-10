<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5;

use Henzeb\Housepedia\Data\Intelligence\Contracts\Api as ApiContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;
use Henzeb\Housepedia\Data\Intelligence\Transport\Transport;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\Address;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\Catalog\Catalog;
use Henzeb\Housepedia\Data\Intelligence\V5\Resources\Account;
use Henzeb\Housepedia\Data\Intelligence\V5\Resources\Addresses;
use Henzeb\Housepedia\Data\Intelligence\V5\Resources\Areas;
use Henzeb\Housepedia\Data\Intelligence\V5\Resources\Buildings;
use Henzeb\Housepedia\Data\Intelligence\V5\Resources\Properties;
use Henzeb\Housepedia\Data\Intelligence\V5\Resources\Valuations;

class Api implements ApiContract
{
    public function addresses(Transport $transport): Addresses
    {
        return new Addresses($transport);
    }

    public function properties(Transport $transport): Properties
    {
        return new Properties($transport);
    }

    public function buildings(Transport $transport): Buildings
    {
        return new Buildings($transport);
    }

    public function areas(Transport $transport): Areas
    {
        return new Areas($transport);
    }

    public function valuations(Transport $transport): Valuations
    {
        return new Valuations($transport);
    }

    public function account(Transport $transport): Account
    {
        return new Account($transport);
    }

    public function catalog(Transport $transport): Catalog
    {
        return Catalog::from($transport->get('/v5/catalog', authenticate: false)->payload());
    }

    public function address(Payload $payload): Address
    {
        return Address::from($payload);
    }
}
