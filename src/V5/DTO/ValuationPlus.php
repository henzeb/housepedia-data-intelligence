<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\ValuationPlus as ValuationPlusContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class ValuationPlus implements ValuationPlusContract
{
    /**
     * @param list<Reference> $references
     */
    public function __construct(
        private Address $address,
        private Estimate $valuation,
        private ?Confidence $confidence,
        private ?Meta $meta,
        private array $references,
        private ?NeighbourhoodMarket $neighbourhood,
        private ?WozValue $woz,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->requireNested('address', Address::from(...)),
            $payload->requireNested('valuation', Estimate::from(...)),
            $payload->nested('confidence', Confidence::from(...)),
            $payload->nested('meta', Meta::from(...)),
            $payload->list('references', Reference::from(...)),
            $payload->nested('neighbourhood', NeighbourhoodMarket::from(...)),
            $payload->nested('woz', WozValue::from(...)),
        );
    }

    public function address(): Address
    {
        return $this->address;
    }

    public function valuation(): Estimate
    {
        return $this->valuation;
    }

    public function confidence(): ?Confidence
    {
        return $this->confidence;
    }

    public function meta(): ?Meta
    {
        return $this->meta;
    }

    /**
     * @return list<Reference>
     */
    public function references(): array
    {
        return $this->references;
    }

    public function neighbourhood(): ?NeighbourhoodMarket
    {
        return $this->neighbourhood;
    }

    public function woz(): ?WozValue
    {
        return $this->woz;
    }
}
