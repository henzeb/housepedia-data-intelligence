<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Surroundings as SurroundingsContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class Surroundings implements SurroundingsContract
{
    /**
     * @param array<string, NearestPlace|null> $nearest
     * @param array<string, int|null>|null $countsInNeighbourhood
     * @param array<string, mixed>|null $mobility
     */
    public function __construct(
        private Address $address,
        private array $nearest,
        private ?array $countsInNeighbourhood,
        private ?array $mobility,
        private ?Meta $meta,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->requireNested('address', Address::from(...)),
            $payload->objectMap('nearest', NearestPlace::from(...)),
            $payload->integerMap('counts_in_neighbourhood'),
            $payload->associative('mobility'),
            $payload->nested('meta', Meta::from(...)),
        );
    }

    public function address(): Address
    {
        return $this->address;
    }

    /**
     * @return array<string, NearestPlace|null>
     */
    public function nearest(): array
    {
        return $this->nearest;
    }

    /**
     * @return array<string, int|null>|null
     */
    public function countsInNeighbourhood(): ?array
    {
        return $this->countsInNeighbourhood;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function mobility(): ?array
    {
        return $this->mobility;
    }

    public function meta(): ?Meta
    {
        return $this->meta;
    }
}
