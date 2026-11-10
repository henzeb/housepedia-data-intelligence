<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\ScenarioResult as ScenarioResultContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class ScenarioResult implements ScenarioResultContract
{
    public function __construct(
        private string $name,
        private ?Estimate $valuation,
        private ?Confidence $confidence,
        private ?int $delta,
        private ?RowError $error,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->requireString('name'),
            $payload->nested('valuation', Estimate::from(...)),
            $payload->nested('confidence', Confidence::from(...)),
            $payload->integer('delta'),
            $payload->nested('error', RowError::from(...)),
        );
    }

    public function name(): string
    {
        return $this->name;
    }

    public function valuation(): ?Estimate
    {
        return $this->valuation;
    }

    public function confidence(): ?Confidence
    {
        return $this->confidence;
    }

    public function delta(): ?int
    {
        return $this->delta;
    }

    public function error(): ?RowError
    {
        return $this->error;
    }
}
