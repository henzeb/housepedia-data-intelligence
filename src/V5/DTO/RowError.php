<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\RowError as RowErrorContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class RowError implements RowErrorContract
{
    public function __construct(
        private string $code,
        private string $message,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->requireString('code'),
            $payload->requireString('message'),
        );
    }

    public function code(): string
    {
        return $this->code;
    }

    public function message(): string
    {
        return $this->message;
    }
}
