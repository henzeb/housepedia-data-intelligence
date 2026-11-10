<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

use DateTimeImmutable;

interface Transaction
{
    public function transactionId(): int;

    public function priceAgreementDate(): ?DateTimeImmutable;

    public function transactionDate(): ?DateTimeImmutable;

    public function askingPrice(): ?int;

    public function listingDate(): ?DateTimeImmutable;

    public function delistedDate(): ?DateTimeImmutable;

    public function livingAreaInSquareMeters(): ?int;

    public function pricePerSquareMeter(): ?float;

    public function source(): string;

    public function price(): string|int|null;

    public function priceIndexed(): string|int|null;
}
