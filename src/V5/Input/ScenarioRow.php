<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\Input;

use Henzeb\Housepedia\Data\Intelligence\Contracts\Input\ScenarioRow as ScenarioRowContract;
use Henzeb\Housepedia\Data\Intelligence\Locators\Locator;

class ScenarioRow implements ScenarioRowContract
{
    /**
     * @param list<Scenario> $scenarios
     */
    public function __construct(
        private Locator $locator,
        private Characteristics $characteristics = new Characteristics(),
        private array $scenarios = [],
    ) {
    }

    /**
     * @return array<string, string|int|list<array<string, string|int>>>
     */
    public function toArray(): array
    {
        return ['locator' => $this->locator->value()]
            + $this->characteristics->toArray()
            + ['scenarios' => array_map(static fn (Scenario $scenario): array => $scenario->toArray(), $this->scenarios)];
    }
}
