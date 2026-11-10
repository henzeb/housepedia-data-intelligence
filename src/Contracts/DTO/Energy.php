<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface Energy
{
    public function energyLabel(): ?string;

    public function energyLabelSource(): ?string;
}
