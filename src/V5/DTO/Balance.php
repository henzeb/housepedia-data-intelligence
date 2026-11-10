<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Balance as BalanceContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class Balance implements BalanceContract
{
    /**
     * @param list<CreditBucket> $buckets
     */
    public function __construct(
        private int $total,
        private array $buckets,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->requireInteger('total'),
            $payload->list('buckets', CreditBucket::from(...)),
        );
    }

    public function total(): int
    {
        return $this->total;
    }

    /**
     * @return list<CreditBucket>
     */
    public function buckets(): array
    {
        return $this->buckets;
    }
}
