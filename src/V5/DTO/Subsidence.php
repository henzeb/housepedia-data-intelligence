<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Subsidence as SubsidenceContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class Subsidence implements SubsidenceContract
{
    public function __construct(
        private ?float $groundElevationChange,
        private ?string $unit,
        private ?string $source,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->float('ground_elevation_change'),
            $payload->string('unit'),
            $payload->string('source'),
        );
    }

    public function groundElevationChange(): ?float
    {
        return $this->groundElevationChange;
    }

    public function unit(): ?string
    {
        return $this->unit;
    }

    public function source(): ?string
    {
        return $this->source;
    }
}
