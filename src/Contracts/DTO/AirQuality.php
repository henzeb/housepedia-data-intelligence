<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface AirQuality
{
    public function no2InMicrogramsPerCubicMeter(): ?float;

    public function pm25InMicrogramsPerCubicMeter(): ?float;

    public function pm10InMicrogramsPerCubicMeter(): ?float;
}
