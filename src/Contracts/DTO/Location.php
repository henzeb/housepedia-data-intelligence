<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface Location
{
    public function lat(): float;

    public function lon(): float;
}
