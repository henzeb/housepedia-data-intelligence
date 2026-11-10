<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Credits as CreditsContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class Credits implements CreditsContract
{
    public function __construct(
        private int $charged,
        private int $balance,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->requireInteger('charged'),
            $payload->requireInteger('balance'),
        );
    }

    public function charged(): int
    {
        return $this->charged;
    }

    public function balance(): int
    {
        return $this->balance;
    }
}
