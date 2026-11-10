<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

use DateTimeImmutable;

interface EnergyLabel
{
    public function address(): Address;

    public function label(): ?string;

    public function source(): ?string;

    public function recordingDate(): ?DateTimeImmutable;

    public function validTo(): ?DateTimeImmutable;

    public function registrationDate(): ?DateTimeImmutable;

    public function isExpired(): ?bool;

    /**
     * @return list<EnergyLabelRecord>
     */
    public function history(): array;

    public function meta(): ?Meta;
}
