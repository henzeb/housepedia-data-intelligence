<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\AgeShares as AgeSharesContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class AgeShares implements AgeSharesContract
{
    public function __construct(
        private ?float $share0To15,
        private ?float $share25To45,
        private ?float $share65Plus,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->float('share_0_15'),
            $payload->float('share_25_45'),
            $payload->float('share_65_plus'),
        );
    }

    public function share0To15(): ?float
    {
        return $this->share0To15;
    }

    public function share25To45(): ?float
    {
        return $this->share25To45;
    }

    public function share65Plus(): ?float
    {
        return $this->share65Plus;
    }
}
