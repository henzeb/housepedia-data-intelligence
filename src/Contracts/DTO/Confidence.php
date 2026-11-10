<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface Confidence
{
    public function scorePercentage(): ?float;

    public function class(): ?string;

    public function within5Percent(): ?float;

    public function within10Percent(): ?float;

    public function within20Percent(): ?float;
}
