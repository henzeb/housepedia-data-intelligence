<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\AddressSuggestion as AddressSuggestionContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class AddressSuggestion implements AddressSuggestionContract
{
    public function __construct(
        private string $type,
        private string $label,
        private ?string $bagId,
        private ?string $postcode,
        private ?int $houseNumber,
        private ?string $houseNumberExt,
        private ?string $street,
        private ?string $city,
        private ?string $municipality,
        private ?Location $location,
        private ?int $addressCount,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->requireString('type'),
            $payload->requireString('label'),
            $payload->string('bag_id'),
            $payload->string('postcode'),
            $payload->integer('house_number'),
            $payload->string('house_number_ext'),
            $payload->string('street'),
            $payload->string('city'),
            $payload->string('municipality'),
            $payload->nested('location', Location::from(...)),
            $payload->integer('address_count'),
        );
    }

    public function type(): string
    {
        return $this->type;
    }

    public function label(): string
    {
        return $this->label;
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

    public function location(): ?Location
    {
        return $this->location;
    }

    public function addressCount(): ?int
    {
        return $this->addressCount;
    }
}
