<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\ReferenceTarget as ReferenceTargetContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class ReferenceTarget implements ReferenceTargetContract
{
    public function __construct(
        private ?string $objectType,
        private ?int $livingAreaInSquareMeters,
        private ?int $constructionYear,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->string('object_type'),
            $payload->integer('living_area_m2'),
            $payload->integer('construction_year'),
        );
    }

    public function objectType(): ?string
    {
        return $this->objectType;
    }

    public function livingAreaInSquareMeters(): ?int
    {
        return $this->livingAreaInSquareMeters;
    }

    public function constructionYear(): ?int
    {
        return $this->constructionYear;
    }
}
