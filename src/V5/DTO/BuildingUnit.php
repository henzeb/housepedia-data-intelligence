<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\BuildingUnit as BuildingUnitContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class BuildingUnit implements BuildingUnitContract
{
    public function __construct(
        private ?string $bagId,
        private ?int $houseNumber,
        private ?string $houseNumberExt,
        private ?int $floorLevel,
        private ?int $livingAreaInSquareMeters,
        private ?string $objectType,
        private ?bool $isResidential,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->string('bag_id'),
            $payload->integer('house_number'),
            $payload->string('house_number_ext'),
            $payload->integer('floor_level'),
            $payload->integer('living_area_m2'),
            $payload->string('object_type'),
            $payload->boolean('is_residential'),
        );
    }

    public function bagId(): ?string
    {
        return $this->bagId;
    }

    public function houseNumber(): ?int
    {
        return $this->houseNumber;
    }

    public function houseNumberExt(): ?string
    {
        return $this->houseNumberExt;
    }

    public function floorLevel(): ?int
    {
        return $this->floorLevel;
    }

    public function livingAreaInSquareMeters(): ?int
    {
        return $this->livingAreaInSquareMeters;
    }

    public function objectType(): ?string
    {
        return $this->objectType;
    }

    public function isResidential(): ?bool
    {
        return $this->isResidential;
    }
}
