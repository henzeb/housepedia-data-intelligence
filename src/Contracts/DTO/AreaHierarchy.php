<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface AreaHierarchy
{
    public function neighbourhood(): ?AreaReference;

    public function district(): ?AreaReference;

    public function municipality(): ?AreaReference;

    public function province(): ?AreaReference;

    public function meta(): ?Meta;
}
