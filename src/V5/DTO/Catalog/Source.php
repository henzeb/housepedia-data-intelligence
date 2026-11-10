<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO\Catalog;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Catalog\Source as SourceContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class Source implements SourceContract
{
    public function __construct(
        private string $id,
        private string $name,
        private ?string $cadence,
        private ?string $licence,
        private ?string $attribution,
        private ?string $url,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->requireString('id'),
            $payload->requireString('name'),
            $payload->string('cadence'),
            $payload->string('licence'),
            $payload->string('attribution'),
            $payload->string('url'),
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

    public function cadence(): ?string
    {
        return $this->cadence;
    }

    public function licence(): ?string
    {
        return $this->licence;
    }

    public function attribution(): ?string
    {
        return $this->attribution;
    }

    public function url(): ?string
    {
        return $this->url;
    }
}
