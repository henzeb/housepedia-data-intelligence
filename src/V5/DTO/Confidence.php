<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Confidence as ConfidenceContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class Confidence implements ConfidenceContract
{
    public function __construct(
        private ?float $scorePercentage,
        private ?string $class,
        private ?float $within5Percent,
        private ?float $within10Percent,
        private ?float $within20Percent,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->float('score_pct'),
            $payload->string('class'),
            $payload->float('within_5_pct'),
            $payload->float('within_10_pct'),
            $payload->float('within_20_pct'),
        );
    }

    public function scorePercentage(): ?float
    {
        return $this->scorePercentage;
    }

    public function class(): ?string
    {
        return $this->class;
    }

    public function within5Percent(): ?float
    {
        return $this->within5Percent;
    }

    public function within10Percent(): ?float
    {
        return $this->within10Percent;
    }

    public function within20Percent(): ?float
    {
        return $this->within20Percent;
    }
}
