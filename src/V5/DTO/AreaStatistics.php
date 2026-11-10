<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\AreaStatistics as AreaStatisticsContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class AreaStatistics implements AreaStatisticsContract
{
    public function __construct(
        private AreaReference $area,
        private string $type,
        private ?string $period,
        private MarketStatistics $market,
        private ?StockStatistics $stock,
        private ?EnvironmentStatistics $environment,
        private ?Meta $meta,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->requireNested('area', AreaReference::from(...)),
            $payload->requireString('type'),
            $payload->string('period'),
            $payload->requireNested('market', MarketStatistics::from(...)),
            $payload->nested('stock', StockStatistics::from(...)),
            $payload->nested('environment', EnvironmentStatistics::from(...)),
            $payload->nested('meta', Meta::from(...)),
        );
    }

    public function area(): AreaReference
    {
        return $this->area;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function period(): ?string
    {
        return $this->period;
    }

    public function market(): MarketStatistics
    {
        return $this->market;
    }

    public function stock(): ?StockStatistics
    {
        return $this->stock;
    }

    public function environment(): ?EnvironmentStatistics
    {
        return $this->environment;
    }

    public function meta(): ?Meta
    {
        return $this->meta;
    }
}
