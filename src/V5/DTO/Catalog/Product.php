<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO\Catalog;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Catalog\Product as ProductContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class Product implements ProductContract
{
    public function __construct(
        private string $id,
        private LocalizedText $name,
        private LocalizedText $description,
        private string $family,
        private string $method,
        private string $path,
        private int $credits,
        private ?string $multiplier,
        private string $status,
        private ?int $cacheMaxAge,
        private ?LocalizedText $docsUrl,
        private bool $contract,
        private ?string $contact,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->requireString('id'),
            $payload->requireNested('name', LocalizedText::from(...)),
            $payload->requireNested('description', LocalizedText::from(...)),
            $payload->requireString('family'),
            $payload->requireString('method'),
            $payload->requireString('path'),
            $payload->requireInteger('credits'),
            $payload->string('multiplier'),
            $payload->requireString('status'),
            $payload->integer('cache_max_age'),
            $payload->nested('docs_url', LocalizedText::from(...)),
            $payload->boolean('contract') ?? false,
            $payload->string('contact'),
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): LocalizedText
    {
        return $this->name;
    }

    public function description(): LocalizedText
    {
        return $this->description;
    }

    public function family(): string
    {
        return $this->family;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function credits(): int
    {
        return $this->credits;
    }

    public function multiplier(): ?string
    {
        return $this->multiplier;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function cacheMaxAge(): ?int
    {
        return $this->cacheMaxAge;
    }

    public function docsUrl(): ?LocalizedText
    {
        return $this->docsUrl;
    }

    public function contract(): bool
    {
        return $this->contract;
    }

    public function contact(): ?string
    {
        return $this->contact;
    }
}
