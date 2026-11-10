<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO\Catalog;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Catalog\Pack as PackContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class Pack implements PackContract
{
    public function __construct(
        private string $id,
        private string $name,
        private float $priceInEuros,
        private int $credits,
        private ?float $creditPriceInEuros,
        private ?string $interval,
        private ?int $savingPercent,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->requireString('id'),
            $payload->requireString('name'),
            $payload->requireFloat('price_eur'),
            $payload->requireInteger('credits'),
            $payload->float('credit_price_eur'),
            $payload->string('interval'),
            $payload->integer('saving_percent'),
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function priceInEuros(): float
    {
        return $this->priceInEuros;
    }

    public function credits(): int
    {
        return $this->credits;
    }

    public function creditPriceInEuros(): ?float
    {
        return $this->creditPriceInEuros;
    }

    public function interval(): ?string
    {
        return $this->interval;
    }

    public function savingPercent(): ?int
    {
        return $this->savingPercent;
    }
}
