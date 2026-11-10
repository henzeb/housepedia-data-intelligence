<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface NearestPlace
{
    public function name(): ?string;

    public function category(): ?string;

    public function distanceInMeters(): float;

    public function location(): ?Location;
}
