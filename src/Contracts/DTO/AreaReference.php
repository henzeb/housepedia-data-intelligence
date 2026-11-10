<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface AreaReference
{
    public function code(): string;

    public function name(): ?string;
}
