<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use DateTimeImmutable;
use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Transaction as TransactionContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class Transaction implements TransactionContract
{
    public function __construct(
        private int $transactionId,
        private ?DateTimeImmutable $priceAgreementDate,
        private ?DateTimeImmutable $transactionDate,
        private ?int $askingPrice,
        private ?DateTimeImmutable $listingDate,
        private ?DateTimeImmutable $delistedDate,
        private ?int $livingAreaInSquareMeters,
        private ?float $pricePerSquareMeter,
        private string $source,
        private int|string|null $price,
        private int|string|null $priceIndexed,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->requireInteger('transaction_id'),
            $payload->date('price_agreement_date'),
            $payload->date('transaction_date'),
            $payload->integer('asking_price'),
            $payload->date('listing_date'),
            $payload->date('delisted_date'),
            $payload->integer('living_area_m2'),
            $payload->float('price_per_m2'),
            $payload->requireString('source'),
            $payload->integerOrString('price'),
            $payload->integerOrString('price_indexed'),
        );
    }

    public function transactionId(): int
    {
        return $this->transactionId;
    }

    public function priceAgreementDate(): ?DateTimeImmutable
    {
        return $this->priceAgreementDate;
    }

    public function transactionDate(): ?DateTimeImmutable
    {
        return $this->transactionDate;
    }

    public function askingPrice(): ?int
    {
        return $this->askingPrice;
    }

    public function listingDate(): ?DateTimeImmutable
    {
        return $this->listingDate;
    }

    public function delistedDate(): ?DateTimeImmutable
    {
        return $this->delistedDate;
    }

    public function livingAreaInSquareMeters(): ?int
    {
        return $this->livingAreaInSquareMeters;
    }

    public function pricePerSquareMeter(): ?float
    {
        return $this->pricePerSquareMeter;
    }

    public function source(): string
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
}
