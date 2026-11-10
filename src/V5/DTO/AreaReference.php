<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\AreaReference as AreaReferenceContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class AreaReference implements AreaReferenceContract
{
    public function __construct(
        private string $code,
        private ?string $name,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->requireString('code'),
            $payload->string('name'),
        );
    }

    public function code(): string
    {
        return $this->code;
    }

    public function name(): ?string
    {
        return $this->name;
    }
}
