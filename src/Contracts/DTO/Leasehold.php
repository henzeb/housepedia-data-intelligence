<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

use DateTimeImmutable;

interface Leasehold
{
    public function isBoughtOff(): ?bool;

    public function buyoutEndDate(): ?DateTimeImmutable;

    public function yearlyCanon(): ?float;

    public function isPerpetual(): ?bool;

    public function periodEndYear(): ?int;

    public function landowner(): ?string;

    public function generalConditions(): ?string;

    public function isRateLocked(): ?bool;

    public function hasCostRisk(): ?bool;

    public function yearsUntilCanonResumes(): ?int;

    public function summary(): ?string;
}
