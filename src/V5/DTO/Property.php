<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Property as PropertyContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class Property implements PropertyContract
{
    public function __construct(
        private Address $address,
        private ?PropertyType $type,
        private ?Dimensions $dimensions,
        private ?Rooms $rooms,
        private ?Outdoor $outdoor,
        private ?Construction $construction,
        private ?Energy $energy,
        private ?Ownership $ownership,
        private ?Maintenance $maintenance,
        private ?Meta $meta,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->requireNested('address', Address::from(...)),
            $payload->nested('type', PropertyType::from(...)),
            $payload->nested('dimensions', Dimensions::from(...)),
            $payload->nested('rooms', Rooms::from(...)),
            $payload->nested('outdoor', Outdoor::from(...)),
            $payload->nested('construction', Construction::from(...)),
            $payload->nested('energy', Energy::from(...)),
            $payload->nested('ownership', Ownership::from(...)),
            $payload->nested('maintenance', Maintenance::from(...)),
            $payload->nested('meta', Meta::from(...)),
        );
    }

    public function address(): Address
    {
        return $this->address;
    }

    public function type(): ?PropertyType
    {
        return $this->type;
    }

    public function dimensions(): ?Dimensions
    {
        return $this->dimensions;
    }

    public function rooms(): ?Rooms
    {
        return $this->rooms;
    }

    public function outdoor(): ?Outdoor
    {
        return $this->outdoor;
    }

    public function construction(): ?Construction
    {
        return $this->construction;
    }

    public function energy(): ?Energy
    {
        return $this->energy;
    }

    public function ownership(): ?Ownership
    {
        return $this->ownership;
    }

    public function maintenance(): ?Maintenance
    {
        return $this->maintenance;
    }

    public function meta(): ?Meta
    {
        return $this->meta;
    }
}
