<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\AccountLimits as AccountLimitsContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class AccountLimits implements AccountLimitsContract
{
    public function __construct(
        private float $requestsPerSecond,
        private int $concurrency,
        private int $monitoredAddressesMax,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->requireFloat('requests_per_second'),
            $payload->requireInteger('concurrency'),
            $payload->requireInteger('monitored_addresses_max'),
        );
    }

    public function requestsPerSecond(): float
    {
        return $this->requestsPerSecond;
    }

    public function concurrency(): int
    {
        return $this->concurrency;
    }

    public function monitoredAddressesMax(): int
    {
        return $this->monitoredAddressesMax;
    }
}
