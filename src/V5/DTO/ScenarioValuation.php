<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\ScenarioValuation as ScenarioValuationContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class ScenarioValuation implements ScenarioValuationContract
{
    /**
     * @param list<ScenarioResult>|null $scenarios
     */
    public function __construct(
        private ?Address $address,
        private ?Estimate $valuation,
        private ?Confidence $confidence,
        private ?array $scenarios,
        private ?RowError $error,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->nested('address', Address::from(...)),
            $payload->nested('valuation', Estimate::from(...)),
            $payload->nested('confidence', Confidence::from(...)),
            $payload->nullableList('scenarios', ScenarioResult::from(...)),
            $payload->nested('error', RowError::from(...)),
        );
    }

    public function address(): ?Address
    {
        return $this->address;
    }

    public function valuation(): ?Estimate
    {
        return $this->valuation;
    }

    public function confidence(): ?Confidence
    {
        return $this->confidence;
    }

    /**
     * @return list<ScenarioResult>|null
     */
    public function scenarios(): ?array
    {
        return $this->scenarios;
    }

    public function error(): ?RowError
    {
        return $this->error;
    }
}
