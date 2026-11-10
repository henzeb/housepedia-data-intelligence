<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Valuation as ValuationContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class Valuation implements ValuationContract
{
    public function __construct(
        private Address $address,
        private Estimate $valuation,
        private ?Confidence $confidence,
        private ?Meta $meta,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->requireNested('address', Address::from(...)),
            $payload->requireNested('valuation', Estimate::from(...)),
            $payload->nested('confidence', Confidence::from(...)),
            $payload->nested('meta', Meta::from(...)),
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
}
