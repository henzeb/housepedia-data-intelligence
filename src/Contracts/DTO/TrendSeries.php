<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface TrendSeries
{
    public function type(): string;

    public function code(): ?string;

    /**
     * @return list<TrendPoint>
     */
    public function points(): array;
}
