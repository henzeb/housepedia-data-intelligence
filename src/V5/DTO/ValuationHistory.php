<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\ValuationHistory as ValuationHistoryContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class ValuationHistory implements ValuationHistoryContract
{
    /**
     * @param list<ValuationHistoryPoint> $points
     */
    public function __construct(
        private Address $address,
        private array $points,
        private ?ValuationHistoryPoint $current,
        private ?Meta $meta,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->requireNested('address', Address::from(...)),
            $payload->list('data', ValuationHistoryPoint::from(...)),
            $payload->nested('current', ValuationHistoryPoint::from(...)),
            $payload->nested('meta', Meta::from(...)),
        );
    }

    public function address(): Address
    {
        return $this->address;
    }

    /**
     * @return list<ValuationHistoryPoint>
     */
    public function points(): array
    {
        return $this->points;
    }

    public function current(): ?ValuationHistoryPoint
    {
        return $this->current;
    }

    public function meta(): ?Meta
    {
        return $this->meta;
    }
}
