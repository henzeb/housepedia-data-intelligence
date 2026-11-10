<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Address as AddressContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class Address implements AddressContract
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
        private ?string $bagVerblijfsobjectId,
        private ?string $bagPandId,
        private ?string $bagStatus,
        private ?bool $isResidential,
        private ?AreaReference $neighbourhood,
        private ?AreaReference $district,
        private ?string $municipalityCode,
        private ?string $provinceCode,
        private ?Meta $meta,
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
            $payload->string('bag_verblijfsobject_id'),
            $payload->string('bag_pand_id'),
            $payload->string('bag_status'),
            $payload->boolean('is_residential'),
            $payload->nested('neighbourhood', AreaReference::from(...)),
            $payload->nested('district', AreaReference::from(...)),
            $payload->string('municipality_code'),
            $payload->string('province_code'),
            $payload->nested('meta', Meta::from(...)),
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

    public function bagVerblijfsobjectId(): ?string
    {
        return $this->bagVerblijfsobjectId;
    }

    public function bagPandId(): ?string
    {
        return $this->bagPandId;
    }

    public function bagStatus(): ?string
    {
        return $this->bagStatus;
    }

    public function isResidential(): ?bool
    {
        return $this->isResidential;
    }

    public function neighbourhood(): ?AreaReference
    {
        return $this->neighbourhood;
    }

    public function district(): ?AreaReference
    {
        return $this->district;
    }

    public function municipalityCode(): ?string
    {
        return $this->municipalityCode;
    }

    public function provinceCode(): ?string
    {
        return $this->provinceCode;
    }

    public function meta(): ?Meta
    {
        return $this->meta;
    }
}
