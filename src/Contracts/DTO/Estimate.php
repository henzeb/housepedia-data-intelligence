<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

use DateTimeImmutable;

interface Estimate
{
    public function value(): int;

    public function lower(): int;

    public function upper(): int;

    public function interval(): ?float;

    public function pricePerSquareMeter(): ?int;

    public function asOfDate(): ?DateTimeImmutable;

    public function model(): ?string;

    public function modelVersion(): ?string;
}
