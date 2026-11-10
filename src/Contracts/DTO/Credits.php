<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface Credits
{
    public function charged(): int;

    public function balance(): int;
}
