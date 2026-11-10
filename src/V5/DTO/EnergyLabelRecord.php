<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use DateTimeImmutable;
use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\EnergyLabelRecord as EnergyLabelRecordContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class EnergyLabelRecord implements EnergyLabelRecordContract
{
    public function __construct(
        private string $label,
        private ?DateTimeImmutable $recordingDate,
        private ?DateTimeImmutable $validTo,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->requireString('label'),
            $payload->date('recording_date'),
            $payload->date('valid_to'),
        );
    }

    public function label(): string
    {
        return $this->label;
    }

    public function recordingDate(): ?DateTimeImmutable
    {
        return $this->recordingDate;
    }

    public function validTo(): ?DateTimeImmutable
    {
        return $this->validTo;
    }
}
