<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use DateTimeImmutable;
use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\WozValue as WozValueContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class WozValue implements WozValueContract
{
    public function __construct(
        private DateTimeImmutable $referenceDate,
        private int $value,
        private ?int $indexedValue,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->requireDate('reference_date'),
            $payload->requireInteger('value'),
            $payload->integer('indexed_value'),
        );
    }

    public function referenceDate(): DateTimeImmutable
    {
        return $this->referenceDate;
    }

    public function value(): int
    {
        return $this->value;
    }

    public function indexedValue(): ?int
    {
        return $this->indexedValue;
    }
}
