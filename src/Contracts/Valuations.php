<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Results;
use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\ScenarioValuation;
use Henzeb\Housepedia\Data\Intelligence\Contracts\Input\ScenarioRow;

interface Valuations
{
    /**
     * @param list<ScenarioRow> $rows
     * @return Results<ScenarioValuation>
     */
    public function scenarios(array $rows): Results;

    public function withMaxCacheAge(int $seconds): static;

    public function withoutCache(): static;
}
