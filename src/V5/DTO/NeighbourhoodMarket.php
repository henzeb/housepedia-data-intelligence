<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\NeighbourhoodMarket as NeighbourhoodMarketContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class NeighbourhoodMarket implements NeighbourhoodMarketContract
{
    public function __construct(
        private ?string $code,
        private ?string $name,
        private ?int $medianTransactionPricePerSquareMeter,
        private ?int $transactionsLastYear,
        private ?float $medianOverbidPercentage,
        private ?float $medianDaysOnMarket,
        private ?float $pricePerSquareMeterYearOverYearPercentage,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->string('code'),
            $payload->string('name'),
            $payload->integer('median_transaction_price_per_m2'),
            $payload->integer('transactions_last_year'),
            $payload->float('median_overbid_pct'),
            $payload->float('median_days_on_market'),
            $payload->float('price_per_m2_yoy_pct'),
        );
    }

    public function code(): ?string
    {
        return $this->code;
    }

    public function name(): ?string
    {
        return $this->name;
    }

    public function medianTransactionPricePerSquareMeter(): ?int
    {
        return $this->medianTransactionPricePerSquareMeter;
    }

    public function transactionsLastYear(): ?int
    {
        return $this->transactionsLastYear;
    }

    public function medianOverbidPercentage(): ?float
    {
        return $this->medianOverbidPercentage;
    }

    public function medianDaysOnMarket(): ?float
    {
        return $this->medianDaysOnMarket;
    }

    public function pricePerSquareMeterYearOverYearPercentage(): ?float
    {
        return $this->pricePerSquareMeterYearOverYearPercentage;
    }
}
