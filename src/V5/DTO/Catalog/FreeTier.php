<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO\Catalog;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Catalog\FreeTier as FreeTierContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class FreeTier implements FreeTierContract
{
    public function __construct(
        private int $creditsPerMonth,
        private ?LocalizedText $conditions,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->requireInteger('credits_per_month'),
            $payload->nested('conditions', LocalizedText::from(...)),
        );
    }

    public function creditsPerMonth(): int
    {
        return $this->creditsPerMonth;
    }

    public function conditions(): ?LocalizedText
    {
        return $this->conditions;
    }
}
