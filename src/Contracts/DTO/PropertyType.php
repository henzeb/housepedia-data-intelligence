<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface PropertyType
{
    public function objectType(): ?string;

    public function houseType(): ?string;

    public function houseTypeDetail(): ?string;

    public function houseKind(): ?string;

    public function constructionKind(): ?string;

    public function constructionPeriod(): ?string;
}
