<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

use DateTimeImmutable;

interface ValuationHistoryPoint
{
    public function date(): DateTimeImmutable;

    public function value(): int;

    public function lower(): int;

    public function upper(): int;

    public function neighbourhoodPricePerSquareMeter(): ?int;

    /**
     * @return array<string, mixed>|null
     */
    public function changedFeatures(): ?array;
}
