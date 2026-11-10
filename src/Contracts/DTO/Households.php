<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface Households
{
    public function averageSize(): ?float;

    public function shareWithChildren(): ?float;

    public function shareSinglePerson(): ?float;
}
