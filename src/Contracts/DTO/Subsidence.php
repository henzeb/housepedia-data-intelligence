<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface Subsidence
{
    public function groundElevationChange(): ?float;

    public function unit(): ?string;

    public function source(): ?string;
}
