<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface Dimensions
{
    public function livingAreaInSquareMeters(): ?int;

    public function plotAreaInSquareMeters(): ?int;

    public function volumeInCubicMeters(): ?int;

    public function externalStorageInSquareMeters(): ?int;

    public function buildingBoundOutdoorInSquareMeters(): ?int;

    public function otherIndoorInSquareMeters(): ?int;

    public function bagAreaInSquareMeters(): ?int;
}
