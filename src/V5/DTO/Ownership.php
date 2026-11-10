<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Ownership as OwnershipContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class Ownership implements OwnershipContract
{
    public function __construct(
        private ?string $tenure,
        private ?Leasehold $leasehold,
        private ?int $vveMonthlyContribution,
        private ?int $dwellingsInBuilding,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->string('tenure'),
            $payload->nested('leasehold', Leasehold::from(...)),
            $payload->integer('vve_monthly_contribution'),
            $payload->integer('dwellings_in_building'),
        );
    }

    public function tenure(): ?string
    {
        return $this->tenure;
    }

    public function leasehold(): ?Leasehold
    {
        return $this->leasehold;
    }

    public function vveMonthlyContribution(): ?int
    {
        return $this->vveMonthlyContribution;
    }

    public function dwellingsInBuilding(): ?int
    {
        return $this->dwellingsInBuilding;
    }
}
