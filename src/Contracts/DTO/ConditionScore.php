<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface ConditionScore
{
    public function score(): ?int;

    public function confidence(): ?float;
}
