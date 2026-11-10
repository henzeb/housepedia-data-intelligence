<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface Balance
{
    public function total(): int;

    /**
     * @return list<CreditBucket>
     */
    public function buckets(): array;
}
