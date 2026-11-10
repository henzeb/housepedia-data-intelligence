<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\MarketStatistics as MarketStatisticsContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class MarketStatistics implements MarketStatisticsContract
{
    public function __construct(
        private ?int $forSaleCount,
        private ?int $transactionsCount,
        private ?int $averageTransactionPrice,
        private ?int $averageTransactionPricePerSquareMeter,
        private ?int $averageAskingPrice,
        private ?int $averageAskingPricePerSquareMeter,
        private ?float $transactionPricePerSquareMeterYearOverYearPercentage,
        private ?float $medianDaysOnMarket,
        private ?float $medianOverbidPercentage,
        private ?float $shareSoldAboveAsking,
        private ?float $listingsPer1000Properties,
        private ?int $medianEstimatedHomeValue,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->integer('for_sale_count'),
            $payload->integer('transactions_count'),
            $payload->integer('avg_transaction_price'),
            $payload->integer('avg_transaction_price_per_m2'),
            $payload->integer('avg_asking_price'),
            $payload->integer('avg_asking_price_per_m2'),
            $payload->float('transaction_price_per_m2_yoy_pct'),
            $payload->float('median_days_on_market'),
            $payload->float('median_overbid_pct'),
            $payload->float('share_sold_above_asking'),
            $payload->float('listings_per_1000_properties'),
            $payload->integer('median_estimated_home_value'),
        );
    }

    public function forSaleCount(): ?int
    {
        return $this->forSaleCount;
    }

    public function transactionsCount(): ?int
    {
        return $this->transactionsCount;
    }

    public function averageTransactionPrice(): ?int
    {
        return $this->averageTransactionPrice;
    }

    public function averageTransactionPricePerSquareMeter(): ?int
    {
        return $this->averageTransactionPricePerSquareMeter;
    }

    public function averageAskingPrice(): ?int
    {
        return $this->averageAskingPrice;
    }

    public function averageAskingPricePerSquareMeter(): ?int
    {
        return $this->averageAskingPricePerSquareMeter;
    }

    public function transactionPricePerSquareMeterYearOverYearPercentage(): ?float
    {
        return $this->transactionPricePerSquareMeterYearOverYearPercentage;
    }

    public function medianDaysOnMarket(): ?float
    {
        return $this->medianDaysOnMarket;
    }

    public function medianOverbidPercentage(): ?float
    {
        return $this->medianOverbidPercentage;
    }

    public function shareSoldAboveAsking(): ?float
    {
        return $this->shareSoldAboveAsking;
    }

    public function listingsPer1000Properties(): ?float
    {
        return $this->listingsPer1000Properties;
    }

    public function medianEstimatedHomeValue(): ?int
    {
        return $this->medianEstimatedHomeValue;
    }
}
