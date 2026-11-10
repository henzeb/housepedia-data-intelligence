<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use DateTimeImmutable;
use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Estimate as EstimateContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class Estimate implements EstimateContract
{
    public function __construct(
        private int $value,
        private int $lower,
        private int $upper,
        private ?float $interval,
        private ?int $pricePerSquareMeter,
        private ?DateTimeImmutable $asOfDate,
        private ?string $model,
        private ?string $modelVersion,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->requireInteger('value'),
            $payload->requireInteger('lower'),
            $payload->requireInteger('upper'),
            $payload->float('interval'),
            $payload->integer('price_per_m2'),
            $payload->date('as_of_date'),
            $payload->string('model'),
            $payload->string('model_version'),
        );
    }

    public function value(): int
    {
        return $this->value;
    }

    public function lower(): int
    {
        return $this->lower;
    }

    public function upper(): int
    {
        return $this->upper;
    }

    public function interval(): ?float
    {
        return $this->interval;
    }

    public function pricePerSquareMeter(): ?int
    {
        return $this->pricePerSquareMeter;
    }

    public function asOfDate(): ?DateTimeImmutable
    {
        return $this->asOfDate;
    }

    public function model(): ?string
    {
        return $this->model;
    }

    public function modelVersion(): ?string
    {
        return $this->modelVersion;
    }
}
