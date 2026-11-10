<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\Input;

interface ScenarioRow
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
