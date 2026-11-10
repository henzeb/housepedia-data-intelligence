<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

use DateTimeImmutable;

interface TrendPoint
{
    public function date(): DateTimeImmutable;

    public function value(): float;

    public function yearOverYearPercentage(): ?float;
}
