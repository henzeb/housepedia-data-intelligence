<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Catalog;

interface FreeTier
{
    public function creditsPerMonth(): int;

    public function conditions(): ?LocalizedText;
}
