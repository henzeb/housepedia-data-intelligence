<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface Ownership
{
    public function tenure(): ?string;

    public function leasehold(): ?Leasehold;

    public function vveMonthlyContribution(): ?int;

    public function dwellingsInBuilding(): ?int;
}
