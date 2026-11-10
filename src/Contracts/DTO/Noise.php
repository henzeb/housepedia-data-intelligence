<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface Noise
{
    public function roadLdenInDecibels(): ?float;

    public function roadLnightInDecibels(): ?float;

    public function railLdenInDecibels(): ?float;

    public function railLnightInDecibels(): ?float;

    public function aviationLdenInDecibels(): ?float;

    public function industryLdenInDecibels(): ?float;

    public function windTurbinesLdenInDecibels(): ?float;
}
