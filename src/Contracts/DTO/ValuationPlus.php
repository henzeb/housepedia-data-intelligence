<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface ValuationPlus
{
    public function address(): Address;

    public function valuation(): Estimate;

    public function confidence(): ?Confidence;

    public function meta(): ?Meta;

    /**
     * @return list<Reference>
     */
    public function references(): array;

    public function neighbourhood(): ?NeighbourhoodMarket;

    public function woz(): ?WozValue;
}
