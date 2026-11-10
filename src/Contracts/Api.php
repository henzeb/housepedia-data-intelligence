<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Address;
use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Catalog\Catalog;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;
use Henzeb\Housepedia\Data\Intelligence\Transport\Transport;

interface Api
{
    public function addresses(Transport $transport): Addresses;

    public function properties(Transport $transport): Properties;

    public function buildings(Transport $transport): Buildings;

    public function areas(Transport $transport): Areas;

    public function valuations(Transport $transport): Valuations;

    public function account(Transport $transport): Account;

    public function catalog(Transport $transport): Catalog;

    public function address(Payload $payload): Address;
}
