<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\StockStatistics as StockStatisticsContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class StockStatistics implements StockStatisticsContract
{
    public function __construct(
        private ?int $propertiesCount,
        private ?int $medianConstructionYear,
        private ?float $sharePrewar,
        private ?float $shareBuiltAfter2000,
        private ?float $medianLivingAreaInSquareMeters,
        private ?float $shareUnder75SquareMeters,
        private ?float $shareMultiFamily,
        private ?float $shareDetached,
        private ?float $shareTerraced,
        private ?float $shareEnergyLabelAOrBetter,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->integer('properties_count'),
            $payload->integer('median_construction_year'),
            $payload->float('share_prewar'),
            $payload->float('share_built_after_2000'),
            $payload->float('median_living_area_m2'),
            $payload->float('share_under_75_m2'),
            $payload->float('share_multi_family'),
            $payload->float('share_detached'),
            $payload->float('share_terraced'),
            $payload->float('share_energy_label_a_or_better'),
        );
    }

    public function propertiesCount(): ?int
    {
        return $this->propertiesCount;
    }

    public function medianConstructionYear(): ?int
    {
        return $this->medianConstructionYear;
    }

    public function sharePrewar(): ?float
    {
        return $this->sharePrewar;
    }

    public function shareBuiltAfter2000(): ?float
    {
        return $this->shareBuiltAfter2000;
    }

    public function medianLivingAreaInSquareMeters(): ?float
    {
        return $this->medianLivingAreaInSquareMeters;
    }

    public function shareUnder75SquareMeters(): ?float
    {
        return $this->shareUnder75SquareMeters;
    }

    public function shareMultiFamily(): ?float
    {
        return $this->shareMultiFamily;
    }

    public function shareDetached(): ?float
    {
        return $this->shareDetached;
    }

    public function shareTerraced(): ?float
    {
        return $this->shareTerraced;
    }

    public function shareEnergyLabelAOrBetter(): ?float
    {
        return $this->shareEnergyLabelAOrBetter;
    }
}
