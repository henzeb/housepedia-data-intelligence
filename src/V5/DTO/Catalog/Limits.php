<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO\Catalog;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Catalog\Limits as LimitsContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class Limits implements LimitsContract
{
    public function __construct(
        private ?float $requestsPerSecond,
        private ?int $burst,
        private ?int $concurrency,
        private ?int $spendCapDefaultCredits,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->float('requests_per_second'),
            $payload->integer('burst'),
            $payload->integer('concurrency'),
            $payload->integer('spend_cap_default_credits'),
        );
    }

    public function requestsPerSecond(): ?float
    {
        return $this->requestsPerSecond;
    }

    public function burst(): ?int
    {
        return $this->burst;
    }

    public function concurrency(): ?int
    {
        return $this->concurrency;
    }

    public function spendCapDefaultCredits(): ?int
    {
        return $this->spendCapDefaultCredits;
    }
}
