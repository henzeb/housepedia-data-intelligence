<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\SpendCap as SpendCapContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class SpendCap implements SpendCapContract
{
    public function __construct(
        private int $monthlyCredits,
        private int $usedThisMonth,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->requireInteger('monthly_credits'),
            $payload->requireInteger('used_this_month'),
        );
    }

    public function monthlyCredits(): int
    {
        return $this->monthlyCredits;
    }

    public function usedThisMonth(): int
    {
        return $this->usedThisMonth;
    }
}
