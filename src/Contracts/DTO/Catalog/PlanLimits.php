<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Catalog;

interface PlanLimits
{
    public function requestsPerSecond(): ?float;

    public function concurrency(): ?int;

    public function signalCallsPerDay(): ?int;
}
