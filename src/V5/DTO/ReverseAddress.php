<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\ReverseAddress as ReverseAddressContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class ReverseAddress implements ReverseAddressContract
{
    public function __construct(
        private ?string $bagId,
        private ?string $postcode,
        private ?int $houseNumber,
        private ?string $houseNumberExt,
        private ?string $street,
        private ?string $city,
        private ?string $municipality,
        private ?string $province,
        private ?Location $location,
        private ?RdLocation $rd,
        private ?string $housepediaUrl,
        private float $distanceInMeters,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->string('bag_id'),
            $payload->string('postcode'),
            $payload->integer('house_number'),
            $payload->string('house_number_ext'),
            $payload->string('street'),
            $payload->string('city'),
            $payload->string('municipality'),
            $payload->string('province'),
            $payload->nested('location', Location::from(...)),
            $payload->nested('rd', RdLocation::from(...)),
            $payload->string('huispedia_url'),
            $payload->requireFloat('distance_m'),
        );
    }

    public function bagId(): ?string
    {
        return $this->bagId;
    }

    public function postcode(): ?string
    {
        return $this->postcode;
    }

    public function houseNumber(): ?int
    {
        return $this->houseNumber;
    }

    public function houseNumberExt(): ?string
    {
        return $this->houseNumberExt;
    }

    public function street(): ?string
    {
        return $this->street;
    }

    public function city(): ?string
    {
        return $this->city;
    }

    public function municipality(): ?string
    {
        return $this->municipality;
    }

    public function province(): ?string
    {
        return $this->province;
    }

    public function location(): ?Location
    {
        return $this->location;
    }

    public function rd(): ?RdLocation
    {
        return $this->rd;
    }

    public function housepediaUrl(): ?string
    {
        return $this->housepediaUrl;
    }

    public function distanceInMeters(): float
    {
        return $this->distanceInMeters;
    }
}
