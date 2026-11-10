<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use DateTimeImmutable;
use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Usage as UsageContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class Usage implements UsageContract
{
    public function __construct(
        private ?DateTimeImmutable $date,
        private ?string $endpoint,
        private ?string $keyId,
        private int $calls,
        private int $succeeded,
        private int $failed,
        private int $credits,
        private int $kadasterRecords,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->date('date'),
            $payload->string('endpoint'),
            $payload->string('key_id'),
            $payload->requireInteger('calls'),
            $payload->requireInteger('succeeded'),
            $payload->requireInteger('failed'),
            $payload->requireInteger('credits'),
            $payload->requireInteger('kadaster_records'),
        );
    }

    public function date(): ?DateTimeImmutable
    {
        return $this->date;
    }

    public function endpoint(): ?string
    {
        return $this->endpoint;
    }

    public function keyId(): ?string
    {
        return $this->keyId;
    }

    public function calls(): int
    {
        return $this->calls;
    }

    public function succeeded(): int
    {
        return $this->succeeded;
    }

    public function failed(): int
    {
        return $this->failed;
    }

    public function credits(): int
    {
        return $this->credits;
    }

    public function kadasterRecords(): int
    {
        return $this->kadasterRecords;
    }
}
