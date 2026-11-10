<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Building as BuildingContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class Building implements BuildingContract
{
    /**
     * @param list<BuildingUnit> $units
     */
    public function __construct(
        private string $bagPandId,
        private ?int $constructionYear,
        private ?string $bagStatus,
        private int $unitsCount,
        private int $residentialUnitsCount,
        private array $units,
        private ?Location $location,
        private ?string $street,
        private ?string $postcode,
        private ?string $city,
        private ?Meta $meta,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->requireString('bag_pand_id'),
            $payload->integer('construction_year'),
            $payload->string('bag_status'),
            $payload->requireInteger('units_count'),
            $payload->requireInteger('residential_units_count'),
            $payload->list('units', BuildingUnit::from(...)),
            $payload->nested('location', Location::from(...)),
            $payload->string('street'),
            $payload->string('postcode'),
            $payload->string('city'),
            $payload->nested('meta', Meta::from(...)),
        );
    }

    public function bagPandId(): string
    {
        return $this->bagPandId;
    }

    public function constructionYear(): ?int
    {
        return $this->constructionYear;
    }

    public function bagStatus(): ?string
    {
        return $this->bagStatus;
    }

    public function unitsCount(): int
    {
        return $this->unitsCount;
    }

    public function residentialUnitsCount(): int
    {
        return $this->residentialUnitsCount;
    }

    /**
     * @return list<BuildingUnit>
     */
    public function units(): array
    {
        return $this->units;
    }

    public function location(): ?Location
    {
        return $this->location;
    }

    public function street(): ?string
    {
        return $this->street;
    }

    public function postcode(): ?string
    {
        return $this->postcode;
    }

    public function city(): ?string
    {
        return $this->city;
    }

    public function meta(): ?Meta
    {
        return $this->meta;
    }
}
