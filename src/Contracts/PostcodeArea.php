<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Area;

interface PostcodeArea
{
    public function find(bool $includeRd = false): Area;

    public function withMaxCacheAge(int $seconds): static;

    public function withoutCache(): static;
}
