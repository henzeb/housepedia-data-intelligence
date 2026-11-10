<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface MarketStatistics
{
    public function forSaleCount(): ?int;

    public function transactionsCount(): ?int;

    public function averageTransactionPrice(): ?int;

    public function averageTransactionPricePerSquareMeter(): ?int;

    public function averageAskingPrice(): ?int;

    public function averageAskingPricePerSquareMeter(): ?int;

    public function transactionPricePerSquareMeterYearOverYearPercentage(): ?float;

    public function medianDaysOnMarket(): ?float;

    public function medianOverbidPercentage(): ?float;

    public function shareSoldAboveAsking(): ?float;

    public function listingsPer1000Properties(): ?float;

    public function medianEstimatedHomeValue(): ?int;
}
