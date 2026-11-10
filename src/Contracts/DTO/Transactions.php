<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface Transactions
{
    public function address(): Address;

    /**
     * @return list<Transaction>
     */
    public function transactions(): array;

    public function meta(): ?Meta;
}
