<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Demographics as DemographicsContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class Demographics implements DemographicsContract
{
    public function __construct(
        private AreaReference $area,
        private ?int $population,
        private ?float $populationDensity,
        private ?float $addressDensity,
        private ?Households $households,
        private ?AgeShares $age,
        private ?float $carsPerHousehold,
        private ?int $cbsVintageYear,
        private ?Meta $meta,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->requireNested('area', AreaReference::from(...)),
            $payload->integer('population'),
            $payload->float('population_density'),
            $payload->float('address_density'),
            $payload->nested('households', Households::from(...)),
            $payload->nested('age', AgeShares::from(...)),
            $payload->float('cars_per_household'),
            $payload->integer('cbs_vintage_year'),
            $payload->nested('meta', Meta::from(...)),
        );
    }

    public function area(): AreaReference
    {
        return $this->area;
    }

    public function population(): ?int
    {
        return $this->population;
    }

    public function populationDensity(): ?float
    {
        return $this->populationDensity;
    }

    public function addressDensity(): ?float
    {
        return $this->addressDensity;
    }

    public function households(): ?Households
    {
        return $this->households;
    }

    public function age(): ?AgeShares
    {
        return $this->age;
    }

    public function carsPerHousehold(): ?float
    {
        return $this->carsPerHousehold;
    }

    public function cbsVintageYear(): ?int
    {
        return $this->cbsVintageYear;
    }

    public function meta(): ?Meta
    {
        return $this->meta;
    }
}
