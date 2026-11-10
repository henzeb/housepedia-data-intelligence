<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface AccountLimits
{
    public function requestsPerSecond(): float;

    public function concurrency(): int;

    public function monitoredAddressesMax(): int;
}
