<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\EnvironmentStatistics as EnvironmentStatisticsContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class EnvironmentStatistics implements EnvironmentStatisticsContract
{
    public function __construct(
        private ?float $averageNoiseRoadLdenInDecibels,
        private ?float $averagePm25InMicrogramsPerCubicMeter,
        private ?float $averageNo2InMicrogramsPerCubicMeter,
        private ?float $averageUrbanHeatIslandInDegreesCelsius,
        private ?float $sharePublicGreen,
        private ?float $shareWater,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->float('avg_noise_road_lden_db'),
            $payload->float('avg_pm25_ug_m3'),
            $payload->float('avg_no2_ug_m3'),
            $payload->float('avg_urban_heat_island_c'),
            $payload->float('share_public_green'),
            $payload->float('share_water'),
        );
    }

    public function averageNoiseRoadLdenInDecibels(): ?float
    {
        return $this->averageNoiseRoadLdenInDecibels;
    }

    public function averagePm25InMicrogramsPerCubicMeter(): ?float
    {
        return $this->averagePm25InMicrogramsPerCubicMeter;
    }

    public function averageNo2InMicrogramsPerCubicMeter(): ?float
    {
        return $this->averageNo2InMicrogramsPerCubicMeter;
    }

    public function averageUrbanHeatIslandInDegreesCelsius(): ?float
    {
        return $this->averageUrbanHeatIslandInDegreesCelsius;
    }

    public function sharePublicGreen(): ?float
    {
        return $this->sharePublicGreen;
    }

    public function shareWater(): ?float
    {
        return $this->shareWater;
    }
}
