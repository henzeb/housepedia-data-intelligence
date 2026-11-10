<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Catalog;

interface Limits
{
    public function requestsPerSecond(): ?float;

    public function burst(): ?int;

    public function concurrency(): ?int;

    public function spendCapDefaultCredits(): ?int;
}
