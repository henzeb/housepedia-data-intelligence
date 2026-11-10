<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\AreaParent as AreaParentContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class AreaParent implements AreaParentContract
{
    public function __construct(
        private string $code,
        private ?string $name,
        private string $type,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->requireString('code'),
            $payload->string('name'),
            $payload->requireString('type'),
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

    public function type(): string
    {
        return $this->type;
    }
}
