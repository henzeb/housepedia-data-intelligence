<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Woz as WozContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class Woz implements WozContract
{
    /**
     * @param list<WozValue> $values
     */
    public function __construct(
        private Address $address,
        private ?WozValue $latest,
        private array $values,
        private ?Meta $meta,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->requireNested('address', Address::from(...)),
            $payload->nested('latest', WozValue::from(...)),
            $payload->list('values', WozValue::from(...)),
            $payload->nested('meta', Meta::from(...)),
        );
    }

    public function address(): Address
    {
        return $this->address;
    }

    public function latest(): ?WozValue
    {
        return $this->latest;
    }

    /**
     * @return list<WozValue>
     */
    public function values(): array
    {
        return $this->values;
    }

    public function meta(): ?Meta
    {
        return $this->meta;
    }
}
