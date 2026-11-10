<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use DateTimeImmutable;
use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\ValuationHistoryPoint as ValuationHistoryPointContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class ValuationHistoryPoint implements ValuationHistoryPointContract
{
    /**
     * @param array<string, mixed>|null $changedFeatures
     */
    public function __construct(
        private DateTimeImmutable $date,
        private int $value,
        private int $lower,
        private int $upper,
        private ?int $neighbourhoodPricePerSquareMeter,
        private ?array $changedFeatures,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->requireDate('date'),
            $payload->requireInteger('value'),
            $payload->requireInteger('lower'),
            $payload->requireInteger('upper'),
            $payload->integer('neighbourhood_price_per_m2'),
            $payload->associative('changed_features'),
        );
    }

    public function date(): DateTimeImmutable
    {
        return $this->date;
    }

    public function value(): int
    {
        return $this->value;
    }

    public function lower(): int
    {
        return $this->lower;
    }

    public function upper(): int
    {
        return $this->upper;
    }

    public function neighbourhoodPricePerSquareMeter(): ?int
    {
        return $this->neighbourhoodPricePerSquareMeter;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function changedFeatures(): ?array
    {
        return $this->changedFeatures;
    }
}
