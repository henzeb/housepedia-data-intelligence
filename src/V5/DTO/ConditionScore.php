<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\ConditionScore as ConditionScoreContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class ConditionScore implements ConditionScoreContract
{
    public function __construct(
        private ?int $score,
        private ?float $confidence,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->integer('score'),
            $payload->float('confidence'),
        );
    }

    public function score(): ?int
    {
        return $this->score;
    }

    public function confidence(): ?float
    {
        return $this->confidence;
    }
}
