<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface AreaStatistics
{
    public function area(): AreaReference;

    public function type(): string;

    public function period(): ?string;

    public function market(): MarketStatistics;

    public function stock(): ?StockStatistics;

    public function environment(): ?EnvironmentStatistics;

    public function meta(): ?Meta;
}
