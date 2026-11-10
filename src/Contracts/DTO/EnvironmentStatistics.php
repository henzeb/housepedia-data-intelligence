<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface EnvironmentStatistics
{
    public function averageNoiseRoadLdenInDecibels(): ?float;

    public function averagePm25InMicrogramsPerCubicMeter(): ?float;

    public function averageNo2InMicrogramsPerCubicMeter(): ?float;

    public function averageUrbanHeatIslandInDegreesCelsius(): ?float;

    public function sharePublicGreen(): ?float;

    public function shareWater(): ?float;
}
