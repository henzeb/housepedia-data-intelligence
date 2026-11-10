<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Noise as NoiseContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class Noise implements NoiseContract
{
    public function __construct(
        private ?float $roadLdenInDecibels,
        private ?float $roadLnightInDecibels,
        private ?float $railLdenInDecibels,
        private ?float $railLnightInDecibels,
        private ?float $aviationLdenInDecibels,
        private ?float $industryLdenInDecibels,
        private ?float $windTurbinesLdenInDecibels,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->float('road_lden_db'),
            $payload->float('road_lnight_db'),
            $payload->float('rail_lden_db'),
            $payload->float('rail_lnight_db'),
            $payload->float('aviation_lden_db'),
            $payload->float('industry_lden_db'),
            $payload->float('wind_turbines_lden_db'),
        );
    }

    public function roadLdenInDecibels(): ?float
    {
        return $this->roadLdenInDecibels;
    }

    public function roadLnightInDecibels(): ?float
    {
        return $this->roadLnightInDecibels;
    }

    public function railLdenInDecibels(): ?float
    {
        return $this->railLdenInDecibels;
    }

    public function railLnightInDecibels(): ?float
    {
        return $this->railLnightInDecibels;
    }

    public function aviationLdenInDecibels(): ?float
    {
        return $this->aviationLdenInDecibels;
    }

    public function industryLdenInDecibels(): ?float
    {
        return $this->industryLdenInDecibels;
    }

    public function windTurbinesLdenInDecibels(): ?float
    {
        return $this->windTurbinesLdenInDecibels;
    }
}
