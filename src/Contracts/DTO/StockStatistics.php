<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface StockStatistics
{
    public function propertiesCount(): ?int;

    public function medianConstructionYear(): ?int;

    public function sharePrewar(): ?float;

    public function shareBuiltAfter2000(): ?float;

    public function medianLivingAreaInSquareMeters(): ?float;

    public function shareUnder75SquareMeters(): ?float;

    public function shareMultiFamily(): ?float;

    public function shareDetached(): ?float;

    public function shareTerraced(): ?float;

    public function shareEnergyLabelAOrBetter(): ?float;
}
