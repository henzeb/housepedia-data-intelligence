<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface AgeShares
{
    public function share0To15(): ?float;

    public function share25To45(): ?float;

    public function share65Plus(): ?float;
}
