<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Heat as HeatContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class Heat implements HeatContract
{
    public function __construct(
        private ?float $urbanHeatIslandInDegreesCelsius,
        private ?float $greenCoolingEffect,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->float('urban_heat_island_c'),
            $payload->float('green_cooling_effect'),
        );
    }

    public function urbanHeatIslandInDegreesCelsius(): ?float
    {
        return $this->urbanHeatIslandInDegreesCelsius;
    }

    public function greenCoolingEffect(): ?float
    {
        return $this->greenCoolingEffect;
    }
}
