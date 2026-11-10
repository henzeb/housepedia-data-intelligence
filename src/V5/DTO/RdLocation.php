<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\RdLocation as RdLocationContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class RdLocation implements RdLocationContract
{
    public function __construct(
        private float $x,
        private float $y,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->requireFloat('x'),
            $payload->requireFloat('y'),
        );
    }

    public function x(): float
    {
        return $this->x;
    }

    public function y(): float
    {
        return $this->y;
    }
}
