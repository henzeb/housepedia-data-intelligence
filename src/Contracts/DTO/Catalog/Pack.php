<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Catalog;

interface Pack
{
    public function id(): string;

    public function name(): string;

    public function priceInEuros(): float;

    public function credits(): int;

    public function creditPriceInEuros(): ?float;

    public function interval(): ?string;

    public function savingPercent(): ?int;
}
