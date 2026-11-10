<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use DateTimeImmutable;
use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\CreditBucket as CreditBucketContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class CreditBucket implements CreditBucketContract
{
    public function __construct(
        private string $kind,
        private int $remaining,
        private int $granted,
        private ?DateTimeImmutable $expiresAt,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->requireString('kind'),
            $payload->requireInteger('remaining'),
            $payload->requireInteger('granted'),
            $payload->date('expires_at'),
        );
    }

    public function kind(): string
    {
        return $this->kind;
    }

    public function remaining(): int
    {
        return $this->remaining;
    }

    public function granted(): int
    {
        return $this->granted;
    }

    public function expiresAt(): ?DateTimeImmutable
    {
        return $this->expiresAt;
    }
}
