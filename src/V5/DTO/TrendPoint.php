<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use DateTimeImmutable;
use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\TrendPoint as TrendPointContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class TrendPoint implements TrendPointContract
{
    public function __construct(
        private DateTimeImmutable $date,
        private float $value,
        private ?float $yearOverYearPercentage,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->requireDate('date'),
            $payload->requireFloat('value'),
            $payload->float('yoy_pct'),
        );
    }

    public function date(): DateTimeImmutable
    {
        return $this->date;
    }

    public function value(): float
    {
        return $this->value;
    }

    public function yearOverYearPercentage(): ?float
    {
        return $this->yearOverYearPercentage;
    }
}
