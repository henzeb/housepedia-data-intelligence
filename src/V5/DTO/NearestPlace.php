<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\NearestPlace as NearestPlaceContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class NearestPlace implements NearestPlaceContract
{
    public function __construct(
        private ?string $name,
        private ?string $category,
        private float $distanceInMeters,
        private ?Location $location,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->string('name'),
            $payload->string('category'),
            $payload->requireFloat('distance_m'),
            $payload->nested('location', Location::from(...)),
        );
    }

    public function name(): ?string
    {
        return $this->name;
    }

    public function category(): ?string
    {
        return $this->category;
    }

    public function distanceInMeters(): float
    {
        return $this->distanceInMeters;
    }

    public function location(): ?Location
    {
        return $this->location;
    }
}
