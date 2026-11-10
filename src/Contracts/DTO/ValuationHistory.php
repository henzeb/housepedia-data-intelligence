<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface ValuationHistory
{
    public function address(): Address;

    /**
     * @return list<ValuationHistoryPoint>
     */
    public function points(): array;

    public function current(): ?ValuationHistoryPoint;

    public function meta(): ?Meta;
}
