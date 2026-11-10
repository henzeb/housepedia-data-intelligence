<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\Input;

class Scenario
{
    public function __construct(
        private string $name,
        private Characteristics $characteristics = new Characteristics(),
    ) {
    }

    /**
     * @return array<string, string|int>
     */
    public function toArray(): array
    {
        return ['name' => $this->name] + $this->characteristics->toArray();
    }
}
