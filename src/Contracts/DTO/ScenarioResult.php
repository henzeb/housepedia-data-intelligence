<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface ScenarioResult
{
    public function name(): string;

    public function valuation(): ?Estimate;

    public function confidence(): ?Confidence;

    public function delta(): ?int;

    public function error(): ?RowError;
}
