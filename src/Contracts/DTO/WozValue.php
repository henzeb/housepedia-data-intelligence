<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

use DateTimeImmutable;

interface WozValue
{
    public function referenceDate(): DateTimeImmutable;

    public function value(): int;

    public function indexedValue(): ?int;
}
