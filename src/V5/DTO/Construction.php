<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Construction as ConstructionContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class Construction implements ConstructionContract
{
    /**
     * @param list<string>|null $insulation
     * @param list<string>|null $heating
     * @param list<string>|null $locationTypes
     */
    public function __construct(
        private ?int $constructionYear,
        private ?string $roofType,
        private ?string $roofMaterial,
        private ?array $insulation,
        private ?array $heating,
        private ?array $locationTypes,
        private ?bool $isMonument,
        private ?string $monumentStatus,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->integer('construction_year'),
            $payload->string('roof_type'),
            $payload->string('roof_material'),
            $payload->nullableStrings('insulation'),
            $payload->nullableStrings('heating'),
            $payload->nullableStrings('location_types'),
            $payload->boolean('is_monument'),
            $payload->string('monument_status'),
        );
    }

    public function constructionYear(): ?int
    {
        return $this->constructionYear;
    }

    public function roofType(): ?string
    {
        return $this->roofType;
    }

    public function roofMaterial(): ?string
    {
        return $this->roofMaterial;
    }

    /**
     * @return list<string>|null
     */
    public function insulation(): ?array
    {
        return $this->insulation;
    }

    /**
     * @return list<string>|null
     */
    public function heating(): ?array
    {
        return $this->heating;
    }

    /**
     * @return list<string>|null
     */
    public function locationTypes(): ?array
    {
        return $this->locationTypes;
    }

    public function isMonument(): ?bool
    {
        return $this->isMonument;
    }

    public function monumentStatus(): ?string
    {
        return $this->monumentStatus;
    }
}
