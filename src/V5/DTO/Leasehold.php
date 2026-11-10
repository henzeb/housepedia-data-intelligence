<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use DateTimeImmutable;
use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Leasehold as LeaseholdContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class Leasehold implements LeaseholdContract
{
    public function __construct(
        private ?bool $isBoughtOff,
        private ?DateTimeImmutable $buyoutEndDate,
        private ?float $yearlyCanon,
        private ?bool $isPerpetual,
        private ?int $periodEndYear,
        private ?string $landowner,
        private ?string $generalConditions,
        private ?bool $isRateLocked,
        private ?bool $hasCostRisk,
        private ?int $yearsUntilCanonResumes,
        private ?string $summary,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->boolean('is_bought_off'),
            $payload->date('buyout_end_date'),
            $payload->float('yearly_canon'),
            $payload->boolean('is_perpetual'),
            $payload->integer('period_end_year'),
            $payload->string('landowner'),
            $payload->string('general_conditions'),
            $payload->boolean('is_rate_locked'),
            $payload->boolean('has_cost_risk'),
            $payload->integer('years_until_canon_resumes'),
            $payload->string('summary'),
        );
    }

    public function isBoughtOff(): ?bool
    {
        return $this->isBoughtOff;
    }

    public function buyoutEndDate(): ?DateTimeImmutable
    {
        return $this->buyoutEndDate;
    }

    public function yearlyCanon(): ?float
    {
        return $this->yearlyCanon;
    }

    public function isPerpetual(): ?bool
    {
        return $this->isPerpetual;
    }

    public function periodEndYear(): ?int
    {
        return $this->periodEndYear;
    }

    public function landowner(): ?string
    {
        return $this->landowner;
    }

    public function generalConditions(): ?string
    {
        return $this->generalConditions;
    }

    public function isRateLocked(): ?bool
    {
        return $this->isRateLocked;
    }

    public function hasCostRisk(): ?bool
    {
        return $this->hasCostRisk;
    }

    public function yearsUntilCanonResumes(): ?int
    {
        return $this->yearsUntilCanonResumes;
    }

    public function summary(): ?string
    {
        return $this->summary;
    }
}
