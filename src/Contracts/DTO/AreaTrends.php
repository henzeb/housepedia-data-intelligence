<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface AreaTrends
{
    public function area(): AreaReference;

    public function type(): string;

    public function metric(): string;

    public function interval(): ?string;

    /**
     * @return list<TrendPoint>
     */
    public function points(): array;

    /**
     * @return list<TrendSeries>|null
     */
    public function parents(): ?array;

    public function meta(): ?Meta;
}
