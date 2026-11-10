<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface ScenarioValuation
{
    public function address(): ?Address;

    public function valuation(): ?Estimate;

    public function confidence(): ?Confidence;

    /**
     * @return list<ScenarioResult>|null
     */
    public function scenarios(): ?array;

    public function error(): ?RowError;
}
