<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

use DateTimeImmutable;

interface CreditBucket
{
    public function kind(): string;

    public function remaining(): int;

    public function granted(): int;

    public function expiresAt(): ?DateTimeImmutable;
}
