<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Households as HouseholdsContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class Households implements HouseholdsContract
{
    public function __construct(
        private ?float $averageSize,
        private ?float $shareWithChildren,
        private ?float $shareSinglePerson,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->float('avg_size'),
            $payload->float('share_with_children'),
            $payload->float('share_single_person'),
        );
    }

    public function averageSize(): ?float
    {
        return $this->averageSize;
    }

    public function shareWithChildren(): ?float
    {
        return $this->shareWithChildren;
    }

    public function shareSinglePerson(): ?float
    {
        return $this->shareSinglePerson;
    }
}
