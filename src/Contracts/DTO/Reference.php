<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

use DateTimeImmutable;

interface Reference
{
    public function address(): Address;

    public function transactionId(): int;

    public function distanceInMeters(): float;

    public function transactionDate(): ?DateTimeImmutable;

    public function source(): ?string;

    public function price(): string|int|null;

    public function priceIndexed(): string|int|null;

    public function livingAreaInSquareMeters(): ?int;

    public function houseType(): ?string;

    public function constructionYear(): ?int;

    public function daysSinceTransaction(): ?int;
}
