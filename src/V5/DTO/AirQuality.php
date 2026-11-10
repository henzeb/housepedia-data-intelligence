<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\AirQuality as AirQualityContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class AirQuality implements AirQualityContract
{
    public function __construct(
        private ?float $no2InMicrogramsPerCubicMeter,
        private ?float $pm25InMicrogramsPerCubicMeter,
        private ?float $pm10InMicrogramsPerCubicMeter,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->float('no2_ug_m3'),
            $payload->float('pm25_ug_m3'),
            $payload->float('pm10_ug_m3'),
        );
    }

    public function no2InMicrogramsPerCubicMeter(): ?float
    {
        return $this->no2InMicrogramsPerCubicMeter;
    }

    public function pm25InMicrogramsPerCubicMeter(): ?float
    {
        return $this->pm25InMicrogramsPerCubicMeter;
    }

    public function pm10InMicrogramsPerCubicMeter(): ?float
    {
        return $this->pm10InMicrogramsPerCubicMeter;
    }
}
