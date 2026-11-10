<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface Maintenance
{
    public function interior(): ?string;

    public function exterior(): ?string;
}
