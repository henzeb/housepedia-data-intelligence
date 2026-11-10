<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\Input;

use DateTimeInterface;

class Characteristics
{
    public function __construct(
        private ?int $livingAreaInSquareMeters = null,
        private ?int $plotAreaInSquareMeters = null,
        private ?int $volumeInCubicMeters = null,
        private ?string $houseType = null,
        private ?string $energyLabel = null,
        private ?int $constructionYear = null,
        private ?int $rooms = null,
        private ?int $bedrooms = null,
        private ?int $bathrooms = null,
        private ?int $separateToilets = null,
        private ?int $gardenAreaInSquareMeters = null,
        private ?int $gardenOrientationInDegrees = null,
        private ?int $dwellingsInBuilding = null,
        private ?int $maintenanceOverall = null,
        private ?int $maintenanceModernity = null,
        private ?int $maintenanceFinish = null,
        private ?DateTimeInterface $asOfDate = null,
    ) {
    }

    /**
     * @return array<string, string|int>
     */
    public function toArray(): array
    {
        return array_filter(
            [
                'living_area_m2' => $this->livingAreaInSquareMeters,
                'plot_area_m2' => $this->plotAreaInSquareMeters,
                'volume_m3' => $this->volumeInCubicMeters,
                'house_type' => $this->houseType,
                'energy_label' => $this->energyLabel,
                'construction_year' => $this->constructionYear,
                'rooms' => $this->rooms,
                'bedrooms' => $this->bedrooms,
                'bathrooms' => $this->bathrooms,
                'separate_toilets' => $this->separateToilets,
                'garden_area_m2' => $this->gardenAreaInSquareMeters,
                'garden_orientation_degrees' => $this->gardenOrientationInDegrees,
                'dwellings_in_building' => $this->dwellingsInBuilding,
                'maintenance_overall' => $this->maintenanceOverall,
                'maintenance_modernity' => $this->maintenanceModernity,
                'maintenance_finish' => $this->maintenanceFinish,
                'as_of_date' => $this->asOfDate?->format('Y-m-d'),
            ],
            static fn (string|int|null $value): bool => $value !== null,
        );
    }
}
