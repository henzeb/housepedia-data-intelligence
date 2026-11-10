<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use DateTimeImmutable;
use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Reference as ReferenceContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class Reference implements ReferenceContract
{
    public function __construct(
        private Address $address,
        private int $transactionId,
        private float $distanceInMeters,
        private ?DateTimeImmutable $transactionDate,
        private ?string $source,
        private int|string|null $price,
        private int|string|null $priceIndexed,
        private ?int $livingAreaInSquareMeters,
        private ?string $houseType,
        private ?int $constructionYear,
        private ?int $daysSinceTransaction,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->requireNested('address', Address::from(...)),
            $payload->requireInteger('transaction_id'),
            $payload->requireFloat('distance_m'),
            $payload->date('transaction_date'),
            $payload->string('source'),
            $payload->integerOrString('price'),
            $payload->integerOrString('price_indexed'),
            $payload->integer('living_area_m2'),
            $payload->string('house_type'),
            $payload->integer('construction_year'),
            $payload->integer('days_since_transaction'),
        );
    }

    public function address(): Address
    {
        return $this->address;
    }

    public function transactionId(): int
    {
        return $this->transactionId;
    }

    public function distanceInMeters(): float
    {
        return $this->distanceInMeters;
    }

    public function transactionDate(): ?DateTimeImmutable
    {
        return $this->transactionDate;
    }

    public function source(): ?string
    {
        return $this->source;
    }

    public function price(): int|string|null
    {
        return $this->price;
    }

    public function priceIndexed(): int|string|null
    {
        return $this->priceIndexed;
    }

    public function livingAreaInSquareMeters(): ?int
    {
        return $this->livingAreaInSquareMeters;
    }

    public function houseType(): ?string
    {
        return $this->houseType;
    }

    public function constructionYear(): ?int
    {
        return $this->constructionYear;
    }

    public function daysSinceTransaction(): ?int
    {
        return $this->daysSinceTransaction;
    }
}
