<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

use DateTimeImmutable;

interface EnergyLabelRecord
{
    public function label(): string;

    public function recordingDate(): ?DateTimeImmutable;

    public function validTo(): ?DateTimeImmutable;
}
