<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface RowError
{
    public function code(): string;

    public function message(): string;
}
