<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO\Catalog;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Catalog\LocalizedText as LocalizedTextContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class LocalizedText implements LocalizedTextContract
{
    public function __construct(
        private ?string $en,
        private ?string $nl,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->string('en'),
            $payload->string('nl'),
        );
    }

    public function en(): ?string
    {
        return $this->en;
    }

    public function nl(): ?string
    {
        return $this->nl;
    }
}
