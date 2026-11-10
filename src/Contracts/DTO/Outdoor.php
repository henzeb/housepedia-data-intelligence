<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface Outdoor
{
    public function gardenAreaInSquareMeters(): ?int;

    public function gardenOrientation(): ?string;

    public function gardenOrientationInDegrees(): ?int;

    public function garageType(): ?string;

    public function garageAreaInSquareMeters(): ?int;

    public function parking(): ?string;
}
