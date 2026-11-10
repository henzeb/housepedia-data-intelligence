<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface BuildingUnit
{
    public function bagId(): ?string;

    public function houseNumber(): ?int;

    public function houseNumberExt(): ?string;

    public function floorLevel(): ?int;

    public function livingAreaInSquareMeters(): ?int;

    public function objectType(): ?string;

    public function isResidential(): ?bool;
}
