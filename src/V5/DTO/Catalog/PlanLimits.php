<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO\Catalog;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Catalog\PlanLimits as PlanLimitsContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class PlanLimits implements PlanLimitsContract
{
    public function __construct(
        private ?float $requestsPerSecond,
        private ?int $concurrency,
        private ?int $signalCallsPerDay,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->float('requests_per_second'),
            $payload->integer('concurrency'),
            $payload->integer('signal_calls_per_day'),
        );
    }

    public function requestsPerSecond(): ?float
    {
        return $this->requestsPerSecond;
    }

    public function concurrency(): ?int
    {
        return $this->concurrency;
    }

    public function signalCallsPerDay(): ?int
    {
        return $this->signalCallsPerDay;
    }
}
