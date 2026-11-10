<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\PropertyType as PropertyTypeContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class PropertyType implements PropertyTypeContract
{
    public function __construct(
        private ?string $objectType,
        private ?string $houseType,
        private ?string $houseTypeDetail,
        private ?string $houseKind,
        private ?string $constructionKind,
        private ?string $constructionPeriod,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->string('object_type'),
            $payload->string('house_type'),
            $payload->string('house_type_detail'),
            $payload->string('house_kind'),
            $payload->string('construction_kind'),
            $payload->string('construction_period'),
        );
    }

    public function objectType(): ?string
    {
        return $this->objectType;
    }

    public function houseType(): ?string
    {
        return $this->houseType;
    }

    public function houseTypeDetail(): ?string
    {
        return $this->houseTypeDetail;
    }

    public function houseKind(): ?string
    {
        return $this->houseKind;
    }

    public function constructionKind(): ?string
    {
        return $this->constructionKind;
    }

    public function constructionPeriod(): ?string
    {
        return $this->constructionPeriod;
    }
}
