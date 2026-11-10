<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Dimensions as DimensionsContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class Dimensions implements DimensionsContract
{
    public function __construct(
        private ?int $livingAreaInSquareMeters,
        private ?int $plotAreaInSquareMeters,
        private ?int $volumeInCubicMeters,
        private ?int $externalStorageInSquareMeters,
        private ?int $buildingBoundOutdoorInSquareMeters,
        private ?int $otherIndoorInSquareMeters,
        private ?int $bagAreaInSquareMeters,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->integer('living_area_m2'),
            $payload->integer('plot_area_m2'),
            $payload->integer('volume_m3'),
            $payload->integer('external_storage_m2'),
            $payload->integer('building_bound_outdoor_m2'),
            $payload->integer('other_indoor_m2'),
            $payload->integer('bag_area_m2'),
        );
    }

    public function livingAreaInSquareMeters(): ?int
    {
        return $this->livingAreaInSquareMeters;
    }

    public function plotAreaInSquareMeters(): ?int
    {
        return $this->plotAreaInSquareMeters;
    }

    public function volumeInCubicMeters(): ?int
    {
        return $this->volumeInCubicMeters;
    }

    public function externalStorageInSquareMeters(): ?int
    {
        return $this->externalStorageInSquareMeters;
    }

    public function buildingBoundOutdoorInSquareMeters(): ?int
    {
        return $this->buildingBoundOutdoorInSquareMeters;
    }

    public function otherIndoorInSquareMeters(): ?int
    {
        return $this->otherIndoorInSquareMeters;
    }

    public function bagAreaInSquareMeters(): ?int
    {
        return $this->bagAreaInSquareMeters;
    }
}
