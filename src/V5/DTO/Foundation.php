<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Foundation as FoundationContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class Foundation implements FoundationContract
{
    public function __construct(
        private ?bool $atRisk,
        private ?string $subsoil,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->boolean('at_risk'),
            $payload->string('subsoil'),
        );
    }

    public function atRisk(): ?bool
    {
        return $this->atRisk;
    }

    public function subsoil(): ?string
    {
        return $this->subsoil;
    }
}
