<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface NeighbourhoodMarket
{
    public function code(): ?string;

    public function name(): ?string;

    public function medianTransactionPricePerSquareMeter(): ?int;

    public function transactionsLastYear(): ?int;

    public function medianOverbidPercentage(): ?float;

    public function medianDaysOnMarket(): ?float;

    public function pricePerSquareMeterYearOverYearPercentage(): ?float;
}
