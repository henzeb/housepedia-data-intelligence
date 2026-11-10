<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Outdoor as OutdoorContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class Outdoor implements OutdoorContract
{
    public function __construct(
        private ?int $gardenAreaInSquareMeters,
        private ?string $gardenOrientation,
        private ?int $gardenOrientationInDegrees,
        private ?string $garageType,
        private ?int $garageAreaInSquareMeters,
        private ?string $parking,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->integer('garden_area_m2'),
            $payload->string('garden_orientation'),
            $payload->integer('garden_orientation_degrees'),
            $payload->string('garage_type'),
            $payload->integer('garage_area_m2'),
            $payload->string('parking'),
        );
    }

    public function gardenAreaInSquareMeters(): ?int
    {
        return $this->gardenAreaInSquareMeters;
    }

    public function gardenOrientation(): ?string
    {
        return $this->gardenOrientation;
    }

    public function gardenOrientationInDegrees(): ?int
    {
        return $this->gardenOrientationInDegrees;
    }

    public function garageType(): ?string
    {
        return $this->garageType;
    }

    public function garageAreaInSquareMeters(): ?int
    {
        return $this->garageAreaInSquareMeters;
    }

    public function parking(): ?string
    {
        return $this->parking;
    }
}
