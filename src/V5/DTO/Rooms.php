<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Rooms as RoomsContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class Rooms implements RoomsContract
{
    public function __construct(
        private ?int $rooms,
        private ?int $bedrooms,
        private ?int $bathrooms,
        private ?int $separateToilets,
        private ?int $floors,
        private ?int $floorLevel,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->integer('rooms'),
            $payload->integer('bedrooms'),
            $payload->integer('bathrooms'),
            $payload->integer('separate_toilets'),
            $payload->integer('floors'),
            $payload->integer('floor_level'),
        );
    }

    public function rooms(): ?int
    {
        return $this->rooms;
    }

    public function bedrooms(): ?int
    {
        return $this->bedrooms;
    }

    public function bathrooms(): ?int
    {
        return $this->bathrooms;
    }

    public function separateToilets(): ?int
    {
        return $this->separateToilets;
    }

    public function floors(): ?int
    {
        return $this->floors;
    }

    public function floorLevel(): ?int
    {
        return $this->floorLevel;
    }
}
