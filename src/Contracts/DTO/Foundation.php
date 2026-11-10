<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface Foundation
{
    public function atRisk(): ?bool;

    public function subsoil(): ?string;
}
