<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use DateTimeImmutable;
use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\EnergyLabel as EnergyLabelContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class EnergyLabel implements EnergyLabelContract
{
    /**
     * @param list<EnergyLabelRecord> $history
     */
    public function __construct(
        private Address $address,
        private ?string $label,
        private ?string $source,
        private ?DateTimeImmutable $recordingDate,
        private ?DateTimeImmutable $validTo,
        private ?DateTimeImmutable $registrationDate,
        private ?bool $isExpired,
        private array $history,
        private ?Meta $meta,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->requireNested('address', Address::from(...)),
            $payload->string('label'),
            $payload->string('source'),
            $payload->date('recording_date'),
            $payload->date('valid_to'),
            $payload->date('registration_date'),
            $payload->boolean('is_expired'),
            $payload->list('history', EnergyLabelRecord::from(...)),
            $payload->nested('meta', Meta::from(...)),
        );
    }

    public function address(): Address
    {
        return $this->address;
    }

    public function label(): ?string
    {
        return $this->label;
    }

    public function source(): ?string
    {
        return $this->source;
    }

    public function recordingDate(): ?DateTimeImmutable
    {
        return $this->recordingDate;
    }

    public function validTo(): ?DateTimeImmutable
    {
        return $this->validTo;
    }

    public function registrationDate(): ?DateTimeImmutable
    {
        return $this->registrationDate;
    }

    public function isExpired(): ?bool
    {
        return $this->isExpired;
    }

    /**
     * @return list<EnergyLabelRecord>
     */
    public function history(): array
    {
        return $this->history;
    }

    public function meta(): ?Meta
    {
        return $this->meta;
    }
}
