<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Location as LocationContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class Location implements LocationContract
{
    public function __construct(
        private float $lat,
        private float $lon,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->requireFloat('lat'),
            $payload->requireFloat('lon'),
        );
    }

    public function lat(): float
    {
        return $this->lat;
    }

    public function lon(): float
    {
        return $this->lon;
    }
}
