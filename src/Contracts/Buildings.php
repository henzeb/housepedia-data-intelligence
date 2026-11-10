<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Building;

interface Buildings
{
    public function withBagPandId(string $bagPandId): static;

    public function find(): Building;

    public function withMaxCacheAge(int $seconds): static;

    public function withoutCache(): static;
}
