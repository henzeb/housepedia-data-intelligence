<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface AreaParent
{
    public function code(): string;

    public function name(): ?string;

    public function type(): string;
}
