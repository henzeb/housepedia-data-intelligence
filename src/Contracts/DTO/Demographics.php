<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface Demographics
{
    public function area(): AreaReference;

    public function population(): ?int;

    public function populationDensity(): ?float;

    public function addressDensity(): ?float;

    public function households(): ?Households;

    public function age(): ?AgeShares;

    public function carsPerHousehold(): ?float;

    public function cbsVintageYear(): ?int;

    public function meta(): ?Meta;
}
