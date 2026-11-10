<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface Heat
{
    public function urbanHeatIslandInDegreesCelsius(): ?float;

    public function greenCoolingEffect(): ?float;
}
