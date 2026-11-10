<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface SpendCap
{
    public function monthlyCredits(): int;

    public function usedThisMonth(): int;
}
